<?php
namespace AppBundle\Command;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Process\ProcessBuilder;

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
        $guzzle = new Client();

        $tables[] = "dgmTypeAttributes";
        $tables[] = "invMarketGroups";
        $tables[] = "invTypes";
        $tables[] = "invTypeMaterials";
        $tables[] = "invGroups";
        $tables[] = "mapSolarSystems";
        $tables[] = "invItems";

        $helper = $this->getHelper('question');
        $output->writeln('');
        $question = new ConfirmationQuestion('Are you sure you want to import the latest SDE? [<comment>Y</comment>,n]: ', true);
		$response = true;
		
		try {
			
			//$response = $helper->ask($input, $output, $question);
			
		} catch(Exception $e) { /* shh */ }
		
        if (!$response) {

            $output->writeln('<comment>Exiting...</comment>');
            return;
			
        } else {

            $bar = new ProgressBar($output, count($tables));
            $output->writeln('');
            $output->writeln('');
            $output->writeln('<info>Downloading SDE files from fuzzwork.co</info>');
            $bar->start();

            foreach($tables as $table) {

                $url = $fuzz . $table . '.sql.bz2';

                $file_handle = fopen($table.'.sql.bz2','w');

                $result = $guzzle->request('GET', $url, ['sink' => $file_handle]);

                fclose($file_handle);

                if($result->getStatusCode() != 200) {

                    $output->writeln('<error>Unable to download ' . $url . '</error>');
                }

                $bar->advance();
            }

            $bar->finish();

            $output->writeln('');
            $output->writeln('');
            $output->writeln('<info>Extracting SDE...</info>');

            $bar = new ProgressBar($output, count($tables));
            $bar->start();

            foreach($tables as $table) {

                $input_file = bzopen($table.'.sql.bz2', 'r');
                $output_file = fopen($table.'.sql', 'w');

                while ($chunk = bzread($input_file, 4096))
                    fwrite($output_file, $chunk, 4096);

                bzclose($input_file);
                fclose($output_file);

                $bar->advance();
            }

            $bar->finish();

            $phelper = $this->getHelper('process');
            $output->writeln('');
            $output->writeln('');
            $output->writeln('<info>Importing sql files to database...</info>');

            $database = $this->getContainer()->getParameter('database_name');
            $database_user = $this->getContainer()->getParameter('database_user');
            $database_password = $this->getContainer()->getParameter('database_password');
            $database_host = $this->getContainer()->getParameter('database_host');

            $bar = new ProgressBar($output, count($tables));
            $bar->start();

            foreach($tables as $table) {

				$import_command = 'mysql -u ' . $database_user . ' --password=' . $database_password . ' -h ' . $database_host . ' '
					. $database . ' < ' . $table . '.sql';

                $phelper->run($output, $import_command, 'Importing files to DB failed, check your config and SQL Setup.');

                $bar->advance();
            }

            $bar->finish();
			
			/*$prices = json_decode(file_get_contents("https://esi.tech.ccp.is/latest/insurance/prices/?datasource=tranquility&language=en-us"), true);
			if(isset($prices[0]['levels'])) {
				
				$database = $this->getContainer()->getParameter('database_name');
				$database_user = $this->getContainer()->getParameter('database_user');
				$database_password = $this->getContainer()->getParameter('database_password');
				$database_host = $this->getContainer()->getParameter('database_host');

				$output->writeln('');
				$output->writeln('');
				$output->writeln('<info>Importing ship insurance values...</info>');
				
				$bar = new ProgressBar($output, count($prices));
				$bar->start();
				
				$queries = [];
				foreach($prices as $ship) {
					foreach($ship['levels'] as $level) {
						$queries []= "(".$ship['type_id'].", '".$level['name']."', ".$level['cost'].", ".$level["payout"].")";
					}
					$bar->advance();
				}
			
				$sql = 'INSERT INTO '.$database.'.insurancePrices (type_id, insurance_level, insurance_cost, insurance_payout) VALUES '.implode(',', $queries).';';
				$import_command = 'mysql -u ' . $database_user . ' --password=' . $database_password . ' -h ' . $database_host . ' ' . $database . ' --execute="'.$sql.'"';
				$phelper->run($output, $import_command);
								
				$bar->finish();
			}*/

            $output->writeln('');
            $output->writeln('<info>Cleaning up...</info>');

            $bar = new ProgressBar($output, count($tables));
            $bar->start();

            foreach($tables as $table) {

                unlink($table.'.sql');
                unlink($table.'.sql.bz2');

                $bar->advance();
            }

            $bar->finish();

            $output->writeln('');
            $output->writeln('');
            $output->writeln('<info>SDE Import is complete.</info>');
        }
    }
}
