<?php
namespace AppBundle\Helper;

use AppBundle\Helper\Helper;
use AppBundle\Entity\LineItemEntity;

/**
 * Handles all text parsing functions
 */
class Parser
{
    private $doctrine;

    public function __construct($doctrine, Helper $helper)
    {
        $this->doctrine = $doctrine;
        $this->helper = $helper;
    }

    public function GetLineItemsFromPasteData($raw)
    {
        dump($raw);
        $results = array();
        $types = $this->doctrine->getRepository('EveBundle:TypeEntity', 'evedata');

        // Build our Item List and TypeID List
        foreach(preg_split("/\r\n|\n|\r/", $raw) as $line)
        {
            dump($line);
            // Split by TAB
            $item = explode("\t", $line);
            // Create result entry
            $lineItem = new LineItemEntity();

            // Did this contain tabs?
            if(count($item) > 1)
            {
                $type = $types->findOneByTypeName($item[0]);

                if($type != null)
                {
                    $lineItem->setTypeId($type->getTypeId());
                    $lineItem->setName($type->getTypeName());
                    //$lineItem->setVolume($type->getVolume());
                }
                else
                {
                    $lineItem->setTypeId(0);
                    $lineItem->setName($item[0]);
                    //$lineItem->setVolume(0);
                    $lineItem->setIsValid(false);
                }

                if($item[1] == "")
                {
                    $lineItem->setQuantity(1);
                }
                else
                {
                    $lineItem->setQuantity(str_replace('.', '', $item[1]));
                    $lineItem->setQuantity(str_replace(',', '', $lineItem->getQuantity()));
                }
            }
            else
            {
                // Didn't contain tabs, so user typed it in?  Try to preg match it
                $itemA = array();

                if(preg_match("/((\d|,)*)\s+(.*)/", $line, $itemA))
                {
                    dump($itemA);
                    // Found '#,### Type Name'
                    $type = $types->findOneByTypeName($itemA[3]);

                    if($type != null)
                    {
                        $lineItem->setTypeId($type->getTypeId());
                        $lineItem->setName($type->getTypeName());
                        //$lineItem->setVolume($type->getVolume());
                    }
                    else
                    {
                        $lineItem->setTypeId(0);
                        $lineItem->setName($itemA[3]);
                        //$lineItem->setVolume(0);
                        $lineItem->setIsValid(false);
                    }

                    if($itemA[1] == "")
                    {
                        $lineItem->setQuantity(1);
                    }
                    else
                    {
                        $lineItem->setQuantity(str_replace('.', '', $itemA[1]));
                        $lineItem->setQuantity(str_replace(',', '', $lineItem->getQuantity()));
                    }
                }
                else
                {
                    $lineItem->setTypeId(0);
                    $lineItem->setName('Item not found: '+$line);
                    //$lineItem->setVolume(0);
                    $lineItem->setIsValid(false);
                }
            }

            $results[] = $lineItem;
        }

        return $results;
    }
}
