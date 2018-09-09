<?php

namespace AppBundle\Controller;


use AppBundle\Entity\LineItemEntity;
use AppBundle\Entity\TransactionEntity;
use AppBundle\Form\ContractValidationForm;
use AppBundle\Model\ContractValidationModel;
use AppBundle\Model\TransactionSummaryModel;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Entity\TrasnactionEntity;

class TransactionsController extends Controller {

	/**
	 * @Route("/admin/contract/validate", name="admin_validate_contract")
	 */
	public function adminValidateContractAction(Request $request)
	{
		$contractDetails = new ContractValidationModel();
		$form = $this->createForm(ContractValidationForm::class, $contractDetails);
		$form->handleRequest($request);

		if ($form->isSubmitted())
		{
			if ($form->isValid())
			{
				// Find our contract code
				$matches = array();
				if (preg_match('/<b>Info by Issuer<\/b><t><color=0xff999999>([A-Za-z0-9]*)<\/color><br>/', $contractDetails->getDetails(), $matches) > 0)
				{
					if (count($matches) == 2)
					{
						$em = $this->getDoctrine()->getManager();

						$contractId = $matches[1];
						$items = $this->get('parser')->GetLineItemsFromPasteData($contractDetails->getItems());

						$contract = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($contractId);
						$missing = array();

						/** @var LineItemEntity $lineItem */
						foreach ($contract->getLineItems() as $lineItem)
						{
							$compItem = array();
							$compItem['typeid'] = $lineItem->getTypeId();
							$compItem['name'] = $lineItem->getName();
							$compItem['isValid'] = true;
							$compItem['quantity'] = $lineItem->getQuantity();

							$index = array_search($compItem, $items);

							if ($index !== false)
							{
								unset($items[$index]);
							} else
							{
								$missing[] = $compItem;
							}
						}

						$contractValid = false;

						if (count($items) == 0 & count($missing) == 0)
						{
							$contractValid = true;
						}

						return $this->render(':transaction:validate_results.html.twig', array(
							'contractValid' => $contractValid,
							'details' => $contractDetails->getDetails(),
							'items' => $items,
							'missing' => $missing,
							'transaction' => $contract
						));

					} else
					{
						$this->addFlash('error', 'Unable to find contract id, please check contract!');
					}
				} else
				{
					$this->addFlash('error', 'Unable to find contract id, please check contract!');
				}
			}
		}

		return $this->render('transaction/validate.html.twig', array(
			'form' => $form->createView()
		));
	}

	/**
	 * @Route("/admin/contract/accept/{id}", name="admin_validate_accept")
	 */
	public function adminValidateAcceptContractAction(Request $request, TransactionEntity $transaction)
	{
		$this->modifyTransaction($transaction->getOrderId(), "Complete");

		$this->addFlash('success', 'Processed '.$transaction->getOrderId());

		return $this->redirectToRoute('admin_validate_contract');
	}

	/**
	 * @Route("/admin/contract/reject/{id}", name="admin_validate_reject")
	 */
	public function adminValidateRejectContractAction(Request $request, TransactionEntity $transaction)
	{
		$this->modifyTransaction($transaction->getOrderId(), "Cancelled");

		$this->addFlash('error', 'Denied '.$transaction->getOrderId());

		return $this->redirectToRoute('admin_validate_contract');
	}



	//MAIN PAGE ACTIONS

	/**
	 * @Route("/admin/sellorder/transactions", name="admin_sell_order_transactions")
	 */
	public function sellOrderAction(Request $request)
	{
		return $this->action('P', 'Sell Order Queue');
	}

	private function action($transactionType, $pageName = 'Transaction Queue', $maxResults = 500)
	{
		$last500Transactions = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findTotalsByTypesAndExcludeStatus(array($transactionType), "Estimate", $maxResults);

		$last500Summary = new TransactionSummaryModel($last500Transactions);

		$totalSummary = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findTransactionTotalsByTypesAndStatus(array($transactionType), "Complete");

		return $this->render('transaction/index.html.twig', array(
			'page_name'      => $pageName, 'sub_text' => 'Process Transactions', 'last500Transactions' => $last500Transactions,
			'last500Summary' => $last500Summary, 'totalSummary' => $totalSummary, 'transactionType' => $transactionType
		));
	}

