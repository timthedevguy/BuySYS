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
        $form = $this->createForm(new BuyBackType(), $bb);

        $form->handleRequest($request);
        $eveCentralOK = $this->get("helper")->getSetting("eveCentralOK");

        $oSales = $this->getDoctrine()->getRepository('AppBundle:TransactionEntity', 'default')->findAllVisibleByUser($this->getUser()); //$query->getResult();
        $news = $this->getDoctrine('default')->getRepository('AppBundle:NewsEntity')->findAllOrderedByDate();

        /*$highSecOres = $this->getQuickReview(array('1230', '17470', '17471', '1228', '17463', '17464', '1224', '17459', '17460', '20', '17452', '17453'));
        $otherHighSecOres = $this->getQuickReview(array('18','17455','17456','1227','17867','17868'));
        $lowSecOres = $this->getQuickReview(array('1226','17448','17449','1231','17444','17445','21','17440','17441'));
        $nullSecOres = $this->getQuickReview(array('22','17425','17426','1223','17428','17429','1225','17432','17433','1232','17436','17437','1229','17865','17866','11396','17869','17870','19','17466','17467'));
        $iceOres = $this->getQuickReview(array('16264','17975','16265','17976','16262','17978','16263','17977','16267','16268','16266','16269'));
        $gasOres = $this->getQuickReview(array('25268','28694','25279','28695','25275','28696','30375','30376','30377','30370','30378','30371','30372','30373','30374','25273','28697','25277','28698','25276','28699','25278','28700','25274','28701'));
        $mineralOres = $this->getQuickReview(array('34','35','36','37','38','39','40','11399'));
        $p0Ores = $this->getQuickReview(array('2268','2305','2267','2288','2287','2307','2272','2309','2073','2310','2270','2306','2286','2311','2308'));
        $p1Ores = $this->getQuickReview(array('2393','2396','3779','2401','2390','2397','2392','3683','2389','2399','2395','2398','9828','2400','3645'));
        $p2Ores = $this->getQuickReview(array('2329','3828','9836','9832','44','3693','15317','3725','3689','2327','9842','2463','2317','2321','3695','9830','3697','9838','2312','3691','2319','9840','3775','2328'));
        $p3Ores = $this->getQuickReview(array('2358','2345','2344','2367','17392','2348','9834','2366','2361','17898','2360','2354','2352','9846','9848','2351','2349','2346','12836','17136','28974'));
        $p4Ores = $this->getQuickReview(array('2867','2868','2869','2870','2871','2872','2875','2876'));*/

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
