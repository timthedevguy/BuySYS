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
        $settings = $this->getDoctrine('default')->getRepository('AppBundle:SettingEntity');

        if($request->getMethod() == 'POST') {

            $em = $this->getDoctrine('default')->getManager();

            $maintenanceMode = $settings->findOneByName('system_maintenance');
            $maintenanceMode->setValue($request->request->get('maintenance_mode'));

            try
            {
                $em->flush();
                $this->addFlash('success', "Settings saved successfully!");
            }
            catch(Exception $e)
            {
                $this->addFlash('error', "Settings not saved!  Contact Lorvulk Munba.");
            }
        }

        $coreSettings = new DefaultSettingsModel();
        $coreSettings->setMaintenanceMode($settings->findOneByName('system_maintenance')->getValue());

        return $this->render('default/settings.html.twig', array(
            'page_name' => 'Settings', 'sub_text' => 'Core Settings', 'model' => $coreSettings));
    }


}
