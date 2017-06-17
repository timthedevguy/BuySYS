<?php
namespace AppBundle\Controller;

use AppBundle\Form\ExclusionForm;

use Doctrine\DBAL\Types\TextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Model\DefaultSettingsModel;
use AppBundle\Model\BuyBackSettingsModel;
use AppBundle\Entity\ExclusionEntity;

class SystemAdminController extends Controller
{
    /**
     * @Route("/admin", name="admin_dashboard")
     */
    public function indexAction(Request $request)
    {
        $usersRepository = $this->getDoctrine('default')->getRepository('AppBundle:UserEntity');
        $tUsers = count($usersRepository->findAll());

        $transactionRepository = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        $query = $transactionRepository->createQueryBuilder('t')
            ->where('t.is_complete = 0')
            ->orderBy('t.created', 'DESC')
            ->getQuery();

        $tTransactions = count($query->getResult());

        return $this->render('admin/index.html.twig', array(
            'page_name' => 'Admin Dashboard', 'sub_text' => 'Admin Dashboard', 'tUsers' => $tUsers, 'tTransactions' => $tTransactions
        ));
    }

    /**
     * @Route("/system/admin/settings/buyback", name="admin_buyback_settings")
     */
    public function buybackSettingsAction(Request $request)
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
                $this->get("helper")->setSetting("buyback_default_buyaction_deny", $request->request->get('default_buyaction_deny'));

                $this->addFlash('success', "Settings saved successfully!");
            }
            catch(Exception $e)
            {
                $this->addFlash('error', "Settings not saved!  Contact Fecals Matters.");
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
        $buybacksettings->setDefaultBuybackActionDeny($this->get("helper")->getSetting("buyback_default_buyaction_deny"));

        return $this->render('buyback/settings.html.twig', array(
            'page_name' => 'Settings', 'sub_text' => 'Buyback Settings', 'model' => $buybacksettings));
    }

    /**
     * @Route("/system/admin/settings/exclusions", name="admin_buyback_exclusions")
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
     * @Route("/system/admin/settings/exclusions/delete", name="admin_delete_exclusion")
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
     * @Route("/system/admin/settings/mode", name="ajax_admin_buyback_mode")
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
     * @Route("/system/admin/tools", name="admin_tools")
     */
    public function toolsAction(Request $request)
    {
        return $this->render('admin/tools.html.twig', array('page_name' => 'Admin Tools', 'sub_text' => 'Tools to help maintain the system'));
    }

    /**
     * @Route("/system/admin/clearcache", name="admin_clearcache")
     */
    public function clearCacheAction(Request $request)
    {
        $this->get('cache')->ClearCache();
        $this->addFlash('success', "Cleared the cache, remember to repopulate!");
        return $this->redirectToRoute('admin_tools');
    }

    /**
     * @Route("/system/admin/updatecache", name="admin_updatecache")
     */
    public function updateCacheAction(Request $request)
    {
        $success = $this->get('cache')->UpdateCache();

        if($success == true) {

            $this->addFlash('success', "Repopulated Cache with defaults");
        } else {

            $this->addFlash('error', "There was an issue repopulating the Cache, Eve Central may be down.");
        }

        return $this->redirectToRoute('admin_tools');
    }



}
