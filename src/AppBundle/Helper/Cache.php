<?php
namespace AppBundle\Helper;

use AppBundle\Helper\Helper;

/**
 * Handles Cache functions
 */
class Cache extends Helper
{
    private $market;

    public function __construct($doctrine, Market $market)
    {
        $this->doctrine = $doctrine;
        $this->market = $market;
    }

    public function ClearCache() {

        // Dump all cache entries
        $em = $this->doctrine->getManager();
        $cache = $this->doctrine->getRepository('AppBundle:CacheEntity', 'default')->findAll();

        foreach($cache as $item) {

            $em->remove($item);
        }

        $em->flush();
    }

    public function UpdateCache() {

        $highSecOres = array('1230', '17470', '17471', '1228', '17463', '17464', '1224', '17459', '17460', '20', '17452', '17453');
        $otherHighSecOres = array('18','17455','17456','1227','17867','17868');
        $lowSecOres = array('1226','17448','17449','1231','17444','17445','21','17440','17441');
        $nullSecOres = array('22','17425','17426','1223','17428','17429','1225','17432','17433',
            '1232','17436','17437','1229','17865','17866','11396','17869','17870'
        ,'19','17466','17467');
        $iceOres = array('16264','17975','16265','17976','16262','17978','16263','17977','16267','16268','16266','16269');
        $gasOres = array('25268','28694','25279','28695','25275','28696','30375','30376','30377',
            '30370','30378','30371','30372','30373','30374','25273','28697','25277',
            '28698','25276','28699','25278','28700','25274','28701');
        $mineralOres = array('34','35','36','37','38','39','40','11399');
        $p0Ores = array('2268','2305','2267','2288','2287','2307','2272','2309','2073','2310',
            '2270','2306','2286','2311','2308');
        $p1Ores = array('2393','2396','3779','2401','2390','2397','2392','3683','2389','2399',
            '2395','2398','9828','2400','3645');
        $p2Ores = array('2329','3828','9836','9832','44','3693','15317','3725','3689','2327',
            '9842','2463','2317','2321','3695','9830','3697','9838','2312','3691','2319','9840','3775','2328');
        $p3Ores = array('2358','2345','2344','2367','17392','2348','9834','2366','2361','17898',
            '2360','2354','2352','9846','9848','2351','2349','2346','12836','17136','28974');
        $p4Ores = array('2867','2868','2869','2870','2871','2872','2875','2876');
        $iceMinerals = array('16272','16274','17889','16273','17888','17887','16275');

        // Get Market Helper
        //$market = $this->getContainer()->get('market');
        //$helper = $this->getContainer()->get('helper');

        $eveCentralOK = $this->market->isEveCentralAlive();
        $this->setSetting("eveCentralOK", $eveCentralOK, 'global');

        if($eveCentralOK == true)
        {
            // Begin Updating the Cache
            $this->market->getBuybackPricesForTypes($mineralOres);
            $this->market->getBuybackPricesForTypes($highSecOres);
            $this->market->getBuybackPricesForTypes($otherHighSecOres);
            $this->market->getBuybackPricesForTypes($lowSecOres);
            $this->market->getBuybackPricesForTypes($nullSecOres);
            $this->market->getBuybackPricesForTypes($gasOres);
            $this->market->getBuybackPricesForTypes($p0Ores);
            $this->market->getBuybackPricesForTypes($p1Ores);
            $this->market->getBuybackPricesForTypes($p2Ores);
            $this->market->getBuybackPricesForTypes($p3Ores);
            $this->market->getBuybackPricesForTypes($p4Ores);
            $this->market->getBuybackPricesForTypes($iceMinerals);
            $this->market->getBuybackPricesForTypes($iceOres);

            return true;
        }

        return false;
    }
}
