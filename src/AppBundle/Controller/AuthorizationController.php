<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/12/17
 * Time: 3:20 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\ContactEntity;
use AppBundle\Model\ContactSummaryModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\ESI\ESI;
use AppBundle\Entity\AuthorizationEntity;
use AppBundle\Security\RoleManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class AuthorizationController extends Controller
{

    private static $contactLevelArray = Array(
        1 => '+10',
        2 => '+5',
        3 => 'Neutral',
        4 => '-5',
        5 =>  '-10',
        6 => 'Not a Contact'
    );

    private static $standingConversionArray = Array(
        "10" => '+10',
        "5" => '+5',
        "0" => 'Neutral',
        "-5" => '-5',
        "-10" => '-10'
    );

    public static function getContactLevels() {
        return self::$contactLevelArray;
    }

    /**
     * @Route("/system/admin/authorization", name="admin_authorization")
     */
    public function authorizationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $manualItems = $em->getRepository('AppBundle:AuthorizationEntity')->findAllManualAuthorizations();
        $contactAuths = $em->getRepository('AppBundle:AuthorizationEntity')->findAllAutoAuthorizations();
        $contactResult = $em->getRepository('AppBundle:ContactEntity')->getContactSummary();


        $contactSummary = Array();

        if(count($contactResult) > 0) {
            //build default
            foreach (self::$contactLevelArray as $id => $contactLevel) {
                if ($id == 6) {
                    $contactSummary[$contactLevel] = new ContactSummaryModel($contactLevel, 'LOTS!');
                } else {
                    $contactSummary[$contactLevel] = new ContactSummaryModel($contactLevel);
                }
            }

            //override with actual results
            foreach ($contactResult as $result) {
                $contactLevel = $result['contactLevel'];

                $contactSummary[$contactLevel] = new ContactSummaryModel(
                    $contactLevel,
                    $result['contactCount'],
                    $result['lastUpdated']);
            }

            //set roles
            foreach ($contactAuths as $auth)
            {
                $contactSummary[$auth->getName()]->setSelectedRole($auth->getRole());
            }

        }

        return $this->render('access_control/authorization.html.twig', array('page_name' => 'Access Control',
            'sub_text' => '', 'items' => $manualItems, 'roles' => RoleManager::getRoles(), 'levels' => self::$contactLevelArray,
            'contactSummary' => $contactSummary));
    }

    /**
     * @Route("/system/admin/authorization/ajax_CorpSearch", name="ajax_CorpSearch")
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

        return $this->render('access_control/_corp_search_results.html.twig', array('corporations' => $corporationNames,
            'alliances' => $allianceNames, 'acount' => count($allianceNames), 'ccount' => count($corporationNames)));
    }

    /**
     * @Route("/system/admin/authorization/insert/manual", name="ajax_AddManualAuthorization")
     */
    public function ajax_AddManualAuthorizationAction(Request $request)
    {
        $eveid = $request->request->get('id');
        $type = $request->request->get('type');
        $name = $request->request->get('name');

        $em = $this->getDoctrine()->getManager();
        $entries = $em->getRepository('AppBundle:AuthorizationEntity', 'default')->findByEveid($eveid);

        if(count($entries) == 0)
        {
            $entry = (new AuthorizationEntity())
                ->setEveId($eveid)
                ->setName($name)
                ->setType($type)
                ->setRole(RoleManager::getDefaultRole());

            $em->persist($entry);
            $em->flush();
        }

        return $this->redirectToRoute('admin_authorization');
    }

    /**
     * @Route("/system/admin/authorization/delete/{id}", name="admin_authorization_delete")
     */
    public function authorizationDeleteAction(Request $request, AuthorizationEntity $item)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($item);
        $em->flush();

        return $this->redirectToRoute('admin_authorization');
    }

    /**
     * @Route("/system/admin/authorization/update/", name="ajax_update_auth_role")
     */
    public function ajax_UpdateAuthorizationRole(Request $request)
    {
        $eveid = $request->request->get('eveid');
        $role = $request->request->get('role');
        $name = $request->request->get('name');
        $type = $request->request->get('type');

        // Get Entity Manager
        $em = $this->getDoctrine('default')->getManager();
        $entry = $this->getDoctrine('default')->getRepository('AppBundle:AuthorizationEntity')->findOneBy(array('eveid' => $eveid));

        if(empty($entry))
        {
            $entry = new AuthorizationEntity();
            $entry->setEveId($eveid);
            $entry->setName($name);
            $entry->setType($type);
            $entry->setRole($role);

            $em->persist($entry);
            $em->flush();
        }
        else
        {
            $entry->setRole($role);
            $em->flush();
        }

        return new Response("OK");
    }


    /**
     * @Route("/system/admin/authorization/insert/contact", name="ajax_update_contacts")
     */
    public function ajax_UpdateContacts(Request $request)
    {
        $apiKey = $request->request->get('apiKey');
        $apiCode = $request->request->get('apiCode');

        if (empty($apiKey) || empty($apiCode))
        {
            return new Response("Missing Input");
        }

        try
        {
            $contacts = $this->get('eve_xml_api')->getContacts($apiKey, $apiCode);

            if (!empty($contacts))
            {
                //delete from table
                $this->getDoctrine('default')->getRepository('AppBundle:ContactEntity')->deleteAll();

                //repopulate

                //get authorization levels
                $em = $this->getDoctrine('default')->getManager();
                $auths = $this->getDoctrine('default')->getRepository('AppBundle:AuthorizationEntity')->findAllAutoAuthorizations();

                $authArray = Array();
                foreach ($auths as $auth)
                {
                    $authArray[$auth->getName()] = $auth;
                }

                //loop through contacts and add
                foreach ($contacts as $contact)
                {
                    $contactEntity = new ContactEntity();
                    $formattedContactLevel = self::$standingConversionArray[$contact->getStanding];

                    $contactEntity
                        ->setContactName($contact->getContactName())
                        ->setContactId($contact->getContactId())
                        ->setContactLevel($formattedContactLevel)
                        ->setContactType($contact->getContactType())
                        ->setLastUpdatedDate(time())
                        ->setAuthorization($authArray[$formattedContactLevel]);

                    $em->persist($contactEntity);
                    $em->flush();
                }

            }
        }
        catch (Exception $e)
        {
            return new Response("ERROR: " . $e->getMessage());
        }

        return new Response("OK");
    }

}