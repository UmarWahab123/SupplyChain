<?php

namespace App\Http\Controllers\Purchasing;

use DB;
use Auth;
use File;
use Excel;
use Image;
use Session;
use App\Menu;
use App\User;
use Validate;
use App\General;
use App\RoleMenu;
use App\Variable;
use Carbon\Carbon;
use App\CourierType;
use App\ExportStatus;
use App\Notification;
use App\PoPaymentRef;
use App\ProductHistory;
use App\ProductsRecord;
use App\ProductQuantity;
use App\QuotationConfig;
use Milon\Barcode\DNS1D;
use App\Helpers\MyHelper;
use App\OrderTransaction;
use App\ImportFileHistory;
use App\Models\Common\Unit;
use App\TransactionHistory;
use App\Models\Common\Brand;
use App\ProductTypeTertiary;
use Illuminate\Http\Request;
use App\Exports\BulkProducts;
use App\PoTransactionHistory;
use App\Events\ProductCreated;
use App\Jobs\AddBulkPricesJob;
use App\Models\Common\Country;
use App\Models\Common\Product;
use App\Models\Sales\Customer;
use App\Models\Common\Currency;
use App\Models\Common\Supplier;
use App\Jobs\AddBulkProductsJob;
use App\Models\Common\Warehouse;
use App\Services\BarcodeService;
use Yajra\Datatables\Datatables;
use App\Models\Common\Deployment;
use App\PurchaseOrderTransaction;
use App\Exports\AllProductsExport;
use App\Exports\soldProductExport;
use App\FiltersForCompleteProduct;
use App\Imports\ProductBulkImport;
use App\Jobs\AccountPayableExpJob;
use App\Jobs\ProductSaleReportJob;
use App\Models\Common\Order\Order;
use App\Models\Common\PaymentType;
use App\Models\Common\ProductType;
use App\Models\Common\TempProduct;
use App\Models\WooCom\EcomProduct;
use App\Jobs\SoldProductsExportJob;
use App\Models\Common\ProductImage;
use App\FiltersForSoldProductReport;
use App\Http\Controllers\Controller;
use App\Jobs\MarginReportBySalesJob;
use App\Models\BarcodeConfiguration;
use App\Models\Common\Configuration;
use Illuminate\Support\Facades\View;
use App\Jobs\MarginReportByOfficeJob;
use App\Exports\completeProductExport;
use App\Models\Common\ProductCategory;
use App\Models\Common\StockOutHistory;
use App\Models\Common\TableHideColumn;
use Illuminate\Support\Facades\Schema;
use App\Jobs\CompleteProductsExportJob;
use App\Jobs\MarginReportByCustomerJob;
use App\Jobs\MarginReportBySupplierJob;
use App\Models\Common\CustomerCategory;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\ProductSaleReportDetailHistory;
use App\Imports\ProductPricesBulkImport;
use App\Jobs\CompleteProductsPosNoteJob;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\StockManagementIn;
use App\Jobs\MoveBulkSupplierProductsJob;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\StatusCheckForSoldProductsExport;
use App\Exports\SupplierAllProductsExport;
use App\Jobs\CompleteProductsPosExportJob;
use App\Jobs\MarginReportByProductNameJob;
use App\Jobs\MarginReportByProductTypeJob;
use App\Jobs\StockMovementReportExportJob;
use App\Imports\ProductSuppliersBulkImport;
use App\Jobs\MarginReportByCustomerTypeJob;
use App\Jobs\MarginReportByProductType2Job;
use App\Jobs\MarginReportByProductType3Job;
use App\Jobs\UserSelectedProductsExportJob;
use App\Models\Common\ProductSecondaryType;
use App\Helpers\Datatables\ProductDatatable;
use App\Models\WooCom\WebEcomProductCategory;
use App\Notifications\AddProductNotification;
use App\Jobs\MarginReportByProductCategoryJob;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Jobs\SoldProductsSupplierMarginDetailExportJob;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Helpers\ProductConfigurationHelper;
use App\Models\Common\Status;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;
use App\Helpers\TransferDocumentHelper;

class ProductController extends Controller
{

    public $curr_quantity;
    public $rsv_quantity;
    protected $user;
    protected $barcode_serv;
    public function __construct()
    {
        $this->barcode_serv = new BarcodeService();
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
        $dummy_data = null;
        if ($this->user && Schema::has('notifications')) {
            $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        }
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;

        $vairables = Variable::select('slug', 'standard_name', 'terminology')->get();
        $global_terminologies = [];
        foreach ($vairables as $variable) {
            if ($variable->terminology != null) {
                $global_terminologies[$variable->slug] = $variable->terminology;
            } else {
                $global_terminologies[$variable->slug] = $variable->standard_name;
            }
        }

        $config = Configuration::first();
        $sys_name = $config->company_name;
        $sys_color = $config;
        $sys_logos = $config;
        $part1 = explode("#", $config->system_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $num1 = hexdec($value);
        $num2 = hexdec('001500');
        $sum = $num1 + $num2;
        $sys_border_color = "#";
        $sys_border_color .= dechex($sum);
        $part1 = explode("#", $config->btn_hover_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $number = hexdec($value);
        $sum = $number + $num2;
        $btn_hover_border = "#";
        $btn_hover_border .= dechex($sum);
        $current_version = '3.8';
        // current controller constructor
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;

        $extra_space_for_select2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        $deployment = Deployment::where('status', 1)->first();

        $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies, 'sys_name' => $sys_name, 'sys_logos' => $sys_logos, 'sys_color' => $sys_color, 'sys_border_color' => $sys_border_color, 'btn_hover_border' => $btn_hover_border, 'current_version' => $current_version, 'dummy_data' => $dummy_data, 'extra_space' => $extra_space_for_select2, 'server' => @$config->server, 'config' => $config, 'deployment' => $deployment, 'product_detail_section' => $product_detail_section]);
    }
    public function index(Request $request)
    {
        $product_category = '';
        $primary_category = '';
        $sub_category = '';
        $product_category;
        $prod_type = '';
        $prod_type_2 = '';
        $prod_type_3 = '';
        $filter = '';
        $ecom_filter = '';
        $selected_supplier = '';
        $from_date = '';
        $to_date = '';
        $product_category_title = '';
        $supplier_country = '';
        $className = '';
        // Parameters from already selected values
        if ($request) {
            $selected_supplier = (int)$request["supplier"];
            $ecom_filter = $request['ecom_filter'];
            $filter = $request['filter'];
            $prod_type = $request['prod_type'];
            $prod_type_2 = $request['prod_type_2'];
            $prod_type_3 = $request['prod_type_3'];
            $sub_category = $request['sub_category'];
            $primary_category = $request['primary_category'];
            $product_category = $request['product_category'];
            $supplier_country = $request['supplier_country'];
            $className = $request['className'];
            if (Auth::user()->role_id == 10) {
                $from_date = $request['from_date'];
                $to_date = $request['to_date'];
            }
        }
        if ($request['product_category'] != null || $request['product_category'] != '') {
            $id_split = explode('-', $request['product_category']);
            $id_split = (int)$id_split[1];
            $product_category_title = ProductCategory::where('id', $id_split)->first()->title;
        }


        // get all products completegetData (push)
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'completed_products')->first();
        $display_prods = ColumnDisplayPreference::where('type', 'completed_products')->where('user_id', Auth::user()->id)->first();
        $suppliers = Supplier::where('status', 1)->orderBy('reference_name')->get();

        $product_parent_categories = ProductCategory::where('parent_id', 0)->with('get_Child')->orderBy('title')->get();
        $product_sub_categories = ProductCategory::where('parent_id', '!=', 0)->orderBy('title')->groupBy('title')->get();
        $product_types = ProductType::all();
        $product_types_2 = ProductSecondaryType::orderBy('title', 'asc')->get();
        $product_types_3 = ProductTypeTertiary::orderBy('title', 'asc')->get();
        // dd($product_parent_category[0]->get_Child);
        $statusCheck = ExportStatus::where('type', 'complete_products')->first();
        $last_downloaded = null;
        if ($statusCheck != null) {
            $last_downloaded = $statusCheck->last_downloaded;
        }
        $getWarehouses = Warehouse::where('status', 1)->get();

        $total_system_units = Unit::whereNotNull('id')->count();
        $getCategories = CustomerCategory::where('is_deleted', 0)->where('show', 1)->get();
        $getCategoriesSuggested = CustomerCategory::where('is_deleted', 0)->where('suggested_price_show', 1)->get();

        $find_index = $getWarehouses->count() * 3;

        $ecommerceconfig = QuotationConfig::where('section', 'ecommerce_configuration')->first();
        if ($ecommerceconfig) {
            $check_status = unserialize($ecommerceconfig->print_prefrences);
            $ecommerceconfig_status = $check_status['status'][0];
        } else {
            $ecommerceconfig_status = '';
        }

        $courier_types = CourierType::select('id', 'type')->where('status', 1)->get();
        $countries = Country::select('id', 'name')->where('status', 1)->get();

        return $this->render('users.products.index', compact('last_downloaded', 'table_hide_columns', 'display_prods', 'suppliers', 'product_sub_categories', 'product_parent_categories', 'product_types', 'getWarehouses', 'getCategories', 'find_index', 'getCategoriesSuggested', 'ecommerceconfig_status', 'total_system_units', 'primary_category', 'sub_category', 'prod_type', 'filter', 'ecom_filter', 'selected_supplier', 'from_date', 'to_date', 'product_category', 'product_category_title', 'className', 'courier_types', 'product_types_2', 'product_types_3', 'prod_type_2', 'prod_type_3', 'supplier_country', 'countries'));
    }

    public function soldProductsReport(Request $request)
    {
        $display_prods = ColumnDisplayPreference::where('type', 'product_sales_report_detail')->where('user_id', Auth::user()->id)->first();
        $from_date = '';
        $to_date = '';
        $warehouse_id = $request->warehouse_id;
        $product_id = $request->product_id;
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'sold_product_report')->first();
        $filter = @$request->filter;
        if ($warehouse_id != null) {
            $draft = "selected";
        } else {
            $draft = "";
        }
        $warehouses = Warehouse::select(['id', 'warehouse_title'])->where('status', 1)->get();
        if (Auth::user()->role_id == 3) {
            $customers = Customer::where(function ($query) {
                $user_id = Auth::user()->id;
                $query->where('primary_sale_id', Auth::user()->id)->orWhereHas('CustomerSecondaryUser', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
            })->where('status', 1)->get();
            $products = Product::select('id', 'refrence_code', 'short_desc')->where('status', 1)->get();
        } else {
            if (Auth::user()->role_id == 9) {
                $customers = Customer::where('ecommerce_customer', 1)->whereNotNull('reference_name')->get();
                $products = Product::select('id', 'refrence_code', 'short_desc')->where('ecommerce_enabled', 1)->get();
            }
        }
        $products = Product::where('status', 1)->get();
        $parentCat = ProductCategory::select(['id', 'title'])->where('parent_id', 0)->orderBy('title')->get();
        $suppliers = Supplier::select('id', 'reference_name')->where('status', 1)->get();

        // New code below
        if (!empty($filter) && $request->filter != "no") {
            $from_date = DB::table('order_products')
                ->join('orders', 'order_products.order_id', '=', 'orders.id')
                ->select('order_products.*', 'orders.created_at AS o_created_at', 'orders.delivery_request_date as o_delivery_date')
                ->where('order_products.product_id', '!=', null);

            if ($request->warehouse_id != null) {
                $users =  User::where('warehouse_id', $request->warehouse_id)->whereNull('parent_id')->pluck('id');
                $orders = Order::whereIn('user_id', $users)->pluck('id');
                $from_date = $from_date->whereIn('order_id', $orders);
                $from_date = $from_date->whereIn('order_id', Order::where('primary_status', 2)->pluck('id'));
            }

            if ($request->product_id != null) {
                $from_date->where('product_id', $request->product_id);
            }
            if ($request->date_type == 2) {
                $from_date = $from_date->get()->min('o_created_at');
            } else {
                $from_date = $from_date->get()->min('o_delivery_date');
            }

            $from_date = $from_date != null ? Carbon::parse($from_date)->format('d/m/Y') : '';


            $to_date = DB::table('order_products')
                ->join('orders', 'order_products.order_id', '=', 'orders.id')
                ->select('order_products.*', 'orders.created_at AS o_created_at', 'orders.delivery_request_date as o_delivery_date')
                ->where('order_products.product_id', '!=', null);

            if ($request->warehouse_id != null) {
                $users =  User::where('warehouse_id', $request->warehouse_id)->pluck('id');
                $orders = Order::whereIn('user_id', $users)->pluck('id');
                $to_date = $to_date->whereIn('order_id', $orders);
                $to_date = $to_date->whereIn('order_id', Order::where('primary_status', 2)->pluck('id'));
            }

            if ($request->product_id != null) {
                $to_date->where('product_id', $request->product_id);
            }
            if ($request->date_type == 2) {
                $to_date = $to_date->get()->max('o_created_at');
            } else {
                $to_date = $to_date->get()->max('o_delivery_date');
            }
            $to_date = $to_date != null ? Carbon::parse($to_date)->format('d/m/Y') : "";
        }

        $date_type = $request->date_type;
        $statusCheck = ExportStatus::where('type', 'sold_product_report')->first();
        $last_downloaded = null;
        if ($statusCheck != null) {
            $last_downloaded = $statusCheck->last_downloaded;
        }

        $p_id    = '';
        $f_date  = '';
        $t_date  = '';
        $inv_ty  = '';
        $w_id    = '';
        $cat_id  = '';
        $cust_id = '';
        $c_ty_id = '';
        $saleid  = '';
        if (Session::get('prod_id')) {
            if ($p_id == 'null') {
                $p_id = '';
            } else {
                $p_id = Session::get('prod_id');
            }
        }
        if (Session::get('from_date')) {
            $f_date = Session::get('from_date');
            if ($f_date != 'null') {
                $f_date = preg_replace('/\_/', '/', $f_date);
            }
        }
        if (Session::get('to_date')) {
            $t_date = Session::get('to_date');
            if ($t_date != 'null') {
                $t_date = preg_replace('/\_/', '/', $t_date);
            }
        }
        if (Session::get('mg_report')) {
            $inv_ty = Session::get('mg_report');
        }
        if (Session::get('w_id')) {
            if ($w_id == 'null') {
                $w_id = '';
            } else {
                $w_id = Session::get('w_id');
            }
        }
        if (Session::get('cat_id')) {
            if ($cat_id == 'null') {
                $cat_id = '';
            } else {
                $cat_id = Session::get('cat_id');
            }
        }
        if (Session::get('cust_id')) {
            if ($cust_id == 'null') {
                $cust_id = '';
            } else {
                $cust_id = Session::get('cust_id');
            }
        }
        if (Session::get('c_ty_id')) {
            if ($c_ty_id == 'null') {
                $c_ty_id = '';
            } else {
                $c_ty_id = Session::get('c_ty_id');
            }
        }
        if (Session::get('saleid')) {
            if ($saleid == 'null') {
                $saleid = '';
            } else {
                $saleid = Session::get('saleid');
            }
        }

        $ecommerceconfig = QuotationConfig::where('section', 'ecommerce_configuration')->first();
        if ($ecommerceconfig) {
            $check_status = unserialize($ecommerceconfig->print_prefrences);
            $ecommerceconfig_status = $check_status['status'][0];
        } else {
            $ecommerceconfig_status = '';
        }

        $sales_persons = User::where('role_id', 3)->whereNull('parent_id')->where('status', 1)->get();
        $getCategories = CustomerCategory::where('is_deleted', 0)->where('show', 1)->get();
        // copied data from appservice provider if condition
        $showRadioButtons = '';

        $globalAccessConfig = QuotationConfig::where('section', 'quotation')->first();
        if ($globalAccessConfig) {
            if ($globalAccessConfig->print_prefrences != null) {
                $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
                foreach ($globalaccessForConfig as $val) {
                    if ($val['slug'] === "radio_buttons") {
                        $showRadioButtons = $val['status'];
                    }
                    if ($val['slug'] === "show_discount") {
                        $showDiscount = $val['status'];
                    }
                    if ($val['slug'] === "show_ppbtn") {
                        $showPrintPickBtn = $val['status'];
                    }
                    if ($val['slug'] === "invoice_date_edit") {
                        $invoiceEditAllow = $val['status'];
                    }
                }
            }
        }
        $product_code = '';
        $product_class = '';
        $product_id_check = $request->product_id != null ? $request->product_id : (Session::get('product_id') != null ? Session::get('product_id') : (Session::get('prod_id') != null ? Session::get('prod_id') : null));
        if ($product_id_check != null && $product_id_check != "null") {
            $product_searched = Product::select('refrence_code', 'id')->where('id', $product_id_check)->first();
            $product_code = $product_searched->refrence_code;
            $product_class = 'cus-' . $product_searched->id;
        }
        $statusCheck = StatusCheckForSoldProductsExport::first();
        $product_types = ProductType::all();
        $product_types_2 = ProductSecondaryType::orderBy('title', 'asc')->get();
        $product_types_3 = ProductTypeTertiary::orderBy('title', 'asc')->get();

        //if redirect from supplier margin report
        $from_supplier_margin = false;
        $from_supplier_margin_from_date = null;
        $from_supplier_margin_to_date = null;
        $from_supplier_margin_id = null;
        if ($request->has('from_supplier_margin')) {
            $from_supplier_margin = true;
            $from_supplier_margin_from_date = $request->from_date;
            $from_supplier_margin_to_date = $request->to_date;
            $from_supplier_margin_id = $request->supplier_id;
        }
        $from_complete_list = $request->from_complete_list;
        return $this->render('users.reports.sold-products-report', compact('invoiceEditAllow', 'showPrintPickBtn', 'showDiscount', 'showRadioButtons', 'warehouses', 'subCats', 'table_hide_columns', 'customers', 'suppliers', 'products', 'warehouse_id', 'product_id', 'draft', 'parentCat', 'filter', 'from_date', 'to_date', 'last_downloaded', 'brands', 'date_type', 'p_id', 'f_date', 't_date', 'inv_ty', 'w_id', 'cat_id', 'cust_id', 'c_ty_id', 'saleid', 'sales_persons', 'display_prods', 'getCategories', 'ecommerceconfig_status', 'product_code', 'product_class', 'product_types', 'product_types_2', 'product_types_3', 'from_supplier_margin', 'from_supplier_margin_from_date', 'from_supplier_margin_to_date', 'from_supplier_margin_id', 'from_complete_list'));
    }

    public function exportSoldProductReport(Request $request)
    {
        $query = OrderProduct::with('product', 'get_order')->select('product_id', 'order_id', 'supplier_id', 'from_warehouse_id', 'id', 'vat', 'total_price', 'unit_price', 'qty_shipped', 'quantity', 'created_at')->whereNotNull('product_id')->whereHas('get_order', function ($q) {
            $q->whereIn('primary_status', [2, 3]);
        });
        if ($request->warehouse_id_exp != null) {
            $users =  User::where('warehouse_id', $request->warehouse_id_exp)->pluck('id');
            $orders = Order::whereIn('user_id', $users)->pluck('id');
            $query = $query->whereIn('order_id', $orders);
        }
        if ($request->customer_id_exp != null) {
            $query = $query->whereIn('order_id', Order::where('customer_id', $request->customer_id_exp)->pluck('id'));
        }
        if ($request->order_type_exp != null) {
            $query = $query->whereIn('order_id', Order::where('primary_status', $request->order_type_exp)->pluck('id'));
        }
        if ($request->product_id_exp != '') {
            $query = $query->where('product_id', $request->product_id_exp);
        }
        if ($request->supplier_id_exp != null) {
            $products_ids = SupplierProducts::where('supplier_id', $request->supplier_id_exp)->where('is_deleted', 0)->pluck('product_id');
            $query = $query->whereIn('product_id', $products_ids);
        }
        if ($request->prod_category_exp != null) {
            $product_ids = Product::where('category_id', $request->prod_category_exp)->where('status', 1)->pluck('id');
            $query = $query->whereIn('product_id', $product_ids);
        }
        if ($request->filter_exp != null) {
            if ($request->filter_exp == 'stock') {
                $query = $query->whereIn('product_id', WarehouseProduct::select('product_id')->where('current_quantity', '>', 0.005)->pluck('product_id'));
            } elseif ($request->filter_exp == 'reorder') {
                $product_ids = Product::where('min_stock', '>', 0)->where('status', 1)->pluck('id');
                $query = $query->whereIn('product_id', $product_ids);
            }
        }

        if ($request->from_date_exp != null) {
            $date = str_replace("/", "-", $request->from_date_exp);
            $date =  date('Y-m-d', strtotime($date));
            $query->whereHas('get_order', function ($q) use ($date) {
                $q->where('created_at', '>=', $date);
            });
        }
        if ($request->to_date_exp != null) {
            $date = str_replace("/", "-", $request->to_date_exp);
            $date =  date('Y-m-d', strtotime($date));
            $query->whereHas('get_order', function ($q) use ($date) {
                $q->where('created_at', '<=', $date . ' 23:59:59');
            });
        }
        $current_date = date("Y-m-d");
        $query = $query->get();
        return \Excel::download(new soldProductExport($query), 'Product sales report - Detail' . $current_date . '.xlsx');
    }

    public function getsoldProductReportFooterValues(Request $request)
    {
        $query = OrderProduct::with('product', 'get_order', 'from_supplier')->select('order_products.product_id', 'order_products.order_id', 'order_products.supplier_id', 'order_products.from_warehouse_id', 'order_products.id', 'order_products.vat', 'order_products.total_price', 'order_products.unit_price', 'order_products.qty_shipped', 'order_products.total_price_with_vat', 'order_products.quantity', 'order_products.created_at', 'order_products.actual_cost', 'order_products.vat_amount_total', 'order_products.type_id', 'order_products.discount', 'order_products.user_warehouse_id', 'order_products.number_of_pieces', 'order_products.pcs_shipped', 'stock_management_outs.order_id as stock_order_id', 'stock_management_outs.quantity_out as stock_quantity_out', 'stock_management_outs.title', 'stock_management_outs.cost', 'stock_management_outs.warehouse_id')->whereNotNull('order_products.product_id')->whereHas('get_order', function ($q) {
            $q->whereIn('primary_status', [2, 3])->where('dont_show', 0);
        });
        if ($request->warehouse_id != null) {
            if ($request->draft != null) {
                $query = $query->where(function ($p) use ($request) {
                    $p->where('from_warehouse_id', $request->warehouse_id)->orWhere(function ($z) use ($request) {
                        $z->whereNull('from_warehouse_id')->whereHas('get_order', function ($y) use ($request) {
                            $y->whereHas('user_created', function ($x) use ($request) {
                                $x->where('warehouse_id', $request->warehouse_id);
                            });
                        });
                    });
                });
            } else {
                if (Auth::user()->role_id == 9 && $request->warehouse_id == 1) {
                    if ($request->product_id != '') {

                        $query = $query->where('order_products.product_id', $request->product_id);
                        $query = $query->whereHas('get_order', function ($q) {
                            $q->where('ecommerce_order', 1);
                        });
                    } else {

                        $query = $query->where(function ($p) use ($request) {
                            $p->where('from_warehouse_id', $request->warehouse_id)->orWhere(function ($z) use ($request) {
                                $z->whereNull('from_warehouse_id')->whereHas('get_order', function ($y) use ($request) {
                                    $y->whereHas('user_created', function ($x) use ($request) {
                                        $x->where('warehouse_id', $request->warehouse_id);
                                    });
                                });
                            });
                        });
                        $query = $query->whereHas('get_order', function ($q) {
                            $q->where('ecommerce_order', 1);
                        });
                    }
                } else {
                    $query = $query->where(function ($p) use ($request) {
                        $p->where('from_warehouse_id', $request->warehouse_id)->orWhere(function ($z) use ($request) {
                            $z->whereNull('from_warehouse_id')->whereHas('get_order', function ($y) use ($request) {
                                $y->whereHas('user_created', function ($x) use ($request) {
                                    $x->where('warehouse_id', $request->warehouse_id);
                                });
                            });
                        });
                    });
                }
            }
        }
        if ($request->customer_id != null) {
            $str = $request->customer_id;
            $split = (explode("-", $str));
            if ($split[0] == 'cus') {
                $customer_id = $split[1];
                $query = $query->whereHas('get_order', function ($z) use ($customer_id) {
                    $z->where('customer_id', $customer_id);
                });
            } else {
                $cat_id = $split[1];
                $query = $query->whereHas('get_order', function ($z) use ($cat_id) {
                    $z->whereHas('customer', function ($cust) use ($cat_id) {
                        $cust->where('category_id', $cat_id);
                    });
                });
            }
        }
        if ($request->c_ty_id != "null" && $request->c_ty_id != null) {
            $getCustByCat = Order::with('customer')->whereHas('customer', function ($q) use ($request) {
                $q->whereHas('CustomerCategory', function ($q1) use ($request) {
                    $q1->where('customer_categories.id', $request->c_ty_id);
                });
            })->where('primary_status', 3)->pluck('id');
            $query = $query->whereIn('order_products.order_id', $getCustByCat);
        }
        if ($request->saleid != "null" && $request->saleid != null) {
            $query = $query->whereHas('get_order', function ($z) use ($request) {
                $z->where('user_id', $request->saleid)->where('primary_status', 3);
            });
        }
        if ($request->sale_person_id != "null" && $request->sale_person_id != null) {
            $query = $query->whereHas('get_order', function ($z) use ($request) {
                $z->where('user_id', $request->sale_person_id);
            });
        }
        if ($request->order_type != null) {
            if ($request->order_type == 0 || $request->order_type == 1) {
                $query = $query->whereHas('get_order', function ($or) use ($request) {
                    $or->where('primary_status', 3)->where('is_vat', $request->order_type);
                });
            } elseif ($request->order_type == 10) {
                $query = $query->whereHas('get_order', function ($or) {
                    $or->where('primary_status', 2);
                });
            } else {
                $query = $query->whereHas('get_order', function ($or) use ($request) {
                    $or->where('primary_status', $request->order_type);
                });
            }
        }
        if ($request->product_idd != '') {
            $query = $query->where('order_products.product_id', $request->product_idd);
        }
        if ($request->supplier_id != null) {
            $products_ids = SupplierProducts::select('id', 'supplier_id', 'is_deleted', 'product_id')->where('supplier_id', $request->supplier_id)->where('is_deleted', 0)->pluck('product_id');
            $query = $query->whereIn('order_products.product_id', $products_ids);
        }
        if ($request->product_id != '') {
            $query = $query->where('order_products.product_id', $request->product_id);
        }
        if ($request->p_c_id != "null" && $request->p_c_id != null) {
            $p_cat_id = ProductCategory::select('id', 'parent_id')->where('parent_id', $request->p_c_id)->pluck('id')->toArray();
            $product_ids = Product::select('id', 'category_id')->whereIn('category_id', $p_cat_id)->pluck('id');
            $query = $query->whereIn('order_products.product_id', $product_ids);
        } else {
            if ($request->product_id_select != null) {
                $id_split = explode('-', $request->product_id_select);
                $id_split = (int)$id_split[1];
                if ($request->className == 'parent') {
                    $p_cat_ids = Product::select('id', 'primary_category', 'status')->where('primary_category', $id_split)->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $p_cat_ids);
                } else if ($request->className == 'child') {
                    $product_ids = Product::select('id', 'category_id', 'status')->where('category_id', $id_split)->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $product_ids);
                } else {
                    $query = $query->where('order_products.product_id', $id_split);
                }
            }
        }

        if ($request->filter != null) {
            if ($request->filter == 'stock') {
                $query = $query->whereIn('order_products.product_id', WarehouseProduct::select('product_id', 'current_quantity')->where('current_quantity', '>', 0.005)->pluck('product_id'));
            } elseif ($request->filter == 'reorder') {
                $product_ids = Product::select('id', 'status', 'min_stock')->where('min_stock', '>', 0)->where('status', 1)->pluck('id');
                $query = $query->whereIn('order_products.product_id', $product_ids);
            } elseif ($request->filter == 'dicount_items') {
                $query = $query->where('order_products.discount', '>', 0);
            }
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->date_type == 2) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('converted_to_invoice_on', '>=', $date . ' 00:00:00');
                });
            } else {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('delivery_request_date', '>=', $date);
                });
            }
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->date_type == 2) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('converted_to_invoice_on', '<=', $date . ' 23:59:59');
                });
            } else {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('delivery_request_date', '<=', $date);
                });
            }
        }
        $query = $query->join('stock_management_outs', 'stock_management_outs.product_id', '=', 'order_products.product_id');
        if ($request->warehouse_id != null) {
            $query->where('stock_management_outs.warehouse_id', $request->warehouse_id);
        }

        $cost_unit_total   = (clone $query)->whereHas('get_order', function ($q) {
            $q->whereIn('primary_status', [2, 3]);
        })->sum('unit_price');

        $total_price_total = (clone $query)->whereHas('get_order', function ($q) {
            $q->whereIn('primary_status', [2, 3]);
        })->sum('total_price_with_vat');

        $sub_total = (clone $query)->whereHas('get_order', function ($q) {
            $q->whereIn('primary_status', [2, 3]);
        })->sum('total_price');

        $total_quantity = (clone $query)->whereHas('get_order', function ($q) {
            $q->where('primary_status', 2);
        })->sum('quantity');
        $total_quantity_manual = (clone $query)->sum('quantity_out');

        $total_quantity2 = (clone $query)->whereHas('get_order', function ($q) {
            $q->where('primary_status', 3);
        })->sum('qty_shipped');

        $total_cogs_val = (clone $query)->whereHas('get_order', function ($q) {
            $q->where('primary_status', 3);
        })->sum(\DB::raw('actual_cost * qty_shipped'));

        $total_vat_thb = (clone $query)->whereHas('get_order', function ($q) {
            $q->whereIn('primary_status', [2, 3]);
        })->sum('vat_amount_total');

        $total_pieces = (clone $query)->whereHas('get_order', function ($q) {
            $q->where('primary_status', 2);
        })->sum('number_of_pieces') + (clone $query)->whereHas('get_order', function ($q) {
            $q->where('primary_status', 3);
        })->sum('pcs_shipped');

        return response()->json(["cost_unit_total" => $cost_unit_total, 'total_price_total' => $total_price_total, 'total_quantity' => ($total_quantity), 'total_quantity2' => $total_quantity2, 'grand_cogs' => $total_cogs_val, 'sub_total' => $sub_total, 'total_vat_thb' => floatval($total_vat_thb), 'total_pieces' => $total_pieces, 'manual_quantity' => abs($total_quantity_manual)]);
    }

    public function getsoldProdDataForReport(Request $request)
    {
        $loggedInSalesPersonId = Auth::user()->id;
        $query = OrderProduct::with('get_order', 'from_supplier', 'product.productType', 'get_order_product_notes', 'get_order.statuses', 'get_order.customer.getbilling', 'purchase_order_detail.PurchaseOrder', 'warehouse_products', 'product.product_fixed_price', 'get_order.user', 'from_warehouse', 'product.productCategory', 'product.productSubCategory', 'product.productType2')->select('order_products.product_id', 'order_products.order_id', 'order_products.supplier_id', 'order_products.from_warehouse_id', 'order_products.id', 'order_products.vat', 'order_products.total_price', 'order_products.unit_price', 'order_products.qty_shipped', 'order_products.pcs_shipped', 'order_products.number_of_pieces', 'order_products.total_price_with_vat', 'order_products.quantity', 'order_products.created_at', 'order_products.actual_cost', 'order_products.vat_amount_total', 'order_products.type_id', 'order_products.discount', 'order_products.user_warehouse_id', 'order_products.status', 'order_products.remarks')->whereNotNull('order_products.product_id')->whereHas('get_order', function ($q) {
            $q->whereIn('primary_status', [2, 3, 37])->where('dont_show', 0);
        });
        if (Auth::user()->role_id == 3) {
            $primaryCustomers = Auth::user()->customersByPrimarySalePerson   ? Auth::user()->customersByPrimarySalePerson()->pluck('id')->toArray() : [];
            $secondaryCustomers = Auth::user()->customersBySecondarySalePerson ? Auth::user()->customersBySecondarySalePerson()->pluck('customer_id')->toArray() : [];
        } else {
            $primaryCustomers = [];
            $secondaryCustomers = [];
        }
        $customersRelatedToSalesPerson = array_merge($primaryCustomers, $secondaryCustomers);
        if ($request->warehouse_id != null) {
            if ($request->draft != null) {
                $query = $query->where(function ($p) use ($request) {
                    $p->where('from_warehouse_id', $request->warehouse_id)->orWhere(function ($z) use ($request) {
                        $z->whereNull('from_warehouse_id')->whereHas('get_order', function ($y) use ($request) {
                            $y->whereHas('user_created', function ($x) use ($request) {
                                $x->where('warehouse_id', $request->warehouse_id);
                            });
                        });
                    });
                });
            } else {
                if (Auth::user()->role_id == 9 && $request->warehouse_id == 1) {
                    if ($request->product_id != '') {
                        $query = $query->where('order_products.product_id', $request->product_id);
                        $query = $query->whereHas('get_order', function ($q) {
                            $q->where('ecommerce_order', 1);
                        });
                    } else {
                        $query = $query->where(function ($p) use ($request) {
                            $p->where('from_warehouse_id', $request->warehouse_id)->orWhere(function ($z) use ($request) {
                                $z->whereNull('from_warehouse_id')->whereHas('get_order', function ($y) use ($request) {
                                    $y->whereHas('user_created', function ($x) use ($request) {
                                        $x->where('warehouse_id', $request->warehouse_id);
                                    });
                                });
                            });
                        });
                        $query = $query->whereHas('get_order', function ($q) {
                            $q->where('ecommerce_order', 1);
                        });
                    }
                } else {
                    $query = $query->where(function ($p) use ($request) {
                        $p->where('from_warehouse_id', $request->warehouse_id)->orWhere(function ($z) use ($request) {
                            $z->whereNull('from_warehouse_id')->whereHas('get_order', function ($y) use ($request) {
                                $y->whereHas('user_created', function ($x) use ($request) {
                                    $x->where('warehouse_id', $request->warehouse_id);
                                });
                            });
                        });
                    });
                }
            }
        }
        if ($request->c_ty_id != "null" && $request->c_ty_id != null) {
            $getCustByCat = Order::with('customer')->whereHas('customer', function ($q) use ($request) {
                $q->whereHas('CustomerCategory', function ($q1) use ($request) {
                    $q1->where('customer_categories.id', $request->c_ty_id);
                });
            })->where('primary_status', 3)->pluck('id');
            $query = $query->whereIn('order_products.order_id', $getCustByCat);
        }
        if ($request->saleid != "null" && $request->saleid != null) {
            $user = User::find($request->saleid);
            if ($user) {
                $primaryCustomers = $user->customersByPrimarySalePerson   ? $user->customersByPrimarySalePerson()->pluck('id')->toArray() : [];
                $secondaryCustomers = $user->customersBySecondarySalePerson ? $user->customersBySecondarySalePerson()->pluck('customer_id')->toArray() : [];
            } else {
                $primaryCustomers = [];
                $secondaryCustomers = [];
            }
            $customersRelatedToSalesPerson = array_merge($primaryCustomers, $secondaryCustomers);

            $query = $query->whereHas('get_order', function ($z) use ($request, $customersRelatedToSalesPerson) {
                $z->where(function ($z) use ($request, $customersRelatedToSalesPerson) {
                    $z->where('user_id', $request->saleid)->orWhereIn('customer_id', $customersRelatedToSalesPerson);
                })->where('primary_status', 3);
            });
        } else if (Auth::user()->role_id == 3) {
            $user_i = Auth::user()->id;
            $query = $query->whereHas('get_order', function ($z) use ($user_i, $customersRelatedToSalesPerson) {
                //$z->where('user_id',$user_i->saleid)->where('primary_status',3);
                $z->where(function ($op) use ($user_i, $customersRelatedToSalesPerson) {
                    $op->where('user_id', $user_i)->orWhereIn('customer_id', $customersRelatedToSalesPerson);
                });
            });
        }
        if ($request->sale_person_id != "null" && $request->sale_person_id != null) {
            $user_primary_customers = Customer::where('primary_sale_id', $request->sale_person_id)->pluck('id')->toArray();
            $query = $query->whereHas('get_order', function ($z) use ($request, $user_primary_customers) {
                $z->where('user_id', $request->sale_person_id)->orWhereIn('customer_id', $user_primary_customers);
            });
        } else if (Auth::user()->role_id == 3) {
            $user_i = Auth::user()->id;
            $query = $query->whereHas('get_order', function ($z) use ($user_i, $customersRelatedToSalesPerson) {
                $z->where(function ($op) use ($user_i, $customersRelatedToSalesPerson) {
                    $op->where('user_id', $user_i)->orWhereIn('customer_id', $customersRelatedToSalesPerson)->orWhereIn('customer_id', Auth::user()->user_customers_secondary->pluck('customer_id')->toArray());
                });
            });
        }
        if ($request->customer_id != null) {
            $str = $request->customer_id;
            $split = (explode("-", $str));
            if ($split[0] == 'cus') {
                $customer_id = $split[1];
                $customer = Customer::find($customer_id);
                if ($customer != null && @$customer->manual_customer == 1) {
                    $query = $query->whereHas('get_order', function ($z) use ($customer_id) {
                        $z->where('customer_id', $customer_id);
                    });
                } else {
                    $query = $query->whereHas('get_order', function ($z) use ($customer_id) {
                        $z->where('customer_id', $customer_id);
                    });
                }
            } else {
                $cat_id = $split[1];
                $query = $query->whereHas('get_order', function ($z) use ($cat_id) {
                    $z->whereHas('customer', function ($cust) use ($cat_id) {
                        $cust->where('category_id', $cat_id);
                    });
                });
            }
        }
        if ($request->order_type != null) {
            if ($request->order_type == 0 || $request->order_type == 1) {
                $query = $query->whereHas('get_order', function ($or) use ($request) {
                    $or->where('primary_status', 3)->where('is_vat', $request->order_type);
                });
            } elseif ($request->order_type == 10) {
                $query = $query->whereHas('get_order', function ($or) {
                    $or->where('primary_status', 2);
                });
            } elseif ($request->order_type == 38) {
                $query = $query->whereHas('get_order', function ($or) {
                    $or->whereIn('primary_status', [3, 37]);
                });
            } else {
                $query = $query->whereHas('get_order', function ($or) use ($request) {
                    $or->where('primary_status', $request->order_type);
                });
            }
        }
        if ($request->product_idd != '') {
            $query = $query->where('order_products.product_id', $request->product_idd);
        }
        if ($request->product_id != '') {
            $query = $query->where('order_products.product_id', $request->product_id);
        }
        if ($request->p_c_id != "null" && $request->p_c_id != null) {
            $p_cat_id = ProductCategory::select('id', 'parent_id')->where('parent_id', $request->p_c_id)->pluck('id')->toArray();
            $product_ids = Product::select('id', 'category_id')->whereIn('category_id', $p_cat_id)->pluck('id');
            $query = $query->whereIn('order_products.product_id', $product_ids);
        } else {
            if ($request->prod_category != null) {
                $cat_id_split = explode('-', $request->prod_category);
                if ($cat_id_split[0] == 'sub') {
                    $product_ids = Product::select('id', 'category_id', 'status')->where('category_id', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $product_ids);
                } else {
                    $p_cat_ids = Product::select('id', 'primary_category', 'status')->where('primary_category', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $p_cat_ids);
                }
            }
        }
        if ($request->supplier_id != null) {
            $products_ids = SupplierProducts::select('id', 'supplier_id', 'is_deleted', 'product_id')->where('supplier_id', $request->supplier_id)->where('is_deleted', 0)->pluck('product_id');
            $query = $query->whereIn('order_products.product_id', $products_ids);
        }
        if ($request->filter != null) {
            if ($request->filter == 'stock') {
                $query = $query->whereIn('order_products.product_id', WarehouseProduct::select('product_id', 'current_quantity')->where('current_quantity', '>', 0.005)->pluck('product_id'));
            } elseif ($request->filter == 'reorder') {
                $product_ids = Product::select('id', 'status', 'min_stock')->where('min_stock', '>', 0)->where('status', 1)->pluck('id');
                $query = $query->whereIn('order_products.product_id', $product_ids);
            } elseif ($request->filter == 'dicount_items') {
                $query = $query->where('order_products.discount', '>', 0);
            }
        }
        if ($request->product_type != null) {
            $query = $query->whereHas('product', function ($p) use ($request) {
                $p->where('type_id', $request->product_type);
            });
        }
        if ($request->product_type_2 != null) {
            $query = $query->whereHas('product', function ($p) use ($request) {
                $p->where('type_id_2', $request->product_type_2);
            });
        }
        if ($request->product_type_3 != null) {
            $query = $query->whereHas('product', function ($p) use ($request) {
                $p->where('type_id_3', $request->product_type_3);
            });
        }
        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->date_type == 2) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('converted_to_invoice_on', '>=', $date . ' 00:00:00');
                });
            } else if ($request->date_type == 1) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('delivery_request_date', '>=', $date);
                });
            } else {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('target_ship_date', '>=', $date);
                });
            }
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->date_type == 2) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('converted_to_invoice_on', '<=', $date . ' 23:59:59');
                });
            } else if ($request->date_type == 1) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('delivery_request_date', '<=', $date);
                });
            } else {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('target_ship_date', '<=', $date);
                });
            }
        }

        $query = OrderProduct::doSortby($request, $query);

        $getCategories = CustomerCategory::select('id', 'title', 'is_deleted')->where('is_deleted', 0)->get();
        $not_visible_arr = [];
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'sold_product_report')->first();
        if ($table_hide_columns != null) {
            $not_visible_arr = explode(',', $table_hide_columns->hide_columns);
        }
        if ($request->type == 'footer') {
            $cost_unit_total   = (clone $query)->whereHas('get_order', function ($q) {
                $q->whereIn('primary_status', [2, 3]);
            })->sum('unit_price');
            $total_price_total = (clone $query)->whereHas('get_order', function ($q) {
                $q->whereIn('primary_status', [2, 3]);
            })->sum('total_price_with_vat');
            $sub_total = (clone $query)->whereHas('get_order', function ($q) {
                $q->whereIn('primary_status', [2, 3]);
            })->sum('total_price');
            $total_quantity = (clone $query)->whereHas('get_order', function ($q) {
                $q->where('primary_status', 2)->orWhere('primary_status', 37);
            })->sum('quantity');
            $total_manual = 0;
            $total_quantity2 = (clone $query)->whereHas('get_order', function ($q) {
                $q->where('primary_status', 3);
            })->sum('qty_shipped');
            $total_cogs_val = (clone $query)->whereHas('get_order', function ($q) {
                $q->where('primary_status', 3)->orWhere('primary_status', 37);
            })->sum(\DB::raw('actual_cost * qty_shipped'));
            $total_cogs_manual = 0;
            $total_vat_thb = (clone $query)->whereHas('get_order', function ($q) {
                $q->whereIn('primary_status', [2, 3]);
            })->sum('vat_amount_total');
            $total_pieces = (clone $query)->whereHas('get_order', function ($q) {
                $q->where('primary_status', 2);
            })->sum('number_of_pieces') + (clone $query)->whereHas('get_order', function ($q) {
                $q->where('primary_status', 3);
            })->sum('pcs_shipped');
            return response()->json(["cost_unit_total" => $cost_unit_total, 'total_price_total' => $total_price_total, 'total_quantity' => $total_quantity, 'total_quantity2' => $total_quantity2, 'grand_cogs' => $total_cogs_val, 'sub_total' => $sub_total, 'total_vat_thb' => floatval($total_vat_thb), 'total_pieces' => $total_pieces, 'total_manual' => $total_manual, 'total_cogs_manual' => $total_cogs_manual]);
        }
        $dt = Datatables::of($query);
        if (!in_array('0', $not_visible_arr)) {
            $dt->addColumn('ref_id', function ($item) {

                if ($item->order_id == null) {
                    return $item->type_id;
                } else if ($item->order_id != null) {
                    $order = @$item->get_order;
                    $ret = $order->get_order_number_and_link($order);
                    $ref_no = $ret[0];
                    $link = $ret[1];
                    return $title = '<a target="_blank" href="' . route($link, ['id' => $order->id]) . '" title="View Detail" class=""><b>' . $ref_no . '</b></a>';
                } else {
                    return "--";
                }
            });
        } else {
            $dt->addColumn('ref_id', function ($item) {
                return '--';
            });
        }
        if (!in_array('1', $not_visible_arr)) {
            $dt->addColumn('status', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $ref_po_no = @$item->get_order != null ? (@$item->get_order->statuses != null ? @$item->get_order->statuses->title : 'N.A') : 'N.A';
                return  $ref_po_no;
            });
        } else {
            $dt->addColumn('status', function ($item) {
                return '--';
            });
        }
        if (!in_array('2', $not_visible_arr)) {
            $dt->addColumn('ref_po_no', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $status = @$item->get_order != null ? (@$item->get_order->memo != NULL ? @$item->get_order->memo : "N.A") : 'N.A';
                return  $status;
            });
        } else {
            $dt->addColumn('ref_po_no', function ($item) {
                return '--';
            });
        }
        if (!in_array('3', $not_visible_arr)) {
            $dt->addColumn('po_no', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return @$item->purchase_order_detail != null ?  $item->purchase_order_detail->PurchaseOrder->ref_id : '--';
            });
        } else {
            $dt->addColumn('po_no', function ($item) {
                return '--';
            });
        }
        if (!in_array('4', $not_visible_arr)) {
            $dt->addColumn('customer_no', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $customer = @$item->get_order->customer;
                return  $customer !== null ? @$customer->reference_number : 'N.A';
            });
        } else {
            $dt->addColumn('customer_no', function ($item) {
                return '--';
            });
        }
        if (!in_array('5', $not_visible_arr)) {
            $dt->addColumn('customer', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $customer = @$item->get_order->customer;
                return  $customer !== null ? @$customer->reference_name : 'N.A';
            });
        } else {
            $dt->addColumn('customer', function ($item) {
                return '--';
            });
        }
        if (!in_array('6', $not_visible_arr)) {
            $dt->addColumn('billing_name', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $customer = @$item->get_order->customer;
                return  $customer !== null ? @$customer->company : 'N.A';
            });
        } else {
            $dt->addColumn('billing_name', function ($item) {
                return '--';
            });
        }
        if (!in_array('7', $not_visible_arr)) {
            $dt->addColumn('tax_id', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $billing = @$item->get_order->customer->getbilling->where('is_default', 1)->first();
                return  $billing !== null ? @$billing->tax_id : 'N.A';
            });
        } else {
            $dt->addColumn('tax_id', function ($item) {
                return '--';
            });
        }
        if (!in_array('8', $not_visible_arr)) {
            $dt->addColumn('reference_address', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $billing = @$item->get_order->customer->getbilling->where('is_default', 1)->first();

                return  $billing !== null ? @$billing->title : 'N.A';
            });
        } else {
            $dt->addColumn('reference_address', function ($item) {
                return '--';
            });
        }

        if (!in_array('9', $not_visible_arr)) {
            $dt->addColumn('sale_person', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $sale_person = @$item->get_order != null ? (@$item->get_order->user_id != null ? @$item->get_order->user->name : '--') : '--';
                return $sale_person;
            });
        } else {
            $dt->addColumn('sale_person', function ($item) {
                return '--';
            });
        }
        if (!in_array('10', $not_visible_arr)) {
            $dt->addColumn('delivery_date', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $order = @$item->get_order;
                return  @$order->delivery_request_date !== null ? Carbon::parse(@$order->delivery_request_date)->format('d/m/Y') : 'N.A';
            });
        } else {
            $dt->addColumn('delivery_date', function ($item) {
                return '--';
            });
        }
        if (!in_array('11', $not_visible_arr)) {
            $dt->addColumn('created_date', function ($item) {
                if ($item->order_id == null) {
                    return Carbon::parse(@$item->created_at)->format('d/m/Y');
                }
                $order = @$item->get_order;
                return  @$order->converted_to_invoice_on !== null ? Carbon::parse(@$order->converted_to_invoice_on)->format('d/m/Y') : 'N.A';
            });
        } else {
            $dt->addColumn('created_date', function ($item) {
                return '--';
            });
        }
        if (!in_array('12', $not_visible_arr)) {
            $dt->addColumn('target_ship_date', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $order = @$item->get_order;
                return  @$order->target_ship_date !== null ? Carbon::parse(@$order->target_ship_date)->format('d/m/Y') : 'N.A';
            });
        } else {
            $dt->addColumn('target_ship_date', function ($item) {
                return '--';
            });
        }
        if (!in_array('13', $not_visible_arr)) {
            $dt->addColumn('supply_from', function ($item) {

                if ($item->supplier_id != NULL && $item->from_warehouse_id == NULL) {
                    return @$item->from_supplier->reference_name;
                } elseif ($item->from_warehouse_id != NULL && $item->supplier_id == NULL) {
                    return @$item->from_warehouse->warehouse_title;
                } else {
                    return "N.A";
                }
            });
        } else {
            $dt->addColumn('supply_from', function ($item) {
                return '--';
            });
        }
        if (!in_array('14', $not_visible_arr)) {
            $dt->editColumn('refrence_code', function ($item) {
                $refrence_code = $item->product_id != null ? '<a target="_blank" href="' . url('get-product-detail/' . $item->product->id) . '" ><b>' . $item->product->refrence_code . '</b></a>' : "N.A";
                return  $refrence_code;
            });
        } else {
            $dt->addColumn('refrence_code', function ($item) {
                return '--';
            });
        }
        if (!in_array('15', $not_visible_arr)) {
            $dt->editColumn('primary_sub_cat', function ($item) {
                $refrence_code = $item->product != null ? ($item->product->productCategory != null ? $item->product->productCategory->title : '') : '';
                $refrence_code .= $item->product != null ? ($item->product->productSubCategory != null ? (' / ' . $item->product->productSubCategory->title) : '') : '';
                return  $refrence_code;
            });
        } else {
            $dt->addColumn('primary_sub_cat', function ($item) {
                return '--';
            });
        }
        if (!in_array('16', $not_visible_arr)) {
            $dt->addColumn('product_type', function ($item) {
                if ($item->product != null) {
                    return $item->product->productType != null ? $item->product->productType->title : '--';
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('product_type', function ($item) {
                return '--';
            });
        }
        if (!in_array('17', $not_visible_arr)) {
            $dt->addColumn('brand', function ($item) {
                if ($item->brand != null) {
                    return $item->brand;
                } elseif ($item->product_id != null) {
                    if ($item->product->brand != null) {
                        return $item->product->brand;
                    } else {
                        return '--';
                    }
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('brand', function ($item) {
                return '--';
            });
        }
        if (!in_array('18', $not_visible_arr)) {
            $dt->addColumn('short_desc', function ($item) {
                return $item->product_id !== null ? '<span id="short_desc">' . $item->product->short_desc . '</span>' : 'N.A';
            });
        } else {
            $dt->addColumn('short_desc', function ($item) {
                return '--';
            });
        }
        if (!in_array('19', $not_visible_arr)) {
            $dt->addColumn('vintage', function ($item) {
                return $item->product_id !== null ? (@$item->product->productType2 != null ? @$item->product->productType2->title : 'N.A') : 'N.A';
            });
        } else {
            $dt->addColumn('vintage', function ($item) {
                return '--';
            });
        }
        if (!in_array('20', $not_visible_arr)) {
            $dt->addColumn('product_type_3', function ($item) {
                return $item->product_id !== null ? (@$item->product->productType3 != null ? @$item->product->productType3->title : 'N.A') : 'N.A';
            });
        } else {
            $dt->addColumn('product_type_3', function ($item) {
                return '--';
            });
        }
        if (!in_array('21', $not_visible_arr)) {
            $dt->addColumn('available_stock', function ($item) use ($request) {
                $warehouse_id = $request->warehouse_id;

                if ($warehouse_id != null) {
                    $stock = $item->product != null ? $item->product->get_stock($item->product->id, $warehouse_id) : 'N.A';
                    return $stock;
                } else {
                    $warehouse_product = $item->warehouse_products->sum('available_quantity');
                    return $warehouse_product != null ? number_format((float)$warehouse_product, 3, '.', '') : 0;
                }
            });
        } else {
            $dt->addColumn('available_stock', function ($item) {
                return '--';
            });
        }
        if (!in_array('22', $not_visible_arr)) {
            $dt->addColumn('unit', function ($item) {
                return $item->product_id !== null ? $item->product->sellingUnits->title : 'N.A';
            });
        } else {
            $dt->addColumn('unit', function ($item) {
                return '--';
            });
        }
        if (!in_array('23', $not_visible_arr)) {
            $dt->addColumn('sum_qty', function ($item) {
                if ($item->order_id == null) {
                    return abs($item->quantity);
                }
                if (@$item->get_order->primary_status == 2) {
                    $qty = $item->quantity !== null ? number_format((float)$item->quantity, 2) : 'N.A';
                } else {
                    $qty = $item->qty_shipped !== null ? number_format((float)$item->qty_shipped, 2) : 'N.A';
                }
                $html = '<span id="qty-' . $item->id . '">' . $qty . '</span>';
                return $html;
            });
        } else {
            $dt->addColumn('sum_qty', function ($item) {
                return '--';
            });
        }
        if (!in_array('24', $not_visible_arr)) {
            $dt->addColumn('sum_piece', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                if (@$item->get_order->primary_status == 2) {
                    $qty = $item->number_of_pieces !== null ? number_format((float)$item->number_of_pieces, 2) : 'N.A';
                } else {
                    $qty = $item->pcs_shipped !== null ? number_format((float)$item->pcs_shipped, 2) : 'N.A';
                }
                return $qty;
            });
        } else {
            $dt->addColumn('sum_piece', function ($item) {
                return '--';
            });
        }
        if (!in_array('25', $not_visible_arr)) {
            $dt->addColumn('cost_unit', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return $item->unit_price !== null ? number_format((float)$item->unit_price, 2) : 'N.A';
            });
        } else {
            $dt->addColumn('cost_unit', function ($item) {
                return '--';
            });
        }
        if (!in_array('26', $not_visible_arr)) {
            $dt->addColumn('discount_value', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return $item->discount !== null ? number_format((float)$item->discount, 2, '.', '') : 'N.A';
            });
        } else {
            $dt->addColumn('discount_value', function ($item) {
                return '--';
            });
        }
        if (!in_array('27', $not_visible_arr)) {
            $dt->addColumn('item_cogs', function ($item) {
                if ($item->order_id == null) {
                    return number_format((float)$item->actual_cost, 2);
                }
                $order = @$item->get_order;
                if (@$order->primary_status == 3) {
                    $cogs_value = $item->actual_cost;
                    $total = number_format(floatval($cogs_value), 2);
                    $total = $total ? $total : 0;
                    $html_string = '<a href="javascript:void(0)" id="cogs-' . $item->id . '" class="purchase_report_w_pm" data-expiry="' . $item->expiration_date . '" data-pid="' . $item->product_id . '" data-oid="' . $order->id . '" data-value="' . (round($item->actual_cost, 2)) . '" title="View Detail"><b>' . $total . '</b></a> <a title="Edit COGS" href="javascript:;" id="edit-cogs-' . $item->id . '" data-id="' . $item->id . '" class="btn-edit"><i class="fa fa-pencil"></i><a><input class="d-none edit-input" type="text" id="input-cogs-' . $item->id . '" data-id="' . $item->id . '" style="width:65px">';
                    return $html_string;
                } else if (@$order->primary_status == 37) {
                    return number_format(floatval($item->actual_cost), 2);
                } else {
                    return 'DRAFT';
                }
            });
        } else {
            $dt->addColumn('item_cogs', function ($item) {
                return '--';
            });
        }
        if (!in_array('28', $not_visible_arr)) {
            $dt->addColumn('cogs', function ($item) {
                if ($item->order_id == null) {
                    return number_format($item->actual_cost * abs($item->quantity), 2);
                }
                $order = @$item->get_order;
                if (@$order->primary_status == 3) {
                    if ($item->actual_cost != null && $item->actual_cost != '' && $item->qty_shipped != null && $item->qty_shipped != '') {
                        $cogs_value = floatval($item->actual_cost) * floatval($item->qty_shipped);
                    } else {
                        $cogs_value = 0;
                    }
                    $total = $cogs_value != null ? number_format($cogs_value, 2) : 0;
                    $html = '<span id="total-cogs-' . $item->id . '">' . $total . '</span>';
                    return $html;
                } else if (@$order->primary_status == 37) {
                    return number_format(floatval($item->actual_cost * $item->quantity), 2);
                } else {
                    return 'DRAFT';
                }
            });
        } else {
            $dt->addColumn('cogs', function ($item) {
                return '--';
            });
        }
        if (!in_array('29', $not_visible_arr)) {
            $dt->addColumn('sub_total', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return $item->total_price !== null ? number_format((float)$item->total_price, 2) : 'N.A';
            });
        } else {
            $dt->addColumn('sub_total', function ($item) {
                return '--';
            });
        }
        if (!in_array('30', $not_visible_arr)) {
            $dt->addColumn('total_cost', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return $item->total_price_with_vat !== null ? number_format((float)$item->total_price_with_vat, 2) : 'N.A';
            });
        } else {
            $dt->addColumn('total_cost', function ($item) {
                return '--';
            });
        }
        if (!in_array('31', $not_visible_arr)) {
            $dt->addColumn('vat_thb', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return $item->vat_amount_total !== null ? number_format((float)$item->vat_amount_total, 2, '.', ',') : 'N.A';
            });
        } else {
            $dt->addColumn('vat_thb', function ($item) {
                return '--';
            });
        }
        if (!in_array('32', $not_visible_arr)) {
            $dt->addColumn('vat', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return $item->vat !== null ? $item->vat . ' %' : 'N.A';
            });
        } else {
            $dt->addColumn('vat', function ($item) {
                return '--';
            });
        }
        if (!in_array('33', $not_visible_arr)) {
            $dt->addColumn('note', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                if ($item->status == 38) {
                    return $item->remarks;
                }
                $html_string = '';
                $order_notes = @$item->get_order_product_notes;
                if ($order_notes->count() > 0) {
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="' . $item->id . '" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
                } else {
                    $html_string = '--';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('note', function ($item) {
                return '--';
            });
        }
        if (!in_array('34', $not_visible_arr)) {
            $dt->addColumn('total_margin', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $sales = $item->total_price;
                $cogs = $item->actual_cost * $item->qty_shipped;
                $margin = $sales - $cogs;
                return number_format($margin, 2,'.',',');
            });
        } else {
            $dt->addColumn('total_margin', function ($item) {
                return '--';
            });
        }
        if (!in_array('35', $not_visible_arr)) {
            $dt->addColumn('margin_percent', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $sales = $item->total_price;

                if ($sales != 0) {
                    $cogs = $item->actual_cost * $item->qty_shipped;
                    $margin = (($sales - $cogs)/$sales) * 100;
                    return number_format($margin, 2,'.',',') . ' %';
                }
                return '0 %';
            });
        } else {
            $dt->addColumn('margin_percent', function ($item) {
                return '--';
            });
        }
        $sold_count = 36;
        //Customer Category Dynamic Columns Starts Here
        if ($getCategories->count() > 0) {
            foreach ($getCategories as $cat) {
                if (!in_array($sold_count++, $not_visible_arr)) {
                    $dt->addColumn($cat->title, function ($item) use ($cat) {
                        $fixed_value = $item->product->product_fixed_price
                            ->where('customer_type_id', $cat->id)
                            ->first();
                        $value = $fixed_value != null ? $fixed_value->fixed_price : '0.00';
                        $formated_value = number_format($value, 3, '.', ',');
                        return $formated_value;
                    });
                } else {
                    $dt->addColumn($cat->title, function ($item) {
                        return '--';
                    });
                }
            }
        }
        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->escapeColumns([]);
        $dt->rawColumns(['ref_id', 'warehouse', 'customer', 'refrence_code', 'short_desc', 'unit', 'sum_qty', 'cost_unit', 'total_cost', 'vat', 'supply_from', 'created_date', 'delivery_date', 'brand', 'cogs', 'item_cogs', 'vat_thb', 'vintage', 'available_stock', 'status', 'sale_person', 'ref_po_no', 'discount_value', 'note', 'sum_piece', 'primary_sub_cat']);
        return $dt->make(true);
    }

    public function getCogsDetails(Request $request)
    {
        $html_string = '';
        $smi = StockManagementIn::where('product_id', $request->prod_id)->where('expiration_date', $request->exp_date)->get();
        $o_p = OrderProduct::where('order_id', $request->ordr_id)->where('product_id', $request->prod_id)->get();
        $getCogsDetails = StockManagementOut::where('product_id', $request->prod_id)->get();
    }

    public function productSalesReport(Request $request)
    {
        $customer_id = null;
        $warehouses = Warehouse::where('status', 1)->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'product_sale_report')->first();
        $product_parent_categories = ProductCategory::where('parent_id', 0)->with('get_Child')->orderBy('title')->get();
        $product_sub_categories = ProductCategory::where('parent_id', '!=', 0)->orderBy('title')->groupBy('title')->get();
        if (Auth::user()->role_id == 3) {
            $user_id = Auth::user()->id;
            $customers = Customer::where(function ($query) use ($user_id) {
                $query->where('primary_sale_id', $user_id)->orWhereHas('CustomerSecondaryUser', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
            })->where('status', 1)->orderBy('reference_name')->get();
        } else {
            $customers = Customer::where('status', 1)->orderBy('reference_name')->get();
        }
        $products            = Product::where('status', 1)->get();
        $suppliers           = Supplier::where('status', 1)->orderBy('reference_name')->get();
        $customer_categories = CustomerCategory::where('is_deleted', 0)->with('customer')->get();
        $sales_persons = User::where('status', 1)->whereNull('parent_id')->where('role_id', 3)->get();
        $filter = @$request->from_margin_report;
        if (!empty($filter)) {
            $customer_id = @$request->customer_id;
            Session::put('customer_id', $customer_id);
        }
        $getCategories = CustomerCategory::where('is_deleted', 0)->where('show', 1)->get();
        $product_types = ProductType::all();
        $product_types_2 = ProductSecondaryType::orderBy('title', 'asc')->get();
        $product_types_3 = ProductTypeTertiary::orderBy('title', 'asc')->get();
        $warehouses = Warehouse::where('status', 1)->get();
        return $this->render('users.reports.product-sales-report', compact('warehouses', 'product_parent_categories', 'product_sub_categories', 'customers', 'products', 'suppliers', 'customer_categories', 'sales_persons', 'getCategories', 'table_hide_columns', 'product_types', 'product_types_2', 'product_types_3', 'warehouses'));
    }

    public function getProductSalesReportData(Request $request)
    {
        $id_split = null;
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $supplier_id = $request->supplier_id;
        $sales_person = $request->sales_person;
        $order_type = $request->order_type;
        $customer_orders_ids = NULL;
        $warehouse_id = $request->warehouse_id;

        $products = Product::select(DB::raw('SUM(CASE
      WHEN o.primary_status="2" THEN op.quantity
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS QuantityText,
      SUM(CASE
      WHEN o.primary_status="2" THEN op.number_of_pieces
      WHEN o.primary_status="3" THEN op.pcs_shipped
      END) AS PiecesText,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat
      END) AS TotalAmount,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.vat_amount_total
      END) AS VatTotalAmount,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price
      END) AS totalPriceSub,
      SUM(CASE
      WHEN (o.primary_status="3") THEN ((op.actual_cost * op.qty_shipped))
      END) AS totalCogs,
      (CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN (SUM(op.locked_actual_cost)/COUNT(op.id))
      END) AS TotalAverage,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat END)/SUM(CASE
      WHEN o.primary_status="2" THEN op.quantity
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS avg_unit_price'), 'products.refrence_code', 'products.selling_unit', 'products.short_desc', 'op.product_id', 'op.vat_amount_total', 'op.total_price', 'products.id', 'products.category_id', 'products.primary_category', 'products.brand', 'products.ecommerce_enabled', 'o.primary_status', 'o.created_by', 'o.dont_show', 'o.user_id', 'products.type_id', 'products.selling_price', 'products.type_id_2', 'products.type_id_3', 'o.customer_id')->whereIn('o.primary_status', [2, 3])->groupBy('op.product_id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');

        if ($supplier_id != null) {
            $products = $products->where('products.supplier_id', $supplier_id);
        }
        if ($order_type != null) {
            $products = $products->where('o.primary_status', $order_type);
        }
        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            if ($request->date_type == '2') {
                $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
            }
            if ($request->date_type == '1') {
                $products = $products->where('o.delivery_request_date', '>=', $from_date);
            }
        }
        if ($request->category_id != null) {
            $cat_id_split = explode('-', $request->category_id);
            if ($cat_id_split[0] == 'sub') {
                $products = $products->where('products.category_id', $cat_id_split[1]);
            } else {
                $products = $products->where('products.primary_category', $cat_id_split[1]);
            }
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            if ($request->date_type == '2') {
                $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
            }
            if ($request->date_type == '1') {
                $products = $products->where('o.delivery_request_date', '<=', $to_date);
            }
        }
        if ($request->customer_group != null) {
            $cat_id_split = explode('-', $request->customer_group);
            if ($cat_id_split[0] == 'cus') {
                $products = $products->where('o.customer_id', $cat_id_split[1]);
            } else {
                $customer_ids = Customer::where('category_id', $cat_id_split[1])->pluck('id');
                $products = $products->whereIn('o.customer_id', $customer_ids);
            }
        }
        if ($request->sales_person !== NULL) {
            $products = $products->where('o.user_id', $request->sales_person);
        } else {
            $products = $products->where('o.dont_show', 0);
        }
        if (Auth::user()->role_id == 3) {
            $sales_id = Auth::user()->id;
            $user_primary_customers = Customer::where('primary_sale_id', $sales_id)->pluck('id')->toArray();
            $products = $products->where(function ($q) use ($sales_id, $user_primary_customers) {
                $q->where('o.user_id', $sales_id)->orWhereIn('o.customer_id', $user_primary_customers);
            });
        }
        if (Auth::user()->role_id == 9) {
            $products->Where('products.ecommerce_enabled', 1);
        }
        if ($request->product_type != null) {
            $products->where('products.type_id', $request->product_type);
        }
        if ($request->product_type_2 != null) {
            $products->where('products.type_id_2', $request->product_type_2);
        }
        if ($request->product_type_3 != null) {
            $products->where('products.type_id_3', $request->product_type_3);
        }
        $products = Product::doSortby($request, $products);
        $date_type =  $request->date_type;
        $getCategories = CustomerCategory::where('is_deleted', 0)->where('show', 1)->get();
        $getWarehouses = Warehouse::where('status', 1)->get();
        $not_visible_arr = [];
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'product_sale_report')->first();
        if ($table_hide_columns != null) {
            $not_visible_arr = explode(',', $table_hide_columns->hide_columns);
        }
        $products = $products->with('sellingUnits', 'productType', 'warehouse_products', 'product_fixed_price', 'productType2');
        if ($request->type == 'footer') {
            $to_get_totals = (clone $products)->get();
            return \response()->json([
                'total_quantity' => $to_get_totals->sum('QuantityText'),
                'total_pieces' => $to_get_totals->sum('PiecesText'),
                'total_amount' => $to_get_totals->sum('TotalAmount'),
                'total_cost' => $to_get_totals->sum('TotalAverage'),
                'avg_unit_price' => $to_get_totals->sum('avg_unit_price'),
                'vat_total_amount' => $to_get_totals->sum('VatTotalAmount'),
                'total_price_sub' => $to_get_totals->sum('totalPriceSub')
            ]);
        }


        $dt = Datatables::of($products);
        $add_columns = ['view', 'selling_unit', 'brand', 'product_type', 'product_type_2', 'product_type_3', 'total_quantity', 'total_pieces', 'total_cost', 'total_amount', 'total_stock', 'sub_total', 'vat_thb', 'avg_unit_price', 'total_visible_stock', 'cogs', 'total_cogs'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $not_visible_arr, $from_date, $to_date, $supplier_id, $customer_id, $date_type, $getWarehouses) {
                return Product::returnAddColumnProductSaleReport($column, $item, $not_visible_arr, $from_date, $to_date, $supplier_id, $customer_id, $date_type, $getWarehouses);
            });
        }

        $edit_columns = ['short_desc', 'refrence_code'];
        foreach ($edit_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $not_visible_arr) {
                return Product::returnEditColumnProductSaleReport($column, $item, $not_visible_arr);
            });
        }

        $num_count = 17;
        if ($getWarehouses->count() > 0) {
            foreach ($getWarehouses as $warehouse) {
                if (!in_array($num_count++, $not_visible_arr)) {
                    $dt->addColumn($warehouse->warehouse_title . 'current', function ($item) use ($warehouse, $warehouse_id) {
                        if ($warehouse_id == 'all') {
                            $warehouse_product = $item->warehouse_products->where('warehouse_id', $warehouse->id)->first();
                            $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity : 0;
                            $this->curr_quantity = $qty;
                            return round($qty, 3) . ' ' . $item->sellingUnits->title;
                        } else if ($warehouse_id != null) {
                            $warehouse_product = ($warehouse->id == $warehouse_id) ? $item->warehouse_products->where('warehouse_id', $warehouse_id)->first() : null;
                            if ($warehouse_product != null) {
                                $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity : 0;
                                $this->curr_quantity = $qty;
                                return round($qty, 3) . ' ' . $item->sellingUnits->title;
                            } else {
                                return '--';
                            }
                        }
                    });
                } else {
                    $dt->addColumn($warehouse->warehouse_title . 'current', function ($item) {
                        return '--';
                    });
                }
            }
        }
        //Customer Category Dynamic Columns Starts Here
        if ($getCategories->count() > 0) {
            foreach ($getCategories as $cat) {
                if (!in_array($num_count++, $not_visible_arr)) {
                    $dt->addColumn($cat->title, function ($item) use ($cat) {
                        $fixed_value = $item->product_fixed_price->where('product_id', $item->id)->where('customer_type_id', $cat->id)->first();
                        $value = $fixed_value != null ? $fixed_value->fixed_price : '0.00';
                        $formated_value = number_format($value, 3, '.', ',');
                        return $formated_value;
                    });
                } else {
                    $dt->addColumn($cat->title, function ($item) {
                        return '--';
                    });
                }
            }
        }
        $dt->escapeColumns([]);
        $dt->rawColumns(['view', 'refrence_code', 'short_desc', 'selling_unit', 'total_quantity', 'total_amount', 'total_stock', 'product_type', 'total_cogs']);
        return $dt->make(true);
    }

    public function getProductSalesReportFooterData(Request $request)
    {
        if ($request->sortbyparam == 1 && $request->sortbyvalue == 1) {
            $sort_variable  = 'refrence_code';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 1 && $request->sortbyvalue == 2) {
            $sort_variable  = 'refrence_code';
            $sort_order     = 'ASC';
        }
        if ($request->sortbyparam == 2 && $request->sortbyvalue == 1) {
            $sort_variable  = 'short_desc';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 2 && $request->sortbyvalue == 2) {
            $sort_variable  = 'short_desc';
            $sort_order     = 'ASC';
        }
        if ($request->sortbyparam == 5 && $request->sortbyvalue == 1) {
            $sort_variable  = 'QuantityText';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 5 && $request->sortbyvalue == 2) {
            $sort_variable  = 'QuantityText';
            $sort_order     = 'ASC';
        }
        if ($request->sortbyparam == 6 && $request->sortbyvalue == 1) {
            $sort_variable  = 'PiecesText';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 6 && $request->sortbyvalue == 2) {
            $sort_variable  = 'PiecesText';
            $sort_order     = 'ASC';
        }
        if ($request->sortbyparam == 7 && $request->sortbyvalue == 1) {
            $sort_variable  = 'TotalAmount';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 7 && $request->sortbyvalue == 2) {
            $sort_variable  = 'TotalAmount';
            $sort_order     = 'ASC';
        }
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $supplier_id = $request->supplier_id;
        $sales_person = $request->sales_person;
        $order_type = $request->order_type;
        $customer_orders_ids = NULL;
        $products = Product::select(DB::raw('SUM(CASE
      WHEN o.primary_status="2" THEN op.quantity
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS QuantityText,
      SUM(CASE
      WHEN o.primary_status="2" THEN op.number_of_pieces
      WHEN o.primary_status="3" THEN op.pcs_shipped
      END) AS PiecesText,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat
      END) AS TotalAmount,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.vat_amount_total
      END) AS VatTotalAmount,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price
      END) AS totalPriceSub,
      (CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN (SUM(op.locked_actual_cost)/COUNT(op.id))
      END) AS TotalAverage,
      SUM(CASE
      WHEN (o.primary_status="2" OR o.primary_status="3") THEN op.total_price_with_vat END)/SUM(CASE
      WHEN o.primary_status="2" THEN op.quantity
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS avg_unit_price'), 'products.refrence_code', 'products.selling_unit', 'products.short_desc', 'op.product_id', 'op.vat_amount_total', 'op.total_price', 'products.id', 'products.category_id', 'products.primary_category', 'products.brand', 'products.ecommerce_enabled', 'o.primary_status', 'o.created_by', 'o.dont_show', 'o.user_id', 'products.type_id')->with('sellingUnits', 'productType', 'product_fixed_price')->whereIn('o.primary_status', [2, 3])->groupBy('op.product_id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        if ($supplier_id != null) {
            $products = $products->where('products.supplier_id', $supplier_id);
        }
        if ($order_type != null) {
            $products = $products->where('o.primary_status', $order_type);
        }
        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            if ($request->date_type == '2') {
                $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
            }
            if ($request->date_type == '1') {
                $products = $products->where('o.delivery_request_date', '>=', $from_date);
            }
        }
        if ($request->product_id_select != null) {
            $product_id_split = explode('-', $request->product_id_select);
            $product_id_split = (int)$product_id_split[1];
            if ($request->productClassName == 'parent') {
                $products = $products->where('products.primary_category', $product_id_split);
            } elseif ($request->productClassName == 'child') {
                $getCategories = ProductCategory::where('parent_id', '!=', 0)->where('id', $product_id_split)->pluck('id')->toArray();
                $products = $products->whereIn('products.category_id', $getCategories);
            }
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));

            if ($request->date_type == '2') {
                $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
            }
            if ($request->date_type == '1') {
                $products = $products->where('o.delivery_request_date', '<=', $to_date);
            }
        }
        if ($request->customer_id_select != null) {
            if ($request->className == 'parent') {
                $customer_ids = Customer::where('category_id', $id_split)->pluck('id');
                $products = $products->whereIn('o.customer_id', $customer_ids);
            } else {
                $products = $products->where('o.customer_id', $id_split);
            }
        }
        if ($request->sales_person !== NULL) {
            $products = $products->where('o.user_id', $request->sales_person);
        } else {
            $products = $products->where('o.dont_show', 0);
        }
        if (Auth::user()->role_id == 3) {
            $sales_id = Auth::user()->id;
            $products = $products->where('o.user_id', Auth::user()->id);
        }
        if (Auth::user()->role_id == 9) {
            $products->Where('products.ecommerce_enabled', 1);
        }
        if ($request->sortbyparam != NULL) {
            $products->orderBy($sort_variable, $sort_order);
        }
        $to_get_totals = (clone $products)->get();
        return \response()->json([
            'total_quantity' => $to_get_totals->sum('QuantityText'),
            'total_pieces' => $to_get_totals->sum('PiecesText'),
            'total_amount' => $to_get_totals->sum('TotalAmount'),
            'total_cost' => $to_get_totals->sum('TotalAverage'),
            'avg_unit_price' => $to_get_totals->sum('avg_unit_price'),
            'vat_total_amount' => $to_get_totals->sum('VatTotalAmount'),
            'total_price_sub' => $to_get_totals->sum('totalPriceSub')
        ]);
    }

    public function exportProductSalesReport(Request $request)
    {
        $status = ExportStatus::where('type', 'product_sale_report')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'product_sale_report';
            $new->status  = 1;
            $new->save();
            ProductSaleReportJob::dispatch($request->order_type_exp, $request->supplier_id_exp, $request->sales_person_exp, $request->from_date_exp, $request->to_date_exp, $request->date_type_exp, Auth::user()->id, Auth::user()->role_id, $request->customer_id_select, $request->product_id_select, $request->className, $request->productClassName, $request->product_id_exp, $request->category_id_exp, $request->customer_group_id_exp, $request->product_type_exp, $request->product_type_2_exp, $request->product_type_3_exp, $request->sortbyparam, $request->sortbyvalue, $request->warehouse_id);
            return response()->json(['msg' => "file is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'product_sale_report')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            ProductSaleReportJob::dispatch($request->order_type_exp, $request->supplier_id_exp, $request->sales_person_exp, $request->from_date_exp, $request->to_date_exp, $request->date_type_exp, Auth::user()->id, Auth::user()->role_id, $request->customer_id_select, $request->product_id_select, $request->className, $request->productClassName, $request->product_id_exp, $request->category_id_exp, $request->customer_group_id_exp, $request->product_type_exp, $request->product_type_2_exp, $request->product_type_3_exp, $request->sortbyparam, $request->sortbyvalue, $request->warehouse_id);
            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckProductReport()
    {
        $status = ExportStatus::where('type', 'product_sale_report')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusFirstTimeForProductReport()
    {
        $status = ExportStatus::where('type', 'product_sale_report')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function MarginReport(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_office')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin-report-by-product-name')->first();

        return $this->render('users.reports.margin-report.margin-report-by-office', compact('warehouses', 'dummy_data', 'table_hide_columns', 'file_name'));
    }

    public function getMarginReport(Request $request)
    {
        $from_date           = $request->from_date;
        $to_date             = $request->to_date;
        $customer_id         = $request->customer_id;
        $sales_person        = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = Warehouse::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'warehouses.warehouse_title', 'warehouses.id as wid', 'op.user_warehouse_id', 'op.product_id', 'o.customer_id', 'o.dont_show', 'warehouses.id')->groupBy('warehouses.id');
        $products->join('order_products AS op', 'op.user_warehouse_id', '=', 'warehouses.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products->where('o.dont_show', 0);
        $products = $products->where('o.primary_status', 3);
        $products = $products->whereNotNull('op.product_id');
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        $products->with('manual_adjustment');
        // $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $to_get_totals = (clone $products)->get();
        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_items_sales = $to_get_totals->sum('sales');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('import_vat_amount');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_man = 0;
        $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total_man);
        $total_gp_percent = 0;
        foreach ($products->get() as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;

            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $adjustment_out = 0;
            $total = $sales - $cogs - abs($adjustment_out);
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }
        $products = Warehouse::doSortby($request, $products, $total_items_sales, $total_items_gp);
        $dt = Datatables::of($products);
        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'percent_sales', 'sales', 'vat_in', 'vat_out', 'short_desc'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales) {
                return Product::returnMarginReportAddColumn($column, $item, $total_items_gp, $total_items_sales);
            });
        }

        $edit_columns = ['warehouse_title'];
        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnMarginReportEditColumn($column, $item);
            });
        }

        $dt->rawColumns(['warehouse_title', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'percent_sales', 'percent_gp'])
            ->with([
                'total_cogs' => $to_get_totals->sum('products_total_cost'),
                'total_sales' => $to_get_totals->sum('sales'),
                'total_vat_out' => $total_vat_out,
                'total_sale_percent' => $total_sale_percent,
                'total_vat_in' => $total_vat_in,
                'total_gp_percent' => $total_gp_percent,
            ]);
        return $dt->make(true);
    }

    public function MarginReport2(Request $request, $from_dashboard = null)
    {
        $sales_filter = null;
        $cat_filter = null;
        $warehouses   = Warehouse::where('status', 1)->get();
        $products     = Product::where('status', 1)->get();
        $suppliers    = Supplier::where('status', 1)->orderBy('reference_name')->get();
        $filter = @$request->secondary_filter;
        $sale_id = @$request->sale_id;
        $category_id = @$request->category_id;
        if (!empty($filter)) {
            if ($sale_id != NULL) {
                $sales_person = User::where('id', $sale_id)->first();
                $sales_filter = $filter;
            }
            if ($category_id != NULL) {
                $category = productCategory::where('id', $category_id)->first();
                $cat_filter = $filter;
            }
        }
        //For admin total_invoice
        $month = date('m');
        $day = '01';
        $year = date('Y');
        $start_of_month = $year . '-' . $month . '-' . $day;
        $today = date('Y-m-d');
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product')->first();
        $customer = Customer::find($request->customer);
        if ($request->has('supplier_margin')) {
            $supplier_id = $from_dashboard;
        } else {
            $supplier_id = null;
        }
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin-report-by-product-name')->first();
        return $this->render('users.reports.margin-report.margin-report-by-product', compact('warehouses', 'products', 'suppliers', 'sales_person', 'sales_filter', 'cat_filter', 'category', 'start_of_month', 'today', 'from_dashboard', 'dummy_data', 'table_hide_columns', 'customer', 'supplier_id', 'file_name'));
    }

    public function getMarginReport2(Request $request)
    {
        if ($request->supplier_id != null && $request->supplier_id != "") {
            $sup_stock_out = StockManagementOut::whereNotNull('supplier_id')->where('supplier_id', $request->supplier_id)->pluck('id')->toArray();
            $sup_order = StockOutHistory::whereIn('stock_out_from_id', $sup_stock_out)->pluck('stock_out_id')->toArray();
            $final_order_ids = StockManagementOut::whereIn('id', $sup_order)->whereNotNull('order_id')->pluck('order_id')->toArray();
        } else {
            $final_order_ids = null;
        }
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = Product::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.quantity) END AS totalQuantity,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount, SUM(CASE
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS qty'), 'products.refrence_code', 'products.short_desc', 'op.product_id', 'products.brand', 'o.dont_show', 'products.id', 'o.customer_id', 'products.supplier_id')->groupBy('op.product_id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        Product::doSort($request, $products);
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        if ($request->sale_id != null) {
            $products = $products->where('o.user_id', $request->sale_id);
        } else {
            $products = $products->where('o.dont_show', 0);
        }
        if ($final_order_ids != null) {
            $products = $products->whereIn('o.id', $final_order_ids);
        }
        if ($request->category_id != null) {
            $products = $products->where('products.primary_category', $request->category_id);
        }
        if ($request->customer_selected != null && $request->customer_selected !== '') {
            $products->where('o.customer_id', $request->customer_selected);
        }
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        //to find cogs of manual adjustments
        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total);
        $to_get_totals = 0;
        $products = Product::MarginReportByProductNameSorting($request, $products, $total_items_sales, $total_items_gp);
        $products = $products->with('purchaseOrderDetailVatIn:id,product_id,pod_vat_actual_total_price,quantity', 'manual_adjustment', 'def_or_last_supplier');

        $dt =  Datatables::of($products);

        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out', 'unit_cogs', 'qty'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales) {
                return Product::returnAddColumnMargin2($column, $item, $total_items_gp, $total_items_sales);
            });
        }

        $edit_columns = ['default_supplier', 'short_desc', 'refrence_code'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMargin2($column, $item);
            });
        }

        $filter_columns = ['default_supplier', 'refrence_code'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return Product::returnFilterColumnMargin2($column, $item, $keyword);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->rawColumns(['refrence_code', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp', 'short_desc', 'default_supplier']);
        return $dt->make(true);
    }

    public function MarginReport3(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_sales')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_sales')->first();
        return $this->render('users.reports.margin-report.margin-report-by-sales', compact('warehouses', 'dummy_data', 'table_hide_columns', 'file_name'));
    }

    public function getMarginReport3(Request $request)
    {
        $from_date           = $request->from_date;
        $to_date             = $request->to_date;
        $customer_id         = $request->customer_id;
        $sales_person        = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = User::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'users.name', 'users.id AS sale_id', 'o.dont_show', 'op.product_id')->groupBy('users.id');
        $products->join('orders AS o', 'o.user_id', '=', 'users.id');
        $products->join('order_products AS op', 'op.order_id', '=', 'o.id');
        $products->where('o.primary_status', 3);
        $products->where('o.dont_show', 0);
        $products->whereNotNull('op.product_id');
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs;
        $products = User::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $dt = Datatables::of($products);
        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales) {
                return Product::returnAddColumnMargin3($column, $item, $total_items_gp, $total_items_sales);
            });
        }

        $edit_columns = ['name', 'short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMargin3($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->rawColumns(['name', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp']);
        return $dt->make(true);
    }

    public function MarginReport4(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_category')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_category')->first();
        return $this->render('users.reports.margin-report.margin-report-by-product-category', compact('warehouses', 'dummy_data', 'table_hide_columns', 'file_name'));
    }

    public function getMarginReport4(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = ProductCategory::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'product_categories.title', 'products.short_desc', 'products.brand', 'op.product_id', 'product_categories.id AS category_id')->groupBy('products.primary_category');
        $products->join('products', 'products.primary_category', '=', 'product_categories.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }
        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total_man_ov);
        $products = ProductCategory::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $dt = Datatables::of($products);

        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales, $request) {
                return Product::returnAddColumnMargin4($column, $item, $total_items_gp, $total_items_sales, $request);
            });
        }

        $edit_columns = ['title', 'short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMargin4($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->rawColumns(['title', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp']);
        return $dt->make(true);
    }

    public function MarginReport5(Request $request)
    {
        $customer_type_filter = null;
        $sale_person_selected = $request->sale_id;
        $warehouses = Warehouse::where('status', 1)->get();
        $suppliers  = Supplier::where('status', 1)->orderBy('reference_name')->get();
        $filter = @$request->secondary_filter;
        $customer_type_id = @$request->customer_type_id;
        if (!empty($filter)) {
            if ($customer_type_id != NULL) {
                $customer_category = CustomerCategory::where('id', $customer_type_id)->first();
                $customer_type_filter = $filter;
            }
        }
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $sale_persons = User::where('role_id', 3)->where('status', 1)->whereNull('parent_id')->orderBy('name', 'asc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_customer')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_customer')->first();
        return $this->render('users.reports.margin-report.margin-report-by-customers', compact('warehouses', 'customer_type_filter', 'customer_category', 'dummy_data', 'table_hide_columns', 'sale_persons', 'sale_person_selected', 'file_name'));
    }

    public function getMarginReport5(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = Customer::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'customers.reference_name', 'customers.reference_number', 'customers.id AS customer_id', 'products.brand')->where('customers.status', 1)->groupBy('customers.id');
        $products->join('orders AS o', 'o.customer_id', '=', 'customers.id');
        $products->join('order_products AS op', 'op.order_id', '=', 'o.id');
        $products->join('products', 'products.id', '=', 'op.product_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        if ($request->selecting_cust_cat != null) {
            $products = $products->where('customers.category_id', $request->selecting_cust_cat);
        }
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        if ($request->sale_person_selected != null && $request->sale_person_selected !== '') {
            $products->where('customers.primary_sale_id', $request->sale_person_selected);
        }
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs;
        $products = Customer::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $add_columns = ['reference_code', 'margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        $dt = Datatables::of($products);
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales, $request) {
                return Product::returnAddColumnMargin5($column, $item, $total_items_gp, $total_items_sales, $request);
            });
        }

        $edit_columns = ['reference_name', 'short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMargin5($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->rawColumns(['reference_name', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp', 'reference_code']);
        return $dt->make(true);
    }

    public function MarginReport6(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_customer_type')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_customer_type')->first();
        return $this->render('users.reports.margin-report.margin-report-by-customer-types', compact('warehouses', 'dummy_data', 'table_hide_columns', $file_name));
    }

    public function getMarginReport6(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = CustomerCategory::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'customer_categories.is_deleted', 'customer_categories.title', 'products.brand', 'customer_categories.id AS customer_type_id', 'o.customer_id')->groupBy('customer_categories.id');
        $products->join('customers AS c', 'c.category_id', '=', 'customer_categories.id');
        $products->join('orders AS o', 'o.customer_id', '=', 'c.id');
        $products->join('order_products AS op', 'op.order_id', '=', 'o.id');
        $products->join('products', 'products.id', '=', 'op.product_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        $products = $products->where('customer_categories.is_deleted', 0);
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs;
        $products = CustomerCategory::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $dt = Datatables::of($products);
        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales) {
                return Product::returnAddColumnMargin6($column, $item, $total_items_gp, $total_items_sales);
            });
        }

        $edit_columns = ['title', 'short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMargin6($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->rawColumns(['title', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp']);
        return $dt->make(true);
    }

    public function MarginReport7(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $products   = Product::where('status', 1)->get();
        $suppliers  = Supplier::where('status', 1)->orderBy('reference_name')->get();
        $preOrderProducts = Product::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price_with_vat) END AS sales'), 'products.refrence_code', 'products.short_desc', 'op.product_id')->groupBy('op.product_id');
        $preOrderProducts->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $preOrderProducts->join('orders AS o', 'o.id', '=', 'op.order_id');
        $preOrderProducts->where('o.primary_status', 3);
        $preOrderProducts->where('products.status', 1);
        $reOrderProducts = (clone $preOrderProducts)->whereNull('products.min_stock')->orWhere('products.min_stock', '==', 0)->get();
        $stocksProducts  = (clone $preOrderProducts)->where('products.min_stock', '>', 0)->get();
        return $this->render('users.reports.margin-report.margin-report-by-preorder-stock', compact('warehouses', 'products', 'suppliers', 'stocksProducts', 'reOrderProducts'));
    }

    public function getMarginReport7(Request $request)
    {
        if ($request->sortbyparam == 1 && $request->sortbyvalue == 1) {
            $sort_variable  = 'sales';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 1 && $request->sortbyvalue == 2) {
            $sort_variable  = 'sales';
            $sort_order     = 'ASC';
        }
        if ($request->sortbyparam == 2 && $request->sortbyvalue == 1) {
            $sort_variable  = 'products_total_cost';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 2 && $request->sortbyvalue == 2) {
            $sort_variable  = 'products_total_cost';
            $sort_order     = 'ASC';
        }
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;
        $preOrderProducts = Product::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price_with_vat) END AS sales'), 'products.refrence_code', 'products.short_desc', 'products.brand', 'op.product_id')->groupBy('op.product_id');
        $preOrderProducts->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $preOrderProducts->join('orders AS o', 'o.id', '=', 'op.order_id');
        $preOrderProducts = $preOrderProducts->where('o.primary_status', 3);
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $preOrderProducts = $preOrderProducts->where('o.converted_to_invoice_on', '>=', $from_date);
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $preOrderProducts = $preOrderProducts->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        if ($request->filter != '') {
            if ($request->filter == 'stock') {
                $query = $query->whereIn('products.id', WarehouseProduct::select('product_id')->where('current_quantity', '>', 0.005)->pluck('product_id'));
            } elseif ($request->filter == 'reorder') {
                $query->where('products.min_stock', '>', 0);
            }
        }
        $to_get_totals_p = (clone $preOrderProducts)->whereNull('products.min_stock')->orWhere('products.min_stock', '==', 0)->get();
        $total_s = $to_get_totals_p->sum('sales');
        $total_c = $to_get_totals_p->sum('products_total_cost');

        $to_get_totals_s = (clone $preOrderProducts)->where('products.min_stock', '>', 0)->get();
        $total_s1 = $to_get_totals_s->sum('sales');
        $total_c1 = $to_get_totals_s->sum('products_total_cost');
        $arrayTotal[] = array(
            'name'   => "Preorder",
            'sales'  => $total_s,
            'cogs'   => $total_c,
            'p_type' => "preorder",
            's_type' => "stock",
        );
        $arrayTotal1[] = array(
            'name'   => "Stock",
            'sales'  => $total_s1,
            'cogs'   => $total_c1,
            'p_type' => "preorder",
            's_type' => "stock",
        );
        $grandArray = array_merge($arrayTotal, $arrayTotal1);
        return Datatables::of($grandArray)
            ->editColumn('refrence_code', function ($item) {
                return $item['name'];
            })
            ->addColumn('sales', function ($item) {
                $total = number_format($item['sales'], 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-ctype="' . $item['p_type'] . '" title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
            })
            ->addColumn('brand', function ($item) {
                return @$item->brand != null ? @$item->brand : '--';
            })
            ->addColumn('cogs', function ($item) {
                $total = number_format($item['cogs'], 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-ctype="' . $item['s_type'] . '" title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
            })
            ->addColumn('gp', function ($item) {
                $sales = $item['sales'];
                $cogs  = $item['cogs'];
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', ',');
                return $formated;
            })
            ->addColumn('margins', function ($item) {
                $sales = $item['sales'];
                $cogs  = $item['cogs'];
                if ($sales != 0) {
                    $total = ($sales - $cogs) / $sales;
                } else {
                    $total = 0;
                }
                if ($total == 0) {
                    $formated = "-100.00";
                } else {
                    $formated = number_format($total * 100, 2);
                }
                return $formated . " %";
            })
            ->rawColumns(['refrence_code', 'cogs', 'sales', 'gp', 'margins'])
            ->with([
                'total_cogs'  => $to_get_totals_p->sum('products_total_cost') + $to_get_totals_s->sum('products_total_cost'),
                'total_sales' => $to_get_totals_p->sum('sales') + $to_get_totals_s->sum('sales'),
            ])
            ->make(true);
    }

    public function MarginReport8(Request $request)
    {
        $id = $request->id;
        $warehouses = Warehouse::where('status', 1)->get();
        $products   = Product::where('status', 1)->get();
        $suppliers  = Supplier::where('status', 1)->orderBy('reference_name')->get();
        return $this->render('users.reports.margin-report.margin-report-by-product-sub-category', compact('warehouses', 'products', 'suppliers', 'id'));
    }

    public function getMarginReport8(Request $request)
    {
        if ($request->sortbyparam == 1 && $request->sortbyvalue == 1) {
            $sort_variable  = 'sales';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 1 && $request->sortbyvalue == 2) {
            $sort_variable  = 'sales';
            $sort_order     = 'ASC';
        }
        if ($request->sortbyparam == 2 && $request->sortbyvalue == 1) {
            $sort_variable  = 'products_total_cost';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 2 && $request->sortbyvalue == 2) {
            $sort_variable  = 'products_total_cost';
            $sort_order     = 'ASC';
        }
        if ($request->sortbyparam == 3 && $request->sortbyvalue == 1) {
            $sort_variable  = 'marg';
            $sort_order     = 'DESC';
        } elseif ($request->sortbyparam == 3 && $request->sortbyvalue == 2) {
            $sort_variable  = 'marg';
            $sort_order     = 'ASC';
        }
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $products = ProductCategory::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'product_categories.title', 'products.short_desc', 'products.brand', 'op.product_id', 'product_categories.id AS category_id', 'products.category_id AS sub_cat_id')->groupBy('products.category_id');
        $products->join('products', 'products.primary_category', '=', 'product_categories.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        $products = $products->where('products.primary_category', $request->id);
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }
        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }
        $to_get_totals = (clone $products)->get();
        return Datatables::of($products)
            ->editColumn('title', function ($item) {
                $cat_name = ProductCategory::where('id', $item->sub_cat_id)->pluck('title')->first();
                $html_string = $cat_name;
                return $html_string;
            })
            ->editColumn('short_desc', function ($item) {
                return $item->short_desc;
            })
            ->addColumn('vat_out', function ($item) {
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
            })
            ->addColumn('vat_in', function ($item) {
                return $item->import_vat_amount != null ? number_format($item->import_vat_amount, 2) : '--';
            })
            ->addColumn('sales', function ($item) {
                $total = number_format($item->sales, 2);
                $html_string = $total;
                return $html_string;
            })
            ->addColumn('brand', function ($item) {
                return @$item->brand != null ? @$item->brand : '--';
            })
            ->addColumn('cogs', function ($item) {
                $total = number_format($item->products_total_cost, 2);
                $html_string = $total;
                return $html_string;
            })
            ->addColumn('gp', function ($item) {
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', ',');
                return $formated;
            })
            ->addColumn('margins', function ($item) {
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                if ($sales != 0) {
                    $total = $item->marg;
                } else {
                    $total = 0;
                }
                if ($total == 0) {
                    $formated = "-100.00";
                } else {
                    $formated = number_format($total * 100, 2);
                }
                return $formated . " %";
            })
            ->setRowId(function ($item) {
                return $item->id;
            })
            ->rawColumns(['title', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in'])
            ->with([
                'total_cogs' => $to_get_totals->sum('products_total_cost'),
                'total_sales' => $to_get_totals->sum('sales'),
            ])
            ->make(true);
    }

    public function deactivatedProducts()
    {
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'completed_products')->first();
        $display_prods = ColumnDisplayPreference::where('type', 'deactivated_products')->where('user_id', Auth::user()->id)->first();
        $suppliers = Supplier::where('status', 1)->orderBy('reference_name')->get();
        return $this->render('users.products.deactivated-products', compact('table_hide_columns', 'display_prods', 'suppliers'));
    }

    public function purchaseAccountPayable()
    {
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'account_payable')->first();
        $purchase_order = PurchaseOrder::where('status', 15)->sum('total_in_thb');
        $total_paidd = PurchaseOrder::where('status', 15)->sum('total_paid');
        $payments = ($purchase_order - $total_paidd);
        $open_invoices = Order::where('primary_status', 3)->where('status', 11)->sum('total_amount');
        $total_paid = Order::where('primary_status', 3)->where('status', 11)->sum('total_paid');
        $account_receivable = ($open_invoices - $total_paid);
        $current_date = Carbon::now();
        $first_of_month = Carbon::now()->firstOfMonth();
        $delete_transaction = TransactionHistory::where('order_transaction_id', null)->whereBetween('created_at', [$first_of_month->format('Y-m-d') . " 00:00:00", $current_date->format('Y-m-d') . " 23:59:59"])->count();
        if (Auth::user()->role_id == 3) {
            $suppliers = Supplier::whereIn('id', PurchaseOrder::where('status', 15)->whereNotNull('supplier_id')->pluck('supplier_id')->toArray())->where('status', 1)->where('user_id', Auth::user()->id)->get();
        } else {
            $suppliers = Supplier::whereIn('id', PurchaseOrder::where('status', 15)->pluck('supplier_id')->toArray())->where('status', 1)->get();
        }
        $payment_methods = PaymentType::get();
        return view('users.purchasing.account-payable', compact('suppliers', 'purchase_order', 'payments', 'account_receivable', 'delete_transaction', 'table_hide_columns', 'payment_methods'));
    }

    public function getAccountPayablePurchaseOrders(Request $request)
    {
        $date = date('Y-m-d H:i:s');
        $query = PurchaseOrder::with('PurchaseOrderDetail', 'createdBy', 'purchaseOrderTransaction', 'pOpaymentTerm', 'PoSupplier.getCurrency', 'po_notes', 'p_o_statuses.parent');
        $payment_types = PaymentType::all();

        PurchaseOrder::doSort($request, $query);
        $query->where(function ($q) {
            $q->whereIn('purchase_orders.status', [15, 27, 33])->whereNotNull('purchase_orders.supplier_id');
        });
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $query = $query->whereDate('invoice_date', '>=', $from_date);
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $query = $query->whereDate('invoice_date', '<=', $to_date);
        }
        if ($request->selecting_supplier != null) {
            $query->where('supplier_id', $request->selecting_supplier);
        }
        if ($request->order_no != null) {
            $query = $query->where('ref_id', $request->order_no);
        }
        if ($request->order_total != null) {
            $query = $query->where(DB::raw("floor(`total_in_thb`)"), floor($request->order_total));
        }
        $query = $query->whereRaw('total_in_thb > total_paid')->orderBy('id', 'DESC');

        $dt = Datatables::of($query);
        $add_columns = ['actions', 'created_at', 'payment_reference_no', 'po_exchange_rate', 'invoice_date', 'amount_paid', 'total_received', 'difference', 'received_date', 'payment_method', 'exchange_rate', 'payment_due_date', 'po_total_in_thb', 'po_total_with_vat', 'po_total', 'total_received', 'supplier', 'po_id', 'target_ship_date', 'payment_terms', 'supplier_currency'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $payment_types) {
                return PurchaseOrder::returnAddColumnAccountPayable($column, $payment_types, $item);
            });
        }

        $edit_columns = ['invoice_number', 'memo'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column, $payment_types) {
                return PurchaseOrder::returnEditColumnAccountPayable($column, $payment_types, $item);
            });
        }

        $filter_columns = ['po_id', 'supplier', 'supplier_currency', 'payment_terms'];
        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrder::returnFilterColumnAccountPayable($column, $item, $keyword);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->rawColumns(['po_id', 'supplier', 'po_total', 'payment_due_date', 'payment_method', 'received_date', 'total_received', 'amount_paid', 'created_at', 'actions', 'payment_reference_no', 'target_ship_date', 'memo', 'supplier_currency', 'payment_terms', 'exchange_rate', 'difference']);
        return $dt->make(true);
    }

    public function getSupplierOrders(Request $request)
    {
        $suppplier_ids = PurchaseOrder::distinct('supplier_id')->where('status', 15)->whereNotNull('supplier_id')->pluck('supplier_id')->toArray();
        $query = Supplier::whereIn('id', $suppplier_ids);
        if ($request->selecting_supplier != null) {
            $query = $query->where('id', $request->selecting_supplier);
        }

        $dt =  Datatables::of($query);
        $add_columns = ['action', 'total_not_due', 'total_due', 'total', 'supplier_company'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnColumnSupplierOrders($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->rawColumns(['supplier_company', 'total', 'total_due', 'total_not_due', 'action']);
        return $dt->make(true);
    }

    public function deletePoTransaction(Request $request)
    {
        $transaction = PurchaseOrderTransaction::find($request->id);
        $payment_ref_no = $transaction->payment_reference_no;
        $po = PurchaseOrder::find($transaction->po_id);
        $po->total_paid -= $transaction->total_received;
        $po->save();
        $transaction_history = new PoTransactionHistory;
        $transaction_history->user_id = Auth::user()->id;
        $transaction_history->po_id = $po->id;
        $transaction_history->payment_method_id = $transaction->payment_method_id;
        $transaction_history->received_date = $transaction->received_date;
        $transaction_history->payment_reference_no = $transaction->get_payment_ref->payment_reference_no;
        $transaction_history->total_received = $transaction->total_received;
        $transaction_history->save();
        $transaction->delete();
        $payment = PoPaymentRef::where('id', $payment_ref_no)->first();
        if ($payment->getTransactions->count() == 0) {
            $payment->delete();
        }
        $po->save();
        return response()->json(['success' => true]);
    }

    public function getPurchaseOrderReceivedAmount(Request $request)
    {
        $date = str_replace("/", "-", $request->received_date);
        $received_date =  date('Y-m-d', strtotime($date));
        $po_payment_ref = PoPaymentRef::where('payment_reference_no', $request->payment_reference_no)->first();
        $po = PurchaseOrder::find($request->po_id[0]);
        if ($po_payment_ref == null) {
            $po_payment_ref = new PoPaymentRef;
            $po_payment_ref->payment_reference_no = $request->payment_reference_no;
            $po_payment_ref->supplier_id = $po->supplier_id;
            $po_payment_ref->payment_method = $request->payment_method;
            $po_payment_ref->received_date = $received_date;
            $po_payment_ref->save();
        } else {
            return response()->json(['payment_reference_no' => 'exists']);
        }
        $already_paid = '';
        $orders = [];
        $orders = $request->po_id;
        $total_received = [];
        $total_received = $request->total_received;
        $i = 0;
        foreach ($orders as $order) {
            $order = PurchaseOrder::find($order);
            if ($order->total_in_thb < $order->total_paid) {
                $already_paid = $order->ref_id . ", ";
                $po_payment_ref = PoPaymentRef::where('payment_reference_no', $request->payment_reference_no)->delete();
            } else {
                $po_transaction = new PurchaseOrderTransaction;
                $po_transaction->po_id = $order->id;
                $po_transaction->user_id         = Auth::user()->id;
                $po_transaction->supplier_id = $order->supplier_id;
                $po_transaction->po_order_ref_no         = $order->ref_id;
                $po_transaction->payment_method_id = $request->payment_method;
                $po_transaction->received_date = $received_date;
                $po_transaction->payment_reference_no = $po_payment_ref->id;
                $po_transaction->total_received = $total_received[$i];
                $po_transaction->save();
                $total_paid = $order->total_paid != null ? $order->total_paid : 0;
                if ($total_received[$i] < ($order->total_in_thb - $total_paid) && $order->status == 27) {
                    $order->status = 33;
                } else if ($order->status == 33 || $order->status === 27) {
                    $order->status = 31;
                }
                $order->total_paid += $total_received[$i];
                $order->save();
            }
            $i++;
        }
        return response()->json(['success' => true, 'already_paid' => $already_paid]);
    }

    public function checkMktStatus(Request $request)
    {
        $product_info = new Product;
        $resturant = $product_info->checkProductMktForResturant($request->id);
        $hotel     = $product_info->checkProductMktForHotel($request->id);
    }

    public function incomplete()
    {
        $parentCat = ProductCategory::where('parent_id', 0)->orderBy('title')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'incomplete_products')->first();
        $display_prods = ColumnDisplayPreference::where('type', 'incomplete_products')->where('user_id', Auth::user()->id)->first();
        return $this->render('users.products.incompleteProduct', compact('table_hide_columns', 'display_prods', 'parentCat'));
    }

    public function uploadBulkProducts(Request $request)
    {
        $user_id = Auth::user()->id;
        $import = new ProductBulkImport($request->supplier, $user_id);
        Excel::import($import, $request->file('excel'));
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id, 'Add Bulk Products', $request->file('excel'));
    }

    public function recursiveExportStatusSupplierBulkProducts(Request $request)
    {
        $status = ExportStatus::where('type', 'supplier_bulk_products_import')->first();
        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception, 'error_msgs' => $status->error_msgs]);
    }

    public function recursiveExportStatusSupplierBulkPrices(Request $request)
    {
        $status = ExportStatus::where('type', 'supplier_bulk_prices_import')->first();
        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception, 'error_msgs' => $status->error_msgs]);
    }

    public function recursiveExportStatusMoveSupplierBulkProducts(Request $request)
    {
        $status = ExportStatus::where('type', 'move_supplier_bulk_products')->first();
        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception, 'error_msgs' => $status->error_msgs]);
    }

    public function uploadSupplierBulkProductJobStatus(Request $request)
    {
        $status = ExportStatus::where('type', 'supplier_bulk_products_import')->first();
        if ($status == null) {
            $new          = new ExportStatus();
            $new->type    = 'supplier_bulk_products_import';
            $new->user_id = Auth::user()->id;
            $new->status  = 1;
            $new->save();
            return response()->json(['status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['status' => 2, 'recursive' => false]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'supplier_bulk_products_import')->update(['status' => 1, 'user_id' => Auth::user()->id, 'exception' => null, 'error_msgs' => null]);
            return response()->json(['msg' => "File is getting ready!", 'status' => 1, 'recursive' => true]);
        }
    }

    public function uploadSupplierBulkPricesJobStatus(Request $request)
    {
        $status = ExportStatus::where('type', 'supplier_bulk_prices_import')->first();
        if ($status == null) {
            $new          = new ExportStatus();
            $new->type    = 'supplier_bulk_prices_import';
            $new->user_id = Auth::user()->id;
            $new->status  = 1;
            $new->save();
            return response()->json(['status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['status' => 2, 'recursive' => false]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'supplier_bulk_prices_import')->update(['status' => 1, 'user_id' => Auth::user()->id, 'exception' => null, 'error_msgs' => null]);
            return response()->json(['msg' => "File is getting ready!", 'status' => 1, 'recursive' => true]);
        }
    }

    public function moveSupplierBulkProductJobStatus(Request $request)
    {
        $status = ExportStatus::where('type', 'move_supplier_bulk_products')->first();
        if ($status == null) {
            $new          = new ExportStatus();
            $new->type    = 'move_supplier_bulk_products';
            $new->user_id = Auth::user()->id;
            $new->status  = 1;
            $new->save();
            return response()->json(['status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['status' => 2, 'recursive' => false]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'move_supplier_bulk_products')->update(['status' => 1, 'user_id' => Auth::user()->id, 'exception' => null, 'error_msgs' => null]);
            return response()->json(['msg' => "File is getting ready!", 'status' => 1, 'recursive' => true]);
        }
    }

    public function uploadPricesBulkProducts(Request $request)
    {
        $user_id = Auth::user()->id;
        $import = new ProductPricesBulkImport($request->supplier, $user_id);
        Excel::import($import, $request->file('excel'));
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id, 'Add Bulk Prices', $request->file('excel'));
    }

    public function bulkUploadProudcts($id = null)
    {
        if ($id != null) {
            $temp_success_products = TempProduct::where('supplier_id', $id)->where('hasError', 0)->get();
            foreach ($temp_success_products as $temp_product) {
                if ($temp_product->system_code != null) {
                    $product = Product::where('system_code', $temp_product->system_code)->first();
                    if ($product != null) {
                        $product->primary_category  = $temp_product->primary_category;
                        $category_id = ProductCategory::where('id', $temp_product->category_id)->first();
                        if ($category_id != null) {
                            $product->category_id     = $temp_product->category_id;
                            $product->hs_code         = $temp_product->tempProductSubCategory->hs_code;
                            $product->import_tax_book = $temp_product->tempProductSubCategory->import_tax_book;
                        }
                        if ($temp_product->vat != null) {
                            $product->vat             = $temp_product->vat;
                        }
                        $product->product_temprature_c = $temp_product->product_temprature_c;
                        $product->weight               = $temp_product->weight;
                        $product->short_desc           = $temp_product->short_desc;
                        $product->type_id              = $temp_product->type_id;
                        $product->brand                = $temp_product->brand;
                        $product->buying_unit          = $temp_product->buying_unit;
                        $product->selling_unit         = $temp_product->selling_unit;
                        $product->stock_unit           = $temp_product->stock_unit;
                        $product->min_stock            = $temp_product->min_stock;
                        $product->unit_conversion_rate = $temp_product->unit_conversion_rate;
                        $product->product_notes        = $temp_product->product_notes;
                        if ($product->supplier_id == $temp_product->supplier_id) {
                            $supplier = Supplier::find($temp_product->supplier_id);
                            $cur = Currency::find($supplier->currency_id);
                            //convert the currency to thai bhat
                            $total_buying_price                 = ($temp_product->buying_price / $cur->conversion_rate);
                            // here is the new condition starts
                            //import tax book is in products subcategories table
                            $importTax                          = $temp_product->import_tax_actual !== NULL ? $temp_product->import_tax_actual : $temp_product->tempProductSubCategory->import_tax_book;
                            // here is the new condition ends
                            $total_buying_price                 = (($importTax / 100) * $total_buying_price) + $total_buying_price;
                            $extras                             = $temp_product->freight + $temp_product->landing + $temp_product->extra_cost + $temp_product->extra_tax;
                            $total_buying_price                 = $total_buying_price + $extras;
                            $product->total_buy_unit_cost_price = ($total_buying_price);
                            //convert the currency again to supplier currency
                            $product->t_b_u_c_p_of_supplier     = $total_buying_price * $cur->conversion_rate;
                            //this is buy unit cost price
                            $total_selling_price                = ($product->total_buy_unit_cost_price) * $temp_product->unit_conversion_rate; //this is selling price
                            $product->selling_price             = $total_selling_price;
                        }
                        $product->update();
                        $supplier = Supplier::where('id', $temp_product->supplier_id)->first();
                        $cur = Currency::find(@$supplier->currency_id);
                        $buying_price_in_thb = $temp_product->buying_price != null ? $temp_product->buying_price / $cur->conversion_rate : null;
                        $supplier_products = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $temp_product->supplier_id)->first();
                        if ($supplier_products == null) {
                            $supplier_products                                = new SupplierProducts;
                        }
                        $supplier_products->supplier_id                   = $temp_product->supplier_id;
                        $supplier_products->product_id                    = $product->id;
                        $supplier_products->product_supplier_reference_no = $temp_product->p_s_r;
                        $supplier_products->supplier_description          = $temp_product->supplier_description;
                        $supplier_products->buying_price                  = (float)$temp_product->buying_price;
                        $supplier_products->buying_price_in_thb           = (float)$buying_price_in_thb;
                        $supplier_products->extra_cost                    = $temp_product->extra_cost;
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
                } else {
                    $product                       = new Product;
                    $getSubCat = ProductCategory::where('id', $temp_product->category_id)->first();
                    $reference_code = null;
                    $prefix = $getSubCat->prefix;

                    $c_p_ref = Product::where('category_id', $temp_product->category_id)->orderBy('refrence_no', 'DESC')->first();
                    if ($c_p_ref == NULL) {
                        $str = '0';
                    } else {
                        $str = $c_p_ref->refrence_no;
                    }
                    $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
                    $product->refrence_code        = $prefix . $system_gen_no;
                    $product->system_code          = $prefix . $system_gen_no;
                    $product->refrence_no          = $system_gen_no;
                    $product->hs_code              = $temp_product->hs_code;
                    $product->short_desc           = $temp_product->short_desc;
                    $product->weight               = $temp_product->weight;
                    $product->primary_category     = $temp_product->primary_category;
                    $product->category_id          = $temp_product->category_id;
                    $product->type_id              = $temp_product->type_id;
                    $product->brand                = $temp_product->brand;
                    $product->product_temprature_c = $temp_product->product_temprature_c;
                    $product->buying_unit          = $temp_product->buying_unit;
                    $product->selling_unit         = $temp_product->selling_unit;
                    $product->stock_unit           = $temp_product->stock_unit;
                    $product->min_stock            = $temp_product->min_stock;
                    $product->unit_conversion_rate = $temp_product->unit_conversion_rate;
                    $product->selling_price        = $temp_product->selling_price;
                    $product->supplier_id          = $temp_product->supplier_id;
                    $product->hs_code              = @$temp_product->tempProductSubCategory->hs_code;
                    $product->import_tax_book      = @$temp_product->tempProductSubCategory->import_tax_book;
                    if ($temp_product->vat == NULL) {
                        $product->vat                = @$temp_product->tempProductSubCategory->vat;
                    } else {
                        $product->vat                = @$temp_product->vat;
                    }
                    $product->created_by           = $temp_product->created_by;
                    $product->status               = $temp_product->status;
                    $product->product_notes        = $temp_product->product_notes;
                    $supplier = Supplier::find($temp_product->supplier_id);
                    $cur = Currency::find($supplier->currency_id);
                    //convert the currency to thai bhat
                    $total_buying_price                 = ($temp_product->buying_price / $cur->conversion_rate);
                    // here is the new condition starts
                    //import tax book is in products subcategories table
                    $importTax                          = $temp_product->import_tax_actual !== NULL ? $temp_product->import_tax_actual : $temp_product->tempProductSubCategory->import_tax_book;
                    // here is the new condition ends
                    $total_buying_price                 = (($importTax / 100) * $total_buying_price) + $total_buying_price;
                    $extras                             = $temp_product->freight + $temp_product->landing + $temp_product->extra_cost;
                    $total_buying_price                 = $total_buying_price + $extras;
                    $product->total_buy_unit_cost_price = ($total_buying_price);
                    //convert the currency again to supplier currency
                    $product->t_b_u_c_p_of_supplier     = $total_buying_price * $cur->conversion_rate;
                    //this is buy unit cost price
                    $total_selling_price                = ($product->total_buy_unit_cost_price) * $temp_product->unit_conversion_rate; //this is selling price
                    $product->selling_price             = $total_selling_price;
                    $product->save();
                    $supplier_products                                = new SupplierProducts;
                    $supplier_products->supplier_id                   = $product->supplier_id;
                    $supplier_products->product_id                    = $product->id;
                    $supplier_products->product_supplier_reference_no = $temp_product->p_s_r;
                    $supplier_products->supplier_description          = $temp_product->supplier_description;
                    $supplier_products->buying_price                  = (float)$temp_product->buying_price;
                    $supplier_products->buying_price_in_thb           = (float)$temp_product->buying_price / $cur->conversion_rate;
                    $supplier_products->extra_cost                    = $temp_product->extra_cost;
                    $supplier_products->freight                       = $temp_product->freight;
                    $supplier_products->landing                       = $temp_product->landing;
                    $supplier_products->gross_weight                  = $temp_product->gross_weight;
                    $supplier_products->leading_time                  = $temp_product->leading_time;
                    $supplier_products->import_tax_actual             = $temp_product->import_tax_actual;
                    $supplier_products->m_o_q                         = $temp_product->m_o_q;
                    $supplier_products->supplier_packaging            = $temp_product->supplier_packaging;
                    $supplier_products->billed_unit                   = $temp_product->billed_unit;
                    $supplier_products->save();
                    $categoryMargins = CustomerTypeCategoryMargin::where('category_id', $product->category_id)->orderBy('id', 'ASC')->get();
                    if ($categoryMargins->count() > 0) {
                        foreach ($categoryMargins as $value) {
                            $productMargin                   = new CustomerTypeProductMargin;
                            $productMargin->product_id       = $product->id;
                            $productMargin->customer_type_id = $value->customer_type_id;
                            $productMargin->default_margin   = $value->default_margin;
                            $productMargin->default_value    = $value->default_value;
                            $productMargin->save();
                        }
                    }
                    $customerCats = CustomerCategory::where('is_deleted', 0)->orderBy('id', 'ASC')->get();
                    if ($customerCats->count() > 0) {
                        foreach ($customerCats as $c_cat) {
                            $productFixedPrices                   = new ProductFixedPrice;
                            $productFixedPrices->product_id       = $product->id;
                            $productFixedPrices->customer_type_id = $c_cat->id;
                            $productFixedPrices->fixed_price      = 0;
                            $productFixedPrices->expiration_date  = NULL;
                            $productFixedPrices->save();
                        }
                    }
                    $warehouse = Warehouse::get();
                    foreach ($warehouse as $w) {
                        $warehouse_product = new WarehouseProduct;
                        $warehouse_product->warehouse_id = $w->id;
                        $warehouse_product->product_id = $product->id;
                        $warehouse_product->save();
                    }
                }
                $temp_product->delete();
            }
        }
        if ($id != null) {
            $temp_product = TempProduct::where('supplier_id', $id)->get();
            if ($temp_product->count() == 0) {
                return redirect()->route('get-supplier-detail', $id);
            }
        }

        $customerCategory = CustomerCategory::where('is_deleted', 0)->get();
        if ($id != null) {
            $temp_success_products_count = TempProduct::where('supplier_id', $id)->where('status', 1)->count();
            $temp_failed_products_count = TempProduct::where('supplier_id', $id)->where('status', 0)->count();
            $temp_prod_data = TempProduct::with('tempProductType', 'tempProductCategory', 'tempProductSubCategory', 'tempUnits', 'tempSellingUnits')->where('supplier_id', $id)->orderBy('id')->get();
        }
        else{
            $temp_success_products_count = TempProduct::where('status', 1)->count();
            $temp_failed_products_count = TempProduct::where('status', 0)->count();
            $temp_prod_data = TempProduct::with('tempProductType', 'tempProductCategory', 'tempProductSubCategory', 'tempUnits', 'tempSellingUnits')->orderBy('id')->get();
        }
        return view('users.products.complete-bulk-upload-products', compact(
            'temp_success_products_count',
            'temp_failed_products_count',
            'primary_category_ids',
            'primary_category',
            'id',
            'customerCategory',
            'temp_prod_data'
        ));
    }

    public function bulkUploadPrices()
    {
        $suppliers = Supplier::where('status', 1)->get();
        $primary_category = ProductCategory::where('parent_id', 0)->get();
        return $this->render('users.products.add-bulk-prices', compact('suppliers', 'primary_category'));
    }

    public function saveBulkTempProduct(Request $request)
    {
        foreach ($request->temp_ids as $temp_id) { 
            $temp_product = TempProduct::find($temp_id);

            if (isset($request['supplier_id_' . $temp_id])) {
                $temp_product->supplier_id            = $request['supplier_id_' . $temp_id];
            }
            if (isset($request['primary_category_' . $temp_id])) {
                $temp_product->primary_category    = $request['primary_category_' . $temp_id];
            }
            if (isset($request['category_id_' . $temp_id])) {
                $temp_product->category_id            = $request['category_id_' . $temp_id];
            }
            if (isset($request['type_id_' . $temp_id])) {
                $temp_product->type_id           = $request['type_id' . $temp_id];
            }
            if (isset($request['buying_unit_' . $temp_id])) {
                $temp_product->buying_unit        = $request['buying_unit_' . $temp_id];
            }
            if (isset($request['selling_unit_' . $temp_id])) {
                $temp_product->selling_unit        = $request['selling_unit_' . $temp_id];
            }
            if (isset($request['stock_unit_' . $temp_id])) {
                $temp_product->stock_unit        = $request['stock_unit_' . $temp_id];
            }
            if (isset($request['brand_id_' . $temp_id])) {
                $temp_product->brand_id        = $request['brand_id_' . $temp_id];
            }
            if ($temp_product->refrence_code == null) {
                $same_product = Product::where('short_desc', $temp_product->short_desc)->where('brand', $temp_product->brand)->first();
                if ($same_product != null) {
                    return response()->json(["duplicate_error" => true, 'short_desc' => $temp_product->short_desc]);
                }
            } else {
                $temp_product->hasError = 0;
                $temp_product->save();
            }
        }

        return response()->json(["success" => true]);
    }

    public function moveTempToIncomplete(Request $request)
    {
        $request = $request->temp_id;
        $user_id = Auth::user()->id;
        $result = MoveBulkSupplierProductsJob::dispatch($user_id, $request);
    }

    public function getAllProdExcel()
    {
        return \Excel::download(new AllProductsExport, 'All Products Data Set.xlsx');
    }

    public function getFilteredProdExcel(Request $request)
    {
        $status = ExportStatus::where('type', 'bulk_price_export')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'bulk_price_export';
            $new->status  = 1;
            $new->save();
            AddBulkPricesJob::dispatch($request->suppliers, $request->primary_category, $request->sub_category, Auth::user()->id);
            return response()->json(['msg' => "file is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'bulk_price_export')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            AddBulkPricesJob::dispatch($request->suppliers, $request->primary_category, $request->sub_category, Auth::user()->id);
            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckBulkPrice()
    {
        $status = ExportStatus::where('type', 'bulk_price_export')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusFirstTimeForBulkPrice()
    {
        $status = ExportStatus::where('type', 'bulk_price_export')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function downloadSupplierAllProducts(Request $request)
    {
        $status = ExportStatus::where('type', 'bulk_product_export')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'bulk_product_export';
            $new->status  = 1;
            $new->save();
            AddBulkProductsJob::dispatch($request->suppliers, $request->primary_category, $request->sub_category, $request->types, $request->types_2, $request->types_3, Auth::user()->id);
            return response()->json(['msg' => "file is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'bulk_product_export')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            AddBulkProductsJob::dispatch($request->suppliers, $request->primary_category, $request->sub_category, $request->types, $request->types_2, $request->types_3, Auth::user()->id);
            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckBulkProduct()
    {
        $status = ExportStatus::where('type', 'bulk_product_export')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusFirstTimeForBulkProduct()
    {
        $status = ExportStatus::where('type', 'bulk_product_export')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function uploadBulkProductSuppliers(Request $request)
    {
        $validator = $request->validate([
            'excel' => 'required|mimes:csv,xlsx,xls'
        ]);
        Excel::import(new ProductSuppliersBulkImport, $request->file('excel'));
        return redirect()->back();
    }

    public function getTempProductData(Request $request)
    {
        $supplier = Supplier::where('status', 1)->get();
        $supplier_ids = $supplier->pluck('id')->toArray();
        $primary_category = ProductCategory::where('parent_id', 0)->get();
        $primary_category_ids = $primary_category->pluck('id')->toArray();
        $sub_category = ProductCategory::where('parent_id', '!=', 0)->get();
        $sub_category_ids = $sub_category->pluck('id')->toArray();
        $product_type = ProductType::all();
        $product_type_ids = $product_type->pluck('id')->toArray();
        $product_type_2 = ProductSecondaryType::all();
        $product_type_2_ids = $product_type_2->pluck('id')->toArray();
        $product_type_3 = ProductTypeTertiary::all();
        $product_type_3_ids = $product_type_3->pluck('id')->toArray();

        $units = Unit::all();
        $units_ids = $units->pluck('id')->toArray();
        $query = TempProduct::query();

        $query->with('tempProductType', 'tempProductCategory', 'tempProductSubCategory', 'tempUnits', 'tempSellingUnits', 'tempSupplier', 'tempStockUnits', 'tempProductType2', 'tempProductType3');
        if ($request->id != null) {
            $query->where('supplier_id', $request->id);
        }
        $query->orderBy('id');

        $dt =  Datatables::of($query);
        $dt->addColumn('action', function ($item) {
            $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
              <input class="custom-control-input check_temp" type="checkbox"
                     id="temp_check_' . $item->id . '" name="selected_ids[]"
                     value="' . $item->id . '">
              <label class="custom-control-label" for="temp_check_' . $item->id . '"></label>
              </div>';
            return $html_string;
        });
        $globalAccessConfig2 = QuotationConfig::where('section', 'products_management_page')->first();
        if ($globalAccessConfig2) {
            $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);

            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "allow_custom_code_edit") {
                    $allow_custom_code_edit = $val['status'];
                }
            }
        } else {
            $allow_custom_code_edit = '';
        }
        $dt->addColumn('product_ref_no', function ($item) use ($allow_custom_code_edit) {
            if ($allow_custom_code_edit == 1) {
                $class = 'inputDoubleClick';
            } else {
                $class = '';
            }
            $html_string = '
        <span class="m-l-15 ' . $class . ' " id="refrence_code"  data-fieldvalue="' . @$item->refrence_code . '">';
            $html_string .= $item->refrence_code != NULL ? $item->refrence_code : "--";
            $html_string .= '</span>';

            $html_string .= '<input type="text" style="width:100%;" name="refrence_code" class="fieldFocus d-none" value="' . $item->refrence_code . '">';
            return $html_string;
        });
        $dt->addColumn('system_code', function ($item) {
            $html_string = $item->system_code != NULL ? $item->system_code : "--";
            return $html_string;
        });
        $dt->addColumn('supplier', function ($item) use ($supplier, $supplier_ids) {
            $html_string = '';
            if ($item->supplier_id == null || !in_array($item->supplier_id, $supplier_ids)) {
                $html_string .= '<td><input type="hidden" name="temp_ids[]" value="' . $item->id . '">
                <select required name="supplier_id"
                    class="form-control turngreen btn-outline-danger" required>
                <option value="" disabled selected>' . @$item->tempSupplier->reference_name . ' (Incomplete) </option>';

                foreach ($supplier as $sup) {
                    $html_string .= '<option value="' . $sup->id . '">' . $sup->reference_name . '</option>';
                }
                $html_string .= '</select>
              </td>';
            } else {
                $html_string .= '<td><input type="hidden" name="temp_ids[]" value="' . $item->id . '">' . $item->tempSupplier->reference_name . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('supplier_description', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="supplier_description"  data-fieldvalue="' . @$item->supplier_description . '">';
            $html_string .= $item->supplier_description != NULL ? $item->supplier_description : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="supplier_description" class="fieldFocus d-none" value="' . $item->supplier_description . '">';
            return $html_string;
        });
        $dt->addColumn('hs_code', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="hs_code"  data-fieldvalue="' . @$item->hs_code . '">';
            $html_string .= $item->hs_code != NULL ? $item->hs_code : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="hs_code" class="fieldFocus d-none" value="' . $item->hs_code . '">';
            return $html_string;
        });
        $dt->addColumn('short_desc', function ($item) {
            if ($item->short_desc == null) {
                $text_color = 'color: red;';
            } else {
                $text_color = '';
            }
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="short_desc" style="' . $text_color . '" data-fieldvalue="' . @$item->short_desc . '">';
            $html_string .= $item->short_desc != NULL ? $item->short_desc : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="short_desc" class="fieldFocus d-none" value="' . $item->short_desc . '">';
            return $html_string;
        });
        $dt->addColumn('weight', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="weight"  data-fieldvalue="' . @$item->weight . '">';
            $html_string .= $item->weight != NULL ? $item->weight : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="weight" class="fieldFocus d-none" value="' . $item->weight . '">';
            return $html_string;
        });
        $dt->addColumn('primary_category', function ($item) use ($primary_category, $primary_category_ids) {
            $html_string = '';
            if ($item->primary_category == null || !in_array($item->primary_category, $primary_category_ids)) {
                $html_string .= '<td>
                  <select required name="primary_category" class="form-control turngreen btn-outline-danger" required>
                      <option value="" disabled selected>' . $item->primary_category . '</option>';

                foreach ($primary_category as $cat) {
                    $html_string .= '<option value="' . $cat->id . '">' . $cat->title . '</option>';
                }
                $html_string .= '</select></td>';
            } else {
                $html_string .= '<td>' . $item->tempProductCategory->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('category_id', function ($item) use ($sub_category, $sub_category_ids) {
            if (is_numeric($item->primary_category)) {
                $sub_category = $item->tempProductSubCategory;
            }
            $html_string = '';
            if ($item->category_id == null || !in_array($item->category_id, $sub_category_ids)) {
                $html_string .= '<td>
                  <select required name="category_id"
                          class="form-control turngreen btn-outline-danger" required>
                      <option value="" disabled selected>' . $item->category_id . '</option>';
                if ($sub_category != null) {
                    foreach ($sub_category as $cat) {
                        $html_string .= '<option value="' . $cat->id . '">' . $cat->title . '</option>';
                    }
                }
                $html_string .= '</select>
              </td>';
            } else {
                $html_string .= '<td>' . $item->tempProductSubCategory->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('type_id', function ($item) use ($product_type, $product_type_ids) {
            $html_string = '';
            if ($item->type_id == null || !in_array($item->type_id, $product_type_ids)) {
                $html_string .= '<td>
                  <select required name="type_id"
                          class="form-control turngreen btn-outline-danger" required>
                      <option value="" disabled selected>' . $item->type_id . '</option>';
                //   dd('product_type');
                foreach ($product_type as $type) {
                    $html_string .= '<option value="' . $type->id . '">' . $type->title . '</option>';
                }
                $html_string .= '</select>
              </td>';
            } else {
                $html_string .= '<td>' . $item->tempProductType->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('type_2_id', function ($item) use ($product_type_2, $product_type_2_ids) {
            $html_string = '';
            if ($item->type_2_id == null || !in_array($item->type_2_id, $product_type_2_ids)) {
                $html_string .= '<td>
                  <select required name="type_2_id"
                          class="form-control turngreen" required>
                      <option value="" disabled selected>' . $item->type_2_id . '</option>';
                //   dd('product_type_2');
                foreach ($product_type_2 as $type) {
                    $html_string .= '<option value="' . $type->id . '">' . $type->title . '</option>';
                }
                $html_string .= '</select>
              </td>';
            } else {
                $html_string .= '<td>' . $item->tempProductType2->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('type_3_id', function ($item) use ($product_type_3, $product_type_3_ids) {
            $html_string = '';
            if ($item->type_3_id == null || !in_array($item->type_3_id, $product_type_3_ids)) {
                $html_string .= '<td>
                  <select required name="type_3_id"
                          class="form-control turngreen" required>
                      <option value="" disabled selected>' . $item->type_3_id . '</option>';
                //   dd('product_type_2');
                foreach ($product_type_3 as $type) {
                    $html_string .= '<option value="' . $type->id . '">' . $type->title . '</option>';
                }
                $html_string .= '</select>
              </td>';
            } else {
                $html_string .= '<td>' . $item->tempProductType3->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('brand_id', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="brand"  data-fieldvalue="' . @$item->brand . '">';
            $html_string .= $item->brand != NULL ? $item->brand : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="' . $item->brand . '">';
            return $html_string;
        });
        $dt->addColumn('product_temprature_c', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="' . @$item->product_temprature_c . '">';
            $html_string .= $item->product_temprature_c != NULL ? $item->product_temprature_c : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="' . $item->product_temprature_c . '">';
            return $html_string;
        });
        $dt->addColumn('buying_unit', function ($item) use ($units, $units_ids) {
            $html_string = '';
            if ($item->buying_unit == null || !in_array($item->buying_unit, $units_ids)) {
                $html_string .= '<td>
                  <select required name="buying_unit"
                          class="form-control turngreen btn-outline-danger" required>
                      <option value="" disabled selected>' . $item->buying_unit . '</option>';
                //   dd('units');
                foreach ($units as $u) {
                    $html_string .= '<option value="' . $u->id . '">' . $u->title . '</option>';
                }

                $html_string .= '</select>
              </td>';
            } else {
                $html_string .= '<td>' . $item->tempUnits->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('selling_unit', function ($item)  use ($units, $units_ids) {
            $html_string = '';
            if ($item->selling_unit == null || !in_array($item->selling_unit, $units_ids)) {
                $html_string .= '<td>
                  <select required name="selling_unit"
                          class="form-control turngreen btn-outline-danger" required>
                      <option value="" disabled selected>' . $item->selling_unit . '</option>';
                //   dd('units');
                foreach ($units as $u) {
                    $html_string .= '<option value="' . $u->id . '">' . $u->title . '</option>';
                }
                $html_string .= '</select>
              </td>';
            } else {
                $html_string .= '<td>' . $item->tempSellingUnits->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('stock_unit', function ($item)  use ($units, $units_ids) {
            $html_string = '';
            if ($item->stock_unit == null || !in_array($item->stock_unit, $units_ids)) {
                $html_string .= '<td>
                <select required name="stock_unit"
                        class="form-control turngreen" required>
                    <option value="" disabled selected>' . $item->stock_unit . '</option>';
                // dd('units');
                foreach ($units as $u) {
                    $html_string .= '<option value="' . $u->id . '">' . $u->title . '</option>';
                }
                $html_string .= '</select>
            </td>';
            } else {
                $html_string .= '<td>' . $item->tempStockUnits->title . '</td>';
            }
            return $html_string;
        });
        $dt->addColumn('unit_conversion_rate', function ($item) {
            if ($item->unit_conversion_rate == null) {
                $text_color = 'color: red;';
            } else {
                $text_color = '';
            }
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate" style="' . $text_color . '"  data-fieldvalue="' . @$item->unit_conversion_rate . '">';
            $html_string .= $item->unit_conversion_rate != NULL ? $item->unit_conversion_rate : "--";
            $html_string .= '</span>';

            $html_string .= '<input type="number" style="width:100%;" name="unit_conversion_rate" class="fieldFocus d-none" value="' . $item->unit_conversion_rate . '">';
            return $html_string;
        });
        $dt->addColumn('min_stock', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="min_stock"  data-fieldvalue="' . @$item->min_stock . '">';
            $html_string .= $item->min_stock != NULL ? $item->min_stock : "--";
            $html_string .= '</span>';

            $html_string .= '<input type="text" style="width:100%;" name="min_stock" class="fieldFocus d-none" value="' . $item->min_stock . '">';
            return $html_string;
        });
        $dt->addColumn('m_o_q', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="m_o_q"  data-fieldvalue="' . @$item->m_o_q . '">';
            $html_string .= $item->m_o_q != NULL ? $item->m_o_q : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="m_o_q" class="fieldFocus d-none" value="' . $item->m_o_q . '">';
            return $html_string;
        });
        $dt->addColumn('supplier_packing_unit', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="supplier_packaging"  data-fieldvalue="' . @$item->supplier_packaging . '">';
            $html_string .= $item->supplier_packaging != NULL ? $item->supplier_packaging : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="supplier_packaging" class="fieldFocus d-none" value="' . $item->supplier_packaging . '">';
            return $html_string;
        });
        $dt->addColumn('billed_unit', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="billed_unit"  data-fieldvalue="' . @$item->billed_unit . '">';
            $html_string .= $item->billed_unit != NULL ? $item->billed_unit : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="billed_unit" class="fieldFocus d-none" value="' . $item->billed_unit . '">';
            return $html_string;
        });
        $dt->addColumn('import_tax_book', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="import_tax_book"  data-fieldvalue="' . @$item->import_tax_book . '">';
            $html_string .= $item->import_tax_book != NULL ? $item->import_tax_book : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="import_tax_book" class="fieldFocus d-none" value="' . $item->import_tax_book . '">';
            return $html_string;
        });
        $dt->addColumn('import_tax_actual', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="import_tax_actual"  data-fieldvalue="' . @$item->import_tax_actual . '">';
            $html_string .= $item->import_tax_actual != NULL ? $item->import_tax_actual : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="import_tax_actual" class="fieldFocus d-none" value="' . $item->import_tax_actual . '">';
            return $html_string;
        });
        $dt->addColumn('vat', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="vat"  data-fieldvalue="' . @$item->vat . '">';
            $html_string .= $item->vat !== NULL ? $item->vat : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="vat" class="fieldFocus d-none" value="' . $item->vat . '">';
            return $html_string;
        });
        $dt->addColumn('p_s_r', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="p_s_r"  data-fieldvalue="' . @$item->p_s_r . '">';
            $html_string .= $item->p_s_r != NULL ? $item->p_s_r : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="p_s_r" class="fieldFocus d-none" value="' . $item->p_s_r . '">';
            return $html_string;
        });
        $dt->addColumn('buying_price', function ($item) {
            if ($item->buying_price == null) {
                $text_color = 'color: red;';
            } else {
                $text_color = '';
            }
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="buying_price" style="' . $text_color . '" data-fieldvalue="' . @$item->buying_price . '">';
            $html_string .= $item->buying_price != NULL ? number_format($item->buying_price, 2, '.', ',') : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="buying_price" class="fieldFocus d-none" value="' . $item->buying_price . '">';
            return $html_string;
        });
        $dt->addColumn('gross_weight', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="gross_weight"  data-fieldvalue="' . @$item->gross_weight . '">';
            $html_string .= $item->gross_weight != NULL ? $item->gross_weight : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="gross_weight" class="fieldFocus d-none" value="' . $item->gross_weight . '">';
            return $html_string;
        });
        $dt->addColumn('freight', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="freight"  data-fieldvalue="' . @$item->freight . '">';
            $html_string .= $item->freight != NULL ? $item->freight : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="freight" class="fieldFocus d-none" value="' . $item->freight . '">';
            return $html_string;
        });
        $dt->addColumn('landing', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="landing"  data-fieldvalue="' . @$item->landing . '">';
            $html_string .= $item->landing != NULL ? $item->landing : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="landing" class="fieldFocus d-none" value="' . $item->landing . '">';
            return $html_string;
        });
        $dt->addColumn('extra_cost', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="extra_cost"  data-fieldvalue="' . @$item->extra_cost . '">';
            $html_string .= $item->extra_cost != NULL ? $item->extra_cost : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="extra_cost" class="fieldFocus d-none" value="' . $item->extra_cost . '">';
            return $html_string;
        });
        $dt->addColumn('extra_tax', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="extra_tax"  data-fieldvalue="' . @$item->extra_tax . '">';
            $html_string .= $item->extra_tax != NULL ? $item->extra_tax : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="number" style="width:100%;" name="extra_tax" class="fieldFocus d-none" value="' . $item->extra_tax . '">';
            return $html_string;
        });
        $dt->addColumn('lead_time', function ($item) {
            if ($item->leading_time == null) {
                $text_color = 'color: red;';
            } else {
                $text_color = '';
            }
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="leading_time" style="' . $text_color . '" data-fieldvalue="' . @$item->leading_time . '">';
            $html_string .= $item->leading_time != NULL ? $item->leading_time : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="leading_time" class="fieldFocus d-none" value="' . $item->leading_time . '">';
            return $html_string;
        });
        $dt->addColumn('product_notes', function ($item) {
            $html_string = '
        <span class="m-l-15 inputDoubleClick" id="product_notes"  data-fieldvalue="' . @$item->product_notes . '">';
            $html_string .= $item->product_notes != NULL ? $item->product_notes : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="product_notes" class="fieldFocus d-none" value="' . $item->product_notes . '">';
            return $html_string;
        });
        $customerCategory = CustomerCategory::where('is_deleted', 0)->get();
        $i = 0;
        // dd('customerCategory');
        foreach ($customerCategory as $customerCat) {
            $title = $customerCat->title;
            $dt->addColumn($customerCat->title, function ($item) use ($i, $title) {
                $fixedPricesarray = unserialize($item->fixed_prices_array);
                if (array_key_exists($i, $fixedPricesarray)) {
                    $html_string = '
            <span class="m-l-15 inputDoubleClick" id="' . $title . '" data-indexval="' . $i . '"  data-fieldvalue="' . $fixedPricesarray[$i] . '">';
                    $html_string .= $fixedPricesarray[$i] !== NULL ? number_format($fixedPricesarray[$i], 2, '.', ',') : "--";
                    $html_string .= '</span>';
                    $html_string .= '<input type="number" style="width:100%;" name="' . $title . '" class="fieldFocusFp d-none" value="' . $fixedPricesarray[$i] . '">';
                } else {
                    $html_string = '
            <span class="m-l-15 inputDoubleClick" id="' . $title . '" data-indexval="" data-fieldvalue="">';
                    $html_string .= "--";
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:100%;" name="' . $title . '" class="fieldFocusFp d-none" value="">';
                }
                return $html_string;
            });
            $i++;
        }
        $dt->addColumn('order_qty_per_piece', function ($item) {
            $html_string = '
          <span class="m-l-15 inputDoubleClick" id="order_qty_per_piece"  data-fieldvalue="' . @$item->order_qty_per_piece . '">';
            $html_string .= $item->order_qty_per_piece != NULL ? $item->order_qty_per_piece : "--";
            $html_string .= '</span>';
            $html_string .= '<input type="text" style="width:100%;" name="order_qty_per_piece" class="fieldFocus d-none" value="' . $item->order_qty_per_piece . '">';
            return $html_string;
        });
        $dt->setRowId(function ($item) {
            return @$item->id;
        });
        $dt->setRowClass(function ($item) {
            if ($item->status == 0) {
                return  'yellowRow';
            }
        });
        $dt->escapeColumns([]);
        return $dt->make(true);
    }

    public function saveTempProductData(Request $request)
    {
        $allow_same_description = '';
        $globalAccessConfig2 = QuotationConfig::where('section', 'products_management_page')->first();
        if ($globalAccessConfig2) {
            $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "same_description") {
                    $allow_same_description = $val['status'];
                }
            }
        }
        $completed = 0;
        $reload = 0;
        $temp_product = TempProduct::find($request->prod_detail_id);
        foreach ($request->except('prod_detail_id') as $key => $value) {
            if ($key == 'primary_category') {
                $temp_product->$key = $value;
                $temp_product->category_id = null;
                $reload = 1;
            } elseif ($key == 'buying_unit' || $key == 'category_id' || $key == 'type_id' || $key == 'stock_unit') {
                $temp_product->$key = $value;
                $reload = 1;
            } elseif ($key == 'selling_unit' || $key == 'supplier_id') {
                $temp_product->$key = $value;
                $reload = 1;
            } elseif ($key == 'short_desc') {
                if ($allow_same_description == 0) {
                    $same_product = Product::where('short_desc', $value)->first();
                    if ($same_product != null) {
                        $reload = 1;
                        return response()->json(['data' => 'short_desc', 'error' => 1]);
                    } else {
                        $temp_product->$key = $value;
                        $reload = 1;
                    }
                } else {
                    $product_detail->$key = $value;
                    $reload = 1;
                }
            } else {
                $temp_product->$key = $value;
                $temp_product->save();
            }
        }
        $temp_product->save();
        if ($temp_product->status == 0) {
            $request->id = $request->prod_detail_id;
            $mark_as_complete = $this->doTempProductCompleted($request);
            $json_response = json_decode($mark_as_complete->getContent());
            if ($json_response->success == true) {
                $prod_complete = TempProduct::find($request->id);
                $prod_complete->status = 1;
                $prod_complete->save();
                $completed = 1;
            }
        }
        return response()->json(['completed' => $completed, 'reload' => $reload]);
    }

    public function saveTempProductDataFixedPrice(Request $request)
    {
        $completed = 0;
        $reload = 0;
        $temp_product = TempProduct::find($request->prod_detail_id);
        $deserialized = unserialize($temp_product->fixed_prices_array);

        if ($request->index_value !== NULL && $request->index_value !== '') {
            foreach ($deserialized as $key => $value) {
                if ($key == $request->index_value) {
                    $deserialized[$request->index_value] = $request->field_value;
                }
            }
            $temp_product->fixed_prices_array = serialize($deserialized);
        } else {
            $max = max(array_keys($deserialized));
            $new_index_val = $max + 1;

            $deserialized[] = $request->field_value;

            $temp_product->fixed_prices_array = serialize($deserialized);
        }
        $temp_product->save();
        if ($temp_product->status == 0) {
            $request->id = $request->prod_detail_id;
            $mark_as_complete = $this->doTempProductCompleted($request);
            $json_response = json_decode($mark_as_complete->getContent());
            if ($json_response->success == true) {
                $prod_complete = TempProduct::find($request->id);
                $prod_complete->status = 1;
                $prod_complete->save();
                $completed = 1;
            }
        }
        return response()->json(['completed' => $completed, 'reload' => $reload]);
    }

    public function doTempProductCompleted(Request $request)
    {
        if ($request->id) {
            $product = TempProduct::find($request->id);
            $missingPrams = array();
            if ($product->short_desc == null) {
                $missingPrams[] = 'Short Description';
            }
            if ($product->primary_category == null) {
                $missingPrams[] = 'Primary Category';
            }
            if ($product->category_id == null) {
                $missingPrams[] = 'Sub Category';
            }
            if ($product->type_id == null) {
                $missingPrams[] = 'Product Type';
            }
            if ($product->buying_unit == null) {
                $missingPrams[] = 'Billed Unit';
            }
            if ($product->selling_unit == null) {
                $missingPrams[] = 'Selling Unit';
            }
            if ($product->unit_conversion_rate == null) {
                $missingPrams[] = 'Unit Conversion Rate';
            }
            if ($product->supplier_id == null) {
                $missingPrams[] = 'Default Supplier';
            }
            if ($product->buying_price == null) {
                $missingPrams[] = 'Buying Price';
            }
            if ($product->leading_time == null) {
                $missingPrams[] = 'Leading Time';
            }
            if (sizeof($missingPrams) == 0) {
                $product->status = 1;
                $product->save();
                $message = "completed";

                return response()->json(['success' => true, 'message' => $message, 'missingPrams' => $missingPrams]);
            } else {
                $message = implode(', ', $missingPrams);
                return response()->json(['success' => false, 'message' => $message]);
            }
        }
    }

    public function discardTemp(Request $request)
    {
        if ($request->id != null) {
            $products = TempProduct::where('supplier_id', $request->id)->get();
        }
        else{
            $products = TempProduct::get();
        }
        foreach ($products as $product) {
            $product->delete();
        }
        return 'true';
    }

    public function discardSelectedTempData(Request $request)
    {
        $temp_prod_ids = $request->prod_ids;
        $products = TempProduct::whereIn('id', $temp_prod_ids)->get();
        foreach ($products as $product) {
            $product->delete();
        }
        return "success";
    }

    public function getInCompleteData(Request $request)
    {
        $query = Product::query();
        $query->with('def_or_last_supplier', 'units', 'prouctImages', 'productSubCategory')->where('status', 0);
        if ($request->product_category_dd != '' && $request->product_category_dd != 0) {
            $query->where('category_id', $request->product_category_dd)->orderBy('id', 'DESC');
        }
        return Datatables::of($query)
            ->addColumn('checkbox', function ($item) {

                $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="' . $item->id . '" id="product_check_' . $item->id . '">
                                <label class="custom-control-label" for="product_check_' . $item->id . '"></label>
                              </div>';
                return $html_string;
            })
            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 <a href="' . url('get-product-detail/' . $item->id) . '" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>
                 ';
                return $html_string;
            })
            ->editColumn('refrence_code', function ($item) {
                if ($item->refrence_code == null) {
                    $html_string = '
                <span class="m-l-15" id="refrence_code"  data-fieldvalue="' . @$item->refrence_code . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';
                    $html_string .= '<input type="text" style="width:100%;" name="refrence_code" class="d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15" id="refrence_code"  data-fieldvalue="' . @$item->refrence_code . '">';
                    $html_string .= $item->refrence_code;
                    $html_string .= '</span>';
                    $html_string .= '<input type="text" style="width:100%;" name="refrence_code" class="d-none" value="' . $item->refrence_code . '">';
                }
                return $html_string;
            })
            ->addColumn('p_s_reference_number', function ($item) {
                if ($item->supplier_id !== 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        $html_string = $getProductDefaultSupplier->product_supplier_reference_no != NULL ? $getProductDefaultSupplier->product_supplier_reference_no : "--";
                    }
                    return $html_string;
                } else {
                    return "--";
                }
            })
            ->addColumn('hs_code', function ($item) {
                $hs_code = $item->hs_code != null ? $item->hs_code : "--";
                return $hs_code;
            })
            ->addColumn('category_id', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClick" id="category_id" data-fieldvalue="' . @$item->category_id . '"> ';
                $html_string .= ($item->primary_category != null) ? $item->productCategory->title . ' / ' . $item->productSubCategory->title : "--";
                $html_string .= '</span>';
                $html_string .= '<div class="incomplete-filter d-none inc-fil-cat">
                <select class="font-weight-bold form-control-lg form-control js-states state-tags select-common category_id" name="category_id" required="true">
                    <option value="">Choose Category</option>';
                $product_parent_category = ProductCategory::where('parent_id', 0)->orderBy('title')->get();
                if ($product_parent_category->count() > 0) {
                    foreach ($product_parent_category as $pcat) {
                        $html_string .= '<optgroup label=' . $pcat->title . '>';
                        $subCat = ProductCategory::where('parent_id', $pcat->id)->orderBy('title')->get();
                        foreach ($subCat as $scat) {
                            $html_string .= '<option ' . ($scat->id == $item->category_id ? 'selected' : '') . ' value="' . $scat->id . '">' . $scat->title . '</option>';
                        }
                        $html_string .= '</optgroup>';
                    }
                }
                $html_string .= '</select></div>';
                return $html_string;
            })
            ->editColumn('short_desc', function ($item) {
                if ($item->short_desc == null) {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick desc-width" id="short_desc" style="' . $text_color . '" data-fieldvalue="' . @$item->short_desc . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';
                    $html_string .= '<input type="text"  name="short_desc" style="width:100%;" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick desc-width" id="short_desc" data-fieldvalue="' . @$item->short_desc . '">';
                    $html_string .= $item->short_desc;
                    $html_string .= '</span>';
                    $html_string .= '<input type="text"  name="short_desc" style="width:100%;" class="fieldFocus d-none" value="' . $item->short_desc . '">';
                }
                return $html_string;
            })
            ->addColumn('buying_unit', function ($item) {
                $units = Unit::all();
                if ($item->buying_unit == null) {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="buying_unit" style="' . $text_color . '"  data-fieldvalue="' . @$item->units->title . '">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';
                    $html_string .= '<select name="buying_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                    if ($units) {
                        foreach ($units as $unit) {
                            $html_string .= '<option  value="' . $unit->id . '"> ' . $unit->title . '</option>';
                        }
                    }
                    $html_string .= '</select>';

                    $html_string .= '<input type="text"  name="buying_unit" class="fieldFocus d-none" value="' . @$item->units->title . '">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="buying_unit"  data-fieldvalue="' . @$item->units->title . '">';
                    $html_string .= @$item->units->title;
                    $html_string .= '</span>';

                    $html_string .= '<select name="buying_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                    if ($units) {
                        foreach ($units as $unit) {
                            $value = $unit->id == $item->buying_unit ? 'selected' : "";
                            $html_string .= '<option ' . $value . ' value="' . $unit->id . '"> ' . $unit->title . '</option>';
                        }
                    }
                    $html_string .= '</select>';

                    $html_string .= '<input type="text" name="buying_unit" class="fieldFocus d-none" value="' . @$item->units->title . '">';
                }
                return $html_string;
            })
            ->addColumn('selling_unit', function ($item) {
                $units = Unit::all();
                if ($item->selling_unit == null) {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit" style="' . $text_color . '"  data-fieldvalue="' . @$item->sellingUnits->title . '">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';
                    $html_string .= '<select name="selling_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                    if ($units) {
                        foreach ($units as $unit) {
                            $html_string .= '<option  value="' . $unit->id . '"> ' . $unit->title . '</option>';
                        }
                    }
                    $html_string .= '</select>';
                    $html_string .= '<input type="text"  name="selling_unit" class="fieldFocus d-none" value="' . @$item->sellingUnits->title . '">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit"  data-fieldvalue="' . @$item->sellingUnits->title . '">';
                    $html_string .= @$item->sellingUnits->title;
                    $html_string .= '</span>';
                    $html_string .= '<select name="selling_unit" class="select-common form-control buying-unit d-none">
                <option>Choose Unit</option>';
                    if ($units) {
                        foreach ($units as $unit) {
                            $value = $unit->id == $item->selling_unit ? 'selected' : "";
                            $html_string .= '<option ' . $value . ' value="' . $unit->id . '"> ' . $unit->title . '</option>';
                        }
                    }
                    $html_string .= '</select>';
                    $html_string .= '<input type="text" name="selling_unit" class="fieldFocus d-none" value="' . @$item->sellingUnits->title . '">';
                }
                return $html_string;
            })
            // new added
            ->addColumn('product_type', function ($item) {
                $product_type = ProductType::all();
                if ($item->type_id == null) {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type" style="' . $text_color . '" data-fieldvalue="' . @$item->type_id . '">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';

                    $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
                <option value="" selected="" disabled="">Choose Product Type</option>';
                    foreach ($product_type as $type) {
                        $html_string .= '<option value="' . $type->id . '" >' . $type->title . '</option>';
                    }
                    $html_string .= '</select>';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type"  data-fieldvalue="' . @$item->type_id . '">';
                    $html_string .= @$item->productType->title;
                    $html_string .= '</span>';

                    $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
                <option value="" disabled="">Choose Type</option>';
                    foreach ($product_type as $type) {
                        $html_string .= '<option value="' . $type->id . '" "' . ($item->type_id == $type->id ? "selected" : "") . '" >' . $type->title . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })
            ->addColumn('product_type_2', function ($item) {
                $product_type = ProductSecondaryType::orderBy('title', 'asc')->get();
                if ($item->type_id_2 == null) {
                    // $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_2" data-fieldvalue="' . @$item->type_id_2 . '">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';

                    $html_string .= '<select name="type_id_2" class="select-common form-control product_type_2 d-none">
                <option value="" selected="" disabled="">Choose Product Type 2</option>';
                    foreach ($product_type as $type) {
                        $html_string .= '<option value="' . $type->id . '" >' . $type->title . '</option>';
                    }
                    $html_string .= '</select>';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_2"  data-fieldvalue="' . @$item->type_id_2 . '">';
                    $html_string .= @$item->productType2->title;
                    $html_string .= '</span>';

                    $html_string .= '<select name="type_id_2" class="select-common form-control product_type d-none">
                <option value="" disabled="">Choose Product Type 2</option>';
                    foreach ($product_type as $type) {
                        $html_string .= '<option value="' . $type->id . '" "' . ($item->type_id_2 == $type->id ? "selected" : "") . '" >' . $type->title . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })
            ->addColumn('product_type_3', function ($item) {
                $product_type = ProductTypeTertiary::orderBy('title', 'asc')->get();
                if ($item->type_id_3 == null) {
                    // $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_3" data-fieldvalue="' . @$item->type_id_3 . '">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';

                    $html_string .= '<select name="type_id_3" class="select-common form-control product_type_3 d-none">
                <option value="" selected="" disabled="">Choose Product Type 3</option>';
                    foreach ($product_type as $type) {
                        $html_string .= '<option value="' . $type->id . '" >' . $type->title . '</option>';
                    }
                    $html_string .= '</select>';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_3"  data-fieldvalue="' . @$item->type_id_3 . '">';
                    $html_string .= @$item->productType3->title;
                    $html_string .= '</span>';

                    $html_string .= '<select name="type_id_3" class="select-common form-control product_type_3 d-none">
                <option value="" disabled="">Choose Product Type 3</option>';
                    foreach ($product_type as $type) {
                        $html_string .= '<option value="' . $type->id . '" "' . ($item->type_id_3 == $type->id ? "selected" : "") . '" >' . $type->title . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })
            ->addColumn('product_brand', function ($item) {
                if ($item->brand == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick brand-width" id="brand"  data-fieldvalue="' . @$item->brand . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick brand-width" id="brand"  data-fieldvalue="' . @$item->brand . '">';
                    $html_string .= $item->brand;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="' . $item->brand . '">';
                }
                return $html_string;
            })
            ->addColumn('product_temprature_c', function ($item) {
                if ($item->product_temprature_c == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick temp-width" id="product_temprature_c"  data-fieldvalue="' . @$item->product_temprature_c . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick temp-width" id="product_temprature_c"  data-fieldvalue="' . @$item->product_temprature_c . '">';
                    $html_string .= $item->product_temprature_c;
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="' . $item->product_temprature_c . '">';
                }
                return $html_string;
            })
            ->addColumn('supplier_id', function ($item) {
                // return @$item->supplier->company;
                // $supplier_products = SupplierProducts::where('product_id',$item->id)->get();
                // $checkedIds = SupplierProducts::where('product_id',$item->id)->where('supplier_id','!=',NULL)->pluck('supplier_id')->toArray();
                // $getSuppliers = Supplier::whereNotIn('id',$checkedIds)->where('status',1)->orderBy('reference_name')->get();

                $getSuppliers = Supplier::where('status', 1)->orderBy('reference_name')->get();
                if ($item->supplier_id === 0) {
                    $text_color = 'color: red;';
                    $html_string = '
                    <span class="m-l-15 inputDoubleClick sup-width" id="supplier_id" style="' . $text_color . '"  data-fieldvalue="' . @$item->supplier_id . '">';
                    $html_string .= 'Select Supplier';
                    $html_string .= '</span>';

                    $html_string .= '<div class="d-none incomplete-filter inc-fil-supp"><select class="font-weight-bold form-control-lg form-control select-common js-states state-tags supplier_id" name="supplier_id" required="true">
                         <option value="" >Choose Supplier</option>';
                    if ($getSuppliers) {
                        foreach ($getSuppliers as $sp) {
                            $html_string .= '<option  value="' . $sp->id . '"> ' . $sp->reference_name . '</option>';
                        }
                    }
                    $html_string .= '</select></div>';

                    // $html_string .= '<select name="supplier_id" data-prod-id="'.$item->id.'" class="select-common form-control  d-none">
                    // <option>Choose Supplier</option>';
                    // if($getSuppliers){
                    // foreach($getSuppliers as $sp){
                    //     $html_string .= '<option  value="'.$sp->id.'"> '.$sp->company.'</option>';
                    // }
                    // }
                    // $html_string .= '</select>';
                } else {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClick sup-width" id="supplier_id"  data-fieldvalue="' . @$item->supplier_id . '">';
                    $html_string .= @$item->def_or_last_supplier->reference_name;
                    $html_string .= '</span>';

                    $html_string .= '<div class="d-none incomplete-filter inc-fil-supp"><select class="font-weight-bold form-control-lg form-control select-common js-states state-tags supplier_id" name="supplier_id" required="true">
                     <option value="" >Choose Supplier</option>';
                    if ($getSuppliers) {
                        foreach ($getSuppliers as $sp) {
                            $html_string .= '<option ' . ($item->supplier_id == $sp->id ? 'selected' : '') . ' value="' . $sp->id . '"> ' . $sp->reference_name . '</option>';
                        }
                    }
                    $html_string .= '</select></div>';

                    // $html_string .= '<select name="supplier_id" data-prod-id="'.$item->id.'"  class="select-common form-control d-none">
                    // <option>Choose supplier</option>';
                    // if($getSuppliers){
                    // foreach($getSuppliers as $sp){
                    // $value = $sp->id == @$item->supplier_id ? 'selected' : "";
                    // $html_string .= '<option '.$value.' value="'.$sp->id.'"> '.$sp->reference_name.'</option>';
                    // }
                    // }
                    // $html_string .= '</select>';

                }
                return $html_string;
            })
            ->addColumn('import_tax_book', function ($item) {
                // $import_tax_book = $item->productSubCategory->import_tax_book != null ? $item->productSubCategory->import_tax_book.' %': "--";
                $import_tax_book = $item->import_tax_book != null ? $item->import_tax_book . ' %' : "--";
                return $import_tax_book;
                // if($item->import_tax_book == null)
                // {
                //     $text_color = 'color: red;';
                //     $html_string = '
                // <span class="m-l-15 inputDoubleClick" id="import_tax_book" style="'.$text_color.'" data-fieldvalue="'.@$item->import_tax_book.'">';
                // $html_string .= '--';
                // $html_string .= '</span>';

                // $html_string .= '<input type="number"  name="import_tax_book" style="width: 80%;" class="fieldFocus d-none" value=""> %';
                // }
                // else
                // {
                //     $html_string = '
                // <span class="m-l-15 inputDoubleClick" id="import_tax_book"  data-fieldvalue="'.@$item->import_tax_book.'">';
                // $html_string .= $item->import_tax_book;
                // $html_string .= '</span>';

                // $html_string .= '<input type="number"  name="import_tax_book" style="width: 80%;" class="fieldFocus d-none" value="'.$item->import_tax_book .'"> %';
                // }
                // return $html_string;

            })
            ->addColumn('vat', function ($item) {
                // $vat = $item->productSubCategory->vat != null ? $item->productSubCategory->vat.' %': "--";
                $vat = $item->vat !== null ? $item->vat . ' %' : "--";
                return $vat;
                // if($item->vat == null)
                // {
                //     $text_color = 'color: red;';
                //     $html_string = '
                // <span class="m-l-15 inputDoubleClick" id="vat" style="'.$text_color.'"  data-fieldvalue="'.@$item->vat.'">';
                // $html_string .= '--';
                // $html_string .= '</span>';

                // $html_string .= '<input type="number"  name="vat" style="width: 80%" class="fieldFocus d-none" value=""> %';
                // }
                // else
                // {
                //     $html_string = '
                // <span class="m-l-15 inputDoubleClick" id="vat"  data-fieldvalue="'.@$item->vat.'">';
                // $html_string .= $item->vat;
                // $html_string .= '</span>';

                // $html_string .= '<input type="number"  name="vat" style="width: 80%;" class="fieldFocus d-none" value="'.$item->vat .'"> %';
                // }
                // return $html_string;
            })
            ->addColumn('image', function ($item) {
                // check already uploaded images //
                $product_images = ProductImage::where('product_id', $item->id)->count('id');

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if ($product_images > 0) {
                    $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#images-modal" data-id="' . $item->id . '" class="fa fa-eye d-block show-prod-image mr-2" title="View Images"></a> ';
                }
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#productImagesModal" class="img-uploader fa fa-plus d-block" title="Add Images"></a>
                          </div>';

                return $html_string;
            })
            ->addColumn('freight', function ($item) {
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        $html_string = '<span class="m-l-15 inputDoubleClick" id="freight"  data-fieldvalue="' . @$getProductDefaultSupplier->freight . '">' . ($getProductDefaultSupplier->freight != NULL ? $getProductDefaultSupplier->freight : "--") . '</span>
                        <input type="text" style="width:100%;" name="freight" class="fieldFocus d-none" value="' . @$getProductDefaultSupplier->freight . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
            })
            ->addColumn('landing', function ($item) {
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        $html_string = '<span class="m-l-15 inputDoubleClick" id="landing"  data-fieldvalue="' . @$getProductDefaultSupplier->landing . '">' . ($getProductDefaultSupplier->landing != NULL ? $getProductDefaultSupplier->landing : "--") . '</span>
                        <input type="text" style="width:100%;" name="landing" class="fieldFocus d-none" value="' . @$getProductDefaultSupplier->landing . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
            })
            ->addColumn('supplier_desc', function ($item) {
                if ($item->supplier_id !== 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        $html_string = '<span class="m-l-15 inputDoubleClick sup_desc_width" id="supplier_description"  data-fieldvalue="' . @$getProductDefaultSupplier->supplier_description . '">' . ($getProductDefaultSupplier->supplier_description != NULL ? $getProductDefaultSupplier->supplier_description : "--") . '</span>
                        <input type="text" style="width:100%;" name="supplier_description" class="fieldFocus d-none" value="' . $getProductDefaultSupplier->supplier_description . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
            })
            ->addColumn('vendor_price', function ($item) {
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        if ($getProductDefaultSupplier->buying_price !== null) {
                            $supplier_currency_logo = @$getProductDefaultSupplier->supplier->getCurrency->currency_symbol;
                        } else {
                            $supplier_currency_logo = '';
                        }

                        $formated_value = number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '');

                        if ($getProductDefaultSupplier->buying_price !== null) {
                            $text_color = '';
                        } else {
                            $text_color = 'color: red;';
                        }

                        $html_string = '<span class="m-l-15 inputDoubleClick" style="' . $text_color . '" id="buying_price"  data-fieldvalue="' . @$getProductDefaultSupplier->buying_price . '">' . ($getProductDefaultSupplier->buying_price !== NULL ?  ' <b>' . @$supplier_currency_logo . '</b> ' . number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '') : "--") . '</span>
                        <input type="text" style="width:100%;" name="buying_price" class="fieldFocus d-none" value="' . number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '') . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
            })
            ->addColumn('vendor_price_in_thb', function ($item) {
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::with('supplier')->where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                    $formated_value = number_format((float)@$getProductDefaultSupplier->buying_price_in_thb, 3, '.', '');
                    return (@$getProductDefaultSupplier->buying_price_in_thb !== null) ? $formated_value : '--';
                } else {
                    return "--";
                }
            })
            ->addColumn('t_b_u_c_p_of_supplier', function ($item) {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                if ($getProductDefaultSupplier) {
                    $supplier_currency_logo = @$getProductDefaultSupplier->supplier->getCurrency->currency_symbol;
                    $formated_value = number_format((float)@$item->t_b_u_c_p_of_supplier, 3, '.', '');
                    return (@$item->t_b_u_c_p_of_supplier !== null) ? ' <b>' . @$supplier_currency_logo . '</b> ' . $formated_value : '--';
                } else {
                    return "--";
                }
            })
            ->addColumn('total_buy_unit_cost_price', function ($item) {
                $formated_value = number_format((float)@$item->total_buy_unit_cost_price, 3, '.', '');
                return (@$item->total_buy_unit_cost_price != null) ? $formated_value : '--';
            })
            ->addColumn('unit_conversion_rate', function ($item) {
                if ($item->unit_conversion_rate == null) {
                    $text_color = 'color: red;';
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate" style="' . $text_color . '" data-fieldvalue="' . number_format((float)@$item->unit_conversion_rate, 3, '.', '') . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="number"  name="unit_conversion_rate" style="width: 80%;" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate"  data-fieldvalue="' . number_format((float)@$item->unit_conversion_rate, 3, '.', '') . '">';
                    $html_string .= number_format((float)@$item->unit_conversion_rate, 3, '.', '');
                    $html_string .= '</span>';

                    $html_string .= '<input type="number"  name="unit_conversion_rate" style="width: 80%;" class="fieldFocus d-none" value="' . number_format((float)@$item->unit_conversion_rate, 3, '.', '') . '">';
                }
                return $html_string;
            })
            ->addColumn('selling_unit_cost_price', function ($item) {
                if ($item->selling_price == null) {
                    $html_string = '
                <span class="m-l-15" id="selling_price"  data-fieldvalue="' . number_format((float)@$item->selling_price, 3, '.', '') . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="selling_price" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15" id="selling_price"  data-fieldvalue="' . @$item->selling_price . '">';
                    $html_string .= number_format((float)@$item->selling_price, 3, '.', '');
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="selling_price" class="fieldFocus d-none" value="' . number_format((float)@$item->selling_price, 3, '.', '') . '">';
                }
                return $html_string;
                return (@$item->selling_price != null) ? number_format((float)@$item->selling_price, 3, '.', '') : '--';
            })
            ->addColumn('weight', function ($item) {
                // return (@$item->weight != null) ? @$item->weight." Kg":'-';
                if ($item->weight == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="weight" data-fieldvalue="' . @$item->weight . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="number"  name="weight" style="width: 100%;" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="weight"  data-fieldvalue="' . @$item->weight . '">';
                    $html_string .= $item->weight;
                    $html_string .= '</span>';

                    $html_string .= '<input type="number"  name="weight" style="width: 100%;" class="fieldFocus d-none" value="' . $item->weight . '">';
                }
                return $html_string;
            })
            ->addColumn('lead_time', function ($item) {
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        if ($getProductDefaultSupplier->leading_time !== null) {
                            $text_color = '';
                        } else {
                            $text_color = 'color: red;';
                        }

                        $html_string = '<span class="m-l-15 inputDoubleClick" style="' . $text_color . '" id="leading_time"  data-fieldvalue="' . @$getProductDefaultSupplier->leading_time . '">' . ($getProductDefaultSupplier->leading_time != NULL ? $getProductDefaultSupplier->leading_time : "--") . '</span>
                        <input type="text" style="width:100%;" name="leading_time" class="fieldFocus d-none" value="' . @$getProductDefaultSupplier->leading_time . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
            })
            ->addColumn('expiry', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->expiry == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->expiry . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="expiry" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->expiry . '">';
                    $html_string .= $item->expiry . " Kg";
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="expiry" class="fieldFocus d-none" value="' . $item->expiry . '">';
                }
                return $html_string;
            })
            ->addColumn('restaruant_price', function ($item) {
                $getRecord = new Product;
                $prodFixPrice   = $getRecord->getDataOfProductMargins($item->id, 1, "prodFixPrice");
                $formated_value = $prodFixPrice != 'N.A' ? number_format($prodFixPrice->fixed_price, 3, '.', ',') : 0;
                return $formated_value;
            })
            ->setRowId(function ($item) {
                return @$item->id;
            })
            ->rawColumns(['checkbox', 'action', 'name', 'primary_category', 'category_id', 'supplier_id', 'image', 'import_tax_book', 'freight', 'landing', 'vendor_price', 'total_buy_unit_cost_price', 'unit_conversion_rate', 'selling_unit_cost_price', 'weight', 'lead_time', 'refrence_code', 'vat', 'hs_code', 'short_desc', 'buying_unit', 'selling_unit', 'expiry', 'product_temprature_c', 'product_type', 'product_brand', 'vendor_price_in_thb', 't_b_u_c_p_of_supplier', 'supplier_desc', 'restaruant_price', 'refrence_no', 'product_type_2', 'product_type_3'])
            ->make(true);
    }

    public function showSingleSupplierRecord(Request $request)
    {
        $product_id  = $request->product_id;
        $supplier_id = $request->supplier_id;

        $prodSuppliers = SupplierProducts::with('supplier')->where('supplier_id', $supplier_id)->where('product_id', $product_id)->first();
        return response()->json([
            "error" => false,
            "prodSuppliers" => $prodSuppliers,
        ]);
    }

    public function purchaseFetchOrders(Request $request)
    {
        $params = $request->except('_token');
        $detail = [];
        if ($request->get('query')) {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $in_order_query  = Order::query();
            $order_query  = Order::query();

            foreach ($search_box_value as $result) {
                if (strstr($result, '-')) {
                    $in_order_query = $in_order_query->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%$result%");
                    $order_query = $order_query->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%$result%")->whereIn('primary_status', [1, 2]);
                } else {
                    $result = preg_replace("/[^0-9]/", "", $result);
                    $in_order_query = $in_order_query->orWhere('in_status_prefix', 'LIKE', "%$result%")->orWhere('in_ref_id', 'LIKE', "%$result%");
                    $order_query = $order_query->orWhere('status_prefix', 'LIKE', "%$result%")->orWhere('ref_id', 'LIKE', "%$result%")->whereIn('primary_status', [1, 2]);
                }
            }

            $detail2  = $in_order_query->take(10)->get();
            $detail  = $order_query->take(10)->get();
            if (!empty($detail)) {
                $output = '<ul class="dropdown-menu search-dropdown search_result_mobile" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                foreach ($detail2 as $row) {
                    if ($row->primary_status == 3) {
                        $output .= '<li><a href="' . route('get-completed-invoices-details', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->in_status_prefix . '-' . @$row->in_ref_prefix . $row->in_ref_id . '</a></li>';
                    } elseif ($row->primary_status == 2) {
                        $output .= '<li><a href="' . route('get-completed-draft-invoices', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                    } elseif ($row->primary_status == 17) {
                        $output .= '<li><a href="' . route('get-cancelled-order-detail', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                    } else {
                        $output .= '<li><a href="' . route('get-completed-quotation-products', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                    }
                }
                foreach ($detail as $row) {
                    if ($row->primary_status == 3) {
                        $output .= '<li><a href="' . route('get-completed-invoices-details', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                    } elseif ($row->primary_status == 1) {
                        $output .= '<li><a href="' . route('get-completed-quotation-products', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                    } elseif ($row->primary_status == 17) {
                        $output .= '<li><a href="' . route('get-cancelled-order-detail', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                    } else {
                        $output .= '<li><a href="' . route('get-completed-draft-invoices', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                    }
                }
                $output .= '</ul>';
                echo $output;
            } else {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        } else {
            echo '';
        }
    }

    public function purchaseFetchPurchaseOrders(Request $request)
    {
        $params = $request->except('_token');
        $detail = [];
        if ($request->get('query')) {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $order_query  = PurchaseOrder::query();

            foreach ($search_box_value as $result) {
                $order_query = $order_query->orWhere('ref_id', 'LIKE', "%$result%");
            }

            // dd($order_query);

            $order_query  = $order_query->pluck('id')->toArray();

            // dd($order_query);

            if (!empty($order_query)) {
                $product_detail = PurchaseOrder::orderBy('id', 'ASC');

                if (!empty($order_query)) {
                    $product_detail->where(function ($q) use ($order_query) {
                        $q->whereIn('id', $order_query);
                    });
                }

                $detail = $product_detail->take(10)->get();
            }

            if (!empty($detail)) {
                $output = '<ul class="dropdown-menu search-dropdown search_result_mobile" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                foreach ($detail as $row) {
                    $output .= '<li><a href="' . route('get-purchase-order-detail', ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->ref_id . '</a></li>';
                }
                $output .= '</ul>';
                echo $output;
            } else {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        } else {
            echo '';
        }
    }

    public function getSearchDraftInvoicesProductsDetails($id)
    {
        $order_invoice = Order::find($id);
        $total_products = $order_invoice->order_products->count('id');
        $sub_total = 0;
        $query = OrderProduct::where('order_id', $id)->get();
        foreach ($query as  $value) {
            $product = Product::where('id', $value->product_id)->first();
            $sub_total += $value->quantity * $product->selling_price;
        }
        return view('users.purchasing.completed-quotation-products-details', compact('id', 'order_invoice', 'total_products', 'sub_total'));
    }

    public function purchaseFetchProduct(Request $request)
    {
        // dd($request->all());
        $params = $request->except('_token');
        $detail = [];
        if ($request->get('query')) {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            // $category_query = ProductCategory::query();
            $supplier_query = Supplier::query();


            // foreach ($search_box_value as $result)
            // {
            // $product_query = $product_query->orWhere('short_desc', 'LIKE', '%'.$result.'%')->orWhere('refrence_code', 'LIKE', $result.'%');
            $product_query = $product_query->where(function ($q) use ($search_box_value) {
                foreach ($search_box_value as $value) {
                    $q->where('short_desc', 'LIKE', '%' . $value . '%');
                }
            })->orWhere('refrence_code', 'LIKE', $query . '%');

            // $category_query = $category_query->orWhere('title', 'LIKE', '%'.$result.'%');

            $supplier_query = $supplier_query->orWhere('reference_name', 'LIKE', '%' . $query . '%');
            // }

            $product_query  = $product_query->pluck('id')->toArray();
            // $category_query = $category_query->pluck('id')->toArray();
            $supplier_query = $supplier_query->pluck('id')->toArray();


            if (!empty($product_query) || !empty($supplier_query)) {
                $product_detail = Product::orderBy('id', 'ASC');

                $product_detail->orWhere(function ($q) use ($product_query, $supplier_query) {

                    if (!empty($product_query)) {
                        $q->orWhereIn('id', $product_query);
                    }
                    // if(! empty($category_query))
                    // {
                    //     $q->orWhereIn('primary_category', $category_query)->orWhereIn('category_id',$category_query);
                    // }
                    if (!empty($supplier_query)) {
                        $q->orWhereIn('supplier_id', $supplier_query);
                    }
                });

                $product_detail->where('status', 1);
                $detail = $product_detail->get();
            }

            if (!empty($detail)) {
                $i = 1;
                $output = '<ul class="dropdown-menu search-dropdown search_result_mobile" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                foreach ($detail as $row) {
                    // dd($row);
                    // if($request->inv_id == null){
                    $output .= '
                            <li>';
                    $output .= '<a tabindex="' . $i . '" target="_blank" href="' . url('get-product-detail/' . $row->id) . '" data-prod_id="' . $row->id . '" class="search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . @$row->refrence_code . ' ' . $row->short_desc . ' ' . @$row->def_or_last_supplier->reference_name . '</a></li>
                            ';
                    // }
                    // else{
                    // $output .= '
                    //     <li tabindex="'.$i.'">

                    //     <a target="_blank" href="javascript:void(0);" data-inv_id="'.$request->inv_id.'" data-prod_id="'.$row->id.'" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>'.@$row->refrence_code.' '.$row->short_desc.' '.@$row->def_or_last_supplier->reference_name.'</a></li>
                    //     ';
                    // }
                    $i++;
                }
                $output .= '</ul>';
                echo $output;
            } else {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        } else {
            echo '';
        }
    }

    public function purchaseFetchProductSpr(Request $request)
    {
        // dd($request->all());
        $params = $request->except('_token');
        $detail = [];
        if ($request->get('query')) {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            // $category_query = ProductCategory::query();
            $supplier_query = Supplier::query();


            // foreach ($search_box_value as $result)
            // {
            // $product_query = $product_query->orWhere('short_desc', 'LIKE', '%'.$result.'%')->orWhere('refrence_code', 'LIKE', $result.'%');
            $product_query = $product_query->where(function ($q) use ($search_box_value) {
                foreach ($search_box_value as $value) {
                    $q->where('short_desc', 'LIKE', '%' . $value . '%');
                }
            })->orWhere('refrence_code', 'LIKE', $query . '%');

            // $category_query = $category_query->orWhere('title', 'LIKE', '%'.$result.'%');

            $supplier_query = $supplier_query->orWhere('reference_name', 'LIKE', '%' . $query . '%');
            // }

            $product_query  = $product_query->pluck('id')->toArray();
            // $category_query = $category_query->pluck('id')->toArray();
            $supplier_query = $supplier_query->pluck('id')->toArray();


            if (!empty($product_query) || !empty($supplier_query)) {
                $product_detail = Product::orderBy('id', 'ASC');

                $product_detail->orWhere(function ($q) use ($product_query, $supplier_query) {

                    if (!empty($product_query)) {
                        $q->orWhereIn('id', $product_query);
                    }
                    // if(! empty($category_query))
                    // {
                    //     $q->orWhereIn('primary_category', $category_query)->orWhereIn('category_id',$category_query);
                    // }
                    if (!empty($supplier_query)) {
                        $q->orWhereIn('supplier_id', $supplier_query);
                    }
                });

                $product_detail->where('status', 1);
                $detail = $product_detail->get();
            }

            if (!empty($detail)) {
                $i = 1;
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:60px; left:5px; width:96%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                foreach ($detail as $row) {
                    // dd($row);
                    if ($request->inv_id == null) {
                        $output .= '
                            <li>';
                        $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" data-prod_id="' . $row->id . '" data-prod_ref_code="' . $row->refrence_code . '" class="search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . @$row->refrence_code . ' ' . $row->short_desc . ' ' . @$row->def_or_last_supplier->reference_name . '</a></li>
                            ';
                    } else {
                        $output .= '
                            <li tabindex="' . $i . '">

                            <a href="javascript:void(0);" data-inv_id="' . $request->inv_id . '" data-prod_id="' . $row->id . '" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . @$row->refrence_code . ' ' . $row->short_desc . ' ' . @$row->def_or_last_supplier->reference_name . '</a></li>
                            ';
                    }
                    $i++;
                }
                $output .= '</ul>';
                echo $output;
            } else {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        } else {
            echo '';
        }
    }

    public function getData(Request $request)
    {
        // dd('Error');
        $search_product = $request->search['value'];
        $query = Product::select('products.refrence_code', 'products.primary_category', 'products.short_desc', 'products.buying_unit', 'products.selling_unit', 'products.type_id', 'products.brand', 'products.product_temprature_c', 'products.supplier_id', 'products.id', 'products.total_buy_unit_cost_price', 'products.weight', 'products.unit_conversion_rate', 'products.selling_price', 'products.vat', 'products.import_tax_book', 'products.hs_code', 'products.hs_description', 'products.name', 'products.category_id', 'products.product_notes', 'products.status', 'products.min_stock', 'products.last_price_updated_date', 'products.ecommerce_enabled', 'products.created_at', 'products.type_id_2', 'products.type_id_3', 'products.min_o_qty', 'products.max_o_qty', 'products.length', 'products.width', 'products.height', 'products.long_desc', 'products.ecommerce_price', 'products.discount_price', 'products.discount_expiry_date', 'products.ecom_selling_unit', 'products.selling_unit_conversion_rate', 'products.ecom_product_weight_per_unit', 'products.product_note_3');
        $query =  $query->with('def_or_last_supplier:id,reference_name,country', 'units:id,title,decimal_places', 'productType:id,title', 'productType2:id,title', 'productSubCategory:id,title', 'supplier_products:id,supplier_id,product_id,product_supplier_reference_no,supplier_description,freight,landing,buying_price,buying_price_in_thb,leading_time,extra_tax', 'supplier_products.supplier:id,currency_id', 'supplier_products.supplier.getCurrency:currency_symbol', 'productCategory:id,title', 'product_fixed_price:id,product_id,customer_type_id,fixed_price', 'customer_type_product_margins:id,product_id,customer_type_id,default_value', 'getPoData:id,product_id,quantity', 'sellingUnits:id,title,decimal_places', 'prouctImages:id,product_id', 'warehouse_products:id,product_id,current_quantity,warehouse_id,available_quantity,reserved_quantity,ecommerce_reserved_quantity', 'check_import_or_not:id,product_id', 'ecomSellingUnits', 'def_or_last_supplier.getcountry:id,name')->where('products.status', 1);
        if (($request->from_date != '' || $request->from_date != null) && ($request->to_date != '' || $request->to_date != null)) {
            $dateS = date("Y-m-d", strtotime(strtr($request->from_date, '/', '-')));
            $dateE = date("Y-m-d", strtotime(strtr($request->to_date, '/', '-')));


            $query = $query->whereBetween('products.created_at', [$dateS . " 00:00:00", $dateE . " 23:59:59"]);
        }
        if ($request->default_supplier != '') {
            $supplier_query = $request->default_supplier;
            $query = $query->whereIn('products.id', SupplierProducts::select('product_id')->where('is_deleted', 0)->where('supplier_id', $supplier_query)->pluck('product_id'));
        }

        if ($request->prod_type != '') {
            $query->where('products.type_id', $request->prod_type)->where('products.status', 1);
        }
        if ($request->prod_type_2 != '') {
            $query->where('products.type_id_2', $request->prod_type_2)->where('products.status', 1);
        }

        if ($request->prod_type_3 != '') {
            $query->where('products.type_id_3', $request->prod_type_3)->where('products.status', 1);
        }

        if ($request->prod_category_primary != '') {
            $id_split = explode('-', $request->prod_category_primary);
            if ($id_split[0] == 'pri') {
                $query->where('products.primary_category', $id_split[1])->where('products.status', 1);
            } else {
                $query->whereIn('products.category_id', ProductCategory::select('id')->where('id', $id_split[1])->where('parent_id', '!=', 0)->pluck('id'))->where('products.status', 1);
            }
        }

        if ($request->filter != '') {
            if ($request->filter == 'stock') {
                $query = $query->whereIn('products.id', WarehouseProduct::select('product_id')->where('current_quantity', '>', 0.005)->pluck('product_id'));
            } elseif ($request->filter == 'reorder') {
                $query->where('products.min_stock', '>', 0);
            }
        }
        if ($request->supplier_country != null) {
            $query = $query->whereHas('def_or_last_supplier', function ($q) use ($request) {
                $q->whereHas('getcountry', function ($z) use ($request) {
                    $z->where('id', $request->supplier_country);
                });
            });
        }

        $ecommerceconfig = QuotationConfig::where('section', 'ecommerce_configuration')->first();
        if ($ecommerceconfig) {
            $check_status = unserialize($ecommerceconfig->print_prefrences);
            $ecommerceconfig_status = $check_status['status'][0];
        } else {
            $ecommerceconfig_status = '';
        }

        if ($ecommerceconfig_status == 1) {
            if ($request->ecomFilter == "ecom-enabled") {
                $query->where('products.ecommerce_enabled', 1);
            }
        }

        if ($request->ecomFilter == "ecom-disable") {
            $query->where('products.ecommerce_enabled', 0);
        }

        $getWarehouses = Warehouse::where('status', 1)->get();

        $getCategories = CustomerCategory::where('is_deleted', 0)->where('show', 1)->get();
        $getCategoriesSuggested = CustomerCategory::where('suggested_price_show', 1)->where('is_deleted', 0)->get();

        if ($search_product != null) {

            $filteredRecords = Product::where('refrence_code', 'LIKE', '%' . $search_product . '%')
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

            $products_ids_prods = $filteredRecords;
        } else {
            $products_ids_prods = $query;
        }

        $total_system_units = Unit::whereNotNull('id')->count();

        if ($total_system_units == 1) {
            $products_ids = $products_ids_prods->pluck('products.id')->toArray();
            $total_unit = WarehouseProduct::whereIn('product_id', $products_ids)->get()->sum('current_quantity');

            $all_stock_array = array();
            foreach ($getWarehouses as $ware) {
                $total_current = WarehouseProduct::whereIn('product_id', $products_ids)->where('warehouse_id', $ware->id)->get()->sum('current_quantity');
                $total_available = WarehouseProduct::whereIn('product_id', $products_ids)->where('warehouse_id', $ware->id)->get()->sum('available_quantity');
                $total_reserved = WarehouseProduct::whereIn('product_id', $products_ids)->where('warehouse_id', $ware->id)->get()->sum('reserved_quantity');

                array_push($all_stock_array, $total_current);
                array_push($all_stock_array, $total_available);
                array_push($all_stock_array, $total_reserved);
            }
        } else {
            $total_unit = 0;
            $all_stock_array = array();
            foreach ($getWarehouses as $ware) {
                array_push($all_stock_array, 0);
                array_push($all_stock_array, 0);
                array_push($all_stock_array, 0);
            }
        }
        $not_visible_arr = [];
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'completed_products')->first();
        if ($table_hide_columns != null) {
            $not_visible_arr = explode(',', $table_hide_columns->hide_columns);
        }


        $on_water = (new PurchaseOrderDetail)->getOnWater($query->pluck('id')->toArray());
        $on_supplier = PurchaseOrderDetail::getOnSupplier($query->pluck('id')->toArray());

        $query = Product::ProductListingSorting($request, $query, $getWarehouses, $getCategories, $getCategoriesSuggested, $not_visible_arr);

        $dt = Datatables::of($query);
        $add_columns = ['checkbox', 'action', 'category_id', 'short_desc', 'buying_unit', 'selling_unit', 'import_tax_book', 'vat', 'on_water', 'image', 'supplier_id', 'p_s_reference_number', 'supplier_description', 'supplier_country', 'freight', 'landing', 'vendor_price', 'vendor_price_in_thb', 'total_buy_unit_cost_price', 'total_visible_stock', 'last_price_history', 'unit_conversion_rate', 'selling_unit_cost_price', 'current', 'available', 'reserve', 'title', 'product_type_2', 'product_type_3', 'lead_time', 'min_order_qty', 'max_order_qty', 'dimension', 'discount_expiry', 'product_type', 'ecom_selling_conversion_rate', 'ecom_cogs_price', 'on_water', 'long_desc', 'brand', 'ecom_product_weight_per_unit', 'selling_price', 'on_supplier', 'ecom_status', 'ecom_selling_unit', 'extra_tax'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $not_visible_arr, $getWarehouses) {
                return Product::returnAddColumn($column, $item, $not_visible_arr, $getWarehouses);
            });
        }

        $edit_columns = ['refrence_code', 'hs_code', 'hs_description', 'short_desc', 'product_notes', 'product_note_3'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column, $not_visible_arr) {
                return Product::returnEditColumn($column, $item, $not_visible_arr);
            });
        }

        $filter_column = ['p_s_reference_number', 'supplier_description', 'refrence_code', 'buying_unit', 'selling_unit' . 'category_id', 'product_notes', 'product_type', 'product_type_2', 'hs_description' . 'brand', 'product_temprature_c', 'import_tax_book', 'vat', 'supplier_id', 'vendor_price', 'vendor_price_in_thb', 'freight', 'landing', 'total_buy_unit_cost_price', 'unit_conversion_rate', 'selling_unit_cost_price', 'weight', 'lead_time', 'name', 'min_order_qty', 'max_order_qty', 'ecom_product_weight_per_unit', 'long_desc', 'selling_price', 'discount_price', 'ecom_selling_unit', 'ecom_selling_conversion_rate', 'short_desc'];

        foreach ($filter_column as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return Product::returnFilterColumn($column, $item, $keyword);
            });
        }

        $current_qty = null;
        $pqty = null;
        $warehouse_count = 49;
        $find_warehouse_prod = null;
        if ($getWarehouses->count() > 0) {
            foreach ($getWarehouses as $warehouse) {

                if (!in_array($warehouse_count++, $not_visible_arr)) {
                    $dt->addColumn($warehouse->warehouse_title . 'current', function ($item) use ($warehouse, $current_qty, $find_warehouse_prod) {
                        $warehouse_product = $item->warehouse_products->where('warehouse_id', $warehouse->id)->first();
                        // $find_warehouse_prod = $warehouse_product;
                        $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity : 0;
                        $this->curr_quantity = $qty;
                        // $this->find_warehouse_prod = $warehouse_product;
                        $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                        return number_format($qty, $decimal_places, '.', ',') . ' ' . $item->sellingUnits->title;
                    });
                } else {
                    $dt->addColumn($warehouse->warehouse_title . 'current', function ($item) {
                        return '--';
                    });
                }

                if (!in_array($warehouse_count++, $not_visible_arr)) {
                    $dt->addColumn($warehouse->warehouse_title . 'available', function ($item) use ($warehouse) {
                        // dd($this->find_warehouse_prod);
                        $warehouse_product = $item->warehouse_products->where('warehouse_id', $warehouse->id)->first();
                        // $warehouse_product = $this->find_warehouse_prod != null ? $this->find_warehouse_prod : $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                        $available_qty = ($warehouse_product->available_quantity != null) ? $warehouse_product->available_quantity : 0;
                        $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                        return number_format($available_qty, $decimal_places, '.', ',');
                    });
                } else {
                    $dt->addColumn($warehouse->warehouse_title . 'available', function ($item) {
                        return '--';
                    });
                }

                if (!in_array($warehouse_count++, $not_visible_arr)) {
                    $dt->addColumn($warehouse->warehouse_title . 'reserve', function ($item) use ($warehouse) {
                        // $warehouse_product = $this->find_warehouse_prod != null ? $this->find_warehouse_prod : $item->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                        $warehouse_product = $item->warehouse_products->where('warehouse_id', $warehouse->id)->first();
                        $qty = ($warehouse_product->reserved_quantity != null || $warehouse_product->ecommerce_reserved_quantity != null) ? ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity) : 0;

                        $this->rsv_quantity = $qty;
                        $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                        $order_products = '
                <a href="' . route('sold-products-report', ['warehouse_id' => $warehouse->id, 'product_id' => $item->id, 'filter' => 'no', 'from_complete_list' => 'yes']) . '" target="_blank" title="View Report"><b>' . number_format($qty, $decimal_places, '.', ',') . '</b></a>';
                        return $order_products;
                    });
                } else {
                    $dt->addColumn($warehouse->warehouse_title . 'reserve', function ($item) {
                        return '--';
                    });
                }
            }
        }

        //Customer Category Dynamic Columns Starts Here
        if ($getCategories->count() > 0) {
            foreach ($getCategories as $cat) {

                if (!in_array($warehouse_count++, $not_visible_arr)) {
                    $dt->addColumn($cat->title, function ($item) use ($cat) {
                        // return 0;
                        $fixed_value = $item->product_fixed_price->where('customer_type_id', $cat->id)->first();
                        $value = $fixed_value != null ? $fixed_value->fixed_price : '0.00';

                        $formated_value = number_format($value, 3, '.', ',');

                        $html_string = '
              <span class="m-l-15 inputDoubleClick" style="font-style: italic;" id="product_fixed_price_' . $cat->id . '"  data-fieldvalue="' . @$formated_value . '">';
                        $html_string .= (@$formated_value !== null) ? $formated_value : '--';
                        $html_string .= '</span>';

                        $html_string .= '<input type="number" style="width:100%;" name="product_fixed_price" class="fieldFocus d-none" data-id="' . $fixed_value->id . '" value="' . $formated_value . '">';
                        return $html_string;
                    });
                } else {
                    $dt->addColumn($cat->title, function ($item) {
                        return '--';
                    });
                }
            }
        }

        //Customer Category Dynamic Columns Starts Here
        if ($getCategoriesSuggested->count() > 0) {
            foreach ($getCategoriesSuggested as $cat) {

                if (!in_array($warehouse_count++, $not_visible_arr)) {
                    $dt->addColumn('suggest_' . $cat->title, function ($item) use ($cat) {
                        // dd($item);
                        // return 0;
                        $selling_price = $item->selling_price;
                        $suggest_price = $item->customer_type_product_margins->where('product_id', $item->id)->where('customer_type_id', $cat->id)->first();
                        $default_value = $suggest_price->default_value;

                        $final_value = $selling_price + ($selling_price * ($default_value / 100));
                        if ($item->check_import_or_not == null) {
                            $redHighlighted = 'style=color:red';
                            $tooltip = 'title="This item has never been imported before in our system, so the suggested price may be incorrect"';
                        } else {
                            $redHighlighted = '';
                            $tooltip = '';
                        }
                        return "<span " . $redHighlighted . ' ' . $tooltip . ">" . ($final_value != null ? number_format($final_value, 3, '.', ',') : 0) . "</span>";
                    });
                } else {
                    $dt->addColumn('suggest_' . $cat->title, function ($item) {
                        return '--';
                    });
                }
            }
        }

        $dt->setRowId(function ($item) {
            return @$item->id;
        });

        $dt->escapeColumns([]);
        $dt->rawColumns(['checkbox', 'action', 'name', 'category_id', 'supplier_id', 'image', 'import_tax_book', 'import_tax_actual', 'freight', 'landing', 'total_buy_unit_cost_price', 'unit_conversion_rate', 'selling_unit_cost_price', 'product_type', 'brand', 'product_temprature_c', 'weight', 'lead_time', 'last_price_history', 'refrence_code', 'vat', 'hs_code', 'hs_description', 'short_desc', 'buying_unit', 'selling_unit', 'supplier_description', 'vendor_price_in_thb', 'vendor_price', 'product_notes', 'total_visible_stock', 'product_type_2', 'title', 'min_o_qty', 'max_o_qty', 'dimension', 'long_desc', 'selling_price', 'discount_price', 'discount_expiry', 'ecom_selling_unit', 'ecom_selling_conversion_rate', 'ecom_cogs_price', 'supplier_country', 'product_note_3']);
        $dt->with(['total_unit' => number_format(floatval($total_unit), 2, '.', ','), 'all_stock_array' => $all_stock_array, 'on_water' => number_format($on_water, 2), 'on_supplier' => number_format($on_supplier, 2)]);
        return $dt->make(true);
    }
    public function exportCompleteProductsStatus(Request $request)
    {
        $data = $request->all();

        $status = ExportStatus::where('type', 'complete_products')->where('user_id', Auth::user()->id)->first();

        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type = 'complete_products';
            $new->status = 1;
            $new->save();
            if ($request['product_export_button'] == 'erp_export' || $request['product_export_button'] == 'pos_product_export') {
                CompleteProductsExportJob::dispatch($data, Auth::user()->id);
            } elseif ($request['product_export_button'] == 'pos_note_export') {
                CompleteProductsPosNoteJob::dispatch($data, Auth::user()->id);
            }
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'complete_products')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            if ($request['product_export_button'] == 'erp_export' || $request['product_export_button'] == 'pos_product_export') {
                CompleteProductsExportJob::dispatch($data, Auth::user()->id);
            } elseif ($request['product_export_button'] == 'pos_note_export') {
                CompleteProductsPosNoteJob::dispatch($data, Auth::user()->id);
            }
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheck()
    {
        $status = ExportStatus::where('type', 'complete_products')->where('user_id', Auth::user()->id)->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception]);
    }

    public function checkStatusFirstTime()
    {
        $status = ExportStatus::where('type', 'complete_products')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function getdeactivatedData(Request $request)
    {
        $query = Product::query();
        $query->with('def_or_last_supplier', 'units', 'prouctImages', 'productType', 'productType2', 'productBrand', 'productSubCategory')->where('status', 2);

        if ($request->default_supplier != '') {
            $supplier_query = $request->default_supplier;
            $query = $query->whereIn('id', SupplierProducts::select('product_id')->where('supplier_id', $supplier_query)->pluck('product_id'));
        }
        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {

                $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="' . $item->id . '" id="product_check_' . $item->id . '">
                                <label class="custom-control-label" for="product_check_' . $item->id . '"></label>
                              </div>';
                return $html_string;
            })
            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="' . url('get-product-detail/' . $item->id) . '" class="actionicon editIcon text-center" title="View Detail"><i class="fa fa-eye"></i></a>
                 ';
                // <a href="javascript:void(0);" class="actionicon deleteIcon deleteProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>

                return $html_string;
            })
            ->editColumn('refrence_code', function ($item) {
                $refrence_code = $item->refrence_code != null ? $item->refrence_code : "--";
                //return $refrence_code;
                $html_string = '
                 <a href="' . url('get-product-detail/' . $item->id) . '" title="View Detail"><b>' . $refrence_code . '</b></a>
                 ';
                // <a href="javascript:void(0);" class="actionicon deleteIcon deleteProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>

                return $html_string;
            })
            ->editColumn('primary_category', function ($item) {
                return @$item->productCategory->title;
            })
            ->editColumn('hs_code', function ($item) {
                $hs_code = $item->hs_code != null ? $item->hs_code : "--";
                return $hs_code;
            })
            ->addColumn('category_id', function ($item) {

                return @$item->productSubCategory->title;
            })
            ->filterColumn('category_id', function ($query, $keyword) {
                $query = $query->whereIn('category_id', ProductCategory::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
            }, true)
            ->addColumn('buying_unit', function ($item) {
                return @$item->units->title;
            })
            ->addColumn('selling_unit', function ($item) {
                return @$item->sellingUnits->title;
            })
            ->addColumn('import_tax_book', function ($item) {
                $import_tax_book = $item->import_tax_book != null ? $item->import_tax_book . ' %' : "--";
                return $import_tax_book;
            })
            ->addColumn('vat', function ($item) {
                $vat = $item->vat !== null ? $item->vat . ' %' : "--";
                return $vat;
            })
            ->addColumn('supplier_id', function ($item) {
                return (@$item->supplier_id != null) ? @$item->def_or_last_supplier->reference_name : '-';
            })
            ->addColumn('freight', function ($item) {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();

                return ($getProductDefaultSupplier != null) ? $getProductDefaultSupplier->freight : '--';
            })
            ->addColumn('landing', function ($item) {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();

                return (@$getProductDefaultSupplier != null) ? @$getProductDefaultSupplier->landing : '--';
            })
            ->addColumn('vendor_price', function ($item) {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                return (@$getProductDefaultSupplier != null) ? number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '') : '--';
            })
            ->addColumn('total_buy_unit_cost_price', function ($item) {

                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                if ($getProductDefaultSupplier !== null) {
                    $importTax = $getProductDefaultSupplier->import_tax_actual ? $getProductDefaultSupplier->import_tax_actual : $item->import_tax_book;

                    $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($getProductDefaultSupplier->buying_price);
                    $newTotalBuyingPrice = (($importTax) / 100) * $total_buying_price;
                    $total_buying_price = $total_buying_price + $newTotalBuyingPrice;

                    return (@$total_buying_price != null) ? number_format((float)@$total_buying_price, 3, '.', '') : '--';
                }
            })
            ->addColumn('unit_conversion_rate', function ($item) {
                return (@$item->unit_conversion_rate != null) ? number_format((float)@$item->unit_conversion_rate, 3, '.', '') : '-';
            })
            ->addColumn('selling_unit_cost_price', function ($item) {
                return (@$item->selling_price != null) ? number_format((float)@$item->selling_price, 3, '.', '') : '-';
            })
            ->addColumn('bangkok_current_qty', function ($item) {
                $warehouse_product = WarehouseProduct::where('product_id', $item->id)->where('warehouse_id', 1)->first();
                $qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : '0 ';
                return $qty . $item->sellingUnits->title;
            })
            ->addColumn('bangkok_reserved_qty', function ($item) {
                $warehouse_product = WarehouseProduct::where('product_id', $item->id)->where('warehouse_id', 1)->first();
                return (@$warehouse_product->reserved_quantity != null) ? @$warehouse_product->reserved_quantity : '0';
            })
            ->addColumn('phuket_current_qty', function ($item) {
                $warehouse_product = WarehouseProduct::where('product_id', $item->id)->where('warehouse_id', 2)->first();
                $qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : '0 ';
                return $qty . $item->sellingUnits->title;
            })
            ->addColumn('phuket_reserved_qty', function ($item) {
                $warehouse_product = WarehouseProduct::where('product_id', $item->id)->where('warehouse_id', 2)->first();
                return (@$warehouse_product->reserved_quantity != null) ? @$warehouse_product->reserved_quantity : '0';
            })
            ->addColumn('weight', function ($item) {
                return (@$item->weight != null) ? @$item->weight : '-';
            })
            ->addColumn('lead_time', function ($item) {
                $getProductLastSupplierName = SupplierProducts::where('product_id', @$item->id)->where('supplier_id', @$item->supplier_id)->first();
                return (@$getProductLastSupplierName->leading_time != null) ? @$getProductLastSupplierName->leading_time : '-';
            })
            ->addColumn('product_type', function ($item) {
                return (@$item->type_id != null) ? @$item->productType->title : '--';
            })
            ->addColumn('product_type_2', function ($item) {
                return (@$item->type_id_2 != null) ? @$item->productType2->title : '--';
            })
            ->addColumn('product_brand', function ($item) {
                return (@$item->brand != null) ? @$item->brand : '--';
            })
            ->addColumn('product_temprature_c', function ($item) {
                return (@$item->product_temprature_c != null) ? @$item->product_temprature_c : '--';
            })
            // ->addColumn('restaruant_price',function($item){
            //     $getRecord = new Product;
            //     $prodFixPrice   = $getRecord->getDataOfProductMargins($item->id, 1, "prodFixPrice");
            //     $formated_value = number_format($prodFixPrice->fixed_price,3,'.',',');
            //     return (@$formated_value !== null) ? $formated_value : '--';

            // })
            ->setRowId(function ($item) {
                return @$item->id;
            })
            ->rawColumns(['checkbox', 'action', 'name', 'primary_category', 'category_id', 'supplier_id', 'import_tax_book', 'import_tax_actual', 'freight', 'landing', 'total_buy_unit_cost_price', 'unit_conversion_rate', 'selling_unit_cost_price', 'product_type', 'product_brand', 'product_temprature_c', 'bangkok_current_qty', 'bangkok_reserved_qty', 'phuket_current_qty', 'phuket_reserved_qty', 'weight', 'lead_time', 'refrence_code', 'vat', 'hs_code', 'short_desc', 'buying_unit', 'selling_unit', 'refrence_code', 'product_type_2'])
            ->make(true);
    }

    public function indexForInquiry()
    {
        $categories = ProductCategory::orderBy('title', 'ASC')->where('parent_id', 0)->get();
        return view('users.products.inquiry', compact('categories'));
    }

    public function getDataForInquiry()
    {
        $query = OrderProduct::query();
        $query = $query->where('order_products.is_billed', 'Inquiry')->with('productSubCategory');

        $dt = Datatables::of($query);
        $add_columns = ['quotation_no', 'added_by', 'category_id', 'default_price', 'qty', 'supplier', 'pieces', 'reference_no', 'checkbox'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return OrderProduct::returnAddColumnInquiryProduct($column, $item);
            });
        }

        $filter_columns = ['quotation_no', 'added_by'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return OrderProduct::returnFilterColumnInquiryProduct($column, $item, $keyword);
            });
        }

        $edit_columns = ['short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return OrderProduct::returnEditColumnInquiryProduct($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->rawColumns(['checkbox', 'added_by', 'quotation_no', 'short_desc', 'default_price', 'qty', 'pieces', 'reference_no', 'category_id', 'supplier']);
        return $dt->make(true);
    }

    public function saveInquiryProductData(Request $request)
    {
        $order_products = OrderProduct::find($request->id);
        foreach ($request->except('id') as $key => $value) {
            if ($key == 'category_id') {
                $order_products->$key = $value;
            }
        }
        $order_products->save();
        return response()->json(['success' => true]);
    }

    public function MoveToInventory(Request $request)
    {
        if ($request->selected_products != null) {
            $rowsCount = 0;
            $id = '';

            foreach ($request->selected_products as $sproduct) {
                $orderProduct = OrderProduct::find($sproduct);
                if ($orderProduct->category_id == NULL) {
                    $errormsg = "Please assign categories first of selected item(s)";
                    return response()->json(['success' => false, 'errormsg' => $errormsg]);
                }
            }

            foreach ($request->selected_products as $sproduct) {
                $rowsCount++;
                $orderProduct = OrderProduct::find($sproduct);

                // Adding to Product Table
                $product   = new Product;
                $getSubCat = ProductCategory::where('id', $orderProduct->category_id)->first();

                $prefix = $getSubCat->prefix;

                $c_p_ref = Product::where('category_id', $orderProduct->category_id)->orderBy('refrence_no', 'DESC')->first();
                // dd($c_p_ref);
                if ($c_p_ref == NULL) {
                    $str = '0';
                } else {
                    $str = $c_p_ref->refrence_no;
                }

                $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);

                $product->refrence_code     = $prefix . $system_gen_no;
                $product->system_code       = $prefix . $system_gen_no;
                $product->refrence_no       = $system_gen_no;

                $product->short_desc        = $orderProduct->short_desc;
                $product->primary_category  = $getSubCat->parent_id;
                $product->category_id       = $orderProduct->category_id;
                $product->supplier_id       = @$orderProduct->default_supplier;
                $product->hs_code           = $getSubCat->hs_code;
                $product->import_tax_book   = $getSubCat->import_tax_book;
                $product->vat               = $getSubCat->vat;
                $product->status            = 0;
                $product->created_by        = Auth::user()->id;
                $product->suggested_by      = $orderProduct->created_by;
                $product->order_product_id  = $sproduct;
                $product->save();

                $recentAdded = Product::find($product->id);

                if ($recentAdded->supplier_id != 0) {
                    // create new entry in supplier product
                    $new_sup_product = new SupplierProducts;
                    $new_sup_product->supplier_id = $recentAdded->supplier_id;
                    $new_sup_product->product_id  = $recentAdded->id;
                    $new_sup_product->save();
                }

                $categoryMargins = CustomerTypeCategoryMargin::where('category_id', $recentAdded->category_id)->orderBy('id', 'ASC')->get();
                if ($categoryMargins->count() > 0) {
                    foreach ($categoryMargins as $value) {
                        $productMargin = new CustomerTypeProductMargin;
                        $productMargin->product_id       = $recentAdded->id;
                        $productMargin->customer_type_id = $value->customer_type_id;
                        $productMargin->default_margin   = $value->default_margin;
                        $productMargin->default_value    = $value->default_value;
                        $productMargin->save();
                    }
                }

                $customerCats = CustomerCategory::where('is_deleted', 0)->orderBy('id', 'ASC')->get();
                if ($customerCats->count() > 0) {
                    foreach ($customerCats as $c_cat) {
                        $productFixedPrices = new ProductFixedPrice;
                        $productFixedPrices->product_id       = $recentAdded->id;
                        $productFixedPrices->customer_type_id = $c_cat->id;
                        $productFixedPrices->fixed_price      = 0;
                        $productFixedPrices->expiration_date  = NULL;
                        $productFixedPrices->save();
                    }
                }

                // warehouse products adding
                $warehouse = Warehouse::get();
                foreach ($warehouse as $w) {
                    $warehouse_product = new WarehouseProduct;
                    $warehouse_product->warehouse_id = $w->id;
                    $warehouse_product->product_id = $recentAdded->id;
                    $warehouse_product->save();
                }

                // Order Product table update
                $orderProduct->product_id = $product->id;
                $orderProduct->vat        = $product->vat;
                $orderProduct->is_billed  = "Incomplete";
                $orderProduct->from_warehouse_id  = null;
                $orderProduct->user_warehouse_id  = @$orderProduct->get_order->from_warehouse_id;
                $orderProduct->is_warehouse  = 0;
                $orderProduct->status     = 6;
                $orderProduct->save();
            }

            if ($rowsCount == 1) {
                $id = $product->id;
            }

            return response()->json(['success' => true, 'successmsg' => 'Products Moved to Inventory Successfully.', 'rowsCount' => $rowsCount, 'id' => $id]);
        }
    }

    public function deleteInquiryProducts(Request $request)
    {
        if ($request->selected_products != null) {
            foreach ($request->selected_products as $product) {
                $order_product = OrderProduct::find($product);
                $order_product->is_billed = "Billed";
                $order_product->save();
            }
            return response()->json(['success' => true]);
        }
    }

    public function addProductImages(Request $request)
    {
        $product_detail = Product::find($request->product_id);
        if (empty($base = $request->product_image))
            die("missing string base64");

        // UPOLOAD MULTIPE IMAGES
        $base = $request->get('product_image');
        if (sizeof($base) > 0) {
            foreach ($base as $index => $base64) {
                $number_of_product_images = ProductImage::where('product_id', $request->product_id)->count();
                if ($number_of_product_images < 4) {
                    if (!empty($base64)) {
                        $image = $this->upload($base64, $request->product_id);
                        $resize_image = Image::make($base64);
                        // $destinationPath = public_path('uploads\products\thumbnails');
                        $destinationPath     = public_path() . '/uploads/products/thumbnails/product_' . $request->product_id . '/';
                        $resize_image->resize(150, 150, function ($constraint) {
                            $constraint->aspectRatio();
                        })->save($destinationPath . '/' . $image);

                        if (!empty($image) && !file_exists($finalFile = sprintf('%1$s/uploads/products/product_' . $request->product_id . '/%2$s', public_path(), $image)))
                            die("Upload error {$finalFile} on index : {$index}");
                        if ($image != '0') {
                            $product_detail->prouctImages()->updateOrCreate(['image' => $image]);
                        }
                    } // endif
                    else {
                        return json_encode(['success' => true]);
                    }
                } else {
                    return json_encode(['error' => true, 'errormsg' => 'You can upload a maximum of 4 images. Please remove some images before adding new.']);
                }
            } // end for loop.

        } //Endif

        return json_encode(['success' => true]);
    }

    private function upload($base64_string, $prodId)
    {
        $data          = explode(';', $base64_string);
        $dataa         = explode(',', $base64_string);
        $part          = explode("/", $data[0]);
        $directory     = public_path() . '/uploads/products/product_' . $prodId . '/';
        $directoryThumbnail     = public_path() . '/uploads/products/thumbnails/product_' . $prodId . '/';

        if (empty($part) or @$part[1] == null or empty(@$part[1])) {
            return false;
        } else {
            $file = md5(uniqid(rand(), true)) . ".{$part[1]}";
            if (!is_dir($directory)) {
                mkdir($directory, 0777, true);
            }
            if (!is_dir($directoryThumbnail)) {
                mkdir($directoryThumbnail, 0777, true);
            }

            $ifp = fopen($directory . "/{$file}", 'wb');
            fwrite($ifp, base64_decode($dataa[1]));
            fclose($ifp);


            return $file;
        }
    }

    public function getProductCategoryChilds(Request $request)
    {
        $subCategories = ProductCategory::where('parent_id', $request->p_cat_id)->get();
        $html_string = '';
        if ($subCategories) {
            $html_string .= '<option>Sub-Categories</option>';
            foreach ($subCategories as $value) {
                $html_string .= '<option value="' . $value->id . '">' . $value->title . '</option>';
            }
        }

        return response()->json([
            'html_string' => $html_string
        ]);
    }

    public function getProdImages(Request $request)
    {
        $prod_images = ProductImage::where('product_id', $request->prod_id)->get();
        $html_string = '';
        if ($prod_images->count() > 0) :
            foreach ($prod_images as $pimage) :
                if (Auth::user()->role_id == 5 || Auth::user()->role_id == 6) {
                    $del_sign = '';
                } else {
                    $del_sign = '&times;';
                }
                $html_string .= '<div class="col-6 col-sm-3 gemstoneImg mb-3" id="prod-image-' . $pimage->id . '">
            <figure>
            <a data-img="' . $pimage->id . '" data-prodId="' . $request->prod_id . '" aria-expanded="true" class="close delete-img-btn delete-btn-two" title="Delete">' . $del_sign . '</a>
            <a href="' . url('public/uploads/products/product_' . $request->prod_id . '/' . $pimage->image) . '" target="_blank">
                <img class="stone-img img-thumbnail" style="width: 150px;
                height: 150px;" src="' . url('public/uploads/products/product_' . $request->prod_id . '/' . $pimage->image) . '">
            </a>
            </figure>
            <input type="radio" name="is_enable" class="is_enable" data-id="' . $pimage->id . '" data-product="' . $request->prod_id . '" ' . ($pimage->is_enabled == 1 ? 'checked' : '') . ' /> Ecom Image
          </div>';
            endforeach;
        else :
            $html_string .= '<div class="col-12 mb-3 text-center">No Record Found</div>';
        endif;

        return $html_string;
    }

    public function removeProdImage(Request $request)
    {
        if (isset($request->id)) {
            // remove images from directory //
            $image = ProductImage::find($request->id);
            $product_id = $image->product_id;
            $directory     = public_path() . '/uploads/products/product_' . $request->prodid . '/';
            //remove main
            $this->removeFile($directory, $image->image);
            // delete record
            $image->delete();

            $productImagesCount = ProductImage::select('id')->where('product_id', $product_id)->count();

            $check_default = ProductImage::where('product_id', $product_id)->where('is_enabled', 1)->first();
            if ($check_default == null) {
                $image = ProductImage::where('product_id', $product_id)->orderBy('id', 'asc')->first();

                if ($image != null) {
                    $image->is_enabled = 1;
                    $image->save();
                }
            }

            return "done" . "-SEPARATOR-" . $request->id . "-SEPARATOR-" . @$productImagesCount;
        }
    }

    public function delProdImgFromDetail(Request $request)
    {
        if (isset($request->img_id)) {
            // remove images from directory //
            $image = ProductImage::find($request->img_id);
            $directory = public_path() . '/uploads/products/product_' . $request->prod_id . '/';
            //remove main
            $this->removeFile($directory, $image->image);
            // delete record
            $image->delete();

            $imageCount = ProductImage::find($request->img_id);
            if ($imageCount == 0) {
                return "no_img";
            } else {
                return "done" . "-SEPARATOR-" . $request->img_id;
            }
        }
    }

    private function removeFile($directory, $imagename)
    {
        if (isset($directory) && isset($imagename))
            File::delete($directory . $imagename);
        return true;
        return false;
    }

    public function getSupplierById($id)
    {
        $suppliers = Supplier::select('first_name', 'last_name', 'id')->where('category_id', $id)->get();
        $html1 = '';
        $html2 = '';

        $html1 = $html1 . '<option value="" selected disabled>Choose Supplier</option>';
        foreach ($suppliers as $supplier) {
            $html1 = $html1 . '<option value="' . $supplier->id . '">' . $supplier->first_name . ' ' . $supplier->last_name . '</option>';
        }
        $html1 = $html1 . '<option value="new">Add New</option>';

        // new html2 is for default supplier set
        $html2 = $html2 . '<option value="" selected disabled>Choose Default Supplier</option>';
        foreach ($suppliers as $supplier) {
            $html2 = $html2 . '<option value="' . $supplier->id . '">' . $supplier->first_name . ' ' . $supplier->last_name . '</option>';
        }
        $html2 = $html2 . '<option value="new_supplier">Add New Supplier</option>';

        return response()->json([
            'html1' => $html1,
            'html2' => $html2,
        ]);
    }

    public function addProduct()
    {
        $products = ProductType::all();
        $supplier = Supplier::where('status', 1)->get();
        $countries = Country::orderby('name', 'ASC')->pluck('name', 'id');
        $categories = ProductCategory::where('parent_id', 0)->get();
        $units = Unit::all();
        return $this->render('users.products.addProduct', compact('products', 'supplier', 'countries', 'categories', 'units'));
    }

    public function addUnit(Request $request)
    {
        $validator = $request->validate([
            'title' => 'required',
        ]);

        $unit = new Unit;
        $unit->title = $request->title;
        $unit->save();

        $newunit = Unit::where('id', $unit->id)->first();
        return response()->json(['success' => true, 'unit' => $newunit]);
    }

    public function addProdType(Request $request)
    {
        $validator = $request->validate([
            'title' => 'required',
        ]);

        $prdouct_type = new ProductType;
        $prdouct_type->title = $request->title;
        $prdouct_type->save();

        $newprdouct_type = ProductType::where('id', $prdouct_type->id)->first();
        return response()->json(['success' => true, 'prdouct_type' => $newprdouct_type]);
    }

    public function addProdCat(Request $request)
    {
        $validator = $request->validate([
            'title' => 'required',
        ]);

        $prdouct_cat = new ProductCategory;
        $prdouct_cat->title = $request->title;
        $prdouct_cat->save();

        $newprdouct_cat = ProductCategory::where('id', $prdouct_cat->id)->first();
        return response()->json(['success' => true, 'prdouct_cat' => $newprdouct_cat]);
    }

    public function add(Request $request)
    {
        // basic detail fields start here
        $product = new Product;
        $getSubCat = ProductCategory::where('id', $request->selected_category_id)->first();

        $prefix = $getSubCat->prefix;

        $c_p_ref = Product::where('category_id', $request->selected_category_id)->orderBy('refrence_no', 'DESC')->first();
        // dd($c_p_ref);
        if ($c_p_ref == NULL) {
            $str = '0';
        } else {
            $str = $c_p_ref->refrence_no;
        }

        $system_gen_no              =  str_pad(@$str + 1, STR_PAD_LEFT);

        $product->refrence_code     = $prefix . $system_gen_no;
        $product->system_code       = $prefix . $system_gen_no;
        $product->refrence_no       = $system_gen_no;
        $product->bar_code          = $prefix . $system_gen_no;
        $product->primary_category  = $getSubCat->parent_id;
        $product->category_id       = $request->selected_category_id;

        $product->hs_code           = $getSubCat->hs_code;
        $product->import_tax_book   = $getSubCat->import_tax_book;
        $product->vat               = $getSubCat->vat;

        $product->created_by        = Auth::user()->id;

        $product->save();

        $recentAdded = Product::find($product->id);

        $categoryMargins = CustomerTypeCategoryMargin::where('category_id', $recentAdded->category_id)->orderBy('id', 'ASC')->get();
        if ($categoryMargins->count() > 0) {
            foreach ($categoryMargins as $value) {
                $productMargin = new CustomerTypeProductMargin;
                $productMargin->product_id       = $recentAdded->id;
                $productMargin->customer_type_id = $value->customer_type_id;
                $productMargin->default_margin   = $value->default_margin;
                $productMargin->default_value    = $value->default_value;
                $productMargin->save();
            }
        }

        $customerCats = CustomerCategory::where('is_deleted', 0)->orderBy('id', 'ASC')->get();
        if ($customerCats->count() > 0) {
            foreach ($customerCats as $c_cat) {
                $productFixedPrices = new ProductFixedPrice;
                $productFixedPrices->product_id       = $recentAdded->id;
                $productFixedPrices->customer_type_id = $c_cat->id;
                $productFixedPrices->fixed_price      = 0;
                $productFixedPrices->expiration_date  = NULL;
                $productFixedPrices->save();
            }
        }

        $warehouse = Warehouse::get();
        foreach ($warehouse as $w) {
            $warehouse_product = new WarehouseProduct;
            $warehouse_product->warehouse_id = $w->id;
            $warehouse_product->product_id = $recentAdded->id;
            $warehouse_product->save();
        }

        $product_id = $product->id;
        $request->id = $product->id;
        // status change check
        $mark_as_complete = $this->doProductCompleted($request);
        $json_response = json_decode($mark_as_complete->getContent());

        $newproduct = Product::where('id', $product->id)->first();
        return response()->json(['success' => false, 'product' => $newproduct, 'selected_cat' => $request->selected_category_id]);
    }

    public function doProductCompleted(Request $request)
    {
        if ($request->id) {
            $product = Product::find($request->id);

            $missingPrams = array();

            if ($product->refrence_code == null) {
                $missingPrams[] = 'Product Reference Code';
            }

            if ($product->short_desc == null) {
                $missingPrams[] = 'Short Description';
            }

            if ($product->primary_category == null) {
                $missingPrams[] = 'Primary Category';
            }

            if ($product->category_id == 0) {
                $missingPrams[] = 'Sub Category';
            }

            if ($product->type_id == null) {
                $missingPrams[] = 'Product Type';
            }

            if ($product->buying_unit == null) {
                $missingPrams[] = 'Billed Unit';
            }

            if ($product->selling_unit == null) {
                $missingPrams[] = 'Selling Unit';
            }

            if ($product->unit_conversion_rate == null || $product->unit_conversion_rate == 0) {
                $missingPrams[] = 'Unit Conversion Rate';
            }

            if ($product->supplier_id == 0) {
                $missingPrams[] = 'Default Supplier';
            }

            if ($product->supplier_id != 0) {
                $checkingProductSupplier = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $product->supplier_id)->first();
                if ($checkingProductSupplier) {
                    if ($checkingProductSupplier->buying_price === null) {
                        $missingPrams[] = 'Supplier Buying Price';
                    }

                    if ($checkingProductSupplier->leading_time === null) {
                        $missingPrams[] = 'Supplier Leading Time';
                    }
                }
            }
            if (sizeof($missingPrams) == 0) {
                $users = User::where('status', 1)->whereNull('parent_id')->where('role_id', 3)->get();
                $details = [
                    'user_id' => @Auth::user()->id,
                    'desc' => 'New Product ' . $product->refrence_code . ' has been added!',
                    'product_id' => $product->id,
                    'reference_code' => $product->refrence_code
                ];
                /*Notification::send($users, new AddProductNotification($details));

                event(new ProductCreated('Farooq'));*/
                $product->status = 1;
                $product->save();
                $message = "completed";

                return response()->json(['success' => true, 'message' => $message]);
            } else {
                $message = implode(', ', $missingPrams);
                return response()->json(['success' => false, 'message' => $message]);
            }
        }
    }

    protected function generateRandomString($length)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getProductSuppliersRecord($id)
    {


        $query = SupplierProducts::with('supplier', 'product')->where('product_id', $id)->where('is_deleted', 0)->get();

        return Datatables::of($query)

            ->addColumn('action', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id != NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = 'd-none';
                    } else {
                        $class = '';
                    }
                } else {
                    $class = '';
                }

                $html_string = '<a href="javascript:void(0);" class="actionicon deleteIcon ' . $class . '" data-prodisupid="' . @$item->supplier->id . '" data-prodid="' . $item->product_id . '" data-rowid="' . $item->id . '" name="delete_sup" id="delete_sup" title="Delete"><i class="fa fa-trash"></i></a>';
                return $html_string;
            })
            ->addColumn('company', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = 'prodSuppInputDoubleClick';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $checkedIds = SupplierProducts::where('product_id', $item->product_id)->where('supplier_id', '!=', NULL)->where('is_deleted',0)->pluck('supplier_id')->toArray();
                $getSuppliers = Supplier::whereNotIn('id', $checkedIds)->where('status', 1)->orderBy('reference_name')->get();

                if ($item->supplier_id != NULL) {
                    $html_string = '<span class="m-l-15 ' . $class . '" id="product_supplier" data-fieldvalue="' . $item->supplier_id . '">' . $item->supplier->reference_name . '</span>';

                    $html_string .= '<div class="d-none select2-incomplete"><select class="form-control select-common js-states state-tags incomp-select2 mb-2" name="supplier_id" data-live-search="true" style="width:100%;">';
                    if ($getSuppliers->count() > 0) {
                        $html_string .= '<option value="" selected="" disabled="">Select Supplier</option>';
                        foreach ($getSuppliers as $supplier) {
                            // $condition = ($item->supplier_id == $supplier->id ? "selected" : "");
                            $html_string .= '<option value="' . $supplier->id . '">' . $supplier->reference_name . '</option>';
                        }
                    }
                    $html_string .= '</select></div>';
                    return $html_string;

                    // $ref_no = $item->supplier->reference_name;
                    // return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier->id).'"  >'.$ref_no.'</a>';
                } else {
                    $color = "red";
                    $html_string = '<span class="m-l-15 ' . $class . '" style="color:' . $color . ';" id="product_supplier" data-fieldvalue="' . $item->supplier_id . '">N.A</span>';

                    $html_string .= '<div class="d-none select2-incomplete"><select class="form-control select-common js-states state-tags incomp-select2 mb-2" name="supplier_id" data-live-search="true" style="width:100%;">';
                    if ($getSuppliers->count() > 0) {
                        $html_string .= '<option value="" selected="" disabled="">Select Supplier</option>';
                        foreach ($getSuppliers as $supplier) {
                            $html_string .= '<option value="' . $supplier->id . '">' . $supplier->reference_name . '</option>';
                        }
                    }
                    $html_string .= '</select></div>';
                    return $html_string;
                }
            })
            ->addColumn('product_supplier_reference_no', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="product_supplier_reference_no"  data-fieldvalue="' . $item->product_supplier_reference_no . '">' . ($item->product_supplier_reference_no != NULL ? $item->product_supplier_reference_no : "--") . '</span>
                <input type="text" style="width:100%;" name="product_supplier_reference_no" class="prodSuppFieldFocus d-none" value="' . $item->product_supplier_reference_no . '">';
                return $html_string;
            })
            ->addColumn('supplier_description', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="supplier_description"  data-fieldvalue="' . $item->supplier_description . '">' . ($item->supplier_description != NULL ? $item->supplier_description : "--") . '</span>
                <input type="text" style="width:100%;" name="supplier_description" class="prodSuppFieldFocus d-none" value="' . $item->supplier_description . '">';
                return $html_string;
            })
            ->addColumn('import_tax_actual', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="import_tax_actual"  data-fieldvalue="' . $item->import_tax_actual . '">' . ($item->import_tax_actual !== NULL ? number_format((float)@$item->import_tax_actual, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="import_tax_actual" class="prodSuppFieldFocus d-none" value="' . $item->import_tax_actual . '">';
                return $html_string;
            })
            ->addColumn('vat_actual', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="vat_actual"  data-fieldvalue="' . $item->vat_actual . '">' . ($item->vat_actual !== NULL ? number_format((float)@$item->vat_actual, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="vat_actual" class="prodSuppFieldFocus d-none" value="' . $item->vat_actual . '">';
                return $html_string;
            })
            ->addColumn('gross_weight', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="gross_weight"  data-fieldvalue="' . $item->gross_weight . '">' . ($item->gross_weight !== NULL ? number_format((float)@$item->gross_weight, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="gross_weight" class="prodSuppFieldFocus d-none" value="' . $item->gross_weight . '">';
                return $html_string;
            })
            ->addColumn('freight', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="freight"  data-fieldvalue="' . $item->freight . '">' . ($item->freight !== NULL ? number_format((float)@$item->freight, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="freight" class="prodSuppFieldFocus d-none" value="' . $item->freight . '">';
                return $html_string;
            })
            ->addColumn('landing', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="landing"  data-fieldvalue="' . $item->landing . '">' . ($item->landing !== NULL ? number_format((float)$item->landing, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="landing" class="prodSuppFieldFocus d-none" value="' . $item->landing . '">';
                return $html_string;
            })
            ->addColumn('buying_price', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                if ($item->buying_price === NULL) {
                    $color = "red";
                } else {
                    $color = "";
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="buying_price" style="color:' . $color . ';" data-fieldvalue="' . $item->buying_price . '">' . ($item->buying_price !== NULL ? number_format((float)$item->buying_price, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="buying_price" class="prodSuppFieldFocus d-none" value="' . $item->buying_price . '">';
                return $html_string;
            })
            ->addColumn('buying_price_in_thb', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = '';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="buying_price_in_thb"  data-fieldvalue="' . $item->buying_price_in_thb . '">' . ($item->buying_price_in_thb !== NULL ? number_format((float)$item->buying_price_in_thb, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="buying_price_in_thb" class="prodSuppFieldFocus d-none" value="' . $item->buying_price_in_thb . '">';
                return $html_string;
            })
            ->addColumn('extra_cost', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="extra_cost"  data-fieldvalue="' . $item->extra_cost . '">' . ($item->extra_cost !== NULL ? number_format((float)$item->extra_cost, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="extra_cost" class="prodSuppFieldFocus d-none" value="' . $item->extra_cost . '">';
                return $html_string;
            })
            ->addColumn('extra_tax', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="extra_tax"  data-fieldvalue="' . $item->extra_tax . '">' . ($item->extra_tax !== NULL ? number_format((float)$item->extra_tax, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="extra_tax" class="prodSuppFieldFocus d-none" value="' . $item->extra_tax . '">';
                return $html_string;
            })
            ->addColumn('unit_import_tax', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="unit_import_tax"  data-fieldvalue="' . $item->unit_import_tax . '">' . ($item->unit_import_tax !== NULL ? number_format((float)$item->unit_import_tax, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="unit_import_tax" class="prodSuppFieldFocus d-none" value="' . $item->unit_import_tax . '">';
                return $html_string;
            })
            ->addColumn('leading_time', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                if ($item->leading_time == NULL) {
                    $color = "red";
                } else {
                    $color = "";
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="leading_time" style="color:' . $color . ';" data-fieldvalue="' . $item->leading_time . '">' . ($item->leading_time !== NULL ? $item->leading_time : "--") . '</span>
                <input type="number" style="width:100%;" name="leading_time" class="prodSuppFieldFocus d-none" value="' . $item->leading_time . '">';
                return $html_string;
            })
            ->addColumn('supplier_packaging', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                $getProductUnit = Unit::all();
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                // $html_string = '<span class="m-l-15 '.$class.' " id="supplier_packaging"  data-fieldvalue="'.$item->supplier_packaging.'">'.($item->supplier_packaging !== NULL ? $item->supplier_packaging : "--").'</span>
                // <input type="number" style="width:100%;" name="supplier_packaging" class="prodSuppFieldFocus d-none" value="'.$item->supplier_packaging.'">';

                $html_string = '<span class="m-l-15 ' . $class . ' " id="supplier_packaging" data-place="listing" data-fieldvalue="' . $item->supplier_packaging . '">' . ($item->supplier_packaging !== NULL ? $item->supplier_packaging : "--") . '</span>';

                $html_string .= '<select name="supplier_packaging" class="selectFocus form-control d-none">
                <option value="" disabled="" selected="">Choose Unit</option>';
                if ($getProductUnit->count() > 0) {
                    foreach ($getProductUnit as $unit) {
                        $html_string .= '<option ' . (@$item->supplier_packaging == $unit->title ? 'selected' : '') . ' value=" ' . $unit->title . ' ">' . $unit->title . '</option>';
                    }
                }
                return $html_string;
            })
            ->addColumn('billed_unit', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="billed_unit"  data-fieldvalue="' . $item->billed_unit . '">' . ($item->billed_unit !== NULL ? number_format((float)$item->billed_unit, 3, '.', '') : "--") . '</span>
                <input type="number" style="width:100%;" name="billed_unit" class="prodSuppFieldFocus d-none" value="' . $item->billed_unit . '">';
                return $html_string;
            })
            ->addColumn('m_o_q', function ($item) {
                $class = '';
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id !== NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        $class = '';
                    } else {
                        $class = 'prodSuppInputDoubleClick';
                    }
                } else {
                    $class = '';
                }
                if (Auth::user()->role_id == 3 || Auth::user()->role_id == 4 || Auth::user()->role_id == 6) {
                    $class = '';
                }
                $html_string = '<span class="m-l-15 ' . $class . ' " id="m_o_q"  data-fieldvalue="' . $item->m_o_q . '">' . ($item->m_o_q !== NULL ? $item->m_o_q : "--") . '</span>
                <input type="number" style="width:100%;" name="m_o_q" class="prodSuppFieldFocus d-none" value="' . $item->m_o_q . '">';
                return $html_string;
            })
            ->setRowId(function ($item) {
                return $item->id;
            })
            // greyRow is a custom style in style.css file
            ->setRowClass(function ($item) {
                $checkLastSupp = Product::find($item->product_id);
                if ($item->supplier_id != NULL) {
                    if ($item->supplier_id == $checkLastSupp->supplier_id) {
                        return $item->supplier_id == $checkLastSupp->supplier_id ? 'greyRow' : '';
                    }
                }
            })
            ->rawColumns(['action', 'company', 'product_supplier_reference_no', 'import_tax_actual', 'buying_price', 'freight', 'landing', 'leading_time', 'gross_weight', 'supplier_packaging', 'billed_unit', 'm_o_q', 'buying_price_in_thb', 'extra_cost', 'extra_tax', 'supplier_description', 'vat_actual', 'unit_import_tax'])
            ->make(true);
    }

    public function updateFixedPricesCheck(Request $request)
    {
        $product = Product::find($request->product_id);
        $product->fixed_price_check = $request->checkbox;
        $product->save();
        return response()->json(['success' => true]);
    }

    public function getProductDetail($id)
    {
        // dd("product detail");
        $config = Configuration::first();
        $sys_name = $config->company_name;
        $sys_color = $config;
        $sys_logos = $config;
        $vairables = Variable::select('slug', 'standard_name', 'terminology')->get();
        $global_terminologies = [];
        $suppliers = Supplier::where('status', 1)->get();

        foreach ($vairables as $variable) {
            if ($variable->terminology != null) {
                $global_terminologies[$variable->slug] = $variable->terminology;
            } else {
                $global_terminologies[$variable->slug] = $variable->standard_name;
            }
        }
        $product_type = ProductType::select('id', 'title')->get();
        $product_type_2 = ProductSecondaryType::select('id', 'title')->orderBy('title', 'asc')->get();
        $product_type_3 = ProductTypeTertiary::select('id', 'title')->orderBy('title', 'asc')->get();
        $product_brand = Brand::select('id', 'title')->get();
        $product = Product::with('def_or_last_supplier.getcountry', 'units', 'productCategory', 'supplier_products', 'productSubCategory')->where('id', $id)->first();

        $ProductCustomerFixedPrices = ProductCustomerFixedPrice::with('customers', 'products')->where('product_id', $id)->orderBy('id', 'ASC')->get();

        $productImages = ProductImage::where('product_id', $id)->orderBy('id', 'ASC')->get();
        $productImagesCount = ProductImage::select('image', 'product_id')->where('product_id', $id)->count();
        $last_or_def_supp_id = $product->supplier_id;

        $extra_tax_percentage = 0;
        $import_tax_percentage = 0;
        $import_tax_actual_in_tbh = 0;
        $imported = false;
        if ($last_or_def_supp_id != 0) {
            $default_or_last_supplier = SupplierProducts::with('supplier')->where('product_id', $id)->where('supplier_id', $last_or_def_supp_id)->where('is_deleted',0)->first();
            $supplier_name = Supplier::select('company')->where('id', $last_or_def_supp_id)->first();
            $supplier_company = @$supplier_name->company;

            if ($default_or_last_supplier != null && $default_or_last_supplier->extra_tax_percent == null) {
                if ($default_or_last_supplier->buying_price_in_thb != null || $default_or_last_supplier->buying_price_in_thb != 0) {
                    $extra_tax_percentage = ($default_or_last_supplier->extra_tax / $default_or_last_supplier->buying_price_in_thb) * 100;
                }
            } else {
                $extra_tax_percentage = $default_or_last_supplier->extra_tax_percent;
            }
            $extra_tax_percentage = number_format((float)$extra_tax_percentage, 3, '.', '');

            if ($default_or_last_supplier->import_tax_actual == null) {
                $import_tax_percentage = $product->import_tax_book;
                // if ($default_or_last_supplier->buying_price_in_thb != null || $default_or_last_supplier->buying_price_in_thb != 0) {
                //   $import_tax_percentage = ($default_or_last_supplier->unit_import_tax / $default_or_last_supplier->buying_price_in_thb) * 100;
                // }
            } else {
                $imported = true;
                $import_tax_percentage = $default_or_last_supplier->import_tax_actual;
            }
            $import_tax_percentage = number_format((float)$import_tax_percentage, 3, '.', '');

            if ($default_or_last_supplier->unit_import_tax == null && $default_or_last_supplier->import_tax_actual == null) {
                $import_tax_actual_in_tbh = ($product->import_tax_book / 100) * $default_or_last_supplier->buying_price_in_thb;
                // if ($default_or_last_supplier->buying_price_in_thb != null || $default_or_last_supplier->buying_price_in_thb != 0) {
                //   $import_tax_actual_in_tbh = ($default_or_last_supplier->import_tax_actual / 100) * $default_or_last_supplier->buying_price_in_thb;
                // }
            } else {
                $import_tax_actual_in_tbh = $default_or_last_supplier->unit_import_tax != null && (int)$default_or_last_supplier->unit_import_tax != 0 ? $default_or_last_supplier->unit_import_tax : $default_or_last_supplier->buying_price_in_thb * ($import_tax_percentage / 100);
            }
        }

        $import_tax_actual_in_tbh = number_format((float)$import_tax_actual_in_tbh, 3, '.', '');


        $warehouses = Warehouse::orderBy('id', 'ASC')->get();
        $stock_card = StockManagementIn::where('product_id', $id)->orderBy('expiration_date', 'DESC')->get();
        $warehouse_products = WarehouseProduct::where('product_id', $id)->get();

        $total_buy_unit_calculation = SupplierProducts::where('product_id', $id)->where('supplier_id', $last_or_def_supp_id)->pluck('import_tax_actual')->first();

        $getProductUnit = Unit::select('id', 'title')->get();

        // $getSuppliers = Supplier::where('status',1)->orderBy('reference_name')->whereIn('id',SupplierProducts::select('supplier_id')->where('product_id',$id)->where('is_deleted',0)->where('supplier_id','!=',null)->pluck('supplier_id'))->get();
        $customers = Customer::select('id', 'company','reference_name')->where('status', 1)->get();

        if ($total_buy_unit_calculation != NULL) {
            $IMPcalculation = 'Purchasing Price + (Import Tax Actual/100 * Purchasing Price) + Extra Cost + Import Tax + Frieght + Landing';
        } elseif ($product->productSubCategory->import_tax_book != null) {
            $IMPcalculation = 'Purchasing Price + (Import Tax Book/100 * Purchasing Price) + Extra Cost + Import Tax + Frieght + Landing';
        } else {
            $IMPcalculation = 'Purchasing Price + (Import Tax Book/100 * Purchasing Price) + Extra Cost + Import Tax + Frieght + Landing';
        }

        $product_parent_category = ProductCategory::select('id', 'title')->where('parent_id', 0)->orderBy('title')->get();

        $ecommerceconfig = QuotationConfig::where('section', 'ecommerce_configuration')->first();

        if ($ecommerceconfig) {
            $check_status = unserialize($ecommerceconfig->print_prefrences);
            $ecommerceconfig_status = $check_status['status'][0];
            $ecommerceconfig_type   = $check_status['status'][6];
        } else {
            $ecommerceconfig_status = '';
            $ecommerceconfig_type   = '';
        }

        if (Auth::user()->role_id == 9) {
            if ($ecommerceconfig_status == 1) {
                if ($ecommerceconfig_type != NULL) {
                    $customerCategories = CustomerCategory::select('id', 'title', 'is_deleted')->where('id', $ecommerceconfig_type)->where('is_deleted', 0)->get();
                } else {
                    $customerCategories = CustomerCategory::select('id', 'title', 'is_deleted')->where('is_deleted', 0)->get();
                }
            } else {
                $customerCategories = CustomerCategory::select('id', 'title', 'is_deleted')->where('is_deleted', 0)->get();
            }
        } else {
            $customerCategories = CustomerCategory::select('id', 'title', 'is_deleted')->where('is_deleted', 0)->get();
        }

        $final_stock = StockManagementOut::select('quantity_out,warehouse_id,quantity_in')->where('product_id', $id);
        $part1 = explode("#", $config->system_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $num1 = hexdec($value);
        $num2 = hexdec('001500');
        $sum = $num1 + $num2;
        $sys_border_color = "#";
        $sys_border_color .= dechex($sum);

        $part1 = explode("#", $config->btn_hover_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $number = hexdec($value);
        $sum = $number + $num2;
        $btn_hover_border = "#";
        $btn_hover_border .= dechex($sum);

        $current_version = "3.8";

        $menus = RoleMenu::where('role_id', Auth::user()->role_id)->groupby('parent_id')->orderBy('order', 'asc')->pluck('parent_id')->toArray();
        $slugs = Menu::whereNotNull('slug')->pluck('slug');
        $global_counters = [];
        foreach ($slugs as $slug) {
            switch ($slug) {
                case "completeProducts":
                    $global_counters['completeProducts'] = Product::where('status', 1)->count('id');
                    break;
                case "incompleteProducts":
                    $global_counters['incompleteProducts'] = Product::where('status', 0)->count('id');
                    break;
                case "inquiryProducts":
                    $global_counters['inquiryProducts'] = OrderProduct::where('is_billed', 'Inquiry')->count('id');
                    break;
                case "cancelledOrders":
                    if (Auth::user()->role_id == 9) {
                        $global_counters['cancelledOrders'] = Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
                    } else {
                        $global_counters['cancelledOrders'] = Order::select('id')->where('primary_status', 17)->count('id');
                    }
                    break;
                case "deactivatedProducts":
                    $global_counters['deactivatedProducts'] = Product::where('status', 2)->count('id');
                    break;
                case "ecommerceProducts":
                    $global_counters['ecommerceProducts'] = Product::where('ecommerce_enabled', 1)->count('id');
                    break;
                case "EcomCancelledOrders":
                    $global_counters['EcomCancelledOrders'] = Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
                    break;
                case "billing-configuration":
                    $global_counters['billing-configuration'] = '';
                    break;
            }
        }

        $globalAccessConfig2 = QuotationConfig::where('section', 'products_management_page')->first();
        if ($globalAccessConfig2) {
            $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "allow_custom_code_edit") {
                    $allow_custom_code_edit = $val['status'];
                }
                if ($val['slug'] === "hide_hs_description") {
                    $hide_hs_description = $val['status'];
                }
                if ($val['slug'] === "same_description") {
                    $allow_same_description = $val['status'];
                }
            }
        } else {
            $allow_custom_code_edit = '';
            $hide_hs_description = '';
            $allow_same_description = '';
        }

        // $product_detail_page = QuotationConfig::where('section', 'product_detail_page')->first();
        // // dd($product_detail_page);
        // $product_detail_section = [];
        // if ($product_detail_page) {
        //     $globalaccessForConfig = unserialize($product_detail_page->print_prefrences);
        //     // dd($globalaccessForConfig);
        //     foreach ($globalaccessForConfig as $key => $value) {
        //         if($value['status'] == 1) {
        //             array_push($product_detail_section, $value['slug']);
        //         }
        //     }
        // }


        $product_detail_page_supplier_detail = QuotationConfig::where('section', 'product_detail_page_supplier_detail')->first();
        $default_supplier_detail_section = [];
        if ($product_detail_page_supplier_detail) {
            $globalAccess = unserialize($product_detail_page_supplier_detail->print_prefrences);

            foreach ($globalAccess as $key => $value) {
                if ($value['status'] == 1) {
                    array_push($default_supplier_detail_section, $value['slug']);
                }
            }
        }


        $data = BarcodeConfiguration::first();
        $height = $data->height ?? '';
        $width = $data->width ?? '';

        $checkItemPo = new Product;
        $checkItemPo = $checkItemPo->checkItemImportExistance($id);
        if (@$default_or_last_supplier->freight == null) {
            $redHighlighted = 'style=color:red';
            // $tooltip = "This item has never been imported before in our system, so the suggested price may be incorrect";
            $tooltip = "WARNING! This product doesn't have freight entered for it yet, so the price may be inaccurate";
        } else {
            $redHighlighted = '';
            $tooltip = '';
        }
        return view('users.products.product-detail', compact('product_type', 'product', 'ProductCustomerFixedPrices', 'default_or_last_supplier', 'supplier_company', 'productImages', 'productImagesCount', 'id', 'product_brand', 'warehouses', 'stock_card', 'IMPcalculation', 'getProductUnit', 'customers', 'warehouse_products', 'product_parent_category', 'customerCategories', 'redHighlighted', 'checkItemPo', 'ecommerceconfig_status', 'ecommerceconfig_type', 'final_stock', 'tooltip', 'id', 'sys_color', 'global_terminologies', 'sys_name', 'sys_logos', 'sys_border_color', 'btn_hover_border', 'current_version', 'menus', 'global_counters', 'allow_custom_code_edit', 'hide_hs_description', 'allow_same_description', 'suppliers', 'extra_tax_percentage', 'import_tax_percentage', 'import_tax_actual_in_tbh', 'product_type_2', 'height', 'width', 'imported', 'product_type_3', 'default_supplier_detail_section'));
    }
    public function productSupplier(Request $request)
    {
        $product_id =$request->id;
        $product_all_suppliers = SupplierProducts::with('supplier')->where('product_id', $product_id)->where('is_deleted',0)->get();
        $option = '';
        $option .='<option value="" disabled="true" selected="true">Choose Supply From</option>';
        foreach($product_all_suppliers as $supplier){
        $option .= '<option value="'.@$supplier->supplier->id.'">'.@$supplier->supplier->reference_name.'</option>';  
        }
        return response()->json(['success' => true, 'response' => $option]);
    }
    public function getWarehouse(Request $request)
    {
        $selectedWarehouses = Warehouse::where('status',1)->where('id',$request->id)->first();
        $selectedoption = '';
        $selectedoption .= '<option value="'.@$selectedWarehouses->id.'">'.@$selectedWarehouses->warehouse_title.'</option>';  
        $warehouses = Warehouse::where('status',1)->where('id','!=',$request->id)->get();
        $products = Product::where('status', 1)->select('id', 'refrence_code', 'short_desc')->get();
        $option = '';
        $option .='<option value="" disabled="true" selected="true">Choose One</option>';
        $option .= '<optgroup label="Warehouses">';
        foreach($warehouses as $warehouse){
            $option .= '<option value="w-'.@$warehouse->id.'">'.@$warehouse->warehouse_title.'</option>';  
        }
        $option .= '</optgroup>';

        $option .= '<optgroup label="Products">';
        foreach($products as $product){
            $option .= '<option value="p-'.@$product->id.'">'.@$product->refrence_code.' - '.@$product->short_desc.'</option>';  
        }
        $option .= '</optgroup>';

        return response()->json(['success' => true, 'response' => $option,'currentwarehouse' =>$selectedoption]);
    }
    public function makeManualStockAdjustment(Request $request)
    {
        if ($request->stock_id == 'parent_stock') {
            $stock_in               = new StockManagementIn;
            $stock_in->product_id   = $request->prod_id;
            $stock_in->warehouse_id = $request->warehouse_id;
            $stock_in->created_by   = Auth::user()->id;
            $stock_in->save();
            return response()->json(['parent_stock' => true]);
        }
        $stock_out                  = new StockManagementOut;
        $stock_out->title           = 'Manual Adjustment';
        $stock_out->smi_id          = $request->stock_id;
        $stock_out->product_id      = $request->prod_id;
        $stock_out->warehouse_id    = $request->warehouse_id;
        if($request->stock_for == 'customer'){
            $stock_out->quantity_out    = '-'.$request->quantity_out;
            $stock_out->customer_id     = $request->customer_id;
        }else if($request->stock_for == 'supplier'){
            $stock_out->quantity_in     = $request->quantity_in;
            $stock_out->supplier_id     = $request->supplier_id;
        }else if($request->stock_for == 'spoilage'){
           $spoilage_customer = Customer::where('manual_customer',2)->first();
           if(!$spoilage_customer){
            $spoilage_customer = Customer::create([
                'company'   => 'Spoilage',
                'reference_name'    => 'Spoilage',
                'manual_customer'   => 2
            ]);
           }
            $stock_out->quantity_out     = $request->quantity_out;
            $stock_out->supplier_id     = $request->supplier_id;
            $stock_out->customer_id     = $spoilage_customer->id;
        }
        $stock_out->cost            = $request->cogs;
        $stock_out->created_by   = Auth::user()->id;
        $stock_out->save();

        $product_history              = new ProductHistory;
        $product_history->user_id     = Auth::user()->id;
        $product_history->product_id  = $request->prod_id;
        $product_history->column_name = $stock_out->get_warehouse->warehouse_title . ' Expiration Date: ' . ($stock_out->get_stock_in->expiration_date != null ? $stock_out->get_stock_in->expiration_date : '---');
        $product_history->old_value   = '---';
        if($request->stock_for == 'spoilage'){
        $product_history->new_value   = 'Spoilage Adjustment';
        }else{
        $product_history->new_value   = 'Manual Adjustment';
        }
        $product_history->save();

        if($request->stock_for == 'customer' || $request->stock_for == 'spoilage' ){
            $new_request = new \Illuminate\Http\Request();
            $new_request->replace(['id' => $stock_out->id, 'quantity_out' => $request->quantity_out, 'old_value' => 0]);
            $this->updateStockRecord($new_request);
        }else{
            $new_request = new \Illuminate\Http\Request();
            $new_request->replace(['id' => $stock_out->id, 'quantity_in' => $request->quantity_in, 'old_value' => 0]);
            $this->updateStockRecord($new_request);
        }
        // dd($product_history);

        $stock_out = StockManagementOut::find($stock_out->id);

        $stock_out_in = StockManagementOut::where('smi_id', $request->stock_id)->sum('quantity_in');
        $stock_out_out = StockManagementOut::where('smi_id', $request->stock_id)->sum('quantity_out');
        //dd($stock_out_in,$stock_out_out);
        $enable = 'inputDoubleClickFirst';
        $html_string = '';
        $html_string .= '<tr><td><a href="javascript:void(0)" class="actionicon deleteIcon text-center deleteStock" data-id="' . $stock_out->id . '"><i class="fa fa-trash" title="Delete Stock"></i></a></td>
                    <td>' . Carbon::parse($stock_out->created_at)->format('d/m/Y') . '</td>';
        $html_string .= '<td>--</td>';

        $html_string .= '<td>
                        <span class="m-l-15 selectDoubleClick" id="title" data-fieldvalue="' . $stock_out->title . '">
                          ' . $stock_out->title . '
                        </span>';
        if($stock_out->order_id != null)
        {
            if(@$stock_out->stock_out_order->primary_status == 37)
            {
                $html_string .= '<a target="_blank" href="'.route('get-completed-draft-invoices', ['id' => $stock_out->stock_out_order->id]).'" title="View Detail" class="font-weight-bold ml-3">ORDER# '.@$stock_out->stock_out_order->full_inv_no.'</a>';
            }
        }
        if($stock_out->po_id != null)
        {
            if(@$stock_out->stock_out_po->status == 40)
            {
                $html_string .= '<a target="_blank" href="'.url('get-purchase-order-detail',$stock_out->po_id).'" title="View Detail" class="font-weight-bold ml-3">PO# '.@$stock_out->stock_out_po->ref_id.'</a>';
            }
        }

        $html_string .= '
                         <select name="title" class="selectFocusStock form-control d-none" data-id="' . $stock_out->id . '">
                          <option>Choose Reason</option>
                          <option ' . (@$stock_out->title == 'Manual Adjustment' ? 'selected' : '') . ' value="">Manual Adjustment</option>
                          <option ' . (@$stock_out->title == 'Expired' ? 'selected' : '') . ' value="">Expired</option>
                          <option ' . (@$stock_out->title == 'Spoilage' ? 'selected' : '') . ' value="">Spoilage</option>
                          <option ' . (@$stock_out->title == 'Lost' ? 'selected' : '') . ' value="">Lost</option>
                          <option ' . (@$stock_out->title == 'Marketing' ? 'selected' : '') . ' value="">Marketing</option>
                          <option ' . (@$stock_out->title == 'Return' ? 'selected' : '') . ' value="">Return</option>
                          <option ' . (@$stock_out->title == 'Transfer' ? 'selected' : '') . ' value="">Transfer</option>
                        </select>
                        <span id="manual_order_' . $stock_out->id . '"></span>';

        
        $html_string .='</td>';
        $html_string .= '<td>--</td>';
        $html_string .= '<td>
                      <span class="m-l-15 ' . $enable . ' disableDoubleInClick-' . $stock_out->id . ' " id="quantity_in_span_' . @$stock_out->id . '"  data-fieldvalue="' . $stock_out->quantity_in . '">' . ($stock_out->quantity_in != null ? $stock_out->quantity_in : '0') . '</span>
                      <input type="number" min="0" style="width:100%;" name="quantity_in" data-type="in" class="fieldFocusStock d-none" data-warehouse_id="' . $stock_out->warehouse_id . '" data-smi="' . $request->stock_id . '" value="' . $stock_out->quantity_in . '" id="quantity_in_' . $stock_out->id . '" data-id="' . $stock_out->id . '">
                    </td>';
        $html_string .= '<td>
                      <span class="m-l-15 ' . $enable . ' disableDoubleOutClick-' . $stock_out->id . ' " id="quantity_out_span_' . @$stock_out->id . '"  data-fieldvalue="' . $stock_out->quantity_out . '">' . ($stock_out->quantity_out != null ? $stock_out->quantity_out : '0') . '</span>
                      <input type="number" min="0" style="width:100%;" name="quantity_out" data-type="out" id="quantity_out_' . $stock_out->id . '"  data-warehouse_id="' . $stock_out->warehouse_id . '" data-smi="' . $request->stock_id . '" class="fieldFocusStock d-none" value="' . $stock_out->quantity_out . '" data-id="' . $stock_out->id . '">
                    </td>';
        $html_string .= '<td>' . round($stock_out_in + $stock_out_out, 3) . '</td>';
        $html_string .= '<td>';
        if(($stock_out->title == 'Manual Adjustment' || $stock_out->title == 'Expired' || $stock_out->title == 'Spoilage' || $stock_out->title == 'Lost' || $stock_out->title == 'Marketing' || $stock_out->title == 'Return' || $stock_out->title == 'Transfer')){
                          $html_string .= '<span class="m-l-15 inputDoubleClick" id="cost"  data-fieldvalue="'.$stock_out->cost.'">
                            '.($stock_out->cost != null ? $stock_out->cost : '--').'
                          </span>
                          <input type="text" autocomplete="nope" name="cost" class="fieldFocusCost d-none form-control" data-id="'.$stock_out->id.'" value="'.(@$stock_out->cost!=null)?$stock_out->cost:''.'">';
                          }
                          else{
                              if($stock_out->cost != null){
                              $html_string .= $stock_out->cost != null ? round(($stock_out->cost),3) : '--';
                              }
                              elseif($stock_out->order_product_id != null && $stock_out->order_product){
                              $html_string .= $stock_out->order_product->actual_cost != null ? number_format($stock_out->order_product->actual_cost,2,'.',',') : '--';
                              }
                              else
                              {
                                $html_string .= '<span>--</span>';
                              }

                          }
        $html_string .= '</td>';
        $html_string .= '<td>
                      <span class="m-l-15 ' . $enable . '" id="note"  data-fieldvalue="' . $stock_out->note . '">' . ($stock_out->note != null ? $stock_out->note : '--') . '</span>
                      <input type="text" style="width:100%;" name="note" class="fieldFocusStock d-none" value="' . $stock_out->note . '" data-id="' . $stock_out->id . '">
                    </td>
                  </tr>';
        return response()->json(['success' => true, 'html_string' => $html_string, 'id' => @$request->stock_id]);
    }

    public function updateStockRecord(Request $request)
    {
        // dd($request->all());
        if ($request->has('expiration_date')) {
            $expiration_date = str_replace("/", "-", $request->expiration_date);
            $expiration_date =  date('Y-m-d', strtotime($expiration_date));

            $stock_in = StockManagementIn::where('id', $request->id)->first();
            $stock_in->expiration_date = $expiration_date;
            $stock_in->save();

            $product_history              = new ProductHistory;
            $product_history->user_id     = Auth::user()->id;
            $product_history->product_id  = $stock_in->product_id;
            $product_history->column_name = 'expiration_date for ' . $stock_in->getWarehouse->warehouse_title;
            $product_history->old_value   = $request->old_value != null ? $request->old_value : '---';
            $product_history->new_value   = $expiration_date;
            $product_history->save();

            return response()->json(['expiration_date' => true]);
        }
        $stock_out = StockManagementOut::find($request->id);
        $stock_out->cost = $stock_out->cost == null ? ($stock_out->get_product != null ? round($stock_out->get_product->selling_price, 3) : null) : $stock_out->cost;
        foreach ($request->except('id', 'old_value') as $key => $value) {
            // $warehouse_products = WarehouseProduct::where('warehouse_id',$stock_out->warehouse_id)->where('product_id',$stock_out->product_id)->first();
            // $my_helper =  new MyHelper;
            // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

            $warehouse_products = WarehouseProduct::where('warehouse_id', $stock_out->warehouse_id)->where('product_id', $stock_out->product_id)->first();
            if ($key == 'quantity_in') {
                if ($stock_out->quantity_out != null) {
                    return response()->json(['success' => false, 'cannot_add' => true, 'id' => $stock_out->id, 'quantity_in' => true]);
                }
                $decimal_places = $stock_out->get_product != null ? ($stock_out->get_product->sellingUnits != null ? $stock_out->get_product->sellingUnits->decimal_places : 3) : 3;
                $value = round($value, $decimal_places);
                $stock_out->$key = $value;
                $stock_out->available_stock = $value;
                $warehouse_products->current_quantity += round($value - $request->old_value, 3);
                $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);
                $stock_out->save();
                if ($stock_out->quantity_in != null && (abs($stock_out->available_stock) == abs($stock_out->quantity_in))) {
                    $find_stock = StockManagementOut::where('smi_id', $stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock', '<', 0)->with('order_product')->get();
                    if ($find_stock->count() > 0) {
                        foreach ($find_stock as $out) {

                            if ($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0) {
                                if ($stock_out->available_stock >= abs($out->available_stock)) {
                                    $qty = abs($out->available_stock);
                                    $out->parent_id_in .= $stock_out->id . ',';
                                    $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                    $out->available_stock = 0;
                                    if($out->order_product_id != null){
                                        $new_stock_out_history = (new StockOutHistory)->setHistory($stock_out,$out,$out->order_product,round(abs($qty),4));
                                    }

                                } else {
                                    $qty = $stock_out->available_stock;
                                    $out->parent_id_in .= $stock_out->id . ',';
                                    $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                    $stock_out->available_stock = 0;

                                    if($out->order_product_id != null){
                                        $new_stock_out_history = (new StockOutHistory)->setHistory($stock_out,$out,$out->order_product,round(abs($qty),4));
                                    }
                                }
                                $out->save();
                                $stock_out->save();
                            }
                        }
                    }
                } else {
                    return response()->json(['quantity_out_reserved' => true]);
                }
                if ($stock_out->po_id == null) {
                    $dummy_order = PurchaseOrder::createManualPo($stock_out);
                } else {
                    $order = OrderProduct::find($stock_out->order_product_id);
                    if ($order) {
                        $order->quantity = abs($stock_out->quantity_out);
                        $order->qty_shipped = abs($stock_out->quantity_out);
                        $order->save();
                    }
                }
            } elseif ($key == 'quantity_out') {
                if ($stock_out->quantity_in != null) {
                    return response()->json(['success' => false, 'cannot_add' => true, 'id' => $stock_out->id, 'quantity_out' => true]);
                }

                $decimal_places = $stock_out->get_product != null ? ($stock_out->get_product->sellingUnits != null ? $stock_out->get_product->sellingUnits->decimal_places : 3) : 3;

                $value = abs(round($value, $decimal_places));
                $old_value = abs($request->old_value);

                if ($value > $old_value) {
                    $diff = $value - $old_value;

                    $final_available = abs($stock_out->available_stock) + $diff;
                    $final_available = '-' . $final_available;
                } else {
                    $final_available = 0;
                    $present_available_stock = abs($stock_out->quantity_out) - abs($value);
                    $result = StockManagementOut::setVatInManualAdjustment($present_available_stock, $stock_out);
                }

                $stock_out->$key = '-' . $value;
                $warehouse_products->current_quantity -= round($value - $old_value, 3);
                $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);
                $stock_out->available_stock = $final_available;
                $stock_out->save();
                $warehouse_products->save();

                if ($stock_out->order_id == null) {
                    $dummy_order = Order::createManualOrder($stock_out, 'Adjustment has been placed by ' . Auth::user()->user_name . ' on Product detail page ' . Carbon::now(), 'Adjustment has been placed by ' . Auth::user()->user_name . ' on Product detail page ' . Carbon::now());
                     //picked again because some fields gets updated while creating dummy order
                    $stock_out = StockManagementOut::find($stock_out->id);
                } else {
                    $order = OrderProduct::find($stock_out->order_product_id);
                    if ($order) {
                        $order->quantity = abs($stock_out->quantity_out);
                        $order->qty_shipped = abs($stock_out->quantity_out);
                        $order->save();
                    }
                }

                if ($stock_out->quantity_out != null && (abs($stock_out->available_stock) > 0)) {
                    
                    $supplierId = $stock_out->supplier_id;
                    if($supplierId){
                        $find_stock = StockManagementOut::where('smi_id', $stock_out->smi_id)->whereNotNull('quantity_in')
                        ->where('available_stock', '>', 0)
                        ->with('supplier')
                        ->when($supplierId, function ($query) use ($supplierId) {
                            $query->orderByRaw("supplier_id = $supplierId desc");
                        })
                        ->get();
                    }else{
                        $find_stock = StockManagementOut::where('smi_id', $stock_out->smi_id)->whereNotNull('quantity_in')
                        ->where('available_stock', '>', 0)->get();
                    }
                    

                    if ($find_stock->count() > 0) {
                        foreach ($find_stock as $out) {

                            if (abs($stock_out->available_stock) > 0) {
                                if ($out->available_stock >= abs($stock_out->available_stock)) {
                                    $qty_to_be_out = $stock_out->quantity_out;
                                    $stock_out->parent_id_in .= $out->id . ',';
                                    $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                    $stock_out->available_stock = 0;
                                    // $new_stock_out_history = (new StockOutHistory)->setHistoryForManualAdjustments($out, $stock_out, abs($qty_to_be_out));
                                    if($stock_out->order_product_id != null){
                                        $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$stock_out->order_product,round(abs($qty_to_be_out),4));
                                    }
                                } else {
                                    $qty_to_be_out = $out->available_stock;
                                    $stock_out->parent_id_in .= $out->id . ',';
                                    $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                    $out->available_stock = 0;
                                    // dd($out);
                                    // $new_stock_out_history = (new StockOutHistory)->setHistoryForManualAdjustments($out, $stock_out, abs($qty_to_be_out));
                                    if($stock_out->order_product_id != null){
                                        $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$stock_out->order_product,round(abs($qty_to_be_out),4));
                                    }
                                }
                                $out->save();
                                $stock_out->save();
                            }
                        }
                    }
                }

                
            } elseif ($key == 'title') {
                $stock_out->$key = $value;
            } elseif ($key == 'note') {
                $stock_out->$key = $value;
            }
            $warehouse_products->save();

            $product_history              = new ProductHistory;
            $product_history->user_id     = Auth::user()->id;
            $product_history->product_id  = $stock_out->product_id;
            $product_history->column_name = $key . ' for ' . $warehouse_products->getWarehouse->warehouse_title . ' in Expiration Date:' . ($stock_out->get_stock_in->expiration_date != null ? $stock_out->get_stock_in->expiration_date : '---');
            $product_history->old_value   = $request->old_value;
            $product_history->new_value   = $value;
            $product_history->save();
        }
        $stock_out->save();
        $total_out_in_exp = StockManagementOut::where('smi_id', $stock_out->smi_id)->sum('quantity_out');
        $total_in_in_exp = StockManagementOut::where('smi_id', $stock_out->smi_id)->sum('quantity_in');

        $final_balance = $total_in_in_exp + $total_out_in_exp;
        $order_re = '';
        $po_no = '';
        if (@$stock_out->stock_out_order->primary_status == 37) {
            $order_re .= '<a target="_blank" href="' . route('get-completed-draft-invoices', ['id' => $stock_out->stock_out_order->id]) . '" title="View Detail" class="font-weight-bold ml-3">ORDER# ' . @$stock_out->stock_out_order->full_inv_no . '</a>';
        }
        if (@$stock_out->po_id != null) {
            if (@$stock_out->stock_out_po->status == 40) {
                $order_re .= '<a target="_blank" href="' . url('get-purchase-order-detail', $stock_out->po_id) . '" title="View Detail" class="font-weight-bold ml-3">PO# ' . @$stock_out->stock_out_po->ref_id . '</a>';
            }
        }
        return response()->json(['success' => true, 'current_stock' => round($warehouse_products->current_quantity, 3), 'total_out_in_exp' => round($total_out_in_exp, 3), 'total_in_in_exp' => round($total_in_in_exp, 3), 'final_balance' => round($final_balance, 3), 'id' => $stock_out->id, 'order_no' => $order_re, 'po_no' => $po_no]);
    }

    public function deleteStockRecord(Request $request)
    {
        $stock_out = StockManagementOut::find($request->id);
        if ($stock_out->quantity_in != null) {
            if ($stock_out->quantity_in != abs($stock_out->available_stock)) {
                return response()->json(['already_out' => true]);
            }
        }
        // $warehouse_products = WarehouseProduct::where('warehouse_id',$stock_out->warehouse_id)->where('product_id',$stock_out->product_id)->first();
        // $my_helper =  new MyHelper;
        // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

        $warehouse_products = WarehouseProduct::where('warehouse_id', $stock_out->warehouse_id)->where('product_id', $stock_out->product_id)->first();

        if ($stock_out->quantity_in != null) {
            $warehouse_products->current_quantity -= $stock_out->quantity_in;
        }
        if ($stock_out->quantity_out != null) {
            if ($stock_out->quantity_out < 0) {
                $val = abs($stock_out->quantity_out);
            } else {
                $val = $stock_out->quantity_out;
            }
            $warehouse_products->current_quantity += $val;
        }
        $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);
        $warehouse_products->save();

        $product_history              = new ProductHistory;
        $product_history->user_id     = Auth::user()->id;
        $product_history->product_id  = $stock_out->product_id;
        $product_history->column_name = 'Stock deleted in Expiration Date:' . ($stock_out->get_stock_in->expiration_date != null ? $stock_out->get_stock_in->expiration_date : '---');
        $product_history->old_value   = 'Qty in ' . ($stock_out->quantity_in != null ? $stock_out->quantity_in : 0) . ' And Qty out ' . ($stock_out->quantity_out != null ? $stock_out->quantity_out : 0) . ' in that stock';
        $product_history->new_value   = 'Deleted';
        $product_history->save();
        $stock_out_history = StockOutHistory::where('stock_out_id', $stock_out->id)->orderBy('id', 'desc')->get();
        if ($stock_out_history->count() > 0) {
            foreach ($stock_out_history as $his) {
                $check_stock__data = StockManagementOut::find($his->stock_out_from_id);
                if ($check_stock__data) {
                    $check_stock__data->available_stock = $check_stock__data->available_stock + $his->quantity;
                    $check_stock__data->save();

                    $his->delete();
                }
            }
        }
        if ($stock_out->order_id != null) {
            $order = Order::find($stock_out->order_id);
            if ($order) {
                $order_prod = OrderProduct::where('order_id', $order->id)->get();
                foreach ($order_prod as $draf_quot_prod) {
                    $draf_quot_prod->delete();
                }

                $order->delete();
            }
        }
        $stock_out->delete();
        return response()->json(['success' => true]);
    }

    public function checkSuppExistInProdSupp(Request $request)
    {
        $check = SupplierProducts::where('product_id', $request->prod_id)->where('supplier_id', $request->add_val_check)->first();
        if ($check) {
            return response()->json(['success' => true, 'check' => true]);
        } else {
            return response()->json(['success' => true, 'check' => false]);
        }
    }

    public function editProdSuppData(Request $request)
    {
        $reload = 0;
        $product_supp = SupplierProducts::find($request->id);
        $buying_p = null;
        $selling_p = null;
        $t_b_u_c_p_of_supplier = null;

        $product_detail = Product::find($request->prod_detail_id);

        foreach ($request->except('prod_detail_id', 'old_value') as $key => $value) {
            if ($key == 'buying_price') {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$request->prod_detail_id)->where('supplier_id', @$product_supp->supplier_id)->first();

                // this is the price of after conversion for THB
                if ($getProductDefaultSupplier->currency_conversion_rate != null) {
                    $supplier_conv_rate_thb = 1/@$getProductDefaultSupplier->currency_conversion_rate;
                }
                else{
                    $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;
                }

                if ($product_detail->supplier_id == $product_supp->supplier_id) {
                    $product_supp->$key = $value;
                    $product_supp->buying_price_in_thb = ($value / $supplier_conv_rate_thb);
                    $product_supp->save();

                    $importTax = $getProductDefaultSupplier->import_tax_actual !== null  ? $getProductDefaultSupplier->import_tax_actual : @$product_detail->import_tax_book;

                    // by function
                    $price_calculation = $getProductDefaultSupplier->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_supp->supplier_id, $value, $getProductDefaultSupplier->freight, $getProductDefaultSupplier->landing, $getProductDefaultSupplier->extra_cost, $importTax, $getProductDefaultSupplier->extra_tax);

                    $newValues = Product::find($request->prod_detail_id);
                    $buying_p = $newValues->total_buy_unit_cost_price;
                    $selling_p = $newValues->selling_price;
                    $total_buying_price = $newValues->t_b_u_c_p_of_supplier;

                    $buying_p = number_format((float)$buying_p, 3, '.', '');
                    $selling_p = number_format((float)$selling_p, 3, '.', '');
                    $t_b_u_c_p_of_supplier = number_format((float)@$total_buying_price, 3, '.', '');
                    $reload = 1;
                    CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
                } else {
                    $product_supp->$key = $value;
                    $product_supp->buying_price_in_thb = ($value / $supplier_conv_rate_thb);
                    $product_supp->save();
                }
            }
            if ($key == 'import_tax_actual' || $key == 'unit_import_tax') {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$request->prod_detail_id)->where('supplier_id', @$product_supp->supplier_id)->first();

                if ($product_detail->supplier_id == $product_supp->supplier_id) {
                    $product_supp->$key = $value;
                    $product_supp->save();

                    if ($key == 'import_tax_actual') {
                        $importTax = $value !== null ? $value : $product_detail->import_tax_book;
                        $getProductDefaultSupplier->unit_import_tax = $getProductDefaultSupplier->buying_price_in_thb * ($value / 100);
                        $getProductDefaultSupplier->save();
                    } else {
                        $getProductDefaultSupplier->import_tax_actual = $value / $getProductDefaultSupplier->buying_price_in_thb * 100;
                        $getProductDefaultSupplier->save();
                        $importTax = $getProductDefaultSupplier->import_tax_actual;
                    }

                    // by function
                    $price_calculation = $getProductDefaultSupplier->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_supp->supplier_id, $getProductDefaultSupplier->buying_price, $getProductDefaultSupplier->freight, $getProductDefaultSupplier->landing, $getProductDefaultSupplier->extra_cost, $importTax, $getProductDefaultSupplier->extra_tax);

                    $newValues = Product::find($request->prod_detail_id);
                    $buying_p = $newValues->total_buy_unit_cost_price;
                    $selling_p = $newValues->selling_price;
                    $total_buying_price = $newValues->t_b_u_c_p_of_supplier;

                    $buying_p = number_format((float)$buying_p, 3, '.', '');
                    $selling_p = number_format((float)$selling_p, 3, '.', '');
                    $t_b_u_c_p_of_supplier = number_format((float)@$total_buying_price, 3, '.', '');
                    $reload = 1;
                    CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
                } else {
                    $product_supp->$key = $value;
                    $product_supp->save();
                }
            }
            if ($key == 'freight') {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$request->prod_detail_id)->where('supplier_id', @$product_supp->supplier_id)->first();

                if ($product_detail->supplier_id == $product_supp->supplier_id) {
                    $product_supp->$key = $value;
                    $product_supp->save();

                    $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : @$product_detail->import_tax_book;

                    // by function
                    $price_calculation = $getProductDefaultSupplier->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_supp->supplier_id, $getProductDefaultSupplier->buying_price, $value, $getProductDefaultSupplier->landing, $getProductDefaultSupplier->extra_cost, $importTax, $getProductDefaultSupplier->extra_tax);

                    $newValues = Product::find($request->prod_detail_id);
                    $buying_p = $newValues->total_buy_unit_cost_price;
                    $selling_p = $newValues->selling_price;
                    $total_buying_price = $newValues->t_b_u_c_p_of_supplier;

                    $buying_p = number_format((float)$buying_p, 3, '.', '');
                    $selling_p = number_format((float)$selling_p, 3, '.', '');
                    $t_b_u_c_p_of_supplier = number_format((float)@$total_buying_price, 3, '.', '');
                    $reload = 1;
                    CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
                } else {
                    $product_supp->$key = $value;
                    $product_supp->save();
                }
            }
            if ($key == 'landing') {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$request->prod_detail_id)->where('supplier_id', @$product_supp->supplier_id)->first();
                if ($product_detail->supplier_id == $product_supp->supplier_id) {
                    $product_supp->$key = $value;
                    $product_supp->save();

                    $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : @$product_detail->import_tax_book;

                    // by function
                    $price_calculation = $getProductDefaultSupplier->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_supp->supplier_id, $getProductDefaultSupplier->buying_price, $getProductDefaultSupplier->freight, $value, $getProductDefaultSupplier->extra_cost, $importTax, $getProductDefaultSupplier->extra_tax);

                    $newValues = Product::find($request->prod_detail_id);
                    $buying_p = $newValues->total_buy_unit_cost_price;
                    $selling_p = $newValues->selling_price;
                    $total_buying_price = $newValues->t_b_u_c_p_of_supplier;

                    $buying_p = number_format((float)$buying_p, 3, '.', '');
                    $selling_p = number_format((float)$selling_p, 3, '.', '');
                    $t_b_u_c_p_of_supplier = number_format((float)@$total_buying_price, 3, '.', '');
                    $reload = 1;
                    CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
                } else {
                    $product_supp->$key = $value;
                    $product_supp->save();
                }
            }
            if ($key == 'extra_cost') {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$request->prod_detail_id)->where('supplier_id', @$product_supp->supplier_id)->first();
                if ($product_detail->supplier_id == $product_supp->supplier_id) {
                    $product_supp->$key = $value;
                    $product_supp->save();
                    $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : @$product_detail->import_tax_book;

                    // by function
                    $price_calculation = $getProductDefaultSupplier->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_supp->supplier_id, $getProductDefaultSupplier->buying_price, $getProductDefaultSupplier->freight, $getProductDefaultSupplier->landing, $value, $importTax, $getProductDefaultSupplier->extra_tax);

                    $newValues = Product::find($request->prod_detail_id);
                    $buying_p = $newValues->total_buy_unit_cost_price;
                    $selling_p = $newValues->selling_price;
                    $total_buying_price = $newValues->t_b_u_c_p_of_supplier;

                    $buying_p = number_format((float)$buying_p, 3, '.', '');
                    $selling_p = number_format((float)$selling_p, 3, '.', '');
                    $t_b_u_c_p_of_supplier = number_format((float)@$total_buying_price, 3, '.', '');
                    $reload = 1;
                    CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
                } else {
                    $product_supp->$key = $value;
                    $product_supp->save();
                }
            }
            if ($key == 'extra_tax' || $key == 'extra_tax_percent') {
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$request->prod_detail_id)->where('supplier_id', @$product_supp->supplier_id)->first();
                if ($product_detail->supplier_id == $product_supp->supplier_id) {
                    $product_supp->$key = $value;
                    $product_supp->save();
                    if ($key == 'extra_tax_percent') {
                        $getProductDefaultSupplier->extra_tax = $getProductDefaultSupplier->buying_price_in_thb * ($value / 100);
                        $getProductDefaultSupplier->save();
                        $extra_tax_value = $getProductDefaultSupplier->extra_tax;
                    } else {
                        $getProductDefaultSupplier->extra_tax_percent = $value / $getProductDefaultSupplier->buying_price_in_thb * 100;
                        $getProductDefaultSupplier->save();
                        $extra_tax_value = $value;
                    }
                    $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : @$product_detail->import_tax_book;


                    // by function
                    $price_calculation = $getProductDefaultSupplier->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_supp->supplier_id, $getProductDefaultSupplier->buying_price, $getProductDefaultSupplier->freight, $getProductDefaultSupplier->landing, $getProductDefaultSupplier->extra_cost, $importTax, $extra_tax_value);

                    $newValues = Product::find($request->prod_detail_id);
                    $buying_p = $newValues->total_buy_unit_cost_price;
                    $selling_p = $newValues->selling_price;
                    $total_buying_price = $newValues->t_b_u_c_p_of_supplier;

                    $buying_p = number_format((float)$buying_p, 3, '.', '');
                    $selling_p = number_format((float)$selling_p, 3, '.', '');
                    $t_b_u_c_p_of_supplier = number_format((float)@$total_buying_price, 3, '.', '');
                    $reload = 1;
                    CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
                } else {
                    $product_supp->$key = $value;
                    $product_supp->save();
                }
            }
            if ($key == 'supplier_id') {
                if ($product_detail->supplier_id == 0 || $product_detail->supplier_id == NULL) {
                    $product_detail->supplier_id = $value;
                    $product_detail->last_price_updated_date = Carbon::now();
                    $product_detail->save();
                    $product_supp->$key = $value;
                    $reload = 1;
                } else {
                    $product_supp->$key = $value;
                    $reload = 0;
                }
            } else {
                $product_supp->$key = $value;
            }
        }
        $product_history = new ProductHistory;
        $product_history->user_id = Auth::user()->id;
        $product_history->product_id = $request->prod_detail_id;
        $product_history->column_name = $key;
        $product_history->old_value = $request->old_value;
        $product_history->new_value = $value;
        $product_history->save();

        $product_supp->save();

        $request_new = new \Illuminate\Http\Request();
        $request_new->replace(['id' => $request->prod_detail_id]);

        $mark_as_complete = $this->doProductCompleted($request_new);
        return response()->json(['success' => true, 'buying_p' => @$buying_p, 'selling_p' => @$selling_p, 'buying_p_of_supp' => @$t_b_u_c_p_of_supplier, 'reload' => $reload]);
    }

    public function purchaseAddBrand(Request $request)
    {
        $new_brand = new Brand;
        $new_brand->title = $request->brand_title;
        $new_brand->save();

        $recentAdded = Brand::find($new_brand->id);

        return response()->json(['success' => true, 'recentAdded' => $recentAdded]);
    }

    public function editProdMarginData(Request $request)
    {
        $product_margins = CustomerTypeProductMargin::find($request->id);
        $product_fixed_price = ProductFixedPrice::find($request->id);
        foreach ($request->except('prod_detail_id', 'id', 'old_value') as $key => $value) {
            if ($product_fixed_price) {
                $custCatName = $product_fixed_price->custType->title;
            } else {
                $custCatName = "";
            }
            $product_history = new ProductHistory;
            $product_history->user_id = Auth::user()->id;
            $product_history->product_id = $request->prod_detail_id;
            $product_history->column_name = $key . " - " . $custCatName;
            $product_history->old_value = $request->old_value;
            $product_history->new_value = $value;
            $product_history->save();

            if ($key == 'default_value') {
                $product_margins->$key = $value;
                $product_margins->last_updated = date('Y-m-d H:i:s');
                $product_margins->save();

                $updatedRow = CustomerTypeProductMargin::find($product_margins->id);
                $product = Product::find($request->prod_detail_id);
                $last_updated = '--';
                if ($updatedRow != null) {
                    if ($updatedRow->last_updated != null) {
                        $last_updated = Carbon::parse($updatedRow->last_updated)->format('d/m/Y H:i:s');
                    }
                }
                $updatedSellingPrice = $product->selling_price + ($product->selling_price * ($updatedRow->default_value / 100));
                $updatedSellingPrice = number_format((float)$updatedSellingPrice, 3, '.', '');

                $last_updated = $product->last_price_updated_date != null ? Carbon::parse($product->last_price_updated_date)->format('d/m/Y') : '--';

                return response()->json(['success' => true, 'updatedRow' => $updatedRow, 'selling_p' => $updatedSellingPrice, 'last_updated' => $last_updated]);
            }
            if ($key == 'product_fixed_price') {
                $product = Product::find($request->prod_detail_id);
                if ($product->fixed_price_check == 1) {
                    $get_product_fixed_prices = ProductFixedPrice::where('product_id', $product->id)->get();
                    foreach ($get_product_fixed_prices as $pf_data) {
                        $pf_data->fixed_price = $value;
                        $pf_data->fixed_price_update_date = Carbon::now();
                        $pf_data->save();
                    }
                    return response()->json(['success' => true, 'redirect' => 'redirect']);
                } else {
                    $product_fixed_price->fixed_price = $value;
                    $product_fixed_price->fixed_price_update_date = Carbon::now();
                    $product_fixed_price->save();
                }
            }
            if ($key == 'expiration_date') {
                if ($value == NULL) {
                    $product_fixed_price->expiration_date = $value;
                    $product_fixed_price->save();
                } else {
                    $value = str_replace("/", "-", $value);
                    $value =  date('Y-m-d', strtotime($value));
                    $product_fixed_price->expiration_date = $value;
                    $product_fixed_price->save();
                }
            }
        }
        return response()->json(['success' => true]);
    }

    public function editProdFixedPriceData(Request $request)
    {
        $ProductCustomerFixedPrice = ProductCustomerFixedPrice::find($request->id);

        foreach ($request->except('prod_detail_id', 'old_value') as $key => $value) {
            if ($key == 'expiration_date') {
                $value = str_replace("/", "-", $value);
                $value =  date('Y-m-d', strtotime($value));
                $ProductCustomerFixedPrice->$key = $value;
            } else if ($key == 'discount') {
                $ProductCustomerFixedPrice->discount = $value;
                $ProductCustomerFixedPrice->price_after_discount = $value != 0 ? $ProductCustomerFixedPrice->fixed_price - ($ProductCustomerFixedPrice->fixed_price * $ProductCustomerFixedPrice->discount) / 100 : $ProductCustomerFixedPrice->fixed_price;
            }
            else if ($key == 'fixed_price'){
                $ProductCustomerFixedPrice->$key = $value;
                $ProductCustomerFixedPrice->price_after_discount = $value - ($value * $ProductCustomerFixedPrice->discount) / 100;
            }
        }
        $product_history = new ProductHistory;
        $product_history->user_id = Auth::user()->id;
        $product_history->product_id = $ProductCustomerFixedPrice->product_id;
        $product_history->column_name = $key;
        $product_history->old_value = $request->old_value;
        $product_history->new_value = $value;
        $product_history->save();

        $ProductCustomerFixedPrice->save();
        $updatedRow = ProductCustomerFixedPrice::find($ProductCustomerFixedPrice->id);
        return response()->json(['success' => true, 'updatedRow' => $updatedRow]);
    }

    public function saveProdDataProdDetailPage(Request $request)
    {
        // dd($request->all());
        $allow_same_description = '';
        $globalAccessConfig2 = QuotationConfig::where('section', 'products_management_page')->first();
        // $globalaccessForConfig=[];
        if ($globalAccessConfig2) {
            $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "same_description") {
                    $allow_same_description = $val['status'];
                }
            }
        }

        $completed = 0;
        $reload = 0;
        $ecomCogs = 0;
        $product_detail = Product::find($request->prod_detail_id);

        foreach ($request->except('prod_detail_id', 'old_value') as $key => $value) {

            if ($key == 'primary_category') {
                $product_detail->$key = $value;
                $product_detail->category_id = 0;
            } elseif ($key == 'discount_expiry_date') {
                $value = str_replace("/", "-", $value);
                $value =  date('Y-m-d', strtotime($value));
                $product_detail->$key = $value;
            } elseif ($key == 'category_id') {
                $product_detail->$key = $value;
                $getParent = ProductCategory::find($value);
                $product_detail->primary_category = $getParent->parent_id;

                // again generating Refernce num of this product according to selected Category
                $prefix = $getParent->prefix;

                $c_p_ref = Product::where('category_id', $value)->orderBy('refrence_no', 'DESC')->first();

                if ($c_p_ref == NULL) {
                    $str = '0';
                } else {
                    $str = $c_p_ref->refrence_no;
                }

                $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);

                if ($product_detail->refrence_code == $product_detail->system_code) {
                    $product_detail->refrence_code = $prefix . $system_gen_no;
                    $product_detail->system_code = $prefix . $system_gen_no;
                } else {
                    $product_detail->system_code = $prefix . $system_gen_no;
                }

                $product_detail->refrence_no     = $system_gen_no;
                $product_detail->hs_code         = $getParent->hs_code;
                $product_detail->import_tax_book = $getParent->import_tax_book;
                $product_detail->vat             = $getParent->vat;
                // $product_detail->save();

                // price calculations starts here
                $total_buying_price = null;
                if ($product_detail->supplier_id != 0 || $product_detail->supplier_id !== NULL) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                    if ($getProductDefaultSupplier !== null) {
                        // this is the price conversion for THB
                        $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                        $buying_price_in_thb = ($getProductDefaultSupplier->buying_price_in_thb);

                        $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : $product_detail->import_tax_book;

                        $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($total_buying_price);

                        $product_detail->total_buy_unit_cost_price = $total_buying_price;

                        // this is supplier buying unit cost price
                        $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                        // this is selling price
                        $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                        $product_detail->selling_price = $total_selling_price;

                        $product_detail->last_price_updated_date = Carbon::now();
                    }
                }
                $reload = 1;
                CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
            } elseif ($key == 'unit_conversion_rate') {
                $product_detail->$key = $value;
                $product_detail->selling_price = $product_detail->total_buy_unit_cost_price * $value;
                $product_detail->last_price_updated_date = Carbon::now();
                // $reload = 1;
                CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
            } elseif ($key == 'supplier_id') {
                $total_buying_price = null;
                $getProductDefaultSupplier = SupplierProducts::where('product_id', @$product_detail->id)->where('supplier_id', @$value)->first();
                if ($getProductDefaultSupplier !== null) {
                    // this is the price conversion for THB
                    $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                    $buying_price_in_thb = ($getProductDefaultSupplier->buying_price_in_thb);

                    $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : $product_detail->import_tax_book;

                    $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                    $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($total_buying_price);

                    $product_detail->total_buy_unit_cost_price = $total_buying_price;

                    // this is supplier buying unit cost price
                    $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                    // this is selling price
                    $total_selling_price = $total_buying_price * $product_detail->unit_conversion_rate;

                    $product_detail->selling_price = $total_selling_price;
                    $product_detail->last_price_updated_date = Carbon::now();
                    // Help code ends here
                }
                $product_detail->$key = $value;
                $reload = 1;
                CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
            } elseif ($key == 'import_tax_book') {
                $total_buying_price = null;
                if ($product_detail->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$product_detail->id)->where('supplier_id', $product_detail->supplier_id)->first();
                    if ($getProductDefaultSupplier !== null) {
                        // this is the price conversion for THB
                        $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                        $buying_price_in_thb = ($getProductDefaultSupplier->buying_price_in_thb);

                        $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : $value;

                        $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($total_buying_price);

                        $product_detail->total_buy_unit_cost_price = $total_buying_price;

                        // this is supplier buying unit cost price
                        $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                        // this is selling price
                        $total_selling_price = $total_buying_price * $product_detail->unit_conversion_rate;

                        $product_detail->selling_price = $total_selling_price;
                        $product_detail->last_price_updated_date = Carbon::now();
                        // Help code ends here
                    }
                    $product_detail->$key = $value;
                    $reload = 1;
                    CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->update(['last_updated' => date('Y-m-d H:i:s')]);
                } else {
                    $product_detail->$key = $value;
                }
            } elseif ($key == 'customer_type_id') {
                $variable = CustomerTypeProductMargin::where('product_id', $request->prod_detail_id)->where('customer_type_id', $request->customer_type_id)->first();
                $variable->is_mkt = $variable->is_mkt == 1 ? 0 : 1;
                $variable->save();
            } elseif ($key == 'refrence_code') {
                if ($value == null) {
                    $product_detail->$key = $product_detail->system_code;
                } else {
                    $check_prod = Product::where('refrence_code', $value)->orWhere('system_code', $value)->first();
                    if ($check_prod !== null) {
                        return response()->json(['find' => true, 'prod_code' => $product_detail->refrence_code]);
                    } else {
                        $product_detail->$key = $value;
                    }
                }
            } elseif ($key == 'short_desc') {
                if ($allow_same_description === 0) {
                    $same_product = Product::where('short_desc', $value)->where('id', '!=', $product_detail->id)->first();
                    if ($same_product != null) {
                        $reload = 1;
                        return response()->json(['find' => 'short_desc', 'completed' => 0]);
                    } else {
                        $product_detail->$key = $value;
                        // $reload = 1;
                    }
                } else {
                    $product_detail->$key = $value;
                    // $reload = 1;
                }
            } elseif ($key == 'selling_unit_conversion_rate') {
                $product_detail->$key = $value;
            } else {
                $product_detail->$key = $value;
            }

            $product_history = new ProductHistory;
            $product_history->user_id = Auth::user()->id;
            $product_history->product_id = $product_detail->id;
            $product_history->column_name = $key;
            $product_history->old_value = $request->old_value;
            $product_history->new_value = $value;
            $product_history->save();
        }

        $product_detail->save();

        if ($product_detail->status == 0) {
            $request->id = $request->prod_detail_id;
            $mark_as_complete = $this->doProductCompleted($request);
            $json_response = json_decode($mark_as_complete->getContent());
            if ($json_response->success == true) {
                $prod_complete = Product::find($request->id);
                $prod_complete->status = 1;
                $prod_complete->save();

                // checking if this product comes from sales (inquiry product)
                if ($prod_complete->order_product_id != NULL) {
                    // getting order && order product
                    $order_product = OrderProduct::find($prod_complete->order_product_id);
                    $order = Order::find($order_product->order_id);

                    // calculations
                    $exp_unit_cost = $prod_complete->selling_price;
                    $price_calculate_return = $prod_complete->price_calculate($prod_complete, $order);
                    $unit_price = $price_calculate_return[0];
                    $marginValue = $price_calculate_return[1];
                    $total_price_with_vat = (($prod_complete->vat / 100) * $unit_price) + $unit_price;

                    // order product data saving
                    $order_product->supplier_id   = $prod_complete->supplier_id;
                    $order_product->is_billed     = "Product";
                    $order_product->exp_unit_cost = $exp_unit_cost;
                    $order_product->margin        = $marginValue;
                    $order_product->unit_price    = $unit_price;
                    $order_product->total_price   = $unit_price;
                    $order_product->total_price_with_vat   = $total_price_with_vat;
                    $order_product->save();

                    // now saving data in order table
                    $order->total_amount += $total_price_with_vat;
                    $order->save();
                }

                $completed = 1;
                $reload = 1;
            }
        }


        $updatedRow = Product::find($product_detail->id);
        $ecomCogs = 0;
        $ecomCogs = number_format($updatedRow->selling_unit_conversion_rate * $updatedRow->selling_price, 3, '.', ',');
        $unit_conversion_rate = number_format((float)@$updatedRow->unit_conversion_rate, 5, '.', '');
        $selling_price = number_format((float)@$updatedRow->selling_price, 3, '.', '');
        $total_buy_unit_cost_price = number_format((float)@$updatedRow->total_buy_unit_cost_price, 3, '.', '');
        $t_b_u_c_p_of_supplier = number_format((float)@$updatedRow->t_b_u_c_p_of_supplier, 3, '.', '');
        $prod_code = $product_detail->refrence_code;
        return response()->json(['success' => true, 'completed' => $completed, 'reload' => $reload, 'product' => $updatedRow, 'unit_cr' => $unit_conversion_rate, 'selling_p' => $selling_price, 'buying_p' => $total_buy_unit_cost_price, 'buying_p_of_supp' => $t_b_u_c_p_of_supplier, 'prod_code' => $prod_code, 'ecom_conversion_rate_updated' => $updatedRow->ecommr_conversion_rate, 'ecomCogs' => $ecomCogs]);
    }


    public function getProductHistory(Request $request)
    {

        $query = ProductHistory::where('product_id', $request->product_id)->orderBy('id', 'DESC')->get();
        // dd($query);
        return Datatables::of($query)
            ->addColumn('user_name', function ($item) {
                return @$item->user_id != null ? $item->user->name : '--';
            })
            ->addColumn('updated_from', function ($item) {
                return @$item->group != null ? '<a href="' . route('importing-completed-receiving-queue-detail', ['id' => $item->group->id]) . '" class="font-weight-bold">Group ' . $item->group->ref_id . '</a>' : '--';
            })

            ->addColumn('column_name', function ($item) {
                return @$item->column_name != null ? ucwords(str_replace('_', ' ', $item->column_name)) : '--';
            })

            ->addColumn('old_value', function ($item) {
                if (@$item->column_name == 'category_id') {
                    return @$item->old_value != null ? $item->old_productSubCategory->title : '--';
                } else if (@$item->column_name == 'type_id') {
                    return @$item->old_value != null ? $item->oldProductType->title : '--';
                } else if (@$item->column_name == 'ecom_product') {
                    return @$item->old_value != null ? @$item->old_value == 0 ? 'Disabled' : 'Enabled' : '--';
                } else if (@$item->column_name == 'buying_unit' || @$item->column_name == 'selling_unit' || @$item->column_name == 'stock_unit') {
                    return @$item->old_value != null ? $item->oldUnits->title : '--';
                } else if (@$item->column_name == 'supplier_id') {
                    if ($item->old_value == 0) {
                        return "--";
                    } else {
                        return @$item->old_value != null ? $item->old_def_or_last_supplier->reference_name : '--';
                    }
                }

                return @$item->old_value != null ? $item->old_value : '--';
            })

            ->addColumn('new_value', function ($item) {
                if (@$item->column_name == 'supplier_id') {
                    return @$item->new_value != null ? $item->def_or_last_supplier->reference_name : '--';
                } else if (@$item->column_name == 'category_id') {
                    return @$item->new_value != null ? $item->new_productSubCategory->title : '--';
                } else if (@$item->column_name == 'type_id') {
                    return @$item->new_value != null ? $item->newProductType->title : '--';
                } else if (@$item->column_name == 'ecom_product') {
                    return @$item->new_value != null ? @$item->new_value == 0 ? 'Disabled' : 'Enabled' : '--';
                } else if (@$item->column_name == 'buying_unit' || @$item->column_name == 'selling_unit' || @$item->column_name == 'stock_unit') {
                    return @$item->new_value != null ? $item->newUnits->title : '--';
                }

                return @$item->new_value != null ? $item->new_value : '--';
            })
            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? $item->created_at->format('d/m/Y') : '--';
            })
            // ->setRowId(function ($item) {
            //   return $item->id;
            // })

            ->rawColumns(['user_name', 'item', 'column_name', 'old_value', 'new_value', 'created_at', 'updated_from'])
            ->make(true);
    }

    public function saveProdDataIncomplete(Request $request)
    {
        // dd($request->all());
        $allow_same_description = '';
        $globalAccessConfig2 = QuotationConfig::where('section', 'products_management_page')->first();
        // $globalaccessForConfig=[];
        if ($globalAccessConfig2) {
            $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "same_description") {
                    $allow_same_description = $val['status'];
                }
            }
        }
        // dd($request->all());
        $completed = 0;
        $reload = 0;
        $dont_run = 0;
        $product_detail = Product::find($request->prod_detail_id);
        $prod_cat = ProductFixedPrice::find($request->prod_detail_id);
        foreach ($request->except('prod_detail_id', 'old_value') as $key => $value) {
            if ($key == 'product_fixed_price') {
                // $product_fixed_price = ProductFixedPrice::where('product_id', $request->prod_detail_id)->where('customer_type_id', 1)->first();
                // $product_fixed_price->fixed_price = $value;
                // $product_fixed_price->save();

                $prod_cat->fixed_price = $value;
                $prod_cat->save();

                $prod = Product::find($prod_cat->product_id);

                $var_title = @$prod_cat->custType->title . " Fixed Price";

                if ($prod->fixed_price_check == 1) {
                    $var_title = "Fixed Price Of All Categories";

                    $get_product_fixed_prices = ProductFixedPrice::where('product_id', $prod->id)->get();
                    foreach ($get_product_fixed_prices as $pf_data) {
                        $pf_data->fixed_price = $value;
                        $pf_data->fixed_price_update_date = Carbon::now();
                        $pf_data->save();
                    }
                }

                $product_history = new ProductHistory;
                $product_history->user_id = Auth::user()->id;
                $product_history->product_id = $prod->id;
                $product_history->column_name = $var_title;
                $product_history->old_value = $request->old_value;
                $product_history->new_value = $value;
                $product_history->save();

                // $reload = 1;
                // return response()->json(['completed' => $completed,'reload' => $reload]);

                return response()->json(['fixed_price' => true]);
            }
            if ($key == 'primary_category') {
                $product_detail->$key = $value;
                $product_detail->category_id = 0;
            }
            if ($key == 'category_id') {
                $product_detail->$key = $value;
                $getParent = ProductCategory::find($value);
                $product_detail->primary_category = $getParent->parent_id;

                // again generating Refernce num of this product according to selected Category
                $prefix = $getParent->prefix;

                $c_p_ref = Product::where('category_id', $value)->orderBy('refrence_no', 'DESC')->first();
                // dd($c_p_ref);
                if ($c_p_ref == NULL) {
                    $str = '0';
                } else {
                    $str = $c_p_ref->refrence_no;
                }

                $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);

                if ($product_detail->refrence_code == $product_detail->system_code) {
                    $product_detail->refrence_code = $prefix . $system_gen_no;
                    $product_detail->system_code = $prefix . $system_gen_no;
                } else {
                    $product_detail->system_code = $prefix . $system_gen_no;
                }

                // $product_detail->refrence_code   = $prefix.$system_gen_no;
                $product_detail->refrence_no     = $system_gen_no;
                $product_detail->hs_code         = $getParent->hs_code;
                $product_detail->import_tax_book = $getParent->import_tax_book;
                $product_detail->vat             = $getParent->vat;
                // $product_detail->save();

                // price calculations starts here
                $total_buying_price = null;
                if ($product_detail->supplier_id != 0 || $product_detail->supplier_id !== NULL) {

                    $getProductDefaultSupplier = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                    if ($getProductDefaultSupplier !== null) {
                        // this is the price conversion for THB
                        $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                        $buying_price_in_thb = ($getProductDefaultSupplier->buying_price_in_thb);

                        $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : $product_detail->import_tax_book;

                        $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($total_buying_price);

                        $product_detail->total_buy_unit_cost_price = $total_buying_price;

                        // this is supplier buying unit cost price
                        $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                        // this is selling price
                        $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                        $product_detail->selling_price = $total_selling_price;
                    }
                }
                $reload = 1;
            } elseif ($key == 'unit_conversion_rate') {
                $product_detail->$key = $value;
                $product_detail->selling_price = $product_detail->total_buy_unit_cost_price * $value;
                // $reload = 1;
            } elseif ($key == 'supplier_id') {
                if ($product_detail->supplier_id === 0 || $product_detail->supplier_id === NULL) {
                    $product_detail->$key = $value;
                    $addNewSupplierProduct = new SupplierProducts;
                    $addNewSupplierProduct->supplier_id = $value;
                    $addNewSupplierProduct->product_id  = $request->prod_detail_id;
                    $addNewSupplierProduct->save();
                    $reload = 1;
                } else {
                    $total_buying_price = null;
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$product_detail->id)->where('supplier_id', @$value)->first();
                    if ($getProductDefaultSupplier !== null) {
                        // this is the price conversion for THB
                        $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                        $buying_price_in_thb = ($getProductDefaultSupplier->buying_price_in_thb);

                        $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : $product_detail->import_tax_book;

                        $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($total_buying_price);

                        $product_detail->total_buy_unit_cost_price = $total_buying_price;

                        // this is supplier buying unit cost price
                        $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                        // this is selling price
                        $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                        $product_detail->selling_price = $total_selling_price;
                    } else {
                        $addNewSupplierProduct = new SupplierProducts;
                        $addNewSupplierProduct->supplier_id = $value;
                        $addNewSupplierProduct->product_id  = $request->prod_detail_id;
                        $addNewSupplierProduct->save();
                    }
                    $product_detail->$key = $value;
                    $reload = 1;
                }
            } elseif ($key == 'import_tax_book') {
                $total_buying_price = null;
                if ($product_detail->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$product_detail->id)->where('supplier_id', $product_detail->supplier_id)->first();
                    if ($getProductDefaultSupplier !== null) {
                        // this is the price conversion for THB
                        $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                        $buying_price_in_thb = ($getProductDefaultSupplier->buying_price_in_thb);

                        $importTax = $getProductDefaultSupplier->import_tax_actual !== null ? $getProductDefaultSupplier->import_tax_actual : $value;

                        $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($total_buying_price);

                        $product_detail->total_buy_unit_cost_price = $total_buying_price;

                        // this is supplier buying unit cost price
                        $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                        // this is selling price
                        $total_selling_price = $total_buying_price * $product_detail->unit_conversion_rate;

                        $product_detail->selling_price = $total_selling_price;
                        // Help code ends here
                    }
                    $product_detail->$key = $value;
                    // $reload = 1;
                } else {
                    $product_detail->$key = $value;
                }
            } elseif ($key == 'import_tax_actual') {
                $total_buying_price = null;
                if ($product_detail->supplier_id != 0) {
                    $getProductDefaultSupplier = SupplierProducts::where('product_id', @$product_detail->id)->where('supplier_id', @$product_detail->supplier_id)->first();
                    if ($getProductDefaultSupplier !== null) {
                        // this is the price conversion for THB
                        $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;

                        $buying_price_in_thb = ($getProductDefaultSupplier->buying_price_in_thb);

                        $importTax = $value !== null ? $value : $product_detail->import_tax_book;

                        $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price = ($getProductDefaultSupplier->freight) + ($getProductDefaultSupplier->landing) + ($getProductDefaultSupplier->extra_cost) + ($getProductDefaultSupplier->extra_tax) + ($total_buying_price);

                        $product_detail->total_buy_unit_cost_price = $total_buying_price;

                        // this is supplier buying unit cost price
                        $product_detail->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;

                        // this is selling price
                        $total_selling_price = $product_detail->total_buy_unit_cost_price * $product_detail->unit_conversion_rate;

                        $product_detail->selling_price = $total_selling_price;
                    }
                }
                $product_detail->$key = $value;
                $reload = 1;
            } elseif ($key == 'supplier_description') {
                $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                $getSupProdData->$key = $value;
                $getSupProdData->save();
                // $reload = 1;
            } elseif ($key == 'buying_price') {
                $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                $getSupProdData->$key = $value;
                $supplier_conv_rate_thb = $getSupProdData->supplier->getCurrency->conversion_rate;
                $getSupProdData->buying_price_in_thb = ($value / $supplier_conv_rate_thb);
                $getSupProdData->save();

                $importTax = $getSupProdData->import_tax_actual !== null  ? $getSupProdData->import_tax_actual : @$product_detail->import_tax_book;

                // by function
                $price_calculation = $getSupProdData->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_detail->supplier_id, $value, $getSupProdData->freight, $getSupProdData->landing, $getSupProdData->extra_cost, $importTax, $getSupProdData->extra_tax);
                // $reload = 1;
            } elseif ($key == 'freight') {
                $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                $getSupProdData->$key = $value;
                $getSupProdData->save();

                $importTax = $getSupProdData->import_tax_actual !== null  ? $getSupProdData->import_tax_actual : @$product_detail->import_tax_book;
                // by function
                $price_calculation = $getSupProdData->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_detail->supplier_id, $getSupProdData->buying_price, $value, $getSupProdData->landing, $getSupProdData->extra_cost, $importTax, $getSupProdData->extra_tax);
                // $reload = 1;
            } elseif ($key == 'landing') {
                $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                $getSupProdData->$key = $value;
                $getSupProdData->save();
                $importTax = $getSupProdData->import_tax_actual !== null  ? $getSupProdData->import_tax_actual : @$product_detail->import_tax_book;
                // by function
                $price_calculation = $getSupProdData->defaultSupplierProductPriceCalculation($request->prod_detail_id, $product_detail->supplier_id, $getSupProdData->buying_price, $getSupProdData->freight, $value, $getSupProdData->extra_cost, $importTax, $getSupProdData->extra_tax);
                // $reload = 1;
            } elseif ($key == 'leading_time') {
                $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                $getSupProdData->$key = $value;
                $getSupProdData->save();
                // $reload = 1;
            } elseif ($key == 'product_supplier_reference_no') {
                $getSupProdData = SupplierProducts::where('product_id', $request->prod_detail_id)->where('supplier_id', $product_detail->supplier_id)->first();
                $getSupProdData->$key = $value;
                $getSupProdData->save();
                // $reload = 1;
            } elseif ($key == 'short_desc') {
                if ($allow_same_description == 0) {
                    $same_product = Product::where('short_desc', $value)->where('id', '!=', $product_detail->id)->first();
                    if ($same_product) {
                        // $reload = 1;
                        $dont_run = 1;
                        return response()->json(['dont_run' => $dont_run, 'error' => 1, 'reload' => $reload]);
                    } else {
                        $product_detail->$key = $value;
                        // $reload = 1;
                    }
                } else {
                    $product_detail->$key = $value;
                    // $reload = 1;
                }
            } else {
                $product_detail->$key = $value;
            }

            $product_history = new ProductHistory;
            $product_history->user_id = Auth::user()->id;
            $product_history->product_id = $product_detail->id;
            $product_history->column_name = $key;
            $product_history->old_value = $request->old_value;
            $product_history->new_value = $value;
            $product_history->save();
        }

        $product_detail->created_by = Auth::user()->id;
        $product_detail->save();

        if ($product_detail->status == 0) {
            $request->id = $request->prod_detail_id;
            $mark_as_complete = $this->doProductCompleted($request);
            $json_response = json_decode($mark_as_complete->getContent());
            if ($json_response->success == true) {
                $prod_complete = Product::find($request->id);
                $prod_complete->status = 1;
                $prod_complete->save();

                // checking if this product comes from sales (inquiry product)
                if ($prod_complete->order_product_id != NULL) {
                    // getting order && order product
                    $order_product = OrderProduct::find($prod_complete->order_product_id);
                    $order = Order::find($order_product->order_id);

                    // calculations
                    $exp_unit_cost = $prod_complete->selling_price;
                    $price_calculate_return = $prod_complete->price_calculate($prod_complete, $order);
                    $unit_price = $price_calculate_return[0];
                    $marginValue = $price_calculate_return[1];
                    $total_price_with_vat = (($prod_complete->vat / 100) * $unit_price) + $unit_price;

                    // order product data saving
                    $order_product->supplier_id   = $prod_complete->supplier_id;
                    $order_product->is_billed     = "Product";
                    $order_product->exp_unit_cost = $exp_unit_cost;
                    $order_product->margin        = $marginValue;
                    $order_product->unit_price    = $unit_price;
                    $order_product->total_price   = $unit_price;
                    $order_product->total_price_with_vat   = $total_price_with_vat;
                    $order_product->save();

                    // now saving data in order table
                    $order->total_amount += $total_price_with_vat;
                    $order->save();
                }

                $completed = 1;
            }
        }
        return response()->json(['completed' => $completed, 'reload' => $reload, 'dont_run' => $dont_run]);
    }

    public function getSupplierExist(Request $request)
    {
        $html_string = '';
        $checkedIds = SupplierProducts::where('product_id', $request->prod_id)->pluck('supplier_id')->toArray();
        $getSuppliers = Supplier::whereNotIn('id', $checkedIds)->get();
        if ($getSuppliers->count() > 0) {
            $html_string .= '<option value="" selected="" disabled="">Select Supplier</option>';
            foreach ($getSuppliers as $supplier) {
                $html_string .= '<option value="' . $supplier->id . '">' . $supplier->company . '</option>';
            }
            return response()->json(['success' => true, 'html_string' => $html_string]);
        }
    }

    public function getCustFixedPriceData(Request $request)
    {
        $html_string = '';
        $checkedIds = ProductCustomerFixedPrice::where('product_id', $request->prod_id)->pluck('customer_id')->toArray();
        $getCustomers = Customer::where('status', 1)->whereNotIn('id', $checkedIds)->orderBy('reference_name')->get();
        if ($getCustomers->count() > 0) {
            $html_string .= '<option value="" selected="" disabled="">Select Customer</option>';
            foreach ($getCustomers as $cust) {
                $html_string .= '<option value="' . $cust->id . '">' . ($cust->reference_name != null ? $cust->reference_name : $cust->company) . '</option>';
            }
            return response()->json(['success' => true, 'html_string' => $html_string]);
        }
    }

    public function addProductSupplier(Request $request)
    {
        $reload = 0;
        // $validator = $request->validate([
        //     'supplier' => 'required|not_in:0',
        //     'buying_price' => 'required',
        //     'leading_time' => 'required',
        // ]);

        $productSupplier = new SupplierProducts;

        $productSupplier->supplier_id  = $request['supplier'];
        $productSupplier->product_id   = $request['product_id'];
        $productSupplier->product_supplier_reference_no   = $request->product_supplier_reference_no;
        $productSupplier->import_tax_actual   = $request->import_tax_actual;
        $productSupplier->gross_weight = $request['gross_weight'];
        $productSupplier->buying_price = $request['buying_price'];
        $productSupplier->extra_cost   = $request['extra_cost'];

        $productSupplier->leading_time = $request['leading_time'];
        $productSupplier->freight      = $request['freight'];
        $productSupplier->landing      = $request['landing'];
        $productSupplier->m_o_q        = $request['m_o_q'];
        $productSupplier->supplier_packaging = $request['supplier_packaging'];
        $productSupplier->billed_unit  = $request['billed_unit'];

        $productSupplier->save();

        // $getSupplier = SupplierProducts::find($productSupplier->id);
        // $supplier_conv_rate_thb = @$getSupplier->supplier->getCurrency->conversion_rate;
        // $getSupplier->buying_price_in_thb = ($getSupplier->buying_price / $supplier_conv_rate_thb);
        // $getSupplier->save();

        $checkDefSuppOfProduct = Product::find($request->product_id);
        if ($checkDefSuppOfProduct->supplier_id == 0) {
            if ($productSupplier->supplier_id !== null) {
                $checkDefSuppOfProduct->supplier_id = $productSupplier->supplier_id;
                $reload = 1;
            } else {
                $checkDefSuppOfProduct->supplier_id = 0;
                $reload = 0;
            }

            $checkDefSuppOfProduct->save();
        }

        $supplier = Supplier::where('id', $productSupplier->supplier_id)->first();

        return response()->json(['success' => true, 'supplier' => $supplier, 'reload' => $reload]);
    }

    public function addProductSupplierDropdown(Request $request)
    {
        $validator = $request->validate([
            'buying_price' => 'required',
            'leading_time' => 'required',
        ]);

        // if ($request->product_supplier_reference_no == null)
        // {
        //     $system_gen_no = 'PSR-' . $this->generateRandomString(4);
        //     $request->product_supplier_reference_no = $system_gen_no;
        // }

        $productSupplier = new SupplierProducts;

        $productSupplier->supplier_id  = $request['selected_supplier_id'];
        $productSupplier->product_id   = $request['product_id'];
        $productSupplier->product_supplier_reference_no   = $request->product_supplier_reference_no;
        $productSupplier->import_tax_actual   = $request->import_tax_actual;
        $productSupplier->gross_weight = $request['gross_weight'];
        $productSupplier->supplier_description = $request['supplier_description'];
        $productSupplier->buying_price = $request['buying_price'];
        $productSupplier->extra_cost   = $request['extra_cost'];

        $productSupplier->leading_time = $request['leading_time'];
        $productSupplier->freight      = $request['freight'];
        $productSupplier->landing      = $request['landing'];
        $productSupplier->m_o_q        = $request['m_o_q'];
        $productSupplier->supplier_packaging = $request['supplier_packaging'];
        $productSupplier->billed_unit  = $request['billed_unit'];

        $productSupplier->save();

        $getSupplier = SupplierProducts::find($productSupplier->id);
        $supplier_conv_rate_thb = @$getSupplier->supplier->getCurrency->conversion_rate;
        $getSupplier->buying_price_in_thb = ($getSupplier->buying_price / $supplier_conv_rate_thb);
        $getSupplier->save();

        $product = Product::find($request['product_id']);
        $product->supplier_id = $request['selected_supplier_id'];

        $importTax = $productSupplier->import_tax_actual ? $productSupplier->import_tax_actual : $product->import_tax_book;

        $total_buying_price = ($productSupplier->freight) + ($productSupplier->landing) + ($productSupplier->buying_price);

        $newTotalBuyingPrice = (($importTax) / 100) * $total_buying_price;

        $total_buying_price = $total_buying_price + $newTotalBuyingPrice;

        // this is the price of after conversion for THB
        $supplier_conv_rate_thb = @$getSupplier->supplier->getCurrency->conversion_rate;

        $product->total_buy_unit_cost_price = ($total_buying_price / $supplier_conv_rate_thb);

        //this is supplier buying unit cost price
        $product->t_b_u_c_p_of_supplier = $total_buying_price;

        // this is selling price
        $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;
        $product->selling_price = $total_selling_price;

        $product->save();

        $supplier = SupplierProducts::with('supplier.getcountry')->where('id', $productSupplier->id)->first();

        return response()->json(['success' => true, 'supplier' => $supplier]);
    }

    public function deleteProdSupplier(Request $request)
    {
        if ($request->rowId != NULL) {
            $getData = SupplierProducts::find($request->rowId);
        }

        $product = Product::select('id', 'supplier_id')->where('id', $getData->product_id)->first();
        if ($product != null) {
            if ($product->supplier_id == $getData->supplier_id) {
                return response()->json(['success' => false, 'msg' => 'Cannot delete default supplier!']);
            }
        }

        $po_detail = PurchaseOrderDetail::with('PurchaseOrder')->whereHas('PurchaseOrder', function ($q) use ($getData) {
            $q->where('supplier_id', $getData->supplier_id);
        })->where('product_id', $getData->product_id)->get();


        $current_date = date('Y-m-d');
        if ($po_detail->count() > 0) {
            $getData->is_deleted = 1;
            $getData->delete_date = $current_date;
            $getData->save();
            $msg = "Supplier Un-linked Successfully";
        } else {
            $delete = SupplierProducts::where('id', $request->rowId)->where('supplier_id', $request->prodSupId)->where('product_id', $request->pordId)->delete();
            $msg = "Supplier deleted Successfully";
        }

        return response()->json(['success' => true, 'msg' => $msg]);
    }

    public function checkSupplierProductExistInPo(Request $request)
    {
        if ($request->rowId != NULL) {
            $getData = SupplierProducts::find($request->rowId);
        }

        $po_detail = PurchaseOrderDetail::with('PurchaseOrder')->whereHas('PurchaseOrder', function ($q) use ($getData) {
            $q->where('supplier_id', $getData->supplier_id);
        })->where('product_id', $getData->product_id)->get();

        // $purchaseOrders = PurchaseOrder::where('supplier_id',$getData->supplier_id)->get();
        // foreach ($purchaseOrders as $po)
        // {
        //   $po_detail = PurchaseOrderDetail::where('po_id',$po->id)->where('product_id',$getData->product_id)->first();
        // }
        if ($po_detail->count() > 0) {
            return response()->json(['success' => false]);
        } else {
            return response()->json(['success' => true]);
        }
    }

    public function deleteProdFixedPrice(Request $request)
    {
        $delete = ProductCustomerFixedPrice::find($request->id)->delete();

        return response()->json(['success' => true]);
    }

    public function deleteSupplier(Request $request)
    {
        $delete = Supplier::where('id', $request->id)->delete();

        return response()->json(['success' => true]);
    }

    public function deleteProdData(Request $request)
    {
        // $deleteProduct = Product::where('id', $request->id)->delete();
        $deleteProduct = Product::find($request->id);
        if($deleteProduct && $deleteProduct->status == 0)
        {
            $deleteProduct->delete();
            $deleteProductSupplier = SupplierProducts::where('product_id', $request->id)->delete();

            $delCustProdMargins = CustomerTypeProductMargin::where('product_id', $request->id)->delete();

            $deleteProdFixedPrice = ProductFixedPrice::where('product_id', $request->id)->delete();

            $deleteProdImages = ProductImage::where('product_id', $request->id)->delete();

            $deleteProdCustFixedPrice = ProductCustomerFixedPrice::where('product_id', $request->id)->delete();

            $warehouse_product = WarehouseProduct::where('product_id', $request->id)->delete();

            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);

    }

    public function deactivateProducts(Request $request)
    {
        if (isset($request->selected_products)) {
            $multi_products = explode(',', $request->selected_products);
            for ($i = 0; $i < sizeof($multi_products); $i++) {
                $product = Product::find($multi_products[$i]);
                if ($product) {
                    // check if this product exist in any order
                    $order_product = OrderProduct::where('product_id', $product->id)->get();
                    // if($order_product->count() > 0)
                    // {
                    //     foreach ($order_product as $order)
                    //     {
                    //             $product->status = 2;
                    //             $product->save();
                    //     }
                    // }
                    // else
                    // {
                    $product->status = 2;
                    $product->save();
                    $product_history = new ProductHistory;
                    $product_history->user_id = Auth::user()->id;
                    $product_history->product_id = $product->id;
                    $product_history->column_name = 'Product Deactivated !!!';
                    $product_history->old_value = 'Active';
                    $product_history->new_value = 'Deactivated';
                    $product_history->save();
                    // }
                }
            }

            return response()->json(['success' => true]);
        }
    }

    public function ecommerceproductsenabled(Request $request)
    {
        $base_link  = config('app.ecom_url');
        if (isset($request->selected_products)) {
            $multi_products = explode(',', $request->selected_products);
            for ($i = 0; $i < count($multi_products); $i++) {
                $product = Product::find($multi_products[$i]);
                if ($product) {
                    $product_history = new ProductHistory;
                    $product_history->user_id = Auth::user()->id;
                    $product_history->product_id = $product->id;
                    $product_history->column_name = 'ecom_product';
                    $product_history->old_value = 0;
                    $product_history->new_value = 1;
                    $product_history->save();

                    $product->ecommerce_enabled            = 1;
                    if ($product->selling_unit_conversion_rate == NULL) {
                        $product->selling_unit_conversion_rate = $product->unit_conversion_rate;
                    }
                    if ($product->ecom_selling_unit == NULL) {
                        $product->ecom_selling_unit            = $product->selling_unit;
                    }
                    if ($product->name == NULL) {
                        $product->name = $product->short_desc;
                    }
                    if ($product->min_o_qty == null) {
                        $product->min_o_qty = 1;
                    }
                    if ($product->discount == null) {
                        $product->discount = 0;
                    }
                    $product->save();
                    $enabled = 1;
                    $uri  = $base_link . "/api/enable-disable-product/" . $product->id . "/" . $enabled;
                    $test = $this->sendRequest($uri);
                }
            }
            return response()->json(['success' => true]);
        }
    }

    public function woocommerceproductsenabled(Request $request)
    {
        //to add product to wordpress
        //check deployment is enabled or not
        // dd($request->all());
        $p_ids = explode(',', $request->selected_products);
        foreach ($p_ids as $p_id) {
            $product = Product::find($p_id);
            $deployment = Deployment::where('status', 1)->first();
            if ($deployment != null) {
                $enabled_image = $product->ecomProuctImage;
                $first_image = $product->productImages;

                if ($enabled_image) {
                    $full_path = config('app.url') . '/public/uploads/products/product_' . $product->id . '/' . $enabled_image->image;
                } else if ($first_image) {
                    $full_path = config('app.url') . '/public/uploads/products/product_' . $product->id . '/' . $first_image->first()->image;
                } else {
                    $full_path = config('app.url') . '/public/uploads/Product-Image-Coming-Soon.png';
                }

                $prices_error = [];
                $name = $product->name != null ? $product->name : $product->short_desc;
                $short_desc = $product->short_desc;
                $long_desc = $product->long_desc;
                // $deployment = Deployment::where('status', 1)->first();

                $fixed_price = $product->getDataOfProductMargins($product->id, $deployment->customerCategory->id, "prodFixPrice");

                $ref_price = $product->getDataOfProductMargins($product->id, $deployment->customerCategory->id, "prodRefPrice");
                // dd($fixed_price);
                if ($fixed_price != null) {
                    $price = $fixed_price->fixed_price;
                } else if ($ref_price != null || $ref_price == 0) {
                    $price = $ref_price;
                } else {
                    array_push($prices_error, 'Price is not set for Product: ' . $product->refrence_code);
                    continue;
                }
                $stock = WarehouseProduct::where('product_id', $product->id)->where('warehouse_id', $deployment->woocom_warehouse_id)->first();
                if ($stock != null) {
                    $stock_quantity = number_format($stock->available_quantity, 4, '.', '');
                } else {
                    $stock_quantity = 0;
                }

                if ($stock_quantity < 0) {
                    $stock_quantity = 0;
                }

                // dd($stock_quantity);

                $cat = WebEcomProductCategory::where('web_category_id', $product->primary_category)->orderBy('id', 'desc')->first();
                if ($cat == null) {
                    $new_category = (new WebEcomProductCategory)->addCat($product->primary_category);
                    $new_category = (new WebEcomProductCategory)->addCat($product->category_id);
                }
                $cat = WebEcomProductCategory::where('web_category_id', $product->primary_category)->orderBy('id', 'desc')->first();
                if ($cat != null) {
                    $ecom_cat = $cat->ecom_category_id;

                    $data       = [
                        'name'          => $name,
                        'short_description'   => $short_desc,
                        'description'       => $long_desc,
                        'regular_price' => $price,
                        'sale_price'    => $price, // 50% off
                        'stock_quantity'    => $stock_quantity,
                        'categories' => [
                            [
                                'id' => $ecom_cat
                            ],
                        ],
                        'images' => [
                            [
                                'src' => $full_path
                            ]
                        ],
                    ];

                    $check_ecom_prod = EcomProduct::where('web_product_id', $product->id)->first();
                    try {

                        $check_product = \Codexshaper\WooCommerce\Facades\Product::find(@$check_ecom_prod->ecom_product_id);
                        $exist = true;
                    } catch (\Exception $e) {
                        $exist = false;
                    }
                    // dd($check_ecom_prod->ecom_product_id);
                    if ($check_ecom_prod == null || !$exist) {
                        $product_ecom = \Codexshaper\WooCommerce\Facades\Product::create($data);
                        if ($check_ecom_prod == null) {
                            $ecom_prod = new EcomProduct;
                            $ecom_prod->web_product_id = $product->id;
                            $ecom_prod->ecom_product_id = $product_ecom['id'];
                            // $ecom_prod->type = 'wordpress';
                            $ecom_prod->save();
                        }
                    } else {
                        // dd('else');
                        $data       = [
                            'regular_price' => $price,
                            'sale_price'    => $price, // 50% off
                            'stock_quantity'    => $stock_quantity,
                            'categories' => [
                                [
                                    'id' => $ecom_cat
                                ],
                            ],
                            'images' => [
                                [
                                    'src' => $full_path
                                ]
                            ],
                        ];

                        $product_ecom = \Codexshaper\WooCommerce\Facades\Product::update(@$check_ecom_prod->ecom_product_id, $data);
                    }
                }
            }
        }
        if (count($prices_error) > 0) {
            return response()->json(['success' => true, 'msg' => 'Products Enabled Successfully To Ecommerce side. Some products prices are not set. Please Set prices for these products below', 'errors' => $prices_error]);
        }
        return response()->json(['success' => true, 'msg' => 'Products Enabled Successfully To Ecommerce side!']);
    }

    public function ecommerceProductDisabled(Request $request)
    {
        $base_link  = config('app.ecom_url');
        $ordered_products = '';
        $ids = array();
        $j = 0;
        if (isset($request->selected_products)) {
            $multi_products = explode(',', $request->selected_products);
            if (sizeof($multi_products) <= 100) {
                for ($i = 0; $i < sizeof($multi_products); $i++) {
                    $product = Product::find($multi_products[$i]);
                    if ($product) {
                        // check if this product is enabled or not
                        $order_product = OrderProduct::where('product_id', $product->id)->get();
                        if ($product->ecommerce_enabled == 1) {
                            $product_history = new ProductHistory;
                            $product_history->user_id = Auth::user()->id;
                            $product_history->product_id = $product->id;
                            $product_history->column_name = 'ecom_product';
                            $product_history->old_value = 1;
                            $product_history->new_value = 0;
                            $product_history->save();

                            $product->ecommerce_enabled = 0;
                            $product->save();

                            $enabled = 0;
                            $uri  = $base_link . "/api/enable-disable-product/" . $product->id . "/" . $enabled;
                            $test = $this->sendRequest($uri);
                        }
                    }
                }

                if ($j == 0) {
                    return response()->json(['success' => true]);
                } else {
                    $msg = $ordered_products . " These products cannot be disabled because it already exists in order(s).";
                    return response()->json(['success' => false, 'msg' => $msg, 'ids' => $ids]);
                }
            } else {
                return response()->json(['error' => 1]);
            }
        }
    }

    public function sendRequest($uri)
    {
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function activateProducts(Request $request)
    {
        if (isset($request->selected_products)) {
            $multi_products = explode(',', $request->selected_products);
            for ($i = 0; $i < sizeof($multi_products); $i++) {
                $product = Product::find($multi_products[$i]);
                if ($product) {
                    // check if this product exist in any order
                    $order_product = OrderProduct::where('product_id', $product->id)->get();
                    // if($order_product->count() > 0)
                    // {
                    //     foreach ($order_product as $order)
                    //     {
                    //         // dd($order->get_order);
                    //         // if($order->get_order->primary_status == 3 && $order->get_order->status == 11)
                    //         // {
                    //             $product->status = 1;
                    //             $product->save();
                    //         // }
                    //         // else
                    //         // {
                    //         //     $msg = "This Product cannot be deactivate, ".$product->refrence_code." exist in the Orders.";
                    //         //     return response()->json(['success' => false, 'msg' => $msg]);
                    //         // }
                    //     }
                    // }
                    // else
                    // {
                    $product->status = 1;
                    $product->save();
                    $product_history = new ProductHistory;
                    $product_history->user_id = Auth::user()->id;
                    $product_history->product_id = $product->id;
                    $product_history->column_name = 'Product Activated !!!';
                    $product_history->old_value = 'Deactive';
                    $product_history->new_value = "Activated";
                    $product_history->save();
                    // }
                }
            }

            return response()->json(['success' => true]);
        }
    }

    public function deleteProducts(Request $request)
    {
        //dd(Auth::user()->id);
        $ordered_products = '';
        $j = 0;
        if (isset($request->selected_products)) {
            $multi_products = explode(',', $request->selected_products);
            // dd($multi_products);
            if (sizeof($multi_products) <= 100) {

                for ($i = 0; $i < sizeof($multi_products); $i++) {
                    $product = Product::find($multi_products[$i]);
                    if ($product) {
                        // check if this product is enabled or not
                        if ($product->ecommerce_enabled == 1) {
                            //dd($product->id);
                            $product_history = new ProductHistory;
                            $product_history->user_id = Auth::user()->id;
                            $product_history->product_id = $product->id;
                            $product_history->column_name = 'ecom_product';
                            $product_history->old_value = 1;
                            $product_history->new_value = 0;
                            $product_history->save();

                            $product->ecommerce_enabled = 0;
                            $product->save();
                        } else {
                            $order_product = OrderProduct::where('product_id', $product->id)->get();
                            if ($order_product->count() > 0) {
                                // dd($order_product);
                                $ordered_products .= ' , ' . $product->refrence_code;
                                $j = 1;
                            } else {
                                // Delete product
                                Product::where('id', $product->id)->delete();
                                $product_images = ProductImage::where('product_id', $product->id)->get();
                                if ($product_images->count() > 0) {
                                    foreach ($product_images as $image) {
                                        $directory  = public_path() . '/uploads/products/product_' . $product->id . '/' . $image->name;
                                        //remove main
                                        $this->removeFile($directory, $image->image);
                                        // delete record
                                        $image->delete();
                                    }
                                }

                                $product_warehouses = WarehouseProduct::where('product_id', $product->id)->get();
                                if ($product_warehouses->count() > 0) {
                                    foreach ($product_warehouses as $product_warehouse) {
                                        $product_warehouse->delete();
                                    }
                                }

                                $product_customer_fixed_price = ProductCustomerFixedPrice::where('product_id', $product->id)->get();
                                if ($product_customer_fixed_price->count() > 0) {
                                    foreach ($product_customer_fixed_price as $product_fix) {
                                        $product_fix->delete();
                                    }
                                }

                                $product_Fixed_Prices = ProductFixedPrice::where('product_id', $product->id)->get();
                                if ($product_Fixed_Prices->count() > 0) {
                                    foreach ($product_Fixed_Prices as $product_Fixed_Price) {
                                        $product_Fixed_Price->delete();
                                    }
                                }

                                $product_margin = CustomerTypeProductMargin::where('product_id', $product->id)->get();
                                if ($product_margin->count() > 0) {
                                    foreach ($product_margin as $product) {
                                        $product->delete();
                                    }
                                }

                                $product_suppliers = SupplierProducts::where('product_id', $product->id)->get();
                                if ($product_suppliers->count() > 0) {
                                    foreach ($product_suppliers as $product_supplier) {
                                        $product_supplier->delete();
                                    }
                                }
                            }
                        }
                    }
                }

                if ($j == 0) {
                    return response()->json(['success' => true]);
                } else {
                    $msg = "These Products cannot be deleted " . $ordered_products . " exist in the Orders.";
                    return response()->json(['success' => false, 'msg' => $msg]);
                }
            } else {
                return response()->json(['error' => 1]);
            }
        }
    }

    public function removeMultipleProducts(Request $request)
    {

        if (isset($request->selected_products)) {
            $multi_products = explode(',', $request->selected_products);
            for ($i = 0; $i < sizeof($multi_products); $i++) {
                $product = Product::find($multi_products[$i]);
                if ($product) {
                    // Delete project
                    $delete_product = Product::where('id', $product->id)->delete();

                    $product_images = ProductImage::where('product_id', $product->id)->get();
                    if ($product_images->count() > 0) {
                        foreach ($product_images as $image) {
                            $directory  = public_path() . '/uploads/products/product_' . $product->id . '/' . $image->name;
                            //remove main
                            $this->removeFile($directory, $image->image);
                            // delete record
                            $image->delete();
                        }
                    }

                    $product_customer_fixed_price = ProductCustomerFixedPrice::where('product_id', $product->id)->get();
                    if ($product_customer_fixed_price->count() > 0) {
                        foreach ($product_customer_fixed_price as $product_fix) {
                            $product_fix->delete();
                        }
                    }

                    $product_warehouses = WarehouseProduct::where('product_id', $product->id)->get();
                    if ($product_warehouses->count() > 0) {
                        foreach ($product_warehouses as $product_warehouse) {
                            $product_warehouse->delete();
                        }
                    }


                    $product_Fixed_Prices = ProductFixedPrice::where('product_id', $product->id)->get();
                    if ($product_Fixed_Prices->count() > 0) {
                        foreach ($product_Fixed_Prices as $product_Fixed_Price) {
                            $product_Fixed_Price->delete();
                        }
                    }

                    $product_margin = CustomerTypeProductMargin::where('product_id', $product->id)->get();
                    if ($product_margin->count() > 0) {
                        foreach ($product_margin as $product) {
                            $product->delete();
                        }
                    }

                    $product_suppliers = SupplierProducts::where('product_id', $product->id)->get();
                    if ($product_suppliers->count() > 0) {
                        foreach ($product_suppliers as $product_supplier) {
                            $product_supplier->delete();
                        }
                    }
                }
            }
            return response()->json(['success' => true]);
        }
    }

    public function addProductMargin(Request $request)
    {
        $validator = $request->validate([
            'customer_type' => 'required',
            'default_margin' => 'required',
            'default_value' => 'required',
            'prod_expiry' => 'required'
        ]);

        $productMargin = new CustomerTypeProductMargin;

        $productMargin->product_id       = $request['product_id'];
        $productMargin->customer_type_id = $request['customer_type'];
        $productMargin->default_margin   = $request['default_margin'];
        $productMargin->default_value    = $request['default_value'];
        $productMargin->expiry           = $request['prod_expiry'];

        $productMargin->save();

        return response()->json(['success' => true]);
    }

    public function addProductCustomerFixedPrice(Request $request)
    {
        // dd($request->all());
        $validator = $request->validate([
            'customers' => 'required|not_in:0',
            'fixed_price' => 'required',
            // 'expiration_date' => 'required'
        ]);

        $expiration_date = str_replace("/", "-", $request['expiration_date']);
        $expiration_date =  date('Y-m-d', strtotime($expiration_date));

        $ProductCustomerFixedPrice = new ProductCustomerFixedPrice;

        $ProductCustomerFixedPrice->product_id      = $request['product_id'];
        $ProductCustomerFixedPrice->customer_id     = $request['customers'];
        $ProductCustomerFixedPrice->fixed_price     = $request['fixed_price'];
        $ProductCustomerFixedPrice->discount     = $request['discount'] != null ? $request['discount'] : 0;
        $ProductCustomerFixedPrice->price_after_discount     = $request['price_after_discount'] != null ? $request['price_after_discount'] : $request['fixed_price'];
        $ProductCustomerFixedPrice->expiration_date = $request['expiration_date'] != null ? $expiration_date : null;
        $ProductCustomerFixedPrice->save();

        return response()->json(['success' => true]);
    }

    public function getSalePriceForSelectedCustomer(Request $request)
    {
        $getCustomer = Customer::find($request->id);

        $ctpmargin = CustomerTypeProductMargin::where('product_id', $request->product_id)->where('customer_type_id', $getCustomer->category_id)->first();

        $product = Product::find($request->product_id);

        $salePrice = $product->selling_price + ($product->selling_price * ($ctpmargin->default_value / 100));
        $formated_value = number_format($salePrice, 3, '.', ',');

        return response()->json(['success' => true, 'value' => $formated_value]);
    }

    public function getProdSubCat(Request $request)
    {
        $subCategory = ProductCategory::where('parent_id', $request->cat_id)->get();
        return response()->json(['success' => true, 'data' => $subCategory]);
    }

    public function getProductsDropDowns(Request $request)
    {
        // dd($request->all());
        if (@$request->choice == 'type') {
            $product_type = ProductType::select('id', 'title')->get();
            $html_string = '<option value="" selected="" disabled="">Choose Product Type</option>';
            foreach ($product_type as $type) {
                $html_string .= '<option value="' . $type->id . '" ' . ($request->value == $type->id ? "selected" : "") . '>' . $type->title . '</option>';
            }

            return response()->json(['html' => $html_string, 'field' => 'type']);
        }
        if (@$request->choice == 'type_2') {
            $product_type = ProductSecondaryType::select('id', 'title')->orderBy('title', 'asc')->get();
            $html_string = '<option value="" selected="" disabled="">Choose Product Type</option>';
            foreach ($product_type as $type) {
                $html_string .= '<option value="' . $type->id . '" ' . ($request->value == $type->id ? "selected" : "") . '>' . $type->title . '</option>';
            }

            return response()->json(['html' => $html_string, 'field' => 'type_2']);
        }

        if (@$request->choice == 'buying_unit') {
            $units = Unit::select('id', 'title')->get();
            $html_string = '<option value="" selected="" disabled="">Choose Unit</option>';

            if ($request->value == null) {
                if ($units) {
                    foreach ($units as $unit) {
                        $html_string .= '<option  value="' . $unit->id . '"> ' . $unit->title . '</option>';
                    }
                }
            } else {
                if ($units) {
                    foreach ($units as $unit) {
                        $value = $unit->id == @$request->value ? 'selected' : "";
                        $html_string .= '<option ' . $value . ' value="' . $unit->id . '"> ' . $unit->title . '</option>';
                    }
                }
            }

            return response()->json(['html' => $html_string, 'field' => 'unit']);
        }

        if (@$request->choice == 'selling_unit') {
            $units = Unit::select('id', 'title')->get();
            $html_string = '<option value="" selected="" disabled="">Choose Unit</option>';

            if ($request->value == null) {
                if ($units) {
                    foreach ($units as $unit) {
                        $html_string .= '<option  value="' . $unit->id . '"> ' . $unit->title . '</option>';
                    }
                }
            } else {
                if ($units) {
                    foreach ($units as $unit) {
                        $value = $unit->id == @$request->value ? 'selected' : "";
                        $html_string .= '<option ' . $value . ' value="' . $unit->id . '"> ' . $unit->title . '</option>';
                    }
                }
            }

            return response()->json(['html' => $html_string, 'field' => 'selling_unit']);
        }

        if (@$request->choice == 'cat') {
            $product_parent_category = ProductCategory::select('id', 'title')->where('parent_id', 0)->orderBy('title')->get();
            // $product = Product::select('type_id')->where('id',$request->prod_id)->first();
            // dd($product);
            $html_string = '<option value="" selected="" disabled="">Choose Category</option>';
            if ($product_parent_category->count() > 0) {
                foreach ($product_parent_category as $pcat) {

                    $html_string .= '<optgroup label=' . $pcat->title . '>';
                    $subCat = ProductCategory::select('id', 'title')->where('parent_id', $pcat->id)->orderBy('title')->get();
                    foreach ($subCat as $scat) {
                        $html_string .= '<option ' . ($scat->id == $request->value ? 'selected' : '') . ' value="' . $scat->id . '">' . $scat->title . '</option>';
                    }
                    $html_string .= '</optgroup>';
                }
            }

            return response()->json(['html' => $html_string, 'field' => 'category_id']);
        }
    }

    public function getSupplierDropDowns(Request $request)
    {
        // dd($request->all());
        if ($request->type == 'supplier_id') {
            $getSuppliers = Supplier::where('status', 1)->orderBy('reference_name')->whereIn('id', SupplierProducts::select('supplier_id')->where('product_id', $request->prod_id)->where('supplier_id', '!=', null)->where('is_deleted', 0)->pluck('supplier_id'))->get();

            $getProduct = Product::find($request->prod_id);
            $html_string = '';
            $html_string = '<option value="">Choose Supplier</option>';
            if ($getSuppliers->count() > 0) {
                foreach ($getSuppliers as $sp) {
                    $html_string .= '<option value="' . $sp->id . '" ' . ($getProduct->supplier_id == $sp->id ? "selected" : "") . '>' . $sp->reference_name . '</option>';
                }
            }

            return response()->json(['success' => true, 'html' => $html_string]);
        }
    }

    public function updateSingleProductPrice(Request $request)
    {
        $product = Product::find($request->product_id);
        $updateSupplierProduct = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $product->supplier_id)->first();

        // updating buying price of supplier in THB
        $supplier_conv_rate_thb = @$updateSupplierProduct->supplier->getCurrency->conversion_rate;

        $updateSupplierProduct->buying_price_in_thb = ($updateSupplierProduct->buying_price / $supplier_conv_rate_thb);
        $updateSupplierProduct->save();

        $importTax = $updateSupplierProduct->import_tax_actual !== null  ? $updateSupplierProduct->import_tax_actual : @$product->import_tax_book;

        // passing values to function to update prices
        $price_update = $updateSupplierProduct->updateSingleProdctPrice($product->id, $product->supplier_id, $updateSupplierProduct->buying_price, $updateSupplierProduct->freight, $updateSupplierProduct->landing, $updateSupplierProduct->extra_cost, $importTax, $updateSupplierProduct->extra_tax);

        return response()->json(['success' => true]);
    }

    public function getiingIncorrectProductPrice(Request $request)
    {
        $incorrectPrice  = array();
        $count = 0;
        $total_buying_price = 0;

        $getProducts = Product::where('status', 1)->get();

        foreach ($getProducts as $product) {
            $count++;
            $updateSupplierProduct = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $product->supplier_id)->first();

            $importTax = $updateSupplierProduct->import_tax_actual !== null  ? $updateSupplierProduct->import_tax_actual : $product->import_tax_book;

            $buying_price_in_thb = $updateSupplierProduct->buying_price_in_thb;

            $total_buying_price = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

            $total_buying_price = ($updateSupplierProduct->freight) + ($updateSupplierProduct->landing) + ($updateSupplierProduct->extra_cost) + ($total_buying_price);

            // $total_selling_price = $total_buying_price * $product->unit_conversion_rate;
            // dd(number_format((float)$product->total_buy_unit_cost_price,3,'.',''), number_format((float)$total_buying_price,3,'.',''));

            if (number_format((float)$product->total_buy_unit_cost_price, 3, '.', '') == number_format((float)$total_buying_price, 3, '.', '')) {
                // do nothing
            } else {
                array_push($incorrectPrice, $product->id);
            }
        }

        // dd($count,$incorrectPrice);

    }

    public function updateBilledQty(Request $request)
    {
        $check = Product::select('id')->whereIn('status', [1, 2])->get();
        // $supplier_products = array();
        // $i = 1;
        foreach ($check as $val) {
            $supplier_pro = SupplierProducts::where('product_id', $val->id)->whereNull('billed_unit')->first();
            if (!empty($supplier_pro)) {
                $supplier_pro->billed_unit = 1;
                $supplier_pro->save();
            }
            // if(!empty($supplier_pro))
            // {
            //   array_push($supplier_products, $supplier_pro);
            // }
            // $i++;
        }
        return response()->json(['success' => true]);
        // dd(sizeof($supplier_products), $i);
    }

    public function saveAccountPayableData(Request $request)
    {
        // dd($request->all());
        $completed = 0;
        $po_order = PurchaseOrder::where('id', $request->cust_detail_id)->first();
        foreach ($request->except('cust_detail_id') as $key => $value) {
            if ($key == 'payment_exchange_rate') {
                if ($value == '') {
                    // $supp_detail->$key = null;
                } else {
                    if ($value == 0 || $value == "0") {
                        $exchange = 0;
                    } else {
                        $exchange = (1 / $value);
                    }
                    $po_order->$key = $exchange;
                }
            } elseif ($key == 'po_total_received') {
                // dd('total_payment');
                // dd($value , number_format($po_order->total,2,'.','') , $po_order);
                $po_order->payment_exchange_rate = 1 / ($value / number_format($po_order->total, 2, '.', ''));
            }
        }
        // $exc = (1 / $value);
        // PurchaseOrderDetail::where('po_id',$po_order->id)
        //             ->update(['currency_conversion_rate' => @$exc,'unit_price_in_thb' => $po_order->pod_unit_price * $value,'total_unit_price_in_thb'=>$po_order->pod_total_unit_price * $value,'pod_import_tax_book_price'=> $po_order->total_unit_price_in_thb * ($po_order->pod_import_tax_book / 100)]);

        // $orders = PurchaseOrderDetail::where('po_id',$po_order->id)->get();
        // foreach ($orders as $order) {
        //   $order->currency_conversion_rate = $exc;
        //   $order->unit_price_in_thb = $order->pod_unit_price * $value;
        //   $order->total_unit_price_in_thb = $order->pod_total_unit_price * $value;
        //   $order->pod_import_tax_book_price = $order->total_unit_price_in_thb * ($order->pod_import_tax_book / 100);
        //   $order->save();
        // }
        // $po_order->total_in_thb = $po_order->total * $value;
        $po_order->save();
        return response()->json(['success' => true]);
    }

    public function saveAccountPayableTranData(Request $request)
    {
        // dd($request->all());
        $completed = 0;
        $reload = 0;
        $order_transaction = PurchaseOrderTransaction::find($request->trans_id);
        $order = PurchaseOrder::find($order_transaction->po_id);
        // dd($order);
        foreach ($request->except('trans_id', 'old_value') as $key => $value) {
            $order->total_paid -= $request->old_value;
            $order->total_paid += $value;
            $order_transaction->$key = $value;

            $transaction_history = new PoTransactionHistory;
            $transaction_history->user_id = Auth::user()->id;
            $transaction_history->po_id = $order->id;
            $transaction_history->po_transaction_id = $order_transaction->id;
            $transaction_history->column_name = 'total_received';
            $transaction_history->old_value = $request->old_value;
            $transaction_history->new_value = $value;
            $transaction_history->save();
        }

        $order->save();
        $order_transaction->save();

        return response()->json(['success' => true]);
    }

    public function getPoTransactionHistory(Request $request)
    {

        $query = PoTransactionHistory::where('po_transaction_id', '!=', null)->orderBy('id', 'DESC')->get();
        // dd($query);
        return Datatables::of($query)
            ->addColumn('user_name', function ($item) {
                return @$item->user_id != null ? $item->user->name : '--';
            })

            ->addColumn('column_name', function ($item) {
                return @$item->column_name != null ? ucwords(str_replace('_', ' ', $item->column_name)) : '--';
            })

            ->addColumn('po_id', function ($item) {
                $item_id = $item->po_id !== null ? $item->po->ref_id : '--';

                $html_string = '
                 <a href="' . url('get-purchase-order-detail/' . $item->po->id) . '" title="View Detail"><b>' . $item_id . '</b></a>';

                return $html_string;
            })

            ->addColumn('old_value', function ($item) {


                return @$item->old_value != null ? $item->old_value : '--';
            })

            ->addColumn('new_value', function ($item) {


                return @$item->new_value != null ? $item->new_value : '--';
            })
            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? $item->created_at->format('d/m/Y') : '--';
            })
            // ->setRowId(function ($item) {
            //   return $item->id;
            // })

            ->rawColumns(['user_name', 'item', 'column_name', 'old_value', 'new_value', 'created_at', 'po_id'])
            ->make(true);
    }

    public function getPoTransactionDelHistory(Request $request)
    {

        $query = PoTransactionHistory::with('user', 'po')->where('po_transaction_id', null)->orderBy('id', 'DESC');
        // dd($query);
        return Datatables::of($query)
            ->addColumn('user_name', function ($item) {
                return @$item->user_id != null ? $item->user->name : '--';
            })

            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? $item->created_at->format('d/m/Y') : '--';
            })

            ->addColumn('payment_reference_no', function ($item) {
                return @$item->payment_reference_no != null ? $item->payment_reference_no : '--';
            })

            ->addColumn('po_id', function ($item) {
                $item_id = $item->po_id !== null ? $item->po->ref_id : '--';

                $html_string = '
                 <a href="' . url('get-purchase-order-detail/' . $item->po->id) . '" title="View Detail"><b>' . $item_id . '</b></a>';

                return $html_string;
            })

            ->addColumn('total_paid', function ($item) {
                return @$item->total_received != null ? $item->total_received : '--';
            })
            // ->setRowId(function ($item) {
            //   return $item->id;
            // })

            ->rawColumns(['user_name', 'created_at', 'po_id', 'total_paid', 'payment_reference_no'])
            ->make(true);
    }

    public function exportSoldProductReportStatus(Request $request)
    {
        $data = $request->all();
        $status = ExportStatus::where('type', 'sold_product_report')->first();
        //export job for supplier margin detail
        if ($request->from_supplier_margin_id != null) {
            if ($status == null) {
                $new = new ExportStatus();
                $new->user_id = Auth::user()->id;
                $new->type = 'sold_product_report';
                $new->status = 1;
                $new->save();
                SoldProductsSupplierMarginDetailExportJob::dispatch($data, Auth::user()->id, Auth::user()->role_id, Auth::user()->user_customers_secondary->pluck('customer_id')->toArray());
                return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
            } elseif ($status->status == 1) {
                return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
            } elseif ($status->status == 0 || $status->status == 2) {
                ExportStatus::where('type', 'sold_product_report')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
                SoldProductsSupplierMarginDetailExportJob::dispatch($data, Auth::user()->id, Auth::user()->role_id, Auth::user()->user_customers_secondary->pluck('customer_id')->toArray());
                return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
            }
        } else {
            if ($status == null) {
                $new = new ExportStatus();
                $new->user_id = Auth::user()->id;
                $new->type = 'sold_product_report';
                $new->status = 1;
                $new->save();
                SoldProductsExportJob::dispatch($data, Auth::user()->id, Auth::user()->role_id, Auth::user()->user_customers_secondary->pluck('customer_id')->toArray());
                return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
            } elseif ($status->status == 1) {
                return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
            } elseif ($status->status == 0 || $status->status == 2) {
                ExportStatus::where('type', 'sold_product_report')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
                SoldProductsExportJob::dispatch($data, Auth::user()->id, Auth::user()->role_id, Auth::user()->user_customers_secondary->pluck('customer_id')->toArray());
                return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
            }
        }
    }

    public function exportStockMovementReportStatus(Request $request)
    {
        $data = $request->all();
        $status = ExportStatus::where('type', 'stock_movement_report')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type = 'stock_movement_report';
            $new->status = 1;
            $new->save();
            StockMovementReportExportJob::dispatch($data, Auth::user()->id, Auth::user()->role_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'stock_movement_report')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            StockMovementReportExportJob::dispatch($data, Auth::user()->id, Auth::user()->role_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckSoldProduct()
    {
        $status = ExportStatus::where('type', 'sold_product_report')->first();

        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception]);
    }

    public function recursiveStatusCheckStockMovementReport()
    {
        $status = ExportStatus::where('type', 'stock_movement_report')->first();
        // dd($status);

        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception]);
    }

    public function checkStatusFirstTimeForSoldProducts()
    {
        $status = ExportStatus::where('type', 'sold_product_report')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function bulkProductUploadFileDownload(Request $request)
    {
        $customerCategory = CustomerCategory::where('is_deleted', 0)->get();
        \Excel::store(new BulkProducts($customerCategory), 'Bulk_Products.xlsx');
        return response()->json(['success' => true]);
    }

    public function enableEcommerce(Request $request)
    {
        $base_link  = config('app.ecom_url');
        $product = Product::find($request['product_id']);

        $product_history = new ProductHistory;
        $product_history->user_id = Auth::user()->id;
        $product_history->product_id = $request['product_id'];
        $product_history->column_name = 'ecom_product';
        $product_history->old_value = $product->ecommerce_enabled;
        $product_history->new_value = $request->ecommerce_enabled;
        $product_history->save();

        if (!$product->ecommerce_enabled) {
            $product->discount = $request['discount'];
            $product->min_o_qty = $request['MinOrder'];
        }

        $product->ecommerce_enabled = $request['ecommerce_enabled'];
        $product->featured_product = $request['featured_product'];

        if ($product->name == NULL) {
            $product->name = $product->short_desc;
        }
        $product->save();

        $enabled = @$request->ecommerce_enabled;
        $uri  = $base_link . "/api/enable-disable-product/" . $product->id . "/" . $enabled;
        $test = $this->sendRequest($uri);
        return response()->json(['success' => true]);
    }

    public function updateStockRecordCost(Request $request)
    {
        $stock = StockManagementOut::where('id', $request->id)->first();
        foreach ($request->except('id', 'new_select_value') as $key => $value) {
            $stock->$key = $value;
        }
        $stock->save();
        return response()->json(['success' => true]);
    }

    public function getsoldProdDataForReportTransfer(Request $request)
    {

        // $pids = PurchaseOrder::where('status',21)->pluck('id')->toArray();
        // $query =  PurchaseOrderDetail::whereIn('po_id',$pids);

        $query = PurchaseOrderDetail::whereNotNull('product_id')->whereHas('PurchaseOrder', function ($q) {
            $q->whereIn('status', [20,21]);
        });

        if ($request->product_id != '' && $request->product_id != null) {
            $query = $query->where('product_id', $request->product_id);
        }

        if ($request->warehouse_id != '' && $request->warehouse_id != null) {
            $query = $query->whereHas('PurchaseOrder', function ($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id);
            });
        }

        if ($request->p_c_id != "null" && $request->p_c_id != null) {
            // do something here
            $p_cat_id = ProductCategory::where('parent_id', $request->p_c_id)->pluck('id')->toArray();
            // dd($p_cat_id);
            $product_ids = Product::whereIn('category_id', $p_cat_id)->pluck('id');
            $query = $query->whereIn('purchase_order_details.product_id', $product_ids);
        } else {
            if ($request->prod_category != null) {
                $cat_id_split = explode('-', $request->prod_category);
                // dd($cat_id_split);
                if ($cat_id_split[0] == 'sub') {
                    // $filter_sub_categories = ProductCategory::where('parent_id','!=',0)->where('title',$request->prod_sub_category)->pluck('id')->toArray();
                    $product_ids = Product::select('id', 'category_id', 'status')->where('category_id', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $product_ids);
                } else {

                    $p_cat_ids = Product::select('id', 'primary_category', 'status')->where('primary_category', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $p_cat_ids);
                }
            }
            // if($request->product_id_select != null)
            // {
            //   $id_split = explode('-', $request->product_id_select);
            //   $id_split = (int)$id_split[1];

            //   if($request->className == 'parent'){
            //     $p_cat_ids = Product::where('primary_category', $id_split)->where('status',1)->pluck('id');
            //     $query = $query->whereIn('purchase_order_details.product_id',$p_cat_ids);
            //   }
            //   else if ($request->className == 'child') {
            //     $product_ids = Product::where('category_id', $id_split)->where('status',1)->pluck('id');
            //     $query = $query->whereIn('purchase_order_details.product_id',$product_ids);
            //   }
            //   else
            //   {
            //     $query = $query->where('product_id',$id_split);
            //   }

            // }
        }
        $query->with(['PurchaseOrder.PoWarehouse', 'PurchaseOrder.ToWarehouse', 'PurchaseOrder.p_o_statuses', 'product',
        'get_td_reserved' => function ($q){
            $q->whereNotNull('inbound_pod_id');
        }
        ]);
        return Datatables::of($query)

            ->addColumn('ref_id', function ($item) {

                $ref_no = 'TD' . $item->PurchaseOrder->ref_id;

                return $title = '<a target="_blank" href="' . route('get-purchase-order-detail', ['id' => @$item->PurchaseOrder->id]) . '" title="View Detail" class=""><b>' . $ref_no . '</b></a>';
            })
            ->addColumn('inbound_po', function ($item) {
                $html = '';
                // dd($item->get_td_reserved);
                foreach ($item->get_td_reserved as $td) {
                    $ref_no = 'PO' . $td->inbound_pod->PurchaseOrder->ref_id;
                    $html .= '<a target="_blank" href="' . route('get-purchase-order-detail', ['id' => $td->inbound_pod->PurchaseOrder->id]) . '" title="View Detail" class=""><b>' . $ref_no . '</b></a><br>';
                }

                return $html != '' ? $html : '--';

            })

            ->addColumn('quantity', function ($item) {
                return $item->quantity != null ? number_format($item->quantity, 2, '.', '') : 0;
            })


            ->addColumn('refrence_code', function ($item) {
                return $item->product != null ? $item->product->refrence_code : 0;
            })

            ->addColumn('from_warehouse', function ($item) {
                return $item->PurchaseOrder != null ? $item->PurchaseOrder->PoWarehouse->warehouse_title : 0;
            })

            ->addColumn('to_warehouse', function ($item) {
                return $item->PurchaseOrder != null ? $item->PurchaseOrder->ToWarehouse->warehouse_title : 0;
            })

            ->addColumn('status', function ($item) {
                $status = $item->PurchaseOrder != null ? $item->PurchaseOrder->p_o_statuses->title : 'N.A';
                return  $status;
            })

            ->addColumn('created_date', function ($item) {
                return $item->created_at != null ? Carbon::parse($item->created_at)->format('d/m/Y') : 'N.A';
            })

            ->addColumn('short_desc', function ($item) {
                return $item->product_id !== null ? $item->product->short_desc : 'N.A';
            })

            ->setRowId(function ($item) {
                return $item->id;
            })
            ->rawColumns(['ref_id', 'quantity', 'from_warehouse', 'to_warehouse', 'reference_code', 'status', 'created_date', 'inbound_po'])
            ->make(true);
    }

    public function getsoldProductReportTransferFooterValues(Request $request)
    {
        $query = PurchaseOrderDetail::whereNotNull('product_id')->whereHas('PurchaseOrder', function ($q) {
            $q->where('status', 21);
        });

        if ($request->product_id != '' && $request->product_id != null) {
            $query = $query->where('product_id', $request->product_id);
        }

        if ($request->warehouse_id != '' && $request->warehouse_id != null) {
            $query = $query->whereHas('PurchaseOrder', function ($q) use ($request) {
                $q->where('from_warehouse_id', $request->warehouse_id);
            });
        }

        if ($request->p_c_id != "null" && $request->p_c_id != null) {
            // do something here
            $p_cat_id = ProductCategory::where('parent_id', $request->p_c_id)->pluck('id')->toArray();
            // dd($p_cat_id);
            $product_ids = Product::whereIn('category_id', $p_cat_id)->pluck('id');
            $query = $query->whereIn('purchase_order_details.product_id', $product_ids);
        } else {
            if ($request->prod_category != null) {
                $cat_id_split = explode('-', $request->prod_category);
                // dd($cat_id_split);
                if ($cat_id_split[0] == 'sub') {
                    // $filter_sub_categories = ProductCategory::where('parent_id','!=',0)->where('title',$request->prod_sub_category)->pluck('id')->toArray();
                    $product_ids = Product::select('id', 'category_id', 'status')->where('category_id', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $product_ids);
                } else {

                    $p_cat_ids = Product::select('id', 'primary_category', 'status')->where('primary_category', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereIn('order_products.product_id', $p_cat_ids);
                }
            }
            // if($request->product_id_select != null)
            // {
            //   $id_split = explode('-', $request->product_id_select);
            //   $id_split = (int)$id_split[1];

            //   if($request->className == 'parent'){
            //     $p_cat_ids = Product::where('primary_category', $id_split)->where('status',1)->pluck('id');
            //     $query = $query->whereIn('purchase_order_details.product_id',$p_cat_ids);
            //   }
            //   else if ($request->className == 'child') {
            //     $product_ids = Product::where('category_id', $id_split)->where('status',1)->pluck('id');
            //     $query = $query->whereIn('purchase_order_details.product_id',$product_ids);
            //   }
            //   else
            //   {
            //     $query = $query->where('product_id',$id_split);
            //   }

            // }
        }

        $total_quantity_transfer = (clone $query)->sum('quantity');

        return response()->json(['total_quantity_transfer' => $total_quantity_transfer]);
    }
    public function purchaseFetchCustomer(Request $request)
    {
        // dd($request->get('query'));
        $query = $request->get('query');
        // dd($search_box_value);
        $params = $request->except('_token');
        $detail = [];
        $customer_query  = Customer::with('CustomerCategory')->select('id', 'reference_name', 'category_id')->where('status', 1);
        $category_query = CustomerCategory::query();
        if ($query) {
            $query = $request->get('query');
            $customer_query = $customer_query->where('reference_number', $query)->orWhere('reference_name', 'LIKE', '%' . $query . '%')->orderBy('category_id', 'ASC')->get();
            // $search_box_value = explode(' ', $query);

            // $customer_query = $customer_query->where(function($q) use($query){

            //             $q->where('reference_name', 'LIKE', '%'.$query.'%')->orderBy('category_id', 'ASC' )->get();

            //     })->orWhere('first_name','%'. 'LIKE', $query.'%')->get();
        }
        if ($query != null) {
            $category_query = $category_query->where('title', 'LIKE', '%' . $query . '%')->where('is_deleted', 0)->get();
        } else {
            $category_query = $category_query->where('is_deleted', 0)->get();
        }


        // dd($customer_query);

        // $customer_query  = $customer_query->pluck('id')->toArray();
        $category_all = $category_query->pluck('id')->toArray();
        // $category_query = $category_query->get();



        if (!empty($customer_query) || !empty($category_query)) {
            $output = '<ul class="dropdown-menu search-dropdown customer_id state-tags select_customer_id" style="display:block; top:65px; left:16px; width:calc(100% - 32px); padding:0px; max-height: 380px;overflow-y: scroll;">';
            // dd($category_query);
            if (!empty($category_query)) {
                $i = 1;
                foreach ($category_query as $key) {
                    $output .= '
                      <li class="list-data parent" data-value="' . $key->title . '" data-id="cat-' . $key->id . '" style="padding:0px 4px;padding-top:2px;">';
                    $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="' . $key->id . '" data-prod_ref_code="" class="select_customer_id"><b>' . $key->title . '</b></a></li>
                      ';
                    $customers = Customer::select('id', 'reference_name')->where('category_id', $key->id)->get();
                    foreach ($customers as $value) {
                        $output .= '
                      <li class="list-data child" data-value="' . $value->reference_name . '" data-id="cus-' . $value->id . '" style="padding:2px 15px;border-bottom: 1px solid #eee;">';
                        $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="cus-' . $value->id . '" data-prod_ref_code="">' . $value->reference_name . '</a></li>
                      ';
                    }

                    $i++;
                }
            }
            // dd($customer_query);
            if (!empty($customer_query)) {
                $i = 1;
                $cat_id = '';
                // dd($customer_query);

                foreach ($customer_query as $value) {
                    if (!in_array($value->category_id, $category_all)) {
                        if (($cat_id == '' || $cat_id != $value->category_id) && $value->CustomerCategory != null) {
                            $output .= '<li class="list-data parent" data-value="" data-id="cat-' . $value->category_id . '" style="padding:0px 4px;padding-top:2px;>';
                            $output .= '<a tabindex="" href="javascript:void(0);" value="" data-prod_ref_code="" class="select_customer_id"><b>' . $value->CustomerCategory->title . '</b></a></li>';
                        }


                        $output .= '<li class="list-data child" data-value="' . $value->reference_name . '" data-id="cus-' . $value->id . '" style="padding:2px 15px;border-bottom: 1px solid #eee;"> ';
                        $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="cus-' . $value->id . '" data-prod_ref_code="">' . $value->reference_name . '</a></li>
                  ';
                        $cat_id = $value->category_id;
                    }



                    $i++;
                }
            }
            $output .= '</ul>';
            echo $output;
        } else {
            $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
            $output .= '<li style="color:red;" align="center">No record found!!!</li>';
            $output .= '</ul>';
            echo $output;
        }
    }

    public function cropImage(Request $request)
    {
        // dd($request->image);
        $product = Product::find($request->id);
        if ($product->prouctImages->count() == 4) {
            return response()->json(['completed' => true]);
        }
        $image = $request->image;

        list($type, $image) = explode(';', $image);
        list(, $image)      = explode(',', $image);
        $image = base64_decode($image);
        $image_name = time() . '.png';
        if (!file_exists(public_path('/uploads/products'))) {
            mkdir(public_path('/uploads/products'), 0755);
        }
        if (!file_exists(public_path('/uploads/products/product_' . $request->id))) {
            mkdir(public_path('/uploads/products/product_' . $request->id), 0755);
        }
        $path = public_path('/uploads/products/product_' . $request->id . '/' . $image_name);

        file_put_contents($path, $image);

        $new_image = new ProductImage;
        $new_image->product_id = $request->id;
        $new_image->image = $image_name;
        $new_image->save();

        $check_images_count = ProductImage::where('product_id', $request->id)->count();
        if ($check_images_count == 1) {
            $new_image->is_enabled = 1;
            $new_image->save();
        }
        return response()->json(['status' => true]);
    }

    public function setDefaultImage(Request $request)
    {
        // dd($request->all());
        $images = ProductImage::where('product_id', $request->id)->where('is_enabled', 1)->first();
        if ($images != null) {
            $images->is_enabled = 0;
            $images->save();
        }

        $change_image = ProductImage::find($request->image);

        if ($change_image) {
            $change_image->is_enabled = 1;
            $change_image->save();
        }

        return response()->json(['success' => true]);
    }

    public function MarginReport9(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        // $products   = Product::where('status',1)->get();
        // $suppliers  = Supplier::where('status',1)->orderBy('reference_name')->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_type')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_type')->first();

        return $this->render('users.reports.margin-report.margin-report-by-product-type', compact('warehouses', 'dummy_data', 'table_hide_columns', 'file_name'));
    }

    public function getMarginReport9(Request $request)
    {

        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        $products = ProductType::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'types.title', 'products.short_desc', 'products.brand', 'op.product_id', 'types.id AS category_id')->groupBy('products.type_id');
        $products->join('products', 'products.type_id', '=', 'types.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        // $products = $products->whereIn('o.customer_id',$customer_ids1);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }

        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total_man_ov);

        $products = ProductType::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $dt = Datatables::of($products);
        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales, $request) {
                return Product::returnAddColumnMargin9($column, $item, $total_items_gp, $total_items_sales, $request);
            });
        }

        $edit_columns = ['title', 'short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMargin9($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->rawColumns(['title', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp']);
        return $dt->make(true);
    }

    public function MarginReport11(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_type')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_type')->first();

        return $this->render('users.reports.margin-report.margin-report-by-product-type-2', compact('warehouses', 'dummy_data', 'table_hide_columns', 'file_name'));
    }

    public function getMarginReport11(Request $request)
    {

        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        $products = ProductSecondaryType::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'product_secondary_types.title', 'products.short_desc', 'products.brand', 'op.product_id', 'product_secondary_types.id AS category_id')->groupBy('products.type_id_2');
        $products->join('products', 'products.type_id_2', '=', 'product_secondary_types.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }

        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total_man_ov);

        $product = ProductSecondaryType::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $dt = Datatables::of($products);
        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales, $request) {
                return Product::returnAddColumnMargin11($column, $item, $total_items_gp, $total_items_sales, $request);
            });
        }

        $edit_columns = ['title', 'short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMargin11($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->rawColumns(['title', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp']);
        return $dt->make(true);
    }


    public function getMarginReport11Footer(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        $products = ProductSecondaryType::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'product_secondary_types.title', 'products.short_desc', 'products.brand', 'op.product_id', 'product_secondary_types.id AS category_id')->groupBy('products.type_id');
        $products->join('products', 'products.type_id', '=', 'product_secondary_types.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        // $products = $products->whereIn('o.customer_id',$customer_ids1);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }

        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }

        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = $products->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost') + abs($total_man_ov);
        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('import_vat_amount');
        $total_gp_percent = 0;
        foreach ($to_get_totals as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;
            $stock = (new ProductType)->get_manual_adjustments($request, $product->category_id);
            $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs - abs($total_man);
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += floatval($formated);
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $total_vat_in,
            'total_gp_percent' => $total_gp_percent,
        ]);
    }

    public function ExportMarginReportByProductType2(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_product_type-2')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_product_type_2';
            $new->file_name = 'Margin-Report-By-Product-Type-2.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByProductType2Job::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_product_type_2')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportByProductType2Job::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function MarginReportProductType3(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_type_3')->first();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_product_type_3')->first();

        return $this->render('users.reports.margin-report.margin-report-by-product-type-3', compact('warehouses', 'dummy_data', 'table_hide_columns', 'file_name'));
    }

    public function getMarginReportProductType3(Request $request)
    {

        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        $products = ProductTypeTertiary::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'product_type_tertiaries.title', 'products.short_desc', 'products.brand', 'op.product_id', 'product_type_tertiaries.id AS category_id')->groupBy('products.type_id_3');
        $products->join('products', 'products.type_id_3', '=', 'product_type_tertiaries.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }

        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total_man_ov);

        ProductTypeTertiary::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $dt = Datatables::of($products);
        $add_columns = ['margins', 'percent_gp', 'gp', 'cogs', 'brand', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales, $request) {
                return Product::returnAddColumnMarginType3($column, $item, $total_items_gp, $total_items_sales, $request);
            });
        }

        $edit_columns = ['title', 'short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Product::returnEditColumnMarginType3($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->rawColumns(['title', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'vat_out', 'vat_in', 'percent_sales', 'percent_gp']);
        return $dt->make(true);
    }

    public function getMarginReportProductType3Footer(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        $products = ProductTypeTertiary::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'product_type_tertiaries.title', 'products.short_desc', 'products.brand', 'op.product_id', 'product_type_tertiaries.id AS category_id')->groupBy('products.type_id_3');
        $products->join('products', 'products.type_id_3', '=', 'product_type_tertiaries.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        // $products = $products->whereIn('o.customer_id',$customer_ids1);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }

        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }

        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = $products->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost') + abs($total_man_ov);
        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('import_vat_amount');
        $total_gp_percent = 0;
        foreach ($to_get_totals as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;
            $stock = (new ProductType)->get_manual_adjustments($request, $product->category_id);
            $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs - abs($total_man);
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $total_vat_in,
            'total_gp_percent' => $total_gp_percent,
        ]);
    }

    public function ExportMarginReportByProductType3(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_product_type-3')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_product_type_3';
            $new->file_name = 'Margin-Report-By-Product-Type-2.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByProductType3Job::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_product_type_3')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportByProductType3Job::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function refreshStock(Request $request)
    {
        if (isset($request->selected_products)) {
            $multi_products = explode(',', $request->selected_products);
            $product = Product::whereIn('id', $multi_products)->get();
            $warehouses = Warehouse::all();
            foreach ($warehouses as $warehouse) {
                foreach ($product->chunk(300) as $prods) {
                    foreach ($prods as $prod) {
                        $pids = PurchaseOrder::where('status', 21)->whereHas('PoWarehouse', function ($qq) use ($warehouse) {
                            $qq->where('from_warehouse_id', $warehouse->id);
                        })->pluck('id')->toArray();
                        $pqty =  PurchaseOrderDetail::whereIn('po_id', $pids)->where('product_id', $prod->id)->sum('quantity');

                        $warehouse_product = WarehouseProduct::where('product_id', $prod->id)->where('warehouse_id', $warehouse->id)->first();
                        $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';

                        $stck_out = StockManagementOut::select('quantity_out,warehouse_id')->where('product_id', $prod->id)->where('warehouse_id', $warehouse->id)->sum('quantity_out');
                        $stck_in = StockManagementOut::select('warehouse_id,quantity_in')->where('product_id', $prod->id)->where('warehouse_id', $warehouse->id)->sum('quantity_in');

                        $current_stock_all = round($stck_in, 3) - abs(round($stck_out, 3));
                        $warehouse_product->current_quantity = round($current_stock_all, 3);
                        $warehouse_product->save();

                        $ids =  Order::where('primary_status', 2)->whereHas('order_products', function ($qq) use ($prod, $warehouse) {
                            $qq->where('product_id', $prod->id);
                            $qq->where('from_warehouse_id', $warehouse->id);
                        })->whereNull('ecommerce_order')->pluck('id')->toArray();

                        $ids1 =  Order::where('primary_status', 2)->whereHas('order_products', function ($qq) use ($prod, $warehouse) {
                            $qq->where('product_id', $prod->id);
                            $qq->whereNull('from_warehouse_id');
                        })->whereHas('user_created', function ($query) use ($warehouse) {
                            $query->where('warehouse_id', $warehouse->id);
                        })->whereNull('ecommerce_order')
                            ->pluck('id')->toArray();

                        $ordered_qty0 =  OrderProduct::whereIn('order_id', $ids)->where('product_id', $prod->id)->sum('quantity');

                        $ordered_qty1 =  OrderProduct::whereIn('order_id', $ids1)->where('product_id', $prod->id)->sum('quantity');

                        $ordered_qty = $ordered_qty0 + $ordered_qty1 + $pqty;

                        //To Update ECOM orders
                        $ecom_ids =  Order::where('primary_status', 2)->whereHas('order_products', function ($qq) use ($prod, $warehouse) {
                            $qq->where('product_id', $prod->id);
                            $qq->where('from_warehouse_id', $warehouse->id);
                        })->where('ecommerce_order', 1)->pluck('id')->toArray();

                        $ecom_ids1 =  Order::where('primary_status', 2)->whereHas('order_products', function ($qq) use ($prod, $warehouse) {
                            $qq->where('product_id', $prod->id);
                            $qq->whereNull('from_warehouse_id');
                        })->whereHas('user_created', function ($query) use ($warehouse) {
                            $query->where('warehouse_id', $warehouse->id);
                        })->where('ecommerce_order', 1)
                            ->pluck('id')->toArray();

                        $ecom_ordered_qty0 =  OrderProduct::whereIn('order_id', $ecom_ids)->where('product_id', $prod->id)->sum('quantity');

                        $ecom_ordered_qty1 =  OrderProduct::whereIn('order_id', $ecom_ids1)->where('product_id', $prod->id)->sum('quantity');

                        $ecom_ordered_qty = $ecom_ordered_qty0 + $ecom_ordered_qty1;
                        $warehouse_product->reserved_quantity = number_format($ordered_qty, 3, '.', '');
                        $warehouse_product->ecommerce_reserved_quantity = number_format($ecom_ordered_qty, 3, '.', '');
                        $warehouse_product->available_quantity = number_format($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity), 3, '.', '');
                        $warehouse_product->save();
                    }
                }
            }
            return response()->json(['success' => true]);
        }
    }
    public function setDefaultSupplier(Request $request)
    {
        if ($request->supplier_id && $request->product_id) {
            // insertion in supplier_prooducts
            $SupplierProducts = new SupplierProducts();
            $SupplierProducts->supplier_id = $request->supplier_id;
            $SupplierProducts->product_id = $request->product_id;
            $SupplierProducts->is_deleted = 0;
            $SupplierProducts->save();
            // updation in products
            Product::where('id', $request->product_id)->update(['supplier_id' => $request->supplier_id]);
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function getMarginReport2Footer(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        // dd($request->all());

        $products = Product::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.quantity) END AS totalQuantity,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount, SUM(CASE
      WHEN o.primary_status="3" THEN op.qty_shipped
      END) AS qty'), 'products.refrence_code', 'products.short_desc', 'op.product_id', 'products.brand', 'o.dont_show', 'products.id', 'o.customer_id')->groupBy('op.product_id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        // $products->leftJoin('stock_out_histories AS soh','soh.order_id','=','o.id');
        //   $products->join('stock_management_outs AS smo','products.id','=','smo.product_id');
        $products = $products->where('o.primary_status', 3);
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->sale_id != null) {
            $products = $products->where('o.user_id', $request->sale_id);
        } else {
            $products = $products->where('o.dont_show', 0);
        }

        if ($request->category_id != null) {
            $products = $products->where('products.primary_category', $request->category_id);
        }

        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }
        if ($request->customer_selected != null && $request->customer_selected !== '') {
            $products->where('o.customer_id', $request->customer_selected);
        }

        $products = $products->with('purchaseOrderDetailVatIn:id,product_id,pod_vat_actual_total_price,quantity');

        $to_get_totals = $products->get();
        $total_items_sales = $to_get_totals->sum('sales');
        //to cogs of manual adjustments
        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_cogs = (clone $stock)->sum(\DB::raw('cost * quantity_out'));

        $total_items_cogs  = $to_get_totals->sum('products_total_cost') + abs($total_man_cogs);

        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        //   $total_vat_in = $to_get_totals->sum('import_vat_amount');
        $total_vat_in = 0;
        $total_gp_percent = 0;
        $vat_in_total_value = $to_get_totals->sum('vat_in');
        $total_qty = $to_get_totals->sum('qty');
        foreach ($to_get_totals as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;

            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs;
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
            // $product=Product::find($product->product_id);
            // $vat_in_total_value += $product->purchaseOrderDetailVatIn != null ? number_format($product->purchaseOrderDetailVatIn->sum('pod_vat_actual_total_price'),2,'.','') : 0;
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_gp_percent' => $total_gp_percent,
            'total_vat_in' => $vat_in_total_value,
            'total_qty' => $total_qty,
        ]);
    }

    public function getMarginReport3Footer(Request $request)
    {
        $from_date           = $request->from_date;
        $to_date             = $request->to_date;
        $customer_id         = $request->customer_id;
        $sales_person        = $request->sales_person;
        $customer_orders_ids = NULL;

        $products = User::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'users.name', 'users.id AS sale_id', 'o.dont_show', 'op.product_id')->where('users.status', 1)->groupBy('users.id');

        $products->join('customers AS c', 'c.primary_sale_id', '=', 'users.id');
        $products->join('orders AS o', 'o.customer_id', '=', 'c.id');
        $products->join('order_products AS op', 'op.order_id', '=', 'o.id');
        $products->where('o.primary_status', 3);
        $products->where('o.dont_show', 0);
        $products->whereNotNull('op.product_id');
        // dd($products->toSql());

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        $to_get_totals = $products->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('vat_in');
        $total_gp_percent = 0;
        foreach ($to_get_totals as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;

            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs;
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $total_vat_in,
            'total_gp_percent' => $total_gp_percent,
        ]);
    }

    public function getMarginReport4Footer(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = ProductCategory::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'product_categories.title', 'products.short_desc', 'products.brand', 'op.product_id', 'product_categories.id AS category_id')->groupBy('products.primary_category');
        $products->join('products', 'products.primary_category', '=', 'product_categories.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        // $products = $products->whereIn('o.customer_id',$customer_ids1);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }

        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }

        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = $products->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost') + abs($total_man_ov);
        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('vat_in');
        $total_gp_percent = 0;
        foreach ($to_get_totals as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;

            $stock = (new ProductCategory)->get_manual_adjustments($request, $product->category_id);
            $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs - abs($total_man);
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $total_vat_in,
            'total_gp_percent' => $total_gp_percent,
        ]);
    }

    public function getMarginReport5Footer(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        $products = Customer::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'customers.reference_name', 'customers.reference_number', 'customers.id AS customer_id', 'products.brand')->where('customers.status', 1)->groupBy('customers.id');
        $products->join('orders AS o', 'o.customer_id', '=', 'customers.id');
        $products->join('order_products AS op', 'op.order_id', '=', 'o.id');
        $products->join('products', 'products.id', '=', 'op.product_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        // $products = $products->whereHas('primary_sale_person', function($qq){
        //   $qq->where('is_include_in_reports',1);
        // });

        if ($request->selecting_cust_cat != null) {
            $products = $products->where('customers.category_id', $request->selecting_cust_cat);
        }

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }
        if ($request->sale_person_selected != null && $request->sale_person_selected !== '') {
            $products->where('customers.primary_sale_id', $request->sale_person_selected);
        }

        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('vat_in');
        $total_gp_percent = 0;
        foreach ($products->get() as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;

            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs;
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $total_vat_in,
            'total_gp_percent' => $total_gp_percent,
        ]);
    }

    public function getMarginReport6Footer(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        // $customer_ids1 = Customer::where('status',1)->whereHas('primary_sale_person', function($qq){
        //   $qq->where('is_include_in_reports',1);
        // })->pluck('id')->toArray();

        $products = CustomerCategory::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'customer_categories.is_deleted', 'customer_categories.title', 'products.brand', 'customer_categories.id AS customer_type_id', 'o.customer_id')->groupBy('customer_categories.id');
        $products->join('customers AS c', 'c.category_id', '=', 'customer_categories.id');
        $products->join('orders AS o', 'o.customer_id', '=', 'c.id');
        $products->join('order_products AS op', 'op.order_id', '=', 'o.id');
        $products->join('products', 'products.id', '=', 'op.product_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        $products = $products->where('customer_categories.is_deleted', 0);
        // $products = $products->whereIn('o.customer_id',$customer_ids1);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }

        $to_get_totals = (clone $products)->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('vat_in');
        $total_gp_percent = 0;
        foreach ($products->get() as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;

            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs;
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $total_vat_in,
            'total_gp_percent' => $total_gp_percent,
        ]);
    }

    public function getMarginReport9Footer(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $customer_id = $request->customer_id;
        $sales_person = $request->sales_person;
        $customer_orders_ids = NULL;

        // $customer_ids1 = Customer::where('status',1)->whereHas('primary_sale_person', function($qq){
        //   $qq->where('is_include_in_reports',1);
        // })->pluck('id')->toArray();

        $products = ProductType::select(DB::raw('SUM(CASE
      WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
      END) AS products_total_cost,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
      CASE
      WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
      CASE
      WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
      WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
      WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'), 'types.title', 'products.short_desc', 'products.brand', 'op.product_id', 'types.id AS category_id')->groupBy('products.type_id');
        $products->join('products', 'products.type_id', '=', 'types.id');
        $products->join('order_products AS op', 'op.product_id', '=', 'products.id');
        $products->join('orders AS o', 'o.id', '=', 'op.order_id');
        $products = $products->where('o.primary_status', 3);
        $products = $products->where('o.dont_show', 0);
        // $products = $products->whereIn('o.customer_id',$customer_ids1);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products = $products->where('o.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products = $products->where('o.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }

        if ($request->product_id != null) {
            $products = $products->where('products.id', $request->product_id);
        }

        if ($request->sortbyparam == 1 || $request->sortbyparam == 2 || $request->sortbyparam == 3) {
            $products->orderBy($sort_variable, $sort_order);
        }

        $stock = (new StockManagementOut)->get_manual_adjustments($request);
        $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
        $to_get_totals = $products->get();
        $total_items_sales = $to_get_totals->sum('sales');
        $total_items_cogs  = $to_get_totals->sum('products_total_cost') + abs($total_man_ov);
        $total_items_gp    = $total_items_sales - $total_items_cogs;

        $total_vat_out = $to_get_totals->sum('vat_amount_total');
        $total_sale_percent = 0;
        $total_vat_in = $to_get_totals->sum('vat_in');
        $total_gp_percent = 0;
        foreach ($to_get_totals as $product) {
            $total = number_format($product->sales, 2, '.', '');
            if ($total_items_sales !== 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;
            $stock = (new ProductType)->get_manual_adjustments($request, $product->category_id);
            $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
            $sales = $product->sales;
            $cogs  = $product->products_total_cost;
            $total = $sales - $cogs - abs($total_man);
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp !== 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }

        return response()->json([
            'total_cogs' => $total_items_cogs,
            'total_sales' => $total_items_sales,
            'total_vat_out' => $total_vat_out,
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $total_vat_in,
            'total_gp_percent' => $total_gp_percent,
        ]);
    }

    public function UpdateCOGSFromPSReport(Request $request)
    {
        // dd($request->all());
        $order_product = OrderProduct::find($request->id);
        $order_product->actual_cost = $request->value;
        $order_product->save();

        $history = new ProductSaleReportDetailHistory();
        $history->order_product_id = $order_product->id;
        $history->column = $request->column;
        $history->old_value = round($request->old_value, 2);
        $history->new_value = round($request->value, 2);
        $history->updated_by = Auth::user()->id;
        $history->save();
        return response()->json(['success' => true]);
    }

    public function getPSRDHistoryData(Request $request)
    {
        $query = ProductSaleReportDetailHistory::orderBy('id', 'desc');
        return Datatables::of($query)
            ->addColumn('user_name', function ($item) {

                return @$item->updated_by != null ? @$item->user->name : '--';
            })
            ->addColumn('column_name', function ($item) {
                return @$item->column != null ? @$item->column : '--';
            })
            ->addColumn('old_value', function ($item) {
                return @$item->old_value != null ? @$item->old_value : '--';
            })
            ->addColumn('new_value', function ($item) {
                return @$item->new_value != null ? @$item->new_value : '--';
            })
            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? @$item->created_at->format('d/m/Y h:i:s') : '--';
            })
            ->addColumn('order_no', function ($item) {
                if ($item->order_product && $item->order_product->get_order) {
                    $order = $item->order_product->get_order;
                    // dd($order);
                    if ($order->primary_status == 3) {
                        return $order->full_inv_no ? $order->full_inv_no : '--';
                    } else if ($order->primary_status == 2) {
                        return $order->status_prefix . $order->ref_prefix . $order->ref_id;
                    }
                    return '--';
                }
                return '--';
            })
            ->addColumn('product', function ($item) {
                if (@$item->order_product && @$item->order_product->product) {
                    $product = $item->order_product->product;
                    $html = '<a href="' . route("get-product-detail", $product->id) . '" target="_blank"><b>' . $product->refrence_code . '</b></a>';
                    return $html;
                }
                return '--';
            })
            ->rawColumns(['user_name', 'column_name', 'old_value', 'new_value', 'created_at', 'order_no', 'product'])
            ->make(true);
    }

    public function purchaseFetchProductCategory(Request $request)
    {
        // dd($request->get('query'));
        $query = $request->get('query');
        // dd($search_box_value);
        $params = $request->except('_token');
        $detail = [];


        $category_query  = ProductCategory::with('get_Child')->where('parent_id', 0)->orderBy('title');
        $product_query = ProductCategory::where('parent_id', '!=', 0)->orderBy('title')->groupBy('title');
        // if($query)
        // {
        $query = $request->get('query');
        $product_query = $product_query->where('title', 'LIKE', '%' . $query . '%')->orderBy('title', 'ASC')->get();
        // }
        // if($query != null)
        // {
        $category_query = $category_query->where('title', 'LIKE', '%' . $query . '%')->get();
        // }
        $category_all = $category_query->pluck('id')->toArray();
        if (!empty($product_query) || !empty($category_query)) {
            $output = '<ul class="dropdown-menu search-dropdown customer_id state-tags select_customer_id" style="display:block; top:64px; left:7px; width:calc(100% - 12px); padding:0px; max-height: 380px;overflow-y: scroll;">';
            // dd($category_query);
            if ($category_query->count() > 0) {
                $i = 1;
                foreach ($category_query as $key) {
                    $output .= '
                  <li class="list-data parent" data-value="' . $key->title . '" data-id="cat-' . $key->id . '" style="padding:0px 4px;padding-top:2px;">';
                    $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="' . $key->id . '" data-prod_ref_code="" class="select_customer_id"><b>' . $key->title . '</b></a></li>
                  ';
                    $customers = $key->get_Child;
                    if ($customers != null) {
                        foreach ($customers as $value) {
                            $output .= '
                      <li class="list-data child" data-value="' . $value->title . '" data-id="cus-' . $value->id . '" style="padding:2px 15px;border-bottom: 1px solid #eee;">';
                            $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="cus-' . $value->id . '" data-prod_ref_code="">' . $value->title . '</a></li>
                      ';
                        }
                    }
                    $i++;
                }
            } elseif ($product_query->count() > 0) {
                $i = 1;
                $cat_id = '';
                // dd($customer_query);

                foreach ($product_query as $value) {
                    if (!in_array($value->id, $category_all)) {
                        if ($cat_id == '' || $cat_id != $value->id) {
                            $output .= '<li class="list-data parent" data-value="" data-id="cat-' . $value->id . '" style="padding:0px 4px;padding-top:2px;>';
                            $output .= '<a tabindex="" href="javascript:void(0);" value="" data-prod_ref_code="" class="select_customer_id"><b>' . $value->get_Parent->title . '</b></a></li>';
                        }
                        $output .= '<li class="list-data child" data-value="' . $value->title . '" data-id="cus-' . $value->id . '" style="padding:2px 15px;border-bottom: 1px solid #eee;"> ';
                        $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="cus-' . $value->id . '" data-prod_ref_code="">' . $value->title . '</a></li>
                    ';
                        $cat_id = $value->id;
                    }
                    $i++;
                }
            }
            $output .= '</ul>';
            echo $output;
        } else {
            $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
            $output .= '<li style="color:red;" align="center">No record found!!!</li>';
            $output .= '</ul>';
            echo $output;
        }
    }

    public function MarginReport10(Request $request)
    {
        $warehouses = Warehouse::where('status', 1)->get();
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'margin_report_by_supplier')->first();


        return $this->render('users.reports.margin-report.margin-report-by-supplier', compact('warehouses', 'dummy_data', 'file_name'));
    }
    public function getMarginReport10(Request $request)
    {
        $products = StockOutHistory::with('supplier', 'get_order')->whereNotNull('supplier_id')->whereNotNull('order_id')->groupBy('supplier_id')->selectRaw('stock_out_histories.*, sum(stock_out_histories.sales) as sales_total, sum(stock_out_histories.vat_in) as vat_in_total,sum(stock_out_histories.total_cost) as total_cost_c, sum(stock_out_histories.vat_out) as vat_out_total, (sum(stock_out_histories.sales) - sum(stock_out_histories.total_cost)) / sum(stock_out_histories.sales) as marg');

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $products->whereHas('get_order', function ($q) use ($from_date) {
                $q->where('converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
            });
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $products->whereHas('get_order', function ($q) use ($to_date) {
                $q->where('converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
            });
        }

        $query = (clone $products)->get();
        $total_items_sales = $query->sum('sales_total');
        $total_items_cogs  = $query->sum('total_cost_c');
        $total_items_gp    = $total_items_sales - $total_items_cogs;
        $total_sale_percent = 0;
        $total_gp_percent = 0;

        foreach ($query as $product) {
            $total = number_format($product->sales_total, 2, '.', '');
            if ($total_items_sales != 0) {
                $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
            } else {
                $total = 0;
            }
            $total_sale_percent += $total;

            $sales = $product->sales_total;
            $cogs  = $product->total_cost_c;
            $total = $sales - $cogs;
            $formated = number_format($total, 2, '.', '');
            if ($total_items_gp != 0) {
                $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
            } else {
                $formated = 0;
            }
            $total_gp_percent += $formated;
        }

        $from_date           = $request->from_date;
        $to_date             = $request->to_date;
        $customer_id         = $request->customer_id;
        $sales_person        = $request->sales_person;
        $customer_orders_ids = NULL;
        $products = StockManagementOut::doSortby($request, $products, $total_items_sales, $total_items_gp);

        $dt = Datatables::of($products);
        $add_columns = ['supplier_name', 'margins', 'percent_gp', 'gp', 'cogs', 'percent_sales', 'sales', 'vat_in', 'vat_out'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $total_items_gp, $total_items_sales, $request) {
                return Product::returnAddColumnMargin10($column, $item, $total_items_gp, $total_items_sales, $request);
            });
        }

        $filter_columns = ['supplier_name'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $query) use ($column) {
                return Product::returnFilterColumnMargin10($column, $item, $query);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->rawColumns(['supplier_name', 'short_desc', 'cogs', 'sales', 'gp', 'margins', 'percent_sales', 'percent_gp']);
        $dt->with([
            'total_cogs' => $query->sum('total_cost_c'),
            'total_sales' => $total_items_sales,
            'total_vat_out' => $query->sum('vat_out_total'),
            'total_sale_percent' => $total_sale_percent,
            'total_vat_in' => $query->sum('vat_in_total'),
            'total_gp_percent' => $total_gp_percent,
        ]);
        return $dt->make(true);
    }

    public function FetchProductWithCategory(Request $request)
    {
        $query = $request->get('query');
        $params = $request->except('_token');
        $detail = [];

        $category_query  = ProductCategory::with('get_Child.subCategoryProducts')->where('parent_id', 0)->where('title', 'LIKE', '%' . $query . '%')->orderBy('title', 'ASC')->orderBy('title')->get();
        $product_query = ProductCategory::with('productSubCategory')->where('parent_id', '!=', 0)->where('title', 'LIKE', '%' . $query . '%')->orderBy('title')->groupBy('title')->get();

        $category_all = $category_query->pluck('id')->toArray();
        if ($product_query->count() > 0 || $category_query->count() > 0) {
            $output = '<ul class="dropdown-menu search-dropdown customer_id select_customer_id" style="display:block; top:64px; left:16px; width:calc(100% - 32px); padding:0px; max-height: 380px;overflow-y: scroll; list-style-type:none;">';
            // dd($category_query);
            if ($category_query->count() > 0) {
                $i = 1;
                foreach ($category_query as $key) {
                    $output .= '
                  <li class="list-data-category parent" data-value="' . $key->title . '" data-id="cat-' . $key->id . '" style="padding:0px 4px;padding-top:2px;">';
                    $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="' . $key->id . '" data-prod_ref_code="" class="select_customer_id"><b>' . $key->title . '</b></a></li>
                  ';
                    // $customers = ProductCategory::select('id','title')->where('parent_id',$key->id)->get();
                    $customers = $key->get_Child;
                    if ($customers != null) {
                        foreach ($customers as $value) {
                            $output .= '
                    <li class="list-data-category child" data-value="' . $value->title . '" data-id="cus-' . $value->id . '" style="padding:2px 15px;border-bottom: 1px solid #eee;">';
                            $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="cus-' . $value->id . '" data-prod_ref_code="">' . $value->title . '</a>
                    ';

                            $output .= '</li>';
                        }
                    }
                    $i++;
                }
            } else if ($product_query->count() > 0) {
                $i = 1;
                $cat_id = '';

                foreach ($product_query as $value) {
                    if (!in_array($value->id, $category_all)) {
                        if ($cat_id == '' || $cat_id != $value->id) {
                            $output .= '<li class="list-data-category parent" data-value="" data-id="cat-' . $value->get_Parent->id . '" style="padding:0px 4px;padding-top:2px;>';
                            $output .= '<a tabindex="" href="javascript:void(0);" value="" data-prod_ref_code="" class="select_customer_id"><b>' . $value->get_Parent->title . '</b></a></li>';
                        }
                        $output .= '<li class="list-data-category child" data-value="' . $value->title . '" data-id="cus-' . $value->id . '" style="padding:2px 15px;border-bottom: 1px solid #eee;"> ';
                        $output .= '<a tabindex="' . $i . '" href="javascript:void(0);" value="cus-' . $value->id . '" data-prod_ref_code="">' . $value->title . '</a>
                    ';

                        $output .= '</li>';
                        $cat_id = $value->id;
                    }
                    $i++;
                }
            }

            $output .= '</ul>';
            echo $output;
        } else {
            $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:64px; left:16px; width:92%; padding:0px ">';
            $output .= '<li style="color:red;" align="center">No record found!!!</li>';
            $output .= '</ul>';
            echo $output;
        }
    }

    public function ExportMarginReportByOffice(Request $request)
    {
        $data = $request->all();
        $status = ExportStatus::where('type', 'margin_report_by_office')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type = 'margin_report_by_office';
            $new->file_name = 'Margin-Report-By-Office.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByOfficeJob::dispatch($data, Auth::user()->id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_office')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            MarginReportByOfficeJob::dispatch($data, Auth::user()->id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckForMarginReports(Request $request)
    {
        $status = ExportStatus::where('type', $request->type)->first();
        if ($status != null) {
            return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function ExportMarginReportByProductName(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_product_name')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_product_name';
            $new->file_name = 'Margin-Report-By-Product-Name.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByProductNameJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_product_name')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportByProductNameJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function ExportMarginReportBySales(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_sales')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_sales';
            $new->file_name = 'Margin-Report-By-Sales.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportBySalesJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_sales')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportBySalesJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function ExportMarginReportByProductCategory(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_product_category')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_product_category';
            $new->file_name = 'Margin-Report-By-Product-Category.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByProductCategoryJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_product_category')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportByProductCategoryJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function ExportMarginReportByCustomer(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_customer')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_customer';
            $new->file_name = 'Margin-Report-By-Customer.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByCustomerJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_customer')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportByCustomerJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function ExportMarginReportByCustomerType(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_customer_type')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_customer_type';
            $new->file_name = 'Margin-Report-By-Customer-Type.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByCustomerTypeJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_customer_type')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportByCustomerTypeJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function ExportMarginReportByProductType(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_product_type')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_product_type';
            $new->file_name = 'Margin-Report-By-Product-Type.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportByProductTypeJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_product_type')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportByProductTypeJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }

    public function ExportMarginReportByupplier(Request $request)
    {
        $data = $request->all();
        $auth_id = (Auth::user() != null) ? Auth::user()->id : 21;
        $status = ExportStatus::where('type', 'margin_report_by_supplier')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = $auth_id;
            $new->type = 'margin_report_by_supplier';
            $new->file_name = 'Margin-Report-By-Supplier.xlsx';
            $new->status = 1;
            $new->save();
            MarginReportBySupplierJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'margin_report_by_supplier')->update(['status' => 1, 'exception' => null, 'user_id' => $auth_id]);
            MarginReportBySupplierJob::dispatch($data, $auth_id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }



    public function exportAccountPayableTable(Request $request)
    {
        $status = ExportStatus::where('type', 'account_payable_report')->first();
        $auth_user = Auth::user();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'account_payable_report';
            $new->status  = 1;
            $new->save();
            AccountPayableExpJob::dispatch($request->select_supplier, $request->select_po, $request->from_date, $request->to_date, $request->select_by_value, $request->type, $request->dosortby, $auth_user, $request->sortbyvalue, $request->sortbyparam, $request->search_value);
            return response()->json(['msg' => "File is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {

            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'account_payable_report')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            AccountPayableExpJob::dispatch($request->select_supplier, $request->select_po, $request->from_date, $request->to_date, $request->select_by_value, $request->type, $request->dosortby, $auth_user, $request->sortbyvalue, $request->sortbyparam, $request->search_value);
            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckAccountPayableTable()
    {
        $status = ExportStatus::where('type', 'account_payable_report')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusFirstTimeForAccountPayableTable()
    {
        $status = ExportStatus::where('type', 'account_payable_report')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }
    public function MarginReport12(Request $request, $id)
    {
        // dd($request->from_date);
        $supplier = Supplier::find($id);
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        return $this->render('users.reports.margin-report.margin-report-by-supplier-detail', compact('supplier', 'dummy_data', 'id', 'from_date', 'to_date'));
    }

    public function exportProductsForEcom(Request $request)
    {
        $data = $request->all();

        $status = ExportStatus::where('type', 'complete_products_excel_user_selected')->first();
        $ids = explode(",", $request->selected_products);
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type = 'complete_products_excel_user_selected';
            $new->status = 1;
            $new->save();
            UserSelectedProductsExportJob::dispatch($ids, Auth::user()->id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'complete_products_excel_user_selected')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            UserSelectedProductsExportJob::dispatch($ids, Auth::user()->id);
            return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
        }
    }
    public function exportProductsForEcomStatus()
    {
        $status = ExportStatus::where('type', 'complete_products_excel_user_selected')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception]);
    }

    public function getMarginReport12(Request $request)
    {
        $query = StockOutHistory::where('stock_out_histories.supplier_id', $request->supplier_id)->with('get_order', 'stock_out', 'stock_out.get_po_group:id,ref_id,is_review', 'stock_out.stock_out_po:id,ref_id');
        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            // $query = $query->where('stock_out_histories.created_at','>=',$from_date.' 00:00:00');
            $query = $query->whereHas('stock_out', function ($s) use ($from_date) {
                $s->where('created_at', '>=', $from_date . ' 00:00:00');
            });
        }

        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            // $query = $query->where('stock_out_histories.created_at','<=',$to_date.' 23:59:59');
            $query = $query->whereHas('stock_out', function ($s) use ($to_date) {
                $s->where('created_at', '<=', $to_date . ' 23:59:59');
            });
        }
        if ($request->type == 'footer') {
            $sales = $query->sum('sales');
            $quantity = $query->sum('quantity');
            // $cost = $query->sum('total_cost');
            $cost = (clone $query)->join('stock_management_outs', 'stock_management_outs.id', '=', 'stock_out_histories.stock_out_from_id')->sum(\DB::Raw('stock_management_outs.cost * stock_out_histories.quantity'));

            return response()->json(['sales' => $sales, 'cost' => $cost, 'quantity' => $quantity]);
        }
        return Datatables::of($query)

            ->addColumn('order#', function ($item) {
                $html_string = @$item->get_order != null ? '<a href="' . route('get-completed-invoices-details', ['id' => @$item->get_order->id]) . '" target="_blank" title="Order Detail" class="font-weight-bold">' . @$item->get_order->full_inv_no . '</a>' : 'Adjustment';
                return $html_string;
            })
            ->addColumn('product', function ($item) {
                return @$item->stock_out->get_product != null ? '<a href="' . route('get-product-detail', ['id' => @$item->stock_out->get_product->id]) . '" target="_blank" title="Product Detail" class="font-weight-bold">' . @$item->stock_out->get_product->refrence_code . '</a>' : '--';
            })
            ->addColumn('sales', function ($item) {
                return number_format($item->sales, 2, '.', ',');
            })
            ->addColumn('cost', function ($item) {
                return number_format(@$item->stock_out->cost * $item->quantity, 2, '.', ',');
            })
            ->addColumn('quantity', function ($item) {
                return $item->quantity != null ? number_format(@$item->quantity, 2) : '--';
            })
            ->addColumn('unit_cost', function ($item) {
                return number_format(@$item->stock_out->cost, 2);
            })
            ->addColumn('shipment_no', function ($item) {
                $url = @$item->stock_out->get_po_group->is_review == 0 ? 'importing-receiving-queue-detail' : 'importing-completed-receiving-queue-detail';

                return @$item->stock_out->get_po_group != null ? '<a href="' . route($url, ['id' => @$item->stock_out->get_po_group->id]) . '" target="_blank" title="Product Detail" class="font-weight-bold">' . @$item->stock_out->get_po_group->ref_id . '</a>' : 'Adjustment';
            })
            ->addColumn('po_no', function ($item) {
                return @$item->stock_out->stock_out_po != null ? '<a href="' . route('get-purchase-order-detail', ['id' => @$item->stock_out->stock_out_po->id]) . '" target="_blank" title="Product Detail" class="font-weight-bold">' . @$item->stock_out->stock_out_po->ref_id . '</a>' : '--';
            })
            ->rawColumns(['order#', 'product', 'shipment_no', 'po_no'])
            ->make(true);
    }
    public function printBarcode($id)
    {
        $data = BarcodeConfiguration::first();
        $height = $data->height ?? '';
        $width = $data->width ?? '';
        $columns = $data->barcode_columns;
        $serialize_columns = unserialize($columns);
        $prd_ids = explode(',', $id);
        $get_prds = Product::with(['productCategory', 'sellingUnits'])->whereIn('id', $prd_ids)->get();
        $pdf = \PDF::loadView('users.barcode.index', compact('data', 'columns', 'get_prds', 'serialize_columns', 'height', 'width'));
        return $pdf->stream(
            'barcode.pdf',
            array(
                'Attachment' => 0
            )
        );
    }
    public function getsoldProdDataForSupplierReport(Request $request)
    {
        $loggedInSalesPersonId = Auth::user()->id;
        $query = StockOutHistory::whereNotNull('stock_out_histories.order_id')->where('stock_out_histories.supplier_id', $request->from_supplier_margin_id)->select('stock_out_histories.*')->with('get_order.customer', 'get_order.user', 'purchase_order_detail.PurchaseOrder', 'get_order_product.from_supplier', 'get_order_product.from_warehouse', 'get_order_product.product.productCategory', 'get_order_product.product.productSubCategory', 'get_order_product.product.productType', 'get_order_product.product.productType2', 'get_order_product.warehouse_products', 'get_order_product.product.sellingUnits')->whereHas('get_order', function ($q) {
            $q->where('dont_show', 0);
        });

        if (Auth::user()->role_id == 3) {
            $primaryCustomers = Auth::user()->customersByPrimarySalePerson   ? Auth::user()->customersByPrimarySalePerson()->pluck('id')->toArray() : [];
            $secondaryCustomers = Auth::user()->customersBySecondarySalePerson ? Auth::user()->customersBySecondarySalePerson()->pluck('customer_id')->toArray() : [];
        } else {
            $primaryCustomers = [];
            $secondaryCustomers = [];
        }
        $customersRelatedToSalesPerson = array_merge($primaryCustomers, $secondaryCustomers);

        if ($request->sale_person_id != "null" && $request->sale_person_id != null) {
            $user_primary_customers = Customer::where('primary_sale_id', $request->sale_person_id)->pluck('id')->toArray();
            $query = $query->whereHas('get_order', function ($z) use ($request, $user_primary_customers) {
                $z->where('user_id', $request->sale_person_id)->orWhereIn('customer_id', $user_primary_customers);
            });
        } else if (Auth::user()->role_id == 3) {
            $user_i = Auth::user()->id;
            $query = $query->whereHas('get_order', function ($z) use ($user_i, $customersRelatedToSalesPerson) {
                $z->where(function ($op) use ($user_i, $customersRelatedToSalesPerson) {
                    $op->where('user_id', $user_i)->orWhereIn('customer_id', $customersRelatedToSalesPerson)->orWhereIn('customer_id', Auth::user()->user_customers_secondary->pluck('customer_id')->toArray());
                });
            });
        }
        if ($request->product_type != null) {
            $query = $query->whereHas('get_order_product', function ($p) use ($request) {
                $p->whereHas('product', function ($pr) use ($request) {
                    $pr->where('type_id', $request->product_type);
                });
            });
        }
        if ($request->product_type_2 != null) {
            $query = $query->whereHas('get_order_product', function ($p) use ($request) {
                $p->whereHas('product', function ($pr) use ($request) {
                    $pr->where('type_id_2', $request->product_type_2);
                });
            });
        }
        if ($request->product_id != '') {
            $query = $query->whereHas('get_order_product', function ($p) use ($request) {
                $p->where('product_id', $request->product_id);
            });
        }

        if ($request->customer_id != null) {
            $str = $request->customer_id;
            $split = (explode("-", $str));
            if ($split[0] == 'cus') {
                $customer_id = $split[1];
                $customer = Customer::find($customer_id);
                if ($customer != null && @$customer->manual_customer == 1) {
                    $query = $query->whereHas('get_order', function ($z) use ($customer_id) {
                        $z->where('customer_id', $customer_id);
                    });
                } else {
                    $query = $query->whereHas('get_order', function ($z) use ($customer_id) {
                        $z->where('customer_id', $customer_id);
                    });
                }
            } else {
                $cat_id = $split[1];
                $query = $query->whereHas('get_order', function ($z) use ($cat_id) {
                    $z->whereHas('customer', function ($cust) use ($cat_id) {
                        $cust->where('category_id', $cat_id);
                    });
                });
            }
        }

        if ($request->p_c_id != "null" && $request->p_c_id != null) {
            $p_cat_id = ProductCategory::select('id', 'parent_id')->where('parent_id', $request->p_c_id)->pluck('id')->toArray();
            $product_ids = Product::select('id', 'category_id')->whereIn('category_id', $p_cat_id)->pluck('id');
            $query = $query->whereHas('get_order_product', function ($op) use ($product_ids) {
                $op->whereIn('product_id', $product_ids);
            });
        } else {
            if ($request->prod_category != null) {
                $cat_id_split = explode('-', $request->prod_category);
                if ($cat_id_split[0] == 'sub') {
                    $product_ids = Product::select('id', 'category_id', 'status')->where('category_id', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereHas('get_order_product', function ($op) use ($product_ids) {
                        $op->whereIn('product_id', $product_ids);
                    });
                } else {
                    $p_cat_ids = Product::select('id', 'primary_category', 'status')->where('primary_category', $cat_id_split[1])->where('status', 1)->pluck('id');
                    $query = $query->whereHas('get_order_product', function ($op) use ($p_cat_ids) {
                        $op->whereIn('order_products.product_id', $p_cat_ids);
                    });
                }
            }
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            // $query->where('stock_out_histories.created_at','>=',$date.' 00:00:00');
            if ($request->date_type == 2) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('converted_to_invoice_on', '>=', $date . ' 00:00:00');
                });
            } else {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('delivery_request_date', '>=', $date);
                });
            }
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            // $query->where('stock_out_histories.created_at','<=',$date.' 23:59:59');

            if ($request->date_type == 2) {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('converted_to_invoice_on', '<=', $date . ' 23:59:59');
                });
            } else {
                $query->whereHas('get_order', function ($q) use ($date) {
                    $q->where('delivery_request_date', '<=', $date);
                });
            }
        }
        // dd($query);
        $query = StockOutHistory::doSortby($request, $query);

        $getCategories = CustomerCategory::select('id', 'title', 'is_deleted')->where('is_deleted', 0)->get();
        $not_visible_arr = [];
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'sold_product_report')->first();
        if ($table_hide_columns != null) {
            $not_visible_arr = explode(',', $table_hide_columns->hide_columns);
        }
        if ($request->type == 'footer') {
            $cost_unit_total   = (clone $query)->join('order_products', 'order_products.id', '=', 'stock_out_histories.order_product_id')->sum('unit_price');
            $total_price_total = (clone $query)->sum('sales');

            $sub_total = (clone $query)->sum('sales');
            $total_quantity = (clone $query)->sum('quantity');
            $total_manual = 0;
            $total_quantity2 = (clone $query)->sum('quantity');
            $total_cogs_val = (clone $query)->sum('total_cost');
            $total_cogs_manual = 0;
            $total_vat_thb = (clone $query)->sum('vat_out');
            $total_pieces = 0;
            return response()->json(["cost_unit_total" => $cost_unit_total, 'total_price_total' => $total_price_total, 'total_quantity' => $total_quantity, 'total_quantity2' => $total_quantity2, 'grand_cogs' => $total_cogs_val, 'sub_total' => $sub_total, 'total_vat_thb' => floatval($total_vat_thb), 'total_pieces' => $total_pieces, 'total_manual' => $total_manual, 'total_cogs_manual' => $total_cogs_manual]);
        }
        $dt = Datatables::of($query);
        if (!in_array('0', $not_visible_arr)) {
            $dt->addColumn('ref_id', function ($item) {

                if ($item->order_id == null) {
                    return $item->type_id;
                } else if ($item->order_id != null) {
                    $order = @$item->get_order;
                    $ret = $order->get_order_number_and_link($order);
                    $ref_no = $ret[0];
                    $link = $ret[1];
                    return $title = '<a target="_blank" href="' . route($link, ['id' => $order->id]) . '" title="View Detail" class=""><b>' . $ref_no . '</b></a>';
                } else {
                    return "--";
                }
            });
        } else {
            $dt->addColumn('ref_id', function ($item) {
                return '--';
            });
        }
        if (!in_array('2', $not_visible_arr)) {
            $dt->addColumn('ref_po_no', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $status = @$item->get_order != null ? (@$item->get_order->memo != NULL ? @$item->get_order->memo : "N.A") : 'N.A';
                return  $status;
            });
        } else {
            $dt->addColumn('ref_po_no', function ($item) {
                return '--';
            });
        }
        if (!in_array('1', $not_visible_arr)) {
            $dt->addColumn('status', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $ref_po_no = @$item->get_order != null ? (@$item->get_order->statuses != null ? @$item->get_order->statuses->title : 'N.A') : 'N.A';
                return  $ref_po_no;
            });
        } else {
            $dt->addColumn('status', function ($item) {
                return '--';
            });
        }
        if (!in_array('20', $not_visible_arr)) {
            $dt->addColumn('discount_value', function ($item) {
                return '--';
            });
        } else {
            $dt->addColumn('discount_value', function ($item) {
                return '--';
            });
        }
        if (!in_array('4', $not_visible_arr)) {
            $dt->addColumn('customer', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $customer = @$item->get_order->customer;
                return  $customer !== null ? @$customer->reference_name : 'N.A';
            });
        } else {
            $dt->addColumn('customer', function ($item) {
                return '--';
            });
        }
        if (!in_array('5', $not_visible_arr)) {
            $dt->addColumn('sale_person', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $sale_person = @$item->get_order != null ? (@$item->get_order->user_id != null ? @$item->get_order->user->name : '--') : '--';
                return $sale_person;
            });
        } else {
            $dt->addColumn('sale_person', function ($item) {
                return '--';
            });
        }
        if (!in_array('3', $not_visible_arr)) {
            $dt->addColumn('po_no', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return @$item->purchase_order_detail != null ?  $item->purchase_order_detail->PurchaseOrder->ref_id : '--';
            });
        } else {
            $dt->addColumn('po_no', function ($item) {
                return '--';
            });
        }
        if (!in_array('6', $not_visible_arr)) {
            $dt->addColumn('delivery_date', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $order = @$item->get_order;
                return  @$order->delivery_request_date !== null ? Carbon::parse(@$order->delivery_request_date)->format('d/m/Y') : 'N.A';
            });
        } else {
            $dt->addColumn('delivery_date', function ($item) {
                return '--';
            });
        }
        if (!in_array('7', $not_visible_arr)) {
            $dt->addColumn('created_date', function ($item) {
                if ($item->order_id == null) {
                    return Carbon::parse(@$item->created_at)->format('d/m/Y');
                }
                $order = @$item->get_order;
                return  @$order->converted_to_invoice_on !== null ? Carbon::parse(@$order->converted_to_invoice_on)->format('d/m/Y') : 'N.A';
            });
        } else {
            $dt->addColumn('created_date', function ($item) {
                return '--';
            });
        }
        if (!in_array('8', $not_visible_arr)) {
            $dt->addColumn('supply_from', function ($item) {

                if (@$item->get_order_product->supplier_id != NULL && @$item->get_order_product->from_warehouse_id == NULL) {
                    return @$item->get_order_product->from_supplier->reference_name;
                } elseif (@$item->get_order_product->from_warehouse_id != NULL && @$item->get_order_product->supplier_id == NULL) {
                    return @$item->get_order_product->from_warehouse->warehouse_title;
                } else {
                    return "N.A";
                }
            });
        } else {
            $dt->addColumn('supply_from', function ($item) {
                return '--';
            });
        }
        if (!in_array('9', $not_visible_arr)) {
            $dt->editColumn('refrence_code', function ($item) {
                $refrence_code = @$item->get_order_product->product_id != null ? '<a target="_blank" href="' . url('get-product-detail/' . @$item->get_order_product->product->product_id) . '" ><b>' . @$item->get_order_product->product->refrence_code . '</b></a>' : "N.A";
                return  $refrence_code;
            });
        } else {
            $dt->addColumn('refrence_code', function ($item) {
                return '--';
            });
        }
        if (!in_array('10', $not_visible_arr)) {
            $dt->editColumn('primary_sub_cat', function ($item) {
                $refrence_code = @$item->get_order_product->product != null ? (@$item->get_order_product->product->productCategory != null ? @$item->get_order_product->product->productCategory->title : '') : '';
                $refrence_code .= @$item->get_order_product->product != null ? (@$item->get_order_product->product->productSubCategory != null ? (' / ' . @$item->get_order_product->product->productSubCategory->title) : '') : '';
                return  $refrence_code;
            });
        } else {
            $dt->addColumn('primary_sub_cat', function ($item) {
                return '--';
            });
        }

        if (!in_array('11', $not_visible_arr)) {
            $dt->addColumn('product_type', function ($item) {
                if (@$item->get_order_product->product != null) {
                    return @$item->get_order_product->product->productType != null ? @$item->get_order_product->product->productType->title : '--';
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('product_type', function ($item) {
                return '--';
            });
        }
        if (!in_array('12', $not_visible_arr)) {
            $dt->addColumn('brand', function ($item) {
                if (@$item->get_order_product->brand != null) {
                    return $item->get_order_product->brand;
                } elseif (@$item->get_order_product->product_id != null) {
                    if (@$item->get_order_product->product->brand != null) {
                        return @$item->get_order_product->product->brand ?? '--';
                    } else {
                        return '--';
                    }
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('brand', function ($item) {
                return '--';
            });
        }
        if (!in_array('13', $not_visible_arr)) {
            $dt->addColumn('short_desc', function ($item) {
                return @$item->get_order_product->product_id !== null ? '<span id="short_desc">' . @$item->get_order_product->product->short_desc . '</span>' : 'N.A';
            });
        } else {
            $dt->addColumn('short_desc', function ($item) {
                return '--';
            });
        }
        if (!in_array('14', $not_visible_arr)) {
            $dt->addColumn('vintage', function ($item) {
                return @$item->get_order_product->product_id !== null ? (@$item->get_order_product->product->productType2 != null ? @$item->get_order_product->product->productType2->title : 'N.A') : 'N.A';
            });
        } else {
            $dt->addColumn('vintage', function ($item) {
                return '--';
            });
        }
        if (!in_array('15', $not_visible_arr)) {
            $dt->addColumn('available_stock', function ($item) use ($request) {
                $warehouse_id = $request->warehouse_id;

                if ($warehouse_id != null) {
                    $stock = @$item->get_order_product->product != null ? @$item->get_order_product->product->get_stock($item->get_order_product->product_id, $warehouse_id) : 'N.A';
                    return $stock;
                } else {
                    $warehouse_product = @$item->get_order_product->warehouse_products->sum('available_quantity');
                    return $warehouse_product != null ? number_format((float)$warehouse_product, 3, '.', '') : 0;
                }
            });
        } else {
            $dt->addColumn('available_stock', function ($item) {
                return '--';
            });
        }
        if (!in_array('16', $not_visible_arr)) {
            $dt->addColumn('unit', function ($item) {
                return @$item->get_order_product->product_id !== null ? @$item->get_order_product->product->sellingUnits->title : 'N.A';
            });
        } else {
            $dt->addColumn('unit', function ($item) {
                return '--';
            });
        }
        if (!in_array('17', $not_visible_arr)) {
            $dt->addColumn('sum_qty', function ($item) {
                if ($item->order_id == null) {
                    return abs($item->quantity);
                }

                $qty = $item->quantity !== null ? number_format((float)$item->quantity, 2) : 'N.A';
                $html = '<span id="qty-' . $item->id . '">' . $qty . '</span>';
                return $html;
            });
        } else {
            $dt->addColumn('sum_qty', function ($item) {
                return '--';
            });
        }
        if (!in_array('18', $not_visible_arr)) {
            $dt->addColumn('sum_piece', function ($item) {
                return '--';
            });
        } else {
            $dt->addColumn('sum_piece', function ($item) {
                return '--';
            });
        }
        if (!in_array('19', $not_visible_arr)) {
            $dt->addColumn('cost_unit', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return @$item->get_order_product->unit_price !== null ? number_format((float)$item->get_order_product->unit_price, 2) : 'N.A';
            });
        } else {
            $dt->addColumn('cost_unit', function ($item) {
                return '--';
            });
        }
        if (!in_array('23', $not_visible_arr)) {
            $dt->addColumn('sub_total', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                $sales = $item->sales;
                $vat = @$item->get_order_product->vat ?? 0;
                $val = (100 - $vat) / 100;
                $final = $sales * $val;
                return @$final !== null ? number_format((float)$final, 2) : 'N.A';
            });
        } else {
            $dt->addColumn('sub_total', function ($item) {
                return '--';
            });
        }
        if (!in_array('24', $not_visible_arr)) {
            $dt->addColumn('total_cost', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return @$item->sales !== null ? number_format((float)$item->sales, 2) : 'N.A';
            });
        } else {
            $dt->addColumn('total_cost', function ($item) {
                return '--';
            });
        }
        if (!in_array('26', $not_visible_arr)) {
            $dt->addColumn('vat', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return @$item->get_order_product->vat !== null ? $item->get_order_product->vat . ' %' : 'N.A';
            });
        } else {
            $dt->addColumn('vat', function ($item) {
                return '--';
            });
        }
        if (!in_array('25', $not_visible_arr)) {
            $dt->addColumn('vat_thb', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                return @$item->vat_out !== null ? number_format((float)$item->vat_out, 2, '.', ',') : 'N.A';
            });
        } else {
            $dt->addColumn('vat_thb', function ($item) {
                return '--';
            });
        }
        if (!in_array('21', $not_visible_arr)) {
            $dt->addColumn('item_cogs', function ($item) {
                $quantity = $item->quantity;
                $total_cost = $item->total_cost;
                $unit_cost = $item->quantity != 0 ? number_format($item->total_cost / $item->quantity, 2) : 0;
                return $unit_cost;
            });
        } else {
            $dt->addColumn('item_cogs', function ($item) {
                return '--';
            });
        }
        if (!in_array('22', $not_visible_arr)) {
            $dt->addColumn('cogs', function ($item) {
                return number_format($item->total_cost, 2, '.', ',');
            });
        } else {
            $dt->addColumn('cogs', function ($item) {
                return '--';
            });
        }
        if (!in_array('27', $not_visible_arr)) {
            $dt->addColumn('note', function ($item) {
                if ($item->order_id == null) {
                    return '--';
                }
                if (@$item->get_order_product->status == 38) {
                    return @$item->get_order_product->remarks;
                }
                $html_string = '';
                $order_notes = @$item->get_order_product->get_order_product_notes;
                if ($order_notes->count() > 0) {
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="' . $item->id . '" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
                } else {
                    $html_string = '--';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('note', function ($item) {
                return '--';
            });
        }
        $sold_count = 28;
        //Customer Category Dynamic Columns Starts Here
        if ($getCategories->count() > 0) {
            foreach ($getCategories as $cat) {
                if (!in_array($sold_count++, $not_visible_arr)) {
                    $dt->addColumn($cat->title, function ($item) use ($cat) {
                        $fixed_value = @$item->get_order_product->product->product_fixed_price
                            ->where('customer_type_id', $cat->id)
                            ->first();
                        $value = $fixed_value != null ? $fixed_value->fixed_price : '0.00';
                        $formated_value = number_format($value, 3, '.', ',');
                        return $formated_value;
                    });
                } else {
                    $dt->addColumn($cat->title, function ($item) {
                        return '--';
                    });
                }
            }
        }
        $dt->setRowId(function ($item) {
            return $item->id;
        });
        $dt->escapeColumns([]);
        $dt->rawColumns(['ref_id', 'warehouse', 'customer', 'refrence_code', 'short_desc', 'unit', 'sum_qty', 'cost_unit', 'total_cost', 'vat', 'supply_from', 'created_date', 'delivery_date', 'target_ship_date', 'brand', 'cogs', 'item_cogs', 'vat_thb', 'vintage', 'available_stock', 'status', 'sale_person', 'ref_po_no', 'discount_value', 'note', 'sum_piece', 'primary_sub_cat']);
        return $dt->make(true);
    }
    public function manualStockAdjacementTD(Request $request){
        $to_w = explode('-', $request->to_warehouse);
        $to_product_id = null;
        if($to_w[0] == 'p'){
            $request['to_warehouse'] = $request->from_warehouse;
            $to_product_id = $to_w[1];
        }else{
            $request['to_warehouse'] = $to_w[1];
        }
        // dd($request->all());
        $quantity = abs($request->quantity_transfer);  
        $st_supplier = Supplier::find(@$request->transfer_stock_supplier_id);
        $td_status = Status::where('id', 19)->first();
        $counter_formula = $td_status->counter_formula;
        $counter_formula = explode('-', $counter_formula);
        $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $year = substr($year, -2);
        $month = sprintf("%02d", $month);
        $date = $year . $month;

        $c_p_ref = PurchaseOrder::where('ref_id', 'LIKE', "$date%")->orderby('id', 'DESC')->first();
        $str = @$c_p_ref->ref_id;
        $onlyIncrementGet = substr($str, 4);
        if ($str == NULL) {
            // $str = $date.'0';
            $onlyIncrementGet = 0;
        }
        $system_gen_no = $date . str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
        $date = date('y-m-d');
        $currentDate = Carbon::now()->format('Y-m-d');
        $from_product = Product::find($request->prod_id);
        $to_product = Product::find($to_product_id);
        $td_msg = @$to_product_id == null ? ("TD created from product details page against supplier ".@$st_supplier->reference_name) : ("TD created from product details page against supplier ".@$st_supplier->reference_name." to transfer stock from product ".@$request->prod_id." to ".@$to_product_id);
        $purchaseOrder = PurchaseOrder::create([
            'ref_id'              => $system_gen_no,
            'status'              => 22,
            'total'               => NULL,
            'total_quantity'      => $quantity,
            'total_gross_weight'  => Null,
            'total_import_tax_book' => Null,
            'total_import_tax_book_price' => NULL,
            // 'supplier_id'         => $request->transfer_stock_supplier_id,
            'from_warehouse_id'   => $request->from_warehouse,
            'created_by'          => Auth::user()->id,
            'memo'                => $td_msg,
            'payment_terms_id'    => NULL,
            'payment_due_date'    => NULL,
            'target_receive_date' => $currentDate,
            'transfer_date'       => $currentDate,
            'confirm_date'        => NULL,
            'to_warehouse_id'     => $request->to_warehouse,
        ]);
         // PO status history maintaining
        //  $page_status = Status::select('title')->whereIn('id', [20])->pluck('title')->toArray();
         $poStatusHistory = new PurchaseOrderStatusHistory;
         $poStatusHistory->user_id    = Auth::user()->id;
         $poStatusHistory->po_id      = $purchaseOrder->id;
         $poStatusHistory->status     = 'Created';
         $poStatusHistory->new_status = 'Complete Transfer';
         $poStatusHistory->save();
         $new_purchase_order_detail = null;
        if(@$to_product_id == null){

             $new_purchase_order_detail = PurchaseOrderDetail::create([
                'po_id'            => $purchaseOrder->id,
                'order_id'         => NULL,
                'customer_id'      => NULL,
                'order_product_id' => NULL,
                'product_id'       => $request->prod_id,
                'pod_import_tax_book' => Null,
                'pod_unit_price'   => Null,
                'pod_gross_weight' => NULL,
                'quantity'         => $quantity,
                'pod_total_gross_weight' => NULL,
                'pod_total_unit_price' => Null,
                'discount' => NULL,
                'pod_import_tax_book_price' => Null,
                'warehouse_id'     => $request->to_warehouse,
                'temperature_c'    => Null,
                'good_type'        => Null,
                'supplier_packaging' => NULL,
                'billed_unit_per_package' => NULL,
                'supplier_invoice_number' => NULL,
                'custom_invoice_number' => Null,
                'custom_line_number' => Null,
            ]);
        }
        $expirationDate = date('Y-m-d', strtotime($request->expiration_date));
        //handling stock management for From warehouse
        // if($request->expiration_date == '' || $request->expiration_date == null){
        //     $stock_in = StockManagementIn::where('product_id', $request->prod_id)->whereNull('expiration_date')
        //             ->where('warehouse_id', $request->from_warehouse)->first();
        //     $expirationDate = null;
        // }else{
        //     $stock_in = StockManagementIn::where('product_id', $request->prod_id)->whereDate('expiration_date', $expirationDate)
        //             ->where('warehouse_id', $request->from_warehouse)->first();
        // }

        $stock_msg = $to_product_id ? 'TD created to transfer stock from '.@$from_product->refrence_code.' to '.@$to_product->refrence_code : null;

        $stock_in = StockManagementIn::find($request->smi_id);
        if(!$stock_in){
            return response()->json(['success' => false, 'stockerrorMsg' => "Expiry Not Found"]);
        }
    
        if(!$stock_in){
            $stock_in = new StockManagementIn;
            $stock_in->title = 'Adjustment';
            $stock_in->product_id = $request->prod_id;
            $stock_in->warehouse_id = $request->from_warehouse;
            $stock_in->expiration_date = $expirationDate;
            $stock_in->save();
        }

        $first_w_stock_in = $stock_in;
        $productId = $to_product_id == null ? $request->prod_id : $to_product_id;

        //making stock entry
        $stock = TransferDocumentHelper::stockManagement($stock_in, $new_purchase_order_detail, '-'.$quantity, 
        $purchaseOrder, null, $request->transfer_stock_supplier_id, $request->from_warehouse, $request->cost, $request->prod_id, $stock_msg);
        $out_available_stock = abs($stock->available_stock);
        
        // $from_which_stock_it_will_deduct = StockManagementOut::where('supplier_id', $request->transfer_stock_supplier_id)->whereNull('quantity_out')
        // ->where('available_stock', '>', 0)->where('product_id', $request->prod_id)->where('warehouse_id', $request->from_warehouse)->get();

        $supplierId = $request->transfer_stock_supplier_id;

        $from_which_stock_it_will_deduct = StockManagementOut::where('supplier_id', $request->transfer_stock_supplier_id)->whereNull('quantity_out')
            ->where('available_stock', '>', 0)->where('product_id', $request->prod_id)->where('warehouse_id', $request->from_warehouse)
            ->with('supplier')
            ->when($supplierId, function ($query) use ($supplierId) {
                $query->orderByRaw("supplier_id = $supplierId desc");
            })
            ->orderBy('supplier_id')
            ->get();

        
        $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted('-'.$quantity, $stock_in, $stock, NULL,$from_which_stock_it_will_deduct);
        
        $warehouse_product = WarehouseProduct::where('product_id', $request->prod_id)->where('warehouse_id', $request->from_warehouse)->first();
        $warehouse_product->current_quantity -= $quantity;
        $warehouse_product->available_quantity -= $quantity;
        $warehouse_product->save();
        // $stock_in = StockManagementIn::find($request->smi_id);   

        //handling stock management for To warehouse
        // if($request->expiration_date == '' || $request->expiration_date == null){
        //     $stock_in = StockManagementIn::where('product_id', $request->prod_id)->whereNull('expiration_date')
        //             ->where('warehouse_id', $request->to_warehouse)->first();
        //     $expirationDate = null;
        // }else{
        //     $stock_in = StockManagementIn::where('product_id', $request->prod_id)->whereDate('expiration_date', $expirationDate)
        //             ->where('warehouse_id', $request->to_warehouse)->first();
        // }
        
        $stock_in = StockManagementIn::where('product_id', $productId)->where('expiration_date', @$first_w_stock_in->expiration_date)
                    ->where('warehouse_id', $request->to_warehouse)->first();
        if(!$stock_in){
            $stock_in = new StockManagementIn;
            $stock_in->title = 'Adjustment';
            $stock_in->product_id = $productId;
            $stock_in->warehouse_id = $request->to_warehouse;
            $stock_in->expiration_date = @$first_w_stock_in->expiration_date;
            $stock_in->save();
        }

        //making stock entry
        $stock = TransferDocumentHelper::stockManagement($stock_in, $new_purchase_order_detail,$quantity, 
        $purchaseOrder, null, $request->transfer_stock_supplier_id, $request->to_warehouse, $request->cost, $productId, $stock_msg);
        $in_available_stock= abs($stock->available_stock);
        $from_which_stock_it_will_filled = StockManagementOut::whereNull('quantity_in')
            ->where('available_stock', '<', 0)->where('product_id', $productId)->where('warehouse_id', $request->to_warehouse)->get();

        $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($quantity, $stock_in, $stock, NULL,$from_which_stock_it_will_filled);

        $warehouse_product = WarehouseProduct::where('product_id', $productId)->where('warehouse_id', $request->to_warehouse)->first();
        $warehouse_product->current_quantity += $quantity;
        $warehouse_product->available_quantity += $quantity;
        $warehouse_product->save();

       return response()->json(['success' => true,'id' => @$request->smi_id, 'successMsg' => "Manual Transfer Document Created Successfully."]);
}
 public function suppliersAvailableStock(Request $request){

    $stocks = DB::table('stock_management_outs')
    ->select(
        DB::raw('IFNULL(suppliers.reference_name, "Non supplier") as reference_name'),
        DB::raw('SUM(stock_management_outs.available_stock) as total_stock')
    )
    ->leftJoin('suppliers', 'stock_management_outs.supplier_id', '=', 'suppliers.id')
    ->whereNull('quantity_out')
    ->where('product_id',$request->product_id)
    ->where('warehouse_id', $request->from_warehouse_id)
    ->groupBy('supplier_id')
    ->get();
    
    // Display the results in a table
    $html = '<table class="table text-center table-bordered">';
    $html .= '<tr><th>Supplier Name</th>';
    $html .='<th>Total Stock</th></tr>';

    foreach ($stocks as $stock) {
        $html .= '<tr>';
        $html .= '<td>' . $stock->reference_name . '</td>';
        $html .= '<td>' . $stock->total_stock . '</td>';
        $html .= '</tr>';
    }

    $html .= '</table>';
    return $html;
 }
}
