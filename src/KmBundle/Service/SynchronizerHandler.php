<?php

namespace KmBundle\Service;

use KmBundle\Entity\Branch;
use TransactionBundle\Entity\Product;
use TransactionBundle\Entity\Stock;

class SynchronizerHandler
{
    //To store the entity manager
    private $client;
    private $em;


    public function __construct($client, $em)
    {
        $this->client = $client;
        $this->em = $em;
    }

    public function start()
    {
        $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/sales/transactions',
                          ['json' => ['foo' => 'bar']]);

        return $response->getBody();

    }
    
    /*
     * @return true | false
     * @description : return true when current cache where clean and download succeed
     * return false when download does not succeed. In such case cache not empty may be
     * the reason
     */
    public function downloadCache()
    {
        $_products = $this->em->getRepository('TransactionBundle:Product')->findAll();
        //Make sure current cache is clean
        if(count($_products) == 0 ){
            
            $response = $this->client->get('http://localhost/BeezyManager2/web/app_dev.php/caches',
                ['json' => []]);
        
            //$products are set of array() items
            $products = json_decode($response->getBody()->getContents(), true);

            foreach ($products['products'] as $product){
                //just create and persist new instance for each one
                $p = new Product();
                $p->setBarcode($product['barcode']);
                $p->setName($product['name']);
                $p->setUnitPrice($product['unit_price']);

                //persist
                $this->em->persist($p);
                $this->em->flush();
            }
            
            return true;
            
        }else{
            
            return false;
        }
        
    }

}
