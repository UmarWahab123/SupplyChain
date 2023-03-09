<?php

namespace App\Helpers;

use App\CustomEmail;
use App\CustomerSecondaryUser;
use App\DraftQuatationProductHistory;
use App\ExportStatus;
use App\Exports\CompleteQuotationExport;
use App\Exports\DraftQuotationExport;
use App\Exports\invoicetableExport;
use App\GlobalAccessForRole;
use App\Helpers\DraftQuotationHelper;
use App\Helpers\MyHelper;
use App\Helpers\QuantityReservedHistory;
use App\Helpers\UpdateOrderQuotationDataHelper;
use App\Helpers\UpdateQuotationDataHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sales\OrderController;
use App\ImportFileHistory;
use App\Imports\AddProductToOrder;
use App\Imports\AddProductToTempQuotation;
use App\Imports\BulkImportForOrder;
use App\Imports\DraftQuotationImport;
use App\InvoiceSetting;
use App\Jobs\CancelledOrderJob;
use App\Jobs\InvoiceSaleExpJob;
use App\Models\Common\Bank;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Company;
use App\Models\Common\CompanyBank;
use App\Models\Common\Configuration;
use App\Models\Common\Country;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\OrderHistory;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\DraftQuotation;
use App\Models\Common\Order\DraftQuotationAttachment;
use App\Models\Common\Order\DraftQuotationNote;
use App\Models\Common\Order\DraftQuotationProduct;
use App\Models\Common\Order\DraftQuotationProductNote;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderAttachment;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PaymentType;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Unit;
use App\Models\Common\UserDetail;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\NotificationConfiguration;
use App\Notifications\DraftInvoiceQtyChangeNotification;
use App\PrintHistory;
use App\QuotationConfig;
use App\RoleMenu;
use App\User;
use App\Variable;
use App\Version;
use Auth;
use DateTime;
use Dompdf\Exception;
use Excel;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use PDF;
use Yajra\Datatables\Datatables;

class QuotationHelper
{
    public static function editCustomerForOrder($request)
    {
        $order = Order::find($request->order_id);
        $old_customer_id = $order->customer_id;
        $order_products = OrderProduct::where('order_id', $order->id)->whereNotNull('product_id')->get();
        $customer = Customer::find($request->customer_id);
        $customer_id = $request->customer_id;
        $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('is_default', 1)->first();
        $customerAddressShipping = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('is_default_shipping', 1)->first();
        if ($customerAddress) {
            $order->billing_address_id = $customerAddress->id;
            $order->shipping_address_id = $customerAddressShipping != null ? $customerAddressShipping->id : $customerAddress->id;
        } else {
            $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->first();
            if ($customerAddress) {
                $order->billing_address_id = $customerAddress->id;
                $order->shipping_address_id = $customerAddressShipping != null ? $customerAddressShipping->id : $customerAddress->id;
            }
        }
        $order->customer_id = $request->customer_id;
        $order->user_id = $customer->primary_sale_person->id;
        $order->save();
        $checkUserReport = User::find($order->user_id);
        if ($checkUserReport) {
            $order->dont_show = ($checkUserReport->is_include_in_reports == 0) ? 1 : 0;
            $order->save();
        }
        foreach ($order_products as $prod) {
            $product = Product::where('id', $prod->product_id)->where('status', 1)->first();
            $price_calculate_return = $product->price_calculate($product, $order);
            $price_type = $price_calculate_return[1];
            $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id', $product->id)->where('customer_type_id', $order->customer->category_id)->first();
            if ($CustomerTypeProductMargin != null) {
                $margin      = $CustomerTypeProductMargin->default_value;
                $margin = (($margin / 100) * $product->selling_price);
                $product_ref_price  = $margin + ($product->selling_price);
                $exp_unit_cost = $product_ref_price;
            }
            $prod->exp_unit_cost       = @$exp_unit_cost;
            $prod->margin              = $price_type;
            $prod->save();
        }
        (new QuotationHelper)->MakeHistory($order->id, null, 'BILL TO', $old_customer_id, $order->customer_id);
        $billing_address = null;
        $shipping_address = null;
        if ($order->billing_address_id != null) {
            $billing_address = CustomerBillingDetail::where('id', $order->billing_address_id)->first();
        }
        if ($order->shipping_address_id) {
            $shipping_address = CustomerBillingDetail::where('id', $order->shipping_address_id)->first();
        }
        $html = '
		<div class="customer-addresses">
		<div class="d-flex align-items-center mb-1">';
        if (@$order->customer->logo != null && file_exists(public_path() . '/uploads/sales/customer/logos/' . @$order->customer->logo)) {
            $html .= '<img src="' . asset('public/uploads/sales/customer/logos/' . @$order->customer->logo) . '" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">';
        } else {
            $html .= '<img src="' . asset('public/img/profileImg.jpg') . '" class="img-fluid mb-5" style="width: 60px;height: 60px;" align="big-qummy">';
        }
        $html .= '
		<div class="pl-2 comp-name" data-customer-id="' . $order->customer->id . '"><p><a href="' . url('sales/get-customer-detail/' . @$order->customer->id) . '" target="_blank">' . $order->customer->reference_name . '</a></p></div></div></div><div class="bill_body">';
        if ($billing_address != null) {
            $html .= "<p class='mb-2'><input type='hidden' value='1' name=''><i class='fa fa-edit edit-address mr-3' data-id=" . $order->customer_id . "></i><span>" . @$billing_address->title . "</span><br>" . @$billing_address->billing_address . "," . (@$order->customer->language ==  'en' ? $billing_address->getcountry->name : (@$billing_address->getcountry->thai_name != null ? @$billing_address->getcountry->thai_name : @$billing_address->getcountry->name)) . ",";
            if (@$order->customer->language == 'en') {
                $html .= @$billing_address->getstate->name . ',';
            } else {
                $html .= @$billing_address->getstate->thai_name != null ? @$billing_address->getstate->thai_name : @$billing_address->getstate->name . ',';
            }
            $html .= @$billing_address->billing_city . ', ' . @$billing_address->billing_zip . '</p>
			<ul class="d-flex list-unstyled">
			<li><a href="#"><i class="fa fa-phone pr-2"></i> ' . @$billing_address->billing_phone . '</a></li>
			<li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> ' . @$billing_address->billing_email . '</a></li>
			</ul>
			<ul class="d-flex list-unstyled">
			<li><b>Tax ID:</b>';
            if ($billing_address->tax_id !== null) {
                $html .= $billing_address->tax_id . '</li>
				</ul>';
            }
        } else {
            $html .=  '<p class="mb-2"><input type="hidden" value="1" name=""><i class="fa fa-edit edit-address" data-id="' . $order->customer_id . '"></i> ' . @$order->customer->address_line_1 . ' ' . @$order->customer->address_line_2 . ', ' . @$order->customer->getcountry->name . ', ' . @$order->customer->getstate->name . ', ' . @$order->customer->city . ', ' . @$order->customer->postalcode . '</p>
			<ul class="d-flex list-unstyled">
			<li><a href="#"><i class="fa fa-phone pr-2"></i> ' . $order->customer->phone . '</a></li>
			<li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> ' . $order->customer->email . '</a></li>
			</ul>';
        }
        $html .= '</div>';
        $shipping_html = '';
        if ($shipping_address != null) {
            $shipping_html = "<p class='mb-2'><input type='hidden' value='2' name=''><i class='fa fa-edit edit-address' data-id=" . $order->customer_id . "></i><span> " . @$shipping_address->title . "</span><br> " . @$shipping_address->billing_address . "," . (@$order->customer->language ==  'en' ? $shipping_address->getcountry->name : (@$shipping_address->getcountry->thai_name != null ? @$shipping_address->getcountry->thai_name : @$shipping_address->getcountry->name)) . ",";

            if (@$order->customer->language == 'en') {
                $shipping_html .= @$shipping_address->getstate->name . ",";
            } else {
                $shipping_html .= @$shipping_address->getstate->thai_name != null ? @$shipping_address->getstate->thai_name : @$shipping_address->getstate->name . ",";
            }
            $shipping_html .= @$shipping_address->billing_city . ", " . @$shipping_address->billing_zip . "</p>
			<ul class='d-flex list-unstyled'>
			<li><a href='#'><i class='fa fa-phone pr-2'></i> " . @$shipping_address->billing_phone . "</a></li>
			<li class='pl-3'> <a href='#'><i class='fa fa-envelope pr-2'></i> " . @$shipping_address->billing_email . "</a></li>
			</ul>";
        } else {
            $shipping_html .= "<p class='mb-2'><input type='hidden' value='2' name=''><i class='fa fa-edit edit-address' data-id=" . $order->customer_id . "></i> " . @$order->customer->address_line_1 . " " . @$order->customer->address_line_2 . ", " . @$order->customer->getcountry->name . ", " . @$order->customer->getstate->name . ", " . @$order->customer->city . ", " . @$order->customer->postalcode . "</p>
			<ul class='d-flex list-unstyled'>
			<li><a href='#'><i class='fa fa-phone pr-2'></i> " . $order->customer->phone . "</a></li>
			<li class='pl-3'> <a href='#'><i class='fa fa-envelope pr-2'></i> " . $order->customer->email . "</a></li>
			</ul>";
        }
        $sales_persons = Customer::with('primary_sale_person')->where('id', $customer_id)->first();
        $sale_person_name = $sales_persons->primary_sale_person != null ? $sales_persons->primary_sale_person->name : "";
        $sale_person_id = $sales_persons->primary_sale_person != null ? $sales_persons->primary_sale_person->id : "";
        $sales_person_html = '
		<optgroup label = "Primary Sale Person">
		<option value = "' . $sale_person_id . '">' . $sale_person_name . '</option>
		</optgroup>
		';
        $secondary_sales = CustomerSecondaryUser::where('customer_id', $customer_id)->get();
        if ($secondary_sales->count() != 0) {
            $sales_person_html .= '<optgroup label = "Secondary Sales Person">';
            foreach ($secondary_sales as $secondary) {
                $sales_person_html .= '
				<option value = "' . $secondary->user_id . '">' . $secondary->secondarySalesPersons->name . '</option>
				';
            }
            $sales_person_html .= '</optgroup>';
        }
        $order->user_id = $sales_persons->primary_sale_id;
        $order->save();
        return response()->json(['success' => true, 'html' => $html, 'shipping_html' => $shipping_html, 'sales_person_html' => $sales_person_html]);
    }

