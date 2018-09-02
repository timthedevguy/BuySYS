<?php
namespace AppBundle\Helper;

use AppBundle\Entity\SettingEntity;
use AppBundle\Utilities\SettingsCacheSingleton;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/* Helper
 *
 * Provides basic helper functions used throughout application
 */
class Helper
{
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /*
     * Provides quick method of getting a specific setting from the settings
     * table.
     */
    public function getSetting(string $name)
    {
        //check cache first
        $settingsCache = SettingsCacheSingleton::getInstance();
        $settingValue = $settingsCache->getSetting($name);

        if($settingValue == null)
        {
            // if not in cache, check DB
            $setting = $this->em->getRepository('AppBundle:SettingEntity', 'default')->findOneByName($name);

            if($setting != null)
            {
                //if it was in DB, add to cache and return
                $settingsCache->setSetting($setting->getName(), $setting->getValue());
                $settingValue =  $setting->getValue();
            }
        }

        return $settingValue;
    }

    /*
     * Provides quick method of saving a specific setting to the settings table.
     */
    public function setSetting(string $name, string $value)
    {
        // Grab our Setting
        $setting = $this->em->getRepository('AppBundle:SettingEntity', 'default')->findOneByName($name);

        // Did the setting exist?
        if($setting != null)
        {
            // Yes, set new Value and save
            $setting->setValue($value);
            $this->em->flush();
        }
        else
        {
            // No, create the setting
            $setting = new SettingEntity();
            $setting->setName($name);
            $setting->setValue($value);
            // Inform Entity Manager to manage this Entity
            $this->em->persist($setting);
            $this->em->flush();
        }

        //add to cache
        $settingsCache = SettingsCacheSingleton::getInstance();
        $settingsCache->setSetting($name, $value);
    }
}


