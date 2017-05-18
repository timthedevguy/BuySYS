<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/12/17
 * Time: 1:51 PM
 */

namespace AppBundle\Security;


use AppBundle\Entity\AuthorizationEntity;
use AppBundle\Entity\UserEntity;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\ORM\EntityManager;

use GuzzleHttp\Client;

class RoleManager
{

    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
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

        $authCount = $this->em->getRepository('AppBundle:AuthorizationEntity')->getCount();
        $isRoleSet = false;

        if ($authCount > 0)
        {
            // We have entries, get corp & alliance info
            try {
                $characterId = $user->getCharacterId();
                $corpId = "";
                $allianceId = "";

                $client = new Client([
                    'base_uri' => 'https://esi.tech.ccp.is',
                    'timeout' => 10.0,
                    'headers' => [
                        'Accept' => 'application/json'
                    ]
                ]);

                $response = $client->get('/v4/characters/' . $characterId);
                $character = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);


                if (array_key_exists('alliance_id', $character))
                {
                    $allianceId = $character['alliance_id'];
                }
                if (array_key_exists('corporation_id', $character))
                {
                    $corpId = $character['corporation_id'];
                }


                $manualAuths = $this->em->getRepository('AppBundle:AuthorizationEntity')->findAllManualAuthorizations($corpId, $allianceId);

                if (count($manualAuths) > 0)
                {
                    $allianceRole = null;
                    $corpRole = null;

                    foreach($manualAuths as $auth)
                    {
                        if($auth->getType == 'A')
                        {
                            $allianceRole = $auth->getRole();
                        }
                        elseif($auth->getType == 'C')
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
                else
                {
                    //no manual auths - check contact/auto auth
                }

            } catch (Exception $e) {

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
}