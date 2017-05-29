<?php

namespace KmBundle\Service;

use TransactionBundle\Entity\Product;
use KmBundle\Entity\Branch;
use UserBundle\Entity\User;

class SynchronizerHandler
{
    //To store the entity manager
    private $client;
    private $serverhost;
    private $em;


    public function __construct($client, $em, $host)
    {
        $this->client = $client;
        $this->em = $em;
        $this->serverhost = $host;
    }

    /*public function start()
    {
        $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/sales/transactions',
                          ['json' => ['foo' => 'bar']]);

        return $response->getBody();

    }*/
    
    /*
     * @return array('statu' => bool, 'message' => 'string')
     * @description : return true when current cache where clean and download succeed
     * return false when download does not succeed. In such case cache not empty may be
     * the reason
     */
    public function download($branchId)
    {
        $_products = $this->em->getRepository('TransactionBundle:Product')->findAll();
        //Make sure current cache is clean
        if(count($_products) == 0 ){
            
            $response = $this->client->post($this->serverhost.'/downloads',
                ['json' => array('branch_id' => $branchId)]);
        
            $data = json_decode($response->getBody()->getContents(), true);
            //Check the response of the server in order to undle any error properly.
            if($data['status'] == true){
                //Create the branch
                $branch = new Branch();
                $branch->setName($data['branch']['name']);
                $branch->setOnlineId($data['branch']['id']);
                $this->em->persist($branch);
                
                //$products are set of array() items
                foreach ($data['products'] as $product){
                    //just create and persist new instance for each one
                    $p = new Product();
                    $p->setOnlineId($product['id']);
                    $p->setBarcode($product['barcode']);
                    $p->setName($product['name']);
                    if(key_exists('unit_price', $product)){
                        $p->setUnitPrice($product['unit_price']);
                    }
                    //persist
                    $this->em->persist($p);
                    //$this->em->flush();
                }

                //$data['users] are set of array() items
                foreach ($data['users'] as $user){
                    //just create and persist new instance for each one
                    $u = new User();
                    $u->setUsername($user['username']);
                    $u->setEmail($user['email']);
                    $u->setPlainPassword($user['email']);
                    $u->setEnabled(true);
                    $u->setRoles($user['roles']);
                    $u->setBranch($branch);

                    //persist
                    $this->em->persist($u);
                    $this->em->flush();
                }
                //Case where the branch_id does exist on server
                return array('status' => $data['status'], 'message' => $data['message']);
            }else{
                //Case where the branch_id does not exist on server
                return array('status' => $data['status'], 'message' => $data['message']);
            }
            
        }else{
            
            return array('status' => false, 'message' => 'product not synchronized.');;
        }
        
    }

}
