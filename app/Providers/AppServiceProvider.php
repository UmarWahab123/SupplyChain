<?php

namespace App\Providers;

use App\GlobalAccessForRole;
use App\Menu;
use App\Models\Common\Configuration;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Sales\Customer;
use App\QuotationConfig;
use App\RoleMenu;
use App\User;
use App\Version;
use App\Models\Common\Status;
use App\Variable;
use Auth;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use App\ExportStatus;
use App\Observers\ConfigurationObserver;
use App\Observers\ProductTypeObserver;
use App\Observers\ProductCategoryObserver;
use App\Models\Common\ProductType;
use App\Models\Common\ProductCategory;
use App\Observers\ProductObserver;
use App\Models\Common\CustomerCategory;
use App\Observers\CustomerCategoryObesrver;
use App\Models\Common\Warehouse;
use App\Observers\WarehouseObserver;
use App\Models\Common\WarehouseProduct;
use App\Observers\WarehouseProductObserver;
use App\Models\Common\WarehouseZipCode;
use App\Observers\WarehouseZipCodeObserver;
use App\Models\Common\ProductImage;
use App\Observers\ProductImageObserver;
use App\Observers\QuotationConfigObserver;
use App\Models\Common\ProductFixedPrice;
use App\Observers\ProductFixedPriceObserver;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Observers\CustomerTypeCategoryMarginObserver;
use App\Models\Common\CustomerTypeProductMargin;
use App\Observers\CustomerTypeProductMarginObserver;
use  App\Observers\CurrencyObserver;
use  App\Observers\StatusObserver;
use  App\Observers\OrderObserver;
use App\Models\Common\Currency;
use App\Models\Common\Bank;
use App\Observers\BankObserver;
use App\Notification;
use Illuminate\Support\Facades\Artisan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        view()->composer('*', function ($view) {
            // dd(\Route::currentRouteName());
            if(\Route::currentRouteName() != 'export-quot-to-pdf-inc-vat' && \Route::currentRouteName() != 'export-quot-to-pdf-exc-vat' && \Route::currentRouteName() != 'get-product-detail' && \Route::currentRouteName() != 'invoices' && \Route::currentRouteName() != 'sales' && \Route::currentRouteName() != 'draft_invoices' && \Route::currentRouteName() != 'complete-list-product' && \Route::currentRouteName() != 'sold-products-report' && \Route::currentRouteName() != 'purchasing-report' && \Route::currentRouteName() != 'product-sales-report' && \Route::currentRouteName() != 'purchasing-report-grouped' && \Route::currentRouteName() != 'customer-sales-report' && \Route::currentRouteName() != 'purchasing-dashboard' && \Route::currentRouteName() != 'get-purchase-order-detail' && \Route::currentRouteName() != 'waiting-shipping-info' && \Route::currentRouteName() != 'dispatch-from-supplier' && \Route::currentRouteName() != 'received-into-stock' && \Route::currentRouteName() != 'all-pos' && \Route::currentRouteName() != 'inquiry-products-to-purchasing' && \Route::currentRouteName() != 'importing-receiving-queue' && \Route::currentRouteName() != 'get-completed-invoices-details' && \Route::currentRouteName() != 'get-completed-quotation-products' && \Route::currentRouteName() != 'get-completed-draft-invoices' && \Route::currentRouteName() != 'get-invoice' && \Route::currentRouteName() != 'importing-receiving-queue-detail' && \Route::currentRouteName() != 'importing-completed-receiving-queue-detail' && \Route::currentRouteName() != 'create-purchase-order-direct' && \Route::currentRouteName() != 'get-draft-po' && \Route::currentRouteName() != 'warehouse-dashboard' && \Route::currentRouteName() != 'pick-instruction' && \Route::currentRouteName() != 'transfer-document-dashboard' && \Route::currentRouteName() != 'warehouse-incompleted-transfer-groups' && \Route::currentRouteName() != 'list-purchasing' && \Route::currentRouteName() != 'stock-report' && \Route::currentRouteName() != 'purchasing-report'  && \Route::currentRouteName() != 'product-sales-report' && \Route::currentRouteName() != 'margin-report' && \Route::currentRouteName() != 'margin-report-2' && \Route::currentRouteName() != 'margin-report-3' && \Route::currentRouteName() != 'margin-report-4' && \Route::currentRouteName() != 'margin-report-5' && \Route::currentRouteName() != 'margin-report-6' && \Route::currentRouteName() != 'margin-report-9' && \Route::currentRouteName() != 'customer-sales-report' && \Route::currentRouteName() != 'create-transfer-document' && \Route::currentRouteName() != 'get-draft-td' && \Route::currentRouteName() != 'roles-list'  && \Route::currentRouteName() != 'list-customer' && \Route::currentRouteName() != 'get-customer-detail' && \Route::currentRouteName() != 'all-users-list' && \Route::currentRouteName() != 'user_detail' && \Route::currentRouteName() != 'list-of-suppliers' && \Route::currentRouteName() != 'accounting-dashboard' && \Route::currentRouteName() != 'get_draft_invoices_dashboard' && \Route::currentRouteName() != 'debit-notes-dashboard' && \Route::currentRouteName() != 'get-supplier-detail' && \Route::currentRouteName() != 'bulk-upload-suppliers-form' && \Route::currentRouteName() != 'purchase-account-payable' && \Route::currentRouteName() != 'account-recievable' && \Route::currentRouteName() != 'warehouse-receiving-queue' && \Route::currentRouteName() != 'get-credit-note-detail' && \Route::currentRouteName() != 'get-debit-note-detail' && \Route::currentRouteName() != 'warehouse-receiving-queue-detail' && \Route::currentRouteName() != 'warehouse-completed-receiving-queue-detail' && \Route::currentRouteName() != 'transfer-warehouse-products-receiving-queue' && \Route::currentRouteName() != 'warehouse-complete-transfer-products-receiving-queue' && \Route::currentRouteName() != 'stock-report-with-params' && \Route::currentRouteName() != 'margin-report-10' && \Route::currentRouteName() != 'ecom-dashboard' && \Route::currentRouteName() != 'ecom-invoices' && \Route::currentRouteName() != 'show-couriers' && \Route::currentRouteName() != 'bulk-products-upload-form' && \Route::currentRouteName() != 'margin-report-11' && \Route::currentRouteName() != 'product-sales-report-by-month' && \Route::currentRouteName() != 'customer-transaction-detail' && \Route::currentRouteName() != 'margin-report-12' && \Route::currentRouteName() != 'list-configuration' && \Route::currentRouteName() != 'importing-receiving-queue-detail-import' && \Route::currentRouteName() != 'get-supplier-credit-note-detail' && \Route::currentRouteName() != 'get-supplier-debit-note-detail' && \Route::currentRouteName() != 'product-detail-config' && \Route::currentRouteName() != 'bulk-upload-products.index' && \Route::currentRouteName() != 'pick-instruction-of-td' && \Route::currentRouteName() != 'spoilage-report' && \Route::currentRouteName() != 'bulk-quantity-upload-form' && \Route::currentRouteName() != 'accounting-config')
            {
                $current_version = null;
                $base_link = config('app.version_server');
                $base_key  = config('app.version_api_key');
                $vairables=Variable::select('slug','standard_name','terminology')->get();
                $global_terminologies=[];
                foreach($vairables as $variable)
                {
                    if($variable->terminology != null)
                    {
                        $global_terminologies[$variable->slug]=$variable->terminology;
                    }
                    else
                    {
                        $global_terminologies[$variable->slug]=$variable->standard_name;
                    }
                }
                if(\Route::currentRouteName() != 'export-group-to-pdf2')
                {
                    if(Auth::user())
                    {
                        $userType = Auth::user();
                        $slugs=Menu::whereNotNull('slug')->pluck('slug');
                        $global_counters=[];
                        foreach($slugs as $slug)
                        {
                            switch ($slug) {
                                case "completeProducts":
                                    $global_counters['completeProducts'] =Product::where('status', 1)->count('id');
                                    break;
                                case "incompleteProducts":
                                    $global_counters['incompleteProducts']= Product::where('status', 0)->count('id');
                                    break;
                                case "inquiryProducts":
                                    $global_counters['inquiryProducts']=OrderProduct::where('is_billed', 'Inquiry')->count('id');
                                    break;
                                case "cancelledOrders":
                                    if(Auth::user()->role_id == 9)
                                    {
                                        $global_counters['cancelledOrders']=Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
                                    }
                                    else
                                    {
                                        $global_counters['cancelledOrders']=Order::select('id')->where('primary_status', 17)->count('id');
                                    }
                                    break;
                                case "deactivatedProducts":
                                    $global_counters['deactivatedProducts']=Product::where('status', 2)->count('id');
                                    break;
                                case "ecommerceProducts":
                                    $global_counters['ecommerceProducts']=Product::where('ecommerce_enabled', 1)->count('id');
                                    break;
                                case "EcomCancelledOrders":
                                    $global_counters['EcomCancelledOrders']=Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
                                    break;
                                case "billing-configuration":
                                    $global_counters['billing-configuration']='';
                                    break;
                            }
                        }

                        $config=Configuration::first();
                        if($config)
                        {
                        $sys_name = $config->company_name;
                        $sys_color = $config;
                        $sys_logos = $config;
                        $part1=explode("#",$config->system_color);
                        $part1=array_filter($part1);
                        $value = implode(",",$part1);
                        $num1 = hexdec($value);
                        $num2 = hexdec('001500');
                        $sum = $num1 + $num2;
                        $sys_border_color = "#";
                        $sys_border_color .= dechex($sum);
                        $part1=explode("#",$config->btn_hover_color);
                        $part1=array_filter($part1);
                        $value = implode(",",$part1);
                        $number = hexdec($value);
                        $sum = $number + $num2;
                        $btn_hover_border = "#";
                        $btn_hover_border .= dechex($sum);
                        }
                        else
                        {
                            $sys_name = 'testing';
                            $sys_logos = 'testing';
                            $sys_color = 'testing';
                            $sys_border_color = 'testing';
                            $btn_hover_border = 'testing';
                            $current_version = '1';
                        }
                        $salesCoordinateInvoicesAmount = null;
                        $month = date('m');
                        $day = '01';
                        $year = date('Y');

                        $start_of_month = $year . '-' . $month . '-' . $day;
                        $today = date('Y-m-d');
                        if($userType->role_id == '9' || $userType->role_id == '4')
                        {
                            $sales1 = Order::whereHas('user',function($q){
                            $q->where('ecommerce_order',1);
                            })->where('primary_status', 1)->count('id');

                            if($sales1 > 0)
                            {
                                $quotation = $sales1;

                            }
                            else
                            {
                                $quotation = 0;
                            }

                            $Draft1 = Order::select('id')->whereHas('user', function($q){
                                $q->where('ecommerce_order',1);
                            })->where('primary_status', 2)->count('id');
                            if($Draft1 > 0)
                            {
                                $salesDraft = $Draft1;

                            }
                            else
                            {
                                $salesDraft = 0;
                            }

                            $my_invoices = Order::whereHas('user',function($q){
                                $q->Where('ecommerce_order',1);
                            })->where('primary_status', 3)->count('id');
                            if($my_invoices > 0)
                            {
                                $Invoice1 = $my_invoices;

                            }
                            else
                            {
                                $Invoice1 = 0;
                            }
                            $all_customers = Customer::all();

                            $sales2 =  Order::whereHas('customer',function($q){
                                $q->whereHas('primary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                })
                                ->orWhereHas('secondary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                });
                            })->where('primary_status', 1)->count('id');

                            $salesCoordinateQuotations = $sales2;

                            $salesCoordinateDraftInvoices =  Order::whereHas('customer',function($q){
                                $q->whereHas('primary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                })
                                ->orWhereHas('secondary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                });
                            })->where('primary_status', 2)->whereNotIn('status',[34])->count('id');

                            $salesCoordinateInvoicesQuery =  Order::whereHas('customer',function($q){
                                $q->whereHas('primary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                })
                                ->orWhereHas('secondary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                });
                            })->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->where('primary_status', 3);

                            $salesCoordinateInvoices = (clone $salesCoordinateInvoicesQuery)->count('id');
                            $salesCoordinateInvoicesAmount = (clone $salesCoordinateInvoicesQuery)->join('order_products','order_products.order_id','=','orders.id')->sum('order_products.total_price');
                            // dd($salesCoordinateInvoicesAmount);

                            $view->with(compact('global_counters','quotation','salesDraft','Invoice1','MAC','all_customers','salesCoordinateQuotations','salesCoordinateDraftInvoices','salesCoordinateInvoices','month','day','salesCoordinateInvoicesAmount'));
                        }
                        else if($userType->role_id == '1' || $userType->role_id == '7')
                        {
                            $MAC = exec('getmac');
                            $MAC = strtok($MAC, ' ');
                            $query = Customer::query();

                            $sales1 = Order::where('dont_show',0)->where('primary_status', 1)->count('id');

                            $quotation = $sales1;

                            $Draft1 = Order::select('id')->where('dont_show',0)->where('primary_status', 2)->count('id');

                            $salesDraft = $Draft1;

                            $Invoice1 = Order::where('dont_show',0)->where('primary_status', 3)->count('id');

                            $my_invoices = Order::whereHas('user',function($q){
                                $q->Where('ecommerce_order',1);
                            })->where('primary_status', 3)->count('id');
                            if($my_invoices > 0)
                            {
                                $Invoice_ecom = $my_invoices;
                            }
                            else
                            {
                                $Invoice_ecom = 0;
                            }

                            $Draft_ecom = Order::select('id')->where('ecommerce_order',1)->where('primary_status', 2)->count('id');
                            if($Draft_ecom > 0)
                            {
                                $salesDraft_ecom = $Draft_ecom;
                            }
                            else
                            {
                                $salesDraft_ecom = 0;
                            }

                            $all_customers = Customer::all();
                            $view->with(compact('global_counters','quotation','salesDraft','Invoice1','MAC','all_customers','Invoice_ecom','salesDraft_ecom'));
                        }
                        else
                        {
                            $warehouse_id = Auth::user()->warehouse_id;
                            $ids = User::select('id')->where('warehouse_id',$warehouse_id)->whereNull('parent_id')->where('role_id',3)->pluck('id')->toArray();
                            $draftQuotations = Order::where('user_id', Auth::user()->id)->where('primary_status', 1)->where('status',5)->orderBy('ref_id')->get();

                            $completeQuotationsCount = Order::where('user_id', Auth::user()->id)->where('primary_status', 1)->where('status', 6)->count('id');

                            $sales1 = Order::whereHas('user', function($q) use($warehouse_id){
                                $q->where('warehouse_id',$warehouse_id)->where('role_id',3);
                            })->where('primary_status', 1)->count('id');

                            $sales2 =  Order::whereHas('customer',function($q){
                                $q->whereHas('primary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                })
                                ->orWhereHas('secondary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                });
                            })->where('primary_status', 1)->count('id');

                            $salesCoordinateQuotations = $sales2;

                            $salesCoordinateDraftInvoices =  Order::whereHas('customer',function($q){
                                $q->whereHas('primary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                })
                                ->orWhereHas('secondary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                });
                            })->where('primary_status', 2)->count('id');

                            $salesCoordinateInvoices =  Order::whereHas('customer',function($q){
                                $q->whereHas('primary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                })
                                ->orWhereHas('secondary_sale_person',function($qq){
                                    $qq->where('warehouse_id',Auth::user()->warehouse_id)->whereIn('role_id',[3,4]);
                                });
                            })->where('primary_status', 3)->count('id');

                            //Total Invoice Widget
                                $month = date('m');
                                $day = '01';
                                $year = date('Y');

                                $start_of_month = $year . '-' . $month . '-' . $day;
                                $today = date('Y-m-d');

                            if(Auth::user()->role_id == 3)
                            {
                                // $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
                                $salesDraft = Order::where('user_id',Auth::user()->id)->where('primary_status', 2)->count('id');
                                $cancelledOrders = Order::with('customer')->where('user_id', Auth::user()->id)->where('primary_status',17)->orderBy('id','DESC')->count('id');
                                // $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());
                                $salesInvoice = Order::where('user_id', Auth::user()->id)->where('primary_status', 3)->whereBetween('converted_to_invoice_on', [$start_of_month.' 00:00:00', $today.' 23:59:59'])->count('id');
                            }
                            else
                            {
                                $Draft1 = Order::select('id')->whereIn('user_id', $ids)->where('primary_status', 2)->count('id');
                                $Draft2 = Order::select('id')->whereIn('customer_id',Auth::user()->customer->pluck('id'))->where('primary_status', 2)->count('id');
                                $salesDraft = $Draft2;
                                $Invoice1 = Order::select('id')->whereIn('user_id',$ids)->where('primary_status', 3)->count('id');
                                $Invoice2 = Order::select('id')->where('user_id',Auth::user()->id)->where('primary_status', 3)->count('id');
                                $salesInvoice = $Invoice2;
                            }
                            $draftInvoiceCount = Order::where('user_id', Auth::user()->id)->where('primary_status', 2)->count('id');

                            $draftInvoices = Order::where('user_id', Auth::user()->id)->where('primary_status', 0)->where('status', 1)->get();

                            $customersCount = Customer::where('user_id', Auth::user()->id)->where('status', 1)->count('id');
                            $productCount = Product::where('status', 1)->count('id');
                            $purchasingdraftInvoiceCount = Order::where('primary_status', 2)->count('id');
                            $all_customers = Customer::all();


                            $view->with(compact('global_counters','draftQuotations','completeQuotationsCount','draftInvoiceCount','purchasingdraftInvoiceCount','draftInvoices','customersCount','productCount','all_customers','salesDraft','salesInvoice','salesCoordinateQuotations','salesCoordinateDraftInvoices','salesCoordinateInvoices','day','month'));
                        }

                        // this is arsalan code at the end Start here
                        $menus=RoleMenu::where('role_id',Auth::user()->role_id)->groupby('parent_id')->orderBy('order','asc')->pluck('parent_id')->toArray();
                        $globalAccess=GlobalAccessForRole::where('role_id',Auth::user()->role_id)->where('status',1)->get();
                        $globalaccess=[];
                        foreach($globalAccess as $ga)
                        {
                            $globalaccess[$ga->slug]=1;
                        }
                        if (Schema::hasTable('quotation_configs')) {
                        $globalAccessConfig = QuotationConfig::where('section','quotation')->first();
                        }
                        if($globalAccessConfig)
                        {
                            if($globalAccessConfig->print_prefrences != null)
                            {
                                $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
                                foreach ($globalaccessForConfig as $val)
                                {
                                    if($val['slug'] === "radio_buttons")
                                    {
                                       $showRadioButtons = $val['status'];
                                    }
                                    if($val['slug'] === "show_discount")
                                    {
                                       $showDiscount = $val['status'];
                                    }
                                    if($val['slug'] === "show_ppbtn")
                                    {
                                       $showPrintPickBtn = $val['status'];
                                    }
                                    if($val['slug'] === "invoice_date_edit")
                                    {
                                       $invoiceEditAllow = $val['status'];
                                    }
                                }
                            }
                            else
                            {
                                $showRadioButtons = '';
                                $showDiscount     = '';
                                $showPrintPickBtn = '';
                                $invoiceEditAllow = '';
                            }
                        }
                        else
                        {
                            $showRadioButtons = '';
                            $showDiscount     = '';
                            $showPrintPickBtn = '';
                            $invoiceEditAllow = '';
                        }
                        if (Schema::hasTable('quotation_configs')) {
                        $globalAccessConfig2 = QuotationConfig::where('section','products_management_page')->first();
                        }
                        if($globalAccessConfig2)
                        {
                            $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
                            foreach ($globalaccessForConfig as $val)
                            {
                                if($val['slug'] === "allow_custom_code_edit")
                                {
                                    $allow_custom_code_edit = $val['status'];
                                }
                                if($val['slug'] === "hide_hs_description")
                                {
                                    $hide_hs_description = $val['status'];
                                }
                                if($val['slug'] === "same_description")
                                {
                                    $allow_same_description = $val['status'];
                                }
                            }
                        }
                        else
                        {
                            $allow_custom_code_edit = '';
                            $hide_hs_description = '';
                            $allow_same_description = '';
                        }
                        if (Schema::hasTable('quotation_configs')) {
                        $globalAccessConfig3 = QuotationConfig::where('section','target_ship_date')->first();
                        }
                        if($globalAccessConfig3!=null)
                        {
                            $targetShipDate=unserialize($globalAccessConfig3->print_prefrences);
                        }
                        else
                        {
                            $targetShipDate=null;
                        }
                        if (Schema::hasTable('quotation_configs')) {
                        $globalAccessConfig4 = QuotationConfig::where('section','groups_management_page')->first();
                        }
                        if($globalAccessConfig4)
                        {
                            $globalaccessForGroups = unserialize($globalAccessConfig4->print_prefrences);
                            foreach ($globalaccessForGroups as $val)
                            {
                                if($val['slug'] === "show_custom_invoice_number")
                                {
                                    $allow_custom_invoice_number = $val['status'];
                                }
                                if($val['slug'] === "show_custom_line_number")
                                {
                                    $show_custom_line_number = $val['status'];
                                }
                                if($val['slug'] === "supplier_invoice_number")
                                {
                                    $show_supplier_invoice_number = $val['status'];
                                }
                            }
                        }
                        else
                        {
                            $allow_custom_invoice_number = '';
                            $show_custom_line_number = '';
                            $show_supplier_invoice_number = '';
                        }
                        if (Schema::hasTable('quotation_configs')) {
                        $confirm_from_draft = QuotationConfig::where('section','warehouse_management_page')->first();
                        }
                        if($confirm_from_draft)
                        {
                            $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
                            foreach ($globalaccessForWarehouse as $val)
                            {
                                if($val['slug'] === "has_warehouse_account")
                                {
                                    $has_warehouse_account = $val['status'];
                                }

                            }
                        }
                        else
                        {
                            $has_warehouse_account = '';
                        }

                        // $current_version = Version::select('version','is_publish','id')->where('is_publish',1)->orderBy('id','desc')->first();

                        if($base_link != null && $base_key != null)
                        {
                            /*for current version*/
                            $uri = $base_link."api/current/".$base_key;
                            $curl = curl_init($uri);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                            $response = curl_exec($curl);
                            curl_close($curl);
                            if($response)
                            {
                                $current_version = json_decode($response);
                                if($current_version)
                                {
                                    $current_version = $current_version->version;
                                }
                            }
                            /*for current version*/
                        }

                        // $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
                        // $check_status = unserialize($ecommerceconfig->print_prefrences);
                        // $ecommerceconfig_status = $check_status['status'][0];
                        // if($ecommerceconfig_status == 1)
                        // {
                        //     Configuration::observe(ConfigurationObserver::class);
                        //     ProductType::observe(ProductTypeObserver::class);
                        //     ProductCategory::observe(ProductCategoryObserver::class);
                        //     Product::observe(ProductObserver::class);
                        //     CustomerCategory::observe(CustomerCategoryObesrver::class);
                        //     Warehouse::observe(WarehouseObserver::class);
                        //     WarehouseProduct::observe(WarehouseProductObserver::class);
                        //     WarehouseZipCode::observe(WarehouseZipCodeObserver::class);
                        //     ProductImage::observe(ProductImageObserver::class);
                        //     QuotationConfig::observe(QuotationConfigObserver::class);
                        //     ProductFixedPrice::observe(ProductFixedPriceObserver::class);
                        //     CustomerTypeCategoryMargin::observe(CustomerTypeCategoryMarginObserver::class);
                        //     CustomerTypeProductMargin::observe(CustomerTypeProductMarginObserver::class);
                        //     Currency::observe(CurrencyObserver::class);
                        //     Status::observe(StatusObserver::class);
                        //     Bank::observe(BankObserver::class);
                        // }

                        if(Auth::user()){
                        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();
                        // $view->with(compact('dummy_data'));
                        }

                        $view->with(compact('globalaccessForConfig','globalaccess','global_terminologies','global_counters','menus','sys_name','sys_color','sys_name','sys_color','sys_border_color','btn_hover_border','sys_logos','showRadioButtons','showDiscount','showPrintPickBtn','allow_custom_code_edit','hide_hs_description','allow_same_description','targetShipDate','invoiceEditAllow','show_custom_line_number','allow_custom_invoice_number','has_warehouse_account','show_supplier_invoice_number','current_version','config','dummy_data'));
                    }
                    else
                    {
                        $config=Configuration::first();
                        if($config)
                        {
                        $sys_name = $config->company_name;
                        $sys_color = $config;
                        $sys_logos = $config;
                        $part1=explode("#",$config->system_color);
                        $part1=array_filter($part1);
                        $value = implode(",",$part1);
                        $num1 = hexdec($value);
                        $num2 = hexdec('001500');
                        $sum = $num1 + $num2;
                        $sys_border_color = "#";
                        $sys_border_color .= dechex($sum);
                        $part1=explode("#",$config->btn_hover_color);
                        $part1=array_filter($part1);
                        $value = implode(",",$part1);
                        $number = hexdec($value);
                        $sum = $number + $num2;
                        $btn_hover_border = "#";
                        $btn_hover_border .= dechex($sum);
                        }
                        else
                        {
                            $sys_name = 'testing';
                            $sys_logos = 'testing';
                            $sys_color = 'testing';
                            $sys_border_color = 'testing';
                            $btn_hover_border = 'testing';
                            $current_version = '1';
                        }

                        $view->with(compact('sys_name','sys_color','sys_border_color','btn_hover_border','sys_logos','config'));
                    }
                }
                else
                {
                    $view->with(compact('global_terminologies'));
                }
            }
            else if(\Route::currentRouteName() != 'warehouse-dashboard' && \Route::currentRouteName() != 'pick-instruction' && \Route::currentRouteName() != 'get-purchase-order-detail' && \Route::currentRouteName() != 'transfer-document-dashboard'  && \Route::currentRouteName() != 'warehouse-incompleted-transfer-groups' && \Route::currentRouteName() != 'create-purchase-order-direct' && \Route::currentRouteName() != 'get-draft-po' && \Route::currentRouteName() != 'importing-receiving-queue' && \Route::currentRouteName() != 'list-purchasing' && \Route::currentRouteName() != 'stock-report' && \Route::currentRouteName() != 'purchasing-report'  && \Route::currentRouteName() != 'sales' && \Route::currentRouteName() != 'draft_invoices' && \Route::currentRouteName() != 'invoices'  && \Route::currentRouteName() != 'product-sales-report' && \Route::currentRouteName() != 'margin-report' && \Route::currentRouteName() != 'margin-report-2' && \Route::currentRouteName() != 'margin-report-3' && \Route::currentRouteName() != 'margin-report-4' && \Route::currentRouteName() != 'margin-report-5' && \Route::currentRouteName() != 'margin-report-6' && \Route::currentRouteName() != 'margin-report-9' && \Route::currentRouteName() != 'customer-sales-report' && \Route::currentRouteName() != 'create-transfer-document' && \Route::currentRouteName() != 'get-draft-td' && \Route::currentRouteName() != 'sold-products-report'&& \Route::currentRouteName() != 'roles-list' && \Route::currentRouteName() != 'list-customer' && \Route::currentRouteName() != 'get-customer-detail' && \Route::currentRouteName() != 'all-users-list' && \Route::currentRouteName() != 'user_detail' && \Route::currentRouteName() != 'list-of-suppliers' && \Route::currentRouteName() != 'accounting-dashboard' && \Route::currentRouteName() != 'get_draft_invoices_dashboard'  && \Route::currentRouteName() != 'debit-notes-dashboard' && \Route::currentRouteName() != 'get-supplier-detail' && \Route::currentRouteName() != 'bulk-upload-suppliers-form'  && \Route::currentRouteName() != 'purchase-account-payable' && \Route::currentRouteName() != 'account-recievable' && \Route::currentRouteName() != 'warehouse-receiving-queue' && \Route::currentRouteName() != 'get-credit-note-detail' && \Route::currentRouteName() != 'get-debit-note-detail' && \Route::currentRouteName() != 'warehouse-receiving-queue-detail' && \Route::currentRouteName() != 'warehouse-completed-receiving-queue-detail' && \Route::currentRouteName() != 'transfer-warehouse-products-receiving-queue' && \Route::currentRouteName() != 'warehouse-complete-transfer-products-receiving-queue' && \Route::currentRouteName() != 'export-draft-quot-to-pdf' && \Route::currentRouteName() != 'stock-report-with-params' && \Route::currentRouteName() != 'margin-report-10' && \Route::currentRouteName() != 'show-couriers' && \Route::currentRouteName() != 'bulk-products-upload-form' && \Route::currentRouteName() != 'margin-report-9' && \Route::currentRouteName() != 'product-sales-report-by-month' && \Route::currentRouteName() != 'customer-transaction-detail' && \Route::currentRouteName() != 'margin-report-12' && \Route::currentRouteName() != 'list-configuration' && \Route::currentRouteName() != 'get-supplier-credit-note-detail' && \Route::currentRouteName() != 'get-supplier-debit-note-detail' && \Route::currentRouteName() != 'bulk-upload-products.index' && \Route::currentRouteName() != 'pick-instruction-of-td' && \Route::currentRouteName() != 'spoilage-report' && \Route::currentRouteName() != 'bulk-quantity-upload-form' && \Route::currentRouteName() != 'accounting-config')
            {
                $vairables=Variable::select('slug','standard_name','terminology')->get();
                $global_terminologies=[];
                foreach($vairables as $variable)
                {
                    if($variable->terminology != null)
                    {
                        $global_terminologies[$variable->slug]=$variable->terminology;
                    }
                    else
                    {
                        $global_terminologies[$variable->slug]=$variable->standard_name;
                    }
                }
                $config=Configuration::first();
                if($config)
                {
                $sys_name = $config->company_name;
                $sys_color = $config;
                $sys_logos = $config;
                $part1=explode("#",$config->system_color);
                $part1=array_filter($part1);
                $value = implode(",",$part1);
                $num1 = hexdec($value);
                $num2 = hexdec('001500');
                $sum = $num1 + $num2;
                $sys_border_color = "#";
                $sys_border_color .= dechex($sum);
                $part1=explode("#",$config->btn_hover_color);
                $part1=array_filter($part1);
                $value = implode(",",$part1);
                $number = hexdec($value);
                $sum = $number + $num2;
                $btn_hover_border = "#";
                $btn_hover_border .= dechex($sum);
                }
                else
                {
                    $sys_name = 'testing';
                    $sys_logos = 'testing';
                    $sys_color = 'testing';
                    $sys_border_color = 'testing';
                    $btn_hover_border = 'testing';
                    $current_version = '1';
                }
                $current_version='4.3';
                // $menus=RoleMenu::where('role_id',Auth::user()->role_id)->groupby('parent_id')->orderBy('order','asc')->pluck('parent_id')->toArray();

                if(\Route::currentRouteName() == 'sold-products-report' || \Route::currentRouteName() == 'get-completed-invoices-details' || \Route::currentRouteName() == 'get-completed-quotation-products' || \Route::currentRouteName() == 'get-completed-draft-invoices' || \Route::currentRouteName() == 'get-invoice')
                {
                    if (Schema::hasTable('quotation_configs')) {
                    $globalAccessConfig = QuotationConfig::where('section','quotation')->first();
                    }
                        if($globalAccessConfig)
                        {
                            if($globalAccessConfig->print_prefrences != null)
                            {
                                $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
                                foreach ($globalaccessForConfig as $val)
                                {
                                    if($val['slug'] === "radio_buttons")
                                    {
                                       $showRadioButtons = $val['status'];
                                    }
                                    if($val['slug'] === "show_discount")
                                    {
                                       $showDiscount = $val['status'];
                                    }
                                    if($val['slug'] === "show_ppbtn")
                                    {
                                       $showPrintPickBtn = $val['status'];
                                    }
                                    if($val['slug'] === "invoice_date_edit")
                                    {
                                       $invoiceEditAllow = $val['status'];
                                    }
                                }
                            }
                            else
                            {
                                $showRadioButtons = '';
                                $showDiscount     = '';
                                $showPrintPickBtn = '';
                                $invoiceEditAllow = '';
                            }
                        }
                        else
                        {
                            $showRadioButtons = '';
                            $showDiscount     = '';
                            $showPrintPickBtn = '';
                            $invoiceEditAllow = '';
                        }
                }

                if(\Route::currentRouteName() == 'purchasing-dashboard' || \Route::currentRouteName() == 'get-purchase-order-detail' || \Route::currentRouteName() != 'waiting-shipping-info' || \Route::currentRouteName() != 'dispatch-from-supplier' || \Route::currentRouteName() != 'received-into-stock' || \Route::currentRouteName() != 'all-pos' || \Route::currentRouteName() != 'inquiry-products-to-purchasing')
                {
                    if (Schema::hasTable('quotation_configs')) {
                    $globalAccessConfig3 = QuotationConfig::where('section','target_ship_date')->first();
                    }
                    if($globalAccessConfig3!=null)
                    {
                        $targetShipDate=unserialize($globalAccessConfig3->print_prefrences);
                    }
                    else
                    {
                        $targetShipDate=null;
                    }
                }
                else
                {
                    $targetShipDate=null;
                }

                if(\Route::currentRouteName() !== 'get-invoice')
                {
                    if (Schema::hasTable('quotation_configs')) {
                    $confirm_from_draft = QuotationConfig::where('section','warehouse_management_page')->first();
                    }
                    if($confirm_from_draft)
                    {
                        $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
                        foreach ($globalaccessForWarehouse as $val)
                        {
                            if($val['slug'] === "has_warehouse_account")
                            {
                                $has_warehouse_account = $val['status'];
                            }

                        }
                    }
                    else
                    {
                        $has_warehouse_account = '';
                    }
                }

                if(Auth::user()){
                $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();
                // $view->with(compact('dummy_data'));
                }

                $view->with(compact('global_terminologies','sys_name','sys_color','sys_logos','sys_border_color','btn_hover_border','current_version','menus','global_counters','showRadioButtons','showDiscount','showPrintPickBtn','invoiceEditAllow','targetShipDate','has_warehouse_account','dummy_data'));
            }
        });


        ////  Observers  ////
        if (Schema::hasTable('quotation_configs')) {
        $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
        }
        $check_status = unserialize(@$ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
        if($ecommerceconfig_status == 1)
        {
            Configuration::observe(ConfigurationObserver::class);
            ProductType::observe(ProductTypeObserver::class);
            ProductCategory::observe(ProductCategoryObserver::class);
            Product::observe(ProductObserver::class);
            CustomerCategory::observe(CustomerCategoryObesrver::class);
            Warehouse::observe(WarehouseObserver::class);
            WarehouseProduct::observe(WarehouseProductObserver::class);
            WarehouseZipCode::observe(WarehouseZipCodeObserver::class);
            ProductImage::observe(ProductImageObserver::class);
            QuotationConfig::observe(QuotationConfigObserver::class);
            ProductFixedPrice::observe(ProductFixedPriceObserver::class);
            CustomerTypeCategoryMargin::observe(CustomerTypeCategoryMarginObserver::class);
            CustomerTypeProductMargin::observe(CustomerTypeProductMarginObserver::class);
            Currency::observe(CurrencyObserver::class);
            Status::observe(StatusObserver::class);
            Bank::observe(BankObserver::class);
            Order::observe(OrderObserver::class);
        }

        // Getting Dummy data for notifications

        // view()->composer('*', function ($view) {

        // });
    }
}
