<?php
/*
 * This file is part of Components of BeezyManager project
 * By contributor S@int-Cyr MAPOUKA
 * (c) iSTech <ceo.itechcar.com>
 * For the full copyrght and license information, please view the LICENSE
 * file that was distributed with this source code
 */
namespace Tests\KmBundle\Service;

use FOS\RestBundle\Serializer\JMSSerializerAdapter;
use KmBundle\Entity\Branch;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use TransactionBundle\Entity\Product;
use TransactionBundle\Entity\Stockg;
use TransactionBundle\Entity\Sale;
use TransactionBundle\Entity\STransaction;

/*
 * this class is compatible with client.yml fixture
 */
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
     * This method aims to test the endpoint API (BSol Server). Thus, make sure the Server
     * is up, running and does have the right fixtures data (check the table of fixtures compatibily in 
     * MED Data Base )
     */
    public function testSendDataToServer()
    {
        //The connected user from the BSol Client App
        $user = $this->em->getRepository('UserBundle:User')->find(1);
        $currentBranch = $this->em->getRepository('KmBundle:Branch')->findOneBy(array('name' => 'BRANCH_1'));
        $branchOnlineID = $currentBranch->getOnlineId();
        $userEmail = $user->getEmail();
        //Make to use the right variables for the test. Notice some of them have to be compatible with
        // the Server's one (branchOnlineId, ..)
        $this->assertEquals($branchOnlineID, 1);
        $this->assertEquals($userEmail, 'mapoukacyr@yahoo.fr');
        
        
        /*
         * SENARIO #1: SUCCESSFUL Synchronization: send data to the server, exepect the server to give back a response (which means 
         * it have save the data in its DB successfully) and finally, compare the stransaction id, sent back by to the server to the one in the 
         * BmClient Data Base in order to notice that they are the same. End of Senario
         * Notice that as this is only a test there is no need to to remove the stransaction in the BmClient DB.
         */
        $id = null;
        
        $objects = $this->em->getRepository('TransactionBundle:STransaction')->findAll();
	foreach ($objects as $ob){
            $id = $ob->getId();
            break;
        }
        
        if($id){
        $st = $this->em->getRepository('TransactionBundle:STransaction')->find($id);
        $stId = $st->getIdSynchrone();
            $dateTime = $st->getCreatedAt()->format('Y-m-d H:i:s');
            //Prepare order
            foreach ($st->getSales() as $sale){
                $totalPrice = $sale->getQuantity() * $sale->getProduct()->getUnitPrice();
                $order[] = array('id' => $sale->getProduct()->getOnlineId(),
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
            $response = $this->client->post('http://localhost/BeezyManager/web/upload2s', ['json' => $outPutData]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->assertEquals($response->getStatusCode(), 200);
            //If there is faillure then lets see the message
            if($data['faild']){
                $this->assertEquals($data['faild'], true);
            }
            //Make sure the server has sent back the right Data structure
            $this->assertNotNull($data);
            $this->assertArrayHasKey('st_synchrone_id', $data);
            $this->assertArrayHasKey('faildMessage', $data);
            $this->assertArrayHasKey('faild', $data);
            $this->assertEquals($data['st_synchrone_id'], $stId);
            
            $_ST = $this->em->getRepository('TransactionBundle:STransaction')
                        ->findOneBy(array('idSynchrone' => $data['st_synchrone_id']));
            //As this is a test, we don't need to remove the ST from the Data Base but just need to check 
            //its presence in the locale Data Base
            $this->assertEquals($_ST->getId(), $stId);
        }else{
            $this->assertEquals('', 'no stransaction to upload.');
        }
    }
}
