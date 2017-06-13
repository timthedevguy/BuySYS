<?php
namespace AppBundle\Helper;

use AppBundle\Entity\CacheEntity;
use AppBundle\Helper\Helper;
use EveBundle\Entity\TypeEntity;
use EveBundle\Entity\TypeMaterialsEntity;
use EveBundle\Entity\MarketGroupsEntity;
use EveBundle\Entity\DgmTypeAttributesEntity;

/**
 * Market Helper provides the needed logic to value an item using all provided buyback rules
 */
class Market
{
    private $doctrine;
    // TODO: Whitelist/Blacklist rules for Buyback Rules
    // TODO: Remove Whitelist/Blacklist seperate system
    // TODO: Add +/- %/ISK values to Buyback Rules
    // -10%, +10%, -10 ISK, +10 ISK
    // /^(?'operand'[\+\-])\s*(?'value'\d*)\s*(?'type'%|ISK)\s*$/gm

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
        // Get Settings
        $bb_source_id = $this->helper->getSetting("buyback_source_id");
        $bb_source_type = $this->helper->getSetting("buyback_source_type");
        $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

        $jsonData = $this->getEveCentralDataForTypes($typeIds, $bb_source_id);

        foreach ($jsonData as $jsonItem)
        {
            // Query DB for matching CacheEntity
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

    /**
     * Checks if Eve Central API is responding by checking
     * the response header
     *
     * @return bool
     */
    public function isEveCentralAlive()
    {
        $header_check = get_headers("https://api.eve-central.com/api/marketstat?typeid=34");

        if(explode(' ', $header_check[0])[1] == '200')
        {
            return true;
        }

        return false;
    }

    /**
     * Get array of refined goods for specified type.  Only returns the
     * Material TypeID and the Quantity after refining penalty.
     *
     * Returned array[TypeID]
     *  ['base']        float Base Materials Quantity
     *  ['adjusted']    float Refined Materials Quantity
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
            $results[$material->getMaterialTypeID()]['base'] = floor($material->getQuantity());
            $results[$material->getMaterialTypeID()]['adjusted'] = floor($material->getQuantity() * ($refineRate / 100));

            // If this is Ore then return 1/100th of the batch size
            /*if($refiningSkill == "Ore")
            {
                $results[$material->getMaterialTypeID()]['adjusted'] = floor($results[$material->getMaterialTypeID()]['adjusted'] / 100);
                $results[$material->getMaterialTypeID()]['base'] = floor($results[$material->getMaterialTypeID()]['base']/100);
            }*/
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
     *  ['issalvage']   boolean Is this item Salvage?
     *  ['refineskill'] string Refining Skill
     *  ['rules']       string List of applied Rules
     *
     * @param $typeId
     * @return array Merged Buyback Rule
     */
    public function getMergedBuybackRuleForType($typeId) {

        // Get System Settings
        $bb_value_minerals = $this->helper->getSetting("buyback_value_minerals");
        $bb_value_salvage = $this->helper->getSetting("buyback_value_salvage");
        $bb_tax = $this->helper->getSetting("buyback_default_tax");
        $bb_deny_all = $this->helper->getSetting("buyback_default_buyaction_deny");

        // Fancy SQL to get Types, GroupID, MarketID and Refining Skill in one go
        $evedataConnection = $this->doctrine->getManager('evedata')->getConnection();
        $sqlQuery = 'SELECT 
                        invTypes.typeID,
                        invTypes.typeName,
                        invTypes.groupID,
                        invTypes.portionSize,
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
        $options['rules'] = "0";
        $options['portionSize'] = $type['portionSize'];

        if($bb_deny_all == 1)
        {
            $options['canbuy'] = false;
        }
        else
        {
            $options['canbuy'] = true;
        }

        // Set Refine Skill and Salvage Flag
        if($type['refineSkill'] != null)
        {
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
        elseif ($type['refineSkill'] == null)
        {
            $options['issalvage'] = true;
            $options['refineskill'] = 'Salvage';
        }

        // Should this item be valued by refined mats?
        if($bb_value_minerals == 1 & $type['refineSkill'] != null |
            $bb_value_salvage == 1 & $type['refineSkill'] == null)
        {
            // Set is Refined
            $options['isrefined'] = true;
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
                case 'canbuy':
                    if($buybackRule->getValue() == 0)
                    {
                        $options['canbuy']  = false;
                    }
                    else
                    {
                        $options['canbuy'] = true;
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
     * Get Market/Adjusted prices from Cache, will update cache as needed.  This is the main method that should
     * be used to get pricing information as it will pull raw data and pull adjusted price by processing all
     * buyback rules.
     *
     * Return array[int TypeID]
     *  ['market']      float Raw Eve Central Market Price
     *  ['adjusted']    float Price after all Buyback Rules
     *
     * @param array $typeIds Array of TypeIds
     * @return array Array of TypeId Keys with market Price values
     */
    public function getBuybackPricesForTypes($typeIds)
    {
        $results = array();

        // Get only Unique TypeIds
        $uniqueTypeIds = array_values(array_unique($typeIds));

        $cacheRepository = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default');

        // Get current cache entries
        $em = $this->doctrine->getManager('default');
        $cachedItems = $cacheRepository->findAllByTypeIds($uniqueTypeIds);

        // If record isn't stale then remove it from the list to pull
        foreach($cachedItems as $cacheItem) {

            // Is the Timestamp later than now + 15 minutes
            if (date_timestamp_get($cacheItem->getLastPull()) > (date_timestamp_get(new \DateTime("now")) - 900)) {

                // Add existing cache entry
                $results[$cacheItem->getTypeId()]['market'] = $cacheItem->getMarket();
                $results[$cacheItem->getTypeId()]['adjusted'] = $cacheItem->getAdjusted();

                // Remove the item so it doesn't get refreshed
                unset($uniqueTypeIds[array_search($cacheItem->getTypeID(), $uniqueTypeIds)]);
            }
        }

        // Get just our TypeIds
        $uniqueTypeIds = array_values($uniqueTypeIds);

        // Update Cache for remaining TypeIds
        if(count($uniqueTypeIds) > 0) {

            // Get Eve Central Settings
            $bb_source_id = $this->helper->getSetting("buyback_source_id");
            $bb_source_type = $this->helper->getSetting("buyback_source_type");
            $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

            // Get updated Stats from Eve Central
            $eveCentralResults = $this->getEveCentralDataForTypes($uniqueTypeIds, $bb_source_id);

            // Parse eve central data
            foreach ($eveCentralResults as $eveCentralResult) {

                // Get the Cache Item
                $typeId = $eveCentralResult[$bb_source_type]["forQuery"]["types"][0];
                $cacheItem = $cacheRepository->findOneByTypeID($typeId);

                if (!$cacheItem) {

                    // If CacheItem is Null then create and populate it
                    $cacheItem = new CacheEntity();
                    $cacheItem->setTypeId($typeId);
                    $em->persist($cacheItem);
                }

                // Set Final stats
                $cacheItem->setMarket($eveCentralResult[$bb_source_type][$bb_source_stat]);
                $cacheItem->setLastPull(new \DateTime("now"));
                $cacheItem->setAdjusted(0.0);

                $mergedRule = $this->getMergedBuybackRuleForType($typeId);
                $adjustedPrice = $cacheItem->getMarket();

                // Check if we can even buy this item
                if ($mergedRule['canbuy'] == true) {

                    // Is the refined flag set?
                    if ($mergedRule['isrefined'] == true) {

                        // Gets the refined materials
                        $materials = $this->getRefinedMaterialsForType($typeId, $mergedRule['refineskill']);
                        // Get the prices
                        $materialPrices = $this->getBuybackPricesForTypes(array_keys($materials));
                        // Is refined so reset the adjusted price
                        $adjustedPrice = 0.0;

                        // Get new price
                        foreach ($materials as $materialTypeId => $quantity) {

                            $adjustedPrice += ($materialPrices[$materialTypeId]['market'] * $quantity['adjusted']);
                        }
                    }

                    // Process the rest of the rules
                    if ($mergedRule['price'] == 0) {

                        // Price isn't set so calculate the taxes
                        $cacheItem->setAdjusted($adjustedPrice * ((100 - $mergedRule['tax']) / 100));

                        // If this is Ore then change to partial value based on Portion Size
                        if ($mergedRule['isrefined'] == true & $mergedRule['refineskill'] == "Ore") {

                            // Adjust Price by portion size
                            $cacheItem->setAdjusted($cacheItem->getAdjusted() / $mergedRule['portionSize']);
                        }
                    } else {

                        $cacheItem->setAdjusted($mergedRule['price']);
                    }

                    $em->flush();

                    $results[$cacheItem->getTypeId()]['market'] = $cacheItem->getMarket();
                    $results[$cacheItem->getTypeId()]['adjusted'] = $cacheItem->getAdjusted();
                } else {

                    $results[$cacheItem->getTypeId()]['market'] = -1;
                    $results[$cacheItem->getTypeId()]['adjusted'] = -1;
                }
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
