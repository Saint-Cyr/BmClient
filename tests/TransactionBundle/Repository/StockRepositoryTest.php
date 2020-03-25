<?php

/*
 * This file is part of Components of KingManager project
 * By contributor S@int-Cyr MAPOUKA
 * (c) TinzapaTech <mapoukacyr@yahoo.fr>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\TransactionBundle\Service;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StockRepositoryTest extends WebTestCase
{
    private $em;
    private $application;
    private $saleHandler;


    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        
        $this->application = new Application(static::$kernel);
        $this->em = $this->application->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
        $this->reportHandler = $this->application->getKernel()->getContainer()->get('km.report_handler');
    }
    
    public function testGetStocked()
    {
        $branch = $this->em->getRepository('KmBundle:Branch')->find(1);
        $stocked = $this->em->getRepository('TransactionBundle:Stock')->getStocked($branch);
        $this->assertEquals(count($stocked), 1);
    }
    
    public function testGetDestocked()
    {
        $branch = $this->em->getRepository('KmBundle:Branch')->find(1);
        $destocked = $this->em->getRepository('TransactionBundle:Stock')->getDestocked($branch);
        $this->assertEquals(count($destocked), 1);
    }
}
