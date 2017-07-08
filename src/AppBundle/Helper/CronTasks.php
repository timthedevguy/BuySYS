<?php
namespace AppBundle\Helper;

use AppBundle\Helper\Helper;
use AppBundle\Security\AuthorizationManager;

/**
 * Handles Cache functions
 */
class CronTasks extends Helper
{
    private $authManager;

    public function __construct($doctrine, AuthorizationManager $authManager)
    {
        $this->doctrine = $doctrine;
        $this->authManager = $authManager;
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
           $this->authManager->updateContacts($apiKey, $apiCode);
        }
    }
}
