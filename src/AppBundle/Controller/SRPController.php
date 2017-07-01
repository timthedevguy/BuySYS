<?php
namespace AppBundle\Controller;

use AppBundle\Entity\UserPreferencesEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\LineItemEntity;
use AppBundle\Entity\TransactionEntity;
use AppBundle\Helper\MarketHelper;
use EveBundle\Entity\TypeEntity;
use AppBundle\Entity\InsurancesEntity;

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
     * @Route("/srp/estimate", name="ajax_srp_estimate")
     */
    public function estimateAction(Request $request)
    {	
		$zkillValue = $insuranceValue = $netLoss = $srpOffered = null;
		$orderID = $hasInvalid = $reason = false;
		
        $zkillID = $request->request->get('zkillID');
		
		if(!is_numeric($zkillID)) {
			$hasInvalid = false;
			$reason = "Invalid ZKillboard ID";
		}
		else {
			$zkillInfo = json_decode(file_get_contents("https://zkillboard.com/api/killID/".$zkillID."/json/"), true);
			if($zkillInfo && isset($zkillInfo[0]) && isset($zkillInfo[0]['killID']) && $zkillInfo[0]['killID'] == $zkillID) {
				$lossTypeID = $zkillInfo[0]['victim']['shipTypeID'];
				$type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($lossTypeID);
				$groupID = $type->getGroupId();
				$group = $this->getDoctrine()->getRepository('EveBundle:GroupsEntity', 'evedata')->findOneByGroupID($groupID);
				
				$itemIDs = [$lossTypeID];
				foreach($zkillInfo[0]['items'] as $item) {
					$itemIDs []= $item['typeID'];
				}
				$itemIDs = array_unique($itemIDs);
				$itemsAtJita = $this->get('market')->getBuybackPricesForTypes($itemIDs);
			
				$zkillValue = $zkillInfo[0]['zkb']['totalValue'];				
			
				//get DB manager
				$em = $this->getDoctrine()->getManager('default');
				
				$insuranceData = $this->getDoctrine('default')->getRepository('AppBundle:InsurancesEntity')->getInsuranceDataByTypeIDAndLevel($lossTypeID);
				$insuranceValue = $insuranceValue = ($insuranceData->getInsurancePayout() - $insuranceData->getInsuranceCost());
				
				$netLoss = ($zkillValue - $insuranceValue);
				
				$groupIDToMaximums = [
					25 => 5000000, //"Frigate",
					26 => 15000000, //"Cruiser",
					27 => 115000000, //"Battleship",
					324 => 15000000, //"Assault Frigate",
					358 => 100000000, //"Heavy Assault Cruiser",
					419 => 35000000, //"Combat Battlecruiser",
					420 => 10000000, //"Destroyer",
					485 => 500000000, //"Dreadnought",
					540 => 115000000, //"Command Ship",
					541 => 25000000, //"Interdictor",
					547 => 500000000, //"Carrier",
					830 => 20000000, //"Covert Ops",
					831 => 25000000, //"Interceptor",
					832 => 250000000, //"Logistics",
					833 => 100000000, //"Force Recon Ship",
					834 => 25000000, //"Stealth Bomber",
					893 => 15000000, //"Electronic Attack Ship",
					894 => 100000000, //"Heavy Interdiction Cruiser",
					898 => 150000000, //"Black Ops",
					906 => 100000000, //"Combat Recon Ship",
					963 => 100000000, //"Strategic Cruiser",
					1201 => 35000000, //"Attack Battlecruiser",
					1305 => 25000000, //"Tactical Destroyer",
					1527 => 100000000, //"Logistics Frigate",
					1534 => 25000000, //"Command Destroyer",
					1538 => 500000000, //"Force Auxiliary",
				];
				
				if(isset($groupIDToMaximums[$groupID])) {
					$srpOffered = $groupIDToMaximums[$groupID];
				}
				else {
					$srpOffered = 0;
					$reason = "This type of ship (".$group->getGroupName().") is not accepted in our SRP Program.";
				}

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
				$lineItem->setNetPrice($itemsAtJita[$lossTypeID]['market']);
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
				$srpOffered = min($srpOffered, 500000000);
				$transaction->setNet($srpOffered);

				$em->flush();
			}
			else {
				$hasInvalid = false;
				$reason = "We were unable to pull this kill from ZKillboard.com";
			}
		}
	
        return $this->render('srp/results.html.twig', [
            "zkillValue" => $zkillValue,
			"insuranceValue" => $insuranceValue,
			"netLoss" => $netLoss,
			"srpOffered" => $srpOffered,
			"hasInvalid" => $srpOffered <= 0,
			"reason" => $reason,
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
		$history = $this->getDoctrine()
					->getManager('default')
					->getRepository('AppBundle:TransactionEntity', 'default')
					->findAllByUserAndTypes($this->getUser(), ['SRP']);
		
        return $this->render('srp/history.html.twig', array('history' => $history));
    }
	
	/**
     * @Route("/srp/accept", name="ajax_accept_srp")
     */
    public function ajax_AcceptAction(Symfony\Component\HttpFoundation\Request $request)
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
