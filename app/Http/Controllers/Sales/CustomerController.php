<?php


namespace App\Http\Controllers\Sales;

use PDF;
use Auth;
use Mail;
use Excel;
use App\User;
use App\Variable;
use Carbon\Carbon;
use App\ExportStatus;
use App\Notification;
use App\QuotationConfig;
use App\OrdersPaymentRef;
use App\OrderTransaction;
use App\ImportFileHistory;
use App\TransactionHistory;
use App\BillingNotesHistory;
use App\Jobs\CustomerExpJob;
use App\Models\Common\State;
use Illuminate\Http\Request;
use App\Models\Common\Status;
use App\CustomerSecondaryUser;
use App\Models\Common\Country;
use App\Models\Common\Product;
use App\Models\Sales\Customer;
use App\Exports\CustomerExport;
use App\Models\Common\Supplier;
use App\Models\BillingNoCounter;
use Yajra\Datatables\Datatables;
use App\Exports\AccReceivableExp;
use App\Jobs\AccTransactionExpJob;
use App\Models\Common\Order\Order;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PaymentType;
use Illuminate\Support\Facades\DB;
use App\Imports\CustomerBulkImport;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use App\Models\Common\EmailTemplate;
use App\Models\Common\TempCustomers;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use App\Exports\AccTransactionExport;
use App\Jobs\AccountReceivableExport;
use App\Models\Common\CustomerContact;
use App\Models\Common\CustomerHistory;
use App\Models\Common\ProductCategory;
use App\Models\Common\TableHideColumn;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Order\CustomerNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\CustomerPaymentType;
use App\Models\Common\Order\DraftQuotation;
use App\Models\Common\Order\OrderAttachment;
use App\Mail\Backend\CustomerActivationEmail;
use App\Mail\Backend\CustomerSuspensionEmail;
use App\Models\Sales\CustomerGeneralDocument;
use App\Exports\CustomerProductFixedPriceExport;
use App\Imports\CustomerProductFixedPriceImport;
use App\Models\Common\CustomerProductFixedPrice;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\CustomerShippingDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrder;

