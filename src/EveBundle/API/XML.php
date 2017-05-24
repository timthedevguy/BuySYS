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
use GuzzleHttp\Exception\RequestException;

use Symfony\Component\Config\Definition\Exception\Exception;

class XML
{
    private static $defaultTimeout = 10.0;
    private static $XML_API_URI = 'https://api.eveonline.com';

    public function &getContacts($APIKey, $APICode)
    {
        $contacts = Array();

        try
        {
            $characterId = $this->getCharacterId($APIKey, $APICode);

            $responseXML = $this->makeRequest('/char/ContactList.xml.aspx?keyID='. $APIKey .'&vCode='. $APICode .'&characterID='. $characterId);

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
                            if (null !== $contact['contactID'] && $contact['contactName'] && $contact['standing'])
                            {

                                //fix abnormal standings
                                $standing = (double) $contact['standing'];
                                if ($standing < -5)
                                {
                                    $standing = -10;
                                }
                                elseif ($standing < 0)
                                {
                                    $standing = -5;
                                }
                                elseif ($standing > 5)
                                {
                                    $standing = 10;
                                }
                                elseif ($standing > 0) {
                                    $standing = 5;
                                }

                                array_push($contacts, new Contact(
                                    (String) $contact['contactID'],
                                    (String) $contact['contactName'],
                                    $contactType,
                                    (String) $standing,
                                    (String) $contact['inWatchlist']
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

        return $contacts;
    }

    private function getCharacterId($APIKey, $APICode)
    {
        $characterId = null;

        try
        {
            $responseXML = $this->makeRequest('/account/APIKeyInfo.xml.aspx?keyID='. $APIKey .'&vCode='. $APICode);
        }
        catch (Exception $e)
        {
            throw $e;
        }

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

            if($response->getStatusCode() !== 200)
            {
                throw new Exception("Bad Request! Error Code: " . $response->getStatusCode());
            }

            return new \SimpleXMLElement($response->getBody()->getContents());

        }
        catch (RequestException $e) {

            throw new Exception($e->getMessage());
        }
        catch (Exception $e) {
            throw $e;
        }
    }
}