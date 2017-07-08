<?php
namespace AppBundle\Helper;

use AppBundle\Entity\SettingEntity;
use AppBundle\Utilities\SettingsCacheSingleton;

/* Helper
 *
 * Provides basic helper functions used throughout application
 */
class Helper
{
    protected $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /*
     * Provides quick method of getting a specific setting from the settings
     * table.
     */
    public function getSetting(string $name, string $type = 'global')
    {
        //check cache first
        $settingsCache = SettingsCacheSingleton::getInstance();
        $settingValue = $settingsCache->getSetting($name, $type);

        if($settingValue == null)
        {
            // if not in cache, check DB
            $setting = $this->doctrine->getRepository('AppBundle:SettingEntity', 'default')
                ->findOneBy(array('name' => $name, 'type' => $type));;

            if($setting != null)
            {
                //if it was in DB, add to cache and return
                $settingsCache->setSetting($setting->getName(), $setting->getType(), $setting->getValue());
                $settingValue =  $setting->getValue();
            }
        }

        return $settingValue;
    }

    /*
     * Provides quick method of saving a specific setting to the settings table.
     */
    public function setSetting(string $name, string $value, string $type = 'global')
    {
        // Grab our Setting
        $setting = $this->doctrine->getRepository('AppBundle:SettingEntity', 'default')
            ->findOneBy(array('name' => $name, 'type' => $type));

        // Get Entity Manager
        $em = $this->doctrine->getManager();

        // Did the setting exist?
        if($setting != null)
        {
            // Yes, set new Value and save
            $setting->setValue($value);
            $em->flush();
        }
        else
        {
            // No, create the setting
            $setting = new SettingEntity();
            $setting->setName($name);
            $setting->setType($type);
            $setting->setValue($value);
            // Inform Entity Manager to manage this Entity
            $em->persist($setting);
            $em->flush();
        }

        //add to cache
        $settingsCache = \SettingsCacheSingleton::getInstance();
        $settingsCache->setSetting($name, $type, $value);
    }
}


