<?php
namespace AppBundle\Helper;

use AppBundle\Entity\CacheEntity;
use AppBundle\Helper\Helper;
use EveBundle\Entity\TypeEntity;
use EveBundle\Entity\TypeMaterialsEntity;
use EveBundle\Entity\MarketGroupsEntity;

/**
 * Market Helper provides all market data lookup functions
 */
class Market {

    private $doctrine;

    public function __construct($doctrine, Helper $helper)
    {
        $this->doctrine = $doctrine;
        $this->helper = $helper;
    }

    /**
     * Forces a cache update for the specified Types
     * @param Array $typeIds Array of Type Ids
     */
    public function UpdateCache($typeIds) {

        $jsonData = $this->GetEveCentralData($typeIds);

        // Get Settings
        $bb_source_type = $this->helper->getSetting("buyback_source_type");
        $bb_source_stat = $this->helper->getSetting("buyback_source_stat");
        $bb_value_minerals = $this->helper->getSetting("buyback_value_minerals");

        foreach($jsonData as $jsonItem)
        {
            // Query DB for matching CacheEntity
            $type = $this->doctrine->getRepository('EveBundle:TypeEntity','evedata')->findOneByTypeID($jsonItem[$bb_source_type]["forQuery"]["types"][0]);
            $cacheItem = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default')->findOneByTypeID($jsonItem[$bb_source_type]["forQuery"]["types"][0]);

            if(!$cacheItem)
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

            if($bb_value_minerals == 1)
            {
                // See if this is Ore
                if($type->getMarketGroupId() >= 0)
                {
                    // Get Market Group
                    $marketGroup = $this->doctrine->getRepository('EveBundle:MarketGroupsEntity','evedata')->findOneByMarketGroupID($type->getMarketGroupId());

                    // Ore Market Category
                    if($marketGroup->getParentGroupID() == 54 | $marketGroup->getMarketGroupID() == 1031)
                    {
                        $cacheItem->setMarket($this->GetMarketPriceByComposition($type));
                    }
                    else
                    {
                        // Item exists now populate it
                        $cacheItem->setMarket($jsonItem[$bb_source_type][$bb_source_stat]);
                    }
                }
                else
                {
                    // Item exists now populate it
                    $cacheItem->setMarket($jsonItem[$bb_source_type][$bb_source_stat]);
                }
            }
            else
            {
                // Item exists now populate it
                $cacheItem->setMarket($jsonItem[$bb_source_type][$bb_source_stat]);
            }

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
            for($i = 0; $i <= count($typeIds); $i += 20)
            {
                $limit = $i+20;
                if($limit > count($typeIds)) {$limit = count($typeIds);}

                $lookup = array();

                for($j = $i; $j < $limit; $j++)
                {
                    $lookup[] = $typeIds[$j];
                }

                // Build EveCentral Query string
                $queryString = "http://api.eve-central.com/api/marketstat/json?typeid=" . implode("&typeid=", $lookup) . "&usesystem=" . $bb_source_id;

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
            $dirtyTypeIds = array_unique($typeIds);

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

                // Get updated Stats from Eve Central
                $json_array = $this->GetEveCentralData($dirtyTypeIds);

                // Parse eve central data
                foreach($json_array as $market_results)
                {
                    $type = $this->doctrine->getRepository('EveBundle:TypeEntity','evedata')->findOneByTypeID($market_results[$bb_source_type]["forQuery"]["types"][0]);
        			$cacheItem = $cache->findOneByTypeID($market_results[$bb_source_type]["forQuery"]["types"][0]);

                    if(!$cacheItem)
                    {
                        $cacheItem = new CacheEntity();
                        $cacheItem->setTypeId($market_results[$bb_source_type]["forQuery"]["types"][0]);
                        $cacheItem->setMarket('0');
                        $cacheItem->setLastPull(new \DateTime("now"));
                        $em->persist($cacheItem);
                        $em->flush();
                    }

                    if($bb_value_minerals == 1)
                    {
                        // See if this is Ore
                        if($type->getMarketGroupId() >= 0)
                        {
                            // Get Market Group
                            $marketGroup = $this->doctrine->getRepository('EveBundle:MarketGroupsEntity','evedata')->findOneByMarketGroupID($type->getMarketGroupId());

                            // Ore Market Category
                            if($marketGroup->getParentGroupID() == 54 | $marketGroup->getMarketGroupID() == 1031)
                            {
                                $cacheItem->setMarket($this->GetMarketPriceByComposition($type));
                            }
                            else
                            {
                                // Item exists now populate it
                                $cacheItem->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                            }
                        }
                        else
                        {
                            // Item exists now populate it
                            $cacheItem->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                        }
                    }
                    else
                    {
                        // Item exists now populate it
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

    /**
     * Checks if Eve Central API is responding
     * @return bool
     */
    public function IsEveCentralAlive()
    {
        $header_check = get_headers("http://api.eve-central.com/api/marketstat?typeid=34");

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
    public function GetMarketPriceByComposition($type)
    {
        $bb_refine_rate = $this->helper->getSetting("buyback_refine_rate");

        // Value by Reprocessing Rate
        $typeMaterials = array();
        $typeMaterials = $this->doctrine->getRepository('EveBundle:TypeMaterialsEntity','evedata')->findByTypeID($type->getTypeId());
        $marketPrice = 0;

        foreach($typeMaterials as $typeMaterial)
        {
            $refinedAmount = floor($typeMaterial->getQuantity() * ($bb_refine_rate/100));
            $mineralCost = $this->doctrine->getRepository('AppBundle:CacheEntity','default')->findOneByTypeID($typeMaterial->getMaterialTypeId())->getMarket();

            if(substr($type->getTypeName(),0,10) != "Compressed")
            {
                $marketPrice += floor((($mineralCost * $refinedAmount)/100));
            }
            else
            {
                $marketPrice += floor(($mineralCost * $refinedAmount));
            }
        }

        return $marketPrice;
    }

    public function PopulateLineItems(&$items, $isPublic = false)
    {
        try
        {
            $typids = array();
            $bb_tax = 100;

            if($isPublic)
            {
                $bb_tax = $this->helper->getSetting("buyback_default_public_tax");
            }
            else
            {
                $bb_tax = $this->helper->getSetting("buyback_default_tax");
            }
            dump($items);
            foreach($items as $lineItem)
            {
                if($lineItem->getIsValid())
                {
                    $typeids[] = $lineItem->getTypeId();
                }
            }
            dump($typeids);
            $prices = $this->GetMarketPrices($typeids);
            dump($prices);
            foreach($items as $lineItemA)
            {
                if($lineItemA->getIsValid())
                {
                    $lineItemA->setMarketPrice($prices[$lineItemA->getTypeId()]);
                    $lineItemA->setGrossPrice($lineItemA->getMarketPrice()*$lineItemA->getQuantity());
                    $lineItemA->setNetPrice(($lineItemA->getMarketPrice()*((100-$bb_tax)/100))*$lineItemA->getQuantity());
                }
            }
        }
        catch (Exception $e)
        {
            return false;
        }
        dump($items);
        return true;
    }
}
