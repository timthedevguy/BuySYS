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
     * This command currently applies DB defaults for alliance market settings to support rules refactor.
     * These defaults are normally set with a clean install via the populateDbCommand, but this command
     * applies only new changes without other defaults overriding current config.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getContainer()->get('helper');
        $helper->setSetting('buyback_default_buyaction_deny', '0');

        $helper->setSetting('buyback_role_member_tax',  $helper->getSetting('buyback_default_tax'));
        $helper->setSetting('buyback_role_ally_tax', '6');
        $helper->setSetting('buyback_role_friend_tax', '8');
        $helper->setSetting('buyback_role_other1_tax', '10');
        $helper->setSetting('buyback_role_other2_tax', '0');
        $helper->setSetting('buyback_role_other3_tax', '0');
    }
}
