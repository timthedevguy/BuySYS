<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/12/17
 * Time: 1:51 PM
 */

namespace AppBundle\Security;


use AppBundle\Controller\AuthorizationController;
use AppBundle\Entity\AuthorizationEntity;
use AppBundle\Entity\UserEntity;
use AppBundle\Helper\Helper;
use AppBundle\Utilities\ESI;
use AppBundle\Utilities\XML;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManager;

use GuzzleHttp\Client;

class AuthorizationManager
{

    private $em;
    private $ESI;
    private $xmlApi;

    public function __construct(EntityManagerInterface $em, ESI $ESI) {
        $this->em = $em;
        $this->ESI = $ESI;
        //$this->xmlApi = $xmlApi;
    }

    private static $rolesArray = Array(
        'ROLE_SYSTEM_ADMIN',
        'ROLE_TRANSACTION_ADMIN',
        'ROLE_MEMBER',
        'ROLE_ALLY',
        'ROLE_GUEST',
        'ROLE_DENIED',
        'ROLE_FRIEND',
        'ROLE_OTHER1',
        'ROLE_OTHER2',
        'ROLE_OTHER3'
    );

    private static $buybackRolesArray = array(
        'ROLE_ALLY',
        'ROLE_FRIEND',
        'ROLE_MEMBER',
        'ROLE_OTHER1',
        'ROLE_OTHER2',
        'ROLE_OTHER3',
        'ROLE_GUEST',
        'ROLE_DENIED'
    );

    private static $standingConversionArray = Array(
        "10" => '+10',
        "5" => '+5',
        "0" => 'Neutral',
        "-5" => '-5',
        "-10" => '-10'
    );

    private static $entitlementsArray = Array(
        'ROLE_ENTITLEMENT_BUYBACK'
    );


    private static $defaultRole = 'ROLE_MEMBER';
    private static $deniedRole = 'ROLE_DENIED';


    public static function getRoles()
    {
        return self::$rolesArray;
    }
    public static function getBuybackRoles()
    {
        return self::$buybackRolesArray;
    }
    public static function getEntitlements()
    {
        return self::$entitlementsArray;
    }
    public static function getDefaultRole()
    {
        return self::$defaultRole;
    }
    public static function getDeniedRole()
    {
        return self::$deniedRole;
    }


    public function updateAutoAppliedAuthorization(UserEntity $user)
    {
        $manualAuthCount = $this->em->getRepository('AppBundle:AuthorizationEntity')->getManualAuthCount();

        $isAuthSet = false;

        if ($manualAuthCount > 0)
        {
            try {
                // We have entries, get character info
                $character = $this->ESI->getCharacter($user->getCharacterId());

                //check manual auths first
                $manualAuths = $this->em->getRepository('AppBundle:AuthorizationEntity')->getExistingAuthorizations($character->getCorpId(), $character->getAllianceId());

                if (count($manualAuths) > 0)
                {
                    $allianceRole = null;
                    $corpRole = null;
                    $allianceEntitlements = "";
                    $corpEntitlements = "";

                    foreach($manualAuths as $auth)
                    {
                        if($auth->getType() == 'A')
                        {
                            $allianceRole = $auth->getRole();
                            $allianceEntitlements = $auth->getEntitlements();
                        }
                        elseif($auth->getType() == 'C')
                        {
                            $corpRole = $auth->getRole();
                            $corpEntitlements = $auth->getEntitlements();
                        }
                    }

                    if(!empty($corpRole)) //apply corp role over alliance if found
                    {
                        $user->setRole($corpRole);
                        $isAuthSet = true;
                    }
                    elseif(!empty($allianceRole))
                    {
                        $user->setRole($allianceRole);
                        $isAuthSet = true;
                    }
                }

            } catch (Exception $e) {
                //not much we can do here if an error occurs while finding roles.  We'll just allow any previously applied authorization to remain (not updating roles)
                $isAuthSet = true;
            }
        }

        if(!$isAuthSet)
        {
            //if no authorization occurred, assign default access
            $authEntity = $this->em->getRepository('AppBundle:AuthorizationEntity')->findOneBy(Array('eveid' => -999));
            $user->setRole($authEntity->getRole());
        }

        return $user;
    }


    public function updateContacts($apiKey, $apiCode)
    {
        try
        {
            $addedContacts = 0;

            $contacts = &$this->xmlApi->getContacts($apiKey, $apiCode);

            if (!empty($contacts))
            {
                //delete from table
                $this->em->getRepository('AppBundle:ContactEntity')->deleteAll();

                //repopulate

                //get authorization levels
                $auths = $this->em->getRepository('AppBundle:AuthorizationEntity')->findAllAutoAuthorizations();

                $authArray = Array();
                foreach ($auths as $auth)
                {
                    $authArray[$auth->getName()] = $auth;
                }

                //loop through contacts and add
                foreach ($contacts as $contact)
                {
                    $contactEntity = new ContactEntity();

                    $formattedContactLevel = self::$standingConversionArray[$contact->getStanding()];

                    $contactEntity
                        ->setContactName($contact->getContactName())
                        ->setContactId($contact->getContactId())
                        ->setContactLevel($formattedContactLevel)
                        ->setContactType($contact->getContactType())
                        ->setLastUpdatedDate(new \DateTime("now"))
                        ->setAuthorization($authArray[$formattedContactLevel]);

                    $this->em->persist($contactEntity);
                    $addedContacts++;
                }
                $this->em->flush();

            }

        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $addedContacts;
    }
}