<?php



namespace TransactionBundle\Service;
use TransactionBundle\Entity\STransaction;
use TransactionBundle\Entity\Sale;
use KmBundle\Entity\Branch;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use TransactionBundle\Service\item;
//use FOS\RestBundle\View\View;

class SaleHandler
{
    //To store the entity manager
    private $em;
    //private $stockHandler;
    private $tokenStorage;
    
    public function __construct($em, $tokenStorage) 
    {
        $this->em = $em;
        //$this->stockHandler = $stockHandler;
        $this->tokenStorage = $tokenStorage;
    }
    
    public function processSaleTransaction(array $inputData, Branch $branch)
    {
        //Create an instance of a SaleTransaction & hydrate it with the branch and the total amount of the transaction
        $stransaction = new STransaction();
        $stransaction->setTotalAmount($inputData['total']);
        $stransaction->setBranch($branch);
        //Link the employee to the transaction
        $user = $this->tokenStorage->getToken()->getUser();
        $stransaction->setUser($user);
        
        //Loop over each sale
        foreach ($inputData['order'] as $s){
            //create an instance of a sale
            $sale = new Sale();
            //Link the sale to the related product
            $product = $this->em->getRepository('TransactionBundle:Product')->find($s['item']['id']);
            $sale->setProduct($product);
            $sale->setProfit();
            //Call the stocktHandler service to update the stock
            //This is call is deprecated and generate error since stock management is no more supportated on client side
            //$this->stockHandler->updateStock($branch, $product, $s['orderedItemCnt'], true);
            //Set the quantity
            $sale->setQuantity($s['orderedItemCnt']);
            $sale->setAmount($s['totalPrice']);
            $sale->setStransaction($stransaction);
            $this->em->persist($sale);
			
			$items [] = new item($product->getName()." #".$sale->getQuantity(),
									 $sale->getQuantity()*$product->getUnitPrice()
										 );
        }
		
        $subtotal = new item('Subtotal (No Tax)', $inputData['total'] - (($inputData['total']/100)*19));
                        $tax = new item('A local tax', '19%');
                        $total = new item('Total', $inputData['total'], true);

        //firstly, make sure the printer is connected, in which case the file /dev/usb/* should exist
        if(file_exists('/dev/usb/lp0')){
            //Start of TM-T20
            try {
            // Enter the share name for your USB printer here
            //$connector = new WindowsPrintConnector("TM-T20");
            $connector = new FilePrintConnector("/dev/usb/lp0");
            $printer = new Printer($connector);

            /* Information for the receipt */

            /* Date is kept the same for testing */
            $date = date('l jS \of F Y h:i:s A');
            //$date = "Monday 6th of April 2015 02:56:25 PM";
            /* Start the printer */

            $logo = EscposImage::load(getcwd()."/images/logo_receipt.jpg", false);
            //$logo_company = EscposImage::load(getcwd()."/images/logo_company.png", false);
            $printer = new Printer($connector);

            /* Name of shop */
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            //$printer -> text("New World Telecom Cameroon Ltd.\n");
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            //$printer -> graphics($logo_company);
            $printer -> setDoubleStrike(false);
            $printer -> setBarcodeWidth(20);
            $printer -> setBarcodeHeight(30);
            //$printer -> barcode("987654321");
            $printer -> selectPrintMode();
            $printer -> feed(1);
            $printer -> text("SOCIETE HAMADJODA & FRERES SARL.\n");
            $printer -> text("Region Ouaka Ville de Bambari.\n");
            $printer -> text("Tel:+(236) 75 90 50 90 / 72 90 50 90\n");
            $printer -> feed();
            /* Title of receipt */
            $printer -> setEmphasis(true);
            $printer -> text("REÇU DE TRANSACION\n");
            $printer -> setEmphasis(false);
            /* Items */
            $printer -> setJustification(Printer::JUSTIFY_LEFT);
            $printer -> setEmphasis(true);
            $printer -> text(new item('', 'FCFA'));
            $printer -> setEmphasis(false);
            foreach ($items as $item) {
            $printer -> text($item);
            }
            $printer -> setEmphasis(true);
            $printer -> text($subtotal);
            $printer -> setEmphasis(false);
            $printer -> feed();
            /* Tax and total */
            $printer -> text($tax);
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> text($total);
            $printer -> selectPrintMode();
            /* Footer */
            $printer -> feed(1);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            //$printer -> text("Caissier: ".$stransaction->getUser()->getName()."\n");
            $printer -> text("NB: Les articles vendus ne sont ni repris,  ni   rembourses\n");
            $printer -> text("Merci de nous avoir fait confiance.\n");
            //$printer -> text("For trading hours, please visit example.com\n");
            $printer -> feed(1);
            /* Print top logo */
            $printer -> text($date . "\n");
            $printer -> text("Caissier: Nouradine GONI\n");
            $printer -> pulse();
            $printer->qRcode("Nouradine GONI");
            //$printer->barcode("12345");
            //$printer->setBarcodeHeight(30);
            //$printer->setBarcodeWidth(10);
            //$priner->setBarcodeTextPosition();
            //$printer->pdf417Code("Saint");

            $printer -> setJustification(Printer::JUSTIFY_RIGHT);
            $printer -> graphics($logo);

            /* Cut the receipt and open the cash drawer */
            $printer -> cut();

            $printer -> close();
            /* A wrapper to do organise item names & prices into columns */
            } catch(Exception $e) {
                    echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
            }
            //End of TM-T20
        }
        //Persist its in DB.
        $this->em->persist($stransaction);
        $this->em->flush();
    }
}
