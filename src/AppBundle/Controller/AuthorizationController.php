<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/12/17
 * Time: 3:20 PM
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\ESI\ESI;
use AppBundle\Entity\RegWhitelistEntity;

class AuthorizationController extends Controller
{

    /**
     * @Route("/system/admin/authorization", name="admin_authorization")
     */
    public function authorizationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository('AppBundle:RegWhitelistEntity')->findAll();

        return $this->render('access_control/authorization.html.twig', array('page_name' => 'Access Control',
            'sub_text' => '', 'items' => $items));
    }

    /**
     * @Route("/system/admin/ajax_CorpSearch", name="ajax_CorpSearch")
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
     * @Route("/system/admin/ajax_AddWhitelist", name="ajax_AddWhitelist")
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

        return $this->redirectToRoute('admin_authorization');
    }

    /**
     * @Route("/system/admin/authorization/delete/{id}", name="admin_authorization_delete")
     */
    public function authorizationDeleteAction(Request $request, RegWhitelistEntity $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();

        return $this->redirectToRoute('admin_authorization');
    }
}