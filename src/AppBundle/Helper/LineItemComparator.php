<?php
/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 12/21/16
 * Time: 12:07 PM
 */

namespace AppBundle\Helper;

/*
 * Compares two LineItem Arrays to see if they have the same items/qnty
 */
use AppBundle\Model\LineItemComparisonModel;

class LineItemComparator
{

    private $market;

    public function __construct($market)
    {
        $this->market = $market;
    }

    public function CompareLineItems(&$lineItemsOriginal, &$lineItemsNew)
    {
        $lineItemComparison = new LineItemComparisonModel();

        // Get counts for item IDs in line items - these are what we will compare
        $originalCounts = $this->getItemIdCounts($lineItemsOriginal);
        $newCounts = $this->getItemIdCounts($lineItemsNew);

        // Start comparison
        $originalExcessCounts = $this->findExcess($originalCounts, $newCounts);
        $newExcessCounts = $this->findExcess($newCounts, $originalCounts);

        $missingLineItems = $this->getLineItemsByIdCounts($originalExcessCounts, $lineItemsOriginal);
        $excessLineItems = $this->getLineItemsByIdCounts($newExcessCounts, $lineItemsNew);

        // Get Market prices for excess items
        if (count($excessLineItems) > 0)
        {
            $this->market->PopulateLineItems($excessLineItems);
        }

        // Build Response
        $lineItemComparison->setIsExactMatch(count($originalExcessCounts) == 0 && count($newExcessCounts) == 0);
        $lineItemComparison->setTotalExcess($this->getTotalGross($excessLineItems));
        $lineItemComparison->setTotalMissing($this->getTotalGross($missingLineItems));
        $lineItemComparison->setExcessLineItems($excessLineItems);
        $lineItemComparison->setMissingLineItems($missingLineItems);

        return $lineItemComparison;
    }


    private function getItemIdCounts(&$lineItems)
    {
        $itemIdCounts = Array();

        foreach($lineItems as $lineItem)
        {
            $id = $lineItem->getTypeId();
            $count = intval($lineItem->getQuantity());

            if (array_key_exists($id, $itemIdCounts)) {
                $itemIdCounts[$id] = $itemIdCounts[$id] + $count;
            }
            else
            {
                $itemIdCounts[$id] = $count;
            }
        }

        return $itemIdCounts;
    }

    private function findExcess(&$firstArray, &$secondArray)
    {
        $excessItems = Array();

        foreach($firstArray as $itemKey => $itemVal)
        {
            if (!array_key_exists($itemKey, $secondArray)) // if the key from first array isn't in second array
            {
                $excessItems[$itemKey] = $itemVal;
            }
            else // if key is in array, check values
            {
                $excessQuantity = $itemVal - $secondArray[$itemKey];
                if ($excessQuantity > 0)
                {
                    $excessItems[$itemKey] = $excessQuantity;
                }
            }
        }

        return $excessItems;
    }

    private function getLineItemsByIdCounts($idCounts, &$lineItems)
    {
        $matchingLineItems = Array();

        foreach($lineItems as $item)
        {
            $itemId = $item->getTypeId();
            if (array_key_exists($itemId, $idCounts))
            {
                $item->setQuantity($idCounts[$itemId]);
                $matchingLineItems[] = $item;
            }
        }

        return $matchingLineItems;
    }

    private function getTotalGross(&$lineItems)
    {
        $totalGross = 0;

        foreach ($lineItems as $item)
        {
            $totalGross += $item->getGrossPrice();
        }

        return $totalGross;
    }
}