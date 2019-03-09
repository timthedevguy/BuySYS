<?php
namespace AppBundle\Command;

use AppBundle\Helper\Market;
use AppBundle\Controller\AuthorizationController;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
	protected $market;
	protected $em;

	/**
	 * SetupCommand constructor.
	 */
	public function __construct(Market $market, EntityManagerInterface $em)
	{
		$this->market = $market;
		$this->em = $em;
		parent::__construct();
	}

    protected function configure()
    {
        $this
            ->setName('buysys:test')
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
        dump($this->market->getMergedBuybackRuleForType(34));
        dump($this->market->getBuybackPricesForTypes(array('19')));
    }
}