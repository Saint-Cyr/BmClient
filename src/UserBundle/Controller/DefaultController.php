<?php

namespace UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserChecker;

class DefaultController extends Controller
{   
    public function vendorRegistrationAction()
    {
        return $this->render('UserBundle:Registration:register_content.html.twig');
    }
    
    public function removeAction($id)
    {
        //Get the security token
        
        //Get the user context
        $userContext = $this->getUser();
        if(!$userContext){
            throw $this->createNotFoundException('user context not found');
        }
        //Get the doctrine entity manager
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('UserBundle:User')->find($id);
        //Make sure $user is not null
        if(!$user){
            throw $this->createNotFoundException('user of ID '.$id.' not found');
        }elseif(($userContext->getId() != $id)&&($this->denyAccessUnlessGranted('ROLE_SUPER_ADMIN', null, 'Unless you\'re a Super-admin, you can\'t remove someone\'s account'))){
            var_dump('you can only remove your own profile');exit;
        }
        //Remove the user form the Database
        //$em->remove($user);
        //Persist the change
        //$em->flush();
        return new Response('user of ID: '.$id.' have been removed successfully');
    }
}
