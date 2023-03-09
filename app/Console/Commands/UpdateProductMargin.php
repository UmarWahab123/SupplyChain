<?php

namespace App\Console\Commands;

use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Flag;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\SupplierProducts;
use App\ProductHistory;
use Illuminate\Console\Command;
use Auth;

class UpdateProductMargin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product_margin:UpdateProductMargin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will update all the products margin';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
         $flag_table = Flag::where('type','product_margin_update')->where('status',0)->first();
        if($flag_table != null)
        {
        $this->info('*****************************************');
        $this->info('Process Started at '.date('Y-m-d H:i:s'));
        $this->info('******************************************');
        $currency_id = $flag_table->currency_id;
        if($currency_id != null)
        {
          $product_cat = ProductCategory::find($currency_id);
          $getProducts = Product::where('category_id',$currency_id)->get();
        }
        $flag_table->total_rows = $getProducts->count();
        $flag_table->save();
         if($getProducts->count() > 0)
        {
        foreach ($getProducts as $product) 
        {
          $customer_categories = CustomerCategory::where('is_deleted','!=',1)->get();    // getting all Customer Categories
          foreach ($customer_categories as $cust_cat) 
          {
            $productMargins = CustomerTypeProductMargin::where('customer_type_id',$cust_cat->id)->where('product_id', $product->id)->first();
            $getCatMargins = CustomerTypeCategoryMargin::where('category_id',$product_cat->id)->where('customer_type_id',$cust_cat->id)->first();

            if($productMargins) // if exist then update the product margins
            {
              $productMargins->default_value  = $getCatMargins->default_value;
              $productMargins->save();
            }
            else               // create new product margin
            {
              $categoryMarginsNew = new CustomerTypeProductMargin;
              $categoryMarginsNew->product_id       = $product->id;
              $categoryMarginsNew->customer_type_id = $cust_cat->id;
              $categoryMarginsNew->default_margin   = $getCatMargins->default_margin;
              $categoryMarginsNew->default_value    = $getCatMargins->default_value;
              $categoryMarginsNew->save();
            }

            $productFixedPrice = ProductFixedPrice::where('customer_type_id',$cust_cat->id)->where('product_id', $product->id)->first();
            if($productFixedPrice)  // if exist then update the ProductFixedPrice
            {
              // do nothing
            }
            else                    // create new ProductFixedPrice
            {
              $productFixedPrices = new ProductFixedPrice;
              $productFixedPrices->product_id       = $product->id;
              $productFixedPrices->customer_type_id = $cust_cat->id;
              $productFixedPrices->fixed_price      = 0;
              $productFixedPrices->expiration_date  = NULL;
              $productFixedPrices->save();
            }
          }

            // Now updating product pricing according to the categories
            $getProduct = Product::find($product->id);
            $old_selling_price = $getProduct->selling_price;
            $getProduct->hs_code          = $product_cat->hs_code;
            $getProduct->import_tax_book  = $product_cat->import_tax_book;
            $getProduct->vat              = $product_cat->vat;
            
            if($getProduct->supplier_id != 0)  // if product default/last supplier exist
            {
              $getProductDefaultSupplier = SupplierProducts::where('product_id',$getProduct->id)->where('supplier_id',$getProduct->supplier_id)->first();
              if($getProductDefaultSupplier->import_tax_actual == null) // if import tax actual is not defined then this condition will execute
              {
                // new
                $importTax = $getProduct->import_tax_book;

                // this is the price of after conversion for THB
                $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                $buying_price_in_thb = $getProductDefaultSupplier->buying_price_in_thb;

                $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                $total_buying_price = ($getProductDefaultSupplier->freight)+($getProductDefaultSupplier->landing)+($getProductDefaultSupplier->extra_cost)+($getProductDefaultSupplier->extra_tax)+($total_buying_price);
                
                $getProduct->total_buy_unit_cost_price = $total_buying_price;

                // this is supplier buying unit cost price 
                $getProduct->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;              
                
                // this is selling price
                $total_selling_price = $total_buying_price * $getProduct->unit_conversion_rate;

                $getProduct->selling_price = $total_selling_price;
              }                
            }

            $getProduct->save();
            $flag_table               = $flag_table->fresh();
            $flag_table->updated_rows = $flag_table->updated_rows+1;
            $flag_table->save();

            $product_history              = new ProductHistory;
            $product_history->user_id     = 'Admin';
            $product_history->product_id  = @$getProduct->id;
            $product_history->column_name = 'Update Through Categories Margin '.@$product_cat->title.'(Selling Price)';
            $product_history->old_value   = @$old_selling_price;
            $product_history->new_value   = $getProduct->selling_price;
            $product_history->save();
        }
            $flag_table->status = 1;
            $flag_table->save();
        }

        $this->info('*****************************************');
        $this->info('Process Ended at '.date('Y-m-d H:i:s'));
        $this->info('******************************************');
        }
    }
}
