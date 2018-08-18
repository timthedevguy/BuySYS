<?php

namespace AppBundle\Controller;

use AppBundle\Entity\RuleEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//use Symfony\Component\Console\Input\ArrayInput;
//use Symfony\Component\Console\Output\NullOutput;
//use Symfony\Component\Validator\Constraints\Time;
//use Symfony\Bundle\FrameworkBundle\Console\Application;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Entity\EstimateEntity;
use AppBundle\Form\AllianceMarketForm;
use AppBundle\Model\MarketRequestModel;
use AppBundle\Model\TransactionSummaryModel;
//use AppBundle\Model\BuyBackItemModel;
use AppBundle\Entity\LineItemEntity;
use AppBundle\Entity\TransactionEntity;
use AppBundle\Helper\MarketHelper;

class BuybackController extends Controller {

	/**
	 * @Route("/estimate", name="estimate")
	 */
	public function estimateAction(Request $request)
	{
		$rawItems = $request->request->get('items');
		$items = $this->get('parser')->GetLineItemsFromPasteData($rawItems);
		$hasInvalid = false;

		// Check to make sure it parsed correctly
		if ($items == null || count($items) <= 0)
		{
			$this->addFlash('error', 'No Valid Items');
		}

		$typeIds = array();

		// Grab our TypeID's to pull pricing for
		foreach ($items as $item)
		{
			$typeIds[] = $item['typeid'];
		}

		// Populate Pricing information
		$typePrices = $this->get('market')->getBuybackPricesForTypes($typeIds);
		$offer = 0;

		// Populate our Line Items from Pricing information
		for ($i = 0; $i < count($items); $i++)
		{
			// Check if price is -1, means Can Buy is False
			if ($typePrices[$items[$i]['typeid']]['adjusted'] == -1 | $items[$i]['typeid'] == 0)
			{
				// Set to all 0 and mark as invalid
				$items[$i]['unitPrice'] = 0;
				$items[$i]['netPrice'] = 0;
				$items[$i]['grossPrice'] = 0;
				$items[$i]['isValid'] = false;

				$hasInvalid = true;
			} else
			{
				// Set prices
				$items[$i]['unitPrice'] = $typePrices[$items[$i]['typeid']]['taxed'] / $typePrices[$items[$i]['typeid']]['data']['options']['portionSize'];
				$items[$i]['netPrice'] = $items[$i]['quantity'] * ($typePrices[$items[$i]['typeid']]['taxed'] / $typePrices[$items[$i]['typeid']]['data']['options']['portionSize']);
				$items[$i]['grossPrice'] = $items[$i]['quantity'] * ($typePrices[$items[$i]['typeid']]['adjusted'] / $typePrices[$items[$i]['typeid']]['data']['options']['portionSize']);

				$offer += $items[$i]['netPrice'];
			}
		}

		$em = $this->getDoctrine()->getManager('default');

		$data = array();
		$data['items'] = $items;
		$data['details'] = $typePrices;
		dump($data);
		$this->getDoctrine()->getRepository('AppBundle:EstimateEntity', 'default')->deleteByUser($this->getUser()->getId());

		$estimate = new EstimateEntity();
		$estimate->setUserId($this->getUser()->getId());
		$estimate->setData($data);
		$em->persist($estimate);
		$em->flush();

		return $this->render('buyback/estimate.html.twig', array(
			'data'       => $data,
			'offer'      => $offer,
			'eId'        => $estimate->getId(),
			'hasInvalid' => $hasInvalid
		));
	}

