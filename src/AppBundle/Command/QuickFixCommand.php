<?php
namespace AppBundle\Command;

use AppBundle\Controller\AuthorizationController;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QuickFixCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('amsys:quickfix:fix')
            ->setDescription('Temporary command for fixing stuff related to an update')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * This command currently applies DB defaults for authorization to support 'roles_update' changes.
     * These defaults are normally set with a clean install via the populateDbCommand, but this command
     * applies only new changes without other defaults overriding current config.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roleManager = $this->getContainer()->get('role_manager');
        $roleManager->setDefaultRoles();
    }
}
