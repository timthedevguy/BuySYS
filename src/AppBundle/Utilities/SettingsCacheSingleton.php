<?php
namespace AppBundle\Utilities;

/**
 * Created by PhpStorm.
 * User: a23413h
 * Date: 7/7/2017
 * Time: 11:53 AM
 */
class SettingsCacheSingleton
{

    //SINGLETON PATTERN
    private function __construct(){}
    public static function getInstance()
    {
        static $singleton = null; //documentation of what this does: http://php.net/manual/en/language.variables.scope.php#language.variables.scope.static
        if ($singleton === null)
        {
            $singleton = new SettingsCacheSingleton();
        }

        return $singleton;
    }

    private $settingCache = Array();

    public function getSetting(string $settingName)
    {
        return $this->settingCache[$settingName] ?? null;
    }

    public function setSetting(string $settingName, string $value)
    {
        $this->settingCache[$settingName] = $value;
    }
}