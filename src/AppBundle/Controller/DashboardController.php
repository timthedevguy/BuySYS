<?php

namespace AppBundle\Controller;

use AppBundle\Model\EstimateModel;
use AppBundle\Form\EstimateForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('home/dashboard.html.twig', array(
            'pendingSales'=> 0,
            'pendingIncome' => 0,
            'actualSales' => 0,
            'actualIncome' => 0,
			'title' => 'Dashboard',
			'subtitle' => 'Sell us your stuff'
		));
    }


    /**
     * @Route("/market/lookup", name="ajax_lookup_price")
     */
    public function ajax_LookupAction(Request $request)
    {

        if(is_numeric($request->request->get('id'))) {

            $typeId = $request->request->get('id');

            // Get Settings
            $bb_source_type = $this->get('helper')->getSetting("source_type", "P");
            $bb_source_stat = $this->get('helper')->getSetting("source_stat", "P");
            $bb_source_id =  $this->get('helper')->getSetting("source_id", "P");

            /*$amarrData = $this->get('market')->getEveCentralDataForTypes(array($typeId), "30002187");
            $jitaData = $this->get('market')->getEveCentralDataForTypes(array($typeId), "30000142");
            $dodixieData = $this->get('market')->getEveCentralDataForTypes(array($typeId), "30002659");
            $rensData = $this->get('market')->getEveCentralDataForTypes(array($typeId), "30002510");
            $hekData = $this->get('market')->getEveCentralDataForTypes(array($typeId), "30002053");*/

            $amarrData = array_merge($this->get('market')->getFuzzworksDataForTypes(array($typeId), "60008494"));
            $jitaData = array_merge($this->get('market')->getFuzzworksDataForTypes(array($typeId), "60003760"));
            $dodixieData = array_merge($this->get('market')->getFuzzworksDataForTypes(array($typeId), "60011866"));
            $rensData = array_merge($this->get('market')->getFuzzworksDataForTypes(array($typeId), "60004588"));
            $hekData = array_merge($this->get('market')->getFuzzworksDataForTypes(array($typeId), "60005686"));

            $type = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($typeId);
            $market_group = $this->getDoctrine()->getRepository('EveBundle:MarketGroupsEntity','evedata')
                ->findOneByMarketGroupID($type->getMarketGroupId())->getMarketGroupName();
            $group = $this->getDoctrine()->getRepository('EveBundle:GroupsEntity', 'evedata')
                ->findOneByGroupID($type->getGroupID())->getGroupName();



            $priceDetails = array();
            $priceDetails['types'] = array();
            $options = $this->get('market')->getMergedBuybackRuleForType($typeId, 'P');


            // Figure out Refining Details
            $refineMaterials = $this->get('market')->getRefinedMaterialsForType($typeId,$options['refineskill'], 'P');
            $materialNames = $this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')
                ->findNamesForTypes(array_keys($refineMaterials));
            $materialPrices = $this->get('market')->getBuybackPricesForTypes(array_keys($refineMaterials),'P', true);

            $refineDetails = array();
            $refinedPrice = 0;

            foreach($refineMaterials as $materialTypeId => $refineDetail)
            {
                $refineDetails[$materialTypeId]['typeid'] = $materialTypeId;
                $refineDetails[$materialTypeId]['name'] = $materialNames[$materialTypeId];
                $refineDetails[$materialTypeId]['price'] = $materialPrices[$materialTypeId];
                $refineDetails[$materialTypeId]['quantity'] = $refineMaterials[$materialTypeId];

                $refinedPrice += $refineMaterials[$materialTypeId]['adjusted'] * $materialPrices[$materialTypeId]['market'];
            }

            $template = $this->render('elements/lookup.html.twig', Array ( 'type_name' => $type->getTypeName(), 'amarr' => $amarrData, 'source_system' => $bb_source_id,
                'source_type' => $bb_source_type, 'source_stat' => $bb_source_stat, 'typeid' => $type->getTypeID(),
                'jita' => $jitaData, 'dodixie' => $dodixieData, 'rens' => $rensData, 'hek' => $hekData,
                'details' => $priceDetails, 'market_group' => $market_group, 'options' => $options, 'refinedPrice' => $refinedPrice, 'refineDetails' => $refineDetails,
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
}
