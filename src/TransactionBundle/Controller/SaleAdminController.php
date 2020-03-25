<?php

namespace TransactionBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

class SaleAdminController extends CRUDController
{
    public function batchActionDelete(ProxyQueryInterface $selectedModelQuery, Request $request = null)
    {
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        
        $modelManager = $this->admin->getModelManager();

        $saleTransactions = $selectedModelQuery->execute();
        
        $this->addFlash('sonata_flash_info', 'Removing Sale(s) is not allowed for technical reason see the documentation for more information');
        return new RedirectResponse(
            $this->admin->generateUrl('list', $this->admin->getFilterParameters())
        );
    }

}
