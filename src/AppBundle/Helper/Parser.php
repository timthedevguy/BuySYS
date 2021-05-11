<?php
namespace AppBundle\Helper;

use AppBundle\Helper\Helper;
use AppBundle\Entity\LineItemEntity;

/**
 * Handles all text parsing functions
 */
class Parser extends Helper
{
    public function GetLineItemsFromPasteData($raw)
    {
        //define Vars
        $results = array();
        $types = $this->em->getRepository('AppBundle:SDE\TypeEntity');

        // Get raw input as array (split on line breaks)
        $rawInputArray = preg_split("/\r\n|\n|\r/", $raw);

        // Check first entry to determine parser type
        $item = explode("\t", $rawInputArray[0]); // Split by TAB
        $oParser = ParserUtils::getParserForItem($raw, $item);
		
        // Loop through results and apply parser
        foreach($rawInputArray as $line)
        {
            $line = trim($line);
            $lineItem = $oParser->parseLine($line, $types);

            $results[] = $lineItem;
        }
		
		/*$fixedResults = [];
		foreach($results as $result)
			if($result && method_exists($result, 'getTypeId'))
				$fixedResults []= $result;*/
		
        // Return results
        return $results;
    }
}


class ParserUtils
{

    public static function getRawNumber($number, $replaceMultiplySign = false)
    {
        $rawNumber = str_replace('.', '', $number);
        $rawNumber = str_replace(',', '', $rawNumber);
        $rawNumber = preg_replace('/\s+/u', '', $rawNumber); // Added for regions that use spaces instead of , or .

        if ($replaceMultiplySign)
        {
            $rawNumber = str_replace('x', '', $rawNumber);
            $rawNumber = str_replace('X', '', $rawNumber);
        }

        return $rawNumber;
    }

    public static function getParserForItem($raw, $item)
    {
        $oParser = new HueristicParser(); //default to hueristic

		if(preg_match('%^\[(.+),(.+)\]%U', $raw))
		{
            $oParser = new EFTInputParser();
		}
		elseif(preg_match('%([1-9][0-9]*(;[0-9]*)?:)+:+%U', $raw))
		{
			// DNA
		}
        elseif(count($item) > 1) // If tabs, likely copy/pasted from game
        {
            // Check format of copy/paste and call appropriate parser
            if(count($item) == 3) //Remote View Can in Station
            {
                $oParser = new RemoteViewCanParser();
            } elseif(count($item) >= 4 or count($item) == 2) { //Inventory or Contract
                $oParser = new InventoryParser();
            }

        }
        else // no tabs, maybe manual user input?
        {
            $oParser = new UserInputParser();
        }

        return $oParser;
    }
}

interface IParser
{
    public function parseLine(&$line, &$itemTypes);
}

abstract class TabbedParser implements IParser
{
    public function parseTabbedLine(&$line, &$itemTypes, $nameIndex, $quantityIndex)
    {
		if(empty($line)) return false;
		
        $lineItem = null;

        // Fix German localization putting in random *
        // Thanks to 4tt1c (https://github.com/timthedevguy/BuySYS/issues/30)
        $replaceStar = null;
        $replaceStar = preg_replace('/\*/', '', $line);

        // Split by TAB
        $item = explode("\t", $replaceStar);

        // Create result entry
        $lineItem = array(); //new LineItemEntity();


        // Make sure array is greater than indexes
        if (count($item) > $nameIndex && count($item) > $quantityIndex)
        {
            // Get type from Eve DB
            $type = $itemTypes->findOneByTypeName($item[$nameIndex]);

            // Set typeId, typeName, and isValid
            if($type != null) //type found in DB
            {
                //$lineItem->setTypeId($type->getTypeId());
                //$lineItem->setName($type->getTypeName());
				$lineItem['typeid'] = $type->getTypeId();
				$lineItem['name'] = $type->getTypeName();
				$lineItem['isValid'] = true;
            }
            else //type not found in DB - set defaults
            {
                //$lineItem->setTypeId(0);
                //$lineItem->setName($item[$nameIndex]);
                //$lineItem->setIsValid(false);
				$lineItem['typeid'] = 0;
				$lineItem['name'] = $item[$nameIndex];
				$lineItem['isValid'] = false;
            }

            if($item[$quantityIndex] == "") //no quantity specified - default to 1
            {
                //$lineItem->setQuantity(1);
				$lineItem['quantity'] = 1;
            }
            else
            {
                //$lineItem->setQuantity(ParserUtils::getRawNumber($item[$quantityIndex]));
				$lineItem['quantity'] = ParserUtils::getRawNumber($item[$quantityIndex]);
            }
        }

        return $lineItem;
    }
}