    public function MakeHistory($order_id, $reference_number, $column_name, $old_value, $new_value)
    {
        $order_history              = new OrderHistory;
        $order_history->user_id     = Auth::user()->id;
        $order_history->column_name = $column_name;
        $order_history->old_value   = $old_value;
        $order_history->new_value   = $new_value;
        $order_history->order_id    = $order_id;
        $order_history->reference_number    = $reference_number;
        $order_history->save();

        return $order_history;
    }

    public static function editCustomerAddressOnCompletedQuotation($request)
    {
        $order = Order::where('id', $request->order_id)->first();
        $address_id = $request->address_id;
        if ($request->previous) {
            if ($request->previous == 1) {
                $order->billing_address_id = $address_id;
            } else if ($request->previous == 2) {
                $order->shipping_address_id = $address_id;
            }
        }
        $order->save();
        $address = CustomerBillingDetail::where('id', $address_id)->first();
        $html =  '<p class="mb-2"><input type="hidden" value="' . $request->previous . '"><i class="fa fa-edit edit-address ml-1" data-id="' . @$order->customer_id . '"></i><span> ' . @$address->title . '</span><br>' . @$address->billing_address . ', ' . @$address->getcountry->name . ', ' . @$address->getstate->name . ', ' . @$address->billing_city . ', ' . @$address->billing_zip . '</p>
		<ul class="d-flex list-unstyled">
		<li><a href="#"><i class="fa fa-phone pr-2"></i> ' . @$address->billing_phone . '</a></li>
		<li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i>' . $address->billing_email . '</a></li>
		</ul>
		<ul class="d-flex list-unstyled">
		<li><b>Tax ID: </b>' . @$address->tax_id . '</li>
		</ul>';
        return response()->json(['html' => $html]);
    }

    public static function SaveOrderData($request)
    {
        $order = Order::find($request->order_id);
        foreach ($request->except('order_id', 'old_value') as $key => $value) {
            $customer_note = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
            $warehouse_note = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
            if ($key == 'comment') {
                if ($customer_note) {
                    $customer_note->note = $value;
                    $customer_note->save();
                } else {
                    $cust_note = new OrderNote;
                    $cust_note->order_id = $order->id;
                    $cust_note->note = $value;
                    $cust_note->type = 'customer';
                    $cust_note->save();
                }
                (new QuotationHelper)->MakeHistory($order->id, null, 'Remark', $request->old_value, $value);
                return response()->json(['success' => true, 'value' => 'remmark']);
            } elseif ($key == 'comment_warehouse') {
                if ($warehouse_note) {
                    $warehouse_note->note = $value;
                    $warehouse_note->save();
                } else {
                    $cust_note = new OrderNote;
                    $cust_note->order_id = $order->id;
                    $cust_note->note = $value;
                    $cust_note->type = 'warehouse';
                    $cust_note->save();
                    // $order_history->save();
                }
                (new QuotationHelper)->MakeHistory($order->id, null, 'Comment To Warehouse', $request->old_value, $value);
                return response()->json(['success' => true, 'value' => 'warehouse_comment']);
            } elseif ($key == 'delivery_request_date') {
                $value = str_replace("/", "-", $request->delivery_request_date);
                $value =  date('Y-m-d', strtotime($value));
                $order->$key = $value;
                if ($order->payment_terms_id !== null) {
                    $getCreditTerm = PaymentTerm::find($order->payment_terms_id);
                    $creditTerm = $getCreditTerm->title;
                    $int = intval(preg_replace('/[^0-9]+/', '', $creditTerm), 10);
                    if ($creditTerm == "COD") // today data if COD
                    {
                        $payment_due_date = $value;
                    }
                    $needle = "EOM";
                    if (strpos($creditTerm, $needle) !== false) {
                        $trdate = $value;
                        $getDayOnly = date('d', strtotime($trdate));
                        $extractMY = new DateTime($trdate);
                        $daysOfMonth = cal_days_in_month(CAL_GREGORIAN, (int)$extractMY->format('m'), $extractMY->format('Y'));
                        $subtractDays = $daysOfMonth - $getDayOnly;
                        $days = $int + $subtractDays;
                        $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                        $newdate = date("Y-m-d", $newdate);
                        $payment_due_date = $newdate;
                    } else {
                        $days = $int;
                        $trdate = $value;
                        $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                        $newdate = date("Y-m-d", $newdate);
                        $payment_due_date = $newdate;
                    }
                    $order->payment_due_date = $payment_due_date;
                }
            } elseif ($key == 'credit_note_date') {
                $value = str_replace("/", "-", $request->credit_note_date);
                $value =  date('Y-m-d', strtotime($value));
                $order->$key = $value;
            } elseif ($key == 'target_ship_date') {
                $value = str_replace("/", "-", $request->target_ship_date);
                $value =  date('Y-m-d', strtotime($value));
                $order->$key = $value;
            } elseif ($key == 'in_ref_id') {
                if ($value == null) {
                    $order->$key = $order->manual_ref_no;
                    $order->is_manual = 0;
                } else {
                    $order->$key = $value;
                    $order->is_manual = 1;
                }
                (new QuotationHelper)->MakeHistory($order->id, null, 'Manual Ref#', $request->old_value, $value);
            } elseif ($key == 'memo') {
                $order->$key = $value;
                (new QuotationHelper)->MakeHistory($order->id, null, 'Ref. Po #', $request->old_value, $value);
            } else {
                $order->$key = $value;
            }
        }
        $order->save();
        $sub_total     = 0;
        $sub_total_with_vat = 0;
        $query         = OrderProduct::where('order_id', $order->id)->get();
        foreach ($query as  $value) {
            $sub_total += $value->quantity * $value->unit_price;
            $sub_total_with_vat = $sub_total_with_vat + $value->total_price_with_vat;
        }
        $vat = $sub_total_with_vat - $sub_total;
        $total = ($sub_total) - ($order->discount) + ($order->shipping) + ($vat);
        $order->total_amount = $total;
        $order->save();
        $ref_no_gen = $order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
        return response()->json(['total' => number_format($total, 2, '.', ','), 'discount' => number_format($order->discount, 2, '.', ','), 'shipping' => number_format($order->shipping, 2, '.', ','), 'order' => $order, 'ref_no_gen' => $ref_no_gen]);
    }

    public static function paymentTermSaveInMyQuotation($request)
    {
        $my_qoutation = Order::find($request->order_id);
        $my_qoutation->payment_terms_id = $request->payment_terms_id;
        if ($my_qoutation->delivery_request_date != null) {
            $getCreditTerm = PaymentTerm::find($request->payment_terms_id);
            $creditTerm = $getCreditTerm->title;
            $int = intval(preg_replace('/[^0-9]+/', '', $creditTerm), 10);
            if ($creditTerm == "COD") // today data if COD
            {
                $payment_due_date = $my_qoutation->delivery_request_date;
            }
            $needle = "EOM";
            if (strpos($creditTerm, $needle) !== false) {
                $trdate = $my_qoutation->delivery_request_date;
                $getDayOnly = date('d', strtotime($trdate));
                $extractMY = new DateTime($trdate);
                $daysOfMonth = cal_days_in_month(CAL_GREGORIAN, (int)$extractMY->format('m'), $extractMY->format('Y'));
                $subtractDays = $daysOfMonth - $getDayOnly;
                $days = $int + $subtractDays;
                $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                $newdate = date("Y-m-d", $newdate);
                $payment_due_date = $newdate;
            } else {
                $days = $int;
                $trdate = $my_qoutation->delivery_request_date;
                $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                $newdate = date("Y-m-d", $newdate);
                $payment_due_date = $newdate;
            }
            $my_qoutation->payment_due_date = $payment_due_date;
        }
        $my_qoutation->save();
        return response()->json([
            'success' => true,
            'payment_due_date' => $my_qoutation->payment_due_date
        ]);
    }

    public static function fromWarehouseSaveInMyQuotation($request)
    {
        $my_qoutation = Order::find($request->order_id);
        $my_qoutation->from_warehouse_id = $request->from_warehouse_id;
        $order_products = OrderProduct::where('order_id', $request->order_id)->where('is_warehouse', 1)->where('is_billed', 'Product')->get();
        $order_products1 = OrderProduct::where('order_id', $request->order_id)->where('is_warehouse', 0)->where('is_billed', 'Product')->get();
        $user_warehouse = @Auth::user()->get_warehouse->id;
        foreach ($order_products as $prod) {
            if (@$my_qoutation->primary_status == 2) {
                DB::beginTransaction();
                try {
                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateReservedQuantity($prod, 'Reserved Deleted by Changing WAREHOUSE on order level', 'subtract');
                    DB::commit();
                } catch (\Excepion $e) {
                    DB::rollBack();
                }
            }
            if ($prod->supplier_id == null) {
                $prod->from_warehouse_id = $request->from_warehouse_id;
            }
            $prod->user_warehouse_id = $request->from_warehouse_id;
            $prod->warehouse_id = $request->from_warehouse_id;
            $prod->save();
            if ($my_qoutation->primary_status == 2) {
                $prod->status = ($user_warehouse == $prod->user_warehouse_id) ? 10 : 7;
                $prod->save();
                //To Update Rerserved Quantity
                if (@$my_qoutation->primary_status == 2) {
                    DB::beginTransaction();
                    try {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateReservedQuantity($prod, 'Reserved Quantity by changing WAREHOUSE on order level', 'add');
                        DB::commit();
                    } catch (\Excepion $e) {
                        DB::rollBack();
                    }
                }
                $order_status = $my_qoutation->order_products->where('is_billed', '=', 'Product')->min('status');
                $my_qoutation->status = $order_status;
                $my_qoutation->save();
            }
        }
        foreach ($order_products1 as $prod) {
            if (@$my_qoutation->primary_status == 2) {
                DB::beginTransaction();
                try {
                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateReservedQuantity($prod, 'Reserved Deleted by Changing WAREHOUSE on order level', 'subtract');
                    DB::commit();
                } catch (\Excepion $e) {
                    DB::rollBack();
                }
            }
            $prod->user_warehouse_id = $request->from_warehouse_id;
            $prod->warehouse_id = $request->from_warehouse_id;
            $prod->save();
            //To Update Rerserved Quantity
            if (@$my_qoutation->primary_status == 2) {
                DB::beginTransaction();
                try {
                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateReservedQuantity($prod, 'Reserved Quantity by changing WAREHOUSE on order level', 'add');
                    DB::commit();
                } catch (\Excepion $e) {
                    DB::rollBack();
                }
            }
            $order_status = $my_qoutation->order_products->where('is_billed', '=', 'Product')->min('status');
            $my_qoutation->status = $order_status;
            $my_qoutation->save();
        }
        $my_qoutation->save();
        return response()->json([
            'success' => true,
            'from_warehouse_id' => $request->from_warehouse_id
        ]);
    }

