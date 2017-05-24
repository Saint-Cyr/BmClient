<?php

namespace TransactionBundle\Service;
use TransactionBundle\Entity\STransaction;
use TransactionBundle\Entity\Sale;
use KmBundle\Entity\Branch;
//use FOS\RestBundle\View\View;

class SaleHandler
{
    //To store the entity manager
    private $em;
    //private $stockHandler;
    private $tokenStorage;
    
    public function __construct($em, $tokenStorage) 
    {
        $this->em = $em;
        //$this->stockHandler = $stockHandler;
        $this->tokenStorage = $tokenStorage;
    }
    
    public function processSaleTransaction(array $inputData, Branch $branch)
    {
        //Create an instance of a SaleTransaction & hydrate it with the branch and the total amount of the transaction
        $stransaction = new STransaction();
        $stransaction->setTotalAmount($inputData['total']);
        $stransaction->setBranch($branch);
        //Link the employee to the transaction
        $user = $this->tokenStorage->getToken()->getUser();
        $stransaction->setUser($user);
        
        //Loop over each sale
        foreach ($inputData['order'] as $s){
            //create an instance of a sale
            $sale = new Sale();
            //Link the sale to the related product
            $product = $this->em->getRepository('TransactionBundle:Product')->find($s['item']['id']);
            $sale->setProduct($product);
            $sale->setProfit();
            //Call the stocktHandler service to update the stock
            //This is call is deprecated and generate error since stock management is no more supportated on client side
//            $this->stockHandler->updateStock($branch, $product, $s['orderedItemCnt'], true);
            //Set the quantity
            $sale->setQuantity($s['orderedItemCnt']);
            $sale->setAmount($s['totalPrice']);
            $sale->setStransaction($stransaction);
            $this->em->persist($sale);
        }
        //Generate idSynchrone here
        
        //Persist its in DB.
        $this->em->persist($stransaction);
        $this->em->flush();
    }
}
