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
use AppBundle\Entity\ContactEntity;
use AppBundle\Entity\UserEntity;
use AppBundle\Helper\Helper;
use EveBundle\API\ESI;
use EveBundle\API\XML;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManager;

use GuzzleHttp\Client;

class AuthorizationManager
{

    private $em;
    private $ESI;
    private $xmlApi;

    public function __construct(EntityManager $em, ESI $ESI, XML $xmlApi) {
        $this->em = $em;
        $this->ESI = $ESI;
        $this->xmlApi = $xmlApi;
    }

    private static $rolesArray = Array(
        'ROLE_SYSTEM_ADMIN',
        'ROLE_TRANSACTION_ADMIN',
        'ROLE_BUY_ADMIN',
        'ROLE_SELL_ADMIN',
        'ROLE_SRP_ADMIN',
        'ROLE_EDITOR',
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
        'ROLE_ENTITLEMENT_BUYBACK',
        'ROLE_ENTITLEMENT_SALES',
        'ROLE_ENTITLEMENT_PAGES',
        'ROLE_ENTITLEMENT_SRP'
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
        $contactCount = $this->em->getRepository('AppBundle:ContactEntity')->getContactCount();
        $isAuthSet = false;

        if ($manualAuthCount > 0 || $contactCount > 0)
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
                        $user->setRole($allianceRole);
                        $user->setEntitlements($allianceEntitlements);
                        $isAuthSet = true;
                    }
                    elseif(!empty($allianceRole))
                    {
                        $user->setRole($corpRole);
                        $user->setEntitlements($corpEntitlements);
                        $isAuthSet = true;
                    }
                }

                if(!$isAuthSet)
                {
                    //nothing set by manual auth, check contacts
                    if($contactCount > 0)
                    {
                        $contacts = $this->em->getRepository('AppBundle:ContactEntity')->getExistingContact($character->getId(), $character->getCorpId(), $character->getAllianceId());

                        $pilotRole = null;
                        $allianceRole = null;
                        $corpRole = null;

                        $pilotEntitlement = "";
                        $allianceEntitlement = "";
                        $corpEntitlement = "";

                        foreach($contacts as $contact)
                        {
                            if($contact->getContactType() == 'A')
                            {
                                $allianceRole = $contact->getAuthorization()->getRole();
                                $allianceEntitlement = $contact->getAuthorization()->getEntitlements();
                            }
                            elseif($contact->getContactType() == 'C')
                            {
                                $corpRole = $contact->getAuthorization()->getRole();
                                $corpEntitlement = $contact->getAuthorization()->getEntitlements();
                            }
                            elseif($contact->getContactType() == 'P')
                            {
                                $pilotRole = $contact->getAuthorization()->getRole();
                                $pilotEntitlement = $contact->getAuthorization()->getEntitlements();
                            }
                        }

                        if(!empty($pilotRole)) //apply pilot role over corp if found
                        {
                            $user->setRole($pilotRole);
                            $user->setEntitlements($pilotEntitlement);
                            $isAuthSet = true;
                        }
                        elseif(!empty($corpRole)) //apply corp role over alliance if found
                        {
                            $user->setRole($corpRole);
                            $user->setEntitlements($corpEntitlement);
                            $isAuthSet = true;
                        }
                        elseif(!empty($allianceRole))
                        {
                            $user->setRole($allianceRole);
                            $user->setEntitlements($allianceEntitlement);
                            $isAuthSet = true;
                        }
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
            $user->setEntitlements($authEntity->getEntitlements());
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