    public static function exportCompleteQuotation($request)
    {
        if ($request->type == 'example') {
            $query = null;
        } else {
            $query = OrderProduct::with('product', 'get_order', 'product.units', 'product.supplier_products')->where('order_id', $request->id);
            if ($request->column_name == 'reference_code' && $request->default_sort !== 'id_sort') {
                $query = $query->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $request->default_sort)->get();
            } elseif ($request->column_name == 'short_desc' && $request->default_sort !== 'id_sort') {
                $query = $query->orderBy('short_desc', $request->default_sort)->get();
            } elseif ($request->column_name == 'supply_from' && $request->default_sort !== 'id_sort') {
                $query = $query->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $request->default_sort)->get();
            } elseif ($request->column_name == 'type_id' && $request->default_sort !== 'id_sort') {
                $query = $query->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $request->default_sort)->get();
            } elseif ($request->column_name == 'brand' && $request->default_sort !== 'id_sort') {
                $query = $query->orderBy($request->column_name, $request->default_sort)->get();
            } else {
                $query = $query->orderBy('id', 'ASC')->get();
            }
        }
        $not_visible_arr = explode(',', $request->table_hide_columns);
        \Excel::store(new CompleteQuotationExport($query, $not_visible_arr), 'Order Export' . $request->id . '.xlsx');
        return response()->json(['success' => true]);
    }

    public static function exportToPDFExcVat($request, $id, $page_type, $column_name, $default_sort, $is_proforma, $bank_id)
    {
        $orders_array = explode(",", $id);
        $id = $orders_array[0];
        $config = Configuration::first();
        //To take history
        $history = PrintHistory::saveHistory($id, 'proforma', $page_type);
        //to get terminology
        $global_terminologies = PrintHistory::getTerminology(['qty', 'comment_to_customer']);
        $with_vat = @$request->with_vat;
        $proforma = @$is_proforma;
        $order = Order::find($id);
        $company_info = Company::where('id', $order->user->company_id)->first();
        $bank = Bank::find($bank_id);
        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $order_products = OrderProduct::select('order_products.*')->with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id);
        if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
            $order_products = $order_products->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        } elseif ($column_name == 'short_desc' && $default_sort !== 'id_sort') {
            $order_products = $order_products->orderBy('short_desc', $default_sort)->get();
        } elseif ($column_name == 'supply_from' && $default_sort !== 'id_sort') {
            $order_products = $order_products->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        } elseif ($column_name == 'type_id' && $default_sort !== 'id_sort') {
            $order_products = $order_products->select('order_products.*')->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
        } elseif ($column_name == 'brand' && $default_sort !== 'id_sort') {
            $order_products = $order_products->orderBy($column_name, $default_sort)->get();
        } else {
            $order_products = $order_products->orderBy('id', 'ASC')->get();
        }
        $query2 = null;
        $customPaper = array(0, 0, 576, 792);
        if (@$order->primary_status == 17) {
            $pdf = PDF::loadView('sales.invoice.quotation-invoice', compact('order', 'order_products', 'query2', 'company_info', 'with_vat', 'customerAddress', 'arr', 'config', 'global_terminologies'))->setPaper($customPaper);
            // making pdf name starts
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
            $makePdfName = $ref_no;
            return $pdf->download($makePdfName . '.pdf');
        } else {
            $getPrintBlade = Status::select('print_1')->where('id', 3)->first();

            if ($config->server == 'lucilla') {
                $pdf = PDF::loadView('sales.invoice.lucila-invoice-exc-vat', compact('order', 'query2', 'company_info', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'pages', 'bank', 'config', 'global_terminologies', 'order_products', 'default_sort', 'orders_array', 'column_name'))->setPaper($customPaper);
            }
            else{
                $pdf = PDF::loadView('sales.invoice.invoice-exc-vat', compact('order', 'query2', 'company_info', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'pages', 'bank', 'config', 'global_terminologies', 'order_products', 'default_sort', 'orders_array', 'column_name'))->setPaper($customPaper);
            }

            // making pdf name starts
            if ($order->primary_status == 3) {
                if (@$order->in_status_prefix !== null) {
                    $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                } else {
                    $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
                }
            } else {
                if (@$order->status_prefix !== null) {
                    $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                } else {
                    $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
                }
            }
            $makePdfName = $ref_no;
            // making pdf name ends
            return $pdf->stream(
                $makePdfName . '.pdf',
                array(
                    'Attachment' => 0
                )
            );
        }
    }
    public static function exportToPoPDFExcVat($request, $id, $page_type, $column_name, $default_sort, $is_proforma, $bank_id)
    {
        $orders_array = explode(",", $id);
        $id = $orders_array[0];
        $config = Configuration::first();
        //To take history
        $history = PrintHistory::saveHistory($id, 'proforma', $page_type);
        //to get terminology
        $global_terminologies = PrintHistory::getTerminology(['qty', 'comment_to_customer']);
        $with_vat = @$request->with_vat;
        $proforma = @$is_proforma;
        $order = Order::find($id);
        $company_info = Company::where('id', $order->user->company_id)->first();
        $bank = Bank::find($bank_id);
        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $order_products = OrderProduct::select('order_products.*')->with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id);
        if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
            $order_products = $order_products->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        } elseif ($column_name == 'short_desc' && $default_sort !== 'id_sort') {
            $order_products = $order_products->orderBy('short_desc', $default_sort)->get();
        } elseif ($column_name == 'supply_from' && $default_sort !== 'id_sort') {
            $order_products = $order_products->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        } elseif ($column_name == 'type_id' && $default_sort !== 'id_sort') {
            $order_products = $order_products->select('order_products.*')->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
        } elseif ($column_name == 'brand' && $default_sort !== 'id_sort') {
            $order_products = $order_products->orderBy($column_name, $default_sort)->get();
        } else {
            $order_products = $order_products->orderBy('id', 'ASC')->get();
        }
        $query2 = null;
        $customPaper = array(0, 0, 576, 792);
        if (@$order->primary_status == 17) {
            $pdf = PDF::loadView('sales.invoice.quotation-invoice', compact('order', 'order_products', 'query2', 'company_info', 'with_vat', 'customerAddress', 'arr', 'config', 'global_terminologies'))->setPaper($customPaper);
            // making pdf name starts
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
            $makePdfName = $ref_no;
            return $pdf->download($makePdfName . '.pdf');
        } else {
            $getPrintBlade = Status::select('print_1')->where('id', 3)->first();

            $pdf = PDF::loadView('sales.invoice.lucila-po-invoice-exc-vat', compact('order', 'query2', 'company_info', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'pages', 'bank', 'config', 'global_terminologies', 'order_products', 'default_sort', 'orders_array', 'column_name'))->setPaper($customPaper);

            // making pdf name starts
            if ($order->primary_status == 3) {
                if (@$order->in_status_prefix !== null) {
                    $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                } else {
                    $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
                }
            } else {
                if (@$order->status_prefix !== null) {
                    $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                } else {
                    $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
                }
            }
            $makePdfName = $ref_no;
            // making pdf name ends
            return $pdf->stream(
                $makePdfName . '.pdf',
                array(
                    'Attachment' => 0
                )
            );
        }
    }

    public static function exportToPDFIncVat($request, $id, $page_type, $column_name, $default_sort, $is_proforma, $bank)
    {
        $orders_array = explode(",", $id);
        $id = $orders_array[0];
        //To take history
        $history = PrintHistory::saveHistory($id, 'delivery-bill', $page_type);
        //to get terminology
        $global_terminologies = PrintHistory::getTerminology(['qty', 'product_description']);
        $do_pages_count = '';
        $all_orders_count = '';
        $last_page_to_show = '';
        $with_vat = @$request->with_vat;
        $proforma = @$is_proforma;
        $order = Order::find($id);
        $bank = Bank::find($bank);
        $company_info = Company::where('id', $order->user->company_id)->first();
        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $query2 = null;
        $customPaper = array(0, 0, 576, 792);
        $globalAccessConfig = QuotationConfig::where('section', 'quotation')->first();
        if ($globalAccessConfig) {
            if ($globalAccessConfig->print_prefrences != null) {
                $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
                foreach ($globalaccessForConfig as $val) {
                    if ($val['slug'] === "invoice_date_edit") {
                        $invoiceEditAllow = $val['status'];
                    }
                }
            } else {
                $invoiceEditAllow = '';
            }
        } else {
            $invoiceEditAllow = '';
        }
        $discount_1 = 0;
        $discount_2 = 0;
        $getPrintBlade = Status::select('print_1')->where('id', 3)->first();
        $config = Configuration::first();
        if ($config->server == 'lucilla'){
            $pdf = PDF::loadView('sales.invoice.lucila-invoice-inc-vat', compact('order', 'order_products', 'query2', 'company_info', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'pages', 'do_pages_count', 'final_pages', 'all_orders_count', 'bank', 'inv_note', 'config', 'invoiceEditAllow', 'global_terminologies', 'default_sort', 'orders_array', 'column_name'))->setPaper($customPaper);
        }
        else{
            $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_1 . '', compact('order', 'order_products', 'query2', 'company_info', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'pages', 'do_pages_count', 'final_pages', 'all_orders_count', 'bank', 'inv_note', 'config', 'invoiceEditAllow', 'global_terminologies', 'default_sort', 'orders_array', 'column_name'))->setPaper($customPaper);
        }
        // making pdf name starts
        if ($order->primary_status == 3) {
            if (@$order->in_status_prefix !== null) {
                $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        } else {
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        }
        $dynamic_path = '';
        $makePdfName = $ref_no;
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
    }

    public static function bulkImortInOrders($request)
    {
        $order = Order::find($request->order_id);
        $import = new BulkImportForOrder($request->order_id, $request->customer_id);
        try {
            $result = Excel::import($import, $request->file('product_excel'));
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'errors' => 'Please Upload Valid File']);
        }
        if ($order->status == 24 || $order->status == 31) {
            return response()->json(['paid' => true]);
        }
        if ($import->getErrors() != '' || $import->getErrors() != null) {
            return response()->json(['success' => false, 'errors' => $import->getErrors()]);
        }
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id, 'Order Detail Page', $request->file('product_excel'));
        return response()->json(['success' => $import->success, 'status' => @$import->status, 'sub_total' => number_format(@$import->sub_total, 2, '.', ','), 'total_vat' => number_format(@$import->total_vat, 2, '.', ','), 'grand_total' => number_format(@$import->grand_total, 2, '.', ','), 'errors' => $import->errors, 'sub_total_without_discount' => number_format(@$import->sub_total_without_discount, 2, '.', ','), 'item_level_dicount' => number_format(@$import->item_level_dicount, 2, '.', ','), 'remaining_amount' => number_format(@$import->remaining_amount, 2, '.', ',')]);
    }

    public static function getQuotationFiles($request)
    {
        $quotation_files = OrderAttachment::where('order_id', $request->quotation_id)->get();
        $html_string = '<div class="table-responsive">
    	<table class="table dot-dash text-center">
    	<thead class="dot-dash">
    	<tr>
    	<th>S.no</th>
    	<th>File</th>
    	<th>Action</th>
    	</tr>
    	</thead><tbody>';
        if ($quotation_files->count() > 0) {
            $i = 0;
            foreach ($quotation_files as $file) {
                $i++;
                $html_string .= '<tr id="quotation-file-' . $file->id . '">
    			<td>' . $i . '</td>
    			<td><a href="' . asset('public/uploads/documents/quotations/' . $file->file) . '" target="_blank">' . $file->file . '</a></td>
    			<td><a href="javascript:void(0);" data-id="' . $file->id . '" class="actionicon deleteIcon delete-quotation-file" title="Delete Quotation File"><i class="fa fa-trash"></i></a></td>
    			</tr>';
            }
        } else {
            $html_string .= '<tr>
    		<td colspan="3">No File Found</td>
    		</tr>';
        }
        $html_string .= '</tbody></table></div>';
        return $html_string;
    }

    public static function uploadOrderDocuments($request)
    {
        if (isset($request->order_docs)) {
            for ($i = 0; $i < sizeof($request->order_docs); $i++) {
                $order_doc        = new OrderAttachment;
                $order_doc->order_id = $request->order_id;
                //file
                $extension = $request->order_docs[$i]->extension();
                $filename = date('m-d-Y') . mt_rand(999, 999999) . '__' . time() . '.' . $extension;
                $request->order_docs[$i]->move(public_path('uploads/documents/quotations/'), $filename);
                $order_doc->file_title = $filename; //file title is now useless but it is not null so just to make it fill
                $order_doc->file = $filename;
                $order_doc->save();
            }
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public static function removeQuotationFile($request)
    {
        if (isset($request->id)) {
            // remove images from directory //
            $quotation_file = OrderAttachment::find($request->id);
            $directory  = public_path() . '/uploads/documents/quotations/';
            //remove main
            (new OrderController)->removeFile($directory, $quotation_file->file);
            // delete record
            $quotation_file->delete();
            return "done" . "-SEPARATOR-" . $request->id;
        }
    }

    public static function UpdateOrderQuotationData($request){
        DB::beginTransaction();
        try {
            $order_product = OrderProduct::find($request->order_id);
            $order = Order::find($order_product->order_id);
            if ($order->primary_status == 37) {
                DB::commit();
                return response()->json(['success' => false, 'manual_order' => true]);
            }
            $radio_click = @$request->old_value;
            $item_unit_price = number_format($order_product->unit_price, 2, '.', '');
            $supply_from = '';
            foreach ($request->except('order_id', 'old_value') as $key => $value) {
                if ($key == 'quantity') {
                    if ($key == 'quantity' && $order_product->product != null) {
                        $decimal_places = $order_product->product->sellingUnits->decimal_places;
                        $value = round($value, $decimal_places);
                    }
                    if ($order->primary_status == 2) {
                            $stock_q = $order->update_stock_card($order_product, $value);
                    }
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'qty';
                    }
                }
                else if ($key == 'number_of_pieces') {
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'pieces';
                    }
                }else if ($key == 'qty_shipped') {
                    if ($key == 'qty_shipped' && $order_product->product != null) {
                        $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
                        if ($confirm_from_draft) {
                            $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
                            foreach ($globalaccessForWarehouse as $val) {
                                if ($val['slug'] === "has_warehouse_account") {
                                    $has_warehouse_account = $val['status'];
                                }
                            }
                        } else {
                            $has_warehouse_account = '';
                        }
                        if ($has_warehouse_account != 1 && @$radio_click != 'clicked') {
                            return response()->json(['QtyNotUpdated' => true, 'msg' => 'Quantity is pulled from warehouse already for update contact to warehouse']);
                        }
                    }
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'qty';
                    }
                    if ($order_product->product_id != null){
                        if ($order->primary_status == 3 && @$radio_click !== 'clicked') {
                            /*This code is done only for invoices to update the stock while updating qty_shipped*/
                            $quantity_diff =  $order_product->qty_shipped - $value;
                            $quantity_diff = round($quantity_diff, 3);
                            $warehouse_id = $order_product->from_warehouse_id !== null ? $order_product->from_warehouse_id : Auth::user()->get_warehouse->id;
                            if ($order_product->expiration_date != null) {
                                $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                                if ($stock == null) {
                                    $stock = new StockManagementIn;
                                    $stock->title           = 'Adjustment';
                                    $stock->product_id      = $order_product->product_id;
                                    $stock->created_by      = Auth::user()->id;
                                    $stock->warehouse_id    = $warehouse_id;
                                    $stock->expiration_date = $order_product->expiration_date;
                                    $stock->save();
                                }
                                if ($stock != null) {
                                    $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $quantity_diff, $warehouse_id);

                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($quantity_diff, $stock, $stock_out, $order_product);
                                }
                            } else {
                                $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                                $shipped = $quantity_diff;
                                foreach ($stock as $st) {
                                    $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                                    $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                                    $balance = ($stock_out_in) + ($stock_out_out);
                                    $balance = round($balance, 3);
                                    if ($balance > 0) {
                                        $inStock = $balance + $shipped;
                                        if ($inStock >= 0) {
                                            $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, $warehouse_id);
                                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped, $st, $stock_out, $order_product);
                                            $shipped = 0;
                                            break;
                                        } else {
                                            $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, $warehouse_id, $balance);
                                            if ($shipped < 0) {
                                                //To find from which stock the order will be deducted
                                                $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                                                if ($find_stock->count() > 0) {
                                                    foreach ($find_stock as $out) {
                                                        if (abs($stock_out->available_stock) > 0) {
                                                            if ($out->available_stock >= abs($stock_out->available_stock)) {
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                $stock_out->available_stock = 0;
                                                            } else {
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                $out->available_stock = 0;
                                                            }
                                                            $out->save();
                                                            $stock_out->save();
                                                        }
                                                    }
                                                }
                                                $shipped = $inStock;
                                            } else {
                                                $find_stock = StockManagementOut::where('smi_id', $stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock', '<', 0)->get();
                                                if ($find_stock->count() > 0) {
                                                    foreach ($find_stock as $out) {
                                                        if ($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0) {
                                                            if ($stock_out->available_stock >= abs($out->available_stock)) {
                                                                $out->parent_id_in .= $stock_out->id . ',';
                                                                $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                                $out->available_stock = 0;
                                                            } else {
                                                                $out->parent_id_in .= $out->id . ',';
                                                                $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                                $stock_out->available_stock = 0;
                                                            }
                                                            $out->save();
                                                            $stock_out->save();
                                                        }
                                                    }
                                                    $shipped = abs($stock_out->available_stock);
                                                    $stock_out->available_stock = 0;
                                                    $stock_out->save();
                                                } else {
                                                    $shipped = $inStock;
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($shipped != 0) {
                                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNull('expiration_date')->first();
                                    if ($stock == null) {
                                        $stock = new StockManagementIn;
                                        $stock->title           = 'Adjustment';
                                        $stock->product_id      = $order_product->product_id;
                                        $stock->created_by      = Auth::user()->id;
                                        $stock->warehouse_id    = $warehouse_id;
                                        $stock->expiration_date = $order_product->expiration_date;
                                        $stock->save();
                                    }
                                    $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $shipped, $warehouse_id);
                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped, $stock, $stock_out, $order_product);
                                }
                            }
                                $new_his = new QuantityReservedHistory;
                                $re      = $new_his->updateCurrentQuantity($order_product, $quantity_diff, 'add', null);
                        }
                    }
                }
                else if ($key == 'pcs_shipped') {
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'pieces';
                    }
                }
                elseif ($key == 'from_warehouse_id') {
                    if ($order_product->purchase_order_detail !== null) {
                        return response()->json(['po_created' => true]);
                    } else {
                        $Stype = explode('-', $value);
                        if ($Stype[0] == 's') {
                            $w_user_id = $order_product->get_order->user_created->warehouse_id;
                            if (@$order->primary_status == 2) {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved delete because of changing SUPPLY FROM from Order Detail', 'subtract');
                            }
                            $order_product->supplier_id = $Stype[1];
                            $order_product->from_warehouse_id = null;
                            $order_product->user_warehouse_id = @$order->from_warehouse_id;
                            $order_product->is_warehouse = 0;
                            if (@$order->primary_status == 2) {
                                $order_product->status = 7;
                                $order_product->save();
                                $order_status = $order->order_products->where('is_billed', '=', 'Product')->min('status');
                                $order->status = $order_status;
                                $order->save();
                            }
                            if (@$order->primary_status == 2) {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved Quantity by changing SUPPLY FROM from Order Detail', 'add');
                            }
                            $supply_from = $order_product->from_supplier != null ? $order_product->from_supplier->reference_name : '--';
                        } else {
                            if (@$order->primary_status == 2) {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved delete because of changing SUPPLY FROM from Order Detail', 'subtract');
                            }
                            $user_warehouse = ($order_product->user_warehouse_id != null ? $order_product->user_warehouse_id : @Auth::user()->get_warehouse->id);
                            $order_product->from_warehouse_id = $order->from_warehouse_id;
                            $order_product->user_warehouse_id = $order->from_warehouse_id;
                            $order_product->is_warehouse = 1;
                            $order_product->supplier_id = null;
                            if ($order->primary_status == 2) {
                                if ($user_warehouse == $order_product->from_warehouse_id) {
                                    $order_product->status = 10;
                                    $order_product->save();
                                } else {
                                    $order_product->status = 7;
                                    $order_product->save();
                                }
                                $order_status = $order->order_products->where('is_billed', '=', 'Product')->min('status');

                                $order->status = $order_status;
                                $order->save();
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved Quantity by changing SUPPLY FROM from Order Detail', 'add');
                            }
                            $supply_from = 'Warehouse';
                        }
                    }
                }

                if ($key != 'from_warehouse_id') {
                    if($key == 'unit_price_with_vat'){
                        $value = number_format(@$value, 4, '.', '');
                        $send_value_vat = $value * (@$order_product->vat / 100);
                        $final_valuee = ($value * 100) / (100 + @$order_product->vat);
                        $final_value = number_format($final_valuee, 2, '.', '');
                        $order_product->unit_price = $final_value;
                    }
                    $order_product->$key = $value;
                    $order_product->save();
                    $calcu = DraftQuotationHelper::orderCalculation($order_product, $order);
                }
                $order_product->save();
                //taking history
                $hist = QuotationHelper::takeHistory($order_product, $request, $key, $value, $order);
                if ($order->status == 8 && $key == 'quantity' && $order_product->is_warehouse == 0 && auth()->user()->email != 'farooq@pkteam.com') {
                    $configuration = Configuration::whereNotNull('purchasing_email')->first();
                    if ($configuration) {
                        if ($order_history) {
                            (new OrderController)->notificationsAndEmails($order, $order_history, $order_product);
                        }
                    }
                }
            }
            $sub_total     = 0;
            $total_vat     = 0;
            $grand_total   = 0;
            $sub_total_w_w   = 0;
            $sub_total_without_discount = 0;
            $item_level_dicount = 0;
            $query         = OrderProduct::where('order_id', $order_product->order_id)->get();
            foreach ($query as  $value) {
                $sub_total += $value->total_price;
                $sub_total_w_w += number_format($value->total_price_with_vat, 4, '.', '');
                $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 4) : (@$value->total_price_with_vat - @$value->total_price);
                if ($value->discount != 0) {
                    if ($value->discount == 100) {
                        if (@$order->primary_status == 3) {
                            if ($value->is_retail == 'pieces') {
                                $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                                $sub_total_without_discount += $discount_full;
                            } else {
                                $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                                $sub_total_without_discount += $discount_full;
                            }
                        } else {
                            if ($value->is_retail == 'pieces') {
                                $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                                $sub_total_without_discount += $discount_full;
                            } else {
                                $discount_full =  $value->unit_price_with_vat * $value->quantity;
                                $sub_total_without_discount += $discount_full;
                            }
                        }
                        $item_level_dicount += $discount_full;
                    } else {
                        $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                        $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                    }
                } else {
                    $sub_total_without_discount += $value->total_price;
                }
            }
            $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);

            $order_product->get_order->update(['total_amount' => number_format($grand_total, 2, '.', '')]);
            //item level calculations
            $total_amount_wo_vat = number_format((float)$order_product->total_price, 2, '.', '');
            $total_amount_w_vat = number_format((float)$order_product->total_price_with_vat, 4, '.', '');
            $unit_price_after_discount = $order_product->unit_price_with_discount != null ?  number_format($order_product->unit_price_with_discount, 2, '.', '') : '--';
            $unit_price = $order_product->unit_price != null ?  number_format($order_product->unit_price, 2, '.', '') : '--';
            $unit_price_w_vat = $order_product->unit_price_with_vat != null ?  number_format($order_product->unit_price_with_vat, 2, '.', '') : '--';
            $quantity = round($order_product->quantity, 4);
            $pcs = round($order_product->number_of_pieces, 4);
            if ($order_product->supplier_id != null) {
                $supply_from = $order_product->from_supplier != null ? $order_product->from_supplier->reference_name : '--';
            } else {
                $supply_from = 'Warehouse';
            }
            $type = $order_product->productType != null ? $order_product->productType->title : 'Select';
            $qty_shipped = round($order_product->qty_shipped, 4);
            $pcs_shipped = round($order_product->pcs_shipped, 4);
            DB::commit();
            return response()->json(['success' => true, 'status' => $order->statuses->title, 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','), 'sub_total_without_discount' => number_format(floor(@$sub_total_without_discount * 100) / 100, 2, '.', ','), 'item_level_dicount' => number_format(floor(@$item_level_dicount * 100) / 100, 2, '.', ','), 'total_amount_wo_vat' => $total_amount_wo_vat, 'total_amount_w_vat' => $total_amount_w_vat, 'id' => $order_product->id, 'unit_price_after_discount' => $unit_price_after_discount, 'unit_price' => $unit_price, 'unit_price_w_vat' => $unit_price_w_vat, 'supply_from' => $supply_from, 'quantity' => $quantity, 'pcs' => $pcs, 'type' => $type, 'type_id' => $order_product->type_id, 'qty_shipped' => $qty_shipped, 'pcs_shipped' => $pcs_shipped]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public static function UpdateOrderQuotationDataOld($request)
    {
        DB::beginTransaction();
        try {
            $order_product = OrderProduct::find($request->order_id);
            $order = Order::find($order_product->order_id);
            if ($order->primary_status == 37) {
                DB::commit();
                return response()->json(['success' => false, 'manual_order' => true]);
            }
            $radio_click = @$request->old_value;
            $item_unit_price = number_format($order_product->unit_price, 2, '.', '');
            $supply_from = '';
            foreach ($request->except('order_id', 'old_value') as $key => $value) {
                if ($key == 'quantity') {
                    if ($key == 'quantity' && $order_product->product != null) {
                        $decimal_places = $order_product->product->sellingUnits->decimal_places;
                        $value = round($value, $decimal_places);
                    }
                    if ($order->primary_status == 2) {
                            $stock_q = $order->update_stock_card($order_product, $value);
                    }
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'qty';
                    }
                    if (@$order_product->is_retail == 'qty') {
                        if ($order_product->product_id == null) {
                            $total_price = $item_unit_price * $value;
                            $discount = $order_product->discount;
                            if ($discount != null) {
                                $dis = $discount / 100;
                                $discount_value = $dis * $total_price;
                                $result = $total_price - $discount_value;
                            } else {
                                $result = $total_price;
                            }
                            $order_product->total_price = round($result, 2);
                            $vat = $order_product->vat;
                            $vat_amountt = @$item_unit_price * (@$vat / 100);
                            $vat_amount = number_format($vat_amountt, 4, '.', '');
                            $vat_amount_total_over_item = $vat_amount * $value;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                            if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            } else {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            }
                            if (@$discount !== null) {
                                $percent_value = $discount / 100;
                                $dis_value = $unit_price_with_vat * $percent_value;
                                $tpwt = $unit_price_with_vat - @$dis_value;

                                $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                            } else {
                                $tpwt = $unit_price_with_vat;
                            }
                            $order_product->total_price_with_vat = @$tpwt;
                            if ($order_product->is_billed == 'Billed') {
                                $order_product->qty_shipped = $value;
                            }
                            $order_product->save();
                        } else {
                            $total_price = $item_unit_price * $value;
                            $discount = $order_product->discount;
                            if ($discount != null) {
                                $dis = $discount / 100;
                                $discount_value = $dis * $total_price;
                                $result = $total_price - $discount_value;
                            } else {
                                $result = $total_price;
                            }
                            $order_product->total_price = round($result, 2);
                            $vat = $order_product->vat;
                            $vat_amountt = @$item_unit_price * (@$vat / 100);
                            $vat_amount = number_format($vat_amountt, 4, '.', '');
                            $vat_amount_total_over_item = $vat_amount * $value;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');

                            if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            } else {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            }
                            if (@$discount !== null) {
                                $percent_value = $discount / 100;
                                $dis_value = $unit_price_with_vat * $percent_value;
                                $tpwt = $unit_price_with_vat - @$dis_value;

                                $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                            } else {
                                $tpwt = $unit_price_with_vat;
                            }
                            $order_product->total_price_with_vat = @$tpwt;
                            if ($order_product->is_billed == 'Billed') {
                                $order_product->qty_shipped = $value;
                            }
                            $order_product->save();
                        }
                    }
                } else if ($key == 'number_of_pieces') {
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'pieces';
                    }
                    if (@$order_product->is_retail == 'pieces') {
                        if ($order_product->product_id == null) {
                            $total_price = $item_unit_price * $value;
                            $discount = $order_product->discount;
                            if ($discount != null) {
                                $dis = $discount / 100;
                                $discount_value = $dis * $total_price;
                                $result = $total_price - $discount_value;
                            } else {
                                $result = $total_price;
                            }
                            $order_product->total_price = round($result, 2);
                            $vat = $order_product->vat;
                            $vat_amountt = @$item_unit_price * (@$vat / 100);
                            $vat_amount = number_format($vat_amountt, 4, '.', '');
                            $vat_amount_total_over_item = $vat_amount * $value;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                            if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            } else {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            }
                            if (@$discount !== null) {
                                $percent_value = $discount / 100;
                                $dis_value = $unit_price_with_vat * $percent_value;
                                $tpwt = $unit_price_with_vat - @$dis_value;

                                $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                            } else {
                                $tpwt = $unit_price_with_vat;
                            }
                            $order_product->total_price_with_vat = round(@$tpwt, 2);
                            $order_product->save();
                        } else {
                            $total_price = $item_unit_price * $value;
                            $discount = $order_product->discount;

                            if ($discount != null) {
                                $dis = $discount / 100;
                                $discount_value = $dis * $total_price;
                                $result = $total_price - $discount_value;
                            } else {
                                $result = $total_price;
                            }
                            $order_product->total_price = round($result, 2);
                            $vat = $order_product->vat;
                            $vat_amountt = @$item_unit_price * (@$vat / 100);
                            $vat_amount = number_format($vat_amountt, 4, '.', '');
                            $vat_amount_total_over_item = $vat_amount * $value;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                            if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            } else {
                                $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                            }
                            if (@$discount !== null) {
                                $percent_value = $discount / 100;
                                $dis_value = $unit_price_with_vat * $percent_value;
                                $tpwt = $unit_price_with_vat - @$dis_value;
                                $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                            } else {
                                $tpwt = $unit_price_with_vat;
                            }
                            $order_product->total_price_with_vat = round(@$tpwt, 2);
                            $order_product->save();
                        }

                        // Sup-739 Funcionality. Order Qty per piece comes from product detail page and change qty inv column when value enteres in pcs column
                        $order_qty_per_piece = $order_product->product->order_qty_per_piece;
                        if ($order_qty_per_piece != null && $order_qty_per_piece != 0 && $order_qty_per_piece != '0' && $value != null && $value != 0 && $value != "0")
                        {
                            $order_qty_per_piece_value =  $value * $order_qty_per_piece;
                            $order_product->quantity = $order_qty_per_piece_value;
                        }
                    }
                    else if(@$order_product->is_retail == 'qty'){
                        $order_qty_per_piece = $order_product->product->order_qty_per_piece;
                        if ($order_qty_per_piece != null && $order_qty_per_piece != 0 && $order_qty_per_piece != '0' && $value != null && $value != 0 && $value != "0")
                        {
                            $order_qty_per_piece_value =  $value * $order_qty_per_piece;
                            $order_product->quantity = $order_qty_per_piece_value;

                            if ($order_product->product_id == null) {
                                $total_price = $item_unit_price * $order_qty_per_piece_value;
                                $discount = $order_product->discount;
                                if ($discount != null) {
                                    $dis = $discount / 100;
                                    $discount_value = $dis * $total_price;
                                    $result = $total_price - $discount_value;
                                } else {
                                    $result = $total_price;
                                }
                                $order_product->total_price = round($result, 2);
                                $vat = $order_product->vat;
                                $vat_amountt = @$item_unit_price * (@$vat / 100);
                                $vat_amount = number_format($vat_amountt, 4, '.', '');
                                $vat_amount_total_over_item = $vat_amount * $order_qty_per_piece_value;
                                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                                if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                                } else {
                                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                                }
                                if (@$discount !== null) {
                                    $percent_value = $discount / 100;
                                    $dis_value = $unit_price_with_vat * $percent_value;
                                    $tpwt = $unit_price_with_vat - @$dis_value;

                                    $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                    $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                    $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                                } else {
                                    $tpwt = $unit_price_with_vat;
                                }
                                $order_product->total_price_with_vat = @$tpwt;
                                if ($order_product->is_billed == 'Billed') {
                                    $order_product->qty_shipped = $order_qty_per_piece_value;
                                }
                                $order_product->save();
                            } else {
                                $total_price = $item_unit_price * $order_qty_per_piece_value;
                                $discount = $order_product->discount;
                                if ($discount != null) {
                                    $dis = $discount / 100;
                                    $discount_value = $dis * $total_price;
                                    $result = $total_price - $discount_value;
                                } else {
                                    $result = $total_price;
                                }
                                $order_product->total_price = round($result, 2);
                                $vat = $order_product->vat;
                                $vat_amountt = @$item_unit_price * (@$vat / 100);
                                $vat_amount = number_format($vat_amountt, 4, '.', '');
                                $vat_amount_total_over_item = $vat_amount * $order_qty_per_piece_value;
                                $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');

                                if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                                } else {
                                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                                }
                                if (@$discount !== null) {
                                    $percent_value = $discount / 100;
                                    $dis_value = $unit_price_with_vat * $percent_value;
                                    $tpwt = $unit_price_with_vat - @$dis_value;

                                    $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                    $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                    $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                                } else {
                                    $tpwt = $unit_price_with_vat;
                                }
                                $order_product->total_price_with_vat = @$tpwt;
                                if ($order_product->is_billed == 'Billed') {
                                    $order_product->qty_shipped = $order_qty_per_piece_value;
                                }
                                $order_product->save();
                            }
                        }
                    }
                    $order_product->save();
                }  else if ($key == 'qty_shipped') {
                    if ($key == 'qty_shipped' && $order_product->product != null) {
                        $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
                        if ($confirm_from_draft) {
                            $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
                            foreach ($globalaccessForWarehouse as $val) {
                                if ($val['slug'] === "has_warehouse_account") {
                                    $has_warehouse_account = $val['status'];
                                }
                            }
                        } else {
                            $has_warehouse_account = '';
                        }
                        if ($has_warehouse_account != 1 && @$radio_click != 'clicked') {
                            return response()->json(['QtyNotUpdated' => true, 'msg' => 'Quantity is pulled from warehouse already for update contact to warehouse']);
                        }
                    }
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'qty';
                    }
                    if ($order_product->product_id == null) {
                        if (@$order_product->is_retail == 'pieces') {
                            $num = @$order_product->pcs_shipped;
                        } else {
                            $num = $value;
                        }
                        $total_price = $item_unit_price * $num;
                        $discount = $order_product->discount;
                        if ($discount != null) {
                            $dis = $discount / 100;
                            $discount_value = $dis * $total_price;
                            $result = $total_price - $discount_value;
                        } else {
                            $result = $total_price;
                        }
                        $order_product->total_price = round($result, 2);
                        $vat = $order_product->vat;
                        $vat_amountt = @$item_unit_price * (@$vat / 100);
                        $vat_amount = number_format($vat_amountt, 4, '.', '');
                        $vat_amount_total_over_item = $vat_amount * $num;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        } else {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        }
                        if (@$discount !== null) {
                            $percent_value = $discount / 100;
                            $dis_value = $unit_price_with_vat * $percent_value;
                            $tpwt = $unit_price_with_vat - @$dis_value;

                            $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                            $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        } else {
                            $tpwt = $unit_price_with_vat;
                        }
                        $order_product->total_price_with_vat = round(@$tpwt, 2);
                        $order_product->save();
                    } else {
                        if ($order->primary_status == 3 && @$radio_click !== 'clicked') {
                            /*This code is done only for invoices to update the stock while updating qty_shipped*/
                            $quantity_diff =  $order_product->qty_shipped - $value;
                            $quantity_diff = round($quantity_diff, 3);
                            $warehouse_id = $order_product->from_warehouse_id !== null ? $order_product->from_warehouse_id : Auth::user()->get_warehouse->id;
                            if ($order_product->expiration_date != null) {
                                $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                                if ($stock == null) {
                                    $stock = new StockManagementIn;
                                    $stock->title           = 'Adjustment';
                                    $stock->product_id      = $order_product->product_id;
                                    $stock->created_by      = Auth::user()->id;
                                    $stock->warehouse_id    = $warehouse_id;
                                    $stock->expiration_date = $order_product->expiration_date;
                                    $stock->save();
                                }
                                if ($stock != null) {
                                    $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $quantity_diff, $warehouse_id);

                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($quantity_diff, $stock, $stock_out, $order_product);
                                }
                            } else {
                                $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                                $shipped = $quantity_diff;
                                foreach ($stock as $st) {
                                    $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                                    $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                                    $balance = ($stock_out_in) + ($stock_out_out);
                                    $balance = round($balance, 3);
                                    if ($balance > 0) {
                                        $inStock = $balance + $shipped;
                                        if ($inStock >= 0) {
                                            $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, $warehouse_id);
                                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped, $st, $stock_out, $order_product);
                                            $shipped = 0;
                                            break;
                                        } else {
                                            $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, $warehouse_id, $balance);
                                            if ($shipped < 0) {
                                                //To find from which stock the order will be deducted
                                                $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                                                if ($find_stock->count() > 0) {
                                                    foreach ($find_stock as $out) {
                                                        if (abs($stock_out->available_stock) > 0) {
                                                            if ($out->available_stock >= abs($stock_out->available_stock)) {
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                $stock_out->available_stock = 0;
                                                            } else {
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                $out->available_stock = 0;
                                                            }
                                                            $out->save();
                                                            $stock_out->save();
                                                        }
                                                    }
                                                }
                                                $shipped = $inStock;
                                            } else {
                                                $find_stock = StockManagementOut::where('smi_id', $stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock', '<', 0)->get();
                                                if ($find_stock->count() > 0) {
                                                    foreach ($find_stock as $out) {
                                                        if ($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0) {
                                                            if ($stock_out->available_stock >= abs($out->available_stock)) {
                                                                $out->parent_id_in .= $stock_out->id . ',';
                                                                $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                                $out->available_stock = 0;
                                                            } else {
                                                                $out->parent_id_in .= $out->id . ',';
                                                                $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                                $stock_out->available_stock = 0;
                                                            }
                                                            $out->save();
                                                            $stock_out->save();
                                                        }
                                                    }
                                                    $shipped = abs($stock_out->available_stock);
                                                    $stock_out->available_stock = 0;
                                                    $stock_out->save();
                                                } else {
                                                    $shipped = $inStock;
                                                }
                                            }
                                        }
                                    }
                                }
                                if ($shipped != 0) {
                                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNull('expiration_date')->first();
                                    if ($stock == null) {
                                        $stock = new StockManagementIn;
                                        $stock->title           = 'Adjustment';
                                        $stock->product_id      = $order_product->product_id;
                                        $stock->created_by      = Auth::user()->id;
                                        $stock->warehouse_id    = $warehouse_id;
                                        $stock->expiration_date = $order_product->expiration_date;
                                        $stock->save();
                                    }
                                    $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $shipped, $warehouse_id);
                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped, $stock, $stock_out, $order_product);
                                }
                            }
                                $new_his = new QuantityReservedHistory;
                                $re      = $new_his->updateCurrentQuantity($order_product, $quantity_diff, 'add', null);
                        }
                        if (@$order_product->is_retail == 'pieces') {
                            $num = @$order_product->pcs_shipped;
                        } else {
                            $num = $value;
                        }
                        $total_price = $item_unit_price * $num;
                        $discount = $order_product->discount;
                        if ($discount != null) {
                            $dis = $discount / 100;
                            $discount_value = $dis * $total_price;
                            $result = $total_price - $discount_value;
                        } else {
                            $result = $total_price;
                        }
                        $order_product->total_price = round($result, 2);
                        $vat = $order_product->vat;
                        $vat_amountt = @$item_unit_price * (@$vat / 100);
                        $vat_amount = number_format($vat_amountt, 4, '.', '');
                        $vat_amount_total_over_item = $vat_amount * $num;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        } else {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        }
                        if (@$discount !== null) {
                            $percent_value = $discount / 100;
                            $dis_value = $unit_price_with_vat * $percent_value;
                            $tpwt = $unit_price_with_vat - @$dis_value;

                            $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                            $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        } else {
                            $tpwt = $unit_price_with_vat;
                        }
                        $order_product->total_price_with_vat = round(@$tpwt, 2);
                        $order_product->save();
                    }
                } else if ($key == 'pcs_shipped') {
                    if (@$radio_click == 'clicked') {
                        $order_product->is_retail = 'pieces';
                    }
                    if ($order_product->product_id == null) {
                        if (@$order_product->is_retail == 'qty') {
                            $num = @$order_product->qty_shipped;
                        } else {
                            $num = $value;
                        }
                        $total_price = $item_unit_price * $num;
                        $discount = $order_product->discount;
                        if ($discount != null) {
                            $dis = $discount / 100;
                            $discount_value = $dis * $total_price;
                            $result = $total_price - $discount_value;
                        } else {
                            $result = $total_price;
                        }
                        $order_product->total_price = round($result, 2);
                        $vat = $order_product->vat;
                        $vat_amountt = @$item_unit_price * (@$vat / 100);
                        $vat_amount = number_format($vat_amountt, 4, '.', '');
                        $vat_amount_total_over_item = $vat_amount * $num;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        } else {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        }
                        if (@$discount !== null) {
                            $percent_value = $discount / 100;
                            $dis_value = $unit_price_with_vat * $percent_value;
                            $tpwt = $unit_price_with_vat - @$dis_value;
                            $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                            $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        } else {
                            $tpwt = $unit_price_with_vat;
                        }
                        $order_product->total_price_with_vat = round(@$tpwt, 2);
                        $order_product->save();
                    } else {
                        if (@$order_product->is_retail == 'qty') {
                            $num = @$order_product->qty_shipped;
                        } else {
                            $num = $value;
                        }
                        $total_price = $item_unit_price * $num;
                        $discount = $order_product->discount;
                        if ($discount != null) {
                            $dis = $discount / 100;
                            $discount_value = $dis * $total_price;
                            $result = $total_price - $discount_value;
                        } else {
                            $result = $total_price;
                        }
                        $order_product->total_price = round($result, 2);
                        $vat = $order_product->vat;
                        $vat_amountt = @$item_unit_price * (@$vat / 100);
                        $vat_amount = number_format($vat_amountt, 4, '.', '');
                        $vat_amount_total_over_item = $vat_amount * $num;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        } else {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        }
                        if (@$discount !== null) {
                            $percent_value = $discount / 100;
                            $dis_value = $unit_price_with_vat * $percent_value;
                            $tpwt = $unit_price_with_vat - @$dis_value;
                            $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                            $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        } else {
                            $tpwt = $unit_price_with_vat;
                        }
                        $order_product->total_price_with_vat = round(@$tpwt, 2);
                        $order_product->save();
                    }
                } elseif ($key == 'unit_price') {
                    if (@$order->primary_status == 3) {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->qty_shipped;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->pcs_shipped;
                        }
                    } else {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->quantity;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->number_of_pieces;
                        }
                    }
                    if ($order_product->product_id == null) {
                        $total_pricee = @$quantity * number_format($value, 2, '.', '');
                        $total_price = $total_pricee;
                        $discount = $order_product->discount;
                        if ($discount != null) {
                            $dis = $discount / 100;
                            $discount_value = $dis * $total_price;
                            $result = $total_price - $discount_value;
                        } else {
                            $result = $total_price;
                        }
                        $order_product->total_price = round($result, 2);
                        $unit_price = number_format($value, 2, '.', '');
                        $vat = $order_product->vat;
                        $vat_amountt = @$unit_price * (@$vat / 100);
                        $vat_amount = number_format($vat_amountt, 4, '.', '');
                        $vat_amount_total_over_item = $vat_amount * $quantity;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        $order_product->unit_price_with_vat = number_format($unit_price + $vat_amount, 2, '.', '');
                        $order_product->save();
                        if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        } else {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        }
                        if (@$discount !== null) {
                            $percent_value = $discount / 100;
                            $dis_value = $unit_price_with_vat * $percent_value;
                            $tpwt = $unit_price_with_vat - @$dis_value;
                            $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                            $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        } else {
                            $tpwt = $unit_price_with_vat;
                        }
                        $order_product->total_price_with_vat = round(@$tpwt, 2);
                        $order_product->save();
                    } else {
                        $total_pricee = @$quantity * number_format($value, 2, '.', '');
                        $total_price = $total_pricee;
                        $discount = $order_product->discount;
                        if ($discount != null) {
                            $dis = $discount / 100;
                            $discount_value = $dis * $total_price;
                            $result = $total_price - $discount_value;
                        } else {
                            $result = $total_price;
                        }
                        $order_product->total_price = round($result, 2);
                        $unit_price = number_format($value, 2, '.', '');
                        $vat = $order_product->vat;
                        $vat_amountt = @$unit_price * (@$vat / 100);
                        $vat_amount = number_format($vat_amountt, 4, '.', '');
                        $vat_amount_total_over_item = $vat_amount * $quantity;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        $order_product->unit_price_with_vat = number_format($unit_price + $vat_amount, 2, '.', '');
                        $order_product->save();
                        if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        } else {
                            $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                        }
                        if (@$discount !== null) {
                            $percent_value = $discount / 100;
                            $dis_value = $unit_price_with_vat * $percent_value;
                            $tpwt = $unit_price_with_vat - @$dis_value;
                            $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                            $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                            $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                        } else {
                            $tpwt = $unit_price_with_vat;
                        }
                        $order_product->total_price_with_vat = round(@$tpwt, 2);
                        $order_product->save();
                    }
                } elseif ($key == 'unit_price_with_vat') {
                    $value = number_format(@$value, 4, '.', '');
                    $send_value_vat = $value * (@$order_product->vat / 100);
                    $final_valuee = ($value * 100) / (100 + @$order_product->vat);
                    $final_value = number_format($final_valuee, 2, '.', '');
                    $order_product->unit_price = $final_value;
                    if (@$order->primary_status == 3) {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->qty_shipped;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->pcs_shipped;
                        }
                    } else {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->quantity;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->number_of_pieces;
                        }
                    }
                    $total_price = $final_value * $quantity;
                    $discount = $order_product->discount;
                    if ($discount != null) {
                        $dis = $discount / 100;
                        $discount_value = $dis * $total_price;
                        $result = $total_price - $discount_value;
                    } else {
                        $result = $total_price;
                    }
                    $order_product->total_price = round($result, 2);
                    $unit_price = @$final_value;
                    $vat = $order_product->vat;
                    $vat_amountt = @$unit_price * (@$vat / 100);
                    $vat_amount = number_format($vat_amountt, 4, '.', '');
                    $vat_amount_total_over_item = $vat_amount * $quantity;
                    $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                    $order_product->$key = $unit_price + $vat_amount;
                    $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                    if (@$discount !== null) {
                        $percent_value = $discount / 100;
                        $dis_value = $unit_price_with_vat * $percent_value;
                        $tpwt = $unit_price_with_vat - @$dis_value;
                        $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                        $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                    } else {
                        $tpwt = $unit_price_with_vat;
                    }
                    $order_product->total_price_with_vat = round(@$tpwt, 2);
                } elseif ($key == 'vat') {
                    if (@$order->primary_status == 3) {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->qty_shipped;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->pcs_shipped;
                        }
                    } else {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->quantity;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->number_of_pieces;
                        }
                    }
                    $total_price = $item_unit_price * $quantity;
                    $discount = $order_product->discount;
                    if ($discount != null) {
                        $dis = $discount / 100;
                        $discount_value = $dis * $total_price;
                        $result = $total_price - $discount_value;
                    } else {
                        $result = $total_price;
                    }
                    $order_product->total_price = round($result, 2);
                    $vat_amountt = @$item_unit_price * (@$value / 100);
                    $vat_amount = number_format($vat_amountt, 4, '.', '');
                    $vat_amount_total_over_item = $vat_amount * $quantity;
                    $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                    $order_product->unit_price_with_vat = number_format($item_unit_price + $vat_amount, 2, '.', '');
                    $order_product->save();
                    if ($order_product->unit_price_with_vat !== null && $value !== 0) {
                        $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                    } else {
                        $unit_price_with_vat = round($total_price, 2) + round($vat_amount_total_over_item, 2);
                    }
                    if (@$discount !== null) {
                        $percent_value = $discount / 100;
                        $dis_value = $unit_price_with_vat * $percent_value;
                        $tpwt = $unit_price_with_vat - @$dis_value;
                        $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                        $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                    } else {
                        $tpwt = $unit_price_with_vat;
                    }
                    $order_product->total_price_with_vat = round(@$tpwt, 2);
                    $order_product->save();
                } elseif ($key == 'from_warehouse_id') {
                    if ($order_product->purchase_order_detail !== null) {
                        return response()->json(['po_created' => true]);
                    } else {
                        $Stype = explode('-', $value);
                        if ($Stype[0] == 's') {
                            $w_user_id = $order_product->get_order->user_created->warehouse_id;
                            if (@$order->primary_status == 2) {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved delete because of changing SUPPLY FROM from Order Detail', 'subtract');
                            }
                            $order_product->supplier_id = $Stype[1];
                            $order_product->from_warehouse_id = null;
                            $order_product->user_warehouse_id = @$order->from_warehouse_id;
                            $order_product->is_warehouse = 0;
                            if (@$order->primary_status == 2) {
                                $order_product->status = 7;
                                $order_product->save();
                                $order_status = $order->order_products->where('is_billed', '=', 'Product')->min('status');
                                $order->status = $order_status;
                                $order->save();
                            }
                            if (@$order->primary_status == 2) {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved Quantity by changing SUPPLY FROM from Order Detail', 'add');
                            }
                            $supply_from = $order_product->from_supplier != null ? $order_product->from_supplier->reference_name : '--';
                        } else {
                            if (@$order->primary_status == 2) {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved delete because of changing SUPPLY FROM from Order Detail', 'subtract');
                            }
                            $user_warehouse = ($order_product->user_warehouse_id != null ? $order_product->user_warehouse_id : @Auth::user()->get_warehouse->id);
                            $order_product->from_warehouse_id = $order->from_warehouse_id;
                            $order_product->user_warehouse_id = $order->from_warehouse_id;
                            $order_product->is_warehouse = 1;
                            $order_product->supplier_id = null;
                            if ($order->primary_status == 2) {
                                if ($user_warehouse == $order_product->from_warehouse_id) {
                                    $order_product->status = 10;
                                    $order_product->save();
                                } else {
                                    $order_product->status = 7;
                                    $order_product->save();
                                }
                                $order_status = $order->order_products->where('is_billed', '=', 'Product')->min('status');

                                $order->status = $order_status;
                                $order->save();
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Reserved Quantity by changing SUPPLY FROM from Order Detail', 'add');
                            }
                            $supply_from = 'Warehouse';
                        }
                    }
                } elseif ($key == 'selling_unit') {
                    $order_product->selling_unit = $value;
                } elseif ($key == 'discount') {
                    $percent_value = $value / 100;
                    if (@$order->primary_status == 3) {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->qty_shipped;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->pcs_shipped;
                        }
                    } else {
                        if ($order_product->is_retail == 'qty') {
                            $quantity = $order_product->quantity;
                        } else if (@$order_product->is_retail == 'pieces') {
                            $quantity = @$order_product->number_of_pieces;
                        }
                    }
                    $discount_val = $item_unit_price * $percent_value;
                    $actual_dis = $item_unit_price - $discount_val;
                    $order_product->unit_price_with_discount = $actual_dis;
                    $total = $item_unit_price * $quantity;
                    $discount_value = $percent_value * $total;
                    $result = $total - $discount_value;
                    $order_product->total_price = round($result, 2);
                    $vat = $order_product->vat;
                    $vat_amountt = @$item_unit_price * (@$vat / 100);
                    $vat_amount = number_format($vat_amountt, 4, '.', '');
                    $vat_amount_total_over_item = $vat_amount * $quantity;
                    $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                    if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                        $unit_price_with_vat = round($total, 2) + round($vat_amount_total_over_item, 2);
                    } else {
                        $unit_price_with_vat = round($total, 2) + round($vat_amount_total_over_item, 2);
                    }
                    if ($value !== null) {
                        $percent_value = $value / 100;
                        $dis_value = $unit_price_with_vat * $percent_value;
                        $tpwt = $unit_price_with_vat - @$dis_value;

                        $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                        $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                        $order_product->vat_amount_total = number_format($vat_amount_total_over_item, 4, '.', '');
                    } else {
                        $tpwt = $unit_price_with_vat;
                    }
                    $order_product->total_price_with_vat = round(@$tpwt, 2);
                }
                if ($key != 'from_warehouse_id') {
                    if ($key == 'unit_price') {
                        $order_product->$key = number_format($value, 2, '.', '');
                        if ($order_product->discount != null && $order_product->discount != 0) {
                            $order_product->unit_price_with_discount = $order_product->unit_price * (100 - $order_product->discount) / 100;
                        } else {
                            $order_product->unit_price_with_discount = $order_product->unit_price;
                        }
                    } else if ($key == 'unit_price_with_vat') {
                        if ($order_product->vat == 0) {
                            $order_product->$key = number_format($value, 2, '.', '');
                        } else {
                            $order_product->$key = number_format($value, 2, '.', '');
                        }

                        if ($order_product->discount != null && $order_product->discount != 0) {
                            $order_product->unit_price_with_discount = $order_product->unit_price * (100 - $order_product->discount) / 100;
                        } else {
                            $order_product->unit_price_with_discount = $order_product->unit_price;
                        }
                    } else {
                        $order_product->$key = $value;
                    }
                }
                $order_product->save();
                $reference_number = @$order_product->product->refrence_code;
                $old_value = @$request->old_value == 'clicked' ? '--' : @$request->old_value;
                if ($key == 'from_warehouse_id') {
                    $column_name = "Supply From";
                } else if ($key == 'short_desc') {
                    $column_name = "Description";
                } else if ($key == 'selling_unit') {
                    $column_name = "Sales Unit";
                } else if ($key == 'discount') {
                    $column_name = "Discount";
                } else if ($key == 'vat') {
                    $column_name = "VAT";
                } else if ($key == 'brand') {
                    $column_name = "Brand";
                } else if ($key == 'quantity') {
                    $column_name = $order->primary_status == 3 ? 'Qty Ordered' : 'Qty';
                } else if ($key == 'qty_shipped') {
                    $column_name = "Qty Sent";
                } else if ($key == 'number_of_pieces') {
                    $column_name = $order->primary_status == 3 ? 'Pieces Ordered' : 'Pieces';
                } else if ($key == 'pcs_shipped') {
                    $column_name = "Pieces Sent";
                } else if ($key == 'unit_price_with_vat') {
                    $column_name = "Unit Price (+VAT)";
                } else if ($key == 'unit_price') {
                    $column_name = "Default Price";
                } else if ($key == 'type_id') {
                    $column_name = "Type";
                } else {
                    $column_name = @$key;
                }
                if ($key == 'from_warehouse_id') {
                    $value = explode('-', $value);
                    $new_value = @$value[1];
                } else {
                    if ($key == 'pcs_shipped') {
                        $new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
                    } else if ($key == 'qty_shipped') {
                        $new_value = @$request->old_value == 'clicked' ? 'kg' : @$request->qty_shipped;
                    } else if ($key == 'quantity') {
                        $new_value = @$request->old_value == 'clicked' ? 'kg' : @$value;
                    } else if ($key == 'number_of_pieces') {
                        $new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
                    } else {
                        $new_value = @$value;
                    }
                }
                $order_history = (new QuotationHelper)->MakeHistory(@$order_product->order_id, $reference_number, $column_name, $old_value, $new_value);
                if ($order->status == 8 && $key == 'quantity' && $order_product->is_warehouse == 0 && auth()->user()->email != 'farooq@pkteam.com') {
                    $configuration = Configuration::whereNotNull('purchasing_email')->first();
                    if ($configuration) {
                        if ($order_history) {
                            (new OrderController)->notificationsAndEmails($order, $order_history, $order_product);
                        }
                    }
                }
            }
            $sub_total     = 0;
            $total_vat     = 0;
            $grand_total   = 0;
            $sub_total_w_w   = 0;
            $sub_total_without_discount = 0;
            $item_level_dicount = 0;
            $query         = OrderProduct::where('order_id', $order_product->order_id)->get();
            foreach ($query as  $value) {
                $sub_total += $value->total_price;
                $sub_total_w_w += number_format($value->total_price_with_vat, 2, '.', '');
                $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);
                if ($value->discount != 0) {
                    if ($value->discount == 100) {
                        if (@$order->primary_status == 3) {
                            if ($value->is_retail == 'pieces') {
                                $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                                $sub_total_without_discount += $discount_full;
                            } else {
                                $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                                $sub_total_without_discount += $discount_full;
                            }
                        } else {
                            if ($value->is_retail == 'pieces') {
                                $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                                $sub_total_without_discount += $discount_full;
                            } else {
                                $discount_full =  $value->unit_price_with_vat * $value->quantity;
                                $sub_total_without_discount += $discount_full;
                            }
                        }
                        $item_level_dicount += $discount_full;
                    } else {
                        $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                        $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                    }
                } else {
                    $sub_total_without_discount += $value->total_price;
                }
            }
            $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);

            $order_product->get_order->update(['total_amount' => number_format($grand_total, 2, '.', '')]);
            //item level calculations
            $total_amount_wo_vat = number_format((float)$order_product->total_price, 2, '.', '');
            $total_amount_w_vat = number_format((float)$order_product->total_price_with_vat, 2, '.', '');
            $unit_price_after_discount = $order_product->unit_price_with_discount != null ?  number_format($order_product->unit_price_with_discount, 2, '.', '') : '--';
            $unit_price = $order_product->unit_price != null ?  number_format($order_product->unit_price, 2, '.', '') : '--';
            $unit_price_w_vat = $order_product->unit_price_with_vat != null ?  number_format($order_product->unit_price_with_vat, 2, '.', '') : '--';
            $quantity = round($order_product->quantity, 4);
            $pcs = round($order_product->number_of_pieces, 4);
            if ($order_product->supplier_id != null) {
                $supply_from = $order_product->from_supplier != null ? $order_product->from_supplier->reference_name : '--';
            } else {
                $supply_from = 'Warehouse';
            }
            $type = $order_product->productType != null ? $order_product->productType->title : 'Select';
            $qty_shipped = round($order_product->qty_shipped, 4);
            $pcs_shipped = round($order_product->pcs_shipped, 4);
            DB::commit();
            return response()->json(['success' => true, 'status' => $order->statuses->title, 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','), 'sub_total_without_discount' => number_format(floor(@$sub_total_without_discount * 100) / 100, 2, '.', ','), 'item_level_dicount' => number_format(floor(@$item_level_dicount * 100) / 100, 2, '.', ','), 'total_amount_wo_vat' => $total_amount_wo_vat, 'total_amount_w_vat' => $total_amount_w_vat, 'id' => $order_product->id, 'unit_price_after_discount' => $unit_price_after_discount, 'unit_price' => $unit_price, 'unit_price_w_vat' => $unit_price_w_vat, 'supply_from' => $supply_from, 'quantity' => $quantity, 'pcs' => $pcs, 'type' => $type, 'type_id' => $order_product->type_id, 'qty_shipped' => $qty_shipped, 'pcs_shipped' => $pcs_shipped]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public static function takeHistory($order_product, $request, $key, $value, $order){
            $reference_number = @$order_product->product->refrence_code;
            $old_value = @$request->old_value == 'clicked' ? '--' : @$request->old_value;
            if ($key == 'from_warehouse_id') {
                $column_name = "Supply From";
            } else if ($key == 'short_desc') {
                $column_name = "Description";
            } else if ($key == 'selling_unit') {
                $column_name = "Sales Unit";
            } else if ($key == 'discount') {
                $column_name = "Discount";
            } else if ($key == 'vat') {
                $column_name = "VAT";
            } else if ($key == 'brand') {
                $column_name = "Brand";
            } else if ($key == 'quantity') {
                $column_name = $order->primary_status == 3 ? 'Qty Ordered' : 'Qty';
            } else if ($key == 'qty_shipped') {
                $column_name = "Qty Sent";
            } else if ($key == 'number_of_pieces') {
                $column_name = $order->primary_status == 3 ? 'Pieces Ordered' : 'Pieces';
            } else if ($key == 'pcs_shipped') {
                $column_name = "Pieces Sent";
            } else if ($key == 'unit_price_with_vat') {
                $column_name = "Unit Price (+VAT)";
            } else if ($key == 'unit_price') {
                $column_name = "Default Price";
            } else if ($key == 'type_id') {
                $column_name = "Type";
            } else {
                $column_name = @$key;
            }
            if ($key == 'from_warehouse_id') {
                $value = explode('-', $value);
                $new_value = @$value[1];
            } else {
                if ($key == 'pcs_shipped') {
                    $new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
                } else if ($key == 'qty_shipped') {
                    $new_value = @$request->old_value == 'clicked' ? 'kg' : @$request->qty_shipped;
                } else if ($key == 'quantity') {
                    $new_value = @$request->old_value == 'clicked' ? 'kg' : @$value;
                } else if ($key == 'number_of_pieces') {
                    $new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
                } else {
                    $new_value = @$value;
                }
            }
            $order_history = (new QuotationHelper)->MakeHistory(@$order_product->order_id, $reference_number, $column_name, $old_value, $new_value);
        return true;
    }
}
