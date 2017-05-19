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
use EveBundle\API\ESI;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManager;

use GuzzleHttp\Client;

class RoleManager
{

    private $em;
    private $ESI;

    public function __construct(EntityManager $em, ESI $ESI) {
        $this->em = $em;
        $this->ESI = $ESI;
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
        'ROLE_DENIED'
    );

    private static $defaultRole = 'ROLE_MEMBER';
    private static $deniedRole = 'ROLE_DENIED';


    public static function getRoles()
    {
        return self::$rolesArray;
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
                            if($contact->getType() == 'A')
                            {
                                $allianceRole = $contact->getAuthorization()->getRole();
                            }
                            elseif($contact->getType() == 'C')
                            {
                                $corpRole = $contact->getAuthorization()->getRole();
                            }
                            elseif($contact->getType() == 'P')
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


    public function setDefaultRoles() {

        $defaultEntry = (new AuthorizationEntity())
            ->setEveId(-999)
            ->setName("Default Access (Everyone Not Configured)")
            ->setType("")
            ->setRole(RoleManager::getDefaultRole());

        $this->em->persist($defaultEntry);
        $this->em->flush();

        foreach(AuthorizationController::getContactLevels() as $id => $level)
        {
            $entry = (new AuthorizationEntity())
                ->setEveId($id)
                ->setName($level)
                ->setType("contact")
                ->setRole(RoleManager::getDefaultRole());

            $this->em->persist($entry);
            $this->em->flush();
        }
    }
}