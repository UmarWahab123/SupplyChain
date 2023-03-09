<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Currency;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\ProductType;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TempProduct;
use App\Models\Common\Unit;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\ProductHistory;
use App\QuotationConfig;
use Carbon\Carbon;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;

class MoveBulkSupplierProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $user_id;
    public $timeout = 1800;
    public $tries = 1;
    protected $row_count = 0;
    public    $error_msgs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id, $request)
    {
        $this->user_id = $user_id;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $user_id     = $this->user_id;
            $request     = $this->request;

            $warehouse = Warehouse::select('id')->get();
            $customerCats = CustomerCategory::select('id')->where('is_deleted',0)->orderBy('id', 'ASC')->get();
            $globalAccessConfig2 = QuotationConfig::select('print_prefrences')->where('section','products_management_page')->first();
            $allow_same_description = '';
            if($globalAccessConfig2)
            {
                $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
                foreach ($globalaccessForConfig as $val)
                {
                  if($val['slug'] === "same_description")
                  {
                    $allow_same_description = $val['status'];
                  }
                }
            }

            $error       = 0;
            $html_string = '';
            $errormsg    = '<ol>';

            $temp_products = TempProduct::with('tempProductCategory:id',
            'tempProductSubCategory:id,hs_code,vat,import_tax_book,prefix',
            'tempProductType:id',
            'tempUnits:id',
            'tempSellingUnits:id',
            'tempStockUnits:id',
            'tempSupplier:id,currency_id,reference_name',
            'tempSupplier.getCurrency:id,conversion_rate',
            'tempProductTableSubCategory:id,refrence_no',
            'tempProductType2:id',
            'tempProductType3:id',
            'tempProductSystemCode',
            'tempProductSystemCode.product_fixed_price',
            'tempProductSystemCode.supplier_products',
            'tempProductShortDesc:id')
            ->whereIn('id', $request);

            $temp_products->chunkById(100, function($rows) use ($warehouse, $customerCats, $allow_same_description, $user_id, $errormsg, $error)
            {
                foreach($rows as $temp_product)
                {
                    // $temp_product = TempProduct::with('tempProductCategory', 'tempProductSubCategory', 'tempProductType', 'tempUnits', 'tempSellingUnits', 'tempStockUnits', 'tempSupplier.getCurrency', 'tempProductTableSubCategory', 'tempProductType2', 'tempProductType3')->find($request[$i]);
                    if($temp_product->system_code != null)
                    {
                    //   $product = Product::with('product_fixed_price', 'supplier_products')->where('system_code',$temp_product->system_code)->first();
                      $product = $temp_product->tempProductSystemCode;
                      if($product != null)
                      {
                        // $primary_category = ProductCategory::where('id',$temp_product->primary_category)->first();

                        // $primary_category = $temp_product->tempProductCategory;
                        // if($primary_category != null)
                        if ($product->primary_category != $temp_product->primary_category)
                        {
                            $product->primary_category = $temp_product->primary_category;
                        }


                        // $category_id = ProductCategory::where('id',$temp_product->category_id)->first();
                        // $category_id = $temp_product->tempProductSubCategory;

                        // if($category_id != null)
                        if($product->category_id != $temp_product->category_id)
                        {
                            if ($product->category_id != $temp_product->category_id) {
                                $product->category_id = $temp_product->category_id;
                                $product->hs_code = $temp_product->tempProductSubCategory->hs_code;
                                $product->import_tax_book = $temp_product->tempProductSubCategory->import_tax_book;
                            }

                            // if($temp_product->vat == null)
                            // {
                            //   $product->vat             = $temp_product->tempProductSubCategory->vat;
                            // }
                            // else
                            // {
                            //   $product->vat             = $temp_product->vat;
                            // }
                        }
                        if ($product->product_temprature_c != $temp_product->product_temprature_c) {
                            $product->product_temprature_c = $temp_product->product_temprature_c;
                        }
                        if ($product->weight != $temp_product->weight) {
                            $product->weight = $temp_product->weight;
                        }
                        if ($product->short_desc != $temp_product->short_desc) {
                            $product->short_desc = $temp_product->short_desc;
                        }
                        if ($product->refrence_code != $temp_product->refrence_code) {
                            $product->refrence_code = $temp_product->refrence_code;
                        }

                        // $type = ProductType::where('id',$temp_product->type_id)->first();
                        // $type = $temp_product->tempProductType;

                        // if($type != null)
                        if($product->type_id != $temp_product->type_id)
                        {
                          $product->type_id = $temp_product->type_id;
                        }

                        // $type_2 = $temp_product->tempProductType2;
                        // if($type_2 != null)
                        if($product->type_id_2 != $temp_product->type_2_id)
                        {
                          $product->type_id_2 = $temp_product->type_2_id;
                        }

                        // $type_3 = $temp_product->tempProductType3;
                        // if($type_3 != null)
                        if($product->type_id_3 != $temp_product->type_3_id)
                        {
                          $product->type_id_3 = $temp_product->type_3_id;
                        }

                        if ($product->brand != $temp_product->brand) {
                            $product->brand = $temp_product->brand;
                        }

                        // $buying_unit = Unit::where('id',$temp_product->buying_unit)->first();
                        // $buying_unit = $temp_product->tempUnits;
                        // if($buying_unit != null)
                        if($product->buying_unit != $temp_product->buying_unit)
                        {
                          $product->buying_unit = $temp_product->buying_unit;
                        }

                        // $selling_unit = Unit::where('id',$temp_product->selling_unit)->first();
                        // $selling_unit = $temp_product->tempSellingUnits;
                        // if($selling_unit != null)
                        if($product->selling_unit != $temp_product->selling_unit)
                        {
                          $product->selling_unit = $temp_product->selling_unit;
                        }

                        // $stock_unit = Unit::where('id',$temp_product->stock_unit)->first();
                        // $stock_unit = $temp_product->tempStockUnits;
                        if($product->stock_unit != $temp_product->stock_unit)
                        {
                          $product->stock_unit = $temp_product->stock_unit;
                        }
                        if ($product->order_qty_per_piece != $temp_product->order_qty_per_piece) {
                            $product->order_qty_per_piece = $temp_product->order_qty_per_piece;
                        }
                        if ($product->min_stock != $temp_product->min_stock) {
                            $product->min_stock = $temp_product->min_stock;
                        }
                        if ($product->unit_conversion_rate != $temp_product->unit_conversion_rate) {
                            $product->unit_conversion_rate = $temp_product->unit_conversion_rate;
                        }
                        if ($product->product_notes != $temp_product->product_notes) {
                            $product->product_notes = $temp_product->product_notes;
                        }

                        if($temp_product->vat !== NULL && $temp_product->vat !== '')
                        {
                          $product->vat                = $temp_product->vat;
                        }

                        if($product->supplier_id == $temp_product->supplier_id)
                        {
                          // $supplier = Supplier::where('id',$temp_product->supplier_id)->first();
                          $supplier = $temp_product->tempSupplier;
                          if($supplier != null)
                          {
                            $product->supplier_id    = $temp_product->supplier_id;
                            // $cur = Currency::find($supplier->currency_id);
                            $cur = $supplier->getCurrency;
                            $total_buying_price      = $temp_product->buying_price != null ? ($temp_product->buying_price/$cur->conversion_rate) : null;

                            // here is the new condition starts
                            $importTax = $temp_product->import_tax_actual !== NULL ? $temp_product->import_tax_actual : $temp_product->tempProductSubCategory->import_tax_book;

                            $total_buying_price  = (($importTax/100) * $total_buying_price) + $total_buying_price;
                            $extras = $temp_product->freight + $temp_product->landing + $temp_product->extra_cost + $temp_product->extra_tax;
                            $total_buying_price                 = $total_buying_price + $extras;
                            $product->total_buy_unit_cost_price = $total_buying_price;
                            $product->t_b_u_c_p_of_supplier     = $total_buying_price*$cur->conversion_rate;

                            //this is buy unit cost price
                            $total_selling_price                = $product->total_buy_unit_cost_price * $temp_product->unit_conversion_rate;
                            $product->selling_price             = $total_selling_price;
                            $product->last_price_updated_date   = Carbon::now();
                          }
                        }
                        $product->save();
                        // Dynamically adding fixed prices for categories based
                        // $customerCats = CustomerCategory::where('is_deleted',0)->orderBy('id', 'ASC')->get();
                        if($customerCats->count() > 0)
                        {
                          $deserialized = unserialize($temp_product->fixed_prices_array);
                          $y=0;
                          foreach ($customerCats as $c_cat)
                          {
                            // $productFixedPrices = ProductFixedPrice::where('product_id',$product->id)->where('customer_type_id',$c_cat->id)->first();
                            $productFixedPrices = $product->product_fixed_price->where('customer_type_id',$c_cat->id)->first();
                            if(array_key_exists($y,$deserialized))
                            {
                                if ($productFixedPrices->fixed_price != $deserialized[$y]) {
                                    $productFixedPrices->fixed_price = $deserialized[$y];
                                    $productFixedPrices->save();
                                }
                            }
                            // else
                            // {
                            //   $productFixedPrices->fixed_price      = 0;
                            // }
                            // $productFixedPrices->expiration_date  = NULL;
                            // $productFixedPrices->save();
                            $y++;
                          }
                        }

                        // $supplier = Supplier::where('id',$temp_product->supplier_id)->first();
                        $supplier = $temp_product->tempSupplier;

                        // $cur = Currency::find(@$supplier->currency_id);
                        $cur = $supplier->getCurrency;
                        $buying_price_in_thb = $temp_product->buying_price != null ? $temp_product->buying_price/$cur->conversion_rate : null;

                        // $supplier_products = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$temp_product->supplier_id)->first();
                        // dd('here');
                        $supplier_products = $product->supplier_products->where('supplier_id',$temp_product->supplier_id)->first();
                        // if($supplier_products == null)
                        // {
                        //   $supplier_products                                = new SupplierProducts;
                        // }
                        $old_price_value = $supplier_products->buying_price;

                        if ($supplier_products->supplier_id != $temp_product->supplier_id) {
                            $supplier_products->supplier_id = $temp_product->supplier_id;
                        }
                        if ($supplier_products->product_id != $product->id) {
                            $supplier_products->product_id = $product->id;
                        }
                        if ($supplier_products->product_supplier_reference_no != $temp_product->p_s_r) {
                            $supplier_products->product_supplier_reference_no = $temp_product->p_s_r;
                        }
                        if ($supplier_products->supplier_description != $temp_product->supplier_description) {
                            $supplier_products->supplier_description = $temp_product->supplier_description;
                        }
                        if ($supplier_products->buying_price != $temp_product->buying_price) {
                            $supplier_products->buying_price = (float)$temp_product->buying_price;
                            $supplier_products->buying_price_in_thb = (float)$buying_price_in_thb;
                        }
                        if ($supplier_products->extra_cost != $temp_product->extra_cost) {
                            $supplier_products->extra_cost = $temp_product->extra_cost;
                        }
                        if ($supplier_products->extra_tax != $temp_product->extra_tax) {
                            $supplier_products->extra_tax = $temp_product->extra_tax;
                        }
                        if ($supplier_products->freight != $temp_product->freight) {
                            $supplier_products->freight = $temp_product->freight;
                        }
                        if ($supplier_products->landing != $temp_product->landing) {
                            $supplier_products->landing = $temp_product->landing;
                        }
                        if ($supplier_products->gross_weight != $temp_product->gross_weight) {
                            $supplier_products->gross_weight = $temp_product->gross_weight;
                        }
                        if ($supplier_products->leading_time != $temp_product->leading_time) {
                            $supplier_products->leading_time = $temp_product->leading_time;
                        }
                        if ($supplier_products->import_tax_actual != $temp_product->import_tax_actual){
                            $supplier_products->import_tax_actual = $temp_product->import_tax_actual;
                        }
                        if ($supplier_products->m_o_q != $temp_product->m_o_q) {
                            $supplier_products->m_o_q = $temp_product->m_o_q;
                        }
                        if ($supplier_products->supplier_packaging != $temp_product->supplier_packaging) {
                            $supplier_products->supplier_packaging = $temp_product->supplier_packaging;
                        }
                        if ($supplier_products->billed_unit != $temp_product->billed_unit) {
                            $supplier_products->billed_unit = $temp_product->billed_unit;
                        }
                        $supplier_products->save();


                        $temp_product->delete();

                        $product_history              = new ProductHistory;
                        $product_history->user_id     = $user_id;
                        $product_history->product_id  = $product->id;
                        $product_history->column_name = "Purchasing Price Update From Bulk Products Upload"." (Supplier - ".$supplier->reference_name." )";
                        $product_history->old_value   = $old_price_value;
                        $product_history->new_value   = $temp_product->buying_price;
                        $product_history->save();
                      }
                      else
                      {
                        $errormsg .= '<li>Product With The System Code ('.$temp_product->system_code.') Didn\'t exist in system.</li>';
                        $error = 1;
                      }
                    }
                    else
                    {
                      if ($allow_same_description == 0)
                      {
                        // $same_product = Product::whereNotNull('short_desc')->where('short_desc', $temp_product->short_desc)->first();
                        $same_product = $temp_product->tempProductShortDesc;
                        if ($same_product != null)
                        {
                          $errormsg .= '<li>Duplicate Produdct Description ('.$temp_product->short_desc.').</li>';
                          $error = 1;
                        }
                        else
                        {
                          // $getCat = ProductCategory::where('id',$temp_product->primary_category)->first();
                          $getCat = $temp_product->tempProductCategory;

                          if($getCat == null)
                          {
                            $errormsg .= '<li>Product Primary Category Not Found  ('.$temp_product->primary_category.').</li>';
                            $error = 1;
                          }
                          // $getSubCat = ProductCategory::where('id',$temp_product->category_id)->first();
                          $getSubCat = $temp_product->tempProductSubCategory;
                          if($getSubCat == null)
                          {
                            $errormsg .= '<li>Product Sub Category Not Found  ('.$temp_product->category_id.').</li>';
                            $error = 1;
                          }
                          else
                          {
                            $product = new Product;
                            $reference_code = null;
                            $prefix = @$getSubCat->prefix;
                            // $c_p_ref = Product::where('category_id', $temp_product->category_id)->orderBy('refrence_no','DESC')->first();
                            $c_p_ref = $temp_product->tempProductTableSubCategory()->orderBy('refrence_no','DESC')->first();

                            if($c_p_ref == NULL)
                            {
                              $str = '0';
                            }
                            else
                            {
                              $str = $c_p_ref->refrence_no;
                            }

                            $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
                            if($temp_product->refrence_code !== null)
                            {
                              $product->refrence_code = $temp_product->refrence_code;
                            }
                            else
                            {
                              $product->refrence_code = $prefix.$system_gen_no;
                            }
                            $product->system_code  = $prefix.$system_gen_no;
                            $product->refrence_no  = $system_gen_no;
                            $product->short_desc   = $temp_product->short_desc;

                            // $primary_category = ProductCategory::where('id',$temp_product->primary_category)->first();
                            $primary_category = $temp_product->tempProductCategory;

                            if($primary_category != null)
                            {
                              $product->primary_category      = $temp_product->primary_category;
                            }

                            // $category_id = ProductCategory::where('id',$temp_product->category_id)->first();
                            $category_id = $temp_product->tempProductSubCategory;
                            if($category_id != null)
                            {
                              $product->category_id     = $temp_product->category_id;
                              $product->hs_code         = $temp_product->tempProductSubCategory->hs_code;
                              $product->import_tax_book = $temp_product->tempProductSubCategory->import_tax_book;
                              if($temp_product->vat == NULL || $temp_product->vat == '')
                              {
                                $product->vat = $temp_product->tempProductSubCategory->vat;
                              }
                              else
                              {
                                $product->vat = $temp_product->vat;
                              }
                            }

                            if($temp_product->vat !== NULL && $temp_product->vat !== '')
                            {
                              $product->vat = $temp_product->vat;
                            }

                            // $buying_unit = Unit::where('id',$temp_product->buying_unit)->first();
                            $buying_unit = $temp_product->tempUnits;
                            if($buying_unit != null)
                            {
                              $product->buying_unit         = $temp_product->buying_unit;
                            }

                            // $selling_unit = Unit::where('id',$temp_product->selling_unit)->first();
                            $selling_unit = $temp_product->tempSellingUnits;
                            if($selling_unit != null)
                            {
                              $product->selling_unit        = $temp_product->selling_unit;
                            }

                            // $stock_unit = Unit::where('id',$temp_product->stock_unit)->first();
                            $stock_unit = $temp_product->tempStockUnits;
                            if($stock_unit != null)
                            {
                              $product->stock_unit          = $temp_product->stock_unit;
                            }
                            $product->order_qty_per_piece   = $temp_product->order_qty_per_piece;
                            $product->min_stock             = $temp_product->min_stock;
                            $product->product_temprature_c  = $temp_product->product_temprature_c;
                            $product->weight                = $temp_product->weight;
                            $product->unit_conversion_rate  = $temp_product->unit_conversion_rate;

                            // $type = ProductType::where('id',$temp_product->type_id)->first();
                            $type = $temp_product->tempProductType;
                            if($type != null)
                            {
                              $product->type_id               = $temp_product->type_id;
                            }

                            $type_2 = $temp_product->tempProductType2;
                            if($type_2 != null)
                            {
                                $product->type_id_3          = $temp_product->type_3_id;
                            }


                            $type_3 = $temp_product->tempProductType3;
                            if($type_3 != null)
                            {
                                $product->type_id_3          = $temp_product->type_3_id;
                            }

                            $product->brand                 = $temp_product->brand;
                            // $supplier = Supplier::where('id',$temp_product->supplier_id)->first();
                            $supplier = $temp_product->tempSupplier;

                            if($supplier != null)
                            {
                              $product->supplier_id           = $temp_product->supplier_id;

                              // $cur = Currency::find($supplier->currency_id);
                              $cur = $supplier->getCurrency;

                              $total_buying_price                 = $temp_product->buying_price != null ? ($temp_product->buying_price/$cur->conversion_rate) : null;

                              //import tax book is in products subcategories table
                              $importTax                          = $temp_product->import_tax_actual !== NULL ? $temp_product->import_tax_actual : $temp_product->tempProductSubCategory->import_tax_book;

                              $total_buying_price                 = (($importTax/100) * $total_buying_price) + $total_buying_price;
                              $extras                             = $temp_product->freight + $temp_product->landing + $temp_product->extra_cost + $temp_product->extra_tax;
                              $total_buying_price                 = $total_buying_price + $extras;
                              $product->total_buy_unit_cost_price = $total_buying_price;
                              $product->t_b_u_c_p_of_supplier     = $total_buying_price*$cur->conversion_rate;

                              //this is buy unit cost price
                              $total_selling_price                = $product->total_buy_unit_cost_price * $temp_product->unit_conversion_rate;
                              $product->selling_price             = $total_selling_price;
                              $product->last_price_updated_date   = Carbon::now();
                            }

                            $product->created_by    = $temp_product->created_by;
                            $product->status        = 0;
                            // $product->status        = $temp_product->status;
                            $product->product_notes = $temp_product->product_notes;
                            $product->save();

                            // $supplier = Supplier::where('id',$temp_product->supplier_id)->first();
                            // $supplier = $temp_product->tempSupplier;
                            $buying_price_in_thb = $temp_product->buying_price != null ? $temp_product->buying_price/$cur->conversion_rate : null;
                            if($supplier != null)
                            {
                              $supplier_products                                = new SupplierProducts;
                              $supplier_products->supplier_id                   = $temp_product->supplier_id;
                              $supplier_products->product_id                    = $product->id;
                              $supplier_products->product_supplier_reference_no = $temp_product->p_s_r;
                              $supplier_products->supplier_description          = $temp_product->supplier_description;
                              $supplier_products->buying_price                  = (float)$temp_product->buying_price;
                              $supplier_products->buying_price_in_thb           = (float)$buying_price_in_thb;
                              $supplier_products->extra_cost                    = $temp_product->extra_cost;
                              $supplier_products->extra_tax                     = $temp_product->extra_tax;
                              $supplier_products->freight                       = $temp_product->freight;
                              $supplier_products->landing                       = $temp_product->landing;
                              $supplier_products->gross_weight                  = $temp_product->gross_weight;
                              $supplier_products->leading_time                  = $temp_product->leading_time;
                              $supplier_products->import_tax_actual             = $temp_product->import_tax_actual;
                              $supplier_products->m_o_q                         = $temp_product->m_o_q;
                              $supplier_products->supplier_packaging            = $temp_product->supplier_packaging;
                              $supplier_products->billed_unit                   = $temp_product->billed_unit;
                              $supplier_products->save();
                            }

                            $recentAdded = Product::with('customer_type_category_margins')->find($product->id);

                            // $warehouse = Warehouse::get();
                            foreach ($warehouse as $w)
                            {
                              $warehouse_product = new WarehouseProduct;
                              $warehouse_product->warehouse_id = $w->id;
                              $warehouse_product->product_id = $recentAdded->id;
                              $warehouse_product->save();
                            }

                            // $categoryMargins = CustomerTypeCategoryMargin::where('category_id', $recentAdded->category_id)->orderBy('id', 'ASC')->get();
                            // $categoryMargins = $recentAdded->customer_type_category_margins()->orderBy('id', 'ASC')->get();
                            $categoryMargins = $recentAdded->customer_type_category_margins;
                            if($categoryMargins->count() > 0)
                            {
                              foreach ($categoryMargins as $value)
                              {
                                $productMargin = new CustomerTypeProductMargin;
                                $productMargin->product_id       = $recentAdded->id;
                                $productMargin->customer_type_id = $value->customer_type_id;
                                $productMargin->default_margin   = $value->default_margin;
                                $productMargin->default_value    = $value->default_value;
                                $productMargin->save();
                              }
                            }

                            // $customerCats = CustomerCategory::where('is_deleted',0)->orderBy('id', 'ASC')->get();
                            if($customerCats->count() > 0)
                            {
                              $deserialized = unserialize($temp_product->fixed_prices_array);
                              $z=0;
                              foreach ($customerCats as $c_cat)
                              {
                                $productFixedPrices = new ProductFixedPrice;
                                $productFixedPrices->product_id       = $recentAdded->id;
                                $productFixedPrices->customer_type_id = $c_cat->id;
                                if(array_key_exists($z,$deserialized))
                                {
                                  $productFixedPrices->fixed_price      = $deserialized[$z];
                                }
                                else
                                {
                                  $productFixedPrices->fixed_price      = 0;
                                }
                                $productFixedPrices->expiration_date  = NULL;
                                $productFixedPrices->save();
                                $z++;
                              }
                            }

                            $p_id = $product->id;
                            $mark_as_complete = $this->doProductCompleted($p_id);

                            $temp_product->delete();
                          }
                        }
                      }
                      else
                      {
                        $product = new Product;
                        // $getCat = ProductCategory::where('id',$temp_product->primary_category)->first();
                        $getCat = $temp_product->tempProductCategory;
                        if($getCat == null)
                        {
                          $errormsg .= '<li>Product Primary Category Not Found  ('.$temp_product->primary_category.').</li>';
                          $error = 1;
                        }
                        // $getSubCat = ProductCategory::where('id',$temp_product->category_id)->first();
                        $getSubCat = $temp_product->tempProductSubCategory;

                        if($getSubCat == null)
                        {
                          $errormsg .= '<li>Product Sub Category Not Found  ('.$temp_product->category_id.').</li>';
                          $error = 1;
                        }
                        else
                        {
                          $reference_code = null;

                          $prefix = @$getSubCat->prefix;

                          // $c_p_ref = Product::where('category_id', $temp_product->category_id)->orderBy('refrence_no','DESC')->first();
                          $c_p_ref = $temp_product->tempProductTableSubCategory()->orderBy('refrence_no','DESC')->first();
                          if($c_p_ref == NULL)
                          {
                            $str = '0';
                          }
                          else
                          {
                            $str = $c_p_ref->refrence_no;
                          }

                          $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
                          if($temp_product->refrence_code !== null)
                          {
                            $product->refrence_code = $temp_product->refrence_code;
                          }
                          else
                          {
                            $product->refrence_code = $prefix.$system_gen_no;
                          }
                          $product->system_code  = $prefix.$system_gen_no;
                          $product->refrence_no  = $system_gen_no;
                          $product->short_desc   = $temp_product->short_desc;

                          // $primary_category = ProductCategory::where('id',$temp_product->primary_category)->first();
                          $primary_category = $temp_product->tempProductCategory;

                          if($primary_category != null)
                          {
                            $product->primary_category      = $temp_product->primary_category;
                          }

                          // $category_id = ProductCategory::where('id',$temp_product->category_id)->first();
                          $category_id = $temp_product->tempProductSubCategory;
                          if($category_id != null)
                          {
                            $product->category_id     = $temp_product->category_id;
                            $product->hs_code         = $temp_product->tempProductSubCategory->hs_code;
                            $product->import_tax_book = $temp_product->tempProductSubCategory->import_tax_book;
                            if($temp_product->vat == NULL || $temp_product->vat == '')
                            {
                              $product->vat = $temp_product->tempProductSubCategory->vat;
                            }
                            else
                            {
                              $product->vat = $temp_product->vat;
                            }
                          }

                          if($temp_product->vat !== NULL && $temp_product->vat !== '')
                          {
                            $product->vat = $temp_product->vat;
                          }

                          // $buying_unit = Unit::where('id',$temp_product->buying_unit)->first();
                          $buying_unit = $temp_product->tempUnits;
                          if($buying_unit != null)
                          {
                            $product->buying_unit           = $temp_product->buying_unit;
                          }

                          // $selling_unit = Unit::where('id',$temp_product->selling_unit)->first();
                          $selling_unit = $temp_product->tempSellingUnits;
                          if($selling_unit != null)
                          {
                            $product->selling_unit          = $temp_product->selling_unit;
                          }

                          // $stock_unit = Unit::where('id',$temp_product->stock_unit)->first();
                          $stock_unit = $temp_product->tempStockUnits;

                          if($stock_unit != null)
                          {
                            $product->stock_unit          = $temp_product->stock_unit;
                          }

                          $product->order_qty_per_piece   = $temp_product->order_qty_per_piece;
                          $product->min_stock             = $temp_product->min_stock;
                          $product->product_temprature_c  = $temp_product->product_temprature_c;
                          $product->weight                = $temp_product->weight;
                          $product->unit_conversion_rate  = $temp_product->unit_conversion_rate;

                          // $type = ProductType::where('id',$temp_product->type_id)->first();
                          $type = $temp_product->tempProductType;
                          if($type != null)
                          {
                            $product->type_id               = $temp_product->type_id;
                          }

                          $type_2 = $temp_product->tempProductType2;
                          if($type_2 != null)
                          {
                            $product->type_id_2          = $temp_product->type_2_id;
                          }

                          $type_3 = $temp_product->tempProductType3;
                          if($type_3 != null)
                          {
                            $product->type_id_3          = $temp_product->type_3_id;
                          }

                          $product->brand                 = $temp_product->brand;
                          // $supplier = Supplier::where('id',$temp_product->supplier_id)->first();
                          $supplier = $temp_product->tempSupplier;
                          if($supplier != null)
                          {
                            $product->supplier_id           = $temp_product->supplier_id;

                            // $cur = Currency::find($supplier->currency_id);
                            $cur = $supplier->getCurrency;

                            $total_buying_price                 = $temp_product->buying_price != null ? ($temp_product->buying_price/$cur->conversion_rate) : null;

                            // here is the new condition starts
                            $importTax                          = $temp_product->import_tax_actual !== NULL ? $temp_product->import_tax_actual : $temp_product->tempProductSubCategory->import_tax_book;
                            // here is the new condition ends

                            $total_buying_price                 = (($importTax/100) * $total_buying_price) + $total_buying_price;
                            $extras                             = $temp_product->freight + $temp_product->landing + $temp_product->extra_cost + $temp_product->extra_tax;
                            $total_buying_price                 = $total_buying_price + $extras;
                            $product->total_buy_unit_cost_price = $total_buying_price;
                            $product->t_b_u_c_p_of_supplier     = $total_buying_price*$cur->conversion_rate;

                            //this is buy unit cost price
                            $total_selling_price                = $product->total_buy_unit_cost_price * $temp_product->unit_conversion_rate;
                            $product->selling_price             = $total_selling_price;
                            $product->last_price_updated_date   = Carbon::now();
                          }

                          $product->created_by    = $temp_product->created_by;
                          $product->status        = 0;
                          // $product->status        = $temp_product->status;
                          $product->product_notes = $temp_product->product_notes;
                          $product->save();

                          // $supplier = Supplier::where('id',$temp_product->supplier_id)->first();
                          $supplier = $temp_product->tempSupplier;
                          $buying_price_in_thb = $temp_product->buying_price != null ? $temp_product->buying_price/$cur->conversion_rate : null;
                          if($supplier != null)
                          {
                            $supplier_products                                = new SupplierProducts;
                            $supplier_products->supplier_id                   = $temp_product->supplier_id;
                            $supplier_products->product_id                    = $product->id;
                            $supplier_products->product_supplier_reference_no = $temp_product->p_s_r;
                            $supplier_products->supplier_description          = $temp_product->supplier_description;
                            $supplier_products->buying_price                  = (float)$temp_product->buying_price;
                            $supplier_products->buying_price_in_thb           = (float)$buying_price_in_thb;
                            $supplier_products->extra_cost                    = $temp_product->extra_cost;
                            $supplier_products->extra_tax                     = $temp_product->extra_tax;
                            $supplier_products->freight                       = $temp_product->freight;
                            $supplier_products->landing                       = $temp_product->landing;
                            $supplier_products->gross_weight                  = $temp_product->gross_weight;
                            $supplier_products->leading_time                  = $temp_product->leading_time;
                            $supplier_products->import_tax_actual             = $temp_product->import_tax_actual;
                            $supplier_products->m_o_q                         = $temp_product->m_o_q;
                            $supplier_products->supplier_packaging            = $temp_product->supplier_packaging;
                            $supplier_products->billed_unit                   = $temp_product->billed_unit;
                            $supplier_products->save();
                          }

                          $recentAdded = Product::with('customer_type_category_margins')->find($product->id);

                          // $categoryMargins = CustomerTypeCategoryMargin::where('category_id', $recentAdded->category_id)->orderBy('id', 'ASC')->get();
                        //   $categoryMargins = $recentAdded->customer_type_category_margins()->orderBy('id', 'ASC')->get();
                          $categoryMargins = $recentAdded->customer_type_category_margins;
                          if($categoryMargins->count() > 0)
                          {
                            foreach ($categoryMargins as $value)
                            {
                              $productMargin = new CustomerTypeProductMargin;
                              $productMargin->product_id       = $recentAdded->id;
                              $productMargin->customer_type_id = $value->customer_type_id;
                              $productMargin->default_margin   = $value->default_margin;
                              $productMargin->default_value    = $value->default_value;
                              $productMargin->save();
                            }
                          }

                          // $customerCats = CustomerCategory::where('is_deleted',0)->orderBy('id', 'ASC')->get();
                          if($customerCats->count() > 0)
                          {
                            $deserialized = unserialize($temp_product->fixed_prices_array);
                            $z=0;
                            foreach ($customerCats as $c_cat)
                            {
                              $productFixedPrices = new ProductFixedPrice;
                              $productFixedPrices->product_id       = $recentAdded->id;
                              $productFixedPrices->customer_type_id = $c_cat->id;
                              if(array_key_exists($z,$deserialized))
                              {
                                $productFixedPrices->fixed_price      = $deserialized[$z];
                              }
                              else
                              {
                                $productFixedPrices->fixed_price      = 0;
                              }
                              $productFixedPrices->expiration_date  = NULL;
                              $productFixedPrices->save();
                              $z++;
                            }
                          }
                          /********************* Dynamic New code for product fixed prices ***************/
                          // $warehouse = Warehouse::get();
                          foreach ($warehouse as $w)
                          {
                            $warehouse_product = new WarehouseProduct;
                            $warehouse_product->warehouse_id = $w->id;
                            $warehouse_product->product_id = $recentAdded->id;
                            $warehouse_product->save();
                          }

                          $p_id = $product->id;
                          $mark_as_complete = $this->doProductCompleted($p_id);

                          $temp_product->delete();
                        }
                      }
                    }
                }
            });

            $errormsg .='</ol>';

            ExportStatus::where('type','move_supplier_bulk_products')->update(['status'=>0,'last_downloaded'=>date('Y-m-d'),'exception'=>$errormsg, 'error_msgs'=>$error]);
            return response()->json(['msg'=>'File Saved']);
        }
        catch(Exception $e)
        {
          $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e)
        {
          $this->failed($e);
        }
    }

    public function failed( $exception)
    {
      ExportStatus::where('type','move_supplier_bulk_products')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException            = new FailedJobException();
      $failedJobException->type      = "move_supplier_bulk_products";
      $failedJobException->exception = $exception->getMessage();
      $failedJobException->save();
    }

    public function doProductCompleted($p_id)
    {
      if($p_id)
      {
        $product = Product::with('supplier_products')->find($p_id);

        $missingPrams = array();

        if($product->refrence_code == null)
        {
          $missingPrams[] = 'Product Reference Code';
        }

        if($product->short_desc == null)
        {
          $missingPrams[] = 'Short Description';
        }

        if($product->primary_category == null)
        {
          $missingPrams[] = 'Primary Category';
        }

        if($product->category_id == 0)
        {
          $missingPrams[] = 'Sub Category';
        }

        if($product->type_id == null)
        {
          $missingPrams[] = 'Product Type';
        }

        // if($product->type_id_2 == null)
        // {
        //   $missingPrams[] = 'Product Type 2';
        // }

        if($product->buying_unit == null)
        {
          $missingPrams[] = 'Billed Unit';
        }

        if($product->selling_unit == null)
        {
          $missingPrams[] = 'Selling Unit';
        }

        if($product->unit_conversion_rate == null)
        {
          $missingPrams[] = 'Unit Conversion Rate';
        }

        if($product->supplier_id == 0)
        {
          $missingPrams[] = 'Default Supplier';
        }

        if($product->supplier_id != 0)
        {
          // $checkingProductSupplier = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();
          $checkingProductSupplier = $product->supplier_products->where('supplier_id',$product->supplier_id)->first();
          if($checkingProductSupplier)
          {
            if($checkingProductSupplier->buying_price === null)
            {
              $missingPrams[] = 'Supplier Buying Price';
            }

            if($checkingProductSupplier->leading_time === null)
            {
              $missingPrams[] = 'Supplier Leading Time';
            }
          }
        }

        if(sizeof($missingPrams) == 0)
        {
          $product->status = 1;
          $product->save();
          $message = "completed";
          return response()->json(['success' => true, 'message' => $message]);
        }
        else
        {
          $message = implode(', ', $missingPrams);
          return response()->json(['success' => false, 'message' => $message]);
        }
      }
    }
}
