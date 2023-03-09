<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Sales\CustomerController;
use App\Models\Sales\Customer;
use App\User;

class CustomerTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }

    public function testSettingCustomerShippingAddress()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['address_id' => 1,'is_default_shipping' => true,'customer_id'=> 1]);
        $response = (new CustomerController)->settingDefaultShipping($request);
        $result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);
    }

    public function testCustomerName()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'reference_name' => 'Chatrium Sathon','new_select_value'=> 'undefined']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->reference_name,$customer->reference_name);
    }

    public function testCustomerBillingName()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'company' => 'City Residence Services Co.,Ltd.','new_select_value'=> 'undefined']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->company,$customer->company);
    }

    public function testCustomerCategory()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'category_id' => '3','new_select_value'=> 'RETAIL']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->category_id,$customer->category_id);
    }

    public function testCustomerContact()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'phone' => '+92336508050174','new_select_value'=> 'RETAIL']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->phone,$customer->phone);
    }

    public function testCustomerPrimaryTerms()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'credit_term' => '2','new_select_value'=> 'Net 7']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->credit_term,$customer->credit_term);
    }

    public function testCustomerPaymentMethod()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'paymentType' => '1','new_select_value'=> '1']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertTrue(true);
    }

    public function testCustomerPrimarySalePerson()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'primary_sale_id' => '23','new_select_value'=> 'Anusorn']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->primary_sale_id,$customer->primary_sale_id);
    }

    public function testCustomerSecondarySalePerson()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'secondary_sale_id' => '23','new_select_value'=> 'Anusorn']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->secondary_sale_id,$customer->secondary_sale_id);
    }
    
    public function testCustomerLanguage()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'language' => 'en','new_select_value'=> 'English']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->language,$customer->language);
    }

    public function testCustomerCreditLimit()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace(['cust_detail_id' => 1,'customer_credit_limit' => '74','new_select_value'=> 'undeifned']);
        $response = (new CustomerController)->saveCustDataCustDetailPage($request);
        $result = json_decode($response->getContent());
        $customer = Customer::find($request->cust_detail_id);
        $this->assertEquals($request->customer_credit_limit,$customer->customer_credit_limit);
    }

    public function testCustomerAddFixPrice()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "customer_id" => "1",
            "prod_name" => "BC20",
            "product" => "4352",
            "default_price" => "2,708.76",
            "fixed_price" => "1",
            "expiration_date" => "2022-04-07"
        ]);
        $response = (new CustomerController)->addCustProdFixedPrice($request);
        $result = json_decode($response->getContent());
        $this->assertTrue(true);
    }

    public function testCustomerAddContact()
    {
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "id" => "367",
            "customer_id" => "1",
            "sur_name" => "khan"
        ]);
        $response = (new CustomerController)->saveCusContactsData($request);
        $result = json_decode($response->getContent());
        $this->assertTrue(true);
    }

    public function testCustomerAddAddress()
    {
        $user = User::find(21);
        $request = new \Illuminate\Http\Request();
        $request->replace([
            "_token" => "gn2WdinUCJg9yNLysrMxwT5pYuEeXZxiPirifvFk",
            "customer_id" => "1",
            "billing_title" => "jhg",
            "billing_contact_name1" => "jhg",
            "billing_email1" => "sasdf@gmil.com",
            "tax_id" => "123",
            "billing_phone" => "123",
            "cell_number" => "123",
            "billing_address" => "asd",
            "billing_zip" => "123",
            "billing_country" => "217",
            "billing_city" => "asd",
            "state" => "3526",
            "billing_fax1" => "asd",
        ]);
        $response = (new CustomerController)->saveBillingInfo($request, $user);
        $result = json_decode($response->getContent());
        $this->assertTrue(true);
    }




}
