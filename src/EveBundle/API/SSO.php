<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/19/17
 * Time: 1:53 PM
 */

namespace EveBundle\API;

use GuzzleHttp\Client;

use EveBundle\Model\CharacterToken;
use EveBundle\Model\SSOToken;

use Symfony\Component\Config\Definition\Exception\Exception;


class SSO
{

    private static $SSO_LOGIN_URI = 'https://login.eveonline.com';
    private static $defaultTimeout = 10.0;

    private $ssoClientId;
    private $ssoSecretKey;
    private $basicAuthHeader;

    public function __construct($ssoClientId, $ssoSecretKey) {
        $this->ssoClientId = $ssoClientId;
        $this->ssoSecretKey = $ssoSecretKey;
        $this->basicAuthHeader = 'Basic ' . base64_encode($ssoClientId . ':' . $ssoSecretKey);
    }


    public function getSSOAccessToken($authCode)
    {
        $ssoToken = new SSOToken();

        try
        {
            $client = new Client([
                'base_uri' => self::$SSO_LOGIN_URI,
                'timeout' => self::$defaultTimeout,
                'headers' => [
                    'Authorization' => $this->basicAuthHeader,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            // Create our Response Object to get Access Token
            $response = $client->post('/oauth/token', [
                'query' => [
                    'grant_type' => 'authorization_code',
                    'code' => $authCode
                ]
            ]);

            // Decode the response body to JSON
            $responseJson = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            $ssoToken
                ->setAccessTokenValue(array_key_exists('access_token', $responseJson) ? $responseJson['access_token'] : null)
                ->setExpiry(array_key_exists('expires_in', $responseJson) ? date("m/d/Y h:i:s a", time() + $responseJson['expires_in']) : null)
                ->setTokenType(array_key_exists('token_type', $responseJson) ? $responseJson['token_type'] : null)
                ->setRefreshToken(array_key_exists('refresh_token', $responseJson) ? $responseJson['refresh_token'] : null);

        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $ssoToken;
    }


    public function updateWithRefreshToken($refreshToken)
    {
        $ssoToken = new SSOToken();

        try
        {
            $client = new Client([
                'base_uri' => self::$SSO_LOGIN_URI,
                'timeout' => self::$defaultTimeout,
                'headers' => [
                    'Authorization' => $this->basicAuthHeader,
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            // Create our Response Object to get Access Token
            $response = $client->post('/oauth/token', [
                'query' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken
                ]
            ]);

            // Decode the response body to JSON
            $responseJson = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            $ssoToken
                ->setAccessTokenValue(array_key_exists('access_token', $responseJson) ? $responseJson['access_token'] : null)
                ->setExpiry(array_key_exists('expires_in', $responseJson) ? date("m/d/Y h:i:s a", time() + $responseJson['expires_in']) : null)
                ->setTokenType(array_key_exists('token_type', $responseJson) ? $responseJson['token_type'] : null)
                ->setRefreshToken(array_key_exists('refresh_token', $responseJson) ? $responseJson['refresh_token'] : null);

        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $ssoToken;
    }


    public function getSSOCharacterToken($accessTokenValue)
    {
        $characterToken = new CharacterToken();

        try
        {
            $client = new Client([
                'base_uri' => self::$SSO_LOGIN_URI,
                'timeout' => self::$defaultTimeout,
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessTokenValue
                ]
            ]);

            $response = $client->get('/oauth/verify');

            $responseJson = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            $characterToken
                ->setCharacterName(array_key_exists('CharacterName', $responseJson) ? $responseJson['CharacterName'] : null)
                ->setCharacterId(array_key_exists('CharacterID', $responseJson) ? $responseJson['CharacterID'] : null)
                ->setScopes(array_key_exists('Scopes', $responseJson) ? $responseJson['Scopes'] : null)
                ->setCharacterOwnerHash(array_key_exists('CharacterOwnerHash', $responseJson) ? $responseJson['CharacterOwnerHash'] : null)
                ->setTokenType(array_key_exists('TokenType', $responseJson) ? $responseJson['TokenType'] : null)
                ->setExpiry(array_key_exists('ExpiresOn', $responseJson) ? $responseJson['ExpiresOn'] : null);

        }
        catch(Exception $e)
        {
            throw $e;
        }

        return $characterToken;
    }
}