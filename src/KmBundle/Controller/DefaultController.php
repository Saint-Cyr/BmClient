<?php

namespace KmBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Hackzilla\BarcodeBundle\Utility\Barcode;

class DefaultController extends Controller
{
    public function synchronizerAction()
    {
        $synchronizerHandler = $this->get('km.synchronizer_handler');
        echo $synchronizerHandler->start();exit;
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
    
    public function alertStockAction()
    {
        //Get all the branch
        $em = $this->getDoctrine()->getManager();
        $branches = $em->getRepository('KmBundle:Branch')->findAll();
        
        return $this->render('KmBundle:Default:alert_stock.html.twig', array('branches' => $branches));
    }
    
    public function stockUpdateAction()
    {
        //Get all the branch
        $em = $this->getDoctrine()->getManager();
        //Get the branch
        $branch = $this->getUser()->getBranch();
        //Get all the updated stock
        $stocked = $em->getRepository('TransactionBundle:Stock')->getStocked($branch);
        $destocked = $em->getRepository('TransactionBundle:Stock')->getDestocked($branch);
        
        return $this->render('KmBundle:Default:stock_update.html.twig',
                array('stocked' => $stocked, 'destocked' => $destocked));
    }
    
    public function dashboardAction()
    {
        //Get the statistic handler service
        $statisticHandler = $this->get('km.statistic_handler');
        //Get the entity manager
        $em = $this->getDoctrine()->getManager();
        //Get all the branches
        $branches = $em->getRepository('KmBundle:Branch')->findAll();
        //Prepare resum for all branch
        $totalSale = null;
        $totalProfit = null;
        $totalExpenditure = null;
        $totalBalance = null;

        foreach ($branches as $b){
            //Hydrate every branch
            $statisticHandler->setBranchFlyData($b);
            $totalSale = $totalSale + $b->getFlySaleAmount();
            $totalExpenditure = $totalExpenditure + $b->getFlyExpenditureAmount();
            $totalProfit = $totalProfit + $b->getFlyProfitAmount();
            $totalBalance = $totalBalance + $b->getFlyBalanceAmount();
        }

        //Get all the sale transaction amount for every month
        $stransactions = $statisticHandler->getSaleByMonth();
        /*//Get the resum for all branches
        $totalSale = $statisticHandler->getSale();
        $totalProfit = $statisticHandler->getProfit();
        $totalExpenditure = $statisticHandler->getExpenditure();
        $totalBalance = $statisticHandler->getBalance();*/

        return $this->render('/admin/vendor_dashboard.html.twig', array('stransactions' => $stransactions,
                                                                        'branches' => $branches,
                                                                        'totalSale' => $totalSale,
                                                                        'totalProfit' => $totalProfit,
                                                                        'totalBalance' => $totalBalance,
                                                                        'totalExpenditure' => $totalExpenditure,));
    }
    
    public function branchesAction()
    {
        $branches = $this->getDoctrine()->getManager()->getRepository('KmBundle:Branch')->findAll();
        return $this->render('KmBundle:Default:branches.html.twig', array('branches' => $branches));
    }
    
    public function validateUpdateStockAction($update)
    {
        $em = $this->getDoctrine()->getManager();
        //Case of stockage
        if($update == 1){
            $stocked = $em->getRepository('TransactionBundle:Stock')->findBy(array('stocked' => true));
            foreach ($stocked as $stock){
                $stock->setStocked(false);
            }
        }else{
            $destocked = $em->getRepository('TransactionBundle:Stock')->findBy(array('destocked' => true));
            foreach ($destocked as $stock){
                $stock->setDestocked(false);
            }
        }
        
        $em->flush();
        
        return $this->redirect($this->generateUrl('transaction_pos_barcode'));
    }
    
    public function reportAAction()
    {
        //return $this->render('/pages/tables.html.twig');
        //return $this->render('/pages/form.html.twig');
        return $this->render('TransactionBundle:Default:report_a.html.twig');
        //return $this->render('TransactionBundle:Default:reciep_a4.html.twig');
    }
    
    public function reportBAction()
    {
        return $this->render('TransactionBundle:Default:report_b.html.twig');
    }
    
    public function settingAction()
    {
        return $this->render('/pages/setting.html.twig');
    }
    
    public function BarcodeTestAction()
    {
        //Get the product from the DB in order to send it to the view
        $em = $this->getDoctrine()->getManager();
        //Get all the products
        $products = $em->getRepository('TransactionBundle:Product')->findBy(array('locked' => false));
        
        return $this->render('TransactionBundle:Default:barecode_test.html.twig', array('products' => $products));
    }
    
    public function indexAction($barcode)
    {
        //Get the product from the DB in order to send it to the view
        $em = $this->getDoctrine()->getManager();
        
        $product = $em->getRepository('TransactionBundle:Product')->findOneBy(array('barcode' => $barcode));
        if(!$product){
            throw $this->createNotFoundException('product of barcode: '.$barcode.' does not exist in DB.');
        }
        $im = imagecreatefromstring($this->getImage($barcode));
        
        imagepng($im, getcwd().'/barcode/'.$barcode.'.png');
        
        return $this->render('TransactionBundle:Default:barecode_list.html.twig', array('barecode' => $barcode,
                                                                                        'product' => $product));  
    }
    
    public function getBarCodeAction($code)
    {
        $barcode = $this->get('hackzilla_barcode');
        $barcode->setMode(Barcode::MODE_PNG);
        
        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$code.'.png"'
        );
        
        //return new Response($code);
        return new Response($barcode->outputImage($code), 200, $headers);
    }
    
    /**
     * Return image in a zip
     */
    public function barcodeZipAction($code)
    {
        $isbns = array(
            '978085934063',
            '500015941539',
            '978085956063',
            '500015921539',
        );

        $zipDir = sys_get_temp_dir();

        if (substr($zipDir, -1) !== '/') {
            $zipDir .= '/';
        }

        $zip = new \ZipArchive();
        $zipName = 'barcodes-' . time() . ".zip";
        $zip->open($zipDir . $zipName, \ZipArchive::CREATE);

        foreach ($isbns as $i => $isbn) {
            $barcode = \trim($isbn);
            $zip->addFromString($barcode . '.png', $this->getImage($barcode));
        }

        $zip->close();

        $response = new Response();
        $response->setContent(readfile($zipDir . $zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-disposition', 'attachment; filename="' . $zipName . '"');
        $response->headers->set('Content-Length', filesize($zipDir . $zipName));

        return $response;
    }

    private function getImage($ean)
    {
        ob_start();

        $barcodeGenerator = new Barcode();
        $barcodeGenerator->setMode(Barcode::MODE_PNG);
        $barcodeGenerator->outputImage($ean);

        $contents = ob_get_clean();

        return $contents;
    }
}
