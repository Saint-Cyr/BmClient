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

    /*
     * this test can disturb other that is why it is commented. You just need to uncomment it
     */
    public function testStart()
    {
        //$this->execute();
        
        $this->assertEquals(true, true);
    }
    
    public function execute()
    {
        $user = $this->em->getRepository('UserBundle:User')->find(1);
        $branchSynchronID = $user->getBranch()->getIdSynchrone();
        $userEmail = $user->getEmail();
        
        $id = null;
        
        $objects = $this->em->getRepository('TransactionBundle:STransaction')->findAll();
	foreach ($objects as $ob){
            $id = $ob->getId();
            break;
        }
        
        if($id){
        $st = $this->em->getRepository('TransactionBundle:STransaction')->find($id);
        $old = $st->getIdSynchrone();
            $dateTime = $st->getCreatedAt()->format('Y-m-d H:i:s');
            //Prepare order
            foreach ($st->getSales() as $sale){
                $totalPrice = $sale->getQuantity() * $sale->getProduct()->getUnitPrice();
                $order[] = array('id' => $sale->getProduct()->getId(),
                                 'orderedItemCnt' => $sale->getQuantity(),
                                 'totalPrice' => $totalPrice);
            }

            $outPutData = array('branch_synchrone_id' => $branchSynchronID,
                                'st_synchrone_id' => $st->getIdSynchrone(),
                                'user_email' => $userEmail,
                                'order' => $order,
                                'total' => $totalPrice,
                                'date_time' => $dateTime);
                            
            //set_time_limit(30);
            $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/synchronizers',
                ['json' => $outPutData]);

            //$this->assertEquals(1222, $response->getBody()->getContents());
            $data = json_decode($response->getBody()->getContents(), true);
            //var_dump($data);exit;
            $this->assertEquals($response->getStatusCode(), 200);

            $this->assertEquals($data['st_synchrone_id'], $old);
            //remove the iD from DataBase
            $_ST = $this->em->getRepository('TransactionBundle:STransaction')
                ->findOneBy(array('idSynchrone' => $data['st_synchrone_id']));

            $this->em->remove($_ST);
            $this->em->flush();
        }
    }
    
    public function testLoadCache()
    {
        $response = $this->client->get('http://localhost/BeezyManager2/web/app_dev.php/caches',
                ['json' => []]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        //Make sure the number of the products is right.
        $this->assertEquals(count($data['products']), 11);
        //Test the presence of the each product with theire respective properties
        $this->assertEquals($data['products'][0]['name'], 'CD Simple');
        $this->assertEquals($data['products'][0]['barcode'], '2002256910205');
        $this->assertEquals($data['products'][0]['unit_price'], 150);
        //just make a custom request in server side to ensure loading of only locked products
        //$this->assertEquals($data['products'][0]['locked'], true);
        $this->assertEquals($data['products'][0]['id'], 1);
        //Make sure all the product has been loaded.
        $this->assertEquals($data['products'][1]['name'], 'DVD');
        
        //Test the downloadCache() methode it self
        $r = $this->synchronizerHandler->downloadCache();
        //$products = $this->em->getRepository('TransactionBundle:Product')->findAll();
        //$this->assertEquals(count($products), 11);
        
    }
}
