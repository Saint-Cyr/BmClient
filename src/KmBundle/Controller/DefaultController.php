<?php

namespace KmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Hackzilla\BarcodeBundle\Utility\Barcode;
use \Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function synchronizerAction()
    {
        $client = $this->get('guzzle.client.api_crm');
        $em = $this->getDoctrine()->getManager();
        $synchronizerHandler = $this->get('km.synchronizer_handler');
        $user = $this->getUser();
        
        $branchOnlineId = $user->getBranch()->getOnlineId();
        $userEmail = $user->getEmail();
        //We want to send one by one
        $id = null;
        $objects = $em->getRepository('TransactionBundle:STransaction')->findAll();
	//If there is nothing then sleep
        
        if(count($objects) == 0){
            return new JsonResponse(array('sleep' => true));
        }
        
        foreach ($objects as $ob){
            $id = $ob->getId();
            break;
        }
        
        if($id){
        $st = $em->getRepository('TransactionBundle:STransaction')->find($id);
        $old = $st->getIdSynchrone();
            $dateTime = $st->getCreatedAt()->format('Y-m-d H:i:s');
            //Prepare order
            foreach ($st->getSales() as $sale){
                $totalPrice = $sale->getQuantity() * $sale->getProduct()->getUnitPrice();
                $order[] = array('id' => $sale->getProduct()->getId(),
                                 'orderedItemCnt' => $sale->getQuantity(),
                                 'totalPrice' => $totalPrice);
            }

            $outPutData = array('branch_online_id' => $branchOnlineId,
                                'st_synchrone_id' => $st->getIdSynchrone(),
                                'user_email' => $userEmail,
                                'order' => $order,
                                'total' => $totalPrice,
                                'date_time' => $dateTime);
                            
            //set_time_limit(30);
            $response = $client->post('http://localhost/BeezyManager2/web/app_dev.php/synchronizers',
                ['json' => $outPutData]);

            //$this->assertEquals(1222, $response->getBody()->getContents());
            $data = json_decode($response->getBody()->getContents(), true);
            //remove the iD from DataBase
            $_ST = $em->getRepository('TransactionBundle:STransaction')
                ->findOneBy(array('idSynchrone' => $data['st_synchrone_id']));

            $em->remove($_ST);
            $em->flush();
            return new JsonResponse('successfull.');
        }
    }

    public function frontAction()
    {
        //when logout, goes to the login page
        //Get the authorization checker
        $authChecker = $this->get('security.authorization_checker');
        if(!$authChecker->isGranted("ROLE_SELLER")){
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        
        return $this->render('KmBundle:Default:front.html.twig');
    }
}
