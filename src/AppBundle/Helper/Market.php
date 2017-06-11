<?php
namespace AppBundle\Helper;

use AppBundle\Entity\CacheEntity;
use AppBundle\Helper\Helper;
use EveBundle\Entity\TypeEntity;
use EveBundle\Entity\TypeMaterialsEntity;
use EveBundle\Entity\MarketGroupsEntity;
use EveBundle\Entity\DgmTypeAttributesEntity;

/**
 * Market Helper provides all market data lookup functions
 */
class Market
{

    private $doctrine;

    public function __construct($doctrine, Helper $helper)
    {
        $this->doctrine = $doctrine;
        $this->helper = $helper;
    }

    /**
     * Forces Cache to Update for specified Types.  No buyback rules
     * are processed.
     *
     * @param $typeIds Array of TypeIDs
     */
    public function forceCacheUpdateForTypes($typeIds)
    {
        $jsonData = $this->GetEveCentralData($typeIds);

        // Get Settings
        $bb_source_type = $this->helper->getSetting("buyback_source_type");
        $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

        foreach ($jsonData as $jsonItem)
        {
            // Query DB for matching CacheEntity
            $type = $this->doctrine->getRepository('EveBundle:TypeEntity','evedata')->findOneByTypeID($jsonItem[$bb_source_type]["forQuery"]["types"][0]);
            $cacheItem = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default')->findOneByTypeID($jsonItem[$bb_source_type]["forQuery"]["types"][0]);

            if (!$cacheItem)
            {
                // Item is null, lets create it
                $cacheItem = new CacheEntity();
                $cacheItem->setTypeId($jsonItem[$bb_source_type]["forQuery"]["types"][0]);
                $cacheItem->setMarket('0');
                $cacheItem->setLastPull(new \DateTime("now"));

                // Persist Item to DB
                $this->doctrine->getManager('default')->persist($cacheItem);
                $this->doctrine->getManager('default')->flush();
            }

            $cacheItem->setMarket($jsonItem[$bb_source_type][$bb_source_stat]);
            $cacheItem->setLastPull(new \DateTime("now"));

            $this->doctrine->getManager('default')->flush();
        }
    }

    /* GetEveCentralData
     *
     * Get EveCentralD Data for TypeIds from System SourceID
     * A SourceID of Zero will use Buyback Default
     * Returns: Json Array of Market Data
     */
    public function GetEveCentralData($typeIds, $bb_source_id = "0")
    {
        $results = array();

        // If $typeIds is not an array, then make it an array
        if(!is_array($typeIds))
        {
            $tmp = $typeIds;
            $typeIds = array();
            $typeIds[] = $tmp;
        }

        if(count($typeIds) > 0)
        {
            // Get Buyback System setting
            if($bb_source_id == 0)
            {
                $bb_source_id = $this->helper->getSetting("buyback_source_id");
            }

            // Lookup in batches of 20
            for($i = 0; $i < count($typeIds); $i += 20)
            {
                $limit = $i+20;
                if($limit > count($typeIds)) {$limit = count($typeIds);}

                $lookup = array();

                for($j = $i; $j < $limit; $j++)
                {
                    $lookup[] = $typeIds[$j];
                }

                // Build EveCentral Query string
                $queryString = "https://api.eve-central.com/api/marketstat/json?typeid=" . implode("&typeid=", $lookup) . "&usesystem=" . $bb_source_id;

                // Query EveCentral and grab results
                $json = file_get_contents($queryString);
        		$json_array = json_decode($json, true);

                // Combine batches to one result set
                $results = array_merge($results, $json_array);
            }
        }

        // Return results
        return $results;
    }

