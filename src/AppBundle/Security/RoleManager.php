<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/12/17
 * Time: 1:51 PM
 */

namespace AppBundle\Security;


use AppBundle\Entity\UserEntity;

class RoleManager
{

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

    public static function updateAutoAppliedRoles(UserEntity $user)
    {

    }

    public static function getRoles()
    {
        return self::$rolesArray;
    }
}