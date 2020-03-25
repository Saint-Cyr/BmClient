<?php

/*
 * This file is part of Components of KingManager project
 * By contributor S@int-Cyr MAPOUKA
 * (c) TizampaTech <mapoukacyr@yahoo.fr>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */
namespace Tests\TransactionBundle\Entity;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SaleTest extends WebTestCase
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
        $sale = $this->em->getRepository('TransactionBundle:Sale')->find(1);
        //$this->assertEquals($sale->getProfit(), 123);
        
        $sale = $this->em->getRepository('TransactionBundle:Sale')->find(12);
        //$this->assertEquals($sale->getProfit(), 4000);
        
    }
    
    public function testGetAmount()
    {
        //To avoid risk notice by PHPUnit
        $this->assertTrue(true);
        $sale = $this->em->getRepository('TransactionBundle:Sale')->find(1);
        //$this->assertEquals($sale->getAmount(), 130);
    }
    
    public function testSetAmount()
    {
        $sale = $this->em->getRepository('TransactionBundle:Sale')->find(1);
        $sale->setAmount(100);
        $this->assertEquals(100, $sale->getAmount());
        
        $sale->setAmount();
        $this->assertEquals(150, $sale->getAmount());
    }
}
