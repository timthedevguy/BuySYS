<?php
namespace AppBundle\ESI;

use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use nullx27\ESI\Api;

/*
 * Magic function accepts anything called and maps it to API_NAMESPACES, which I hardcoded for convenience.
 * Follow the ESI documentation for the endpoints to send the right data. Headers are done for you.
 *
 * Usage via Controller:
 * $ESI = new ESI($this->get('eve_sso'), $request->getSession());
 * $walletSummary = $ESI->getCharactersCharacterIdWallets(["characterId" => $this->getUser()->getCharacterId()]);
 * $walletSummary[0]['balance']
 */

class ESI
{
    const ESI_URL = 'https://esi.tech.ccp.is';
    const SEARCH = '/v1/search/';
    const ALLIANCE_NAMES = '/v1/alliances/names/';
    const CORPORATION_NAMES = '/v1/corporations/names/';
	const API_NAMESPACES = [
		"AllianceAPI" => ["getAlliances", "getAlliancesAllianceId"],
		"WalletApi" => ["getCharactersCharacterIdWallets"],
		"KillmailsApi" => ["getCharactersCharacterIdKillmailsRecent"],
		"MailApi" => ["postCharactersCharacterIdMail"]
	];
	
	protected $session;

    public function __construct(\EveBundle\API\SSO $eveSSO, Session $session)
    {
        $this->session = $session;
		
		if(strtotime($session->get("esi_access_expire")) <= time()+10) {
			
			$refreshToken = $eveSSO->updateWithRefreshToken($session->get("esi_refresh_token"));
			
			$accessTokenValue = $refreshToken->getAccessTokenValue();
			$accessTokenExpire = $refreshToken->getExpiry();
			$refreshTokenValue = $refreshToken->getRefreshToken();
			
			$session->set("esi_access_token", $accessTokenValue);
			$session->set("esi_access_expire", $accessTokenExpire);
			$session->set("esi_refresh_token", $refreshTokenValue);
			
		}
    }
	
	public function __call($method, $arguments)
    {
		if(count($arguments) != 1 || !is_array($arguments[0]))
			throw new \Exception("ESI::[API Method] requires a single array parameter of key=>value pairs.");
		$arguments = $arguments[0];
		
		$found = false;
		foreach(self::API_NAMESPACES as $namespace => $functions)
			if(in_array($method, $functions)) {
				$found = $namespace;
				break;
			}
			
		if($found===false) {
			return ["Error" => "Unknown method '".$method."'..."];
		}
		
		$class = "nullx27\\ESI\\Api\\".$found;
		$api_instance = new $class();
		
		try {
			
			$r = new \ReflectionMethod($class, $method);
			$params = $r->getParameters();
			
			$api_requirements_met = true;
			$api_params = [];
			foreach($params as $param) {
				
				$apiParamName = $param->getName();
				$apiParamOptional = $param->isOptional();
				$apiParamProvided = isset($arguments[$apiParamName]) ? $arguments[$apiParamName] : null;
				
				switch($apiParamName) {
					case "token":
						if(empty($this->session)) {
							return ["Error" => "No access_token from session."];
						}
						$apiParamProvided = $this->session->get("esi_access_token");
						break;
						
					case "user_agent":
						$apiParamProvided = "AmSYS";
						break;
						
					case "datasource":
						$apiParamProvided = "tranquility";
						break;
				}
				
				if(!$apiParamOptional && $apiParamProvided === null) {
					$api_requirements_met = $apiParamName;
					break;
				}
				
				$api_params[$param->getName()] = $apiParamProvided;
			}			
			
			if($api_requirements_met !== true) {
				if(gettype($api_requirements_met) == "boolean") {
					return ["Error" => "Missing required parameter(s)"];
				}
				else {
					return ["Error" => "Missing required parameter '".$api_requirements_met."'!"];
				}
			}
			
			$result = null;
			try {
				$result = call_user_func_array(array($api_instance, $method), $api_params);
				return $result;
			}
			catch(\nullx27\ESI\ApiException $e) {
				return ["Error" => "Error when calling ".$method.": ".$e->getMessage(), "headers" => $e->getResponseHeaders (), "body" => $e->getResponseBody()];
			}
			catch(Exception $e) {
				return ["Error" => "Error when calling ".$method.": ".$e->getMessage(), "type" => gettype($e)];
				
			}
		}
		catch (Exception $e) {
			return ["Error" => "Class Not Found: ".$e->getMessage()];
		}
	}
	
    //https://esi.tech.ccp.is/latest/search/?categories=alliance%2Ccorporation&datasource=tranquility&language=en-us&search=test&strict=false

    /**
     * @param Array $categories Array with any of agent, alliance, character, constellation, corporation, faction
     *                    inventoryType, region, solarsystem, station, wormhole
     * @param string $datasource
     * @param string $language
     * @param string $search Search term
     * @param boolean $strict Strict match?
     *
     * @return JSON ESI Results
     */
    public static function Search($categories, $search, $datasource = 'tranquility', $language = 'en-us', $strict = false)
    {
        $client = new Client([
            'base_uri' => self::ESI_URL,
            'timeout' => 10.0,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $results = $client->get(self::SEARCH, [
            'query' => [
                'categories' => implode(',', $categories),
                'datasource' => $datasource,
                'language' => $language,
                'search' => $search,
                'strict' => $strict
            ]
        ]);

        return \GuzzleHttp\json_decode($results->getBody()->getContents(), true);
    }

    /**
     * @param array $allianceids Array of Alliance IDs to pull Name for
     * @param string $datasource
     *
     * @return mixed
     */
    public static function AllianceNames($allianceids, $datasource = 'tranquility')
    {
        $results = array();

        $client = new Client([
            'base_uri' => self::ESI_URL,
            'timeout' => 10.0,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if(count($allianceids) > 0)
        {
            // Lookup in batches of 20
            for($i = 0; $i < count($allianceids); $i += 20)
            {
                $limit = $i+20;
                if($limit > count($allianceids)) {$limit = count($allianceids);}

                $lookup = array();

                for($j = $i; $j < $limit; $j++)
                {
                    $lookup[] = $allianceids[$j];
                }

                $lookupResults = $client->get(self::ALLIANCE_NAMES, [
                    'query' => [
                        'alliance_ids' => implode(',', $allianceids),
                        'datasource' => $datasource
                    ]
                ]);

                $results = array_merge($results, \GuzzleHttp\json_decode($lookupResults->getBody()->getContents(), true));
            }
        }

        return $results;
    }

    /**
     * @param $corporationids
     * @param string $datasource
     *
     * @return mixed
     */
    public static function CorporationNames($corporationids, $datasource = 'tranquility')
    {
        $results = array();

        $client = new Client([
            'base_uri' => self::ESI_URL,
            'timeout' => 10.0,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        if(count($corporationids) > 0)
        {
            // Lookup in batches of 20
            for($i = 0; $i < count($corporationids); $i += 20)
            {
                $limit = $i+20;
                if($limit > count($corporationids)) {$limit = count($corporationids);}

                $lookup = array();

                for($j = $i; $j < $limit; $j++)
                {
                    $lookup[] = $corporationids[$j];
                }

                $lookupResults = $client->get(self::CORPORATION_NAMES, [
                    'query' => [
                        'corporation_ids' => implode(',', $lookup),
                        'datasource' => $datasource
                    ]
                ]);

                $results = array_merge($results, \GuzzleHttp\json_decode($lookupResults->getBody()->getContents(), true));
            }
        }

        return $results;
    }
}
