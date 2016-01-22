<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use AppBundle\Model\BuyBackModel;
use AppBundle\Form\BuyBackType;
use AppBundle\Helper\Helper;
use AppBundle\Model\DefaultSettingsModel;
use AppBundle\Entity\SettingEntity;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $bb = new BuyBackModel();
        $form = $this->createForm(new BuyBackType(), $bb);

        $form->handleRequest($request);

        // Get count of outstanding transactions
        $transactions = $this->getDoctrine('default')->getRepository('AppBundle\Entity\TransactionEntity');
        $query = $transactions->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.is_complete = 0')
            ->andWhere('t.type = :type')
            ->orderBy('t.created', 'DESC')
            ->setParameter('user', $this->getUser())
            ->setParameter('type', "P")
            ->getQuery();

        //$outstandingSales = count($query->getResult());
        $oSales = $query->getResult();
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAll();

        $this->get("market")->GetMarketPrice('1228');
        return $this->render('default/index.html.twig', array(
            'base_dir' => 'test', 'page_name' => 'Dashboard', 'sub_text' => 'User Dashboard', 'form' => $form->createView(), 'mode' => 'USER',
         'oSales' => $oSales, 'news' => $news));
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
            'page_name' => 'Core System', 'sub_text' => '', 'mode' => 'ADMIN', 'model' => $coreSettings));
    }

    private function getHighSecOres() {

        $highSecOres = array('1230', '17470', '17471', '1228', '17463', '17464', '1224', '17459', '17460', '20', '17452', '17453');

        //$em = $this->getDoctrine()->getManager('evedata');
        //$query = $em->createQuery('SELECT c FROM EveBundle:TypeEntity c WHERE c.typeID IN (:types)')->setParameter('types', $highSecOres);
        //$types = $query->getResult();


    }
}
