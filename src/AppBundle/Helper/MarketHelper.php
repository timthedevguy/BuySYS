<?php
namespace AppBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\Time;
use AppBundle\Entity\CacheEntity;

class MarketHelper
{
    private static function initialize()
    {
    	if (self::$initialized)
    		return;

        //self::$greeting .= ' There!';
    }

    public static function GetMarket1Prices($typeids, $controller)
    {
        $cache = $controller->getDoctrine()->getRepository('AppBundle:CacheEntity', 'default');

        // Find Cached Prices for Unique TypeIDs
        $dirtyTypeIds = array_unique($typeids);
        $em = $controller->getDoctrine()->getManager('default');
        $query = $em->createQuery('SELECT c FROM AppBundle:CacheEntity c WHERE c.typeID IN (:types)')->setParameter('types', $dirtyTypeIds);
        $cached = $query->getResult();
        $priceLookup = array();

        // If a price is good then remove it from the Dirty List
        foreach($cached as $cacheItem)
        {
            if(date_timestamp_get($cacheItem->getLastPull()) > (date_timestamp_get(new \DateTime("now")) - 900))
            {
                unset($dirtyTypeIds[array_search($cacheItem->getTypeID(), $dirtyTypeIds)]);
            }
        }

        // If we have dirty cache pull new data
        if(count($dirtyTypeIds) > 0)
        {
            // Get Settings
            $bb_source_id = $controller->get("helper")->getSetting("buyback_source_id");
            $bb_source_type = $controller->get("helper")->getSetting("buyback_source_type");
            $bb_source_stat = $controller->get("helper")->getSetting("buyback_source_stat");

            // Build EveCentral Query string
            $queryString = "http://api.eve-central.com/api/marketstat/json?typeid=" . implode("&typeid=", $dirtyTypeIds) . "&usesystem=" . $bb_source_id;

            $json = file_get_contents($queryString);
    		$json_array = json_decode($json, true);

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

                $cached[] = $cacheItem;
    		}
        }

        foreach($cached as $cacheItem)
        {
            $priceLookup[$cacheItem->getTypeId()] = $cacheItem->getMarket();
        }

        return $priceLookup;
    }
}
