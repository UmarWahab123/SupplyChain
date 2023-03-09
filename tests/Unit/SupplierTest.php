<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Controllers\Purchasing\SupplierController;
use App\User;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierContacts;
use App\Models\Common\SupplierAccount;
use Illuminate\Http\Request;

class SupplierTest extends TestCase
{
    public function setUp() : void
	{   parent::setUp();
     	$user = User::find(21);
		$this->actingAs($user);
    }
    
    public function test_Save_Supp_Data_im_Supp_Detail()
    {
    	$request = new Request();

    	//manage request data accoeding to field entered
    	$request->replace([
    		'supplier_id' => '82',
			// 'reference_name' => 'Calafate'
			'company' => 'Mariscos Calafate S.L'
			// 'email' => 'maricoscalafate1@gmail.com'
			// 'phone' => '123'
			// 'address_line_1' => 'Paseo Maritimo 54, Bajo'
			// 'country' => '205'
			// 'state' => '3255'
			// 'city' => 'Adra (Almeria)'
			// 'postalcode' => '04770'
			// 'currency_id' => '3'
			// 'credit_term' => '3'
			// 'tax_id' => '12456'
    	]);
    	$order = new SupplierController();
    	$response = $order->saveSuppDataSuppDetail($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $supplier = Supplier::find($request->supplier_id);
        // Uncomment the line acording to request data and comment others
        //reference_name
        // $this->assertEquals($request->reference_name, $supplier->reference_name);
        //company
        $this->assertEquals($request->company, $supplier->company);
        //email
        // $this->assertEquals($request->email, $supplier->email);
        //phone
        // $this->assertEquals($request->phone, $supplier->phone);
        //address_line_1
        // $this->assertEquals($request->address_line_1, $supplier->address_line_1);
        //country
        // $this->assertEquals($request->country, $supplier->country);
        //state
        // $this->assertEquals($request->state, $supplier->state);
        //city
        // $this->assertEquals($request->city, $supplier->city);
        //postalcode
        // $this->assertEquals($request->postalcode, $supplier->postalcode);
        //currency_id
        // $this->assertEquals($request->currency_id, $supplier->currency_id);
        //credit_term
        // $this->assertEquals($request->credit_term, $supplier->credit_term);
        //tax_id
        // $this->assertEquals($request->tax_id, $supplier->tax_id);
    }

    public function test_Save_Supp_Contacts_Data()
    {
    	$request = new Request();

    	//manage request data accoeding to field entered
    	$request->replace([
    		'id' => '63',
    		'supplier_id' => '82',
			'name' => 'Paula'
			// 'sur_name' => 'Casas'
			// 'email' => 'pgarcia@omedoil.com'
			// 'telehone_number' => '+34 699 313 240'
			// 'postion' => 'Sales Manager'
    	]);
    	$order = new SupplierController();
    	$response = $order->saveSuppContactsData($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $supplier_contact = SupplierContacts::where('id',$request->id)->where('supplier_id',$request->supplier_id)->first();
        // Uncomment the line acording to request data and comment others
        //name
        $this->assertEquals($request->name, $supplier_contact->name);
        //sur_name
        // $this->assertEquals($request->sur_name, $supplier_contact->sur_name);
        //email
        // $this->assertEquals($request->email, $supplier_contact->email);
        //telehone_number
        // $this->assertEquals($request->telehone_number, $supplier_contact->telehone_number);
        //position
        // $this->assertEquals($request->position, $supplier_contact->position);
    }
    
    public function test_Delete_Supp_Contacts_Data_Record()
    {
    	$request = new Request();
    	$request->replace([
    		'id' => '104'
    	]);
    	$order = new SupplierController();
    	$response = $order->deleteSupplierContact($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $supplier_contact = SupplierContacts::find($request->id);
        $this->assertEquals(null, $supplier_contact);
    }

    public function test_Save_Supp_Accounts_Data()
    {
    	$request = new Request();

    	//manage request data accoeding to field entered
    	$request->replace([
    		'id' => '26',
    		'supplier_id' => '82',
			'bank_name' => 'abc'
			// 'bank_address' => 'abcd'
			// 'account_name' => 'xyz'
			// 'account_no' => '240'
			// 'swift_code' => '123'
    	]);
    	$order = new SupplierController();
    	$response = $order->saveSuppAccountData($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $supplier_contact = SupplierAccount::where('id',$request->id)->where('supplier_id',$request->supplier_id)->first();
        // Uncomment the line acording to request data and comment others
        //bank_name
        $this->assertEquals($request->bank_name, $supplier_contact->bank_name);
        //bank_address
        // $this->assertEquals($request->bank_address, $supplier_contact->bank_address);
        //account_name
        // $this->assertEquals($request->account_name, $supplier_contact->account_name);
        //account_no
        // $this->assertEquals($request->account_no, $supplier_contact->account_no);
        //swift_code
        // $this->assertEquals($request->swift_code, $supplier_contact->swift_code);
    }

    public function test_Delete_Supp_Accounts_Data_Record()
    {
    	$request = new Request();
    	$request->replace([
    		'id' => '29'
    	]);
    	$order = new SupplierController();
    	$response = $order->deleteSupplierAccount($request);
    	$result = json_decode($response->getContent());
        $this->assertEquals(true,$result->success);

        $supplier_contact = SupplierAccount::find($request->id);
        $this->assertEquals(null, $supplier_contact);
    }
}
