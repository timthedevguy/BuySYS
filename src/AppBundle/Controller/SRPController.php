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

use AppBundle\Model\MarketRequestModel;
use AppBundle\Form\AllianceMarketForm;
use AppBundle\Model\TransactionSummaryModel;

class SRPController extends Controller
{
	const groupIDToMaximums = [
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
	
	private function lineItemsToSRP(&$lineItems = [], $killID = null, $estLossValue = 0) {		
		$srpOffered = 0;
		$lossValue = 0;
		$lossTypeID = null;
		$lossType = null;
		$lossInsuranceProfit = 0;
		$reason = null;
        $hasInvalid = $hasWarning = false;
		$typeIDs = [];
		
		if(empty($killID)) {
			$killID = "Estimate";
		}
		
		if(!$lineItems || count($lineItems) == 0) {
			$hasInvalid = true;
			$reason = "There were no items parsed from your input. [".$lineItems[0]->getTypeId()."] ".print_r($lineItems, true)."...";
			return [
				"lossType" => null,
				"lossGross" => null,
				"lossNet" => null,
				"lossInsurance" => null,
				"lossSRP" => null,
				"orderId" => null,
				"hasInvalid" => $hasInvalid,
				"hasWarning" => $hasWarning,
				"reason" => $reason
			];
		}
		
		/* FIND THE SHIP! */
		foreach($lineItems as $lineItem) {
			if(!method_exists($lineItem, 'getTypeId'))
				continue;
			$typeID = $lineItem->getTypeId();
			$typeIDs []= $typeID;
			$type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($typeID);
			if($type) {
				$groupID = $type->getGroupId();
				if(isset(self::groupIDToMaximums[$groupID])) {
					// Is a ship
					if($lossTypeID === null) {
						$lossTypeID = $typeID;
						$lossType = $type;
					}
					else {
						$hasInvalid = true;
						$reason = "Estimate contains more than one ship. This is naughty.";
						return [
							"lossType" => null,
							"lossGross" => null,
							"lossNet" => null,
							"lossInsurance" => null,
							"lossSRP" => null,
							"orderId" => null,
							"hasInvalid" => $hasInvalid,
							"hasWarning" => $hasWarning,
							"reason" => $reason
						];
					}
				}
			}
		}
		
		if(empty($lossTypeID)) {
			$hasInvalid = true;
			$reason = "Estimate contains no ship. This is silly.";
			return [
				"lossType" => null,
				"lossGross" => null,
				"lossNet" => null,
				"lossInsurance" => null,
				"lossSRP" => null,
				"orderId" => null,
				"hasInvalid" => $hasInvalid,
				"hasWarning" => $hasWarning,
				"reason" => $reason
			];
		}
		
		$orig = $this->get('helper')->getSetting("source_type", "P");
		$this->get('helper')->setSetting("source_type", "sell", "P");
        $this->get('market')->forceCacheUpdateForTypes($typeIDs);	
        $typePrices = $this->get('market')->getBuybackPricesForTypes($typeIDs);
        $this->get('market')->forceCacheUpdateForTypes($typeIDs);	
		$this->get('helper')->setSetting("source_type", $orig, "P");
	
		//get DB manager
		$em = $this->getDoctrine()->getManager('default');
		
		$insuranceData = $this->getDoctrine('default')->getRepository('AppBundle:InsurancesEntity')->getInsuranceDataByTypeIDAndLevel($lossTypeID);
		$insuranceValue = $insuranceValue = ($insuranceData->getInsurancePayout() - $insuranceData->getInsuranceCost());
		
		$srpOffered = self::groupIDToMaximums[$lossType->getGroupId()];

		///build transaction
        $transaction = new TransactionEntity();
        $transaction->setUser($this->getUser());
        $transaction->setType("SRP"); //will reset to PS if accepted with shares
        $transaction->setIsComplete(false);
        $transaction->setOrderId($transaction->getType() . $killID);
        $transaction->setGross(0);
        $transaction->setNet(0);
        $transaction->setCreated(new \DateTime("now"));
        $transaction->setStatus("Estimate");
        $em->persist($transaction);
		
        foreach($lineItems as &$lineItem)
        {
			if(!method_exists($lineItem, 'getTypeId'))
				continue;
			$typeID = $lineItem->getTypeId();
			$type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($typeID);
            if($type) {
				$lineItem->setName($type->getTypeName());
				$lineItem->setTax(0);
				$lineItem->setMarketPrice(0);
				$lineItem->setGrossPrice(0);
				$lineItem->setNetPrice(0);
				if(isset($typePrices[$lineItem->getTypeId()]) && $typePrices[$lineItem->getTypeId()]['market'] > 0) {
					$lossValue += $lineItem->getQuantity() * $typePrices[$lineItem->getTypeId()]['market'];
					$lineItem->setMarketPrice($typePrices[$lineItem->getTypeId()]['market']);
					$lineItem->setGrossPrice($lineItem->getQuantity() * $typePrices[$lineItem->getTypeId()]['market']);
					$lineItem->setNetPrice($lineItem->getQuantity() * $typePrices[$lineItem->getTypeId()]['adjusted']);
				}
				else {
					$hasWarning = true;
					$reason = "Some items were not evaluated correctly. This estimation may be off significantly...";
				}
                $em->persist($lineItem);
                $transaction->addLineitem($lineItem);
            }
			else
				$hasInvalid |= true;
			unset($lineItem);
        }
		
		// Reenforce our SRP Offer Amount
		$srpOffered = min($srpOffered, 500000000);
		$transaction->setGross($estLossValue > 0 ? $estLossValue : $lossValue);		
		
		/* For larger ships, we're not going to SRP more than the net loss of your ship.after insurance  */
		if($transaction->getGross() >= 50000000 && $transaction->getGross() - $insuranceValue < $srpOffered) {
			$srpOffered = min($srpOffered, $transaction->getGross() - $insuranceValue);
		}
		
		if($srpOffered <= 0) {
			$hasInvalid = true;
			$reason = trim(trim(trim($reason." | No SRP can be given for this loss."), "|"));
		}
		
		$transaction->setNet($srpOffered);

		$em->flush();
		
		return [
			"lossType" => $lossType,
			"lossGross" => $transaction->getGross(),
			"lossNet" => max($transaction->getGross() - $insuranceValue, 0),
			"lossInsurance" => $insuranceValue,
			"lossSRP" => $srpOffered,
			"orderId" => $transaction->getOrderId(),
			"hasInvalid" => $hasInvalid,
			"hasWarning" => $hasWarning,
			"reason" => $reason
		];
		
	}
	
    /**
     * @Route("/srp", name="srp")
     */
    public function indexAction(Request $request)
    {	
        $fModel = new MarketRequestModel();
        $form = $this->createForm(AllianceMarketForm::class, $fModel);
        $form->handleRequest($request);
		
        $oSRP = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllByUserTypesAndExcludeStatus($this->getUser(), ['SRP'], "Estimate");
        $srpSummary = new TransactionSummaryModel($oSRP);
	
        return $this->render('srp/srp.html.twig', [
            'base_dir' => 'test',
			'page_name' => 'My SRP',
			'sub_text' => null,
			'form' => $form->createView(),
			'srpSummary' => $srpSummary]);
    }
	
    /**
     * @Route("/srp/quick_estimate", name="ajax_srp_quick_estimate")
     */
    public function quickEstimateAction(Request $request)
	{
		$fModel = new MarketRequestModel();
        $form = $this->createForm(AllianceMarketForm::class, $fModel);
        $form->handleRequest($request);

        // Parse form input
        $items = $this->get('parser')->GetLineItemsFromPasteData($fModel->getItems());

        // Check to make sure it parsed correctly
        if($items == null || count($items) <= 0) {
            return $this->render('srp/novalid.html.twig');
        }

        $results = self::lineItemsToSRP($items);

        return $this->render('srp/results.html.twig', [
            "lossValue" => $results['lossGross'],
			"insuranceValue" => $results['lossInsurance'],
			"netLoss" => $results['lossNet'],
			"srpOffered" => $results['lossSRP'],
			"hasInvalid" => $results['hasInvalid'],
			"hasWarning" => $results['hasWarning'],
			"reason" => $results['reason'],
			"orderId" => $results['orderId'],
			"items" => $items]);
	}
	
    /**
     * @Route("/srp/estimate", name="ajax_srp_estimate")
     */
    public function estimateAction(Request $request)
    {	
		$hasInvalid = $reason = false;		
        $killID = $request->request->get('killID');
		
		if(!is_numeric($killID)) {
			$hasInvalid = true;
			$reason = "Invalid ZKillboard ID";
		}
		else {
			$zkillInfo = json_decode(file_get_contents("https://zkillboard.com/api/killID/".$killID."/json/"), true);
			if($zkillInfo && isset($zkillInfo[0]) && isset($zkillInfo[0]['killID']) && $zkillInfo[0]['killID'] == $killID) {
				$lossTypeID = $zkillInfo[0]['victim']['shipTypeID'];
				$type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($lossTypeID);
				$groupID = $type->getGroupId();
				$group = $this->getDoctrine()->getRepository('EveBundle:GroupsEntity', 'evedata')->findOneByGroupID($groupID);
				
				$items = [];
				$lineItem = new LineItemEntity();
				$lineItem->setTypeId($lossTypeID);
				$lineItem->setQuantity(1);
				$lineItem->setMarketPrice(0);
				$lineItem->setGrossPrice(0);
				$lineItem->setNetPrice(0);
				$lineItem->setTax(0);
				$items []= $lineItem;
				foreach($zkillInfo[0]['items'] as $item) {
					$lineItem = new LineItemEntity();
					$lineItem->setTypeId($item['typeID']);
					$lineItem->setQuantity($item['qtyDropped']+$item['qtyDestroyed']);
					$lineItem->setMarketPrice(0);
					$lineItem->setGrossPrice(0);
					$lineItem->setNetPrice(0);
					$lineItem->setTax(0);
					$items []= $lineItem;
				}
				$results = self::lineItemsToSRP($items, $killID, $zkillInfo[0]['zkb']['totalValue']);

				return $this->render('srp/results.html.twig', [
					"lossValue" => $results['lossGross'],
					"insuranceValue" => $results['lossInsurance'],
					"netLoss" => $results['lossNet'],
					"srpOffered" => $results['lossSRP'],
					"hasInvalid" => $results['hasInvalid'],
					"hasWarning" => $results['hasWarning'],
					"reason" => $results['reason'],
					"orderId" => $results['orderId'],
					"items" => $items]);
			}
			else {
				$hasInvalid = true;
				$reason = "We were unable to pull this kill from ZKillboard.com";
			}
		}
	
        return $this->render('srp/results.html.twig', [
            "lossValue" => null,
			"insuranceValue" => null,
			"netLoss" => null,
			"srpOffered" => null,
			"hasInvalid" => $hasInvalid,
			"hasWarning" => false,
			"reason" => $reason,
			"orderId" => null]);
    }
    /**
     * @Route("/srp/admin", name="srp_admin")
     */
    public function adminAction(Request $request)
    {
		$last500Transactions = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findValidTransactionsByTypesAndOrderedByDate(['SRP'], 500);
        $last500Summary = new TransactionSummaryModel($last500Transactions);

        $totalSummary = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findTransactionTotalsByTypesAndStatus(['SRP'], "Complete");

        return $this->render('srp/admin.html.twig', array(
            'page_name' => 'SRP Queue', 'sub_text' => 'Requests', 'last500Transactions' => $last500Transactions,
            'last500Summary' => $last500Summary, 'totalSummary' => $totalSummary
        ));
    }
	
	/**
     * @Route("/srp/history", name="ajax_srp_history")
     */
    public function ajax_GetSRPHistory()
    {
		$history = $this->getDoctrine()
					->getManager('default')
					->getRepository('AppBundle:TransactionEntity', 'default')
					->findAllByUserTypesAndExcludeStatus($this->getUser(), ['SRP']);

		
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