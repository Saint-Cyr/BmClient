<?php

namespace TransactionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TransactionBundle\Entity\Product;
use UserBundle\Entity\User;

class ApiController extends Controller
{
    /*this methode post csv products to the sychronizer
     * 
     */
    public function postProductsAction(Request $request)
    {
        //Get the CSV type (Product or User) from the input parameter
        //Get the input data sent by the front application
        $inputData = json_decode($request->getContent(), true);
        $csvType = $request->get('csv_type');
        //var_dump($request->get('csv_type'));
        //var_dump($request->get('language'));exit;
        //We'll surely need to interact with DB
        $em = $this->getDoctrine()->getManager();
        //Read products list from a CSV file and save in DB if aplicable
        if(true){
             $file = $request->files->all()["file"];
            // If a file was uploaded
            if(!is_null($file)){
               // rename the file but keep the extension
               $inputFileName = "one.".$file->getClientOriginalExtension();
               $path = getcwd().'/';
               $file->move($path, $inputFileName); // move the file to a path
               $status = array('status' => "success","fileUploaded" => true);
            }
            
            $inputFileType = 'CSV';
            $sheetname = 'Data Sheet #2';
            
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $spreadsheet = $reader->load($inputFileName);
            
            //Get the second sheet for the first content an introduction message
            $spreadsheet->getSheetByName('PRODUCT LIST');

            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            //interesting to know whether there was some error
            $unrecorded = 0;
            //Count the number of recorded in order to display it back to the end user
            $recorded = 0;
            //Save in DB
            foreach ($rows as $key => $r){
                if($key != 0 && $csvType == 'Products'){
                    //Save this rows in DB
                    //Make sure it has not yet been input in DB
                    $product_id = $r[0];
                    $product_name = $r[1];
                    $product_uniprice = $r[2];
                    $product_whole_sale_price = $r[3];
                    $product_barcode = $r[4];
                    //Check whether the current product already exist in DB.
                    $dbProduct1 = $em->getRepository('TransactionBundle:Product')
                                    ->findOneBy(array('barcode' => $product_barcode));
                    $dbProduct2 = $em->getRepository('TransactionBundle:Product')
                                    ->findOneBy(array('name' => $product_name));
                    //Make sure the entire product or only his name does not yet exist in DB.
                    //Update on sunday 25th August 2019: and make sure also any of the comming product 
                    //editable property has not been edited
                    if($dbProduct1 || $dbProduct2){
                        $unrecorded ++;
                    }else{
                        //create new product object
                        $newProduct = new Product();
                        //Make sure to set the onlineId 
                        $newProduct->setOnlineId($product_id);
                        $newProduct->setName($product_name);
                        $newProduct->setUnitPrice($product_uniprice);
                        $newProduct->setWholeSalePrice($wholeSalePrice = null);
                        $newProduct->setBarcode($product_barcode);
                        $em->persist($newProduct);
                        $recorded++;
                    }
                }elseif($key != 0 && $csvType == 'Users'){
                    //Fetch variables from the income data
                        $username = $r[0];
                        $email = $r[1];
                        $password = $r[2];
                        //Make sure this user does not yet exist in DB before insert it.
                        //Check whether the current product already exist in DB.
                        $dbUser = $em->getRepository('UserBundle:User')
                                    ->findOneBy(array('email' => $email));
                        if(!$dbUser)
                        {
                            //Get the default Branch from DB. This is necessary to perform sale transaction
                            $branch = $em->getRepository('KmBundle:Branch')
                                    ->findOneBy(array('name' => 'BRANCH_1'));
                            //create new product object
                            $user = new User();
                            //In order to avoid error when performing STransaction, user must be linked to a branch
                            //this is right to link all the user to the current branch because this is where the App
                            //have been installed
                            $user->setBranch($branch);
                            $user->setUsername($username);
                            $user->setEmail($email);
                            $user->setPassword($password);
                            $user->setEmailCanonical($email);
                            $user->setEnabled(true);
                            $user->setRoles(array('ROLE_SUPER_ADMIN'));

                            $em->persist($user);
                            $recorded++;
                        }else{
                            $unrecorded++;
                        }
                }
            }
            
            $em->flush();
            
            return array("#Recorded: ".$recorded." #Unrecorded: ".$unrecorded);
        }
    }
    
    public function postSaleTransactionAction(Request $request)
    {
        //Get the input data sent by the front application
        $inputData = json_decode($request->getContent(), true);
        //Get the branch from the user object
        $user = $this->get('security.token_storage')->getToken()->getUser();
        //Make sure this is not the default user otherwise, denied  transaction
        if($user->getUserName() == 'admin'){
            return new Response('[ERROR] Default user not allowed.');
        }
        
        $branch = $user->getBranch();
        $data = $inputData['data'];
        //Make sure $data is valide(precision: order where not empty)
        if(count($data['order']) == 0){
            $response = new Response($this->get('translator')->trans('[ERROR] No order detected.'));
            return $response;
        }
        //Get the STransaction handler service
        $saleHandler = $this->get('transaction.sale_handler');
        //Process the sale transaction
        $saleHandler->processSaleTransaction($data, $branch);
        
        $response = new Response($this->get('translator')->trans('Successfull transaction!'));
                            
        $response->headers->set('Access-Control-Allow-Origin', 'http://127.0.0.1');
        return $response;
    }
}