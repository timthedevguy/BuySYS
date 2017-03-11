<?php
namespace AppBundle\EveSSO;

use Symfony\Component\Config\Definition\Exception\Exception;
use GuzzleHttp\Client;

class EveSSO
{
    const SSO_URL = 'https://login.eveonline.com/oauth/authorize';

    private $clientid;
    private $secretkey;
    private $auth_code;
    private $access_token;

    public function __construct($clientid, $secretkey, &$request)
    {
        if($request != null)
        {
            $session = $request->getSession();

            if($session != null)
            {
                $state = "";

                try
                {
                    $this->auth_code = $request->query->get('code');
                    $state = $request->query->get('state');
                }
                catch(Exception $e)
                {
                    throw new Exception('EVESSO :: Unable to retrieve AuthCode or State from return header.');
                }

                if( $request->getSession()->get('oauth') == $state)
                {
                    $this->clientid = $clientid;
                    $this->secretkey = $secretkey;
                }
                else
                {
                    throw new Exception('EVESSO :: Possible Hijacking Attempt, State does not match OAuth code.');
                }
            }
            else
            {
                throw new Exception('EVESSO :: Session in request parameter cannot be null.');
            }
        }
        else
        {
            throw new Exception('EVESSO :: request parameter cannot be null.');
        }
    }

    /**
     * Generates a properly formatted URL for an EveSSO Login button
     *
     * @param string $callbackurl
     * @param string $clientid
     * @param Symfony\Component\HttpFoundation\Session\Session $session
     * @return string
     */
    public static function generateURL($callbackurl, $clientid, &$session)
    {
        // Generates an oauth code to ensure Session didn't get hijacked
        $oauth = uniqid('OAA', true);
        // Add OAuth code to session
        $session->set('oauth', $oauth);

        // Return completed URL
        return self::SSO_URL.'?response_type=code&redirect_uri='.$callbackurl.'&client_id='.$clientid.'&state='.$oauth;
    }

    public function authorize()
    {
        $character = null;

        // Get our Access Token
        try
        {
            // Create new Guzzle Client with Authorization Headers
            $client = new Client([
                'base_uri' => 'https://login.eveonline.com',
                'timeout' => 10.0,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($this->clientid . ':' . $this->secretkey),
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ]
            ]);

            // Create our Response Object to get Access Token
            $response = $client->post('/oauth/token', [
                'query' => [
                    'grant_type' => 'authorization_code',
                    'code' => $this->auth_code
                ]
            ]);

            // Decode the response body to JSON
            $results = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

            // Grab Access Token
            $this->access_token = $results['access_token'];
        }
        catch(Exception $e)
        {
            throw new Exception('EVESSO :: Unable to obtain Access Token');
        }

        unset($client);
        unset($response);

        // Get Character ID
        try
        {
            $client = new Client([
                'base_uri' => 'https://login.eveonline.com',
                'timeout' => 10.0,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->access_token
                ]
            ]);

            $response = $client->get('/oauth/verify');

            $character = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
        }
        catch(Exception $e)
        {
            throw new Exception('EVESSO :: Unable to obtain Character ID');
        }

        unset($client);
        unset($response);

        // Get Full Character Information
        try
        {
            $client = new Client([
                'base_uri' => 'https://esi.tech.ccp.is',
                'timeout' => 10.0,
                'headers' => [
                    'Accept' => 'application/json'
                ]
            ]);

            $response = $client->get('/v4/characters/'.$character['CharacterID']);
            $results = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);
            $results['character_id'] = $character['CharacterID'];

            return $results;
        }
        catch(Exception $e)
        {
            throw new Exception('EVESSO :: Unable to obtain Character Information.');
        }
    }
}