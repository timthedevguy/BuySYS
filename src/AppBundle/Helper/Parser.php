<?php
namespace AppBundle\Helper;

use AppBundle\Helper\Helper;
use AppBundle\Entity\LineItemEntity;
use AppBundle\Entity\ExclusionEntity;

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
        //define Vars
        $rawInputArray = array();
        $results = array();
        $types = $this->doctrine->getRepository('EveBundle:TypeEntity', 'evedata');
        // Get any exclusions
        $mode = $this->helper->getSetting("buyback_whitelist_mode");
        $exclusions = $this->doctrine->getRepository('AppBundle:ExclusionEntity')->findByWhitelist($mode);
        $groups = array();

        // Get exclusion groups
        foreach($exclusions as $exclusion) {

            $groups[] = $exclusion->getMarketGroupId();
        }

        // Get raw input as array (split on line breaks)
        $rawInputArray = preg_split("/\r\n|\n|\r/", $raw);

        // Check first entry to determine parser type
        $item = explode("\t", $rawInputArray[0]); // Split by TAB

        if (count($item) > 2) { // If more than 2 tabs, likely copy/pasted from game (TODO: CHECK!)
          // Check format of copy/paste and call appropriate parser

          //inventory parser
          //remote can parser
          //contract parser
        } else { // Likely manual user input
          // Check format of user input and call appropriate parser

          //number first parser
          //number last parser
        }


        // Return results

        // Build our Item List and TypeID List
        foreach(preg_split("/\r\n|\n|\r/", $raw) as $line)
        {
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

                    if(in_array($type->getMarketGroupId(), $groups) & $mode == "false") {

                        $lineItem->setIsValid(false);
                    }elseif(!in_array($type->getMarketGroupID(), $groups) & $mode == "true") {

                        $lineItem->setIsValid(false);
                    }
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
                    // Found '#,### Type Name'
                    $type = $types->findOneByTypeName($itemA[3]);

                    if($type != null)
                    {
                        $lineItem->setTypeId($type->getTypeId());
                        $lineItem->setName($type->getTypeName());

                        if(in_array($type->getMarketGroupId(), $groups) & $mode == "false") {

                            $lineItem->setIsValid(false);
                        }elseif(!in_array($type->getMarketGroupID(), $groups) & $mode == "true") {

                            $lineItem->setIsValid(false);
                        }
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
