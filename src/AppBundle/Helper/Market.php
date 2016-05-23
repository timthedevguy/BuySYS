<?php
namespace AppBundle\Helper;

use AppBundle\Entity\CacheEntity;
use AppBundle\Helper\Helper;

/* Helper
 *
 * Provides basic helper functions used throughout application
 */
class Market {

    private $doctrine;

    public function __construct($doctrine, Helper $helper)
    {
        $this->doctrine = $doctrine;
        $this->helper = $helper;
    }

    public function UpdateCache($typeIds) {

        $jsonData = $this->GetEveCentralData($typeIds);

        // Get Settings
        $bb_source_type = $this->helper->getSetting("buyback_source_type");
        $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

        foreach($jsonData as $jsonItem)
        {
            // Query DB for matching CacheEntity
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

            // Item exists now populate it
            $cacheItem->setMarket($jsonItem[$bb_source_type][$bb_source_stat]);
            $cacheItem->setLastPull(new \DateTime("now"));

            $this->doctrine->getManager('default')->flush();
        }
    }

    public function GetEveCentralData($typeIds, $bb_source_id = "0") {

        if(!is_array($typeIds)) {

            $tmp = $typeIds;
            $typeIds = array();
            $typeIds[] = $tmp;
        }

        if(count($typeIds) > 0)
        {
            // Get Buyback System setting
            if($bb_source_id == 0) {
                $bb_source_id = $this->helper->getSetting("buyback_source_id");
            }

            // Build EveCentral Query string
            $queryString = "http://api.eve-central.com/api/marketstat/json?typeid=" . implode("&typeid=", $typeIds) . "&usesystem=" . $bb_source_id;

            // Query EveCentral and grab results
            $json = file_get_contents($queryString);
    		$json_array = json_decode($json, true);

            // return results in a json array
            return $json_array;
        }
    }

    public function GetMarketPrices($typeIds) {

        try {
            // Get only Unique TypeIds
            $dirtyTypeIds = array_unique($typeIds);

            $cache = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default');

            // Get current cache entries
            $em = $this->doctrine->getManager('default');
            //$query = $em->createQuery('SELECT c FROM AppBundle:CacheEntity c WHERE c.typeID IN (:types)')->setParameter('types', $dirtyTypeIds);
            //$cached = $query->getResult();
            $cached = $cache->findAllByTypeIds($typeIds);
            $results = array();

            // If record isn't stale then remove it from the list to pull
            foreach($cached as $cacheItem) {

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

                // Do this in batches of 20
                for($i = 0;$i<=count($dirtyTypeIds);$i+=20) {

                	$limit = $i+20;
                	if($limit > count($dirtyTypeIds)) {$limit = count($dirtyTypeIds);}

                    $lookup = array();

                	for($j=$i;$j<$limit;$j++) {

                		$lookup[] = $dirtyTypeIds[$j];
                	}

                    // Get 20 results from EveCentral
                    $json_array = $this->GetEveCentralData($lookup);

                    // Parse eve central data
                    foreach($json_array as $market_results)
                    {
            			$cacheItem = $cache->findOneByTypeID($market_results[$bb_source_type]["forQuery"]["types"][0]);

                        if(!$cacheItem)
                        {
                            $cacheItem = new CacheEntity();
                            $cacheItem->setTypeId($market_results[$bb_source_type]["forQuery"]["types"][0]);
                            $cacheItem->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                            $cacheItem->setLastPull(new \DateTime("now"));
                            $em->persist($cacheItem);
                            $em->flush();
                        } else {

                            $cacheItem->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                            $cacheItem->setLastPull(new \DateTime("now"));
                            $em->flush();
                        }

                        //$cached[] = $cacheItem;
                        $results[$cacheItem->getTypeId()] = $cacheItem->getMarket();
            		}
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

    public function GetMarketPrice($typeId) {

        $cache = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default');
        $em = $this->doctrine->getManager('default');
        $cacheEntity = $cache->findOneByTypeID($typeId);

        $isStale = false;

        // Is Empty?
        if(!$cacheEntity) {

            $isStale = true;

            // Lets Create the item
            $cacheEntity = new CacheEntity();
            $cacheEntity->setTypeId($typeId);
            $cacheEntity->setMarket('0');
            $cacheEntity->setLastPull(new \DateTime("now"));
            // Save the item
            $em->persist($cacheEntity);
            $em->flush();

        } else {

            // Is Stale?
            if(date_timestamp_get($cacheEntity->getLastPull()) < (date_timestamp_get(new \DateTime("now")) - 1200)) {

                $isStale = true;
            }
        }

        if($isStale) {

            // Pull New Information
            // Get Settings
            $bb_source_id = $this->helper->getSetting("buyback_source_id");
            $bb_source_type = $this->helper->getSetting("buyback_source_type");
            $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

            // Build EveCentral Query string
            $queryString = "http://api.eve-central.com/api/marketstat/json?typeid=" . $typeId . "&usesystem=" . $bb_source_id;

            $json = file_get_contents($queryString);
    		$json_array = json_decode($json, true);

    		foreach($json_array as $market_results)
            {
                $cacheEntity->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                $cacheEntity->setLastPull(new \DateTime("now"));
                $em->flush();
    		}
        }

        return $cacheEntity->getMarket();
    }

    public function IsEveCentralAlive()
    {
        $header_check = get_headers("http://api.eve-central.com/api/marketstat?typeid=34");

        if(explode(' ', $header_check[0])[1] == '200')
        {
            return true;
        }

        return false;
    }

    public function GetCacheMarketPrice($typeId) {

        $cache = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default');
        $em = $this->doctrine->getManager('default');
        $cacheEntity = $cache->findOneByTypeID($typeId);

        $isStale = false;

        // Is Empty?
        if(!$cacheEntity) {

            $isStale = true;

            // Lets Create the item
            $cacheEntity = new CacheEntity();
            $cacheEntity->setTypeId($typeId);
            $cacheEntity->setMarket('0');
            $cacheEntity->setLastPull(new \DateTime("now"));
            // Save the item
            $em->persist($cacheEntity);
            $em->flush();

        } else {

            // Is Stale?
            if(date_timestamp_get($cacheEntity->getLastPull()) < (date_timestamp_get(new \DateTime("now")) - 1200)) {

                $isStale = true;
            }
        }

        /*if($isStale) {

            // Pull New Information
            // Get Settings
            $bb_source_id = $this->helper->getSetting("buyback_source_id");
            $bb_source_type = $this->helper->getSetting("buyback_source_type");
            $bb_source_stat = $this->helper->getSetting("buyback_source_stat");

            // Build EveCentral Query string
            $queryString = "http://api.eve-central.com/api/marketstat/json?typeid=" . $typeId . "&usesystem=" . $bb_source_id;

            $json = file_get_contents($queryString);
    		$json_array = json_decode($json, true);

    		foreach($json_array as $market_results)
            {
                $cacheEntity->setMarket($market_results[$bb_source_type][$bb_source_stat]);
                $cacheEntity->setLastPull(new \DateTime("now"));
                $em->flush();
    		}
        }*/

        return $cacheEntity->getMarket();
    }
}
