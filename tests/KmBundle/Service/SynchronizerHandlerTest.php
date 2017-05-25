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
        $this->execute();
        
        $this->assertEquals(true, true);
    }
    
    public function execute()
    {
        $user = $this->em->getRepository('UserBundle:User')->find(1);
        $branchOnlineID = $user->getBranch()->getOnlineId();
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

            $outPutData = array('branch_online_id' => $branchOnlineID,
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
    
    public function testDownloadCache()
    {
        //Case of BATA ( branch_id = 1)
        $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/caches',
                ['json' => ['branch_id' => 1]]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        //Make sure the number of the products is right.
        $this->assertEquals(count($data['products']), 4);
        //Test the presence of the each product with theire respective properties
        $this->assertEquals($data['products'][0]['name'], 'CD Simple');
        $this->assertEquals($data['products'][0]['barcode'], '2002256910205');
        $this->assertEquals($data['products'][0]['unit_price'], 150);
        //just make a custom request in server side to ensure loading of only locked products
        //$this->assertEquals($data['products'][0]['locked'], true);
        $this->assertEquals($data['products'][0]['id'], 1);
        //Make sure all the product has been loaded.
        $this->assertEquals($data['products'][1]['name'], 'DVD');
        //Check whether there products in DB in order to prapare response accordintly
        $products = $this->em->getRepository('TransactionBundle:Product')->findAll();
        
        if(count($products) > 0){
            $resp = false;
        }else{
            $resp = true;
        }
        
        //Test the downloadCache() methode itself
        $r = $this->synchronizerHandler->downloadCache(1);
        $this->assertEquals($r['status'], $resp);
        //In the case request has been sent to the server ($r['status'] == true), then 
        //make sure the server has sent the branch_id back in order for the client to create
        //and persist it in the cache because when creating users, it have to be linked to them
        //also when a user is creating  STransaction, it also have to be linked to it
        
        $this->assertEquals($data['branch']['name'], 'BATA');
        $this->assertEquals($data['branch']['id'], 1);
        
         
        //Test users
        $this->assertCount(3, $data['users']);
        //Test the presence of the each user with theire respective properties
        $this->assertEquals($data['users'][0]['username'], 'super-admin');
        $this->assertEquals($data['users'][1]['email'], 'admin@domain.com');
        $this->assertEquals($data['users'][1]['roles'], array('ROLE_ADMIN'));
        //Make sure the API work in branch context (Case of VALLEY)
        $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/caches',
                ['json' => ['branch_id' => 2]]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        //Only one user at VALLEY according to the fixture
        $this->assertCount(1, $data['users']);
        //Make sure the error message is right when sending a wrong branchId via the API
        $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/caches',
                ['json' => ['branch_id' => 3]]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertFalse($data['status']);
        $this->assertEquals($data['message'], 'branch not found');
        
        //Test the download method
        $data = $this->synchronizerHandler->downloadCache(20);
        //When sending wrong branch_id, the server responde ['status'] => false
        $this->assertFalse($data['status']);
    }
}
