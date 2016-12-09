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
        $results = array();
        $types = $this->doctrine->getRepository('EveBundle:TypeEntity', 'evedata');
        // Get any exclusions
        $mode = $this->helper->getSetting("buyback_whitelist_mode");
        $exclusions = $this->doctrine->getRepository('AppBundle:ExclusionEntity')->findByWhitelist($mode);
        $groups = array();
        $oParser = new HueristicParser(); //default to hueristic

        // Get exclusion groups
        foreach($exclusions as $exclusion)
        {
            $groups[] = $exclusion->getMarketGroupId();
        }

        // Get raw input as array (split on line breaks)
        $rawInputArray = preg_split("/\r\n|\n|\r/", $raw);

        // Check first entry to determine parser type
        $item = explode("\t", $rawInputArray[0]); // Split by TAB

        if(count($item) > 1)
        { // If tabs, likely copy/pasted from game

            // Check format of copy/paste and call appropriate parser
            if(count($item) == 3) //Remote View Can in Station
            {
                $oParser = new RemoteViewCanParser();
            } elseif(count($item) == 4) { //Inventory or Contract
                $oParser = new InventoryParser();
            }

        }
        else // try manual user input
        {
            $oParser = new UserInputParser();
        }

        // Loop through results and apply parser
        foreach($rawInputArray as $line)
        {
            $lineItem = $oParser->parseLine(trim($line), $types, $groups, $mode);

            if($lineItem == null) //parser failed - try hueristic
            {
                $lineItem = (new HueristicParser())->parseLine(trim($line), $types, $groups, $mode);
            }

            $results[] = $lineItem;
        }


        // Return results
        return $results;

//        // Build our Item List and TypeID List
//        foreach(preg_split("/\r\n|\n|\r/", $raw) as $line)
//        {
//            // Split by TAB
//            $item = explode("\t", $line);
//            // Create result entry
//            $lineItem = new LineItemEntity();
//
//            // Did this contain tabs?
//            if(count($item) > 1)
//            {
//                $type = $types->findOneByTypeName($item[0]);
//
//                if($type != null)
//                {
//                    $lineItem->setTypeId($type->getTypeId());
//                    $lineItem->setName($type->getTypeName());
//
//                    if(in_array($type->getMarketGroupId(), $groups) & $mode == "false") {
//
//                        $lineItem->setIsValid(false);
//                    }elseif(!in_array($type->getMarketGroupID(), $groups) & $mode == "true") {
//
//                        $lineItem->setIsValid(false);
//                    }
//                    //$lineItem->setVolume($type->getVolume());
//                }
//                else
//                {
//                    $lineItem->setTypeId(0);
//                    $lineItem->setName($item[0]);
//                    //$lineItem->setVolume(0);
//                    $lineItem->setIsValid(false);
//                }
//
//                if($item[1] == "")
//                {
//                    $lineItem->setQuantity(1);
//                }
//                else
//                {
//                    $lineItem->setQuantity(str_replace('.', '', $item[1]));
//                    $lineItem->setQuantity(str_replace(',', '', $lineItem->getQuantity()));
//                }
//            }
//            else
//            {
//                // Didn't contain tabs, so user typed it in?  Try to preg match it
//                $itemA = array();
//
//                if(preg_match("/((\d|,)*)\s+(.*)/", $line, $itemA))
//                {
//                    // Found '#,### Type Name'
//                    $type = $types->findOneByTypeName($itemA[3]);
//
//                    if($type != null)
//                    {
//                        $lineItem->setTypeId($type->getTypeId());
//                        $lineItem->setName($type->getTypeName());
//
//                        if(in_array($type->getMarketGroupId(), $groups) & $mode == "false") {
//
//                            $lineItem->setIsValid(false);
//                        }elseif(!in_array($type->getMarketGroupID(), $groups) & $mode == "true") {
//
//                            $lineItem->setIsValid(false);
//                        }
//                    }
//                    else
//                    {
//                        $lineItem->setTypeId(0);
//                        $lineItem->setName($itemA[3]);
//                        //$lineItem->setVolume(0);
//                        $lineItem->setIsValid(false);
//                    }
//
//                    if($itemA[1] == "")
//                    {
//                        $lineItem->setQuantity(1);
//                    }
//                    else
//                    {
//                        $lineItem->setQuantity(str_replace('.', '', $itemA[1]));
//                        $lineItem->setQuantity(str_replace(',', '', $lineItem->getQuantity()));
//                    }
//                }
//                else
//                {
//                    $lineItem->setTypeId(0);
//                    $lineItem->setName('Item not found: '+$line);
//                    //$lineItem->setVolume(0);
//                    $lineItem->setIsValid(false);
//                }
//            }
//
//            $results[] = $lineItem;
//        }
//
//        return $results;
    }
}


