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

    public static function generateURL($callbackurl, $clientid, &$session)
    {
        // Generates an oauth code to ensure Session didn't get hijacked
        $oauth = uniqid('OAA', true);
        $session->set('oauth', $oauth);

        // Return completed URL
        return self::SSO_URL.'?response_type=code&redirect_uri='.$callbackurl.'&client_id='.$clientid.'&state='.$oauth;
    }

    public function authorize()
    {
        // Create new Guzzle Client with default Headers
        $client = new Client([
            'base_uri' => 'https://login.eveonline.com',
            'timeout'  => 10.0,
            'headers' => [
                'Authorization' => 'Basic '.base64_encode($this->clientid.':'.$this->secretkey),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);

        // Create our Response Object
        $response = $client->post('/oauth/token', [
            'query' => [
                'grant_type' => 'authorization_code',
                'code' => $this->auth_code
            ]
        ]);

        // Decode the response body to JSON
        $results = \GuzzleHttp\json_decode($response->getBody()->getContents());
        dump($results);

    }
}