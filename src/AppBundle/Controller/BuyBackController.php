<?php
namespace AppBundle\Controller;

use AppBundle\Form\ExclusionForm;
use AppBundle\Model\GroupRuleModel;
use AppBundle\Model\TypeRuleModel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\HttpFoundation\JsonResponse;

use AppBundle\Form\BuyBackForm;
use AppBundle\Form\BuyBackHiddenForm;
use AppBundle\Model\BuyBackModel;
use AppBundle\Model\BuyBackItemModel;
use AppBundle\Entity\LineItemEntity;
use EveBundle\Entity\TypeEntity;
use AppBundle\Entity\CacheEntity;
use AppBundle\Entity\TransactionEntity;
use AppBundle\Helper\MarketHelper;
use AppBundle\Model\BuyBackSettingsModel;
use AppBundle\Entity\ExclusionEntity;

class BuyBackController extends Controller
{
    /**
     * @Route("/admin/settings/buyback", name="admin_buyback_settings")
     */
    public function settingsAction(Request $request)
    {
        //$settings = $this->getDoctrine('default')->getRepository('AppBundle:SettingEntity');

        if($request->getMethod() == 'POST') {

            try
            {
                $this->get('helper')->setSetting("buyback_source_id", $request->request->get('source_id'));
                $this->get("helper")->setSetting("buyback_source_type", $request->request->get('source_type'));
                $this->get("helper")->setSetting("buyback_source_stat", $request->request->get('source_stat'));
                $this->get("helper")->setSetting("buyback_default_tax", $request->request->get('default_tax'));
                $this->get("helper")->setSetting("buyback_value_minerals", $request->request->get('value_minerals'));
                $this->get("helper")->setSetting("buyback_value_salvage", $request->request->get('value_salvage'));
                $this->get("helper")->setSetting("buyback_ore_refine_rate", $request->request->get('ore_refine_rate'));
                $this->get("helper")->setSetting("buyback_ice_refine_rate", $request->request->get('ice_refine_rate'));
                $this->get("helper")->setSetting("buyback_salvage_refine_rate", $request->request->get('salvage_refine_rate'));
                $this->get("helper")->setSetting("buyback_default_public_tax", $request->request->get('default_public_tax'));

                $this->addFlash('success', "Settings saved successfully!");
            }
            catch(Exception $e)
            {
                $this->addFlash('error', "Settings not saved!  Contact Lorvulk Munba.");
            }
        }

        $buybacksettings = new BuyBackSettingsModel();

        $buybacksettings->setSourceId($this->get("helper")->getSetting("buyback_source_id"));
        $buybacksettings->setSourceType($this->get("helper")->getSetting("buyback_source_type"));
        $buybacksettings->setSourceStat($this->get("helper")->getSetting("buyback_source_stat"));
        $buybacksettings->setDefaultTax($this->get("helper")->getSetting("buyback_default_tax"));
        $buybacksettings->setValueMinerals($this->get("helper")->getSetting("buyback_value_minerals"));
        $buybacksettings->setValueSalvage($this->get("helper")->getSetting("buyback_value_salvage"));
        $buybacksettings->setOreRefineRate($this->get("helper")->getSetting("buyback_ore_refine_rate"));
        $buybacksettings->setDefaultPublicTax($this->get("helper")->getSetting("buyback_default_public_tax"));
        $buybacksettings->setIceRefineRate($this->get("helper")->getSetting("buyback_ice_refine_rate"));
        $buybacksettings->setSalvageRefineRate($this->get("helper")->getSetting("buyback_salvage_refine_rate"));

        return $this->render('buyback/settings.html.twig', array(
            'page_name' => 'Settings', 'sub_text' => 'Buyback Settings', 'model' => $buybacksettings));
    }

    /**
     * @Route("/admin/settings/exclusions", name="admin_buyback_exclusions")
     */
    public function exclusionsAction(Request $request)
    {
        $mode = $this->get("helper")->getSetting("buyback_whitelist_mode");
        $form = $this->createForm(ExclusionForm::class);

        if($request->getMethod() == "POST") {

            $form_results = $request->request->get('exclusion_form');
            $exclusion = new ExclusionEntity();
            $exclusion->setMarketGroupId($form_results['marketgroupid']);
            $exclusion->setWhitelist($mode);
            $group = $this->getDoctrine()->getRepository('EveBundle:MarketGroupsEntity','evedata')->
                findOneByMarketGroupID($exclusion->getMarketGroupId());
            $exclusion->setMarketGroupName($group->getMarketGroupName());
            $em = $this->getDoctrine()->getManager();
            $em->persist($exclusion);
            $em->flush();
        }

        $exclusions = $this->getDoctrine()->getRepository('AppBundle:ExclusionEntity')->findByWhitelist($mode);

        return $this->render('buyback/exclusions.html.twig', array(
            'page_name' => 'Settings', 'sub_text' => 'Buyback Exclusions', 'mode' => $mode,
            'exclusions' => $exclusions, 'form' => $form->createView()));
    }

