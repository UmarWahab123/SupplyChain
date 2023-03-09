<?php

namespace App\Http\Controllers\Warehouse;

use DB;
use Auth;
use App\User;
use App\General;
use App\Variable;
use Carbon\Carbon;
use App\Notification;
use App\QuotationConfig;
use Illuminate\Http\Request;
use App\Models\Common\Status;
use App\Models\Common\PoGroup;
use App\Models\Common\Product;
use App\Models\Common\Supplier;
use App\Models\Common\Warehouse;
use Yajra\Datatables\Datatables;
use App\Models\Common\Order\Order;
use App\Models\Common\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use App\Models\Common\PoGroupDetail;
use Illuminate\Support\Facades\View;
use App\Helpers\POGroupSortingHelper;
use App\Models\Common\StockOutHistory;
use App\Models\Common\TableHideColumn;
use Illuminate\Support\Facades\Schema;
use App\Exports\ProductReceivingRecord;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\Helpers\QuantityReservedHistory;
use App\Models\Common\StockManagementIn;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\TransferDocumentReservedQuantity;
use App\Models\Common\PoGroupProductDetail;
use App\Helpers\Datatables\PoGroupDatatable;
use App\Models\Common\RevertedPurchaseOrder;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\ProductReceivingHistory;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;

class PoGroupsController extends Controller
{
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
            $dummy_data = Notification::where('notifiable_id', $this->user->id)->orderby('created_at','desc')->get();
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
    public function receivingQueue()
    {
		$table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'receiving_queue')->first();
		$display_prods = ColumnDisplayPreference::where('type', 'receiving_queue')->where('user_id', Auth::user()->id)->first();
        return $this->render('warehouse.po-groups.new-incompleted-po-groups',compact('table_hide_columns','display_prods'));
    }

    public function getWarehouseReceivingPoGroups(Request $request)
    {

    	$today = Carbon::now();
		$today = date('Y-m-d',strtotime("+3 days"));
    	$is_con = $request->dosortby;
    	if(Auth::user()->role_id == 2 || Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
    	{
    		$query = PoGroup::where('po_groups.from_warehouse_id',NULL);
    	}
    	else
    	{
        	$query = PoGroup::where('po_groups.warehouse_id',Auth::user()->get_warehouse->id)->where('po_groups.from_warehouse_id',NULL);
        }
        if($is_con == 2)
        {
        	$query = $query->where('po_groups.is_cancel',$is_con);
        }
        else
        {
        	$query = $query->where('po_groups.is_confirm',$is_con)->where('po_groups.is_cancel',NULL);
        }

        if($request->from_date != null)
        {
        	$from_date = str_replace("/","-",$request->from_date);
            $from_date =  date('Y-m-d',strtotime($from_date));
           	$query->where('po_groups.target_receive_date', '>=', $from_date);
        }
        if($request->to_date != null)
        {
        	$to_date = str_replace("/","-",$request->to_date);
            $to_date =  date('Y-m-d',strtotime($to_date));
           	$query->where('po_groups.target_receive_date', '<=', $to_date);
        }

        $query->with('ToWarehouse','po_courier', 'po_group_detail.purchase_order.PoSupplier', 'po_group_product_details')->select('po_groups.*');
        $query = POGroupSortingHelper::ReceivingQueueSorting($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['courier', 'warehouse', 'target_receive_date', 'po_total', 'net_weight', 'quantity', 'supplier_ref_no', 'po_number', 'id', 'issue_date'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function($item) use($column) {
                return PoGroupDatatable::returnAddColumnReceivingQueue($column, $item);
            });
        }

	    $dt->rawColumns(['po_number','bill_of_lading','airway_bill','tax','freight','landing','bl_awb','courier','vendor','vendor_ref_no','supplier_ref_no','id']);

	    return $dt->make(true);
	}

	public function viewPoNumbersWarehouse(Request $request)
	{
		$i = 1;
        $po_group = PoGroup::find($request->id);
        $po_group_detail = $po_group->po_group_detail;
        $html_string = '';
        // foreach ($po_group_detail as $p_g_d) {
        //     $link = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $p_g_d->purchase_order->id]).'" title="View Detail"><b>'.$p_g_d->purchase_order->ref_id.'</b></a>';
        //     $html_string .= '<tr><td style="text-align:center">'.$i.'</td><td style="text-align:center">'.@ $link.'</td></tr>';
        //     $i++;
		// }
		foreach ($po_group_detail as $p_g_d) {
			$link = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $p_g_d->purchase_order->id]).'" title="View Detail"><b>'.$p_g_d->purchase_order->ref_id.'</b></a>';
		  $html_string .= '<tr><td style="text-align:center">'.$i.'</td><td style="text-align:center">'.@ $link.'</td></tr>';
		  $i++;
		}
        return $html_string;
	}

	public function viewSupplierNamesWarehouse(Request $request)
	{
		$i = 1;
        $po_group = PoGroup::find($request->id);
        $po_group_detail = $po_group->po_group_detail;
        $html_string = '';
        // foreach ($po_group_detail as $p_g_d)
        // {
        //     if($p_g_d->purchase_order->supplier_id != null)
        //     {
        //         $ref_no = $p_g_d->purchase_order->PoSupplier->reference_number;
        //         $name = $p_g_d->purchase_order->PoSupplier->reference_name;
        //     }
        //     else
        //     {
        //         $ref_no = $p_g_d->purchase_order->PoWarehouse->location_code;
        //         $name = $p_g_d->purchase_order->PoWarehouse->warehouse_title;
        //     }
        //     $html_string .= '<tr><td style="text-align:center">'.$i.'</td><td style="text-align:center">'.@$ref_no.'</td><td style="text-align:center">'.@$name.'</td></tr>';
        //     $i++;
		// }
		foreach ($po_group_detail as $p_g_d)
		{
			if($p_g_d->purchase_order->supplier_id != null)
			{
			$ref_no = $p_g_d->purchase_order->PoSupplier->reference_number;
			$name = $p_g_d->purchase_order->PoSupplier->reference_name;
			}
			else
			{
			$ref_no = $p_g_d->purchase_order->PoWarehouse->location_code;
			$name = $p_g_d->purchase_order->PoWarehouse->warehouse_title;
			}
			$html_string .= '<tr><td style="text-align:center">'.$i.'</td><td style="text-align:center">'.@$ref_no.'</td><td style="text-align:center">'.@$name.'</td></tr>';
			$i++;
		}
        return $html_string;
	}

    public function receivingQueueDetail($id)
    {
    	// dd('here');
		$po_group = PoGroup::find($id);
		$table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'open_product_receiving')->first();

		// dd($product_receiving_history);
		$group_detail = PoGroupProductDetail::where('status',1)->where('po_group_id',$id)->count();
       	$status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();
       	$suppliers = Supplier::select('id','reference_name')->where('status',1)->get();
       	$products = Product::select('id','short_desc')->where('status',1)->get();

       	#to find the po in the group
       	$pos = PoGroupDetail::where('po_group_id',$po_group->id)->pluck('purchase_order_id')->toArray();
       	$pos_supplier_invoice_no = PurchaseOrder::select('id','invoice_number')->whereNotNull('invoice_number')->whereIn('id',$pos)->get();

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

        return $this->render('warehouse.po-groups.products-receiving-detail',compact('po_group','id','product_receiving_history','status_history','group_detail','suppliers','products','table_hide_columns','pos_supplier_invoice_no','allow_custom_invoice_number','show_custom_line_number','show_supplier_invoice_number'));
	}

    public function getPoGroupProductDetailsHistory(Request $request)
    {
        $query = ProductReceivingHistory::with('get_user:id,name', 'get_po_group_product_detail.product:id,refrence_code')->where('po_group_id',$request->id)->orderBy('id','DESC');
        return Datatables::of($query)
        ->addColumn('user',function($item){
            return $item->get_user != null ? $item->get_user->name : '--';
        })
        ->addColumn('date',function($item){
            return $item->created_at != null ? Carbon::parse($item->created_at)->format('d/m/Y, H:i:s') : '--';
        })
        ->addColumn('product',function($item){
            return $item->get_po_group_product_detail != null ? $item->get_po_group_product_detail->product->refrence_code : '--';
        })
        ->addColumn('column',function($item){
            return $item->term_key != null ? $item->term_key : '--';
        })
        ->addColumn('old_value',function($item){
             return $item->old_value != null ? $item->old_value : '--';
        })
        ->addColumn('new_value',function($item){
             return $item->new_value != null ? $item->new_value : '--';
        })
        ->make(true);

    }

	public function getPoGroupProductDetails($id,Request $request)
    {
        $all_record = PoGroupProductDetail::where('po_group_product_details.status',1)->where('po_group_product_details.po_group_id',$id);

        if($request->supplier_id != null)
        {
        	$all_record = $all_record->where('po_group_product_details.supplier_id',$request->supplier_id);
        }

        if($request->product_id != null)
        {
        	$all_record = $all_record->where('po_group_product_details.product_id',$request->product_id);
        }
        $all_record = $all_record->with('product.supplier_products','get_supplier','product.units','product.sellingUnits', 'purchase_order', 'order.user.get_warehouse', 'get_warehouse', 'order.customer')->select('po_group_product_details.*');
        $all_record = POGroupSortingHelper::WarehouseProductRecevingRecordsSorting($request, $all_record);

		$goods_types = ProductType::all();

		$not_visible_arr = [];
        $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','open_product_receiving')->where('user_id',Auth::user()->id)->first();
        if($not_visible_columns != null)
        {
          $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
        }

		$dt = Datatables::of($all_record);
		$dt->addColumn('occurrence',function($item){
            return $item->occurrence;
        });

		if(!in_array('1', $not_visible_arr))
        {
			$dt->addColumn('po_number',function($item){
            $occurrence = $item->occurrence;
            if($occurrence == 1)
            {
                $po = $item->purchase_order;
                if($po->ref_id !== null){
                    $html_string = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $po->id]).'" title="View Detail"><b>'.$po->ref_id.'</b></a>';
                    return $html_string;
                }
                else{
                    return "--";
                }
            }
            else
            {
            	return '--';
            }
        });
		}
		else
		{
			$dt->addColumn('po_number',function($item){
				return '--';
			});
		}

		if(!in_array('5', $not_visible_arr))
        {
	    	$dt->addColumn('order_warehouse',function($item){
                $occurrence = $item->occurrence;
                if($occurrence == 1)
                {
                    $order = $item->order;
                    return $order !== null ? $order->user->get_warehouse->warehouse_title : "N.A" ;
                }
                else
                {
                    return '--';
                }
        });
	    }
	    else
	    {
	    	$dt->addColumn('order_warehouse',function($item){
				return '--';
			});
	    }
	    if(!in_array('2', $not_visible_arr))
        {
	    	$dt->addColumn('order_no',function($item){
                $occurrence = $item->occurrence;
                if($occurrence == 1)
                {
                    $order = $item->order;
                    if ($order != null) {
                       $ret = $order->get_order_number_and_link($order);
                        $ref_no = $ret[0];
                        $html_string = '<a target="_blank" href="'.route('pick-instruction', ['id' => $order->id]).'" title="View Detail"><b>'.$ref_no.'</b></a>';
                        return $html_string;
                    }
                    else{
                        return "N.A";
                    }
                }
                else
                {
                    return '--';
                }
        });
	    }
	    else
	    {
	    	$dt->addColumn('order_no',function($item){
				return '--';
			});
	    }

	    if(!in_array('6', $not_visible_arr))
        {
			$dt->addColumn('supplier',function($item){
			if($item->supplier_id !== NULL)
            {
				return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier_id).'"><b>'.$item->get_supplier->reference_name.'</b></a>';
			}
            else
            {
                $sup_name = $item->get_warehouse->warehouse_title;
                return  $html_string = $sup_name->warehouse_title;
            }

        });
		}
		else
	    {
	    	$dt->addColumn('supplier',function($item){
				return '--';
			});
	    }
        $dt->filterColumn('supplier', function( $query, $keyword ) {
            $query = $query->whereIn('supplier_id', Supplier::select('id')->where('reference_name','LIKE',"%$keyword%")->pluck('id'));
        },true );
        if(!in_array('3', $not_visible_arr))
        {
	    	$dt->addColumn('reference_number',function($item){
	    	if($item->supplier_id !== NULL)
	    	{
                $sup_name = $item->product->supplier_products->first();
	            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
	        }
	        else
	        {
	        	return "N.A";
	        }

        });
	    }
	    else
	    {
	    	$dt->addColumn('reference_number',function($item){
				return '--';
			});
	    }
        $dt->filterColumn('reference_number', function( $query, $keyword ) {
            $query = $query->whereIn('supplier_id', SupplierProducts::select('supplier_id')->where('product_supplier_reference_no','LIKE',"%$keyword%")->pluck('supplier_id'))->whereIn('product_id', SupplierProducts::select('product_id')->where('product_supplier_reference_no','LIKE',"%$keyword%")->pluck('product_id'));
        },true );

        if(!in_array('4', $not_visible_arr))
        {
	    	$dt->addColumn('prod_reference_number',function($item){
            return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"><b>'.$item->product->refrence_code.'</b></a>';
        });
	    }
	    else
	    {
	    	$dt->addColumn('prod_reference_number',function($item){
				return '--';
			});
	    }
        $dt->filterColumn('prod_reference_number', function( $query, $keyword ) {
            $query = $query->whereIn('product_id', Product::select('id')->where('refrence_code','LIKE',"%$keyword%")->pluck('id'));
        },true );
        if(!in_array('7', $not_visible_arr))
        {
	    	$dt->addColumn('desc',function($item){
		    return $item->product_id != null ? $item->product->short_desc : '' ;
        });
	    }
	    else
	    {
	    	$dt->addColumn('desc',function($item){
				return '--';
			});
	    }
        $dt->filterColumn('desc', function( $query, $keyword ) {
            $query = $query->whereIn('product_id', Product::select('id')->where('short_desc','LIKE',"%$keyword%")->pluck('id'));
        },true );
        if(!in_array('8', $not_visible_arr))
        {
        	$dt->addColumn('customer',function($item){
            $occurrence = $item->occurrence;
            if($occurrence == 1)
            {
                $order = $item->order;
				if($order !== null){

              	$html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.$order->customer_id).'"><b>'.$order->customer->reference_name.'</b></a>';
              	return $html_string;

				}else{
					return "N.A";
				}
            }
            else
            {
            	return '--';
            }
        });
        }
        else
	    {
	    	$dt->addColumn('customer',function($item){
				return '--';
			});
	    }

	    if(!in_array('11', $not_visible_arr))
        {
	    	$dt->addColumn('buying_unit',function($item){
			return $item->product->units != null ? $item->product->units->title : '';
        });
	    }
	    else
	    {
	    	$dt->addColumn('buying_unit',function($item){
				return '--';
			});
	    }
	    if(!in_array('9', $not_visible_arr))
        {
	    	$dt->addColumn('qty_ordered',function($item){
	    	return round($item->quantity_ordered,3).' '.@$item->product->sellingUnits->title;
	    });
	    }
	    else
	    {
	    	$dt->addColumn('qty_ordered',function($item){
				return '--';
			});
	    }

	    if(!in_array('10', $not_visible_arr))
        {
	    	$dt->addColumn('qty_inv',function($item){
	    	return round($item->quantity_inv,3);
	    });
	    }
	    else
	    {
	    	$dt->addColumn('qty_inv',function($item){
				return '--';
			});
	    }

	    if(!in_array('12', $not_visible_arr))
        {
	    	$dt->addColumn('qty_receive',function($item){
	    	$quantity_received_1 = $item->quantity_received_1 != null ? $item->quantity_received_1 : '' ;

			$html_string = '<input type="number"  name="quantity_received_1" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_1.'" class="fieldFocus" value="'. $quantity_received_1.'" style="width:100%">';
	    	return $html_string;
	    });
	    }
	    else
	    {
	    	$dt->addColumn('qty_receive',function($item){
				return '--';
			});
	    }

	    if(!in_array('13', $not_visible_arr))
        {
	    	$dt->addColumn('expiration_date',function($item){
			$expiration_date_1 = $item->expiration_date_1 !== null ? Carbon::parse($item->expiration_date_1)->format('d/m/Y') : '';
			$html_string = '<input type="text" id="expiration_date_1"  name="expiration_date_1" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date_1.'" style="width:100%">';
	    	return $html_string;
		});
	    }
	    else
	    {
	    	$dt->addColumn('expiration_date',function($item){
				return '--';
			});
	    }
	    if(!in_array('14', $not_visible_arr))
        {
	    	$dt->addColumn('quantity_received_2',function($item){
	    	$quantity_received_2 = $item->quantity_received_2 != null ? $item->quantity_received_2 : '' ;

			$html_string = '<input type="number"  name="quantity_received_2" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_2.'" class="fieldFocus" value="'. $quantity_received_2.'" style="width:100%">';
	    	return $html_string;
	    });
	    }
	    else
	    {
	    	$dt->addColumn('quantity_received_2',function($item){
				return '--';
			});
	    }

	    if(!in_array('15', $not_visible_arr))
        {
	    	$dt->addColumn('expiration_date_2',function($item){
			$expiration_date_2 = $item->expiration_date_2 !== null ? Carbon::parse($item->expiration_date_2)->format('d/m/Y') : '';
			$html_string = '<input type="text" id="expiration_date_2"  name="expiration_date_2" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date_2.'" style="width:100%">';
	    	return $html_string;
		});
	    }
	    else
	    {
	    	$dt->addColumn('expiration_date_2',function($item){
				return '--';
			});
	    }

	    if(!in_array('16', $not_visible_arr))
        {
	    	$dt->addColumn('goods_condition',function($item){
			$check = $item->good_condition;
			$html_string = '<div class="d-flex">
			<div class="custom-control custom-radio custom-control-inline">';
			$html_string .= '<input type="checkbox" class="condition custom-control-input" ' .($item->good_condition == "normal" ? "checked" : ""). ' id="n'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="normal">';

			$html_string .='<label class="custom-control-label" for="n'.$item->id.'">Normal</label>
		   </div><div class="custom-control custom-radio custom-control-inline">
			 <input type="checkbox" class="condition custom-control-input" ' .($item->good_condition == "problem" ? "checked" : ""). ' id="p'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="problem"><label class="custom-control-label" for="p'.$item->id.'">Problem</label></div></div>';
	    	return $html_string;
		});
	    }
	    else
	    {
	    	$dt->addColumn('goods_condition',function($item){
				return '--';
			});
	    }

	    if(!in_array('17', $not_visible_arr))
        {
	    	$dt->addColumn('results',function($item){
			$check = $item->result;
			$html_string = '<div class="d-flex">
			<div class="custom-control custom-radio custom-control-inline">
			 <input type="checkbox" class="condition custom-control-input" ' .($item->result == "pass" ? "checked" : ""). ' id="pass'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="pass">
			 <label class="custom-control-label" for="pass'.$item->id.'">Pass</label>
		   </div>

		   <div class="custom-control custom-radio custom-control-inline">
			 <input type="checkbox" class="condition custom-control-input" ' .($item->result == "fail" ? "checked" : ""). ' id="fail'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="fail">
			 <label class="custom-control-label" for="fail'.$item->id.'">Fail</label>
		   </div>

		   </div>';

	    	return $html_string;
	    });
	    }
	    else
	    {
	    	$dt->addColumn('results',function($item){
				return '--';
			});
	    }

	    if(!in_array('18', $not_visible_arr))
        {
	    	$dt->addColumn('goods_type',function($item) use ($goods_types){
			$html_string = '<div class="d-flex">';
			foreach ($goods_types as $type) {
			$html_string .= '<div class="custom-control custom-radio custom-control-inline">
			 <input type="checkbox" class="condition custom-control-input" ' .($item->good_type == $type->id ? "checked" : ""). ' name="condition'.$item->id.'" data-id="'.$item->id.'" id="'.$type->title.$item->id.'" value="'.$type->id.'">
			 <label class="custom-control-label" for="'.$type->title.$item->id.'">'.$type->title.'</label>
		   </div>';
		}

		   $html_string .= '</div>';
	        return $html_string;
		});
		}
		else
	    {
	    	$dt->addColumn('goods_type',function($item){
				return '--';
			});
	    }

	    if(!in_array('19', $not_visible_arr))
        {
			$dt->addColumn('goods_temp',function($item){
			$goods_temp = $item->temperature_c;

			$html_string = '<input type="text"  name="temperature_c" data-id="'.$item->id.'" data-fieldvalue="'.$goods_temp.'" class="fieldFocus" value="'.$goods_temp.'" style="width:100%">';
	    	return $html_string;
		});
		}
		else
	    {
	    	$dt->addColumn('goods_temp',function($item){
				return '--';
			});
	    }

	    if(!in_array('20', $not_visible_arr))
        {
			$dt->addColumn('checker',function($item){
			$checker = $item->checker;

			$html_string = '<input type="text"  name="checker" data-id="'.$item->id.'" data-fieldvalue="'.$checker.'" class="fieldFocus" value="'.$checker.'" style="width:100%">';
	    	return $html_string;
	    });
		}
		else
	    {
	    	$dt->addColumn('checker',function($item){
				return '--';
			});
	    }

	    if(!in_array('21', $not_visible_arr))
        {
	    	$dt->addColumn('problem_found',function($item){
			$problem_found = $item->problem_found;

			$html_string = '<input type="text"  name="problem_found" data-id="'.$item->id.'" data-fieldvalue="'.$problem_found.'" class="fieldFocus" value="'.$problem_found.'" style="width:100%">';
	    	return $html_string;
		});
	    }
	    else
	    {
	    	$dt->addColumn('problem_found',function($item){
				return '--';
			});
	    }

	    if(!in_array('22', $not_visible_arr))
        {
			$dt->addColumn('solution',function($item){
			$solution = $item->solution;

			$html_string = '<input type="text"  name="solution" data-id="'.$item->id.'" class="fieldFocus" value="'.$solution.'" style="width:100%">';
	    	return $html_string;
		});
		}
		else
	    {
	    	$dt->addColumn('solution',function($item){
				return '--';
			});
	    }

	    if(!in_array('23', $not_visible_arr))
        {
			$dt->addColumn('changes',function($item){
			$authorized_changes = $item->authorized_changes;

			$html_string = '<input type="text"  name="authorized_changes" data-id="'.$item->id.'" class="fieldFocus" value="'.$authorized_changes.'" style="width:100%">';
	    	return $html_string;
		});
		}
		else
	    {
	    	$dt->addColumn('changes',function($item){
				return '--';
			});
	    }

	    if(!in_array('24', $not_visible_arr))
        {
		 	$dt->addColumn('custom_line_number',function($item){
            $html_string = '<input type="text"  name="custom_line_number" data-id="'.$item->id.'" data-fieldvalue="'.@$item->custom_line_number.'" class="fieldFocus" value="'.@$item->custom_line_number.'" style="width:100%">';
            return $html_string;
        });
		}
		else
	    {
	    	$dt->addColumn('custom_line_number',function($item){
				return '--';
			});
	    }
	    $dt->rawColumns(['po_number','order_no','supplier','reference_number','prod_reference_number','desc','customer','kg','qty_inv','qty_receive','quantity_received_2','goods_condition','results','goods_type','goods_temp','checker','problem_found','solution','changes','order_id','expiration_date','expiration_date_2','custom_line_number']);
		return $dt->make(true);
	}

	public function fullQtyForReceiving(Request $request)
    {
		$po_group_por_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$request->id)->get();
		foreach($po_group_por_details as $item)
		{
			if($item->quantity_inv == null || $item->quantity_inv == 0){
				$item->quantity_received_1 = 0;
			}
			else{
				$item->quantity_received_1 = $item->quantity_inv;
			}
			$item->save();
		}
		return response()->json(['success' => true]);
    }


    public function editPoGroupProductDetails(Request $request)
    {
    	// dd($request->all());
        $po_detail = PoGroupProductDetail::where('id',$request->p_g_p_d_id)->first();
        if($po_detail != null)
        {
        	$warehouse_id = $po_detail->to_warehouse_id != null ? $po_detail->to_warehouse_id : Auth::user()->warehouse_id;
        }
        else
        {
        	// return response()->json(['success' => true]);
        	$warehouse_id = 1;
        }
        foreach($request->except('p_g_p_d_id','po_group_id') as $key => $value)
        {
          	if($value == ''){
              // $supp_detail->$key = null;
          	}
          	elseif($key == 'quantity_received_1')
          	{
          		// if( $value > $po_detail->quantity ){
          		// 	return response()->json(['success' => false,'extra_quantity'=>$value-$po_detail->quantity]);
          		// }
          		if(true)
          		{
          			$group = PoGroup::find($request->po_group_id);
		            if($group->is_confirm == 1)
		            {
		            	if($po_detail->product->unit_conversion_rate == 0)
						{
							$u_c_r = 1;
						}
						else
						{
							$u_c_r = $po_detail->product->unit_conversion_rate;
						}
		            	$quantity_received_1 = $value - $po_detail->quantity_received_1;
		            	$quantity_received_1 = ($quantity_received_1/$u_c_r);
		            	$decimal_places = $po_detail->product->units->decimal_places;
                        if($decimal_places == 0)
                        {
                            $quantity_received_1 = round($quantity_received_1,0);
                        }
                        elseif($decimal_places == 1)
                        {
                            $quantity_received_1 = round($quantity_received_1,1);
                        }
                        elseif($decimal_places == 2)
                        {
                            $quantity_received_1 = round($quantity_received_1,2);
                        }
                        elseif($decimal_places == 3)
                        {
                            $quantity_received_1 = round($quantity_received_1,3);
                        }
                        else
                        {
                            $quantity_received_1 = round($quantity_received_1,4);
                        }
		            	$stock = StockManagementIn::where('expiration_date',$po_detail->expiration_date_1)->where('product_id',$po_detail->product_id)->where('warehouse_id',$warehouse_id)->first();
						if($stock != null)
		                {
							$stock_out               = new StockManagementOut;
							$stock_out->smi_id       = $stock->id;
							$stock_out->product_id   = $po_detail->product_id;
							$stock_out->po_group_id  = $po_detail->po_group_id;
							// $stock_out->order_id   = $po_detail->order_id;
							$stock_out->po_id   = $po_detail->po_id;
			                $stock_out->p_o_d_id   = $po_detail->pod_id;
			                $stock_out->supplier_id   = $po_detail->supplier_id;
		                    if($quantity_received_1 < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received_1;
		                        $stock_out->available_stock = $quantity_received_1;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received_1;
		                       $stock_out->available_stock  = $quantity_received_1;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    if($group->is_review == 1)
			                {
								$stock_out->cost      = $po_detail->product_cost;
		                	}else{
                                $stock_out->cost      = @$po_detail->product->selling_price;
                            }
							$stock_out->cost_date = Carbon::now();
		                    $stock_out->save();

		                    if($quantity_received_1 < 0)
                            {
                                $dummy_order = Order::createManualOrder($stock_out,'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now());
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
                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
                                                    }
                                                    else
                                                    {
                                                        $history_quantity = $out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id.',';
                                                        $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                        $out->available_stock = 0;

                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
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
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              else
                                              {
                                                  $history_quantity = $stock_out->available_stock;
                                                  $out->parent_id_in .= $out->id.',';
                                                  $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                  $stock_out->available_stock = 0;
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              $out->save();
                                              $stock_out->save();
                                      }
                                  }
                              }
                            }
		                }
		                else
		                {
		                	if($po_detail->expiration_date_1 == null)
		                	{
			                    $stock = StockManagementIn::where('product_id',$po_detail->product_id)->where('warehouse_id',$warehouse_id)->whereNull('expiration_date')->first();
		                	}

		                    if($stock == null)
		                    {
		                        $stock = new StockManagementIn;
		                    }

		                    $stock->title           = 'Adjustment';
		                    $stock->product_id      = $po_detail->product_id;
		                    $stock->created_by      = Auth::user()->id;
		                    $stock->warehouse_id    = $warehouse_id;
		                    $stock->expiration_date = $po_detail->expiration_date_1;
		                    $stock->save();

		                    $stock_out               = new StockManagementOut;
		                    $stock_out->smi_id       = $stock->id;
		                    $stock_out->product_id   = $po_detail->product_id;
		                    $stock_out->po_group_id  = $po_detail->po_group_id;
		                    // $stock_out->order_id   = $po_detail->order_id;
							$stock_out->po_id   = $po_detail->po_id;
			                $stock_out->p_o_d_id   = $po_detail->pod_id;
			                $stock_out->supplier_id   = $po_detail->supplier_id;
		                    if($quantity_received_1 < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received_1;
		                        $stock_out->available_stock = $quantity_received_1;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received_1;
		                       $stock_out->available_stock  = $quantity_received_1;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    if($group->is_review == 1)
			                {
								$stock_out->cost      = $po_detail->product_cost;
			                }else{
                                $stock_out->cost      = @$po_detail->product->selling_price;
                            }
							$stock_out->cost_date = Carbon::now();
		                    $stock_out->save();

		                    if($quantity_received_1 < 0)
                            {
                                $dummy_order = Order::createManualOrder($stock_out,'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now());
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
                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
                                                    }
                                                    else
                                                    {
                                                        $history_quantity = $out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id.',';
                                                        $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                        $out->available_stock = 0;
                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
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
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              else
                                              {
                                                  $history_quantity = $stock_out->available_stock;
                                                  $out->parent_id_in .= $out->id.',';
                                                  $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                  $stock_out->available_stock = 0;
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              $out->save();
                                              $stock_out->save();
                                      }
                                  }
                              }
                            }
		                }

						// $warehouse_products = WarehouseProduct::where('warehouse_id',$warehouse_id)->where('product_id',$po_detail->product_id)->first();
						// $warehouse_products->current_quantity += round($quantity_received_1,3);
						// $warehouse_products->available_quantity = round($warehouse_products->current_quantity,3) - round($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity,3);
						// $warehouse_products->save();

						DB::beginTransaction();
		              	try
		                {
		                  $new_his = new QuantityReservedHistory;
		                  $re      = $new_his->updateTDCurrentQuantity($po_detail,$po_detail,$quantity_received_1,'add');
		                  DB::commit();
		                }
		                catch(\Excepion $e)
		                {
		                  DB::rollBack();
		                }
		            }


		          	$params['term_key']  = $key;
		            $params['old_value'] = $po_detail->$key;
		            $params['new_value'] = $value;
		            $params['ip_address'] = $request->ip();
		            $this->saveProductReceivingHistory($params, $po_detail->id,$request->po_group_id);
		            $po_detail->$key = $value;
	        	}
	    	}
	    	elseif($key == 'quantity_received_2')
          	{
          		if(true)
          		{
          			$group = PoGroup::find($request->po_group_id);
		            if($group->is_confirm == 1)
		            {
		            	if($po_detail->product->unit_conversion_rate == 0)
						{
							$u_c_r = 1;
						}
						else
						{
							$u_c_r = $po_detail->product->unit_conversion_rate;
						}
		            	$quantity_received_2 = $value-$po_detail->quantity_received_2;
		            	$quantity_received_2 = ($quantity_received_2/$u_c_r);
		            	$decimal_places = $po_detail->product->units->decimal_places;
                        if($decimal_places == 0)
                        {
                            $quantity_received_2 = round($quantity_received_2,0);
                        }
                        elseif($decimal_places == 1)
                        {
                            $quantity_received_2 = round($quantity_received_2,1);
                        }
                        elseif($decimal_places == 2)
                        {
                            $quantity_received_2 = round($quantity_received_2,2);
                        }
                        elseif($decimal_places == 3)
                        {
                            $quantity_received_2 = round($quantity_received_2,3);
                        }
                        else
                        {
                            $quantity_received_2 = round($quantity_received_2,4);
                        }
		            	$stock = StockManagementIn::where('expiration_date',$po_detail->expiration_date_2)->where('product_id',$po_detail->product_id)->where('warehouse_id',$warehouse_id)->first();
						if($stock != null)
		                {
							$stock_out               = new StockManagementOut;
							$stock_out->smi_id       = $stock->id;
							$stock_out->product_id   = $po_detail->product_id;
							$stock_out->po_group_id  = $po_detail->po_group_id;
							// $stock_out->order_id   = @$po_detail->order_id;
							$stock_out->po_id   = @$po_detail->po_id;
			                $stock_out->p_o_d_id   = @$po_detail->pod_id;
			                $stock_out->supplier_id   = @$po_detail->supplier_id;
		                    if($quantity_received_2 < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received_2;
		                        $stock_out->available_stock = $quantity_received_2;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received_2;
		                       $stock_out->available_stock  = $quantity_received_2;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    if(@$group->is_review == 1)
			                {
								$stock_out->cost      = @$po_detail->product_cost;
			                }else{
                                $stock_out->cost      = @$po_detail->product->selling_price;
                            }
							$stock_out->cost_date = Carbon::now();
		                    $stock_out->save();

		                    if($quantity_received_2 < 0)
                            {
                                $dummy_order = Order::createManualOrder($stock_out,'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now());
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
                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
                                                    }
                                                    else
                                                    {
                                                        $history_quantity = $out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id.',';
                                                        $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                        $out->available_stock = 0;
                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
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
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              else
                                              {
                                                $history_quantity = $stock_out->available_stock;
                                                  $out->parent_id_in .= $out->id.',';
                                                  $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                  $stock_out->available_stock = 0;
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              $out->save();
                                              $stock_out->save();
                                      }
                                  }
                              }
                            }
		                }
		                else
		                {
		                	if($po_detail->expiration_date_2 == null)
		                	{
			                    $stock = StockManagementIn::where('product_id',$po_detail->product_id)->where('warehouse_id',$warehouse_id)->whereNull('expiration_date')->first();
		                	}

		                    if($stock == null)
		                    {
		                        $stock = new StockManagementIn;
		                    }

		                    $stock->title           = 'Adjustment';
		                    $stock->product_id      = $po_detail->product_id;
		                    $stock->created_by      = Auth::user()->id;
		                    $stock->warehouse_id    = $warehouse_id;
		                    $stock->expiration_date = $po_detail->expiration_date_2;
		                    $stock->save();

		                    $stock_out               = new StockManagementOut;
		                    $stock_out->smi_id       = $stock->id;
		                    $stock_out->product_id   = $po_detail->product_id;
		                    $stock_out->po_group_id  = $po_detail->po_group_id;
		                    // $stock_out->order_id   = @$po_detail->order_id;
							$stock_out->po_id   = @$po_detail->po_id;
			                $stock_out->p_o_d_id   = @$po_detail->pod_id;
			                $stock_out->supplier_id   = @$po_detail->supplier_id;
		                    if($quantity_received_2 < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received_2;
		                        $stock_out->available_stock = $quantity_received_2;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received_2;
		                       $stock_out->available_stock  = $quantity_received_2;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    if(@$group->is_review == 1)
			                {
								$stock_out->cost      = @$po_detail->product_cost;
			                }else{
                                $stock_out->cost      = @$po_detail->product->selling_price;
                            }
							$stock_out->cost_date = Carbon::now();
		                    $stock_out->save();

		                    if($quantity_received_2 < 0)
                            {
                                $dummy_order = Order::createManualOrder($stock_out,'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received 1 updated in shipment '.@$po_detail->po_group_id. ' by '.Auth::user()->user_name. ' on '. Carbon::now());
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
                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
                                                    }
                                                    else
                                                    {
                                                        $history_quantity = $out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id.',';
                                                        $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                        $out->available_stock = 0;
                                                        $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,round(abs($history_quantity),4));
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
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              else
                                              {
                                                $history_quantity = $stock_out->available_stock;
                                                  $out->parent_id_in .= $out->id.',';
                                                  $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                                  $stock_out->available_stock = 0;
                                                  $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,round(abs($history_quantity),4));
                                              }
                                              $out->save();
                                              $stock_out->save();
                                      }
                                  }
                              }
                            }
		                }

						// $warehouse_products = WarehouseProduct::where('warehouse_id',$warehouse_id)->where('product_id',$po_detail->product_id)->first();
						// $warehouse_products->current_quantity += round($quantity_received_2,3);
						// $warehouse_products->available_quantity = round($warehouse_products->current_quantity,3) - round($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity,3);
						// $warehouse_products->save();

						DB::beginTransaction();
		              	try
		                {
		                  $new_his = new QuantityReservedHistory;
		                  $re      = $new_his->updateTDCurrentQuantity($po_detail,$po_detail,$quantity_received_2,'add');
		                  DB::commit();
		                }
		                catch(\Excepion $e)
		                {
		                  DB::rollBack();
		                }
		            }


		          	$params['term_key']  = $key;
		            $params['old_value'] = $po_detail->$key;
		            $params['new_value'] = $value;
		            $params['ip_address'] = $request->ip();
		            $this->saveProductReceivingHistory($params, $po_detail->id,$request->po_group_id);
		            $po_detail->$key = $value;
	        	}
	    	}
	    	elseif($key == 'expiration_date_1')
            {
                $value = str_replace("/","-",$request->expiration_date_1);
                $value =  date('Y-m-d',strtotime($value));
                $params['term_key']  = $key;
                $params['old_value'] = $po_detail->$key;
                $params['new_value'] = $value;
                $params['ip_address'] = $request->ip();
                $this->saveProductReceivingHistory($params, $po_detail->id,$request->po_group_id);
                $po_detail->$key = $value;
            }
            elseif($key == 'expiration_date_2')
            {
                $value = str_replace("/","-",$request->expiration_date_2);
                $value =  date('Y-m-d',strtotime($value));
                $params['term_key']  = $key;
                $params['old_value'] = $po_detail->$key;
                $params['new_value'] = $value;
                $params['ip_address'] = $request->ip();
                $this->saveProductReceivingHistory($params, $po_detail->id,$request->po_group_id);
                $po_detail->$key = $value;
            }
            elseif($key == 'custom_invoice_number'){
                $po_group_custom_invoice = PoGroup::find($request->p_g_p_d_id);

                if($po_group_custom_invoice !== null)
                {
                    $po_group_custom_invoice->$key = $value;
                    $po_group_custom_invoice->save();
                    return response()->json(['custom_invoice_number' => true]);
                }
          	}
          	elseif($key == 'custom_line_number'){
            $po_detail->$key = $value;
          	}
	    	else
	    	{
            	$po_detail->$key = $value;
          	}
        }
        $po_detail->save();

        return response()->json(['success' => true]);
	}

	private function saveProductReceivingHistory($params = [], $p_g_p_d_id,$po_group_id)
	{
		$product_receiving_history              = new ProductReceivingHistory;
		$product_receiving_history->po_group_id = $po_group_id;
		$product_receiving_history->p_g_p_d_id  = $p_g_p_d_id;

        if($params['term_key'] == 'quantity_received_1' || $params['term_key'] == 'quantity_received_2' || $params['term_key'] == 'expiration_date_1' || $params['term_key'] == 'expiration_date_2'){
            $key =  ucwords(str_replace('_', ' ',$params['term_key']));
            $old_value  = $params['old_value'];
            $new_value  = $params['new_value'];
        }

		$product_receiving_history->term_key   = $key;
		$product_receiving_history->old_value  = $old_value;
		$product_receiving_history->new_value  = $new_value;
		$product_receiving_history->updated_by = Auth::user()->id;
		$product_receiving_history->ip_address = $params['ip_address'];
        $product_receiving_history->save();
    }

    public function savePoGroupGoodsData(Request $request)
	{
		// dd($request->all());
		$group_detail = PoGroupProductDetail::where('status',1)->where('id',$request->id)->first();
		if($request->value == 'normal' || $request->value == 'problem'){
			$group_detail->good_condition = $request->value;
		}
		else if($request->value == 'pass' || $request->value == 'fail'){
			$group_detail->result = $request->value;
		}
		else if($request->value == '1' || $request->value == '2' || $request->value == '3'|| $request->value == '4'){
			$group_detail->good_type = $request->value;
		}
		$group_detail->save();
		return response()->json(['success' => true]);
	}

	public function confirmPoGroupDetail(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $po_group = PoGroup::find($request->id);
            if($po_group->is_confirm == 1)
            {
                DB::commit();
                $errorMsg = "This group is already confirmed by a Warehouse staff !!!";
                return response()->json(['success' => false, 'errorMsg' => $errorMsg ]);
            }

            $purchase_orders_date_check = $po_group->purchase_orders;

            $globalAccessConfig3 = QuotationConfig::where('section','target_ship_date')->first();
            if($globalAccessConfig3 != NULL)
            {
                $targetShipDate = unserialize($globalAccessConfig3->print_prefrences);
            }
            else
            {
                $targetShipDate = NULL;
            }

            if($targetShipDate != NULL && $targetShipDate['target_ship_date'] == 1)
            {
                $pos_ref_ids = '';
                $is_terminate = 0;
                foreach ($purchase_orders_date_check as $po_check)
                {
                    if($po_check->target_receive_date == NULL)
                    {
                        $pos_ref_ids .= $po_check->ref_id.', ';
                        $is_terminate = 1;
                    }
                }

                if($is_terminate == 1)
                {
                    DB::commit();
                    $errorMsg = "Target Ship Date of these PO's ".$pos_ref_ids."Still not filled, Please Fill Target Ship Date First of these PO's !!!";
                    return response()->json(['success' => false, 'errorMsg' => $errorMsg ]);
                }
            }

            $confirm_from_draft = QuotationConfig::where('section','warehouse_management_page')->first();
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

            if($has_warehouse_account == 1)
            {
                $po_group_por_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$request->id)->get();
                foreach($po_group_por_details as $item)
                {
                    if($item->quantity_ordered == null || $item->quantity_ordered == 0)
                    {
                        $item->quantity_received_1 = 0;
                    }
                    else
                    {
                        $item->quantity_received_1 = $item->quantity_ordered;
                    }
                    $item->save();
                }
            }
            $po_group->is_confirm = 1;
            $po_group->save();
            $purchase_orders = $po_group->purchase_orders;
            foreach ($purchase_orders as $PO)
            {
                $PO->status = 15;
                $PO->confirm_date = date("Y-m-d");
                $PO->save();
                // PO status history maintaining
                $page_status = Status::select('title')->whereIn('id',[14,15])->pluck('title')->toArray();
                $poStatusHistory = new PurchaseOrderStatusHistory;
                $poStatusHistory->user_id    = Auth::user()->id;
                $poStatusHistory->po_id      = $PO->id;
                $poStatusHistory->status     = $page_status[0];
                $poStatusHistory->new_status = $page_status[1];
                $poStatusHistory->save();

                $p_o_ds = PurchaseOrderDetail::where('po_id',$PO->id)->whereNotNull('purchase_order_details.product_id')->get();
                foreach ($p_o_ds as $p_o_d)
                {
                    if($p_o_d->order_product_id != null)
                    {
                        $order_product = $p_o_d->order_product;
                        $order         = $order_product->get_order;
                        if($order->primary_status !== 3 && $order->primary_status !== 17 && $order->is_processing == 0)
                        {
                            $order_product->status = 10;
                            $order_product->save();

                            $order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('quantity','!=',0)->where('status','!=',10)->count();
                            if($order_products_status_count == 0)
                            {
                                $order->status = 10;
                                $order->save();
                                $order_history = new OrderStatusHistory;
                                $order_history->user_id = Auth::user()->id;
                                $order_history->order_id = @$order->id;
                                $order_history->status = 'DI(Importing)';
                                $order_history->new_status = 'DI(Waiting To Pick)';
                                $order_history->save();
                            }
                        }
                    }
                }
            }

            $po_group_product_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$request->id)->get();
            foreach ($po_group_product_details as $p_g_p_d)
            {
                if($p_g_p_d->product->unit_conversion_rate == 0)
                {
                    $u_c_r = 1;
                }
                else
                {
                    $u_c_r = $p_g_p_d->product->unit_conversion_rate;
                }
                $quantity_inv   = $p_g_p_d->quantity_received_1/$u_c_r;
                $quantity_inv_2 = $p_g_p_d->quantity_received_2/$u_c_r;
                $decimal_places = $p_g_p_d->product->sellingUnits->decimal_places;
                if($decimal_places == 0)
                {
                    $quantity_inv   = round($quantity_inv,0);
                    $quantity_inv_2 = round($quantity_inv_2,0);
                }
                elseif($decimal_places == 1)
                {
                    $quantity_inv   = round($quantity_inv,1);
                    $quantity_inv_2 = round($quantity_inv_2,1);
                }
                elseif($decimal_places == 2)
                {
                    $quantity_inv   = round($quantity_inv,2);
                    $quantity_inv_2 = round($quantity_inv_2,2);
                }
                elseif($decimal_places == 3)
                {
                    $quantity_inv   = round($quantity_inv,3);
                    $quantity_inv_2 = round($quantity_inv_2,3);
                }
                else
                {
                    $quantity_inv   = round($quantity_inv,4);
                    $quantity_inv_2 = round($quantity_inv_2,4);
                }

                if($quantity_inv !== null && $p_g_p_d->quantity_received_1 !== null)
                {
                    $stock = StockManagementIn::where('expiration_date',$p_g_p_d->expiration_date_1)->where('product_id',$p_g_p_d->product_id)->where('warehouse_id',$p_g_p_d->to_warehouse_id)->first();
                    if($stock != null)
                    {
                        $stock_out               = new StockManagementOut;
                        $stock_out->smi_id       = $stock->id;
                        $stock_out->po_group_id  = $p_g_p_d->po_group_id;
                        $stock_out->product_id   = $p_g_p_d->product_id;
                        // $stock_out->order_id   = $p_g_p_d->order_id;
                        $stock_out->po_id   = $p_g_p_d->po_id;
                        $stock_out->p_o_d_id   = $p_g_p_d->pod_id;
                        $stock_out->supplier_id   = $p_g_p_d->supplier_id;
                        if($quantity_inv < 0)
                        {
                            $stock_out->quantity_out = $quantity_inv;
                            $stock_out->available_stock = $quantity_inv;
                        }
                        else
                        {
                           $stock_out->quantity_in  = $quantity_inv;
                           $stock_out->available_stock  = $quantity_inv;
                        }
                        $stock_out->created_by   = Auth::user()->id;
                        $stock_out->warehouse_id = $p_g_p_d->to_warehouse_id;
                        if($po_group->is_review == 1)
                        {
                            $stock_out->cost      = $p_g_p_d->product_cost;
                            $stock_out->cost_date = Carbon::now();
                        }
                        $stock_out->save();

                        if($quantity_inv < 0)
                        {
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
                                            $stock_out->parent_id_in .= $out->id.',';
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $stock_out->available_stock = 0;
                                        }
                                        else
                                        {
                                            $stock_out->parent_id_in .= $out->id.',';
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                        else
                        {
                            $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->orderby('id','asc')->get();
                            if($find_stock->count() > 0)
                            {
                                foreach ($find_stock as $out) {

                                    if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
                                    {
                                        if($stock_out->available_stock >= abs($out->available_stock))
                                        {
                                            $history_quantity = $out->available_stock;
                                            $out->parent_id_in .= $stock_out->id.',';
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $out->available_stock = 0;

                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        else
                                        {
                                            $history_quantity = $stock_out->available_stock;
                                            $out->parent_id_in .= $out->id.',';
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $stock_out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if($p_g_p_d->expiration_date_1 == null)
                        {
                            $stock = StockManagementIn::where('product_id',$p_g_p_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->whereNull('expiration_date')->first();
                        }

                        if($stock == null)
                        {
                            $stock = new StockManagementIn;
                        }

                        $stock->title           = 'Adjustment';
                        $stock->product_id      = $p_g_p_d->product_id;
                        $stock->quantity_in     = $quantity_inv;
                        $stock->created_by      = Auth::user()->id;
                        $stock->warehouse_id    = $p_g_p_d->to_warehouse_id;
                        $stock->expiration_date = $p_g_p_d->expiration_date_1;
                        $stock->save();

                        $stock_out               = new StockManagementOut;
                        $stock_out->smi_id       = $stock->id;
                        $stock_out->po_group_id  = $p_g_p_d->po_group_id;
                        $stock_out->product_id   = $p_g_p_d->product_id;
                        // $stock_out->order_id   = $p_g_p_d->order_id;
                        $stock_out->po_id   = $p_g_p_d->po_id;
                        $stock_out->p_o_d_id   = $p_g_p_d->pod_id;
                        $stock_out->supplier_id   = $p_g_p_d->supplier_id;
                        if($quantity_inv < 0)
                        {
                            $stock_out->quantity_out = $quantity_inv;
                            $stock_out->available_stock = $quantity_inv;
                        }
                        else
                        {
                           $stock_out->quantity_in  = $quantity_inv;
                           $stock_out->available_stock  = $quantity_inv;
                        }
                        $stock_out->created_by   = Auth::user()->id;
                        $stock_out->warehouse_id = $p_g_p_d->to_warehouse_id;
                        if($po_group->is_review == 1)
                        {
                            $stock_out->cost      = $p_g_p_d->product_cost;
                            $stock_out->cost_date = Carbon::now();
                        }
                        $stock_out->save();

                        if($quantity_inv < 0)
                        {
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
                                            $stock_out->parent_id_in .= $out->id.',';
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $stock_out->available_stock = 0;
                                        }
                                        else
                                        {
                                            $stock_out->parent_id_in .= $out->id.',';
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;
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
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        else
                                        {
                                            $history_quantity = $stock_out->available_stock;
                                            $out->parent_id_in .= $out->id.',';
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $stock_out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                    }

                    // $warehouse_products = WarehouseProduct::where('warehouse_id',$p_g_p_d->to_warehouse_id)->where('product_id',$p_g_p_d->product_id)->first();
                    // $warehouse_products->current_quantity += round($quantity_inv,3);
                    // $warehouse_products->available_quantity = round($warehouse_products->current_quantity,3) - round($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity,3);
                    // $warehouse_products->save();

                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateTDCurrentQuantity($p_g_p_d,$p_g_p_d,$quantity_inv,'add');
                }
                if($quantity_inv_2 !== null && $p_g_p_d->quantity_received_2 !== null)
                {
                    $stock = StockManagementIn::where('expiration_date',$p_g_p_d->expiration_date_2)->where('product_id',$p_g_p_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->first();
                    if($stock != null)
                    {
                        $stock_out               = new StockManagementOut;
                        $stock_out->smi_id       = $stock->id;
                        $stock_out->po_group_id  = $p_g_p_d->po_group_id;
                        $stock_out->product_id   = $p_g_p_d->product_id;
                        // $stock_out->order_id   = $p_g_p_d->order_id;
                        $stock_out->po_id   = $p_g_p_d->po_id;
                        $stock_out->p_o_d_id   = $p_g_p_d->pod_id;
                        $stock_out->supplier_id   = $p_g_p_d->supplier_id;
                        if($quantity_inv_2 < 0)
                        {
                            $stock_out->quantity_out = $quantity_inv_2;
                            $stock_out->available_stock = $quantity_inv_2;
                        }
                        else
                        {
                           $stock_out->quantity_in  = $quantity_inv_2;
                           $stock_out->available_stock  = $quantity_inv_2;
                        }
                        $stock_out->created_by   = Auth::user()->id;
                        $stock_out->warehouse_id = $p_g_p_d->to_warehouse_id;
                        if($po_group->is_review == 1)
                        {
                            $stock_out->cost      = $p_g_p_d->product_cost;
                            $stock_out->cost_date = Carbon::now();
                        }
                        $stock_out->save();

                        if($quantity_inv_2 < 0)
                        {
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
                                            $stock_out->parent_id_in .= $out->id.',';
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $stock_out->available_stock = 0;
                                        }
                                        else
                                        {
                                            $stock_out->parent_id_in .= $out->id.',';
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;
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
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        else
                                        {
                                            $history_quantity = $stock_out->available_stock;
                                            $out->parent_id_in .= $out->id.',';
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $stock_out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        if($p_g_p_d->expiration_date_2 == null)
                        {
                            $stock = StockManagementIn::where('product_id',$p_g_p_d->product_id)->where('warehouse_id',$p_g_p_d->to_warehouse_id)->whereNull('expiration_date')->first();
                        }

                        if($stock == null)
                        {
                            $stock = new StockManagementIn;
                        }

                        $stock->title           = 'Adjustment';
                        $stock->product_id      = $p_g_p_d->product_id;
                        $stock->quantity_in     = $quantity_inv_2;
                        $stock->created_by      = Auth::user()->id;
                        $stock->warehouse_id    = $PO->to_warehouse_id;
                        $stock->expiration_date = $p_g_p_d->expiration_date_2;
                        $stock->save();

                        $stock_out               = new StockManagementOut;
                        $stock_out->smi_id       = $stock->id;
                        $stock_out->po_group_id  = $p_g_p_d->po_group_id;
                        $stock_out->product_id   = $p_g_p_d->product_id;
                        // $stock_out->order_id   = $p_g_p_d->order_id;
                        $stock_out->po_id   = $p_g_p_d->po_id;
                        $stock_out->p_o_d_id   = $p_g_p_d->pod_id;
                        $stock_out->supplier_id   = $p_g_p_d->supplier_id;
                        if($quantity_inv_2 < 0)
                        {
                            $stock_out->quantity_out = $quantity_inv_2;
                            $stock_out->available_stock = $quantity_inv_2;
                        }
                        else
                        {
                           $stock_out->quantity_in  = $quantity_inv_2;
                           $stock_out->available_stock  = $quantity_inv_2;
                        }
                        $stock_out->created_by   = Auth::user()->id;
                        $stock_out->warehouse_id = $p_g_p_d->to_warehouse_id;
                        if($po_group->is_review == 1)
                        {
                            $stock_out->cost      = $p_g_p_d->product_cost;
                            $stock_out->cost_date = Carbon::now();
                        }
                        $stock_out->save();

                        if($quantity_inv_2 < 0)
                        {
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
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $stock_out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        else
                                        {
                                            $history_quantity = $out->available_stock;
                                            $stock_out->parent_id_in .= $out->id.',';
                                            $stock_out->supplier_id = $out->supplier_id;
                                            $stock_out->po_id = $out->po_id;
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
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
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        else
                                        {
                                            $history_quantity = $out->available_stock;
                                            $out->parent_id_in .= $out->id.',';
                                            $out->supplier_id = $stock_out->supplier_id;
                                            $out->po_id = $stock_out->po_id;
                                            $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $stock_out->available_stock = 0;
                                            $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_g_p_d,round(abs($history_quantity),4));
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                    }

                    // $warehouse_products = WarehouseProduct::where('warehouse_id',$p_g_p_d->to_warehouse_id)->where('product_id',$p_g_p_d->product_id)->first();
                    // $warehouse_products->current_quantity += round($quantity_inv_2,3);
                    // $warehouse_products->available_quantity = round($warehouse_products->current_quantity,3) - round($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity,3);
                    // $warehouse_products->save();

                    $new_his = new QuantityReservedHistory;
                    $re      = $new_his->updateTDCurrentQuantity($p_g_p_d,$p_g_p_d,$quantity_inv_2,'add');
                }
            }

            $group_status_history              = new PoGroupStatusHistory;
            $group_status_history->user_id     = Auth::user()->id;
            $group_status_history->po_group_id = @$po_group->id;
            $group_status_history->status      = 'Confirmed';
            $group_status_history->new_status  = 'Closed Product Receiving Queue';
            $group_status_history->save();
            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (\Exception $e)
        {
            DB::rollback();
            dd($e);
        }
    }

	public function completeReceivingQueueDetail($id)
    {
		$po_group = PoGroup::find($id);
		$status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();

		#to find the po in the group
       	$pos = PoGroupDetail::where('po_group_id',$po_group->id)->pluck('purchase_order_id')->toArray();
       	$pos_supplier_invoice_no = PurchaseOrder::select('id','invoice_number')->whereNotNull('invoice_number')->whereIn('id',$pos)->get();

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

        return $this->render('warehouse.po-groups.complete-products-receiving',compact('po_group','id','product_receiving_history','status_history','pos_supplier_invoice_no','allow_custom_invoice_number','show_custom_line_number','show_supplier_invoice_number'));
	}

	public function getCompletedPoGroupProductDetails(Request $request, $id)
    {
        $all_record = PoGroupProductDetail::where('po_group_product_details.po_group_id',$id);
        $all_record = $all_record->with('product.supplier_products','get_supplier','product.units','product.sellingUnits', 'purchase_order', 'order.user.get_warehouse', 'order.customer', 'get_good_type')->select('po_group_product_details.*');
        $all_record = POGroupSortingHelper::WarehouseProductRecevingRecordsSorting($request, $all_record);
		$goods_types = ProductType::all();
		return Datatables::of($all_record)

		->addColumn('occurrence',function($item){
            return $item->occurrence;
        })

	    ->addColumn('po_number',function($item){
            $occurrence = $item->occurrence;
            if($occurrence == 1)
            {
                $po = $item->purchase_order;
                if($po != null && $po->ref_id !== null){
                    $html_string = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $po->id]).'" title="View Detail"><b>'.$po->ref_id.'</b></a>';
                    return $html_string;
                }
                else{
                    return "--";
                }
            }
            else
            {
            	return '--';
            }
        })

	    ->addColumn('order_warehouse',function($item){
            $occurrence = $item->occurrence;
            if($occurrence == 1)
            {
                $order = $item->order;
                return $order !== null ? $order->user->get_warehouse->warehouse_title : "N.A" ;
            }
            else
            {
                return '--';
            }
        })

	    ->addColumn('order_no',function($item){
            $occurrence = $item->occurrence;
            if($occurrence == 1)
            {
                $order = $item->order;
                if ($order != null) {
                   $ret = $order->get_order_number_and_link($order);
                    $ref_no = $ret[0];
                    $html_string = '<a target="_blank" href="'.route('pick-instruction', ['id' => $order->id]).'" title="View Detail"><b>'.$ref_no.'</b></a>';
                    return $html_string;
                }
                else{
                    return "N.A";
                }
            }
            else
            {
                return '--';
            }
        })

		->addColumn('supplier',function($item){
			return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier_id).'"><b>'.$item->get_supplier->reference_name.'</b></a>';
        })

		->filterColumn('supplier', function( $query, $keyword ) {
            $query = $query->whereIn('supplier_id', Supplier::select('id')->where('reference_name','LIKE',"%$keyword%")->pluck('id'));
        },true )

	    ->addColumn('reference_number',function($item){
            $sup_name = $item->product->supplier_products->first();
            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
        })

		->filterColumn('reference_number', function( $query, $keyword ) {
            $query = $query->whereIn('supplier_id', SupplierProducts::select('supplier_id')->where('product_supplier_reference_no','LIKE',"%$keyword%")->pluck('supplier_id'))->whereIn('product_id', SupplierProducts::select('product_id')->where('product_supplier_reference_no','LIKE',"%$keyword%")->pluck('product_id'));
        },true )

	    ->addColumn('prod_reference_number',function($item){
            return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"><b>'.$item->product->refrence_code.'</b></a>';
        })

	    ->filterColumn('prod_reference_number', function( $query, $keyword ) {
            $query = $query->whereIn('product_id', Product::select('id')->where('refrence_code','LIKE',"%$keyword%")->pluck('id'));
        },true )

	    ->addColumn('desc',function($item){
		    return $item->product_id != null ? $item->product->short_desc : '' ;
        })

	    ->filterColumn('desc', function( $query, $keyword ) {
            $query = $query->whereIn('product_id', Product::select('id')->where('short_desc','LIKE',"%$keyword%")->pluck('id'));
        },true )

	    ->addColumn('customer',function($item){
            $occurrence = $item->occurrence;
            if($occurrence == 1)
            {
                $order = $item->order;
				if($order !== null){

              	$html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.$order->customer_id).'"><b>'.$order->customer->reference_name.'</b></a>';
              	return $html_string;

				}
				else{
					return "N.A";
				}
            }
            else
            {
            	return '--';
            }
        })

	    ->addColumn('unit',function($item){
			return $item->product->units->title != null ? $item->product->units->title : '';
        })

	    ->addColumn('qty_ordered',function($item){
	    	return round($item->quantity_ordered,3).' '.@$item->product->sellingUnits->title;;
	    })

	    ->addColumn('qty_inv',function($item){
	    	return round($item->quantity_inv,3);
	    })

	    ->addColumn('qty_receive',function($item){
	    	$quantity_received_1 = $item->quantity_received_1 != null ? $item->quantity_received_1 : '' ;

			$html_string = '<input type="number"  name="quantity_received_1" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_1.'" class="fieldFocus" value="'. $quantity_received_1.'" style="width:100%">';
	    	return $html_string;
	    })

	    ->addColumn('expiration_date',function($item){
			$expiration_date_1 = $item->expiration_date_1 !== null ? Carbon::parse($item->expiration_date_1)->format('d/m/Y') : '';

			$html_string = '<input type="text" id="expiration_date_1" name="expiration_date_1" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date_1.'" style="width:100%" data-fieldvalue="'.$expiration_date_1.'">';
	    	return $html_string;
		})

	    ->addColumn('quantity_received_2',function($item){
	    	$quantity_received_2 = $item->quantity_received_2 != null ? $item->quantity_received_2 : '' ;

			$html_string = '<input type="number"  name="quantity_received_2" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_2.'" class="fieldFocus" value="'. $quantity_received_2.'" style="width:100%">';
	    	return $html_string;
	    })

	    ->addColumn('expiration_date_2',function($item){
			$expiration_date_2 = $item->expiration_date_2 !== null ? Carbon::parse($item->expiration_date_2)->format('d/m/Y') : '';
			$html_string = '<input type="text" id="expiration_date_2" name="expiration_date_2" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date_2.'" style="width:100%" data-fieldvalue="'.$expiration_date_2.'">';
	    	return $html_string;
		})

	    ->addColumn('goods_condition',function($item){
			$check = $item->good_condition;
	    	return $check;
		})

	    ->addColumn('results',function($item){
			$check = $item->result;
	    	return $check;
	    })

	    ->addColumn('goods_type',function($item){
			return $item->get_good_type->title;
		})

		->addColumn('goods_temp',function($item){
			$goods_temp = $item->temperature_c != null ? $item->temperature_c :'N.A';
	    	return $goods_temp;
		})
		->addColumn('checker',function($item){
			$checker = $item->checker != null ? $item->checker :'N.A';
	    	return $checker;
	    })
	    ->addColumn('problem_found',function($item){
			$problem_found = $item->problem_found != null ? $item->problem_found :'N.A';
	    	return $problem_found;
		})
		->addColumn('solution',function($item){
			$solution = $item->solution != null ? $item->solution :'N.A';
	    	return $solution;
		})
		->addColumn('changes',function($item){
			$authorized_changes = $item->authorized_changes != null ? $item->authorized_changes :'N.A';
	    	return $authorized_changes;
		})

		 ->addColumn('custom_line_number',function($item){
            $html_string = '<input type="text"  name="custom_line_number" data-id="'.$item->id.'" data-fieldvalue="'.@$item->custom_line_number.'" class="fieldFocus" value="'.@$item->custom_line_number.'" style="width:100%">';
            return $html_string;
        })
        ->setRowClass(function ($item) {
        	return $item->status == 0 ? 'yellowRow' : '';
        })

	    ->rawColumns(['po_number','order_no','supplier','reference_number','prod_reference_number','desc','customer','unit','qty_ordered','qty_inv','qty_receive','quantity_received_2','goods_condition','results','goods_type','goods_temp','checker','problem_found','solution','changes','expiration_date','expiration_date_2','custom_line_number'])

	    ->make(true);
    }

    public function getPoGroupEveryProductDetails(Request $request)
    {
    	$all_ids = PurchaseOrder::where('po_group_id',$request->group_id)->where('supplier_id',$request->supplier_id)->pluck('id');
    	$all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$request->product_id)->with('product','PurchaseOrder','getOrder','product.units','getOrder.user','getOrder.customer');

		return Datatables::of($all_record)

		->addColumn('po_no',function($item){
		   if($item->PurchaseOrder->ref_id !== null){
			$html_string = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $item->PurchaseOrder->id]).'" title="View Detail"><b>'.$item->PurchaseOrder->ref_id.'</b></a>';
			return $html_string;
			}
			else{
				return "--";
			}
        })

		->addColumn('order_warehouse',function($item){
		    $order = $item->getOrder;
			return $order !== null ? $order->user->get_warehouse->warehouse_title : "--" ;
		})

		->addColumn('order_no',function($item){
    		$order = $item->getOrder;
			if($order !== null){
				$ret = $order->get_order_number_and_link($order);
                $ref_no = $ret[0];

				$html_string = '<a target="_blank" href="'.route('pick-instruction', ['id' => $order->id]).'" title="View Detail"><b>'.$ref_no.'</b></a>';
				return $html_string;
			}
			else{
				return "--";
			}
        })

        ->addColumn('supplier_ref_name',function($item){
			if($item->PurchaseOrder->supplier_id !== NULL)
            {
				$sup_name = Supplier::where('id',$item->PurchaseOrder->supplier_id)->first();
				return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->PurchaseOrder->supplier_id).'"><b>'.$sup_name->reference_name.'</b></a>';
			}
            else
            {
                $sup_name = Warehouse::where('id',$item->PurchaseOrder->from_warehouse_id)->first();
                return  $html_string = $sup_name->warehouse_title;
            }
	    })

	    ->addColumn('supplier_ref_no',function($item){
	    	if($item->PurchaseOrder->supplier_id !== NULL)
	    	{
	    		$sup_name = SupplierProducts::where('supplier_id',$item->PurchaseOrder->supplier_id)->where('product_id',$item->product_id)->first();
	            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
	        }
	        else
	        {
	        	return "N.A";
	        }

	    })

	    ->addColumn('product_ref_no',function($item){
            return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'"><b>'.$item->product->refrence_code.'</b></a>';
        })

        ->addColumn('short_desc',function($item){
		    return $item->product->short_desc != null ? $item->product->short_desc : '' ;
        })

        ->addColumn('buying_unit',function($item){
			return $item->product->units->title != null ? $item->product->units->title : '';

        })

        ->addColumn('quantity_ordered',function($item){
    	if($item->order_product_id != null)
    	{
	    	return $item->order_product->quantity;
    	}
    	else
    	{
    		return '--';
    	}
    	})

    	->addColumn('customer',function($item){

            $order = Order::find($item->order_id);
            if($order !== null){

            $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.$order->customer_id).'"><b>'.$order->customer->reference_name.'</b></a>';
            return $html_string;

            }else{
                return "N.A";
            }

        })

    	->addColumn('quantity_inv',function($item){
	    	return $item->quantity;
	    })

	    ->addColumn('empty_col',function($item){
	    	return '--';
	    })


	    ->rawColumns(['po_no','order_no','supplier_ref_name','product_ref_no','empty_col','customer'])
		->make(true);
    }

    public function exportProductReceivingRecord(Request $request)
    {
      	$query = PoGroupProductDetail::where('po_group_product_details.status',1)->where('po_group_product_details.po_group_id',$request->id);

        if($request->supplier_id != null)
        {
            $query = $query->where('po_group_product_details.supplier_id',$request->supplier_id);
        }

        if($request->product_id != null)
        {
            $query = $query->where('po_group_product_details.product_id',$request->product_id);
        }
        $query = $query->with('product.supplier_products','get_supplier','product.units','product.sellingUnits', 'purchase_order', 'order.user.get_warehouse', 'get_warehouse', 'order.customer')->select('po_group_product_details.*');
        $query = POGroupSortingHelper::WarehouseProductRecevingRecordsSorting($request, $query);
		$query = $query->get();
		$table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'open_product_receiving')->first();
		if($table_hide_columns == NULL)
		{
			$not_in_arr = [];
		}
		else{
		$not_in_arr = explode(',', $table_hide_columns->hide_columns);
		}
        $group_no = $query->first()->po_group->ref_id;
       \Excel::store(new ProductReceivingRecord($query,$not_in_arr), 'Group No '.@$group_no.'.xlsx');
        return response()->json(['success' => true]);

    }

    public function getGroupRevertedPos(Request $request)
    {
        $query = RevertedPurchaseOrder::with('supplier','product','po_group','PurchaseOrder')->where('group_id',$request->id)->orderBy('id','DESC');
        return Datatables::of($query)
        ->addColumn('po',function($item){
            return $item->PurchaseOrder != null ? $item->PurchaseOrder->ref_id : '--';
        })
        ->addColumn('group',function($item){
            return $item->po_group != null ? $item->po_group->ref_id : '--';
        })
        ->addColumn('product',function($item){
            return $item->product != null ? $item->product->refrence_code : '--';
        })
        ->addColumn('supplier',function($item){
            return $item->supplier != null ? $item->supplier->reference_name : '--';
        })
        ->addColumn('quantity',function($item){
             return $item->quantity != null ? $item->quantity : '--';
        })
        ->addColumn('total_quantity',function($item){
             return $item->total_received != null ? $item->total_received : '--';
        })
        ->make(true);

    }

}
