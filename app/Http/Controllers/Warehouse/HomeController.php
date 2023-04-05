<?php

namespace App\Http\Controllers\Warehouse;

use App\ExportStatus;
use App\General;
use App\Helpers\Datatables\PickInstructionDatatable;
use App\Helpers\DraftQuotationHelper;
use App\Helpers\QuantityReservedHistory;
use App\Helpers\TransferDocumentHelper;
use App\Http\Controllers\Controller;
use App\Jobs\Order\PartialMailJob;
use App\Jobs\PickInstructionJob;
use App\Mail\PartialMail;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Company;
use App\Models\Common\Configuration;
use App\Models\Common\Country;
use App\Models\Common\Courier;
use App\Models\Common\CustomerCategory;
use App\Models\Common\DraftPurchaseOrderDocument;
use App\Models\Common\DraftPurchaseOrderNote;
use App\Models\Common\OrderHistory;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrderDocument;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrderNote;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\StockOutHistory;
use App\Models\Common\Supplier;
use App\Models\Common\TableHideColumn;
use App\Models\Common\UserDetail;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\Notification;
use App\OrderTransaction;
use App\OrdersPaymentRef;
use App\QuotationConfig;
use App\TransferDocumentReservedQuantity;
use App\User;
use App\Variable;
use Auth;
use Carbon\Carbon;
use DB;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use PDF;
use Yajra\Datatables\Datatables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data]);
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function completeProfile()
    {
        $check_profile_completed = UserDetail::where('user_id', Auth::user()->id)->count();
        if ($check_profile_completed > 0) {
            return redirect()->back();
        }
        $countries = Country::get();

        return $this->render('warehouse.home.profile-complete', compact('countries'));
    }

    public function warehouseStockAdjustment()
    {
        $suppliers = Supplier::where('status', 1)->orderBy('reference_name')->get();
        $warehouses = Warehouse::find(Auth::user()->warehouse_id);
        $primary_category = ProductCategory::where('parent_id', 0)->orderBy('title')->get();
        $types = ProductType::orderBy('title')->get();
        return $this->render('warehouse.products.add-bulk-quantity', compact('suppliers', 'primary_category', 'warehouses', 'types'));
    }

    public function completeProfileProcess(Request $request)
    {
        // dd('here');
        $validator = $request->validate([
            'name' => 'required',
            'company' => 'required',
            'address' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'phone_number' => 'required',
            //'image' =>'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
        ]);

        $user_detail = new UserDetail;
        $user_detail->user_id = Auth::user()->id;
        $user_detail->company_name = $request['company'];
        $user_detail->address = $request['address'];
        $user_detail->country_id = $request['country'];
        $user_detail->state_id = $request['state'];
        $user_detail->city_name = $request['city'];
        $user_detail->zip_code = $request['zip_code'];
        $user_detail->phone_no = $request['phone_number'];
        $user_detail->save();

        return response()->json([
            "success" => true,
        ]);
    }

    public function getHome()
    {
        $today = Carbon::now();
        $today = date('Y-m-d', strtotime("+3 days"));

        $upcomming = Order::where('primary_status', 2)->whereIn('status', [9, 10])->whereHas('user', function ($q) {
            $q->whereHas('get_warehouse', function ($qu) {
                $qu->where('id', Auth::user()->get_warehouse->id);
            });
        })->select('orders.*')->where('target_ship_date', '<', $today)->count();

        $config = Configuration::first();
        if ($config->server == 'lucilla') {
            $all = Order::with('customer')->whereIn('primary_status', [2,3,17,25])->whereIn('status', [9,10,11,18,31,35])->whereHas('user', function ($q) {
                $q->whereHas('get_warehouse', function ($qu) {
                    $qu->where('id', Auth::user()->get_warehouse->id);
                });
            })->select('orders.*')->count();
        }
        else{
            $all = Order::with('customer')->where('primary_status', 2)->whereHas('user', function ($q) {
                $q->whereHas('get_warehouse', function ($qu) {
                    $qu->where('id', Auth::user()->get_warehouse->id);
                });
            })->select('orders.*')->count();
        }

        $completed = Order::with('customer')->where('primary_status', 3)->whereHas('user', function ($q) {
            $q->whereHas('get_warehouse', function ($qu) {
                $qu->where('id', Auth::user()->get_warehouse->id);
            });
        })->select('orders.*')->count();

        $delivery = Order::with('customer')->where('primary_status', 2)->where('status', 10)->whereHas('user', function ($q) {
            $q->whereHas('get_warehouse', function ($qu) {
                $qu->where('id', Auth::user()->get_warehouse->id);
            });
        })->select('orders.*')->count();

        $importing = Order::with('customer')->where('primary_status', 2)->where('status', 9)->whereHas('user', function ($q) {
            $q->whereHas('get_warehouse', function ($qu) {
                $qu->where('id', Auth::user()->get_warehouse->id);
            });
        })->select('orders.*')->count();

        $query = PurchaseOrder::where('status', 21);
        if (Auth::user()->role_id != 2 && Auth::user()->role_id != 1 && Auth::user()->role_id != 11) {
            $waitingTransferDoc = $query->whereHas('PoWarehouse', function ($q) {
                $q->where('id', Auth::user()->get_warehouse->id);
            })->count();
        } else {
            $waitingTransferDoc = $query->count();
        }

        $query = PurchaseOrder::where('status', 22);
        if (Auth::user()->role_id != 2 && Auth::user()->role_id != 1 && Auth::user()->role_id != 11) {
            $completeTransferDoc = $query->whereHas('PoWarehouse', function ($q) {
                $q->where('id', Auth::user()->get_warehouse->id);
            })->count();
        } else {
            $completeTransferDoc = $query->count();
        }

        // $completeTransferDoc = PurchaseOrder::where('status', 22)->whereHas('PoWarehouse',function($q){
        //     $q->where('id',Auth::user()->get_warehouse->id);
        // })->count();

        $sales_persons = User::where('status', 1)->whereNull('parent_id')->where('role_id', 3)->orderBy('name')->get();

        $referenceNameWithId = Customer::where('status', 1)->orderby('reference_name', 'asc')->get(['id', 'reference_name']);
        $page_status = Status::select('title')->whereIn('id', [9, 10, 11])->pluck('title')->toArray();
        $page_status_dropdown = Status::select('id', 'title')->whereIn('id', [9, 10, 11, 35])->get();

        $page_status_td = Status::select('title')->whereIn('id', [21, 22])->pluck('title')->toArray();
        $page_status_td_dropdown = Status::select('id', 'title')->whereIn('id', [21, 22])->get();
        $display_my_quotation = ColumnDisplayPreference::select('display_order')->where('type', 'pick_instruction_dashboard')->where('user_id', Auth::user()->id)->first();
        $display_my_transfer = ColumnDisplayPreference::select('display_order')->where('type', 'pick_instruction_dashboard_transfer')->where('user_id', Auth::user()->id)->first();
        // dd($display_my_quotation->display_order);
        return $this->render('warehouse.home.dashboard', compact('upcomming', 'all', 'completed', 'delivery', 'importing', 'waitingTransferDoc', 'completeTransferDoc', 'referenceNameWithId', 'sales_persons', 'page_status', 'page_status_dropdown', 'page_status_td', 'page_status_td_dropdown','display_my_quotation', 'display_my_transfer'));
    }

    public function getDraftInvoices(Request $request)
    {
        $check = 0;
        $today = Carbon::now();
        $today = date('Y-m-d', strtotime("+3 days"));
        $config = Configuration::first();
        if ($request->orders_status == '11') {
            $query = Order::where('primary_status', 3)->where('from_warehouse_id',Auth::user()->warehouse_id);
        }
        else if ($request->orders_status == '18') {
            $query = Order::where('previous_primary_status', 3)->where('from_warehouse_id',Auth::user()->warehouse_id);
        }
         else {
            if ($config->server == 'lucilla') {
                $query = Order::whereIn('primary_status', [2,3,17,25])->where('from_warehouse_id',Auth::user()->warehouse_id);
            }
            else{
                $query = Order::where('primary_status', 2)->where('from_warehouse_id',Auth::user()->warehouse_id);
            }
        }

        $query->with('customer', 'customer.primary_sale_person', 'statuses', 'order_warehouse_note', 'order_customer_note', 'order_products_not_null', 'order_products_not_null.warehouse_products_existing', 'draft_invoice_pick_instruction_printed');

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('delivery_request_date', '>=', $date);
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('delivery_request_date', '<=', $date);
        }


        if ($config->server == 'lucilla' && $request->orders_status == 'all') {
            $query->whereIn('orders.status', [9, 10, 11, 18, 31, 35]);
        }
        if ($request->orders_status != '' && (int) $request->orders_status) {
            //dd('here');
            $query->whereIn('orders.status', [$request->orders_status, 31]);
            $check = 1;
        } elseif ($request->orders_status == 'UpComming') {
            $query->whereIn('orders.status', [9, 10, 35])->where('delivery_request_date', '<', $today);
        }

        if ($request->customer_id != '') {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->salesperson_id != '') {
            $id = $request->salesperson_id;
            $query->whereHas('customer', function ($q) use ($id) {
                $q->whereHas('primary_sale_person', function ($q1) use ($id) {
                    $q1->where('users.id', $id);
                });
            });
        }

        Order::doSort($request, $query, $check);

        $dt = Datatables::of($query);
        $add_columns = ['total_amount1', 'comment_to_customer', 'comment_to_warehouse', 'invoice_date1', 'ref_id1', 'status1', 'delivery_request_date1', 'customer_name', 'customer_ref_no1', 'user_id1', 'customer_reference_name1', 'printed'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function($item) use($column) {
                return PickInstructionDatatable::returnAddColumn($column, $item);
            });
        }

        $filter_columns = ['customer_reference_name1', 'customer_ref_no1', 'comment_to_customer', 'ref_id1', 'user_id1'];
        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function($item, $keyword) use($column) {
                return PickInstructionDatatable::returnFilterColumn($column, $item, $keyword);
            });
        }

        // return Datatables::of($query)

        //     ->addColumn('customer_reference_name1', function ($item) {
        //         if ($item->customer_id != null) {
        //             if($item->customer->ecommerce_customer == 1){
        //                 $ref_no = $item->customer !== null ? $item->customer->first_name.' '.$item->customer->last_name : "--";

        //             }else{
        //                 $ref_no = $item->customer !== null ? $item->customer->reference_name : "--";
        //             }
        //             return $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail', $item->customer->id) . '"><b>' . $ref_no . '</b></a>';
        //         } else {
        //             $html_string = 'N.A';
        //         }

        //         return $html_string;
        //     })

        //     ->addColumn('user_id1', function ($item) {
        //         return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
        //     })

        //     ->filterColumn('user_id1', function ($query, $keyword) {
        //         $query = $query->whereIn('user_id', User::select('id')->where('name', 'LIKE', "%$keyword%")->pluck('id'));
        //     }, true)

        //     ->addColumn('customer_ref_no1', function ($item) {
        //           return $item->customer->reference_number;

        //     })

        //     ->addColumn('customer_name', function ($item) {
        //         // return $item->customer->company;
        //         return ($item->customer->company !== null ? @$item->customer->company : '--');
        //     })

        //     ->addColumn('delivery_request_date1', function ($item) {
        //         return $item->delivery_request_date != null ? Carbon::parse(@$item->delivery_request_date)->format('d/m/Y') : '--';
        //     })

        //     ->addColumn('status1', function ($item) {
        //         $html = '<span class="sentverification">' . $item->statuses->title . '</span>';
        //         return $html;
        //     })

        //     ->addColumn('ref_id1', function ($item) {
        //         if ($item->status_prefix !== null || $item->in_status_prefix !== null) {
        //             if ($item->primary_status == 3) {
        //                 $ref_no = @$item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
        //             } else {
        //                 $ref_no = @$item->status_prefix . '-' . $item->ref_prefix . $item->ref_id;
        //             }
        //         } else {
        //             $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id;
        //         }

        //         return $html_string = '<a href="' . route('pick-instruction', ['id' => $item->id]) . '"><b>' . $ref_no . '</b></a>';
        //     })

        //     ->filterColumn('ref_id1', function ($query, $keyword) {
        //         $result = $keyword;
        //         if (strstr($result, '-')) {
        //             $query = $query->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), $result);
        //             $query = $query->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), $result);

        //            // dd($query->toSql());
        //         } else {
        //             $resultt = preg_replace("/[^0-9]/", "", $result);
        //             if ($resultt != '') {
        //                 $query = $query->where('ref_id', 'LIKE', "%$resultt%");
        //             }
        //         }
        //     })

        //     ->addColumn('invoice_date1', function ($item) {

        //         return $item->updated_at != null ? Carbon::parse($item->updated_at)->format('d/m/Y') : "N.A";
        //     })
        //      ->addColumn('comment_to_warehouse',function($item){
        //         $warehouse_note = $item->order_warehouse_note;

        //         // $warehouse_note = OrderNote::where('order_id', $item->id)->where('type', 'warehouse')->first();

        //       return $warehouse_note != null ? $warehouse_note->note  : '--';
        //     })

        //     ->addColumn('comment_to_customer', function ($item) {
        //         // $warehouse_note = OrderNote::where('order_id', $item->id)->where('type', 'customer')->first();
        //         $warehouse_note = $item->order_customer_note;
        //         if($item->ecommerce_order == 1){
        //             return $item->delivery_note != null ? $item->delivery_note : '--';
        //         }else{
        //             return $warehouse_note != null ? $warehouse_note->note : '--';
        //         }

        //     })

        //     ->filterColumn('comment_to_customer', function ($query, $keyword) {
        //         $query = $query->whereIn('id', OrderNote::select('order_id')->where('type', 'customer')->where('note', 'LIKE', "%$keyword%")->pluck('order_id'));
        //     }, true)

        //     ->addColumn('total_amount1', function ($item) {

        //         return number_format($item->total_amount, 3, '.', ',');
        //     })

        //     ->filterColumn('customer_ref_no1', function ($query, $keyword) {
        //         $query->whereHas('customer', function ($q) use ($keyword) {
        //             $q->where('reference_number', 'LIKE', "%$keyword%");
        //         });
        //     }, true)

        //     ->filterColumn('customer_reference_name1', function ($query, $keyword) {
        //         $query->whereHas('customer', function ($q) use ($keyword) {
        //             // dd($keyword);
        //             $q->where('reference_name', 'LIKE', "%$keyword%");
        //         });
        //         // dd($query->toSql());
        //     }, true)

            $dt->setRowClass(function ($item) {
                $order_products = $item->order_products_not_null;
                $warehouse_id = Auth::user()->warehouse_id;
                $not_exist = 0;
                foreach ($order_products as $prod) {
                    $product_exist = $prod->warehouse_products_existing->where('warehouse_id', $warehouse_id)->first();
                    if ($product_exist->current_quantity != null) {
                        if ($product_exist->current_quantity >= $prod->quantity) {
                        } else {
                            $not_exist += 1;
                        }
                    } else {
                        $not_exist += 1;
                    }
                }
                if ($not_exist == 0) {
                    return 'yellowRow';
                }
            });

            $dt->rawColumns(['ref_id1', 'customer_reference_name1', 'number_of_products1', 'status1', 'user_id1', 'customer_ref_no1','comment_to_warehouse', 'printed']);
            return $dt->make(true);
    }

    public function getTransferDocument(Request $request)
    {
        // dd($request->all());
        // $query = PurchaseOrder::with('PoWarehouse', 'ToWarehouse');
        $query = PurchaseOrder::with('PoWarehouse', 'ToWarehouse','createdBy','p_o_statuses');
        if (Auth::user()->role_id != 2 && Auth::user()->role_id != 1 && Auth::user()->role_id != 11) {
            $query->whereHas('PoWarehouse', function ($q) {
                $q->where('id', Auth::user()->get_warehouse->id);
            });
        }

        $query = PurchaseOrder::doSortPickInstruction($request, $query);


        if ($request->orders_status == 21) {
            $query->where('purchase_orders.status', 21);
        } elseif ($request->orders_status == 22) {
            $query->where('purchase_orders.status', 22);
        } elseif ($request->orders_status == "all-transfer") {
            $query->whereIn('purchase_orders.status', [21, 22]);
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('transfer_date', '>=', $date);
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('transfer_date', '<=', $date);
        }

        return Datatables::of($query)

            ->addColumn('customer', function ($item) {
                if ($item->to_warehouse_id != null) {
                    $ref_no = $item->ToWarehouse->warehouse_title;
                    return $html_string = $ref_no;
                } else {
                    $html_string = 'N.A';
                }

                return $html_string;
            })

            ->addColumn('user_id', function ($item) {
                return ($item->created_by != null ? @$item->createdBy->name : '--');
            })

            ->addColumn('customer_ref_no', function ($item) {
                return $html_string = "--";
            })

            ->addColumn('delivery_request_date', function ($item) {
                return $item->target_receive_date != null ? Carbon::parse(@$item->target_receive_date)->format('d/m/Y') : '--';
            })

            ->addColumn('status', function ($item) {
                return $item->status != null ? $item->p_o_statuses->title : '--';
            })

            ->addColumn('ref_id', function ($item) {
                $ref_no = $item->ref_id !== null ? $item->ref_id : "--";
                $html_string = '<a href="' . route('pick-instruction-of-td', ['id' => $item->id]) . '"><b>' . $ref_no . '</b></a>';
                return $html_string;
            })

            ->filterColumn('ref_id', function ($query, $keyword) {
                $query->where('ref_id', 'LIKE', "%$keyword%");
            }, true)

            ->addColumn('invoice_date', function ($item) {
                return $item->confirm_date !== null ? Carbon::parse(@$item->confirm_date)->format('d/m/Y') : '--';
            })

            ->addColumn('transfer_date', function ($item) {
                return $item->transfer_date !== null ? Carbon::parse(@$item->transfer_date)->format('d/m/Y') : '--';
            })

            ->addColumn('total_amount', function ($item) {

                return number_format($item->total, 3, '.', ',');
            })

        // ->addColumn('action', function ($item) {
        // $html_string = '<a href="'.route('pick-instruction-of-td', ['id' => $item->id]).'" title="View Pick Instruction" class="actionicon viewIcon"><i class="fa fa-eye"></i>';
        // // $html_string = '<a href="#" title="View Pick Instruction" class="actionicon viewIcon"><i class="fa fa-eye"></i>';
        // return $html_string;
        // })

            ->rawColumns(['ref_id', 'customer', 'number_of_products', 'status', 'transfer_date'])
            ->make(true);
    }

    public function pickInstructionOfTransferDocument($id)
    {
        $order = PurchaseOrder::find($id);
        // $comment = OrderNote::where('order_id',$order->id)->where('type','warehouse')->first();
        return $this->render('warehouse.instruction.transferDocPickInstruction', compact('order', 'id'));
    }

    public function getTransferPickInstruction(Request $request, $id)
    {
        // dd($request->all());
        $query = PurchaseOrderDetail::with('PurchaseOrder')->where('po_id', $id)->whereNotNull('product_id');

        $query = PurchaseOrderDetail::doSort($request, $query);

        return Datatables::of($query)

            ->addColumn('item_no', function ($item) {
                return $html_string = '<a target="_blank" href="' . url('get-product-detail/' . $item->product->id) . '"><b>' . $item->product->refrence_code . '</b></a>';
            })

            ->addColumn('description', function ($item) {
                $html_string = $item->product != null ? $item->product->short_desc : 'N.A';

                return $html_string;
            })

            ->addColumn('location_code', function ($item) {
                return @$item->PurchaseOrder->from_warehouse_id != null ? @$item->PurchaseOrder->PoWarehouse->location_code : '--';
            })

            ->addColumn('trasnfer_num_of_pieces', function ($item) {
                $html_string = $item->trasnfer_num_of_pieces != null ? $item->trasnfer_num_of_pieces : 'N.A';

                return $html_string;
            })

            ->addColumn('qty_ordered', function ($item) {
                $html_string = $item->quantity != null ? $item->quantity : 'N.A';

                return $html_string;
            })

            ->addColumn('unit_of_measure', function ($item) {
                $html_string = $item->product != null ? $item->product->sellingUnits->title : 'N.A';

                return $html_string;
            })

            ->addColumn('unit_price', function ($item) {
                $html_string = $item->pod_unit_price !== null ? number_format($item->pod_unit_price, 3, '.', ',') : 'N.A';

                return $html_string;
            })

            ->addColumn('trasnfer_pcs_shipped', function ($item) {
                if ($item->PurchaseOrder->status == 22) {
                    return $item->trasnfer_pcs_shipped != null ? $item->trasnfer_pcs_shipped : 'N.A';
                } else {
                    $html_string = '<input type="number"  name="trasnfer_pcs_shipped" data-id="' . $item->id . '" class="fieldFocus" data-fieldvalue="' . $item->trasnfer_pcs_shipped . '" value="' . $item->trasnfer_pcs_shipped . '" data-id="'.$item->id.'" data-product_id="'.$item->product_id.'" readonly disabled style="width:100%">';
                    return $html_string;
                }
            })

            ->addColumn('trasnfer_qty_shipped', function ($item) {
                if ($item->PurchaseOrder->status == 22) {
                    return $item->trasnfer_qty_shipped != null ? $item->trasnfer_qty_shipped : 'N.A';
                } else {
                    $html_string = '<input type="number"  name="trasnfer_qty_shipped" data-id="' . $item->id . '" class="fieldFocus" data-fieldvalue="' . $item->trasnfer_qty_shipped . '" value="' . $item->trasnfer_qty_shipped . '" readonly disabled style="width:100%">';
                    return $html_string;
                }
            })

            ->addColumn('trasnfer_expiration_date', function ($item) {
                if ($item->PurchaseOrder->status == 22) {
                    return $item->trasnfer_expiration_date != null ? $item->trasnfer_expiration_date : 'N.A';
                } else {
                    $html_string = '<input type="text"  name="trasnfer_expiration_date" data-id="' . $item->id . '" class="trasnfer_expiration_date" data-fieldvalue="' . $item->trasnfer_expiration_date . '" value="' . $item->trasnfer_expiration_date . '" readonly="readonly" disabled style="width:100%" id="trasnfer_expiration_date">';
                    return $html_string;
                }
            })

            ->setRowClass(function ($item) {
                if ($item->PurchaseOrder->status == 22) {
                    // do nothing
                } else {
                    $warehouse_id = Auth::user()->warehouse_id;
                    $not_exist = 0;
                    // dd($order_products);
                    $product_exist = WarehouseProduct::select('current_quantity')->where('warehouse_id', $warehouse_id)->where('product_id', $item->product_id)->first();

                    if ($product_exist->current_quantity != null) {
                        if ($product_exist->current_quantity >= $item->quantity) {
                        } else {
                            $not_exist += 1;
                        }
                    } else {
                        $not_exist += 1;
                    }

                    if ($not_exist == 0) {
                        return 'yellowRow';
                    }
                }
            })

            ->rawColumns(['item_no', 'trasnfer_pcs_shipped', 'trasnfer_qty_shipped', 'trasnfer_expiration_date'])
            ->make(true);
    }

    public function pickInstruction($id)
    {
        $order = Order::find($id);
        $comment = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
        $comment_to_customer = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
        if ($order->primary_status == 3 || $order->previous_primary_status == 3) {
            $display_my_quotation = ColumnDisplayPreference::select('display_order')->where('type', 'completed_pick_instruction_detail')->where('user_id', Auth::user()->id)->first();
            return $this->render('warehouse.instruction.completed-pickinstruction', compact('order', 'id', 'comment', 'comment_to_customer','display_my_quotation'));
        } else {
            $display_my_quotation = ColumnDisplayPreference::select('display_order')->where('type', 'pick_instruction_detail')->where('user_id', Auth::user()->id)->first();
            // sup-251 done for this

            $pi_redirection_config = QuotationConfig::where('section','pick_instruction_redirecion')->first();
            return $this->render('warehouse.instruction.pickInstruction', compact('order', 'id', 'comment', 'comment_to_customer','display_my_quotation', 'pi_redirection_config'));
        }
    }

    public function exportPiToPdf(Request $request, $id,$column_name,$default_sort)
    {
        $orders_array = explode(",",$id);
        $id = $orders_array[0];
        // dd('here');
        $ordersProducts = OrderProduct::with('get_order')->where('order_id', $id)->whereNotNull('order_products.product_id')->where('order_products.quantity', '!=', 0);
        $ordersProducts = OrderProduct::doSortPickInstructionDetialPagePrint($column_name, $default_sort, $ordersProducts);
        // dd($ordersProducts);
        $order = Order::find($id);
        $comment = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
        $comment_to_customer = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();

        $cust_id = $order->customer_id;
        $default_sort = 'id_sort';

        $customer = Customer::find($cust_id);
        $view = view('warehouse.pick-instruction.invoice', compact('ordersProducts', 'order', 'id', 'customer', 'comment', 'comment_to_customer','orders_array','default_sort', 'column_name'))->render();
        // return $view;
        // return response()->json(['view'=>$view,'success'=>true]);
        $pdf = PDF::loadView('warehouse.pick-instruction.invoice', compact('ordersProducts', 'order', 'id', 'customer', 'comment', 'comment_to_customer','orders_array','default_sort', 'column_name'))->setPaper('letter', 'landscape');

        // making pdf name starts
        $makePdfName = 'Pick Instruction-' . $id . '';
        // $makePdfName='PO '.($mainObj->user_ref_id != null ? $mainObj->user_ref_id  : $mainObj->ref_id);
        // making pdf name ends

        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0,
            )
        );

        // return $pdf->download($makePdfName.'.pdf');

    }

    public function exportStrickerToPdf(Request $request, $id){

        $ordersProducts = OrderProduct::with('get_order')->where('order_id', $id)->whereNotNull('product_id')->where('quantity', '!=', 0)->orderBy('id', 'ASC')->get();
        $order = Order::find($id);

        $cust_id = $order->customer_id;

        $customer = Customer::find($cust_id);
        // dd($customer);
        $view = view('warehouse.pick-instruction.sticker', compact('ordersProducts', 'order', 'customer'))->render();
        $pdf = PDF::loadView('warehouse.pick-instruction.sticker', compact('ordersProducts', 'order', 'customer'))->setPaper('letter', 'landscape');
        // $customPaper = array(0,0,283.465,170.079);
        // $pdf->setPaper($customPaper);
        $makePdfName = 'Pick Instruction Sticker-' . $id . '';
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0,
            )
        );
    }

    public function getPickInstruction(Request $request, $id)
    {
        // dd($request->all());
        // $query = OrderProduct::with('get_order')->where('order_id', $id)->whereNotNull('product_id')->orderBy('id', 'ASC')->get();
        $query = OrderProduct::with('get_order','product','warehouse','po_detail_product','get_order_product_notes','warehouse_products_existing')->where('order_id', $id)->whereNotNull('order_products.product_id');
        $order_ids = Order::where('primary_status', 2)->whereHas('user', function ($q) {
            $q->where('warehouse_id', Auth::user()->warehouse_id);
        })->pluck('id')->toArray();
        $pids = PurchaseOrder::select('id')->where('status', 21)->with('PoWarehouse');


        $query = OrderProduct::doSortPickInstructionDetialPage($request, $query);



        return Datatables::of($query)

            ->addColumn('item_no', function ($item) {
                return $html_string = '<a target="_blank" href="' . url('get-product-detail/' . $item->product->id) . '"><b>' . $item->product->refrence_code . '</b></a>';
            })

            ->addColumn('description', function ($item) {
                $html_string = $item->product != null ? $item->product->short_desc : 'N.A';

                return $html_string;
            })

            ->addColumn('location_code', function ($item) {
                return @$item->warehouse != null ? @$item->warehouse->location_code : '--';
            })

            ->addColumn('pcs_ordered', function ($item) {
                $html_string = $item->number_of_pieces != null ? $item->number_of_pieces : 'N.A';
                $html_string .= '
                <div class="custom-control custom-radio custom-control-inline pull-right">';
                $html_string .= '<input type="checkbox" class="condition custom-control-input" id="pieces' . @$item->id . '" name="is_retail" data-id="' . $item->id . ' ' . @$item->number_of_pieces . '" value="pieces" ' . ($item->is_retail == "pieces" ? "checked" : "") . ' disabled>';

                $html_string .= '<label class="custom-control-label" for="pieces' . @$item->id . '"></label></div>';
                return $html_string;
            })

            ->addColumn('qty_ordered', function ($item) {
                $html_string = $item->quantity != null ? $item->quantity : 'N.A';
                $html_string .= '
              <div class="custom-control custom-radio custom-control-inline pull-right">';
                $html_string .= '<input type="checkbox" class="condition custom-control-input" id="is_retail' . @$item->id . '" name="is_retail" data-id="' . $item->id . ' ' . @$item->quantity . '" value="qty" ' . ($item->is_retail == "qty" ? "checked" : "") . ' disabled>';

                $html_string .= '<label class="custom-control-label" for="is_retail' . @$item->id . '"></label></div>';

                return $html_string;
            })

            ->addColumn('unit_of_measure', function ($item) {
                $html_string = $item->product != null ? $item->product->sellingUnits->title : 'N.A';

                return $html_string;
            })

            ->addColumn('qty_to_ship', function ($item) {

                return 0;
            })

            ->addColumn('unit_price', function ($item) {
                $html_string = $item->product != null ? number_format($item->unit_price, 3, '.', ',') : 'N.A';

                return $html_string;
            })

            ->addColumn('pcs_shipped', function ($item) {
                // if($item->get_order->primary_status == 3)
                // {
                //     return $item->pcs_shipped != null ? $item->pcs_shipped : 'N.A';
                // }
                // else
                // {
                $html_string = '<input type="number"  name="pcs_shipped" data-id="' . $item->id . '" class="fieldFocus" data-fieldvalue="' . $item->pcs_shipped . '" value="' . $item->pcs_shipped . '" readonly disabled style="width:100%">';
                return $html_string;
                // }
            })
        // ->addColumn('reserved_qty',function($item){
        //     $order_ids = Order::where('primary_status',2)->whereHas('user',function($q){
        //         $q->where('warehouse_id',Auth::user()->warehouse_id);
        //       })->pluck('id')->toArray();
        //       $order_products =  OrderProduct::whereIn('order_id',$order_ids)->where('product_id',$item->product_id)->sum('qty_shipped');
        //       return round($order_products,3);
        // })

            ->addColumn('qty_shipped', function ($item) {
                $html_string = '<input type="number"  name="qty_shipped" data-id="' . $item->id . '" class="fieldFocus" data-fieldvalue="' . $item->qty_shipped . '" value="' . $item->qty_shipped . '" readonly disabled style="width:100%">';
                return $html_string;
            })

            ->addColumn('expiration_date', function ($item) {
                $exp_date = $item->expiration_date !== null ? Carbon::parse($item->expiration_date)->format('d/m/Y') : '';
                $html_string = '<input type="text" id="expiration_date" name="expiration_date" data-id="' . $item->id . '" class="expiration_date" data-fieldvalue="' . $exp_date . '" value="' . $exp_date . '" readonly disabled style="width:100%">';
                return $html_string;
            })

            ->addColumn('notes', function ($item) {
                // check already uploaded images //
                //$notes = OrderProductNote::where('order_product_id', $item->id)->count();
                $notes = $item->get_order_product_notes;
                //$html_string = '<div class="d-flex justify-content-center text-center">';
                if ($notes->count() > 0) {
                    //$html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';

                    $html_string = '';
                    foreach ($notes as $note) {
                        $html_string .= $note->note . '.<br>';
                    }
                } else {
                    $html_string = 'N.A';
                }
                // if(@$item->status != 18){
                //     // $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus" title="Add Note"></a>
                //     //       </div>';
                //     $compl_quot_notes = OrderProductNote::where('order_product_id',$request->compl_quot_id)->get();
                //     if ($compl_quot_notes->count() > 0) {
                //         $html_string ='';
                //         foreach ($compl_quot_notes as $note) {
                //             $html_string .= $note->note.',';
                //         }
                //     }else{
                //         $html_string = 'N.A';
                //     }
                // }else{
                //     $html_string = '--';
                // }

                return $html_string;
            })

            ->addColumn('current_qty', function ($item) {
                // $warehouse_product = WarehouseProduct::where('product_id', $item->product_id)->where('warehouse_id', Auth::user()->warehouse_id)->first();
                $warehouse_product =$item->warehouse_products_existing->where('warehouse_id', Auth::user()->warehouse_id)->first();
                $qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
                return round($qty, 3) . ' ' . $item->product->sellingUnits->title;
            })

            ->addColumn('reserved_qty', function ($item) use($order_ids,$pids) {
                // $order_ids = Order::where('primary_status', 2)->whereHas('user', function ($q) {
                //     $q->where('warehouse_id', Auth::user()->warehouse_id);
                // })->pluck('id')->toArray();
                // $order_products = $item->whereIn('order_id', $order_ids)->where('product_id', $item->product_id)->sum('quantity');
                // $order_products = OrderProduct::whereIn('order_id', $order_ids)->where('product_id', $item->product_id)->sum('quantity');
                // $pids = PurchaseOrder::where('status', 21)->whereHas('PoWarehouse', function ($qq) use ($item) {
                //     $qq->where('from_warehouse_id', $item->warehouse_id);
                // })->pluck('id')->toArray();
                // $pids=$pids->whereHas('PoWarehouse', function ($qq) use ($item) {
                //         $qq->where('from_warehouse_id', $item->warehouse_id);
                //     })->toArray();
                // $pqty = $item->po_detail_product->whereIn('po_id', $pids)->where('product_id', $item->id)->sum('quantity');
                // return round($order_products + $pqty, 3);

                $warehouse_product =$item->warehouse_products_existing->where('warehouse_id', Auth::user()->warehouse_id)->first();
                $qty = (@$warehouse_product->reserved_quantity != null) ? (@$warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity) : ' 0';

                return round($qty,3);
            })

            ->setRowClass(function ($item) {
                if ($item->get_order->primary_status == 3) {
                } else {
                    $warehouse_id = Auth::user()->warehouse_id;
                    $not_exist = 0;
                    // dd($order_products);
                    $product_exist = $item->warehouse_products_existing->where('warehouse_id', $warehouse_id)->first();
                    // $product_exist = WarehouseProduct::select('current_quantity')->where('warehouse_id', $warehouse_id)->where('product_id', $item->product_id)->first();

                    if ($product_exist->current_quantity != null) {
                        if ($product_exist->current_quantity >= $item->quantity) {
                        } else {
                            $not_exist += 1;
                        }
                    } else {
                        $not_exist += 1;
                    }

                    if ($not_exist == 0) {
                        return 'yellowRow';
                    }
                }
            })

            ->rawColumns(['item_no', 'pcs_shipped', 'qty_shipped', 'expiration_date', 'notes', 'qty_ordered', 'pcs_ordered'])
            ->make(true);
    }

    public function fullQTYShippedFunction(Request $request){
        $query = PurchaseOrderDetail::with('PurchaseOrder')->where('po_id', $request->id)->whereNotNull('product_id')->orderBy('product_id','ASC')->get();
        foreach($query as $item){
            $item->trasnfer_qty_shipped = $item->quantity;
            $item->save();
        }
        return response()->json(['success' => true]);
    }

    public function editPickInstruction(Request $request)
    {
        $order_product = OrderProduct::where('id', $request->order_product_id)->first();
        $order = Order::where('id', $order_product->order_id)->first();
        $item_unit_price = number_format($order_product->unit_price, 2, '.', '');
        foreach ($request->except('order_product_id') as $key => $value) {
            if ($order_product->get_order->primary_status == 3) {
                if ($key == 'qty_shipped') {
                    if($order_product->product != null)
                    {
                      $decimal_places = $order_product->product != null ? ($order_product->product->sellingUnits != null ? $order_product->product->sellingUnits->decimal_places : 3) : 3;
                      $value = round($value,$decimal_places);
                    }
                    $quantity_shipped = $order_product->qty_shipped - $value;
                    if ($order_product->expiration_date != null) {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title = 'Adjustment';
                            $stock->product_id = $order_product->product_id;
                            $stock->created_by = Auth::user()->id;
                            $stock->warehouse_id = Auth::user()->get_warehouse->id;
                            $stock->expiration_date = $order_product->expiration_date;
                            $stock->save();
                        }
                        if ($stock != null) {
                            $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $quantity_shipped, Auth::user()->get_warehouse->id);
                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($quantity_shipped,$stock, $stock_out, $order_product);
                        }
                    } else {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                        $shipped = $quantity_shipped;
                        foreach ($stock as $st) {
                            $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                            $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                            $balance = ($stock_out_in) + ($stock_out_out);
                            $balance = round($balance, 3);
                            if ($balance > 0) {
                                $inStock = $balance + $shipped;
                                if ($inStock >= 0) {
                                    $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, Auth::user()->get_warehouse->id);
                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped,$st, $stock_out, $order_product);

                                    $shipped = 0;
                                    break;
                                } else {
                                    $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, Auth::user()->get_warehouse->id);
                                    if($shipped < 0)
                                        {
                                          //To find from which stock the order will be deducted
                                                $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
                                                if($find_stock->count() > 0)
                                                {
                                                    foreach ($find_stock as $out)
                                                    {

                                                        if(abs($stock_out->available_stock) > 0)
                                                        {
                                                                if($out->available_stock >= abs($stock_out->available_stock))
                                                                {
                                                                    $stock_out->parent_id_in .= $out->id.',';
                                                                    $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                    $stock_out->available_stock = 0;
                                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,abs($stock_out->available_stock));
                                                                }
                                                                else
                                                                {
                                                                    $history_quantity = $out->available_stock;
                                                                    $stock_out->parent_id_in .= $out->id.',';
                                                                    $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                    $out->available_stock = 0;
                                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,round(abs($history_quantity),4));
                                                                }
                                                                $out->save();
                                                                $stock_out->save();
                                                        }
                                                    }

                                                    $shipped = abs($stock_out->available_stock);

                                                    $stock_out->available_stock = 0;
                                                    $stock_out->save();
                                                }
                                                else
                                                {
                                                  $shipped = $inStock;
                                                }
                                        }
                                        else
                                        {
                                          $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
                                          if($find_stock->count() > 0)
                                          {
                                              foreach ($find_stock as $out) {

                                                  if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
                                                  {
                                                          if($stock_out->available_stock >= abs($out->available_stock))
                                                          {
                                                              $history_quantity = $out->available_stock;
                                                              $out->parent_id_in .= $stock_out->id.',';
                                                              $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                              $out->available_stock = 0;

                                                              $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,round(abs($history_quantity),4));
                                                          }
                                                          else
                                                          {
                                                              $out->parent_id_in .= $out->id.',';
                                                              $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                              $stock_out->available_stock = 0;
                                                              $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,abs($stock_out->available_stock));
                                                          }
                                                          $out->save();
                                                          $stock_out->save();
                                                  }
                                              }
                                              $shipped = abs($stock_out->available_stock);

                                              $stock_out->available_stock = 0;
                                              $stock_out->save();
                                          }
                                          else
                                          {
                                            $shipped = $inStock;
                                          }
                                        }
                                    // $shipped = $inStock;
                                }
                            }
                        }
                        if ($shipped != 0) {
                            $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->whereNull('expiration_date')->first();
                            if ($stock == null) {
                                $stock = new StockManagementIn;
                                $stock->title = 'Adjustment';
                                $stock->product_id = $order_product->product_id;
                                $stock->created_by = Auth::user()->id;
                                $stock->warehouse_id = Auth::user()->get_warehouse->id;
                                $stock->expiration_date = $order_product->expiration_date;
                                $stock->save();
                            }
                            $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $shipped, Auth::user()->get_warehouse->id);
                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped,$stock, $stock_out, $order_product);
                        }
                    }
                    DB::beginTransaction();
                    try
                      {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentQuantity($order_product,$quantity_shipped,'add',null);
                        DB::commit();
                      }
                      catch(\Exception $e)
                      {
                        DB::rollBack();
                      }

                    $order_history = new OrderHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->reference_number = $order_product->product->refrence_code;
                    $order_history->column_name = "Qty Sent";
                    $order_history->old_value = $order_product->qty_shipped;
                    $order_history->new_value = $value;
                    $order_history->order_id = $order_product->order_id;
                    $order_history->save();
                    if ($order_product->is_retail == 'qty') {
                        $order_product->$key = $value;
                        $order_product->save();
                        $calcu = DraftQuotationHelper::orderCalculation($order_product, $order);

                        $sub_total = 0;
                        $total_vat = 0;
                        $grand_total = 0;
                        $sub_total_w_w = 0;
                        $all_products = OrderProduct::where('order_id', $order_product->order_id)->get();
                        foreach ($all_products as $valuee) {
                            // $sub_total += $value->quantity * $value->unit_price;
                            $sub_total += $valuee->total_price;
                            $sub_total_w_w += $valuee->total_price_with_vat;
                            $total_vat += @$valuee->vat_amount_total !== null ? @$valuee->vat_amount_total : (@$valuee->total_price_with_vat-@$valuee->total_price);

                            // $total_vat += @$valuee->total_price * (@$valuee->vat / 100);

                        }
                        $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);
                        $order->update(['total_amount' => $grand_total]);
                    }
                }

                if ($key == 'pcs_shipped') {
                    $order_history = new OrderHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->reference_number = $order_product->product->refrence_code;
                    $order_history->column_name = "Pieces Sent";
                    $order_history->old_value = $order_product->pcs_shipped;
                    $order_history->new_value = $value;
                    $order_history->order_id = $order_product->order_id;
                    $order_history->save();
                    if ($order_product->is_retail == 'pieces') {
                        $order_product->$key = $value;
                        $order_product->save();
                        $calcu = DraftQuotationHelper::orderCalculation($order_product, $order);

                        $sub_total = 0;
                        $total_vat = 0;
                        $grand_total = 0;
                        $sub_total_w_w = 0;
                        $all_products = OrderProduct::where('order_id', $order_product->order_id)->get();
                        foreach ($all_products as $valuee) {
                            // $sub_total += $value->quantity * $value->unit_price;
                            $sub_total += $valuee->total_price;
                            // $total_vat += @$valuee->total_price * (@$valuee->vat / 100);;
                            $sub_total_w_w += $valuee->total_price_with_vat;
                            $total_vat += @$valuee->vat_amount_total !== null ? @$valuee->vat_amount_total : (@$valuee->total_price_with_vat-@$valuee->total_price);
                        }
                        $grand_total = ($sub_total) - ($order->discount) + ($order->shipping);
                        $order->update(['total_amount' => $grand_total]);
                    }
                }

                $order_product->$key = $value;
                $order_product->save();
            }
            else if ($order_product->get_order->previous_primary_status == 3) {
                if ($key == 'qty_shipped') {
                    if($order_product->product != null)
                    {
                      $decimal_places = $order_product->product != null ? ($order_product->product->sellingUnits != null ? $order_product->product->sellingUnits->decimal_places : 3) : 3;
                      $value = round($value,$decimal_places);
                    }
                    $quantity_shipped = $order_product->qty_shipped - $value;
                    if ($order_product->expiration_date != null) {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title = 'Adjustment';
                            $stock->product_id = $order_product->product_id;
                            $stock->created_by = Auth::user()->id;
                            $stock->warehouse_id = Auth::user()->get_warehouse->id;
                            $stock->expiration_date = $order_product->expiration_date;
                            $stock->save();
                        }
                        if ($stock != null) {
                            $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $quantity_shipped, Auth::user()->get_warehouse->id);

                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($quantity_shipped,$stock, $stock_out, $order_product);
                        }
                    } else {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                        $shipped = $quantity_shipped;
                        foreach ($stock as $st) {
                            $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                            $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                            $balance = ($stock_out_in) + ($stock_out_out);
                            $balance = round($balance, 3);
                            if ($balance > 0) {
                                $inStock = $balance + $shipped;
                                if ($inStock >= 0) {
                                    $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, Auth::user()->get_warehouse->id);
                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped,$st, $stock_out, $order_product);
                                    $shipped = 0;
                                    break;
                                } else {
                                    $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, $shipped, Auth::user()->get_warehouse->id,$balance);
                                    if($shipped < 0)
                                        {
                                          //To find from which stock the order will be deducted
                                                $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
                                                if($find_stock->count() > 0)
                                                {
                                                    foreach ($find_stock as $out)
                                                    {

                                                        if(abs($stock_out->available_stock) > 0)
                                                        {
                                                                if($out->available_stock >= abs($stock_out->available_stock))
                                                                {
                                                                    $stock_out->parent_id_in .= $out->id.',';
                                                                    $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                    $stock_out->available_stock = 0;
                                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,abs($stock_out->available_stock));
                                                                }
                                                                else
                                                                {
                                                                    $history_quantity = $out->available_stock;
                                                                    $stock_out->parent_id_in .= $out->id.',';
                                                                    $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                                    $out->available_stock = 0;
                                                                    $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,round(abs($history_quantity),4));
                                                                }
                                                                $out->save();
                                                                $stock_out->save();
                                                        }
                                                    }

                                                    $shipped = abs($stock_out->available_stock);

                                                    $stock_out->available_stock = 0;
                                                    $stock_out->save();
                                                }
                                                else
                                                {
                                                  $shipped = $inStock;
                                                }
                                        }
                                        else
                                        {
                                          $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
                                          if($find_stock->count() > 0)
                                          {
                                              foreach ($find_stock as $out) {

                                                  if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
                                                  {
                                                          if($stock_out->available_stock >= abs($out->available_stock))
                                                          {
                                                              $history_quantity = $out->available_stock;
                                                              $out->parent_id_in .= $stock_out->id.',';
                                                              $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                              $out->available_stock = 0;
                                                              $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,round(abs($history_quantity),4));
                                                          }
                                                          else
                                                          {
                                                              $out->parent_id_in .= $out->id.',';
                                                              $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                              $stock_out->available_stock = 0;
                                                              $new_stock_out_history = (new StockOutHistory)->setHistory($out,$stock_out,$order_product,abs($stock_out->available_stock));
                                                          }
                                                          $out->save();
                                                          $stock_out->save();
                                                  }
                                              }
                                              $shipped = abs($stock_out->available_stock);

                                              $stock_out->available_stock = 0;
                                              $stock_out->save();
                                          }
                                          else
                                          {
                                            $shipped = $inStock;
                                          }
                                        }
                                    // $shipped = $inStock;
                                }
                            }
                        }
                        if ($shipped != 0) {
                            $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->whereNull('expiration_date')->first();
                            if ($stock == null) {
                                $stock = new StockManagementIn;
                                $stock->title = 'Adjustment';
                                $stock->product_id = $order_product->product_id;
                                $stock->created_by = Auth::user()->id;
                                $stock->warehouse_id = Auth::user()->get_warehouse->id;
                                $stock->expiration_date = $order_product->expiration_date;
                                $stock->save();
                            }
                            $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $shipped, Auth::user()->get_warehouse->id);
                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($shipped,$stock, $stock_out, $order_product);
                        }
                    }

                    DB::beginTransaction();
                    try
                      {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentQuantity($order_product,$quantity_shipped,'add',null);
                        DB::commit();
                      }
                      catch(\Excepion $e)
                      {
                        DB::rollBack();
                      }

                    $order_history = new OrderHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->reference_number = $order_product->product->refrence_code;
                    $order_history->column_name = "Qty Sent";
                    $order_history->old_value = $order_product->qty_shipped;
                    $order_history->new_value = $value;
                    $order_history->order_id = $order_product->order_id;
                    $order_history->save();
                    if ($order_product->is_retail == 'qty') {
                        $order_product->$key = $value;
                        $order_product->save();
                        $calcu = DraftQuotationHelper::orderCalculation($order_product, $order);

                        $sub_total = 0;
                        $total_vat = 0;
                        $grand_total = 0;
                        $sub_total_w_w = 0;
                        $all_products = OrderProduct::where('order_id', $order_product->order_id)->get();
                        foreach ($all_products as $valuee) {
                            // $sub_total += $value->quantity * $value->unit_price;
                            $sub_total += $valuee->total_price;
                            $sub_total_w_w += $valuee->total_price_with_vat;
                            $total_vat += @$valuee->vat_amount_total !== null ? @$valuee->vat_amount_total : (@$valuee->total_price_with_vat-@$valuee->total_price);

                            // $total_vat += @$valuee->total_price * (@$valuee->vat / 100);

                        }
                        $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);
                        $order->update(['total_amount' => $grand_total]);
                    }
                }

                if ($key == 'pcs_shipped') {
                    $order_history = new OrderHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->reference_number = $order_product->product->refrence_code;
                    $order_history->column_name = "Pieces Sent";
                    $order_history->old_value = $order_product->pcs_shipped;
                    $order_history->new_value = $value;
                    $order_history->order_id = $order_product->order_id;
                    $order_history->save();
                    if ($order_product->is_retail == 'pieces') {
                        $order_product->$key = $value;
                        $order_product->save();
                        $calcu = DraftQuotationHelper::orderCalculation($order_product, $order);
                        
                        $sub_total = 0;
                        $total_vat = 0;
                        $grand_total = 0;
                        $sub_total_w_w = 0;
                        $all_products = OrderProduct::where('order_id', $order_product->order_id)->get();
                        foreach ($all_products as $valuee) {
                            // $sub_total += $value->quantity * $value->unit_price;
                            $sub_total += $valuee->total_price;
                            // $total_vat += @$valuee->total_price * (@$valuee->vat / 100);;
                            $sub_total_w_w += $valuee->total_price_with_vat;
                            $total_vat += @$valuee->vat_amount_total !== null ? @$valuee->vat_amount_total : (@$valuee->total_price_with_vat-@$valuee->total_price);
                        }
                        $grand_total = ($sub_total) - ($order->discount) + ($order->shipping);
                        $order->update(['total_amount' => $grand_total]);
                    }
                }

                $order_product->$key = $value;
                $order_product->save();
            } else {
                if ($key == 'expiration_date') {
                    $value = str_replace("/", "-", $request->expiration_date);
                    $value = date('Y-m-d', strtotime($value));
                    $order_product->$key = $value;
                    $order_product->save();
                } else {
                    if($key == 'qty_shipped')
                    {
                        if($order_product->product != null)
                        {
                          $decimal_places = $order_product->product != null ? ($order_product->product->sellingUnits != null ? $order_product->product->sellingUnits->decimal_places : 3) : 3;
                          $value = round($value,$decimal_places);
                        }
                    }
                    $order_product->$key = $value;
                    $order_product->save();
                }
            }
        }
        return response()->json(['success' => true]);
    }

    public function fullQtyShipImporting(Request $request){
        $query = OrderProduct::where('order_id', $request->id)->get();
        foreach($query as $item){
            $item->qty_shipped = $item->quantity;
            $item->save();
        }
        return response()->json(['success' => true]);
    }
    public function fullPCSShipImporting(Request $request){
        $query = OrderProduct::where('order_id', $request->id)->get();
        foreach($query as $item){
            $item->pcs_shipped = $item->number_of_pieces;
            $item->save();
        }
        return response()->json(['success' => true]);
    }
    public function editTransferPickInstruction(Request $request)
    {
        // dd($request->all());
        $order_product = PurchaseOrderDetail::where('id', $request->pod_id)->first();
        $manual_supplier = Supplier::where('manual_supplier',1)->first();
        foreach ($request->except('pod_id') as $key => $value) {
            if ($order_product->PurchaseOrder->status == 22) {
                $supply_from_id = $order_product->PurchaseOrder->from_warehouse_id;
                if ($key == 'trasnfer_qty_shipped') {
                    $decimal_places = ($order_product->product->units != null ? $order_product->product->units->decimal_places : 4);
                    $value = round($value,$decimal_places);

                    $quantity_shipped = $order_product->trasnfer_qty_shipped - $value;
                    $stock = StockManagementIn::where('expiration_date', $order_product->trasnfer_expiration_date)->where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->first();
                    if ($stock != null) {
                        $stock_out = new StockManagementOut;
                        $stock_out->smi_id = $stock->id;
                        $stock_out->po_id = @$order_product->po_id;
                        $stock_out->p_o_d_id = @$order_product->id;
                        // $stock_out->order_id = $order_product->get_order->id;
                        // $stock_out->order_product_id = $order_product->id;
                        $stock_out->product_id = $order_product->product_id;
                        if ($quantity_shipped < 0) {
                            $stock_out->quantity_out = $quantity_shipped;
                            $stock_out->available_stock = $quantity_shipped;
                        } else {
                            $stock_out->quantity_in = $quantity_shipped;
                            $stock_out->available_stock = $quantity_shipped;
                            $stock_out->supplier_id = @$manual_supplier->id;
                        }
                        $stock_out->created_by = Auth::user()->id;
                        $stock_out->warehouse_id = @$supply_from_id;
                        $stock_out->save();

                        if($quantity_shipped < 0)
                        {
                            $dummy_order = Order::createManualOrder($stock_out);
                          //To find from which stock the order will be deducted
                                $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
                                if($find_stock->count() > 0)
                                {
                                    foreach ($find_stock as $out)
                                    {

                                        if(abs($stock_out->available_stock) > 0)
                                        {
                                                if($out->available_stock >= abs($stock_out->available_stock))
                                                {
                                                    $history_quantity = $stock_out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id.',';
                                                    $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                    $stock_out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,abs($history_quantity));
                                                }
                                                else
                                                {
                                                    $history_quantity = $out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id.',';
                                                    $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                    $out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,abs($history_quantity));
                                                }
                                                $out->save();
                                                $stock_out->save();
                                        }
                                    }
                                }
                        }
                        else
                        {
                          $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
                          if($find_stock->count() > 0)
                          {
                              foreach ($find_stock as $out) {

                                  if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
                                  {
                                          if($stock_out->available_stock >= abs($out->available_stock))
                                          {
                                            $history_quantity = $out->available_stock;
                                              $out->parent_id_in .= $stock_out->id.',';
                                              $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                              $out->available_stock = 0;
                                              $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$order_product,abs($history_quantity));
                                          }
                                          else
                                          {
                                              $history_quantity = $stock_out->available_stock;
                                              $out->parent_id_in .= $out->id.',';
                                              $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                              $stock_out->available_stock = 0;
                                              $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$order_product,abs($history_quantity));
                                          }
                                          $out->save();
                                          $stock_out->save();
                                  }
                              }
                          }
                        }
                    } else {
                        if ($order_product->trasnfer_expiration_date != null) {
                            $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->orderBy('expiration_date', 'ASC')->whereNotNull('expiration_date')->first();
                        } else {
                            $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->whereNull('expiration_date')->first();
                            if ($stock == null) {

                                $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->orderBy('expiration_date', 'ASC')->first();
                            }
                        }

                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title = 'Adjustment';
                            $stock->product_id = $order_product->product_id;
                            $stock->created_by = Auth::user()->id;
                            $stock->warehouse_id = @$supply_from_id;
                            $stock->expiration_date = $order_product->trasnfer_expiration_date;
                            $stock->save();
                        }

                        $stock_out = new StockManagementOut;
                        $stock_out->smi_id = $stock->id;
                        // $stock_out->order_id = $order_product->get_order->id;
                        // $stock_out->order_product_id = $order_product->id;
                        $stock_out->po_id = @$order_product->po_id;
                        $stock_out->p_o_d_id = @$order_product->id;
                        $stock_out->product_id = $order_product->product_id;
                        if ($quantity_shipped < 0) {
                            $stock_out->quantity_out = $quantity_shipped;
                            $stock_out->available_stock = $quantity_shipped;
                        } else {
                            $stock_out->quantity_in = $quantity_shipped;
                            $stock_out->available_stock = $quantity_shipped;
                            $stock_out->supplier_id = @$manual_supplier->id;
                        }
                        $stock_out->created_by = Auth::user()->id;
                        $stock_out->warehouse_id = @$supply_from_id;
                        $stock_out->save();

                        if($quantity_shipped < 0)
                        {
                            $dummy_order = Order::createManualOrder($stock_out);
                          //To find from which stock the order will be deducted
                                $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
                                if($find_stock->count() > 0)
                                {
                                    foreach ($find_stock as $out)
                                    {

                                        if(abs($stock_out->available_stock) > 0)
                                        {
                                                if($out->available_stock >= abs($stock_out->available_stock))
                                                {
                                                    $history_quantity = $stock_out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id.',';
                                                    $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                    $stock_out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,abs($history_quantity));
                                                }
                                                else
                                                {
                                                    $history_quantity = $out->available_stock;
                                                    $stock_out->parent_id_in .= $out->id.',';
                                                    $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                    $out->available_stock = 0;
                                                    $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,abs($history_quantity));
                                                }
                                                $out->save();
                                                $stock_out->save();
                                        }
                                    }
                                }
                        }
                        else
                        {
                          $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
                          if($find_stock->count() > 0)
                          {
                              foreach ($find_stock as $out) {

                                  if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
                                  {
                                          if($stock_out->available_stock >= abs($out->available_stock))
                                          {
                                              $history_quantity = $out->available_stock;
                                              $out->parent_id_in .= $stock_out->id.',';
                                              $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                              $out->available_stock = 0;
                                              $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$order_product,abs($history_quantity));
                                          }
                                          else
                                          {
                                              $history_quantity = $stock_out->available_stock;
                                              $out->parent_id_in .= $out->id.',';
                                              $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                              $stock_out->available_stock = 0;
                                              $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$order_product,abs($history_quantity));
                                          }
                                          $out->save();
                                          $stock_out->save();
                                  }
                              }
                          }
                        }
                    }


                    // $warehouse_products = WarehouseProduct::where('warehouse_id', @$supply_from_id)->where('product_id', $order_product->product_id)->first();
                    // $warehouse_products->current_quantity += round($quantity_shipped, 3);
                    // $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);

                    // $warehouse_products->save();

                    DB::beginTransaction();
                    try
                      {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentQuantity($order_product,$quantity_shipped,'add',$supply_from_id);
                        DB::commit();
                      }
                      catch(\Excepion $e)
                      {
                        DB::rollBack();
                      }
                }

                if ($key == 'trasnfer_expiration_date') {
                    $value = str_replace("/", "-", $request->trasnfer_expiration_date);
                    $value = date('Y-m-d', strtotime($value));
                    $order_product->$key = $value;
                    $order_product->save();
                } else {
                    $order_product->$key = $value;
                    $order_product->save();
                }
            }
            else
            {
                if ($key == 'trasnfer_qty_shipped') {
                    $decimal_places = ($order_product->product->units != null ? $order_product->product->units->decimal_places : 4);
                    $value = round($value,$decimal_places);
                }
                if ($key == 'trasnfer_expiration_date') {
                    $value = str_replace("/", "-", $request->trasnfer_expiration_date);
                    $value = date('Y-m-d', strtotime($value));
                    $order_product->$key = $value;
                    $order_product->save();
                } else {
                    $order_product->$key = $value;
                    $order_product->expiration_date = $value;
                    $order_product->save();
                }
            }

            if ($key != 'trasnfer_expiration_date') {
            //checking values of reserve stock
                foreach ($order_product->get_td_reserved as $res)
                {
                    $stock_out = StockManagementOut::find($res->stock_id);
                    if($stock_out)
                    {
                        if ($res->old_qty_shipped != null && $res->qty_shipped > $res->old_qty_shipped) {
                            $difference = $res->qty_shipped - $res->old_qty_shipped;
                            $stock_out->available_stock -= $difference;
                        }
                        else if ($res->old_qty_shipped != null && $res->qty_shipped < $res->old_qty_shipped){
                            $difference = $res->old_qty_shipped - $res->qty_shipped;
                            $stock_out->available_stock += $difference;
                        }
                        else if($res->old_qty_shipped == null && $res->qty_shipped != null){
                            $difference = $res->reserved_quantity - $res->qty_shipped;
                            $stock_out->available_stock += $difference;
                        }
                        $stock_out->save();
                    }
                }
            }
        }
        return response()->json(['success' => true]);
    }

    public function confirmTransferPickInstruction(Request $request)
    {
        DB::beginTransaction();
        $response = TransferDocumentHelper::confirmTransferPickInstruction($request);
        $result = json_decode($response->getContent());
        if ($result->success) {
            DB::commit();
        }
        else{
            DB::rollBack();
        }
        return $response;

        // DB::beginTransaction();
        // $redirect_response = '';
        // $confirm_from_draft = QuotationConfig::where('section','warehouse_management_page')->first();
        // if($confirm_from_draft)
        // {
        //     $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
        //     foreach ($globalaccessForWarehouse as $val)
        //     {
        //         if($val['slug'] === "has_warehouse_account")
        //         {
        //             $has_warehouse_account = $val['status'];
        //         }

        //     }
        // }
        // else
        // {
        //     $has_warehouse_account = '';
        // }
        // $purchaseOrder = PurchaseOrder::find($request->po_id);

        // if($purchaseOrder->status == 22)
        // {
        //     DB::rollBack();
        //     $redirect_response = 'dashboard';
        //     $errorMsg = "Pick instruction is already confirmed.";
        //     return response()->json(['success' => false, 'errorMsg' => $errorMsg, 'redirect' => $redirect_response]);
        // }

        // if($has_warehouse_account == 1)
        // {
        //     $query = PurchaseOrderDetail::with('PurchaseOrder')->where('po_id', $request->po_id)->whereNotNull('product_id')->orderBy('product_id','ASC')->get();
        //     foreach($query as $item){
        //         $item->trasnfer_qty_shipped = $item->quantity;
        //         $item->save();
        //     }
        // }

        // $po_detail_checks = PurchaseOrderDetail::with('PurchaseOrder', 'product')->where('po_id', $purchaseOrder->id)->where('is_billed', '=', 'Product')->get();
        // foreach ($po_detail_checks as $pod)
        // {
        //     if ($pod->trasnfer_qty_shipped === null)
        //     {
        //         DB::rollBack();
        //         $errorMsg = "Quantity Shipped Cannot Be Null, Please Enter The Quantity Shipped Of All Items.";
        //         return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
        //     }
        //     else
        //     {
        //         $pi_config = [];
        //         $pids=null;
        //         $pi_config = QuotationConfig::where('section', 'pick_instruction')->first();
        //         if ($pi_config != null)
        //         {
        //             $pi_config = unserialize($pi_config->print_prefrences);
        //         }

        //         $pids = PurchaseOrder::where('status', 21)->where('id','!=',$pod->po_id)->WhereNotNull('from_warehouse_id')->whereHas('PoWarehouse', function ($qq) use($pod) {
        //             $qq->where('from_warehouse_id', $pod->PurchaseOrder->from_warehouse_id);
        //         })->where('id','!=',$pod->po_id)->pluck('id')->toArray();

        //         $order_ids = Order::where('primary_status', 2)->whereHas('order_products', function ($q) use($pod) {
        //             $q->where('from_warehouse_id', $pod->PurchaseOrder->from_warehouse_id);
        //         })->pluck('id')->toArray();

        //         $warehouse_product = null;
        //         if ($pi_config['pi_confirming_condition'] == 2)
        //         {
        //             $warehouse_product = WarehouseProduct::where('product_id', $pod->product_id)->where('warehouse_id', $pod->PurchaseOrder->from_warehouse_id)->first();
        //             $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
        //             if($pod->quantity > $stock_qty)
        //             {
        //                 DB::rollBack();
        //                 return response()->json(['success'=> false, 'type' => 'stock', 'stock_qty' => 'less_than_order', 'product' => $pod->product->refrence_code]);
        //             }
        //             else
        //             {
        //                 if ($stock_qty <= 0)
        //                 {
        //                     if ($stock_qty < 0)
        //                     {
        //                         DB::rollBack();
        //                         return response()->json(['success'=> false, 'type' => 'stock', 'stock_qty' => 'less_than_zero', 'product' => $pod->product->refrence_code]);
        //                     }
        //                     else if ($stock_qty == 0)
        //                     {
        //                         DB::rollBack();
        //                         return response()->json(['success'=> false, 'type' => 'stock', 'stock_qty' => 'equals_to_zero', 'product' => $pod->product->refrence_code]);
        //                     }
        //                 }
        //             }
        //         }
        //         elseif ($pi_config['pi_confirming_condition'] == 3)
        //         {
        //             $warehouse_product = WarehouseProduct::where('product_id', $pod->product_id)->where('warehouse_id', $pod->PurchaseOrder->from_warehouse_id)->first();
        //             $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
        //             $order_rsv_qty = OrderProduct::whereIn('order_id', $order_ids)->where('product_id', $pod->product_id)->sum('quantity');
        //             $pick_rsv_qty = PurchaseOrderDetail::whereIn('po_id', $pids)->where('product_id', $pod->product_id)->sum('quantity');
        //             $available_qty = $stock_qty -($order_rsv_qty + $pick_rsv_qty);
        //             if($pod->quantity > $available_qty)
        //             {
        //                 DB::rollBack();
        //                 return response()->json(['success'=> false, 'type' => 'available', 'available_qty' => 'less_than_order', 'product' => $pod->product->refrence_code]);
        //             }
        //             else
        //             {
        //                 if ($available_qty <= 0)
        //                 {
        //                     if ($available_qty < 0)
        //                     {
        //                         DB::rollBack();
        //                         return response()->json(['success'=> false, 'type' => 'available', 'available_qty' => 'less_than_zero', 'product' => $pod->product->refrence_code]);
        //                     }
        //                     else if ($available_qty == 0)
        //                     {
        //                         DB::rollBack();
        //                         return response()->json(['success'=> false, 'type' => 'available', 'available_qty' => 'equals_to_zero', 'product' => $pod->product->refrence_code]);
        //                     }

        //                 }
        //             }
        //         }
        //     }
        // }

        // $po_detail = PurchaseOrderDetail::with('PurchaseOrder', 'product.sellingUnits', 'get_td_reserved', 'order_product.get_order')->where('po_id', $purchaseOrder->id)->where('is_billed', '=', 'Product')->get();
        // foreach ($po_detail as $order_product) {
        //     $supply_from_id = $order_product->PurchaseOrder->from_warehouse_id;

        //         $decimal_places = $order_product->product->sellingUnits->decimal_places;
        //         if($decimal_places == 0)
        //         {
        //             $quantity_inv   = round($order_product->trasnfer_qty_shipped,0);
        //         }
        //         elseif($decimal_places == 1)
        //         {
        //             $quantity_inv   = round($order_product->trasnfer_qty_shipped,1);
        //         }
        //         elseif($decimal_places == 2)
        //         {
        //             $quantity_inv   = round($order_product->trasnfer_qty_shipped,2);
        //         }
        //         elseif($decimal_places == 3)
        //         {
        //             $quantity_inv   = round($order_product->trasnfer_qty_shipped,3);
        //         }
        //         else
        //         {
        //             $quantity_inv   = round($order_product->trasnfer_qty_shipped,4);
        //         }
        //     if ($quantity_inv !== null) {
        //         // if ($order_product->trasnfer_expiration_date != null) {
        //         //     $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->where('expiration_date', $order_product->trasnfer_expiration_date)->whereNotNull('expiration_date')->first();
        //         //     if ($stock == null) {
        //         //         $stock = new StockManagementIn;
        //         //         $stock->title = 'Adjustment';
        //         //         $stock->product_id = $order_product->product_id;
        //         //         $stock->created_by = Auth::user()->id;
        //         //         $stock->warehouse_id = @$supply_from_id;
        //         //         $stock->expiration_date = $order_product->trasnfer_expiration_date;
        //         //         $stock->save();
        //         //     }
        //         //     if ($stock != null) {
        //         //         $stock_out = new StockManagementOut;
        //         //         $stock_out->title = 'TD';
        //         //         $stock_out->smi_id = $stock->id;
        //         //         $stock_out->p_o_d_id = $order_product->id;
        //         //         $stock_out->product_id = $order_product->product_id;
        //         //         $stock_out->quantity_out = $quantity_inv != null ? '-' .$quantity_inv : 0;
        //         //         if($order_product->get_td_reserved->count() > 0)
        //         //         {
        //         //             $stock_out->available_stock = 0;
        //         //         }
        //         //         else
        //         //         {
        //         //             $stock_out->available_stock = $quantity_inv != null ? '-' . $quantity_inv : 0;
        //         //         }
        //         //         $stock_out->created_by = Auth::user()->id;
        //         //         $stock_out->warehouse_id = @$supply_from_id;
        //         //         $stock_out->save();

        //         //         if($order_product->get_td_reserved->count() > 0)
        //         //         {
        //         //             foreach ($order_product->get_td_reserved as $prod) {

        //         //                 $stock_out->parent_id_in .= $prod->stock_id.',';
        //         //                 $stock_out->save();
        //         //             }
        //         //         }
        //         //         else
        //         //         {

        //         //             //To find from which stock the order will be deducted
        //         //             $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
        //         //             if($find_stock->count() > 0)
        //         //             {
        //         //                 foreach ($find_stock as $out)
        //         //                 {

        //         //                     if(abs($stock_out->available_stock) > 0)
        //         //                     {
        //         //                             if($out->available_stock >= abs($stock_out->available_stock))
        //         //                             {
        //         //                                 $history_quantity = $stock_out->available_stock;
        //         //                                 $stock_out->parent_id_in .= $out->id.',';
        //         //                                 $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
        //         //                                 $stock_out->available_stock = 0;
        //         //                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                             }
        //         //                             else
        //         //                             {
        //         //                                 $history_quantity = $out->available_stock;
        //         //                                 $stock_out->parent_id_in .= $out->id.',';
        //         //                                 $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
        //         //                                 $out->available_stock = 0;
        //         //                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                             }
        //         //                             $out->save();
        //         //                             $stock_out->save();
        //         //                     }
        //         //                 }
        //         //             }
        //         //         }
        //         //     }
        //         // } else {
        //         //     $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
        //         //     $shipped = $quantity_inv;
        //         //     foreach ($stock as $st) {
        //         //         $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
        //         //         $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
        //         //         $balance = ($stock_out_in) + ($stock_out_out);
        //         //         $balance = round($balance, 3);
        //         //         if ($balance > 0) {
        //         //             $inStock = $balance - $shipped;
        //         //             if ($inStock >= 0) {
        //         //                 $stock_out = new StockManagementOut;
        //         //                 $stock_out->title = 'TD';
        //         //                 $stock_out->smi_id = $st->id;
        //         //                 $stock_out->p_o_d_id = $order_product->id;
        //         //                 $stock_out->product_id = $order_product->product_id;
        //         //                 $stock_out->quantity_out = $shipped != null ? -$shipped : 0;
        //         //                 if($order_product->get_td_reserved->count() > 0)
        //         //                 {
        //         //                     $stock_out->available_stock = 0;
        //         //                 }
        //         //                 else
        //         //                 {
        //         //                     $stock_out->available_stock = $shipped != null ? -$shipped : 0;
        //         //                 }
        //         //                 $stock_out->created_by = Auth::user()->id;
        //         //                 $stock_out->warehouse_id = @$supply_from_id;
        //         //                 $stock_out->save();

        //         //                 if($order_product->get_td_reserved->count() > 0)
        //         //                 {
        //         //                     foreach ($order_product->get_td_reserved as $prod) {

        //         //                         $stock_out->parent_id_in .= $prod->stock_id.',';
        //         //                         $stock_out->save();
        //         //                     }
        //         //                 }
        //         //                 else
        //         //                 {
        //         //                     //To find from which stock the order will be deducted
        //         //                     $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
        //         //                     if($find_stock->count() > 0)
        //         //                     {
        //         //                         foreach ($find_stock as $out)
        //         //                         {

        //         //                         if($shipped > 0)
        //         //                             {
        //         //                                     if($out->available_stock >= $shipped)
        //         //                                     {
        //         //                                         $history_quantity = $stock_out->available_stock;
        //         //                                         $stock_out->parent_id_in .= $out->id.',';
        //         //                                         $out->available_stock = $out->available_stock - $shipped;
        //         //                                         $stock_out->available_stock = 0;
        //         //                                         $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                                     }
        //         //                                     else
        //         //                                     {
        //         //                                         $history_quantity = $out->available_stock;
        //         //                                         $stock_out->parent_id_in .= $out->id.',';
        //         //                                         $stock_out->available_stock = $out->available_stock - $shipped;
        //         //                                         $out->available_stock = 0;
        //         //                                         $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                                     }
        //         //                                     $out->save();
        //         //                                     $stock_out->save();
        //         //                                     $shipped = abs($stock_out->available_stock);
        //         //                             }
        //         //                         }
        //         //                     }
        //         //                 }
        //         //                 $shipped = 0;
        //         //                 break;
        //         //             } else {
        //         //                 $stock_out = new StockManagementOut;
        //         //                 $stock_out->title = 'TD';
        //         //                 $stock_out->smi_id = $st->id;
        //         //                 $stock_out->p_o_d_id = $order_product->id;
        //         //                 $stock_out->product_id = $order_product->product_id;
        //         //                 $stock_out->quantity_out = -$balance;
        //         //                 if($order_product->get_td_reserved->count() > 0)
        //         //                 {
        //         //                     $stock_out->available_stock = 0;
        //         //                 }
        //         //                 else
        //         //                 {
        //         //                     $stock_out->available_stock = -$balance;
        //         //                 }
        //         //                 $stock_out->created_by = Auth::user()->id;
        //         //                 $stock_out->warehouse_id = @$supply_from_id;
        //         //                 $stock_out->save();

        //         //                 if($order_product->get_td_reserved->count() > 0)
        //         //                 {
        //         //                     foreach ($order_product->get_td_reserved as $prod) {

        //         //                         $stock_out->parent_id_in .= $prod->stock_id.',';
        //         //                         $stock_out->save();
        //         //                     }
        //         //                 }
        //         //                 else
        //         //                 {
        //         //                      //To find from which stock the order will be deducted
        //         //                     $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();

        //         //                     // $find_available_stock = $find_stock->sum('available_stock');
        //         //                     if($find_stock->count() > 0)
        //         //                     {
        //         //                         foreach ($find_stock as $out)
        //         //                         {

        //         //                             if($balance > 0)
        //         //                             {
        //         //                                     if($out->available_stock >= $balance)
        //         //                                     {
        //         //                                         $history_quantity = $stock_out->available_stock;
        //         //                                         $stock_out->parent_id_in .= $out->id.',';
        //         //                                         $out->available_stock = $out->available_stock - $balance;
        //         //                                         $stock_out->available_stock = 0;
        //         //                                         $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                                     }
        //         //                                     else
        //         //                                     {
        //         //                                         $history_quantity = $out->available_stock;
        //         //                                         $stock_out->parent_id_in .= $out->id.',';
        //         //                                         $stock_out->available_stock = $out->available_stock - $balance;
        //         //                                         $out->available_stock = 0;
        //         //                                         $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                                     }
        //         //                                     $out->save();
        //         //                                     $stock_out->save();
        //         //                                     // $shipped = abs($stock_out->available_stock);
        //         //                                     $balance = abs($stock_out->available_stock);
        //         //                             }
        //         //                         }
        //         //                         // $shipped = abs($stock_out->available_stock);

        //         //                         // $stock_out->available_stock = 0;
        //         //                         // $stock_out->save();
        //         //                     }
        //         //                 }
        //         //                 // else
        //         //                 // {
        //         //                 //     $shipped = abs($inStock);
        //         //                 // }
        //         //                 $shipped = abs($inStock);
        //         //             }
        //         //         }
        //         //     }
        //         //     if ($shipped != 0) {
        //         //         $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->whereNull('expiration_date')->first();
        //         //         if ($stock == null) {
        //         //             $stock = new StockManagementIn;
        //         //             $stock->title = 'Adjustment';
        //         //             $stock->product_id = $order_product->product_id;
        //         //             $stock->created_by = Auth::user()->id;
        //         //             $stock->warehouse_id = @$supply_from_id;
        //         //             $stock->expiration_date = $order_product->trasnfer_expiration_date;
        //         //             $stock->save();
        //         //         }

        //         //         //To find from which stock the order will be deducted
        //         //         $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();

        //         //         $stock_out = new StockManagementOut;
        //         //         $stock_out->title = 'TD';
        //         //         $stock_out->smi_id = $stock->id;
        //         //         $stock_out->p_o_d_id = $order_product->id;
        //         //         $stock_out->product_id = $order_product->product_id;
        //         //         $stock_out->quantity_out = $shipped != null ? -$shipped : 0;
        //         //         if($order_product->get_td_reserved->count() > 0)
        //         //         {
        //         //             $stock_out->available_stock = 0;
        //         //         }
        //         //         else
        //         //         {
        //         //             $stock_out->available_stock = $shipped != null ? -$shipped : 0;
        //         //         }
        //         //         $stock_out->created_by = Auth::user()->id;
        //         //         $stock_out->warehouse_id = @$supply_from_id;
        //         //         $stock_out->save();

        //         //         if($order_product->get_td_reserved->count() > 0)
        //         //         {
        //         //             foreach ($order_product->get_td_reserved as $prod) {

        //         //                 $stock_out->parent_id_in .= $prod->stock_id.',';
        //         //                 $stock_out->save();
        //         //             }
        //         //         }
        //         //         else
        //         //         {
        //         //             if($find_stock->count() > 0)
        //         //             {
        //         //                 foreach ($find_stock as $out) {

        //         //                     if(abs($stock_out->available_stock) > 0)
        //         //                     {
        //         //                         if($out->available_stock >= abs($stock_out->available_stock))
        //         //                         {
        //         //                             $history_quantity = $stock_out->available_stock;
        //         //                             $stock_out->parent_id_in .= $out->id.',';
        //         //                             $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
        //         //                             $stock_out->available_stock = 0;
        //         //                             $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                         }
        //         //                         else
        //         //                         {
        //         //                             $history_quantity = $out->available_stock;
        //         //                             $stock_out->parent_id_in .= $out->id.',';
        //         //                             $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
        //         //                             $out->available_stock = 0;
        //         //                             $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$order_product,round(abs($history_quantity),4));
        //         //                         }
        //         //                         $out->save();
        //         //                         $stock_out->save();
        //         //                     }
        //         //             }
        //         //             }
        //         //             else
        //         //             {
        //         //                 $stock_out->available_stock = '-'.@$shipped;
        //         //                 $stock_out->save();
        //         //             }
        //         //         }
        //         //     }
        //         // }

        //         // $warehouse_products = WarehouseProduct::where('warehouse_id', @$supply_from_id)->where('product_id', $order_product->product_id)->first();
        //         // $warehouse_products->current_quantity -= $quantity_inv;
        //         // $warehouse_products->reserved_quantity = $warehouse_products->reserved_quantity - $order_product->quantity;
        //         // $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);

        //         // $warehouse_products->save();

        //         if ($order_product->trasnfer_expiration_date != null) {
        //             $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->where('expiration_date', $order_product->trasnfer_expiration_date)->whereNotNull('expiration_date')->first();
        //             if ($stock == null) {
        //                 $stock = new StockManagementIn;
        //                 $stock->title = 'Adjustment';
        //                 $stock->product_id = $order_product->product_id;
        //                 $stock->created_by = Auth::user()->id;
        //                 $stock->warehouse_id = @$supply_from_id;
        //                 $stock->expiration_date = $order_product->trasnfer_expiration_date;
        //                 $stock->save();
        //             }
        //             if ($stock != null) {
        //                 if($order_product->get_td_reserved->count() > 0)
        //                 {
        //                     foreach ($order_product->get_td_reserved as $prod) {
        //                         if ($prod->reserved_quantity != null) {
        //                             $stock_out = new StockManagementOut;
        //                             $stock_out->title = 'TD';
        //                             $stock_out->smi_id = $stock->id;
        //                             $stock_out->p_o_d_id = $order_product->id;
        //                             $stock_out->product_id = $order_product->product_id;
        //                             $stock_out->quantity_out = -$prod->qty_shipped;
        //                             $stock_out->available_stock = $prod->qty_shipped;
        //                             $stock_out->created_by = Auth::user()->id;
        //                             $stock_out->warehouse_id = @$supply_from_id;
        //                             $stock_out->parent_id_in .= $prod->stock_id.',';
        //                             $stock_out->save();
        //                         }
        //                     }
        //                 }
        //             }
        //         } else {
        //             foreach ($order_product->get_td_reserved as $prod) {
        //                 if ($prod->reserved_quantity != null) {
        //                     $stock_m_out = StockManagementOut::find($prod->stock_id);
        //                     if ($stock_m_out) {
        //                         $stock = StockManagementIn::find($stock_m_out->smi_id);
        //                         $stock_out = new StockManagementOut;
        //                         $stock_out->title = 'TD';
        //                         $stock_out->smi_id = $stock->id;
        //                         $stock_out->p_o_d_id = $order_product->id;
        //                         $stock_out->product_id = $order_product->product_id;
        //                         $stock_out->quantity_out = -$prod->qty_shipped;
        //                         $stock_out->available_stock = $prod->qty_shipped;
        //                         $stock_out->created_by = Auth::user()->id;
        //                         $stock_out->warehouse_id = @$supply_from_id;
        //                         $stock_out->parent_id_in .= $prod->stock_id.',';
        //                         $stock_out->save();
        //                     }
        //                     else{
        //                         $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', @$supply_from_id)->whereNull('expiration_date')->first();
        //                         if ($stock == null) {
        //                             $stock = new StockManagementIn;
        //                             $stock->title = 'Adjustment';
        //                             $stock->product_id = $order_product->product_id;
        //                             $stock->created_by = Auth::user()->id;
        //                             $stock->warehouse_id = @$supply_from_id;
        //                             $stock->expiration_date = $order_product->trasnfer_expiration_date;
        //                             $stock->save();
        //                         }

        //                         $stock_out = new StockManagementOut;
        //                         $stock_out->title = 'TD';
        //                         $stock_out->smi_id = $stock->id;
        //                         $stock_out->p_o_d_id = $order_product->id;
        //                         $stock_out->product_id = $order_product->product_id;
        //                         $stock_out->quantity_out = -$prod->qty_shipped;
        //                         $stock_out->available_stock = $prod->qty_shipped;
        //                         $stock_out->created_by = Auth::user()->id;
        //                         $stock_out->warehouse_id = @$supply_from_id;
        //                         $stock_out->save();
        //                     }
        //                 }
        //             }
        //         }

        //         // $warehouse_product = WarehouseProduct::where('warehouse_id', @$supply_from_id)->where('product_id', $order_product->product_id)->first();
        //         // $warehouse_product->current_quantity -= $quantity_inv;
        //         // $warehouse_product->reserved_quantity = $warehouse_product->reserved_quantity - $order_product->quantity;
        //         // $warehouse_product->available_quantity = $warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity);

        //         // $warehouse_product->save();

        //         $reserved_qty = $order_product->get_td_reserved()->whereNotNull('stock_id')->sum('qty_shipped');
        //         $reserved_qty = $reserved_qty != null ? $reserved_qty : 0;

        //         $new_his = new QuantityReservedHistory;
        //         // $re      = $new_his->updateTDCurrentReservedQuantity($order_product->PurchaseOrder,$order_product,$quantity_inv,'TD Confirmed By Warehouse Reserved Subtracted ','subtract',null);
        //         $re      = $new_his->updateTDCurrentReservedQuantity($order_product->PurchaseOrder,$order_product,$reserved_qty,'TD Confirmed By Warehouse Reserved Subtracted ','subtract',null);
        //     }

        //     if ($order_product->order_product_id != null) {
        //         $order_product = $order_product->order_product;
        //         $order = $order_product->get_order;
        //         if ($order->primary_status !== 3 && $order->primary_status !== 17) {
        //             $order_product->status = 9;
        //             $order_product->save();

        //             $order_products_status_count = OrderProduct::where('order_id', $order_product->order_id)->where('is_billed', '=', 'Product')->where('status', '!=', 9)->count();
        //             if ($order_products_status_count == 0) {
        //                 $order->status = 9;
        //                 $order->save();
        //                 $order_history = new OrderStatusHistory;
        //                 $order_history->user_id = Auth::user()->id;
        //                 $order_history->order_id = @$order->id;
        //                 $order_history->status = 'DI(Purchasing)';
        //                 $order_history->new_status = 'DI(Importing)';
        //                 $order_history->save();
        //             }
        //         }
        //     }
        // }

        // $purchaseOrder->status = 22;
        // $purchaseOrder->save();

        // $page_status = Status::select('title')->whereIn('id', [21, 22])->pluck('title')->toArray();

        // $poStatusHistory = new PurchaseOrderStatusHistory;
        // $poStatusHistory->user_id = Auth::user()->id;
        // $poStatusHistory->po_id = $purchaseOrder->id;
        // $poStatusHistory->status = $page_status[0];
        // $poStatusHistory->new_status = $page_status[1];
        // $poStatusHistory->save();
        // session(['td_status' => 22]);
        // DB::commit();
        // return response()->json(['success' => true]);
    }

    public function confirmPickInstruction(Request $request)
    {
        // $job_status = ExportStatus::where('type', 'pick_instruction_job')->where('user_id', Auth::user()->id)->first();
        // if ($job_status == null)
        // {
        //     $job_status = new ExportStatus();
        //     $job_status->type = 'pick_instruction_job';
        //     $job_status->user_id = Auth::user()->id;
        // }
        // $job_status->status = 1;
        // $job_status->exception = null;
        // $job_status->error_msgs = null;
        // $job_status->save();

        // PickInstructionJob::dispatch($request->all(), Auth::user());
        // return response()->json(['success' => true]);
        $error_msg = '';
        $order = Order::where('id', $request->order_id)->first();
        if ($order->is_processing == 1) {
            return response()->json(['already_confirmed' => true]);
        }
        $order->is_processing = 1;
        $order->save();
        DB::beginTransaction();
        try {
            $stock_cost = [];
            if ($order->primary_status == 3) {
                DB::rollBack();
                $order->is_processing = 0;
                $order->save();
                return response()->json(['already_confirmed' => true]);
            }
            $order->primary_status = 3;
            $order->save();
            $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Product')->get();
            $order_products_billed = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Billed')->get();

            $rec_date_ot = Carbon::now();
            $rec_date =  date('Y-m-d', strtotime($rec_date_ot));
            if ($request->page_info == "draft") {
                if ($order_products->count() > 0) {
                    // Use the update method to update the records in a single query
                    OrderProduct::whereIn('id', $order_products->pluck('id')->toArray())
                        ->update([
                            'pcs_shipped' => DB::raw('number_of_pieces'),
                            'qty_shipped' => DB::raw('quantity')
                        ]);
                    // foreach ($order_products as $order_product) {
                    //     $order_product->pcs_shipped = $order_product->number_of_pieces;
                    //     $order_product->qty_shipped = $order_product->quantity;
                    //     $order_product->save();
                    // }
                }
            }

            $pi_config = [];
            $pi_config = QuotationConfig::where('section', 'pick_instruction')->first();
            if ($pi_config != null) {
                $pi_config = unserialize($pi_config->print_prefrences);
            }
            foreach ($order_products as $order_product) {
                if ($order_product->is_retail == 'qty') {
                    if ($order_product->qty_shipped == null) {
                        DB::rollBack();
                        $order->is_processing = 0;
                        $order->save();
                        return response()->json(['qty_shipped' => 'is_null', 'product' => $order_product->product->refrence_code]);
                    } else {
                        if ($pi_config['pi_confirming_condition'] == 2) {
                            $warehouse_product = WarehouseProduct::where('product_id', $order_product->product_id)->where('warehouse_id', $order_product->user_warehouse_id)->first();
                            $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
                            if (($stock_qty < $order_product->quantity) && ($order_product->qty_shipped !== '0')) {
                                DB::rollBack();
                                $order->is_processing = 0;
                                $order->save();
                                return response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_order', 'product' => $order_product->product->refrence_code]);
                            } else {
                                if ($stock_qty <= 0  && ($order_product->qty_shipped !== '0')) {
                                    if ($stock_qty < 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        return response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'less_than_zero', 'product' => $order_product->product->refrence_code]);
                                    } else if ($stock_qty == 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        return response()->json(['success' => false, 'type' => 'stock', 'stock_qty' => 'equals_to_zero', 'product' => $order_product->product->refrence_code]);
                                    }
                                }
                            }
                        } elseif ($pi_config['pi_confirming_condition'] == 3) {
                            $pids = PurchaseOrder::where('status', 21)->WhereNotNull('from_warehouse_id')->whereHas('PoWarehouse', function ($qq) use ($order_product) {
                            $qq->where('from_warehouse_id', $order_product->user_warehouse_id);
                            })->pluck('id')->toArray();

                            $order_ids = Order::where('primary_status', 2)->where('id', '!=', $order_product->order_id)->whereHas('order_products', function ($q) use ($order_product) {
                                $q->where('from_warehouse_id', $order_product->user_warehouse_id);
                            })->pluck('id')->toArray();

                            $warehouse_product = WarehouseProduct::where('product_id', $order_product->product_id)->where('warehouse_id', $order_product->user_warehouse_id)->first();
                            $stock_qty = (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity : ' 0';
                            $order_rsv_qty = OrderProduct::whereIn('order_id', $order_ids)->where('product_id', $order_product->product_id)->sum('quantity');
                            $pick_rsv_qty = PurchaseOrderDetail::whereIn('po_id', $pids)->where('product_id', $order_product->product_id)->sum('quantity');
                            $available_qty = $stock_qty - ($order_rsv_qty + $pick_rsv_qty);
                            if ($order_product->quantity > $available_qty) {
                                DB::rollBack();
                                $order->is_processing = 0;
                                $order->save();
                                return response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_order', 'product' => $order_product->product->refrence_code]);
                            } else {
                                if ($available_qty <= 0) {
                                    if ($available_qty < 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        return response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'less_than_zero', 'product' => $order_product->product->refrence_code]);
                                    } else if ($available_qty == 0) {
                                        DB::rollBack();
                                        $order->is_processing = 0;
                                        $order->save();
                                        return response()->json(['success' => false, 'type' => 'available', 'available_qty' => 'equals_to_zero', 'product' => $order_product->product->refrence_code]);
                                    }
                                }
                            }
                        }
                    }
                } else if ($order_product->is_retail == 'pieces') {
                    if ($order_product->pcs_shipped == null || $order_product->qty_shipped == null) {
                        DB::rollBack();
                        $order->is_processing = 0;
                        $order->save();
                        return response()->json(['pcs_shipped' => 'is_null', 'product' => $order_product->product->refrence_code]);
                    }
                }
            }
            $status_history = new OrderStatusHistory;
            $status_history->user_id = Auth::user()->id;
            $status_history->order_id = $order->id;
            if ($order->ecommerce_order == 1) {
                $getStatus = Status::find($order->status);
                if ($getStatus) {
                    $status_history->status = $getStatus->title;
                }
            } else {
                $status_history->status = 'DI(Waiting To Pick)';
            }
            $status_history->new_status = 'Invoice';
            $status_history->save();

            $order_total = 0;
            foreach ($order_products as $order_product) {
                $warehouse_id = $order_product->user_warehouse_id != null ? $order_product->user_warehouse_id : ($order_product->get_order->user_created != null ? $order_product->get_order->user_created->warehouse_id : Auth::user()->warehouse_id);
                $order_product->status = 11;
                $order_product->save();
                $item_unit_price = number_format($order_product->unit_price, 2, '.', '');
                if ($order_product->qty_shipped !== null) {
                    $total_cost = null;
                    $total_count = null;
                    if ($order_product->expiration_date != null) {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                        $shipped = $order_product->qty_shipped;
                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title = 'Adjustment';
                            $stock->product_id = $order_product->product_id;
                            $stock->created_by = Auth::user()->id;
                            $stock->warehouse_id = $warehouse_id;
                            $stock->expiration_date = $order_product->expiration_date;
                            $stock->save();
                        }
                        if ($stock != null) {
                            $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, '-'.$order_product->qty_shipped, $warehouse_id, null, true);

                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted('-'.$order_product->qty_shipped, $stock, $stock_out, $order_product);

                            // $stock_out = new StockManagementOut;
                            // $stock_out->smi_id = $stock->id;
                            // $stock_out->order_id = $order_product->order_id;
                            // $stock_out->order_product_id = $order_product->id;
                            // $stock_out->product_id = $order_product->product_id;
                            // $stock_out->quantity_out = $order_product->qty_shipped != null ? '-' . $order_product->qty_shipped : 0;
                            // $stock_out->created_by = Auth::user()->id;
                            // $stock_out->warehouse_id = $warehouse_id;
                            // $stock_out->available_stock = $order_product->qty_shipped != null ? '-' . $order_product->qty_shipped : 0;
                            // $stock_out->save();

                            // //To find from which stock the order will be deducted
                            // $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                            // if ($find_stock->count() > 0) {
                            //     foreach ($find_stock as $out) {

                            //         if (abs($stock_out->available_stock) > 0) {
                            //             if ($out->available_stock >= abs($stock_out->available_stock)) {
                            //                 $history_quantity = $stock_out->available_stock;
                            //                 $stock_out->parent_id_in .= $out->id . ',';
                            //                 $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                            //                 $stock_out->available_stock = 0;

                            //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                            //             } else {
                            //                 $history_quantity = $out->available_stock;
                            //                 $stock_out->parent_id_in .= $out->id . ',';
                            //                 $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                            //                 $out->available_stock = 0;

                            //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                            //             }
                            //             $out->save();
                            //             $stock_out->save();
                            //         }
                            //     }
                            // }
                        }
                    } else {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                        $shipped = $order_product->qty_shipped;
                        foreach ($stock as $st) {
                            $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                            $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                            $balance = ($stock_out_in) + ($stock_out_out);
                            $balance = round($balance, 3);
                            if ($balance > 0) {
                                $inStock = $balance - $shipped;
                                if ($inStock >= 0) {
                                    $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, '-'.$shipped, $warehouse_id, null, true);
                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted('-'.$shipped, $st, $stock_out, $order_product);

                                    // $stock_out = new StockManagementOut;
                                    // $stock_out->smi_id = $st->id;
                                    // $stock_out->order_id = $order_product->order_id;
                                    // $stock_out->order_product_id = $order_product->id;
                                    // $stock_out->product_id = $order_product->product_id;
                                    // $stock_out->quantity_out = $shipped != null ? '-' . $shipped : 0;
                                    // $stock_out->available_stock = '-' . $shipped;
                                    // $stock_out->created_by = Auth::user()->id;
                                    // $stock_out->warehouse_id = $warehouse_id;
                                    // $stock_out->save();


                                    // //To find from which stock the order will be deducted
                                    // $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                                    // if ($find_stock->count() > 0) {
                                    //     foreach ($find_stock as $out) {

                                    //         if ($shipped > 0) {
                                    //             if ($out->available_stock >= $shipped) {
                                    //                 $stock_out->parent_id_in .= $out->id . ',';
                                    //                 $out->available_stock = $out->available_stock - $shipped;
                                    //                 $stock_out->available_stock = 0;
                                    //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($shipped));
                                    //             } else {
                                    //                 $history_quantity = $out->available_stock;
                                    //                 $stock_out->parent_id_in .= $out->id . ',';
                                    //                 $stock_out->available_stock = $out->available_stock - $shipped;
                                    //                 $out->available_stock = 0;
                                    //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                    //             }
                                    //             $out->save();
                                    //             $stock_out->save();
                                    //             $shipped = abs($stock_out->available_stock);
                                    //         }
                                    //     }
                                    // }
                                    $shipped = 0;
                                    break;
                                } else {
                                    $stock_out = StockManagementOut::addManualAdjustment($st, $order_product, '-'.$balance, $warehouse_id, null, true);
                                    $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted('-'.$balance, $st, $stock_out, $order_product);

                                    // $stock_out = new StockManagementOut;
                                    // $stock_out->smi_id = $st->id;
                                    // $stock_out->order_id = $order_product->order_id;
                                    // $stock_out->order_product_id = $order_product->id;
                                    // $stock_out->product_id = $order_product->product_id;
                                    // $stock_out->quantity_out = -$balance;
                                    // $stock_out->available_stock = -$balance;
                                    // // $stock_out->available_stock = $inStock;
                                    // $stock_out->created_by = Auth::user()->id;
                                    // $stock_out->warehouse_id = $warehouse_id;
                                    // $stock_out->save();

                                    // //To find from which stock the order will be deducted
                                    // $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                                    // $find_available_stock = $find_stock->sum('available_stock');
                                    // if ($find_stock->count() > 0) {
                                    //     foreach ($find_stock as $out) {

                                    //         if ($balance > 0) {
                                    //             if ($out->available_stock >= $balance) {
                                    //                 $stock_out->parent_id_in .= $out->id . ',';
                                    //                 $out->available_stock = $out->available_stock - $balance;
                                    //                 $stock_out->available_stock = 0;

                                    //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($balance));
                                    //             } else {
                                    //                 $history_quantity = $out->available_stock;
                                    //                 $stock_out->parent_id_in .= $out->id . ',';
                                    //                 $stock_out->available_stock = $out->available_stock - $balance;
                                    //                 $out->available_stock = 0;
                                    //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                                    //             }
                                    //             $out->save();
                                    //             $stock_out->save();

                                    //             $balance = abs($stock_out->available_stock);
                                    //         }
                                    //     }
                                    // }
                                    $shipped = abs($inStock);
                                }
                            }
                        }
                        if ($shipped != 0) {
                            $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNull('expiration_date')->first();
                            if ($stock == null) {
                                $stock = new StockManagementIn;
                                $stock->title = 'Adjustment';
                                $stock->product_id = $order_product->product_id;
                                $stock->created_by = Auth::user()->id;
                                $stock->warehouse_id = $warehouse_id;
                                $stock->expiration_date = $order_product->expiration_date;
                                $stock->save();
                            }

                            $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, '-'.$shipped, $warehouse_id, null, true);
                            $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted('-'.$shipped, $stock, $stock_out, $order_product);

                            // //To find from which stock the order will be deducted
                            // $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                            // $stock_out = new StockManagementOut;
                            // $stock_out->smi_id = $stock->id;
                            // $stock_out->order_id = $order_product->order_id;
                            // $stock_out->order_product_id = $order_product->id;
                            // $stock_out->product_id = $order_product->product_id;
                            // $stock_out->quantity_out = $shipped != null ? '-' . $shipped : 0;
                            // $stock_out->available_stock = $shipped != null ? '-' . $shipped : 0;
                            // $stock_out->created_by = Auth::user()->id;
                            // $stock_out->warehouse_id = $warehouse_id;
                            // $stock_out->save();

                            // if ($find_stock->count() > 0) {
                            //     foreach ($find_stock as $out) {

                            //         if ($shipped > 0) {
                            //             if ($out->available_stock >= $shipped) {
                            //                 $stock_out->parent_id_in .= $out->id . ',';
                            //                 $out->available_stock = $out->available_stock - $shipped;
                            //                 $stock_out->available_stock = 0;
                            //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($shipped));
                            //             } else {
                            //                 $history_quantity = $out->available_stock;
                            //                 $stock_out->parent_id_in .= $out->id . ',';
                            //                 $stock_out->available_stock = $out->available_stock - $shipped;
                            //                 $out->available_stock = 0;
                            //                 $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, round(abs($history_quantity), 4));
                            //             }
                            //             $out->save();
                            //             $stock_out->save();
                            //             $shipped = abs($stock_out->available_stock);
                            //         }
                            //     }
                            // } else {
                            //     $stock_out->available_stock = '-' . @$shipped;
                            //     $stock_out->save();
                            // }
                        }
                    }

                    if ($order_product->get_order->ecommerce_order == 1) {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentReservedQuantity($order_product, 'Order Confirmed By Warehouse Ecom Reserved Subtracted ', 'subtract', Auth::user()->id);
                    } else {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentReservedQuantity($order_product, 'Order Confirmed By Warehouse Reserved Subtracted ', 'subtract', Auth::user()->id);
                    }
                } else if ($order_product->qty_shipped == 0) {
                    $msg = $order_product->get_order->ecommerce_order == 1 ? 'Ecom' : '';
                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateCurrentReservedQuantity($order_product, 'Order Confirmed By Warehouse ' . $msg . ' Reserved Subtracted ', 'subtract', Auth::user()->id);
                }

                
                $calcu = DraftQuotationHelper::orderCalculation($order_product, $order);
                $order_total += @$order_product->total_price_with_vat;

                $order_history = new OrderHistory;
                $order_history->user_id = Auth::user()->id;
                $order_history->reference_number = $order_product->product->refrence_code;
                $order_history->column_name = "Qty Sent";
                $order_history->old_value = null;
                $order_history->new_value = $order_product->qty_shipped;
                $order_history->order_id = $order_product->order_id;
                $order_history->save();

                if ($order_product->pcs_shipped != null) {
                    $order_history = new OrderHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->reference_number = $order_product->product->refrence_code;
                    $order_history->column_name = "Pieces Sent";
                    $order_history->old_value = null;
                    $order_history->new_value = $order_product->pcs_shipped;
                    $order_history->order_id = $order_product->order_id;
                    $order_history->save();
                }
            }

            foreach ($order_products_billed as $order_product) {
                $calcu = DraftQuotationHelper::orderCalculation($order_product, $order);
                $order_total += @$order_product->total_price_with_vat;
            }

            $order->primary_status = 3;
            if ($order->ecommerce_order == 1) {
                // This will directly goes to paid status
                $order->status = 24;
                $order_products = OrderProduct::where('order_id', $order->id)->update([
                    'status' => 24
                ]);
            } else {
                $order->status = 11;
            }

            $order->total_amount = @$order_total;
            $order->converted_to_invoice_on = Carbon::now();
            // $order->target_ship_date = Carbon::now()->format('Y-m-d');

            $inv_status = Status::where('id', 3)->first();
            $counter_formula = $inv_status->counter_formula;
            $counter_formula = explode('-', $counter_formula);
            $counter_length = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

            $date = Carbon::now();
            $date = $date->format($counter_formula[0]);
            $company_prefix = @Auth::user()->getCompany->prefix;
            if ($order->ecommerce_order == 1) {
                $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
                $quotation_config =  unserialize($quotation_qry->print_prefrences);
                $default_warehouse = $quotation_config['status'][5];
                $warehouse_short_code = Warehouse::select('order_short_code')->where('id', $default_warehouse)->first();
                $company_prefix = $warehouse_short_code->order_short_code;
            } else {
                $company_prefix = $company_prefix;
            }
            // $company_prefix          = @$order->customer->primary_sale_person->getCompany->prefix;
            $draft_customer_category = $order->customer->CustomerCategory;

            if ($order->customer->category_id == 6 && $order->ecommerce_order != 1) {
                $p_cat = CustomerCategory::where('id', 4)->first();
                $ref_prefix = $p_cat->short_code;
            } else {
                $ref_prefix              = $draft_customer_category->short_code;
            }

            if ($order->is_vat == 0) {
                $status_prefix           = $inv_status->prefix . $company_prefix;
                $c_p_ref = Order::where('in_status_prefix', '=', $status_prefix)->where('in_ref_prefix', $ref_prefix)->where('in_ref_id', 'LIKE', "$date%")->orderby('converted_to_invoice_on', 'DESC')->first();
            } else {
                $status_prefix           = "DO" . $company_prefix;
                $c_p_ref = Order::where('in_status_prefix', '=', $status_prefix)->where('manual_ref_no', 'LIKE', "$date%")->orderby('converted_to_invoice_on', 'DESC')->first();
            }

            $str = @$c_p_ref->in_ref_id;
            $onlyIncrementGet = substr($str, 4);
            if ($str == null) {
                $onlyIncrementGet = 0;
            }
            $system_gen_no = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
            $system_gen_no = $date . $system_gen_no;

            $order->in_status_prefix = $status_prefix;

            $order->in_ref_id = $system_gen_no;
            if ($order->is_vat == 0) {
                $order->in_ref_prefix = $ref_prefix;
            }
            if ($order->is_vat == 1) {
                $order->manual_ref_no = $system_gen_no;
            }
            $order->full_inv_no = $status_prefix . '-' . $ref_prefix . $system_gen_no;
            // $order->full_inv_no = 'unique-1234';
            $order->save();

            if ($order->ecommerce_order == 1) {
                $base_link  = config('app.ecom_url');
                $order_payment_ref = new OrdersPaymentRef;
                $order_payment_ref->payment_reference_no = 'EC' . $order->in_ref_id;
                $order_payment_ref->customer_id = $order->customer_id;
                $order_payment_ref->payment_method = 3;
                $order_payment_ref->received_date = $rec_date;
                $order_payment_ref->save();

                $order_transaction                   = new OrderTransaction;
                $order_transaction->order_id         = $request->order_id;
                $order_transaction->customer_id         = $order->customer_id;
                $order_transaction->order_ref_no         = $order->ref_id;
                $order_transaction->user_id         = $order->user_id;
                $order_transaction->payment_method_id = 3;
                $order_transaction->payment_reference_no = $order_payment_ref->id;
                $order_transaction->received_date    = $rec_date_ot;
                $order_transaction->total_received   = $order->total_amount;
                $order_transaction->vat_total_paid   = $order->vat_total_paid;
                $order_transaction->non_vat_total_paid   = $order->non_vat_total_paid;
                $order_transaction->save();

                //for status at ecom date sending through APi
                $uri = $base_link . "/api/updateorderstatus/" . $order->ecommerce_order_no . "/" . $order->primary_status . "/" . $order->status;
                // $test =  $this->sendRequest($uri);
                $test =  app('App\Http\Controllers\Warehouse\HomeController')->sendRequest($uri);
            }

            $is_partial = false;
            $partial_pick = QuotationConfig::where('section', 'partial_pick_instruction')->first();
            if ($partial_pick != null) {
                if ($partial_pick->display_prefrences == 1 || $partial_pick->display_prefrences == "1") {
                    $ordered_products = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Product')->get();
                    if ($ordered_products->count() > 0) {
                        foreach ($ordered_products as $products) {
                            if ($products->qty_shipped < $products->quantity) {
                                $is_partial = true;
                                break;
                            }
                        }
                    }

                    if ($is_partial == true) {
                        PartialMailJob::dispatch($order);
                    }
                }
            }
            $order->is_processing = 0;
            $order->save();


            DB::commit();
            return response()->json(['success' => true, 'full_inv_no' => $order->full_inv_no, 'order_id' => $order->id]);
        } catch (\Exception $e) {
            // dd($e);
            DB::rollBack();
            $order_check = Order::where('id', $request->order_id)->first();
            $order_check->is_processing = 0;
            $order_check->save();
            // $c_p_ref = Order::where('in_status_prefix','=',$status_prefix)->where('in_ref_prefix',$ref_prefix)->where('in_ref_id',$system_gen_no)->first();
            // $c_p_ref->converted_to_invoice_on = carbon::now();
            // $c_p_ref->save();
            // DB::commit();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function recursiveCallForPIJob(Request $request)
    {
        $job_status = ExportStatus::where('type', 'pick_instruction_job')->where('user_id', Auth::user()->id)->first();
        if ($job_status->status == 0) {
            // return $job_status->error_msgs;
            return response()->json(json_decode($job_status->error_msgs));
        }
        else if ($job_status->status == 1 || $job_status->status == 2) {
            return response()->json(['status' => $job_status->status]);
        }
    }

    public function sendRequest($uri){
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function changePassword()
    {
        return view('warehouse.password-management.index');
    }

    public function checkOldPassword(Request $request)
    {

        $hashedPassword = Auth::user()->password;
        $old_password = $request->old_password;
        if (Hash::check($old_password, $hashedPassword)) {
            $error = false;
        } else {
            $error = true;
        }

        return response()->json([
            "error" => $error,
        ]);
    }

    public function changePasswordProcess(Request $request)
    {
        // dd($request->all());
        $validator = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_new_password' => 'required',

        ]);

        $user = User::where('id', Auth::user()->id)->first();
        // dd($user);
        if ($user) {
            $hashedPassword = Auth::user()->password;
            $old_password = $request['old_password'];
            if (Hash::check($old_password, $hashedPassword)) {
                if ($request['new_password'] == $request['confirm_new_password']) {
                    $user->password = bcrypt($request['new_password']);
                }
            }
            $user->save();
        }

        return response()->json(['success' => true]);
    }

    public function profile()
    {
        $user_states = [];
        $countries = Country::orderBy('name', 'ASC')->get();
        $user_detail = UserDetail::where('user_id', Auth::user()->id)->first();
        if ($user_detail) {
            $user_states = State::where('country_id', $user_detail->country_id)->get();
        }
        return view('warehouse.profile-setting.index', ['countries' => $countries, 'user_detail' => $user_detail, 'user_states' => $user_states]);
    }

    public function updateProfile(Request $request)
    {
        $validator = $request->validate([
            'name' => 'required',
            'company' => 'required',
            'address' => 'required',
            'country' => 'required',
            'state' => 'required',
            'city' => 'required',
            'zip_code' => 'required',
            'phone_number' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
        ]);

        $error = false;
        $user = User::where('id', Auth::user()->id)->first();
        if ($user) {
            // dd('here');
            $user->name = $request['name'];
            $user->save();

            $user_detail = UserDetail::where('user_id', Auth::user()->id)->first();
            if ($user_detail) {
                $user_detail->address = $request['address'];
                $user_detail->country_id = $request['country'];
                $user_detail->state_id = $request['state'];
                $user_detail->city_name = $request['city'];
                $user_detail->zip_code = $request['zip_code'];
                $user_detail->phone_no = $request['phone_number'];
                $user_detail->company_name = $request['company'];

                //image

                if ($request->hasFile('image') && $request->image->isValid()) {
                    $fileNameWithExt = $request->file('image')->getClientOriginalName();
                    $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
                    $path = $request->file('image')->move('public/uploads/warehouse/images/', $fileNameToStore);
                    $user_detail->image = $fileNameToStore;
                }

                $user_detail->save();

                return response()->json([
                    "error" => $error,
                ]);
            } else {
                // dd('here');
                $user_detail = new UserDetail;
                $user_detail->user_id = Auth::user()->id;
                $user_detail->address = $request['address'];
                $user_detail->country_id = $request['country'];
                $user_detail->state_id = $request['state'];
                $user_detail->city_name = $request['city'];
                $user_detail->zip_code = $request['zip_code'];
                $user_detail->phone_no = $request['phone_number'];
                $user_detail->company_name = $request['company'];

                //image

                if ($request->hasFile('image') && $request->image->isValid()) {
                    $fileNameWithExt = $request->file('image')->getClientOriginalName();
                    $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);
                    $extension = $request->file('image')->getClientOriginalExtension();
                    $fileNameToStore = $fileName . '_' . time() . '.' . $extension;
                    $path = $request->file('image')->move('public/uploads/warehouse/images/', $fileNameToStore);
                    $user_detail->image = $fileNameToStore;
                }

                $user_detail->save();

                return response()->json([
                    "error" => $error,
                ]);
            }
        }
    }

    public function getWarehouseTransferDashboard()
    {
        $purchasingStatuses = Status::where('parent_id', 19)->get();

        $waitingConfirmTd = PurchaseOrder::where('status', 20)->where('to_warehouse_id', Auth::user()->warehouse_id)->count();
        $waitingTransfer = PurchaseOrder::where('status', 21)->where('to_warehouse_id', Auth::user()->warehouse_id)->count();
        $completetransfer = PurchaseOrder::where('status', 22)->where('to_warehouse_id', Auth::user()->warehouse_id)->count();
        $page_status = Status::select('title')->whereIn('id', [20, 21, 22])->pluck('title')->toArray();
        return $this->render('warehouse.home.transfer-document-dashboard', compact('purchasingStatuses', 'waitingConfirmTd', 'waitingTransfer', 'completetransfer', 'page_status'));
    }

    public function getWarehouseTransferDetail($id)
    {
        $getPurchaseOrderDetail = PurchaseOrderDetail::with('customer')->where('po_id', $id)->first();
        $paymentTerms = PaymentTerm::all();
        $getPurchaseOrder = PurchaseOrder::find($id);
        $warehouses = Warehouse::where('status',1)->get();
        $company_info = Company::where('id', $getPurchaseOrder->createdBy->company_id)->first();
        $getPoNote = PurchaseOrderNote::where('po_id', $id)->first();
        $checkPoDocs = PurchaseOrderDocument::where('po_id', $id)->get()->count();

        return view('warehouse.transfer.warehouse-purchase-order-detail', compact('getPurchaseOrderDetail', 'id', 'getPurchaseOrder', 'checkPoDocs', 'getPoNote', 'po_setting', 'company_info', 'paymentTerms'));
    }

    public function confirmWarehouseTransferDocument(Request $request)
    {
        $total_import_tax_book_price = null;
        $confirm_date = date("Y-m-d");
        $po = PurchaseOrder::find($request->id);

        $po_detail = PurchaseOrderDetail::where('po_id', $request->id)->get();
        if ($po_detail->count() > 0) {
            foreach ($po_detail as $value) {
                if ($value->quantity == null || $value->quantity == 0) {
                    $errorMsg = 'Quantity cannot be Null or Zero, please enter the quantity of the added items';
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                }
            }
        }

        $po->confirm_date = $confirm_date;
        $po->status = 21;
        $po->save();
        // PO status history maintaining
        $page_status = Status::select('title')->whereIn('id', [20, 21])->pluck('title')->toArray();
        $poStatusHistory = new PurchaseOrderStatusHistory;
        $poStatusHistory->user_id = Auth::user()->id;
        $poStatusHistory->po_id = $po->id;
        $poStatusHistory->status = $page_status[0];
        $poStatusHistory->new_status = $page_status[1];
        $poStatusHistory->save();

        //  creating group of generated transfer document
        $total_quantity = null;
        $total_price = null;
        $total_import_tax_book_price = null;
        $total_gross_weight = null;
        $po_group = new PoGroup;

        // generating ref #
        $year2 = Carbon::now()->year;
        $month2 = Carbon::now()->month;

        $year2 = substr($year2, -2);
        $month2 = sprintf("%02d", $month2);
        $date = $year2 . $month2;

        $c_p_ref2 = PoGroup::where('ref_id', 'LIKE', "$date%")->orderby('id', 'DESC')->first();
        $str2 = @$c_p_ref2->ref_id;
        $onlyIncrementGet2 = substr($str2, 4);
        if ($str2 == null) {
            $onlyIncrementGet2 = 0;
        }
        $system_gen_no2 = $date . str_pad(@$onlyIncrementGet2 + 1, STR_PAD_LEFT);

        $po_group->ref_id = $system_gen_no2;
        $po_group->bill_of_landing_or_airway_bill = '';
        $po_group->bill_of_lading = '';
        $po_group->airway_bill = '';
        $po_group->courier = '';
        $po_group->target_receive_date = $po->target_receive_date;
        $po_group->warehouse_id = $po->to_warehouse_id;
        $po_group->from_warehouse_id = $po->from_warehouse_id;
        $po_group->save();

        $po_group_detail = new PoGroupDetail;
        $po_group_detail->po_group_id = $po_group->id;
        $po_group_detail->purchase_order_id = $po->id;
        $po_group_detail->save();

        $purchase_order = PurchaseOrder::find($po->id);
        foreach ($purchase_order->PurchaseOrderDetail as $p_o_d) {
            $total_quantity += $p_o_d->quantity;
            if ($p_o_d->order_product_id != null) {
                $p_o_d->order_product->status = 9;
                $p_o_d->order_product->save();

                $order_products_status_count = OrderProduct::where('order_id', $p_o_d->order_id)->where('is_billed', '=', 'Product')->where('status', '!=', 9)->count();
                if ($order_products_status_count == 0) {
                    $p_o_d->order_product->get_order->status = 9;
                    $p_o_d->order_product->get_order->save();
                    $order_history = new OrderStatusHistory;
                    $order_history->user_id = Auth::user()->id;
                    $order_history->order_id = @$p_o_d->order_product->get_order->id;
                    $order_history->status = 'DI(Purchasing)';
                    $order_history->new_status = 'DI(Importing)';
                    $order_history->save();
                }
            }
        }

        $total_import_tax_book_price += $purchase_order->total_import_tax_book_price;
        $total_gross_weight += $purchase_order->total_gross_weight;
        $purchase_order->status = 21;
        $purchase_order->save();
        // dd($purchase_order);

        $po_group->total_quantity = $total_quantity;
        $po_group->po_group_import_tax_book = $total_import_tax_book_price;
        $po_group->po_group_total_gross_weight = $total_gross_weight;
        $po_group->save();

        $group_status_history = new PoGroupStatusHistory;
        $group_status_history->user_id = Auth::user()->id;
        $group_status_history->po_group_id = @$po_group->id;
        $group_status_history->status = 'Created';
        $group_status_history->new_status = 'Open Product Receiving Queue';
        $group_status_history->save();

        $status = "dfs";
        session(['td_status' => 21]);
        return response()->json(['success' => true, 'status' => $status]);
    }

    public function getWarehouseTransferDocumentData(Request $request)
    {
        $query = PurchaseOrder::with('PurchaseOrderDetail', 'createdBy', 'PoSupplier', 'po_notes')->where('to_warehouse_id', Auth::user()->warehouse_id);

        if ($request->dosortby == 20) {
            $query->where(function ($q) {
                $q->where('status', 20)->orderBy('id', 'ASC');
            });
        } elseif ($request->dosortby == 21) {
            $query->where(function ($q) {
                $q->where('status', 21)->orderBy('id', 'ASC');
            });
        } elseif ($request->dosortby == 22) {
            $query->where(function ($q) {
                $q->where('status', 22)->orderBy('id', 'ASC');
            });
        } elseif ($request->dosortby == 'all') {
            $query->where(function ($q) {
                $q->whereIn('status', [20, 21, 22, 23])->orderBy('id', 'ASC');
            });
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('transfer_date', '>=', $date);
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('transfer_date', '<=', $date);
        }

        if ($request->selecting_suppliers != null) {
            $query->where('supplier_id', $request->selecting_suppliers);
        }
        $query->orderBy('id', 'DESC');
        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {
                if ($item->status == 20) {
                    $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="' . $item->id . '" id="product_check_' . $item->id . '">
                                <label class="custom-control-label" for="product_check_' . $item->id . '"></label>
                              </div>';
                } else {
                    $html_string = 'N.A';
                }
                return $html_string;
            })

        // ->addColumn('action', function ($item) {
        //     $html_string = '
        //      <a href="'.url('warehouse/get-warehouse-transfer-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
        //      // <a href="javascript:void(0);" class="actionicon deleteIcon custDeleteIcon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
        //     return $html_string;
        // })
            ->editColumn('ref_id', function ($item) {
                if ($item->ref_id !== null) {
                    $html_string = '
                    <a href="' . url('warehouse/get-warehouse-transfer-detail/' . $item->id) . '"><b>' . $item->ref_id . '</b></a>';
                    return $html_string;
                } else {
                    return '--';
                }
            })
            ->addColumn('supplier', function ($item) {
                return $item->from_warehouse_id !== null ? $item->PoWarehouse->warehouse_title : "N.A";
            })

            ->filterColumn('supplier', function ($query, $keyword) {
                $query->whereHas('PoWarehouse', function ($q) use ($keyword) {
                    $q->where('warehouses.warehouse_title', 'LIKE', "%$keyword%");
                });
            }, true)

            ->addColumn('supplier_ref', function ($item) {
                return $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';
            })
            ->addColumn('confirm_date', function ($item) {
                return $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';
            })
            ->addColumn('received_date', function ($item) {
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
            })
            ->addColumn('transfer_date', function ($item) {
                return $item->transfer_date !== null ? Carbon::parse($item->transfer_date)->format('d/m/Y') : '--';
            })
            ->addColumn('po_total', function ($item) {
                return $item->total !== null ? number_format($item->total, 3, '.', ',') : '--';
            })
            ->addColumn('target_receive_date', function ($item) {
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
            })
            ->addColumn('payment_due_date', function ($item) {
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';
            })
            ->addColumn('note', function ($item) {
                if ($item->po_notes->count() > 0) {
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#note-modal" data-id="' . $item->id . '" class="d-block show-po-note mr-2" title="View Notes"><i class="fa fa-info"></i></a> ';
                } else {
                    $html_string = '---';
                }
                return $html_string;
            })
        // ->addColumn('status', function ($item) {
        //     $getStatuses = Status::where('parent_id',4)->where('title','!=','Un-Finished PO')->get();
        //         $html_string = '<select class="font-weight-bold form-control-lg form-control change-po-status input-height select-tag" name="change-po-status" id="change-po-status">';
        //         foreach ($getStatuses as $status)
        //         {
        //             $value = $status->id == @$item->status ? 'selected' : "";
        //             $html_string .= '<option '.$value.' value="'.$status->id.'">'.$status->title.'</option>';
        //         }
        //         $html_string .= '</select>';
        //     return $html_string;
        // })
            ->addColumn('customer', function ($item) {
                $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', null)->where('po_id', $item->id)->get()->groupBy('customer_id');

                $html_string = '';

                if ($getCust->count() > 1) {
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="' . $item->id . '" class="fa fa-group d-block show-po-cust mr-2" title="View Customers"></a> ';
                } elseif ($getCust->count() == 1) {
                    foreach ($getCust as $value) {
                        if ($value != null) {
                            $html_string = @$value[0]->customer->reference_name;
                        }
                    }
                } elseif ($getCust->count() == 0) {
                    $html_string = "---";
                }

                return $html_string;
            })

            ->addColumn('to_warehouse', function ($item) {
                return $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : 'N.A';
            })

            ->filterColumn('to_warehouse', function ($query, $keyword) {
                $query->whereHas('ToWarehouse', function ($q) use ($keyword) {
                    $q->where('warehouses.warehouse_title', 'LIKE', "%$keyword%");
                });
            }, true)

            ->setRowId(function ($item) {
                return @$item->id;
            })
            ->rawColumns(['checkbox', 'status', 'ref_id', 'supplier', 'supplier_ref', 'confirm_date', 'po_total', 'target_receive_date', 'payment_due_date', 'note', 'customer', 'transfer_date'])
            ->make(true);
    }

    public function deleteTransferDocWarehouse(Request $request)
    {
        $multi_tds = explode(',', $request->selected_tds);
        //dd($multi_tds);
        if (sizeof($multi_tds) <= 100) {

            for ($i = 0; $i < sizeof($multi_tds); $i++) {
                $purchase_order = PurchaseOrder::find($multi_tds[$i]);

                if ($purchase_order) {
                    $purchase_order_detail = $purchase_order->PurchaseOrderDetail;
                    foreach ($purchase_order_detail as $pod) {
                        $pod->delete();
                    }

                    $po_status_history = PurchaseOrderStatusHistory::where('po_id', $purchase_order->id)->delete();
                    $po_his = PurchaseOrdersHistory::where('po_id', $purchase_order->id)->delete();

                    $purchase_order->delete();
                }
            }
        } else {
            return response()->json(['error' => 1]);
        }
        return response()->json(['success' => true]);
    }

    public function getDraftWarehouseTdData(Request $request)
    {
        // dd($request->all());
        $query = DraftPurchaseOrder::with('draftPoDetail', 'getSupplier')->where('to_warehouse_id', Auth::user()->warehouse_id);

        if ($request->dosortby == 23) {
            $query->where(function ($q) {
                $q->where('status', 23)->orderBy('id', 'DESC');
            });
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('target_receive_date', '>=', $date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date = date('Y-m-d', strtotime($date));
            $query->where('target_receive_date', '<=', $date . ' 00:00:00');
        }

        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {
                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                    <input type="checkbox" class="custom-control-input check1" value="' . $item->id . '" id="stone_check_' . $item->id . '">
                    <label class="custom-control-label" for="stone_check_' . $item->id . '"></label>
                </div>';
                return $html_string;
            })

        // ->addColumn('action', function ($item) {
        //     $html_string = '
        //      <a href="'.url('warehouse/get-draft-warehouse-td/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
        //      // <a href="javascript:void(0);" class="actionicon deleteIcon custDeleteIcon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
        //     return $html_string;
        // })
            ->addColumn('po_id', function ($item) {
                // return $item->id !== null ? $item->id : 'N.A';
                if ($item->id !== null) {
                    $html_string = '<a href="' . url('warehouse/get-draft-warehouse-td/' . $item->id) . '"><b>' . $item->id . '</b></a>';
                } else {
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
            ->rawColumns(['action', 'status', 'po_id', 'supplier', 'supplier_ref', 'confirm_date', 'po_total', 'target_receive_date', 'supply_to', 'checkbox'])
            ->make(true);
    }

    public function createTransferDocWarehouse()
    {
        $draft_td = new DraftPurchaseOrder;
        $draft_td->status = 23;
        $draft_td->to_warehouse_id = Auth::user()->warehouse_id;
        $draft_td->created_by = Auth::user()->id;
        $draft_td->save();
        return redirect()->route("get-draft-warehouse-td", $draft_td->id);
    }

    public function getDraftWarehouseTd($id)
    {
        $draft_po = DraftPurchaseOrder::find($id);
        $getPoNote = DraftPurchaseOrderNote::where('po_id', $id)->first();
        $company_info = Company::where('id', $draft_po->createdBy->company_id)->first();
        $warehouses = Warehouse::where('status',1)->where('id', '!=', Auth::user()->warehouse_id)->orderBy('warehouse_title')->get();
        $warehousesTo = Warehouse::where('status',1)->orderBy('warehouse_title')->get();
        $paymentTerms = PaymentTerm::all();
        $sub_total = 0;
        $query = DraftPurchaseOrderDetail::where('po_id', $id)->get();
        foreach ($query as $value) {
            $unit_price = $value->pod_unit_price;
            $sub = $value->quantity * $unit_price - (($value->quantity * $unit_price) * (@$value->discount / 100));
            $sub_total += $sub;
        }
        $checkDraftPoDocs = DraftPurchaseOrderDocument::where('po_id', $id)->get()->count();
        return view('warehouse.transfer.create-direct-td', compact('id', 'suppliers', 'draft_po', 'sub_total', 'checkDraftPoDocs', 'po_setting', 'company_info', 'warehouses', 'warehousesTo', 'getPoNote', 'paymentTerms'));
    }

    public function doActionDraftWarehouseTd(Request $request)
    {
        $action = $request->action;
        if ($action == 'save') {
            $draft_po = DraftPurchaseOrder::find($request->draft_po_id);

            if ($draft_po->draftPoDetail()->count() == 0) {
                $errorMsg = 'Please add some products in the Transfer Document';
                return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
            }

            $draft_po_detail = DraftPurchaseOrderDetail::where('po_id', $request->draft_po_id)->get();

            if ($draft_po_detail->count() > 0) {
                foreach ($draft_po_detail as $value) {
                    if ($value->quantity == null) {
                        $errorMsg = 'Quantity cannot be Null, please enter quantity of the added items';
                        return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
                    }
                }
            }

            $year = Carbon::now()->year;
            $month = Carbon::now()->month;

            $year = substr($year, -2);
            $month = sprintf("%02d", $month);
            $date = $year . $month;

            $c_p_ref = PurchaseOrder::where('ref_id', 'LIKE', "$date%")->orderby('id', 'DESC')->first();
            $str = @$c_p_ref->ref_id;
            $onlyIncrementGet = substr($str, 4);
            if ($str == null) {
                // $str = $date.'0';
                $onlyIncrementGet = 0;
            }
            $system_gen_no = $date . str_pad(@$onlyIncrementGet + 1, STR_PAD_LEFT);
            $date = date('y-m-d');

            $purchaseOrder = PurchaseOrder::create([
                'ref_id' => $system_gen_no,
                'status' => 20,
                'total' => $draft_po->total,
                'total_quantity' => $draft_po->total_quantity,
                'total_gross_weight' => $draft_po->total_gross_weight,
                'total_import_tax_book' => $draft_po->total_import_tax_book,
                'total_import_tax_book_price' => $draft_po->total_import_tax_book_price,
                'supplier_id' => $draft_po->supplier_id,
                'from_warehouse_id' => $draft_po->from_warehouse_id,
                'created_by' => Auth::user()->id,
                'memo' => @$draft_po->memo,
                'payment_terms_id' => $draft_po->payment_terms_id,
                'payment_due_date' => $draft_po->payment_due_date,
                'target_receive_date' => $draft_po->target_receive_date,
                'transfer_date' => $draft_po->transfer_date,
                'confirm_date' => $date,
                'to_warehouse_id' => $draft_po->to_warehouse_id,
            ]);

            // PO status history maintaining
            $page_status = Status::select('title')->whereIn('id', [20])->pluck('title')->toArray();
            $poStatusHistory = new PurchaseOrderStatusHistory;
            $poStatusHistory->user_id = Auth::user()->id;
            $poStatusHistory->po_id = $purchaseOrder->id;
            $poStatusHistory->status = 'Created';
            $poStatusHistory->new_status = $page_status[0];
            $poStatusHistory->save();

            $draft_po_detail = DraftPurchaseOrderDetail::where('po_id', $draft_po->id)->get();

            foreach ($draft_po_detail as $dpo_detail) {
                $product = Product::where('id', $dpo_detail->product_id)->first();
                PurchaseOrderDetail::create([
                    'po_id' => $purchaseOrder->id,
                    'order_id' => null,
                    'customer_id' => null,
                    'order_product_id' => null,
                    'product_id' => $dpo_detail->product_id,
                    'pod_import_tax_book' => $dpo_detail->pod_import_tax_book,
                    'pod_unit_price' => $dpo_detail->pod_unit_price,
                    'pod_gross_weight' => $dpo_detail->pod_gross_weight,
                    'quantity' => $dpo_detail->quantity,
                    'pod_total_gross_weight' => $dpo_detail->pod_total_gross_weight,
                    'pod_total_unit_price' => $dpo_detail->pod_total_unit_price,
                    'discount' => $dpo_detail->discount,
                    'pod_import_tax_book_price' => $dpo_detail->pod_import_tax_book_price,
                    'warehouse_id' => $draft_po->to_warehouse_id,
                    'temperature_c' => $product->product_temprature_c,
                    'good_type' => $product->type_id,
                    'supplier_packaging' => $dpo_detail->supplier_packaging,
                    'billed_unit_per_package' => $dpo_detail->billed_unit_per_package,
                ]);
            }

            // getting documents of draft_Po
            $draft_po_docs = DraftPurchaseOrderDocument::where('po_id', $request->draft_po_id)->get();
            foreach ($draft_po_docs as $docs) {
                PurchaseOrderDocument::create([
                    'po_id' => $purchaseOrder->id,
                    'file_name' => $docs->file_name,
                ]);
            }

            $draft_notes = DraftPurchaseOrderNote::where('po_id', $request->draft_po_id)->get();
            if (@$draft_notes != null) {
                foreach ($draft_notes as $note) {
                    $order_note = new PurchaseOrderNote;
                    $order_note->po_id = $purchaseOrder->id;
                    $order_note->note = $note->note;
                    $order_note->created_by = @$note->created_by;
                    $order_note->save();
                }
            }

            $delete_draft_po = DraftPurchaseOrder::find($request->draft_po_id);
            $delete_draft_po->draftPoDetail()->delete();
            $delete_draft_po->draft_po_notes()->delete();
            $delete_draft_po->delete();

            $delete_draft_po_docs = DraftPurchaseOrderDocument::where('po_id', $request->draft_po_id)->get();
            foreach ($delete_draft_po_docs as $del) {
                $del->delete();
            }

            $errorMsg = 'Transfer Document Created Successfully.';
            return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
        }
    }
}
