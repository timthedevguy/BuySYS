<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserPreferencesEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Entity\LineItemEntity;
use AppBundle\Entity\TransactionEntity;
use AppBundle\Helper\MarketHelper;

class SRPController extends Controller
{
    /**
     * @Route("/srp", name="srp")
     */
    public function indexAction(Request $request)
    {	
		$stats = [
			"all" => $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findCountByUserAndTypes($this->getUser(), ["SRP"]),
			"pending" => $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findCountByUserTypesAndStatus($this->getUser(), ["SRP"], "Pending")
		];
		
		$incomePending = 0;
		$pendingTransactions = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllByUserTypesAndStatus($this->getUser(), ["SRP"], "Pending");
		if($pendingTransactions && count($pendingTransactions) > 0)
			foreach($pendingTransactions as $pending)
				$incomePending += $pending->getNet();
	
        return $this->render('srp/srp.html.twig', [
            'base_dir' => 'test',
			'page_name' => 'My SRP Requests',
			'sub_text' => null,
			'stats' => $stats,
			'incomePending' => $incomePending]);
    }
	
    /**
     * @Route("/srp/estimate", name="srp_estimate")
     */
    public function estimateAction(Request $request)
    {	
		$zkillValue = $insuranceValue = $netLoss = $srpOffered = null;
		$orderID = $hasInvalid = false;
		
        $zkillID = $request->request->get('zkillID');
		
		if(!is_numeric($zkillID)) {
			$hasInvalid = true;
		}
		else {
			$zkillInfo = json_decode(file_get_contents("https://zkillboard.com/api/killID/".$zkillID."/json/"), true);
			if($zkillInfo && isset($zkillInfo[0]) && isset($zkillInfo[0]['killID']) && $zkillInfo[0]['killID'] == $zkillID) {
				$lossTypeID = $zkillInfo[0]['victim']['shipTypeID'];
				$type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($lossTypeID);
				
				$itemIDs = [$lossTypeID];
				foreach($zkillInfo[0]['items'] as $item) {
					$itemIDs []= $item['typeID'];
				}
				$itemIDs = array_unique($itemIDs);
				$itemsAtJita = $this->get('market')->getBuybackPricesForTypes($itemIDs);
			
				$zkillValue = $zkillInfo[0]['zkb']['totalValue'];
				$insuranceValue = 0;
				$netLoss = ($zkillValue - $insuranceValue);
				$srpOffered = 100;
			
				//get DB manager
				$em = $this->getDoctrine()->getManager('default');

				//build transaction
				$transaction = new TransactionEntity();
				$transaction->setUser($this->getUser());
				$transaction->setType("SRP"); //will reset to PS if accepted with shares
				$transaction->setIsComplete(false);
				$orderID = $transaction->getType() . $zkillID;
				$transaction->setOrderId($orderID);
				$transaction->setCreated(new \DateTime("now"));
				$transaction->setStatus("Estimate");
				$em->persist($transaction);
				
				// Add ship as lost item
				$lineItem = new LineItemEntity();
				$lineItem->setTypeId($lossTypeID);
				$lineItem->setName($type->getTypeName());
				$lineItem->setQuantity(1);
				$lineItem->setMarketPrice($itemsAtJita[$lossTypeID]['market']);
				$lineItem->setTax(0.0);
				$lineItem->setGrossPrice($itemsAtJita[$lossTypeID]['market']);
				$lineItem->setNetPrice($itemsAtJita[$lossTypeID]['adjusted']);
				$em->persist($lineItem);
				$transaction->addLineitem($lineItem);
				// End add ship as lost item
				
				foreach($zkillInfo[0]['items'] as $item)
				{
					$type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($item['typeID']);
					$lineItem = new LineItemEntity();
					$lineItem->setTypeId($item['typeID']);
					$lineItem->setName($type->getTypeName());
					$lineItem->setQuantity($item['qtyDestroyed'] + $item['qtyDropped']);
					if(isset($itemsAtJita[$item['typeID']]))
						$lineItem->setMarketPrice(floatval($itemsAtJita[$item['typeID']]['market']));
					$lineItem->setTax(0.0);
					$lineItem->setGrossPrice($lineItem->getMarketPrice());
					$lineItem->setNetPrice($lineItem->getMarketPrice());
					$em->persist($lineItem);
					$transaction->addLineitem($lineItem);
				}
				// End add all killmail loot lost items
				
				// Reenforce our SRP Offer Amount
				$srpOffered = $transaction->getNet();
				$transaction->setNet($srpOffered);

				$em->flush();
			}
			else {
				$hasInvalid = true;
			}
		}
	
        return $this->render('srp/results.html.twig', [
            "zkillValue" => $zkillValue,
			"insuranceValue" => $insuranceValue,
			"netLoss" => $netLoss,
			"srpOffered" => $srpOffered,
			"hasInvalid" => $srpOffered <= 0,
			"orderId" => $orderID]);
    }
    /**
     * @Route("/srp/admin", name="srp_admin")
     */
    public function adminAction(Request $request)
    {	
        return $this->render('srp/srp.html.twig', [
            'base_dir' => 'test',
			'page_name' => 'SRP Approval Queue',
			'sub_text' => 'Accept or Deny SRP Requests',
			'userCharacterName' => $this->getUser()->getUsername(),
			'history' => [],
			'hiddenTypes' => array(670)]);
    }
	
	/**
     * @Route("/srp/history", name="ajax_srp_history")
     */
    public function ajax_GetSRPHistory()
    {
		$history = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllByUserAndTypes($this->getUser(), ['SRP']);
		
        return $this->render('srp/history.html.twig', array('history' => $history));
    }
	
	/**
     * @Route("/srp/accept", name="ajax_accept_srp")
     */
    public function ajax_AcceptAction(Request $request)
    {
        // Get our list of Items
        $order_id = $request->request->get('orderId');

        // Pull data from DB
        $em = $this->getDoctrine()->getManager('default');
        $transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

        //update status
        $transaction->setStatus("Pending");

        $em->flush();

        $share_value = 0;

        return $this->render('srp/accepted.html.twig', [
			'srpOffered' => $transaction->getNet(),
			'transaction' => $transaction,
			'shares' => 0,
			'share_value' => 0]);
    }

    /**
     * @Route("/srp/decline", name="ajax_decline_srp")
     */
    public function ajax_DeclineAction(Request $request)
    {
        // Get our list of Items
        $order_id = $request->request->get('orderId');

        // Pull data from DB
        $em = $this->getDoctrine()->getManager('default');
        $transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

        //delete transaction
        $em->remove($transaction);

        $em->flush();

        return new Response();
    }


}
