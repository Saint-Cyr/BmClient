<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /*
     * By Saint-Cyr MAPOUKA BSol Project Manager 
     * info : ceo@itechcar.com
     * This test cases are for the default account
     * provided by BSol Client when installed freshly on
     * a customer PC.
     */
    public function testDefaultAccount()
    {
        //Open a webbrowser
        $client1 = static::createClient();
        //Make sure the login page is display correctly
        $crawler = $client1->request('GET', '/login');
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertContains('Sign In', $client1->getResponse()->getContent());
        //Login as a seller
        $crawler = $this->login($crawler, $client1, 'admin', 'admin');
       
        //Make sure that everything is fine after redirecting to the front page (/)
        $crawler = $client1->request('GET', '/');
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertContains('Documentation', $client1->getResponse()->getContent());
        $this->assertContains('Salomon', $client1->getResponse()->getContent());
        $this->assertContains('POS #1', $client1->getResponse()->getContent());
        
        //Go to the POS 1 page (/pos_barcode)
        $crawler = $client1->request('GET', '/pos_barcode');
        //Make sure that everything is fine.
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertContains('FCFA', $client1->getResponse()->getContent());
        $this->assertContains('CHECKOUT', $client1->getResponse()->getContent());
        
        //Make sure the default user cannot make a sales transaction from #POS3
        $crawler = $client1->request('GET', '/admin/transaction/stransaction/create');
        //Make sure that everything is fine.
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Go to the D1 page (/admin)
        $crawler = $client1->request('GET', '/admin/dashboard');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Go to the D1 page (/transaction/product/list)
        $crawler = $client1->request('GET', '/admin/transaction/product/list');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Go to the D1 page (/transaction/stransaction/list)
        $crawler = $client1->request('GET', '/admin/transaction/stransaction/list');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Try to make a sale transaction can be perform
        $crawler = $client1->request('POST', '/sales/transactions');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertEquals('[ERROR] Default user not allowed.', $client1->getResponse()->getContent());
    }
    
    public function testSeller()
    {
        //Open a webbrowser
        $client1 = static::createClient();
        //Make sure the login page is display correctly
        $crawler = $client1->request('GET', '/login');
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertContains('Sign In', $client1->getResponse()->getContent());
        //Login as a seller
        $crawler = $this->login($crawler, $client1, 'seller', 'test');
       
        //Make sure that everything is fine after redirecting to the front page (/)
        $crawler = $client1->request('GET', '/');
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertContains('Documentation', $client1->getResponse()->getContent());
        $this->assertContains('Salomon', $client1->getResponse()->getContent());
        $this->assertContains('POS #1', $client1->getResponse()->getContent());
        
        //Go to the POS 1 page (/pos_barcode)
        $crawler = $client1->request('GET', '/pos_barcode');
        //Make sure that everything is fine.
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertContains('FCFA', $client1->getResponse()->getContent());
        $this->assertContains('CHECKOUT', $client1->getResponse()->getContent());
        
        //Make sure the default user cannot make a sales transaction from #POS3
        $crawler = $client1->request('GET', '/admin/transaction/stransaction/create');
        //Make sure that everything is fine.
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Go to the D1 page (/admin)
        $crawler = $client1->request('GET', '/admin/dashboard');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Go to the D1 page (/transaction/product/list)
        $crawler = $client1->request('GET', '/admin/transaction/product/list');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Go to the D1 page (/transaction/stransaction/list)
        $crawler = $client1->request('GET', '/admin/transaction/stransaction/list');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        
        //Try to make a sale transaction can be perform
        $crawler = $client1->request('POST', '/sales/transactions');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertEquals('[ERROR] No order detected.', $client1->getResponse()->getContent());
    }
    
    public function testAsSuperAdmin()
    {
        //Open a webbrowser
        $client1 = static::createClient();
        //Make sure the login page is display correctly
        $crawler = $client1->request('GET', '/login');
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertContains('Sign In', $client1->getResponse()->getContent());
        //Login as a seller
        $crawler = $this->login($crawler, $client1, 'super-admin', 'test');
        
        //Try to make a sale transaction can be perform
        $crawler = $client1->request('POST', '/sales/transactions');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
        $this->assertEquals('[ERROR] No order detected.', $client1->getResponse()->getContent());
        
        //Go to the D1 page (/transaction/sale/list)
        $crawler = $client1->request('GET', '/admin/transaction/sale/list');
        //Make sure that everything is fine after redirecting to the front page (/pos_barcode)
        $this->assertEquals(200, $client1->getResponse()->getStatusCode());
    }
    
    /*
     * this methode have 4 parameters:
     * @crawler : the robot
     * @client: the browser
     * @userName: the username 
     * @password: the password
     */
    public function login($crawler, $client1, $userName = 'seller', $password = 'test')
    {
        //Fill the login form with the right credentials from the fixtures
        $form = $crawler->selectButton('btn_create_and_create')->form(array(
                                                            '_username'  => $userName,
                                                            '_password'  => $password,
                                                        ));
        //Submit the form in order to login
        $client1->submit($form);
        //The system redirect to the front page (/)
        $crawler = $client1->followRedirect();
    }
}