    /**
     * Used to get array of Market Prices.  Will pull from cache and update
     * cache as needed.  Takes in to account ore composition if Buyback
     * settings dictate it.
     * @param array $typeIds Array of TypeIds
     * @return array Array of Prices
     */
    public function GetMarketPrices($typeIds)
    {
        $results = array();

        // If $typeIds is not an array, then make it an array
        if(!is_array($typeIds))
        {
            $tmp = $typeIds;
            $typeIds = array();
            $typeIds[] = $tmp;
        }

        try
        {
            // Get only Unique TypeIds
            $dirtyTypeIds = array_values(array_unique($typeIds));

            $cache = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default');

            // Get current cache entries
            $em = $this->doctrine->getManager('default');
            $cached = $cache->findAllByTypeIds($typeIds);

            // If record isn't stale then remove it from the list to pull
            foreach($cached as $cacheItem)
            {
                if(date_timestamp_get($cacheItem->getLastPull()) > (date_timestamp_get(new \DateTime("now")) - 900)) {

                    unset($dirtyTypeIds[array_search($cacheItem->getTypeID(), $dirtyTypeIds)]);
                }
            }

            $dirtyTypeIds = array_values($dirtyTypeIds);

            // If we have dirty cache pull new data
            if(count($dirtyTypeIds) > 0)
            {
                // Get Settings
                $bb_source_id = $this->helper->getSetting("buyback_source_id");
                $bb_source_type = $this->helper->getSetting("buyback_source_type");
                $bb_source_stat = $this->helper->getSetting("buyback_source_stat");
                $bb_value_minerals = $this->helper->getSetting("buyback_value_minerals");
                $bb_value_salvage = $this->helper->getSetting("buyback_value_salvage");

                // Get updated Stats from Eve Central
                $json_array = $this->GetEveCentralData($dirtyTypeIds);

                // Parse eve central data
                foreach($json_array as $market_results)
                {
                    $type = $this->doctrine->getRepository('EveBundle:TypeEntity','evedata')->findOneByTypeID($market_results[$bb_source_type]["forQuery"]["types"][0]);
        			$cacheItem = $cache->findOneByTypeID($market_results[$bb_source_type]["forQuery"]["types"][0]);
                    $options = $this->getMergedBuybackRuleForType($type->getTypeID());

                    if(!$cacheItem)
                    {
                        $cacheItem = new CacheEntity();
                        $cacheItem->setTypeId($market_results[$bb_source_type]["forQuery"]["types"][0]);
                        $cacheItem->setMarket('0');
                        $cacheItem->setLastPull(new \DateTime("now"));
                        $em->persist($cacheItem);
                        $em->flush();
                    }

                    // TODO Add Rules Code here

                    // Is refining option turned on?
                    if($options['isrefined'] == true & $options['price'] == '0') {

                        // Get our Composite price
                        $calcValue = $this->GetMarketPriceByComposition($type, $options);

                        if($calcValue >= 0)
                        {
                            // Set Market Value to Value of refined goods
                            $cacheItem->setMarket($calcValue);
                        }
                        elseif($calcValue == -1)
                        {
                            // Set Market Value to Eve Central Data
                            $cacheItem->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                        }
                    } else if($options['price'] != '0') {

                        // Set hardcoded price
                        $cacheItem->setMarket($options['price']);
                    } else {
                        // Set Market Value to Eve Central Data
                        $cacheItem->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                    }

                    $cacheItem->setLastPull(new \DateTime("now"));
                    $em->flush();

                    $results[$cacheItem->getTypeId()] = $cacheItem->getMarket();
        		}
            }

            foreach($cached as $cacheItem)
            {
                $results[$cacheItem->getTypeId()] = $cacheItem->getMarket();
            }

            return $results;

        } catch(Exception $e) {

            return $e;
        }
    }

    public function IsPricedByMinerals($typeId) {

        $bb_value_minerals = $this->helper->getSetting("buyback_value_minerals");
        $bb_value_salvage = $this->helper->getSetting("buyback_value_salvage");

        $refineSkill = $this->doctrine->getRepository('EveBundle:DgmTypeAttributesEntity','evedata')->findBy(
            array('typeID' => $typeId, 'attributeID' => '790')
        );

        // Is refining option turned on?
        if(($bb_value_minerals == 1 & $refineSkill != null) |
            ($bb_value_salvage == 1 & $refineSkill == null))
        {
            return true;
        }

        return false;
    }

