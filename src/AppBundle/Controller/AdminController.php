<?php
namespace AppBundle\Controller;

use AppBundle\Entity\RegWhitelistEntity;
use Doctrine\DBAL\Types\TextType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\ESI\ESI;

class AdminController extends Controller
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
     * @Route("/admin/tools", name="admin_tools")
     */
    public function toolsAction(Request $request)
    {
        return $this->render('admin/tools.html.twig', array('page_name' => 'Admin Tools', 'sub_text' => 'Tools to help maintain the system'));
    }

    /**
     * @Route("/admin/clearcache", name="admin_clearcache")
     */
    public function clearCacheAction(Request $request)
    {
        $this->get('cache')->ClearCache();
        $this->addFlash('success', "Cleared the cache, remember to repopulate!");
        return $this->redirectToRoute('admin_tools');
    }

    /**
     * @Route("/admin/updatecache", name="admin_updatecache")
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

    /**
     * @Route("/admin/registration", name="admin_registration")
     */
    public function registrationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository('AppBundle:RegWhitelistEntity')->findAll();

        return $this->render('admin/registration.html.twig', array('page_name' => 'Admin',
            'sub_text' => 'Control who can register on the System', 'items' => $items));
    }

    /**
     * @Route("/admin/ajax_CorpSearch", name="ajax_CorpSearch")
     */
    public function ajax_CorpSearchAction(Request $request)
    {
        $searchString = $request->request->get('searchstring');

        $searchResults = ESI::Search(array('corporation', 'alliance'), $searchString);

        $allianceNames = array();
        $corporationNames = array();

        if(array_key_exists('alliance', $searchResults))
        {
            $allianceNames = ESI::AllianceNames($searchResults['alliance']);
        }

        if(array_key_exists('corporation', $searchResults))
        {
            $corporationNames = ESI::CorporationNames($searchResults['corporation']);
        }

        return $this->render('admin/_corp_search_results.html.twig', array('corporations' => $corporationNames,
            'alliances' => $allianceNames, 'acount' => count($allianceNames), 'ccount' => count($corporationNames)));
    }

    /**
     * @Route("/admin/ajax_AddWhitelist", name="ajax_AddWhitelist")
     */
    public function ajax_AddWhitelistAction(Request $request)
    {
        $eveid = $request->request->get('id');
        $type = $request->request->get('type');
        $name = $request->request->get('name');

        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:RegWhitelistEntity', 'default')->findByEveid($eveid);

        if(count($entries) == 0)
        {
            $entry = new RegWhitelistEntity();
            $entry->setEveId($eveid);
            $entry->setName($name);
            $entry->setType($type);

            $em->persist($entry);
            $em->flush();
        }

        return $this->redirectToRoute('admin_registration');
    }

    /**
     * @Route("/admin/registration/delete/{id}", name="admin_registration_delete")
     */
    public function registerDeleteAction(Request $request, RegWhitelistEntity $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();

        return $this->redirectToRoute('admin_registration');
    }


}
