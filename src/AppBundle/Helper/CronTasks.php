<?php
namespace AppBundle\Helper;

use AppBundle\Helper\Helper;
use AppBundle\Security\RoleManager;

/**
 * Handles Cache functions
 */
class CronTasks
{
    private $helper;
    private $roleManager;

    public function __construct(Helper $helper, RoleManager $roleManager)
    {
        $this->helper = $helper;
        $this->roleManager = $roleManager;
    }


    public function runTask($taskName)
    {
        if($taskName === 'contacts')
        {
            $this->updateContacts();
        }
    }


    private function updateContacts()
    {
        $apiKey = $this->helper->getSetting('ContactAPIKey');
        $apiCode = $this->helper->getSetting('ContactAPICode');

        if (!empty($apiKey) && !empty($apiCode))
        {
           $this->roleManager->updateContacts($apiKey, $apiCode);
        }
    }
}
