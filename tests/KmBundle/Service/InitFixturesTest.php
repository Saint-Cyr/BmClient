<?php

/*
 * This file is part of Components of BeezyManager project
 * By contributor S@int-Cyr MAPOUKA
 * (c) StCyrLabs <mapoukacyr@yahoo.fr>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\KmBundle\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class InitFixturesTest extends WebTestCase
{
    private $em;
    private $application;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        
        $this->application = new Application(static::$kernel);
        $this->em = $this->application->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
    }
    
    public function testInit()
    {
        $this->assertTrue(true);
        $this->Init();
    }

    public function Init()
    {
        //Step 1: drop the DB.
        $command = new DropDatabaseDoctrineCommand();
        $this->application->add($command);
        //Prepare the command input
        $input = new ArrayInput(array('command' => 'doctrine:database:drop',
                                      '--force' => true));
        //Run the command
        $command->run($input, new NullOutput);
        //We have to close the connexion in order to do not get "no database connexion error"
        $connection = $this->application->getKernel()->getContainer()->get('doctrine')->getConnection();
         if ($connection->isConnected()) {
            $connection->close();
        }
        
        //Step 2: create new DB
        $command = new CreateDatabaseDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array('command' => 'doctrine:database:create'));
        $command->run($input, new NullOutput);
        
        //Step 3: create the DB schema.
        $command = new CreateSchemaDoctrineCommand();
        $this->application->add($command);
        $input = new ArrayInput(array('command' => 'doctrine:schema:create'));
        $command->run($input, new NullOutput);
        
        //Step 4: Load the fixtures
        //Get all the necessary services
        $executor = $this->application->getKernel()->getContainer()->get('hautelook_alice.doctrine.executor.fixtures_executor');
        $loaderGenerator = $this->application->getKernel()->getContainer()->get('hautelook_alice.doctrine.orm.loader_generator');
        $loader = $this->application->getKernel()->getContainer()->get('hautelook_alice.fixtures.loader');
        $fixtureLoader = $this->application->getKernel()->getContainer()->get('hautelook_alice.alice.fixtures.loader');
        $fixtureFinder = $this->application->getKernel()->getContainer()->get('hautelook_alice.doctrine.orm.fixtures_finder');
        $kernel = $this->application->getKernel();
        
        //Launch the execution
        $executor->execute( 
                            $this->em,
                            $loaderGenerator->generate($loader, $fixtureLoader, $kernel->getBundles(), 'test'),
                            $fixtureFinder->resolveFixtures($kernel, array('@KmBundle/DataFixtures/ORM/installation.yml')),
                            false,
                            false
                          );
        
        
        $this->assertEquals(true, true);
    }
    
    /*
     * To avoid PHPUnit to generate
     * no tests found exception
     */
    public function testDummy()
    {
        $this->assertTrue(true);
        
    }
    
}
