<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use Exception;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\SupplierProducts;
use App\ProductHistory;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;

class UpdateProductMarginJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1500;
    public $tries = 1;
    protected $id;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id,$user_id)
    {
        $this->id = $id;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{

            $id = $this->id;
            $user_id = $this->user_id;

            if($id != null)
            {
              $product_cat = ProductCategory::find($id);
              $getProducts = Product::where('category_id',$id)->get();
            }

            if($getProducts->count() > 0)
            {
                foreach ($getProducts as $product)
                {
                    $customer_categories = CustomerCategory::where('is_deleted','!=',1)->get();
                    foreach ($customer_categories as $cust_cat)
                    {
                        $productMargins = CustomerTypeProductMargin::where('customer_type_id',$cust_cat->id)->where('product_id', $product->id)->first();
                        $getCatMargins = CustomerTypeCategoryMargin::where('category_id',$product_cat->id)->where('customer_type_id',$cust_cat->id)->first();

                        if($productMargins)
                        {
                          $productMargins->default_value  = $getCatMargins->default_value;
                          $productMargins->save();
                        }
                        else
                        {
                          $categoryMarginsNew = new CustomerTypeProductMargin;
                          $categoryMarginsNew->product_id       = $product->id;
                          $categoryMarginsNew->customer_type_id = $cust_cat->id;
                          $categoryMarginsNew->default_margin   = $getCatMargins->default_margin;
                          $categoryMarginsNew->default_value    = $getCatMargins->default_value;
                          $categoryMarginsNew->save();
                        }

                        $productFixedPrice = ProductFixedPrice::where('customer_type_id',$cust_cat->id)->where('product_id', $product->id)->first();
                        if($productFixedPrice)
                        {
                          // do nothing
                        }
                        else
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
                    $old_selling_price            = $getProduct->selling_price;
                    $getProduct->hs_code          = $product_cat->hs_code;
                    $getProduct->import_tax_book  = $product_cat->import_tax_book;
                    $getProduct->vat              = $product_cat->vat;

                    if($getProduct->supplier_id != 0)
                    {
                        $getProductDefaultSupplier = SupplierProducts::where('product_id',$getProduct->id)->where('supplier_id',$getProduct->supplier_id)->first();
                        if($getProductDefaultSupplier->import_tax_actual == null)
                        {
                            $importTax = $getProduct->import_tax_book;
                            $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;
                            $buying_price_in_thb    = $getProductDefaultSupplier->buying_price_in_thb;
                            $total_buying_price     = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;
                            $total_buying_price     = ($getProductDefaultSupplier->freight)+($getProductDefaultSupplier->landing)+($getProductDefaultSupplier->extra_cost)+($getProductDefaultSupplier->extra_tax)+($total_buying_price);

                            $getProduct->total_buy_unit_cost_price = $total_buying_price;
                            $getProduct->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;
                            $total_selling_price                   = $total_buying_price * $getProduct->unit_conversion_rate;
                            $getProduct->selling_price             = $total_selling_price;
                        }
                    }

                    $getProduct->save();

                    $product_history              = new ProductHistory;
                    $product_history->user_id     = $user_id;
                    $product_history->product_id  = @$getProduct->id;
                    $product_history->column_name = 'Update Through Categories Margin '.@$product_cat->title.'(Selling Price)';
                    $product_history->old_value   = @$old_selling_price;
                    $product_history->new_value   = $getProduct->selling_price;
                    $product_history->save();
                }
            }

            ExportStatus::where('type','margins_update_on_products')->update(['status' => 0]);
            return response()->json(['msg' => 'File Saved']);

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
        ExportStatus::where('type','margins_update_on_products')->update(['status' => 2, 'exception' => $exception->getMessage()]);
        $failedJobException            = new FailedJobException();
        $failedJobException->type      = "Proudcts Margins Update On Products Level";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }
}