	/**
	 * @Route("/estimate/{id}/accept", name="estimate-accept")
	 */
	public function estimateAcceptAction(Request $request, $id)
	{
		$estimate = $this->getDoctrine()->getRepository('AppBundle:EstimateEntity', 'default')->find($id);

		if ($estimate)
		{
			if ($estimate->getUserId() == $this->getUser()->getId())
			{
				$data = $estimate->getData();

				$transaction = new TransactionEntity();
				$transaction->setUser($this->getUser());
				$transaction->setType('P');
				$transaction->setIsComplete(false);
				$transaction->setOrderId($transaction->getType() . uniqid());
				$transaction->setStatus('Pending');
				$transaction->setCreated(new \DateTime("now"));
				$transaction->setNet(0);
				$transaction->setGross(0);

				$this->getDoctrine()->getManager('default')->persist($transaction);

				foreach ($data['items'] as $entry)
				{
					if ($entry['isValid'] == true)
					{
						$item = new LineItemEntity();
						$item->setTypeId($entry['typeid']);
						$item->setIsValid($entry['isValid']);
						$item->setGrossPrice($entry['grossPrice']);
						$item->setMarketPrice($entry['unitPrice']);
						$item->setName($entry['name']);
						$item->setNetPrice($entry['netPrice']);
						$item->setQuantity($entry['quantity']);

						$this->getDoctrine()->getManager('default')->persist($item);

						$transaction->addLineitem($item);
					}
				}

				$this->getDoctrine()->getManager('default')->flush();

				return $this->render('buyback/accept.html.twig', array(
					'orderId' => $transaction->getOrderId(),
					'amount'  => $transaction->getNet()
				));

			} else
			{
				return $this->render('elements/error.html.twig', array(
					'title'   => 'Invalid User!',
					'message' => 'You do not match the user who submitted this estimate.  BEGONE!!!'
				));
			}
		} else
		{
			// Estimate doesn't exist
			return $this->render('elements/error.html.twig', array(
				'title'   => "Estimate doesn't exist!",
				'message' => 'The estimate you are attempting to accept no longer exists.  Estimates only exist until the next estimate is appraised.'
			));
		}

		return $this->render('buyback/accept.html.twig', array());
	}

	/**
	 * @Route("/estimate/{id}/details/{itemid}", name="estimate-item-details", defaults={"itemid" = null})
	 */
	public function estimateItemDetailsAction(Request $request, $id, $itemid)
	{
		$estimate = $this->getDoctrine()->getRepository('AppBundle:EstimateEntity', 'default')->find($id);

		if ($estimate)
		{
			$data = $estimate->getData();
			$item = $data['items'][$itemid];
			$details = $data['details'][$item['typeid']];

			$ruleIds = explode(',', $details['data']['options']['rules']);
			$readableRules = array();
			dump($ruleIds);
			foreach ($ruleIds as $ruleId)
			{
				if ($ruleId == 0)
				{
					if ($this->get('helper')->getSetting('value_minerals') == 1)
					{
						$readableRules[] = "All items will be Refined by Default";
					} else
					{
						$readableRules[] = "No Ore will be reprocessed";
					}

					if ($this->get('helper')->getSetting('value_salvage') == 1)
					{
						$readableRules[] = "All items will be Salvaged by Default";
					} else
					{
						$readableRules[] = "No Salvage will be reprocessed";
					}

					if ($this->get('helper')->getSetting('default_buyaction_deny') == 0)
					{
						$readableRules[] = "All items will be bought by Default";
					} else
					{
						$readableRules[] = "All items will be rejected by Default";
					}
				} else {

					/** @var RuleEntity $rule */
					$rule = $this->getDoctrine()->getRepository('AppBundle:RuleEntity')->findOneBySort($ruleId);
					$ruleText = '';

					if($rule->getTarget() == 'type') {
						$ruleText = "Item '".$rule->getTargetName()."' ";
					} else if($rule->getTarget() == 'group') {
						$ruleText = "Items in group '".$rule->getTargetName()."' ";
					} else if($rule->getTarget() == 'marketgroup') {
						$ruleText = "Items in market group '".$rule->getTargetName()."' ";
					}

					if($rule->getAttribute() == 'tax') {
						$ruleText = $ruleText . ' has a Tax adjustment of ' . $rule->getValue();
					} else if($rule->getAttribute() == 'price') {
						$ruleText = $ruleText . ' has an admin set price of ' . $rule->getValue();
					} else if($rule->getAttribute() == 'canbuy') {
						if($rule->getValue() == 1) {
							$ruleText = $ruleText . ' can be bought';
						} else {
							$ruleText = $ruleText . ' cannot be bought';
						}
					} else if($rule->getAttribute() == 'isrefined') {
						if($rule->getValue() == 1) {
							$ruleText = $ruleText . ' will be reprocessed';
						} else {
							$ruleText = $ruleText . ' will not be reprocessed';
						}
					}

					$readableRules[] = $ruleText;
				}
			}

			return $this->render('buyback/_details.html.twig', array(
				'item'    => $item,
				'details' => $details,
				'rules'   => $readableRules
			));

		} else
		{
			// Estimate doesn't exist
			return $this->render('elements/error.html.twig', array(
				'title'   => "Estimate doesn't exist!",
				'message' => 'The estimate you are attempting to accept no longer exists.  Estimates only exist until the next estimate is appraised.'
			));
		}


	}


