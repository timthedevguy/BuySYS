<?php
namespace AppBundle\Helper;

use AppBundle\Entity\SettingEntity;

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
        // get setting
        $setting = $this->doctrine->getRepository('AppBundle:SettingEntity', 'default')
            ->findOneBy(array('name' => $name, 'type' => $type));;

        // Did the setting exist?
        if($setting != null)
        {
            // Yes, return value
            return $setting->getValue();
        }

        // No, return null
        return null;
    }

    /*
     * Provides quick method of saving a specific setting to the settings table.
     */
    public function setSetting(string $name, string $value, string $type = 'global')
    {
        // Grab our Setting
        $setting = $this->doctrine->getRepository('AppBundle:SettingEntity', 'default')
            ->findOneBy(array('name' => $name, 'type' => $type));;

        // Get Entity Manager
        $em = $this->doctrine->getManager();

        // Did the setting exist?
        if($setting != null)
        {
            // Yes, set new Value and save
            $setting->setValue($value);
        } else {

            // No, create the setting
            $setting = new SettingEntity();
            $setting->setName($name);
            $setting->setType($type);
            $setting->setValue($value);
            // Inform Entity Manager to manage this Entity
            $em->persist($setting);
        }

        // Save to disk
        $em->flush();
    }

    /*
     * Generates all needed settings and sets default values
     */
    public function generateDefaultSettings()
    {
        //GLOBAL SETTINGS
        $this->setSetting('eveCentralOK', '1', 'global');
        $this->setSetting("system_maintenance", "0", 'global');

        //BUYBACK SETTINGS
        $this->setSetting("buyback_source_id", "30002510", 'P');
        $this->setSetting("buyback_source_type", "buy", 'P');
        $this->setSetting("buyback_source_stat", "fivePercent", 'P');
        $this->setSetting("buyback_ore_refine_rate", "70", 'P');
        $this->setSetting("buyback_ice_refine_rate", "70", 'P');
        $this->setSetting("buyback_salvage_refine_rate", "60", 'P');
        $this->setSetting('buyback_value_minerals', '1', 'P');
        $this->setSetting('buyback_value_salvage', '1', 'P');
        $this->setSetting('buyback_role_member_tax', '5', 'P');
        $this->setSetting('buyback_role_ally_tax', '6', 'P');
        $this->setSetting('buyback_role_friend_tax', '8', 'P');
        $this->setSetting('buyback_role_other1_tax', '10', 'P');
        $this->setSetting('buyback_role_other2_tax', '0', 'P');
        $this->setSetting('buyback_role_other3_tax', '0', 'P');

        $this->setSetting('buyback_default_buyaction_deny', '0', 'P');

        //SALES SETTINGS

        //SRP SETTINGS
    }
}


		