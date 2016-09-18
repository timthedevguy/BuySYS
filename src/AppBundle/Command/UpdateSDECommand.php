<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class UpdateSDECommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('amsys:sde:update')
            ->setDescription('Update evedata with latest SDE dump')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fuzz = "https://www.fuzzwork.co.uk/dump/latest/";
        $tables = array();
        $tables[] = "dgmTypeAttributes.sql.bz2";
        $tables[] = "invMarketGroups.sql.bz2";
        $tables[] = "invTypes.sql.bz2";
        $tables[] = "invTypeMaterials.sql.bz2";

        $helper = $this->getHelper('question');
        $output->writeln('');
        $question = new ConfirmationQuestion('Are you sure you want to import the latest SDE? [<comment>Y</comment>,n]: ', true);

        if (!$helper->ask($input, $output, $question)) {

            $output->writeln('<comment>Exiting...</comment>');
            return;
        } else {

            $output->writeln('<comment>YAY!!</comment>');
        }
    }
}
