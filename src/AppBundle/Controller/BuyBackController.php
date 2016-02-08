<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Time;

use AppBundle\Form\BuyBackType;
use AppBundle\Model\BuyBackModel;
use AppBundle\Model\BuyBackItemModel;
use AppBundle\Entity\LineItemEntity;
use EveBundle\Entity\TypeEntity;
use AppBundle\Entity\CacheEntity;
use AppBundle\Entity\TransactionEntity;
use AppBundle\Helper\MarketHelper;
use AppBundle\Model\BuyBackSettingsModel;

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

        return $this->render('buyback/settings.html.twig', array(
            'page_name' => 'Buyback System', 'sub_text' => 'Buyback Settings', 'mode' => 'ADMIN', 'model' => $buybacksettings));
    }

    /**
     * @Route("/buyback/estimate", name="ajax_estimate_buyback")
     */
    public function ajax_EstimateAction(Request $request) {

        $buyback = new BuyBackModel();
        $form = $this->createForm(new BuyBackType(), $buyback);
        $form->handleRequest($request);

        $types = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata');
        $cache = $this->getDoctrine()->getRepository('AppBundle:CacheEntity', 'default');
        $items = array();
        $typeids = array();

        // Build our Item List and TypeID List
        foreach(explode("\n", $buyback->getItems()) as $line) {

            // Array counts
            // 5 -> View Contents list
            // 6 -> Inventory list

            // Split by TAB
            $item = explode("\t", $line);

            // Did this contain tabs?
            if(count($item) > 1) {

                // 6 Columns -> Means this is pasted from Inventory Screen
                //if(count($item) == 6) {

                    // Get TYPE from Eve Database
                    $type = $types->findOneByTypeName($item[0]);

                    if($type != null) {

                        // Create & Populate our BuyBackItemModel
                        $lineItem = new BuyBackItemModel();
                        $lineItem->setTypeId($type->getTypeId());

                        if($item[1] == "") {
                            $lineItem->setQuantity(1);
                        } else {
                            $lineItem->setQuantity(str_replace('.', '', $item[1]));
                            $lineItem->setQuantity(str_replace(',', '', $lineItem->getQuantity()));
                        }

                        $lineItem->setName($type->getTypeName());
                        $lineItem->setVolume($type->getVolume());

                        $items[] = $lineItem;

                        // Build our list of TypeID's
                        $typeids[] = $type->getTypeId();
                    } else {

                        $template = $this->render('elements/error_modal.html.twig', Array( 'message' => "Item doesn't exist in Eve Database: ".$item[0]));
                        return $template;
                    }
                //}
            } else {

                // Didn't contain tabs, so user typed it in?  Try to preg match it
                $item = array();
                preg_match("/((\d|,)*)\s+(.*)/", $line, $item);

                // Get TYPE from Eve Database
                $type = $types->findOneByTypeName($item[3]);

                if($type != null) {

                    // Create & Populate our BuyBackItemModel
                    $lineItem = new BuyBackItemModel();
                    $lineItem->setTypeId($type->getTypeId());
                    $lineItem->setQuantity(str_replace(',', '', $item[1]));
                    $lineItem->setName($type->getTypeName());
                    $lineItem->setVolume($type->getVolume());

                    $items[] = $lineItem;

                    // Build our list of TypeID's
                    $typeids[] = $type->getTypeId();
                }
            }
        }

        //$priceLookup = MarketHelper::GetMarketPrices($typeids, $this);
        $priceLookup = $this->get('market')->GetMarketPrices($typeids);

        if(!is_array($priceLookup)) {

            $template = $this->render('elements/error_modal.html.twig', Array( 'message' => "No Prices Found"));
            return $template;
        }

        $totalValue = 0;
        $ajaxData = "[";

        foreach($items as $lineItem) {

            $value = ((int)$lineItem->getQuantity() * $priceLookup[$lineItem->getTypeId()]) * .85;
            $totalValue += $value;
            $lineItem->setValue($value);
            $ajaxData .= "{ typeid:" . $lineItem->getTypeId() . ", quantity:" . $lineItem->getQuantity() . "},";
        }

        $ajaxData .= "]";
        $ajaxData = rtrim($ajaxData, ",");

        if($items != null) {

            $template = $this->render('buyback/results.html.twig', Array ( 'items' => $items, 'total' => $totalValue, 'ajaxData' => $ajaxData ));
        } else {

            $template = $this->render('buyback/novalid.html.twig');
        }

        return $template;
    }

    /**
     * @Route("/buyback/accept", name="ajax_accept_buyback")
     */
    public function ajax_AcceptAction(Request $request) {

        // Get our list of Items
        $items = $request->request->get('items');

        // Generate list of unique items to pull from cache
        $typeids = Array();
        $typeids = array_unique(array_map(function($n){return($n['typeid']);}, $items));

        // Get Type Database
        $types = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata');

        // Pull data from Cache
        $em = $this->getDoctrine()->getManager('default');
        $query = $em->createQuery('SELECT c FROM AppBundle:CacheEntity c WHERE c.typeID IN (:types)')->setParameter('types', $typeids);
        $cached = $query->getResult();

        $transaction = new TransactionEntity();
        $transaction->setUser($this->getUser());
        $transaction->setType("P");
        $transaction->setIsComplete(false);
        $transaction->setOrderId($transaction->getType() . uniqid());
        $transaction->setGross(0);
        $transaction->setNet(0);
        $transaction->setCreated(new \DateTime("now"));
        $transaction->setStatus("Pending");
        $em->persist($transaction);

        $gross = 0;
        $net = 0;

        foreach($items as $item) {

            $lineItem = new LineItemEntity();
            $lineItem->setTypeId($item['typeid']);
            $lineItem->setQuantity($item['quantity']);
            $lineItem->setName( $types->findOneByTypeID($item['typeid'])->getTypeName() );
            $lineItem->setTax(15);

            foreach($cached as $cache) {

                if($cache->getTypeId() == $lineItem->getTypeId()) {

                    $lineItem->setMarketPrice($cache->getMarket());
                    $lineItem->setGrossPrice(($lineItem->getMarketPrice() * $lineItem->getQuantity()));
                    $gross +=  $lineItem->getGrossPrice();
                    $lineItem->setNetPrice(($lineItem->getMarketPrice() * $lineItem->getQuantity()) * ((100-$lineItem->getTax())/100));
                    $net += $lineItem->getNetPrice();
                    break;
                }
            }

            $transaction->addLineItem($lineItem);
            $em->persist($lineItem);
        }

        $transaction->setGross($gross);
        $transaction->setNet($net);

        //$em->persist($transaction);
        $em->flush();

        $template = $this->render('buyback/accepted.html.twig', Array ( 'auth_code' => $transaction->getOrderId(), 'total_value' => $net, 'transaction' => $transaction ));
        return $template;
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

            $template = $this->render('buyback/lookup.html.twig', Array ( 'type_name' => $type->getTypeName(), 'amarr' => $amarrData, 'source_system' => $bb_source_id,
                                        'source_type' => $bb_source_type, 'source_stat' => $bb_source_stat, 'typeid' => $type->getTypeID(),
                                        'jita' => $jitaData, 'dodixie' => $dodixieData, 'rens' => $rensData, 'hek' => $hekData));
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
}
