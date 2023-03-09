<?php

namespace App\Http\Controllers\Ecom;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Product;
use App\Models\Common\Configuration;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\ProductImage;
use App\Models\Common\ProductType;
use App\QuotationConfig;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseZipCode;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Status;


class EcomApiController extends Controller
{
	
    public function updateproducts(){
    	$products = Product::where('ecommerce_enabled',1)->get();
    	$link = '/api/fetch-data-su-product';
    	$this->curl_call($link, $products);
    }

	public function updateconfiguration(){
    	$configuration = Configuration::all();
		$link = '/api/fetch-data-su-configuration';
		$this->curl_call($link, $configuration);	
    }	

    public function updatecustomercategory(){

    	$customer_cat = CustomerCategory::all();
		$link = '/api/fetch-data-su-customer-category';
		$this->curl_call($link, $customer_cat);	
	}

	public function updatecustomertypecategorymargin(){
    	$cus_type_cat_mar = CustomerTypeCategoryMargin::all();
    	$link = '/api/fetch-data-su-customer-type-category-margin';
		$this->curl_call($link, $cus_type_cat_mar);	
	}
    
    public function customertypeproductmargin(){
    	$cus_type_pro_mar = CustomerTypeProductMargin::all()->chunk(1000);
    	foreach($cus_type_pro_mar as $prodata){
			$link = '/api/fetch-data-su-customer-type-product-margin';
			$this->curl_call($link, $prodata);	
		}
	}
  	
  	public function updateproductcategory(){
    	$pro_category = ProductCategory::all();
		$link = '/api/fetch-data-su-product-category';
		$this->curl_call($link, $pro_category);	
	}
    
    public function updateproductcustomerfixedprice(){
    	$pro_cus_fixed_price = ProductCustomerFixedPrice::all();
		$link = '/api/fetch-data-su-product-customer-fixed-price';
		$this->curl_call($link, $pro_cus_fixed_price);	
	}
    
    public function updateproductfixedprice(){
    	$pro_fixed_price = ProductFixedPrice::all();
		$link = '/api/fetch-data-su-product-fixed-price';
		$this->curl_call($link, $pro_fixed_price);	
    }

    public function updateproductimage(){
    	$pro_image = ProductImage::all();
		$link = '/api/fetch-data-su-product-image';
		$this->curl_call($link, $pro_image);	
	}
    	
	public function producttype(){
    	$pro_type = ProductType::all();
		$link = '/api/fetch-data-su-product-type';
		$this->curl_call($link, $pro_type);	
    	}

    public function updatequotationconfig(){
    	$quo_config = QuotationConfig::all();
		$link = '/api/fetch-data-su-quotation-config';
		$this->curl_call($link, $quo_config);	
    }

    public function updatewarehouse(){
    	$warehouse = Warehouse::where('status',1)->get();
    	$link = '/api/fetch-data-su-warehouse';
		$this->curl_call($link, $warehouse);	
    }
    
    public function updatewarehousezipcode(){
    	$whouse_zipcode = WarehouseZipCode::all();
    	$link = '/api/fetch-data-su-warehouse-zipcode';
		$this->curl_call($link, $whouse_zipcode);	
    }

    public function updatewarehouseproduct(){
    	$whouse_product = WarehouseProduct::all();
		$link = '/api/fetch-data-su-warehouse-product';
		$this->curl_call($link, $whouse_product);	
    }


    public function updatescstatuses(){
        $statuses = status::all();
        $link = '/api/fetch-data-su-statuses';
        $this->curl_call($link, $statuses);   
    }

    public function curl_call($link, $data){
     	$url =  config('app.ecom_url').$link;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 08f91779-330f-bf8f-1a64-d425e13710f9",
          ),
        ));

        $response = curl_exec($curl);
        dd($response);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }
}
