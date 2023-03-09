<?php

namespace App\Console\Commands;

use App\Models\Common\Flag;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateProductCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product_currency:UpdateProductCurrency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will update all the Products Prices';

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
        $flag_table = Flag::where('type','currency_update')->where('status',0)->first();
        if($flag_table != null)
        {
        $this->info('*****************************************');
        $this->info('Process Started at '.date('Y-m-d H:i:s'));
        $this->info('******************************************');
        $currency_id = $flag_table->currency_id;
        if($currency_id != null)
        {
            $getProducts = DB::table('products')->select('products.id','products.supplier_id','products.import_tax_book')->leftjoin('suppliers','products.supplier_id','=','suppliers.id')->where('suppliers.currency_id',$currency_id)->where('products.status',1)->get();
        }
        else
        {
            $getProducts = Product::where('status',1)->get();
        }
        // dd($getProducts);
        $flag_table->total_rows = $getProducts->count();
        $flag_table->save();
        
        foreach ($getProducts as $product) 
        {
          $updateSupplierProduct = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();

          // updating buying price of supplier in THB
          $supplier_conv_rate_thb = @$updateSupplierProduct->supplier->getCurrency->conversion_rate;

          $updateSupplierProduct->buying_price_in_thb = ($updateSupplierProduct->buying_price / $supplier_conv_rate_thb);
          $updateSupplierProduct->save();

          $importTax = $updateSupplierProduct->import_tax_actual !== null  ? $updateSupplierProduct->import_tax_actual : @$product->import_tax_book;

          // passing values to function to update prices
          $price_update = $updateSupplierProduct->defaultSupplierProductPriceUpdate($product->id, $product->supplier_id, $updateSupplierProduct->buying_price, $updateSupplierProduct->freight, $updateSupplierProduct->landing, $updateSupplierProduct->extra_cost, $importTax, $updateSupplierProduct->extra_tax);
          $flag_table               = $flag_table->fresh();
          $flag_table->updated_rows = $flag_table->updated_rows+1;
          $flag_table->save();
        }

        $flag_table->status = 1;
        $flag_table->save();

        $this->info('*****************************************');
        $this->info('Process Ended at '.date('Y-m-d H:i:s'));
        $this->info('******************************************');
        }
    }
}
