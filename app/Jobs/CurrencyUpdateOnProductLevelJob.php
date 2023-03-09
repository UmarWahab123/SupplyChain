<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\ProductHistory;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Common\Currency;

class CurrencyUpdateOnProductLevelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;
    protected $currency_id;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($currency_id,$user_id)
    {
        $this->currency_id = $currency_id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $currency_id = $this->currency_id;
      $user_id = $this->user_id;
      try
      {
        if($currency_id != null)
        {
          $getProducts = DB::table('products')->select('products.id','products.supplier_id','products.import_tax_book')->leftjoin('suppliers','products.supplier_id','=','suppliers.id')->where('suppliers.currency_id',$currency_id)->where('products.status',1)->get();
        }
        else
        {
            $getProducts = Product::where('status',1)->get();
        }
        
        foreach ($getProducts as $product) 
        {
          $updateSupplierProduct = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();
          $old_price = $updateSupplierProduct->buying_price_in_thb;

          $supplier_conv_rate_thb = @$updateSupplierProduct->supplier->getCurrency->conversion_rate;
          $updateSupplierProduct->buying_price_in_thb = ($updateSupplierProduct->buying_price / $supplier_conv_rate_thb);
          $updateSupplierProduct->save();
          $importTax = $updateSupplierProduct->import_tax_actual !== null  ? $updateSupplierProduct->import_tax_actual : @$product->import_tax_book;

          // passing values to function to update prices
          $price_update = $updateSupplierProduct->defaultSupplierProductPriceUpdate($product->id, $product->supplier_id, $updateSupplierProduct->buying_price, $updateSupplierProduct->freight, $updateSupplierProduct->landing, $updateSupplierProduct->extra_cost, $importTax, $updateSupplierProduct->extra_tax);

          $product_history              = new ProductHistory;
          $product_history->user_id     = $user_id;
          $product_history->product_id  = $product->id;
          $product_history->column_name = "Currency Update On Product Level";
          $product_history->old_value   = $old_price;
          $product_history->new_value   = $updateSupplierProduct->buying_price_in_thb;
          $product_history->save();
        }

        ExportStatus::where('type','currency_update_on_product_level')->update(['status'=>0]);
        $find_currency = Currency::find($currency_id);
        if($find_currency)
        {
          $find_currency->last_updated_date = carbon::now();
          $find_currency->last_update_by = $user_id;
          $find_currency->save();
        }
        return response()->json(['msg'=>'File Saved']);
      }
      catch(Exception $e) {
        $this->failed($e);
      }
      catch(MaxAttemptsExceededException $e) {
        $this->failed($e);
      }
    }

    public function failed($exception)
    {
      ExportStatus::where('type','currency_update_on_product_level')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException            = new FailedJobException();
      $failedJobException->type      = "Currency Update On Products Level";
      $failedJobException->exception = $exception->getMessage();
      $failedJobException->save();
    }
}
