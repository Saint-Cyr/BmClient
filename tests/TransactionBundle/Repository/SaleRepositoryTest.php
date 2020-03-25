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

class SaleRepositoryTest extends WebTestCase
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

    public function testGetFromTo()
    {
        //Case 1
        $product = $this->em->getRepository('TransactionBundle:Product')->find(10);
        $this->assertEquals($product->getName(), 'Chemise cartonier');

        $initDate = new \DateTime('now');
        $finalDate = new \DateTime('now');

        $sales = $this->em->getRepository('TransactionBundle:Sale')
            ->getFromTo($initDate, $finalDate, $product);

        $this->assertCount(2, $sales);

        //Case 2
        $product = $this->em->getRepository('TransactionBundle:Product')->find(5);
        $this->assertEquals($product->getName(), 'Scanner');

        $initDate = new \DateTime('2011-01-01');
        $finalDate = new \DateTime('2011-01-01');

        $sales = $this->em->getRepository('TransactionBundle:Sale')
            ->getFromTo($initDate, $finalDate, $product);

        $this->assertCount(3, $sales);

    }
}