class CustomerController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
        $dummy_data = null;
        if ($this->user && Schema::has('notifications')) {
            $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
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

        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies, 'sys_name' => $sys_name, 'sys_logos' => $sys_logos, 'sys_color' => $sys_color, 'sys_border_color' => $sys_border_color, 'btn_hover_border' => $btn_hover_border, 'current_version' => $current_version, 'dummy_data' => $dummy_data]);
    }
    public function index()
    {
        // dd('here');
        $countries    = Country::orderby('name', 'ASC')->pluck('name', 'id');
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'customer_list')->first();
        //dd($table_hide_columns);
        $category     = CustomerCategory::where('is_deleted', 0)->pluck('title', 'id');
        $payment_term = PaymentTerm::all()->pluck('title', 'id');
        $payment_types = PaymentType::all();
        $states       = State::select('id', 'name')->orderby('name', 'ASC')->where('country_id', 217)->get();
        $users        = User::where('status', 1)->whereNull('parent_id')->where('role_id', 3)->get();
        $customer_categories = CustomerCategory::where('is_deleted', 0)->get();
        // $states = State::where('country_id',217)->get();
        return view('sales.customer.index', compact('category', 'countries', 'table_hide_columns', 'payment_term', 'payment_types', 'states', 'users', 'customer_categories'));
    }

    public function getProductAgainstCat(Request $request)
    {
        $html_string = '';
        $checkedIds = ProductCustomerFixedPrice::where('customer_id', $request->cust_detail_id)->pluck('product_id')->toArray();
        $getProducts = Product::where('status', 1)->where('category_id', $request->cat_id)->whereNotIn('id', $checkedIds)->orderBy('id')->get();
        if ($getProducts->count() > 0) {
            $html_string .= '<option value="" selected="" disabled="">Select Product</option>';
            foreach ($getProducts as $prod) {
                $html_string .= '<option value="' . $prod->id . '">' . $prod->short_desc . '</option>';
            }
        } else {
            return response()->json(['success' => false]);
        }

        return response()->json(['success' => true, 'html_string' => $html_string]);
    }

    public function getCustProdData(Request $request)
    {

        $html_string2 = '';

        $categories = ProductCategory::where('parent_id', 0)->orderBy('title', 'ASC')->get();
        if ($categories->count() > 0) {
            $html_string2 .= '<option value=" ">Choose Category</option>';
            foreach ($categories as $cat) {
                $html_string2 .= '<optgroup label="' . $cat->title . '">';
                foreach ($cat->get_Child as $pc_child) {
                    $html_string2 .= '<option value="' . $pc_child->id . '">' . $pc_child->title . '</option>';
                }
                $html_string2 .= '</optgroup>';
            }
        }

        return response()->json(['success' => true, 'html_string2' => $html_string2]);
    }

    public function addCustomer()
    {
        $customer                 = new Customer;

        $customer->user_id        = Auth::user()->id;
        if (Auth::user()->role_id == 3) {
            $customer->primary_sale_id = Auth::user()->id;
        }
        $customer->country        = 217;
        $customer->status         = 0;
        $customer->save();

        $customer_billing                 = new CustomerBillingDetail;
        $customer_billing->customer_id    = $customer->id;
        $customer_billing->billing_country = 217;
        $customer_billing->title          = 'Default Address';
        $customer_billing->is_default     = '1';
        //    $customer_billing->billing_zip = 0 ;
        $customer_billing->save();
        return response()->json(['id' => $customer->id]);
        // dd($customer->id);
        // return redirect('sales/get-customer-detail/'.$customer->id);
    }

    public function accountRecievable()
    {
        //open_invoices   payments  account_receivable  delete_transaction
        $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'account_receivable')->first();
        if (Auth::user()->role_id == 9) {
            $payments = Order::where('primary_status', 3)->where('ecommerce_order', 1)->where('status', 31)->sum('total_amount');
            $open_invoices = 0.00;
            $account_receivable = 0.00;
        } else {
            if (Auth::user()->role_id == 3) {
                $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(), Auth::user()->secondary_customer->pluck('id')->toArray());
                $open_invoices = Order::where('primary_status', 3)->whereIn('customer_id', $all_customer_ids)->where('status', 11)->sum('total_amount');
                $total_paidd = Order::where('primary_status', 3)->whereIn('customer_id', $all_customer_ids)->where('status', 11)->sum('total_paid');

                $purchase_order = PurchaseOrder::where('status', 15)->sum('total_in_thb');
                $total_paid = PurchaseOrder::where('status', 15)->sum('total_paid');
                $payments = ($purchase_order - $total_paid);
                $account_receivable = ($open_invoices - $total_paidd);
            } else {
                $open_invoices = Order::where('primary_status', 3)->where('status', 11)->sum('total_amount');
                $total_paidd = Order::where('primary_status', 3)->where('status', 11)->sum('total_paid');

                $purchase_order = PurchaseOrder::where('status', 15)->sum('total_in_thb');
                $total_paid = PurchaseOrder::where('status', 15)->sum('total_paid');
                $payments = ($purchase_order - $total_paid);
                $account_receivable = ($open_invoices - $total_paidd);
            }
        }

        $current_date = Carbon::now();
        $first_of_month = Carbon::now()->firstOfMonth();
        $delete_transaction = TransactionHistory::where('order_transaction_id', null)->whereBetween('created_at', [$first_of_month->format('Y-m-d') . " 00:00:00", $current_date->format('Y-m-d') . " 23:59:59"])->count();
        if (Auth::user()->role_id == 3) {
            $customers = Customer::where(function ($query) {
                $query->where('primary_sale_id', Auth::user()->id)->orWhere('secondary_sale_id', Auth::user()->id);
            })->where('status', 1)->get();
            // $customers = Customer::whereIn('id',Order::where('primary_status',3)->pluck('customer_id')->toArray())->where('status',1)->where('user_id', Auth::user()->id)->get();
        } else {
            if (Auth::user()->role_id == 9) {
                $customers = Customer::where(function ($query) {
                    $query->where('primary_sale_id', Auth::user()->id)->orWhere('secondary_sale_id', Auth::user()->id);
                })->where('ecommerce_customer', 1)->where('status', 1)->get();
                // $customers = Customer::whereIn('id',Order::where('primary_status',3)->where('ecommerce_order', 1)->pluck('customer_id')->toArray())->where('ecommerce_customer',1)->whereNotNull('reference_name')->get();
            } else {
                $customers = Customer::where('status', 1)->get();
                // $customers = Customer::whereIn('id',Order::where('primary_status',3)->pluck('customer_id')->toArray())->where('status',1)->get();
            }
        }
        $file_name = ExportStatus::where('user_id', Auth::user()->id)->where('type', 'account_receivable_export')->first();
        $sales_persons = User::where('status', 1)->whereNull('parent_id')->where('role_id', 3)->orderBy('name')->get();
        $payment_methods = PaymentType::get();

        $ref_no_config = QuotationConfig::where('section', 'account_receiveable_auto_run_payment_ref_no')->first();

        return view('sales.customer.account-recievable', compact('customers', 'table_hide_columns', 'payment_methods', 'sales_persons', 'open_invoices', 'payments', 'account_receivable', 'delete_transaction', 'file_name', 'ref_no_config'));
    }

    public function exportAccountReceivableInvoices(Request $request)
    {

        $statusCheck = ExportStatus::where('type', 'account_receivable_export')->where('user_id', Auth::user()->id)->first();
        $data = $request->all();
        $sortbyparam = $request->sortbyparam;
        $sortbyvalue = $request->sortbyvalue;
        if ($statusCheck == null) {
            $new = new ExportStatus();
            $new->type = 'account_receivable_export';
            $new->user_id = Auth::user()->id;
            $new->status = 1;
            if ($new->save()) {
                AccountReceivableExport::dispatch($data, Auth::user()->id, Auth::user()->role_id, $sortbyparam, $sortbyvalue);
                return response()->json(['status' => 1]);
            }
        } else if ($statusCheck->status == 0 || $statusCheck->status == 2) {

            ExportStatus::where('type', 'account_receivable_export')->where('user_id', Auth::user()->id)->update(['status' => 1, 'exception' => null]);

            AccountReceivableExport::dispatch($data, Auth::user()->id, Auth::user()->role_id, $sortbyparam, $sortbyvalue);
            return response()->json(['status' => 1]);
        } else {
            return response()->json(['msg' => 'Export already being prepared', 'status' => 2]);
        }

        // if($request->selecting_customerx != null){
        //   $datee = date('Y-m-d H:i:s');
        //   if(Auth::user()->role_id == 3)
        //   {
        //     $query = Order::where('primary_status',3)->where('status',11)->whereIn('customer_id',Customer::select('id')->where('user_id',Auth::user()->id)->pluck('id'))->where('total_amount','!=',0);
        //   }
        //   else
        //   {
        //     $query = Order::where(function($q){
        //       $q->where(function($p){
        //         $p->where('primary_status',3)->where('status',11)->where('total_amount','!=',0);
        //       })->orWhere(function($r){
        //         $r->where('primary_status',25)->where('status',27)->where('total_amount','!=',0);
        //       });
        //     });
        //   }

        //   if($request->from_datex != null)
        //   {
        //     $date = str_replace("/","-",$request->from_datex);
        //     $date =  date('Y-m-d',strtotime($date));
        //     $query = $query->where('orders.converted_to_invoice_on', '>=', $date);

        //   }
        //   if($request->to_datex != null)
        //   {
        //     $date = str_replace("/","-",$request->to_datex);
        //     $date =  date('Y-m-d',strtotime($date));
        //     $query = $query->where('orders.converted_to_invoice_on', '<=', $date);
        //   }
        //   if($request->selecting_customerx == null && $request->order_no == null)
        //   {
        //     $query = $query->where('payment_due_date','<',$datee)->orWhere(function($q){
        //       $q->where('primary_status',25)->where('status',27);
        //     });
        //   }
        //   if($request->selecting_customerx != null)
        //   {
        //     $cust_id = $request->selecting_customerx;
        //     $query = $query->where('customer_id', $request->selecting_customerx)->orWhere(function($q) use ($cust_id){
        //       $q->where('primary_status',25)->where('status',27)->where('customer_id',$cust_id);
        //     });
        //   }

        //   if($request->order_total != null)
        //   {
        //     $query = $query->where(DB::raw("floor(`total_amount`)"),floor($request->order_total));
        //   }

        //   if($request->selecting_salex != null)
        //   {
        //     $query = $query->whereIn('customer_id',User::where('id',$request->selecting_salex)->first()->customer->pluck('id'));
        //   }

        //   if($request->order_nox != null)
        //   {
        //     $result = $request->order_nox;
        //     if (strstr($result,'-'))
        //     {
        //       $query = $query->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");
        //     }
        //     else if(@$result[0] == 'C')
        //     {
        //       $query = $query->where(DB::raw("CONCAT(`status_prefix`,`ref_id`)"), 'LIKE', "%".$result."%");
        //     }
        //     else
        //     {
        //       $resultt = preg_replace("/[^0-9]/", "", $result );
        //       $query = $query->where('in_ref_id',$resultt);
        //     }
        //     if($result[0] == 'C')
        //     {
        //       $query = $query->where('status',27)->where('total_amount','!=',0);
        //     }
        //     else
        //     {
        //       $query = $query->where('status',11)->where('total_amount','!=',0);
        //     }

        //   }

        //   // dd($request->all());
        //   // $date = date('Y-m-d H:i:s');
        //   //   if(Auth::user()->role_id == 3)
        //   //   {
        //   //     $query = Order::where('primary_status',3)->where('status',11)->whereIn('customer_id',Customer::select('id')->where('user_id',Auth::user()->id)->pluck('id'))->where('total_amount','!=',0);
        //   //   }
        //   //   else
        //   //   {
        //   //     $query = Order::where('primary_status',3)->where('status',11)->where('total_amount','!=',0);
        //   //   }

        //   //   if($request->from_datex != null)
        //   //   {
        //   //     $date = str_replace("/","-",$request->from_datex);
        //   //     $date =  date('Y-m-d',strtotime($date));
        //   //     $query->where('converted_to_invoice_on', '>=', $date);
        //   //   }
        //   //   if($request->to_datex != null)
        //   //   {
        //   //     $date = str_replace("/","-",$request->to_datex);
        //   //     $date =  date('Y-m-d',strtotime($date));
        //   //     $query->where('converted_to_invoice_on', '<=', $date);
        //   //   }

        //   //   if($request->selecting_customerx == null && $request->order_no == null)
        //   //   {
        //   //     $query = $query->where('payment_due_date','<',$date);
        //   //   }
        //   //   if($request->selecting_customerx != null)
        //   //   {
        //   //     $query->where('customer_id', $request->selecting_customerx);
        //   //   }

        //   //   if($request->search_by_valx != null)
        //   //   {
        //   //     $query = $query->where(DB::raw("floor(`total_amount`)"),floor($request->search_by_valx));
        //   //   }

        //   //   if($request->selecting_salex != null)
        //   //   {
        //   //     $query = $query->whereIn('customer_id',User::where('id',$request->selecting_salex)->first()->customer->pluck('id'));
        //   //   }

        //   //   if($request->order_nox != null)
        //   //   {
        //   //     $result = $request->order_nox;
        //   //     if (strstr($result,'-'))
        //   //     {
        //   //       $query = $query->where(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");
        //   //     }
        //   //     else
        //   //     {
        //   //       $resultt = preg_replace("/[^0-9]/", "", $result );
        //   //       $query = $query->where('ref_id',$resultt)->orWhere('in_ref_id',$resultt);
        //   //     }
        //   //     $query = $query->where('status',11)->where('total_amount','!=',0);
        //   //   }
        //   $query = $query->get();
        //   $current_date = date("Y-m-d");

        //   return \Excel::download(new AccReceivableExp($query), 'Account Receivable Export'.$current_date.'.xlsx');
        // }else{
        //   return redirect()->route('account-recievable')->with('errorMsg','Please Select Customer First!');
        // }
        // dd($query);
    }

    public function exportAccountTransaction(Request $request)
    {
        // dd($request->all());

        $status = ExportStatus::where('type', 'acc_transaction_exp')->first();
        if ($status == null) {
            $new          = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'acc_transaction_exp';
            $new->status  = 1;
            $new->save();
            AccTransactionExpJob::dispatch($request->from_account_tr_date, $request->to_account_tr_date, $request->customer_account_tr, $request->invoice_account_tr, $request->reference_account_tr, Auth::user()->id, Auth::user()->role_id);
            return response()->json(['msg' => "file is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'acc_transaction_exp')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            AccTransactionExpJob::dispatch($request->from_account_tr_date, $request->to_account_tr_date, $request->customer_account_tr, $request->invoice_account_tr, $request->reference_account_tr, Auth::user()->id, Auth::user()->role_id);
            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }


        //dd($request->all());

        // if($request->from_account_tr_date != null || $request->to_account_tr_date != null || $request->customer_account_tr != null || $request->invoice_account_tr != null || $request->reference_account_tr != null)
        //   {
        //     $query = OrderTransaction::latest();
        //   }
        //   else
        //   {
        //     //dd('here');
        //     $query = OrderTransaction::orderBy('id', 'desc')->limit(10);
        //   }

        //   if($request->from_account_tr_date != null)
        //   {
        //     $date = str_replace("/","-",$request->from_account_tr_date);
        //     $date =  date('Y-m-d',strtotime($date));
        //     $query = $query->whereDate('received_date', '>=', $date);
        //   }
        //   if($request->to_account_tr_date != null)
        //   {
        //     $date = str_replace("/","-",$request->to_account_tr_date);
        //     $date =  date('Y-m-d',strtotime($date));
        //     $query = $query->whereDate('received_date', '<=', $date);
        //   }

        //   if($request->customer_account_tr != null)
        //   {
        //     $query = $query->whereIn('order_id',Order::where('customer_id' , $request->customer_account_tr)->pluck('id')->toArray());

        //   }

        //   if($request->invoice_account_tr != null)
        //   {

        //     $result = $request->invoice_account_tr;
        //         if (strstr($result,'-'))
        //         {
        //           $order = Order::where(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_id`)"), 'LIKE', "%".$result."%");
        //         }
        //         else
        //         {
        //           $resultt = preg_replace("/[^0-9]/", "", $result );
        //           $order = Order::where('ref_id',$resultt)->orWhere('in_ref_id',$resultt);
        //         }

        //         // dd($order->toSql());
        //         $order_id = $order->pluck('id');
        //         // $order_id = Order::where('ref_id','LIKE','%'.$request->order_no.'%')->pluck('id');
        //     $query = @$query->where('order_id',$order_id[0]);
        //   }

        //   if($request->reference_account_tr != null)

        //   {
        //     $payment_ref = OrdersPaymentRef::where('payment_reference_no',$request->reference_account_tr)->first();
        //     $query = @$query->where('payment_reference_no',$payment_ref->id);

        //   }

        //   $query = $query->get();
        //   $current_date = date("Y-m-d");


        //   return \Excel::download(new AccTransactionExport($query), 'Account Transaction'.$current_date.'.xlsx');
    }

    public function recursiveTransactionExp()
    {
        $status = ExportStatus::where('type', 'acc_transaction_exp')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusTransactionExp()
    {
        //dd('here');
        $status = ExportStatus::where('type', 'acc_transaction_exp')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function getTransactionHistory(Request $request)
    {
        $query = TransactionHistory::where('order_transaction_id', '!=', null)->orderBy('id', 'DESC')->get();
        // dd($query);
        return Datatables::of($query)
            ->addColumn('user_name', function ($item) {
                return @$item->user_id != null ? $item->user->name : '--';
            })

            ->addColumn('column_name', function ($item) {
                return @$item->column_name != null ? ucwords(str_replace('_', ' ', $item->column_name)) : '--';
            })

            ->addColumn('invoice_no', function ($item) {
                $order = $item->order;
                // dd($order);
                if ($order->status_prefix !== null || $order->ref_prefix !== null) {
                    return @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                } else {
                    return @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id;
                }
            })

            ->addColumn('old_value', function ($item) {


                return @$item->old_value != null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $item->old_value), 2, '.', ',') : '--';
            })

            ->addColumn('new_value', function ($item) {


                return @$item->new_value != null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $item->new_value), 2, '.', ',') : '--';
            })
            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? $item->created_at->format('d/m/Y') : '--';
            })
            // ->setRowId(function ($item) {
            //   return $item->id;
            // })

            ->rawColumns(['user_name', 'item', 'column_name', 'old_value', 'new_value', 'created_at', 'invoice_no'])
            ->make(true);
    }

    public function getDeletedTransaction(Request $request)
    {
        $query = TransactionHistory::with('user', 'order.customer.primary_sale_person.get_warehouse', 'order.customer.CustomerCategory')->where('order_transaction_id', null)->orderBy('id', 'DESC');
        // dd($query);
        if (Auth::user()->role_id == 9) {
            $query = $query->whereIn('order_id', Order::where('ecommerce_order', 1)->pluck('id')->toArray());
        }

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

            ->addColumn('invoice_no', function ($item) {
                $order = $item->order;
                // dd($order);
                if ($order->status_prefix !== null || $order->ref_prefix !== null) {
                    return @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                } else {
                    return @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id;
                }
            })

            ->addColumn('total_paid', function ($item) {
                return @$item->total_received != null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $item->total_received), 2, '.', ',') : '--';
            })

            ->addColumn('reason', function ($item) {
                return @$item->reason != null ? $item->reason : '--';
            })
            // ->setRowId(function ($item) {
            //   return $item->id;
            // })

            ->rawColumns(['user_name', 'created_at', 'invoice_no', 'total_paid', 'payment_reference_no'])
            ->make(true);
    }

    public function exportOrdersReceipt(Request $request, $customer_id = null, $total_received = null, $orders_a = null, $receipt_date = null)
    {
        $prints_checking = "";
        $all_orders_count = '';
        $receipt_date = preg_replace('/\_/', '/', $receipt_date);
        //Auto Generation of number
        $counter_length  = 4;
        $date = Carbon::now();
        $date = $date->format('ym'); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday

        $c_p_ref = BillingNoCounter::where('ref_no', 'LIKE', "$date%")->where('type', 'receipt')->orderby('id', 'DESC')->first();
        $str = @$c_p_ref->ref_no;
        $onlyIncrementGet = substr($str, 4);
        if ($str == NULL) {
            $onlyIncrementGet = 0;
        }
        $system_gen_no = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
        $system_gen_no = $date . $system_gen_no;

        $billing_ref_no = new BillingNoCounter;
        $billing_ref_no->prefix = 'RV';
        $billing_ref_no->ref_no = $system_gen_no;
        $billing_ref_no->type = 'receipt';
        $billing_ref_no->save();
        // END

        $globalAccessConfig = QuotationConfig::where('section', 'quotation')->first();
        if ($globalAccessConfig) {
            $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "receipt") {
                    $bladePrint = $val['title'];
                }
            }
        } else {
            $bladePrint = 'receipt-invoice-preview';
        }

        if ($bladePrint != 'receipt-invoice-preview') {
            $orders_a = explode(',', @$orders_a);
            $total_received = explode(',', @$total_received);

            $customer = Customer::find($customer_id);
            // $orders = Order::where('id',$orders_a[0])->first();
            $orders = [];
            $total_pages = 0;
            foreach ($orders_a as $id) {
                $ord = Order::find($id);
                $all_orders_count = $ord->order_products->count();
                if ($all_orders_count <= 8) {
                    $total_pages += ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    // if($final_pages == 0)
                    // {
                    //   // $do_pages_count++;
                    // }
                } else {
                    $total_pages == ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    if ($final_pages == 0) {
                        $total_pages++;
                    }
                }
                array_push($orders, $ord);
            }

            $customerAddress = CustomerBillingDetail::where('customer_id', $customer->id)->where('id', $orders[0]->billing_address_id)->first();
        } else {
            $do_pages_count = '';
            $total_pages  = 0;

            $orders_a = explode(',', @$orders_a);
            $total_received = explode(',', @$total_received);

            $customer = Customer::find($customer_id);
            // $orders = Order::whereIn('id',$orders_a)->get();
            $orders = [];
            foreach ($orders_a as $id) {
                $ord = Order::with('get_order_transactions')->find($id);

                array_push($orders, $ord);
            }

            $orders = collect($orders);
            // dd(collect($orders));
            $customerAddress = CustomerBillingDetail::where('customer_id', $customer->id)->where('id', $orders[0]->billing_address_id)->first();
        }
        $pdf = PDF::loadView('accounting.invoices.' . $bladePrint . '', compact('customer', 'orders', 'customerAddress', 'total_received', 'do_pages_count', 'billing_ref_no', 'receipt_date', 'all_orders_count', 'prints_checking', 'total_pages'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        // making pdf name starts
        $makePdfName = 'invoice';
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
        return $pdf->download($makePdfName . '.pdf');
    }

    public function getInvoicesForReceivables(Request $request)
    {
        //dd($request->all());
        if ($request->from_date != null || $request->to_date != null || $request->selecting_customer != null || $request->order_no != null || $request->reference != null) {
            //$query = OrderTransaction::latest();
            $query = OrderTransaction::select('order_transactions.id', 'order_transactions.payment_reference_no', 'order_transactions.received_date', 'order_transactions.order_id', 'order_transactions.customer_id', 'order_transactions.vat_total_paid', 'order_transactions.non_vat_total_paid', 'order_transactions.total_received', 'order_transactions.payment_method_id', 'order_transactions.remarks')->with('order', 'get_payment_type', 'get_payment_ref')->where('payment_reference_no', '!=', NULL)->with('order.customer.primary_sale_person.get_warehouse', 'order.customer.CustomerCategory', 'get_payment_ref', 'get_payment_type');
        } else {
            $query = OrderTransaction::with('order.customer.primary_sale_person.get_warehouse', 'order.customer.CustomerCategory', 'get_payment_ref', 'get_payment_type')->orderBy('id', 'desc')->limit(10)->get();
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            $query = $query->whereDate('received_date', '>=', $date);
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            $query = $query->whereDate('received_date', '<=', $date);
        }

        if ($request->selecting_customer != null) {
            $query = $query->whereIn('order_id', Order::where('customer_id', $request->selecting_customer)->pluck('id')->toArray());
        }

        if ($request->order_no != null) {

            $result = $request->order_no;
            if (strstr($result, '-')) {
                $order = Order::where(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%" . $result . "%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $result . "%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_id`)"), 'LIKE', "%" . $result . "%");
            } else {
                $resultt = preg_replace("/[^0-9]/", "", $result);
                $order = Order::where('ref_id', $resultt)->orWhere('in_ref_id', $resultt);
            }

            // dd($order->toSql());
            $order_id = $order->pluck('id');
            // $order_id = Order::where('ref_id','LIKE','%'.$request->order_no.'%')->pluck('id');
            $query = @$query->whereIn('order_id', $order_id);
        }

        if ($request->reference != null) {

            $payment_ref = OrdersPaymentRef::where('payment_reference_no', $request->reference)->orWhere('auto_payment_ref_no', $request->reference)->first();

            $query = @$query->where('payment_reference_no', $payment_ref->id);
        }
        if (Auth::user()->role_id == 9) {
            $query = $query->whereIn('order_id', Order::where('ecommerce_order', 1)->pluck('id')->toArray());
        }

        return Datatables::of($query)
            ->addColumn('ref_no', function ($item) {
                $order = $item->order;
                if ($order->primary_status == 3) {
                    if ($order->in_status_prefix !== null || $order->in_ref_prefix !== null) {
                        return '<a target="_blank" href="' . route('get-completed-invoices-details', ['id' => $item->order->id]) . '" title="View Products" ><b>' . @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id . '</b></a>';
                    } else {
                        return '<a target="_blank" href="' . route('get-completed-invoices-details', ['id' => $item->order->id]) . '" title="View Products" ><b>' . @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id . '</b></a>';
                    }
                } else if ($order->primary_status == 25) {

                    return '<a target="_blank" href="' . route('get-credit-note-detail', ['id' => $item->order->id]) . '" title="View Products" ><b>' . @$order->status_prefix . $order->ref_id . '</b></a>';
                } else {
                    if ($order->status_prefix !== null || $order->ref_prefix !== null) {
                        return @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                    } else {
                        return @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id;
                    }
                }
            })

            ->addColumn('customer_company', function ($item) {
                // $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.$item->customer->id).'" title="View Detail">'.($item->customer !== null ? $item->customer->company : "N.A").'</a>';
                return $item->order->customer->company != null ? @$item->order->customer->company : 'N.A';
            })

            ->addColumn('customer_reference_name', function ($item) {
                // $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.$item->order->customer->id).'" title="View Detail"><b>'.($item->order->customer !== null ? $item->order->customer->reference_name : "N.A").'</b></a>';
                // return $html_string;
                return $item->order->customer->reference_name != null ? @$item->order->customer->reference_name : 'N.A';
            })

            ->addColumn('invoice_total', function ($item) {
                return $item->order->total_amount != null ? number_format(preg_replace('/(\.\d\d).*/', '$1', round(@$item->order->total_amount, 4)), 2, '.', ',') : 'N.A';
            })

            ->addColumn('total_paid', function ($item) {

                $html_string = '
                <span class="m-l-15 " id="total_received" data-fieldvalue="' . preg_replace('/(\.\d\d).*/', '$1', round(@$item->total_received, 4)) . '">';
                $html_string .= number_format(preg_replace('/(\.\d\d).*/', '$1', round(@$item->total_received, 4)), 2, '.', ',');
                $html_string .= '</span>';

                // $html_string .= '<input type="number"  name="total_received" style="width:100%;" class="fieldFocus d-none" value="'.round($item->total_received,2).'">';

                // dd($html_string);
                return $html_string;

                // return $item->total_received != null ?  number_format($item->total_received,2) : 'N.A';
            })

            ->addColumn('vat_total_paid', function ($item) {
                $vat_total_paid = preg_replace('/(\.\d\d).*/', '$1', round(@$item->vat_total_paid, 4));
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="vat_total_paid" data-fieldvalue="' . @round($item->vat_total_paid, 4) . '">';
                $html_string .= number_format($vat_total_paid, 2, '.', ',');
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="vat_total_paid" style="width:100%;" class="fieldFocus d-none" value="' . round($item->vat_total_paid, 2) . '" pattern="^\d*(\.\d{0,2})?$">';

                // dd($html_string);
                return $html_string;

                // return $item->vat_total_paid != null ?  number_format($item->vat_total_paid,2) : 'N.A';
            })

            ->addColumn('non_vat_total_paid', function ($item) {
                $non_vat_total_paid = preg_replace('/(\.\d\d).*/', '$1', round(@$item->non_vat_total_paid, 4));
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="non_vat_total_paid" data-fieldvalue="' . @round($item->non_vat_total_paid, 4) . '">';
                $html_string .= number_format($non_vat_total_paid, 2, '.', ',');
                $html_string .= '</span>';


                $html_string .= '<input type="number"  name="non_vat_total_paid" style="width:100%;" class="fieldFocus d-none" value="' . round($item->non_vat_total_paid, 2) . '" pattern="^\d*(\.\d{0,2})?$">';

                // dd($html_string);
                return $html_string;

                // return $item->non_vat_total_paid != null ?  number_format($item->non_vat_total_paid,2) : 'N.A';
            })

            ->addColumn('difference', function ($item) {
                $diff = $item->order->total_amount - ($item->order->vat_total_paid + $item->order->non_vat_total_paid);
                $diff = number_format(preg_replace('/(\.\d\d).*/', '$1', number_format(@$diff, 4, '.', '')), 2, '.', ',');
                return $diff;
            })

            ->addColumn('received_date', function ($item) {
                return $item->received_date != null ? Carbon::parse(@$item->received_date)->format('d/m/Y') : 'N.A';
            })

            ->addColumn('delivery_date', function ($item) {
                if (@$item->order->primary_status == 25) {
                    return $item->order->credit_note_date != null ? Carbon::parse(@$item->order->credit_note_date)->format('d/m/Y') : 'N.A';
                } else {
                    return $item->order->delivery_request_date != null ? Carbon::parse(@$item->order->delivery_request_date)->format('d/m/Y') : 'N.A';
                }
            })

            ->addColumn('payment_type', function ($item) {
                return $item->payment_method_id != null ? $item->get_payment_type->title : 'N.A';
            })

            ->addColumn('payment_reference_no', function ($item) {
                $ref_no = $item->get_payment_ref->payment_reference_no;
                $ref_no  = $ref_no != null ? $ref_no : $item->get_payment_ref->auto_payment_ref_no;
                return '<a href="javascript:void(0)" class="download_transaction" data-id="' . @$item->payment_reference_no . '"><b>' . $ref_no . '</b></a>';
            })

            ->addColumn('sales_person', function ($item) {
                return $item->order->customer->primary_sale_person != null ? @$item->order->customer->primary_sale_person->name : 'N.A';
            })

            ->addColumn('remarks', function ($item) {
                return $item->remarks != null ? @$item->remarks : '--';
            })

            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon delete_order_transaction" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';

                return $html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['ref_no', 'customer_company', 'invoice_total', 'total_paid', 'received_date', 'payment_type', 'payment_reference_no', 'sales_person', 'delivery_date', 'difference', 'action', 'customer_reference_name', 'vat_total_paid', 'non_vat_total_paid'])
            ->make(true);
    }

    public function PrivateToEcom(Request $request)
    {
        dd($request->all());
        //   foreach($request->quotations as $quot){
        //     $customer = Customer::find($quot);
        // }
        // return response()->json(['success' => true]);
    }

    public function saveTransactionDataIncomplete(Request $request)
    {
        // dd($request->all());
        $completed = 0;
        $reload = 0;
        $order_transaction = OrderTransaction::find($request->trans_id);
        $order = Order::find($order_transaction->order_id);
        // dd($order);
        foreach ($request->except('trans_id', 'old_value') as $key => $value) {
            if ($key == "vat_total_paid") {
                $vat_total = @$order->order_products != null ? @$order->getOrderTotalVatAccounting($order->id, 0) : 0;
                $vat_total = (floatval(preg_replace('/[^\d.]/', '', $vat_total)));
                // dd( $key , $vat_total);
                if ($vat_total == 0) {
                    return response()->json(['success' => 'vat_zero']);
                }
            }

            if ($key == "non_vat_total_paid") {
                $non_vat_total = @$order->order_products != null ? @$order->getOrderTotalVatAccounting($order->id, 2) : 0;
                $non_vat_total = floatval(preg_replace('/[^\d.]/', '', $non_vat_total));
                // dd($key , $non_vat_total);
                if ($non_vat_total == 0) {
                    return response()->json(['success' => 'non_vat_zero']);
                }
            }

            $order->total_paid -= $request->old_value;
            $order->total_paid += $value;
            $order->$key = $value;

            $order_transaction->total_received -= $request->old_value;
            $order_transaction->total_received += $value;
            $order_transaction->$key = $value;

            $transaction_history = new TransactionHistory;
            $transaction_history->user_id = Auth::user()->id;
            $transaction_history->order_id = $order->id;
            $transaction_history->order_transaction_id = $order_transaction->id;
            $transaction_history->column_name = 'total_received';
            $transaction_history->old_value = $request->old_value;
            $transaction_history->new_value = $value;
            $transaction_history->save();
        }

        if (preg_replace('/(\.\d\d).*/', '$1', @$order->total_amount) <= $order->total_paid) {
            if ($order->primary_status == 3) {
                $order->status = 24;
                $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', 'Product')->update([
                    'status' => 24
                ]);
            } elseif ($order->primary_status == 25) {
                $order->status = 31;
                $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', 'Product')->update([
                    'status' => 31
                ]);
            }
        }

        if (preg_replace('/(\.\d\d).*/', '$1', @$order->total_amount) > $order->total_paid) {
            if ($order->primary_status == 3) {
                $order->status = 11;
                $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', 'Product')->update([
                    'status' => 11
                ]);
            } elseif ($order->primary_status == 25) {
                $order->status = 27;
                $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', 'Product')->update([
                    'status' => 27
                ]);
            }
        }

        $order->save();
        $order_transaction->save();

        return response()->json(['success' => true]);
    }

    public function getPaymentRefInvoicesForReceivables(Request $request)
    {
        // dd($request->all());
        $customer_id = $request->selecting_customer;
        $query = OrdersPaymentRef::where('customer_id', $customer_id);

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $query->whereDate('received_date', '>=', $from_date);
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $query->whereDate('received_date', '<=', $to_date);
        }


        return Datatables::of($query)
            ->addColumn('ref_no', function ($item) use ($customer_id) {
                // $html_string = '<a target="_blank" href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" >'.$item->order->ref_id.'</a>';
                $ref = [];
                $order_id = $item->getTransactions->where('customer_id', $customer_id)->pluck('order_id')->unique()->toArray();
                $orders_ref_no = Order::whereIn('id', $order_id)->get();
                foreach ($orders_ref_no as $order) {
                    if ($order->primary_status == 3) {
                        if ($order->in_status_prefix !== null || $order->in_ref_prefix !== null) {
                            array_push($ref,  @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id);
                        } else {
                            array_push($ref,  '<a target="_blank" href="' . route('get-completed-invoices-details', ['id' => $order->id]) . '" title="View Products" ><b>' . @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id . '</b></a>');
                        }
                    } else {
                        if ($order->status_prefix !== null || $order->ref_prefix !== null) {
                            array_push($ref,  @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id);
                        } else {
                            array_push($ref,  @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id);
                        }
                    }
                }
                // dd($ref);
                $orders_ref_no = implode(", ", $ref);
                // dd($orders_ref_no);
                return $orders_ref_no;
            })



            ->addColumn('invoice_total', function ($item) use ($customer_id) {
                $ids = $item->getTransactions->where('customer_id', $customer_id)->pluck('order_id')->unique()->toArray();
                $total_amount = Order::whereIn('id', $ids)->sum('total_amount');
                return number_format($total_amount, 2, '.', ',');
            })

            ->addColumn('total_paid', function ($item) use ($customer_id) {
                $total_paid = $item->getTransactions->where('customer_id', $customer_id)->sum('total_received');
                return number_format($total_paid, 2, '.', ',');
            })

            ->addColumn('reference_name', function ($item) {
                // $total_paid = $item->customer->reference_name;
                // return $item->customer !== null ? $item->customer->reference_name : '--';

                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $item->customer->id) . '" title="View Detail"><b>' . ($item->customer !== null ? $item->customer->reference_name : '--') . '</b></a>';
                return $html_string;
            })
            ->addColumn('reference_number', function ($item) {
                // $total_paid = $item->customer->reference_name;
                // return $item->customer !== null ? $item->customer->reference_number : '--';
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $item->customer->id) . '" title="View Detail"><b>' . ($item->customer !== null ? $item->customer->reference_number : '--') . '</b></a>';
                return $html_string;
            })

            ->addColumn('received_date', function ($item) use ($customer_id) {

                return $item->received_date !== null ? Carbon::parse($item->received_date)->format('d/m/Y') : '--';
            })

            ->addColumn('payment_type', function ($item) use ($customer_id) {
                // $ids = $item->getTransactions->where('customer_id' , $customer_id)->pluck('payment_method_id')->unique()->toArray();
                // $payment_types = PaymentType::whereIn('id' , $ids)->pluck('title')->unique()->toArray();
                // $payment_types = implode (", ", $payment_types);
                // return $payment_types;
                return $item->get_payment_type->title;
            })

            ->addColumn('payment_reference_no', function ($item) use ($customer_id) {
                $html_string = '<a href="javascript:void(0)" class="download_transaction" data-id="' . @$item->id . '"><b>' . @$item->payment_reference_no . '</b></a>';
                return $html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['ref_no', 'invoice_total', 'total_paid', 'received_date', 'payment_type', 'payment_reference_no', 'reference_name', 'reference_number'])
            ->make(true);
    }

    public function getOpenInvoicesReceivedAmount(Request $request)
    {
        // dd($request->all());
        $date = str_replace("/", "-", $request->received_date);
        $received_date =  date('Y-m-d', strtotime($date));
        // dd(number_format($request->total_received[0],2));
        $order_customer = Order::find($request->order_id[0]);

        $order_payment_ref = OrdersPaymentRef::where('payment_reference_no', $request->payment_reference_no)->first();

        $rec_date = str_replace("/", "-", $request->received_date);
        $rec_date =  date('Y-m-d', strtotime($date));

        $order_payment_ref_d_p = OrdersPaymentRef::where('payment_reference_no', $request->payment_reference_no)->where('payment_method', $request->payment_method)->where('received_date', $rec_date)->first();

        // dd($request->all() , $order_payment_ref_d_p , $order_payment_ref);
        $ref_no_config = QuotationConfig::where('section', 'account_receiveable_auto_run_payment_ref_no')->first();
        if ($order_payment_ref == null) {
            $order_payment_ref = new OrdersPaymentRef;
            if ($ref_no_config && $ref_no_config->display_prefrences == 1) {
                $order_payment_ref->auto_payment_ref_no = $request->payment_reference_no;
            }
            else{
                $order_payment_ref->payment_reference_no = $request->payment_reference_no;
            }
            $order_payment_ref->customer_id = $order_customer->customer_id;
            $order_payment_ref->payment_method = $request->payment_method;
            $order_payment_ref->received_date = $received_date;
            $order_payment_ref->save();
        } else if ($order_payment_ref_d_p != null) {
            $order_payment_ref = $order_payment_ref_d_p;
        } else {
            return response()->json(['payment_reference_no' => 'exists']);
        }
        $orders = [];
        $orders = $request->order_id;
        $total_received = [];
        $total_received = $request->total_received;
        // dd($total_received , $orders);
        $i = 0;
        foreach ($orders as $order) {
            $order = Order::find($order);
            // dd($total_received[$i++] + $total_received[$i++],$i);
            $vat_total = $total_received[$i++];
            $non_vat_total = $total_received[$i++];
            # code...
            $order_transaction                   = new OrderTransaction;
            $order_transaction->order_id         = $order->id;
            $order_transaction->customer_id         = $order->customer_id;
            $order_transaction->order_ref_no         = $order->ref_id;
            $order_transaction->user_id         = Auth::user()->id;
            $order_transaction->payment_method_id = $request->payment_method;
            $order_transaction->payment_reference_no = $order_payment_ref->id;
            $order_transaction->received_date    = $received_date;
            $order_transaction->total_received   = $vat_total + $non_vat_total;
            $order_transaction->vat_total_paid   = $vat_total;
            $order_transaction->non_vat_total_paid   = $non_vat_total;
            $order_transaction->remarks   = $request->remarks;
            $order_transaction->save();

            $order->total_paid += round($vat_total, 2) + round($non_vat_total, 2);
            $order->vat_total_paid += $vat_total;
            $order->non_vat_total_paid += $non_vat_total;

            if (number_format(preg_replace('/(\.\d\d).*/', '$1', $order->total_amount), 2, '.', '') <= round($order->total_paid, 2)) {
                if (@$order->primary_status == 25) {
                    $order->status = 31;
                    $order_products = OrderProduct::where('order_id', $order->id)->update([
                        'status' => 31
                    ]);
                } else {
                    $order->status = 24;
                    $order_products = OrderProduct::where('order_id', $order->id)->update([
                        'status' => 24
                    ]);
                }
            } elseif (number_format(preg_replace('/(\.\d\d).*/', '$1', $order->total_amount), 2, '.', '') > round($order->total_paid, 2) && round($order->total_paid, 2) > 0) {
                if (@$order->primary_status == 25) {
                    $order->status = 33;
                    $order_products = OrderProduct::where('order_id', $order->id)->update([
                        'status' => 33
                    ]);
                } else {
                    $order->status = 32;
                    $order_products = OrderProduct::where('order_id', $order->id)->update([
                        'status' => 32
                    ]);
                }
            }
            $order->save();

            // $i++;

        }


        return response()->json(['success' => true, 'payment_id' => $order_payment_ref->id]);
    }

    public function getOpenInvoicesForReceivables(Request $request)
    {
        $date = date('Y-m-d H:i:s');
        if (Auth::user()->role_id == 3) {
            $query = Order::where('primary_status', 3)->whereIn('orders.status', [11, 32])->whereIn('customer_id', Customer::select('id')->where('user_id', Auth::user()->id)->pluck('id'))->where('total_amount', '!=', 0)->select('orders.*');
        } else {
            $query = Order::select('orders.*')->where(function ($q) {
                $q->where(function ($p) {
                    $p->where('primary_status', 3)->whereIn('orders.status', [11, 32])->where('total_amount', '!=', 0);
                })->orWhere(function ($r) {
                    $r->where('primary_status', 25)->whereIn('orders.status', [27, 33])->where('total_amount', '!=', 0);
                });
            });
        }

        if ($request->selecting_customer == null && $request->order_no == null) {
            $query = $query->where(function ($q) use ($date) {
                $q->where('payment_due_date', '<', $date)->orWhere(function ($q) {
                    $q->where('primary_status', 25)->whereIn('orders.status', [27, 33]);
                });
            });
        }
        if ($request->selecting_customer != null) {
            $cust_id = $request->selecting_customer;
            $query->where(function ($z) use ($cust_id) {
                $z->where('customer_id', $cust_id)->orWhere(function ($q) use ($cust_id) {
                    $q->where('primary_status', 25)->whereIn('orders.status', [27, 33])->where('customer_id', $cust_id);
                });
            })->where('total_amount', '!=', 0);
        }

        if ($request->from_date != null) {
            $from_date = str_replace("/", "-", $request->from_date);
            $from_date =  date('Y-m-d', strtotime($from_date));
            $query = $query->where('orders.converted_to_invoice_on', '>=', $from_date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $to_date = str_replace("/", "-", $request->to_date);
            $to_date =  date('Y-m-d', strtotime($to_date));
            $query = $query->where('orders.converted_to_invoice_on', '<=', $to_date . ' 23:59:59');
        }
        if ($request->order_total != null) {
            $query = $query->where(DB::raw("floor(`total_amount`)"), floor($request->order_total));
        }

        if ($request->selecting_sale != null) {
            $query = $query->whereIn('customer_id', User::where('id', $request->selecting_sale)->first()->customer->pluck('id'));
        }

        if ($request->order_no != null) {
            $result = $request->order_no;
            if (strstr($result, '-')) {
                $query = $query->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $result . "%");
            } else if (@$result[0] == 'C') {
                $query = $query->where(DB::raw("CONCAT(`status_prefix`,`ref_id`)"), 'LIKE', "%" . $result . "%");
            } else {
                $resultt = preg_replace("/[^0-9]/", "", $result);
                $query = $query->where('in_ref_id', $resultt);
            }
            if ($result[0] == 'C') {
                $query = $query->whereIn('orders.status', [27, 33])->where('total_amount', '!=', 0);
            } else {
                $query = $query->whereIn('orders.status', [11, 32])->where('total_amount', '!=', 0);
            }
        }

        $ids = (clone $query)->pluck('orders.id')->toArray();
        $sub_t = OrderProduct::select('total_price', 'order_id')->whereIn('order_id', $ids)->sum('total_price');
        $total = (clone $query)->sum('total_amount');
        $query = $query->with('customer.primary_sale_person.get_warehouse', 'customer.CustomerCategory', 'order_products', 'get_order_transactions', 'order_products_vat_2');

        Customer::doSort($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['delivery_date', 'invoice_date', 'payment_due_date', 'amount_due', 'amount_paid', 'total_received_non_vat', 'total_received', 'sales_person', 'sub_total_amount', 'invoice_total', 'sub_total_2', 'reference_id_vat_2', 'vat_1', 'sub_total_1', 'reference_id_vat', 'customer_company', 'customer_reference_name', 'ref_no', 'checkbox'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return Order::returnAddColumnAccountReceivable($column, $item);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->rawColumns(['ref_no', 'customer_company', 'invoice_total', 'total_received', 'amount_paid', 'payment_due_date', 'sales_person', 'amount_due', 'checkbox', 'delivery_date', 'reference_id_vat', 'sub_total_1', 'vat_1', 'reference_id_vat_2', 'sub_total_2', 'customer_reference_name', 'total_received_non_vat', 'sub_total_amount']);
        $dt->with(['sub_t' => $sub_t, 'total' => $total]);
        return $dt->make(true);
    }

    public function getCustomerOrders(Request $request)
    {
        if (Auth::user()->role_id == 3) {
            $query = Customer::whereHas('customer_orders', function ($q) {
                $q->where('primary_status', 3);
            })->where('user_id', Auth::user()->id)->where('status', 1);
        } else {
            $query = Customer::whereHas('customer_orders', function ($q) {
                $q->where('primary_status', 3);
            })->where('status', 1);
        }
        if ($request->selecting_customer != null) {
            $query = $query->where('id', $request->selecting_customer);
        }
        if (Auth::user()->role_id == 9) {
            $query = $query->where('ecommerce_customer', 1);
        }
        $query = $query->with('customer_orders', 'get_order_transactions');
        return Datatables::of($query)

            ->addColumn('customer_company', function ($item) {
                $html_string = '<span>' . ($item->reference_name !== null ? $item->company : "N.A") . '</span>';
                return  $html_string;
            })

            ->addColumn('customer_reference_name', function ($item) {
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $item->id) . '" title="View Detail"><b>' . ($item->reference_name !== null ? $item->reference_name : "N.A") . '</b></a>';
                return  $html_string;
            })

            ->addColumn('total', function ($item) {
                $date = date('Y-m-d H:i:s');
                if (Auth::user()->role_id == 3) {

                    $total = $item->customer_orders->where('primary_status', 3)->where('user_id', Auth::user()->id)->sum('total_amount');
                } else {
                    $total = $item->customer_orders->whereIn('primary_status', [3, 25])->sum('total_amount');
                }


                // dd( $total );

                $total = number_format($total, 2);
                return  $total;
            })


            ->addColumn('total_due', function ($item) {
                $date = date('Y-m-d H:i:s');
                $orders = null;
                if (Auth::user()->role_id == 3) {

                    $orders = $item->customer_orders->where('primary_status', 3)->where('user_id', Auth::user()->id);
                } else {
                    $orders = $item->customer_orders->whereIn('primary_status', [3, 25]);
                }
                // $total_not_due = OrderTransaction::whereIn('order_id',$orders->pluck('id'))->sum('total_received');
                $total_not_due = $item->get_order_transactions->sum('total_received');
                $total = $orders->sum('total_amount');


                $total_due = round($total, 2) - round($total_not_due, 2);
                return number_format($total_due, 2, '.', ',');
            })

            ->addColumn('total_not_due', function ($item) {

                $date = date('Y-m-d H:i:s');
                if (Auth::user()->role_id == 3) {

                    $order_ids = $item->customer_orders->where('primary_status', 3)->where('user_id', Auth::user()->id)->pluck('id')->toArray();
                } else {
                    $order_ids = $item->customer_orders->whereIn('primary_status', [3, 25])->pluck('id')->toArray();
                }
                // $total_not_due = OrderTransaction::whereIn('order_id',$order_ids)->sum('total_received');
                $total_not_due = $item->get_order_transactions->sum('total_received');
                return number_format($total_not_due, 2, '.', ',');
            })

            ->addColumn('overdue', function ($item) {
                $date = date('Y-m-d H:i:s');
                if (Auth::user()->role_id == 3) {

                    $orders = $item->customer_orders->where('primary_status', 3)->where('payment_due_date', '<', $date)->where('user_id', Auth::user()->id);
                } else {
                    $orders = $item->customer_orders->whereIn('primary_status', [3, 25])->where('payment_due_date', '<', $date);
                }
                $total = $orders->sum('total_amount');
                // $total_not_due = OrderTransaction::whereIn('order_id',$orders->pluck('id'))->sum('total_received');
                $total_not_due = $item->get_order_transactions->sum('total_received');

                $overdue = round($total, 2) - round($total_not_due, 2);
                $html_string = '<span style="color:red;">' . number_format($overdue, 2, '.', ',') . '</span>';
                return @$html_string;
            })


            ->addColumn('action', function ($item) {
                $html_string = '<a href="' . url('sales/customer-transaction-detail/' . $item->id) . '" class="actionicon" title="View Detail"><i class="fa fa-eye"></i></a>';

                return @$html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['customer_company', 'total', 'total_due', 'total_not_due', 'overdue', 'action', 'customer_reference_name'])
            ->make(true);
    }

    public function getCustomerTransactionDetail($id)
    {
        // dd('hi');
        $customer = Customer::find($id);

        if (Auth::user()->role_id == 3) {
            $customers = Customer::whereIn('id', Order::where('primary_status', 3)->pluck('customer_id')->toArray())->where('status', 1)->where('user_id', Auth::user()->id)->get();
        } else {
            $customers = Customer::whereIn('id', Order::where('primary_status', 3)->pluck('customer_id')->toArray())->where('status', 1)->get();
        }

        return view('sales.customer.customer-transaction-detail', compact('customer', 'customers', 'id'));
    }

    public function getCustomerContact(Request $request)
    {
        $query = CustomerContact::where('customer_id', $request->id)->get();
        return Datatables::of($query)
            ->addColumn('name', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="name"  data-fieldvalue="' . @$item->name . '">' . (@$item->name != NULL ? @$item->name : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="name" class="fieldFocusContact d-none" value="' . @$item->name . '">';
                return $html_string;
            })
            ->addColumn('sur_name', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="sur_name"  data-fieldvalue="' . @$item->sur_name . '">' . (@$item->sur_name != NULL ? @$item->sur_name : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="sur_name" class="fieldFocusContact d-none" value="' . @$item->sur_name . '">';
                return $html_string;
            })

            ->addColumn('email', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="email"  data-fieldvalue="' . @$item->email . '">' . (@$item->email != NULL ? @$item->email : "--") . '</span>
                <input type="email" autocomplete="nope" style="width:100%;" name="email" class="fieldFocusContact d-none" value="' . @$item->email . '">';
                return $html_string;
            })

            ->addColumn('telehone_number', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="telehone_number"  data-fieldvalue="' . @$item->telehone_number . '">' . (@$item->telehone_number != NULL ? @$item->telehone_number : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="telehone_number" class="fieldFocusContact d-none" value="' . @$item->telehone_number . '">';
                return $html_string;
            })

            ->addColumn('postion', function ($item) {
                $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="postion"  data-fieldvalue="' . @$item->postion . '">' . (@$item->postion != NULL ? @$item->postion : "--") . '</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="postion" class="fieldFocusContact d-none" value="' . @$item->postion . '">';
                return $html_string;
            })
            ->addColumn('action', function ($item) {
                if ($item->is_default != 1) {
                    $html_string = '
                     <a href="javascript:void(0);" class="actionicon deleteIcon deleteCustomerContact" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                     ';

                    return $html_string;
                }
                return '--';
            })
            ->addColumn('is_default', function ($item) {
                $checked = '';
                if ($item->is_default == 1) {
                    $checked = 'checked';
                }
                $html_string = '
                 <input type="checkbox" name="is_default" class="default_customer" data-id="' . $item->id . '" '.$checked.'>
                 ';

                return $html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['name', 'sur_name', 'email', 'telehone_number', 'postion', 'action', 'is_default'])
            ->make(true);
    }

    public function getCustomerCompanyAddresses(Request $request)
    {
        $query = CustomerBillingDetail::where('customer_id', $request->id)->get();
        $states = State::where('country_id', 217)->get();

        return Datatables::of($query)
            ->addColumn('reference_name', function ($item) {
                $html_string = '<span id="billing-title" class="m-l-15 inputDoubleClick"> ' . (@$item->title != null ? $item->title : "N.A") . '</span><input type="text" name="title" class="billing-fieldFocus d-none" value=' . (@$item->title != null ? $item->title : "--") . '>';
                return $html_string;
            })
            ->addColumn('phone_no', function ($item) {
                $html_string = '
                <span id="billing-primary-contact" class="m-l-15 inputDoubleClick"> ' . (@$item->billing_phone != NULL ? @$item->billing_phone : "--") . '</span>';
                $html_string .= '
          <input type="text" name="billing_phone" class="billing-fieldFocus d-none" value="' . (@$item->billing_phone != null ? $item->billing_phone : "--") . '">';
                return $html_string;
            })
            ->addColumn('cell_no', function ($item) {
                $html_string = '
               <span id="cell_number" class="m-l-15 inputDoubleClick">' . (@$item->cell_number != null ? $item->cell_number : "--") . '</span><input type="text" name="cell_number" class="billing-fieldFocus d-none" value="' . (@$item->cell_number != null ? $item->cell_number : "--") . '">';
                return $html_string;
            })
            ->addColumn('address', function ($item) {
                $html_string = '<span id="billing-address" class="m-l-15 inputDoubleClick"> ' . (@$item->billing_address != NULL ? @$item->billing_address : "--") . '</span><input type="text" name="billing_address" class="billing-fieldFocus d-none" value="' . (@$item->billing_address != null ? $item->billing_address : "--") . '">';
                return $html_string;
            })
            ->addColumn('tax_id', function ($item) {
                $html_string = '
              <span id="cell_number" class="m-l-15 inputDoubleClick">' . (@$item->tax_id != null ? $item->tax_id : "--") . '</span>
             <input type="text" name="tax_id" class="billing-fieldFocus d-none" value="' . (@$item->tax_id != null ? $item->tax_id : "--") . '">';
                return $html_string;
            })
            ->addColumn('email', function ($item) {
                $html_string = '
              <span id="billing-email" class="m-l-15 inputDoubleClick"> ' . (@$item->billing_email != null ? $item->billing_email : "--") . '</span><input type="email" name="billing_email" class="billing-fieldFocus d-none" value="' . (@$item->billing_email != null ? $item->billing_email : "--") . '">';
                return $html_string;
            })
            ->addColumn('fax', function ($item) {
                $html_string = '
             <span id="billing-fax" class="m-l-15 inputDoubleClick"> ' . (@$item->billing_fax != null ? $item->billing_fax : "--") . '</span>
          <input type="number" name="billing_fax" class="billing-fieldFocus d-none" value="' . (@$item->billing_fax != null ? $item->billing_fax : "--") . '">';
                return $html_string;
            })
            ->addColumn('state', function ($item) use ($states) {
                $html_string = '
            <span id="billing-statee" class="m-l-15 inputDoubleClick">
            ' . (@$item->billing_state != Null ? @$item->getstate->name : "--") . '
          </span><div class="d-none state-div"><select class="form-control state-tags update_state" id="billing-state" name="billing_state"><option selected="selected">Select District</option>';
                foreach ($states as $state) {
                    if ($state->id == @$item->getstate->id) {

                        $html_string .= '<option value="' . $state->id . '" selected="true">' . $state->name . '</option>';
                    } else {

                        $html_string .= '<option value="' . $state->id . '">' . $state->name . '</option>';
                    }
                }
                $html_string .= '</select>
              </div>';
                return $html_string;
            })
            ->addColumn('city', function ($item) {
                $html_string = '
             <span id="billing-city" class="m-l-15 inputDoubleClick">
            ' . (@$item->billing_city != Null ? @$item->billing_city : "--") . '</span>
          <input type="text" name="billing_city" class="billing-fieldFocus d-none" value="' . (@$item->billing_city != null ? $item->billing_city : "--") . '">';
                return $html_string;
            })
            ->addColumn('zip', function ($item) {
                $html_string = '
            <span id="billing-zip" class="m-l-15 inputDoubleClick"> ' . (@$item->billing_zip != null ? $item->billing_zip : "--") . ' </span>
          <input type="number" name="billing_zip" class="billing-fieldFocus d-none" value="' . (@$item->billing_zip != null ? $item->billing_zip : "--") . '">';
                return $html_string;
            })
            ->addColumn('is_default', function ($item) {
                if (@$item->is_default == 1)
                    $html_string = '<span id="billing_default">Yes</span>';
                else
                    $html_string = '<span id="">
          <input type="checkbox" id="is_default" class=" is_default " name="is_default">
              <input type="hidden" id="is_default_value" class="form-control" name="is_default_value" value="0">
              <input type="hidden" id="billing_address_id" class="form-control" name="is_default_value" value="' . @$item->id . '">

          </span>';
                return $html_string;
            })


            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['reference_name', 'phone_no', 'cell_no', 'address', 'tax_id', 'email', 'fax', 'state', 'city', 'zip', 'is_default'])
            ->make(true);
    }

    public function getFixPriceExcel(Request $request)
    {
        //dd($request->all());
        if ($request->suppliers == null && $request->primary_category == null && $request->sub_category == null) {
            $name = 'ALL';
        } else {
            $name = 'FILTERED';
        }
        return Excel::download(new CustomerProductFixedPriceExport($request->suppliers, $request->primary_category, $request->sub_category, $request->customer_id), $name . ' PRODUCTS DATA.xlsx');
    }

    public function uploadFixPricesBulk(Request $request)
    {

        $validator = $request->validate([
            'excel' => 'required|mimes:csv,xlsx,xls'
        ]);
        try {
            Excel::import(new CustomerProductFixedPriceImport($request->customer_id), $request->file('excel'));
        } catch (\Exception $e) {
            if ($e->getMessage() == 'Please do not upload empty file') {
                return redirect()->back()->with('errormsg', $e->getMessage());
            } elseif ($e->getMessage() == 'Please Upload Valid File') {
                return redirect()->back()->with('errormsg', $e->getMessage());
            }
        }

        return redirect()->back()->with('successmsg', 'File Imported Sucessfully!');
    }

    public function saveCusContactsData(Request $request)
    {
        $customer_contacts = CustomerContact::where('id', $request->id)->where('customer_id', $request->customer_id)->first();

        foreach ($request->except('customer_id', 'id') as $key => $value) {
            if ($value == '') {
                // $customer_contacts->$key = null;
            } else {
                $customer_contacts->$key = $value;
            }
        }
        $customer_contacts->save();
        return response()->json(['success' => true]);
    }

    public function getCustomerGeneralDocuments(Request $request)
    {
        if (@$request->al == true) {
            $query = CustomerGeneralDocument::where('customer_id', $request->id)->get();
        } else {

            $query = CustomerGeneralDocument::where('customer_id', $request->id)->limit(5);
        }
        // dd($query->get());
        return Datatables::of($query)

            ->addColumn('date', function ($item) {
                return $item->created_at !== null ? $item->created_at->format('d/m/Y') : 'N.A';
            })
            ->addColumn('file_name', function ($item) {
                return $item->file_name !== null ? $item->file_name : 'N.A';
            })
            ->addColumn('description', function ($item) {
                return $item->description ? $item->description : "N.A";
            })
            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteGeneralDocument" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';

                $html_string .= '<a href="' . asset('public/uploads/documents/' . $item->file_name) . '" class="actionicon download" data-id="' . @$item->file_name . '" title="Download"><i class="fa fa-download"></i></a>';

                return $html_string;
            })
            ->rawColumns(['file_name', 'description', 'date', 'action'])
            ->make(true);



        //    ->setRowId(function ($item) {
        //           return $item->id;
        //       })
        //    // yellowRow is a custom style in style.css file
        //    ->setRowClass(function ($item) {
        //         if($item->product == null){
        //     return  'yellowRow';
        //           }

        // })

    }

    public function deleteCustomerGeneralDocuments(Request $request)
    {
        $deleteProduct = CustomerGeneralDocument::where('id', $request->id)->delete();
        $totalDocuments = CustomerGeneralDocument::select('id')->where('id', '!=', $request->id)->where('customer_id', $request->customer_id)->count();

        return response()->json(['success' => true, 'totalDocuments' => $totalDocuments]);
    }

    public function getCustomerDocuments(Request $request)
    {
        $id = $request->id;
        $customer = Customer::where('id', $id)->first();
        return view('sales.customer.customer-documents', compact('id', 'customer'));
    }

    public function getCustomerProductFixedPrices(Request $request)
    {
        $id = $request->id;
        $customer = Customer::where('id', $id)->first();
        $ProductCustomerFixedPrice = ProductCustomerFixedPrice::where('customer_id', $id)->get();
        return view('sales.customer.customer-product-fixedprices', compact('id', 'customer', 'ProductCustomerFixedPrice'));
    }

    public function deleteCustomerContact(Request $request)
    {
        $deleteProduct = CustomerContact::where('id', $request->id)->delete();
        return response()->json(['success' => true]);
    }

    public function getData(Request $request)
    {
        $check_group = 0;
        $query = Customer::query();
        if (Auth::user()->role_id == 4) {
            $warehouse_id = Auth::user()->warehouse_id;
            $users = User::where(function ($q) {
                $q->where('role_id', 3)->orWhere('role_id', 4)->orWhere('role_id', 1);
            })->where('warehouse_id', $warehouse_id)->whereNull('parent_id')->pluck('id')->toArray();

            $u_id = Auth::user()->id;
            $query->select('customers.*')
                ->where(function ($q) use ($users, $u_id) {
                    $q->whereIn('primary_sale_id', $users)->orWhereHas('CustomerSecondaryUser', function ($q) use ($users) {
                        $q->WhereIn('user_id', $users);
                    })->orWhereIn('user_id', [$u_id]);
                });
        } else {
            $query->select('customers.*');
        }
        $user_id = $request->user_id;
        if ($request->customers_status !== null) {
            if ($request->user_id !== null) {
                if ($request->customers_type != '') {
                    if ($request->customers_type == 0) {
                        $query->where('customers.status', $request->customers_status)->where(function ($query) use ($user_id) {
                            $query->whereHas('CustomerSecondaryUser', function ($q) use ($user_id) {
                                $q->where('user_id', $user_id);
                            });
                        });
                    } else if ($request->customers_type == 1) {
                        $query->where('customers.status', $request->customers_status)->where(function ($query) use ($user_id) {
                            $query->where('primary_sale_id', $user_id);
                        });
                    }
                } else {
                    $query->where('customers.status', $request->customers_status)->where(function ($query) use ($user_id) {
                        $query->where('primary_sale_id', $user_id)->orWhereHas('CustomerSecondaryUser', function ($q) use ($user_id) {
                            $q->where('user_id', $user_id);
                        });
                    });
                }
            } else {
                $query->where('customers.status', $request->customers_status);
            }
        } else if ($request->customers_status === null) {
            if ($request->user_id !== null) {
                if ($request->customers_type != '') {
                    if ($request->customers_type == 0) {
                        $query->whereIn('customers.status', [0, 1, 2])->where(function ($query) use ($user_id) {
                            $query->whereHas('CustomerSecondaryUser', function ($q) use ($user_id) {
                                $q->where('user_id', $user_id);
                            });
                        });
                    } else if ($request->customers_type == 1) {
                        $query->whereIn('customers.status', [0, 1, 2])->where(function ($query) use ($user_id) {
                            $query->where('primary_sale_id', $user_id);
                        });
                    }
                } else {
                    $query->whereIn('customers.status', [0, 1, 2])->where(function ($query) use ($user_id) {
                        $query->where('primary_sale_id', $user_id)->orWhereHas('CustomerSecondaryUser', function ($query) use ($user_id) {
                            $query->where('user_id', $user_id);
                        });
                    });
                }
            } else {
                $query->whereIn('customers.status', [0, 1, 2]);
            }
        }

        if ($request->selecting_customer_group != null) {
            $check_group = $request->selecting_customer_group;
            $query->where('category_id', @$request->selecting_customer_group);
        }

        if (Auth::user()->role_id == 9) {
            if ($check_group == 4) {
                $query->get();
            } else {
                $query->where('ecommerce_customer', 1);
            }
        }
        /*********************  Sorting code ************************/
        $query = Customer::CustomerlIstSorting($request, $query);
        /*********************************************/
        $query->with('CustomerCategory', 'primary_sale_person:id,name', 'CustomerSecondaryUser.secondarySalesPersons', 'getcountry:id,name', 'getstate', 'getpayment_term:id,title', 'getbilling.getstate', 'getbilling.getcountry', 'getnotes');

        $add_columns = ['checkbox', 'address', 'category', 'user_id', 'secondary_sp', 'country', 'state', 'credit_term', 'email', 'city', 'postalcode', 'created_at', 'draft_orders', 'total_orders', 'last_order_date', 'status', 'action', 'notes', 'tax_id', 'address_reference'];

        $dt = Datatables::of($query);
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return Customer::returnAddColumn($column, $item);
            });
        }

        $edit_columns = ['reference_name', 'reference_number', 'company', 'phone'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return Customer::returnEditColumn($column, $item);
            });
        }

        $filter_columns = ['email', 'user_id', 'city', 'state', 'category', 'credit_term', 'company'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return Customer::returnFilterColumn($column, $item, $keyword);
            });
        }



        $dt->rawColumns(['action', 'status', 'country', 'state', 'email', 'city', 'postalcode', 'notes', 'created_at', 'draft_orders', 'total_orders', 'last_order_date', 'notess', 'reference_name', 'checkbox', 'reference_number', 'user_id', 'secondary_sp', 'company']);
        return $dt->make(true);
    }

    public function exportCustomerData(Request $request)
    {
        $status = ExportStatus::where('type', 'customer_list_report')->first();
        if ($status == null) {
            $new          = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'customer_list_report';
            $new->status  = 1;
            $new->save();
            CustomerExpJob::dispatch($request->customers_status_exp, $request->sales_person_exp, $request->customers_type_exp, $request->selecting_customer_group_exp, Auth::user()->id, Auth::user()->role_id, $request->sortbyparam, $request->sortbyvalue, $request->search_value);
            return response()->json(['msg' => "file is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'customer_list_report')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            CustomerExpJob::dispatch($request->customers_status_exp, $request->sales_person_exp, $request->customers_type_exp, $request->selecting_customer_group_exp, Auth::user()->id, Auth::user()->role_id, $request->sortbyparam, $request->sortbyvalue, $request->search_value);
            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckCustomerList()
    {
        $status = ExportStatus::where('type', 'customer_list_report')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusFirstTimeForCustomerList()
    {
        // dd('here');
        $status = ExportStatus::where('type', 'customer_list_report')->where('user_id', Auth::user()->id)->first();
        // dd($status);
        if ($status != null) {
            return response()->json(['status' => $status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }


    public function bulkUploadCustomersForm(Request $request)
    {
        return view('sales.customer.add-bulk-customers');
    }

    public function getTempCustomers(Request $request)
    {
        $all_users = User::whereNull('parent_id')->get();
        $customer_categories = CustomerCategory::where('is_deleted', 0)->get();
        $credit_terms = PaymentTerm::all();
        $payment_types = PaymentType::all();
        $temp_customers = TempCustomers::with('primary_sales_person', 'secondary_sales_person', 'customer_category', 'payment_term');
        // dd($temp_customers);

        return Datatables::of($temp_customers)
            ->addColumn('checkbox', function ($item) {

                $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="' . $item->id . '" id="customer_check_' . $item->id . '">
                                <label class="custom-control-label" for="customer_check_' . $item->id . '"></label>
                              </div>';
                return $html_string;
            })

            // ->addColumn('reference_number',function($item){
            //     // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
            //     $error_alert = "";
            //     if($item->reference_number == null)
            //     {
            //         $html_string = '
            //     <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="'.@$item->reference_number.'">';
            //     $html_string .= '--';
            //     $html_string .= '</span>';

            //     $html_string .= '<input type="text"  name="reference_number" class="fieldFocus d-none" value="">';
            //     }
            //     else
            //     {

            //       $reference_number = Customer::where('reference_number',@$item->reference_number)->first();
            //       if($reference_number == null)
            //       {
            //         $error_alert = "alert-danger";
            //       }

            //         $html_string = '
            //     <span class="m-l-15 inputDoubleClick '.$error_alert.' " id="reference_number"  data-fieldvalue="'.@$item->reference_number.'">';
            //     $html_string .= $item->reference_number;
            //     $html_string .= '</span>';

            //     $html_string .= '<input type="text"  name="reference_number" class="fieldFocus d-none" value="'.$item->reference_number .'">';
            //     }
            //     return $html_string;
            // })

            ->addColumn('reference_name', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->reference_name == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->reference_name . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="reference_name" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="reference_name"  data-fieldvalue="' . @$item->reference_name . '">';
                    $html_string .= $item->reference_name;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="reference_name" class="fieldFocus d-none" value="' . $item->reference_name . '">';
                }
                return $html_string;
            })

            ->addColumn('sales_person', function ($item) use ($all_users) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                $error_alert = "";

                if ($item->sales_person == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->sales_person . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="sales_person" class="fieldFocus d-none" value="">';

                    $html_string .= '<select name="sales_person" id="sales_person" class="select-common form-control sales_person d-none"><option disabled value="">Select sales_person</option>';
                    foreach ($all_users as $usr) {


                        $html_string .= '<option value="' . $usr->id . '" >' . $usr->name . '</option>';
                    }

                    $html_string .= '</select>';
                } else {
                    // $user = User::where('name', $item->sales_person)->first();
                    $user = $item->primary_sales_person;
                    if ($user == null) {
                        $error_alert = "alert-danger";
                    }
                    $html_string = '
                <span class="m-l-15 inputDoubleClick ' . $error_alert . ' " id="sales_person"  data-fieldvalue="' . @$item->sales_person . '">';
                    $html_string .= $item->sales_person;
                    $html_string .= '</span>';


                    $html_string .= '<select name="sales_person" id="sales_person" class="select-common form-control sales_person d-none"><option disabled value="">Select sales_person</option>';
                    foreach ($all_users as $usr) {
                        $variable = '';
                        if ($usr->name == $item->sales_person) {
                            $variable = "selected";
                        }

                        $html_string .= '<option value="' . $usr->id . '" ' . $variable . '>' . $usr->name . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })

            ->addColumn('secondary_sale', function ($item) use ($all_users) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                $error_alert = "";
                // $all_users = User::whereNull('parent_id')->get();
                if ($item->secondary_sale == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->secondary_sale . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="secondary_sale" class="fieldFocus d-none" value="">';

                    $html_string .= '<select name="secondary_sale" id="secondary_sale" class="select-common form-control sales_person d-none"><option disabled value="">Select Secondary Sale</option>';
                    foreach ($all_users as $usr) {


                        $html_string .= '<option value="' . $usr->id . '" >' . $usr->name . '</option>';
                    }

                    $html_string .= '</select>';
                } else {
                    // $user = User::where('name', $item->secondary_sale)->first();
                    $user = $item->secondary_sales_person;
                    if ($user == null) {
                        $error_alert = "alert-danger";
                    }
                    $html_string = '
                <span class="m-l-15 inputDoubleClick ' . $error_alert . ' " id="secondary_sale"  data-fieldvalue="' . @$item->secondary_sale . '">';
                    $html_string .= $item->secondary_sale;
                    $html_string .= '</span>';


                    $html_string .= '<select name="secondary_sale" id="secondary_sale" class="select-common form-control secondary_sale d-none"><option disabled value="">Select Secondary Sale</option>';
                    foreach ($all_users as $usr) {
                        $variable = '';
                        if ($usr->name == $item->secondary_sale) {
                            $variable = "selected";
                        }

                        $html_string .= '<option value="' . $usr->id . '" ' . $variable . '>' . $usr->name . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })

            ->addColumn('company_name', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->company_name == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->company_name . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="company_name" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="company_name"  data-fieldvalue="' . @$item->company_name . '">';
                    $html_string .= $item->company_name;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="company_name" class="fieldFocus d-none" value="' . $item->company_name . '">';
                }
                return $html_string;
            })

            ->addColumn('classification', function ($item) use ($customer_categories) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                $error_alert = "";
                // $customer_categories = CustomerCategory::where('is_deleted', 0)->get();
                if ($item->classification == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->classification . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<select name="classification" id="classification" class="select-common form-control classification d-none"><option disabled value="">Select Classification</option>';
                    foreach ($customer_categories as $customer_cat) {

                        $html_string .= '<option value="' . $customer_cat->id . '">' . $customer_cat->title . '</option>';
                    }

                    $html_string .= '</select>';
                } else {

                    // $customer_category = CustomerCategory::where('is_deleted', 0)->where('title', $item->classification)->first();
                    $customer_category = $item->customer_category;
                    if ($customer_category == null) {
                        $error_alert = "alert-danger";
                    }

                    $html_string = '
                <span class="m-l-15 inputDoubleClick ' . $error_alert . ' " id="classification"  data-fieldvalue="' . @$item->classification . '">';
                    $html_string .= $item->classification;
                    $html_string .= '</span>';

                    $html_string .= '<select name="classification" id="classification" class="select-common form-control classification d-none"><option disabled value="">Select Classification</option>';
                    foreach ($customer_categories as $customer_cat) {
                        $variable = '';
                        if ($customer_cat->title == $item->classification) {
                            $variable = "selected";
                        }

                        $html_string .= '<option value="' . $customer_cat->id . '" ' . $variable . '>' . $customer_cat->title . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })

            ->addColumn('credit_term', function ($item) use ($credit_terms) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                $error_alert = "";
                // $credit_terms = PaymentTerm::all();

                if ($item->credit_term == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->credit_term . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    // $html_string .= '<input type="text"  name="credit_term" class="fieldFocus d-none" value="">';

                    $html_string .= '<select name="credit_term" id="credit_term" class="select-common form-control credit_term d-none"><option disabled value="">Select credit_term</option>';
                    foreach ($credit_terms as $credit_term) {


                        $html_string .= '<option value="' . $credit_term->id . '" >' . $credit_term->title . '</option>';
                    }

                    $html_string .= '</select>';
                } else {

                    // $credit_term = PaymentTerm::where('title', $item->credit_term)->first();

                    $credit_term = $item->credit_term;
                    if ($credit_term == null) {
                        $error_alert = "alert-danger";
                    }

                    $html_string = '
                <span class="m-l-15 inputDoubleClick ' . $error_alert . ' " id="credit_term"  data-fieldvalue="' . @$item->credit_term . '">';
                    $html_string .= $item->credit_term;
                    $html_string .= '</span>';

                    // $html_string .= '<input type="text"  name="credit_term" class="fieldFocus d-none" value="'.$item->credit_term .'">';

                    $html_string .= '<select name="credit_term" id="credit_term" class="select-common form-control credit_term d-none"><option disabled value="">Select credit_term</option>';
                    foreach ($credit_terms as $credit_term) {
                        $variable = '';
                        if ($credit_term->title == $item->credit_term) {
                            $variable = "selected";
                        }

                        $html_string .= '<option value="' . $credit_term->id . '" ' . $variable . '>' . $credit_term->title . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })

            ->addColumn('payment_method', function ($item) use ($payment_types) {
                // $payment_types = PaymentType::all();
                $error_alert = "";
                $html_string = "";

                $payment_methods = explode(',', preg_replace('/\s+/', '', $item->payment_method));


                if ($item->payment_method == null) {
                    $text_color = 'color: red;';
                    $html_string .= '
                <span class="m-l-15 inputDoubleClick" id="payment_methodd" data-fieldvalue="' . @$item->type_id . '">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';

                    $html_string .= '<select name="payment_method[]" id="payment_method" multiple class="select-common form-control payment_method d-none">';
                    foreach ($payment_types as $type) {
                        $html_string .= '<option value="' . $type->id . '" >' . $type->title . '</option>';
                    }
                    $html_string .= '</select>';
                } else {

                    foreach ($payment_methods as $payment_meth) {
                        $payment_meth_check = PaymentType::where('title', $payment_meth)->first();
                        if ($payment_meth_check == null) {
                            $error_alert = "alert-danger";
                        }
                    }


                    $html_string .= '
                <span class="m-l-15 inputDoubleClick ' . $error_alert . ' " id="payment_methodd"  data-fieldvalue="' . @$item->payment_method . '">';
                    $html_string .= @$item->payment_method;
                    $html_string .= '</span>';

                    $html_string .= '<select name="payment_method[]" id="payment_method" multiple class="select-common form-control payment_method d-none">';
                    foreach ($payment_types as $type) {
                        $variable = '';
                        foreach ($payment_methods as $p) {

                            if ($p == $type->title) {
                                $variable = "selected";
                            }
                            # code...
                        }
                        $html_string .= '<option value="' . $type->id . '" ' . $variable . '>' . $type->title . '</option>';
                    }

                    $html_string .= '</select>';
                }
                return $html_string;
            })

            ->addColumn('address_reference_name', function ($item) {
                $error_alert = "";
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->address_reference_name == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->address_reference_name . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="address_reference_name" class="fieldFocus d-none" value="">';
                } else {

                    // $address_reference_name = CustomerBillingDetail::where('title',$item->address_reference_name)->first();

                    // if($address_reference_name == null)
                    // {
                    //   $error_alert = "alert-danger" ;
                    // }

                    $html_string = '
                <span class="m-l-15 inputDoubleClick ' . $error_alert . ' " id="address_reference_name"  data-fieldvalue="' . @$item->address_reference_name . '">';
                    $html_string .= $item->address_reference_name;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="address_reference_name" class="fieldFocus d-none" value="' . $item->address_reference_name . '">';
                }
                return $html_string;
            })

            ->addColumn('phone_no', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->phone_no == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->phone_no . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="phone_no" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="phone_no"  data-fieldvalue="' . @$item->phone_no . '">';
                    $html_string .= $item->phone_no;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="phone_no" class="fieldFocus d-none" value="' . $item->phone_no . '">';
                }
                return $html_string;
            })

            ->addColumn('cell_no', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->cell_no == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->cell_no . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="cell_no" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="cell_no"  data-fieldvalue="' . @$item->cell_no . '">';
                    $html_string .= $item->cell_no;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="cell_no" class="fieldFocus d-none" value="' . $item->cell_no . '">';
                }
                return $html_string;
            })

            ->addColumn('address', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->address == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->address . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="address" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="address"  data-fieldvalue="' . @$item->address . '">';
                    $html_string .= $item->address;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="address" class="fieldFocus d-none" value="' . $item->address . '">';
                }
                return $html_string;
            })

            ->addColumn('tax_id', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->tax_id == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->tax_id . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="tax_id" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="tax_id"  data-fieldvalue="' . @$item->tax_id . '">';
                    $html_string .= $item->tax_id;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="tax_id" class="fieldFocus d-none" value="' . $item->tax_id . '">';
                }
                return $html_string;
            })

            ->addColumn('email', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->email == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->email . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="email" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="email"  data-fieldvalue="' . @$item->email . '">';
                    $html_string .= $item->email;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="email" class="fieldFocus d-none" value="' . $item->email . '">';
                }
                return $html_string;
            })

            ->addColumn('fax', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->fax == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->fax . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="fax" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="fax"  data-fieldvalue="' . @$item->fax . '">';
                    $html_string .= $item->fax;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="fax" class="fieldFocus d-none" value="' . $item->fax . '">';
                }
                return $html_string;
            })

            ->addColumn('state', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->state == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->state . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="state" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="state"  data-fieldvalue="' . @$item->state . '">';
                    $html_string .= $item->state;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="state" class="fieldFocus d-none" value="' . $item->state . '">';
                }
                return $html_string;
            })

            ->addColumn('city', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->city == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->city . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="city" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="city"  data-fieldvalue="' . @$item->city . '">';
                    $html_string .= $item->city;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="city" class="fieldFocus d-none" value="' . $item->city . '">';
                }
                return $html_string;
            })

            ->addColumn('zip', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->zip == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->zip . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="zip" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="zip"  data-fieldvalue="' . @$item->zip . '">';
                    $html_string .= $item->zip;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="zip" class="fieldFocus d-none" value="' . $item->zip . '">';
                }
                return $html_string;
            })

            ->addColumn('contact_name', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->contact_name == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->contact_name . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_name" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="contact_name"  data-fieldvalue="' . @$item->contact_name . '">';
                    $html_string .= $item->contact_name;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_name" class="fieldFocus d-none" value="' . $item->contact_name . '">';
                }
                return $html_string;
            })

            ->addColumn('contact_sur_name', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->contact_sur_name == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->contact_sur_name . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_sur_name" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="contact_sur_name"  data-fieldvalue="' . @$item->contact_sur_name . '">';
                    $html_string .= $item->contact_sur_name;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_sur_name" class="fieldFocus d-none" value="' . $item->contact_sur_name . '">';
                }
                return $html_string;
            })

            ->addColumn('contact_email', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->contact_email == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->contact_email . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_email" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="contact_email"  data-fieldvalue="' . @$item->contact_email . '">';
                    $html_string .= $item->contact_email;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_email" class="fieldFocus d-none" value="' . $item->contact_email . '">';
                }
                return $html_string;
            })

            ->addColumn('contact_tel', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->contact_tel == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->contact_tel . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_tel" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="contact_tel"  data-fieldvalue="' . @$item->contact_tel . '">';
                    $html_string .= $item->contact_tel;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_tel" class="fieldFocus d-none" value="' . $item->contact_tel . '">';
                }
                return $html_string;
            })

            ->addColumn('contact_position', function ($item) {
                // return (@$item->expiry != null) ? @$item->expiry." Kg":'-';
                if ($item->contact_position == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="expiry"  data-fieldvalue="' . @$item->contact_position . '">';
                    $html_string .= '--';
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_position" class="fieldFocus d-none" value="">';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick" id="contact_position"  data-fieldvalue="' . @$item->contact_position . '">';
                    $html_string .= $item->contact_position;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text"  name="contact_position" class="fieldFocus d-none" value="' . $item->contact_position . '">';
                }
                return $html_string;
            })

            ->addColumn('created_at', function ($item) {
                return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
            })

            // ->setRowClass(function ($item)  {
            //   return $item->id % 2 == 0 ? 'alert-info' : '';
            // })

            ->setRowId(function ($item) {
                return @$item->id;
            })

            ->rawColumns(['created_at', 'checkbox', 'reference_number', 'reference_name', 'sales_person', 'company_name', 'classification', 'credit_term', 'payment_method', 'address_reference_name', 'phone_no', 'cell_no', 'address', 'tax_id', 'email', 'fax', 'state', 'city', 'zip', 'contact_name', 'contact_sur_name', 'contact_email', 'contact_tel', 'contact_position', 'secondary_sale'])
            ->make(true);
    }

    public function saveTempCustomerDataa(Request $request)
    {
        $temp_customer = TempCustomers::find($request->temp_customer_id);
        $status = 1;
        foreach ($request->except('temp_customer_id') as $key => $value) {
            // dd($key , $value);
            $temp_customer->$key = $value;
        }
        // if($temp_customer->reference_number != null)
        //   {
        //       $reference_number = Customer::where('reference_number',$temp_customer->reference_number)->first();
        //       if($reference_number !== null)
        //       {
        //         $status = 0 ;
        //       }
        //   }

        if ($temp_customer->classification != null) {
            $customer_category = CustomerCategory::where('is_deleted', 0)->where('title', $temp_customer->classification)->first();
            if ($customer_category == null) {
                $status = 0;
            }
        }

        if ($temp_customer->sales_person != null) {
            $user = User::where('name', $temp_customer->sales_person)->first();
            if ($user == null) {
                $status = 0;
            }
        }

        // if($temp_customer->payment_method != null)
        //   {
        //       $payment_methods = explode(',',preg_replace('/\s+/', '', $temp_customer->payment_method));
        //       foreach ($payment_methods as $payment_meth) {
        //         $payment_type = PaymentType::where('title',$payment_meth)->first();
        //         if($payment_type == null)
        //         {
        //           $status = 0 ;
        //         }
        //       }
        //   }

        // if($temp_customer->address_reference_name != null)
        // {
        //     $address_reference_name = CustomerBillingDetail::where('title',$temp_customer->address_reference_name)->first();

        //     if($address_reference_name == null)
        //     {
        //       $status = 0 ;
        //     }
        // }

        // if($temp_customer->credit_term != null)
        // {

        //     $credit_term = PaymentTerm::where('title',$temp_customer->credit_term)->first();
        //     if($credit_term == null)
        //     {
        //       $status = 0 ;
        //     }
        // }
        // dd($status);
        $temp_customer->status = $status;
        $temp_customer->save();
        return response()->json(['success' => true]);
    }

    public function deleteTempCustomers(Request $request)
    {
        if (isset($request->selected_customers)) {
            $multi_customers = explode(',', $request->selected_customers);
            $delete_temp_customers = TempCustomers::whereIn('id', $multi_customers)->delete();
            return response()->json(['success' => true]);
        }
    }

    public function bulkUploadCustomers(Request $request)
    {
        $validator = $request->validate([
            'customer_excel' => 'required'
        ]);

        $extensions = array("xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw");
        $result = array($request->file('customer_excel')->getClientOriginalExtension());

        if (in_array($result[0], $extensions)) {
            $excel = Excel::import(new CustomerBulkImport(), $request->file('customer_excel'));
            // dd($excel);
            ImportFileHistory::insertRecordIntoDb(Auth::user()->id, 'TEMP BULK CUSTOMERS', $request->file('customer_excel'));
            return redirect()->back();
        } else {
            return redirect()->back();
        }
    }

    public function moveCustomersToInventory(Request $request)
    {
        if (isset($request->selected_temp_customers)) {
            $errorMsg = '';
            $hasError = 0;
            $selected_temp_customers = explode(',', $request->selected_temp_customers);
            $temp_customers = TempCustomers::whereIn('id', $selected_temp_customers)->where('status', 1)->get();
            if ($temp_customers->count() > 0) {
                foreach ($temp_customers as $temp_customer) {
                    if ($temp_customer->reference_number != null) {
                        $customer = Customer::where('reference_number', $temp_customer->reference_number)->first();
                        if ($customer == null) {
                            $customer = new Customer();
                        }
                        $customer->reference_number = $temp_customer->reference_number;
                        $customer->reference_name = $temp_customer->reference_name;
                        $customer->company = $temp_customer->company_name;

                        $customer_category = CustomerCategory::where('is_deleted', 0)->where('title', $temp_customer->classification)->first();

                        $customer->category_id = @$customer_category->id;

                        $credit_term_name = PaymentTerm::where('title',$temp_customer->credit_term)->first();
                        $credit_term_name = $credit_term_name != null ? $credit_term_name->id : 1;
                        $customer->credit_term = $credit_term_name;

                        $user = User::where('name', $temp_customer->sales_person)->first();
                        $secondary_user = User::where('name', $temp_customer->secondary_sale)->first();

                        $customer->user_id = @$user->id;
                        $customer->primary_sale_id = @$user->id;
                        $customer->secondary_sale_id = @$secondary_user->id;

                        $customer->save();

                        $payment_methods = explode(',', $temp_customer->payment_method);
                        $payment_types = PaymentType::whereIn('title', $payment_methods)->get();
                        if($payment_types->count() > 0){
                            foreach ($payment_types as $payment_type) {
                                $if_exist_payment_method = CustomerPaymentType::where('customer_id', $customer->id)->where('payment_type_id', $payment_type->id)->first();
                                if ($if_exist_payment_method == null) {
                                    $customer_payment_type = new CustomerPaymentType;
                                    $customer_payment_type->customer_id = $customer->id;
                                    $customer_payment_type->payment_type_id = $payment_type->id;
                                    $customer_payment_type->save();
                                }
                            }
                        }else{
                            $payment_type = PaymentType::where('title', 'Cash')->first();
                            $customer_payment_type = new CustomerPaymentType;
                            $customer_payment_type->customer_id = $customer->id;
                            $customer_payment_type->payment_type_id = @$payment_type->id;
                            $customer_payment_type->save();
                        }

                        if (
                            $temp_customer->address_reference_name != null ||
                            $temp_customer->phone_no != null ||
                            $temp_customer->cell_no != null ||
                            $temp_customer->address != null ||
                            $temp_customer->tax_id != null ||
                            $temp_customer->email != null ||
                            $temp_customer->fax != null ||
                            $temp_customer->state != null ||
                            $temp_customer->city != null ||
                            $temp_customer->zip != null
                        ) {
                            $customer_billing_detail = CustomerBillingDetail::where('customer_id', $customer->id)->where('is_default', 1)->first();
                            if ($customer_billing_detail == null) {
                                $customer_billing_detail = new CustomerBillingDetail();
                            }
                            $customer_billing_detail->customer_id = @$customer->id;
                            $customer_billing_detail->title = $temp_customer->address_reference_name;
                            $customer_billing_detail->billing_phone = $temp_customer->phone_no;
                            $customer_billing_detail->cell_number = $temp_customer->cell_no;
                            $customer_billing_detail->billing_address = $temp_customer->address;
                            $customer_billing_detail->tax_id = $temp_customer->tax_id;
                            $customer_billing_detail->billing_email = $temp_customer->email;
                            $customer_billing_detail->billing_fax = $temp_customer->fax;
                            $customer_billing_detail->billing_country = 217;
                            $customer_billing_detail->is_default = 1;
                            $c_state = State::where('name', $temp_customer->city)->first();
                            $customer_billing_detail->billing_state = @$c_state->id;
                            $customer_billing_detail->billing_city = $temp_customer->state;
                            $customer_billing_detail->billing_zip = $temp_customer->zip;
                            $customer_billing_detail->save();
                        }

                        if (
                            $temp_customer->contact_name != null ||
                            $temp_customer->contact_sur_name != null ||
                            $temp_customer->contact_email != null ||
                            $temp_customer->contact_tel != null ||
                            $temp_customer->contact_position != null
                        ) {
                            $customer_contact = CustomerContact::where('customer_id', $customer->id)->first();
                            if ($customer_contact == null) {
                                $customer_contact = new CustomerContact;
                            }
                            $customer_contact->customer_id = $customer->id;
                            $customer_contact->name = $temp_customer->contact_name;
                            $customer_contact->sur_name = $temp_customer->contact_sur_name;
                            $customer_contact->email = $temp_customer->contact_email;
                            $customer_contact->telehone_number = $temp_customer->contact_tel;
                            $customer_contact->postion = $temp_customer->contact_position;
                            $customer_contact->save();
                        }

                        $temp_customer->delete();
                    } elseif ($temp_customer->reference_name != null) {
                        $checkRefName = Customer::where('reference_name', $temp_customer->reference_name)->first();

                        if ($checkRefName) {

                            $hasError = 1;
                            $errorMsg .= '<ol>';
                            $errorMsg .= "Customer with this Refrence name '<b>" . $temp_customer->reference_name . "</b>' already exist." . "<br>";
                            $errorMsg .= '</ol>';
                        } else {
                            $customer = new Customer;

                            $customer->reference_name = $temp_customer->reference_name;
                            $customer->reference_number = @$temp_customer->reference_number;

                            $customer->company = $temp_customer->company_name;

                            $customer_category = CustomerCategory::where('is_deleted', 0)->where('title', $temp_customer->classification)->first();

                            $customer->category_id = @$customer_category->id;

                            $credit_term_name = PaymentTerm::where('title',$temp_customer->credit_term)->first();
                            $credit_term_name = $credit_term_name != null ? $credit_term_name->id : 1;
                            $customer->credit_term = $credit_term_name;

                            $user = User::where('name', $temp_customer->sales_person)->first();
                            $secondary_user = User::where('name', $temp_customer->secondary_sale)->first();
                            $customer->user_id = Auth()->user()->id;
                            $customer->primary_sale_id = @$user->id;
                            $customer->secondary_sale_id = @$secondary_user->id;
                            $customer->status = 0;

                            $customer->save();

                            $payment_methods = explode(',', $temp_customer->payment_method);
                            $payment_types = PaymentType::whereIn('title', $payment_methods)->get();
                            if($payment_types->count() > 0){
                                foreach ($payment_types as $payment_type) {
                                    $customer_payment_type = new CustomerPaymentType;
                                    $customer_payment_type->customer_id = $customer->id;
                                    $customer_payment_type->payment_type_id = $payment_type->id;
                                    $customer_payment_type->save();
                                }
                            }else{
                                $payment_type = PaymentType::where('title', 'Cash')->first();
                                $customer_payment_type = new CustomerPaymentType;
                                $customer_payment_type->customer_id = $customer->id;
                                $customer_payment_type->payment_type_id = @$payment_type->id;
                                $customer_payment_type->save();
                            }

                            if (
                                $temp_customer->address_reference_name != null ||
                                $temp_customer->phone_no != null ||
                                $temp_customer->cell_no != null ||
                                $temp_customer->address != null ||
                                $temp_customer->tax_id != null ||
                                $temp_customer->email != null ||
                                $temp_customer->fax != null ||
                                $temp_customer->state != null ||
                                $temp_customer->city != null ||
                                $temp_customer->zip != null
                            ) {
                                $customer_billing_detail = new CustomerBillingDetail;
                                $customer_billing_detail->customer_id = $customer->id;
                                $customer_billing_detail->title = $temp_customer->address_reference_name;
                                $customer_billing_detail->billing_phone = $temp_customer->phone_no;
                                $customer_billing_detail->cell_number = $temp_customer->cell_no;
                                $customer_billing_detail->billing_address = $temp_customer->address;
                                $customer_billing_detail->tax_id = $temp_customer->tax_id;
                                $customer_billing_detail->billing_email = $temp_customer->email;
                                $customer_billing_detail->billing_fax = $temp_customer->fax;
                                $customer_billing_detail->billing_country = 217;
                                $customer_billing_detail->is_default = 1;
                                $c_state = State::where('name', $temp_customer->city)->first();
                                $customer_billing_detail->billing_state = @$c_state->id;
                                $customer_billing_detail->billing_city = $temp_customer->state;
                                $customer_billing_detail->billing_zip = $temp_customer->zip;
                                $customer_billing_detail->save();
                            }

                            if (
                                $temp_customer->contact_name != null ||
                                $temp_customer->contact_sur_name != null ||
                                $temp_customer->contact_email != null ||
                                $temp_customer->contact_tel != null ||
                                $temp_customer->contact_position != null
                            ) {
                                $customer_contact = new CustomerContact;
                                $customer_contact->customer_id = $customer->id;
                                $customer_contact->name = $temp_customer->contact_name;
                                $customer_contact->sur_name = $temp_customer->contact_sur_name;
                                $customer_contact->email = $temp_customer->contact_email;
                                $customer_contact->telehone_number = $temp_customer->contact_tel;
                                $customer_contact->postion = $temp_customer->contact_position;
                                $customer_contact->save();
                            }


                            $customer_detail = Customer::find($customer->id);
                            $missingPrams = array();

                            if ($customer_detail->reference_name == null) {
                                $missingPrams[] = 'Reference Name';
                            }

                            if ($customer_detail->company == null) {
                                $missingPrams[] = 'Company';
                            }

                            if ($customer_detail->category_id == null) {
                                $missingPrams[] = 'Category';
                            }


                            // if($customer_detail->credit_term == null)
                            // {
                            //   $missingPrams[] = 'Credit term';
                            // }

                            $check_payment_term = CustomerPaymentType::where('customer_id', $customer->id)->whereNotNull('payment_type_id')->select('id');
                            // if($check_payment_term->count() == 0){
                            //   $missingPrams[] = 'Payment Term';
                            // }

                            $billing_detail = CustomerBillingDetail::where('customer_id', $customer->id)->whereNotNull('title')
                                ->whereNotNull('billing_email')->whereNotNull('billing_phone')->whereNotNull('billing_address')->whereNotNull('billing_country')
                                ->whereNotNull('billing_state')->whereNotNull('tax_id')->whereNotNull('billing_city')->whereNotNull('billing_zip')->where('is_default', 1)->select('id', 'status', 'billing_email')->first();
                            // dd($billing_detail);
                            if ($billing_detail != null) {
                                if ($billing_detail->count() == 0) {
                                    $missingPrams[] = 'Billing  details missing';
                                }
                            } else if ($billing_detail == null) {
                                $missingPrams[] = 'Billing  details missing';
                            }


                            // if(sizeof($missingPrams) == 0)
                            // {
                            if ($customer_detail->reference_number == null) {
                                $customer = Customer::orderby('id', 'DESC')->first();

                                if ($customer_detail->category_id == 1) {
                                    $prefix = 'RC';
                                } elseif ($customer_detail->category_id == 2) {
                                    $prefix = 'HC';
                                } elseif ($customer_detail->category_id == 3) {
                                    $prefix = 'RC';
                                } elseif ($customer_detail->category_id == 4) {
                                    $prefix = 'PC';
                                } elseif ($customer_detail->category_id == 5) {
                                    $prefix = 'CC';
                                } elseif ($customer_detail->category_id == 6) {
                                    $prefix = 'EC';
                                }

                                // $c_p_ref = Customer::where('reference_number','LIKE',"%$prefix%")->where('category_id',$customer_detail->category_id)->orderby('id','DESC')->first();

                                $c_p_ref = Customer::where('category_id', $customer_detail->category_id)->orderby('reference_no', 'DESC')->first();

                                $str = @$c_p_ref->reference_no;
                                if ($str  == NULL) {
                                    $str = "0";
                                }
                                // $matches = array();
                                // preg_match('/([a-zA-Z]+)(\d+)/', $str, $matches );
                                // $system_gen_no =  $prefix.str_pad(@$matches[2] + 1, STR_PAD_LEFT);
                                // $customer_detail->reference_number = $system_gen_no;
                                $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
                                $customer_detail->reference_number      = $prefix . $system_gen_no;
                                $customer_detail->reference_no          = $system_gen_no;
                            }
                            $customer_detail->status = 1;
                            $customer_detail->save();
                            if ($billing_detail != null) {
                                $billing_detail->status = 1;
                                $billing_detail->save();
                            }
                            // }

                            $temp_customer->delete();
                        }
                    }
                }

                if ($hasError == 0) {
                    return response()->json(['success' => true]);
                } else {
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            } else {
                return response()->json(['customers' => 'incomplete']);
            }
        }

        return response()->json(['success' => false]);
    }

    public function updateCustomerProfile(Request $request, $id)
    {
        $customer = Customer::where('id', $id)->first();
        if (File::exists("public/uploads/sales/customer/logos/" . $customer->logo)) {
            File::delete("public/uploads/sales/customer/logos/" . $customer->logo);
        }
        if ($request->hasFile('logo') && $request->logo->isValid()) {
            $fileNameWithExt = $request->file('logo')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
            $extension = $request->file('logo')->getClientOriginalExtension();
            $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
            $path = $request->file('logo')->move('public/uploads/sales/customer/logos/', $fileNameToStore);
            $customer->logo = $fileNameToStore;
        }

        $customer->save();
        return redirect()->back();
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

    public function getCustomerDetail($id)
    {
        $customer = Customer::with('getcountry', 'getstate', 'CustomerSecondaryUser', 'CustomerCategory', 'getpayment_term:id,title', 'customer_payment_types.get_payment_type')->where('id', $id)->first();
        $customerShipping = CustomerShippingDetail::with('getcountry', 'getstate')->where('customer_id', $id)->latest()->first(); //Here it will return only one result but we have many
        $customerBilling = CustomerBillingDetail::with('getcountry', 'getstate')->where('customer_id', $id)->where('is_default', 1)->first(); //Here it will return only one result but we have many
        $states = State::select('id', 'name', 'thai_name')->orderby('name', 'ASC')->where('country_id', @$customerBilling->billing_country)->get();

        if (!$customerBilling) {
            $customerBilling = CustomerBillingDetail::with('getcountry', 'getstate')->where('customer_id', $id)->latest()->first(); //Here it will return only one result but we have many
        }

        $customerNotes = CustomerNote::with('getuser')->where('customer_id', $id)->get(); //Here it will return only one result but we have many
        // $countries = Country::orderby('name', 'ASC')->get();
        $ProductCustomerFixedPrice = ProductCustomerFixedPrice::with('products', 'customers')->where('customer_id', $id)->get();
        $categories = CustomerCategory::select('id', 'title')->where('is_deleted', 0)->get();
        // $getCustShipping = CustomerShippingDetail::where('customer_id',$id)->latest()->get();
        $getCustBilling = CustomerBillingDetail::select('id', 'title')->where('customer_id', $id)->latest()->get();
        $paymentTerms = PaymentTerm::select('id', 'title')->get();
        $paymentTypes = PaymentType::select('id', 'title')->where("visible_in_customer", 1)->get();
        // $customer_contacts = CustomerContact::where('customer_id' , $id)->get();

        $orders_data = Order::where('customer_id', $id)->where('primary_status', 3)->where(function ($query) {
            $query->where('status', 11)->orWhere('status', 24);
        })->orderBy('converted_to_invoice_on', 'ASC')->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->converted_to_invoice_on)->format('M Y');
            });
        // dd($orders_data);
        $outstandings = 0;
        foreach ($orders_data as $order) {
            $outstandings += number_format($order->sum('total_amount') - $order->where('status', 24)->sum('total_paid'), 2, '.', '');
        }

        $customer_order_docs = OrderAttachment::whereHas('get_order', function ($q) use ($id) {
            $q->where('primary_status', 3)->where('customer_id', $id);
            $q->where('status', 11);
        })->orderBy('order_id', 'ASC')->get();

        // $suppliers = Supplier::all();
        // $primary_category = ProductCategory::where('parent_id',0)->get();

        $sales_or_coordinators = User::where('status', 1)->whereNull('parent_id')->where('role_id', 3)->get();
        $ecommerceconfig = QuotationConfig::where('section', 'ecommerce_configuration')->first();
        if ($ecommerceconfig) {
            $search_array_config = unserialize($ecommerceconfig->print_prefrences);
        } else {
            $search_array_config = [];
        }
        $cust_histry = CustomerHistory::with('customers:id,reference_name')->find($id);
        // $cust_histry = CustomerHistory::with('customers:id,first_name')->find($id);
        // return response()->json([$cust_histry->customers->reference_name]);
        // return response()->json([$cust_histry]);

        $customer_history = CustomerHistory::with(['user:id,name', 'customers:id,first_name', 'customers:id,reference_name'])->where('customer_id', $id)->get();
        // dd($customer_history);
        // return response()->json([$customer_history]);
        $customer_detail_config = QuotationConfig::where('section', 'customer_detail_page')->first();
        $customer_detail_section = '';
        if ($customer_detail_config) {
            $globalaccessForConfig = unserialize($customer_detail_config->print_prefrences);
            foreach ($globalaccessForConfig as $key => $value) {
                if($value['status'] == 1) {
                    $customer_detail_section = $value['slug'];
                }
            }
        }

        return view('sales.customer.customer-detail', compact('cust_histry', 'customer', 'customer_history', 'customerShipping', 'customerBilling', 'customerNotes', 'countries', 'ProductCustomerFixedPrice', 'categories', 'getCustShipping', 'getCustBilling', 'paymentTerms', 'paymentTypes', 'states', 'customer_contacts', 'orders_data', 'customer_order_docs', 'suppliers', 'primary_category', 'sales_or_coordinators', 'products', 'outstandings', 'id', 'search_array_config', 'customer_detail_section'));
    }


    public function deleteCustomerFixedPrice(Request $request)
    {
        // dd($request->all);
        $product_Customer_fixed_price =  ProductCustomerFixedPrice::where('id', $request->id)->delete();
        return response()->json([
            "success" => true,
        ]);
    }
    public function deleteCustomerCompanyAddress(Request $request)
    {
        $checkInOrders = Order::where('billing_address_id', $request->id)->orWhere('shipping_address_id', $request->id)->first();
        if ($checkInOrders) {
            return response()->json(['success' => false]);
        } else {
            $customerBillingDetail =  CustomerBillingDetail::find($request->id);
            $customerBillingDetail->delete();
            return response()->json(['success' => true]);
        }
    }

    public function deleteCustomerNote(Request $request)
    {
        $note_id = $request->id;
        $customer_id = $request->cust_detail_id;

        $customerNote = CustomerNote::where('customer_id', $customer_id)->where('id', $note_id)->delete();
        return response()->json([
            "error" => false,
            // "customerShipping"=>$customerShipping,
            // "new_value" => $new_value
        ]);
    }

    public function saveProductUpdateData(Request $request)
    {
        $cust_detail_id  = $request['cust_detail_id'];
        $field_name      = $request['field_name'];
        $field_value     = $request['field_value'];
        $product_id     = $request['product_id'];

        //dd(Auth::user()->id);

        $product = ProductCustomerFixedPrice::where('product_id', $product_id)->where('customer_id', $cust_detail_id)->first();

        if ($field_name == 'fixed-price') {
            $col_name = 'Customer Price';
            $old_value = number_format(preg_replace('/(\.\d\d).*/', '$1', round(@$product->fixed_price, 4)), 2, '.', ',');
        }
        elseif ($field_name == 'discount') {
            $col_name = 'Customer Price';
            $old_value = number_format(preg_replace('/(\.\d\d).*/', '$1', round(@$product->fixed_price, 4)), 2, '.', ',');
        }
        elseif ($field_name == 'expiration-date') {
            $col_name = 'Expiration Date';
            $old_value = $product->expiration_date;
            // $old_value = date('d/m/y', strtotime($old_value));
        }
        $customer_history = new CustomerHistory;
        $customer_history->user_id = Auth::user()->id;
        $customer_history->customer_id = $cust_detail_id;
        $customer_history->column_name = $col_name;
        $customer_history->old_value = $old_value;
        $customer_history->new_value = $field_value;
        $customer_history->save();

        if ($product) {
            // Getting selling price of customer
            $getCustomer = Customer::find($cust_detail_id);
            if($getCustomer->category_id != null) {
                $ctpmargin = \App\Models\Common\CustomerTypeProductMargin::where('product_id',@$product->products->id)->where('customer_type_id',$getCustomer->category_id)->first();
            } else {
                $ctpmargin = \App\Models\Common\CustomerTypeProductMargin::where('product_id',@$product->products->id)->first();
            }
            $salePrice = $product->products->selling_price+(@$product->products->selling_price*(@$ctpmargin->default_value/100));
            // ends here

            if ($field_name == 'fixed-price') {
                if ($field_value == '' || $field_value == 0) {
                    $product->fixed_price = NULL;

                    $product->price_after_discount = $product->discount != 0 ? ($salePrice) - ($salePrice * $product->discount) / 100 : $salePrice;
                    $product->fixed_price_update_date = Carbon::now();
                } else {
                    $p_cont =  $product->fixed_price;
                    if ($field_value == $p_cont) {
                        return response()->json([
                            "error" => true,
                        ]);
                    } else {
                        $product->fixed_price = $field_value;
                        $product->price_after_discount = $product->discount != 0 ? ($field_value) - ($field_value * $product->discount) / 100 : $field_value;
                        $product->fixed_price_update_date = Carbon::now();
                    }
                }
                $product->save();
                $new_value = $field_value;
                return response()->json([
                    "error" => false,
                    "product" => $product,
                    "new_value" => $new_value
                ]);
            }
            else if ($field_name == 'discount') {
                if ($field_value == '') {
                    $product->discount = NULL;

                    $product->price_after_discount = $product->fixed_price != null ? $product->fixed_price : $salePrice;
                    $product->fixed_price_update_date = Carbon::now();
                } else {
                    $p_cont =  $product->fixed_price;
                    if ($field_value == $p_cont) {
                        return response()->json([
                            "error" => true,
                        ]);
                    } else {
                        $product->discount = $field_value;
                        if ($product->fixed_price != null) {
                            $price_after_discount = $product->fixed_price - ($product->fixed_price * $field_value) / 100;
                        }
                        else{
                            $price_after_discount = $salePrice - ($salePrice * $field_value) / 100;
                        }
                        $product->price_after_discount = $price_after_discount;
                        $product->fixed_price_update_date = Carbon::now();
                    }
                }
                $product->save();
                $new_value = $field_value;
                return response()->json([
                    "error" => false,
                    "product" => $product,
                    "new_value" => $new_value
                ]);
            }
            else if ($field_name == 'expiration-date') {
                if ($field_value == '') {
                    $product->expiration_date = NULL;
                } else {
                    $p_cont =  $product->expiration_date;
                    if ($field_value == $p_cont) {
                        return response()->json([
                            "error" => true,
                        ]);
                    } else {
                        $product->expiration_date = $field_value;
                    }
                }
                $product->save();
                $new_value = $field_value;
                return response()->json([
                    "error" => false,
                    "product" => $product,
                    "new_value" => $new_value
                ]);
            }
        }
    }

    public function saveShippingUpdateData(Request $request)
    {
        $customerShipping = CustomerShippingDetail::where('customer_id', $request->cust_detail_id)->where('id', $request->shipping_id)->first();;

        foreach ($request->except('cust_detail_id', 'shipping_id') as $key => $value) {
            if ($key == 'country') {
                $supp_detail->$key = $value;
                $supp_detail->state = NULL;
            }
            if ($value == '') {
                // $supp_detail->$key = null;
            } else {
                $customerShipping->$key = $value;
            }
        }
        $customerShipping->save();

        return response()->json(['success' => true]);
    }

    public function saveBillingUpdateData(Request $request)
    {
        // dd($request->all());
        $state = null;
        $completed = 0;
        $customerBilling = CustomerBillingDetail::where('customer_id', $request->cust_detail_id)->where('id', $request->billing_id)->first();

        // dd($customerBilling);

        foreach ($request->except('cust_detail_id', 'billing_id') as $key => $value) {
            if ($key == 'country') {
                $customerBilling->$key = $value;
                $customerBilling->billing_state = NULL;
            }
            if ($value == '') {
                // $customerBilling->$key = null;
            } else if ($key == 'no_name') {
                // $customerBilling->$key = null;
            } else {
                $customerBilling->$key = $value;
            }
            if ($key == 'billing_country') {
                $customerBilling->billing_state = null;
                $states = State::where('country_id', $value)->get();
            }
        }
        $customerBilling->save();
        $customer = Customer::find($request->cust_detail_id);
        if ($customer->status == 0) {
            $request->id = $request->cust_detail_id;
            $mark_as_complete = $this->doCustomerCompleted($request);
            // if($mark_as_complete == 100){
            //   return response()->json(['success' => false,'message' => 'Already Taken']);
            // }
            $json_response = json_decode($mark_as_complete->getContent());
            // dd($json_response);
            if ($json_response->success == true) {
                $customer_complete = Customer::find($request->id);
                $customer_complete->status = 1;
                $customer_complete->save();
                $completed = 1;
            }
            if ($json_response->success == false && $json_response->success == 'already_taken') {
                return response()->json(['success' => false, 'message' => 'Already Taken']);
            }
        }
        if ($customerBilling->getstate != null) {
            $state = @$customer->language == 'en' ? $customerBilling->getstate->name : ($customerBilling->getstate->thai_name !== null ? $customerBilling->getstate->thai_name : $customerBilling->getstate->name);
        } else {
            $state = '';
        }

        if ($customerBilling->getcountry != null) {
            $country = @$customer->language == 'en' ? $customerBilling->getcountry->name : ($customerBilling->getcountry->thai_name !== null ? $customerBilling->getcountry->thai_name : $customerBilling->getcountry->name);
        } else {
            $country = '';
        }
        $sta = null;
        if (@$states != null) {
            $sta = '<option disabled="true">Select State</option>';
            foreach (@$states as $state) {
                $stateee = @$customer->language == 'en' ? @$state->name : (@$state->thai_name !== null ? @$state->thai_name : @$state->name);
                $sta .= '<option value=' . $state->id . '>' . $stateee . '</option>';
            }
        }
        // dd($sta);

        return response()->json(['success' => true, 'completed' => $completed, 'state' => $state, 'country' => $country, 'sta' => $sta]);
    }

    public function doCustomerCompleted(Request $request)
    {
        if ($request->id) {
            $customer_detail = Customer::find($request->id);
            $check_ref_name = Customer::where('reference_name', $customer_detail->reference_name)->where('status', '!=', 0)->first();
            if ($check_ref_name != null) {
                return response()->json(['success' => false, 'message' => 'already_taken']);;
            }
            $missingPrams = array();
            if ($customer_detail->category_id == 4) {
                // dd('here');
                if ($customer_detail->company == null) {
                    $missingPrams[] = 'Company';
                }
                if ($customer_detail->reference_name == null) {
                    $missingPrams[] = 'Reference Name';
                }
                // if($customer_detail->credit_term == null)
                // {
                //   $missingPrams[] = 'Credit term';
                // }
                $billing_detail = CustomerBillingDetail::where('customer_id', $request->id)->whereNotNull('billing_phone')->where('is_default', 1)->select('id', 'status', 'billing_email')->first();
                // dd($billing_detail);
                if ($billing_detail != null) {
                    if ($billing_detail->count() == 0) {
                        $missingPrams[] = 'Billing  details missing';
                    }
                } else if ($billing_detail == null) {
                    $missingPrams[] = 'Billing  details missing';
                }

                if (sizeof($missingPrams) == 0) {
                    if ($customer_detail->reference_number == null) {
                        $customer = Customer::orderby('id', 'DESC')->first();

                        if ($customer_detail->category_id == 4) {
                            $prefix = 'PC';
                        }

                        $c_p_ref = Customer::where('category_id', $customer_detail->category_id)->orderby('reference_no', 'DESC')->first();

                        $str = @$c_p_ref->reference_no;
                        if ($str  == NULL) {
                            $str = "0";
                        }

                        // $matches = array();
                        // preg_match('/([a-zA-Z]+)(\d+)/', $str, $matches );
                        // $system_gen_no =  $prefix.str_pad(@$matches[2] + 1, STR_PAD_LEFT);

                        $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
                        $customer_detail->reference_number      = $prefix . $system_gen_no;
                        $customer_detail->reference_no          = $system_gen_no;

                        // $customer_detail->reference_number = $system_gen_no;
                    }
                    $customer_detail->status = 1;
                    $customer_detail->email = $billing_detail->billing_email;
                    $customer_detail->save();

                    if ($billing_detail != null) {
                        $billing_detail->status = 1;
                        $billing_detail->save();
                    }
                    return response()->json(['success' => true]);
                } else {
                    $message = implode(', ', $missingPrams);
                    return response()->json(['success' => false, 'message' => $message]);
                }
            } else {

                if ($customer_detail->reference_name == null) {
                    $missingPrams[] = 'Reference Name';
                }

                if ($customer_detail->company == null) {
                    $missingPrams[] = 'Company';
                }

                if ($customer_detail->category_id == null) {
                    $missingPrams[] = 'Category';
                }


                // if($customer_detail->credit_term == null)
                // {
                //   $missingPrams[] = 'Credit term';
                // }

                if (Auth::user()->role_id == 1 || Auth::user()->role_id == 4 || Auth::user()->role_id == 11) {
                    if ($customer_detail->primary_sale_id == null) {
                        $missingPrams[] = 'Primary Sales Person';
                    }
                }

                /*$check_payment_term = CustomerPaymentType::where('customer_id', $request->id)->whereNotNull('payment_type_id')->select('id');
            if($check_payment_term->count() == 0){
              $missingPrams[] = 'Payment Term';
            }*/

                $billing_detail = CustomerBillingDetail::where('customer_id', $request->id)->whereNotNull('title')
                    ->whereNotNull('billing_email')->whereNotNull('billing_phone')->whereNotNull('billing_address')->whereNotNull('billing_country')
                    ->whereNotNull('billing_state')->whereNotNull('tax_id')->whereNotNull('billing_city')->whereNotNull('billing_zip')->where('is_default', 1)->select('id', 'status', 'billing_email')->first();
                // dd($billing_detail);
                if ($billing_detail != null) {
                    if ($billing_detail->count() == 0) {
                        $missingPrams[] = 'Billing  details missing';
                    }
                } else if ($billing_detail == null) {
                    $missingPrams[] = 'Billing  details missing';
                }


                if (sizeof($missingPrams) == 0) {
                    if ($customer_detail->reference_number == null) {
                        $customer = Customer::orderby('id', 'DESC')->first();

                        if ($customer_detail->category_id == 1) {
                            $prefix = 'RC';
                        } elseif ($customer_detail->category_id == 2) {
                            $prefix = 'HC';
                        } elseif ($customer_detail->category_id == 3) {
                            $prefix = 'RC';
                        } elseif ($customer_detail->category_id == 4) {
                            $prefix = 'PC';
                        } elseif ($customer_detail->category_id == 5) {
                            $prefix = 'CC';
                        } elseif ($customer_detail->category_id == 6) {
                            $prefix = 'EC';
                        }
                        // $c_p_ref = Customer::where('reference_number','LIKE',"%$prefix%")->where('category_id',$customer_detail->category_id)->orderby('id','DESC')->first();
                        $c_p_ref = Customer::where('category_id', $customer_detail->category_id)->orderby('reference_no', 'DESC')->first();

                        $str = @$c_p_ref->reference_no;
                        if ($str  == NULL) {
                            $str = "0";
                        }
                        // $matches = array();
                        // preg_match('/([a-zA-Z]+)(\d+)/', $str, $matches );
                        // $system_gen_no =  $prefix.str_pad(@$matches[2] + 1, STR_PAD_LEFT);
                        // $customer_detail->reference_number = $system_gen_no;

                        $system_gen_no =  str_pad(@$str + 1, STR_PAD_LEFT);
                        $customer_detail->reference_number      = $prefix . $system_gen_no;
                        $customer_detail->reference_no          = $system_gen_no;
                    }
                    $customer_detail->status = 1;
                    $customer_detail->email = $billing_detail->billing_email;
                    $customer_detail->save();
                    if ($billing_detail != null) {
                        $billing_detail->status = 1;
                        $billing_detail->save();
                    }
                    return response()->json(['success' => true]);
                } else {
                    $message = implode(', ', $missingPrams);
                    return response()->json(['success' => false, 'message' => $message]);
                }
            }
        }
    }
    public function saveCustDataCustDetailPage(Request $request)
    {
        // dd($request->cust_detail_id);
        // if($request->)

        $completed = 0;
        $customer = Customer::where('id', $request->cust_detail_id)->first();
        $payment_type = CustomerPaymentType::where('id', $customer->id)->first();
        $customer_history_record = new CustomerHistory();
        $customer_history_record->user_id = auth()->user()->id;
        $customer_history_record->customer_id = $request->cust_detail_id;
        // dd($request->all());

        if($request['reference_number']) {
            $check_customer = Customer::where('reference_number',$request['reference_number'])->first();
            if($check_customer != null) {
                return response()->json(['success' => false, 'message' => 'Customer with this reference number already exist !!']);
            }
        }


        foreach ($request->except('cust_detail_id', 'old_value') as $key => $value) {
            if ($key == 'reference_name') {
                $customer_history_record->column_name = 'reference_name';
                $customer_history_record->old_value = $customer->reference_name;
                $customer_history_record->new_value = $request->reference_name;
                $customer_history_record->save();
            } else if ($key == 'company') {
                $customer_history_record->column_name = 'company';
                $customer_history_record->old_value = $customer->company;
                $customer_history_record->new_value = $request->company;
                $customer_history_record->save();
            } else if ($key == 'category_id') {
                $customer_history_record->column_name = 'category';
                $customer_history_record->old_value = $customer->category_id;
                $customer_history_record->new_value = $request->category_id;
                $customer_history_record->save();
            } else if ($key == 'phone') {
                $customer_history_record->column_name = 'phone';
                $customer_history_record->old_value = $customer->phone;
                $customer_history_record->new_value = $request->phone;
                $customer_history_record->save();
            } else if ($key == 'paymentType') {
                $customer_history_record->column_name = 'paymentType';
                $customer_history_record->old_value = @$payment_type->payment_type_id;
                $customer_history_record->new_value = $request->paymentType;
                $customer_history_record->save();
            } else if ($key == 'primary_sale_id') {
                $customer_history_record->column_name = 'primary_sale_id';
                $customer_history_record->old_value = $customer->primary_sale_id;
                $customer_history_record->new_value = $request->primary_sale_id;
                $customer_history_record->save();
            } else if ($key == 'secondary_sale_id') {
                $customer_history_record->column_name = 'secondary_sale_id';
                $customer_history_record->old_value = $customer->secondary_sale_id;
                $customer_history_record->new_value = $request->secondary_sale_id;
                $customer_history_record->save();
            } else if ($key == 'language') {
                $customer_history_record->column_name = 'language';
                $customer_history_record->old_value = $customer->language;
                $customer_history_record->new_value = $request->language;
                $customer_history_record->save();
            } else if ($key == 'customer_credit_limit') {
                $customer_history_record->column_name = 'customer_credit_limit';
                $customer_history_record->old_value = $customer->customer_credit_limit;
                $customer_history_record->new_value = $request->customer_credit_limit;
                $customer_history_record->save();
            }  else if ($key == 'reference_number') {
                $customer_history_record->column_name = 'reference_number';
                $customer_history_record->old_value = $customer->reference_number;
                $customer_history_record->new_value = $request->reference_number;
                $customer_history_record->save();
            }   else if ($key == 'discount') {
                $customer_history_record->column_name = 'discount';
                $customer_history_record->old_value = $customer->discount;
                $customer_history_record->new_value = $request->discount;
                $customer_history_record->save();
            }
        }

        $customerPaymentType = CustomerPaymentType::where('customer_id', $request->cust_detail_id)->where('payment_type_id', $request->paymentType)->get();
        $show_title = $request->new_select_value;
        foreach ($request->except('cust_detail_id', 'new_select_value') as $key => $value) {
            if ($key == 'country') {
                $supp_detail->$key = $value;
                $supp_detail->state = NULL;
            }
            if ($value == '') {
                $customer->$key = null;
            } else if ($key == 'paymentType') {
                if ($customerPaymentType->count() > 0) {
                    // $supp_detail->phone = null;
                    $customerPaymentDelete = CustomerPaymentType::where('customer_id', $request->cust_detail_id)->where('payment_type_id', $request->paymentType)->delete();
                    $payment = 'delete';
                } else {
                    $customerPaymentAdd = new CustomerPaymentType;
                    $customerPaymentAdd->customer_id = $request->cust_detail_id;
                    $customerPaymentAdd->payment_type_id = $request->paymentType;
                    $customerPaymentAdd->save();
                    $payment = 'added';
                }
            } else if ($key == 'show_title') {
                // dd($value,$show_title);
                $cust_add = CustomerBillingDetail::find($value);
                $cust_add->show_title = $show_title;
                $cust_add->save();
            } else if ($key == 'company') {
                $customerr = Customer::where('company', $value)->where('id', '!=', $customer->id)->first();
                if ($customerr != null) {
                    if ($customerr->count() > 0) {
                        return response()->json(['company' => true]);
                    }
                } else {
                    // dd($value);
                    $customer->$key = $value;
                }
            } else if ($key == 'reference_name') {
                $cus = Customer::where('reference_name', $value)->where('status', '!=', 0)->first();
                if ($cus !== null) {
                    if ($cus->status == 1) {
                        $status = 'Completed';
                    }
                    // else if($cus->status == 0)
                    // {
                    //   $status = 'Incompleted';
                    // }
                    else if ($cus->status == 2) {
                        $status = 'Suspended';
                    }
                    return response()->json(['reference_name' => true, 'status' => $status]);
                }
                $customer->$key = $value;
            } else if ($key == 'phone') {
                $customer_phone = Customer::where('phone', $value)->where('id', '!=', $customer->id)->first();
                if ($customer_phone == null) {
                    $customer->$key = $value;
                    $customer->save();
                    if ($customer->ecommerce_customer_id != null) {
                        $array_val = array();
                        $phone = str_replace(' ', '$$$', $customer->phone);

                        $link = "/api/update-customer-information" . "/" . $customer->ecommerce_customer_id . "/" . $phone;
                        $url  = config('app.ecom_url') . $link;
                        // dd($link);
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 30,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "GET",
                            CURLOPT_POSTFIELDS => json_encode($array_val),
                            CURLOPT_HTTPHEADER => array(
                                "cache-control: no-cache",
                                "content-type: application/pdf",
                                "postman-token: 3c9a9b06-8be6-66e9-ea75-4a2b3acbd840"
                            ),
                        ));

                        $response = curl_exec($curl);
                    }
                } else {
                    return response()->json(['phone_already_exist' => true, 'old_phone' => $customer->phone]);
                }
            } else if ($key == 'secondary_sale_id') {
                $check_entry = CustomerSecondaryUser::where('customer_id', $customer->id)->where('user_id', $value)->first();
                if ($check_entry == null) {
                    $CustomerSecondaryUser = new CustomerSecondaryUser();
                    $CustomerSecondaryUser->customer_id = $customer->id;
                    $CustomerSecondaryUser->user_id = $value;
                    $CustomerSecondaryUser->status = 1;
                    $CustomerSecondaryUser->save();
                    $customer->$key = $value;
                }
            } else {
                $customer->$key = $value;
            }
        }

        $customer->save();

        if ($customer->status == 0) {
            $request->id = $request->cust_detail_id;
            $mark_as_complete = $this->doCustomerCompleted($request);
            // if($mark_as_complete == 100){
            //   return response()->json(['success' => false,'message' => 'Already Taken']);
            // }
            $json_response = json_decode($mark_as_complete->getContent());
            if ($json_response->success == true) {
                $customer_complete = Customer::find($request->id);
                $customer_complete->status = 1;
                $customer_complete->save();
                $completed = 1;
            }
            if ($json_response->success == false && $json_response->success == 'already_taken') {
                return response()->json(['success' => false, 'message' => 'Already Taken']);
            }
        }

        return response()->json(['success' => true, 'completed' => $completed]);
    }

    public function saveCustCheckCustDetailPage(Request $request)
    {
        $error = false;
        $cust_detail_id  = $request['cust_detail_id'];
        $field_name      = $request['field_name'];
        $field_value     = $request['field_value'];
        $old_value       = $request['oldValue'];
        $customer = Customer::where('id', $cust_detail_id)->first();

        if ($customer) {

            //category_id
            if ($field_name == "category_id") {
                if ($field_value == '') {
                    // $customer->category_id = NULL;
                } else {
                    $s_r_no = $customer->category_id;
                    if ($field_value == $s_r_no) {
                        return response()->json([
                            "error" => true,
                        ]);
                    } else {
                        $customer->category_id = $field_value;
                    }
                }
                // $customer->created_by = $this->user->id;
                $customer->save();
                $new_value = $field_value;
                return response()->json([
                    "error" => false,
                    "customer" => $customer,
                    "new_value" => $new_value
                ]);
            }


            //Payment terms
            if ($field_name == "credit-term") {
                if ($field_value == '') {
                    // $supp_detail->phone = null;
                } else {
                    $s_r_no = $customer->credit_term;
                    if ($field_value == $s_r_no) {
                        return response()->json([
                            "error" => true,
                        ]);
                    } else {
                        $customer->credit_term = $field_value;
                        // $supp_detail->state = null;
                    }
                }
                $customer->save();
                // $new_value = $field_value;
                $new_value = $customer->getpayment_term->title;
                return response()->json([
                    "error" => false,
                    "customer" => $customer,
                    "new_value" => $new_value
                ]);
            }
            //Cash
            if ($field_name == "payment-type") {
                $customerPaymentType = CustomerPaymentType::where('customer_id', $cust_detail_id)->where('payment_type_id', $field_value)->get();

                if ($customerPaymentType->count() > 0) {
                    // $supp_detail->phone = null;
                    $customerPaymentDelete = CustomerPaymentType::where('customer_id', $cust_detail_id)->where('payment_type_id', $field_value)->delete();
                    $payment = 'delete';
                } else {
                    $customerPaymentAdd = new CustomerPaymentType;
                    $customerPaymentAdd->customer_id = $cust_detail_id;
                    $customerPaymentAdd->payment_type_id = $field_value;
                    $customerPaymentAdd->save();
                    $payment = 'added';
                }
                // $customer->save();
                // $new_value = $field_value;
                // $new_value = $customer->getpayment_term->title;
                return response()->json([
                    "error" => false,
                    "payment" => $payment,
                    //  "customer" => $customer,
                    //  "new_value" => $new_value
                ]);
            }
        }
    }

    public function showSingleBilling(Request $request)
    {
        $customer_id = $request->cust_detail_id;
        $billing_id = $request->billing_id;

        $customerBillingDetails = CustomerBillingDetail::where('customer_id', $customer_id)->where('id', $billing_id)->first();
        $userState = @$customerBillingDetails->getstate->name;
        $userCountry = @$customerBillingDetails->getcountry->name;

        $states = State::where('country_id', $customerBillingDetails->billing_country)->get();
        $sta = null;
        if (@$states != null) {
            $sta = '<option disabled="true">Select State</option>';
            foreach (@$states as $state) {
                $sta .= '<option value=' . $state->id . '>' . $state->name . '</option>';
            }
        }
        return response()->json([
            "error" => false,
            "billingCustomer" => $customerBillingDetails,
            "userState" => $userState,
            "userCountry" => $userCountry,
            "states" => $sta,
            //  "new_value" => $new_value
        ]);
    }

    public function showSingleShipping(Request $request)
    {
        $customer_id = $request->cust_detail_id;
        $shipping_id = $request->shipping_id;

        $customerShippingDetails = CustomerShippingDetail::where('customer_id', $customer_id)->where('id', $shipping_id)->first();
        return response()->json([
            "error" => false,
            "shippingCustomer" => $customerShippingDetails,
            //  "customer" => $customer,
            //  "new_value" => $new_value
        ]);
    }

    public function addCustProdFixedPrice(Request $request)
    {
        // dd($request->all());
        $validator = $request->validate([
            'product' => 'required',
            'fixed_price' => 'required',
            // 'expiration_date' => 'required',
        ]);

        $ProductCustomerFixedPrice = new ProductCustomerFixedPrice;

        $ProductCustomerFixedPrice->product_id      = $request['product'];
        $ProductCustomerFixedPrice->customer_id     = $request['customer_id'];
        $ProductCustomerFixedPrice->fixed_price     = $request['fixed_price'];
        $ProductCustomerFixedPrice->discount     = $request['discount'] != null ? $request['discount'] : 0;
        $ProductCustomerFixedPrice->price_after_discount     = $request['price_after_discount'] != null ? $request['price_after_discount'] : $request['fixed_price'];
        $ProductCustomerFixedPrice->fixed_price_update_date = Carbon::now();
        $ProductCustomerFixedPrice->save();

        return response()->json(['success' => true]);
    }

    public function addCustomerContact(Request $request)
    {
        // $validator = $request->validate([
        //       'name' => 'required',
        //   ]);
        // dd($request->all());
        $customer_cont = CustomerContact::where('customer_id', $request['id'])->count();
        if ($customer_cont >= 5) {
            return response()->json(['contacts' => true]);
        } else {
            $customer_contact  = new CustomerContact;
            $customer_contact->name = $request['name'];
            $customer_contact->customer_id = $request['id'];
            $customer_contact->sur_name = $request['sur_name'];
            $customer_contact->email = $request['email'];
            $customer_contact->telehone_number = $request['telehone_number'];
            $customer_contact->postion = $request['postion'];
            $customer_contact->is_default = $customer_cont == 0 ? 1 : 0;
            $customer_contact->save();

            return response()->json(['success' => true]);
        }
    }

    public function getProductSellingPrice(Request $request)
    {
        $getCustomer = Customer::find($request->customer_id);
        if ($getCustomer->category_id != null) {
            $ctpmargin = CustomerTypeProductMargin::where('product_id', $request->product_id)->where('customer_type_id', $getCustomer->category_id)->first();
        } else {
            $ctpmargin = CustomerTypeProductMargin::where('product_id', $request->product_id)->first();
        }
        // dd($ctpmargin->default_value);
        $product = Product::find($request->product_id);
        $salePrice = $product->selling_price + ($product->selling_price * ($ctpmargin->default_value / 100));
        $formated_value = number_format($salePrice, 2, '.', ',');
        // dd($formated_value);
        return response()->json(['success' => true, 'price' => $formated_value, 'product' => $product]);
    }

    // public function saveShippingInfo(Request $request)
    // {
    //   // dd($request->all());
    //   $shippingInfo                        = new CustomerShippingDetail;
    //   $shippingInfo->customer_id           = $request->customer_id;
    //   $shippingInfo->title                 = $request->shipping_title;
    //   $shippingInfo->shipping_contact_name = $request->shipping_contact_name;
    //   $shippingInfo->shipping_email        = $request->shipping_email;
    //   $shippingInfo->company_name          = $request->company_name;
    //   $shippingInfo->shipping_phone        = $request->shipping_phone;
    //   $shippingInfo->shipping_fax          = $request->shipping_fax;
    //   $shippingInfo->shipping_address      = $request->shipping_address;
    //   $shippingInfo->shipping_country      = $request->shipping_country;
    //   $shippingInfo->shipping_state        = $request->state;
    //   $shippingInfo->shipping_city         = $request->shipping_information_city;
    //   $shippingInfo->shipping_zip          = $request->shipping_zip;
    //   $shippingInfo->status                = 1;
    //   $shippingInfo->created_by            = $this->user->id;

    //   $shippingInfo->save();
    //   return redirect()->back();
    // }

    public function checkDefaultAddress(Request $request)
    {
        $billing_Address = $request->billing_address;
        $customer_id = $request->customer_id;
        $confirm_is_default = CustomerBillingDetail::where('is_default', 1)->where('customer_id', $customer_id)->get();
        if ($confirm_is_default->count() == 0) {
            $confirm_is_default = CustomerBillingDetail::where('id', $billing_Address)->first();
            $confirm_is_default->is_default = 1;
            $confirm_is_default->save();
            return response()->json([
                "set_default" => true,
                'error' => true,
            ]);
        }
        if ($confirm_is_default->count() > 0) {
            return response()->json([
                "error" => false,
            ]);
        }
    }

    public function settingDefaultShipping(Request $request)
    {
        $address_id = $request->address_id;
        $is_default_shipping = $request->is_default_shipping;
        $customer_id = $request->customer_id;
        // dd($request->all());

        $confirm_is_default = CustomerBillingDetail::where('is_default_shipping', 1)->where('customer_id', $customer_id)->first();
        if ($confirm_is_default) {
            $confirm_is_default->is_default_shipping = 0;
            $confirm_is_default->save();
            if ($request->is_default_shipping != "true") {
                return response()->json([
                    "error" => false,
                    "success" => true
                ]);
            }
        }

        $billing_Address = CustomerBillingDetail::where('id', $address_id)->first();
        if ($billing_Address && $request->is_default_shipping == "true") {
            $billing_Address->is_default_shipping = 1;
            $billing_Address->save();
            return response()->json([
                "error" => false,
                "success" => true
            ]);
        }
        return response()->json([
            "success" => false
        ]);
    }

    public function replaceDefaultAddress(Request $request)
    {
        $customer_id = $request->customer_id;
        $address = $request->billing_address;
        $confirm_is_default = CustomerBillingDetail::where('is_default', 1)->where('customer_id', $customer_id)->first();

        $confirm_is_default->is_default = 0;
        $confirm_is_default->save();

        $billing_Address = CustomerBillingDetail::where('id', $address)->first();
        if ($billing_Address) {
            $billing_Address->is_default = 1;
            $billing_Address->save();
            return response()->json([
                "error" => true,
                "update" => true
            ]);
        }
        return response()->json([
            "error" => false,
        ]);
    }

    public function saveBillingInfo(Request $request)
    {
        // dd($request->all());
        $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('title', $request->billing_title)->first();
        if ($customerAddress != null) {
            return response()->json(['success' => false, 'field' => 'billing_title']);
        }
        if ($request->choice) {
            $choic = $request->choice;
        } else {
            $choic = null;
        }

        $check_value = $request->is_default_value;
        if ($check_value == 1) {
            $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('is_default', 1)->first();
            $customerAddress->is_default = 0;
            $customerAddress->save();
        }
        $customer = Customer::where('id', $request->customer_id)->first();
        $billingInfo                        = new CustomerBillingDetail;
        $billingInfo->customer_id           = $request->customer_id;
        $billingInfo->title                 = $request->billing_title;
        $billingInfo->billing_contact_name = $request->billing_contact_name1;
        $billingInfo->billing_email        = $request->billing_email1;
        $billingInfo->tax_id          = $request->tax_id;
        $billingInfo->billing_phone        = $request->billing_phone;
        $billingInfo->cell_number        = @$request->cell_number;
        $billingInfo->billing_fax          = $request->billing_fax1;
        $billingInfo->billing_address      = $request->billing_address;
        if (@$request->billing_country != null) {
            $billingInfo->billing_country      = $request->billing_country;
        } else {
            $billingInfo->billing_country      = '217';
        }
        $billingInfo->billing_state        = $request->state;
        $billingInfo->billing_city         = $request->billing_city;
        $billingInfo->billing_zip          = $request->billing_zip;
        $billingInfo->is_default          = $request->is_default_value;
        $billingInfo->status                = 1;
        $billingInfo->created_by            = $this->user->id;

        $billingInfo->save();
        if ($request->quotation_id || $request->order_id) {
            if ($request->quotation_id != null) {
                $draft = DraftQuotation::where('id', $request->quotation_id)->first();
                $draft->customer_id = $request->customer_id;
                $draft->billing_address_id = $billingInfo->id;
                $draft->save();
            } else if ($request->order_id != null) {
                $order = Order::where('id', $request->order_id)->first();
                $order->billing_address_id = $billingInfo->id;
                $order->save();
            }

            $customerAddress = CustomerBillingDetail::find($billingInfo->id);
            $html = ' <p class="edit-functionality"><i class="fa fa-edit edit-address" data-id="' . @$customer->id . '"></i>
        ' . @$customerAddress->billing_address . ', ' . @$customerAddress->getcountry->name . ',' . @$customerAddress->getstate->name . ',' . @$customerAddress->billing_city . ',' . @$customerAddress->billing_zip . '</p>
         <ul class="d-flex list-unstyled">
            <li><a href="#"><i class="fa fa-phone pr-2"></i> ' . @$customerAddress->billing_phone . '</a></li>
            <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> ' . @$customerAddress->billing_email . '</a></li>
          </ul>

          <ul class="d-flex list-unstyled">
            <li><a href="#"><b>Tax ID: </b>' . @$customerAddress->tax_id . '</a></li>
          </ul>
        </div>';
            if ($customerAddress) {
                return response()->json(['html' => $html, 'choicee' => $choic]);
            }
        } else {
            return response()->json(['sucess' => true]);
        }
    }

    public function checkDuplicateAddress(Request $request)
    {
        $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('title', $request->title)->first();
        if ($customerAddress) {
            return response()->json(['success' => false, 'field' => 'billing_title']);
        } else {
            return response()->json(['success' => true, 'field' => 'billing_title']);
        }
    }

    public function saveShippingInfo(Request $request)
    {
        $customer = Customer::where('id', $request->customer_id)->first();


        $billingInfo                        = new CustomerBillingDetail;
        $billingInfo->customer_id           = $request->customer_id;
        $billingInfo->title                 = $request->billing_title;
        $billingInfo->billing_contact_name = $request->billing_contact_name1;
        $billingInfo->billing_email        = $request->billing_email1;
        $billingInfo->company_name          = $request->company_name1;
        $billingInfo->billing_phone        = $request->billing_phone;
        $billingInfo->billing_fax          = $request->billing_fax1;
        $billingInfo->billing_address      = $request->billing_address;
        $billingInfo->billing_country      = '217';
        $billingInfo->billing_state        = $request->state;
        $billingInfo->billing_city         = $request->billing_city;
        $billingInfo->billing_zip          = $request->billing_zip;
        $billingInfo->is_default          = $request->is_default_value;
        $billingInfo->status                = 1;
        $billingInfo->created_by            = $this->user->id;

        $billingInfo->save();

        $draft = DraftQuotation::where('id', $request->quotation_id)->first();
        $draft->customer_id = $request->customer_id;
        $draft->shipping_address_id = $billingInfo->id;
        $draft->save();
        $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('id', $billingInfo->id)->first();

        $html = '
         <p class="edit-functionality-ship"><i class="fa fa-edit edit-address-ship" data-id="' . @$customer->id . '"></i>
         ' . @$customerAddress->billing_address . ', ' . @$customerAddress->getcountry->name . ',' . @$customerAddress->getstate->name . ',' . @$customerAddress->billing_city . ',' . @$customerAddress->billing_zip . '</p>
         <ul class="d-flex list-unstyled">
            <li><a href="#"><i class="fa fa-phone pr-2"></i> ' . @$customerAddress->billing_phone . '</a></li>
            <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> ' . @$customerAddress->billing_email . '</a></li>
          </ul>
        </div>';
        if ($customerAddress) {
            return response()->json(['html' => $html]);
        } else {
            return redirect()->back();
        }
    }

    public function addCustomerNote(Request $request)
    {
        $request->validate([
            'note_description' => 'required|max:255',
        ]);

        $customer = Customer::find($request->customer_id);

        $customer->getnotes()->create([
            'note_title' => 'note',
            'note_description' => $request->note_description,
            'user_id' => Auth::user()->id,
        ]);

        return json_encode(['success' => true]);
    }

    public function getCustomerNote(Request $request)
    {
        $customer_notes = CustomerNote::where('customer_id', $request->customer_id)->get();


        $html_string = '<div class="table-responsive">
                                <table class="table table-bordered text-center">
                                <thead class="table-bordered">
                                <tr>
                                    <th>S.no</th>

                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                                </thead><tbody>';
        if ($customer_notes->count() > 0) {
            $i = 0;
            foreach ($customer_notes as $note) {
                $i++;
                $html_string .= '<tr id="cust-note-' . $note->id . '">
                                    <td>' . $i . '</td>

                                    <td>' . $note->note_description . '</td>
                                    <td><a href="javascript:void(0);" data-id="' . $note->id . '" class="delete-note actionicon deleteIcon" title="Delete Note"><i class="fa fa-trash"></i></a></td>
                                 </tr>';
            }
        } else {
            $html_string .= '<tr>
                                    <td colspan="4">No Note Found</td>
                                 </tr>';
        }


        $html_string .= '</tbody></table></div>';
        return $html_string;
    }

    public function deleteCustomer(Request $request)
    {

        $customer = Customer::find($request->id)->update(['deleted_at' => NOW(), 'status' => 3]);

        return $customer ? response()->json(['success' => true]) : response()->json(['success' => false]);
        // $customerOrders = Order::where('customer_id',$customer->id)->get();
        // //dd($customerOrders->count());
        // if($customerOrders->count() > 0)
        // {
        //   return response()->json(['success' => false]);
        // }
        // else
        // {
        //   $customer->getbilling()->delete();
        //   $customer->getnotes()->delete();
        //   $customer->delete();
        //   return response()->json(['success' => true]);
        // }
    }

    public function deleteCustomerNoteInfo(Request $request)
    {
        $customer_notes = CustomerNote::where('id', $request->id)->first();
        $customer_notes->delete();
        return response()->json(['success' => true]);
    }

    public function getCustomerAddresses(Request $request)
    {
        $current_address = $request->current_Address;
        $addresses = CustomerBillingDetail::where('customer_id', $request->customer_id)->get();
        if ($request->choice) {
            if ($request->choice == 1) {
                $html_string = '<label>Billing Address:</label><input type="hidden" class="bill" value="' . $request->choice . '">';
            } else if ($request->choice == 2) {
                $html_string = '<label>Shipping Address:</label><input type="hidden" class="ship" value="' . $request->choice . '">';
            }
        } else {
            $html_string = '<label>Billing Address:</label>';
        }
        $html_string .= '<select name="customer-address" class="form-control confirm-address mb-2" data-id="' . $request->customer_id . '"><option>Select Address</option>';
        foreach ($addresses as $address) {
            if ($address->id == $current_address) {
                $html_string .= ' <option value="' . $address->id . '" selected> ' . $address->title . '</option>';
            } else {
                $html_string .= ' <option value="' . $address->id . '"> ' . $address->title . '</option>';
            }
        }
        //      if($request->choice){
        //       if($request->choice == 1){
        //     $html_string .= '<option value="add-new">Add New</option>';
        //   }else if($request->choice == 2){
        //     $html_string .= '<option value="add-new-ship">Add New</option>';
        //   }
        // }else{
        //     $html_string .= '<option value="add-new">Add New</option>';
        //   }
        $html_string .= '</select>';

        // dd($html_string);
        return response()->json(['html' => $html_string]);
    }

    public function getCustomerAddressesShip(Request $request)
    {
        $current_address = $request->current_Address;
        $addresses = CustomerBillingDetail::where('customer_id', $request->customer_id)->get();
        $html_string = '<label>Shipping Address:</label><select name="customer-address-ship" class="form-control confirm-address-ship mb-2" data-id="' . $request->customer_id . '">';
        foreach ($addresses as $address) {
            if ($address->id == $current_address) {
                $html_string .= ' <option value="' . $address->id . '" selected> ' . $address->title . '</option>';
            } else {
                $html_string .= ' <option value="' . $address->id . '"> ' . $address->title . '</option>';
            }
        }
        // $html_string .= '<option value="add-new">Add New</option>';
        $html_string .= '</select>';
        return response()->json(['html' => $html_string]);
    }

    public function suspendCustomer(Request $request)
    {
        $suspend = Customer::where('id', $request->id)->update(['status' => 2]);

        $suspendcustomer = Customer::find($request->id);
        $first_name = $suspendcustomer->first_name;
        $name = $first_name . " " . $suspendcustomer->last_name;

        // get suspension email //
        // $template = EmailTemplate::where('type', 'account-suspension')->first();

        // send email //
        // Mail::to($suspendcustomer->email, $name)->send(new CustomerSuspensionEmail($suspendcustomer, $template));

        return response()->json(['error' => false, 'successmsg' => 'Customer has been blocked']);
    }

    public function activateCustomer(Request $request)
    {
        $activate = Customer::where('id', $request->id)->update(['status' => 1]);

        $activatecustomer = Customer::find($request->id);
        $first_name = $activatecustomer->first_name;
        $name = $first_name . " " . $activatecustomer->last_name;


        // get activation email //
        // $template = EmailTemplate::where('type', 'account-activation')->first();

        // send email //
        // Mail::to($activatecustomer->email, $name)->send(new CustomerActivationEmail($activatecustomer, $template));

        return response()->json(['error' => false, 'successmsg' => 'The customer account has been activated']);
    }

    public function uploadCustomerGeneralDocuments(Request $request)
    {

        $this->validate($request, [
            'po_docs' => 'required|max:45000',
        ]);

        $count = CustomerGeneralDocument::where('customer_id', $request->customer_id)->count();
        if (isset($request->po_docs)) {
            for ($i = 0; $i < sizeof($request->po_docs); $i++) {

                $doc        = new CustomerGeneralDocument;
                $doc->customer_id = $request->customer_id;
                //file
                $extension = $request->po_docs[$i]->extension();
                $filename = date('m-d-Y') . mt_rand(999, 999999) . '__' . time() . '.' . $extension;
                $request->po_docs[$i]->move(public_path('uploads/documents'), $filename);
                $doc->file_name = $filename;
                $doc->description = $request->description;
                $doc->save();
            }
        }
        $con = 0;
        if ($count < 6) {
            if ($count + sizeof($request->po_docs) > 5)
                $con = true;
        }
        return response()->json(['success' => true, 'con' => $con]);
    }

    public function saveIncomCustomer()
    {
        return redirect('sales/customer')->with('msg', 'incomplete');
    }

    public function exportAccountReceivablePDF(Request $request, $orders, $billing_type, $receipt_date = null)
    {
        $receipt_date = preg_replace('/\_/', '/', $receipt_date);
        //Auto Generation of number
        $counter_length  = 4;

        $date = Carbon::now();
        $date = $date->format('ym'); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday

        if ($billing_type == 'new_billing_note') {
            $c_p_ref = BillingNoCounter::where('ref_no', 'LIKE', "$date%")->where('type', 'billing_note')->orderby('id', 'DESC')->first();
            $str = @$c_p_ref->ref_no;
            $onlyIncrementGet = substr($str, 4);
            if ($str == NULL) {
                $onlyIncrementGet = 0;
            }
            $system_gen_no = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
            $system_gen_no = $date . $system_gen_no;

            $billing_ref_no = new BillingNoCounter;
            $billing_ref_no->prefix = 'BI';
            $billing_ref_no->ref_no = $system_gen_no;
            $billing_ref_no->type = 'billing_note';
            $billing_ref_no->save();
        }
        else{
            $ref_no = explode('-', $billing_type);

            $billing_ref_no = BillingNoCounter::where('prefix', $ref_no[0])->where('ref_no', $ref_no[1])->first();
        }
        // END


        $orders = explode(',', $request->orders);

        $all_orders = Order::with('order_products')->whereIn('id', $orders)->orderBy('delivery_request_date', 'asc')->orderBy('credit_note_date', 'asc')->get();

        // Billing Note History
        if ($billing_type == 'new_billing_note'){
            $reference_number = $billing_ref_no->prefix . '-' . $billing_ref_no->ref_no;

            $order_ids = [];
            foreach ($all_orders as $item){
                $data = ['id' => $item->id, 'inv_no' => $item->full_inv_no];
                array_push($order_ids, $data);
            }

            $billing_notes_history = new BillingNotesHistory();
            $billing_notes_history->ref_no = $reference_number;
            $billing_notes_history->customer_id = $all_orders->first()->customer_id;
            $billing_notes_history->orders = serialize($order_ids);
            $billing_notes_history->save();
        }
        // END

        $pm_check = Order::with('order_products')->whereIn('id', $orders)->whereNotNull('payment_terms_id')->orderBy('delivery_request_date', 'asc')->orderBy('credit_note_date', 'asc')->get();
        $samePaymentTerm = [];
        foreach ($pm_check as $value) {
            array_push($samePaymentTerm, $value->payment_terms_id);
        }

        $paymentTerm = null;

        if (!empty($samePaymentTerm)) {
            if (count(array_unique($samePaymentTerm)) === 1) {
                $paymentTerm = $samePaymentTerm[0];
            } else {
                $paymentTerm = NULL;
            }
        }

        if ($paymentTerm != null) {
            $getPaymentTerm = PaymentTerm::select('title')->find($paymentTerm);
        } else {
            $getPaymentTerm = NULL;
        }

        $all_orders_count = $all_orders->count();
        $pages = ceil($all_orders_count / 10);

        $customerAddress = CustomerBillingDetail::where('customer_id', $all_orders[0]->customer->id)->where('id', $all_orders[0]->billing_address_id)->first();
        $globalAccessConfig = QuotationConfig::where('section', 'quotation')->first();
        if ($globalAccessConfig) {
            $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "billing_note") {
                    $bladePrint = $val['title'];
                }
            }
        } else {
            $bladePrint = 'account-receivable-invoice';
        }

        $config = Configuration::first();
        if ($config && $config->server == 'lucilla') {
            $bladePrint = 'lucilla-account-receivable-invoice';
        }
        $pdf = PDF::loadView('accounting.invoices.' . $bladePrint . '', compact('all_orders', 'customerAddress', 'pages', 'billing_ref_no', 'receipt_date', 'getPaymentTerm'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        // making pdf name starts
        $makePdfName = 'invoice';
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
        return $pdf->download($makePdfName . '.pdf');
    }

    public function exportPaymentReceiptPDF(Request $request, $payment_id)
    {
        $prints_checking = "REAL";
        $payment = OrdersPaymentRef::find($payment_id);
        $transactions = $payment->getTransactions()->get();
        $customerAddress = CustomerBillingDetail::where('customer_id', $transactions[0]->order->customer->id)->where('id', $transactions[0]->order->billing_address_id)->first();

        $globalAccessConfig = QuotationConfig::where('section', 'quotation')->first();
        if ($globalAccessConfig) {
            $globalaccessForConfig = unserialize($globalAccessConfig->print_prefrences);
            foreach ($globalaccessForConfig as $val) {
                if ($val['slug'] === "paid_receipt") {
                    $bladePrint = $val['title'];
                }
            }
        } else {
            $bladePrint = 'payment-receipt-invoice';
        }
        $total_pages = 1;
        if ($bladePrint != 'payment-receipt-invoice') {
            $customer = Customer::find($transactions[0]->customer_id);
            $order_ids = [];
            foreach ($transactions as $trans){
                array_push($order_ids, $trans->order_id);
            }
            // $orders = Order::with('order_products')->where('id', $transactions[0]->order_id)->get();
            $orders = Order::with('order_products')->whereIn('id', $order_ids)->get();
            $all_orders_count = $orders[0]->order_products->count();
            $total_pages == ceil($all_orders_count / 8);
            if ($all_orders_count <= 8) {
                $do_pages_count = ceil($all_orders_count / 9);
                $final_pages = $all_orders_count % 9;
                if ($final_pages == 0) {
                    // $do_pages_count++;
                }
            } else {
                $do_pages_count = ceil($all_orders_count / 9);
                $final_pages = $all_orders_count % 9;
                if ($final_pages == 0) {
                    $do_pages_count++;
                }
            }

            $customerAddress = CustomerBillingDetail::where('customer_id', $customer->id)->where('id', $orders[0]->billing_address_id)->first();
        } else {
            $customer         = '';
            $orders           = '';
            $all_orders_count = '';
            $do_pages_count   = '';
        }
        // dd($bladePrint);
        $system_config = Configuration::first();
        if ($system_config && $system_config->server == 'lucilla') {
            $bladePrint = 'lucila-payment-receipt-invoice';
        }

        $pdf = PDF::loadView('accounting.invoices.' . $bladePrint . '', compact('payment', 'transactions', 'customerAddress', 'customer', 'orders', 'customerAddress', 'do_pages_count', 'all_orders_count', 'prints_checking', 'total_pages'));
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        // making pdf name starts
        $makePdfName = 'invoice';
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
        return $pdf->download($makePdfName . '.pdf');
    }

    public function exportPaymentReceiptPDFF(Request $request)
    {
        $payment = OrdersPaymentRef::find($request->payment_id);
        $transactions = $payment->getTransactions()->get();
        $customerAddress = CustomerBillingDetail::where('customer_id', $transactions[0]->order->customer->id)->where('id', $transactions[0]->order->billing_address_id)->first();
        // dd($transactions);
        $pdf = PDF::loadView('accounting.invoices.payment-receipt-invoice', compact('payment', 'transactions', 'customerAddress'));
        $pdf->setPaper('A4', 'portrait');
        // making pdf name starts
        $makePdfName = 'invoice';
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
        return $pdf->download($makePdfName . '.pdf');
    }

    // public function getStateFromCountry(Request $request){
    //   dd($request->all());
    // }

    public function deleteCustomersPermanent(Request $request)
    {
        // dd($request->all());
        $errorsArray = array();
        $errorMsg = '';
        $hasError = 0;
        if (isset($request->customers)) {
            $multi_customers = explode(',', $request->customers);
            for ($i = 0; $i < sizeof($multi_customers); $i++) {
                $customer = Customer::find($multi_customers[$i]);
                if ($customer->status == 1 || $customer->status == 2) // checking if this supplier have a po
                {
                    $customerOrders = Order::where('customer_id', $customer->id)->get();
                    if ($customerOrders->count() > 0) {
                        $hasError = 1;
                        $errorMsg .= '<ol>';
                        // foreach ($customerOrders as $co)
                        // {
                        $errorMsg .= "Customer '<b>" . $customer->reference_name . "</b>' exist in the Orders." . "<br>";
                        // }
                        $errorMsg .= '</ol>';
                        // array_push($errorsArray, $errorMsg);
                    } else {
                        $customer->getbilling()->delete();
                        $customer->getnotes()->delete();
                        $customer->CustomerSecondaryUser()->delete();
                        $customer->delete();
                    }
                } else {
                    $customer->getbilling()->delete();
                    $customer->getnotes()->delete();
                    $customer->delete();
                }
            }
        }
        if ($hasError == 0) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        }
    }

    public function getPaymentRefInvoicesForReceivablesLastFive(Request $request)
    {
        // dd($request->all());
        $customer_id = $request->selecting_customer;

        $query = OrdersPaymentRef::orderBy('id', 'desc')->limit(5);
        if ($customer_id !== null) {
            $query = $query->where('customer_id', $customer_id);
        }
        if ($request->order_no !== null) {
            $query = $query->where('payment_reference_no', 'LIKE', '%' . $request->order_no . '%');
        }
        $query = $query->get();

        return Datatables::of($query)
            ->addColumn('ref_no', function ($item) {
                $i = 1;
                // $html_string = '<a target="_blank" href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" >'.$item->order->ref_id.'</a>';

                $orders_ref_no = $item->getTransactions->pluck('order_ref_no')->unique()->toArray();
                // $orders_ref_no = implode (", ", $orders_ref_no);

                $html_string = '
                        <a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal' . $item->id . '">
                          <i class="fa fa-tty"></i>
                        </a>
                    ';

                $html_string .= '
                    <div class="modal fade" id="poNumberModal' . $item->id . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Order ref #</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                          <table class="bordered" style="width:100%;">
                                <thead style="border:1px solid #eee;text-align:center;">
                                    <tr><th>S.No</th><th>Order ref#</th></tr>
                                </thead>
                                <tbody>';
                foreach ($orders_ref_no as $p_g_d) {
                    $html_string .= '<tr><td>' . $i . '</td><td>' . @$p_g_d . '</td></tr>';
                    $i++;
                }
                $html_string .= '
                                </tbody>
                          </table>

                          </div>
                        </div>
                      </div>
                    </div>
                    ';
                // dd($orders_ref_no);
                // return $orders_ref_no;
                return $html_string;
            })



            ->addColumn('invoice_total', function ($item) {
                $ids = $item->getTransactions->pluck('order_id')->unique()->toArray();
                $total_amount = Order::whereIn('id', $ids)->sum('total_amount');
                return number_format($total_amount);
            })

            ->addColumn('reference_name', function ($item) {
                // $total_paid = $item->customer->reference_name;
                return $item->customer !== null ? $item->customer->reference_name : '--';
            })
            ->addColumn('reference_number', function ($item) {
                // $total_paid = $item->customer->reference_name;
                return $item->customer !== null ? $item->customer->reference_number : '--';
            })

            ->addColumn('total_paid', function ($item) {
                $total_paid = $item->getTransactions->sum('total_received');
                return number_format($total_paid);
            })

            ->addColumn('received_date', function ($item) {
                $order_transaction = $item->getTransactions->last();
                return Carbon::parse($order_transaction->received_date)->format('d/m/Y');
            })

            ->addColumn('payment_type', function ($item) {
                $ids = $item->getTransactions->pluck('payment_method_id')->unique()->toArray();
                $payment_types = PaymentType::whereIn('id', $ids)->pluck('title')->unique()->toArray();
                $payment_types = implode(", ", $payment_types);
                return $payment_types;
            })

            ->addColumn('payment_reference_no', function ($item) {
                $html_string = '<a href="javascript:void(0)" class="download_transaction" data-id="' . @$item->id . '">' . @$item->payment_reference_no . '</a>';
                return $html_string;
            })

            ->setRowId(function ($item) {
                return $item->id;
            })

            ->rawColumns(['ref_no', 'invoice_total', 'total_paid', 'received_date', 'payment_type', 'payment_reference_no'])
            ->make(true);
    }

    public function deleteOrderTransaction(Request $request)
    {
        $transaction = OrderTransaction::find($request->dot_id);
        $payment_ref_no = $transaction->payment_reference_no;
        // dd($transaction);

        $order = Order::find($transaction->order_id);
        // dd($order);
        $order->total_paid -= $transaction->total_received;
        $order->vat_total_paid -= $transaction->vat_total_paid;
        $order->non_vat_total_paid -= $transaction->non_vat_total_paid;
        $order->save();

        $transaction_history = new TransactionHistory;
        $transaction_history->user_id = Auth::user()->id;
        $transaction_history->order_id = $order->id;
        $transaction_history->payment_method_id = $transaction->payment_method_id;
        $transaction_history->received_date = $transaction->received_date;
        $transaction_history->payment_reference_no = $transaction->get_payment_ref->payment_reference_no;
        $transaction_history->total_received = $transaction->total_received;
        $transaction_history->reason = $request->delete_reason;
        $transaction_history->save();


        $transaction->delete();

        $payment = OrdersPaymentRef::where('id', $payment_ref_no)->first();
        // dd($payment->getTransactions->count());
        if ($payment->getTransactions->count() == 0) {
            $payment->delete();
        }

        if (preg_replace('/(\.\d\d).*/', '$1', @$order->total_amount) > $order->total_paid) {
            if ($order->primary_status == 3) {
                $order->status = 11;
                $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', 'Product')->update([
                    'status' => 11
                ]);
            } elseif ($order->primary_status == 25) {
                $order->status = 27;
                $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', 'Product')->update([
                    'status' => 27
                ]);
            }
        }
        $order->save();

        return response()->json(['success' => true]);
    }

    public function getSalesperson(Request $request)
    {
        // dd($request->all());
        if (@$request->choice == 'salesperson') {
            $users = User::select('id', 'name')->whereNull('parent_id')->where('status', 1)->where('role_id', 3)->get();
            $html_string = '<option value="" >Choose Primary Salesperson</option>';
            foreach ($users as $user) {
                $html_string .= '<option value="' . $user->id . '" ' . ($request->value == $user->id ? "selected" : "") . '>' . $user->name . '</option>';
            }
            return response()->json(['html' => $html_string, 'field' => 'salesperson']);
        }

        if (@$request->choice == 'secondary_salesperosn') {
            $users = User::select('id', 'name')->whereNull('parent_id')->where('status', 1)->where('role_id', 3)->get();
            $html_string = '<option value=""  >Choose Secondary Salesperson</option>';
            foreach ($users as $user) {
                $html_string .= '<option value="' . $user->id . '" >' . $user->name . '</option>';
            }
            return response()->json(['html' => $html_string, 'field' => 'secondary_salesperson']);
        }
    }

    public function saveCustomerData(Request $request)
    {
        $user = User::find($request->new_value);
        if ($request->field_name == 'primary_salesperson_id') {
            Customer::where('id', $request->id)->update(['primary_sale_id' => $request->new_value]);
            return response()->json(['success' => true, 'user' => $user]);
        } elseif ($request->field_name == 'secondary_salesperson_id') {
            Customer::where('id', $request->id)->update(['secondary_sale_id' => $request->new_value]);
            $isSalePersonExist = CustomerSecondaryUser::where('user_id', $request->new_value)->where('customer_id', $request->id)->count();
            if ($isSalePersonExist < 1) {
                $CustomerSecondaryUser = new CustomerSecondaryUser;
                $CustomerSecondaryUser->user_id = $request->new_value;
                $CustomerSecondaryUser->customer_id = $request->id;
                $CustomerSecondaryUser->status = 1;
                $CustomerSecondaryUser->save();
            }

            return response()->json(['success' => true, 'user' => $user]);
        } else
            return response()->json(['success' => false]);
    }

    public function getCustomerSecondarySalesPerson(Request $request)
    {
        $customerSecondarySalesPersons = CustomerSecondaryUser::with(['customers', 'secondarySalesPersons'])->where('customer_id', $request->cust_detail_id)->get();
        return response()->json(['success' => true, 'customerSecondarySalesPersons' => $customerSecondarySalesPersons]);
    }
    // Delete Sales Person record
    public function deleteSalesPersonRecord(Request $request)
    {

        $isDeletedSecondarySAlesPerson = CustomerSecondaryUser::find($request->salesPersonRecordId)->delete();
        if ($isDeletedSecondarySAlesPerson) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }



    public function customerBulkUploadPO(Request $request)
    {
        $customer = new Customer();
        return $customer->customerBulkUploadPO($request);
    }

    public function customerRecursiveCallForBulkPos(Request $request)
    {
        $customer = new Customer();
        return $customer->customerRecursiveCallForBulkPos($request);
    }

    public function customerCheckStatusFirstTimeForBulkPos(Request $request)
    {
        $customer = new Customer();
        return $customer->customerCheckStatusFirstTimeForBulkPos($request);
    }


    public function syncCustomers(Request $request)
    {
        $result = (new Customer)->syncCustomerEcom($request->id);
        if ($result['success'] == true) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function settingDefaultConatact(Request $request)
    {
        if ($request->is_default == 'true') {
            $contact = CustomerContact::where('is_default', 1)->first();
            if ($contact) {
                $contact->is_default = 0;
                $contact->save();
            }
            $contact = CustomerContact::find($request->contact_id);
            $contact->is_default = 1;
            $contact->save();
            return response()->json(['success' => true]);
        }
        else{
            $contact = CustomerContact::find($request->contact_id);
            $contact->is_default = 0;
            $contact->save();
            return response()->json(['success' => true]);
        }
    }

    public function getBllingNoesHistory(Request $request)
    {
        $query = BillingNotesHistory::with('customer')->orderBy('id', 'DESC');
        if ($request->selecting_customer != null) {
            $query = $query->where('customer_id', @$request->selecting_customer);
        }

        if ($request->inv_no != null) {
            $query = $query->where('orders', 'LIKE', '%'.@$request->inv_no.'%');
        }

        return Datatables::of($query)
            ->addColumn('reference_name', function ($item) {
                return @$item->customer_id != null ? @$item->customer->reference_name : '--';
            })

            ->addColumn('inv_no', function ($item) {
                $html = '';
                if (@$item->orders != null) {
                    $orders = unserialize(@$item->orders);
                    foreach ($orders as $order) {
                        $html .= '<a class="font-weight-bold" href="'.route("get-completed-invoices-details", @$order["id"]).'">'.@$order["inv_no"].'</a>, ';
                    }
                    $html = rtrim($html, ', ');
                }
                return $html != '' ? $html : '--';
            })
            ->addColumn('billing_ref_no', function ($item) {
                $order_ids = '';
                if (@$item->orders != null) {
                    $orders = unserialize(@$item->orders);
                    foreach ($orders as $order) {
                        $order_ids .= @$order['id'].',';
                    }
                    $order_ids = rtrim(@$order_ids,",");
                }
                $html = '<a href="javascript:void(0)" class="btn_view_billing_note font-weight-bold" data-order_ids="'.$order_ids.'" data-ref_no="'.@$item->ref_no.'">'.@$item->ref_no.'</a>';
                return $html;
            })
            ->addColumn('action', function ($item) {
                $html = '<a href="javascript:void(0)" class="btn_delete_billing_note actionicon deleteIcon" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>, ';
                return $html;
            })


            ->rawColumns(['reference_name', 'inv_no', 'billing_ref_no', 'action'])
            ->make(true);
    }

    public function deleteBllingNoesHistory(Request $request)
    {
        $billing_note = BillingNotesHistory::find($request->id);
        if ($billing_note) {
            $billing_note->delete();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public function getAutoRefNo(Request $request)
    {
        $ref_no_config = QuotationConfig::where('section', 'account_receiveable_auto_run_payment_ref_no')->first();
        $payment_ref_no = '';
        if ($ref_no_config && $ref_no_config->display_prefrences == 1)
        {
            // Getting Auto generated payment ref no
            $auto_ref_no = Status::where('id',41)->first();
            $counter_formula = $auto_ref_no->counter_formula;
            $counter_formula = explode('-',$counter_formula);
            $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;
            $date = Carbon::now();
            $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday

            $c_p_ref = OrdersPaymentRef::withTrashed()->orderBy('auto_payment_ref_no', 'desc')->first();
            $str = @$c_p_ref->auto_payment_ref_no;
            $onlyIncrementGet = substr($str, 6);
            if($str == NULL)
            {
                $onlyIncrementGet = 0;
            }
            $system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
            $system_gen_no = $date . $system_gen_no;
            $payment_ref_no = $auto_ref_no->prefix . $system_gen_no;
        }
        return response()->json(['payment_ref_no' => $payment_ref_no]);
    }
}
