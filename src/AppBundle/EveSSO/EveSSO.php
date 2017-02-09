<?php
namespace AppBundle\EveSSO;

class EveSSO
{
    const SSO_URL = 'https://login.eveonline.com/oauth/authorize';

    private $clientid;
    private $secretkey;
    private $callbackurl;

    public function __construct($clientid, $secretkey, $callbackurl, $request)
    {
        $this->clientid = $clientid;
        $this->secretkey = $secretkey;
        $this->callbackurl = $callbackurl;
    }

    public static function generateURL($callbackurl, $clientid, &$session)
    {
        // Generates an oauth code to ensure Session didn't get hijacked
        $oauth = uniqid('OAA', true);
        $session->set('oauth', $oauth);

        // Return completed URL
        return self::SSO_URL.'?response_type=code&redirect_uri='.$callbackurl.'&client_id='.$clientid.'&state='.$oauth;
    }
}