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
    public function getSetting($name)
    {
        // Get Settings repository
        $settings = $this->doctrine->getRepository('AppBundle:SettingEntity', 'default');
        // Grab our setting
        $setting = $settings->findOneByName($name);

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
    public function setSetting($name, $value)
    {
        // Get Settings Repository & the Entity Manager
        $settings = $this->doctrine->getRepository('AppBundle:SettingEntity', 'default');
        $em = $this->doctrine->getManager();
        // Grab our Setting
        $setting = $settings->findOneByName($name);

        // Did the setting exist?
        if($setting != null)
        {
            // Yes, set new Value and save
            $setting->setValue($value);
            $em->flush();
        } else {

            // No, create the setting, set Name & Value
            $setting = new SettingEntity();
            $setting->setName($name);
            $setting->setValue($value);
            // Inform Entity Manager to manage this Entity
            $em->persist($setting);
            // Save to disk
            $em->flush();
        }
    }

    /*
     * Generates all needed settings and sets default values
     */
    public function generateDefaultSettings() {

        $this->setSetting('eveCentralOK', '1');
        $this->setSetting("system_maintenance", "0");
        $this->setSetting("buyback_source_id", "30002510");
        $this->setSetting("buyback_source_type", "buy");
        $this->setSetting("buyback_source_stat", "fivePercent");
        $this->setSetting("buyback_default_tax", "15");
        $this->setSetting("buyback_ore_refine_rate", "70");
        $this->setSetting("buyback_ice_refine_rate", "70");
        $this->setSetting("buyback_salvage_refine_rate", "60");
        $this->setSetting('buyback_value_minerals', '1');
        $this->setSetting('buyback_value_salvage', '1');
        $this->setSetting('buyback_role_member_tax', '5');
        $this->setSetting('buyback_role_ally_tax', '6');
        $this->setSetting('buyback_role_friend_tax', '8');
        $this->setSetting('buyback_role_other1_tax', '10');
        $this->setSetting('buyback_role_other2_tax', '0');
        $this->setSetting('buyback_role_other3_tax', '0');
		
    }
}