    /**
     * @Route("/admin/settings/exclusions/delete", name="admin_delete_exclusion")
     */
    public function deleteExclusionAction(Request $request)
    {
        $exclusion = $this->getDoctrine()->getRepository('AppBundle:ExclusionEntity')->
            findOneById($request->query->get('id'));
        $em = $this->getDoctrine()->getManager();
        $em->remove($exclusion);
        $em->flush();

        return $this->redirectToRoute('admin_buyback_exclusions');
    }

    /**
     * @Route("/admin/settings/mode", name="ajax_admin_buyback_mode")
     */
    public function ajax_ExclusionModeAction(Request $request)
    {
        $mode = $request->request->get("mode");

        $this->get("helper")->setSetting("buyback_whitelist_mode", $mode);

        $response = new Response();
        $response->setStatusCode(200);

        return $response;
    }

    /**
     * @Route("/buyback/estimate", name="ajax_estimate_buyback")
     */
    public function ajax_EstimateAction(Request $request) {

        //setup model and form
        $buyback = new BuyBackModel();
        $form = $this->createForm(BuyBackForm::class, $buyback);
        $form->handleRequest($request);

        //$types = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata');
        //$cache = $this->getDoctrine()->getRepository('AppBundle:CacheEntity', 'default');
        //$typeids = array();
        //$items = array();

        //parse form input
        $items = $this->get('parser')->GetLineItemsFromPasteData($buyback->getItems());

        //check to make sure it parsed correctly
        if($items == null || count($items) <= 0) {
            return $this->render('buyback/novalid.html.twig');
        }

        //get market value and buyback values
        if(!$this->get('market')->PopulateLineItems($items))
        {
            return $this->render('elements/error_modal.html.twig', Array( 'message' => "No Prices Found"));
        }

        //insert into DB and return quote

        //get DB manager
        $em = $this->getDoctrine()->getManager('default');

        //build transaction
        $transaction = new TransactionEntity();
        $transaction->setUser($this->getUser());
        $transaction->setType("P"); //will reset to PS if accepted with shares

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




        return $this->render('buyback/results.html.twig', Array ( 'items' => $items, 'total' => $transaction->getNet(),
            'hasInvalid' => $hasInvalid, 'orderId' => $transaction->getOrderId() ));
    }

    /**
     * @Route("/buyback/accept", name="ajax_accept_buyback")
     */
    public function ajax_AcceptAction(Request $request) {

        // Get our list of Items
        $total = $request->request->get('total');
        $order_id = $request->request->get('orderId');
        $shares = $request->request->get('shares');

        // Pull data from DB
        $em = $this->getDoctrine()->getManager('default');
        $transaction = $em->getRepository('AppBundle:TransactionEntity')->findOneByOrderId($order_id);

        //update status
        $transaction->setStatus("Pending");

        //update shares if needed
        if ($shares == 1) {
            $transaction->setType("PS");
        }

        $em->flush();

        $share_value = 0;

        return $this->render('buyback/accepted.html.twig', Array('auth_code' => $order_id, 'total_value' => $transaction->getNet(),
            'transaction' => $transaction, 'shares' => $shares, 'share_value' => $share_value));
    }

