<?php
namespace AppBundle\Controller;

use AppBundle\Form\ExclusionForm;

use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
	 * @Route("/admin/settings", name="admin_settings_buyback")
	 */
	public function settingsAction(Request $request)
    {
        if($request->getMethod() == 'POST')
        {
            foreach($request->request->keys() as $setting)
            {
                $this->get('helper')->setSetting($setting, $request->request->get($setting));
            }
            $this->addFlash('success', 'Settings saved!');
        }

        $allSettings = $this->getDoctrine()->getRepository('AppBundle:SettingEntity', 'default')->findAll();

        $settings = array();
        foreach($allSettings as $setting)
        {
            $settings[$setting->getName()] = $setting->getValue();
        }

        return $this->render('admin/settings.html.twig', array(
            'settings' => $settings
        ));
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

        if($success == true)
        {
            $this->addFlash('success', "Repopulated Cache with defaults");
        }
        else
        {
            $this->addFlash('error', "There was an issue repopulating the Cache, Eve Central may be down.");
        }
        return $this->redirectToRoute('admin_tools');
    }

}
