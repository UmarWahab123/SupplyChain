<?php

namespace App\Http\Controllers\Purchasing;

use App\DraftPurchaseOrderHistory;
use App\Exports\draftPOExport;
use App\Exports\draftTDExport;
use App\Exports\waitingConformationPOExport;
use App\Exports\waitingConformationTDExport;
use App\ExportStatus;
use App\General;
use App\Http\Controllers\Controller;
use App\ImportFileHistory;
use App\Imports\BulkProductPurchaseOrder;
use App\Imports\BulkProductPurchaseOrderDetail;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Company;
use App\Models\Common\Country;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\DraftPurchaseOrderDocument;
use App\Models\Common\DraftPurchaseOrderNote;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductReceivingHistory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrderDocument;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetailNote;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrderNote;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Models\Common\PurchaseOrders\PoVatConfiguration;
use App\Models\Common\Status;
use App\Models\Common\StockManagementOut;
use App\Models\Common\StockManagementIn;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Unit;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Configuration;
use App\Models\Sales\Customer;
use App\ProductHistory;
use App\PurchaseOrderSetting;
use App\QuotationConfig;
use App\User;
use App\Variable;
use App\TransferDocumentReservedQuantity;
use Auth;
use Carbon\Carbon;
use DB;
use DateTime;
use Excel;
use File;
use Illuminate\Http\Request;
use PDF;
use Session;
use Validate;
use Yajra\Datatables\Datatables;
use App\Helpers\QuantityReservedHistory;
use App\Notification;
use Illuminate\Support\Facades\View;
use App\Models\Common\RevertedPurchaseOrder;
use App\Helpers\DraftPOInsertUpdateHelper;
use App\Helpers\PODetailCRUDHelper;
use App\Helpers\TransferDocumentHelper;
use App\Helpers\Datatables\TransferDocumentDatatable;
use App\Jobs\StockCardJob;

class PurchaseOrderController extends Controller
{
    protected $targetShipDateConfig;
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user= Auth::user();

            return $next($request);
        });
        $dummy_data=null;
        if($this->user && Schema::has('notifications')){
            $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();
            }
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;

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
        $current_version='4.3';

        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data, 'config' => $config]);
    }

    Public function createPurchaseOrder(Request $request)
    {
        // dd($request->all());
        // initiallaizing all 5 values to 0
        $total = 0;
        $total_gross_weight = 0;
        $total_item_product_quantities = 0;
        $total_import_tax_book = 0;
        $total_import_tax_book_price = 0;
        $total_vat_act = 0;
        $total_vat_act_price = 0;
        $supplyFromSupplier  = array();
        $supplyFromWarehouse = array();
        $supplyToWarehouse   = array();
        $targetShipDate      = array();

        if($request->selected_ids[0] != null)
        {
            for($i=0; $i<sizeof($request->selected_ids); $i++)
            {
                $check_s_f_and_t = OrderProduct::find($request->selected_ids[$i]);
                if($check_s_f_and_t->supplier_id == null && $check_s_f_and_t->from_warehouse_id == null)
                {
                    $errorMsg = "Select Supply From First To Combine To PO !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
                if($check_s_f_and_t->supplier_id != null)
                {
                    array_push($supplyFromSupplier, $check_s_f_and_t->supplier_id);
                }
                if($check_s_f_and_t->from_warehouse_id != null)
                {
                    array_push($supplyFromWarehouse, $check_s_f_and_t->from_warehouse_id);
                }
                if($check_s_f_and_t->warehouse_id != null)
                {
                    array_push($supplyToWarehouse, $check_s_f_and_t->warehouse_id);
                }
                else
                {
                    $errorMsg = "Selected Items Supply To Must Be Selected !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

                $id = $request->selected_ids[$i];
                $check_draft = Order::whereHas('order_products',function($q) use ($id){
                    $q->where('id',$id);
                })->where('primary_status',1)->first();
                // dd($check_draft);
                if($check_draft != null)
                {
                    $errorMsg = "The Selected Product is in Quotation Stage. Can't generate PO for it !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

                $getStatus = Status::find(7);
                $check_if_action_taken = PurchaseOrderDetail::where('order_product_id',$id)->first();

                if($check_if_action_taken != null)
                {
                    $errorMsg = "The Selected Product is not in ".$getStatus->title." Stage. Can't generate PO for it !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'action' => "Refresh"]);
                }

                array_push($targetShipDate, $check_s_f_and_t->get_order->target_ship_date);
            }
        }

        if (empty($supplyFromSupplier) && empty($supplyFromWarehouse) )
        {
            $errorMsg = "Select Supply From First Of Selected Items To Combine To PO !!!";
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }

        if (!empty($supplyFromSupplier) && !empty($supplyFromWarehouse) )
        {
            $errorMsg = "Please Select Same Supply From For Selected Items !!!";
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }

        if (!empty($supplyFromSupplier) && empty($supplyFromWarehouse) )
        {
            if (count(array_unique($supplyFromSupplier)) === 1)
            {
                // do nothing
            }
            else
            {
                $errorMsg = "Selected Items Supply From (Suppliers) Must Be Same !!!";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }
        }

        if (!empty($supplyFromWarehouse) && empty($supplyFromSupplier) )
        {
            if (count(array_unique($supplyFromWarehouse)) === 1)
            {
                // do nothing
            }
            else
            {
                $errorMsg = "Selected Items Supply From (Warehouse) Must Be Same !!!";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }
        }

        if (!empty($supplyToWarehouse))
        {
            if (count(array_unique($supplyToWarehouse)) === 1)
            {
                // do nothing
            }
            else
            {
                $errorMsg = "Please Select Same Supply To For Selected Items !!!";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }
        }

        if($request->target_ship_date_status==1 && $request->target_ship_date_required==1)
        {
            if (in_array(NULL, $targetShipDate, true))
            {
                $errorMsg = "Missing Target Ship Date Of Selected Items !!!";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }
        }

        if($request->target_ship_date_status==1)
        {
            if (!empty($targetShipDate))
            {
                if (count(array_unique($targetShipDate)) === 1)
                {
                    // do nothing
                }
                else
                {
                    $errorMsg = "Selected Items Target Ship Date Must Be Same !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

            }
        }

        if($request->target_ship_date_status==1 && $request->target_ship_date_required==1)
        {
            if (empty($targetShipDate))
            {
                $errorMsg = "Target Ship Date Required Of All Selected Items !!!";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }
        }

        // if supply from and supply to is same
        if(!empty($supplyFromWarehouse) && empty($supplyFromSupplier))
        {
            if($supplyFromWarehouse[0] == $supplyToWarehouse[0]) // if same supply from and supply to (warehouse) then this condition is true
            {
                if($request->selected_ids[0] != null)
                {
                    for($i=0; $i<sizeof($request->selected_ids); $i++)
                    {
                        $getOrderproduct = OrderProduct::find($request->selected_ids[$i]);
                        $getOrderproduct->status = 10;
                        $getOrderproduct->save();

                        $getOrderById = Order::where('id',$getOrderproduct->order_id)->first();

                        // changing orders status to delivery
                        $order_products_status_count = OrderProduct::where('order_id',$getOrderById->id)->where('is_billed','=','Product')->where('status','!=',10)->count();
                        if($order_products_status_count == 0)
                        {
                            $getOrderById->status = 10;
                            $getOrderById->save();
                            $order_history = new OrderStatusHistory;
                            $order_history->user_id = Auth::user()->id;
                            $order_history->order_id = @$getOrderById->id;
                            $order_history->status = 'DI(Waiting Gen PO)';
                            $order_history->new_status = 'DI(Waiting To Pick)';
                            $order_history->save();
                        }
                    }
                }

                return response()->json(['success' => true, 'action' => "Refresh"]);
            }
            else  // if not same supply from and supply to (warehouse) then this condition is true
            {
                // Generating PO ref#
                $po_status = Status::where('id',4)->first();
                $counter_formula = $po_status->counter_formula;
                $counter_formula = explode('-',$counter_formula);
                $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

                $year  = Carbon::now()->year;
                $month = Carbon::now()->month;

                $year  = substr($year, -2);
                $month = sprintf("%02d", $month);
                $date  = $year.$month;

                $c_p_ref = PurchaseOrder::where('ref_id','LIKE',"$date%")->orderby('id','DESC')->first();
                $str = @$c_p_ref->ref_id;
                $onlyIncrementGet = substr($str, 4);
                if($str == NULL)
                {
                  $onlyIncrementGet = 0;
                }
                $system_gen_no = $date.str_pad(@$onlyIncrementGet + 1, $counter_length,0, STR_PAD_LEFT);

                $purchase_order = new PurchaseOrder;
                $purchase_order->ref_id              = $system_gen_no;
                $purchase_order->status              = 20;
                $purchase_order->created_by          = Auth::user()->id;
                $purchase_order->target_receive_date = $targetShipDate[0];
                $purchase_order->to_warehouse_id     = $supplyToWarehouse[0];
                $purchase_order->from_warehouse_id   = $supplyFromWarehouse[0];
                $purchase_order->save();

                $purchase_order_id = $purchase_order->id;

                if($request->selected_ids[0] != null)
                {
                    for($i=0; $i<sizeof($request->selected_ids); $i++)
                    {
                        $gettingOrderProductDataById = OrderProduct::with('product')->where('id',$request->selected_ids[$i])->first();

                        $supplier_id = $gettingOrderProductDataById->product->supplier_id ;
                        $getCustomerByOrdInvId = Order::where('id',$gettingOrderProductDataById->order_id)->first();

                        $purchaseOrderDetail                   = new PurchaseOrderDetail;
                        $purchaseOrderDetail->po_id            = $purchase_order->id;
                        $purchaseOrderDetail->order_id         = $gettingOrderProductDataById->order_id;
                        $purchaseOrderDetail->order_product_id = $gettingOrderProductDataById->id;
                        $purchaseOrderDetail->product_id       = $gettingOrderProductDataById->product_id;
                        $purchaseOrderDetail->customer_id      = $getCustomerByOrdInvId->customer_id;

                        $gettingProdSuppData = SupplierProducts::where('product_id',$gettingOrderProductDataById->product_id)->where('supplier_id',$supplier_id)->first();

                        $purchaseOrderDetail->pod_import_tax_book  = $gettingOrderProductDataById->product->import_tax_book;
                        $purchaseOrderDetail->pod_vat_actual       = $gettingOrderProductDataById->product->vat;
                        $purchaseOrderDetail->pod_unit_price       = $gettingProdSuppData->buying_price;
                        $purchaseOrderDetail->pod_gross_weight     = $gettingProdSuppData->gross_weight;

                        /*vat calculations*/
                        $vat_calculations = $purchaseOrderDetail->calculateVat($gettingProdSuppData->buying_price, $gettingOrderProductDataById->product->vat);
                        $purchaseOrderDetail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                        $purchaseOrderDetail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                        /*convert val to thb's*/
                        $converted_vals = $purchaseOrderDetail->calculateVatToSystemCurrency($purchase_order->id, $vat_calculations['vat_amount']);
                        $purchaseOrderDetail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                        // supplier packagign and billed unit per package add 24 Feb, 2020
                        $quantity_inv = $gettingOrderProductDataById->quantity*$gettingOrderProductDataById->product->unit_conversion_rate;
                        $decimal_places = $purchaseOrderDetail->product->units->decimal_places;
                        if($decimal_places == 0)
                        {
                            $quantity_inv = round($quantity_inv,0);
                        }
                        elseif($decimal_places == 1)
                        {
                            $quantity_inv = round($quantity_inv,1);
                        }
                        elseif($decimal_places == 2)
                        {
                            $quantity_inv = round($quantity_inv,2);
                        }
                        elseif($decimal_places == 3)
                        {
                            $quantity_inv = round($quantity_inv,3);
                        }
                        else
                        {
                            $quantity_inv = round($quantity_inv,4);
                        }
                        $purchaseOrderDetail->supplier_packaging      = $gettingProdSuppData->supplier_packaging;
                        $purchaseOrderDetail->billed_unit_per_package = $gettingProdSuppData->billed_unit;
                        $purchaseOrderDetail->quantity                = $quantity_inv;
                        $purchaseOrderDetail->pod_total_gross_weight  = ($quantity_inv * $gettingProdSuppData->gross_weight);
                        $purchaseOrderDetail->pod_total_unit_price    = ($quantity_inv * $gettingProdSuppData->buying_price);

                        $calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_import_tax_book / 100);
                        $purchaseOrderDetail->pod_import_tax_book_price = $calculations;

                        $vat_calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_vat_actual / 100);
                        $purchaseOrderDetail->pod_vat_actual_price = $vat_calculations;

                        $purchaseOrderDetail->good_type     = $gettingOrderProductDataById->product->type_id;
                        $purchaseOrderDetail->temperature_c = $gettingOrderProductDataById->product->product_temprature_c;

                        $purchaseOrderDetail->warehouse_id  = $gettingOrderProductDataById->warehouse_id;
                        $purchaseOrderDetail->remarks       = @$gettingOrderProductDataById->remarks;
                        $purchaseOrderDetail->save();

                        $total_item_product_quantities = $quantity_inv + $total_item_product_quantities;
                        // order product status change
                        $gettingOrderProductDataById->status = 8;
                        $gettingOrderProductDataById->save();

                        // changing orders status
                        $order_products_status_count = OrderProduct::where('order_id',$getCustomerByOrdInvId->id)->where('is_billed','=','Product')->where('status','<',8)->count();
                        if($order_products_status_count == 0)
                        {
                            $getCustomerByOrdInvId->status = 8;
                            $getCustomerByOrdInvId->save();
                            $order_history = new OrderStatusHistory;
                            $order_history->user_id = Auth::user()->id;
                            $order_history->order_id = @$getCustomerByOrdInvId->id;
                            $order_history->status = 'DI(Waiting Gen PO)';
                            $order_history->new_status = 'DI(Purchasing)';
                            $order_history->save();
                        }

                        $total += $purchaseOrderDetail->quantity * $purchaseOrderDetail->pod_unit_price;

                        $total_gross_weight = ($purchaseOrderDetail->quantity * $purchaseOrderDetail->pod_gross_weight) + $total_gross_weight;

                        $total_import_tax_book = $total_import_tax_book + $purchaseOrderDetail->pod_import_tax_book;

                        $total_import_tax_book_price = $total_import_tax_book_price + $purchaseOrderDetail->pod_import_tax_book_price;

                        $total_vat_act = $total_vat_act + $purchaseOrderDetail->pod_vat_actual;

                        $total_vat_act_price = $total_vat_act_price + $purchaseOrderDetail->pod_vat_actual_price;

                        $request_to_update_prices = new \Illuminate\Http\Request();
                        $request_to_update_prices->replace(['rowId' => $purchaseOrderDetail->id, 'po_id' => $purchaseOrderDetail->po_id,'unit_price' => $purchaseOrderDetail->pod_unit_price, 'old_value' => '--']);
                        $this->UpdateUnitPrice($request_to_update_prices);

                    }
                        $purchase_order = PurchaseOrder::find($purchase_order_id);
                        $purchase_order->total = $total;
                        $purchase_order->total_gross_weight = $total_gross_weight;
                        $purchase_order->total_import_tax_book = $total_import_tax_book;
                        $purchase_order->total_import_tax_book_price = $total_import_tax_book_price;
                        $purchase_order->total_vat_actual = $total_vat_act;
                        $purchase_order->total_vat_actual_price = $total_vat_act_price;
                        $purchase_order->total_quantity = $total_item_product_quantities;
                        $purchase_order->save();

                        // PO status history maintaining
                        $page_status = Status::select('title')->whereIn('id',[7,12])->pluck('title')->toArray();

                        $poStatusHistory = new PurchaseOrderStatusHistory;
                        $poStatusHistory->user_id    = Auth::user()->id;
                        $poStatusHistory->po_id      = $purchase_order->id;
                        $poStatusHistory->status     = $page_status[0];
                        $poStatusHistory->new_status = $page_status[1];
                        $poStatusHistory->save();
                }

                return response()->json(['success' => true, 'po_id' => $purchase_order_id, 'action' => "LoadTD"]);
            }
        }
        elseif(!empty($supplyFromSupplier) && empty($supplyFromWarehouse))
        {
            // Generating PO ref#
            $po_status = Status::where('id',4)->first();
            $counter_formula = $po_status->counter_formula;
            $counter_formula = explode('-',$counter_formula);
            $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

            $year  = Carbon::now()->year;
            $month = Carbon::now()->month;

            $year  = substr($year, -2);
            $month = sprintf("%02d", $month);
            $date  = $year.$month;

            $c_p_ref = PurchaseOrder::where('ref_id','LIKE',"$date%")->orderby('id','DESC')->first();
            $str = @$c_p_ref->ref_id;
            $onlyIncrementGet = substr($str, 4);
            if($str == NULL)
            {
              $onlyIncrementGet = 0;
            }
            $system_gen_no = $date.str_pad(@$onlyIncrementGet + 1, $counter_length,0, STR_PAD_LEFT);

            $purchase_order = new PurchaseOrder;
            $purchase_order->ref_id              = $system_gen_no;
            $purchase_order->status              = 12;
            $purchase_order->created_by          = Auth::user()->id;
            $purchase_order->target_receive_date = $targetShipDate[0];
            $purchase_order->to_warehouse_id     = $supplyToWarehouse[0];
            $purchase_order->supplier_id         = $supplyFromSupplier[0];
            $purchase_order->save();

            $purchase_order->payment_terms_id    = $purchase_order->PoSupplier->credit_term;
            $purchase_order->exchange_rate       = $purchase_order->PoSupplier->getCurrency->conversion_rate;
            $purchase_order->save();

            $purchase_order_id = $purchase_order->id;

            if($request->selected_ids[0] != null)
            {

                for($i=0; $i<sizeof($request->selected_ids); $i++)
                {
                    $gettingOrderProductDataById = OrderProduct::with('product')->where('id',$request->selected_ids[$i])->first();

                    $supplier_id = $purchase_order->supplier_id;
                    $getCustomerByOrdInvId = Order::where('id',$gettingOrderProductDataById->order_id)->first();

                    $purchaseOrderDetail                   = new PurchaseOrderDetail;
                    $purchaseOrderDetail->po_id            = $purchase_order->id;
                    $purchaseOrderDetail->order_id         = $gettingOrderProductDataById->order_id;
                    $purchaseOrderDetail->order_product_id = $gettingOrderProductDataById->id;
                    $purchaseOrderDetail->product_id       = $gettingOrderProductDataById->product_id;
                    $purchaseOrderDetail->customer_id      = $getCustomerByOrdInvId->customer_id;
                    $purchaseOrderDetail->currency_conversion_rate = $purchase_order->exchange_rate;

                    $gettingProdSuppData = SupplierProducts::where('product_id',$gettingOrderProductDataById->product_id)->where('supplier_id',$supplier_id)->first();

                    $purchaseOrderDetail->pod_import_tax_book  = $gettingOrderProductDataById->product->import_tax_book;
                    $purchaseOrderDetail->pod_vat_actual       = $gettingOrderProductDataById->product->vat;
                    $purchaseOrderDetail->pod_unit_price       = $gettingProdSuppData->buying_price;
                    $purchaseOrderDetail->pod_gross_weight     = $gettingProdSuppData->gross_weight;

                    /*vat calculations*/
                    $vat_calculations = $purchaseOrderDetail->calculateVat($gettingProdSuppData->buying_price, $gettingOrderProductDataById->product->vat);
                    $purchaseOrderDetail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                    $purchaseOrderDetail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                    /*convert val to thb's*/
                    $converted_vals = $purchaseOrderDetail->calculateVatToSystemCurrency($purchase_order->id, $vat_calculations['vat_amount']);
                    $purchaseOrderDetail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                    // supplier packagign and billed unit per package add 24 Feb, 2020
                    $quantity_inv = $gettingOrderProductDataById->quantity*$gettingOrderProductDataById->product->unit_conversion_rate;
                    $decimal_places = $purchaseOrderDetail->product->units->decimal_places;
                    if($decimal_places == 0)
                    {
                        $quantity_inv = round($quantity_inv,0);
                    }
                    elseif($decimal_places == 1)
                    {
                        $quantity_inv = round($quantity_inv,1);
                    }
                    elseif($decimal_places == 2)
                    {
                        $quantity_inv = round($quantity_inv,2);
                    }
                    elseif($decimal_places == 3)
                    {
                        $quantity_inv = round($quantity_inv,3);
                    }
                    else
                    {
                        $quantity_inv = round($quantity_inv,4);
                    }
                    $purchaseOrderDetail->supplier_packaging      = $gettingProdSuppData->supplier_packaging;
                    $purchaseOrderDetail->quantity                = $quantity_inv;

                    if($gettingProdSuppData->billed_unit != NULL && $gettingProdSuppData->billed_unit != 0)
                    {
                        $purchaseOrderDetail->desired_qty         = ($quantity_inv / $gettingProdSuppData->billed_unit);
                    }
                    else
                    {
                        $gettingProdSuppData->billed_unit = 1;
                        $gettingProdSuppData->save();
                        $purchaseOrderDetail->desired_qty         = ($quantity_inv / 1);
                    }

                    $purchaseOrderDetail->billed_unit_per_package = $gettingProdSuppData->billed_unit;

                    $purchaseOrderDetail->pod_total_gross_weight  = ($quantity_inv * $gettingProdSuppData->gross_weight);
                    $purchaseOrderDetail->pod_total_unit_price    = ($quantity_inv * $gettingProdSuppData->buying_price);

                    $calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_import_tax_book / 100);
                    $purchaseOrderDetail->pod_import_tax_book_price = $calculations;

                    $vat_calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_vat_actual / 100);
                    $purchaseOrderDetail->pod_vat_actual_price = $vat_calculations;

                    $purchaseOrderDetail->good_type     = $gettingOrderProductDataById->product->type_id;
                    $purchaseOrderDetail->temperature_c = $gettingOrderProductDataById->product->product_temprature_c;

                    $purchaseOrderDetail->warehouse_id  = $gettingOrderProductDataById->warehouse_id;
                    $purchaseOrderDetail->remarks       = @$gettingOrderProductDataById->remarks;
                    $purchaseOrderDetail->save();

                    $total_item_product_quantities = $quantity_inv + $total_item_product_quantities;
                    // order product status change
                    $gettingOrderProductDataById->status = 8;
                    $gettingOrderProductDataById->save();

                    // changing orders status
                    $order_products_status_count = OrderProduct::where('order_id',$getCustomerByOrdInvId->id)->where('is_billed','=','Product')->where('status','<',8)->count();
                    if($order_products_status_count == 0)
                    {
                        $getCustomerByOrdInvId->status = 8;
                        $getCustomerByOrdInvId->save();
                        $order_history = new OrderStatusHistory;
                        $order_history->user_id = Auth::user()->id;
                        $order_history->order_id = @$getCustomerByOrdInvId->id;
                        $order_history->status = 'DI(Waiting Gen PO)';
                        $order_history->new_status = 'DI(Purchasing)';
                        $order_history->save();
                    }
                   // dd($purchaseOrderDetail->PurchaseOrder->PoSupplier);
                    $total += $purchaseOrderDetail->quantity * $purchaseOrderDetail->pod_unit_price;

                    $total_gross_weight = ($purchaseOrderDetail->quantity * $purchaseOrderDetail->pod_gross_weight) + $total_gross_weight;

                    $total_import_tax_book = $total_import_tax_book + $purchaseOrderDetail->pod_import_tax_book;

                    $total_import_tax_book_price = $total_import_tax_book_price + $purchaseOrderDetail->pod_import_tax_book_price;

                    $total_vat_act = $total_vat_act + $purchaseOrderDetail->pod_vat_actual;

                    $total_vat_act_price = $total_vat_act_price + $purchaseOrderDetail->pod_vat_actual_price;

                    $request_to_update_prices = new \Illuminate\Http\Request();
                    $request_to_update_prices->replace(['rowId' => $purchaseOrderDetail->id, 'po_id' => $purchaseOrderDetail->po_id,'unit_price' => $purchaseOrderDetail->pod_unit_price, 'old_value' => '']);
                    $resultsss = $this->UpdateUnitPrice($request_to_update_prices);
                    // dd($resultsss);

                }
                    $purchase_order = PurchaseOrder::find($purchase_order_id);
                    $purchase_order->total = $total;
                    $purchase_order->total_gross_weight = $total_gross_weight;
                    $purchase_order->total_import_tax_book = $total_import_tax_book;
                    $purchase_order->total_import_tax_book_price = $total_import_tax_book_price;
                    $purchase_order->total_vat_actual = $total_vat_act;
                    $purchase_order->total_vat_actual_price = $total_vat_act_price;
                    $purchase_order->total_quantity = $total_item_product_quantities;
                    $purchase_order->save();

                    // PO status history maintaining
                    $page_status = Status::select('title')->whereIn('id',[7,12])->pluck('title')->toArray();

                    $poStatusHistory = new PurchaseOrderStatusHistory;
                    $poStatusHistory->user_id    = Auth::user()->id;
                    $poStatusHistory->po_id      = $purchase_order->id;
                    $poStatusHistory->status     = $page_status[0];
                    $poStatusHistory->new_status = $page_status[1];
                    $poStatusHistory->save();

                    /*calulation through a function*/
                    $objectCreated = new PurchaseOrderDetail;
                    $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($purchase_order_id);
            }

            return response()->json(['success' => true, 'po_id' => $purchase_order_id, 'action' => "LoadPO"]);
        }

    }

    public function createPoGroup(Request $request)
    {
        DB::beginTransaction();
        $total_import_tax_book_price = null;
        $total_vat_actual_price = null;
        $confirm_date = date("Y-m-d");
        $warehouse = [];
        $already_in_group = [];
        $po_ids = explode(',', $request->selected_ids);

        // to check if PO's are already in group or not
        foreach ($po_ids as $po_id)
        {
            $purchase_order = PurchaseOrder::find($po_id);
            if($purchase_order)
            {
                if($purchase_order->status == 14 || $purchase_order->status == '14')
                {
                    array_push($already_in_group, $purchase_order->ref_id);
                }
            }
        }

        if(!empty($already_in_group))
        {
            $already_in_group = implode(', ', $already_in_group);
            $errorMsg = $already_in_group." These PO's already have a group !!!";
            DB::rollBack();
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }

        // this if condition is for if we are creating group from waiting status
        if($request->page == "waiting_confirm")
        {
            $vulnerabilities = 0;
            foreach ($po_ids as $po_id)
            {
                $purchase_order = PurchaseOrder::find($po_id);
                if($purchase_order)
                {
                    if($purchase_order->to_warehouse_id == NULL)
                    {
                        $vulnerabilities = 1;
                        break;
                    }
                    else
                    {
                        continue;
                    }
                }
            }

            if($vulnerabilities == 1)
            {
                $errorMsg = "Please make sure selected Purchase Orders must have Supply To Warehouse <b>Selected</b> !!!";
                DB::rollBack();
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

            foreach ($po_ids as $po_id)
            {
                $purchase_order = PurchaseOrder::find($po_id);
                $po_detail = PurchaseOrderDetail::where('po_id',$purchase_order->id)->get();
                if($po_detail->count() > 0)
                {
                  foreach ($po_detail as $value)
                  {
                    if($value->quantity === null)
                    {
                        $errorMsg =  'Please make sure selected Purchase Orders must have Quantity <b>Filled</b> !!!';
                        DB::rollBack();
                        return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    }

                    if($value->is_billed == "Billed")
                    {
                        if($value->billed_desc == null)
                        {
                            $errorMsg =  'Please make sure selected Purchase Orders must have Billed item Description <b>Filled</b> !!!';
                            DB::rollBack();
                            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                        }

                        if($value->pod_unit_price === null)
                        {
                            $errorMsg =  'Please make sure selected Purchase Orders must have Billed item Unit Price <b>Filled</b> !!!';
                            DB::rollBack();
                            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                        }
                    }
                  }
                }
            }

            // updating informations for a Purchase order
            foreach ($po_ids as $po_id)
            {
                $po = PurchaseOrder::find($po_id);
                if($po->supplier_id != null)
                {
                    if($po->exchange_rate == null)
                    {
                        $supplier_conv_rate_thb = $po->PoSupplier->getCurrency->conversion_rate;
                    }
                    else
                    {
                        $supplier_conv_rate_thb = $po->exchange_rate;
                    }

                    $getDataViaListing = PurchaseOrderDetail::where('po_id',$po->id)->orderBy('product_id','ASC')->get();
                    foreach ($getDataViaListing as $p_o_d)
                    {
                        // New Logic on confirm update product detail page values starts here
                        if($p_o_d->is_billed == "Product")
                        {
                            $getProduct = Product::find($p_o_d->product_id);

                            $productCount = PurchaseOrderDetail::where('po_id',$po->id)->where('product_id',$p_o_d->product_id)->where('discount','<',100)->orderBy('product_id','DESC')->first();
                            if($productCount)
                            {
                                if($p_o_d->discount < 100 || $p_o_d->discount == NULL)
                                {
                                    $discount_price = $p_o_d->quantity * $p_o_d->pod_unit_price - (($p_o_d->quantity * $p_o_d->pod_unit_price) * ($p_o_d->discount / 100));
                                    if($p_o_d->quantity != 0 && $p_o_d->quantity != null)
                                    {
                                        $calculated_unit_price = ($discount_price / $p_o_d->quantity);
                                    }
                                    else
                                    {
                                        $calculated_unit_price = $discount_price;
                                    }

                                    $gettingProdSuppData  = SupplierProducts::where('product_id',$p_o_d->product_id)->where('supplier_id',$po->supplier_id)->first();

                                    if($gettingProdSuppData)
                                    {
                                        $old_price_value = $gettingProdSuppData->buying_price;
                                        $gettingProdSuppData->gross_weight        = $p_o_d->pod_gross_weight;
                                        $gettingProdSuppData->save();

                                        if($calculated_unit_price != 0 && $calculated_unit_price !== 0)
                                        {
                                            if(($p_o_d->pod_unit_price != null || $p_o_d->pod_unit_price != 0) && ($p_o_d->pod_unit_price != $gettingProdSuppData->buying_price) )
                                            {
                                                $gettingProdSuppData->buying_price        = $calculated_unit_price;
                                                $gettingProdSuppData->buying_price_in_thb = ($calculated_unit_price / $supplier_conv_rate_thb);
                                                $gettingProdSuppData->save();

                                                $p_o_d->last_updated_price_on   = date('Y-m-d');
                                                $p_o_d->save();

                                                // Updating product COGS
                                                if($getProduct->supplier_id == $po->supplier_id)
                                                {
                                                    $buying_price_in_thb = ($gettingProdSuppData->buying_price / $supplier_conv_rate_thb);

                                                    $importTax = $gettingProdSuppData->import_tax_actual !== null ? $gettingProdSuppData->import_tax_actual : $getProduct->import_tax_book;

                                                    $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                                                    $total_buying_price = ($gettingProdSuppData->freight)+($gettingProdSuppData->landing)+($gettingProdSuppData->extra_cost)+($gettingProdSuppData->extra_tax)+($total_buying_price);

                                                    $getProduct->total_buy_unit_cost_price = $total_buying_price;

                                                    $getProduct->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;

                                                    $total_selling_price = $getProduct->total_buy_unit_cost_price * $getProduct->unit_conversion_rate;

                                                    $getProduct->selling_price           = $total_selling_price;
                                                    $getProduct->last_price_updated_date = Carbon::now();
                                                    $getProduct->save();

                                                    $product_history              = new ProductHistory;
                                                    $product_history->user_id     = Auth::user()->id;
                                                    $product_history->product_id  = $getProduct->id;
                                                    $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                                    $product_history->old_value   = $old_price_value;
                                                    $product_history->new_value   = $gettingProdSuppData->buying_price;
                                                    $product_history->save();
                                                }
                                                else
                                                {
                                                    $product_history              = new ProductHistory;
                                                    $product_history->user_id     = Auth::user()->id;
                                                    $product_history->product_id  = $getProduct->id;
                                                    $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                                    $product_history->old_value   = $old_price_value;
                                                    $product_history->new_value   = $gettingProdSuppData->buying_price;
                                                    $product_history->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            else
                            {
                                if($p_o_d->discount < 100 || $p_o_d->discount == NULL)
                                {
                                    $discount_price = $p_o_d->quantity * $p_o_d->pod_unit_price - (($p_o_d->quantity * $p_o_d->pod_unit_price) * ($p_o_d->discount / 100));
                                    if($p_o_d->quantity != 0 && $p_o_d->quantity != null)
                                    {
                                        $calculated_unit_price = ($discount_price / $p_o_d->quantity);
                                    }
                                    else
                                    {
                                        $calculated_unit_price = $discount_price;
                                    }

                                    $gettingProdSuppData  = SupplierProducts::where('product_id',$p_o_d->product_id)->where('supplier_id',$po->supplier_id)->first();

                                    if($gettingProdSuppData)
                                    {
                                        $old_price_value = $gettingProdSuppData->buying_price;
                                        $gettingProdSuppData->gross_weight        = $p_o_d->pod_gross_weight;
                                        $gettingProdSuppData->save();

                                        if($calculated_unit_price != 0 && $calculated_unit_price !== 0)
                                        {
                                            if(($p_o_d->pod_unit_price != null || $p_o_d->pod_unit_price != 0) && ($p_o_d->pod_unit_price != $gettingProdSuppData->buying_price) )
                                            {
                                                $gettingProdSuppData->buying_price        = $calculated_unit_price;
                                                $gettingProdSuppData->buying_price_in_thb = ($calculated_unit_price / $supplier_conv_rate_thb);
                                                $gettingProdSuppData->save();

                                                $p_o_d->last_updated_price_on   = date('Y-m-d');
                                                $p_o_d->save();

                                                // Updating product COGS
                                                if($getProduct->supplier_id == $po->supplier_id)
                                                {
                                                    $buying_price_in_thb = ($gettingProdSuppData->buying_price / $supplier_conv_rate_thb);

                                                    $importTax = $gettingProdSuppData->import_tax_actual !== null ? $gettingProdSuppData->import_tax_actual : $getProduct->import_tax_book;

                                                    $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                                                    $total_buying_price = ($gettingProdSuppData->freight)+($gettingProdSuppData->landing)+($gettingProdSuppData->extra_cost)+($gettingProdSuppData->extra_tax)+($total_buying_price);

                                                    $getProduct->total_buy_unit_cost_price = $total_buying_price;

                                                    $getProduct->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;

                                                    $total_selling_price = $getProduct->total_buy_unit_cost_price * $getProduct->unit_conversion_rate;

                                                    $getProduct->selling_price           = $total_selling_price;
                                                    $getProduct->last_price_updated_date = Carbon::now();
                                                    $getProduct->save();

                                                    $product_history              = new ProductHistory;
                                                    $product_history->user_id     = Auth::user()->id;
                                                    $product_history->product_id  = $getProduct->id;
                                                    $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                                    $product_history->old_value   = $old_price_value;
                                                    $product_history->new_value   = $gettingProdSuppData->buying_price;
                                                    $product_history->save();
                                                }
                                                else
                                                {
                                                    $product_history              = new ProductHistory;
                                                    $product_history->user_id     = Auth::user()->id;
                                                    $product_history->product_id  = $getProduct->id;
                                                    $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                                    $product_history->old_value   = $old_price_value;
                                                    $product_history->new_value   = $gettingProdSuppData->buying_price;
                                                    $product_history->save();
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        // New Logic on confirm update product detail page values ends here

                        $p_o_d->currency_conversion_rate  = $supplier_conv_rate_thb;
                        $p_o_d->unit_price_in_thb         = $p_o_d->pod_unit_price/$supplier_conv_rate_thb;
                        $p_o_d->total_unit_price_in_thb   = $p_o_d->pod_total_unit_price/$supplier_conv_rate_thb;
                        $p_o_d->pod_import_tax_book_price = ($p_o_d->pod_import_tax_book/100)*$p_o_d->total_unit_price_in_thb;
                        $total_import_tax_book_price      += $p_o_d->pod_import_tax_book_price;

                        $p_o_d->save();
                    }

                    $po->confirm_date                = $confirm_date;
                    $po->total_import_tax_book_price = $total_import_tax_book_price;
                    $po->total_in_thb                = $po->total/$supplier_conv_rate_thb;
                    $po->save();
                }
            }
        }

        foreach ($po_ids as $po_id)
        {
            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($po_id);

            $purchase_order = PurchaseOrder::find($po_id);
            array_push($warehouse, $purchase_order->to_warehouse_id);
        }

        for($i = 0 ; $i < sizeof($warehouse);$i++)
        {
            if($warehouse[0] != $warehouse[$i])
            {
                $errorMsg = "You may only group PO's that are set to be delivered to the same warehouse into the same shipment. Please select again";
                DB::rollBack();
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }
        }

        $total_quantity                     = null;
        $total_price                        = null;
        $total_import_tax_book_price        = null;
        $total_vat_actual_price             = null;
        $total_buying_price_in_thb          = null;
        $total_buying_price_with_vat_in_thb = null;
        $total_gross_weight                 = null;
        $po_group_level_vat_actual          = null;
        $po_group                           = new PoGroup;

        // generating ref #
        $year  = Carbon::now()->year;
        $month = Carbon::now()->month;

        $year  = substr($year, -2);
        $month = sprintf("%02d", $month);
        $date  = $year.$month;

        $c_p_ref = PoGroup::where('ref_id','LIKE',"$date%")->whereNull('from_warehouse_id')->orderby('id','DESC')->first();
        $str = @$c_p_ref->ref_id;
        $onlyIncrementGet = substr($str, 4);
        if($str == NULL)
        {
          $onlyIncrementGet = 0;
        }
        $system_gen_no = $date.str_pad(@$onlyIncrementGet + 1, 3, 0, STR_PAD_LEFT);

        if($request->target_receive_date != NULL)
        {
            $target_receive_date = str_replace("/","-",$request->target_receive_date);
            $target_receive_date =  date('Y-m-d',strtotime($target_receive_date));
        }
        else
        {
            $target_receive_date = NULL;
        }

        $po_group->ref_id                         = $system_gen_no;
        $po_group->bill_of_landing_or_airway_bill = $request->bl_awb;
        $po_group->bill_of_lading                 = '';
        $po_group->airway_bill                    = '';
        $po_group->courier                        = $request->courier;
        $po_group->target_receive_date            = $target_receive_date;
        $po_group->warehouse_id                   = $warehouse[0];
        $po_group->note                           = $request->note;
        $po_group->save();

        foreach ($po_ids as $po_id) {
            $po_group_detail                    = new PoGroupDetail;
            $po_group_detail->po_group_id       = $po_group->id;
            $po_group_detail->purchase_order_id = $po_id;
            $po_group_detail->save();

            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {
                $total_quantity +=  $p_o_d->quantity;
                if($p_o_d->order_product_id != null)
                {
                    $order_product = $p_o_d->order_product;
                    $order         = $order_product->get_order;
                    if($order->primary_status !== 3 && $order->primary_status !== 17)
                    {
                        $order_product->status = 9;
                        $order_product->save();

                        $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('status','<',9)->count();
                        if($order_products_status_count == 0)
                        {
                            $order->status = 9;
                            $order->save();
                            $order_history             = new OrderStatusHistory;
                            $order_history->user_id    = Auth::user()->id;
                            $order_history->order_id   = @$order->id;
                            $order_history->status     = 'DI(Purchasing)';
                            $order_history->new_status = 'DI(Importing)';
                            $order_history->save();
                        }
                    }
                }
            }

            if($purchase_order->exchange_rate == null)
            {
                $exch_rate = $purchase_order->PoSupplier->getCurrency->conversion_rate;
            }
            else
            {
                $exch_rate = $purchase_order->exchange_rate;
            }

            $total_import_tax_book_price        += $purchase_order->total_import_tax_book_price;
            $total_vat_actual_price             += $purchase_order->total_vat_actual_price_in_thb;
            // $total_vat_actual_price       += $purchase_order->total_vat_actual_price;
            $total_gross_weight                 += $purchase_order->total_gross_weight;
            $total_buying_price_with_vat_in_thb += $purchase_order->total_with_vat_in_thb;
            $total_buying_price_in_thb          += $purchase_order->total_in_thb;
            $po_group_level_vat_actual          += ($purchase_order->vat_amount_total / $exch_rate);
            $purchase_order->status             = 14;
            $purchase_order->save();

            // PO status history maintaining
            if($request->page == "waiting_confirm")
            {
                $page_status = Status::select('title')->whereIn('id',[12,14])->pluck('title')->toArray();
            }
            else
            {
                $page_status = Status::select('title')->whereIn('id',[13,14])->pluck('title')->toArray();
            }

            $poStatusHistory = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id    = Auth::user()->id;
            $poStatusHistory->po_id      = $purchase_order->id;
            $poStatusHistory->status     = $page_status[0];
            $poStatusHistory->new_status = $page_status[1];
            $poStatusHistory->save();
        }
        $po_group->total_quantity                     = $total_quantity;
        $po_group->po_group_import_tax_book           = floor($total_import_tax_book_price * 100) / 100;
        $po_group->po_group_vat_actual                = floor($total_vat_actual_price * 100) / 100;
        $po_group->total_buying_price_in_thb          = $total_buying_price_in_thb;
        $po_group->total_buying_price_in_thb_with_vat = $total_buying_price_with_vat_in_thb; //(new col)
        $po_group->po_group_total_gross_weight        = $total_gross_weight;
        $po_group->vat_actual_tax                     = $po_group_level_vat_actual;
        $po_group->save();

        $group_status_history              = new PoGroupStatusHistory;
        $group_status_history->user_id     = Auth::user()->id;
        $group_status_history->po_group_id = @$po_group->id;
        $group_status_history->status      = 'Created';
        $group_status_history->new_status  = 'Open Product Receiving Queue';
        $group_status_history->save();

        /*********************Here starts the new code for groups*************/
        $occurrence = null;
        $total_import_tax_book_percent = null;
        $po_group_vat_actual_percent = null;

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order->po_group_id = $po_group->id;
            $purchase_order->save();

            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {

                $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group->id)->where('supplier_id',$purchase_order->supplier_id)->first();
                if($po_group_product != null)
                {
                    $po_group_product->quantity_ordered          += @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv              += $p_o_d->quantity;
                    $po_group_product->import_tax_book_price     += $p_o_d->pod_import_tax_book_price;
                    // $po_group_product->pogpd_vat_actual_percent  += $p_o_d->pod_vat_actual_price;

                    // prices in euro
                    $po_group_product->unit_price                += $p_o_d->pod_unit_price;
                    $po_group_product->unit_price_with_vat       += $p_o_d->pod_unit_price_with_vat;
                    // to calculate total unit price
                    $amount = $p_o_d->pod_unit_price * $p_o_d->quantity;
                    if($p_o_d->discount !== null)
                    {
                        $amount = $amount - ($amount * ($p_o_d->discount / 100));
                    }
                    $po_group_product->total_unit_price  = floatval($po_group_product->total_unit_price) + ($amount !== null ? number_format((float)$amount,3,'.','') : 0);

                    // to calculate total unit price with vat
                    $amount_with_vat = $p_o_d->pod_unit_price_with_vat * $p_o_d->quantity;
                    if($p_o_d->discount !== null)
                    {
                        $amount_with_vat = $amount_with_vat - ($amount_with_vat * ($p_o_d->discount / 100));
                    }
                    $po_group_product->total_unit_price_with_vat  = floatval($po_group_product->total_unit_price_with_vat) + ($amount_with_vat !== null ? number_format((float)$amount_with_vat,3,'.','') : 0);

                    $po_group_product->pogpd_vat_actual_percent  += $p_o_d->pod_vat_actual_price_in_thb;
                    $po_group_product->total_gross_weight        += $p_o_d->pod_total_gross_weight;

                    // prices in thb
                    $po_group_product->unit_price_in_thb         += $p_o_d->unit_price_in_thb;
                    $po_group_product->total_unit_price_in_thb   += $p_o_d->total_unit_price_in_thb;

                    $po_group_product->unit_price_in_thb_with_vat += $p_o_d->unit_price_with_vat_in_thb;
                    $po_group_product->total_unit_price_in_thb_with_vat   += $p_o_d->total_unit_price_with_vat_in_thb; //(new col)
                    $po_group_product->unit_gross_weight         += $p_o_d->pod_gross_weight;
                    $po_group_product->occurrence                += 1;


                    if($p_o_d->pod_vat_actual_total_price !== 0 && $p_o_d->pod_vat_actual_total_price != 0)
                    {
                        $po_group_product->vat_weighted_percent  += ($p_o_d->pod_vat_actual_total_price / $purchase_order->vat_amount_total) * 100;
                    }
                    else
                    {
                        $po_group_product->vat_weighted_percent  += 0;
                    }

                    $po_group_product->pogpd_vat_actual_price    += ($p_o_d->pod_vat_actual/100 * $p_o_d->unit_price_in_thb);

                    $find_item_tax_value = (($p_o_d->pod_vat_actual/100) * $p_o_d->total_unit_price_in_thb);
                    if($p_o_d->total_unit_price_in_thb != 0)
                    {
                        $p_vat_percent = ($find_item_tax_value / $p_o_d->total_unit_price_in_thb) * 100;
                    }
                    else
                    {
                        $p_vat_percent = 0;
                    }

                    $po_group_product->pogpd_vat_actual_percent_val += $p_vat_percent;


                    $po_group_product->save();
                    $po_group_product->unit_gross_weight         = ($po_group_product->unit_gross_weight / 2);
                    $find_product = $p_o_d->product;
                    if($find_product != null)
                    {
                        $supplier_id = $find_product->supplier_id;
                        $default_or_last_supplier = SupplierProducts::with('supplier')->where('product_id',$find_product->id)->where('supplier_id',$supplier_id)->first();
                        if($default_or_last_supplier)
                        {
                            $p_o_d->total_extra_tax = ($default_or_last_supplier->extra_tax * $p_o_d->quantity);
                            $p_o_d->total_extra_cost = ($default_or_last_supplier->extra_cost * $p_o_d->quantity);

                            $p_o_d->unit_extra_tax = $default_or_last_supplier->extra_tax;
                            $p_o_d->unit_extra_cost = $default_or_last_supplier->extra_cost;
                            $p_o_d->save();
                        }
                    }

                    $po_group_product->save();
                }
                else
                {
                    $find_product = $p_o_d->product;
                    $po_group_product = new PoGroupProductDetail;
                    $po_group_product->po_group_id               = $po_group->id;
                    $po_group_product->po_id               = $p_o_d->po_id;
                    $po_group_product->pod_id               = $p_o_d->id;
                    $po_group_product->order_id               = $p_o_d->order_id;

                    if($purchase_order->supplier_id != null)
                    {
                        $po_group_product->supplier_id               = $purchase_order->supplier_id;
                    }
                    else
                    {
                        $po_group_product->from_warehouse_id         = $purchase_order->from_warehouse_id;
                    }
                    $po_group_product->to_warehouse_id           = $purchase_order->to_warehouse_id;
                    $po_group_product->product_id                = $p_o_d->product_id;
                    $po_group_product->quantity_ordered          = @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv              = $p_o_d->quantity;
                    $po_group_product->import_tax_book           = $p_o_d->pod_import_tax_book;
                    $po_group_product->import_tax_book_price     = $p_o_d->pod_import_tax_book_price;

                    $po_group_product->pogpd_vat_actual          = $find_product->vat;
                    $po_group_product->pogpd_vat_actual_percent  = $p_o_d->pod_vat_actual_price_in_thb;
                    // $po_group_product->pogpd_vat_actual_percent  = $p_o_d->pod_vat_actual_price;

                    if($p_o_d->pod_vat_actual_price !== 0 && $p_o_d->pod_vat_actual_price != 0)
                    {
                        if($purchase_order->vat_amount_total != 0 && $purchase_order->vat_amount_total !== 0)
                        {
                            $po_group_product->vat_weighted_percent  = ($p_o_d->pod_vat_actual_total_price / $purchase_order->vat_amount_total) * 100;
                        }
                        else
                        {
                            $po_group_product->vat_weighted_percent = 0;
                        }
                    }
                    else
                    {
                        $po_group_product->vat_weighted_percent  = 0;
                    }

                    $po_group_product->pogpd_vat_actual_price    = ($p_o_d->pod_vat_actual/100 * $p_o_d->unit_price_in_thb);


                    $find_item_tax_value = (($p_o_d->pod_vat_actual/100) * $p_o_d->total_unit_price_in_thb);
                    if($p_o_d->total_unit_price_in_thb != 0)
                    {
                        $p_vat_percent = ($find_item_tax_value / $p_o_d->total_unit_price_in_thb) * 100;
                    }
                    else
                    {
                        $p_vat_percent = 0;
                    }

                    $po_group_product->pogpd_vat_actual_percent_val    = $p_vat_percent;

                    $po_group_product->total_gross_weight        = $p_o_d->pod_total_gross_weight;
                    $po_group_product->unit_gross_weight         = $p_o_d->pod_gross_weight;
                    $po_group_product->unit_price                = $p_o_d->pod_unit_price;
                    $po_group_product->unit_price_with_vat       = $p_o_d->pod_unit_price_with_vat;

                    // to calculate total unit price
                    $amount = $p_o_d->pod_unit_price * $p_o_d->quantity;
                    $amount = $amount - ($amount * (@$p_o_d->discount / 100));
                    $po_group_product->total_unit_price  = $amount !== null ? number_format((float)$amount,3,'.','') : "--";

                    // to calculate total unit price with vat
                    $amount_with_vat = $p_o_d->pod_unit_price_with_vat * $p_o_d->quantity;
                    $amount_with_vat = $amount_with_vat - ($amount_with_vat * (@$p_o_d->discount / 100));
                    $po_group_product->total_unit_price_with_vat  = $amount_with_vat !== null ? number_format((float)$amount_with_vat,3,'.','') : "--";


                    $po_group_product->discount                  = $p_o_d->discount;
                    $po_group_product->currency_conversion_rate  = $p_o_d->currency_conversion_rate;
                    $po_group_product->unit_price_in_thb         = $p_o_d->unit_price_in_thb;
                    $po_group_product->total_unit_price_in_thb   = $p_o_d->total_unit_price_in_thb;

                    /*vat cols*/
                    $po_group_product->unit_price_in_thb_with_vat         = $p_o_d->unit_price_with_vat_in_thb;
                    $po_group_product->total_unit_price_in_thb_with_vat   = $p_o_d->total_unit_price_with_vat_in_thb;
                    /*vat cols*/

                    $po_group_product->good_condition            = $p_o_d->good_condition;
                    $po_group_product->result                    = $p_o_d->result;
                    $po_group_product->good_type                 = $p_o_d->good_type;
                    $po_group_product->temperature_c             = $p_o_d->temperature_c;
                    $po_group_product->occurrence                = 1;
                    if($find_product != null)
                    {
                        $supplier_id = $purchase_order->supplier_id != null ? $purchase_order->supplier_id : $find_product->supplier_id;
                        $default_or_last_supplier = SupplierProducts::with('supplier')->where('product_id',$find_product->id)->where('supplier_id',$supplier_id)->first();
                        // dd($purchase_order);
                        if($default_or_last_supplier)
                        {
                            $po_group_product->total_extra_tax  = ($default_or_last_supplier->extra_tax * $po_group_product->quantity_inv);
                            $po_group_product->total_extra_cost = ($default_or_last_supplier->extra_cost * $po_group_product->quantity_inv);

                            $po_group_product->unit_extra_tax   = $default_or_last_supplier->extra_tax;
                            $po_group_product->unit_extra_cost  = $default_or_last_supplier->extra_cost;

                            $p_o_d->total_extra_tax = ($default_or_last_supplier->extra_tax * $p_o_d->quantity);
                            $p_o_d->total_extra_cost = ($default_or_last_supplier->extra_cost * $p_o_d->quantity);

                            $p_o_d->unit_extra_tax = $default_or_last_supplier->extra_tax;
                            $p_o_d->unit_extra_cost = $default_or_last_supplier->extra_cost;
                            $p_o_d->save();
                        }
                    }
                    $po_group_product->save();
                    $total_import_tax_book_percent += $p_o_d->pod_import_tax_book;
                    $po_group_vat_actual_percent += $p_o_d->pod_vat_actual;
                }
            }
        }

        $po_group = PoGroup::where('id',$po_group->id)->first();

        $total_import_tax_book_price = 0;
        $total_vat_actual_price = 0;

        $total_import_tax_book_price_with_vat = 0;
        $total_vat_actual_price_with_vat = 0;

        $po_group_details = $po_group->po_group_product_details;

        foreach ($po_group_details as $po_group_detail) {
            $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
            $total_vat_actual_price += ($po_group_detail->pogpd_vat_actual_percent);

            if($po_group_detail->occurrence > 1)
            {
                $po_group_detail->pogpd_vat_actual_price    = $po_group_detail->pogpd_vat_actual_price / $po_group_detail->occurrence;
                $po_group_detail->pogpd_vat_actual_percent_val    = $po_group_detail->pogpd_vat_actual_percent_val / $po_group_detail->occurrence;

                $po_group_detail->unit_price_with_vat    = $po_group_detail->unit_price_with_vat / $po_group_detail->occurrence;
                $po_group_detail->unit_price    = $po_group_detail->unit_price / $po_group_detail->occurrence;
                $po_group_detail->unit_price_in_thb_with_vat    = $po_group_detail->unit_price_in_thb_with_vat / $po_group_detail->occurrence;
                $po_group_detail->unit_price_in_thb    = $po_group_detail->unit_price_in_thb / $po_group_detail->occurrence;
                // $po_group_detail->vat_weighted_percent = $po_group_detail->vat_weighted_percent / $po_group_detail->occurrence;
                $po_group_detail->save();

                $all_ids = PurchaseOrder::where('po_group_id',$po_group_detail->po_group_id)->where('supplier_id',$po_group_detail->supplier_id)->pluck('id');
                $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$po_group_detail->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

                if($all_record->count() > 0)
                {
                    //to update total extra tax column in po group product detail
                    $po_group_detail->total_extra_cost = $all_record->sum('total_extra_cost');
                    $po_group_detail->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                    $po_group_detail->total_extra_tax = $all_record->sum('total_extra_tax');
                    $po_group_detail->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();
                    $po_group_detail->save();
                }
            }
        }

        if($total_import_tax_book_price == 0)
        {
            foreach ($po_group_details as $po_group_detail) {
                $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                $total_import_tax_book_price += $book_tax;

                $book_tax_with_vat = (1/$count)* $po_group_detail->total_unit_price_in_thb_with_vat;
                $total_import_tax_book_price_with_vat += $book_tax_with_vat;
            }
        }

        if($total_vat_actual_price == 0)
        {
            foreach ($po_group_details as $po_group_detail) {
                $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                $total_vat_actual_price += $book_tax;

                $book_tax_with_vat = (1/$count)* $po_group_detail->total_unit_price_in_thb_with_vat;
                $total_vat_actual_price_with_vat += $book_tax_with_vat;
            }
        }

        $po_group->total_import_tax_book_percent = $total_import_tax_book_percent;
        $po_group->po_group_vat_actual_percent = $po_group_vat_actual_percent;
        $po_group->po_group_import_tax_book = floor($total_import_tax_book_price * 100) / 100;
        $po_group->po_group_vat_actual = floor($total_vat_actual_price * 100) / 100;

        $po_group->po_group_import_tax_book_with_vat = floor($total_import_tax_book_price_with_vat * 100) / 100;
        $po_group->po_group_vat_actual_with_vat = floor($total_vat_actual_price_with_vat * 100) / 100;

        $po_group->save();
        DB::commit();
        return response()->json(['success' => true]);
    }

    public function checkExistingPoGroups(Request $request)
    {
        // dd($request->all());

        $po_group = PoGroup::find($request->group_id);
        // dd($po_group->po_group_detail);

        $po_ids = $request->selected_ids;
        $po_warehouses = PurchaseOrder::whereIn('id' , $po_ids)->pluck('to_warehouse_id')->toArray();
        // dd($po_warehouses);
        for($i = 0 ; $i < sizeof($po_warehouses);$i++)
        {
            if($po_group->warehouse_id != $po_warehouses[$i])
            {
                return response()->json(['success' => false]);
            }
        }

        $total_quantity              = null;
        $total_price                 = null;
        $total_import_tax_book_price = null;
        $total_buying_price_in_thb   = null;
        $total_gross_weight          = null;
        $total_vat_actual_price             = null;
        $po_group_level_vat_actual          = null;
        $total_buying_price_with_vat_in_thb          = null;
        foreach ($po_ids as $po_id) {
            $po_group_detail                    = new PoGroupDetail;
            $po_group_detail->po_group_id       = $po_group->id;
            $po_group_detail->purchase_order_id = $po_id;
            $po_group_detail->save();

            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {
                $total_quantity +=  $p_o_d->quantity;
                if($p_o_d->order_product_id != null)
                {
                    $order_product = $p_o_d->order_product;
                    $order         = $order_product->get_order;
                    if($order->primary_status !== 3 && $order->primary_status !== 17)
                    {
                        $order_product->status = 9;
                        $order_product->save();

                        $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('status','<',9)->count();
                        if($order_products_status_count == 0)
                        {
                            $order->status = 9;
                            $order->save();
                            $order_history             = new OrderStatusHistory;
                            $order_history->user_id    = Auth::user()->id;
                            $order_history->order_id   = @$order->id;
                            $order_history->status     = 'DI(Purchasing)';
                            $order_history->new_status = 'DI(Importing)';
                            $order_history->save();
                        }
                    }
                }

            }

            if($purchase_order->exchange_rate == null)
            {
                $exch_rate = $purchase_order->PoSupplier->getCurrency->conversion_rate;
            }
            else
            {
                $exch_rate = $purchase_order->exchange_rate;
            }

            $total_import_tax_book_price += $purchase_order->total_import_tax_book_price;
            $total_gross_weight += $purchase_order->total_gross_weight;
            $total_buying_price_in_thb      += $purchase_order->total_in_thb;
            $po_group_level_vat_actual          += ($purchase_order->vat_amount_total / $exch_rate);
            $total_vat_actual_price             += $purchase_order->total_vat_actual_price_in_thb;
            $total_buying_price_with_vat_in_thb += $purchase_order->total_with_vat_in_thb;
            $purchase_order->status = 14;
            $purchase_order->save();
            // dd($purchase_order);

            // PO status history maintaining
            $page_status = Status::select('title')->whereIn('id',[13,14])->pluck('title')->toArray();

            $poStatusHistory = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id    = Auth::user()->id;
            $poStatusHistory->po_id      = $purchase_order->id;
            $poStatusHistory->status     = $page_status[0];
            $poStatusHistory->new_status = $page_status[1];
            $poStatusHistory->save();
        }
        $po_group->total_quantity              += $total_quantity;
        $po_group->po_group_import_tax_book    += floor($total_import_tax_book_price * 100) / 100;
        $po_group->po_group_vat_actual                = floor($total_vat_actual_price * 100) / 100;
        $po_group->total_buying_price_in_thb   += $total_buying_price_in_thb;
        $po_group->total_buying_price_in_thb_with_vat = $total_buying_price_with_vat_in_thb; //(new col)
        $po_group->po_group_total_gross_weight += $total_gross_weight;
        $po_group->vat_actual_tax                     = $po_group_level_vat_actual;
        $po_group->save();

        /*********************Here starts the new code for groups*************/
        $occurrence = null;
        $total_import_tax_book_percent = null;

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order->po_group_id = $po_group->id;
            $purchase_order->save();

            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {
                if ($p_o_d->new_item_status == 1) {
                    $p_o_d->new_item_status = 0;
                    $p_o_d->save();
                }
                $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group->id)->where('supplier_id',$purchase_order->supplier_id)->first();
                if($po_group_product != null)
                {
                    $find_product = $p_o_d->product;
                    $po_group_product->quantity_ordered          += @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv              += $p_o_d->quantity;
                    $po_group_product->import_tax_book_price     += $p_o_d->pod_import_tax_book_price;
                    $po_group_product->total_gross_weight        += $p_o_d->pod_total_gross_weight;
                    $po_group_product->total_unit_price_in_thb   += $p_o_d->unit_price_in_thb * $p_o_d->quantity;
                    $po_group_product->total_unit_price_in_thb_with_vat   += $p_o_d->total_unit_price_with_vat_in_thb;
                    $po_group_product->unit_gross_weight         += $p_o_d->pod_gross_weight;
                    $po_group_product->pogpd_vat_actual_percent  += $p_o_d->pod_vat_actual_price_in_thb;
                    $po_group_product->occurrence                += 1;
                    $po_group_product->save();
                    $po_group_product->unit_gross_weight         = ($po_group_product->unit_gross_weight / 2);

                    if($find_product != null)
                    {
                        $supplier_id = $find_product->supplier_id;
                        $default_or_last_supplier = SupplierProducts::with('supplier')->where('product_id',$find_product->id)->where('supplier_id',$supplier_id)->first();
                        if($default_or_last_supplier)
                        {
                            $p_o_d->total_extra_tax = ($default_or_last_supplier->extra_tax * $p_o_d->quantity);
                            $p_o_d->total_extra_cost = ($default_or_last_supplier->extra_cost * $p_o_d->quantity);

                            $p_o_d->unit_extra_tax = $default_or_last_supplier->extra_tax;
                            $p_o_d->unit_extra_cost = $default_or_last_supplier->extra_cost;
                            $p_o_d->save();
                        }
                    }

                    $po_group_product->save();
                }
                else
                {
                    $find_product = $p_o_d->product;
                    $po_group_product = new PoGroupProductDetail;
                    $po_group_product->po_group_id = $po_group->id;
                    $po_group_product->po_id               = $p_o_d->po_id;
                    $po_group_product->pod_id               = $p_o_d->id;
                    $po_group_product->order_id               = $p_o_d->order_id;
                    if($purchase_order->supplier_id != null)
                    {
                        $po_group_product->supplier_id = $purchase_order->supplier_id;
                    }
                    else
                    {
                        $po_group_product->from_warehouse_id = $purchase_order->from_warehouse_id;
                    }
                    $po_group_product->to_warehouse_id = $purchase_order->to_warehouse_id;
                    $po_group_product->product_id = $p_o_d->product_id;
                    $po_group_product->quantity_ordered = @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv = $p_o_d->quantity;
                    $po_group_product->import_tax_book = $p_o_d->pod_import_tax_book;
                    $po_group_product->import_tax_book_price = $p_o_d->pod_import_tax_book_price;
                    $po_group_product->total_gross_weight = $p_o_d->pod_total_gross_weight;
                    $po_group_product->unit_gross_weight = $p_o_d->pod_gross_weight;
                    $po_group_product->unit_price = $p_o_d->pod_unit_price;
                    $po_group_product->unit_price_with_vat       = $p_o_d->pod_unit_price_with_vat;
                    $po_group_product->pogpd_vat_actual          = $p_o_d->pod_vat_actual;
                    $po_group_product->pogpd_vat_actual_percent  = $p_o_d->pod_vat_actual_price_in_thb;

                    // to calculate total unit price
                    $amount = $p_o_d->pod_unit_price * $p_o_d->quantity;
                    $amount = $amount - ($amount * (@$p_o_d->discount / 100));

                    $po_group_product->total_unit_price = $amount !== null ? number_format((float)$amount,3,'.','') : "--";
                    // to calculate total unit price with vat
                    $amount_with_vat = $p_o_d->pod_unit_price_with_vat * $p_o_d->quantity;
                    $amount_with_vat = $amount_with_vat - ($amount_with_vat * (@$p_o_d->discount / 100));
                    $po_group_product->total_unit_price_with_vat  = $amount_with_vat !== null ? number_format((float)$amount_with_vat,3,'.','') : "--";

                    $po_group_product->discount = $p_o_d->discount;
                    $po_group_product->currency_conversion_rate = $p_o_d->currency_conversion_rate;
                    $po_group_product->unit_price_in_thb = $p_o_d->unit_price_in_thb;
                    $po_group_product->total_unit_price_in_thb = $p_o_d->unit_price_in_thb* $p_o_d->quantity;
                    /*vat cols*/
                    $po_group_product->unit_price_in_thb_with_vat         = $p_o_d->unit_price_with_vat_in_thb;
                    $po_group_product->total_unit_price_in_thb_with_vat   = $p_o_d->total_unit_price_with_vat_in_thb;
                    /*vat cols*/
                    $po_group_product->good_condition = $p_o_d->good_condition;
                    $po_group_product->result = $p_o_d->result;
                    $po_group_product->good_type = $p_o_d->good_type;
                    $po_group_product->temperature_c = $p_o_d->temperature_c;
                    $po_group_product->occurrence = 1;
                    if($find_product != null)
                    {
                        $supplier_id = $find_product->supplier_id;
                        $default_or_last_supplier = SupplierProducts::with('supplier')->where('product_id',$find_product->id)->where('supplier_id',$supplier_id)->first();
                        if($default_or_last_supplier)
                        {
                            $po_group_product->total_extra_tax = ($default_or_last_supplier->extra_tax * $po_group_product->quantity_inv);
                            $po_group_product->total_extra_cost = ($default_or_last_supplier->extra_cost * $po_group_product->quantity_inv);

                            $po_group_product->unit_extra_tax = $default_or_last_supplier->extra_tax;
                            $po_group_product->unit_extra_cost = $default_or_last_supplier->extra_cost;

                            $p_o_d->total_extra_tax = ($default_or_last_supplier->extra_tax * $p_o_d->quantity);
                            $p_o_d->total_extra_cost = ($default_or_last_supplier->extra_cost * $p_o_d->quantity);

                            $p_o_d->unit_extra_tax = $default_or_last_supplier->extra_tax;
                            $p_o_d->unit_extra_cost = $default_or_last_supplier->extra_cost;
                            $p_o_d->save();
                        }
                    }
                    $po_group_product->save();
                    $total_import_tax_book_percent += $p_o_d->pod_import_tax_book;
                }
            }
        }

        $po_group = PoGroup::where('id',$po_group->id)->first();

        $total_import_tax_book_price = 0;
        $po_group_details = $po_group->po_group_product_details;
        foreach ($po_group_details as $po_group_detail) {
            $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
        }
        if($total_import_tax_book_price == 0)
        {
            foreach ($po_group_details as $po_group_detail) {
                $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                $total_import_tax_book_price += $book_tax;
            }
        }
        // dd($total_import_tax_book_price);
        $po_group->total_import_tax_book_percent += $total_import_tax_book_percent;
        $po_group->save();

        // group product detail
        $po_group_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->where('quantity_inv','!=',0)->get();
        foreach ($po_group_details as $group_detail) {
            if($po_group->freight != null &&
                            $po_group->po_group_total_gross_weight != 0 )
            {
                $item_gross_weight     = $group_detail->total_gross_weight;
                $total_gross_weight    = $po_group->po_group_total_gross_weight;
                $total_freight         = $po_group->freight;
                $total_quantity        = $group_detail->quantity_inv;
                $freight               = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
                $group_detail->freight = $freight;
            }

            if($po_group->landing != null &&
                             $po_group->po_group_total_gross_weight != 0)
            {
                $item_gross_weight     = $group_detail->total_gross_weight;
                $total_gross_weight    = $po_group->po_group_total_gross_weight;
                $total_quantity        = $group_detail->quantity_inv;
                $total_landing         = $po_group->landing;
                $landing               = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
                $group_detail->landing = $landing;
            }

            if($po_group->tax != null)
            {
                $tax                              = $po_group->tax;
                $total_import_tax                 = $po_group->po_group_import_tax_book;
                $import_tax                       = $group_detail->import_tax_book;
                $actual_tax_percent               = ($tax/$total_import_tax*$import_tax);
                $group_detail->actual_tax_percent = $actual_tax_percent;
            }

            if($group_detail->occurrence > 1)
            {
             $all_ids = PurchaseOrder::where('po_group_id',$group_detail->po_group_id)->where('supplier_id',$group_detail->supplier_id)->pluck('id');
                $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$group_detail->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer')->get();

                if($all_record->count() > 0)
                {
                    //to update total extra tax column in po group product detail
                    $group_detail->total_extra_cost = $all_record->sum('total_extra_cost');
                    $group_detail->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                    $group_detail->total_extra_tax = $all_record->sum('total_extra_tax');
                    $group_detail->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();
                    $group_detail->save();
                }
            }

            $group_detail->save();
        }

            $po_group_details = $po_group->po_group_product_details;
            $final_book_percent = 0;
            $final_vat_actual_percent = 0;
            foreach ($po_group_details as $value)
            {
                if($value->import_tax_book != null && $value->import_tax_book != 0)
                {
                    // $final_book_percent = $final_book_percent +(($value->import_tax_book/100) * $value->total_unit_price_in_thb);
                    $check_dis = $value->discount;
                    $discount_val = 0;
                    if($check_dis != null){
                        $discount_val = $value->unit_price_in_thb * ($value->discount/100);
                    }
                    $final_book_percent = $final_book_percent + round((($value->import_tax_book/100) * ($value->unit_price_in_thb-$discount_val)) * $value->quantity_inv,2);
                }

                if($value->pogpd_vat_actual != null && $value->pogpd_vat_actual != 0)
                {
                    $final_vat_actual_percent = $final_vat_actual_percent +(($value->pogpd_vat_actual/100) * $value->total_unit_price_in_thb);
                }
            }
            foreach ($po_group_details as $group_detail) {
                if($po_group->tax !== null)
                {
                    $group_tax = $po_group->tax;
                    $check_dis = $group_detail->discount;
                    $discount_val = 0;
                    if($check_dis != null){
                        $discount_val = $group_detail->unit_price_in_thb * ($group_detail->discount/100);
                    }
                    // $find_item_tax_value = $group_detail->import_tax_book/100 * $group_detail->total_unit_price_in_thb;
                    $find_item_tax_value = round(round(($group_detail->import_tax_book / 100)*($group_detail->unit_price_in_thb - $discount_val),2) * $group_detail->quantity_inv,2);
                    if($final_book_percent != 0 && $group_tax != 0)
                    {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;

                        $cost = $find_item_tax * $group_tax;
                        if($group_tax != 0)
                        {
                            $group_detail->weighted_percent =  number_format(($find_item_tax)*100,8,'.','');
                        }
                        else
                        {
                            $group_detail->weighted_percent = 0;
                        }
                        $group_detail->save();

                        // $weighted_percent = ($group_detail->weighted_percent/100) * $group_tax;
                        $weighted_percent = round(($group_detail->weighted_percent/100) * $group_tax,2);

                        if($group_detail->quantity_inv != 0)
                        {
                            // $group_detail->actual_tax_price =  number_format(round($find_item_tax*$group_tax,2) / $group_detail->quantity_inv,2,'.','');
                            $group_detail->actual_tax_price =  round($weighted_percent / $group_detail->quantity_inv,2);
                        }
                        else
                        {
                            $group_detail->actual_tax_price =  0;
                        }
                        $group_detail->save();

                        if($group_detail->unit_price_in_thb != 0)
                        {
                            $group_detail->actual_tax_percent = number_format(($group_detail->actual_tax_price/$group_detail->unit_price_in_thb)* 100,2,'.','');
                        }
                        else
                        {
                            $group_detail->actual_tax_percent = 0;
                        }
                        $group_detail->save();
                    }
                    else if($group_tax != 0)
                    {
                        $all_pgpd = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->count();

                        $total_import_tax = $group_detail->po_group->po_group_import_tax_book;
                        $po_group_import_tax_book = $group_detail->po_group->total_import_tax_book_percent;
                        $total_buying_price_in_thb = $group_detail->po_group->total_buying_price_in_thb;

                        $import_tax = $group_detail->import_tax_book;
                        $total_price = $group_detail->total_unit_price_in_thb;
                        $book_tax = (($import_tax/100)*$total_price);


                        $check_book_tax = (($po_group_import_tax_book*$total_buying_price_in_thb)/100);


                        if($check_book_tax != 0)
                        {
                            $book_tax = round($book_tax,2);
                        }
                        else
                        {
                            $book_tax = (1/$all_pgpd)* $group_detail->total_unit_price_in_thb;
                            $book_tax = round($book_tax,2);
                        }
                        if($total_import_tax != 0)
                        {
                            $weighted = ($book_tax/$total_import_tax);
                        }
                        else
                        {
                            $weighted = 0;
                        }
                        $tax = $group_detail->po_group->tax;
                        $group_detail->actual_tax_price = number_format(($weighted*$tax),2,'.','');
                        $group_detail->save();

                        if($group_detail->total_unit_price_in_thb != 0)
                        {
                            $group_detail->actual_tax_percent = ($group_detail->actual_tax_price / $group_detail->total_unit_price_in_thb)*100;
                        }
                    }
                }

                if($po_group->vat_actual_tax !== NULL)
                {
                    $vat_actual_tax = $po_group->vat_actual_tax;

                    $find_item_tax_value = $group_detail->pogpd_vat_actual/100 * $group_detail->total_unit_price_in_thb;
                    if($final_vat_actual_percent != 0 && $vat_actual_tax != 0)
                    {
                        $find_item_tax = $find_item_tax_value / $final_vat_actual_percent;

                        $cost = $find_item_tax * $vat_actual_tax;
                        if($vat_actual_tax != 0)
                        {
                            $group_detail->vat_weighted_percent =  number_format(($cost/$vat_actual_tax)*100,4,'.','');
                        }
                        else
                        {
                            $group_detail->vat_weighted_percent = 0;
                        }
                        $group_detail->save();

                        $vat_weighted_percent = ($group_detail->vat_weighted_percent/100) * $vat_actual_tax;

                        if($group_detail->quantity_inv != 0)
                        {
                            $group_detail->pogpd_vat_actual_price =  number_format(round($find_item_tax*$vat_actual_tax,2) / $group_detail->quantity_inv,2,'.','');
                        }
                        else
                        {
                            $group_detail->pogpd_vat_actual_price =  0;
                        }
                        $group_detail->save();

                        if($group_detail->unit_price_in_thb != 0)
                        {
                            $group_detail->pogpd_vat_actual_percent_val = number_format(($group_detail->pogpd_vat_actual_price/$group_detail->unit_price_in_thb)* 100,2,'.','');
                        }
                        else
                        {
                            $group_detail->pogpd_vat_actual_percent_val = 0;
                        }
                        $group_detail->save();
                    }
                }
            }
            $po_group_id = $po_group->id;
            if($po_group_id)
            {
                $group_po_id = PoGroupDetail::where('po_group_id',$po_group_id)->first();

                if($group_po_id != null)
                {
                    $this->updateGroupViaPo($group_po_id->purchase_order_id);
                }
            }

        // group product detail end

        // dd($po_group->po_group_detail);
        return response()->json(['success' => true]);

    }

    public function purchaseOrders()
    {
        $suppliers = Supplier::where('status',1)->get();
        return view('users.purchase-order.create-purchase-orders',compact('suppliers'));
    }

    public function createTransferDoc()
    {
        $draft_td = new DraftPurchaseOrder;
        $draft_td->status = 23;
        if(Auth::user()->role_id == 6)
        {
            $draft_td->to_warehouse_id = Auth::user()->warehouse_id;
        }
        $draft_td->created_by = Auth::user()->id;
        $draft_td->save();
        return redirect()->route("get-draft-td",$draft_td->id);
    }

    public function getDraftTd($id)
    {
        // dd($this->user);
        // $po_setting = PurchaseOrderSetting::first();
        $draft_po = DraftPurchaseOrder::find($id);
        if ($draft_po) {
            $getPoNote = DraftPurchaseOrderNote::where('po_id',$id)->first();
            $company_info = Company::with('getcountry','getstate')->where('id',$draft_po->createdBy->company_id)->first();

            if (Auth::user()->role_id == 6)
            {
                $warehouses = Warehouse::where('id', '!=', Auth::user()->warehouse_id)->orderBy('warehouse_title')->get();
                $warehousesTo = Warehouse::where('status',1)->orderBy('warehouse_title')->get();
            }
            else
            {
                $warehousesTo = Warehouse::orderBy('warehouse_title')->get();
                $warehouses = Warehouse::where('status',1)->orderBy('warehouse_title')->get();
            }

            $paymentTerms = PaymentTerm::all();
            $sub_total     = 0 ;
            $query         = DraftPurchaseOrderDetail::where('po_id',$id)->get();

            foreach ($query as  $value)
            {
                $unit_price = $value->pod_unit_price;
                $sub        = $value->quantity * $unit_price - (($value->quantity * $unit_price) * (@$value->discount/100));
                $sub_total  += $sub;
            }

            $checkDraftPoDocs = DraftPurchaseOrderDocument::where('po_id',$id)->get()->count();
            $total_system_units = Unit::whereNotNull('id')->count();

            $itemsCount = DraftPurchaseOrderDetail::where('is_billed','Product')->where('po_id',$id)->sum('quantity');

            $allow_custom_invoice_number = '';
            $show_custom_line_number = '';
            $show_supplier_invoice_number = '';
            $globalAccessConfig4 = QuotationConfig::where('section','groups_management_page')->first();
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

            $display_prods = ColumnDisplayPreference::where('type', 'draft_td')->where('user_id', Auth::user()->id)->first();

            return view('users.purchase-order.create-direct-td',compact('id','draft_po','sub_total','checkDraftPoDocs','company_info','warehouses','getPoNote','paymentTerms','warehousesTo','total_system_units','itemsCount','allow_custom_invoice_number','show_custom_line_number','show_supplier_invoice_number', 'display_prods'));
        }
    }

    public function createDirectPurchaseOrder()
    {
        $draft_po = new DraftPurchaseOrder;
        $draft_po->status = 16;
        $draft_po->created_by = Auth::user()->id;
        $draft_po->save();
        return redirect()->route("get-draft-po",$draft_po->id);
    }

    public function getDraftPo($id)
    {
        $po_setting = PurchaseOrderSetting::first();
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'draft_po_detail')->first();
        $display_purchase_list = ColumnDisplayPreference::where('type', 'draft_po_detail')->where('user_id', Auth::user()->id)->first();
        $suppliers    = Supplier::where('status',1)->orderBy('reference_name')->get();
        $draft_po     = DraftPurchaseOrder::find($id);
        $getPoNote    = DraftPurchaseOrderNote::where('po_id',$id)->first();
        $company_info = Company::with('getcountry','getstate')->where('id',$draft_po->createdBy->company_id)->first();
        $warehouses   = Warehouse::where('status',1)->orderBy('warehouse_title')->get();
        $paymentTerms = PaymentTerm::all();
        $sub_total    = 0 ;
        $query        = DraftPurchaseOrderDetail::where('po_id',$id)->get();
        foreach ($query as  $value)
        {
            $sub = $value->quantity * $value->pod_unit_price - (($value->quantity * $value->pod_unit_price) * (@$value->discount/100));
            $sub_total += $sub;
        }

        $quotation_config        = QuotationConfig::where('section','purchase_order')->first();
        $hidden_by_default       = '';
        $columns_prefrences      = null;
        $shouldnt_show_columns   = [4,13,14,20];
        $hidden_columns          = null;
        $hidden_columns_by_admin = [];
        if($quotation_config == null)
        {
            $hidden_by_default='';
        }
        else
        {
            $dislay_prefrences=$quotation_config->display_prefrences;
            $hide_columns=$quotation_config->show_columns;
            if($quotation_config->show_columns!=null)
            {
                $hidden_columns=json_decode($hide_columns);
                if(!in_array($hidden_columns,$shouldnt_show_columns))
                {
                    $hidden_columns=array_merge($hidden_columns,$shouldnt_show_columns);
                    $hidden_columns=implode (",", $hidden_columns);
                    $hidden_columns_by_admin=explode (",", $hidden_columns);
                }
            }
            else
            {
                $hidden_columns=implode (",", $shouldnt_show_columns);
                $hidden_columns_by_admin=explode (",", $hidden_columns);
            }
            $user_hidden_columns=[];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','draft_po_detail')->where('user_id',Auth::user()->id)->first();
            if($not_visible_columns!=null)
            {
                $user_hidden_columns = $not_visible_columns->hide_columns;
            }
            else
            {
                $user_hidden_columns="";
            }
            $user_plus_admin_hidden_columns=$user_hidden_columns.','.$hidden_columns;
            $columns_prefrences=json_decode($quotation_config->display_prefrences);
            $columns_prefrences=implode(",",$columns_prefrences);
        }
        $checkDraftPoDocs = DraftPurchaseOrderDocument::where('po_id',$id)->get()->count();
        $globalAccessConfig3 = QuotationConfig::where('section','target_ship_date')->first();
        if($globalAccessConfig3!=null)
        {
            $targetShipDate=unserialize($globalAccessConfig3->print_prefrences);
        }
        else
        {
            $targetShipDate=null;
        }
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();

        $display_prods = ColumnDisplayPreference::where('type', 'draft_po_detail')->where('user_id', Auth::user()->id)->first();

        return view('users.purchase-order.create-direct-po',compact('id','suppliers','table_hide_columns','display_purchase_list','draft_po','sub_total','checkDraftPoDocs','po_setting','company_info','warehouses','getPoNote','paymentTerms','hidden_by_default','user_plus_admin_hidden_columns','columns_prefrences','hidden_columns_by_admin','targetShipDate','dummy_data', 'display_prods'));
    }

    public function uploadBulkProductsInPos(Request $request)
    {
        $import = new BulkProductPurchaseOrder($request->d_po_id,$request->d_supplier_id);
        Excel::import($import ,$request->file('product_excel'));
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id,'Draft PO Detail Page',$request->file('product_excel'));
        if($import->result == "false")
        {
            return response()->json(['success' => true, 'msg' => $import->response, 'errorMsg' => $import->error_msgs, 'sub_total' => $import->sub_total,'total' => $import->total, 'vat_total' => $import->vat_total]);
        }
        if($import->result == "true")
        {
            return response()->json(['success' => false, 'msg' => $import->response, 'errorMsg' => $import->error_msgs, 'sub_total' => $import->sub_total]);
        }
        if($import->result == "withissues")
        {
            return response()->json(['success' => "withissues", 'msg' => $import->response, 'errorMsg' => $import->error_msgs, 'sub_total' => $import->sub_total]);
        }
    }

    public function uploadBulkProductsInPosDetail(Request $request)
    {
        $import = new BulkProductPurchaseOrderDetail($request->d_po_id,$request->d_supplier_id);
        // Excel::import($import ,$request->file('product_excel'));

        try {
            Excel::import($import ,$request->file('product_excel'));
          } catch (\Exception $e) {
            return response()->json(['success'=>false,'msg'=> 'Please Upload Valid File']);
          }

        ImportFileHistory::insertRecordIntoDb(Auth::user()->id,'PO Detail Page',$request->file('product_excel'));
        if($import->result == "false")
        {
            return response()->json(['success' => true, 'msg' => $import->response, 'errorMsg' => $import->error_msgs, 'sub_total' => $import->sub_total,'total' => $import->total, 'vat_total' => $import->vat_total]);
        }
        if($import->result == "true")
        {
            return response()->json(['success' => false, 'msg' => $import->response, 'errorMsg' => $import->error_msgs, 'sub_total' => $import->sub_total,'total' => $import->total, 'vat_total' => $import->vat_total]);
        }
        if($import->result == "withissues")
        {
            return response()->json(['success' => "withissues", 'msg' => $import->response, 'errorMsg' => $import->error_msgs, 'sub_total' => $import->sub_total,'total' => $import->total, 'vat_total' => $import->vat_total]);
        }
    }

    public function checkIfProductExistOnDpo(Request $request)
    {
        $po_detail = DraftPurchaseOrderDetail::where('po_id',$request->draft_po_id)->get();
        if($po_detail->count() > 0)
        {
            return response()->json(['success' => false]);
        }
        else
        {
            $po = DraftPurchaseOrder::find($request->draft_po_id);
            $po->supplier_id = NULL;
            $po->save();
            $suppliers = Supplier::where('status',1)->get();

            $html = '<select class="form-control js-states state-tags-2 mb-2 add-supp" name="supplier">
              <option value="new">Choose Supplier</option>';
               foreach($suppliers as $supplier)
               {
            $html .= '<option value="'.$supplier->id.'"> '.$supplier->reference_name.' </option>';
              }
            $html .= '</select>
            <div class="supplier_info"></div>';

            return response()->json(['success' => true, 'html' => $html]);
        }
    }

    public function AddSupplierToDraftPo(Request $request)
    {
        // dd($request->all());
        $draft_po = DraftPurchaseOrder::find($request->draft_po_id);

        // new code starts here 03 March,2020
        $Stype = explode('-', $request->supplier_id);
        if($Stype[0] == 's')
        {
            $draft_po->from_warehouse_id = NULL;
            $draft_po->supplier_id = $Stype[1];
            $draft_po->target_receive_date = null;
            $draft_po->payment_due_date = null;
            $draft_po->invoice_date = null;
            $draft_po->exchange_rate = $draft_po->getSupplier->getCurrency->conversion_rate;

            if($draft_po->getSupplier->credit_term != null)
            {
                $draft_po->payment_terms_id = $draft_po->getSupplier->credit_term;

            }
            else
            {
                $draft_po->payment_terms_id = null;
            }
        }
        if($Stype[0] == 'w')
        {
            $draft_po->supplier_id = NULL;
            $draft_po->from_warehouse_id = $Stype[1];
        }
        // new code ends here
        $draft_po->save();

        $supplier = Supplier::find($Stype[1]);
        if($Stype[0] == 's')
        {

            $html = '<i class="fa fa-edit edit-address change_supplier" title="Change Supply From" style="cursor: pointer;"></i>';
            if($supplier->logo != null && file_exists( public_path() . '/uploads/sales/customer/logos/' . @$supplier->logo)){
            $html .= '
            <div class="d-flex align-items-center mb-1">
              <div><img src="'.asset('public/uploads/sales/customer/logos'.'/'.@$supplier->logo).'" class="img-fluid" align="big-qummy" style="width: 65px;
            height: auto;" ></div>';
            }else{

             $html .= '
            <div class="d-flex align-items-center mb-1">
              <div><img src="'.asset('public/uploads/logo/temp-logo.png').'" class="img-fluid" align="big-qummy" style="width: 65px;
            height: auto;" ></div>';
            }
            $html .='<div class="pl-2 comp-name" data-supplier-id="'.@$supplier->id.'"><p>'.@$supplier->reference_name.'</p> </div>
            </div>


             <p class="mb-1"> '.@$supplier->address_line_1.' '.@$supplier->address_line_2.','.@$supplier->getcountry->name.','.@$supplier->getstate->name .','.@$supplier->city .','.@$supplier->postalcode .'</p>
             <ul class="d-flex list-unstyled">
                <li><i class="fa fa-phone pr-2"></i> '.@$supplier->phone.'</li>
                <li class="pl-3"><i class="fa fa-envelope pr-2"></i> '.@$supplier->email.'</li>
              </ul>
            </div>';

            $type = "PO";
            return response()->json(['html' => $html, 'type' => $type , 'payment_term' => $supplier->getpayment_term, 'exchange_rate' => (1 / $draft_po->exchange_rate) ]);
        }
        if($Stype[0] == 'w')
        {
            $warehouse = Warehouse::find($Stype[1]);

            $html = '<i class="fa fa-edit edit-address change_supplier" title="Change Supply From" style="cursor: pointer;"></i>';
            $html .= '
            <div class="d-flex align-items-center mb-1">
              <div><img src="'.asset('public/img/warehouse-logo.png').'" class="img-fluid" align="big-qummy" style="width: 65px;
            height: auto;" ></div>';

            $html .='<div class="pl-2 comp-name" data-supplier-id="'.@$warehouse->id.'"><p>'.@$warehouse->warehouse_title.'</p> </div>
            </div>

             <p class="mb-1"> '.@$warehouse->getCompany->billing_address.','.@$warehouse->getCompany->getcountry->name.','.@$warehouse->getCompany->getstate->name .','.@$warehouse->getCompany->city .','.@$warehouse->getCompany->postalcode .'</p>
             <ul class="d-flex list-unstyled">
                <li><i class="fa fa-phone pr-2"></i> '.@$warehouse->getCompany->billing_phone.'</li>
                <li class="pl-3"><i class="fa fa-envelope pr-2"></i> '.@$warehouse->getCompany->billing_email.'</li>
              </ul>
            </div>';

            $type = "TD";
            return response()->json(['html' => $html, 'type' => $type]);
        }

    }

    public function autocompleteFetchProduct(Request $request)
    {
        // dd($request->all());
        $params = $request->except('_token');
        $detail = [];
        if($request->get('query'))
        {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            $supplier_query = Supplier::query();

                $product_query = $product_query->where(function($q) use($search_box_value){
                    foreach ($search_box_value as $value) {
                        $q->where('short_desc', 'LIKE', '%'.$value.'%');
                    }
                })->orWhere('refrence_code', 'LIKE', $query.'%')->orWhere('brand', 'LIKE', $query.'%');


                if($request->supplier_id != NULL)
                {
                    $supplier_query = $supplier_query->where('id', $request->supplier_id);
                }
                else
                {
                    $supplier_query = $supplier_query->where('reference_name', 'like', '%'.$query.'%');
                }



            $product_query  = $product_query->pluck('id')->toArray();
            $supplier_query = $supplier_query->pluck('id')->toArray();

            if(! empty($product_query) || ! empty($supplier_query) )
            {
                $product_detail = Product::query();
                if(! empty($supplier_query))
                {
                    $product_detail = $product_detail->whereIn('id', SupplierProducts::select('product_id')->where('supplier_id',$supplier_query)->where('is_deleted',0)->pluck('product_id'));
                }

                if(! empty($product_query))
                {
                    $product_detail->where(function ($q) use ($product_query) {
                        $q->orWhereIn('id', $product_query);
                    });
                }


                $detail = $product_detail->where('status',1)->orderBy('id','ASC')->get();
                // dd($detail);
            }
            if(!empty($detail) && sizeof($detail) != 0 )
            {
                $variable=Variable::where('slug','type')->first();
                if($variable->terminology != null){
                  $type = $variable->terminology;
                }else{
                  $type = $variable->standard_name;
                }
                $product_description=Variable::where('slug','product_description')->first();
                if($product_description->terminology != null){
                  $desc = $product_description->terminology;
                }else{
                  $desc = $product_description->standard_name;
                }
                $our_reference_number=Variable::where('slug','our_reference_number')->first();
                if($our_reference_number->terminology != null){
                  $product_ref = $our_reference_number->terminology;
                }else{
                  $product_ref = $our_reference_number->standard_name;
                }
                $terminology_brand=Variable::where('slug','brand')->first();
                if($terminology_brand->terminology != null){
                  $brand = $terminology_brand->terminology;
                }else{
                  $brand = $terminology_brand->standard_name;
                }
                $terminology_note=Variable::where('slug','note_two')->first();
                if($terminology_note->terminology != null){
                  $note = $terminology_note->terminology;
                }else{
                  $note = $terminology_note->standard_name;
                }

                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px; height: 300px; overflow: scroll; ">';
                $output .= '<li>
                   <a href="javascript:void(0);" class="search_product fontbold d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;visibility:hidden;"></i>';

                $output .= '<div class="supplier_ref pr-2">Sup Ref#</div><div class="pf pr-2">'.$product_ref.'</div><div class="supplier pr-2">Supplier</div><div class="p_winery">'.$brand.'</div><div class="description">'.$desc.'</div><div class="p_type">'.$type.'</div><div class="p_notes">'.$note.'</div></a>
                </li>';
                    foreach($detail as $row)
                    {
                        $getProductDefaultSupplier = $row->supplier_products->where('supplier_id',$row->supplier_id)->first();

                        if($request->draft_po_id == null){
                           $output .= '
                            <li>';
                            $output .= '<a href="'.url('get-product-detail',$row->id).'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>'.$row->short_desc.'</a></li>
                            ';
                        }
                        else{
                        $output .= '
                            <li>
                            <a href="javascript:void(0);" data-draft_po_id="'.$request->draft_po_id.'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>';

                            if($getProductDefaultSupplier->product_supplier_reference_no !== NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">'.$getProductDefaultSupplier->product_supplier_reference_no.'</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "N.A").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div>');
                            }
                            elseif($getProductDefaultSupplier->product_supplier_reference_no == NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">-</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "N.A").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div>');
                            }
                        $output .= '</a></li>';
                        }
                    }
                $output .= '</ul>';
                echo $output;
            }
            else
            {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }

            }

            else{
                echo '';
            }

    }

    public function autocompleteFetchProductForTD(Request $request)
    {
        // dd($request->all());
        $params = $request->except('_token');
        $detail = [];
        if($request->get('query'))
        {
            $query = $request->get('query');
            // $search_box_value = explode('-', $query);
            $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            // $category_query = ProductCategory::query();
            $supplier_query = Supplier::query();

            // foreach ($search_box_value as $result)
            // {
                // $product_query = $product_query->orWhere('short_desc', 'LIKE', '%'.$query.'%')->orWhere('refrence_code', 'LIKE', $query.'%');
                $product_query = $product_query->where(function($q) use($search_box_value){
                    foreach ($search_box_value as $value) {
                        $q->where('short_desc', 'LIKE', '%'.$value.'%');
                    }
                })->orWhere('refrence_code', 'LIKE', $query.'%')->orWhere('brand', 'LIKE', $query.'%');

                // $category_query = $category_query->orWhere('title', 'LIKE', '%'.$result.'%');

                $supplier_query = $supplier_query->orWhere('reference_name', 'LIKE', '%'.$query.'%');
            // }


            $product_query  = $product_query->pluck('id')->toArray();
            // $category_query = $category_query->pluck('id')->toArray();
            $supplier_query = $supplier_query->pluck('id')->toArray();

            // dd($product_query,$category_query,$supplier_query);

            if(! empty($product_query) || ! empty($supplier_query) )
            {
                $product_detail = Product::query();
                if(! empty($supplier_query))
                {
                    $product_detail = $product_detail->whereIn('id', SupplierProducts::select('product_id')->where('supplier_id',$supplier_query)->pluck('product_id'));
                }

                if(! empty($product_query))
                {
                    $product_detail->where(function ($q) use ($product_query) {
                        $q->orWhereIn('id', $product_query);
                    });
                }


                $detail = $product_detail->where('status',1)->orderBy('id','ASC')->get();
                // dd($detail);
            }
            if(!empty($detail) && sizeof($detail) != 0 )
            {
                $variable=Variable::where('slug','type')->first();
                if($variable->terminology != null){
                  $type = $variable->terminology;
                }else{
                  $type = $variable->standard_name;
                }
                $product_description=Variable::where('slug','product_description')->first();
                if($product_description->terminology != null){
                  $desc = $product_description->terminology;
                }else{
                  $desc = $product_description->standard_name;
                }
                $our_reference_number=Variable::where('slug','our_reference_number')->first();
                if($our_reference_number->terminology != null){
                  $product_ref = $our_reference_number->terminology;
                }else{
                  $product_ref = $our_reference_number->standard_name;
                }
                $terminology_brand=Variable::where('slug','brand')->first();
                if($terminology_brand->terminology != null){
                  $brand = $terminology_brand->terminology;
                }else{
                  $brand = $terminology_brand->standard_name;
                }
                $terminology_note=Variable::where('slug','note_two')->first();
                if($terminology_note->terminology != null){
                  $note = $terminology_note->terminology;
                }else{
                  $note = $terminology_note->standard_name;
                }

                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px; height: 300px; overflow: scroll; ">';
                $output .= '<li>
                   <a href="javascript:void(0);" class="search_product fontbold d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;visibility:hidden;"></i>';
                $output .= '<div class="supplier_ref pr-2">Sup Ref#</div><div class="pf pr-2">'.$product_ref.'</div><div class="supplier pr-2">Supplier</div><div class="p_winery">'.$brand.'</div><div class="description">'.$desc.'</div><div class="p_type">'.$type.'</div><div class="p_notes">'.$note.'</div><span class="rsv pl-2">Rsv</span><span class="pStock pl-2">Stock</span></a>
                </li>';
                    foreach($detail as $row)
                    {
                        $warehouse_products = WarehouseProduct::where('product_id',$row->id)->where('warehouse_id',$request->warehouse_id)->first();

                        $getProductDefaultSupplier = $row->supplier_products->where('supplier_id',$row->supplier_id)->first();

                        if($request->draft_po_id == null){
                           $output .= '
                            <li>';
                            $output .= '<a href="'.url('get-product-detail',$row->id).'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>'.$row->short_desc.'</a></li>
                            ';
                        }
                        else{
                        $output .= '
                            <li>
                            <a href="javascript:void(0);" data-draft_po_id="'.$request->draft_po_id.'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>';

                            if($getProductDefaultSupplier->product_supplier_reference_no !== NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">'.$getProductDefaultSupplier->product_supplier_reference_no.'</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "-").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div><span class="rsv pl-2">'.($warehouse_products->reserved_quantity != null ? round($warehouse_products->reserved_quantity,3) : 0).'</span><span class="pStock pl-2">'.($warehouse_products->current_quantity != null ? round($warehouse_products->current_quantity,3) : 0).'</span> ');
                            }
                            elseif($getProductDefaultSupplier->product_supplier_reference_no == NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">-</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "-").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div><span class="rsv pl-2">'.($warehouse_products->reserved_quantity != null ? round($warehouse_products->reserved_quantity,3) : 0).'</span><span class="pStock pl-2">'.($warehouse_products->current_quantity != null ? round($warehouse_products->current_quantity,3) : 0).'</span> ');
                            }
                        $output .= '</a></li>';
                        }
                    }
                $output .= '</ul>';
                echo $output;
            }
            else
            {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }

            }

            else{
                echo '';
            }

    }

    public function autocompleteFetchProductsForPurchaseOrder(Request $request)
    {
        $params = $request->except('_token');
        $detail = [];
        if($request->get('query'))
        {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            $supplier_query = Supplier::query();

                $product_query = $product_query->where(function($q) use($search_box_value){
                    foreach ($search_box_value as $value) {
                        $q->where('short_desc', 'LIKE', '%'.$value.'%');
                    }
                })->orWhere('refrence_code', 'LIKE', $query.'%')->orWhere('brand', 'LIKE', $query.'%');

                if($request->supplier_id != NULL)
                {
                    $supplier_query = $supplier_query->where('id', $request->supplier_id);
                }
                else
                {
                    $supplier_query = $supplier_query->orWhere('reference_name', 'like', '%'.$query.'%');
                }


            $product_query  = $product_query->pluck('id')->toArray();
            $supplier_query = $supplier_query->pluck('id')->toArray();

            if(! empty($product_query) || ! empty($supplier_query) )
            {
                $product_detail = Product::query();

                if(! empty($supplier_query))
                {
                    $product_detail = $product_detail->whereIn('id', SupplierProducts::select('product_id')->where('supplier_id',$supplier_query)->pluck('product_id'));
                }


                if(! empty($product_query))
                {
                    $product_detail->Where(function ($q) use ($product_query) {
                        $q->orWhereIn('id', $product_query);
                    });
                }

                $product_detail->where('status',1)->orderBy('id','ASC');
                $detail = $product_detail->get();
            }
            if(!empty($detail) && sizeof($detail) != 0)
            {
                $variable=Variable::where('slug','type')->first();
                if($variable->terminology != null){
                  $type = $variable->terminology;
                }else{
                  $type = $variable->standard_name;
                }
                $product_description=Variable::where('slug','product_description')->first();
                if($product_description->terminology != null){
                  $desc = $product_description->terminology;
                }else{
                  $desc = $product_description->standard_name;
                }
                $our_reference_number=Variable::where('slug','our_reference_number')->first();
                if($our_reference_number->terminology != null){
                  $product_ref = $our_reference_number->terminology;
                }else{
                  $product_ref = $our_reference_number->standard_name;
                }
                $terminology_brand=Variable::where('slug','brand')->first();
                if($terminology_brand->terminology != null){
                  $brand = $terminology_brand->terminology;
                }else{
                  $brand = $terminology_brand->standard_name;
                }
                $terminology_note=Variable::where('slug','note_two')->first();
                if($terminology_note->terminology != null){
                  $note = $terminology_note->terminology;
                }else{
                  $note = $terminology_note->standard_name;
                }

                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                $output .= '<li>
                   <a href="javascript:void(0);" class="search_product fontbold d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;visibility:hidden;"></i>';

                $output .= '<div class="supplier_ref pr-2">Sup Ref#</div><div class="pf pr-2">'.$product_ref.'</div><div class="supplier pr-2">Supplier</div><div class="p_winery">'.$brand.'</div><div class="description">'.$desc.'</div><div class="p_type">'.$type.'</div><div class="p_notes">'.$note.'</div></a>
                </li>';
                    foreach($detail as $row)
                    {
                        $getProductDefaultSupplier = $row->supplier_products->where('supplier_id',$row->supplier_id)->first();

                        if($request->po_id == null){
                           $output .= '
                            <li>';
                            $output .= '<a href="'.url('get-product-detail',$row->id).'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>'.$row->short_desc.'</a></li>
                            ';
                        }
                        else{
                        $output .= '
                            <li>

                            <a href="javascript:void(0);" data-po_id="'.$request->po_id.'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>';

                            if($getProductDefaultSupplier->product_supplier_reference_no !== NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">'.$getProductDefaultSupplier->product_supplier_reference_no.'</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "-").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div>');
                            }
                            elseif($getProductDefaultSupplier->product_supplier_reference_no == NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">-</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "-").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div>');
                            }
                        $output .= '</a></li>';
                        }
                    }
                $output .= '</ul>';
                echo $output;
            }
            else
            {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        }
        else
        {
            echo '';
        }
    }

    public function autocompleteFetchProductsForTransferDoc(Request $request)
    {
        $params = $request->except('_token');
        $detail = [];
        if($request->get('query'))
        {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            // $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            // $category_query = ProductCategory::query();
            $supplier_query = Supplier::query();

            // foreach ($search_box_value as $result)
            // {
                // $product_query = $product_query->orWhere('short_desc', 'LIKE', '%'.$query.'%')->orWhere('refrence_code', 'LIKE', $query.'%');
                $product_query = $product_query->where(function($q) use($search_box_value){
                    foreach ($search_box_value as $value) {
                        $q->where('short_desc', 'LIKE', '%'.$value.'%');
                    }
                })->orWhere('refrence_code', 'LIKE', $query.'%')->orWhere('brand', 'LIKE', $query.'%');

                // $category_query = $category_query->orWhere('title', 'LIKE', '%'.$result.'%');

                $supplier_query = $supplier_query->orWhere('reference_name', 'LIKE', '%'.$query.'%');
            // }

            $product_query  = $product_query->pluck('id')->toArray();
            // $category_query = $category_query->pluck('id')->toArray();
            $supplier_query = $supplier_query->pluck('id')->toArray();

            if(! empty($product_query) || ! empty($supplier_query) )
            {
                $product_detail = Product::query();

                if(! empty($supplier_query))
                {
                    $product_detail = $product_detail->whereIn('id', SupplierProducts::select('product_id')->where('supplier_id',$supplier_query)->pluck('product_id'));
                }

                if(! empty($product_query))
                {
                    $product_detail->Where(function ($q) use ($product_query) {
                        $q->orWhereIn('id', $product_query);
                    });
                }

                $product_detail->where('status',1)->orderBy('id','ASC');
                $detail = $product_detail->get();
            }
            if(!empty($detail) && sizeof($detail) != 0)
            {
                $variable=Variable::where('slug','type')->first();
                if($variable->terminology != null){
                  $type = $variable->terminology;
                }else{
                  $type = $variable->standard_name;
                }
                $product_description=Variable::where('slug','product_description')->first();
                if($product_description->terminology != null){
                  $desc = $product_description->terminology;
                }else{
                  $desc = $product_description->standard_name;
                }
                $our_reference_number=Variable::where('slug','our_reference_number')->first();
                if($our_reference_number->terminology != null){
                  $product_ref = $our_reference_number->terminology;
                }else{
                  $product_ref = $our_reference_number->standard_name;
                }
                $terminology_brand=Variable::where('slug','brand')->first();
                if($terminology_brand->terminology != null){
                  $brand = $terminology_brand->terminology;
                }else{
                  $brand = $terminology_brand->standard_name;
                }
                $terminology_note=Variable::where('slug','note_two')->first();
                if($terminology_note->terminology != null){
                  $note = $terminology_note->terminology;
                }else{
                  $note = $terminology_note->standard_name;
                }

                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                $output .= '<li>
                   <a href="javascript:void(0);" class="search_product fontbold d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;visibility:hidden;"></i>';

                $output .= '<div class="supplier_ref pr-2">Sup Ref#</div><div class="pf pr-2">'.$product_ref.'</div><div class="supplier pr-2">Supplier</div><div class="p_winery">'.$brand.'</div><div class="description">'.$desc.'</div><div class="p_type">'.$type.'</div><div class="p_notes">'.$note.'</div><span class="rsv pl-2">Rsv</span><span class="pStock pl-2">Stock</span></a>
                </li>';
                    foreach($detail as $row)
                    {
                        $warehouse_products = WarehouseProduct::where('product_id',$row->id)->where('warehouse_id',$request->warehouse_id)->first();
                        $getProductDefaultSupplier = $row->supplier_products->where('supplier_id',$row->supplier_id)->first();

                        if($request->po_id == null){
                           $output .= '
                            <li>';
                            $output .= '<a href="'.url('get-product-detail',$row->id).'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>'.$row->short_desc.'</a></li>
                            ';
                        }
                        else{
                        $output .= '
                            <li>

                            <a href="javascript:void(0);" data-po_id="'.$request->po_id.'" data-prod_id="'.$row->id.'" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>';
                            if($getProductDefaultSupplier->product_supplier_reference_no !== NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">'.$getProductDefaultSupplier->product_supplier_reference_no.'</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "-").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div><span class="rsv pl-2">'.($warehouse_products->reserved_quantity != null ? round($warehouse_products->reserved_quantity,3) : 0).'</span><span class="pStock pl-2">'.($warehouse_products->current_quantity != null ? round($warehouse_products->current_quantity,3) : 0).'</span> ');
                            }
                            elseif($getProductDefaultSupplier->product_supplier_reference_no == NULL)
                            {
                                $output .= ('<div class="supplier_ref pr-2">-</div><div class="pf pr-2">'.$row->refrence_code.'</div><div class="supplier pr-2">'.@$row->def_or_last_supplier->reference_name.'</div><div class="p_winery">'.($row->brand != null ? $row->brand : "-").'</div><div class="description">'.$row->short_desc.'</div><div class="p_type">'.($row->type_id != null ? $row->productType->title : "-").'</div><div class="p_notes">'.($row->product_notes != null ? $row->product_notes : "-").'</div><span class="rsv pl-2">'.($warehouse_products->reserved_quantity != null ? round($warehouse_products->reserved_quantity,3) : 0).'</span><span class="pStock pl-2">'.($warehouse_products->current_quantity != null ? round($warehouse_products->current_quantity,3) : 0).'</span> ');

                            }
                        $output .= '</a></li>';
                        }
                    }
                $output .= '</ul>';
                echo $output;
            }
            else
            {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        }
        else
        {
            echo '';
        }
    }

    public function addProdByRefrenceNumber(Request $request)
    {
        return DraftPOInsertUpdateHelper::addProdByRefrenceNumber($request);
    }

    public function addProdByRefrenceNumberInPoDetail(Request $request)
    {
        return PODetailCRUDHelper::addProdByRefrenceNumberInPoDetail($request);
    }

    public function addProdToPo(Request $request)
    {
        return DraftPOInsertUpdateHelper::addProdToPo($request);
    }

    public function addProdToPoDetail(Request $request)
    {
        $order = PurchaseOrder::find($request->po_id);
        $product_arr = explode(',', $request->selected_products);
        $add_products_to_po_detail = null;
        foreach($product_arr as $product)
        {
            $product = Product::find($product);
            if($product) {
                if($order->supplier_id != NULL && $order->from_warehouse_id == NULL)
                {
                    $supplier_products = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$order->supplier_id)->count();
                    if($supplier_products == 0)
                    {
                        return response()->json(['success' => false, 'successmsg' => $order->PoSupplier->reference_name.' do not provide us '.$product->name.' ( '.$product->refrence_code.' )']);
                    }

                    $add_products_to_po_detail = new PurchaseOrderDetail;
                    $add_products_to_po_detail->po_id = $request->po_id;

                    $add_products_to_po_detail->pod_import_tax_book  = $product->import_tax_book;
                    $add_products_to_po_detail->pod_vat_actual       = $request->purchasing_vat == null ? $product->vat : null;

                    $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$order->supplier_id)->first();

                    $add_products_to_po_detail->pod_unit_price        = $gettingProdSuppData->buying_price;
                    $add_products_to_po_detail->pod_gross_weight      = $gettingProdSuppData->gross_weight;
                    // $add_products_to_po_detail->pod_unit_price        = $checkProduct->pod_unit_price;
                    $add_products_to_po_detail->last_updated_price_on = $product->last_price_updated_date;

                    /*vat calculations*/
                    $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price, $request->purchasing_vat == null ? $product->vat : null);
                    $add_products_to_po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                    $add_products_to_po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                    /*convert val to thb's*/
                    $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->po_id, $vat_calculations['vat_amount']);
                    $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                    $add_products_to_po_detail->good_type               = $product->type_id;
                    $add_products_to_po_detail->temperature_c           = $product->product_temprature_c;
                    $add_products_to_po_detail->product_id              = $product->id;
                    $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                    $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;

                    if ($order->status == 14) {
                        $add_products_to_po_detail->new_item_status = 1;
                    }
                    $add_products_to_po_detail->save();

                    $order_history = new PurchaseOrdersHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->reference_number = $product->refrence_code;
                    $order_history->old_value = "";
                    $order_history->new_value = "New Item";
                    $order_history->po_id = $request->po_id;
                    $order_history->save();
                }
                if($order->from_warehouse_id != NULL && $order->supplier_id == NULL)
                {
                    $warehouse_products = WarehouseProduct::where('product_id',$product->id)->where('warehouse_id',$order->from_warehouse_id)->count();

                    if($warehouse_products == 0)
                    {
                        return response()->json(['success' => false, 'successmsg' => $order->PoWarehouse->warehouse_title.' dosent have '.$product->short_desc.' ( '.$product->refrence_code.' )']);
                    }

                    $add_products_to_po_detail = new PurchaseOrderDetail;

                    $add_products_to_po_detail->pod_import_tax_book  = $product->import_tax_book;
                    $add_products_to_po_detail->pod_vat_actual       = $request->purchasing_vat == null ? $product->vat : null;

                    $gettingProdSuppData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();

                    $add_products_to_po_detail->pod_unit_price       = $gettingProdSuppData->buying_price;
                    $add_products_to_po_detail->pod_gross_weight     = $gettingProdSuppData->gross_weight;
                    $add_products_to_po_detail->po_id                = $request->po_id;
                    $add_products_to_po_detail->product_id           = $product->id;

                    $add_products_to_po_detail->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                    $add_products_to_po_detail->billed_unit_per_package = @$gettingProdSuppData->billed_unit;
                    $add_products_to_po_detail->warehouse_id            = Auth::user()->get_warehouse->id;

                    /*vat calculations*/
                    $vat_calculations = $add_products_to_po_detail->calculateVat($gettingProdSuppData->buying_price, $request->purchasing_vat == null ? $product->vat : null);
                    $add_products_to_po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                    $add_products_to_po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                    /*convert val to thb's*/
                    $converted_vals = $add_products_to_po_detail->calculateVatToSystemCurrency($request->po_id, $vat_calculations['vat_amount']);
                    $add_products_to_po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                    $add_products_to_po_detail->save();

                    $order_history = new PurchaseOrdersHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->reference_number = $product->refrence_code;
                    $order_history->old_value = "";
                    $order_history->new_value = "New Item";
                    $order_history->po_id = $request->po_id;
                    $order_history->save();
                }
            }
        }

        /*calulation through a function*/
        $objectCreated = new PurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);
        if ($order->status == 14) {
            PODetailCRUDHelper::updatePOGroupInShipment($order, $product->id, $add_products_to_po_detail);
        }

        return response()->json([
            'success'    => true,
            'successmsg' => 'Product Added Successfully',
            'sub_total'  => $grandCalculations['sub_total'],
            'vat_amout'  => $grandCalculations['vat_amout'],
            'total_w_v'  => $grandCalculations['total_w_v'],
        ]);
    }

    public function getDataFromPoDetail(Request $request, $id)
    {
        if($request->has('is_transfer') && $request->is_transfer == 'true'){
            $PO = true;
        }
        else
        {
            $PO = false;
        }
        $query = DraftPurchaseOrderDetail::with('getProduct.supplier_products','draftPo.getSupplier.getCurrency','getProduct.productType','getProduct.units','getProduct.sellingUnits','notes')->where('po_id', $id)->select('draft_purchase_order_details.*');
        $query = DraftPurchaseOrderDetail::DraftPOSorting($request, $query);

        return Datatables::of($query)

            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteProd" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';
                return $html_string;
            })
            ->addColumn('supplier_id',function($item){
                if($item->product_id != null)
                {
                    if($item->draftPo->supplier_id == NULL && $item->draftPo->from_warehouse_id != NULL)
                    {
                        $supplier_id = $item->getProduct->supplier_id;
                    }
                    else
                    {
                        $supplier_id = $item->draftPo->getSupplier->id;
                    }

                    // $gettingProdSuppData = SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();

                    $gettingProdSuppData = $item->getProduct->supplier_products->where('supplier_id',$supplier_id)->first();

                    $ref_no = $gettingProdSuppData->product_supplier_reference_no != null ? $gettingProdSuppData->product_supplier_reference_no : "--";
                    return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"><b>'.$ref_no.'</b></a>';
                    //return  $ref_no;
                }
                else
                {
                    return "N.A";
                }

            })
            ->addColumn('item_ref',function($item){
                if($item->product_id != null)
                {
                    $ref_no = $item->product_id !== null ? $item->getProduct->refrence_code : "--" ;
                    return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  ><b>'.$ref_no.'</b></a>';
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('brand',function($item){
                 return $item->product_id !== null ? ($item->getProduct->brand != null ? $item->getProduct->brand : "--") : "--" ;
            })
            ->addColumn('short_desc',function($item){
                if($item->product_id != null)
                {
                    if($item->draftPo->supplier_id != NULL)
                    {
                        $supplier_id = $item->draftPo->getSupplier->id;

                        // $getDescription = SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$supplier_id)->first();
                        $getDescription = $item->getProduct->supplier_products->where('supplier_id',$supplier_id)->first();
                        return @$getDescription->supplier_description != null ? $getDescription->supplier_description : ($item->getProduct->short_desc != null ? $item->getProduct->short_desc : "--") ;
                    }
                    else
                    {
                        return $item->getProduct->short_desc != null ? $item->getProduct->short_desc : "--" ;
                    }
                }
                else
                {
                    if($item->billed_desc == null)
                    {
                        $style = "color:red;";
                    }
                    else
                    {
                        $style = "";
                    }
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity billed_desc" style="'.$style.'" data-id id="billed_desc"  data-fieldvalue="'.@$item->billed_desc.'">';
                    $html_string .= ($item->billed_desc != NULL ? $item->billed_desc : "--");
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="billed_desc" class="d-none" value="'.$item->billed_desc .'">';
                    return $html_string;
                }
            })
            ->addColumn('type',function($item){
                return $item->product_id !== null ? $item->getProduct->productType->title : "--" ;
           })
            ->addColumn('buying_unit',function($item){
                return $item->product_id !== null ? $item->getProduct->units->title : "--" ;
            })
            ->addColumn('selling_unit',function($item){
                return $item->product_id !== null ? $item->getProduct->sellingUnits->title : "--" ;
            })
            ->addColumn('quantity',function($item) use ($PO) {
                $billed_unit = $item->product_id !== null ? @$item->getProduct->units->title : 'N.A';
                $decimals = $item->getProduct != null ? ($item->getProduct->units != null ? $item->getProduct->units->decimal_places : 0) : 0;
                if($item->quantity == null)
                {
                    $style = "color:red;";
                }
                else
                {
                    $style = "";
                }
                    $html_string = '
                <span class="m-l-15 inputDoubleClickQuantity quantity quantity_span_'.$item->id.' mr-2" style="'.$style.'" data-id id="quantity_span_'.$item->product_id.'"  data-fieldvalue="'.number_format($item->quantity,$decimals,'.','').'">';
                $html_string .= ($item->quantity != NULL ? number_format($item->quantity,$decimals,'.','') : "--");
                $html_string .= '</span>';

                $html_string .= '<input type="number" style="width:100%;" name="quantity" id = "quantity_input_'.$item->product_id.'" class="form-control input-height d-none quantity_field_'.$item->id.'" id = "quantity_field_span_'.$item->product_id.'" value="'.number_format($item->quantity,$decimals,'.','').'">';
                $html_string .= $billed_unit;
                return $html_string;
            })
            ->addColumn('unit_price',function($item){
                $billed_unit = $item->product_id !== null ? @$item->getProduct->units->title : 'N.A';
                $sup_currenc = $item->draftPo->supplier_id != NULL ? $item->draftPo->getSupplier->getCurrency->currency_code : '';
                if($item->pod_unit_price === null)
                {
                    $style = "color:red;";
                }
                else
                {
                    $style = "";
                }

                $html_string = '
                <span class="m-l-15 inputDoubleClickQuantity unit_price unit_price_span_'.$item->id.' mr-2" style="'.$style.'" data-id id="unit_price_span_'.$item->product_id.'"  data-fieldvalue="'.number_format($item->pod_unit_price,3,'.','').'">';
                $html_string .= $item->pod_unit_price !== null ? number_format(@$item->pod_unit_price, 3, '.', ',') : "--" ;
                $html_string .= '</span>';
                $html_string .= '<input type="number" style="width:100%;" name="unit_price" class="form-control input-height d-none unit_price_field_'.$item->product_id.'" value="'.number_format($item->pod_unit_price,3,'.','').'">';
                $html_string .= $sup_currenc.' / '.$billed_unit;
                return $html_string;
            })
            ->addColumn('unit_price_with_vat',function($item){
                $billed_unit = $item->product_id !== null ? @$item->getProduct->units->title : 'N.A';
                $sup_currenc = $item->draftPo->supplier_id != NULL ? $item->draftPo->getSupplier->getCurrency->currency_code : '';
                if($item->pod_unit_price_with_vat === null)
                {
                    $style = "color:red;";
                }
                else
                {
                    $style = "";
                }

                $html_string = '
                <span class="m-l-15 inputDoubleClickQuantity pod_unit_price_with_vat unit_price_with_vat_span_'.$item->id.' mr-2" style="'.$style.'" data-id id="pod_unit_price_with_vat_span_'.$item->product_id.'"  data-fieldvalue="'.number_format($item->pod_unit_price_with_vat,3,'.','').'">';
                $html_string .= $item->pod_unit_price_with_vat !== null ? number_format(@$item->pod_unit_price_with_vat, 3, '.', ',') : "--" ;
                $html_string .= '</span>';
                $html_string .= '<input type="number" style="width:100%;" name="pod_unit_price_with_vat" class="form-control input-height d-none unit_price_with_vat_field_'.$item->id.'" id = "pod_unit_price_with_vat_'.$item->product_id.'" value="'.number_format($item->pod_unit_price_with_vat,3,'.','').'">';
                $html_string .= $sup_currenc.' / '.$billed_unit;
                return $html_string;
            })
            ->addColumn('last_updated_price_on',function($item){
                if($item->last_updated_price_on!=null)
                {
                  return Carbon::parse($item->last_updated_price_on)->format('d/m/Y');
                }
                else
                {
                  return '--';
                }
              })
            ->addColumn('amount',function($item){
                $amount = $item->pod_unit_price * $item->quantity;
                $amount = $amount - ($amount * (@$item->discount / 100));
                return $amount !== null ? '<span class="amount_'.$item->id.'">'.number_format($amount,3,'.',',').'</span>' : "--";
            })
            ->addColumn('amount_with_vat',function($item){
                $amount = $item->pod_unit_price_with_vat * $item->quantity;
                $amount = $amount - ($amount * ($item->discount / 100));
                return $amount !== null ? '<span class="amount_with_vat_'.$item->id.'">'.number_format($amount,3,'.',',').'</span>' : "--";
            })
            ->addColumn('supplier_packaging',function($item){
                $billed_unit = $item->supplier_packaging !== null ? $item->supplier_packaging: "N.A" ;
                $decimals = $item->getProduct != null ? ($item->getProduct->units != null ? $item->getProduct->units->decimal_places : 0) : 0;
                if($item->product_id != null )
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity desired_qty desired_qty_span_'.$item->id.'" data-id id="desired_qty_span_'.$item->product_id.'"  data-fieldvalue="'.number_format($item->desired_qty,$decimals,'.','').'">';
                    $html_string .= ($item->desired_qty != null ? number_format($item->desired_qty,$decimals,'.','') : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:60%;" name="desired_qty" class="d-none desired_qty_field_'.$item->id.'" id = "desired_qty_'.$item->product_id.'" value="'.number_format($item->desired_qty,$decimals,'.','').'">';
                    $html_string .= '<span class="ml-2">'.$billed_unit.'</span>';
                    return $html_string;
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('billed_unit_per_package',function($item){
                $billed_unit = $item->billed_unit_per_package !== null ? $item->billed_unit_per_package: "N.A" ;
                if($item->product_id != null )
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity billed_unit_per_package" data-id id="billed_unit_per_package"  data-fieldvalue="'.number_format($item->billed_unit_per_package,3,'.','').'">';
                    $html_string .= ($item->billed_unit_per_package != null ? number_format($item->billed_unit_per_package,3,'.','') : "--" );
                    $html_string .= '</span>';

                    $html_string .= '<input type="number" style="width:60%;" name="billed_unit_per_package" class="d-none" value="'.number_format($item->billed_unit_per_package,3,'.','').'" id = "billed_unit_per_package_id_'.$item->product_id.'">';
                    // $html_string .= '<span class="ml-2">'.$billed_unit.'</span>';
                    return $html_string;
                }
                else
                {
                    return "N.A";
                }
            })
            // ->addColumn('warehouse',function($item){
            //     $html =  '<select class="font-weight-bold form-control-lg form-control warehouse_id input-height select-tag" name="warehouse_id" required>
            //             <option selected disabled value="">Select warehouse</option>';
            //     $warehouses = Warehouse::all();
            //     foreach ($warehouses as $w)
            //     {
            //         if($item->warehouse_id == $w->id)
            //         {
            //             $html = $html.'<option selected value="'.$w->id.'">'.$w->warehouse_title.'</option>';
            //         }
            //         else
            //         {
            //             $html = $html.'<option value="'.$w->id.'">'.$w->warehouse_title.'</option>';
            //         }
            //     }

            //   $html = $html.'
            //   </select>';
            //     return $html;
            // })
            ->addColumn('gross_weight',function($item){
                return $item->pod_total_gross_weight !== null ? '<span class="total_gross_weight_'.$item->id.'">'.number_format($item->pod_total_gross_weight, 3, '.', ',').'</span>' : 'N.A';
            })
            ->addColumn('unit_gross_weight',function($item){
                if($item->product_id != null)
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity pod_gross_weight unit_gross_weight_'.$item->id.'" data-id id="pod_gross_weight_span_'.$item->product_id.'"  data-fieldvalue="'.@$item->pod_gross_weight.'">';
                    $html_string .= $item->pod_gross_weight != NULL ? number_format(@$item->pod_gross_weight, 3, '.', ',') : '--';
                    $html_string .= '</span>';
                    $html_string .= '<input type="number" style="width:100%;" name="pod_gross_weight" class="d-none unit_gross_weight_field_'.$item->id.'" id="pod_gross_weight_'.$item->product_id.'" value="'.@$item->pod_gross_weight.'">';
                    return $html_string;
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('discount',function($item){
                if($item->product_id != null)
                {
                    $html = '<span class="inputDoubleClick font-weight-bold" data-fieldvalue="'.$item->discount.'">'.($item->discount != null ? $item->discount : "--" ).'</span><input type="number" name="discount" value="'.$item->discount.'" class="discount form-control input-height d-none" style="width:85%" maxlength="5" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);">';
                    return $html.' %';
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('item_notes', function($item) {
                // $notes = DraftPurchaseOrderDetailNote::where('draft_po_id', $item->id)->count();
                $notes = $item->notes->count();
                $product_note = ($notes > 0) ? $item->notes->first()->note : '';
                $product_note = substr($product_note, 0, 30);

                if(Auth::user()->role_id != 7)
                {
                    $html_string = '<div class="d-flex justify-content-center text-center">';
                // if($notes > 0){
                $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="font-weight-bold note_'.$item->id.' show-notes mr-2 '.($notes > 0 ? "" : "d-none").'" title="View Notes">'.$product_note.' ...</a>';
                // }

                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus mt-1" title="Add Note"></a>
                          </div>';
                }
                else
                {
                    $html_string = "--";
                }
                return $html_string;
            })
            ->addColumn('customer', function ($item) {
               return '--';
                // return $item->customer_id !== null ? @$item->customer->reference_name : 'Stock';
            })
            ->addColumn('product_description', function ($item) {
                if($item->product_id != null)
                {
                    return  $item->getProduct->short_desc;
                //    / return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  ><b>'.$ref_no.'</b></a>';
                }
                else
                {
                    // return  $html_string = '--';
                    if($item->billed_desc == null)
                    {
                        $style = "color:red;";
                    }
                    else
                    {
                        $style = "";
                    }
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity billed_desc" style="'.$style.'" data-id id="billed_desc"  data-fieldvalue="'.@$item->billed_desc.'">';
                    $html_string .= ($item->billed_desc != NULL ? $item->billed_desc : "--");
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="billed_desc" class="d-none" value="'.$item->billed_desc .'">';
                    return $html_string;
                }
            })
            ->addColumn('leading_time', function ($item) {
                if($item->draftPo->supplier_id != null)
                {
                    if($item->product_id != null)
                    {
                        // $gettingProdSuppData = SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$item->draftPo->supplier_id)->first();

                        $gettingProdSuppData = $item->getProduct->supplier_products->where('supplier_id',$item->draftPo->supplier_id)->first();

                        $leading_time = $gettingProdSuppData !== null ? $gettingProdSuppData->leading_time : "--";

                        return  $leading_time;
                    }
                    else
                    {
                        return  $html_string1 = 'N.A';
                    }
                }
                else{
                        return '--';
                }

            })
            ->addColumn('order_no', function ($item) {
                return '--';
            })
            ->addColumn('customer_qty', function ($item) {
                return '--';

            })
            ->addColumn('customer_pcs', function ($item) {
                return '--';

            })
            ->addColumn('supplier_invoice_number',function($item) use ($PO){
                if($PO == false)
                {
                    return '--';
                }
                $html_string = '';
                    $warehouse_id = $item->draftPo != null ? $item->draftPo->from_warehouse_id : '';
                    $groups_id = StockManagementOut::where('product_id',$item->product_id)->whereNotNull('quantity_in')->where(function($q)use($item){
                    $q->where('available_stock','>',0)->orWhereIn('id',$item->get_td_reserved()->pluck('stock_id')->toArray());
                    })->whereNotNull('po_group_id')->where('warehouse_id',$warehouse_id)->pluck('po_group_id')->toArray();

                    $existing_groups = $item->get_td_reserved()->pluck('stock_id')->toArray();

                    $groups_ids = StockManagementOut::whereIn('id',$existing_groups)->pluck('po_group_id')->toArray();

                    $pos = PurchaseOrder::select('id','invoice_number','ref_id')->whereHas('PurchaseOrderDetail',function($p)use($item){
                        $p->where('product_id',$item->product_id);
                    })->whereIn('po_group_id',$groups_ids)->get();

                    //for finding supplier invoice number
                    $pos_sup_inv = PurchaseOrder::select('id','invoice_number','ref_id')->whereHas('PurchaseOrderDetail',function($p)use($item){
                        $p->where('product_id',$item->product_id);
                    })->whereIn('po_group_id',$groups_id)->get();

                    if(true)
                    {
                        if($pos->count() > 0)
                        {
                            $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModal'.$item->id.'">';
                            foreach ($pos as $group) {
                                $html_string .= ($group->invoice_number != null ? $group->invoice_number : '--').'<br>';
                            }
                            $html_string .= '</a>';
                        }
                        else
                        {
                            $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModal'.$item->id.'">--';
                            $html_string .= '</a>';
                        }
                        // $html_string .= '<button type="button" class="btn p-0 pl-1 pr-1" data-toggle="modal" data-target="#myModal">
                        //                 <i class="fa fa-eye"></i>
                        //                 </button>';
                        $html_string .= '
                                        <div class="modal" id="groupsModal'.$item->id.'" aria-hidden="true" data-backdrop="false" style="top:50px;" tabindex="-1">
                                          <div class="modal-dialog">
                                            <div class="modal-content">

                                              <div class="modal-header">
                                                <h4 class="modal-title">PO\'s Supplier Inv.#</h4>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                              </div>

                                              <div class="modal-body">
                                                <table width="100%" class="supplier_invoice_number_table">
                                                  <thead>
                                                    <tr>
                                                      <th>PO No.</th>
                                                      <th>Supplier Inv.#</th>
                                                    </tr>
                                                  </thead>
                                                  <tbody>';
                                        foreach($pos_sup_inv as $inv)
                                        {
                                                    $html_string .= '<tr>
                                                        <td><a class="font-weight-bold" href="'.route('get-purchase-order-detail',['id'=> $inv->id]).'" target="_blank">'.@$inv->ref_id.'</a></td>';
                                                        if($inv->invoice_number != null)
                                                        {
                                                            $html_string .= '<td> '.$inv->invoice_number.' </td>';
                                                        }
                                                        else
                                                        {
                                                            $html_string .= '<td>--</td>';
                                                        }

                                                      $html_string .= '</tr>';
                                        }

                                                $html_string .= '</tbody>
                                                </table>
                                              </div>

                                              <div class="modal-footer">
                                                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                              </div>

                                            </div>
                                          </div>
                                        </div>
                        ';

                        return $html_string;
                    }
                    else
                    {
                        return '--';
                    }
            })
            ->addColumn('custom_line_number',function($item) use ($PO){
                if($PO == false)
                {
                    return '--';
                }
                $html_string = '';

                $warehouse_id = $item->draftPo != null ? $item->draftPo->from_warehouse_id : '';
                $groups_id = StockManagementOut::where('product_id',$item->product_id)->whereNotNull('quantity_in')->where(function($q)use($item){
                    $q->where('available_stock','>',0)->orWhereIn('id',$item->get_td_reserved()->pluck('stock_id')->toArray());
                })->whereNotNull('po_group_id')->where('warehouse_id',$warehouse_id)->pluck('po_group_id')->toArray();

                $existing_groups = $item->get_td_reserved()->pluck('stock_id')->toArray();
                $groups_ids = StockManagementOut::whereIn('id',$existing_groups)->pluck('po_group_id')->toArray();
                $pos = PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm','po_group_product_details.custom_line_number')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_ids)->get();

                $pos_l = PoGroup::select('po_groups.id','po_group_product_details.po_group_id','po_group_product_details.product_id','po_groups.custom_invoice_number','po_groups.ref_id','po_groups.is_confirm','po_group_product_details.custom_line_number')->join('po_group_product_details','po_groups.id','=','po_group_product_details.po_group_id')->where('po_group_product_details.product_id',$item->product_id)->whereIn('po_groups.id',$groups_id)->get();


                if(true)
                {
                    if($pos->count() > 0)
                    {
                        $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModalLine'.$item->id.'">';
                        foreach ($pos as $group) {
                            $html_string .= ($group->custom_line_number != null ? $group->custom_line_number : '--').'<br>';
                        }
                        $html_string .= '</a>';
                    }
                    else
                    {
                        $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModalLine'.$item->id.'">--';
                        $html_string .= '</a>';
                    }
                    $html_string .= '
                                    <div class="modal" id="groupsModalLine'.$item->id.'" aria-hidden="true" data-backdrop="false" style="top:50px;" tabindex="-1">
                                      <div class="modal-dialog">
                                        <div class="modal-content">

                                          <div class="modal-header">
                                            <h4 class="modal-title">Groups Custom\'s Line#</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                          </div>

                                          <div class="modal-body">
                                            <table width="100%" class="supplier_invoice_number_table">
                                              <thead>
                                                <tr>
                                                  <th>Group No.</th>
                                                  <th>Custom\'s Line#</th>
                                                </tr>
                                              </thead>
                                              <tbody>';
                                    foreach($pos_l as $inv)
                                    {
                                    if($inv->is_confirm == 1)
                                    {
                                        $url = 'importing-completed-receiving-queue-detail';
                                    }
                                    else
                                    {
                                        $url = 'importing-receiving-queue-detail';
                                    }
                                    $html_string .= '<tr>
                                        <td><a class="font-weight-bold" href="'.route($url,['id'=> @$inv->id]).'" target="_blank">'.@$inv->ref_id.'</a></td>';
                                        if(@$inv->custom_line_number != null)
                                        {
                                            $html_string .= '<td>'.@$inv->custom_line_number.'</td>';
                                        }
                                        else
                                        {
                                            $html_string .= '<td>--</td>';
                                        }

                                      $html_string .= '</tr>';
                                    }


                                            $html_string .= '</tbody>
                                            </table>
                                          </div>

                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                          </div>

                                        </div>
                                      </div>
                                    </div>
                    ';

                    return $html_string;
                }
                else
                {
                    return '--';
                }
            })
            ->addColumn('custom_invoice_number',function($item) use ($PO){
                if($PO == false)
                {
                    return '--';
                }
                $html_string = '';
                $warehouse_id = $item->draftPo != null ? $item->draftPo->from_warehouse_id : '';

                $groups_id = StockManagementOut::where('product_id',$item->product_id)->whereNotNull('quantity_in')->where(function($q)use($item){
                    $q->where('available_stock','>',0)->orWhereIn('id',$item->get_td_reserved()->pluck('stock_id')->toArray());
                })->whereNotNull('po_group_id')->where('warehouse_id',$warehouse_id)->get();

                $existing_groups = $item->get_td_reserved()->pluck('stock_id')->toArray();

                $groups_ids = StockManagementOut::whereIn('id',$existing_groups)->pluck('po_group_id')->toArray();

                $groups_c_i_n = PoGroup::select('custom_invoice_number','id')->whereIn('id',$groups_ids)->get();

                if(true)
                {
                    if($groups_c_i_n->count() > 0)
                    {
                        $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModalInv'.$item->id.'">';
                        foreach ($groups_c_i_n as $group) {
                            $html_string .= ($group->custom_invoice_number != null ? $group->custom_invoice_number : '--').'<br>';
                        }
                        $html_string .= '</a>';
                    }
                    else
                    {
                        $html_string .= '<a href="javascript:void(0)" class="font-weight-bold" data-toggle="modal" data-target="#groupsModalInv'.$item->id.'">--';
                        $html_string .= '</a>';
                    }
                    $html_string .= '
                                    <div class="modal" id="groupsModalInv'.$item->id.'" aria-hidden="true" data-backdrop="false" style="top:50px;" tabindex="-1">
                                      <div class="modal-dialog modal-lg">
                                        <div class="modal-content">

                                          <div class="modal-header">
                                            <h4 class="modal-title">Groups Custom\'s Inv.#</h4>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                          </div>

                                          <div class="modal-body">
                                            <table width="100%" class="supplier_invoice_number_table">
                                              <thead>
                                                <tr>
                                                  <th>Reserved From</th>
                                                  <th>Group No.</th>
                                                  <th>Custom\'s Inv.#</th>
                                                  <th>Available Stock </th>
                                                  <th>Reserved For <br>This Item </th>
                                                  <th>Remaining Stock</th>
                                                </tr>
                                              </thead>
                                              <tbody>';
                                    foreach($groups_id as $inv)
                                    {
                                    if($item->get_td_reserved()->where('stock_id',$inv->id)->where('draft_pod_id',$item->id)->first())
                                    {
                                        $check_class = 'checked';
                                    }
                                    else
                                    {
                                        $check_class = '';
                                    }
                                    if($inv->get_po_group->is_confirm == 1)
                                    {
                                        $url = 'importing-completed-receiving-queue-detail';
                                    }
                                    else
                                    {
                                        $url = 'importing-receiving-queue-detail';
                                    }
                                    $html_string .= '<tr>
                                        <td>
                                            <input type="checkbox" name="reservedFrom" class="pay-check" value="'.$inv->available_stock.'" data-id="'.$item->id.'" data-stockid="'.$inv->id.'" data-poid="'.$item->po_id.'" '.$check_class.'>
                                        </td>
                                        <td><a class="font-weight-bold" href="'.route($url,['id'=> $inv->po_group_id]).'" target="_blank">'.@$inv->get_po_group->ref_id.'</a></td>';
                                        if($item->get_td_reserved->count() > 0)
                                        {
                                            $all_rsv = $item->get_td_reserved()->where('stock_id',$inv->id)->sum('reserved_quantity');
                                            if($all_rsv)
                                            {
                                                $rsv_q = $all_rsv;
                                            }
                                            else
                                            {
                                                $rsv_q = 0;
                                            }
                                        }
                                        else
                                        {
                                            $rsv_q = 0;
                                        }

                                        if($inv->get_po_group->custom_invoice_number != null)
                                        {
                                            $html_string .= '<td>'.$inv->get_po_group->custom_invoice_number.'</td>';
                                            $html_string .= '<td>'.($inv->available_stock + $rsv_q).'</td>';
                                            $html_string .= '<td>'.$rsv_q.'</td>';
                                            $html_string .= '<td>'.$inv->available_stock.'</td>';
                                        }
                                        else
                                        {
                                            $html_string .= '<td>--</td>';
                                            $html_string .= '<td>'.($inv->available_stock + $rsv_q).'</td>';
                                            $html_string .= '<td>'.$rsv_q.'</td>';
                                            $html_string .= '<td>'.$inv->available_stock.'</td>';
                                        }

                                      $html_string .= '</tr>';
                                    }


                                            $html_string .= '</tbody>
                                            </table>
                                          </div>

                                          <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                                          </div>

                                        </div>
                                      </div>
                                    </div>
                    ';

                    return $html_string;
                }
                else
                {
                    return '--';
                }
            })
            ->addColumn('weight', function ($item) {
            if($item->product_id != null)
            {
            $html_string = '
            <span class="m-l-15 " id="weight"  data-fieldvalue="'.@$item->getProduct->weight.'">';
            $html_string .= $item->getProduct->weight != NULL ? $item->getProduct->weight : "--";
            $html_string .= '</span>';
            return $html_string;
            return @$item->id;
            }
            else
            {
            return "N.A";
            }
            })
            ->addColumn('purchasing_vat', function ($item) {
                $vat = $item->pod_vat_actual !== null ? $item->pod_vat_actual.' %' : '--';

                $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity pod_gross_weight" data-id id="pod_vat_actual_span_'.$item->product_id.'"  data-fieldvalue="'.@$item->pod_vat_actual.'">';
                    $html_string .= $vat;
                    $html_string .= '</span>';
                    $html_string .= '<input type="number" style="width:100%;" name="pod_vat_actual" class="d-none" id="pod_vat_actual_span_'.$item->product_id.'" value="'.@$item->pod_vat_actual.'">';
                    return $html_string;
            })
            ->setRowId(function ($item){
                return $item->id;
            })
            ->rawColumns(['action','supplier_id','item_ref','short_desc','buying_unit','quantity','unit_price','amount','gross_weight','supplier_packaging','billed_unit_per_package','unit_gross_weight','discount','item_notes','customer','product_description','leading_time','order_no','customer_qty','customer_pcs','custom_line_number','custom_invoice_number','supplier_invoice_number','weight','purchasing_vat','unit_price_with_vat','amount_with_vat'])
            ->make(true);
    }

    public function exportDraftTd(Request $request)
    {
        $query = DraftPurchaseOrderDetail::with('getProduct')->where('po_id', $request->id)->select('draft_purchase_order_details.*');
        $query = DraftPurchaseOrderDetail::DraftPOSorting($request, $query)->get();

        if($query != null){
            $to_warehouse_id = $query[0]->draftPo->getWarehoue !== null ? $query[0]->draftPo->getWarehoue->is_bonded : null;
        }
        else{
            $to_warehouse_id = null;
        }
        $getPurchaseOrder = DraftPurchaseOrder::find($request->id);
        $is_bonded = $getPurchaseOrder->getWarehoue->is_bonded;
        $globalAccessConfig4 = QuotationConfig::where('section','groups_management_page')->first();
        $allow_custom_invoice_number = '';
        $show_custom_line_number = '';
        $show_supplier_invoice_number = '';
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

        $current_date = date("Y-m-d");
        \Excel::store(new draftTDExport($query,$to_warehouse_id,$is_bonded,$allow_custom_invoice_number,$show_custom_line_number,$show_supplier_invoice_number), 'Draft TD Export.xlsx');
        return response()->json(['success' => true]);
    }

    public function checkQtyExportDraftPo(Request $request)
    {
        $query = DraftPurchaseOrderDetail::with('getProduct')->where('po_id', $request->draft_id)->orderBy('id', 'ASC')->get();
        if($query->count() == 0)
        {
            $errorMsg =  'Please add some products in Order';
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }
        else
        {
            if($query[0]->quantity != NULL)
            {
                return response()->json(['success' => true, 'errorMsg' => 'Success']);
            }
            else
            {
                $errorMsg =  'Please add products QTY first';
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

        }
    }

    public function exportDraftPo(Request $request)
    {
        if ($request->type == 'example') {
            $query = null;
        }
        else{
            $query = DraftPurchaseOrderDetail::with('getProduct')->where('po_id', $request->id)->select('draft_purchase_order_details.*');
            $query = DraftPurchaseOrderDetail::DraftPOSorting($request, $query);
        }

        $quotation_config      = QuotationConfig::where('section','purchase_order')->first();
        $hidden_by_default     = '';
        $columns_prefrences    = null;
        $shouldnt_show_columns = [4,13,14,20];
        $hidden_columns        = null;
        $hidden_columns_by_admin = [];
        if($quotation_config == null)
        {
            $hidden_by_default = '';
        }
        else
        {
          $dislay_prefrences = $quotation_config->display_prefrences;
          $hide_columns = $quotation_config->show_columns;
          if($quotation_config->show_columns != null)
          {
            $hidden_columns = json_decode($hide_columns);
            if(!in_array($hidden_columns,$shouldnt_show_columns))
            {
                $hidden_columns = array_merge($hidden_columns,$shouldnt_show_columns);
                $hidden_columns = implode (",", $hidden_columns);
                $hidden_columns_by_admin = explode (",", $hidden_columns);
            }
          }
          else
          {
            $hidden_columns = implode (",", $shouldnt_show_columns);
            $hidden_columns_by_admin = explode (",", $hidden_columns);
          }
          $user_hidden_columns = [];
          $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','draft_po_detail')->where('user_id',Auth::user()->id)->first();
          if($not_visible_columns != null)
          {
            $user_hidden_columns = $not_visible_columns->hide_columns;
          }
          else
          {
            $user_hidden_columns="";
          }

          $user_plus_admin_hidden_columns = $user_hidden_columns.','.$hidden_columns;
          $user_plus_admin_hidden_columns = trim($user_plus_admin_hidden_columns,",");
          $user_plus_admin_hidden_columns = explode (",", $user_plus_admin_hidden_columns);
        }

        $current_date = date("Y-m-d");
        \Excel::store(new draftPOExport($query,$user_plus_admin_hidden_columns), 'Draft PO Export.xlsx');
        return response()->json(['success' => true]);

    }

    public function getDraftPoDetailNote(Request $request)
    {
        $purchase_list_notes = DraftPurchaseOrderDetailNote::where('draft_po_id',$request->id)->get();

        $html_string ='<div class="table-responsive">
            <table class="table table-bordered text-center">
            <thead class="table-bordered">
            <tr>
                <th>S.no</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
            </thead><tbody>';
        if($purchase_list_notes->count() > 0)
        {
            $i = 0;
            foreach($purchase_list_notes as $note)
            {
                $i++;
                $html_string .= '<tr id="gem-note-'.$note->id.'">
                <td>'.$i.'</td>
                <td>'.$note->note.'</td>
                <td><a href="javascript:void(0);" data-id="'.$note->id.'" id="draf_po_delete" class="draf_po_delete actionicon" title="Delete Note"><i class="fa fa-trash" style="color:red;"></i></a></td>
                </tr>';
            }
        }
        else
        {
            return response()->json(['no_data'=>true]);
            $html_string .= '<tr>
                <td colspan="4">No Note Found</td>
            </tr>';
        }

        $html_string .= '</tbody></table></div>';
        return $html_string;

    }

    public function addDraftPoItemNote(Request $request)
    {
        $purchase_list  = new DraftPurchaseOrderDetailNote;
        $purchase_list->draft_po_id = $request['purchase_list_id'];
        $purchase_list->note = $request['note_description'];
        $purchase_list->save();
        return response()->json(['success'=>true,'id' => $request['purchase_list_id']]);
    }

    public function warehouseSaveInDraftPo(Request $request)
    {
        return TransferDocumentHelper::warehouseSaveInDraftPo($request);
    }

    public function getData(Request $request)
    {
        $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','PoSupplier','po_notes');

        if($request->dosortby == 12)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 12);
            });
        }
        elseif($request->dosortby == 13)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 13);
            });
        }
        elseif($request->dosortby == 14)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 14);
            });
        }
        elseif($request->dosortby == 15)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 15);
            });
        }
        elseif($request->dosortby == 16)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 16);
            });
        }
        elseif($request->dosortby == 'all')
        {
            $query->where(function($q){
             $q->whereIn('purchase_orders.status',[12,13,14,15,16]);
            });
        }

        if ($request->date_radio == '1') {
            $date_column = 'target_receive_date';
        }
        else{
            $date_column = 'invoice_date';
        }

        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('target_receive_date', '>=', $date);
            $query->where($date_column, '>=', $date);
        }
        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('target_receive_date', '<=', $date);
            $query->where($date_column, '<=', $date);
        }

        if($request->selecting_suppliers != null)
        {
            $query->where('supplier_id', $request->selecting_suppliers);
        }
        $query = PurchaseOrder::doSortBy($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['to_warehouse', 'customer', 'note', 'payment_due_date', 'target_receive_date', 'confirm_date', 'supplier_ref', 'supplier', 'action', 'checkbox', 'exchange_rate', 'po_total'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnAddColumnWaitingConfirm($column, $item);
            });
        }

        $edit_columns = ['ref_id', 'invoice_number', 'invoice_date'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnEditColumnWaitingConfirm($column, $item);
            });
        }

        $filter_columns = ['to_warehouse', 'customer', 'note', 'supplier'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrder::returnFilterColumnWaitingConfirm($column, $item, $keyword);
            });
        }

            $dt->setRowId(function ($item) {
                    return @$item->id;
            });
            $dt->rawColumns(['checkbox','action','ref_id','status','ref_id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','payment_due_date','note','customer','transfer_date', 'invoice_date']);
            return $dt->make(true);
    }

    public function getTransferDocumentData(Request $request)
    {
        if(Auth::user()->role_id == 6)
        {
            $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','PoSupplier','po_notes','PoWarehouse','po_detail.customer','ToWarehouse')->where('to_warehouse_id',Auth::user()->warehouse_id)->select('purchase_orders.*');
        }
        else
        {
            $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','PoSupplier','po_notes','PoWarehouse','po_detail.customer','ToWarehouse')->select('purchase_orders.*');
        }

        if($request->dosortby == 20)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 20);
            });
        }
        elseif($request->dosortby == 21)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 21);
            });
        }
        elseif($request->dosortby == 22)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 22);
            });
        }
        elseif($request->dosortby == 'all')
        {
            $query->where(function($q){
             $q->whereIn('purchase_orders.status',[20,21,22,23]);
            });
        }

        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-d-m',strtotime($date));
            // dd($date);
            $query->where('transfer_date', '>=', $date);
        }
        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            $query->where('transfer_date', '<=', $date);
        }

        if($request->selecting_suppliers != null)
        {
            $query->where('supplier_id', $request->selecting_suppliers);
        }

        $query = PurchaseOrder::TransferDashboardSorting($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['to_warehouse', 'customer', 'note', 'payment_due_date', 'target_receive_date', 'po_total', 'received_date', 'transfer_date', 'confirm_date', 'supplier_ref',  'supplier', 'action', 'checkbox'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return TransferDocumentDatatable::returnAddColumnTransferDoc($column, $item);
            });
        }

        $edit_columns = ['ref_id'];
        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return TransferDocumentDatatable::returnEditColumnTransferDoc($column, $item);
            });
        }

        $filter_columns = ['to_warehouse', 'supplier', 'note', 'customer'];
        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return TransferDocumentDatatable::returnFilterColumnTransferDoc($column, $item, $keyword);
            });
        }

            $dt->setRowId(function ($item) {
                    return @$item->id;
            });
            $dt->rawColumns(['checkbox','action','ref_id','status','ref_id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','payment_due_date','note','customer','transfer_date']);
            return $dt->make(true);
    }

    public function deleteTransferDocuments(Request $request)
    {
        $multi_tds = explode(',', $request->selected_tds);

        if(sizeof($multi_tds) <= 100)
        {

            for($i=0; $i<sizeof($multi_tds); $i++)
            {
                $purchase_order = PurchaseOrder::find($multi_tds[$i]);

                if($purchase_order){
                    $purchase_order_detail = $purchase_order->PurchaseOrderDetail;
                    foreach($purchase_order_detail as $pod){
                        // Reserved qty in TransferDocumentReservedQuantity should be reverted if deleting entire TD in Waiting Confirmation status
                        $reserved_qty = $pod->get_td_reserved->where('pod_id', $pod->id)->sum('reserved_quantity');
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateTDReservedQuantity($purchase_order,$pod,$reserved_qty,$reserved_qty,'Reserved Quantity by confirming TD','subtract');
                        $pod->delete();
                    }
                    $po_status_history = PurchaseOrderStatusHistory::where('po_id',$purchase_order->id)->delete();
                    $po_his = PurchaseOrdersHistory::where('po_id',$purchase_order->id)->delete();

                    $reserved = TransferDocumentReservedQuantity::where('po_id', $purchase_order->id)->get();

                    foreach ($reserved as $item) {
                        $stock_m_out = StockManagementOut::find($item->stock_id);
                        if ($stock_m_out) {
                            $stock_m_out->available_stock += $item->reserved_quantity;
                            $stock_m_out->save();
                        }

                        $po_detail = PurchaseOrderDetail::find($item->inbound_pod_id);
                        if ($po_detail) {
                            $po_detail->reserved_qty -= $item->reserved_quantity;
                            $po_detail->save();
                        }
                        $item->delete();
                    }

                    $purchase_order->delete();
                }
            }
        }else{
            return response()->json(['error' => 1]);
        }
        return response()->json(['success' => true]);
    }

    public function get_wsInfoData(Request $request)
    {
        $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','PoSupplier','po_notes');
        $query->where(function($q){
             $q->where('purchase_orders.status', 13);
            });


        if($request->dosortby == 16)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 16);
            });
        }
        elseif($request->dosortby == 'all')
        {
            $query->where(function($q){
             $q->whereIn('purchase_orders.status',[12,13,14,15,16]);
            });
        }

        if ($request->date_radio == '1') {
            $date_column = 'target_receive_date';
        }
        else{
            $date_column = 'invoice_date';
        }
        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('target_receive_date', '>=', $date);
            $query->where($date_column, '>=', $date);
        }
        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('target_receive_date', '<=', $date);
            $query->where($date_column, '<=', $date);
        }

        if($request->selecting_suppliers != null)
        {
            $query->where('supplier_id', $request->selecting_suppliers);
        }

        $query = PurchaseOrder::doSortBy($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['to_warehouse', 'customer', 'note', 'payment_due_date', 'target_receive_date', 'po_total', 'confirm_date', 'supplier_ref', 'supplier', 'action', 'checkbox', 'exchange_rate'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnAddColumnShipping($column, $item);
            });
        }

        $edit_columns = ['ref_id', 'invoice_number', 'invoice_date'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnEditColumnShipping($column, $item);
            });
        }

        $filter_columns = ['to_warehouse', 'customer', 'note', 'supplier'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrder::returnFilterColumnShipping($column, $item, $keyword);
            });
        }

            $dt->setRowId(function ($item) {
                    return @$item->id;
            });
            $dt->rawColumns(['checkbox','action','ref_id','status','ref_id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','payment_due_date','note','customer']);
            return $dt->make(true);
    }

    public function getAllPoData(Request $request)
    {
        $currentMonth = date('m');
        $currentYear = date('Y');

        $query = PurchaseOrder::whereIn('purchase_orders.status',[12,13,14,15]);

        if ($request->date_radio == '1') {
            $date_column = 'purchase_orders.target_receive_date';
        }
        else{
            $date_column = 'purchase_orders.invoice_date';
        }
        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('purchase_orders.target_receive_date', '>=', $date);
            $query->where($date_column, '>=', $date);
        }
        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('purchase_orders.target_receive_date', '<=', $date);
            $query->where($date_column, '<=', $date);
        }

        if($request->selecting_suppliers != null)
        {
            $query->where('supplier_id', $request->selecting_suppliers);
        }

        $query = PurchaseOrder::doSortBy($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['to_warehouse', 'customer', 'note', 'payment_due_date', 'target_receive_date', 'po_total', 'confirm_date', 'supplier_ref', 'supplier', 'action', 'checkbox'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnAddColumnAllPos($column, $item);
            });
        }

        $edit_columns = ['ref_id', 'invoice_date'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnEditColumnAllPos($column, $item);
            });
        }

        $filter_columns = ['to_warehouse', 'customer', 'note', 'supplier'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrder::returnFilterColumnAllPos($column, $item, $keyword);
            });
        }

            $dt->setRowId(function ($item) {
                    return @$item->id;
            });
            $dt->rawColumns(['checkbox','action','ref_id','status','ref_id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','payment_due_date','note','customer']);
            $dt->with('post',$query->sum('total'));
            return $dt->make(true);
    }

    public function get_dfSupplierData(Request $request)
    {
        $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','PoSupplier','po_notes','po_group:id,ref_id');
        $query->where(function($q){
             $q->where('purchase_orders.status', 14);
            });

        if($request->dosortby == 16)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 16);
            });
        }
        elseif($request->dosortby == 'all')
        {
            $query->where(function($q){
             $q->whereIn('purchase_orders.status',[12,13,14,15,16]);
            });
        }

        if ($request->date_radio == '1') {
            $date_column = 'target_receive_date';
        }
        else{
            $date_column = 'invoice_date';
        }
        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-m-d',strtotime($date));
            $query->where($date_column, '>=', $date);
        }
        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            $query->where($date_column, '<=', $date);
        }

        if($request->selecting_suppliers != null)
        {
            $query->where('supplier_id', $request->selecting_suppliers);
        }

        $query = PurchaseOrder::doSortBy($request, $query);

        $dt =  Datatables::of($query);
        $add_columns = ['to_warehouse', 'customer', 'note', 'payment_due_date', 'target_receive_date', 'po_total', 'confirm_date', 'supplier_ref', 'supplier', 'group_number', 'action', 'checkbox', 'exchange_rate'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnAddColumnDispatchFromSupplier($column, $item);
            });
        }

        $edit_columns = ['ref_id', 'invoice_number', 'invoice_date'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnEditColumnDispatchFromSupplier($column, $item);
            });
        }

        $filter_columns = ['note', 'to_warehouse', 'customer', 'supplier'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                // dd($item->get());
                return PurchaseOrder::returnFilterColumnDispatchFromSupplier($column, $item, $keyword);
            });
        }

            $dt->setRowId(function ($item) {
                    return @$item->id;
            });
            $dt->rawColumns(['checkbox','action','ref_id','status','ref_id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','payment_due_date','note','customer']);
            return $dt->make(true);
    }

    public function get_riStockData(Request $request)
    {
        $currentMonth = date('m');
        $currentYear = date('Y');
        $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','PoSupplier','po_notes');
        $query->where(function($q) {
             $q->where('purchase_orders.status', 15);
            });


        if($request->dosortby == 16)
        {
            $query->where(function($q){
             $q->where('purchase_orders.status', 16);
            });
        }
        elseif($request->dosortby == 'all')
        {
            $query->where(function($q){
             $q->whereIn('purchase_orders.status',[12,13,14,15,16]);
            });
        }

        if ($request->date_radio == '1') {
            $date_column = 'purchase_orders.target_receive_date';
        }
        else{
            $date_column = 'purchase_orders.invoice_date';
        }
        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('purchase_orders.target_receive_date', '>=', $date);
            $query->where($date_column, '>=', $date);
        }
        else
        {
            if($this->targetShipDateConfig['target_ship_date'] == 1)
            {
                $query->whereRaw('MONTH(purchase_orders.target_receive_date) = ?',[$currentMonth]);
                $query->whereRaw('YEAR(purchase_orders.target_receive_date) = ?',[$currentYear]);
            }
        }

        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            // $query->where('purchase_orders.target_receive_date', '<=', $date);
            $query->where($date_column, '<=', $date);
        }
        else
        {
            if($this->targetShipDateConfig['target_ship_date'] == 1)
            {
                $query->whereRaw('MONTH(purchase_orders.target_receive_date) = ?',[$currentMonth]);
                $query->whereRaw('YEAR(purchase_orders.target_receive_date) = ?',[$currentYear]);
            }
        }

        if($request->selecting_suppliers != null)
        {
            $query->where('supplier_id', $request->selecting_suppliers);
        }

        $query = PurchaseOrder::doSortBy($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['to_warehouse', 'customer', 'note', 'payment_due_date', 'target_receive_date', 'po_total', 'confirm_date', 'supplier_ref', 'supplier', 'group_number', 'action', 'checkbox', 'exchange_rate'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnAddColumnReceivedIntoStock($column, $item);
            });
        }

        $edit_columns = ['ref_id', 'invoice_number', 'invoice_date',];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return PurchaseOrder::returnEditColumnReceivedIntoStock($column, $item);
            });
        }

        $filter_columns = ['note', 'to_warehouse', 'customer', 'supplier'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrder::returnFilterColumnReceivedIntoStock($column, $item, $keyword);
            });
        }
            $dt->setRowId(function ($item) {
                    return @$item->id;
            });
            $dt->rawColumns(['checkbox','action','ref_id','status','ref_id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','payment_due_date','note','customer']);
            return $dt->make(true);
    }

    public function getDraftTdData(Request $request)
    {
        $query = DraftPurchaseOrder::with('draftPoDetail','getSupplier');

        if($request->dosortby == 23)
        {
            $query->where(function($q){
             $q->where('status', 23)->orderBy('id', 'DESC');
            });
        }

        if($request->from_date != null)
        {
           $query->where('target_receive_date', '>', $request->from_date.' 00:00:00');
        }
        if($request->to_date != null)
        {
           $query->where('target_receive_date', '<', $request->to_date.' 00:00:00');
        }

        return Datatables::of($query)

             ->addColumn('checkbox', function ($item) {

                    $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="stone_check_'.$item->id.'">
                                    <label class="custom-control-label" for="stone_check_'.$item->id.'"></label>
                                </div>';
                    return $html_string;
                })
            ->addColumn('po_id', function ($item) {
                if($item->id !== null){
                    $html_string = '
                    <a href="'.url('get-draft-td/'.$item->id).'"><b>'.$item->id.'</b></a>';
                }else{
                    $html_string = 'N.A';
                }
                return $html_string;
            })
            ->addColumn('supplier', function ($item) {
                return $item->from_warehouse_id !== null ? @$item->getFromWarehoue->warehouse_title : 'N.A';
            })
            ->addColumn('supplier_ref', function ($item) {
                return $item->supplier_id !== null ? $item->getSupplier->reference_number : 'N.A';
            })
            ->addColumn('confirm_date', function ($item) {
                $html_string = '---';
                return $html_string;
            })
            ->addColumn('target_receive_date', function ($item) {
                $html_string = '---';
                return $html_string;
            })
            ->addColumn('supply_to', function ($item) {
                return $item->to_warehouse_id !== null ? @$item->getWarehoue->warehouse_title : 'N.A';
            })
            ->rawColumns(['action', 'status','po_id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','supply_to','checkbox'])
            ->make(true);
    }

    public function getDraftPoData(Request $request)
    {
        $query = DraftPurchaseOrder::with('draftPoDetail','getSupplier');

        if($request->dosortby === 16)
        {
            $query->where(function($q){
                $q->where('status', 16)->orderBy('id', 'DESC');
            });
        }

        if ($request->date_radio == '1') {
            $date_column = 'target_receive_date';
        }
        else{
            $date_column = 'invoice_date';
        }
        if($request->from_date != null)
        {
        //    $query->where('target_receive_date', '>', $request->from_date.' 00:00:00');
           $query->where($date_column, '>', $request->from_date.' 00:00:00');
        }
        if($request->to_date != null)
        {
        //    $query->where('target_receive_date', '<', $request->to_date.' 23:59:59');
           $query->where($date_column, '<', $request->to_date.' 23:59:59');
        }

        if($request->selecting_suppliers != null)
        {
            $query->where('supplier_id', $request->selecting_suppliers);
        }

        return Datatables::of($query)

             ->addColumn('checkbox', function ($item) {

                    $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="stone_check_'.$item->id.'">
                                    <label class="custom-control-label" for="stone_check_'.$item->id.'"></label>
                                </div>';
                    return $html_string;
                })

            ->editColumn('id', function ($item) {
                if($item->id !== null){
                    $html_string = '<a href="'.url('get-draft-po/'.$item->id).'"><b>'.$item->id.'</b></a>';
                }else{
                    $html_string = 'N.A';
                }
                return $html_string;
            })
            ->addColumn('supplier', function ($item) {
                return $item->supplier_id !== null ? @$item->getSupplier->reference_name : 'N.A';
            })
            ->addColumn('supplier_ref', function ($item) {
                return $item->supplier_id !== null ? $item->getSupplier->reference_number : 'N.A';
            })
            ->addColumn('confirm_date', function ($item) {
                $html_string = '---';
                return $html_string;
            })
            ->addColumn('target_receive_date', function ($item) {
                $html_string = '---';
                return $html_string;
            })
            ->addColumn('customer', function ($item) {
                $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="'.$item->id.'" class="fa fa-group d-block show-po-cust mr-2" title="View Customers"></a> ';
                return $html_string;
            })
            ->addColumn('invoice_date', function ($item) {
                $date = $item->invoice_date != null ? $item->invoice_date : '---';
                return $date;
            })
            ->rawColumns(['status','id','supplier','supplier_ref','confirm_date','po_total','target_receive_date','customer','checkbox'])
            ->make(true);
    }

    public function changeStatusOfPo(Request $request)
    {
        $statusChange = PurchaseOrder::find($request->pId);
        $statusChange->status = $request->id;
        $statusChange->save();

        return response()->json(['success' => true]);
    }

    public function getPoCustomers(Request $request)
    {
        $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', NULL)->where('po_id',$request->po_id)->get()->groupBy('customer_id');

        $html_string = '';
        $i = 0;
        if($getCust->count() > 0)
        {
            foreach ($getCust as $value)
            {
                if($value != Null)
                {
                    $i++;
                    $html_string .= '<tr>';

                    $html_string .= '<td>';
                    $html_string .= $i;
                    $html_string .= '</td>';

                    $html_string .= '<td>';
                    $html_string .= @$value[0]->customer->reference_name;
                    $html_string .= '</td>';

                    $html_string .= '</tr>';
                }
            }
        }
        else
        {
            $html_string .= '<tr>';
            $html_string .= '<td colspan="2">';
            $html_string .= "No Customer Found In This Purchase Order!!!";
            $html_string .= '</td>';
            $html_string .= '</tr>';
        }

        return response()->json(['success' => true, 'html_string' => $html_string]);
    }

    public function getPoNotes(Request $request)
    {
        $getNotes = PurchaseOrderNote::where('po_id',$request->po_id)->get();
        $html_string = '';
        $i = 0;
        if($getNotes->count() > 0)
        {
            foreach ($getNotes as $getNote)
            {
                $i++;
                $html_string .= '<tr>';

                    $html_string .= '<td>';
                    $html_string .= $i;
                    $html_string .= '</td>';

                    $html_string .= '<td>';
                    $html_string .= @$getNote->note;
                    $html_string .= '</td>';

                $html_string .= '</tr>';
            }
        }
        else
        {
            $html_string .= '<tr>';
            $html_string .= '<td colspan="2">';
            $html_string .= "No Notes Found In This Purchase Order!!!";
            $html_string .= '</td>';
            $html_string .= '</tr>';
        }

        return response()->json(['success' => true, 'html_string' => $html_string]);
    }

    public function getPurchaseOrderDetail($id)
    {
        $paymentTerms           = PaymentTerm::all();
        $getPurchaseOrder       = PurchaseOrder::with('p_o_group.po_group','p_o_statuses','PoSupplier','ToWarehouse','pOpaymentTerm','createdBy','po_notes','po_documents')->find($id);
        $warehouses             = Warehouse::where('status',1)->get();
        $company_info           = Company::with('getcountry','getstate')->where('id',$getPurchaseOrder->createdBy->company_id)->first();
        $getPoNote              = $getPurchaseOrder->po_notes->first();
        $checkPoDocs            = $getPurchaseOrder->po_documents->count();

        $quotation_config   = QuotationConfig::where('section','purchase_order')->first();
        $hidden_by_default  = '';
        $hidden_columns     = null;
        $columns_prefrences = null;
        $hidden_columns_by_admin = [];
        $user_plus_admin_hidden_columns = [];
        if($quotation_config == null)
        {
            $hidden_by_default = '';
        }
        else
        {
          $dislay_prefrences = $quotation_config->display_prefrences;
          $hide_columns = $quotation_config->show_columns;
          if($quotation_config->show_columns != null)
          {
            $hidden_columns = json_decode($hide_columns);
            $hidden_columns = implode (",", $hidden_columns);
            $hidden_columns_by_admin = explode (",", $hidden_columns);
          }
          $columns_prefrences = json_decode($quotation_config->display_prefrences);
          $columns_prefrences = implode(",",$columns_prefrences);

          $user_hidden_columns = [];
          $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','po_detail')->where('user_id',Auth::user()->id)->first();
          if($not_visible_columns != null)
          {
            $user_hidden_columns = $not_visible_columns->hide_columns;
          }
          else
          {
            $user_hidden_columns = "";
          }
          $user_plus_admin_hidden_columns = $user_hidden_columns.','.$hidden_columns;
        }

        $supplier_currency_logo = @$getPurchaseOrder->PoSupplier->getCurrency->currency_symbol;

        if($getPurchaseOrder->status == 14)
        {
            $check_group = $getPurchaseOrder->po_group->id;
            $all_group = PoGroupDetail::where('po_group_id',$check_group)->get();
            if($all_group->count() > 1)
            {
                $pos_count = 2;
            }
            else if($all_group->count() == 1)
            {
                $pos_count = 1;
            }
            else
            {
                $pos_count = 0;
            }
        }
        else
        {
            $pos_count = 0;
        }

        $total_system_units = Unit::whereNotNull('id')->count();
        $itemsCount = PurchaseOrderDetail::where('is_billed','Product')->where('po_id',$id)->sum('quantity');

        $dummy_data = null;
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();
        $globalAccessConfig3 = QuotationConfig::where('section','target_ship_date')->first();
        if($globalAccessConfig3!=null)
        {
            $targetShipDate=unserialize($globalAccessConfig3->print_prefrences);
        }
        else
        {
            $targetShipDate=null;
        }

        if($getPurchaseOrder->supplier_id != null)
        {
            $display_prods = ColumnDisplayPreference::where('type', 'po_detail')->where('user_id', Auth::user()->id)->first();
            return view('users.purchase-order.purchase-order-detail',compact('getPurchaseOrderDetail','table_hide_columns','display_purchase_list','id','getPurchaseOrder','checkPoDocs','getPoNote','po_setting','company_info','warehouses','supplier_currency_logo','paymentTerms','columns_prefrences','hidden_columns','pos_count','hidden_columns_by_admin','user_plus_admin_hidden_columns','dummy_data','targetShipDate', 'display_prods'));
        }
        else
        {
            $allow_custom_invoice_number = '';
            $show_custom_line_number = '';
            $show_supplier_invoice_number = '';
            $globalAccessConfig4 = QuotationConfig::where('section','groups_management_page')->first();
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

            $display_prods = ColumnDisplayPreference::where('type', 'td_detail')->where('user_id', Auth::user()->id)->first();

            return view('users.purchase-order.warehouse-purchase-order-detail',compact('getPurchaseOrderDetail','id','getPurchaseOrder','checkPoDocs','getPoNote','po_setting','company_info','paymentTerms','total_system_units','itemsCount','global_terminologies','sys_name','sys_logos','sys_color','sys_border_color','btn_hover_border','current_version','dummy_data','targetShipDate','allow_custom_invoice_number','show_custom_line_number','show_supplier_invoice_number', 'display_prods'));
        }
    }

    public function SavePoNote(Request $request)
    {
        return PODetailCRUDHelper::SavePoNote($request);
    }

    public function SaveDraftPoDates(Request $request)
    {
        return DraftPOInsertUpdateHelper::SaveDraftPoDates($request);
    }

    public function SavePoProductQuantity(Request $request)
    {
        return PODetailCRUDHelper::SavePoProductQuantity($request);
    }

    public function SavePoProductVatActual(Request $request)
    {
        return PODetailCRUDHelper::SavePoProductVatActual($request);
    }

    public function SavePoProductDesc(Request $request)
    {
        $po = PurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
        foreach($request->except('rowId','po_id','old_value') as $key => $value)
        {
            if($key == 'billed_desc')
            {
                $po->$key = $value;
                $order_history = new PurchaseOrdersHistory;
                $order_history->user_id = Auth::user()->id;
                $order_history->reference_number = "Billed Item";
                $order_history->old_value = @$request->old_value;
                $order_history->column_name = "Billed Description";
                $order_history->new_value = @$value;
                $order_history->po_id = @$po->po_id;
                $order_history->save();
            }
        }

        $po->save();

        return response()->json(['success' => true]);
    }

    public function SaveDpoProductDesc(Request $request)
    {

        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->po_id)->first();
        foreach($request->except('rowId','po_id','old_value') as $key => $value)
        {
            if($key == 'billed_desc')
            {
                $po->$key = $value;
            }
        }

        $po->save();

        return response()->json(['success' => true]);
    }

    public function SavePoProductDiscount(Request $request)
    {
        return PODetailCRUDHelper::SavePoProductDiscount($request);
    }

    public function SaveDraftPoProductQuantity(Request $request)
    {
        return DraftPOInsertUpdateHelper::SaveDraftPoProductQuantity($request);
    }

    public function SaveDraftPoVatActual(Request $request)
    {
        return DraftPOInsertUpdateHelper::SaveDraftPoVatActual($request);
    }

    public function updateDraftPoDesiredQuantity(Request $request)
    {
        return DraftPOInsertUpdateHelper::updateDraftPoDesiredQuantity($request);
    }

    public function updateDraftPoBilledUnitPerPackage(Request $request)
    {
        return DraftPOInsertUpdateHelper::updateDraftPoBilledUnitPerPackage($request);
    }

    public function SaveDraftPoProductDiscount(Request $request)
    {
        // dd($request->all());
        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();
        $checkSameProduct = DraftPurchaseOrderDetail::where('po_id',$po->po_id)->where('product_id',$po->product_id)->get();
        foreach($request->except('rowId','draft_po_id','total') as $key => $value)
        {
            if($key == 'discount')
            {

                        $po->$key = $value;
                        $po->save();
            }
        }

        $updateRow = DraftPurchaseOrderDetail::find($po->id);
        $sub_total = 0;
        $query     = DraftPurchaseOrderDetail::where('po_id',$request->draft_po_id)->get();

        foreach ($query as  $value)
        {
            $unit_price = $value->pod_unit_price;
            $sub        = $value->quantity * $unit_price - (($value->quantity * $unit_price) * ($value->discount / 100));
            $value->pod_total_unit_price = $sub;
            $value->save();
            $sub_total += $sub;
        }
        $po = DraftPurchaseOrderDetail::where('id',$request->rowId)->where('po_id',$request->draft_po_id)->first();

        foreach($request->except('rowId','old_value','draft_po_id') as $key => $value)
        {
             if($key == 'discount')
            {

                $order_history = new DraftPurchaseOrderHistory;
                $order_history->user_id = Auth::user()->id;
                $order_history->reference_number = $po->product_id;
                $order_history->old_value = $request->old_value;
                $order_history->column_name = "Discount";
                $order_history->po_id = $request->draft_po_id;
                $order_history->new_value = $value;
                $order_history->save();
            }

        }

        $po_modifications = DraftPurchaseOrder::find($request->draft_po_id);
        $po_modifications->total = $sub_total;
        $po_modifications->save();

        $calColumn = $objectCreated->calColumns($po);

        return response()->json([
            'success' => true,
            'updateRow' => $updateRow,
            'sub_total'=>$sub_total,
            'id'        => $po->id,
            'unit_price' => $calColumn['unit_price'],
            'unit_price_w_vat' => $calColumn['unit_price_w_vat'],
            'total_amount_wo_vat' => $calColumn['total_amount_wo_vat'],
            'total_amount_w_vat' => $calColumn['total_amount_w_vat'],
            'unit_gross_weight' => $calColumn['unit_gross_weight'],
            'total_gross_weight' => $calColumn['total_gross_weight'],
            'desired_qty' => $calColumn['desired_qty'],
            'quantity' => $calColumn['quantity'],
        ]);
    }

    public function getPurchaseOrderProdDetail(Request $request, $id)
    {
        $details = PurchaseOrderDetail::with('customer','product.supplier_products','getOrder','PurchaseOrder.PoSupplier.getCurrency','product.units','getWarehouse','pod_histories','order_product.product.sellingUnits','order_product.get_order_product_notes','pod_notes','product.productType','getProductStock')->where('purchase_order_details.po_id',$id)->select('purchase_order_details.*');
        $details = PurchaseOrderDetail::PurchaseOrderDetailSorting($request, $details);
        // dd($details->first()->getProductStock->where('warehouse_id', 1)->first()->id);

        $dt = Datatables::of($details);

        $add_columns = ['weight', 'unit_gross_weight', 'discount', 'remarks', 'supplier_packaging', 'order_no', 'amount_with_vat', 'amount', 'last_updated_price_on', 'unit_price_with_vat', 'unit_price', 'billed_unit_per_package', 'purchasing_vat', 'desired_qty', 'gross_weight', 'customer_pcs', 'customer_qty', 'quantity', 'warehouse', 'buying_unit', 'type', 'leading_time', 'brand', 'product_description', 'customer', 'item_ref', 'supplier_id', 'action','current_stock_qty', 'unit_price_after_discount'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function($item) use ($column) {
                return PurchaseOrderDetail::returnAddColumn($column, $item);
             });
        }

        $edit_columns = ['short_desc'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return PurchaseOrderDetail::returnEditColumn($column, $item);
             });
        }

        $filter_columns = ['short_desc', 'product_description', 'customer', 'item_ref', 'supplier_id'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrderDetail::returnFilterColumn($column, $item, $keyword);
            });
        }

        $dt->setRowId(function ($item) {
            return @$item->id;
        });
        $dt->rawColumns(['action', 'supplier_id','supplier_ref','item_ref','customer','short_desc','buying_unit','quantity','unit_price','amount','remarks','order_no','warehouse','gross_weight','supplier_packaging','billed_unit_per_package','customer_qty','discount','desired_qty','pkg_billed_est','customer_pcs','product_description','a','unit_gross_weight','weight','amount_with_vat','unit_price_with_vat','purchasing_vat', 'unit_price_after_discount']);
        return $dt->make(true);
    }

    public function exportWaitingConformationPO(Request $request)
    {
        if ($request->type == 'example') {
            $query = null;
        }
        else{
            $query = PurchaseOrderDetail::with('customer','product','getOrder')->where('purchase_order_details.po_id',$request->id)->select('purchase_order_details.*');
            $query = PurchaseOrderDetail::PurchaseOrderDetailSorting($request, $query);
        }

        $current_date = date("Y-m-d");

        $quotation_config   = QuotationConfig::where('section','purchase_order')->first();
        $hidden_by_default  = '';
        $hidden_columns     = null;
        $columns_prefrences = null;
        $hidden_columns_by_admin = [];
        $user_plus_admin_hidden_columns = [];
        if($quotation_config == null)
        {
            $hidden_by_default = '';
        }
        else
        {
          $dislay_prefrences = $quotation_config->display_prefrences;
          $hide_columns = $quotation_config->show_columns;
          if($quotation_config->show_columns != null)
          {
            $hidden_columns = json_decode($hide_columns);
            $hidden_columns = implode (",", $hidden_columns);
            $hidden_columns_by_admin = explode (",", $hidden_columns);
          }
          $columns_prefrences = json_decode($quotation_config->display_prefrences);
          $columns_prefrences = implode(",",$columns_prefrences);

          $user_hidden_columns = [];
          $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','po_detail')->where('user_id',Auth::user()->id)->first();
          if($not_visible_columns != null)
          {
            $user_hidden_columns = $not_visible_columns->hide_columns;
          }
          else
          {
            $user_hidden_columns = "";
          }
          $user_plus_admin_hidden_columns = $user_hidden_columns.','.$hidden_columns;
          $user_plus_admin_hidden_columns = trim($user_plus_admin_hidden_columns,",");
          $user_plus_admin_hidden_columns = explode (",", $user_plus_admin_hidden_columns);
        }
        $filename = 'Waiting Conformation PO Export.xlsx';
        \Excel::store(new waitingConformationPOExport($query,$user_plus_admin_hidden_columns), $filename);
        return response()->json(['success' =>true]);
    }

    public function getPurchaseOrderProdDetailTD(Request $request, $id)
    {
        $is_transfer = $request->is_transfer;
        $details = PurchaseOrderDetail::with('customer','product','getOrder', 'PurchaseOrder')->where('po_id',$id)->select('purchase_order_details.*');

        $details = PurchaseOrderDetail::PurchaseOrderDetailSorting($request, $details);

        $dt = Datatables::of($details);
        $add_columns = ['custom_invoice_number', 'custom_line_number', 'supplier_invoice_number', 'discount', 'remarks', 'billed_unit_per_package', 'supplier_packaging', 'order_no', 'amount', 'unit_price', 'quantity_received', 'qty_sent', 'gross_weight', 'customer_qty', 'quantity', 'warehouse', 'selling_unit', 'buying_unit', 'short_desc', 'customer', 'type', 'brand', 'item_ref', 'supplier_id', 'action'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column,$is_transfer) {
                return TransferDocumentDatatable::returnAddColumnTransferDocDetailPage($column, $item, $is_transfer);
            });
        }

            $dt->setRowId(function ($item) {
                    return @$item->id;
            });
            $dt->rawColumns(['action', 'supplier_id','supplier_ref','item_ref','customer','short_desc','buying_unit','quantity','unit_price','amount','remarks','order_no','warehouse','gross_weight','supplier_packaging','billed_unit_per_package','customer_qty','discount','custom_line_number','custom_invoice_number','supplier_invoice_number', 'quantity_received']);
            return $dt->make(true);
    }


    public function exportWaitingConformationTD(Request $request)
    {
        $query = PurchaseOrderDetail::with('customer','product','getOrder')->where('po_id',$request->id)->select('purchase_order_details.*');
         $query = PurchaseOrderDetail::PurchaseOrderDetailSorting($request, $query)->get();
        $getPurchaseOrder = PurchaseOrder::find($request->id);
        $is_bonded = $getPurchaseOrder->ToWarehouse->is_bonded;
        $globalAccessConfig4 = QuotationConfig::where('section','groups_management_page')->first();
        $allow_custom_invoice_number = '';
        $show_custom_line_number = '';
        $show_supplier_invoice_number = '';
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
        $current_date = date("Y-m-d");
        \Excel::store(new waitingConformationTDExport($query, $is_bonded, $allow_custom_invoice_number, $show_custom_line_number, $show_supplier_invoice_number), 'Waiting Conformation TD Export.xlsx');
        return response()->json(['success' => true]);
    }

    public function SavePoProductWarehouse(Request $request)
    {
        return PODetailCRUDHelper::SavePoProductWarehouse($request);
    }

    public function UpdateUnitPrice(Request $request)
    {
        return PODetailCRUDHelper::UpdateUnitPrice($request);
    }
    public function UpdateUnitPriceAfterDiscount(Request $request)
    {
        return PODetailCRUDHelper::UpdateUnitPriceAfterDiscount($request);
    }

    public function UpdateUnitPriceWithVat(Request $request)
    {
        return PODetailCRUDHelper::UpdateUnitPriceWithVat($request);
    }

    public function updateUnitGrossWeight(Request $request)
    {
        return PODetailCRUDHelper::updateUnitGrossWeight($request);
    }

    public function updateGroupViaPo($id)
    {
        // $total_import_tax_book_price = 0;
        // $total_vat_actual_price      = 0;
        $po_totoal_change = PurchaseOrder::find($id);

        $total_buying_price_with_vat_in_thb = null;
        $total_gross_weight                 = null;
        $po_group_level_vat_actual          = null;

        $total_import_tax_book_price2 = null;
        $total_vat_actual_price2      = null;
        $total_buying_price_in_thb2   = null;

        // getting all po's with this po group
        $gettingAllPos = PoGroupDetail::select('purchase_order_id')->where('po_group_id', $po_totoal_change->po_group_id)->get();
        $po_group = PoGroup::find($po_totoal_change->po_group_id);

        if($po_group != null && $po_group->is_review == 0)
        {
            if($gettingAllPos->count() > 0)
            {
                foreach ($gettingAllPos as $allPos)
                {
                    $purchase_order = PurchaseOrder::find($allPos->purchase_order_id);
                    if($purchase_order->exchange_rate == null)
                    {
                        $exch_rate = $purchase_order->PoSupplier->getCurrency->conversion_rate;
                    }
                    else
                    {
                        $exch_rate = $purchase_order->exchange_rate;
                    }
                    $total_import_tax_book_price2       += $purchase_order->total_import_tax_book_price;
                    $total_gross_weight                 += $purchase_order->total_gross_weight;
                    $total_vat_actual_price2            += $purchase_order->total_vat_actual_price_in_thb;
                    $total_buying_price_with_vat_in_thb += $purchase_order->total_with_vat_in_thb;
                    $total_buying_price_in_thb2         += $purchase_order->total_in_thb;
                    $po_group_level_vat_actual          += ($purchase_order->vat_amount_total / $exch_rate);
                }
            }

            $po_group->po_group_import_tax_book           = $total_import_tax_book_price2;
            $po_group->po_group_vat_actual                = $total_vat_actual_price2;
            $po_group->total_buying_price_in_thb          = $total_buying_price_in_thb2;
            $po_group->total_buying_price_in_thb_with_vat = $total_buying_price_with_vat_in_thb; //(new col)
            $po_group->po_group_total_gross_weight        = $total_gross_weight;
            $po_group->vat_actual_tax                     = $po_group_level_vat_actual;
            $po_group->save();

            #average unit price
            $average_unit_price = 0;
            $average_count = 0;
            foreach ($gettingAllPos as $po_id)
            {
                $average_count++;
                $purchase_order = PurchaseOrder::find($po_id->purchase_order_id);

                $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
                foreach ($purchase_order_details as $p_o_d) {

                    $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group->id)->where('supplier_id',$purchase_order->supplier_id)->first();

                    if($po_group_product != null)
                    {
                        if($po_group_product->occurrence > 1)
                        {
                            $ccr = $po_group_product->po_group->purchase_orders()->where('supplier_id',$po_group_product->supplier_id)->pluck('id')->toArray();
                            $buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price');
                            $po_group_product->unit_price_with_vat =  $buying_price / $po_group_product->occurrence;

                            $buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_wo_vat');
                            $po_group_product->unit_price          =  $buying_price_wo_vat / $po_group_product->occurrence;

                            $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price');
                            $po_group_product->total_unit_price_with_vat    =  $total_buying_price;

                            $total_buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_wo_vat');
                            $po_group_product->total_unit_price    =  $total_buying_price_wo_vat;

                            /*currency conversion rate*/
                            $average_currency = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'currency_conversion_rate');
                            $po_group_product->currency_conversion_rate = $average_currency/$po_group_product->occurrence;

                            /**/
                            $buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_in_thb');
                            $po_group_product->unit_price_in_thb_with_vat =  $buying_price / $po_group_product->occurrence;

                            $buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'buying_price_in_thb_wo_vat');
                            $po_group_product->unit_price_in_thb          =  $buying_price_wo_vat / $po_group_product->occurrence;

                            $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_in_thb');
                            $po_group_product->total_unit_price_in_thb_with_vat    =  $total_buying_price;

                            $total_buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_in_thb_wo_vat');
                            $po_group_product->total_unit_price_in_thb    =  $total_buying_price_wo_vat;
                        }
                        else
                        {
                            $po_group_product->unit_price                 = $p_o_d->pod_unit_price;
                            $po_group_product->unit_price_with_vat        = $p_o_d->pod_unit_price_with_vat;
                            $po_group_product->currency_conversion_rate   = $p_o_d->currency_conversion_rate;

                            $po_group_product->unit_price_in_thb          =  $p_o_d->unit_price_in_thb;
                            $po_group_product->unit_price_in_thb_with_vat =  $p_o_d->unit_price_with_vat_in_thb;

                            $ccr = $po_group_product->po_group->purchase_orders()->where('supplier_id',$po_group_product->supplier_id)->pluck('id')->toArray();

                            $total_buying_price = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price');
                            $po_group_product->total_unit_price_with_vat    =  $total_buying_price;

                            $total_buying_price_wo_vat = $po_group_product->averageCurrency($ccr,$po_group_product->product_id,'total_buying_price_wo_vat');
                            $po_group_product->total_unit_price    =  $total_buying_price_wo_vat;

                            if($po_group_product->discount > 0)
                            {
                                $discount_value = ($p_o_d->unit_price_in_thb * $po_group_product->quantity_inv) * ($po_group_product->discount / 100);
                                $discount_value_with_vat = ($p_o_d->unit_price_with_vat_in_thb * $po_group_product->quantity_inv) * ($po_group_product->discount / 100);
                            }
                            else
                            {
                                $discount_value = 0;
                                $discount_value_with_vat = 0;
                            }

                            $po_group_product->total_unit_price_in_thb          = $p_o_d->unit_price_in_thb * $po_group_product->quantity_inv - $discount_value;
                            $po_group_product->total_unit_price_in_thb_with_vat = $p_o_d->unit_price_with_vat_in_thb * $po_group_product->quantity_inv - $discount_value_with_vat;

                            $po_group_product->import_tax_book_price     = ($po_group_product->import_tax_book/100)*$po_group_product->total_unit_price_in_thb;

                            $po_group_product->pogpd_vat_actual_percent  = ($po_group_product->pogpd_vat_actual/100)*$po_group_product->total_unit_price_in_thb;
                        }


                        $po_group_product->save();
                    }
                }
            }

            $po_group = PoGroup::with('po_group_product_details')->where('id',$po_group->id)->first();

            $total_import_tax_book_price = 0;
            $total_vat_actual_price = 0;

            $total_import_tax_book_price_with_vat = 0;
            $total_vat_actual_price_with_vat = 0;

            $po_group_details = $po_group->po_group_product_details;
            // foreach ($po_group_details as $po_group_detail)
            // {
            //     $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
            //     $total_vat_actual_price += ($po_group_detail->pogpd_vat_actual_percent);
            // }
            $total_import_tax_book_price = $po_group->po_group_product_details->sum('import_tax_book_price');
            $total_vat_actual_price = $po_group->po_group_product_details->sum('pogpd_vat_actual_percent');

            if($total_import_tax_book_price == 0 || $total_vat_actual_price == 0)
            {
                foreach ($po_group_details as $po_group_detail)
                {
                    if($total_import_tax_book_price == 0)
                    {
                        $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                        $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                        $total_import_tax_book_price += $book_tax;

                        $book_tax_with_vat = (1/$count)* $po_group_detail->total_unit_price_in_thb_with_vat;
                        $total_import_tax_book_price_with_vat += $book_tax_with_vat;
                    }
                    if($total_vat_actual_price == 0)
                    {
                        $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                        $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                        $total_vat_actual_price += $book_tax;

                        $book_tax_with_vat = (1/$count)* $po_group_detail->total_unit_price_in_thb_with_vat;
                        $total_vat_actual_price_with_vat += $book_tax_with_vat;
                    }

                    $po_ids = $po_group_detail->po_group->po_group_detail->pluck('purchase_order_id')->toArray();
                    $pods = PurchaseOrderDetail::whereIn('po_id',$po_ids)->whereHas('PurchaseOrder',function($po) use ($po_group_detail){
                            $po->where('supplier_id',$po_group_detail->supplier_id);
                        })->where('product_id',$po_group_detail->product_id)->get();

                    $po_group_detail->total_gross_weight = $pods->sum('pod_total_gross_weight');
                    $po_group_detail->unit_gross_weight = $pods->sum('pod_gross_weight');
                    $po_group_detail->save();

                }
            }

            $po_group->po_group_import_tax_book  = floor($total_import_tax_book_price * 100) / 100;
            $po_group->po_group_vat_actual       = floor($total_vat_actual_price * 100) / 100;

            $po_group->po_group_import_tax_book_with_vat = floor($total_import_tax_book_price_with_vat * 100) / 100;
            $po_group->po_group_vat_actual_with_vat = floor($total_vat_actual_price_with_vat * 100) / 100;

            $po_group->save();

            $po_group_details = $po_group->po_group_product_details;
            $final_book_percent = 0;
            $final_vat_actual_percent = 0;
            $po_group_level_purchasing_vat = 0;
            foreach ($po_group_details as $po_group_detail)
            {
                if($po_group->freight !== NULL)
                {
                    $item_gross_weight     = $po_group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_freight         = $po_group->freight;
                    $total_quantity        = $po_group_detail->quantity_inv;
                    if($total_gross_weight != 0 && $total_gross_weight !== 0 && $total_quantity != 0 && $total_quantity !== 0)
                    {
                        $freight               = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    }
                    else
                    {
                        $freight = 0;
                    }
                    $po_group_detail->freight = $freight;

                    $po_group_detail->save();
                }
                if($po_group->landing !== NULL)
                {
                    $item_gross_weight     = $po_group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_quantity        = $po_group_detail->quantity_inv;
                    $total_landing         = $po_group->landing;
                    if($total_gross_weight != 0 && $total_gross_weight !== 0 && $total_quantity != 0 && $total_quantity !== 0)
                    {
                        $landing               = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    }
                    else
                    {
                        $landing = 0;
                    }
                    $po_group_detail->landing = $landing;

                    $po_group_detail->save();
                }

                if($po_group_detail->import_tax_book != null && $po_group_detail->import_tax_book != 0)
                {
                    // $final_book_percent = $final_book_percent +(($po_group_detail->import_tax_book/100) * $po_group_detail->total_unit_price_in_thb);
                    $check_dis = $po_group_detail->discount;
                    $discount_val = 0;
                    if($check_dis != null){
                        $discount_val = $po_group_detail->unit_price_in_thb * ($po_group_detail->discount/100);
                    }
                    $final_book_percent = $final_book_percent + round((($po_group_detail->import_tax_book/100) * round(($po_group_detail->unit_price_in_thb-$discount_val),2)) * $po_group_detail->quantity_inv,2);
                }

                if($po_group_detail->pogpd_vat_actual != null && $po_group_detail->pogpd_vat_actual != 0)
                {
                    $final_vat_actual_percent = $final_vat_actual_percent +(($po_group_detail->pogpd_vat_actual/100) * $po_group_detail->total_unit_price_in_thb);
                }
                $po_group_level_purchasing_vat += $po_group_detail->pogpd_vat_actual_price * $po_group_detail->quantity_inv;
            }
            $po_group->vat_actual_tax = $po_group_level_purchasing_vat;
            $po_group->save();

            $po_group_vat_actual = 0;
            $po_group_vat_actual_percent = 0;
            $po_group_details = $po_group->po_group_product_details;

            $po_group_vat_actual = $po_group_details->sum('pogpd_vat_actual_percent');
            $po_group_vat_actual_percent = $po_group_details->sum('pogpd_vat_actual');

            $total_import_tax_book_price = 0;
            $total_import_tax_book_percent = 0;

            $total_import_tax_book_price = $po_group->po_group_product_details->sum('import_tax_book_price');
            $total_import_tax_book_percent = $po_group->po_group_product_details->sum('import_tax_book');

            if($po_group_vat_actual == 0 || $total_import_tax_book_price == 0)
            {
                foreach ($po_group_details as $po_group_detail)
                {
                    if($po_group_vat_actual == 0)
                    {
                        $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                        $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                        $po_group_vat_actual += $book_tax;
                    }

                    if($total_import_tax_book_price == 0)
                    {
                        $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                        $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                        $total_import_tax_book_price += $book_tax;
                    }
                }
            }

            $po_group->po_group_vat_actual = $po_group_vat_actual;
            $po_group->po_group_vat_actual_percent = $po_group_vat_actual_percent;

            $po_group->po_group_import_tax_book = $total_import_tax_book_price;
            $po_group->total_import_tax_book_percent = $total_import_tax_book_percent;

            $po_group->save();

            foreach ($po_group_details as $group_detail) {
                if($po_group->tax !== null)
                {
                    $group_tax = $po_group->tax;

                    // $find_item_tax_value = $group_detail->import_tax_book/100 * $group_detail->total_unit_price_in_thb;
                    $check_dis = $group_detail->discount;
                    $discount_val = 0;
                    if($check_dis != null){
                        $discount_val = $group_detail->unit_price_in_thb * ($group_detail->discount/100);
                    }
                    $find_item_tax_value = round(round(($group_detail->import_tax_book / 100)*($group_detail->unit_price_in_thb - $discount_val),2) * $group_detail->quantity_inv,2);
                    if($final_book_percent != 0 && $group_tax != 0)
                    {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;

                        $cost = $find_item_tax * $group_tax;
                        if($group_tax != 0)
                        {
                            $group_detail->weighted_percent =  number_format(($find_item_tax)*100,8,'.','');
                        }
                        else
                        {
                            $group_detail->weighted_percent = 0;
                        }
                        $group_detail->save();

                        // $weighted_percent = ($group_detail->weighted_percent/100) * $group_tax;
                        $weighted_percent = round(($group_detail->weighted_percent/100) * $group_tax,2);

                        if($group_detail->quantity_inv != 0)
                        {
                            // $group_detail->actual_tax_price =  number_format(round($find_item_tax*$group_tax,2) / $group_detail->quantity_inv,2,'.','');
                            $group_detail->actual_tax_price =  round($weighted_percent / $group_detail->quantity_inv,2);
                        }
                        else
                        {
                            $group_detail->actual_tax_price =  0;
                        }
                        $group_detail->save();

                        if($group_detail->unit_price_in_thb != 0)
                        {
                            $group_detail->actual_tax_percent = number_format(($group_detail->actual_tax_price/$group_detail->unit_price_in_thb)* 100,2,'.','');
                        }
                        else
                        {
                            $group_detail->actual_tax_percent = 0;
                        }
                        $group_detail->save();
                    }
                    else if($group_tax != 0)
                    {
                        $all_pgpd = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->count();

                        $tax = $group_tax;
                        $weighted = 1 / $all_pgpd;
                        if($group_detail->quantity_inv != 0 && $group_detail->quantity_inv !== 0)
                        {
                            $group_detail->actual_tax_price = number_format(($weighted*$tax) / $group_detail->quantity_inv,2,'.','');
                        }
                        else
                        {
                            $group_detail->actual_tax_price = number_format(($weighted*$tax),2,'.','');
                        }
                        $group_detail->save();

                        if($group_detail->unit_price_in_thb != 0)
                        {
                            $group_detail->actual_tax_percent = ($group_detail->actual_tax_price / $group_detail->unit_price_in_thb)*100;
                        }
                        $group_detail->weighted_percent =  number_format(($weighted)*100,8,'.','');
                        $group_detail->save();
                    }

                }

                if($po_group->vat_actual_tax !== NULL)
                {
                    $vat_actual_tax = $po_group->vat_actual_tax;

                    $find_item_tax_value = $group_detail->pogpd_vat_actual/100 * $group_detail->total_unit_price_in_thb;
                    if($final_vat_actual_percent != 0 && $vat_actual_tax != 0)
                    {
                        $find_item_tax = $find_item_tax_value / $final_vat_actual_percent;

                        $cost = $find_item_tax * $vat_actual_tax;
                        if($vat_actual_tax != 0)
                        {
                            $group_detail->vat_weighted_percent =  number_format(($cost/$vat_actual_tax)*100,4,'.','');
                        }
                        else
                        {
                            $group_detail->vat_weighted_percent = 0;
                        }
                        $group_detail->save();

                        $vat_weighted_percent = ($group_detail->vat_weighted_percent/100) * $vat_actual_tax;

                        if($group_detail->quantity_inv != 0)
                        {
                            $group_detail->pogpd_vat_actual_price =  number_format(round($find_item_tax*$vat_actual_tax,2) / $group_detail->quantity_inv,2,'.','');
                        }
                        else
                        {
                            $group_detail->pogpd_vat_actual_price =  0;
                        }
                        $group_detail->save();

                        if($group_detail->unit_price_in_thb != 0)
                        {
                            $group_detail->pogpd_vat_actual_percent_val = number_format(($group_detail->pogpd_vat_actual_price/$group_detail->unit_price_in_thb)* 100,2,'.','');
                        }
                        else
                        {
                            $group_detail->pogpd_vat_actual_percent_val = 0;
                        }
                        $group_detail->save();
                    }
                    else if($vat_actual_tax != 0)
                    {
                    $all_pgpd = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->count();

                    $tax = $group_detail->po_group->vat_actual_tax;
                    $weighted = 1 / $all_pgpd;

                    if($group_detail->quantity_inv != 0 && $group_detail->quantity_inv !== 0)
                    {
                        $group_detail->pogpd_vat_actual_price = number_format(($weighted*$tax) / $group_detail->quantity_inv,2,'.','');
                    }
                    else
                    {
                        $group_detail->pogpd_vat_actual_price = number_format(($weighted*$tax),2,'.','');
                    }
                    $group_detail->save();

                    if($group_detail->unit_price_in_thb != 0)
                    {
                        $group_detail->pogpd_vat_actual_percent_val = ($group_detail->pogpd_vat_actual_price / $group_detail->unit_price_in_thb)*100;
                    }
                    $group_detail->vat_weighted_percent =  number_format(($weighted)*100,4,'.','');
                    $group_detail->save();
                }
                }
            }

        }

        return true;
    }

    public function UpdateBilledUnitPerPackage(Request $request)
    {
        return PODetailCRUDHelper::UpdateBilledUnitPerPackage($request);
    }

    public function UpdateDesireQty(Request $request)
    {
        return PODetailCRUDHelper::UpdateDesireQty($request);
    }

    public function UpdateDraftPoUnitPrice(Request $request)
    {
        return DraftPOInsertUpdateHelper::UpdateDraftPoUnitPrice($request);
    }

    public function UpdateDraftPoUnitPriceVat(Request $request)
    {
        return DraftPOInsertUpdateHelper::UpdateDraftPoUnitPriceVat($request);
    }

    public function UpdateDraftPoUnitGrossWeight(Request $request)
    {
        return DraftPOInsertUpdateHelper::UpdateDraftPoUnitGrossWeight($request);
    }

    public function setTargetReceiveDate(Request $request)
    {
        $setPoShipDate = PurchaseOrder::find($request->id);
        $setPoShipDate->target_receive_date = $request->receive_date;
        $setPoShipDate->save();
    }

    public function uploadPurchaseOrderDocuments(Request $request)
    {
        if(isset($request->po_docs))
        {
            // dd('here');
            for($i=0;$i<sizeof($request->po_docs);$i++){

                $purchase_order_doc        = new PurchaseOrderDocument;
                $purchase_order_doc->po_id = $request->purchase_order_id;
                //file
                $extension=$request->po_docs[$i]->extension();
                $filename=date('m-d-Y').mt_rand(999,999999).'__'.time().'.'.$extension;
                $request->po_docs[$i]->move(public_path('uploads/documents'),$filename);
                $purchase_order_doc->file_name = $filename;
                $purchase_order_doc->save();
            }
        }
        return response()->json(['success' => true]);
    }

    public function removeDraftOrderFile(Request $request)
    {
        if(isset($request->id)){
            // remove images from directory //
            $quotation_file = DraftPurchaseOrderDocument::find($request->id);

            $directory  = public_path().'/uploads/documents/';
            //remove main
            $this->removeFile($directory, $quotation_file->file_name);
            // delete record
            $quotation_file->delete();

            return "done"."-SEPARATOR-".$request->id;
        }
    }

    public function getDraftOrderFiles(Request $request)
    {
        $quotation_files = DraftPurchaseOrderDocument::where('po_id', $request->order_id)->get();

            $html_string ='<div class="table-responsive">
                            <table class="table dot-dash text-center">
                            <thead class="dot-dash">
                            <tr>
                                <th>S.no</th>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
                            </thead><tbody>';
                            if($quotation_files->count() > 0){
                            $i = 0;
                            foreach($quotation_files as $file){
                            $i++;
            $html_string .= '<tr id="quotation-file-'.$file->id.'">
                                <td>'.$i.'</td>
                                <td><a href="'.asset('public/uploads/documents/'.$file->file_name).'" target="_blank">'.$file->file_name.'</a></td>
                                <td><a href="javascript:void(0);" data-id="'.$file->id.'" class="actionicon deleteFileIcon delete-quotation-file" title="Delete Quotation File"><i class="fa fa-trash"></i></a></td>
                             </tr>';
                            }
                            }else{
            $html_string .= '<tr>
                                <td colspan="3">No File Found</td>
                             </tr>';
                            }


            $html_string .= '</tbody></table></div>';
            return $html_string;
    }

    public function uploadDraftPurchaseOrderDocuments(Request $request)
    {
        if(isset($request->po_docs))
        {
            for($i=0;$i<sizeof($request->po_docs);$i++){

                $purchase_order_doc        = new DraftPurchaseOrderDocument;
                $purchase_order_doc->po_id = $request->draft_purchase_order_id;
                //file
                $extension=$request->po_docs[$i]->extension();
                $filename=date('m-d-Y').mt_rand(999,999999).'__'.time().'.'.$extension;
                $request->po_docs[$i]->move(public_path('uploads/documents'),$filename);
                $purchase_order_doc->file_name = $filename;
                $purchase_order_doc->save();
            }
        }
        return response()->json(['success' => true]);
    }

    public function deleteProdFromPo(Request $request)
    {
        if($request->order_id != null)
        {
            $getToDelete = PurchaseOrderDetail::where('order_id',$request->order_id)->where('order_product_id',$request->order_product_id)->first();
            if($getToDelete->is_billed == "Product")
            {
                $order_history = new PurchaseOrdersHistory;
                $order_history->user_id = Auth::user()->id;
                $order_history->order_id         = @$getToDelete->order_id;
                $order_history->reference_number = $getToDelete->product->refrence_code." ( Ref ID#. ".$getToDelete->id." )";
                $order_history->old_value = "Purchase Order";
                $order_history->new_value = "Reverted";
                $order_history->po_id = $request->po_id;
                $order_history->save();
            }

            $delProdFromList = PurchaseOrderDetail::where('order_id',$request->order_id)->where('order_product_id',$request->order_product_id)->delete();
            $orderStatusChange = Order::where('id',$request->order_id)->first();

            if($orderStatusChange->primary_status != 3 && $orderStatusChange->primary_status != 17 )
            {
                $orderStatusChange->status = 7;
                $orderStatusChange->save();

                // now order_products table status change
                $orderProductStatus = OrderProduct::find($request->order_product_id);
                $orderProductStatus->status = 7;
                $orderProductStatus->save();
            }
        }
        else
        {
            $getToDelete = PurchaseOrderDetail::where('po_id',$request->po_id)->where('id',$request->id)->first();
            if($getToDelete->is_billed == "Product")
            {
                $order_history = new PurchaseOrdersHistory;
                $order_history->user_id = Auth::user()->id;
                $order_history->order_id         = @$getToDelete->order_id;
                $order_history->reference_number = $getToDelete->product->refrence_code." ( Ref ID#. ".$getToDelete->id." )";
                $order_history->old_value = "Purchase Order";
                $order_history->new_value = "Revert To Purchase List";
                $order_history->po_id = $request->po_id;
                $order_history->save();
            }

            $delProdFromList = PurchaseOrderDetail::where('po_id',$request->po_id)->where('id',$request->id)->delete();
        }

        $redirect = "no";
        $checkPoDetailProduct = PurchaseOrderDetail::where('po_id',$request->po_id)->get();
        $itemsCount = PurchaseOrderDetail::where('is_billed','Product')->where('po_id',$request->po_id)->sum('quantity');

        if($checkPoDetailProduct->count() > 0)
        {
            /*calulation through a function*/
            $objectCreated = new PurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);
        }
        else
        {
            $deletePurchaseOrder = PurchaseOrder::where('id',$request->po_id)->delete();
            $redirect = "yes";
        }

        return response()->json([
            'success'   => true,
            'sub_total' => @$grandCalculations['sub_total'],
            'total_qty' => @$grandCalculations['total_qty'],
            'vat_amout' => @$grandCalculations['vat_amout'],
            'total_w_v' => @$grandCalculations['total_w_v'],
            'redirect'  => $redirect,
            'total_qty' => $itemsCount
        ]);
    }

    public function deleteProdFromPoDetail(Request $request)
    {
        return PODetailCRUDHelper::deleteProdFromPoDetail($request);
    }

    public function checkPoProductNumbers(Request $request)
    {
        $msg = "";
        $checkPoDetailProduct = PurchaseOrderDetail::where('po_id',$request->po_id)->count();
        if($checkPoDetailProduct == 1)
        {
            if($request->doc_for == "PO")
            {
                $msg = "This is the last item left in this PO, if you delete this it will also delete the PO !!!";
            }
            else
            {
                $msg = "This is the last item left in this TD, if you delete this it will also delete the TD !!!";
            }
            return response()->json(['success' => true, 'msg' => $msg]);
        }
        else
        {
            $msg = "";
            return response()->json(['success' => true, 'msg' => $msg]);
        }
    }

    public function deleteDraftPoNote(Request $request)
    {
        $draft_po_detail = DraftPurchaseOrderDetailNote::find($request->id);
        $draft_po_detail->delete();

        return response()->json(['success' => true]);
    }

    public function deletePoDetailNote(Request $request)
    {
        // dd($request->all());
        $draft_po_detail = OrderProductNote::find($request->id);
        $draft_po_detail->delete();

        return response()->json(['success' => true]);
    }

    public function removeProductFromDraftPo(Request $request)
    {
        return DraftPOInsertUpdateHelper::removeProductFromDraftPo($request);
    }

    public function downloadDocuments(Request $request, $id)
    {
        $downloadDocs = PurchaseOrderDocument::where('po_id',$id)->get();
        $zipper = new \Chumper\Zipper\Zipper;
        $path = public_path('uploads\\documents\\');

        foreach($downloadDocs as $docs)
        {
            $files[] = glob($path.$docs->file_name);
        }

        $zipper->make(public_path('uploads\\documents\\'.$id.'\\zipped\\files.zip'))->add($files);
        $zipper->close();
        return response()->download(public_path('uploads\\documents\\'.$id.'\\zipped\\files.zip'));
    }

    public function getPurchaseOrderFiles(Request $request)
    {
        $purchase_order_files = PurchaseOrderDocument::where('po_id', $request->po_id)->get();

            $html_string ='<div class="table-responsive">
                            <table class="table dot-dash text-center">
                            <thead class="dot-dash">
                            <tr>
                                <th>S.no</th>
                                <th>File</th>
                                <th>Action</th>
                            </tr>
                            </thead><tbody>';
                            if($purchase_order_files->count() > 0){
                            $i = 0;
                            foreach($purchase_order_files as $file){
                            $i++;
            $html_string .= '<tr id="purchase-order-file-'.$file->id.'">
                                <td>'.$i.'</td>
                                <td><a href="'.asset('public/uploads/documents/'.$file->file_name).'" target="_blank">'.$file->file_name.'</a></td>
                                <td><a href="javascript:void(0);" data-id="'.$file->id.'" class="actionicon deleteIcon delete-purchase-order-file" title="Delete Transfer Document File"><i class="fa fa-trash"></i></a></td>
                             </tr>';
                            }
                            }else{
            $html_string .= '<tr>
                                <td colspan="3">No File Found</td>
                             </tr>';
                            }


            $html_string .= '</tbody></table></div>';
            return $html_string;
    }

    public function removePurchaseOrderFile(Request $request)
    {
        if(isset($request->id)){
            // remove images from directory //
            $quotation_file = PurchaseOrderDocument::find($request->id);

            $directory  = public_path().'/uploads/documents/';
            //remove main
            $this->removeFile($directory, $quotation_file->file_name);
            // delete record
            $quotation_file->delete();

            return "done"."-SEPARATOR-".$request->id;
        }
    }

    private function removeFile($directory, $imagename)
    {
        if(isset($directory) && isset($imagename))
            File::delete($directory.$imagename);
            return true;
        return false;
    }

    public function downloadDraftPoDocuments(Request $request, $id)
    {
        $downloadDocs = DraftPurchaseOrderDocument::where('po_id',$id)->get();
        $zipper = new \Chumper\Zipper\Zipper;
        $path = public_path('uploads\\documents\\');

        foreach($downloadDocs as $docs)
        {
            $files[] = glob($path.$docs->file_name);
        }

        $zipper->make(public_path('uploads\\documents\\'.$id.'\\zipped\\files.zip'))->add($files);
        $zipper->close();
        return response()->download(public_path('uploads\\documents\\'.$id.'\\zipped\\files.zip'));
    }

    public function confirmPurchaseOrder(Request $request)
    {
        $total_import_tax_book_price = null;
        $total_vat_actual_price = null;
        $confirm_date = date("Y-m-d");
        $po = PurchaseOrder::find($request->id);

        if($po->status == 13 || $po->status == '13')
        {
          $errorMsg =  'This Purchase Order is already confirmed !!!';
          $status = "waiting-shipping-info";
          return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'status' => $status]);
        }
        if($po->status == 27 || $po->status == '27')
        {
          $errorMsg =  'This Credit Note is already confirmed !!!';
          $status = "Complete";
          return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'status' => $status]);
        }
        if($po->status == 30 || $po->status == '30')
        {
          $errorMsg =  'This Debit Note is already confirmed !!!';
          $status = "Complete";
          return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'status' => $status]);
        }

        $po_detail = PurchaseOrderDetail::where('po_id',$request->id)->get();
        if($po_detail->count() > 0)
        {
          foreach ($po_detail as $value)
          {
            if($value->quantity === null)
            {
              $errorMsg =  'Quantity cannot be Null, please enter the quantity of the added items';
              return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

            if($value->is_billed == "Billed")
            {
                if($value->billed_desc == null)
                {
                  $errorMsg =  'Billed Item Description Cannot Be Empty.';
                  return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

                if($value->pod_unit_price === null)
                {
                  $errorMsg =  'Billed Item Unit Price Cannot Be Empty.';
                  return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }
          }
        }

        if($po->supplier_id != null)
        {
            if($po->exchange_rate == null)
            {
                $supplier_conv_rate_thb = $po->PoSupplier->getCurrency->conversion_rate;
            }
            else
            {
                $supplier_conv_rate_thb = $po->exchange_rate;
            }

            $getDataViaListing = PurchaseOrderDetail::where('po_id',$request->id)->orderBy('product_id','ASC')->get();
            foreach ($getDataViaListing as $p_o_d)
            {
                // New Logic on confirm update product detail page values starts here
                if($p_o_d->is_billed == "Product")
                {
                    $getProduct = Product::find($p_o_d->product_id);

                    $productCount = PurchaseOrderDetail::where('po_id',$po->id)->where('product_id',$p_o_d->product_id)->where('discount','<',100)->orderBy('product_id','DESC')->first();
                    if($productCount)
                    {
                        if($p_o_d->discount < 100 || $p_o_d->discount == NULL)
                        {
                            $discount_price = $p_o_d->quantity * $p_o_d->pod_unit_price - (($p_o_d->quantity * $p_o_d->pod_unit_price) * ($p_o_d->discount / 100));
                            if($p_o_d->quantity != 0 && $p_o_d->quantity != null)
                            {
                                $calculated_unit_price = ($discount_price / $p_o_d->quantity);
                            }
                            else
                            {
                                $calculated_unit_price = $discount_price;
                            }

                            $gettingProdSuppData  = SupplierProducts::where('product_id',$p_o_d->product_id)->where('supplier_id',$po->supplier_id)->first();

                            if($gettingProdSuppData)
                            {
                                $old_price_value = $gettingProdSuppData->buying_price;
                                $gettingProdSuppData->gross_weight        = $p_o_d->pod_gross_weight;
                                $gettingProdSuppData->save();

                                if($calculated_unit_price != 0 && $calculated_unit_price !== 0)
                                {
                                    if(($p_o_d->pod_unit_price != null || $p_o_d->pod_unit_price != 0) && ($p_o_d->pod_unit_price != $gettingProdSuppData->buying_price || $p_o_d->discount != NULL ) )
                                    {
                                        $gettingProdSuppData->buying_price        = $calculated_unit_price;
                                        $gettingProdSuppData->buying_price_in_thb = ($calculated_unit_price / $supplier_conv_rate_thb);
                                        $gettingProdSuppData->save();

                                        // $p_o_d->last_updated_price_on   = date('Y-m-d');
                                        $p_o_d->save();

                                        // Updating product COGS
                                        if($getProduct->supplier_id == $po->supplier_id)
                                        {
                                            $buying_price_in_thb = ($gettingProdSuppData->buying_price / $supplier_conv_rate_thb);

                                            $importTax = $gettingProdSuppData->import_tax_actual !== null ? $gettingProdSuppData->import_tax_actual : $getProduct->import_tax_book;

                                            $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                                            $total_buying_price = ($gettingProdSuppData->freight)+($gettingProdSuppData->landing)+($gettingProdSuppData->extra_cost)+($gettingProdSuppData->extra_tax)+($total_buying_price);

                                            $getProduct->total_buy_unit_cost_price = $total_buying_price;

                                            $getProduct->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;

                                            $total_selling_price = $getProduct->total_buy_unit_cost_price * $getProduct->unit_conversion_rate;

                                            $getProduct->selling_price           = $total_selling_price;
                                            $getProduct->last_price_updated_date = $p_o_d->last_updated_price_on != null ? $p_o_d->last_updated_price_on : Carbon::now();
                                            $getProduct->save();

                                            $product_history              = new ProductHistory;
                                            $product_history->user_id     = Auth::user()->id;
                                            $product_history->product_id  = $getProduct->id;
                                            $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                            $product_history->old_value   = $old_price_value;
                                            $product_history->new_value   = $gettingProdSuppData->buying_price;
                                            $product_history->save();
                                        }
                                        else
                                        {
                                            $product_history              = new ProductHistory;
                                            $product_history->user_id     = Auth::user()->id;
                                            $product_history->product_id  = $getProduct->id;
                                            $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                            $product_history->old_value   = $old_price_value;
                                            $product_history->new_value   = $gettingProdSuppData->buying_price;
                                            $product_history->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if($p_o_d->discount < 100 || $p_o_d->discount == NULL)
                        {
                            $discount_price = $p_o_d->quantity * $p_o_d->pod_unit_price - (($p_o_d->quantity * $p_o_d->pod_unit_price) * ($p_o_d->discount / 100));
                            if($p_o_d->quantity != 0 && $p_o_d->quantity != null)
                            {
                                $calculated_unit_price = ($discount_price / $p_o_d->quantity);
                            }
                            else
                            {
                                $calculated_unit_price = $discount_price;
                            }

                            $gettingProdSuppData  = SupplierProducts::where('product_id',$p_o_d->product_id)->where('supplier_id',$po->supplier_id)->first();

                            if($gettingProdSuppData)
                            {
                                $old_price_value = $gettingProdSuppData->buying_price;
                                $gettingProdSuppData->gross_weight        = $p_o_d->pod_gross_weight;
                                $gettingProdSuppData->save();

                                if($calculated_unit_price != 0 && $calculated_unit_price !== 0)
                                {
                                    if(($p_o_d->pod_unit_price != null || $p_o_d->pod_unit_price != 0) && ($p_o_d->pod_unit_price != $gettingProdSuppData->buying_price || $p_o_d->discount != NULL ) )
                                    {
                                        $gettingProdSuppData->buying_price        = $calculated_unit_price;
                                        $gettingProdSuppData->buying_price_in_thb = ($calculated_unit_price / $supplier_conv_rate_thb);
                                        $gettingProdSuppData->gross_weight        = $p_o_d->pod_gross_weight;
                                        $gettingProdSuppData->save();

                                        // $p_o_d->last_updated_price_on   = date('Y-m-d');
                                        $p_o_d->save();

                                        // Updating product COGS
                                        if($getProduct->supplier_id == $po->supplier_id)
                                        {
                                            $buying_price_in_thb = ($gettingProdSuppData->buying_price / $supplier_conv_rate_thb);

                                            $importTax = $gettingProdSuppData->import_tax_actual !== null ? $gettingProdSuppData->import_tax_actual : $getProduct->import_tax_book;

                                            $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

                                            $total_buying_price = ($gettingProdSuppData->freight)+($gettingProdSuppData->landing)+($gettingProdSuppData->extra_cost)+($gettingProdSuppData->extra_tax)+($total_buying_price);

                                            $getProduct->total_buy_unit_cost_price = $total_buying_price;

                                            $getProduct->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;

                                            $total_selling_price = $getProduct->total_buy_unit_cost_price * $getProduct->unit_conversion_rate;

                                            $getProduct->selling_price           = $total_selling_price;
                                            $getProduct->last_price_updated_date = $p_o_d->last_updated_price_on != null ? $p_o_d->last_updated_price_on : Carbon::now();
                                            $getProduct->save();

                                            $product_history              = new ProductHistory;
                                            $product_history->user_id     = Auth::user()->id;
                                            $product_history->product_id  = $getProduct->id;
                                            $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                            $product_history->old_value   = $old_price_value;
                                            $product_history->new_value   = $gettingProdSuppData->buying_price;
                                            $product_history->save();
                                        }
                                        else
                                        {
                                            $product_history              = new ProductHistory;
                                            $product_history->user_id     = Auth::user()->id;
                                            $product_history->product_id  = $getProduct->id;
                                            $product_history->column_name = "Purchasing Price Update (From PO - ".$p_o_d->PurchaseOrder->ref_id.")"." Ref ID#. ".$p_o_d->id;
                                            $product_history->old_value   = $old_price_value;
                                            $product_history->new_value   = $gettingProdSuppData->buying_price;
                                            $product_history->save();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                // New Logic on confirm update product detail page values ends here

                $p_o_d->currency_conversion_rate  = $supplier_conv_rate_thb;
                $p_o_d->unit_price_in_thb         = $p_o_d->pod_unit_price/$supplier_conv_rate_thb;
                $p_o_d->total_unit_price_in_thb   = $p_o_d->pod_total_unit_price/$supplier_conv_rate_thb;
                $p_o_d->pod_import_tax_book_price = ($p_o_d->pod_import_tax_book/100)*$p_o_d->total_unit_price_in_thb;
                $total_import_tax_book_price      += $p_o_d->pod_import_tax_book_price;
                // $p_o_d->pod_vat_actual_price      = ($p_o_d->pod_vat_actual/100)*$p_o_d->total_unit_price_in_thb;
                // $total_vat_actual_price           += $p_o_d->pod_vat_actual_price;
                $p_o_d->save();
            }
            if ($request->type == 'credit_note' || $request->type == 'debit_note') {
                $po->exchange_rate = $supplier_conv_rate_thb;
                $po->payment_terms_id = $po->PoSupplier->credit_term;
            }
            $po->confirm_date                = $confirm_date;
            $po->total_import_tax_book_price = $total_import_tax_book_price;
            // $po->total_vat_actual_price      = $total_vat_actual_price;
            if ($request->type == 'credit_note') {
                $po->status                      = 27;
                $po->primary_status              = 25;
            }
            else if ($request->type == 'debit_note') {
                $po->status                      = 30;
                $po->primary_status              = 28;
            }
            else{
                $po->status                      = 13;
            }
            $po->total_in_thb                = $po->total/$supplier_conv_rate_thb;
            $po->save();

            // PO status history maintaining
            if ($request->type == 'credit_note'){
                $page_status = Status::select('title')->whereIn('id',[26,27])->pluck('title')->toArray();
            }
            else if ($request->type == 'debit_note'){
                $page_status = Status::select('title')->whereIn('id',[29,30])->pluck('title')->toArray();
            }
            else{
                $page_status = Status::select('title')->whereIn('id',[12,13])->pluck('title')->toArray();
            }
            $poStatusHistory             = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id    = Auth::user()->id;
            $poStatusHistory->po_id      = $po->id;
            $poStatusHistory->status     = $page_status[0];
            $poStatusHistory->new_status = $page_status[1];
            $poStatusHistory->save();
        }
        else
        {
            foreach ($po->PurchaseOrderDetail as $p_o_d)
            {
                if($p_o_d->order_product_id != null)
                {
                    $p_o_d->order_product->status = 10;
                    $p_o_d->order_product->save();

                    $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('status','!=',10)->count();
                    if($order_products_status_count == 0)
                    {
                        $p_o_d->order_product->get_order->status = 10;
                        $p_o_d->order_product->get_order->save();
                        $order_history = new OrderStatusHistory;
                        $order_history->user_id = Auth::user()->id;
                        $order_history->order_id = @$p_o_d->order_product->get_order->id;
                        $order_history->status = 'DI(Importing)';
                        $order_history->new_status = 'DI(Waiting To Pick)';
                        $order_history->save();
                    }
                }
            }
            $po->status = 15;
            // PO status history maintaining
            $page_status = Status::select('title')->whereIn('id',[12,15])->pluck('title')->toArray();

            $poStatusHistory = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id    = Auth::user()->id;
            $poStatusHistory->po_id      = $po->id;
            $poStatusHistory->status     = $page_status[0];
            $poStatusHistory->new_status = $page_status[1];
            $poStatusHistory->save();
        }

        $po->save();

        /*calulation through a function*/
        $objectCreated = new PurchaseOrderDetail;
        $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($po->id);
        if($po->status == 13)
        {
            $status = "waiting-shipping-info";
        }
        else if($po->status == 27 || $po->status == 30)
        {
            $status = "Complete";
        }
        else
        {
            $status = "received-into-stock";
        }
        return response()->json(['success' => true, 'status' => $status]);

    }

    public function cancelPurchaseOrder(Request $request)
    {
        $pod_items = PurchaseOrderDetail::where('po_id',$request->id)->where('is_billed','Product')->whereNotNull('product_id')->get();
        foreach($pod_items as $pod_item)
        {
            if($pod_item->order_id != NULL && $pod_item->order_product_id != NULL)
            {
                if($pod_item->getOrder->primary_status == 3)
                {
                    OrderProduct::where('id',$pod_item->order_product_id)->where('product_id',$pod_item->product_id)->update(['status' => $pod_item->getOrder->status]);
                    // Order::where('id',$pod_item->order_id)->update(['status'=>7]);
                }
                else
                {
                    OrderProduct::where('id',$pod_item->order_product_id)->where('product_id',$pod_item->product_id)->update(['status'=>7]);
                    Order::where('id',$pod_item->order_id)->update(['status'=>7]);
                }
            }
        }

        PurchaseOrder::where('id',$request->id)->delete();
        PurchaseOrderDetail::where('po_id',$request->id)->delete();
        PurchaseOrderNote::where('po_id',$request->id)->delete();
        PurchaseOrderDocument::where('po_id',$request->id)->delete();
        PurchaseOrderStatusHistory::where('po_id',$request->id)->delete();
        PurchaseOrdersHistory::where('po_id',$request->id)->delete();

        return response()->json(['success' => true, 'status' =>'Deleted']);

    }

    public function paymentTermSaveInDpo(Request $request)
    {
        $draft_po = DraftPurchaseOrder::find($request->draft_po_id);
        $draft_po->payment_terms_id = $request->payment_terms_id;
        if($draft_po->invoice_date != null)
        {
            $getCreditTerm = PaymentTerm::find($request->payment_terms_id);
                $creditTerm = $getCreditTerm->title;
                $int = intval(preg_replace('/[^0-9]+/', '', $creditTerm), 10);

                if($creditTerm == "COD") // today data if COD
                {
                    $payment_due_date = $draft_po->invoice_date;
                }
                $needle = "EOM";
                if(strpos($creditTerm,$needle) !== false)
                {
                    // $currentMonthDays = date('t');                              // getting current month days
                    // $getRemainingDays = date('t') - date('j');                  // getting remaining current month days
                    $trdate = $draft_po->invoice_date;
                    $getDayOnly = date('d', strtotime($trdate));
                    $extractMY = new DateTime($trdate);

                    $daysOfMonth = cal_days_in_month(CAL_GREGORIAN, (int)$extractMY->format('m'), $extractMY->format('Y') );
                    $subtractDays = $daysOfMonth - $getDayOnly;

                    $days = $int + $subtractDays;
                    $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                    $newdate = date("Y-m-d",$newdate);
                    $payment_due_date = $newdate;
                }
                else
                {
                    $days = $int;
                    $trdate = $draft_po->invoice_date;
                    $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                    $newdate = date("Y-m-d",$newdate);
                    $payment_due_date = $newdate;
                }
                // dd($payment_due_date);

                $draft_po->payment_due_date = $payment_due_date;
            }

        $draft_po->save();

        return response()->json([
            'success' => true,
            'payment_due_date' => $draft_po->payment_due_date
        ]);
    }

    public function paymentTermSaveInPo(Request $request)
    {
        return PODetailCRUDHelper::paymentTermSaveInPo($request);
    }

    public function doActionDraftTd(Request $request)
    {
        return TransferDocumentHelper::doActionDraftTd($request);
    }

    public function doActionDraftPo(Request $request)
    {
        return DraftPOInsertUpdateHelper::doActionDraftPo($request);
    }

    public function exportToPDF(Request $request, $id)
    {
        $price_checked = $request->show_price_input;
        $poNote = PurchaseOrderNote::where('po_id',$id)->first();
        $pf_logo = $request->pf_logo;
        if($pf_logo == 1)
        {
            $pf = Company::where('tax_id' , '0105561152253')->where('billing_state','3528')->first();
        }
        else
        {
            $pf = null;
        }
        $purchaseOrder = PurchaseOrder::with('PoSupplier')->where('id',$id)->first();
        $getPoDetail = PurchaseOrderDetail::where('purchase_order_details.po_id',$id)->where('purchase_order_details.quantity','!=',0)->where('purchase_order_details.is_billed','Product')->whereNotNull('purchase_order_details.quantity')->select('purchase_order_details.*');

        $getPoDetail = PurchaseOrderDetail::PurchaseOrderDetailSorting($request, $getPoDetail);
        $getPoDetail = $getPoDetail->get()->groupBy('product_id');
        $getPoDetailForNote = PurchaseOrderDetail::where('po_id',$id)->where('quantity','!=',0)->where('is_billed','Product')->whereNotNull('quantity')->orderBy('product_id','ASC')->get();
        $createDate = Carbon::parse($purchaseOrder->created_at)->format('d/m/Y');
        $getPrintBlade = Status::select('print_1')->where('id',4)->first();
        //to check purchasing vat is enabled or not
        $hidden_columns_by_admin = [];
        $quotation_config   = QuotationConfig::where('section','purchase_order')->first();
        $hide_columns = $quotation_config->show_columns;
        if($quotation_config->show_columns != null)
        {
            $hidden_columns = json_decode($hide_columns);
            $hidden_columns = implode (",", $hidden_columns);
            $hidden_columns_by_admin = explode (",", $hidden_columns);
        }

        $system_config = Configuration::select('server')->first();
        $pdf = PDF::loadView('users.purchase-order.'.$getPrintBlade->print_1.'',compact('getPoDetail','purchaseOrder','price_checked','createDate','poNote','pf','getPoDetailForNote','hidden_columns_by_admin', 'system_config'));

        // making pdf name starts
        $makePdfName='Purchase Order-'.$purchaseOrder->ref_id.'';
        // making pdf name ends

        return $pdf->download($makePdfName.'.pdf');

    }

    public function getPoInOutBalance(Request $request)
    {
        $pod = PurchaseOrderDetail::where('warehouse_id',$request->warehouse_id)->where('product_id',$request->product_id)->get();

        return response()->json(['success' => true]);
    }

    public function getPurchaseOrderHistory(Request $request)
    {
      // dd($request->order_id);
      $query = PurchaseOrdersHistory::with('user','getOrder','product')->where('type','PO')->where('po_id',$request->order_id)->orderBy('id','DESC');

       return Datatables::of($query)
         ->addColumn('user_name',function($item){
              return @$item->user != null ? $item->user->name : '--';
          })
         ->addColumn('user_name',function($item){
              return @$item->user != null ? $item->user->name : '--';
          })

         ->addColumn('item',function($item){

              if($item->reference_number != null){
                 if($item->reference_number != 'Billed Item'){
                     // $pro_count = Product::withOut('sellingUnits')->where('refrence_code',$item->reference_number)->first();
                    if($item->product != null){
                        $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'"  ><b>'.$item->reference_number.'<b></a>';
                    }else{
                        $html_string = $item->reference_number;
                    }
                 }else{
                    $html_string = $item->reference_number;
                 }
             }else{
                $html_string = '--';
             }
             return $html_string;
          })

         ->addColumn('order_no',function($item){
            if($item->order_id != null)
            {


                if($item->getOrder->in_status_prefix !== null && $item->getOrder->in_ref_prefix !== null && $item->getOrder->in_ref_id !== null )
                {
                    $ref_no = @$item->getOrder->in_status_prefix.'-'.$item->getOrder->in_ref_prefix.$item->getOrder->in_ref_id;
                }
                elseif($item->getOrder->status_prefix !== null && $item->getOrder->ref_prefix !== null && $item->getOrder->ref_id !== null )
                {
                    $ref_no = @$item->getOrder->status_prefix.'-'.$item->getOrder->ref_prefix.$item->getOrder->ref_id;
                }
                else
                {
                    $ref_no = @$item->getOrder->customer->primary_sale_person->get_warehouse->order_short_code.@$item->getOrder->customer->CustomerCategory->short_code.@$item->getOrder->ref_id;
                }

              if(@$item->getOrder->primary_status == 3)
              {
                $link = 'get-completed-invoices-details';
              }
              elseif(@$item->getOrder->primary_status == 17)
              {
                $link = 'get-cancelled-order-detail';
              }
              else
              {
                $link = 'get-completed-draft-invoices';
              }
                return $title = '<a target="_blank" href="'.route($link, ['id' => $item->getOrder->id]).'" title="View Detail" class=""><b>'.$ref_no .'</b></a>';

            }
            else
            {
                return '--';
            }
          })

         ->addColumn('column_name',function($item){
              return @$item->column_name != null ? $item->column_name : '--';
          })

         ->addColumn('old_value',function($item){
              return @$item->old_value != null ? $item->old_value : '--';
          })

         ->addColumn('new_value',function($item){
          if(@$item->column_name == 'selling_unit'){
              return @$item->new_value != null ? $item->units->title : '--';

          }else if(@$item->column_name == 'Supply From'){
            return @$item->new_value != null ? $item->from_warehouse->warehouse_title : '--';
          }
          else{

              return @$item->new_value != null ? $item->new_value : '--';
          }
          })
           ->addColumn('created_at',function($item){
              return @$item->created_at != null ? $item->created_at->format('d/m/Y H:i:s') : '--';
          })

            ->rawColumns(['user_name','item','column_name','old_value','new_value','created_at','order_no'])
            ->make(true);

    }

    public function getPurchaseOrderStatusHistory(Request $request)
    {
      $query = PurchaseOrderStatusHistory::where('po_id',$request->order_id)->orderBy('id','ASC');

       return Datatables::of($query)
        ->addColumn('user_name',function($item){
          return @$item->user != null ? $item->user->name : '--';
        })

        ->addColumn('created_at',function($item){
          return @$item->created_at != null ? $item->created_at->format('d/m/Y H:i:s') : '--';
        })

        ->addColumn('status',function($item){
          return @$item->status != null ? $item->status : '--';
        })

        ->addColumn('new_status',function($item){
          return @$item->new_status != null ? $item->new_status : '--';
        })

        ->rawColumns(['user_name','status','new_status','created_at'])
        ->make(true);

    }

    public function deleteDraftPo(Request $request)
    {
        // dd($request->all());
        foreach($request->quotations as $quot){
        $order = DraftPurchaseOrder::find($quot);

        $order_prod = DraftPurchaseOrderDetail::where('po_id',$order->id)->get();
        $order_attachments = DraftPurchaseOrderDocument::where('po_id',$order->id)->get();
        $order_notes = DraftPurchaseOrderNote::where('po_id',$order->id)->get();


        $reserved = TransferDocumentReservedQuantity::where('draft_po_id', $order->id)->get();

        foreach ($reserved as $item) {
            $stock_m_out = StockManagementOut::find($item->stock_id);
            $stock_m_out->available_stock += $item->reserved_quantity;
            $stock_m_out->save();
            $item->delete();
        }

        $order_notes->each->delete();
        $order_attachments->each->delete();

          foreach ($order_prod as $draf_quot_prod)
          {
              $draf_quot_prod->delete();
          }

          $order->delete();
        }
        return response()->json(['success' => true]);

    }

    public function confirmTransferDocument(Request $request)
    {
        DB::beginTransaction();
        $response = TransferDocumentHelper::confirmTransferDocument($request);
        $result = json_decode($response->getContent());
        if ($result->success) {
            DB::commit();
        }
        else{
            DB::rollBack();
        }
        return $response;

        // $total_import_tax_book_price = null;
        // $total_vat_actual_price = null;
        // $confirm_date = date("Y-m-d");
        // $po = PurchaseOrder::with('PurchaseOrderDetail')->find($request->id);

        // if($po->status == 21 || $po->status == '21')
        // {
        //   $errorMsg =  'This Transfer Document is already confirmed !!!';
        //   $status = "transfer-document-dashboard";
        //   return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'status' => $status]);
        // }

        // $po_detail = PurchaseOrderDetail::where('po_id',$request->id)->get();
        // if($po_detail->count() > 0)
        // {
        //   foreach ($po_detail as $value)
        //   {
        //     if($value->quantity == null || $value->quantity == 0)
        //     {
        //       $errorMsg =  'Quantity cannot be Null or Zero, please enter the quantity of the added items';
        //       return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        //     }

        //     if($value->get_td_reserved->count() > 0)
        //     {
        //         $total_res = $value->get_td_reserved()->sum('reserved_quantity');
        //         $quant = $value->quantity;

        //         if($total_res != $quant)
        //         {
        //             return response()->json(['res_error' => true,'item' => $value->product->refrence_code]);
        //         }
        //     }
        //     // else{
        //     //     //Transfer Documnet Reserve Quantity
        //     //     $stock_m_outs = StockManagementOut::where('warehouse_id', $po->from_warehouse_id)->where('product_id', $value->product_id)->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
        //     //     $res = null;
        //     //     foreach ($stock_m_outs as $stock_m_out){
        //     //         $quantity_out = $value->quantity;
        //     //         $res = PurchaseOrderDetail::reserveQtyForTD($res != null ? $res : $quantity_out, $stock_m_out, $value);
        //     //         if($res == 0){
        //     //             break;
        //     //         }
        //     //     }
        //     // }

        //     //checking the available qty of each product reserve qty
        //     // foreach ($value->get_td_reserved as $transfer)
        //     // {
        //     //     if ($transfer->stock_m_out && $transfer->reserved_quantity > $transfer->stock_m_out->available_stock) {
        //     //         $errorMsg =  'Available Stock is less then the reserved qty for '. $value->product->refrence_code;
        //     //         return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        //     //     }
        //     //     else if($transfer->inbound_pod && $transfer->reserved_quantity > $transfer->inbound_pod->quantity) {
        //     //         $errorMsg =  'Available QTy is less then the reserved qty in Inbound PO for '. $value->product->refrence_code;
        //     //         return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        //     //     }
        //     // }
        //     // end
        //   }
        // }

        // $po->confirm_date = $confirm_date;
        // $po->status = 21;
        // $po->save();

        // // PO status history maintaining
        // if(@$has_warehouse_account == 1)
        // {
        //     $page_status = Status::select('title')->whereIn('id',[20,22])->pluck('title')->toArray();
        // }
        // else
        // {
        //     $page_status = Status::select('title')->whereIn('id',[20,21])->pluck('title')->toArray();
        // }
        // $poStatusHistory = new PurchaseOrderStatusHistory;
        // $poStatusHistory->user_id    = Auth::user()->id;
        // $poStatusHistory->po_id      = $po->id;
        // $poStatusHistory->status     = $page_status[0];
        // $poStatusHistory->new_status = $page_status[1];
        // $poStatusHistory->save();

        // //  creating group of generated transfer document
        // $total_quantity              = null;
        // $total_price                 = null;
        // $total_import_tax_book_price = null;
        // $total_vat_actual_price      = null;
        // $total_gross_weight          = null;
        // $po_group                    = new PoGroup;

        // // generating ref #
        // $year2  = Carbon::now()->year;
        // $month2 = Carbon::now()->month;

        // $year2  = substr($year2, -2);
        // $month2 = sprintf("%02d", $month2);
        // $date  = $year2.$month2;

        // $c_p_ref2 = PoGroup::where('ref_id','LIKE',"$date%")->whereNotNull('from_warehouse_id')->orderby('id','DESC')->first();
        // $str2 = @$c_p_ref2->ref_id;
        // $onlyIncrementGet2 = substr($str2, 4);
        // if($str2 == NULL)
        // {
        //   $onlyIncrementGet2 = 0;
        // }
        // $system_gen_no2 = $date.str_pad(@$onlyIncrementGet2 + 1, STR_PAD_LEFT);

        // $po_group->ref_id                         = $system_gen_no2;
        // $po_group->bill_of_landing_or_airway_bill = '';
        // $po_group->bill_of_lading                 = '';
        // $po_group->airway_bill                    = '';
        // $po_group->courier                        = '';
        // $po_group->target_receive_date            = $po->target_receive_date;
        // $po_group->warehouse_id                   = $po->to_warehouse_id;
        // $po_group->from_warehouse_id              = $po->from_warehouse_id;
        // $po_group->save();

        // $po_group_detail                    = new PoGroupDetail;
        // $po_group_detail->po_group_id       = $po_group->id;
        // $po_group_detail->purchase_order_id = $po->id;
        // $po_group_detail->save();

        // // $purchase_order = PurchaseOrder::find($po->id);
        // foreach ($po->PurchaseOrderDetail as $p_o_d)
        // {
        //     $total_quantity +=  $p_o_d->quantity;
        //     if($p_o_d->order_product_id != null)
        //     {
        //         $order_product = $p_o_d->order_product;
        //         $order         = $order_product->get_order;
        //         if($order->primary_status !== 3 && $order->primary_status !== 17)
        //         {
        //             $order_product->status = 9;
        //             $order_product->save();

        //             $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('status','!=',9)->count();
        //             if($order_products_status_count == 0)
        //             {
        //                 $order->status = 9;
        //                 $order->save();
        //                 $order_history             = new OrderStatusHistory;
        //                 $order_history->user_id    = Auth::user()->id;
        //                 $order_history->order_id   = @$order->id;
        //                 $order_history->status     = 'DI(Purchasing)';
        //                 $order_history->new_status = 'DI(Importing)';
        //                 $order_history->save();
        //             }
        //         }
        //     }

        //     if($po != null)
        //     {
        //         $old_qty = $p_o_d->get_td_reserved()->whereNotNull('stock_id')->sum('old_qty');
        //         $new_reserve = $p_o_d->get_td_reserved()->whereNotNull('stock_id')->sum('reserved_quantity');
        //         if ($new_reserve != $old_qty) {
        //             $total_reserve = $new_reserve - $old_qty;
        //             $new_his = new QuantityReservedHistory;
        //             // $re      = $new_his->updateTDReservedQuantity($po,$p_o_d,$p_o_d->quantity,$p_o_d->quantity,'Reserved Quantity by confirming TD','add');
        //             $re      = $new_his->updateTDReservedQuantity($po,$p_o_d,$total_reserve,$total_reserve,'Reserved Quantity by confirming TD','add');
        //         }

        //     //   DB::beginTransaction();
        //     //   try
        //     //   {
        //         // DB::commit();
        //     //   }
        //     //   catch(\Excepion $e)
        //     //   {
        //     //     DB::rollBack();
        //     //   }
        //     }
        // }

        // $total_import_tax_book_price += $po->total_import_tax_book_price;
        // $total_vat_actual_price += $po->total_vat_actual_price;
        // $total_gross_weight += $po->total_gross_weight;
        // $po->status = 21;
        // $po->save();
        // // dd($purchase_order);

        // $po_group->total_quantity              = $total_quantity;
        // $po_group->po_group_import_tax_book    = $total_import_tax_book_price;
        // $po_group->po_group_vat_actual         = $total_vat_actual_price;
        // $po_group->po_group_total_gross_weight = $total_gross_weight;
        // $po_group->save();

        // $group_status_history              = new PoGroupStatusHistory;
        // $group_status_history->user_id     = Auth::user()->id;
        // $group_status_history->po_group_id = @$po_group->id;
        // $group_status_history->status      = 'Created';
        // $group_status_history->new_status  = 'Open Product Receiving Queue';
        // $group_status_history->save();

        // $status = "dfs";
        // session(['td_status' => 21]);
        // return response()->json(['success' => true, 'status' => $status]);

    }

    public function checkExistingPos(Request $request)
    {
        $total = 0;
        $vat_amount_total = 0;
        $total_gross_weight = 0;
        $total_item_product_quantities = 0;
        $total_import_tax_book = 0;
        $total_import_tax_book_price = 0;
        $total_vat_act = 0;
        $total_vat_act_price = 0;
        $supplyFromSupplier  = array();
        $supplyFromWarehouse = array();
        $supplyToWarehouse   = array();
        $targetShipDate      = array();
        if($request->selected_ids[0] != null)
        {
            for($i=0; $i<sizeof($request->selected_ids); $i++)
            {
                $getOrderproductData = OrderProduct::find($request->selected_ids[$i]);
                if($getOrderproductData->supplier_id == null && $getOrderproductData->from_warehouse_id == null)
                {
                    $errorMsg = "Select Supply From First To Combine To PO !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

                if($getOrderproductData->supplier_id != null)
                {
                    array_push($supplyFromSupplier, $getOrderproductData->supplier_id);
                }

                if($getOrderproductData->from_warehouse_id != null)
                {
                    array_push($supplyFromWarehouse, $getOrderproductData->from_warehouse_id);
                }

                if($getOrderproductData->warehouse_id != null)
                {
                    array_push($supplyToWarehouse, $getOrderproductData->warehouse_id);
                }
                else
                {
                    $errorMsg = "Selected Items Supply To Must Be Selected !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

                $id = $request->selected_ids[$i];

                $check_draft = Order::whereHas('order_products',function($q) use ($id){
                    $q->where('id',$id);
                })->where('primary_status',1)->first();

                if($check_draft != null)
                {
                    $errorMsg = "The Selected Product is in QUOTATION Stage, Can't generate PO for it !!!";
                    return response()->json(['success' => false, 'page_reload' => true, 'errorMsg' => $errorMsg]);
                }

                $check_invoice = Order::whereHas('order_products',function($q) use ($id){
                    $q->where('id',$id);
                })->where('primary_status',3)->first();

                if($check_invoice != null)
                {
                    $errorMsg = "The Selected Product is already converted to INVOICE, Can't generate PO for it !!!";
                    return response()->json(['success' => false, 'page_reload' => true, 'errorMsg' => $errorMsg]);
                }

                $check_cancelled_invoice = Order::whereHas('order_products',function($q) use ($id){
                    $q->where('id',$id);
                })->where('primary_status',17)->first();

                if($check_cancelled_invoice != null)
                {
                    $errorMsg = "The Selected Product is in CANCELLED status, Can't generate PO for it !!!";
                    return response()->json(['success' => false, 'page_reload' => true, 'errorMsg' => $errorMsg]);
                }

                array_push($targetShipDate, $getOrderproductData->get_order->target_ship_date);
            }

            if (empty($supplyFromSupplier) && empty($supplyFromWarehouse) )
            {
                $errorMsg = "Selected items have empty Supply From, Must select Supply From of seleced items.";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

            if (empty($supplyToWarehouse))
            {
                $errorMsg = "Selected items have empty Supply To, Must select Supply To of seleced items.";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

            if (!empty($supplyFromSupplier) && !empty($supplyFromWarehouse) )
            {
                $errorMsg = "Supply From Must Be Same For Selected Items.";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

            if (!empty($supplyFromSupplier) && empty($supplyFromWarehouse) )
            {
                if (count(array_unique($supplyFromSupplier)) === 1)
                {
                    // do nothing
                }
                else
                {
                    $errorMsg = "Must select same Supply From (Supplier) for seleced items.";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }

            if (!empty($supplyFromWarehouse) && empty($supplyFromSupplier) )
            {
                if (count(array_unique($supplyFromWarehouse)) === 1)
                {
                    // do nothing
                }
                else
                {
                    $errorMsg = "Must select same Supply From (Warehouse) for seleced items.";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }

            if (!empty($supplyToWarehouse))
            {
                if (count(array_unique($supplyToWarehouse)) === 1)
                {
                    // do nothing
                }
                else
                {
                    $errorMsg = "Supply To Must Be Same For Selected Items.";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }
            if($request->target_ship_date_status==1 && $request->target_ship_date_required==1)
            {
                if (in_array(NULL, $targetShipDate, true))
                {
                    $errorMsg = "Missing Target Ship Date Of Selected Items !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }

            }
           if($request->target_ship_date_status==1)
           {
                if (!empty($targetShipDate))
                {
                    if (count(array_unique($targetShipDate)) === 1)
                    {
                        // do nothing
                    }
                    else
                    {
                        $errorMsg = "Selected Items Target Ship Date Must Be Same As Selected PO !!!";
                        return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    }

                }
           }
           if($request->target_ship_date_status==1 && $request->target_ship_date_required==1)
            {
                if (empty($targetShipDate))
                {
                    $errorMsg = "Target Ship Date Required Of Selected Items !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }

            $getPoData = PurchaseOrder::find($request->po_id);

            if(!empty($supplyFromSupplier) && empty($supplyFromWarehouse) && !empty($supplyToWarehouse) )
            {
                if($getPoData != null && $getPoData->supplier_id == $supplyFromSupplier[0] && $getPoData->to_warehouse_id == $supplyToWarehouse[0] && $getPoData->target_receive_date  == $targetShipDate[0])
                {
                    for($i=0; $i<sizeof($request->selected_ids); $i++)
                    {
                        $gettingOrderProductDataById = OrderProduct::with('product')->where('id',$request->selected_ids[$i])->first();

                        $supplier_id = $getPoData->supplier_id;
                        $getCustomerByOrdInvId = Order::where('id',$gettingOrderProductDataById->order_id)->first();

                        $purchaseOrderDetail                   = new PurchaseOrderDetail;
                        $purchaseOrderDetail->po_id            = $request->po_id;
                        $purchaseOrderDetail->order_id         = $gettingOrderProductDataById->order_id;
                        $purchaseOrderDetail->order_product_id = $gettingOrderProductDataById->id;
                        $purchaseOrderDetail->product_id       = $gettingOrderProductDataById->product_id;
                        $purchaseOrderDetail->customer_id      = $getCustomerByOrdInvId->customer_id;

                        $gettingProdSuppData = SupplierProducts::where('product_id',$gettingOrderProductDataById->product_id)->where('supplier_id',$supplier_id)->first();

                        $purchaseOrderDetail->pod_import_tax_book  = $gettingOrderProductDataById->product->import_tax_book;
                        $purchaseOrderDetail->pod_vat_actual       = $gettingOrderProductDataById->product->vat;
                        $purchaseOrderDetail->pod_unit_price       = $gettingProdSuppData->buying_price;

                        $product_vat = $gettingOrderProductDataById->product->vat;

                        $purchaseOrderDetail->pod_unit_price_with_vat = ($gettingProdSuppData->buying_price * $product_vat) + $gettingProdSuppData->buying_price;

                        $purchaseOrderDetail->pod_gross_weight     = $gettingProdSuppData->gross_weight;

                        // supplier packagign and billed unit per package add 24 Feb, 2020
                        $quantity_inv = $gettingOrderProductDataById->quantity*$gettingOrderProductDataById->product->unit_conversion_rate;
                        $decimal_places = $purchaseOrderDetail->product->units->decimal_places;
                        if($decimal_places == 0)
                        {
                            $quantity_inv = round($quantity_inv,0);
                        }
                        elseif($decimal_places == 1)
                        {
                            $quantity_inv = round($quantity_inv,1);
                        }
                        elseif($decimal_places == 2)
                        {
                            $quantity_inv = round($quantity_inv,2);
                        }
                        elseif($decimal_places == 3)
                        {
                            $quantity_inv = round($quantity_inv,3);
                        }
                        else
                        {
                            $quantity_inv = round($quantity_inv,4);
                        }

                        $purchaseOrderDetail->supplier_packaging      = $gettingProdSuppData->supplier_packaging;
                        $purchaseOrderDetail->quantity                = $quantity_inv;

                        if($gettingProdSuppData->billed_unit != NULL && $gettingProdSuppData->billed_unit != 0)
                        {
                            $purchaseOrderDetail->desired_qty         = ($quantity_inv / $gettingProdSuppData->billed_unit);
                        }
                        else
                        {
                            $gettingProdSuppData->billed_unit = 1;
                            $gettingProdSuppData->save();
                            $purchaseOrderDetail->desired_qty         = ($quantity_inv / 1);
                        }
                        $purchaseOrderDetail->billed_unit_per_package = $gettingProdSuppData->billed_unit;

                        $purchaseOrderDetail->pod_total_gross_weight  = ($quantity_inv * $gettingProdSuppData->gross_weight);
                        $purchaseOrderDetail->pod_total_unit_price    = ($quantity_inv * $gettingProdSuppData->buying_price);
                        $purchaseOrderDetail->pod_total_unit_price_with_vat    = ($quantity_inv * $purchaseOrderDetail->pod_unit_price_with_vat);

                        $calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_import_tax_book / 100);
                        $purchaseOrderDetail->pod_import_tax_book_price = $calculations;

                        $vat_calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_vat_actual / 100);
                        $purchaseOrderDetail->pod_vat_actual_price = $vat_calculations;
                        $purchaseOrderDetail->pod_vat_actual_total_price = $quantity_inv * $vat_calculations;

                        $purchaseOrderDetail->good_type     = $gettingOrderProductDataById->product->type_id;
                        $purchaseOrderDetail->temperature_c = $gettingOrderProductDataById->product->product_temprature_c;

                        $purchaseOrderDetail->warehouse_id  = $gettingOrderProductDataById->warehouse_id;
                        $purchaseOrderDetail->remarks       = @$gettingOrderProductDataById->remarks;

                        /*vat calculations*/
                        $vat_calculations = $purchaseOrderDetail->calculateVat($gettingProdSuppData->buying_price, @$gettingOrderProductDataById->product->vat);
                        $purchaseOrderDetail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                        $purchaseOrderDetail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                        /*convert val to thb's*/
                        $converted_vals = $purchaseOrderDetail->calculateVatToSystemCurrency($request->po_id, $vat_calculations['vat_amount']);
                        $purchaseOrderDetail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                        $purchaseOrderDetail->save();

                        $total_item_product_quantities = $quantity_inv + $total_item_product_quantities;
                        // order product status change
                        $gettingOrderProductDataById->status = 8;

                        $gettingOrderProductDataById->save();

                        // changing orders status
                        $order_products_status_count = OrderProduct::where('order_id',$getCustomerByOrdInvId->id)->where('is_billed','=','Product')->where('status','<',8)->count();
                        if($order_products_status_count == 0)
                        {
                            $getCustomerByOrdInvId->status = 8;
                            $getCustomerByOrdInvId->save();
                            $order_history = new OrderStatusHistory;
                            $order_history->user_id = Auth::user()->id;
                            $order_history->order_id = @$getCustomerByOrdInvId->id;
                            $order_history->status = 'DI(Waiting Gen PO)';
                            $order_history->new_status = 'DI(Purchasing)';
                            $order_history->save();
                        }

                        $order_history = new PurchaseOrdersHistory;
                        $order_history->user_id = Auth::user()->id;
                        $order_history->reference_number = $gettingOrderProductDataById->product->refrence_code;
                        $order_history->order_id = $gettingOrderProductDataById->order_id;
                        $order_history->order_product_id = $gettingOrderProductDataById->id;
                        $order_history->old_value = "Purchase List";
                        $order_history->new_value = "Purchase Order";
                        $order_history->po_id = $request->po_id;
                        $order_history->save();

                    }

                    $purchase_order = PurchaseOrder::find($request->po_id);
                    $supplier_conv_rate_thb = $purchase_order->PoSupplier->getCurrency->conversion_rate;

                    // $query     = PurchaseOrderDetail::where('po_id',$request->po_id)->get();
                    // foreach ($query as  $value)
                    // {
                    //     $supplier_conv_rate_thb = $supplier_conv_rate_thb != 0 ? $supplier_conv_rate_thb : 1;

                    //     $value->currency_conversion_rate  = $supplier_conv_rate_thb;
                    //     $value->unit_price_in_thb         = $value->pod_unit_price/$supplier_conv_rate_thb;
                    //     $value->unit_price_with_vat_in_thb = $value->pod_unit_price_with_vat/$supplier_conv_rate_thb;

                    //     $value->total_unit_price_in_thb   = $value->pod_total_unit_price/$supplier_conv_rate_thb;
                    //     $value->total_unit_price_with_vat_in_thb   = $value->pod_total_unit_price_with_vat/$supplier_conv_rate_thb;

                    //     $value->pod_vat_actual_price_in_thb   = $value->pod_vat_actual_price/$supplier_conv_rate_thb;
                    //     $value->pod_vat_actual_total_price_in_thb   = $value->pod_vat_actual_total_price/$supplier_conv_rate_thb;

                    //     $value->pod_import_tax_book_price = ($value->pod_import_tax_book/100)*$value->total_unit_price_in_thb;
                    //     $total_import_tax_book_price      += $value->pod_import_tax_book_price;
                    //     $total_vat_act_price              += $value->pod_vat_actual_price;
                    //     $value->save();
                    //     // new up
                    //     $total += $value->quantity * $value->pod_unit_price;

                    //     $total_gross_weight = ($value->quantity * $value->pod_gross_weight) + $total_gross_weight;

                    //     $total_item_product_quantities = $total_item_product_quantities + $value->quantity;

                    //     $total_import_tax_book = $total_import_tax_book + $value->pod_import_tax_book;
                    //     $total_vat_act = $total_vat_act + $value->pod_vat_actual;

                    //     // $total_import_tax_book_price = $total_import_tax_book_price + $value->pod_import_tax_book_price;

                    //     $vat_amount_total += $gettingProdSuppData->buying_price * $product_vat;
                    // }

                    //     $purchase_order->total                       = $total;
                    //     $purchase_order->vat_amount_total            = $vat_amount_total;
                    //     $purchase_order->total_with_vat              = $total + $vat_amount_total;

                    //     $purchase_order->total_in_thb                = $purchase_order->total/$supplier_conv_rate_thb;
                    //     $purchase_order->total_gross_weight          = $total_gross_weight;
                    //     $purchase_order->total_import_tax_book       = $total_import_tax_book;
                    //     $purchase_order->total_import_tax_book_price = $total_import_tax_book_price;
                    //     $purchase_order->total_quantity              = $total_item_product_quantities;
                    //     $purchase_order->total_vat_actual            = $total_vat_act;
                    //     $purchase_order->total_vat_actual_price      = $total_vat_act_price;
                    //     $purchase_order->save();
                    /*calulation through a function*/
                    $objectCreated = new PurchaseOrderDetail;
                    $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

                        $errorMsg = "Item(s) Added In PO Successfully !!!";
                        return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
                }
                else
                {
                    $errorMsg = "You Cannot Add These Items Into This PO, Becasue Selected Items Have Differnt Supply From/To, Target Ship Date. !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }
            elseif(!empty($supplyFromWarehouse) && empty($supplyFromSupplier) && !empty($supplyToWarehouse) )
            {
                if($getPoData != null && $getPoData->from_warehouse_id == $supplyFromWarehouse[0] && $getPoData->to_warehouse_id == $supplyToWarehouse[0]  && $getPoData->target_receive_date  == $targetShipDate[0])
                {
                    for($i=0; $i<sizeof($request->selected_ids); $i++)
                    {
                        $gettingOrderProductDataById = OrderProduct::with('product')->where('id',$request->selected_ids[$i])->first();

                        $supplier_id = $gettingOrderProductDataById->product->supplier_id ;
                        $getCustomerByOrdInvId = Order::where('id',$gettingOrderProductDataById->order_id)->first();

                        $purchaseOrderDetail                   = new PurchaseOrderDetail;
                        $purchaseOrderDetail->po_id            = $request->po_id;
                        $purchaseOrderDetail->order_id         = $gettingOrderProductDataById->order_id;
                        $purchaseOrderDetail->order_product_id = $gettingOrderProductDataById->id;
                        $purchaseOrderDetail->product_id       = $gettingOrderProductDataById->product_id;
                        $purchaseOrderDetail->customer_id      = $getCustomerByOrdInvId->customer_id;

                        $gettingProdSuppData = SupplierProducts::where('product_id',$gettingOrderProductDataById->product_id)->where('supplier_id',$supplier_id)->first();

                        $purchaseOrderDetail->pod_import_tax_book  = $gettingOrderProductDataById->product->import_tax_book;
                        $purchaseOrderDetail->pod_vat_actual       = $gettingOrderProductDataById->product->vat;
                        $purchaseOrderDetail->pod_unit_price       = $gettingProdSuppData->buying_price;

                        $product_vat = $gettingOrderProductDataById->product->vat;

                        $purchaseOrderDetail->pod_unit_price_with_vat = ($gettingProdSuppData->buying_price * $product_vat) + $gettingProdSuppData->buying_price;

                        $purchaseOrderDetail->pod_gross_weight     = $gettingProdSuppData->gross_weight;


                        // supplier packagign and billed unit per package add 24 Feb, 2020
                        $quantity_inv = $gettingOrderProductDataById->quantity*$gettingOrderProductDataById->product->unit_conversion_rate;
                        $decimal_places = $purchaseOrderDetail->product->units->decimal_places;
                        if($decimal_places == 0)
                        {
                            $quantity_inv = round($quantity_inv,0);
                        }
                        elseif($decimal_places == 1)
                        {
                            $quantity_inv = round($quantity_inv,1);
                        }
                        elseif($decimal_places == 2)
                        {
                            $quantity_inv = round($quantity_inv,2);
                        }
                        elseif($decimal_places == 3)
                        {
                            $quantity_inv = round($quantity_inv,3);
                        }
                        else
                        {
                            $quantity_inv = round($quantity_inv,4);
                        }

                        $purchaseOrderDetail->supplier_packaging      = $gettingProdSuppData->supplier_packaging;
                        $purchaseOrderDetail->quantity                = $quantity_inv;

                        if($gettingProdSuppData->billed_unit != NULL && $gettingProdSuppData->billed_unit != 0)
                        {
                            $purchaseOrderDetail->desired_qty         = ($quantity_inv / $gettingProdSuppData->billed_unit);
                        }
                        else
                        {
                            $gettingProdSuppData->billed_unit = 1;
                            $gettingProdSuppData->save();
                            $purchaseOrderDetail->desired_qty         = ($quantity_inv / 1);
                        }

                        $purchaseOrderDetail->billed_unit_per_package = $gettingProdSuppData->billed_unit;

                        $purchaseOrderDetail->pod_total_gross_weight  = ($quantity_inv * $gettingProdSuppData->gross_weight);
                        $purchaseOrderDetail->pod_total_unit_price    = ($quantity_inv * $gettingProdSuppData->buying_price);

                        $purchaseOrderDetail->pod_total_unit_price_with_vat    = ($quantity_inv * $purchaseOrderDetail->pod_unit_price_with_vat);


                        $calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_import_tax_book / 100);
                        $purchaseOrderDetail->pod_import_tax_book_price = $calculations;

                        $vat_calculations = $purchaseOrderDetail->total_unit_price_in_thb * ($purchaseOrderDetail->pod_vat_actual / 100);
                        $purchaseOrderDetail->pod_vat_actual_price = $vat_calculations;
                        $purchaseOrderDetail->pod_vat_actual_total_price = $quantity_inv * $vat_calculations;

                        $purchaseOrderDetail->good_type     = $gettingOrderProductDataById->product->type_id;
                        $purchaseOrderDetail->temperature_c = $gettingOrderProductDataById->product->product_temprature_c;

                        $purchaseOrderDetail->warehouse_id  = $gettingOrderProductDataById->warehouse_id;
                        $purchaseOrderDetail->remarks       = @$gettingOrderProductDataById->remarks;

                        /*vat calculations*/
                        $vat_calculations = $purchaseOrderDetail->calculateVat($gettingProdSuppData->buying_price, @$gettingOrderProductDataById->product->vat);
                        $purchaseOrderDetail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                        $purchaseOrderDetail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                        /*convert val to thb's*/
                        $converted_vals = $purchaseOrderDetail->calculateVatToSystemCurrency($request->po_id, $vat_calculations['vat_amount']);
                        $purchaseOrderDetail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');

                        $purchaseOrderDetail->save();

                        $total_item_product_quantities = $quantity_inv + $total_item_product_quantities;
                        // order product status change
                        $gettingOrderProductDataById->status = 8;

                        $gettingOrderProductDataById->save();

                        // changing orders status
                        $order_products_status_count = OrderProduct::where('order_id',$getCustomerByOrdInvId->id)->where('is_billed','=','Product')->where('status','<',8)->count();
                        if($order_products_status_count == 0)
                        {
                            $getCustomerByOrdInvId->status = 8;
                            $getCustomerByOrdInvId->save();
                            $order_history = new OrderStatusHistory;
                            $order_history->user_id = Auth::user()->id;
                            $order_history->order_id = @$getCustomerByOrdInvId->id;
                            $order_history->status = 'DI(Waiting Gen PO)';
                            $order_history->new_status = 'DI(Purchasing)';
                            $order_history->save();
                        }

                        $order_history = new PurchaseOrdersHistory;
                        $order_history->user_id = Auth::user()->id;
                        $order_history->reference_number = $gettingOrderProductDataById->product->refrence_code;
                        $order_history->order_id = $gettingOrderProductDataById->order_id;
                        $order_history->order_product_id = $gettingOrderProductDataById->id;
                        $order_history->old_value = "Purchase List";
                        $order_history->new_value = "Purchase Order";
                        $order_history->po_id = $request->po_id;
                        $order_history->save();

                    }

                    // $query     = PurchaseOrderDetail::where('po_id',$request->po_id)->get();
                    // foreach ($query as  $value)
                    // {
                    //     $total += $value->quantity * $value->pod_unit_price;

                    //     $total_gross_weight = ($value->quantity * $value->pod_gross_weight) + $total_gross_weight;

                    //     $total_item_product_quantities = $total_item_product_quantities + $value->quantity;

                    //     $total_import_tax_book = $total_import_tax_book + $value->pod_import_tax_book;

                    //     $total_import_tax_book_price = $total_import_tax_book_price + $value->pod_import_tax_book_price;

                    //     $total_vat_act = $total_vat_act + $value->pod_vat_actual;

                    //     $total_vat_act_price = $total_vat_act_price + $value->pod_vat_actual_price;

                    //     $vat_amount_total += $gettingProdSuppData->buying_price * $product_vat;
                    // }

                    //     $purchase_order = PurchaseOrder::find($request->po_id);
                    //     $purchase_order->total = $total;
                    //     $purchase_order->vat_amount_total            = $vat_amount_total;
                    //     $purchase_order->total_with_vat              = $total + $vat_amount_total;
                    //     $purchase_order->total_gross_weight          = $total_gross_weight;
                    //     $purchase_order->total_import_tax_book       = $total_import_tax_book;
                    //     $purchase_order->total_import_tax_book_price = $total_import_tax_book_price;
                    //     $purchase_order->total_vat_actual            = $total_vat_act;
                    //     $purchase_order->total_vat_actual_price      = $total_vat_act_price;
                    //     $purchase_order->total_quantity              = $total_item_product_quantities;
                    //     $purchase_order->save();

                    /*calulation through a function*/
                    $objectCreated = new PurchaseOrderDetail;
                    $grandCalculations = $objectCreated->grandCalculationForPurchaseOrder($request->po_id);

                        $errorMsg = "Item(s) Added In PO Successfully !!!";
                        return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
                }
                else
                {
                    $errorMsg = "You Cannot Add These Items Into This PO, Becasue Selected Items Have Differnt Supply From/To, Target Ship Date. !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }
        }
    }

    public function addBilledItemInPo(Request $request)
    {
        $addBilledItem = new PurchaseOrderDetail;
        $addBilledItem->po_id = $request->id;
        $addBilledItem->is_billed = "Billed";
        $addBilledItem->created_by = Auth::user()->id;
        $addBilledItem->save();

        $order_history = new PurchaseOrdersHistory;
        $order_history->user_id = Auth::user()->id;
        $order_history->old_value = "";
        $order_history->new_value = "Billed Item";
        $order_history->po_id = $request->id;
        $order_history->save();

        return response()->json(['success' => true]);
    }

    public function addBilledItemInDpo(Request $request)
    {
        $addBilledItem = new DraftPurchaseOrderDetail;
        $addBilledItem->po_id = $request->id;
        $addBilledItem->is_billed = "Billed";
        $addBilledItem->created_by = Auth::user()->id;
        $addBilledItem->save();

        return response()->json(['success' => true]);
    }

    public function revertPoStatusToWc(Request $request)
    {
        $po_ids = explode(',', $request->selected_ids);

        if($po_ids[0] != null)
        {
            for($i=0; $i<sizeof($po_ids); $i++)
            {
                $purchase_order = PurchaseOrder::find($po_ids[$i]);
                $purchase_order->status = 12;
                $purchase_order->save();

                foreach ($purchase_order->PurchaseOrderDetail as $p_o_d)
                {
                    if($p_o_d->order_product_id != null)
                    {
                        if(@$p_o_d->order_product->get_order->primary_status != 3)
                        {
                            $p_o_d->order_product->status = 8;
                            $p_o_d->order_product->save();

                            $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('status','!=',8)->count();
                            if($order_products_status_count == 0)
                            {
                                $p_o_d->order_product->get_order->status = 8;
                                $p_o_d->order_product->get_order->save();
                            }
                        }
                    }
                }

                // PO status history maintaining
                $page_status = Status::select('title')->whereIn('id',[12,13])->pluck('title')->toArray();
                $poStatusHistory = new PurchaseOrderStatusHistory;
                $poStatusHistory->user_id    = Auth::user()->id;
                $poStatusHistory->po_id      = $purchase_order->id;
                $poStatusHistory->status     = $page_status[1];
                $poStatusHistory->new_status = $page_status[0];
                $poStatusHistory->save();
            }

            return response()->json(['success' => true, 'status' => "wc"]);
        }
    }

    public function removeFromExistingGroup(Request $request)
    {
        // DB::transaction(function () use ($request) {
        DB::beginTransaction();

        if(gettype($request->selected_ids) == 'string')
        {
            $po_ids = explode(' ', $request->selected_ids);
        }
        else
        {
            $po_ids = $request->selected_ids;
        }
        $po_warehouses = PurchaseOrder::whereIn('id' , $po_ids)->pluck('to_warehouse_id')->toArray();

        $total_quantity              = null;
        $total_price                 = null;
        $total_import_tax_book_price = null;
        $total_vat_actual_price      = null;
        $total_buying_price_in_thb   = null;
        $total_gross_weight          = null;
        $not_in_group = [];

        $reserved_qty_tds = '<ul>';
        $count_reserved = 0;
        foreach ($po_ids as $po_id)
        {
            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d)
            {
                foreach ($p_o_d->get_inbound_td_reserved as $transfer)
                {
                    if ($transfer->inbound_pod_id == $p_o_d->id)
                    {
                        $count_reserved++;

                        $reserved_qty_tds .= '<li>TD: '.$transfer->po->ref_id.', PO: '.$purchase_order->ref_id.', Product: '.$transfer->po_detail->product->refrence_code.'</li>';
                    }
                }
            }
        }
        $reserved_qty_tds .= '</ul>';
        if ($count_reserved > 0) {
            $errorMsg = 'Cannot revert PO(s) to shipping, Following TD(s) have reserved qty.';
            return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'reserved_qty_tds' => $reserved_qty_tds]);
        }

        foreach ($po_ids as $po_id)
        {
            $purchase_order = PurchaseOrder::find($po_id);
            if($purchase_order)
            {
                if($purchase_order->status < 14 || $purchase_order->status < '14' || $purchase_order->status == 15)
                {
                    array_push($not_in_group, $purchase_order->ref_id);
                }
            }
        }

        if(!empty($not_in_group))
        {
            $not_in_group = implode(', ', $not_in_group);
            $errorMsg = $not_in_group." These PO's already removed from a group or Received into stock !!!";
            DB::rollBack();
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }

        $group_cannot_be_deleted     = '';
        $already_converted           = false;

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);
            if($purchase_order->status > 14)
            {
                $group_cannot_be_deleted = ''.$purchase_order->ref_id.' ';
                $already_converted = true;
                // return;
            }
        }

        if($already_converted == true)
        {
            return response()->json(['already_converted' => true,'group_cannot_be_deleted' => $group_cannot_be_deleted]);
        }
        // dd($not_in_group);

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {
                $total_quantity +=  $p_o_d->quantity;
                if($p_o_d->order_product_id != null)
                {
                    $order_product = $p_o_d->order_product;
                    $order         = $order_product->get_order;
                    if($order->primary_status !== 3 && $order->primary_status !== 17)
                    {
                        $order_product->status = 8;
                        $order_product->save();

                        $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('status','<',8)->count();
                        if($order_products_status_count == 0)
                        {
                            $order->status = 8;
                            $order->save();
                            $order_history             = new OrderStatusHistory;
                            $order_history->user_id    = Auth::user()->id;
                            $order_history->order_id   = @$order->id;
                            $order_history->status     = 'DI(Importing)';
                            $order_history->new_status = 'DI(Purchasing)';
                            $order_history->save();
                        }
                    }
                }
            }

            $total_import_tax_book_price += $purchase_order->total_import_tax_book_price;
            $total_vat_actual_price      += $purchase_order->total_vat_actual_price;
            $total_gross_weight          += $purchase_order->total_gross_weight;
            $total_buying_price_in_thb   += $purchase_order->total_in_thb;
            $purchase_order->status      = 13;
            $purchase_order->save();

            // PO status history maintaining
            $page_status = Status::select('title')->whereIn('id',[13,14])->pluck('title')->toArray();

            $poStatusHistory = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id    = Auth::user()->id;
            $poStatusHistory->po_id      = $purchase_order->id;
            $poStatusHistory->status     = $page_status[1];
            $poStatusHistory->new_status = $page_status[0];
            $poStatusHistory->save();

            $po_group = PoGroup::find($purchase_order->po_group_id);

            $po_group->total_quantity              -= $total_quantity;
            $po_group->po_group_import_tax_book    -= floor($total_import_tax_book_price * 100) / 100;
            $po_group->po_group_vat_actual         -= floor($total_vat_actual_price * 100) / 100;
            $po_group->total_buying_price_in_thb   -= $total_buying_price_in_thb;
            $po_group->po_group_total_gross_weight -= $total_gross_weight;
            // if($po_group->po_group_detail->count() < 1)
            // {
            //     $po_group->is_cancel = 2;
            // }
            $po_group->save();
        }


        /*********************Here starts the new code for groups*************/
        $occurrence = null;
        $total_import_tax_book_percent = null;

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);

            $po_group_id = $purchase_order->po_group_id;

            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {

                $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group_id)->where('supplier_id',$purchase_order->supplier_id)->first();
                if($po_group_product != null)
                {
                    $po_group_product->quantity_ordered          -= @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv              -= $p_o_d->quantity;
                    $po_group_product->import_tax_book_price     -= $p_o_d->pod_import_tax_book_price;
                    $po_group_product->pogpd_vat_actual_percent  -= $p_o_d->pod_vat_actual_price;
                    $po_group_product->total_gross_weight        -= $p_o_d->pod_total_gross_weight;
                    $po_group_product->total_unit_price_in_thb   -= $p_o_d->unit_price_in_thb * $p_o_d->quantity;
                    $po_group_product->occurrence                -= 1;
                    $po_group_product->save();

                    if($po_group_product->occurrence == 0)
                    {
                        $po_group_product->delete();
                    }
                }
                else
                {

                }
            }

            $po_removed_history = new ProductReceivingHistory;

            $po_removed_history->po_group_id = $po_group_id;
            $po_removed_history->pod_id = null;
            $po_removed_history->term_key = $purchase_order->ref_id;
            $po_removed_history->old_value = 'Dispatched From Supplier';
            $po_removed_history->new_value = 'Shipping';
            $po_removed_history->updated_by = Auth::user()->id;

            @$po_removed_history->save();

            $po_group = PoGroup::where('id',$po_group_id)->first();

            $total_import_tax_book_price = 0;
            $total_vat_actual_price = 0;
            $po_group_details = $po_group->po_group_product_details;
            foreach ($po_group_details as $po_group_detail) {
                $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
                $total_vat_actual_price += ($po_group_detail->pogpd_vat_actual_percent);
            }

            if($total_import_tax_book_price == 0)
            {
                foreach ($po_group_details as $po_group_detail) {
                    $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                    $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                    $total_import_tax_book_price += $book_tax;
                }
            }

            if($total_vat_actual_price == 0)
            {
                foreach ($po_group_details as $po_group_detail) {
                    $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                    $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                    $total_vat_actual_price += $book_tax;
                }
            }

            // $po_group->total_import_tax_book_percent += $total_import_tax_book_percent;
            $po_group->save();

            // group product detail
            $po_group_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->where('quantity_inv','!=',0)->get();
            foreach ($po_group_details as $group_detail) {
                if($po_group->freight != null && $po_group->po_group_total_gross_weight != 0 )
                {
                    $item_gross_weight     = $group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_freight         = $po_group->freight;
                    $total_quantity        = $group_detail->quantity_inv;
                    $freight               = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    $group_detail->freight = $freight;
                }

                if($po_group->landing != null && $po_group->po_group_total_gross_weight != 0)
                {
                    $item_gross_weight     = $group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_quantity        = $group_detail->quantity_inv;
                    $total_landing         = $po_group->landing;
                    $landing               = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    $group_detail->landing = $landing;
                }

                $group_detail->save();
            }

            $purchase_order->po_group_id = null;
            $purchase_order->save();

            //To Delete Entry From POGroup Detail

            $check_group_detail = PoGroupDetail::where('po_group_id',$po_group->id)->where('purchase_order_id',$purchase_order->id)->first();

            if($check_group_detail !== null)
            {
                $check_group_detail->delete();
            }

            if($po_group->po_group_detail->count() < 1)
            {
                $po_group->is_cancel = 2;
                $po_group->save();
            }
        }
        // group product detail end

        // });
        if($po_group_id)
        {
            $group_po_id = PoGroupDetail::where('po_group_id',$po_group_id)->first();

            if($group_po_id != null)
            {
                $this->updateGroupViaPo($group_po_id->purchase_order_id);
            }
        }
        DB::commit();
        return response()->json(['success' => true]);

    }

    public function updateTransferDocument(Request $request)
    {
        $po_detail = DraftPurchaseOrder::where('id',$request->pod_id)->first();

        foreach($request->except('pod_id') as $key => $value)
        {
            $po_detail->$key = $value;
        }
        $po_detail->save();

        return response()->json(['success' => true]);
    }

    public function updatePoTransferDocument(Request $request)
    {
        $po_detail = PurchaseOrder::where('id',$request->pod_id)->first();

        foreach($request->except('pod_id') as $key => $value)
        {
            $po_detail->$key = $value;
        }
        $po_detail->save();

        return response()->json(['success' => true]);
    }

    public function updateTransferDocumentDetail(Request $request)
    {
        $po_detail = DraftPurchaseOrderDetail::where('id',$request->pod_id)->first();

        foreach($request->except('pod_id') as $key => $value)
        {
            $po_detail->$key = $value;
        }
        $po_detail->save();

        return response()->json(['success' => true]);
    }

    public function updatePoTransferDocumentDetail(Request $request)
    {
        $po_detail = PurchaseOrderDetail::where('id',$request->pod_id)->first();

        foreach($request->except('pod_id') as $key => $value)
        {
            $po_detail->$key = $value;
        }
        $po_detail->save();

        return response()->json(['success' => true]);
    }

    public function updatePoTransferDocumentDetailForReserving(Request $request)
    {
        // dd($request->all());
        $po_detail = PurchaseOrderDetail::where('id',$request->pod_id)->first();
        $po = PurchaseOrder::find($request->po_id);

        $check_reserved_quantity = TransferDocumentReservedQuantity::where('po_id',$po->id)->where('pod_id',$po_detail->id)->sum('reserved_quantity');
        // dd($check_reserved_quantity);
        if($request->groupSelected == 1)
        {
            if($check_reserved_quantity == $po_detail->quantity)
            {
                return response()->json(['reserved_completed' => true]);
            }
        }

        if($request->groupSelected == 1)
        {
            $check = TransferDocumentReservedQuantity::where('po_id',$request->po_id)->where('pod_id',$request->pod_id)->where('stock_id',$request->stock_id)->first();
            if(!$check)
            {
                $check_available_stock = StockManagementOut::find($request->stock_id);

                $check_group_available_stock = StockManagementOut::whereNotNull('po_group_id')->where('warehouse_id',$po->from_warehouse_id)->where('product_id',$po_detail->product_id)->sum('available_stock');

                $final_stock = $check_group_available_stock;

                $remaining_qty = $po_detail->quantity - $check_reserved_quantity;

                if(($final_stock < $remaining_qty))
                {
                    return response()->json(['cannot_select' => true]);
                }
                if($check_available_stock->available_stock > 0)
                {
                    $new_res = new TransferDocumentReservedQuantity;
                    $new_res->po_id = $request->po_id;
                    $new_res->pod_id = $request->pod_id;
                    $new_res->stock_id = $request->stock_id;
                    if($check_available_stock->available_stock >= $remaining_qty)
                    {
                        $new_res->reserved_quantity = $remaining_qty;
                        $check_available_stock->available_stock -= $remaining_qty;
                    }
                    else
                    {
                        $new_res->reserved_quantity = $check_available_stock->available_stock;
                        $check_available_stock->available_stock = 0;
                    }
                    $new_res->save();
                    $check_available_stock->save();

                    return response()->json(['success' => true]);
                }
                else
                {
                    return response()->json(['available_stock' => false]);
                }

            }
            else
            {
                return response()->json(['success' => false]);
            }
        }
        elseif($request->groupSelected == 0)
        {
            $check = TransferDocumentReservedQuantity::where('po_id',$request->po_id)->where('pod_id',$request->pod_id)->where('stock_id',$request->stock_id)->first();
            if($check)
            {
                $check_available_stock = StockManagementOut::find($request->stock_id);
                $check_available_stock->available_stock += $check->reserved_quantity;
                $check_available_stock->save();

                $check->delete();

                return response()->json(['success' => true]);
            }
            else
            {
                return response()->json(['success' => false]);
            }
        }

        return response()->json(['success' => true]);
    }
    public function updateDraftPoTransferDocumentDetailForReserving(Request $request)
    {
        // dd($request->all());
        $po_detail = DraftPurchaseOrderDetail::where('id',$request->pod_id)->first();
        $po = DraftPurchaseOrder::find($request->po_id);

        $check_reserved_quantity = TransferDocumentReservedQuantity::where('draft_po_id',$po->id)->where('draft_pod_id',$po_detail->id)->sum('reserved_quantity');
        // dd($check_reserved_quantity);
        if($request->groupSelected == 1)
        {
            if($check_reserved_quantity == $po_detail->quantity)
            {
                return response()->json(['reserved_completed' => true]);
            }
        }

        if($request->groupSelected == 1)
        {
            $check = TransferDocumentReservedQuantity::where('draft_po_id',$request->po_id)->where('draft_pod_id',$request->pod_id)->where('stock_id',$request->stock_id)->first();
            if(!$check)
            {
                $check_available_stock = StockManagementOut::find($request->stock_id);

                $check_group_available_stock = StockManagementOut::whereNotNull('po_group_id')->where('warehouse_id',$po->from_warehouse_id)->where('product_id',$po_detail->product_id)->sum('available_stock');

                $final_stock = $check_group_available_stock;

                $remaining_qty = $po_detail->quantity - $check_reserved_quantity;
                if(($final_stock < $remaining_qty))
                {
                    return response()->json(['cannot_select' => true]);
                }
                if($check_available_stock->available_stock > 0)
                {
                    $new_res = new TransferDocumentReservedQuantity;
                    $new_res->draft_po_id = $request->po_id;
                    $new_res->draft_pod_id = $request->pod_id;
                    $new_res->stock_id = $request->stock_id;
                    if($check_available_stock->available_stock >= $remaining_qty)
                    {
                        $new_res->reserved_quantity = $remaining_qty;
                        $check_available_stock->available_stock -= $remaining_qty;
                    }
                    else
                    {
                        $new_res->reserved_quantity = $check_available_stock->available_stock;
                        $check_available_stock->available_stock = 0;
                    }
                    $new_res->save();
                    $check_available_stock->save();

                    return response()->json(['success' => true]);
                }
                else
                {
                    return response()->json(['available_stock' => false]);
                }

            }
            else
            {
                return response()->json(['success' => false]);
            }
        }
        elseif($request->groupSelected == 0)
        {
            $check = TransferDocumentReservedQuantity::where('draft_po_id',$request->po_id)->where('draft_pod_id',$request->pod_id)->where('stock_id',$request->stock_id)->first();
            if($check)
            {
                $check_available_stock = StockManagementOut::find($request->stock_id);
                $check_available_stock->available_stock += $check->reserved_quantity;
                $check_available_stock->save();

                $check->delete();

                return response()->json(['success' => true]);
            }
            else
            {
                return response()->json(['success' => false]);
            }
        }

        return response()->json(['success' => true]);
    }
      public function getDraftPurchaseOrderHistory(Request $request)
    {
      // dd($request->order_id);
      $query = DraftPurchaseOrderHistory::with('product','user')->where('po_id',$request->order_id)->orderBy('id','DESC');
       return Datatables::of($query)
         ->addColumn('user_name',function($item){
              return @$item->user != null ? $item->user->name : '--';
          })

         ->addColumn('item',function($item){

            return @$item->reference_number != null ? @$item->product->refrence_code : '--';

          })

         ->addColumn('order_no',function($item){
              return @$item->po_id != null ? $item->po_id : '--';
          })

         ->addColumn('column_name',function($item){
              return @$item->column_name != null ? $item->column_name : '--';
          })

         ->addColumn('old_value',function($item){
              return @$item->old_value != null ? $item->old_value : '--';
          })

         ->addColumn('new_value',function($item){
          if(@$item->column_name == 'selling_unit'){
              return @$item->new_value != null ? $item->units->title : '--';

          }else if(@$item->column_name == 'Supply From'){
            return @$item->new_value != null ? $item->from_warehouse->warehouse_title : '--';
          }
          else{

              return @$item->new_value != null ? $item->new_value : '--';
          }
          })
           ->addColumn('created_at',function($item){
              return @$item->created_at != null ? $item->created_at->format('d/m/Y H:i:s') : '--';
          })
          // ->setRowId(function ($item) {
          //   return $item->id;
          // })

            ->rawColumns(['user_name','item','column_name','old_value','new_value','created_at','order_no'])
            ->make(true);

    }

    public function getStockData(Request $request)
    {

        // $job_status = ExportStatus::where('type', 'stock_card_job')->where('user_id', Auth::user()->id)->first();
        // if ($job_status == null)
        // {
        //     $job_status = new ExportStatus();
        //     $job_status->type = 'stock_card_job';
        //     $job_status->user_id = Auth::user()->id;
        // }
        // $job_status->status = 1;
        // $job_status->exception = null;
        // $job_status->error_msgs = null;
        // $job_status->save();

        // StockCardJob::dispatch($request->all(), Auth::user());
        // return response()->json(['success' => true]);

         $custom_table_data = '';
         $wh = WarehouseProduct::where('warehouse_id',$request->warehouse_id)->where('product_id',$request->product_id)->first();
         $final_stock = StockManagementOut::select('quantity_out','warehouse_id','quantity_in')->where('product_id',$request->product_id)->where('warehouse_id',$request->warehouse_id)->get();
        $stock_card = StockManagementIn::where('product_id',$request->product_id)->where('warehouse_id',$request->warehouse_id)->orderBy('expiration_date','DESC')->get();
        $product = Product::find($request->product_id);
        $decimal_places = $product->sellingUnits != null ? $product->sellingUnits->decimal_places : 3;

        $stock_out_out_t = -0;
        $stock_out_in_t = 0;
        $prev_out = null;

            $custom_table_data .= '<div class="bg-white table-responsive h-100" style="min-height: 235px;">
                <div class="">
                  <div class="">';
                      $stck_out = (clone $final_stock)->where('warehouse_id',$wh->warehouse_id)->sum('quantity_out');
                      $stck_in = (clone $final_stock)->where('warehouse_id',$wh->warehouse_id)->sum('quantity_in');
                      $current_stock_all = round($stck_in,$decimal_places) - abs(round($stck_out,$decimal_places));
                $custom_table_data .= '</div>
                </div>
                <table class="table headings-color table-po-pod-list mb-0" style="width: 100%;">
                  <tbody>
                    <tr>
                      <th style="width: 25%;border-left: 1px solid #eee;">
                        <span><b>Current Stock : </b> <span class="span-current-quantity-'.$wh->warehouse_id.'"> '.($current_stock_all != null ? $current_stock_all :0).' </span><input type="hidden" class="current-quantity-'.$wh->warehouse_id.'" value="'.($current_stock_all != null ? $current_stock_all :0).'"></span>
                      </th>
                      <th style="width: 15%;border-left: 1px solid #eee;">
                        <span><b>Selling Unit : </b> '.(@$product->sellingUnits->title != null ? @$product->sellingUnits->title : @$product->sellingUnits->title).'</span>
                      </th>
                      <th style="width: 15%;"></th>
                      <th style="width: 15%;"></th>
                      <th style="width: 10%;"></th>
                    </tr>
                  </tbody>
                </table>';
          $custom_table_data .= '<table class="table headings-color table-po-pod-list mb-0" style="width: 100%;">
            <tbody>';
              if($stock_card->count() > 0)
              {
                foreach($stock_card as $card)
                {
                  if($wh->getWarehouse->id == $card->warehouse_id)
                  {
                      $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
                      $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');
                      if(round(($stock_out_in+$stock_out_out),$decimal_places) != 0 || ($stock_out_in == 0 && $stock_out_out == 0)){
                    $custom_table_data .= '<tr class="header">
                    <th style="width: 25%;border: 1px solid #eee;">EXP:
                      <span class="m-l-15 inputDoubleClickFirst" id="expiration_date"  data-fieldvalue="'.($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') :'').'">'.($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') :'---').'</span>
                      <input type="text" style="width:75%;" placeholder="Expiration Date" name="expiration_date" class="d-none expiration_date_sc" value="'.($card->expiration_date != null ? Carbon::parse($card->expiration_date)->format('d/m/Y') :'').'" data-id="'.$card->id.'">
                    </th>
                    <th style="width: 15%;border: 1px solid #eee;">IN: <span class="span-expiry-in-value-'.$card->id.'">'.($stock_out_in != NULL ? number_format($stock_out_in,$decimal_places,'.',',') : number_format(0,$decimal_places,'.',',')).'</span> <input  type="hidden" value="'.($stock_out_in != NULL ? round($stock_out_in,$decimal_places) : 0).'" class="expiry-in-value-'.$card->id.'"></th>
                    <th style="width: 15%;border: 1px solid #eee;">OUT: <span class="span-expiry-out-value-'.$card->id.'">'.($stock_out_out != NULL ? number_format($stock_out_out,$decimal_places,'.',',') : number_format(0,$decimal_places,'.',',')).'</span> <input type="hidden" value="'.($stock_out_out != NULL ? round($stock_out_out,$decimal_places) : 0).'" class="expiry-out-value-'.$card->id.'"></th>
                    <th style="width: 15%;border: 1px solid #eee;border-right: 0px;">Balance: <span class="span-expiry-balance-value-'.$card->id.'">'.number_format(($stock_out_in+$stock_out_out),$decimal_places,'.',',').'</span> <input type="hidden" value="'.round(($stock_out_in+$stock_out_out),$decimal_places).'" class="expiry-balance-value-'.$card->id.'"></th>
                    <th style="width: 10%;border: 1px solid #eee;border-left: 0px;">
                      <a href="javascript:void(0)" data-id="'.$card->id.'" class="collapse_rows "><button class="btn recived-button view-supplier-btn toggle-btn1 pull-right show_card_detail" data-id="'.$card->id.'" data-toggle="collapse" style="width: 20%;"><span id="sign'.@$card->id.'">+</span></button></a>
                    </th>
                  </tr>
                  <tr class="ddddd" id="ddddd'.$card->id.'" style="display: none;">
                <td colspan="6" style="padding: 0">
                <div style="max-height: 40vh;overflow-y:auto;" class="tableFix">
                <table width="100%" class="dataTable stock_table table-theme-header" id="stock-detail-table'.$card->id.'" >
                  <thead>
                    <tr>
                      <th>Action</th>
                      <th>Date </th>
                      <th>Customer ref #</th>
                      <th>Title # </th>
                      <th>Spoilage</th>
                      <th>IN </th>
                      <th>Out </th>
                      <th>Balance </th>';
                      if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
                      {
                        $custom_table_data .= '<th>COGS</th>';
                      }

                      $custom_table_data .= '<th>Note</th>
                    </tr>
                  </thead>
                  <tbody id="stock-detail-table-body'.@$card->id.'">';
                    
                  $custom_table_data .= '<tr><td col-span="8">
                  <i class="fa fa-spinner fa-spin fa-3x fa-fw" style="color:#13436c;"></i><span class="sr-only">Loading...</span>
                  </td></tr></tbody>
                </table>
                </div>';
                if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 4 || Auth::user()->role_id == 9 || Auth::user()->role_id == 11){
                $custom_table_data .= '<tr><td><a href="javascript:void(0)" class="btn btn-sale recived-button add-new-stock-btn" id="add-new-stock-btn'.$card->id.'" style="width: 40%; display: none;" data-warehouse_id="'.$card->warehouse_id.'" data-id="'.$card->id.'" title="Add Manual Stock">+</a></td></tr>';
                 }
              $custom_table_data .= '</td>
              </tr>';
                  }
                }
              }
              $custom_table_data .= '<tr class="header"></tr>';
              }
              else
              {
               $custom_table_data .= '<tr>
                  <td align="center">No Data Found!!!</td>
                </tr>';
              }
            $custom_table_data .= '</tbody>
            </table></div>';

            return response()->json(['success' => true, 'html' => $custom_table_data]);
    }

    public function getStockDataCard(Request $request){
        $card = StockManagementIn::find($request->id);
        $product = Product::find($card->product_id);
        $wh = WarehouseProduct::where('warehouse_id',$card->warehouse_id)->where('product_id',$card->product_id)->first();
        $decimal_places = $product->sellingUnits != null ? $product->sellingUnits->decimal_places : 3;
        $custom_table_data = '';
        $stock_out_out_t = -0;
        $stock_out_in_t = 0;
        $prev_out = null;
          $stock_out = \App\Models\Common\StockManagementOut::where('smi_id',$card->id)->with('stock_out_order.customer','stock_out_po')->orderBy('id','DESC')->limit(50)->get();
                    if($stock_out->count() > 0){
                      foreach($stock_out as $out){
                          // $stock_out_in = \App\Models\Common\StockManagementOut::where('smi_id',$card->id)->where('id','<=',$out->id)->sum('quantity_in');
                          // $stock_out_out = \App\Models\Common\StockManagementOut::where('smi_id',$card->id)->where('id','<=',$out->id)->sum('quantity_out');
                          if($stock_out_out_t == 0 || $stock_out_in_t == 0){
                            $stock_out_in = \App\Models\Common\StockManagementOut::where('smi_id', $card->id)->where('id', '<=', $out->id)->sum('quantity_in');
                            $stock_out_out = \App\Models\Common\StockManagementOut::where('smi_id', $card->id)->where('id', '<=', $out->id)->sum('quantity_out');

                            $stock_out_in_t = $stock_out_in;
                            $stock_out_out_t = $stock_out_out;
                          }else{
                            $stock_out_in_t -= @$prev_out->quantity_in;
                            $stock_out_out_t -= @$prev_out->quantity_out;
                          }
                          $prev_out = $out;

                          $stock_out_in = $stock_out_in_t;
                          $stock_out_out = $stock_out_out_t;

                      $custom_table_data .= '<tr>
                        <td>';
                          if(($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer')){
                          $custom_table_data .= '<a href="javascript:void(0)" class="actionicon deleteIcon text-center deleteStock" data-id="'.$out->id.'"><i class="fa fa-trash" title="Delete Stock"></i></a>';
                          }
                          else{
                            $custom_table_data .= '<span>--</span>';
                          }
                        $custom_table_data .= '</td>
                        <td>'.Carbon::parse(@$out->created_at)->format('d/m/Y').'</td>
                        <td width="10%">';
                          if($out->order_id !== null && @$out->stock_out_order && @$out->stock_out_order->customer){
                          $custom_table_data .= '<a href="'.route('get-customer-detail',@$out->stock_out_order->customer->id).'" target="_blank">
                            '.(@$out->stock_out_order != null ? $out->stock_out_order->customer->reference_name : "--").'
                          </a>';
                          }
                          elseif($out->po_group_id != null){
                            $groups = $out->get_po_group;
                            if($groups != null)
                            {
                              $customers = $groups->find_customers($groups,$request->product_id);
                            }
                          if($customers->count() > 0){
                            $i = 1;
                            $customer_names = '';
                            $j = 0;
                            foreach ($customers as $cust) {
                                if ($j < 3) {
                                    $customer_names .= $cust->reference_name . '<br>';
                                }
                                else{
                                    break;
                                }
                                $j++;
                            }
                              $custom_table_data .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal'.$out->id.'" title="Customers" class="font-weight-bold">
                              '.$customer_names.'
                            </a>
                          <div class="modal fade" id="poNumberModal'.$out->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                              <div class="modal-content">
                                <div class="modal-header">
                                  <h5 class="modal-title" id="exampleModalLabel">Customers</h5>
                                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                  </button>
                                </div>
                                <div class="modal-body">
                                <table class="bordered" style="width:100%;">
                                    <thead style="border:1px solid #eee;text-align:center;">
                                      <tr><th>S.No</th><th>Customer Ref #</th></tr>
                                    </thead>
                                    <tbody>';
                                    foreach ($customers as $cust){
                                      $link = '<a target="_blank" href="'.route('get-customer-detail', $cust->id).'" title="View Detail"><b>'.$cust->reference_name.'</b></a>';
                                    $custom_table_data .= '<tr>
                                      <td>
                                        '.$i.'
                                      </td>
                                      <td>
                                      <a target="_blank" href="'.route('get-customer-detail', $cust->id).'" title="View Detail"><b>'.$cust->reference_name.'</b></a>
                                      </td>
                                    </tr>';
                                    $i++;
                                    }
                                    $custom_table_data .= '</tbody>
                                </table>
                                </div>
                              </div>
                            </div>
                          </div>';
                        }
                        else{
                            $custom_table_data .= '<span>--</span>';
                          }
                        }
                        else{
                          $custom_table_data .= '<span>--</span>';
                          }
                        $custom_table_data .= '</td>
                        <td>';
                          if($out->order_id !== null && $out->title == null && $out->stock_out_order){
                              $ret = $out->stock_out_order->get_order_number_and_link($out->stock_out_order);
                              $ref_no = $ret[0];
                              $link = $ret[1];
                            $custom_table_data .= '<a target="_blank" href="'.route($link, ['id' => $out->stock_out_order->id]).'" title="View Detail" class="">ORDER: '.$ref_no.'</a>';
                          }
                          elseif($out->po_group_id != null){
                            $custom_table_data .= '<a target="_blank" href="'.url('warehouse/warehouse-completed-receiving-queue-detail',$out->po_group_id).'" class="" title="View Detail">SHIPMENT: '.$out->get_po_group->ref_id.'</a>';
                          }
                          elseif($out->p_o_d_id != null && $out->stock_out_po != null && $out->stock_out_po->status != 40){
                            $custom_table_data .= '<a target="_blank" href="'.url('get-purchase-order-detail',$out->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($out->title != null ? $out->title : 'PO' ). ' : '.($out->stock_out_purchase_order_detail->PurchaseOrder->ref_id).'</a>';
                          }
                          elseif($out->p_o_d_id != null && $out->stock_out_po != null && $out->stock_out_po->status != 40){
                            $custom_table_data .= '<a target="_blank" href="'.url('get-purchase-order-detail',$out->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($out->title != null ? $out->title : 'PO' ). ' : '.($out->stock_out_purchase_order_detail->PurchaseOrder->ref_id).'</a>';
                          }
                          elseif($out->p_o_d_id != null && $out->title == 'TD'){
                            $custom_table_data .= '<a target="_blank" href="'.url('get-purchase-order-detail',@$out->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($out->title != null ? $out->title : 'PO' ). ' : '.(@$out->stock_out_purchase_order_detail->PurchaseOrder->ref_id).'</a>';
                          }
                          elseif($out->p_o_d_id != null)
                            {
                                $custom_table_data .= '<b><span style="color:black;"><a target="_blank" href="'.url('get-purchase-order-detail',$out->stock_out_purchase_order_detail->PurchaseOrder->id).'" class="" title="View Detail">'.($out->title != null ? $out->title : (@$out->stock_out_purchase_order_detail->PurchaseOrder->supplier_id == null ? 'TD' : 'PO') ) .':'. $out->stock_out_purchase_order_detail->PurchaseOrder->ref_id .'</span></a></b>';
                                // $title = "PO:".$out->stock_out_purchase_order_detail->PurchaseOrder->ref_id  ;
                            }

                          elseif($out->title != null){
                            $custom_table_data .= '<span class="m-l-15 selectDoubleClick" id="title" data-fieldvalue="'.@$out->title.'">
                              '.(@$out->title != null ?$out->title:'Select').'
                            </span>
                            <select name="title" class="selectFocusStock form-control d-none" data-id="'.$out->id.'">
                              <option>Choose Reason</option>
                              <option '.($out->title == 'Manual Adjustment' ? 'selected' : '' ).' value="Manual Adjustment">Manual Adjustment</option>
                              <option '.($out->title == 'Expired' ? 'selected' : '' ).' value="Expired">Expired</option>
                              <option '.($out->title == 'Spoilage ' ? 'selected' : '' ).' value="Spoilage">Spoilage</option>
                              <option '.($out->title == 'Lost ' ? 'selected' : '' ).' value="Lost">Lost</option>
                              <option '.($out->title == 'Marketing ' ? 'selected' : '' ).'value="Marketing">Marketing</option>
                              <option '.($out->title == 'Return ' ? 'selected' : '' ).' value="Return">Return</option>
                              <option '.($out->title == 'Transfer ' ? 'selected' : '' ).' value="Transfer">Transfer</option>
                            </select>';
                            if($out->order_id != null)
                            {
                                if(@$out->stock_out_order->primary_status == 37)
                                {
                                    $custom_table_data .= '<a target="_blank" href="'.route('get-completed-draft-invoices', ['id' => $out->stock_out_order->id]).'" title="View Detail" class="font-weight-bold ml-3">ORDER# '.@$out->stock_out_order->full_inv_no.'</a>';
                                }
                            }
                            if($out->po_id != null)
                            {
                                if(@$out->stock_out_po->status == 40)
                                {
                                    $custom_table_data .= '<a target="_blank" href="'.url('get-purchase-order-detail',$out->po_id).'" title="View Detail" class="font-weight-bold ml-3">PO# '.@$out->stock_out_po->ref_id.'</a>';
                                }
                            }
                          }
                          else{
                            $custom_table_data .= 'Adjustment';
                          }
                        $custom_table_data .= '</td>';

                        if ($out->spoilage != null) {
                            $spoilage_type = $out->spoilage_type != null ? ' (' .$out->spoilage . ')' : '';
                            $custom_table_data .= '<td>'.$out->spoilage . $spoilage_type .'</td>';
                        }
                        else{
                            $custom_table_data .= '<td>--</td>';
                        }


                        $custom_table_data .= '<td>';
                            if(($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer') && ($out->quantity_out == null || $out->quantity_out == 0))
                            {
                              $enable = 'inputDoubleClickFirst';
                            }
                            else
                            {
                              $enable = '';
                            }
                          $custom_table_data .= '<span class="m-l-15 '.$enable.' disableDoubleInClick-'.$out->id.'" id="quantity_in"  data-fieldvalue="'.@$out->quantity_in.'">'.(@$out->quantity_in != null ? number_format($out->quantity_in,$decimal_places,'.',',') :number_format(0,$decimal_places,'.',',')).'</span>
                          <input type="number" min="0" style="width:100%;" name="quantity_in" class="fieldFocusStock d-none " data-type="in" value="'.@$out->quantity_in.'" data-warehouse_id="'.$wh->getWarehouse->id.'" data-smi="'.$card->id.'" data-id="'.$out->id.'"></td>
                        <td>';
                            if(($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer')  && ($out->quantity_in == null || $out->quantity_in == 0))
                            {
                              $enable2 = 'inputDoubleClickFirst';
                            }
                            else
                            {
                              $enable2 = '';
                            }
                          $custom_table_data .= '<span class="m-l-15 '.$enable2.' disableDoubleOutClick-'.$out->id.' " id="quantity_out"   data-fieldvalue="'.@$out->quantity_out.'">'.(@$out->quantity_out != null ? number_format($out->quantity_out,$decimal_places,'.',',') :number_format(0,$decimal_places,'.',',')).'</span>
                          <input type="number" min="0" style="width:100%;" name="quantity_out"  class="fieldFocusStock d-none " data-type="out" data-warehouse_id="'.$wh->getWarehouse->id.'" data-smi="'.$card->id.'"   value="'.@$out->quantity_out.'" data-id="'.$out->id.'">
                        </td>
                        <td>'.number_format(($stock_out_in+$stock_out_out),$decimal_places,'.',',').'</td>';
                        if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6){
                        $custom_table_data .=  '<td>';
                          if(($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer')){
                          $custom_table_data .= '<span class="m-l-15 inputDoubleClick" id="cost"  data-fieldvalue="'.$out->cost.'">
                            '.($out->cost != null ? $out->cost : '--').'
                          </span>
                          <input type="text" autocomplete="nope" name="cost" class="fieldFocusCost d-none form-control" data-id="'.$out->id.'" value="'.(@$out->cost!=null)?$out->cost:''.'">';
                          }
                          else{
                              if($out->cost != null){
                              $custom_table_data .= $out->cost != null ? round(($out->cost),3) : '--';
                              }
                              elseif($out->order_product_id != null && $out->order_product){
                              $custom_table_data .= $out->order_product->actual_cost != null ? number_format($out->order_product->actual_cost,2,'.',',') : '--';
                              }
                              else
                              {
                                $custom_table_data .= '<span>--</span>';
                              }

                          }
                        $custom_table_data .= '</td>';
                        }
                        $custom_table_data .= '<td>';
                            if(($out->title == 'Manual Adjustment' || $out->title == 'Expired' || $out->title == 'Spoilage' || $out->title == 'Lost' || $out->title == 'Marketing' || $out->title == 'Return' || $out->title == 'Transfer'))
                            {
                              $enable2 = 'inputDoubleClickFirst';
                            }
                            else
                            {
                              $enable2 = '';
                            }
                          $custom_table_data .= '<span class="m-l-15 '.$enable2.'" id="note"  data-fieldvalue="'.@$out->note.'">'.(@$out->note != null ? $out->note : '--').'</span>
                          <input type="text" style="width:100%;" name="note" class="fieldFocusStock d-none" value="'.@$out->note.'" data-id="'.$out->id.'">
                        </td>
                      </tr>';
                      }
                    }
                    else
                    {
                      $custom_table_data .= '<tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-center"></td>
                        <td class="text-center"></td>
                        <td></td>
                        <td></td>';
                        if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6){
                        $custom_table_data .= '<td></td>';
                        }
                        $custom_table_data .= '<td></td>
                      </tr>';
                    }
                    return response()->json(['success' => true, 'html' => $custom_table_data]);
    }

    public function recursiveCallForStockCardJob(Request $request)
    {
        $job_status = ExportStatus::where('type', 'stock_card_job')->where('user_id', Auth::user()->id)->first();
        return response()->json(['status' => $job_status->status, 'html' => $job_status->exception]);
    }

    public function removeFromExistingGroupReceived(Request $request)
    {
        // DB::transaction(function () use ($request) {
        DB::beginTransaction();

        if(gettype($request->selected_ids) == 'string')
        {
            $po_ids = explode(' ', $request->selected_ids);
        }
        else
        {
            $po_ids = $request->selected_ids;
        }
        $po_warehouses = PurchaseOrder::whereIn('id' , $po_ids)->pluck('to_warehouse_id')->toArray();

        $total_quantity              = null;
        $total_price                 = null;
        $total_import_tax_book_price = null;
        $total_vat_actual_price      = null;
        $total_buying_price_in_thb   = null;
        $total_gross_weight          = null;
        $not_in_group = [];

        foreach ($po_ids as $po_id)
        {
            $purchase_order = PurchaseOrder::find($po_id);
            if($purchase_order)
            {
                if($purchase_order->status < 14 || $purchase_order->status < '14')
                {
                    array_push($not_in_group, $purchase_order->ref_id);
                }
            }
        }

        if(!empty($not_in_group))
        {
            $not_in_group = implode(', ', $not_in_group);
            $errorMsg = $not_in_group." These PO's already removed from a group or Received into stock !!!";
            DB::rollBack();
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }

        $group_cannot_be_deleted     = '';
        $already_converted           = false;

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);
            if($purchase_order->status > 15)
            {
                $group_cannot_be_deleted = ''.$purchase_order->ref_id.' ';
                $already_converted = true;
                // return;
            }
        }

        if($already_converted == true)
        {
            return response()->json(['already_converted' => true,'group_cannot_be_deleted' => $group_cannot_be_deleted]);
        }
        // dd($not_in_group);

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {
                $total_quantity +=  $p_o_d->quantity;
                if($p_o_d->order_product_id != null)
                {
                    $order_product = $p_o_d->order_product;
                    $order         = $order_product->get_order;
                    if($order->primary_status !== 3 && $order->primary_status !== 17)
                    {
                        $order_product->status = 8;
                        $order_product->save();

                        $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('status','<',8)->count();
                        if($order_products_status_count == 0)
                        {
                            $order->status = 8;
                            $order->save();
                            $order_history             = new OrderStatusHistory;
                            $order_history->user_id    = Auth::user()->id;
                            $order_history->order_id   = @$order->id;
                            $order_history->status     = 'DI(Importing)';
                            $order_history->new_status = 'DI(Purchasing)';
                            $order_history->save();
                        }
                    }
                }
            }

            $total_import_tax_book_price += $purchase_order->total_import_tax_book_price;
            $total_vat_actual_price      += $purchase_order->total_vat_actual_price;
            $total_gross_weight          += $purchase_order->total_gross_weight;
            $total_buying_price_in_thb   += $purchase_order->total_in_thb;
            $purchase_order->status      = 13;
            $purchase_order->save();

            // PO status history maintaining
            $page_status = Status::select('title')->whereIn('id',[13,14])->pluck('title')->toArray();

            $poStatusHistory = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id    = Auth::user()->id;
            $poStatusHistory->po_id      = $purchase_order->id;
            $poStatusHistory->status     = $page_status[1];
            $poStatusHistory->new_status = $page_status[0];
            $poStatusHistory->save();

            $po_group = PoGroup::find($purchase_order->po_group_id);

            $po_group->total_quantity              -= $total_quantity;
            $po_group->po_group_import_tax_book    -= floor($total_import_tax_book_price * 100) / 100;
            $po_group->po_group_vat_actual         -= floor($total_vat_actual_price * 100) / 100;
            $po_group->total_buying_price_in_thb   -= $total_buying_price_in_thb;
            $po_group->po_group_total_gross_weight -= $total_gross_weight;

            $po_group->save();
        }


        /*********************Here starts the new code for groups*************/
        $occurrence = null;
        $total_import_tax_book_percent = null;

        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);

            $po_group_id = $purchase_order->po_group_id;
            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {

                $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$po_group_id)->where('supplier_id',$purchase_order->supplier_id)->first();
                if($po_group_product != null)
                {
                    $reverted_history = new RevertedPurchaseOrder;
                    $reverted_history->group_id = $po_group_product->po_group_id;
                    $reverted_history->po_group_product_detail_id = $po_group_product->id;
                    $reverted_history->po_id = $purchase_order->id;
                    $reverted_history->product_id = $p_o_d->product_id;
                    $reverted_history->supplier_id = $purchase_order->supplier_id;
                    $reverted_history->quantity = $p_o_d->quantity;
                    $reverted_history->total_received = $po_group_product->quantity_received_1 + $po_group_product->quantity_received_2;
                    $reverted_history->occurrence = $po_group_product->occurrence;
                    $reverted_history->save();

                    $po_group_product->quantity_ordered          -= @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv              -= $p_o_d->quantity;
                    $po_group_product->import_tax_book_price     -= $p_o_d->pod_import_tax_book_price;
                    $po_group_product->pogpd_vat_actual_percent  -= $p_o_d->pod_vat_actual_price;
                    $po_group_product->total_gross_weight        -= $p_o_d->pod_total_gross_weight;
                    $po_group_product->total_unit_price_in_thb   -= $p_o_d->unit_price_in_thb * $p_o_d->quantity;
                    $po_group_product->occurrence                -= 1;
                    $po_group_product->save();

                    if($po_group_product->occurrence == 0)
                    {
                        $po_group_product->delete();
                    }
                    else if($po_group_product->occurrence == 1)
                    {
                        $po_id_s = PurchaseOrder::where('po_group_id',$po_group_id)->where('supplier_id',$purchase_order->supplier_id)->whereNotIn('id',$po_ids)->pluck('id');
                        $all_record = PurchaseOrderDetail::whereIn('po_id',$po_id_s)->where('product_id',$p_o_d->product_id)->first();
                        if($all_record){
                            $po_group_product->po_id = $all_record->po_id;
                            $po_group_product->pod_id = $all_record->id;
                            $po_group_product->order_id = $all_record->order_product != null ? $all_record->order_product->order_id : null;
                            $po_group_product->save();
                        }
                        
                    }
                }
            }

            $po_removed_history = new ProductReceivingHistory;

            $po_removed_history->po_group_id = $po_group_id;
            $po_removed_history->pod_id = null;
            $po_removed_history->term_key = $purchase_order->ref_id;
            $po_removed_history->old_value = 'Received Into Stock';
            $po_removed_history->new_value = 'Shipping';
            $po_removed_history->updated_by = Auth::user()->id;

            @$po_removed_history->save();

            $po_group = PoGroup::where('id',$po_group_id)->first();

            $total_import_tax_book_price = 0;
            $total_vat_actual_price = 0;
            $po_group_details = $po_group->po_group_product_details;
            foreach ($po_group_details as $po_group_detail) {
                $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
                $total_vat_actual_price += ($po_group_detail->pogpd_vat_actual_percent);
            }

            if($total_import_tax_book_price == 0)
            {
                foreach ($po_group_details as $po_group_detail) {
                    $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                    $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                    $total_import_tax_book_price += $book_tax;
                }
            }

            if($total_vat_actual_price == 0)
            {
                foreach ($po_group_details as $po_group_detail) {
                    $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group_detail->po_group_id)->count();
                    $book_tax = (1/$count)* $po_group_detail->total_unit_price_in_thb;
                    $total_vat_actual_price += $book_tax;
                }
            }

            // $po_group->total_import_tax_book_percent += $total_import_tax_book_percent;
            $po_group->save();

            // group product detail
            $po_group_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$po_group->id)->where('quantity_inv','!=',0)->get();
            foreach ($po_group_details as $group_detail) {
                if($po_group->freight != null && $po_group->po_group_total_gross_weight != 0 )
                {
                    $item_gross_weight     = $group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_freight         = $po_group->freight;
                    $total_quantity        = $group_detail->quantity_inv;
                    $freight               = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    $group_detail->freight = $freight;
                }

                if($po_group->landing != null && $po_group->po_group_total_gross_weight != 0)
                {
                    $item_gross_weight     = $group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_quantity        = $group_detail->quantity_inv;
                    $total_landing         = $po_group->landing;
                    $landing               = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    $group_detail->landing = $landing;
                }
                $group_detail->save();
            }

            $purchase_order->po_group_id = null;
            $purchase_order->save();

            //To Delete Entry From POGroup Detail

            $check_group_detail = PoGroupDetail::where('po_group_id',$po_group->id)->where('purchase_order_id',$purchase_order->id)->first();

            if($check_group_detail !== null)
            {
                $check_group_detail->delete();
            }

            if($po_group->po_group_detail->count() < 1)
            {
                $po_group->is_cancel = 2;
                $po_group->save();
            }
        }
        // group product detail end

        // });
        if($po_group_id)
        {
            $group_po_id = PoGroupDetail::where('po_group_id',$po_group_id)->first();

            if($group_po_id != null)
            {
                $this->updateGroupViaPo($group_po_id->purchase_order_id);
            }
        }
        DB::commit();
        return response()->json(['success' => true]);

    }

    public function exportDraftPOToPDF($id, $show_price_input, $column_name, $sort_order, $pf_logo = null)
    {
        // dd($request->all());
        $price_checked = $show_price_input;

        // $purchaseOrder = PurchaseOrder::find($id);
        $poNote = DraftPurchaseOrderNote::where('po_id',$id)->first();
        $pf_logo = $pf_logo;
        if($pf_logo == 1)
        {
            $pf = Company::where('tax_id' , '0105561152253')->where('billing_state','3528')->first();
        }
        else
        {
            $pf = null;
        }

        $purchaseOrder = DraftPurchaseOrder::with('getSupplier', 'getFromWarehoue')->where('id',$id)->first();
        $getPoDetail = DraftPurchaseOrderDetail::where('po_id',$id)->where('draft_purchase_order_details.quantity','!=',0)->where('draft_purchase_order_details.is_billed','Product')->whereNotNull('draft_purchase_order_details.quantity')->select('draft_purchase_order_details.*');
        $request_data = new Request();
        $request_data->replace(['column_name' => $column_name, 'sort_order' => $sort_order]);
        $getPoDetail = DraftPurchaseOrderDetail::DraftPOSorting($request_data, $getPoDetail);
        $getPoDetail = $getPoDetail->get()->groupBy('product_id');
        $getPoDetailForNote = DraftPurchaseOrderDetail::with('notes')->where('po_id',$id)->where('quantity','!=',0)->where('is_billed','Product')->whereNotNull('quantity')->orderBy('product_id','ASC')->get();
        //dd($getPoDetailForNote);
        $createDate = Carbon::parse($purchaseOrder->created_at)->format('d/m/Y');

        $hidden_columns_by_admin = [];
        $quotation_config   = QuotationConfig::where('section','purchase_order')->first();
        $hide_columns = $quotation_config->show_columns;
        if($quotation_config->show_columns != null)
        {
            $hidden_columns = json_decode($hide_columns);
            $hidden_columns = implode (",", $hidden_columns);
            $hidden_columns_by_admin = explode (",", $hidden_columns);
        }

        $system_config = Configuration::select('server')->first();
        $pdf = PDF::loadView('users.purchase-order.draft-invoice',compact('getPoDetail','purchaseOrder','price_checked','createDate','poNote','pf','getPoDetailForNote','hidden_columns_by_admin', 'system_config'));


        // making pdf name starts
        $makePdfName='Draft Purchase Order-'.$purchaseOrder->ref_id.'';

        // making pdf name ends
        return $pdf->stream(
        $makePdfName.'.pdf',
          array(
            'Attachment' => 0
          )
        );

    }

    public function ClearReevrtPurchasingVat(Request $request)
    {
        $po_details = PurchaseOrderDetail::where('po_id',$request->po_id)->get();
        $old_value = null;
        $new_value = null;
        $total = 0;
        $vat_amount = 0;
        foreach($po_details as $po_detail)
        {
            if ($po_detail->product_id != null) {
                if ($request->action != 'Undo') {
                    $old_value = $po_detail->pod_vat_actual != null ? $po_detail->pod_vat_actual : '--';
                    $new_value = null;
                }
                else{
                    $history = PurchaseOrdersHistory::where('po_id', $request->po_id)->where('pod_id', $po_detail->id)->where('column_name', 'Purchasing Vat')->orderBy('id', 'desc')->first();
                    $old_value = $history->new_value != '--' ? $history->new_value : null;
                    $new_value = $history->old_value != '--' ? $history->old_value : null;
                }
                $po_detail->pod_vat_actual = $new_value != '--' ? $new_value : null;
                $po_detail->save();
                $order_history = new PurchaseOrdersHistory;
                $order_history->user_id = Auth::user()->id;
                $order_history->order_id = $po_detail->order_id;
                $order_history->reference_number = $po_detail->product->refrence_code;
                $order_history->old_value = $old_value;
                $order_history->column_name = "Purchasing Vat";
                $order_history->new_value = $new_value != null ? $new_value : '--';
                $order_history->po_id = $po_detail->po_id;
                $order_history->pod_id = $po_detail->id;
                $order_history->save();

                /*vat calculations*/
                $vat_calculations = $po_detail->calculateVat($po_detail->pod_unit_price, $po_detail->pod_vat_actual);
                $po_detail->pod_unit_price_with_vat = $vat_calculations['pod_unit_price_with_vat'];
                $po_detail->pod_vat_actual_price    = $vat_calculations['vat_amount'];

                /*convert val to thb's*/
                $converted_vals = $po_detail->calculateVatToSystemCurrency($request->po_id, $vat_calculations['vat_amount']);
                $po_detail->pod_vat_actual_price_in_thb = number_format($converted_vals['converted_amount'],3,'.','');
                $po_detail->save();

                $amount = $po_detail->pod_unit_price * $po_detail->quantity;
                $amount = $amount - ($amount * (@$po_detail->discount / 100));
                $total += $amount;
                $vat_amount += ($po_detail->pod_vat_actual / 100) * $amount;
            }
        }

        $po = PurchaseOrder::find($request->po_id);
        $po->total = number_format($total,3,'.','');
        $po->vat_amount_total = number_format($vat_amount,3,'.','');
        $po->total_with_vat = number_format($total + $vat_amount,3,'.','');
        $po->save();


        $msg = '';
        if ($request->action != 'Undo') {
            $msg = 'Values Cleared Successfully.';
        }
        else{
            $msg = 'Values Reverted Successfully.';
        }
        return response()->json([
            'success'   => true,
            'msg'   => $msg,
            'sub_total' => number_format($total,3,'.',''),
            'vat_amout' => number_format($vat_amount,3,'.',''),
            'total_w_v' => number_format($total + $vat_amount,3,'.','')
        ]);
    }

    public function getpogpd_id(Request $request)
    {
        $po_detail = PurchaseOrderDetail::select('product_id')->where('id',$request->rowId)->first();
        $pogpd = PoGroupProductDetail::select('id')->where('po_id', $request->po_id)->where('product_id', $po_detail->product_id)->first();
        $pogpd__id = $pogpd != null ? $pogpd->id : null;
        return response()->json(['success' => true, 'pogpd__id' => $pogpd__id]);
    }

    public function actionsForSelectedPos(Request $request)
    {
        if ($request->action == 'calculate_gross_weight') {
            $total_gross_weight = PurchaseOrder::whereIn('id', $request->selected_pos)->sum('total_gross_weight');
            return response()->json(['success' => true, 'total_gross_weight' => $total_gross_weight]);
        }
    }
}
