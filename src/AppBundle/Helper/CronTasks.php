<?php
namespace AppBundle\Helper;

use AppBundle\Helper\Helper;
use AppBundle\Security\AuthorizationManager;

/**
 * Handles Cache functions
 */
class CronTasks extends Helper
{
    private $roleManager;

    public function __construct($doctrine, AuthorizationManager $roleManager)
    {
        $this->doctrine = $doctrine;
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
        $apiKey = $this->getSetting('ContactAPIKey', 'global');
        $apiCode = $this->getSetting('ContactAPICode', 'global');

        if (!empty($apiKey) && !empty($apiCode))
        {
           $this->roleManager->updateContacts($apiKey, $apiCode);
        }
    }
}
