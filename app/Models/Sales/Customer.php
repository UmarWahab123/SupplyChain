<?php

namespace App\Models\Sales;

use Illuminate\Database\Eloquent\Model;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\ExportStatus;
use Illuminate\Support\Facades\Storage;
use App\Jobs\CustomerBulkUploadPOJob;
use App\Models\Common\CustomerCategory;
use Auth;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\PaymentTerm;
use App\Models\Common\State;
use App\User;


class Customer extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = ['last_order_date', 'deleted_at', 'user_id', 'category_id', 'credit_term', 'address_line_1', 'address_line_2', 'logo', 'language', 'first_name', 'last_name', 'company', 'phone', 'postalcode', 'email', 'country', 'state', 'city', 'reference_number', 'status', 'reference_no', 'reference_name', 'primary_sale_id', 'secondary_sale_id', 'ecommerce_customer_id', 'ecommerce_customer', 'customer_credit_limit', 'manual_customer'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function primary_sale_person()
    {
        return $this->belongsTo('App\User', 'primary_sale_id', 'id');
    }

    public function secondary_sale_person()
    {
        return $this->belongsTo('App\User', 'secondary_sale_id', 'id');
    }

    public function CustomerCategory()
    {
        return $this->belongsTo('App\Models\Common\CustomerCategory', 'category_id', 'id');
    }

    public function getcountry()
    {
        return $this->belongsTo('App\Models\Common\Country', 'country', 'id');
    }

    public function getstate()
    {
        return $this->belongsTo('App\Models\Common\State', 'state', 'id');
    }

    public function getshipping()
    {
        return $this->hasMany('App\Models\Common\Order\CustomerShippingDetail', 'customer_id', 'id');
    }
    public function getbilling()
    {
        return $this->hasMany('App\Models\Common\Order\CustomerBillingDetail', 'customer_id', 'id');
    }

    public function getDefaultBilling()
    {
        return $this->hasOne('App\Models\Common\Order\CustomerBillingDetail', 'customer_id', 'id')->where('is_default', 1);
    }

    public function getnotes()
    {
        return $this->hasMany('App\Models\Common\Order\CustomerNote', 'customer_id', 'id');
    }

    public function getpayment_term()
    {
        return $this->belongsTo('App\Models\Common\PaymentTerm', 'credit_term', 'id');
    }

    public function customer_payment_types()
    {
        return $this->hasMany('App\Models\Common\CustomerPaymentType', 'customer_id', 'id');
    }

    public function customer_contacts()
    {
        return $this->hasMany('App\Models\Common\CustomerContact', 'customer_id', 'id');
    }
    public function primary_contact()
    {
        return $this->hasOne('App\Models\Common\CustomerContact', 'customer_id', 'id')->where('is_default',1);
    }

    public function customer_orders()
    {
        return $this->hasMany('App\Models\Common\Order\Order', 'customer_id', 'id');
    }

    public function productCustomerFixedPrice()
    {
        return $this->hasMany('App\Models\Common\ProductCustomerFixedPrice', 'customer_id', 'id');
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'customer_id', 'id');
    }

    public function get_customer_documents()
    {
        return $this->hasMany('App\Models\Sales\CustomerGeneralDocument', 'customer_id', 'id');
    }

    public function getMonthWiseSale($customer_id, $year, $month)
    {
        $month_totalAmount = Order::where('customer_id', $customer_id)
            ->where('primary_status', 3)
            ->whereYear('converted_to_invoice_on', $year)
            ->whereMonth('converted_to_invoice_on', $month)
            ->sum('total_amount');

        return round(@$month_totalAmount, 2);
    }

    public function getallMonthWiseSale($customers_ids, $year, $month)
    {
        $month_totalAmount = Order::whereIn('customer_id', $customers_ids)
            ->where('primary_status', 3)
            ->whereYear('converted_to_invoice_on', $year)
            ->whereMonth('converted_to_invoice_on', $month)
            ->sum('total_amount');

        return round(@$month_totalAmount, 2);
    }

    public function getYearWiseSale($customer_id, $year)
    {
        $month_totalAmount = Order::where('customer_id', $customer_id)
            ->where('primary_status', 3)
            ->whereYear('converted_to_invoice_on', $year)
            ->sum('total_amount');

        return round(@$month_totalAmount, 2);
    }

    public function getAllYearWiseSale($customers_ids, $year)
    {
        $month_totalAmount = Order::whereIn('customer_id', $customers_ids)
            ->where('primary_status', 3)
            ->whereYear('converted_to_invoice_on', $year)
            ->sum('total_amount');

        return round(@$month_totalAmount, 2);
    }

    public function get_total_price_with_vat($customer_id)
    {
        $orders = Order::where('customer_id', $customer_id)->whereIn('primary_status', [2, 3])->pluck('id')->toArray();
        $order_products = OrderProduct::whereIn('order_id', $orders)->sum('total_price_with_vat');
        return round($order_products, 2);
    }

    public function get_total_draft_orders($customer_id)
    {
        // $orders = Order::where('customer_id',$customer_id)->where('primary_status',2)->pluck('id')->toArray();
        // $order_products = OrderProduct::whereIn('order_id' , $orders)->sum('total_price_with_vat');
        // return round($order_products,2);

        $order_products = Order::join('order_products', 'orders.id', '=', 'order_products.order_id')->where("customer_id", $customer_id)->where('primary_status', 2)->sum('order_products.total_price_with_vat');
        return round($order_products, 2);
    }

    public function CustomerSecondaryUser()
    {
        return $this->hasMany('App\CustomerSecondaryUser', 'customer_id', 'id');
    }

    public function get_order_transactions()
    {
        return $this->hasMany('App\OrderTransaction', 'customer_id', 'id')->orderBy('id', 'ASC');
    }

    public function getLastOrderDate($id)
    {
        $orders = Order::where('customer_id', $id)->select('created_at')->whereIn('primary_status', [2, 3])->orderby('id', 'desc')->first();
        if ($orders == null) {
            return 'N.A';
        } else {
            return Carbon::parse($orders->created_at)->format('d/m/Y');
        }
    }



    public function customerBulkUploadPO($request)
    {
    // dd('here');
        $validator = $request->validate([
            'excel' => 'required|mimes:xlsx'
        ]);
        try {
            $fileName = time() . '_' . $request['excel']->getClientOriginalName();
            $contents = file_get_contents($request['excel']->getRealPath());
            Storage::disk('temp')->put($fileName, $contents);
            $statusCheck = ExportStatus::where('type', 'customer_bulk_upload_po')->where('user_id', Auth::user()->id)->first();
            if ($statusCheck == null) {
                $new = new ExportStatus();
                $new->type = 'customer_bulk_upload_po';
                $new->user_id = Auth::user()->id;
                $new->status = 1;
                if ($new->save()) {
                    CustomerBulkUploadPOJob::dispatch($fileName, Auth::user()->id, Auth::user());
                    return response()->json(['status' => 1, 'error_msgs' => $statusCheck->error_msgs]);
                }
            } else if ($statusCheck->status == 0 || $statusCheck->status == 2) {
                ExportStatus::where('type', 'customer_bulk_upload_po')->where('user_id', Auth::user()->id)->update(['status' => 1, 'exception' => null, 'error_msgs' => null]);
                $res = CustomerBulkUploadPOJob::dispatch($fileName, Auth::user()->id, Auth::user());
                $statusCheck = ExportStatus::where('type', 'customer_bulk_upload_po')->where('user_id', Auth::user()->id)->first();
                return response()->json(['status' => $statusCheck->status, 'error_msgs' => $statusCheck->error_msgs]);
            } else {
                return response()->json(['msg' => 'File is Already Uploding, Please wait...!', 'status' => 1, 'error_msgs' => $statusCheck->error_msgs]);
            }
        } catch (Exception $e) {
            return response()->json([
                'errors' => $e->getMessage(),
                'success' => false
            ]);
        }
    }


    public function customerRecursiveCallForBulkPos($request)
    {
        $status = ExportStatus::where('type', 'customer_bulk_upload_po')->where('user_id',auth()->user()->id)->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'error_msgs' => $status->error_msgs]);
    }


    public function customerCheckStatusFirstTimeForBulkPos($request)
    {
        $status = ExportStatus::where('type', 'customer_bulk_upload_po')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }
    public static function doSort($request, $query)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';

        if ($request['sortbyparam'] == 'invoice_date') {
            $query->orderBy('converted_to_invoice_on', $sort_order);
        }

        if ($request['sortbyparam'] == 'del_date') {
            $query->orderBy('delivery_request_date', $sort_order);
        }

        if ($request['sortbyparam'] == 'invoice_number' || $request['sortbyparam'] == 'inv_number' || $request['sortbyparam'] == 'invoice_no') {
            $query->orderBy('ref_id', $sort_order);
        }

        if ($request['sortbyparam'] == 'sales_person') {
            $query->leftJoin('users', 'users.id', '=', 'orders.user_id')->orderBy('users.name', $sort_order);
        }

        if ($request['sortbyparam'] == 'company_name') {
            $query->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')->orderBy('customers.company', $sort_order);
        }

        if ($request['sortbyparam'] == 'ref_name') {
            $query->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')->orderBy('customers.reference_name', $sort_order);
        }

        if ($request['sortbyparam'] == 'sub_total') {
            $query->leftJoin('order_products', 'order_products.order_id', '=', 'orders.id')->orderBy(\DB::Raw('SUM(order_products.total_price)'), $sort_order)->groupBy('orders.id');
        } elseif ($request['sortbyparam'] == null && $request->sortbyvalue == 1) {
            $query->leftJoin('order_products', 'order_products.order_id', '=', 'orders.id')->orderBy(\DB::Raw('SUM(order_products.total_price)'), 'ASC')->groupBy('orders.id');
        }

        if ($request['sortbyparam'] == 'total_amount_paid') {
            $query->leftJoin('order_transactions', 'order_transactions.order_id', '=', 'orders.id')->orderBy(\DB::Raw('SUM(order_transactions.total_received)'), $sort_order)->groupBy('orders.id');
        }

        if ($request['sortbyparam'] == 'total_amount_due') {
            $query->leftJoin('order_transactions', 'order_transactions.order_id', '=', 'orders.id')->orderBy(\DB::Raw('(orders.total_amount+0)-SUM(order_transactions.total_received+0)'), $sort_order)->groupBy('orders.id');
        }

        if ($request['sortbyparam'] == 'payment_due_date') {
            $query->orderBy($request['sortbyparam'], $sort_order);
        }

        if ($request['sortbyparam'] == 'order_total') {
            $query->orderBy('total_amount', $sort_order);
        }
    }
    public static function doSortby($request, $products, $total_items_sale, $total_items_gp)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        if ($request['sortbyparam'] == 1) {
            $sort_variable  = 'sales';
        } elseif ($request['sortbyparam'] == 2) {
            $sort_variable  = 'products_total_cost';
        } elseif ($request['sortbyparam'] == 3) {
            $sort_variable  = 'marg';
        } elseif ($request['sortbyparam'] == 'customer_ref_no') {
            $sort_variable  = 'reference_number';
        } elseif ($request['sortbyparam'] == 'customer') {
            $sort_variable  = 'reference_name';
        } elseif ($request['sortbyparam'] == 'vat_out') {
            $sort_variable  = 'vat_amount_total';
        } elseif ($request['sortbyparam'] == 'percent_sales') {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END /' . $total_items_sale . ')*100'), $sort_order);
        } elseif ($request['sortbyparam'] == 'vat_in') {
            $sort_variable  = 'vat_in';
        }
        elseif($request['sortbyparam'] == 'gp')
        {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'percent_gp')
        {
            $products->orderBy(\DB::raw('((CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))) /'.$total_items_gp.'*100'), $sort_order);
        }

        if ($sort_variable != null) {
            $products->orderBy($sort_variable, $sort_order);
        }
        return $products;
    }

    public static function CustomerlIstSorting($request, $query)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        if ($request['sortbyparam'] == 3) {
            $sort_variable  = 'reference_name';
        } elseif ($request['sortbyparam'] == 4) {
            $sort_variable  = 'company';
        } elseif ($request['sortbyparam'] == 5) {
            $sort_variable  = 'last_order_date';
        } elseif ($request['sortbyparam'] == 5) {
            $sort_variable  = 'last_order_date';
        } elseif ($request['sortbyparam'] == 'customer_no') {
            $sort_variable  = 'reference_number';
        }
        // elseif($request['sortbyparam'] == 'email')
        // {
        //   // $sort_variable  = 'email';
        //   $query->leftJoin('customer_billing_details as cb', 'cb.customer_id', '=', 'customers.id')
        //   // ->where('is_default', 1)
        //   ->orderBy('cb.billing_email', $sort_order);
        // }
        elseif ($request['sortbyparam'] == 'primary_sale_person') {
            $query->leftJoin('users as u', 'u.id', '=', 'customers.primary_sale_id')->orderBy('u.name', $sort_order);
        } elseif ($request['sortbyparam'] == 'primary_contact') {
            $sort_variable = 'phone';
        } elseif ($request['sortbyparam'] == 'district') {
            $query->leftJoin('customer_billing_details as d', 'd.customer_id', '=', 'customers.id')->where('d.is_default', 1)->orderBy('d.billing_city', $sort_order);
        } elseif ($request['sortbyparam'] == 'city') {
            $query->leftJoin('customer_billing_details as d', 'd.customer_id', '=', 'customers.id')->leftJoin('states as s', 's.id', '=', 'd.billing_state')->where('d.is_default', 1)->orderBy('s.name', $sort_order);
        } elseif ($request['sortbyparam'] == 'classification') {
            $query->leftJoin('customer_categories as cat', 'cat.id', '=', 'customers.category_id')->orderBy('cat.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'payment_terms') {
            $query->leftJoin('payment_terms as pt', 'pt.id', '=', 'customers.credit_term')->orderBy('pt.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'customer_since') {
            $query->orderBy('created_at', $sort_order);
        } elseif ($request['sortbyparam'] == 'draft_orders') {
            $query->orderBy('created_at', $sort_order);
        }

        if ($sort_variable != null) {
            $query->orderBy($sort_variable, $sort_order);
        }
        return $query;
    }

    public function syncCustomerEcom($id)
    {
        $customer = Customer::find($id);
        $customer_billing_details = CustomerBillingDetail::Where('customer_id', $id)->Where('is_default', 1)->first();

        $customer_shipping_details = CustomerBillingDetail::Where('customer_id', $id)->Where('is_default_shipping', 1)->first();
        $shipping = null;
        if ($customer_shipping_details) {
            $shipping = [
                'first_name' => (string)$customer->first_name == null || $customer->first_name == '' ? $customer->reference_name : $customer->first_name,
                'last_name' => (string)$customer->first_name == null || $customer->first_name == '' ? '' : $customer->last_name,
                'company' => (string)$customer_shipping_details->company_name,
                'address_1' => (string)$customer_shipping_details->billing_address,
                'address_2' => (string)'',
                'city' => (string)$customer_shipping_details->billing_city,
                'state' => (string)$customer_shipping_details->getstate->name,
                'postcode' => (string)$customer_shipping_details->billing_zip,
                'country' => (string)$customer_shipping_details->getcountry->name
            ];
        } else {
            $shipping = [
                'first_name' => (string)$customer->first_name == null || $customer->first_name == '' ? $customer->reference_name : $customer->first_name,
                'last_name' => (string)$customer->first_name == null || $customer->first_name == '' ? '' : $customer->last_name,
                'company' => (string)$customer_billing_details->company_name,
                'address_1' => (string)$customer_billing_details->billing_address,
                'address_2' => (string)'',
                'city' => (string)$customer_billing_details->billing_city,
                'state' => (string)$customer_billing_details->getstate->name,
                'postcode' => (string)$customer_billing_details->billing_zip,
                'country' => (string)$customer_billing_details->getcountry->name
            ];
        }

        $data = [
            'email' => (string)$customer->email,
            'first_name' => (string)$customer->first_name == null || $customer->first_name == '' ? $customer->reference_name : $customer->first_name,
            'last_name' => (string)$customer->first_name == null || $customer->first_name == '' ? '' : $customer->last_name,
            'username' => (string)$customer->email,
            'billing' => [
                'first_name' => (string)$customer->first_name == null || $customer->first_name == '' ? $customer->reference_name : $customer->first_name,
                'last_name' => (string)$customer->first_name == null || $customer->first_name == '' ? '' : $customer->last_name,
                'company' => (string)$customer_billing_details->company_name,
                'address_1' => (string)$customer_billing_details->billing_address,
                'address_2' => (string)'',
                'city' => (string)$customer_billing_details->billing_city,
                'state' => (string)$customer_billing_details->getstate->name,
                'postcode' => (string)$customer_billing_details->billing_zip,
                'country' => (string)$customer_billing_details->getcountry->name,
                'email' => (string)$customer_billing_details->billing_email,
                'phone' => (string)$customer_billing_details->billing_phone
            ],
            'shipping' => $shipping
        ];

        $customer = \Codexshaper\WooCommerce\Facades\Customer::where('email', $customer->email)->first();
        if ($customer) {
            return ['success' => false];
        }
        $customer = \Codexshaper\WooCommerce\Facades\Customer::create($data);
        if ($customer) {
            return [
                'success' => true
            ];
        }
    }

    public static function returnAddColumn($column, $item)
    {
        switch ($column) {
            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                <input type="checkbox" class="custom-control-input check" value="' . $item->id . '" id="customer_check_' . $item->id . '">
                <label class="custom-control-label" for="customer_check_' . $item->id . '"></label>
                </div>';
                return $html_string;
                break;

            case 'address':
                $getbilling = $item->getbilling->where('is_default', 1)->first();
                return $getbilling !== null && $getbilling->billing_address !== null ? $getbilling->billing_address : '--';
                break;

            case 'category':
                if ($item->category_id == null) {
                    return 'N.A';
                } else {
                    return $item->CustomerCategory->title;
                }
                break;

            case 'user_id':
                $html_string = '<span class="m-l-15 inputDoubleClick primary_span_' . $item->id . '" id="primary_salesperson_id" data-fieldvalue="' . @$item->primary_sale_id . '" data-id="salesperson ' . @$item->primary_sale_id . ' ' . @$item->id . '"> ';
                $html_string .= ($item->primary_sale_id != null) ? $item->primary_sale_person->name : "--";
                $html_string .= '</span>';

                $html_string .= '<div class="incomplete-filter d-none inc-fil-salesperson primary_select_' . $item->id . '">
                <select data-row_id="' . @$item->id . '" class=" font-weight-bold form-control-lg form-control js-states state-tags select-common primary_salesperson_id primary_salespersons_select' . @$item->id . '" name="primary_salesperson_id" required="true">';
                $html_string .= '</select></div>';
                return $html_string;
                break;

            case 'secondary_sp':
                $html_string = '';
                if ($item->CustomerSecondaryUser != null) {
                    if ($item->CustomerSecondaryUser->count() > 1) {
                        $html_string = '<span class=" inputDoubleClick secondary_span_' . $item->id . '" id="secondary_salesperson_id" data-fieldvalue="' . @$item->secondary_sale_id . '" data-id="secondary_salesperosn ' . @$item->secondary_sale_id . ' ' . @$item->id . '"> ';
                        $html_string .=  '<b class="font-weight-bold">Add</b>';
                        $html_string .= '</span>';
                        $html_string .= '<div class="incomplete-filter d-none inc-fil-salesperson secondary_select_' . $item->id . '">
                    <select data-row_id="' . @$item->id . '" class="font-weight-bold form-control-lg form-control js-states state-tags select-common secondary_salesperson_id secondary_salespersons_select' . @$item->id . '" name="secondary_salesperson_id" required="true">';
                        $html_string .= '</select></div>';
                        // $html_string='<span>Add</span>';
                        $html_string .= '<span><button class="btn view-sp-btn add-btn btn-color pull-left add-cust-fp-btn btn-sm mr-3" data-id="' . $item->id . '" type="button" title="view Secondary Sale Persons" id="Show-Secondary-Suppliers"><i class="fa fa-eye"></i></button></span>';
                    } else {

                        $html_string = '<span class="m-l-15 inputDoubleClick secondary_span_' . $item->id . '" id="secondary_salesperson_id" data-fieldvalue="' . @$item->secondary_sale_id . '" data-id="secondary_salesperosn ' . @$item->secondary_sale_id . ' ' . @$item->id . '"> ';
                        $html_string .= (count($item->CustomerSecondaryUser) > 0) ?  $item->CustomerSecondaryUser[0]->secondarySalesPersons->name : "--";
                        $html_string .= '</span>';

                        $html_string .= '<div class="incomplete-filter d-none inc-fil-salesperson secondary_select_' . $item->id . '">
                    <select data-row_id="' . @$item->id . '" class="font-weight-bold form-control-lg form-control js-states state-tags select-common secondary_salesperson_id secondary_salespersons_select' . @$item->id . '" name="secondary_salesperson_id" required="true">';
                        $html_string .= '</select></div>';
                    }
                } else {
                    $html_string = '<span class="m-l-15 inputDoubleClick secondary_span_' . $item->id . '" id="secondary_salesperson_id" data-fieldvalue="' . @$item->secondary_sale_id . '" data-id="secondary_salesperosn ' . @$item->secondary_sale_id . ' ' . @$item->id . '"> ';
                    $html_string .= ($item->secondary_sale_id != null) ? $item->secondary_sale_person->name : "--";
                    $html_string .= '</span>';

                    $html_string .= '<div class="incomplete-filter d-none inc-fil-salesperson secondary_select_' . $item->id . '">
                    <select data-row_id="' . @$item->id . '" class="font-weight-bold form-control-lg form-control js-states state-tags select-common secondary_salesperson_id secondary_salespersons_select' . @$item->id . '" name="secondary_salesperson_id" required="true">';
                    $html_string .= '</select></div>';
                }

                return $html_string;
                break;

            case 'country':
                $getbilling = $item->getbilling->where('is_default', 1)->first();
                return $getbilling != null && $getbilling->getcountry !== null ? @$getbilling->getcountry->name : '--';
                break;

            case 'state':
                $customerAddress = $item->getbilling->where('is_default', 1)->first();
                if ($customerAddress) {
                    return $customerAddress->billing_state !== null ? @$customerAddress->getstate->name : 'N.A';
                } else {
                    return 'N.A';
                }
                break;

            case 'credit_term':
                return $item->getpayment_term !== null ? @$item->getpayment_term->title : 'N.A';
                break;

            case 'email':
                $customerAddress = $item->getbilling->where('is_default', 1)->first();
                if ($customerAddress) {
                    return $customerAddress->billing_email !== null ? @$customerAddress->billing_email : 'N.A';
                } else {
                    return 'N.A';
                }
                break;

            case 'city':
                $customerAddress = $item->getbilling->where('is_default', 1)->first();
                if ($customerAddress != null) {
                    return $customerAddress->billing_city !== null ? @$customerAddress->billing_city : 'N.A';
                } else {
                    return 'N.A';
                }
                break;

            case 'postalcode':
                $getbilling = $item->getbilling->where('is_default', 1)->first();
                return $getbilling !== null && $getbilling->billing_zip != null ? $getbilling->billing_zip : '--';
                break;

            case 'created_at':
                return $item->created_at !== null ? Carbon::parse(@$item->created_at)->format('d/m/Y') : 'N.A';
                break;

            case 'draft_orders':
                return $item->get_total_draft_orders($item->id);
                break;

            case 'total_orders':
                $total = $item->customer_orders()->whereIn('primary_status', [2, 3])->sum('total_amount');
                return number_format($total, 2, '.', ',');
                break;

            case 'last_order_date':
                if ($item->last_order_date == null) {
                    return 'N.A';
                }
                return Carbon::parse($item->last_order_date)->format('d/m/Y');
                break;

            case 'status':
                if ($item->status == 1) {
                    $status = '<span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Completed</span>';
                } elseif ($item->status == 2) {
                    $status = '<span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Suspend</span>';
                } else {
                    $status = '<span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Incomplete</span>';
                }
                return @$status;
                break;

            case 'action':
                $html_string = '';
                if ($item->status != 0 && Auth::user()->role_id != 7) {
                    if ($item->status == 1) {
                        if (Auth::user()->role_id != 4) {
                            $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon suspend-customer" data-id="' . $item->id . '" title="Suspend"><i class="fa fa-ban"></i></a>';
                        }
                    } elseif ($item->status == 2) {
                        if (Auth::user()->role_id != 4) {
                            $html_string .= ' <a href="javascript:void(0);" class="actionicon viewIcon activateIcon" data-id="' . $item->id . '" title="Activate"><i class="fa fa-check"></i></a>';
                            $html_string .= ' <a href="javascript:void(0);" class="actionicon deleteIcon delete-customer" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                        }
                    }
                }
                if ($item->status == 0 && Auth::user()->role_id != 7) {
                    if (Auth::user()->role_id != 4) {
                        $html_string .= ' <a href="javascript:void(0);" class="actionicon deleteIcon delete-customer" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                    }
                    $html_string .= '<a href="' . url('sales/get-customer-detail/' . $item->id) . '" class="actionicon" title="View Detail"><i class="fa fa-eye"></i></a>';
                }
                return @$html_string;
                break;

            case 'notes':
                $notes = $item->getnotes->count();
                $html_string = '<div class="d-flex justify-content-center text-center">';
                if ($notes > 0) {
                    $note = $item->getnotes->first()->note_description;
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="' . $item->id . '" class="font-weight-bold d-block show-notes mr-2" title="View Notes">' . mb_substr($note, 0, 30) . ' ...</a>';
                }

                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="' . $item->id . '"  class="add-notes fa fa-plus" title="Add Note"></a>
                            </div>';
                return @$html_string;
                break;
            case 'tax_id':
                $getbilling = $item->getbilling->where('is_default', 1)->first();
                return $getbilling != null && $getbilling->tax_id != null ? $getbilling->tax_id : '--';
                break;
            case 'address_reference':
                $getbilling = $item->getbilling->where('is_default', 1)->first();
                return $getbilling != null && $getbilling->title != null ? $getbilling->title : '--';
                break;
        }
    }

    public static function returnEditColumn($column, $item)
    {
        switch ($column) {
            case 'reference_number':
                if ($item->reference_number !== null) {
                    $html_string = '<a href="' . url('sales/get-customer-detail/' . $item->id) . '"  ><b>' . $item->reference_number . '</b></a>';
                } else {
                    $html_string = '--';
                }
                return $html_string;
                break;

            case 'reference_name':
                if ($item->reference_name !== null) {
                    $html_string = '<a class="description_wrap" href="' . url('sales/get-customer-detail/' . $item->id) . '"><b>' . $item->reference_name . '</b></a>';
                } else {
                    $html_string = '--';
                }
                return $html_string;
                break;

            case 'company':
                if ($item->company !== null) {
                    $html_string = '<span class="description_wrap">'.@$item->company.'</span>';
                }
                else {
                    $html_string = 'N.A';
                }
                return $html_string;
                // return $item->company !== null ? @$item->company : 'N.A';
                break;

            case 'phone':
                return $item->phone !== null ? $item->phone : 'N.A';
                break;
        }
    }

    public static function returnFilterColumn($column, $item, $keyword)
    {
        switch ($column) {
            case 'email':
                $query = $item->whereIn('customers.id', CustomerBillingDetail::select('customer_id')->where('billing_email', 'LIKE', "%$keyword%")->pluck('customer_id'));
                return $query;
                break;
            case 'user_id':
                $query = $item->whereIn('primary_sale_id', User::select('id')->where('name', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'city':
                $query = $item->whereIn('customers.id', CustomerBillingDetail::select('customer_id')->where('billing_city', 'LIKE', "%$keyword%")->pluck('customer_id'));
                return $query;
                break;
            case 'state':
                $query = $item->whereIn('customers.id', CustomerBillingDetail::select('customer_id')->whereIn('billing_state', State::select('id')->where('name', 'LIKE', "%$keyword%")->pluck('id'))->pluck('customer_id'));
                return $query;
                break;
            case 'category':
                $query = $item->whereIn('category_id', CustomerCategory::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'credit_term':
                $query = $item->whereIn('credit_term', PaymentTerm::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'company':
                $query = $item->where('company',  'LIKE', "%$keyword%");
                return $query;
                break;
        }
    }


    public static function returnAddColumnCustomerReport($column, $item, $not_visible_arr)
    {
        switch ($column) {
            case 'payment_term':
                if (in_array('16', $not_visible_arr))
                    return '--';
                return @$item->getpayment_term->title;
                break;

            case 'sale_person':
                if (in_array('15', $not_visible_arr))
                    return '--';
                if (auth()->user()->role_id == 3)
                    return auth()->user()->name;
                return @$item->primary_sale_person->name;
                break;

            case 'orders':
                if (in_array('13', $not_visible_arr))
                    return '--';
                return $item->customer_orders_total != null ? number_format($item->customer_orders_total, 2, '.', ',') : '0.00';
                break;

            case 'location_code':
                if (in_array('14', $not_visible_arr))
                    return '--';
                return @$item->primary_sale_person->get_warehouse->location_code;
                break;

            case 'Dec':
                if (in_array('12', $not_visible_arr))
                    return '--';
                if ($item->dec_totalAmount - $item->dec_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->dec_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->dec_totalAmount != null ? number_format($item->dec_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Nov':
                if (in_array('11', $not_visible_arr))
                    return '--';
                if ($item->nov_totalAmount - $item->nov_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->nov_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->nov_totalAmount != null ? number_format($item->nov_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Oct':
                if (in_array('10', $not_visible_arr))
                    return '--';
                if ($item->oct_totalAmount - $item->oct_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->oct_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->oct_totalAmount != null ? number_format($item->oct_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Sep':
                if (in_array('9', $not_visible_arr))
                    return '--';
                if ($item->sep_totalAmount - $item->sep_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->sep_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->sep_totalAmount != null ? number_format($item->sep_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Aug':
                if (in_array('8', $not_visible_arr))
                    return '--';
                if ($item->aug_totalAmount - $item->aug_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->aug_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->aug_totalAmount != null ? number_format($item->aug_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Jul':
                if (in_array('7', $not_visible_arr))
                    return '--';
                if ($item->jul_totalAmount - $item->jul_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->jul_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->jul_totalAmount != null ? number_format($item->jul_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Jun':
                if (in_array('6', $not_visible_arr))
                    return '--';
                if ($item->jun_totalAmount - $item->jun_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->jun_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->jun_totalAmount != null ? number_format($item->jun_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'May':
                if (in_array('5', $not_visible_arr))
                    return '--';
                if ($item->may_totalAmount - $item->may_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->may_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->may_totalAmount != null ? number_format($item->may_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Apr':
                if (in_array('4', $not_visible_arr))
                    return '--';
                if ($item->apr_totalAmount - $item->apr_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->apr_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->apr_totalAmount != null ? number_format($item->apr_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Mar':
                if (in_array('3', $not_visible_arr))
                    return '--';
                if ($item->mar_totalAmount - $item->mar_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->mar_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->mar_totalAmount != null ? number_format($item->mar_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Feb':
                if (in_array('2', $not_visible_arr))
                    return '--';
                if ($item->feb_totalAmount - $item->feb_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;">' . number_format($item->feb_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->feb_totalAmount != null ? number_format($item->feb_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Jan':
                if (in_array('1', $not_visible_arr))
                    return '--';
                if ($item->jan_totalAmount - $item->jan_paidAmount > 5) {
                    return '<span style = "color:red; font-weight:bold;" class="t-' . $item->jan_totalAmount . ' p-' . $item->jan_paidAmount . '">' . number_format($item->jan_totalAmount, 2, '.', ',') . '</span>';
                }
                return $item->jan_totalAmount != null ? number_format($item->jan_totalAmount, 2, '.', ',') : '0.00';
                break;
        }
    }

    public static function returnEditColumnCustomerReport($column, $item, $sale_year, $not_visible_arr)
    {
        switch ($column) {
            case 'reference_name':
                if (in_array('0', $not_visible_arr))
                    return '--';

                $html_string = '<a target="_blank" href="' . url('admin/get_customer_invoices_from_report/' . $item->id . '/' . @$sale_year) . '"><b>' . $item->reference_name . '</b></a>';
                return @$html_string;
                break;
        }
    }

    public static function returnFilterColumnCustomerReport($column, $item, $keyword, $not_visible_arr, $sale_year)
    {
        switch ($column) {
            case 'reference_name':
                if (in_array('0', $not_visible_arr))
                    return '--';

                $html_string = '<a target="_blank" href="' . url('admin/get_customer_invoices_from_report/' . $item->id . '/' . @$sale_year) . '"><b>' . $item->reference_name . '</b></a>';
                return @$html_string;
                break;
        }
    }
}
