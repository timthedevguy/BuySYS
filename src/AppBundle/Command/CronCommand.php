<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

class CronCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('buysys:cron:run')
            ->setDescription('Executes a task that is configured to run on a schedule')
            ->addArgument('taskName', InputArgument::REQUIRED, 'The name of the task to execute')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $taskName = $input->getArgument('taskName');

        $cron = $this->getContainer()->get('cron_tasks');
        $cron->runTask($taskName);
    }
}
