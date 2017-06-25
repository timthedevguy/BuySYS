<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class AutoUpdateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('amsys:cache:update')
            ->setDescription('Update Market Price of a configured set of items.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = new AnonymousToken('service', 'service', ['ROLE_SYSTEM_ADMIN']);
        $this->getContainer()->get('security.token_storage')->setToken($token);

        $output->writeln('');
        $output->writeln('<info>Updating cache...this can take a minute or two!!!</info>');
        $cache = $this->getContainer()->get('cache');
        $cache->UpdateCache();
    }
}
