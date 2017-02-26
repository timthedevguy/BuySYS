<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Model\BuyBackModel;
use AppBundle\Form\BuyBackForm;
use AppBundle\Helper\Helper;
use AppBundle\Model\DefaultSettingsModel;
use AppBundle\Entity\SettingEntity;
use AppBundle\Model\OreReviewModel;
use EveBundle\Entity\TypeEntity;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $bb = new BuyBackModel();
        $form = $this->createForm(BuyBackForm::class, $bb);

        $form->handleRequest($request);
        $eveCentralOK = $this->get("helper")->getSetting("eveCentralOK");
        $oSales = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllVisibleByUser($this->getUser()); //$query->getResult();
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAllOrderedByDate();
        
        return $this->render('default/index.html.twig', array(
            'base_dir' => 'test', 'page_name' => 'Dashboard', 'sub_text' => 'User Dashboard', 'form' => $form->createView(),
         'oSales' => $oSales, 'news' => $news, 'eveCentralOK' => $eveCentralOK ));
    }

    /**
     * @Route("/admin/settings", name="admin_core_settings")
     */
    public function settingsAction(Request $request)
    {
        // Get Settings Helper
        $settings = $this->get('helper');

        // Check if POST
        if($request->getMethod() == 'POST')
        {
            // Save our settings and provide Flash
            try
            {
                // Settings Helper flushes as needed
                $settings->setSetting('system_maintenance', $request->request->get('maintenance_mode'));
                $settings->setSetting('sso_clientid', $request->request->get('clientid'));
                $settings->setSetting('sso_secretkey', $request->request->get('secretkey'));

                $this->addFlash('success', "Settings saved successfully!");
            }
            catch(Exception $e)
            {
                $this->addFlash('error', "Settings not saved!  Contact Lorvulk Munba.");
            }
        }

        // Create our Model
        $coreSettings = new DefaultSettingsModel();
        $coreSettings->setMaintenanceMode($settings->getSetting('system_maintenance'));
        $coreSettings->setClientId($settings->getSetting('sso_clientid'));
        $coreSettings->setSecretKey($settings->getSetting('sso_secretkey'));

        return $this->render('default/settings.html.twig', array('page_name' => 'Settings', 'sub_text' => 'Core Settings',
            'model' => $coreSettings));
    }


}
