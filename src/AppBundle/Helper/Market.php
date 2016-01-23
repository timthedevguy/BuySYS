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

        foreach($jsonData as $jsonItem) {

            // Query DB for matching CacheEntity
            $cacheItem = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default')->findOneByTypeID($jsonItem[$bb_source_type]["forQuery"]["types"][0]);

            if(!$cacheItem) {

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

    public function GetEveCentralData($typeIds) {

        if(count($typeIds) > 0)
        {
            // Get Buyback System setting
            $bb_source_id = $this->helper->getSetting("buyback_source_id");

            // Build EveCentral Query string
            $queryString = "http://api.eve-central.com/api/marketstat/json?typeid=" . implode("&typeid=", $typeIds) . "&usesystem=" . $bb_source_id;

            // Query EveCentral and grab results
            $json = file_get_contents($queryString);
    		$json_array = json_decode($json, true);

            // return results in a json array
            return $json_array;
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
}
