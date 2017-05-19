<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 5/19/17
 * Time: 1:59 PM
 */

namespace EveBundle\API;

use EveBundle\Model\Contact;
use GuzzleHttp\Client;

use Symfony\Component\Config\Definition\Exception\Exception;

class XML
{
    private static $defaultTimeout = 10.0;
    private static $XML_API_URI = 'https://api.eveonline.com';

    public function getContacts($APIKey, $APICode)
    {
        $contacts = Array();

        try
        {
            $characterId = $this->getCharacterId($APIKey, $APICode);

            $responseXML = $this->makeRequest('/char/ContactList.xml.aspx?keyID='. $APIKey .'&vCode='. $APICode .'&characterID='. $characterId);
            dump($responseXML);
            if (!empty($responseXML))
            {
                foreach ($responseXML->result->rowset as $rowSet)
                {
                    $rowSetName = (string)$rowSet->attributes()->name;

                    if ($rowSetName == 'corporateContactList')
                    {
                        $contactType = 'C';
                    }
                    elseif ($rowSetName == 'allianceContactList')
                    {
                        $contactType = 'A';
                    }
                    else
                    {
                        $contactType = 'P';
                    }

                    if ($rowSetName === 'corporateContactList' || 'allianceContactList' || 'contactList')
                    {
                        foreach ($rowSet->row as $contact)
                        {
                            dump($contact);
                            break;
                            if (null !== $contact['characterID'] && $contact['contactName'] && $contact['standing'])
                            {
                                array_push($contacts, new Contact(
                                    $contact['characterID'],
                                    $contact['contactName'],
                                    $contactType,
                                    $contact['standing'],
                                    $contact['inWatchlist']
                                ));
                            }
                        }
                    }
                }
            }
        }
        catch (Exception $e)
        {
            throw $e;
        }
        dump(empty($contacts));
        return $contacts;
    }

    private function getCharacterId($APIKey, $APICode)
    {
        $characterId = null;

        $responseXML = $this->makeRequest('/account/APIKeyInfo.xml.aspx?keyID='. $APIKey .'&vCode='. $APICode);
        $responseXML->result->rowset;

        if (!empty($responseXML))
        {
            foreach ($responseXML->result->key->rowset as $characters)
            {
                if ((string)$characters->attributes()->name == 'characters')
                {
                    foreach ($characters->row as $character)
                    {
                        if (null !== $character['characterID'])
                        {
                            $characterId = $character['characterID'];
                            break;
                        }
                    }
                    if(!empty($characterId))
                    {
                        break;
                    }
                }
            }
        }

        return $characterId;
    }

    private function makeRequest($queryString)
    {
        try {

            $client = new Client([
                'base_uri' => self::$XML_API_URI,
                'timeout' => self::$defaultTimeout
            ]);

            $response = $client->get($queryString);
            $responseXML = new \SimpleXMLElement($response->getBody()->getContents());

            return $responseXML;

        } catch (Exception $e) {
            throw $e;
        }
    }
}