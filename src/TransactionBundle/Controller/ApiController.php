<?php

namespace TransactionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{   
    public function postSaleTransactionAction(Request $request)
    {
        
        //Get the input data sent by the front application
        $inputData = json_decode($request->getContent(), true);
        //Get the branch from the user object
        
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        $branch = $user->getBranch();
        
        $data = $inputData['data'];
        
        //Get the STransaction handler service
        $saleHandler = $this->get('transaction.sale_handler');
        //Process the sale transaction
        $saleHandler->processSaleTransaction($data, $branch);
        
        $response = new Response($this->get('translator')->trans('Successfull transaction!'));
                            
        $response->headers->set('Access-Control-Allow-Origin', 'http://127.0.0.1');
        return $response;
        //return View::create(array('info' => 'user of verifier_token: '.$inputData['verifier_token'].' has been logged out successfully.'), 200);
        
        return 'successfull transaction!';
    }
}