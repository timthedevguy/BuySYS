<?php
namespace AppBundle\Controller;


use AppBundle\Model\TransactionSummaryModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Model\MarketRequestModel;
use AppBundle\Form\AllianceMarketForm;
use AppBundle\Entity\TranactionEntity;

class TransactionsController extends Controller
{
    /**
     * @Route("/admin/buy/transactions", name="admin_transactions")
     */
    public function indexAction(Request $request)
    {

        $last500Transactions = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findValidTransactionsByTypesAndOrderedByDate(['P', 'PS', 'S'], 500);
        $last500Summary = new TransactionSummaryModel($last500Transactions);

        $totalSummary = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAcceptedTransactionTotals(['P', 'PS', 'S']);

        return $this->render('transaction/index.html.twig', array(
            'page_name' => 'Contract Queue', 'sub_text' => 'Transactions', 'last500Transactions' => $last500Transactions,
            'last500Summary' => $last500Summary, 'totalSummary' => $totalSummary
        ));
    }

    /**
     * @Route("/admin/transaction/process", name="ajax_process_transaction")
     */
    public function ajax_ProcessAction(Request $request)
    {
        // Set up text area form for comparing transaction
        $bb = new MarketRequestModel();
        $form = $this->createForm(AllianceMarketForm::class, $bb);

        $form->handleRequest($request);

        // Get Transaction Id
        // Get our list of Items
        $order_id = $request->request->get('id');

        $transactions = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        $transaction = $transactions->findOneByOrderId($order_id);

        $template = $this->render('transaction/process.html.twig', Array ( 'transaction' => $transaction, 'form' => $form->createView()));
        return $template;
    }

    /**
     * @Route("/admin/transaction/close", name="ajax_close_transaction")
     */
    public function ajax_CloseAction(Request $request)
    {
        // Handles the Transaction (IE Closes it)
        $order_id = $request->request->get('id');

        //$transactions = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        //$transaction = $transactions->findOneByOrderId($order_id);
        $em = $this->getDoctrine('default')->getManager();
        $transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

        $transaction->setIsComplete(true);
        $transaction->setStatus("Complete");
        $em->flush();

        return new Response('OK');
    }

    /**
     * @Route("/transaction/decline", name="ajax_decline_transaction")
     */
    public function ajax_DeclineAction(Request $request)
    {
        // Handles the Transaction (IE Closes it)
        $order_id = $request->request->get('id');

        //$transactions = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        //$transaction = $transactions->findOneByOrderId($order_id);
        $em = $this->getDoctrine('default')->getManager();
        $transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

        $transaction->setIsComplete(true);
        $transaction->setStatus("Cancelled");
        $em->flush();

        return new Response('OK');
    }

    /**
     * @Route("/admin/transaction/reopen", name="ajax_reopen_transaction")
     */
    public function ajax_ReopenAction(Request $request)
    {
        // Handles the Transaction (IE Closes it)
        $order_id = $request->request->get('id');

        //$transactions = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        //$transaction = $transactions->findOneByOrderId($order_id);
        $em = $this->getDoctrine('default')->getManager();
        $transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

        $transaction->setIsComplete(false);
        $transaction->setStatus("Pending");
        $em->flush();

        return new Response('OK');
    }

    /**
     * @Route("/transaction/view", name="ajax_view_transaction")
     */
    public function ajax_ViewAction(Request $request)
    {
        // Handles the Transaction (IE Closes it)
        $order_id = $request->query->get('id');
        $order_type = $request->query->get('transaction_type');

        $em = $this->getDoctrine('default')->getManager();
        $transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

        $template = $this->render('transaction/view-p.html.twig', Array ( 'transaction' => $transaction ));
        return $template;

    }

    /**
     * @Route("/admin/transaction/validate", name="ajax_validate_transaction")
     */
    public function ajax_ValidateTransaction(Request $request)
    {
        dump($request);
        // Get list of items from stored transaction
        $orderId = $request->request->get('orderId');
        $pasteData = $request->request->get('formInput');

        $transactions = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        $transaction = $transactions->findOneByOrderId($orderId);
        $transactionItems = $transaction->getLineitems();


        // Get items posted in request (copied from game contract)
        if ($pasteData == null || $pasteData == "")
        {
            $requestItems = Array();
        }
        else
        {
            $requestItems = $this->get('parser')->GetLineItemsFromPasteData($pasteData);
        }

        // Compare
        $lineItemComparison = $this->get('lineItemComparator')->CompareLineItems($transactionItems, $requestItems);

        // Build response
        $template = $this->render('transaction/verify.html.twig', Array ( 'lineItemComparison' => $lineItemComparison));
        return $template;

    }


    /**
     * @Route("/admin/transaction/badging", name="ajax_transaction_badging")
     */
    public function ajax_getTransactionBadges(Request $request) {

        $purchaseQueueBadge = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findCountByTypesAndStatus(['P'], 'Pending');
        $SRPQueueBadge = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findCountByTypesAndStatus(['SRP'], 'Pending');

        return new JsonResponse([
			'purchaseQueueBadge' => $purchaseQueueBadge,
			'SRPQueueBadge' => $SRPQueueBadge
		]);
    }

}
