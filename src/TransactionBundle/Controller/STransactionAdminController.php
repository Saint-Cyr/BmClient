<?php

namespace TransactionBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;



class STransactionAdminController extends CRUDController
{
    
    /**
     * Create action.
     *
     * @return Response
     *
     * @throws AccessDeniedException If access is not granted
     */
    public function createAction()
    {
        
        $request = $this->getRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

        $this->admin->checkAccess('create');

        $class = new \ReflectionClass($this->admin->hasActiveSubClass() ? $this->admin->getActiveSubClass() : $this->admin->getClass());

        if ($class->isAbstract()) {
            return $this->render(
                'SonataAdminBundle:CRUD:select_subclass.html.twig',
                array(
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ),
                null,
                $request
            );
        }

        $object = $this->admin->getNewInstance();

        $preResponse = $this->preCreate($request, $object);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //TODO: remove this check for 4.0
            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($object);
            }
            
            $isFormValid = $form->isValid();
            
            $username = $this->getUser()->getUserName();
            // persist if the form was valid and make sure that this is not the default user
            if ($isFormValid && (!$this->isInPreviewMode($request) || $this->isPreviewApproved($request)) && $username !== 'admin') {
                //By S@int-Cyr
                //Get the Branch from the User object
                $branch = $this->getUser()->getBranch();
                if(!$branch){
                    $this->createNotFoundException('Branch not found.');
                }
                //If no user selected, use the current one
                if(!$object->getUser()){
                    $object->setUser($this->getUser());   
                }
                //hydrate each sale with the unitPrice of it related product
                foreach ($object->getSales() as $sale){
                    //Update the stock (deprecated)
                    //This is deprecated because it is the client
                    //$stockHandler->updateStock($branch, $sale->getProduct(), $sale->getQuantity(), true);
                }
                //$object->setTotalAmount($object->getTotalAmount())
                
                //Link the STransaction to the contextual branch
                $object->setBranch($branch);
                //S@int-Cyr end
                $this->admin->checkAccess('create', $object);

                try {
                    $object = $this->admin->create($object);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_create_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($object);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_create_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($object))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // pick the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('Symfony\Bridge\Twig\Extension\FormExtension')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'create',
            'form' => $view,
            'object' => $object,
        ), null);
    }
    
    public function batchActionDelete(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        
        $modelManager = $this->admin->getModelManager();

        $saleTransactions = $selectedModelQuery->execute();
        
        $this->addFlash('sonata_flash_info', 'Removing Sale transaction is not allowed for technical reason see the documentation for more information');
        return new RedirectResponse(
            $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }
    
    public function batchActionReport(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        /*if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }*/

        
        $modelManager = $this->admin->getModelManager();

        $saleTransactions = $selectedModelQuery->execute();
        
        //Get the reportHandler service
        $reportHandler = $this->get('km.report_handler');
        
        $totalSaleAmount = $reportHandler->getSaleAmountOnFly($saleTransactions);
        $totalProfit = $reportHandler->getProfitOnFly($saleTransactions);
        
        return $this->render('TransactionBundle:Default:report_b.html.twig', array('saleTransactions' => $saleTransactions,
                                                                                   'totalSaleAmount' => $totalSaleAmount,
                                                                                    'totalProfit' => $totalProfit,));
        
        //return $this->redirect($this->generateUrl('km_report_b', array('saleTransactions' => $saleTransactions)));
        // do the merge work here
        try {
            foreach ($selectedModels as $selectedModel) {
                //Generate the report 
            }
            
            $modelManager->update($selectedModel);
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', 'flash_batch_activation_error');

            return new RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        $this->addFlash('sonata_flash_success', $this->get('translator')->trans(' successful operations !'));

        return new RedirectResponse(
            $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }
    
    public function batchActionCancel(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {

        
        $modelManager = $this->admin->getModelManager();

        $selectedModels = $selectedModelQuery->execute();

        $saleTransactions = $selectedModelQuery->execute();
        
        //Get the stockHandler service
        //$stockHandler = $this->get('km.stock_handler');
        $branch = $this->getUser()->getBranch();
        
        // do the merge work here
        try {
                foreach ($selectedModels as $selectedModel) {
                    //Generate the report
                    foreach ($selectedModel->getSales() as $sale){
                        //$stockHandler->updateStock($branch, $sale->getProduct(), $sale->getQuantity(), false);
                    }
                    $modelManager->delete($selectedModel);
                    
                }
            
            
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', 'flash_batch_activation_error');

            return new RedirectResponse(
                $this->admin->generateUrl('list', $this->admin->getFilterParameters())
            );
        }

        $this->addFlash('sonata_flash_success', $this->get('translator')->trans(' successful operations !'));

        return new RedirectResponse(
            $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }
    
    
}
