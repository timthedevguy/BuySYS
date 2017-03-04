<?php
namespace AppBundle\ESI;

use GuzzleHttp\Client;

class ESI
{
    const ESI_URL = 'https://esi.tech.ccp.is';

    const SEARCH = '/v1/search/';
    const ALLIANCE_NAMES = '/v1/alliances/names/';
    const CORPORATION_NAMES = '/v1/corporations/names/';

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
