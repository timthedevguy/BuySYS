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

        $state = $request->query->get('state');
        $auth_code = $request->query->get('code');

        //if we have auth_code from SSO, attempt to authenticate user
        if(!empty($auth_code) and !empty($state)) {

            if($request->getSession()->get('oauth') != $state) { //make sure session didn't get hijacked
                throw new AuthenticationException('Invalid Session State - Please try again');
            }

            // Get our Access Token
            try {
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
            } catch (Exception $e) {
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
            $client = new Client([
                'base_uri' => 'https://login.eveonline.com',
                'timeout' => 10.0,
                'headers' => [
                    'Authorization' => 'Bearer ' . $credentials['access_token']
                ]
            ]);

            $response = $client->get('/oauth/verify');

            $character = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
            $characterId = $character['CharacterID'];

            $user = $this->em->getRepository('AppBundle:UserEntity')->findOneBy(array('characterId' => $characterId));

            //if user doesnt exist, create one!
            if(empty($user)) {
                $user = new UserEntity();
                    $user->setCharacterId($characterId);
                    $user->setCharacterName($character['CharacterName']);
                    $user->setUsername($character['CharacterName']);
                    $user->setIsActive(true);
                    $user->setLastLogin(new \DateTime());
                    $user->setRole("ROLE_MEMBER");
                $preferences = new UserPreferencesEntity(); //constructor sets defaults

                // Save User
                $this->em->persist($user);//persist user
                $preferences->setUser($user);
                $this->em->persist($preferences);//persist user
                $this->em->flush();
            } else {
                //update last login
                $user->setLastLogin(new \DateTime());
                $this->em->flush();
            }

            return $user;
        }
        catch(Exception $e)
        {
            throw new AuthenticationException('Unable to obtain Character from Eve SSO - Please try again later\'');
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        //check roles and fail check if user does not have access to app
        $isAuthorized = false;

        if(!in_array('ROLE_DENIED', $user->getRoles()))
        {

            $whitelist = $this->em->getRepository('AppBundle:RegWhitelistEntity')->getCount();
            if ($whitelist > 0) {
                // We have entries, check if the alliance or corporation is allowed
                try {
                    $client = new Client([
                        'base_uri' => 'https://esi.tech.ccp.is',
                        'timeout' => 10.0,
                        'headers' => [
                            'Accept' => 'application/json'
                        ]
                    ]);

                    $response = $client->get('/v4/characters/' . $user->getCharacterId());
                    $character = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);


                    if (array_key_exists('alliance_id', $character)) {
                        if ($this->em->getRepository('AppBundle:RegWhitelistEntity')->findAllianceCount($character['alliance_id']) > 0) {
                            $isAuthorized = true;
                        }
                    }

                    if (!$isAuthorized and array_key_exists('corporation_id', $character)) {
                        if ($this->em->getRepository('AppBundle:RegWhitelistEntity')->findCorporationCount($character['corporation_id']) > 0) {
                            $isAuthorized = true;
                        }
                    }
                } catch (Exception $e) {
                    throw new AuthenticationException('Unable to obtain Corporation Affiliation from Eve ESI - Please try again later');
                }

            } else {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            throw new AuthenticationException('This character is not authorized to use this application.  Please contact an administrator if you believe you should have access.');
        }

        return $isAuthorized;
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