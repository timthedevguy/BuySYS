<?php
namespace AppBundle\Helper;

class Helper
{
    private static function initialize()
    {
    	if (self::$initialized)
    		return;

        //self::$greeting .= ' There!';
    }
}
