<?php

namespace App\Jobs;

use Exception;
use App\Variable;
use App\ExportStatus;
use App\ProductsRecord;
use App\QuotationConfig;
use App\FailedJobException;
use Illuminate\Bus\Queueable;

use App\Models\Common\Product;
use App\Models\Common\Warehouse;
use App\Models\Common\Order\Order;
use App\Exports\completeProductExport;
use App\Models\Common\ProductCategory;
use App\Models\Common\TableHideColumn;
use Illuminate\Queue\SerializesModels;
use App\Models\Common\CustomerCategory;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use Illuminate\Queue\InteractsWithQueue;
use App\Exports\completeProductPosExport;
use App\Models\Common\Order\OrderProduct;
use App\Helpers\ProductConfigurationHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
// use App\Exports\completeProductPosExport;

class CompleteProductsExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1500;
    public $tries = 2;
    protected $data;
    protected $user_id;
    protected $productsArr = [];
    protected $dataArr = [];


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $user_id)
    {

        $this->data = $data;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $request = $this->data;
            $user_id = $this->user_id;
            $getWarehouses = Warehouse::where('status', 1)->get();

            $getCategories = CustomerCategory::where('is_deleted', 0)->where('show', 1)->get();
            $getCategoriesSuggestedPrices = CustomerCategory::where('is_deleted', 0)->where('suggested_price_show', 1)->get();
            $customer_suggested_prices_array = [];

            $not_visible_arr = [];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'completed_products')->where('user_id', $user_id)->first();

            if ($not_visible_columns != null) {
                $not_visible_arr = explode(',', $not_visible_columns->hide_columns);
            }

            $fileName = 'All';

            $hide_hs_description = null;
            $globalAccessConfig2 = QuotationConfig::where('section', 'products_management_page')->first();

            if ($globalAccessConfig2) {
                $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
                foreach ($globalaccessForConfig as $val) {
                    if ($val['slug'] === "hide_hs_description") {
                        $hide_hs_description = $val['status'];
                    }
                }
            } else {
                $hide_hs_description = '';
            }

            $vairables = Variable::select('slug', 'standard_name', 'terminology')->get();
            $global_terminologies = [];
            foreach ($vairables as $variable) {
                if ($variable->terminology != null) {
                    $global_terminologies[$variable->slug] = $variable->terminology;
                } else {
                    $global_terminologies[$variable->slug] = $variable->standard_name;
                }
            }

            $ecommerceconfig = QuotationConfig::where('section', 'ecommerce_configuration')->first();
            if ($ecommerceconfig) {
                $check_status = unserialize($ecommerceconfig->print_prefrences);
                $ecommerceconfig_status = $check_status['status'][0];
            } else {
                $ecommerceconfig_status = '';
            }

            $query = Product::select('products.refrence_code', 'products.primary_category', 'products.short_desc', 'products.buying_unit', 'products.selling_unit', 'products.type_id', 'products.brand', 'products.product_temprature_c', 'products.supplier_id', 'products.id', 'products.total_buy_unit_cost_price', 'products.weight', 'products.unit_conversion_rate', 'products.selling_price', 'products.vat', 'products.import_tax_book', 'products.hs_code', 'products.hs_description', 'products.name', 'products.category_id', 'products.product_notes', 'products.status', 'products.min_stock', 'products.last_price_updated_date', 'products.ecommerce_enabled', 'products.created_at', 'products.type_id_2', 'products.min_o_qty', 'products.max_o_qty', 'products.length', 'products.width', 'products.height', 'products.long_desc', 'products.ecommerce_price', 'products.discount_price', 'products.discount_expiry_date', 'products.ecom_selling_unit', 'products.selling_unit_conversion_rate', 'ecom_product_weight_per_unit','products.bar_code','products.barcode_type', 'products.type_id_3', 'product_note_3');
            $query =  $query->with('def_or_last_supplier:id,reference_name,country', 'units:id,title,decimal_places', 'productType:id,title', 'productType2:id,title', 'productSubCategory:id,title', 'supplier_products:id,supplier_id,product_id,product_supplier_reference_no,supplier_description,freight,landing,buying_price,buying_price_in_thb,leading_time,extra_tax', 'supplier_products.supplier:id,currency_id', 'supplier_products.supplier.getCurrency:currency_symbol', 'productCategory:id,title', 'product_fixed_price:id,product_id,customer_type_id,fixed_price', 'customer_type_product_margins:id,product_id,customer_type_id,default_value', 'getPoData:id,product_id,quantity', 'sellingUnits:id,title,decimal_places', 'prouctImages:id,product_id', 'warehouse_products:id,product_id,current_quantity,warehouse_id,available_quantity,reserved_quantity,ecommerce_reserved_quantity', 'check_import_or_not:id,product_id', 'ecomSellingUnits', 'def_or_last_supplier.getcountry:id,name','woocommerce_enabled')->where('products.status', 1);
            if (($request['from_date_exp'] != '' || $request['from_date_exp'] != null) && ($request['to_date_exp'] != '' || $request['to_date_exp'] != null)) {
                $dateS = date("Y-m-d", strtotime(strtr($request['from_date_exp'], '/', '-')));
                $dateE = date("Y-m-d", strtotime(strtr($request['to_date_exp'], '/', '-')));


                $query = $query->whereBetween('products.created_at', [$dateS . " 00:00:00", $dateE . " 23:59:59"]);
            }
            if ($request['default_supplier_exp'] != '') {
                $fileName = "Filtered";
                $supplier_query = $request['default_supplier_exp'];
                $query = $query->whereIn('products.id', SupplierProducts::select('product_id')->where('is_deleted', 0)->where('supplier_id', $supplier_query)->pluck('product_id'));
            }

            if ($request['prod_type_exp'] != '') {
                $fileName = "Filtered";
                $query->where('products.type_id', $request['prod_type_exp'])->where('products.status', 1);
            }
            if ($request['prod_type_2_exp'] != '') {
                $fileName = "Filtered";
                $query->where('products.type_id_2', $request['prod_type_2_exp'])->where('products.status', 1);
            }
            if ($request['prod_type_3_exp'] != '') {
                $fileName = "Filtered";
                $query->where('products.type_id_3', $request['prod_type_3_exp'])->where('products.status', 1);
            }

            if ($request['prod_category_primary_exp'] != '') {
                $fileName = "Filtered";
                $id_split = explode('-', $request["prod_category_primary_exp"]);
                if ($id_split[0] == 'pri') {
                    $query->where('products.primary_category', $id_split[1])->where('products.status', 1);
                } else {
                    $query->whereIn('products.category_id', ProductCategory::select('id')->where('id', $id_split[1])->where('parent_id', '!=', 0)->pluck('id'))->where('products.status', 1);
                }
                // $query->where('products.primary_category', $request['prod_category_primary_exp'])->where('products.status',1);
            }

            if ($request['filter-dropdown_exp'] != '') {
                $fileName = "Filtered";
                if ($request['filter-dropdown_exp'] == 'stock') {
                    $query = $query->whereIn('products.id', WarehouseProduct::select('product_id')->where('current_quantity', '>', 0.005)->pluck('product_id'));
                } elseif ($request['filter-dropdown_exp'] == 'reorder') {
                    $query->where('products.min_stock', '>', 0);
                }
            }

            if ($request['supplier_country'] != null) {
                $query = $query->whereHas('def_or_last_supplier', function($q) use ($request){
                    $q->whereHas('getcountry', function($z) use ($request)
                    {
                        $z->where('id', $request['supplier_country']);
                    });
                });
            }



            $getWarehouses = Warehouse::where('status', 1)->get();

            $getCategories = CustomerCategory::where('is_deleted', 0)->where('show', 1)->get();
            $getCategoriesSuggested = CustomerCategory::where('suggested_price_show', 1)->where('is_deleted', 0)->get();

            $query = Product::ProductListingSorting($request, $query, $getWarehouses, $getCategories, $getCategoriesSuggested, $not_visible_arr);

            if ($request['search_value'] != '') {
                $fileName = "Filtered";
                $search_product = $request["search_value"];

                $query = $query->where(function ($p) use ($search_product) {
                    $p->where('refrence_code', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('hs_code', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('hs_description', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('name', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('short_desc', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('product_notes', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('brand', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('product_notes', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('product_temprature_c', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('import_tax_book', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('vat', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('total_buy_unit_cost_price', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('selling_price', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('unit_conversion_rate', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('weight', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('min_o_qty', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('max_o_qty', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('ecom_product_weight_per_unit', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('long_desc', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('ecommerce_price', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('discount_price', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('ecom_selling_unit', 'LIKE', '%' . $search_product . '%')
                        ->orWhere('selling_unit_conversion_rate', 'LIKE', '%' . $search_product . '%')
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('supplier_products', function ($z) use ($search_product) {
                                $z->where('product_supplier_reference_no', 'LIKE', '%' . $search_product . '%')
                                    ->orWhere('supplier_description', 'LIKE', '%' . $search_product . '%')
                                    ->orWhere('buying_price', 'LIKE', '%' . $search_product . '%')
                                    ->orWhere('buying_price_in_thb', 'LIKE', '%' . $search_product . '%')
                                    ->orWhere('freight', 'LIKE', '%' . $search_product . '%')
                                    ->orWhere('landing', 'LIKE', '%' . $search_product . '%')
                                    ->orWhere('leading_time', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('units', function ($z) use ($search_product) {
                                $z->where('title', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('sellingUnits', function ($z) use ($search_product) {
                                $z->where('title', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('productCategory', function ($z) use ($search_product) {
                                $z->where('title', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('productSubCategory', function ($z) use ($search_product) {
                                $z->where('title', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('productType', function ($z) use ($search_product) {
                                $z->where('title', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('productType2', function ($z) use ($search_product) {
                                $z->where('title', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('productType3', function ($z) use ($search_product) {
                                $z->where('title', 'LIKE', '%' . $search_product . '%');
                            });
                        })
                        ->orWhere(function ($q) use ($search_product) {
                            $q->whereHas('def_or_last_supplier', function ($z) use ($search_product) {
                                $z->where('reference_name', 'LIKE', '%' . $search_product . '%');
                            });
                        });
                });
            }


            if ($ecommerceconfig_status == 1) {
                if ($request['ecom-filter_exp'] == "ecom-enabled") {
                    $query->where('products.ecommerce_enabled', 1);
                }
                if ($request['ecom-filter_exp'] == "wocom-enabled"){
                    $query->has('woocommerce_enabled');
                }
                if ($request['ecom-filter_exp'] == "wocom-disable"){
                    $query->doesntHave('woocommerce_enabled');
                }
            }

            if($request['product_export_button'] == 'erp_export') {
                $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
                $return = \Excel::store(new completeProductExport($query, $not_visible_arr, $global_terminologies, $hide_hs_description, $getWarehouses, $getCategories, $getCategoriesSuggestedPrices, $customer_suggested_prices_array, $product_detail_section), 'Completed-Product-Report.xlsx');
            } elseif($request['product_export_button'] == 'pos_product_export') {
                $return = \Excel::store(new completeProductPosExport($query, $not_visible_arr, $global_terminologies, $hide_hs_description, $getWarehouses, $getCategories, $getCategoriesSuggestedPrices, $customer_suggested_prices_array), 'Pos-products-export.xlsx');
            }



            if ($return) {
                ExportStatus::where('type', 'complete_products')->update(['status' => 0, 'last_downloaded' => date('Y-m-d H:i:s')]);
                return response()->json(['msg' => 'File Saved']);
            }
        } catch (Exception $e) {
            $this->failed($e);
        } catch (MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed($exception)
    {
        ExportStatus::where('type', 'complete_products')->update(['status' => 2, 'exception' => $exception->getMessage()]);
        $failedJobException = new FailedJobException();
        $failedJobException->type = "Complete Products Export";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }
}
