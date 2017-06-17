<?php

/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/19/17
 * Time: 12:50 PM
 */
namespace EveBundle\API;

use GuzzleHttp\Client;
use EveBundle\Model\Character;

class ESI
{
    private static $ESI_URI = 'https://esi.tech.ccp.is';
    private static $defaultTimeout = 10.0;


    public function getCharacter($characterId)
    {
        $character = new Character();

        //call API
        $client = new Client([
            'base_uri' => self::$ESI_URI,
            'timeout' => self::$defaultTimeout,
            'headers' => [
                'Accept' => 'application/json'
            ]
        ]);

        $response = $client->get('/v4/characters/' . $characterId);
        $responseJson = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        //build model
        $character
            ->setId($characterId)
            ->setName(array_key_exists('name', $responseJson) ? $responseJson['name'] : null)
            ->setCorpId(array_key_exists('corporation_id', $responseJson) ? $responseJson['corporation_id'] : null)
            ->setAllianceId(array_key_exists('alliance_id', $responseJson) ? $responseJson['alliance_id'] : null)
            ->setDOB(array_key_exists('birthday', $responseJson) ? $responseJson['birthday'] : null)
            ->setGender(array_key_exists('gender', $responseJson) ? $responseJson['gender'] : null)
            ->setRaceId(array_key_exists('race_id', $responseJson) ? $responseJson['race_id'] : null)
            ->setSecStatus(array_key_exists('security_status', $responseJson) ? $responseJson['security_status'] : null)
            ->setDescription(array_key_exists('description', $responseJson) ? $responseJson['description'] : null);

        return $character;
    }
}