    /**
     * @Route("/buyback/decline", name="ajax_decline_buyback")
     */
    public function ajax_DeclineAction(Request $request) {

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
     * @Route("/market/lookup", name="ajax_lookup_price")
     */
    public function ajax_LookupAction(Request $request) {

        if(is_numeric($request->request->get('id'))) {

            $typeId = $request->request->get('id');

            // Get Settings
            $bb_source_type = $this->get('helper')->getSetting("buyback_source_type");
            $bb_source_stat = $this->get('helper')->getSetting("buyback_source_stat");
            $bb_source_id =  $this->get('helper')->getSetting("buyback_source_id");

            $amarrData = $this->get('market')->GetEveCentralData($typeId, $bb_source_id);
            $jitaData = $this->get('market')->GetEveCentralData($typeId, "30000142");
            $dodixieData = $this->get('market')->GetEveCentralData($typeId, "30002659");
            $rensData = $this->get('market')->GetEveCentralData($typeId, "30002510");
            $hekData = $this->get('market')->GetEveCentralData($typeId, "30002053");
            $type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($typeId);
            $market_group = $this->getDoctrine()->getRepository('EveBundle:MarketGroupsEntity','evedata')
                ->findOneByMarketGroupID($type->getMarketGroupId())->getMarketGroupName();
            $group = $this->getDoctrine()->getRepository('EveBundle:GroupsEntity', 'evedata')
                ->findOneByGroupID($type->getGroupID())->getGroupName();
            $priceDetails = array();
            $priceDetails['types'] = array();
            $options = $this->get('market')->ProcessBuybackRules($typeId);
            $value = $this->get('market')->GetMarketPriceByComposition($type, $options, $priceDetails);
            $isPricedByMinerals = $this->get('market')->IsPricedByMinerals($typeId);

            $template = $this->render('buyback/lookup.html.twig', Array ( 'type_name' => $type->getTypeName(), 'amarr' => $amarrData, 'source_system' => $bb_source_id,
                                        'source_type' => $bb_source_type, 'source_stat' => $bb_source_stat, 'typeid' => $type->getTypeID(),
                                        'jita' => $jitaData, 'dodixie' => $dodixieData, 'rens' => $rensData, 'hek' => $hekData, 'value' => $value,
                                        'details' => $priceDetails, 'market_group' => $market_group, 'is_priced' => $isPricedByMinerals, 'options' => $options,
                'group' => $group));
            return $template;

        } else {

            // Get item name searched for
            $name = $request->request->get('id');

            // Get all matching types
            $types = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findAllLikeName($name);

            $template = $this->render('elements/searchResultsByType.html.twig', Array ( 'items' => $types ));
            return $template;
        }
    }

    /**
     * @Route("/guest/buyback", name="guest_buyback")
     */
    public function guestBuybackIndexAction(Request $request)
    {
        // Get Eve Central Online Status
        $eveCentralOK = $this->get("helper")->getSetting("eveCentralOK");

        // Create Buyback Form
        $bb = new BuyBackModel();
        $form = $this->createForm(BuyBackForm::class, $bb);

        // Handle Form
        $form->handleRequest($request);

        // If form is valid
        if ($form->isValid() && $form->isSubmitted())
        {
            $types = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata');
            $cache = $this->getDoctrine()->getRepository('AppBundle:CacheEntity', 'default');
            $items = array();
            $typeids = array();

            $items = $this->get('parser')->GetLineItemsFromPasteData($bb->getItems());

            if(!$this->get('market')->PopulateLineItems($items, true))
            {
                $template = $this->render('elements/error_modal.html.twig', Array( 'message' => "No Prices Found"));
                return $template;
            }

            //$priceLookup = $this->get('market')->GetMarketPrices($typeids);

            //if(!is_array($priceLookup)) {

                //$this->addFlash('error', "No pricing information found.  Please Eve mail 'Lorvulk Ormand' in game if you feel this is in error.");
                //return $this->redirectToRoute('guest_buyback');
            //}

            $totalValue = 0;

            foreach($items as $lineItem) {
                //$taxAmount = ;
                $totalValue += $lineItem->getNetPrice();
            }

            if($items == null)
            {
                $this->addFlash('error', "No valid items found.  Please Eve mail 'Lorvulk Ormand' in game if you feel this is in error.");
                return $this->redirectToRoute('guest_buyback');
            }

            $formH = $this->createForm(BuyBackHiddenForm::class, $bb, array( 'action' => $this->generateUrl('guest_accept_offer')));
            $formH->handleRequest($request);

            return $this->render('buyback/step_two.html.twig', array('items' => $items, 'total' => $totalValue, 'rawitems' => $bb->getItems(), 'form' => $formH->createView() ));
        }

        return $this->render('buyback/index.html.twig', array('form' => $form->createView(), 'eveCentralOK' => $eveCentralOK ));
    }

    /**
     * @Route("/guest/accept", name="guest_accept_offer")
     */
    public function guestBuybackAcceptAction(Request $request)
    {
        // Create Buyback Form
        $bb = new BuyBackModel();
        $form = $this->createForm(BuyBackHiddenForm::class, $bb);

        // Handle Form
        $form->handleRequest($request);

        // If form is valid
        if ($form->isValid() && $form->isSubmitted())
        {
            $types = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata');
            $cache = $this->getDoctrine()->getRepository('AppBundle:CacheEntity', 'default');
            $items = array();
            $typeids = array();

            $items = $this->get('parser')->GetLineItemsFromPasteData($bb->getItems(), true);

            $em = $this->getDoctrine()->getManager('default');

            if(!$this->get('market')->PopulateLineItems($items)) {

                $this->addFlash('error', "No pricing information found.  Please Eve mail 'Lorvulk Ormand' in game if you feel this is in error.");
                return $this->redirectToRoute('guest_buyback');
            }

            $totalValue = 0;
            $gross = 0;
            $net = 0;

            foreach($items as $lineItem)
            {
                $gross +=  $lineItem->getGrossPrice();
                $net += $lineItem->getNetPrice();
            }

            $transaction = new TransactionEntity();
            //$transaction->setUser($this->getUser());

            $transaction->setType("P");

            $transaction->setIsComplete(false);
            $transaction->setOrderId($transaction->getType() . uniqid());
            $transaction->setGross(0);
            $transaction->setNet(0);
            $transaction->setCreated(new \DateTime("now"));
            $transaction->setStatus("Pending");
            $em->persist($transaction);

            foreach($items as $lineItem)
            {
                $transaction->addLineItem($lineItem);
                $em->persist($lineItem);
            }

            $transaction->setGross($gross);
            $transaction->setNet($net);

            //$em->persist($transaction);
            $em->flush();

            return $this->render('buyback/step_three.html.twig', array('total_value' => $net, 'auth_code' => $transaction->getOrderId()));
        }
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