	/**
	 * @Route("/admin/buyorder/transactions", name="admin_buy_order_transactions")
	 */
	public function buyOrderAction(Request $request)
	{
		return $this->action('S', 'Buy Order Queue');
	}

	/**
	 * @Route("/admin/srp/transactions", name="admin_srp_transactions")
	 */
	public function srpAction(Request $request)
	{
		return $this->action('SRP', 'SRP Queue');
	}


	//MODIFY TRANSACTION ACTIONS

	/**
	 * @Route("/admin/transaction/close", name="ajax_close_transaction")
	 */
	public function ajax_CloseAction(Request $request)
	{
		$this->modifyTransaction($request->request->get('id'), "Complete");

		return new Response('OK');
	}

	private function modifyTransaction(string $transactionId, string $status)
	{
		$em = $this->getDoctrine('default')->getManager();
		$transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($transactionId);

		$transaction->setIsComplete($status != "Pending");
		$transaction->setStatus($status);

		$em->flush();
	}

	/**
	 * @Route("/transaction/decline", name="ajax_decline_transaction")
	 */
	public function ajax_DeclineAction(Request $request)
	{
		$this->modifyTransaction($request->request->get('id'), "Cancelled");

		return new Response('OK');
	}

	/**
	 * @Route("/admin/transaction/reopen", name="ajax_reopen_transaction")
	 */
	public function ajax_ReopenAction(Request $request)
	{
		$this->modifyTransaction($request->request->get('id'), "Pending");

		return new Response('OK');
	}


	//VIEW TRANSACTION ACTIONS

	/**
	 * @Route("/admin/transaction/process", name="ajax_process_transaction")
	 */
	public function ajax_ProcessAction(Request $request)
	{
		// Set up text area form for comparing transaction
		/*$form = $this->createForm(AllianceMarketForm::class, new MarketRequestModel());
		$form->handleRequest($request);*/

		$transaction = $this->getTransactionById($request->request->get('id'));

		return $this->render('transaction/details.html.twig', Array(
			'transaction'     => $transaction,
			'transactionType' => $transaction->getType()
		));
	}

	private function getTransactionById(string $transactionId)
	{
		return $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity')->findOneByOrderId($transactionId);
	}

	/**
	 * @Route("/transaction/view", name="ajax_view_transaction")
	 */
	public function ajax_ViewAction(Request $request)
	{
		return $this->render('transaction/view.html.twig', Array(
			'transaction' => $this->getTransactionById($request->query->get('id'))
		));
	}


	//REVIEW TRANSACTION ACTION

	/**
	 * @Route("/admin/transaction/validate", name="ajax_validate_transaction")
	 */
	public function ajax_ValidateTransaction(Request $request)
	{
		// Get items posted in request (copied from game contract)
		$requestItems = Array();
		$pasteData = $request->request->get('formInput');

		$transaction = $this->getTransactionById($request->request->get('orderId'));
		$transactionItems = $transaction->getLineitems();

		if ( ! empty($pasteData))
		{
			$requestItems = $this->get('parser')->GetLineItemsFromPasteData($pasteData);
		}

		$results = array();

		foreach ($requestItems as $line)
		{

			if ($line['isValid'] == true)
			{
				$item = new LineItemEntity();
				$item->setTypeId($line['typeid']);
				$item->setName($line['name']);
				$item->setIsValid(true);
				$item->setQuantity($line['quantity']);

				$results[] = $item;
			}
		}

		// Compare
		$lineItemComparison = $this->get('lineItemComparator')->CompareLineItems($transactionItems, $results);

		// Build response
		return $this->render('transaction/auto_verify.html.twig', Array('lineItemComparison' => $lineItemComparison));

	}


	//BADGING

	/**
	 * @Route("/admin/transaction/badging", name="ajax_transaction_badging")
	 */
	public function ajax_getTransactionBadges(Request $request)
	{

		$purchaseQueueBadge = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findCountByTypesAndStatus(array('P'), "Pending");
		$salesQueueBadge = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findCountByTypesAndStatus(array('S'), "Pending");
		$srpQueueBadge = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findCountByTypesAndStatus(array('SRP'), "Pending");

		return new JsonResponse(array(
			'purchaseQueueBadge' => (int) $purchaseQueueBadge,
			'salesQueueBadge'    => (int) $salesQueueBadge,
			'SRPQueueBadge'      => (int) $srpQueueBadge));
	}
}
