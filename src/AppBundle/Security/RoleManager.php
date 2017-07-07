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

class RoleManager
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

    public static function getDefaultRole()
    {
        return self::$defaultRole;
    }

    public static function getDeniedRole()
    {
        return self::$deniedRole;
    }


    public function updateAutoAppliedRoles(UserEntity $user)
    {

        $manualAuthCount = $this->em->getRepository('AppBundle:AuthorizationEntity')->getManualAuthCount();
        $contactCount = $this->em->getRepository('AppBundle:ContactEntity')->getContactCount();
        $isRoleSet = false;

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

                    foreach($manualAuths as $auth)
                    {
                        if($auth->getType() == 'A')
                        {
                            $allianceRole = $auth->getRole();
                        }
                        elseif($auth->getType() == 'C')
                        {
                            $corpRole = $auth->getRole();
                        }
                    }

                    if(!empty($corpRole)) //apply corp role over alliance if found
                    {
                        $user->setRole($corpRole);
                        $isRoleSet = true;
                    }
                    elseif(!empty($allianceRole))
                    {
                        $user->setRole($allianceRole);
                        $isRoleSet = true;
                    }
                }

                if(!$isRoleSet)
                {
                    //nothing set by manual auth, check contacts
                    $contacts = $this->em->getRepository('AppBundle:ContactEntity')->getExistingContact($character->getId(), $character->getCorpId(), $character->getAllianceId());

                    if(count($contacts) > 0)
                    {

                        $pilotRole = null;
                        $allianceRole = null;
                        $corpRole = null;


                        foreach($contacts as $contact)
                        {
                            if($contact->getContactType() == 'A')
                            {
                                $allianceRole = $contact->getAuthorization()->getRole();
                            }
                            elseif($contact->getContactType() == 'C')
                            {
                                $corpRole = $contact->getAuthorization()->getRole();
                            }
                            elseif($contact->getContactType() == 'P')
                            {
                                $pilotRole = $contact->getAuthorization()->getRole();
                            }
                        }

                        if(!empty($pilotRole)) //apply pilot role over corp if found
                        {
                            $user->setRole($pilotRole);
                            $isRoleSet = true;
                        }
                        elseif(!empty($corpRole)) //apply corp role over alliance if found
                        {
                            $user->setRole($corpRole);
                            $isRoleSet = true;
                        }
                        elseif(!empty($allianceRole))
                        {
                            $user->setRole($allianceRole);
                            $isRoleSet = true;
                        }
                    }
                }

            } catch (Exception $e) {
                //not much we can do here if an error occurs while finding roles.  We'll just allow any previously applied roles to remain (not updating roles)
                $isRoleSet = true;
            }
        }

        if(!$isRoleSet)
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