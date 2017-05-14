<?php

/*
 * This file is part of Components of BeezyManager project
 * By contributor S@int-Cyr MAPOUKA
 * (c) TinzapaTech <mapoukacyr@yahoo.fr>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Tests\KmBundle\Service;

use FOS\RestBundle\Serializer\JMSSerializerAdapter;
use KmBundle\Entity\Branch;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TransactionBundle\Entity\Product;
use TransactionBundle\Entity\Sale;
use TransactionBundle\Entity\STransaction;

class SynchronizerHandlerTest extends WebTestCase
{
    private $em;
    private $client;
    private $application;
    private $synchronizerHandler;
    private $serializer;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->application = new Application(static::$kernel);
        $this->em = $this->application->getKernel()->getContainer()->get('doctrine.orm.entity_manager');
        $this->synchronizerHandler = $this->application->getKernel()
                        ->getContainer()->get('km.synchronizer_handler');
        $this->client = $this->application->getKernel()
            ->getContainer()->get('guzzle.client.api_crm');
        $this->serializer = $this->application->getKernel()
            ->getContainer()->get('jms_serializer');

    }

    public function testStart()
    {
        $branchSynchronID = 1;
        $userEmail = 'mapoukacyr@yahoo.fr';

        $stransaction = $this->em->getRepository('TransactionBundle:STransaction')->find(5);
        $stransactionsTab[] = $stransaction;
        //$st = $this->em->getRepository('TransactionBundle:STransaction')->find(1);

        foreach ($stransactionsTab as $st){
            $dateTime = $st->getCreatedAt()->format('Y-m-d H:i:s');
            //Prepare order
            foreach ($st->getSales() as $sale){
                $totalPrice = $sale->getQuantity() * $sale->getProduct()->getUnitPrice();
                $order[] = array('item' => ['id' => $sale->getProduct()->getId()],
                                 'orderedItemCnt' => $sale->getQuantity(),
                                 'totalPrice' => $totalPrice);
            }

            $outPutData = array('branch_synchrone_id' => $branchSynchronID,
                                'st_synchrone_id' => $st->getIdSynchrone(),
                                'user_email' => $userEmail,
                                'order' => $order,
                                'total' => $totalPrice,
                                'date_time' => $dateTime);

            set_time_limit(30);
            $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/synchronizers',
                ['json' => $outPutData]);

            //$this->assertEquals(1222, $response->getBody()->getContents());
            $data = json_decode($response->getBody()->getContents(), true);
            $this->assertEquals($response->getStatusCode(), 200);

            //$this->assertEquals($data['st_synchrone_id'], 2);
            $this->assertTrue(is_int($data['st_synchrone_id']));
            //remove the iD from DataBase
            $_ST = $this->em->getRepository('TransactionBundle:STransaction')
                ->findOneBy(array('idSynchrone' => $data['st_synchrone_id']));

            $this->em->remove($_ST);
            $this->em->flush();


        }

        //$outPut = $response->getBody()->getContents();
        //$tab = json_decode($outPut, true);
        //$this->assertEquals($tab['synchronized'], true);


    }
}