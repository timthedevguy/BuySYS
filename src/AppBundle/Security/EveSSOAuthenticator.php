<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/5/17
 * Time: 3:49 PM
 */

namespace AppBundle\Security;

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
    private $ssoClientId;
    private $ssoSecretKey;

    public function __construct(EntityManager $em, \Symfony\Component\Routing\RouterInterface $router, $ssoClientId, $ssoSecretKey) {
        $this->router = $router;
        $this->em = $em;
        $this->ssoClientId = $ssoClientId;
        $this->ssoSecretKey = $ssoSecretKey;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('login_route');
        return new RedirectResponse($url);
    }

    public function getCredentials(Request $request)
    {

//                    $state = $request->query->get('state');
        $auth_code = $request->query->get('code');

        //if we have auth_code from SSO, attempt to authenticate user
        if(!empty($auth_code)) {

            // Get our Access Token
            try
            {
                $client = new Client([
                    'base_uri' => 'https://login.eveonline.com',
                    'timeout' => 10.0,
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($this->ssoClientId . ':' . $this->ssoSecretKey),
                        'Content-Type' => 'application/x-www-form-urlencoded'
                    ]
                ]);

                // Create our Response Object to get Access Token
                $response = $client->post('/oauth/token', [
                    'query' => [
                        'grant_type' => 'authorization_code',
                        'code' => $auth_code
                    ]
                ]);

                // Decode the response body to JSON
                $results = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);


                return array(
                    'access_token' => $results['access_token']
                );
            }
            catch(Exception $e)
            {
                throw new AuthenticationException('EVESSO :: Unable to obtain Access Token');
            }
        }


        //if no auth_code, return null to call 'start()'
        return null;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try
        {
            $client = new Client([
                'base_uri' => 'https://login.eveonline.com',
                'timeout' => 10.0,
                'headers' => [
                    'Authorization' => 'Bearer ' . $credentials['access_token']
                ]
            ]);

            $response = $client->get('/oauth/verify');

            $character = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
            $username = $character['CharacterName'];

            $user = $this->em->getRepository('AppBundle:UserEntity')->findOneBy(array('username' => $username));

            if(empty($user)) {
                //TODO: register user if none found!
            }

            return $user;
        }
        catch(Exception $e)
        {
            throw new AuthenticationException('EVESSO :: Unable to obtain Character ID');
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        //no need to do anything - Eve SSO authenticated user and provided the username
        return true;

    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
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