class InventoryParser extends TabbedParser
{
    public function parseLine(&$line, &$itemTypes)
    {
        return self::parseTabbedLine($line, $itemTypes, 0, 1); //inventory (and contracts) use 1st position for name and second for quantity
    }
}

class RemoteViewCanParser extends TabbedParser
{
    public function parseLine(&$line, &$itemTypes)
    {
        return self::parseTabbedLine($line, $itemTypes, 0, 2);
    }
}

class UserInputParser extends TabbedParser
{
    // Going to turn this into a tabbed line and then parse it as such
    public function parseLine(&$line, &$itemTypes)
    {
        $item = preg_split('/ +/', $line); //split on spaces

        if (is_numeric(ParserUtils::getRawNumber($item[0], true))) // if number is first thing input
        {
            //build tabbed line
            $formattedLine = ParserUtils::getRawNumber($item[0], true)."\t";
            foreach(array_slice($item, 1) as $word)
            {
                $formattedLine .= $word." ";
            }
        }
        elseif (is_numeric(ParserUtils::getRawNumber($item[count($item) - 1]))) // if number is last input
        {
            //build tabbed line
            $formattedLine = ParserUtils::getRawNumber($item[count($item) - 1])."\t";
            foreach(array_slice($item, 0, count($item) - 1) as $word)
            {
                $formattedLine .= $word." ";
            }
        }
        else // no number? default to 1
        {
            //build tabbed line
            $formattedLine = "1\t";
            foreach($item as $word)
            {
                $formattedLine .= $word." ";
            }
        }
        $formattedLine = trim($formattedLine);

        return self::parseTabbedLine($formattedLine, $itemTypes, 1, 0);
    }
}

class EFTInputParser extends TabbedParser
{
    public function parseLine(&$line, &$itemTypes)
    {		
		if(preg_match('%^\[(.+)(,(.+)?)\]$%U', $line, $match)) {
			$formattedLine = "1\t".trim($match[1]);
			return self::parseTabbedLine($formattedLine, $itemTypes, 1, 0);
		}
		
		if(!$line) return false; /* Ignore empty lines */
		if(preg_match('%^\[empty (low|med|high|rig|subsystem) slot\]$%', trim($line))) return false;
		if(strpos($line, ',') !== false) {
			list($module, $charge) = explode(',', $line, 2);
			$module = trim($module);
			$charge = trim($charge);
		} else {
			$module = $line; /* Already trimmed */
			$charge = false;
		}

		if(preg_match('%^(.+)(\s+)x([0-9]+)$%U', $module, $match)) {
			$module = $match[1];
			$qty = (int)$match[3];
			if(!$qty) return false; /* Foobar x0 ?! */
		} else {
			$qty = 1;
		}
		
		$formattedLine = $qty."\t".trim($module);
		return self::parseTabbedLine($formattedLine, $itemTypes, 1, 0);
    }
}

class HueristicParser implements IParser
{
    public function parseLine(&$line, &$itemTypes)
    {
        //TODO: build parser


        // Create result entry
        $lineItem = new LineItemEntity();

        $lineItem->setTypeId(0);
        $lineItem->setName("No Item Found");
        $lineItem->setIsValid(false);
        //$lineItem->setVolume(0);

        return $lineItem;

    }
}