	/**
	 * @Route("/alliance_market/sellorders", name="buyback")
	 */
	public function buybackAction(Request $request)
	{
		return $this->action($request, 'P', 'Sell Orders', 'Sell your stuff!');
	}

	private function action(Request &$request, string $transactionType, string $pageName = 'Market Orders', string $subText = 'Create an Order!')
	{
		$form = $this->createForm(AllianceMarketForm::class, new MarketRequestModel());
		$form->handleRequest($request);

		$eveCentralOK = $this->get("helper")->getSetting("eveCentralOK", "global");
		$oTransaction = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllByUserAndTypes($this->getUser(), array($transactionType));
		$news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAllOrderedByDate();

		$transactionSummary = new TransactionSummaryModel($oTransaction);

		return $this->render('alliance_market/index.html.twig', array(
			'transactionType' => $transactionType, 'page_name' => $pageName, 'sub_text' => $subText, 'form' => $form->createView(),
			'oTransaction'    => $oTransaction, 'transactionSummary' => $transactionSummary, 'news' => $news, 'eveCentralOK' => $eveCentralOK));
	}

	/**
	 * @Route("/alliance_market/buyorders", name="sales")
	 */
	public function salesAction(Request $request)
	{
		return $this->action($request, 'S', 'Buy Orders', 'Place a buy order!');
	}

	/**
	 * @Route("/alliance_market/estimate", name="ajax_alliance_market_estimate")
	 */
	public function ajax_EstimateAction(Request $request)
	{
		// Setup model and form
		$marketRequest = new MarketRequestModel();
		$form = $this->createForm(AllianceMarketForm::class, $marketRequest);
		$form->handleRequest($request);
		$rawRequestItems = $marketRequest->getItems();
		$transactionType = $request->request->get('transactionType');

		$transactionType = $request->request->get('transactionType');

		if (empty($rawRequestItems))
		{
			return $this->render('alliance_market/novalid.html.twig');
		} else
		{
			// Parse form input
			$items = $this->get('parser')->GetLineItemsFromPasteData($rawRequestItems);

			// Check to make sure it parsed correctly
			if ($items == null || count($items) <= 0)
			{
				return $this->render('alliance_market/novalid.html.twig');
			}

			$typeIds = array();

			// Grab our TypeID's to pull pricing for
			foreach ($items as $item)
			{
				$typeIds[] = $item->getTypeId();
			}

			$typePrices = $this->get('market')->getBuybackPricesForTypes($typeIds, $transactionType);

			foreach ($items as $item)
			{
				// Check if price is -1, means Can Buy is False
				if ($typePrices[$item->getTypeId()]['adjusted'] == -1)
				{
					// Set to all 0 and mark as invalid
					$item->setMarketPrice(0);
					$item->setGrossPrice(0);
					$item->setNetPrice(0);
					$item->setTax(0);
					$item->setIsValid(false);
				} else
				{
					// Set prices
					$item->setMarketPrice($typePrices[$item->getTypeId()]['adjusted']);
					$item->setGrossPrice($item->getQuantity() * $typePrices[$item->getTypeId()]['market']);
					$item->setNetPrice($item->getQuantity() * $typePrices[$item->getTypeId()]['adjusted']);
					$item->setTax(0);
				}
			}

			//insert into DB and return quote

			//get DB manager
			$em = $this->getDoctrine()->getManager('default');

			//build transaction
			$transaction = new TransactionEntity();
			$transaction->setUser($this->getUser());

			$transaction->setType($transactionType);

			$transaction->setIsComplete(false);
			$transaction->setOrderId($transaction->getType() . uniqid());
			$transaction->setGross(0);
			$transaction->setNet(0);
			$transaction->setCreated(new \DateTime("now"));
			$transaction->setStatus("Estimate");

			$em->persist($transaction);

			$hasInvalid = false;
			foreach ($items as $lineItem)
			{
				if ($lineItem->getIsValid())
				{
					$em->persist($lineItem);
					$transaction->addLineitem($lineItem);
				} else
				{
					$hasInvalid = true;
				}
			}

			$em->flush();
		}


		return $this->render('alliance_market/estimate.html.twig', Array('items'      => $items, 'total' => $transaction->getNet(),
																		 'hasInvalid' => $hasInvalid, 'orderId' => $transaction->getOrderId(), 'transactionType' => $transactionType));
	}

