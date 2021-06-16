<?php

namespace KmBundle\Service;

use TransactionBundle\Entity\Product;
use KmBundle\Entity\Branch;
use UserBundle\Entity\User;
use KmBundle\Entity\Setting;

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
    
    /*
     * check whether the product issue by the stock from the server contained duplicated data, which
     * case will surely faild the synchronization because of unique constraint on barcode property
     */
    public function checkDuplicatedStock(array $arrays)
    {
            
            $iIds = array_map(function ($a) { return $a['name']; }, $arrays);
            //print_r($array);exit;
            
            
            $counts = array_count_values($iIds);
            $present_3_times = array();
            foreach($counts as $v=>$count){
                if($count >= 2)//Present 2 times
                    return $v;
            }
    }
    
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
                ['version'=>1.0,'json' => array('branch_id' => $branchId)]);
        
            $data = json_decode($response->getBody()->getContents(), true);
            
            //Check the response of the server in order to handle any error properly.
            if($data['status'] == true){
                //Create the branch
                $branch = new Branch();
                $branch->setName($data['branch']['name']);
                $branch->setOnlineId($data['branch']['id']);
                $this->em->persist($branch);
                
                //update 7 May 2018: Make sure there is not duplicate
                $outPut = $this->checkDuplicatedStock($data['products']);
                if($outPut){
                    return array('status' => false, 'message' => 'Solution: remove all required duplicated stock ('.$outPut.')'
                        . ' from the Branch:'.$data['branch']['name']);
                }
            
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
                
                //Don't forget the create setting which is used to display(or not) console
                $setting = new Setting();
                $setting->setName('bmClient');
                //set the variable installed to true in order to avoid to load the console next time
                $setting->setAppInstalled(true);
                $this->em->persist($setting);

                //$data['users] are set of array() items
                foreach ($data['users'] as $user){
                    //just create and persist new instance for each one
                    $u = new User();
                    $u->setUsername($user['username']);
                    $u->setEmail($user['email']);
                    //To do : Change the parameter email by a randon string and send it by SMS or email
                    $u->setPlainPassword($user['email']);
                    $u->setEnabled(true);
                    $u->setRoles($user['roles']);
                    $u->setBranch($branch);

                    //persist
                    $this->em->persist($u);
                    $this->em->flush();
                }
                
                return array('status' => $data['status'], 'message' => 'Your Data Base has been synchronized successfully! you can now start to sale offline.');
            }else{
                //Case where the branch_id does not exist on server
                return array('status' => $data['status'], 'message' => $data['message']);
            }
            
        }else{
            
            return array('status' => false, 'message' => 'product not synchronized.');
        }
        
    }

}
