<?php
namespace AppBundle\Command;

use AppBundle\Helper\Market;
use AppBundle\Controller\AuthorizationController;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('amsys:test')
            ->setDescription('Testing Command')
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
        /** @var Market $market */
        $market = $this->getContainer()->get('market');
        $typeids = array('17843', '621', '17715');

        $results = $market->GetMarketPricesForTypes($typeids);
        dump($results);
    }
}