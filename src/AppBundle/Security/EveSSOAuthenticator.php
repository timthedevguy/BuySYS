<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/5/17
 * Time: 3:49 PM
 */

namespace AppBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class EveSSOAuthenticator - more info: http://symfony.com/doc/current/security/guard_authentication.html
 * @package AppBundle\Security
 */
class EveSSOAuthenticator extends AbstractGuardAuthenticator
{

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // TODO: Implement start() method.
    }

    public function getCredentials(Request $request)
    {

        //if authenticated, return null to continue without authentication
        if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            return;
        }

        return array(
            'token' => $token,
        );

        //if not authenticated, try to authenticate
        // TODO: Implement getCredentials() method.
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // TODO: Implement getUser() method.
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // TODO: Implement checkCredentials() method.
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // TODO: Implement onAuthenticationFailure() method.
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // on success, let the request continue
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }
}