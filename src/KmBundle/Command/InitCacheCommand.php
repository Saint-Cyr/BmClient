<?php
namespace KmBundle\Command;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;

class InitCacheCommand extends ContainerAwareCommand
{
    protected function configure() {
        //set the name of the command
        $this->setName('beezymanager:init:cache');
        //set the description when type bin/console
        $this->setDescription('Initialize the client cache');
        //set the helper message
        $this->setHelp(' (miniserver for a branch) cache at 100%');
        //Add the BranchId (synchroneId) argument
        //$this->addArgument('branchId', InputArgument::REQUIRED, 'The Branch #ID generated by the Server');
        $this->addArgument('branchId', InputArgument::REQUIRED, 'The Branch #ID');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        
        //Synchronize the stransactions to the server
        $synchronizerHandler = $this->getContainer()->get('km.synchronizer_handler');
        $client = $this->getContainer()->get('guzzle.client.api_crm');
        $kernel = $this->getContainer()->get('kernel');
        $application = new Application($kernel);
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        
        
        $sts = $em->getRepository('TransactionBundle:STransaction')->findAll();
        
        if(count($sts) == 0){
            $var = true;
        }else{
            $var = false;
        }
        
        
        if(true){
            $output->writeln('', '');
            // outputs multiple lines to the console (adding "\n" at the end of each line)
            $output->writeln([
                '================ <options=bold;fg=magenta>BeezyManager®</></> is a trademark of Saint-Cyr MAPOUKA. All rights reserved. ==============',
                '',
                'Client Cache Initialization...',
            ]);
            
            
            
            $command = new DropDatabaseDoctrineCommand();
            
            // outputs a message followed by a "\n"
            $output->writeln('<fg=yellow><options=bold>Notice</>: Initialization succeed only when the STransactions are fully synchronized or when setting up new Branch</>');
            
            $branchId = $input->getArgument('branchId');
            $output->writeln('Branch #ID: '.$branchId);
            
            $rows = 100;
            $progressBar = new ProgressBar($output, $rows);
            $progressBar->setBarCharacter('<fg=magenta>></>');
            $progressBar->setProgressCharacter("\xF0\x9F\x8D\xBA");

            $table = new Table($output);
            
            for ($i = 0; $i<$rows; $i++) {
                
                $table->addRow([
                    sprintf('Row <info># %s</info>', $i),
                    rand(0, 1000)
                ]);
                
                usleep(30000);

                $progressBar->advance();
                
                if($i == 3){
                    //Step 1: drop the DB.
                    $application->add($command);
                    //Prepare the command input
                    $input = new ArrayInput(array('command' => 'doctrine:database:drop',
                                                  '--force' => true));
                    //Run the command
                    $command->run($input, new NullOutput);
                    //We have to close the connexion in order to do not get "no database connexion error"
                    $connection = $application->getKernel()->getContainer()->get('doctrine')->getConnection();
                    if ($connection->isConnected()) {
                        $connection->close();
                    }
                }
                
                if($i == 29){
                    //Step 2: create new DB
                    $command = new CreateDatabaseDoctrineCommand();
                    $application->add($command);
                    $input = new ArrayInput(array('command' => 'doctrine:database:create'));
                    $command->run($input, new NullOutput);
                }
                
                
                if($i == 82){
                    //Step 3: create the DB schema.
                    $command = new CreateSchemaDoctrineCommand();
                    $application->add($command);
                    $input = new ArrayInput(array('command' => 'doctrine:schema:create'));
                    $command->run($input, new NullOutput);
                    $progressBar->finish();
                    //$output->writeln('');
                    //$progressBar->finish();
                    break;
                }
            }
            
                //Dowload the cache 
                $r = $synchronizerHandler->download($branchId);
                //Get the branch name to display it
                $branch = $em->getRepository('KmBundle:Branch')->findOneBy(array('onlineId' => $branchId));
                
                if(!$r['status']){
                    $output->writeln('');
                    $output->writeln('<error>[Failed]. '.$r['message'].'</error>');
                }else{
                    $output->writeln('');
                    $output->writeln('');
                    $output->writeln('<bg=green>[OK]. '.$r['message'].'</>');
                    $output->writeln('');
                    $output->writeln('<fg=yellow><options=bold>Notice</>:Your synchronizer is ready! Just close and re-open the Application in order to start using it. </>');
                    $output->writeln('');
                    $output->writeln('<fg=yellow><options=bold>Branch #ID</>:</> '.$branch->getOnlineId());
                    
                    $output->writeln('<fg=yellow><options=bold>Branch Name</>:</> '.$branch->getName());
                    
                    $output->writeln('');
                    $output->writeln([
                '================ <options=bold;fg=magenta>BeezyManager © 2018</></> ==============']);
                    $output->writeln('');
                    $output->writeln(['Powered by <fg=yellow><options=bold>iTech CAR.</></> BP:#__ Bangui(RCA) 1er Arrd. Av. des Martyres Immble. MARABENA (1er Etage) Tel: +236 728 030 37']);
                    $output->writeln('');
                    $output->writeln([
                '================ <options=bold;fg=magenta>www.beezymanager.com</></> ==============']);
                    
                }
                
        }else{
            $output->writeln('<error>[Failed]. '.$r['message'].'</error>');
        }
        
    }
    
    
    
}