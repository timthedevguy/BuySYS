<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AutoUpdateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('auto:update')
            ->setDescription('Update Market Price of a configured set of items.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $highSecOres = array('1230', '17470', '17471', '1228', '17463', '17464', '1224', '17459', '17460', '20', '17452', '17453');
        $otherHighSecOres = array('18','17455','17456','1227','17867','17868');
        $lowSecOres = array('1226','17448','17449','1231','17444','17445','21','17440','17441');
        $nullSecOres = array('22','17425','17426','1223','17428','17429','1225','17432','17433',
                            '1232','17436','17437','1229','17865','17866','11396','17869','17870'
                            ,'19','17466','17467');
        $iceOres = array('16264','17975','16265','17976','16262','17978','16263','17977','16267','16268','16266','16269');
        $gasOres = array('25268','28694','25279','28695','25275','28696','30375','30376','30377',
                        '30370','30378','30371','30372','30373','30374','25273','28697','25277',
                        '28698','25276','28699','25278','28700','25274','28701');
        $mineralOres = array('34','35','36','37','38','39','40','11399');

        // Get Market Helper
        $market = $this->getContainer()->get('market');
        $logger = $this->getContainer()->get('logger');
        // Begin Updating the Cache
        $logger->info('Auto Updating High Sec Ores');
        $output->writeln('Auto Updating High Sec Ores');
        $market->UpdateCache($highSecOres);
        $logger->info('Auto Updating Other High Sec Ores');
        $output->writeln('Auto Updating Other High Sec Ores');
        $market->UpdateCache($otherHighSecOres);
        $logger->info('Auto Updating Low Sec Ores');
        $output->writeln('Auto Updating Low Sec Ores');
        $market->UpdateCache($lowSecOres);
        $logger->info('Auto Updating Null Sec Ores');
        $output->writeln('Auto Updating Null Sec Ores');
        $market->UpdateCache($nullSecOres);
        $logger->info('Auto Updating Ice');
        $output->writeln('Auto Updating Ice');
        $market->UpdateCache($iceOres);
        $logger->info('Auto Updating Gas');
        $output->writeln('Auto Updating Gas');
        $market->UpdateCache($gasOres);
        $logger->info('Auto Updating Minerals');
        $output->writeln('Auto Updating Minerals');
        $market->UpdateCache($mineralOres);
    }
}