    public function GetLiveMarketPrices($typeIds) {

        $results = array();

        // If $typeIds is not an array, then make it an array
        if(!is_array($typeIds))
        {
            $tmp = $typeIds;
            $typeIds = array();
            $typeIds[] = $tmp;
        }

        try
        {
            // Get only Unique TypeIds
            $dirtyTypeIds = array_values(array_unique($typeIds));

            // If we have dirty cache pull new data
            if(count($dirtyTypeIds) > 0) {

                // Get Settings
                $bb_source_type = $this->helper->getSetting("buyback_source_type");
                $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

                // Get updated Stats from Eve Central
                $json_array = $this->GetEveCentralData($dirtyTypeIds);

                // Parse eve central data
                foreach ($json_array as $market_results) {
                    $type = $this->doctrine->getRepository('EveBundle:TypeEntity', 'evedata')->findOneByTypeID($market_results[$bb_source_type]["forQuery"]["types"][0]);

                    $results[$type->getTypeId()] = $market_results[$bb_source_type][$bb_source_stat];
                }
            }

            return $results;

        } catch(Exception $e) {

            return $e;
        }
    }

    /**
     * Checks if Eve Central API is responding
     * @return bool
     */
    public function IsEveCentralAlive()
    {
        $header_check = get_headers("https://api.eve-central.com/api/marketstat?typeid=34");

        if(explode(' ', $header_check[0])[1] == '200')
        {
            return true;
        }

        return false;
    }

    /**
     * Returns cached price, if price doesn't exist we don't care
     * @param array $typeIds Array of TypeIds
     * @return array[typeid] = market price
     */
    public function GetCachedMarketPrices($typeIds)
    {
        $results = array();

        // If $typeIds is not an array, then make it an array
        if(!is_array($typeIds))
        {
            $tmp = $typeIds;
            $typeIds = array();
            $typeIds[] = $tmp;
        }

        $cache = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default');
        $em = $this->doctrine->getManager('default');
        $cacheEntities = $cache->findAllByTypeIds($typeIds);

        foreach($cacheEntities as $cacheItem)
        {
            $results[$cacheItem->getTypeId()] = $cacheItem->getMarket();
        }

        return $results;
    }

    /* GetMarketPriceByComposition
     *
     * Get Market price by ore mineral composition
     */
    public function GetMarketPriceByComposition($type, $options, &$details = array())
    {
        // TODO Maybe add rule code here
        // Set Default to Salvage Rate
        $bb_refine_rate = $this->helper->getSetting("buyback_salvage_refine_rate");
        $details['name'] = $type->getTypeName();
        $details['typeid'] = $type->getTypeId();
        $details['refineskill'] = 'Salvage Refine Rate';

        // Value by Reprocessing Rate
        $typeMaterials = array();
        $typeMaterials = $this->doctrine->getRepository('EveBundle:TypeMaterialsEntity','evedata')->findByTypeID($type->getTypeId());
        $refineSkill = $this->doctrine->getRepository('EveBundle:DgmTypeAttributesEntity','evedata')->findBy(
            array('typeID' => $type->getTypeId(), 'attributeID' => '790')
        );

        if(!$refineSkill == null)
        {
            if($refineSkill == 18025)
            {
                // Ice Reprocessing
                $bb_refine_rate = $this->helper->getSetting("buyback_ice_refine_rate");
                $details['refineskill'] = 'Ice Refine Rate';
            }
            else
            {
                // Ore Reprocessing
                $bb_refine_rate = $this->helper->getSetting("buyback_ore_refine_rate");
                $details['refineskill'] = 'Ore Refine Rate';
            }
        }

        $marketPrice = 0;

        if(count($typeMaterials) > 0)
        {
            $details['materialcount'] = count($typeMaterials);
            $details['types'] = array();

            foreach($typeMaterials as $typeMaterial)
            {
                $refinedAmount = floor($typeMaterial->getQuantity() * ($bb_refine_rate/100));
                $mineralCost = $this->GetMarketPrices($typeMaterial->getMaterialTypeId())[$typeMaterial->getMaterialTypeId()];
                $marketPrice += floor((($mineralCost * $refinedAmount)/$type->getPortionSize()));

                $mDetails = array();
                $mDetails['typeid'] = $typeMaterial->getMaterialTypeId();
                $mDetails['name'] = $this->doctrine->getRepository('EveBundle:TypeEntity','evedata')->findOneByTypeID($typeMaterial->getMaterialTypeId())->getTypeName();
                $mDetails['quantity'] = $typeMaterial->getQuantity();
                $mDetails['refinedquantity'] = $refinedAmount;
                $mDetails['marketprice'] = floor((($mineralCost * $refinedAmount)/$type->getPortionSize()));

                $details['types'][$typeMaterial->getMaterialTypeId()] = $mDetails;
            }

            return $marketPrice;
        }

        return -1;
    }

