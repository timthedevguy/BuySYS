<?php

namespace AppBundle\Helper;

use AppBundle\Helper\Helper;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handles Cache functions
 */
class Cache extends Helper {

	private $market;

	public function __construct(EntityManagerInterface $em, Market $market)
	{
		$this->em = $em;
		$this->market = $market;
	}

	public function ClearCache()
	{
		// Dump all cache entries
		$cache = $this->em->getRepository('AppBundle:CacheEntity')->findAll();

		foreach ($cache as $item)
		{
			$this->em->remove($item);
		}

		$this->em->flush();
	}

	public function UpdateCache()
	{
		$highSecOres = array('1230', '17470', '17471', '1228', '17463', '17464', '1224', '17459', '17460', '20', '17452', '17453', '46689', '46686', '46687', '46683');
		$compressedHighSecOres = array('28430', '28431', '28432', '46705', '28424', '28425', '28426', '46702', '28427', '28428', '28429', '46703', '28409', '28410', '28411');

		$otherHighSecOres = array('18', '17455', '17456', '1227', '17867', '17868', '46685', '46684');
		$compressedOtherHighSecOres = array('28421', '28422', '28423', '46701', '28415', '28416', '28417', '46700');

		$lowSecOres = array('1226', '17448', '17449', '1231', '17444', '17445', '21', '17440', '17441', '46682', '46681', '46680');
		$compressedLowSecOres = array('28406', '28407', '28408', '46698', '28403', '28404', '28405', '46697', '28400', '28401', '28402', '46696');

		$nullSecOres = array('22', '17425', '17426', '1223', '17428', '17429', '1225', '17432', '17433',
			'1232', '17436', '17437', '1229', '17865', '17866', '11396', '17869', '17870', '19', '17466', '17467', '46679', '46688', '46678', '46677', '46676', '46675');
		$compressedNullSecOres = array('28412', '28413', '28414', '28397', '28398', '28399', '46695', '28418', '28419', '28420',
			'46704', '28367', '28385', '28387', '46691', '28391', '28392', '28393', '46693', '28388', '28389', '28390', '46692', '28394', '28395', '28396', '46694');

		$iceOres = array('16264', '17975', '16265', '17976', '16262', '17978', '16263', '17977', '16267', '16268', '16266', '16269');
		$gasOres = array('25268', '28694', '25279', '28695', '25275', '28696', '30375', '30376', '30377',
			'30370', '30378', '30371', '30372', '30373', '30374', '25273', '28697', '25277',
			'28698', '25276', '28699', '25278', '28700', '25274', '28701');
		$mineralOres = array('34', '35', '36', '37', '38', '39', '40', '11399');
		$p0Ores = array('2268', '2305', '2267', '2288', '2287', '2307', '2272', '2309', '2073', '2310',
			'2270', '2306', '2286', '2311', '2308');
		$p1Ores = array('2393', '2396', '3779', '2401', '2390', '2397', '2392', '3683', '2389', '2399',
			'2395', '2398', '9828', '2400', '3645');
		$p2Ores = array('2329', '3828', '9836', '9832', '44', '3693', '15317', '3725', '3689', '2327',
			'9842', '2463', '2317', '2321', '3695', '9830', '3697', '9838', '2312', '3691', '2319', '9840', '3775', '2328');
		$p3Ores = array('2358', '2345', '2344', '2367', '17392', '2348', '9834', '2366', '2361', '17898',
			'2360', '2354', '2352', '9846', '9848', '2351', '2349', '2346', '12836', '17136', '28974');
		$p4Ores = array('2867', '2868', '2869', '2870', '2871', '2872', '2875', '2876');
		$iceMinerals = array('16272', '16274', '17889', '16273', '17888', '17887', '16275');

		$masterArray = array_merge( //faster as 1 array
			$mineralOres,
			$highSecOres,
			$compressedOtherHighSecOres,
			$otherHighSecOres,
			$compressedOtherHighSecOres,
			$lowSecOres,
			$compressedLowSecOres,
			$nullSecOres,
			$compressedNullSecOres,
			$gasOres,
			$p0Ores,
			$p1Ores,
			$p2Ores,
			$p3Ores,
			$p4Ores,
			$iceMinerals,
			$iceMinerals
		);

		$this->market->getUpdatedCacheItems($masterArray);

		return true;
	}

	public function rebootCache()
	{
		$this->em->getRepository('AppBundle:CacheEntity')->deleteAll();
		$this->UpdateCache();
	}
}