	/**
	 * @Route("/alliance_market/accept", name="ajax_alliance_market_accept")
	 */
	public function ajax_AcceptAction(Request $request)
	{
		// Get our list of Items
		$order_id = $request->request->get('orderId');
		$transactionType = $request->request->get('transactionType');

		// Pull data from DB
		$em = $this->getDoctrine()->getManager('default');
		$transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

		//update status
		$transaction->setStatus("Pending");

		$em->flush();

		return $this->render('alliance_market/accepted.html.twig', Array('auth_code'   => $order_id, 'total_value' => $transaction->getNet(),
																		 'transaction' => $transaction, 'transactionType' => $transactionType));
	}

	/**
	 * @Route("/alliance_market/decline", name="ajax_alliance_market_decline")
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


	/**
	 * @Route("/ajax_type_list", name="ajax_type_list")
	 */
	public function ajax_TypeListAction(Request $request)
	{
		$query = $request->request->get("query");
		$limit = $request->request->get("limit");

		$types = $this->getDoctrine()->getRepository('AppBundle:SDE\TypeEntity')->findAllLikeName($query);

		$results = array();

		if (count($types) < $limit)
		{
			$limit = count($types);
		}

		for ($count = 0; $count < $limit; $count++)
		{
			$result = array();
			$result['id'] = $types[$count]->getTypeId();
			$result['value'] = $types[$count]->getTypeName();
			$results[] = $result;
		}

		return new JsonResponse($results);
	}

	/**
	 * @Route("/ajax_market_list", name="ajax_market_list")
	 */
	public function ajax_MarketListAction(Request $request)
	{
		$query = $request->request->get("query");
		$limit = $request->request->get("limit");

		$groups = $this->getDoctrine()->getRepository('AppBundle:SDE\MarketGroupsEntity')->findAllLikeName($query);

		$results = array();
		for ($count = 0; $count < count($groups); $count++)
		{
			$result = array();
			$result['id'] = $groups[$count]->getMarketGroupId();
			$result['value'] = $groups[$count]->getMarketGroupName();

			$results[] = $result;

			if ($count >= $limit)
			{
				break;
			}
		}

		return new JsonResponse($results);
	}

	/**
	 * @Route("/ajax_group_list", name="ajax_group_list")
	 */
	public function ajax_GroupListAction(Request $request)
	{
		$query = $request->request->get("query");
		$limit = $request->request->get("limit");

		$groups = $this->getDoctrine()->getRepository('AppBundle:SDE\GroupsEntity')->findAllLikeName($query);

		$results = array();
		for ($count = 0; $count < count($groups); $count++)
		{
			$result = array();
			$result['id'] = $groups[$count]->getGroupId();
			$result['value'] = $groups[$count]->getGroupName();

			$results[] = $result;

			if ($count >= $limit)
			{
				break;
			}
		}

		return new JsonResponse($results);
	}
}