    public function GetMarketPriceByCompositionByTypeId($typeid) {

        $type = $this->doctrine->getRepository('EveBundle:TypeEntity','evedata')->findOneByTypeID($typeid);
        $details = array();
        return $this->GetMarketPriceByComposition($type,$details);
    }

    public function PopulateLineItems(&$items, $isPublic = false, $isLive = false)
    {
        try
        {
            $typeids = array();
            $publicTax = 0;

            //add guest buyback tax if applicable
            if($isPublic) {
                $publicTax = $this->helper->getSetting("buyback_default_public_tax");
            }

            foreach($items as $lineItem)
            {
                if($lineItem->getIsValid())
                {
                    $typeids[] = $lineItem->getTypeId();
                }
            }

            if($isLive == true) {

                $prices = $this->GetLiveMarketPrices($typeids);
            } else {

                $prices = $this->GetMarketPrices($typeids);
            }

            foreach($items as $lineItemA)
            {
                if($lineItemA->getIsValid())
                {
                    $options = $this->getMergedBuybackRuleForType($lineItemA->getTypeId(), $isPublic);

                    if($isLive != true) {
                        $bb_tax = $options['tax'] + $publicTax;
                    } else {
                        $bb_tax = 0;
                    }

                    $lineItemA->setMarketPrice($prices[$lineItemA->getTypeId()]);
                    $lineItemA->setGrossPrice($lineItemA->getMarketPrice()*$lineItemA->getQuantity());
                    $lineItemA->setNetPrice(($lineItemA->getMarketPrice()*((100-$bb_tax)/100))*$lineItemA->getQuantity());
                    $lineItemA->setTax($bb_tax);
                }
            }
        }
        catch (Exception $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Get array of refined goods for specified type.  Only returns the
     * Material TypeID and the Quantity after refining penalty.
     *
     * Returned array[TypeID => Quantity]
     *
     * @param $typeId
     * @param $refiningSkill
     * @return array
     */
    public function getRefinedMaterialsForType($typeId, $refiningSkill)
    {
        $results = array();

        $refineRate = 0;

        // Get the setting to use for Refining Rate
        switch($refiningSkill)
        {
            case 'Ice':
                $refineRate = $this->helper->getSetting('buyback_ice_refine_rate');
                break;
            case 'Ore':
                $refineRate = $this->helper->getSetting('buyback_ore_refine_rate');
                break;
            case 'Salvage':
                $refineRate = $this->helper->getSetting('buyback_salvage_refine_rate');
                break;
        }

        // Get refined Materials
        $materials = $this->doctrine->getRepository('EveBundle:TypeMaterialsEntity','evedata')->findByTypeID($typeId);

        // Calculate the return
        foreach($materials as $material)
        {
            $results[$material->getMaterialTypeID()] = floor($material->getQuantity() * ($refineRate / 100));
        }

        return $results;
    }

    /**
     * Gets all Buyback Rules and merges them to form the final
     * Buyback Rule used to calculate Adjusted Price
     *
     * Returned Array[]
     *  ['tax']         string Buyback Tax Value
     *  ['price']       float Hardcoded BuyBack Price
     *  ['isrefined']   boolean Refined Flag
     *  ['name']        string Type Name
     *  ['typeid']      int Type ID
     *  ['issalvage']   boolean Is this item Salvage? (Only present if Refined Flag = true)
     *  ['refineskill'] string Refining Skill (Only present if Refined Flag = true)
     *
     * @param $typeId
     * @return array Merged Buyback Rule
     */
    public function getMergedBuybackRuleForType($typeId) {

        // Get System Settings
        $bb_value_minerals = $this->helper->getSetting("buyback_value_minerals");
        $bb_value_salvage = $this->helper->getSetting("buyback_value_salvage");
        $bb_tax = $this->helper->getSetting("buyback_default_tax");

        // Fancy SQL to get Types, GroupID, MarketID and Refining Skill in one go
        $evedataConnection = $this->doctrine->getManager('evedata')->getConnection();
        $sqlQuery = 'SELECT 
                        invTypes.typeID,
                        invTypes.typeName,
                        invTypes.groupID,
                        invTypes.marketGroupID,
                        (SELECT valueInt 
                            FROM 
                              dgmTypeAttributes
                            WHERE
                              dgmTypeAttributes.typeID = invTypes.typeID
                            AND
                              dgmTypeAttributes.attributeID = 790
                        ) as refineSkill
                     FROM
                        invTypes
                     WHERE
                        invTypes.typeID = ?;';

        // Run the SQL Statement
        $type = $evedataConnection->fetchAll($sqlQuery, array($typeId))[0];

        // Get rules for the TypeId, GroupId and MarketGroupId sorted ASC
        $buybackRules = $this->doctrine->getRepository('AppBundle:RuleEntity', 'default')
            ->findAllByTypeAndGroup($type['typeID'], $type['groupID'], $type['marketGroupID']);

        $options = array();
        $options['tax'] = $bb_tax;
        $options['price'] = 0;
        $options['isrefined'] = false;

        // Should this item be valued by refined mats?
        if($bb_value_minerals == 1 & $type['refineSkill'] != null)
        {
            // Set is Refined
            $options['isrefined'] = true;
            $options['issalvage'] = false;

            if($type['refineSkill'] == 18025)
            {
                $options['refineskill'] = 'Ice';
            }
            else
            {
                $options['refineskill'] = 'Ore';
            }
        }
        else if($bb_value_salvage == 1 & $type['refineSkill'] == null)
        {
            // Set is Refined
            $options['isrefined'] = true;
            $options['issalvage'] = true;
            $options['refineskill'] = 'Salvage';
        }

        foreach($buybackRules as $buybackRule)
        {
            switch ($buybackRule->getAttribute())
            {
                case 'tax':
                    $options['tax'] = $buybackRule->getValue();
                    break;
                case 'price':
                    $options['price'] = $buybackRule->getValue();
                    break;
                case 'isrefined':
                    if($buybackRule->getValue() == 0)
                    {
                        $options['isrefined']  = false;
                    }
                    else
                    {
                        $options['isrefined'] = true;
                    }
                    break;
            }

            $options['rules'] = $options['rules'].', '.$buybackRule->getSort();
        }

        $options['name'] = $type['typeName'];
        $options['typeid'] = $type['typeID'];

        return $options;
    }

    /**
     * Takes raw Market Price and applies all rules/refining steps
     *
     * @param array $typeIds TypeId
     * @param $rawPrice Raw Price
     * @return mixed Final Market Price
     */
    public function getAdjustedMarketPriceForTypes($typeIds)
    {
        // Get Cached Prices for Items
        $prices = $this->getRawMarketPricesForTypes($typeIds);

        foreach($prices as $typeId => $price)
        {
            $mergedRule = $this->getMergedBuybackRuleForType($typeId);

            // Is the refined flag set?
            if($mergedRule['isrefined'] == true)
            {
                // Gets the refined materials
                $materials = $this->getRefinedMaterialsForType($typeId, $mergedRule['refineskill']);
                // Get the prices
                $materialPrices = $this->getRawMarketPricesForTypes(array_keys($materials));

                // Set market price to 0
                $prices[$typeId] = 0;

                // Get new price
                foreach($materials as $materialTypeId => $quantity)
                {
                    $prices[$typeId] += $materialPrices[$materialTypeId] * $quantity;
                }
            }

            // Process the rest of the rules
            if($mergedRule['price'] == 0)
            {
                // Price isn't set so calculate the taxes
                $prices[$typeId] = ceil($prices[$typeId] * ((100 - $mergedRule['tax']) / 100));
            }
            else
            {
                $prices[$typeId] = ceil($mergedRule['price']);
            }
        }

        return $prices;
    }

    /**
     * Get Market Prices for provided TypeIds.  Buyback rules
     * are not processed.
     *
     * Return array[int TypeID => string Raw Eve Central Price]
     *
     * @param array $typeIds Array of TypeIds
     * @return array Array of TypeId Keys with market Price values
     */
    public function getRawMarketPricesForTypes($typeIds)
    {
        $results = array();

        // Get only Unique TypeIds
        $uniqueTypeIds = array_values(array_unique($typeIds));

        $cacheRepository = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default');

        // Get current cache entries
        $em = $this->doctrine->getManager('default');
        $cachedItems = $cacheRepository->findAllByTypeIds($uniqueTypeIds);

        // If record isn't stale then remove it from the list to pull
        foreach($cachedItems as $cacheItem)
        {
            // Is the Timestamp later than now + 15 minutes
            if(date_timestamp_get($cacheItem->getLastPull()) > (date_timestamp_get(new \DateTime("now")) - 900))
            {
                // Add existing cache entry
                $results[$cacheItem->getTypeId()] = $cacheItem->getMarket();

                // Remove the item so it doesn't get refreshed
                unset($uniqueTypeIds[array_search($cacheItem->getTypeID(), $uniqueTypeIds)]);
            }
        }

        // Get just our TypeIds
        $uniqueTypeIds = array_values($uniqueTypeIds);

        // Update Cache for remaining TypeIds
        if(count($uniqueTypeIds) > 0)
        {
            // Get Eve Central Settings
            $bb_source_id = $this->helper->getSetting("buyback_source_id");
            $bb_source_type = $this->helper->getSetting("buyback_source_type");
            $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

            // Get updated Stats from Eve Central
            $eveCentralResults = $this->getEveCentralDataForTypes($uniqueTypeIds, $bb_source_id);

            // Parse eve central data
            foreach($eveCentralResults as $eveCentralResult)
            {
                // Get the Cache Item
                $cacheItem = $cacheRepository->findOneByTypeID($eveCentralResult[$bb_source_type]["forQuery"]["types"][0]);

                if(!$cacheItem)
                {
                    // If CacheItem is Null then create and populate it
                    $cacheItem = new CacheEntity();
                    $cacheItem->setTypeId($eveCentralResult[$bb_source_type]["forQuery"]["types"][0]);
                    $em->persist($cacheItem);
                }

                // Set Final stats
                $cacheItem->setMarket($eveCentralResult[$bb_source_type][$bb_source_stat]);
                $cacheItem->setLastPull(new \DateTime("now"));

                $em->flush();

                $results[$cacheItem->getTypeId()] = $cacheItem->getMarket();
            }
        }

        return $results;
    }

    /**
     * Get raw EveCentral Data from Eve Central.
     *
     * @param array $typeIds
     * @param string $fromSystemId
     * @return array Array of Json data from Eve Central
     */
    public function getEveCentralDataForTypes(array $typeIds, string $fromSystemId)
    {
        $results = array();

        if(count($typeIds) > 0)
        {
            // Lookup in batches of 20
            for($i = 0; $i < count($typeIds); $i += 20)
            {
                $limit = $i+20;
                if($limit > count($typeIds)) {$limit = count($typeIds);}

                $lookup = array();

                for($j = $i; $j < $limit; $j++)
                {
                    $lookup[] = $typeIds[$j];
                }

                // Build EveCentral Query string
                $queryString = "https://api.eve-central.com/api/marketstat/json?typeid=" . implode("&typeid=", $lookup) . "&usesystem=" . $fromSystemId;

                // Query EveCentral and grab results
                $json = file_get_contents($queryString);
                $json_array = json_decode($json, true);

                // Combine batches to one result set
                $results = array_merge($results, $json_array);
            }
        }

        // Return results
        return $results;
    }
}
