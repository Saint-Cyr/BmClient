<?php

namespace UserBundle\Event;

use Sonata\AdminBundle\Event\ConfigureEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UserCustomAction
{

    /**
     * show an error if user is not superadmin and tries to manage other user details
     * 
     * @param  ConfigureEvent $event sonata admin event
     * @return null
     */
    public function sonataAdminCheckUserRights(ConfigureEvent $event)
    {
        // ignore this if user is admin
        if ($event->getAdmin()->isGranted('ROLE_SUPER_ADMIN')||$event->getAdmin()->getBaseCodeRoute() != 'user.admin.user') {
            return;
        }
        
        if($event->getAdmin()->getRequest()->attributes->has('id')){
            $user_id = $event->getAdmin()->getRequest()->attributes->all()['id'];
        }else{
            return;
        }
        
        // we can get container from getAdmin
        $session_id = $event->getAdmin()->getConfigurationPool()->getContainer()
                ->get('security.token_storage')->getToken()->getUser()->getId();
       
        if ($user_id != $session_id) {
            throw new AccessDeniedException();
        }
    }
}