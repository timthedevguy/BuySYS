<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/5/17
 * Time: 3:49 PM
 */

namespace AppBundle\Security;

use AppBundle\Entity\UserEntity;
use AppBundle\Entity\UserPreferencesEntity;
use EveBundle\API\SSO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManager;

use GuzzleHttp\Client;

/**
 * Class EveSSOAuthenticator - more info: http://symfony.com/doc/current/security/guard_authentication.html
 * @package AppBundle\Security
 */
class EveSSOAuthenticator extends AbstractGuardAuthenticator
{

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;
    private $em;
    private $roleManager;
    private $eveSSO;

    public function __construct(EntityManager $em, \Symfony\Component\Routing\RouterInterface $router, RoleManager $roleManager, SSO $eveSSO) {
        $this->router = $router;
        $this->em = $em;
        $this->roleManager = $roleManager;
        $this->eveSSO = $eveSSO;

    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('login_route');
        return new RedirectResponse($url);
    }

    public function getCredentials(Request $request)
    {

        $state = $request->query->get('state');
        $auth_code = $request->query->get('code');

        //if we have auth_code from SSO, attempt to authenticate user
        if(!empty($auth_code) and !empty($state))
        {

            if($request->getSession()->get('oauth') != $state)
            { //make sure session didn't get hijacked
                throw new AuthenticationException('Invalid Session State - Please try again');
            }

            try
            {
                // Get our Access Token
                $accessToken = $this->eveSSO->getSSOAccessToken($auth_code);

                return array(
                    'access_token' => $accessToken->getAccessTokenValue()
                );
            }
            catch (Exception $e)
            {
                throw new AuthenticationException('Unable to obtain Access Token from EVE SSO - Please try again later');
            }
        }

        //if no auth_code, return null to call 'start()'
        return null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try
        {
            $characterToken = $this->eveSSO->getSSOCharacterToken($credentials['access_token']);

            $user = $this->em->getRepository('AppBundle:UserEntity')->findOneBy(array('characterId' => $characterToken->getCharacterId()));

            //if user doesnt exist, create one!
            if(empty($user)) {
                $user = new UserEntity();
                    $user->setCharacterId($characterToken->getCharacterId());
                    $user->setUsername($characterToken->getCharacterName());
                    $user->setIsActive(true);
                    $user->setLastLogin(new \DateTime());
                    $user->setRole(RoleManager::getDeniedRole()); //don't grant access until we're able to update roles
                $preferences = new UserPreferencesEntity(); //constructor sets defaults

                // Save User
                $this->em->persist($user);//persist user
                $preferences->setUser($user);
                $this->em->persist($preferences);//persist user
            } else {
                //update last login
                $user->setLastLogin(new \DateTime());
            }

            //update roles as needed
            $user = $this->roleManager->updateAutoAppliedRoles($user);
            $this->em->flush();

            return $user;
        }
        catch(Exception $e)
        {
            throw new AuthenticationException('Unable to obtain Character from Eve SSO - Please try again later');
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        //if they are denied or don't have roles, throw an authentication exception
        if(empty($user->getRoles()) || in_array(RoleManager::getDeniedRole(), $user->getRoles()))
        {
            throw new AuthenticationException('This character is not authorized to use this application.  Please contact an administrator if you believe you should have access.');
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        //send user to login page with error message
        $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

        $url = $this->router->generate('login_route');
        return new RedirectResponse($url);
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