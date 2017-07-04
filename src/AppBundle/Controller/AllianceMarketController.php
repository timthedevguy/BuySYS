<?php
namespace AppBundle\Controller;

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

use AppBundle\Form\AllianceMarketForm;
use AppBundle\Model\MarketRequestModel;
use AppBundle\Model\TransactionSummaryModel;
//use AppBundle\Model\BuyBackItemModel;
use AppBundle\Entity\LineItemEntity;
use AppBundle\Entity\TransactionEntity;
use AppBundle\Helper\MarketHelper;

class AllianceMarketController extends Controller
{


    /**
     * @Route("/alliance_market/sellorders", name="buyback")
     */
    public function buybackAction(Request $request)
    {
        $bb = new MarketRequestModel();
        $form = $this->createForm(AllianceMarketForm::class, $bb);

        $form->handleRequest($request);
        $eveCentralOK = $this->get("helper")->getSetting("eveCentralOK");
        $oTransaction = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllByUserAndTypes($this->getUser(), array('P'));
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAllOrderedByDate();

        $transactionSummary = new TransactionSummaryModel($oTransaction);

        return $this->render('alliance_market/index.html.twig', array(
            'transactionType' => 'P', 'page_name' => 'Sell Orders', 'sub_text' => 'Sell your stuff!', 'form' => $form->createView(),
            'oTransaction' => $oTransaction, 'transactionSummary'=> $transactionSummary, 'news' => $news, 'eveCentralOK' => $eveCentralOK ));
    }

    /**
     * @Route("/alliance_market/buyorders", name="sales")
     */
    public function salesAction(Request $request)
    {
        $bb = new MarketRequestModel();
        $form = $this->createForm(AllianceMarketForm::class, $bb);

        $form->handleRequest($request);
        $eveCentralOK = $this->get("helper")->getSetting("eveCentralOK");
        $oTransaction = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllByUserAndTypes($this->getUser(), array('S'));
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAllOrderedByDate();

        $transactionSummary = new TransactionSummaryModel($oTransaction);

        return $this->render('alliance_market/index.html.twig', array(
            'transactionType' => 'S', 'page_name' => 'Buy Orders', 'sub_text' => 'Place a buy order!', 'form' => $form->createView(),
            'oTransaction' => $oTransaction, 'transactionSummary'=> $transactionSummary, 'news' => $news, 'eveCentralOK' => $eveCentralOK ));
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

        if(empty($rawRequestItems))
        {
            return $this->render('alliance_market/novalid.html.twig');
        }
        else
        {
            // Parse form input
            $items = $this->get('parser')->GetLineItemsFromPasteData($rawRequestItems);

            // Check to make sure it parsed correctly
            if($items == null || count($items) <= 0) {
                return $this->render('alliance_market/novalid.html.twig');
            }

            $typeIds = array();

            // Grab our TypeID's to pull pricing for
            foreach($items as $item)
            {
                $typeIds[] = $item->getTypeId();
            }

            $typePrices = $this->get('market')->getBuybackPricesForTypes($typeIds);

            foreach($items as $item)
            {
                // Check if price is -1, means Can Buy is False
                if($typePrices[$item->getTypeId()]['adjusted'] == -1) {

                    // Set to all 0 and mark as invalid
                    $item->setMarketPrice(0);
                    $item->setGrossPrice(0);
                    $item->setNetPrice(0);
                    $item->setTax(0);
                    $item->setIsValid(false);
                } else {

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

            //add line items to transaction
            $hasInvalid = false;

            /* @var $lineItem LineItemEntity */
            foreach($items as $lineItem)
            {
                if($lineItem->getIsValid()) {
                    $em->persist($lineItem);
                    $transaction->addLineitem($lineItem);
                } else {
                    $hasInvalid = true;
                }
            }

            $em->flush();
        }


        return $this->render('alliance_market/estimate.html.twig', Array ( 'items' => $items, 'total' => $transaction->getNet(),
            'hasInvalid' => $hasInvalid, 'orderId' => $transaction->getOrderId(), 'transactionType' => $transactionType ));
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

        return $this->render('alliance_market/accepted.html.twig', Array('auth_code' => $order_id, 'total_value' => $transaction->getNet(),
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

        $types = $this->getDoctrine()->getRepository('EveBundle:TypeEntity','evedata')->findAllLikeName($query);

        $results = array();

        if(count($types) < $limit) { $limit = count($types); }

        for($count = 0;$count < $limit;$count++)
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

        $groups = $this->getDoctrine()->getRepository('EveBundle:MarketGroupsEntity','evedata')->findAllLikeName($query);

        $results = array();
        for($count = 0;$count < count($groups);$count++)
        {
            $result = array();
            $result['id'] = $groups[$count]->getMarketGroupId();
            $result['value'] = $groups[$count]->getMarketGroupName();

            $results[] = $result;

            if($count >= $limit) {break;}
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

        $groups = $this->getDoctrine()->getRepository('EveBundle:GroupsEntity','evedata')->findAllLikeName($query);

        $results = array();
        for($count = 0;$count < count($groups);$count++)
        {
            $result = array();
            $result['id'] = $groups[$count]->getGroupId();
            $result['value'] = $groups[$count]->getGroupName();

            $results[] = $result;

            if($count >= $limit) {break;}
        }

        return new JsonResponse($results);
    }
}
