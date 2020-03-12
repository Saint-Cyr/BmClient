<?php

namespace KmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function synchronizerAction()
    {
        $serverhost = $this->getParameter('serverhost');
        $em = $this->getDoctrine()->getManager();
        $synchronizerHandler = $this->get('km.synchronizer_handler');
        $client = $this->get('guzzle.client.api_crm');
        $user = $this->getUser();
        //Make sure $user is not empty
        if(!$user){
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
        }
        //Check it againt
        if(!$user){
            return new JsonResponse('Could not find user from local DB');
        }
        
        $userEmail = $user->getEmail();
        //We want to send one by one
        $id = null;
        $objects = $em->getRepository('TransactionBundle:STransaction')->findAll();
        //First of all make sure there is at least 1 STransaction
        //in order to do not call the remote server for nothing
        if(count($objects) == 0){
            return new JsonResponse(array('sleep' => true));
        }
        
        foreach ($objects as $ob){
            $id = $ob->getId();
            //Make sure $st is valid
            if($id){
                $st = $em->getRepository('TransactionBundle:STransaction')->find($id);
                $old = $st->getIdSynchrone();
                $dateTime = $st->getCreatedAt()->format('Y-m-d H:i:s');
                //Prepare order
                //Definitly, we'll need  this variable
                $order = array();
                foreach ($st->getSales() as $sale){
                    $totalPrice = $sale->getQuantity() * $sale->getProduct()->getUnitPrice();
                    $order[] = array('id' => $sale->getProduct()->getOnlineId(),
                                     'orderedItemCnt' => $sale->getQuantity(),
                                     'totalPrice' => $totalPrice);
                }
                //Make sure $order is not empty
                if(!(count($order) == 0)){
                    $outPutData = array(
                                    'st_synchrone_id' => $st->getIdSynchrone(),
                                    'user_email' => $userEmail,
                                    'order' => $order,
                                    'total' => $totalPrice,
                                    'sellerUserName' => $st->getUser()->getUserName(),
                                    'branch_online_id' => $user->getBranch()->getOnlineId(),
                                    'date_time' => $dateTime);
                   break; 

                }else{
                    //Let's go to the next id
                }
            }
        }
        
        if($id){
        $st = $em->getRepository('TransactionBundle:STransaction')->find($id);
        $old = $st->getIdSynchrone();
            $dateTime = $st->getCreatedAt()->format('Y-m-d H:i:s');
            //Prepare order
            //Definitly, we'll need  this variable
            $order = array();
            foreach ($st->getSales() as $sale){
                $totalPrice = $sale->getQuantity() * $sale->getProduct()->getUnitPrice();
                $order[] = array('id' => $sale->getProduct()->getOnlineId(),
                                 'orderedItemCnt' => $sale->getQuantity(),
                                 'totalPrice' => $totalPrice);
            }
            //Make sure $order is not empty
            if(!(count($order) == 0)){
                $outPutData = array(
                                'st_synchrone_id' => $st->getIdSynchrone(),
                                'user_email' => $userEmail,
                                'order' => $order,
                                'total' => $totalPrice,
                                'sellerUserName' => $st->getUser()->getUserName(),
                                'branch_online_id' => $user->getBranch()->getOnlineId(),
                                'date_time' => $dateTime);
                
            }else{
                $data['faildMessage'] = 'Empty order';
                return new JsonResponse($data['faildMessage']);
            }
            //set_time_limit(30);
            $response = $client->post($serverhost.'/uploads',
                ['json' => $outPutData]);
            //At this stage, make sure the request have got the server
            //.....
            $data = json_decode($response->getBody()->getContents(), true);
            
            
            //To do : make sure the variable $faild is false before continue
            //if everything went welrel
        if(!$data['faild'] || array_key_exists('remove_st', $data)){
                //remove the ST from DataBase
                $_ST = $em->getRepository('TransactionBundle:STransaction')
                    ->findOneBy(array('idSynchrone' => $data['st_synchrone_id']));
                //Make sure $_ST exist
                if($_ST){
                    $em->remove($_ST);
                }
                $em->flush();
                return new JsonResponse('successfull.');
            }else{
                return new JsonResponse($data['faild_message']);
            }
        }
    }

    public function frontAction(Request $request)
    {
        //when logout, goes to the login page
        //Get the authorization checker
        $authChecker = $this->get('security.authorization_checker');
        //this is needed to upload products (.csv) or users to be registered in DB
        $localhost = 'http://localhost'.$request->getBaseUrl();
        //Windows environement
        //$localhost = 'http://127.0.0.1:49160'; 
        if(!$authChecker->isGranted("ROLE_SELLER")){
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        //Get the entity manager
        $em = $this->getDoctrine()->getManager();
        //Get the total number of all STransaction
        $stransactionNb = count($em->getRepository('TransactionBundle:STransaction')->findAll());
        
        return $this->render('KmBundle:Default:front.html.twig',
                             array('stransactionNb' => $stransactionNb,
                                   'localhost' => $localhost));
    }
}
