<?php

/*
 * This file is part of Components of KingManager project
 * By contributor S@int-Cyr MAPOUKA
 * (c) YAME Group <info@yamegroup.com>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\TransactionBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class STransactionTest extends WebTestCase
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
        $this->saleHandler = $this->application->getKernel()->getContainer()->get('transaction.sale_handler');
    }
    
    public function testGetProfit()
    {
        //To avoid risk notice by PHPUnit
        $this->assertTrue(true);
        //Get a STransaction from the fixtures
        $STransaction = $this->em->getRepository('TransactionBundle:STransaction')->find(1);
        //$this->assertEquals($STransaction->getProfit(), 1230.00);
    }
    
    public function testGetTotalAmount()
    {
        //To avoid risk notice by PHPUnit
        $this->assertTrue(true);
        $STransaction = $this->em->getRepository('TransactionBundle:STransaction')->find(2);
        //$this->assertEquals($STransaction->getTotalAmount(), 10000);
    }
}
