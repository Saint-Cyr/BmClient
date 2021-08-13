<?php

namespace TransactionBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class ProductAdmin extends AbstractAdmin
{
    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
         
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, array('editable' => true))
            ->add('onlineId')
            ->add('unitPrice', 'decimal', array('editable' => true))
            ->add('wholeSalePrice', 'decimal', array('editable' => true));    
        
        if($this->isGranted('ROLE_SUPER_ADMIN')){
                $listMapper->add('profit');
        };
        
        $listMapper
            ->add('imagePos', null, array('editable' => true));
        
            
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        //The super-user have to be able to edit barcode
        $disabled = true;
        
        if($this->isGranted('SUPER-ADMIN')){
            $disabled = false;       
        }
        
        //$option = (preg_match('/_edit$/', $this->getRequest()->get('_route'))) ? false : true;
        $formMapper
        ->with('General information', array('class' => 'col-md-8'))
            ->add('name')
            ->add('categories');
            $formMapper->add('barcode', null, array('disabled' => $disabled))
            ->add('file', 'file', array('required' => false))
        ->end()
        ->with('Pricing', array('class' => 'col-md-4'))
            ->add('unitPrice', null, array('required' => false))
            ->add('wholeSalePrice', null, array('required' => false))
        ->end()
        ;
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('unitPrice')
        ;
    }
    
    public function preValidate($object) {
        parent::preValidate($object);
        //If the unit sale price has not been field then initialize it
        if(!$object->getUnitPrice()){
            $object->setUnitPrice(0);
        }
        //If the whole sale price has not been field then initialize it
        if(!$object->getWholeSalePrice()){
            $object->setWholeSalePrice(0);
        }
    }
    
    public function getBatchActions()
    {
        // retrieve the default batch actions (currently only delete)
        $actions = parent::getBatchActions();
       
        if (
          $this->hasRoute('edit') && $this->isGranted('EDIT') &&
          $this->hasRoute('delete') && $this->isGranted('DELETE')
            ) {
        }

        return $actions;
    }
    
    public function prePersist($image)
    {
        $this->manageFileUpload($image);
    }

    public function preUpdate($image)
    {
        $this->manageFileUpload($image);
    }

    private function manageFileUpload($image)
    {
        if ($image->getFile()) {
            $image->refreshUpdated();
        }
    }
}
