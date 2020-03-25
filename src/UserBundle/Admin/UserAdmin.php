<?php

namespace UserBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class UserAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('username')
            ->add('email')
            ->add('enabled')
            ->add('branch')
        ;
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('image', null, array('template' => 'UserBundle:Default:list.html.twig'))
            ->add('name')
            ->add('email')
            ->add('branch')
            ->add('enabled', null, array('editable' => true))
            ->add('lastLogin')
            ->add('_action', null, array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ))
        ;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $edition = (preg_match('/_edit$/', $this->getRequest()->get('_route'))) ? false : true;
        $typeContext = array();
        
        if($this->isGranted('ROLE_SUPER_ADMIN') && ($edition)){
            $typeContext['Super-Admin'] = 'super-admin';
            $typeContext['Administrator'] = 'administrator';
        }
        
        if($this->isGranted('ROLE_ADMIN') && ($edition)){
            $typeContext['Seller'] = 'seller';
        }
        
        $formMapper
            ->with('Connexion Information', array('class' => 'col-md-4'))
                ->add('username')
                ->add('email')
                ->add('plainPassword', 'repeated', array(
                        'type' => 'password',
                        'invalid_message' => 'The password fields must match.',
                        'required' => $edition,
                        'first_options'  => array('label' => 'Password'),
                        'second_options' => array('label' => 'Repeat Password'),
                    ))
                
            ->end()
                
            ->with('Personal information', array('class' => 'col-md-4'))

                ->add('name', null, array('label' => 'Name (length must be more than 5)'))
                ->add('branch')
                ->add('phoneNumber')
                ->add('file', 'file', array('required' => false))
            ;
        
        
        if ($this->isGranted('EDIT')) {
            $formMapper->end()
            ->with('Security', array('class' => 'col-md-4'))
                ->add('type', 'choice', array('choices' => $typeContext,
                                              'expanded' => true))
            ->end();
        }
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('username')
            ->add('email')
            ->add('enabled')
            ->add('lastLogin')
        ;
    }
    
    public function preValidate($object) {
        parent::preValidate($object);
        $object->setEnabled(true);
        
        switch ($object->getType()){
            case 'super-admin':
                $object->setRoles(array('ROLE_SUPER_ADMIN'));
            break;
            case 'administrator':
                $object->setRoles(array('ROLE_ADMIN'));
            break;
            case 'seller':
                $object->setRoles(array('ROLE_SELLER'));
            break;
        }
    }
    
    public function prePersist($user)
    {
        $this->manageFileUpload($user);
    }
    
    public function __construct($code, $class, $baseControllerName, $manager = null) {
        parent::__construct($code, $class, $baseControllerName);
        $this->userManager = $manager;
    }
    
    public function preUpdate($user) {
        $this->manageFileUpload($user);
        $this->userManager->updateCanonicalFields($user);
        $this->userManager->updatePassword($user);
    }

    private function manageFileUpload($image)
    {
        if ($image->getFile()) {
            $image->refreshUpdated();
        }
    }
}