class ParserUtils
{

    public static function getRawNumber($number, $replaceMultiplySign = false)
    {
        $rawNumber = str_replace('.', '', $number);
        $rawNumber = str_replace(',', '', $rawNumber);

        if ($replaceMultiplySign)
        {
            $rawNumber = str_replace('x', '', $rawNumber);
            $rawNumber = str_replace('X', '', $rawNumber);
        }

        return $rawNumber;
    }
}

interface IParser
{
    public function parseLine(&$line, &$itemTypes, &$excludedGroups, $whiteListMode);
}

abstract class TabbedParser implements IParser
{
    public function parseTabbedLine(&$line, &$itemTypes, &$excludedGroups, $whiteListMode, $nameIndex, $quantityIndex)
    {
        $lineItem = null;

        // Split by TAB
        $item = explode("\t", $line);

        // Create result entry
        $lineItem = new LineItemEntity();

        // Get type from Eve DB
        $type = $itemTypes->findOneByTypeName($item[$nameIndex]);

        // Set typeId, typeName, and isValid
        if($type != null) //type found in DB
        {
            $lineItem->setTypeId($type->getTypeId());
            $lineItem->setName($type->getTypeName());

            if(in_array($type->getMarketGroupId(), $excludedGroups) & $whiteListMode == "false")
            {
                $lineItem->setIsValid(false);
            }
            elseif(!in_array($type->getMarketGroupID(), $excludedGroups) & $whiteListMode == "true")
            {
                $lineItem->setIsValid(false);
            }
            //$lineItem->setVolume($type->getVolume());
        }
        else //type not found in DB - set defaults
        {
            $lineItem->setTypeId(0);
            $lineItem->setName($item[$nameIndex]);
            $lineItem->setIsValid(false);
            //$lineItem->setVolume(0);
        }

        if($item[$quantityIndex] == "") //no quantity specified - default to 1
        {
            $lineItem->setQuantity(1);
        }
        else
        {
            $lineItem->setQuantity(ParserUtils::getRawNumber($item[$quantityIndex]));
        }


        return $lineItem;
    }
}

class InventoryParser extends TabbedParser
{
    public function parseLine(&$line, &$itemTypes, &$excludedGroups, $whiteListMode)
    {
        return self::parseTabbedLine($line, $itemTypes, $excludedGroups, $whiteListMode, 0, 1); //inventory (and contracts) use 1st position for name and second for quantity
    }
}

class RemoteViewCanParser extends TabbedParser
{

    public function parseLine(&$line, &$itemTypes, &$excludedGroups, $whiteListMode)
    {
        return self::parseTabbedLine($line, $itemTypes, $excludedGroups, $whiteListMode, 0, 2);
    }
}

class UserInputParser extends TabbedParser
{
    // Going to turn this into a tabbed line and then parse it as such
    public function parseLine(&$line, &$itemTypes, &$excludedGroups, $whiteListMode)
    {

        $item = preg_split('/ +/', $line); //split on spaces

        if(is_numeric(ParserUtils::getRawNumber($item[0], true))) //if number is first thing input
        {
            //build tabbed line
            $formattedLine = ParserUtils::getRawNumber($item[0], true)."\t";
            foreach(array_slice($item, 1) as $word)
            {
                $formattedLine .= $word;
            }
        }
        else //assume number is last
        {
            //build tabbed line
            $formattedLine = ParserUtils::getRawNumber($item[count($item) - 1])."\t";
            foreach(array_slice($item, 0, count($item) - 1) as $word)
            {
                $formattedLine .= $word;
            }
        }

        return self::parseTabbedLine($formattedLine, $itemTypes, $excludedGroups, $whiteListMode, 0, 1);
    }
}

class HueristicParser implements IParser
{
    public function parseLine(&$line, &$itemTypes, &$excludedGroups, $whiteListMode)
    {
        $lineItem = null;
        // Create result entry
        $lineItem = new LineItemEntity();

        return $lineItem;
    }
}