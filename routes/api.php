<?php

use Illuminate\Http\Request;
use App\Models\Sales\Customer;
use App\QuotationConfig;
use App\Models\Common\Order\CustomerBillingDetail;
use App\EcomerceHoliday;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/customer-update','Observer\CustomerObserverController@updatedata');
Route::post('/order-create','Observer\OrderObserverController@order');
Route::post('/order-products','Observer\OrderObserverController@orderproducts');
Route::post('/whproducts-reserve','Observer\WarehouseProductObsever@update_reserve_quantity');
Route::post('/update-order-image','Observer\OrderObserverController@updateorderimage');
Route::post('/create-ecom-order','Observer\OrderObserverController@createecommerceorder');
Route::get('/get-product-stock/{product_id}/{warehouse_id}','Observer\OrderObserverController@getProductStock');
Route::get('/check-if-user-exist/{phone_number}/{password}','Observer\OrderObserverController@checkIfUserExist');
Route::get('/get-new-ecom-customer-id/{cust_id}/{ecom_cust_id}','Observer\OrderObserverController@getNewEcomCustId');

Route::post('/ecom-customer','Observer\OrderObserverController@customer');
Route::get('/check-warehouse-free-ship/{id}','Observer\OrderObserverController@checkWarehouseFreeShip');

Route::post('/get-customer-detail-for-ecom', function(Request $request){
	$getCustomer = Customer::where('phone',$request->phone)->first();
	if($getCustomer)
	{
		return response()->json(['response' => 'Found' ]);
	}
	else
	{
		$quotation_qry    = QuotationConfig::where('section', 'ecommerce_configuration')->first();
        $quotation_config =  unserialize($quotation_qry->print_prefrences);

    	$customer = new Customer;
    	$customer->ecommerce_customer_id = $request->id;
        // 4 id is for Private Customer Category and PC is prefix for Private Customer Category
        $prefix  = 'PC';
        $c_p_ref = Customer::where('category_id',4)->orderby('reference_no','DESC')->first();
        $str     = @$c_p_ref->reference_no;
        if($str  == NULL)
        {
          $str = "0";
        }
        $system_gen_no              =  str_pad(@$str + 1, STR_PAD_LEFT);
        $customer->reference_number = $prefix.$system_gen_no;
        $customer->reference_no     = $system_gen_no;
        $customer->category_id      = 4;


    	// $customer->user_id = $quotation_config['status'][7];
    	$customer->primary_sale_id = $quotation_config['status'][7];;
    	$customer->first_name      = $request->first_name;
    	$customer->last_name       = $request->last_name;
    	$customer->email           = $request->email;
    	$customer->phone           = $request->phone;
        $customer->country         = 217;
    	$customer->address_line_1  = $request->address_line_1;
    	$customer->city            = $request->city;
    	$customer->postalcode      = $request->postalcode;
    	$customer->status          = 0;
    	$customer->ecommerce_customer = 1;
    	$customer->language        = 'en';
    	$customer->save();

        $customer_id                               = $customer->id;
        $customer_billing_address                  = new CustomerBillingDetail;
        $customer_billing_address->customer_id     = $customer_id;
        $customer_billing_address->title           = 'Default Address';
        $customer_billing_address->show_title      = 1;
        $customer_billing_address->billing_email   = $request->email;
        $customer_billing_address->billing_address = $request->address_line_1;
        $customer_billing_address->billing_country = 217;
        $customer_billing_address->billing_city    =  $request->city;
        $customer_billing_address->is_default      = 1;
        $customer_billing_address->status          = 1;
        $customer_billing_address->save();

		return response()->json(['response' => 'Added' ]);
	}
});

Route::get('/get-all-customers-of-private','Observer\OrderObserverController@sendcustomerstoecom');

Route::post('/get-supplychain-print-from-ecom/{id}/{page_type?}/{bank_id?}','Sales\OrderController@exportToPDFIncVatFromEcom');
Route::get('/get-project-versions','Sales\OrderController@getProjectVersions');

Route::get('/get-ecom-holidays', function(){
  $getHolidays = EcomerceHoliday::select('holiday_date')->pluck('holiday_date')->toArray();
  $makeArray = array();
  if(!empty($getHolidays))
  {
    for($i=0; $i<sizeof($getHolidays); $i++)
    {
      array_push($makeArray, date('d-n-Y',strtotime($getHolidays[$i])));
    }
  }
  return $makeArray;
})->name('get-ecom-holidays');
// Route::post('login', 'AuthController@login');
//Api End Points
Route::group([
   'middleware' => ['cross_side','woocommerce'],
], function ($router) {

   Route::post('logout', 'AuthController@logout');
   Route::post('refresh', 'AuthController@refresh');
   Route::post('me', 'AuthController@me');


   Route::get('categories','Api\ProductCategoryController@getCategoriesThrougApi');
   //to get all products
   Route::get('products/{warehouse_id?}','Api\ProductController@index');

   //single product
   Route::get('product/{id}/{warehouse_id?}','Api\ProductController@show');

   //All customers
   Route::get('customers','Api\CustomerController@index');
   //single customer
   Route::get('customer/{id}','Api\CustomerController@show');
   //create customer
   Route::post('add-customer','Api\CustomerController@store');
   //create customer during checkout process
   Route::post('add-customer-during-checkout','Api\CustomerController@addCustomerDuringCheckout');

   //customer billing address and shipping address
   Route::post('add-customer-billing-address','Api\CustomerController@addCustomerBillingAddress');
   Route::post('add-customer-shipping-address','Api\CustomerController@addCustomerShippingAddress');

   //all orders
   Route::get('orders','Api\OrderController@index');
   //single order
   Route::get('order/{id}','Api\OrderController@show');
   //create order
   Route::post('create-order','Api\OrderController@create');

});

Route::get('get_warehouses','Api\PosController@get_warehouses');
Route::post('store','Api\PosController@store');
Route::post('get_categories','Api\PosController@get_categories');
Route::post('get_products','Api\PosController@get_products');
