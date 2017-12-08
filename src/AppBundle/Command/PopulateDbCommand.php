<?php
namespace AppBundle\Command;

use AppBundle\Controller\AuthorizationController;
use AppBundle\Entity\AuthorizationEntity;
use AppBundle\Security\AuthorizationManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateDbCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('amsys:settings:populate')
            ->setDescription('Populate Database with default settings')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->populateSettingsDefaults();
        $this->populateRolesDefaults();
    }


    //POPULATE FUNCTIONS
    private function populateSettingsDefaults()
    {
        $helper = $this->getContainer()->get('helper');

        //GLOBAL SETTINGS
        $helper->setSetting('eveCentralOK', '1', 'global');
        $helper->setSetting("system_maintenance", "0", 'global');

        //COMMON SETTINGS
        foreach(array('P', 'S', 'SRP') as $settingType)
        {
            $helper->setSetting('default_buyaction_deny', '0', $settingType);
            $helper->setSetting("source_id", "60003760", $settingType);
            $helper->setSetting("source_type", "buy", $settingType);
            $helper->setSetting("source_stat", "percentile", $settingType);
            $helper->setSetting('role_member_tax', '5', $settingType);
            $helper->setSetting('role_ally_tax', '6', $settingType);
            $helper->setSetting('role_friend_tax', '8', $settingType);
            $helper->setSetting('role_other1_tax', '10', $settingType);
            $helper->setSetting('role_other2_tax', '0', $settingType);
            $helper->setSetting('role_other3_tax', '0', $settingType);
        }

        //BUYBACK SETTINGS
        $helper->setSetting('value_minerals', '1', 'P');
        $helper->setSetting('value_salvage', '1', 'P');
        $helper->setSetting("ore_refine_rate", "70", 'P');
        $helper->setSetting("ice_refine_rate", "70", 'P');
        $helper->setSetting("moon_refine_rate", "70", 'P');
        $helper->setSetting("salvage_refine_rate", "60", 'P');

        //SALES SETTINGS

        //SRP SETTINGS

    }

    private function populateRolesDefaults()
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        $defaultEntry = (new AuthorizationEntity())
            ->setEveId(-999)
            ->setName("Default Access (Everyone Not Configured)")
            ->setType("")
            ->setRole(AuthorizationManager::getDefaultRole());

        $em->persist($defaultEntry);
        $em->flush();

        foreach(AuthorizationController::getContactLevels() as $id => $level)
        {
            $entry = (new AuthorizationEntity())
                ->setEveId($id)
                ->setName($level)
                ->setType("contact")
                ->setRole(AuthorizationManager::getDefaultRole());

            $em->persist($entry);
            $em->flush();
        }
    }

}
