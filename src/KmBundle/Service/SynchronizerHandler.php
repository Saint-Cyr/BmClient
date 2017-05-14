<?php

namespace KmBundle\Service;

use KmBundle\Entity\Branch;
use TransactionBundle\Entity\Product;
use TransactionBundle\Entity\Stock;

class SynchronizerHandler
{
    //To store the entity manager
    private $client;
    
    public function __construct($client)
    {
        $this->client = $client;
    }

    public function start()
    {
        $response = $this->client->post('http://localhost/BeezyManager2/web/app_dev.php/sales/transactions',
                          ['json' => ['foo' => 'bar']]);

        return $response->getBody();

    }
    

}
