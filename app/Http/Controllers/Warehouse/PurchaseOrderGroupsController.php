<?php

namespace App\Http\Controllers\Warehouse;
use DB;
use PDF;
use Auth;
use Excel;
use App\User;
use App\General;
use App\Variable;
use Carbon\Carbon;
use App\PdfsStatus;
use App\Notification;
use App\QuotationConfig;
use App\Models\Common\Brand;
use Illuminate\Http\Request;
use App\Models\Common\Status;
use App\Models\Common\PoGroup;
use App\Models\Common\Product;
use App\Models\Common\Supplier;
use App\Models\Common\Warehouse;
use Yajra\Datatables\Datatables;
use App\Jobs\ExportGroupToPDFJob;
use App\Models\Common\Order\Order;
use App\Models\Common\ProductType;
use App\Models\Common\ProductImage;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use App\Models\Common\PoGroupDetail;
use Illuminate\Support\Facades\View;
use App\Helpers\POGroupSortingHelper;
use App\Models\Common\ProductCategory;
use App\Models\Common\StockOutHistory;
use App\Models\Common\TableHideColumn;
use Illuminate\Support\Facades\Schema;
use App\Helpers\TransferDocumentHelper;
use App\Imports\ProductQtyInBulkImport;
use App\Models\Common\SupplierProducts;
use App\Models\Common\WarehouseProduct;
use App\Helpers\QuantityReservedHistory;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\StockManagementIn;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\StockManagementOut;
use App\TransferDocumentReservedQuantity;
use App\Models\Common\PoGroupProductDetail;
use App\Exports\FilteredStockProductsExport;
use App\Models\Common\ProductReceivingHistory;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
use App\Http\Controllers\Importing\saveProductReceivingHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory;
use App\Helpers\Datatables\TransferDocumentReceivingQueueDatatable;

class PurchaseOrderGroupsController extends Controller
{
	protected $dummy_data;
	public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
			$this->user= Auth::user();

			return $next($request);
		});
		if(Auth::user()){
            $this->dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at','desc')->get();
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
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data'=>$this->dummy_data]);
    }
    public function inCompletedPoGroups()
    {
        return $this->render('warehouse.home.incompleted-groups');
    }

    public function inCompletedTransferGroups()
    {
        return $this->render('warehouse.home.incompleted-transfer-groups');
    }

    public function getWarehouseInCompletedTDGroupsData(Request $request)
    {
        // dd($request->all());
    	// $today = Carbon::now();
		// $today = date('Y-m-d',strtotime("+3 days"));

    	$is_con = $request->dosortby;

        $query = PoGroup::with('po_group_detail.purchase_order.PoSupplier', 'po_group_detail.purchase_order.PoWarehouse', 'ToWarehouse')->where('po_groups.is_confirm',$is_con)->select('po_groups.*');

		if(Auth::user()->role_id != 2 && Auth::user()->role_id != 1 && Auth::user()->role_id != 11)
		{
    		$query = $query->where('po_groups.warehouse_id',Auth::user()->get_warehouse->id);
		}

        // $query =$query->where('po_groups.from_warehouse_id','!=',NULL)->where('po_groups.target_receive_date','<=',$today);
        $query =$query->where('po_groups.from_warehouse_id','!=',NULL)->where('po_groups.target_receive_date','>=','2022-01-01');

        if($request->from_date != null)
        {
        	$date = str_replace("/","-",$request->from_date);
            $date =  date('Y-d-m',strtotime($date));
           	$query->whereDate('created_at', '>=', $date);
        }
        if($request->to_date != null)
        {
        	$date = str_replace("/","-",$request->to_date);
            $date =  date('Y-d-m',strtotime($date));
           	$query->whereDate('created_at', '<=', $date);
        }
        $query = POGroupSortingHelper::TransferReceivingQueueSorting($request, $query);
        $dt = Datatables::of($query);
        $add_columns = ['warehouse', 'target_receive_date', 'po_total', 'issue_date', 'net_weight', 'quantity', 'supplier_ref_no', 'po_number', 'id'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return TransferDocumentReceivingQueueDatatable::returnAddColumn($column, $item);
            });
        }

        $add_columns = ['warehouse', 'target_receive_date', 'po_total', 'issue_date', 'net_weight', 'quantity', 'supplier_ref_no', 'po_number', 'id'];
        foreach ($add_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return TransferDocumentReceivingQueueDatatable::returnFilterColumn($column, $item, $keyword);
            });
        }



        	// ->addColumn('id', function($item){
			// 	// $r_id = $item->ref_id != NULL ? $item->ref_id : "N.A" ;
			// 	if($item->ref_id != NULL){
            //         if ($item->is_confirm == 0) {
            //             $html_string = '<a href="'.url('warehouse/transfer-warehouse-products-receiving-queue', $item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id.'</b></a>';
            //             return $html_string;
            //         } else {
            //             $html_string = '<a href="'.url('warehouse/warehouse-complete-transfer-products-receiving-queue', $item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id .'</b></a>';
            //             return $html_string;
            //         }
			// 	}
			// 	else{
			// 		return "N.A";
			// 	}
            // })

		    // ->addColumn('po_number',function($item){
		    // 	$i = 1;
		    // 	$po_group_detail = $item->po_group_detail;
		    // 	$po_id = [];
		    // 	foreach ($po_group_detail as $p_g_d)
		    // 	{
		    // 		array_push($po_id, $p_g_d->purchase_order->ref_id);
		    // 	}
		    // 	sort($po_id);
		    //     // return $item->po_group_detail !== null ? $po_id : "--" ;
		    // 	if(sizeof($po_id) > 1)
		    // 	{
			//         $html_string = '
			//         	<a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal'.$item->id.'">
			// 			  <i class="fa fa-tty"></i>
			// 			</a>
			//         ';

			//         $html_string .= '
			// 		<div class="modal fade" id="poNumberModal'.$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			// 		  <div class="modal-dialog" role="document">
			// 		    <div class="modal-content">
			// 		      <div class="modal-header">
			// 		        <h5 class="modal-title" id="exampleModalLabel">PO Numbers</h5>
			// 		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			// 		          <span aria-hidden="true">&times;</span>
			// 		        </button>
			// 		      </div>
			// 		      <div class="modal-body">
			// 		      <table class="bordered" style="width:100%;">
			// 		      		<thead style="border:1px solid #eee;text-align:center;">
			// 		      			<tr><th>S.No</th><th>PO No.</th></tr>
			// 		      		</thead>
			// 		      		<tbody>';
			// 		      		foreach ($po_group_detail as $p_g_d) {
			//     		$html_string .= '<tr><td>'.$i.'</td><td>'.@ $p_g_d->purchase_order->ref_id.'</td></tr>';
			//     		$i++;
			//     	}
			// 		  $html_string .= '
			// 		      		</tbody>
			// 		      </table>

			// 		      </div>
			// 		    </div>
			// 		  </div>
			// 		</div>
			//         ';
			//         return $html_string;
		    //     }
		    //     else
		    //     {
		    //     	return $item->po_group_detail !== null ? $po_id : "--" ;
		    //     }

		    //     })

		    // ->addColumn('supplier_ref_no',function($item){
		    // 		$i = 1;
		    //         $po_group_detail = $item->po_group_detail;
			//         // return $item->po_group_detail !== null ? $supplier : "--" ;
			//     	if($po_group_detail->count() > 1 )
			//     	{

			// 	        $html_string = '
			// 	        	<a href="javascript:void(0)" data-toggle="modal" data-target="#Supplier'.$item->id.'">
			// 				  <i class="fa fa-user-plus"></i>
			// 				</a>
			// 	        ';

			// 	        $html_string .= '
			// 			<div class="modal fade" id="Supplier'.@$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			// 			  <div class="modal-dialog" role="document">
			// 			    <div class="modal-content">
			// 			      <div class="modal-header">
			// 			        <h5 class="modal-title" id="exampleModalLabel">Supplier(s)</h5>
			// 			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			// 			          <span aria-hidden="true">&times;</span>
			// 			        </button>
			// 			      </div>
			// 			      <div class="modal-body">
			// 			      <table class="bordered" style="width:100%;">
			// 			      		<thead style="border:1px solid #eee;text-align:center;">
			// 			      			<tr><th>S.No</th><th>Supplier #</th><th>Supplier Reference Name</th></tr>
			// 			      		</thead>
			// 			      		<tbody>';
			// 			      		foreach ($po_group_detail as $p_g_d)
			// 			      		{
			// 			      			if($p_g_d->purchase_order->supplier_id != null)
			// 			      			{
			// 			      				$ref_no = $p_g_d->purchase_order->PoSupplier->reference_number;
			// 			      				$name = $p_g_d->purchase_order->PoSupplier->reference_name;
			// 			      			}
			// 			      			else
			// 			      			{
			// 			      				$ref_no = $p_g_d->purchase_order->PoWarehouse->location_code;
			// 			      				$name = $p_g_d->purchase_order->PoWarehouse->warehouse_title;
			// 			      			}
			// 	    					$html_string .= '<tr><td>'.$i.'</td><td>'.@$ref_no.'</td><td>'.@$name.'</td></tr>';
			// 	    					$i++;
			// 	    				}
			// 			  			$html_string .= '
			// 			      		</tbody>
			// 			      </table>

			// 			      </div>
			// 			    </div>
			// 			  </div>
			// 			</div>
			// 	        ';
			// 	        return $html_string;
			// 	    }
			// 	    else
			// 	    {
			// 	    	if($item->po_group_detail[0]->purchase_order->supplier_id != null)
		    //   			{
		    //   				return $item->po_group_detail[0]->purchase_order->PoSupplier->reference_name;
		    //   			}
		    //   			else
		    //   			{
		    //   				return $item->po_group_detail[0]->purchase_order->PoWarehouse->warehouse_title;
		    //   			}
			// 	    }
		    //     })

		    // ->addColumn('quantity',function($item){
		    // 	$po_group_detail = $item->po_group_detail;
			//     $total_quantity = null;
			//     foreach ($po_group_detail as $p_g_d) {
			//     	$total_quantity += $p_g_d->purchase_order->total_quantity;
			//     }
			//     return $total_quantity ;
			// })

		    // ->addColumn('net_weight',function($item){
		    // 	$po_group_detail = $item->po_group_detail;
		    // 	$weight = null;
		    // 	foreach ($po_group_detail as $p_g_d) {
		    // 		$weight += $p_g_d->purchase_order->total_gross_weight;
		    // 	}
		    //     return $weight ;
		    // })

		    // ->addColumn('issue_date',function($item){
		    // 	$created_at = Carbon::parse($item->created_at)->format('d/m/Y');
		    // 	return $created_at;
		    // })

		    // ->addColumn('po_total',function($item){
		    // 	$po_group_detail = $item->po_group_detail;
			//     	$total = null;
			//     	foreach ($po_group_detail as $p_g_d) {
			//     		$total += $p_g_d->purchase_order->total_in_thb;
			//     	}
			//         return number_format($total,2,'.',',') ;
		    // })

		    // ->addColumn('target_receive_date',function($item){
		    //     return $item->target_receive_date !== null ? $item->target_receive_date: "--" ;
		    // })

            // ->addColumn('warehouse',function($item){
		    //         return $item->ToWarehouse !== null ? $item->ToWarehouse->warehouse_title: "--" ;
		    //     })



            $dt->rawColumns(['po_number','bill_of_lading','airway_bill','tax','freight','landing','bl_awb','courier','vendor','vendor_ref_no','supplier_ref_no','id']);

		    return $dt->make(true);
    }

    public function getWarehouseInCompletedPoGroupsData(Request $request)
    {
        // dd($request->all());
    	$today = Carbon::now();
		$today = date('Y-m-d',strtotime("+3 days"));
    	$is_con = $request->dosortby;
        $query = PoGroup::where('is_confirm',$is_con)->where('warehouse_id',Auth::user()->get_warehouse->id)->where('from_warehouse_id',NULL)->where('target_receive_date','<',$today)->orderBy('id', 'DESC');

        if($request->from_date != null)
        {
           $query->where('target_receive_date', '>=', $request->from_date);
        }
        if($request->to_date != null)
        {
           $query->where('target_receive_date', '<=', $request->to_date);
        }

        return Datatables::of($query)

        	->addColumn('id', function($item){
                return $item->ref_id != NULL ? $item->ref_id : "N.A" ;
            })

		    ->addColumn('po_number',function($item){
		    	$i = 1;
		    	$po_group_detail = $item->po_group_detail;
		    	$po_id = [];
		    	foreach ($po_group_detail as $p_g_d)
		    	{
		    		array_push($po_id, $p_g_d->purchase_order->ref_id);
		    	}
		    	sort($po_id);
		        // return $item->po_group_detail !== null ? $po_id : "--" ;
		    	if(sizeof($po_id) > 1)
		    	{
			        $html_string = '
			        	<a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal'.$item->id.'">
						  <i class="fa fa-tty"></i>
						</a>
			        ';

			        $html_string .= '
					<div class="modal fade" id="poNumberModal'.$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					  <div class="modal-dialog" role="document">
					    <div class="modal-content">
					      <div class="modal-header">
					        <h5 class="modal-title" id="exampleModalLabel">PO Numbers</h5>
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					          <span aria-hidden="true">&times;</span>
					        </button>
					      </div>
					      <div class="modal-body">
					      <table class="bordered" style="width:100%;">
					      		<thead style="border:1px solid #eee;text-align:center;">
					      			<tr><th>S.No</th><th>PO No.</th></tr>
					      		</thead>
					      		<tbody>';
					      		foreach ($po_group_detail as $p_g_d) {
			    		$html_string .= '<tr><td>'.$i.'</td><td>'.@ $p_g_d->purchase_order->ref_id.'</td></tr>';
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
			        return $html_string;
		        }
		        else
		        {
		        	return $item->po_group_detail !== null ? $po_id : "--" ;
		        }

		        })

		    ->addColumn('supplier_ref_no',function($item){
		    		$i = 1;
		            $po_group_detail = $item->po_group_detail;
			        // return $item->po_group_detail !== null ? $supplier : "--" ;
			    	if($po_group_detail->count() > 1 )
			    	{

				        $html_string = '
				        	<a href="javascript:void(0)" data-toggle="modal" data-target="#Supplier'.$item->id.'">
							  <i class="fa fa-user-plus"></i>
							</a>
				        ';

				        $html_string .= '
						<div class="modal fade" id="Supplier'.@$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						      <div class="modal-header">
						        <h5 class="modal-title" id="exampleModalLabel">Supplier(s)</h5>
						        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
						          <span aria-hidden="true">&times;</span>
						        </button>
						      </div>
						      <div class="modal-body">
						      <table class="bordered" style="width:100%;">
						      		<thead style="border:1px solid #eee;text-align:center;">
						      			<tr><th>S.No</th><th>Supplier #</th><th>Supplier Reference Name</th></tr>
						      		</thead>
						      		<tbody>';
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
				    					$html_string .= '<tr><td>'.$i.'</td><td>'.@$ref_no.'</td><td>'.@$name.'</td></tr>';
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
				        return $html_string;
				    }
				    else
				    {
				    	if($item->po_group_detail[0]->purchase_order->supplier_id != null)
		      			{
		      				return $item->po_group_detail[0]->purchase_order->PoSupplier->reference_name;
		      			}
		      			else
		      			{
		      				return $item->po_group_detail[0]->purchase_order->PoWarehouse->warehouse_title;
		      			}
				    }
		        })

		    ->addColumn('quantity',function($item){
		    	$po_group_detail = $item->po_group_detail;
			    $total_quantity = null;
			    foreach ($po_group_detail as $p_g_d) {
			    	$total_quantity += $p_g_d->purchase_order->total_quantity;
			    }
			    return $total_quantity ;
			})

		    ->addColumn('net_weight',function($item){
		    	$po_group_detail = $item->po_group_detail;
		    	$weight = null;
		    	foreach ($po_group_detail as $p_g_d) {
		    		$weight += $p_g_d->purchase_order->total_gross_weight;
		    	}
		        return $weight ;
		    })

		    ->addColumn('issue_date',function($item){
		    	$created_at = Carbon::parse($item->created_at)->format('Y-m-d');
		    	return $created_at;
		    })

		    ->addColumn('po_total',function($item){
		    	$po_group_detail = $item->po_group_detail;
			    	$total = null;
			    	foreach ($po_group_detail as $p_g_d) {
			    		$total += $p_g_d->purchase_order->total_in_thb;
			    	}
			        return number_format($total,2,'.',',') ;
		    })

		    ->addColumn('target_receive_date',function($item){
		        return $item->target_receive_date !== null ? $item->target_receive_date: "--" ;
		    })

		    ->addColumn('action', function ($item) {
				if($item->is_confirm == 0)
				{
                $html_string = '<a href="'.url('warehouse/warehouse-products-receiving-queue',$item->id).'" class="actionicon viewIcon" data-id="' . $item->id . '" title="View"><i class="fa fa-eye"></i></a>';
                return $html_string;
				}
				else
				{
					$html_string = '<a href="'.url('warehouse/warehouse-complete-products-receiving-queue',$item->id).'" class="actionicon viewIcon" data-id="' . $item->id . '" title="View"><i class="fa fa-eye"></i></a>';
                	return $html_string;
				}
            })

            ->addColumn('warehouse',function($item){
		            return $item->ToWarehouse !== null ? $item->ToWarehouse->warehouse_title: "--" ;
		        })


		    ->rawColumns(['po_number','bill_of_lading','airway_bill','tax','freight','landing','bl_awb','courier','vendor','vendor_ref_no','action','supplier_ref_no','id'])

		    ->make(true);
    }

    public function transferProductReceivingQueue($id)
    {
    	// dd('here');
		$po_group = PoGroup::find($id);
		$po_group_detail = $po_group->po_group_detail;
		$group_detail = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*','po_groups.target_receive_date as datee','po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->count();
        $status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();
        return $this->render('warehouse.home.transfer-products-receiving',compact('po_group','id','status_history','group_detail'));
	}

	public function gettransferProductReceivingQueueHistory(Request $request)
    {
        $query = ProductReceivingHistory::with('get_user', 'get_po_group_product_detail.product','get_pod.product')->where('po_group_id',$request->id);
        return Datatables::of($query)
        ->addColumn('user',function($item){
            return $item->get_user != null ? $item->get_user->name : '--';
        })
        ->addColumn('date',function($item){
            return $item->created_at != null ? Carbon::parse($item->created_at)->format('d/m/Y, H:i:s') : '--';
        })
        ->addColumn('product',function($item){
            return $item->get_po_group_product_detail != null ? $item->get_po_group_product_detail->product->refrence_code : (@$item->get_pod != null ? @$item->get_pod->product->refrence_code : '--');
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

    public function productReceivingQueue($id)
    {
    	// dd('here');
		$po_group = PoGroup::find($id);
		$po_group_detail = $po_group->po_group_detail;
		$product_receiving_history = ProductReceivingHistory::with('get_user')->where('po_group_id',$id)->get();
		$group_detail = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*','po_groups.target_receive_date as datee','po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->count();
       $status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();

        return $this->render('warehouse.home.products-receiving',compact('po_group','id','product_receiving_history','status_history','group_detail'));
	}

	public function completeTransferProductReceivingQueue($id)
    {
		$po_group = PoGroup::find($id);
		$po_group_detail = $po_group->po_group_detail;
		$status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();

		$group_detail = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*','po_groups.target_receive_date as datee','po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->count();

            // dd($group_detail);

        return $this->render('warehouse.home.complete-transfer-products-receiving',compact('po_group','id','status_history','group_detail'));
	}

	public function getDetailsOfTransDoc(Request $request, $id)
    {
    	$goods_types = ProductType::all();
		$po_group = PoGroup::find($id);
		$all_record = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*',
            		'po_group_details.purchase_order_id',
            		'purchase_orders.supplier_id',
            		'purchase_order_details.product_id',
            		'purchase_order_details.quantity',
            		'purchase_order_details.trasnfer_qty_shipped',
            		'purchase_order_details.quantity_received',
            		'purchase_order_details.expiration_date',
            		'purchase_order_details.quantity_received_2',
            		'purchase_order_details.expiration_date_2',
            		'purchase_order_details.good_condition',
            		'purchase_order_details.result',
            		'purchase_order_details.good_type',
            		'purchase_order_details.temperature_c',
            		'purchase_order_details.checker',
            		'purchase_order_details.problem_found',
            		'purchase_order_details.solution',
            		'purchase_order_details.authorized_changes',
            		'purchase_order_details.custom_line_number','purchase_order_details.id')->where('po_groups.id',$id)
            ->whereNotNull('purchase_order_details.product_id');

        $all_record = POGroupSortingHelper::TDReceivingRecordsSorting($request, $all_record);

		return Datatables::of($all_record)

		    ->addColumn('po_number',function($item){
				if($item->ref_id !== null){
						$html_string = '
						<a href="'.url('warehouse/get-warehouse-transfer-detail/'.$item->purchase_order_id).'"><b>'.$item->ref_id.'</b></a>';
						return $html_string;
				}else{
					return '--';
				}
		        })
		    ->addColumn('order_warehouse',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->user->get_warehouse->warehouse_title : "--" ;
		        })
		    ->addColumn('order_no',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->ref_id : "--" ;
		        })
			->addColumn('supplier',function($item){
				if($item->supplier_id !== NULL)
                {
					$sup_name = Supplier::where('id',$item->supplier_id)->first();
					return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier_id).'"><b>'.$sup_name->reference_name.'</b></a>';
				}
                else
                {
                    $sup_name = Warehouse::where('id',$item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
		            // return $sup_name->company != null ? $sup_name->company :"--" ;
                }

		        })

		    ->addColumn('reference_number',function($item){
		    	if($item->supplier_id !== NULL)
		    	{
		    		$sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
		            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
		        }
		        else
		        {
		        	return "N.A";
		        }

		        })

		    ->addColumn('prod_reference_number',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"><b>'.$product->refrence_code.'</b></a>';
		        })

		    ->addColumn('desc',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
				    return $product->short_desc != null ? $product->short_desc : '' ;
		        })

		    ->addColumn('kg',function($item){
		    	$product = Product::where('id',$item->product_id)->first();

				return $product->units->title != null ? $product->units->title : '';

		        })
		    ->addColumn('selling_unit',function($item){
		    	$product = Product::where('id',$item->product_id)->first();

				return $product->sellingUnits->title != null ? $product->sellingUnits->title : '';

		        })

		    ->addColumn('qty_ordered',function($item){
		    	return $item->quantity;
		    })

		    ->addColumn('qty_inv',function($item){
		    	return $item->trasnfer_qty_shipped !== null ? $item->trasnfer_qty_shipped : "--" ;
		    })

		    ->addColumn('qty_receive',function($item){
		    	$quantity_received = $item->quantity_received != null ? $item->quantity_received : 0 ;

				$html_string = '<input type="number"  name="quantity_received" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received.'" class="fieldFocus" value="'. $quantity_received.'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date',function($item){
				$expiration_date = $item->expiration_date !== null ? Carbon::parse($item->expiration_date)->format('d/m/Y') : '';
				$html_string = '<input type="text" id="expiration_date" name="expiration_date" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date.'" disabled readonly style="width:100%">';
		    	return $html_string;
			})

		    ->addColumn('quantity_received_2',function($item){
		    	$quantity_received_2 = $item->quantity_received_2 != null ? $item->quantity_received_2 : 0 ;

				$html_string = '<input type="number"  name="quantity_received_2" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_2.'" class="fieldFocus" value="'. $quantity_received_2.'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date_2',function($item){
				$expiration_date_2 = $item->expiration_date_2 !== null ? Carbon::parse($item->expiration_date_2)->format('d/m/Y') : '';
				$html_string = '<input type="text" id="expiration_date_2" name="expiration_date_2" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date_2.'" disabled readonly style="width:100%">';
		    	return $html_string;
			})

		    ->addColumn('goods_condition',function($item){
				$check = $item->good_condition;
				$html_string = '<div class="d-flex">
				<div class="custom-control custom-radio custom-control-inline">';
				$html_string .= '<input type="checkbox" class="condition custom-control-input" ' .($item->good_condition == "normal" ? "checked" : ""). ' id="n'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="normal">';

				$html_string .='<label class="custom-control-label" for="n'.$item->id.'">Normal</label>
			   </div><div class="custom-control custom-radio custom-control-inline">
				 <input type="checkbox" class="condition custom-control-input" ' .($item->good_condition == "problem" ? "checked" : ""). ' id="p'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="problem"><label class="custom-control-label" for="p'.$item->id.'">Problem</label></div></div>';
		    	return $html_string;
			})

		    ->addColumn('results',function($item){

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
		    })

		    ->addColumn('goods_type',function($item) use ($goods_types){
				$html_string = '<div class="d-flex">';
				foreach ($goods_types as $type) {

				$html_string .= '<div class="custom-control custom-radio custom-control-inline">
				 <input type="checkbox" class="condition custom-control-input" ' .($item->good_type == $type->id ? "checked" : ""). ' name="condition'.$item->id.'" data-id="'.$item->id.'" id="'.$type->title.$item->id.'" value="'.$type->id.'">
				 <label class="custom-control-label" for="'.$type->title.$item->id.'">'.$type->title.'</label>
			   </div>';
			}

			   $html_string .= '</div>';
		        return $html_string;
			})

			->addColumn('goods_temp',function($item){
				$goods_temp = $item->temperature_c;

				$html_string = '<input type="text"  name="temperature_c" data-id="'.$item->id.'" data-fieldvalue="'.$goods_temp.'" class="fieldFocus" value="'.$goods_temp.'" disabled style="width:100%">';
		    	return $html_string;
			})
			->addColumn('checker',function($item){
				$checker = $item->checker;

				$html_string = '<input type="text"  name="checker" data-id="'.$item->id.'" data-fieldvalue="'.$checker.'" class="fieldFocus" value="'.$checker.'" disabled style="width:100%">';
		    	return $html_string;
		    })
		    ->addColumn('problem_found',function($item){
				$problem_found = $item->problem_found;

				$html_string = '<input type="text"  name="problem_found" data-id="'.$item->id.'" data-fieldvalue="'.$problem_found.'" class="fieldFocus" value="'.$problem_found.'" disabled style="width:100%">';
		    	return $html_string;
			})
			->addColumn('solution',function($item){
				$solution = $item->solution;

				$html_string = '<input type="text"  name="solution" data-id="'.$item->id.'" class="fieldFocus" value="'.$solution.'" disabled style="width:100%">';
		    	return $html_string;
			})
			->addColumn('changes',function($item){
				$authorized_changes = $item->authorized_changes;

				$html_string = '<input type="text"  name="authorized_changes" data-id="'.$item->id.'" class="fieldFocus" value="'.$authorized_changes.'" disabled style="width:100%">';
		    	return $html_string;
			})

			->addColumn('custom_line_number',function($item){
            $html_string = '<input type="text"  name="custom_line_number" data-id="'.$item->id.'" data-fieldvalue="'.@$item->custom_line_number.'" class="fieldFocus" value="'.@$item->custom_line_number.'" readonly disabled style="width:100%">';
            return $html_string;
        	})

        	->addColumn('custom_invoice_number',function($item){
            $html_string = '<input type="text"  name="custom_invoice_number" data-id="'.$item->id.'" data-fieldvalue="'.@$item->custom_invoice_number.'" class="fieldFocus" value="'.@$item->custom_invoice_number.'" readonly disabled style="width:100%">';
            return $html_string;
        	})

		    ->rawColumns(['po_number','supplier','reference_number','prod_reference_number','desc','kg','qty_inv','qty_receive','quantity_received_2','goods_condition','results','goods_type','goods_temp','checker','problem_found','solution','changes','order_id','expiration_date','expiration_date_2','custom_line_number','custom_invoice_number'])

		    ->make(true);
    }

    public function getDetailsOfCompleteTransDoc($id)
    {
		$po_group = PoGroup::find($id);
		$all_record = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*', 'po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->whereNotNull('purchase_order_details.product_id');;

		return Datatables::of($all_record)

		    ->addColumn('po_number',function($item){
				   if($item->ref_id !== null){
						$html_string = '
						<a href="'.url('warehouse/get-warehouse-transfer-detail/'.$item->purchase_order_id).'"><b>'.$item->ref_id.'</b></a>';
						return $html_string;
				   }else{
                    return '--';
                }
		        })
		    ->addColumn('order_warehouse',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->user->get_warehouse->warehouse_title : "--" ;
		        })
		    ->addColumn('order_no',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->ref_id : "--" ;
		        })
			->addColumn('supplier',function($item){
				if($item->supplier_id !== NULL)
                {
					$sup_name = Supplier::where('id',$item->supplier_id)->first();
					return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier_id).'"><b>'.$sup_name->reference_name.'</b></a>';
				}
                else
                {
                    $sup_name = Warehouse::where('id',$item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
		            // return $sup_name->company != null ? $sup_name->company :"--" ;
                }

		        })

		    ->addColumn('reference_number',function($item){
		    	if($item->supplier_id !== NULL)
		    	{
		    		$sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
		            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
		        }
		        else
		        {
		        	return "N.A";
		        }

		        })

		    ->addColumn('prod_reference_number',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  ><b>'.$product->refrence_code.'</b></a>';
		        })

		    ->addColumn('desc',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
				    return $product->short_desc != null ? $product->short_desc : '' ;
		        })

		    ->addColumn('kg',function($item){
		    	$product = Product::where('id',$item->product_id)->first();

				return $product->units->title != null ? $product->units->title : '';

		        })

		    ->addColumn('selling_unit',function($item){
		    	$product = Product::where('id',$item->product_id)->first();

				return $product->sellingUnits->title != null ? $product->sellingUnits->title : '';

		        })

		    ->addColumn('qty_ordered',function($item){
		    	return $item->quantity;
		    })

		    ->addColumn('qty_inv',function($item){
		    	return $item->trasnfer_qty_shipped !== null ? $item->trasnfer_qty_shipped : "--" ;
		    })

		    ->addColumn('qty_receive',function($item){
		    	$quantity_received = $item->quantity_received != null ? $item->quantity_received : 0 ;

				$html_string = '<input type="number"  name="quantity_received" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received.'" class="fieldFocus" value="'. $quantity_received.'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date',function($item){
				$expiration_date = $item->expiration_date !== null ? Carbon::parse($item->expiration_date)->format('d/m/Y') : '';

				// $html_string = '<input type="text" id="expiration_date" name="expiration_date" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date.'" disabled readonly style="width:100%">';
				$html_string = '<input type="text" id="expiration_date" name="expiration_date" data-id="'.$item->id.'" class="" value="'.$expiration_date.'" disabled readonly style="width:100%">';
		    	return $html_string;
			})

		    ->addColumn('quantity_received_2',function($item){
		    	$quantity_received_2 = $item->quantity_received_2 != null ? $item->quantity_received_2 : 0 ;

				// $html_string = '<input type="number"  name="quantity_received_2" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_2.'" class="fieldFocus" value="'. $quantity_received_2.'" readonly disabled style="width:100%">';
				$html_string = '<input type="number"  name="quantity_received_2" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_2.'" class="" value="'. $quantity_received_2.'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date_2',function($item){
				$expiration_date_2 = $item->expiration_date_2 !== null ? Carbon::parse($item->expiration_date_2)->format('d/m/Y') : '';
				// $html_string = '<input type="text" id="expiration_date_2" name="expiration_date_2" data-id="'.$item->id.'" class="expirations_dates" value="'.$expiration_date_2.'" disabled readonly style="width:100%">';
				$html_string = '<input type="text" id="expiration_date_2" name="expiration_date_2" data-id="'.$item->id.'" class="" value="'.$expiration_date_2.'" disabled readonly style="width:100%">';
		    	return $html_string;
			})

			->addColumn('goods_condition',function($item){
				if($item->good_condition != null)
		    	{
					$check = $item->good_condition;
			    	return $check;
				}
				else
				{
					return "N.A";
				}
			})

		    ->addColumn('results',function($item){
		    	if($item->result != null)
		    	{
					$check = $item->result;
		    		return $check;
		    	}
		    	else
		    	{
		    		return "N.A";
				}
		    })

		    ->addColumn('goods_type',function($item){
		    	if($item->good_type != null)
		    	{
		    		$goods_type = ProductType::find($item->good_type);
					return $goods_type->title;
		    	}
		    	else
		    	{
		    		return "N.A";
		    	}

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

		    ->rawColumns(['po_number','supplier','reference_number','prod_reference_number','desc','kg','qty_inv','qty_receive','quantity_received_2','goods_condition','results','goods_type','goods_temp','checker','problem_found','solution','changes','order_id','expiration_date','expiration_date_2'])

		    ->make(true);
    }

    public function checkTransferStatus(Request $request)
    {
		$purchase_orders = PurchaseOrder::whereIn('id',PoGroupDetail::where('po_group_id',$request->id)->pluck('purchase_order_id'))->get();
		foreach ($purchase_orders as $PO)
		{
			if($PO->status == 21)
			{
				$msg = $PO->PoWarehouse->warehouse_title." didnt complete the Pick Instruction yet, Do you still want to Confirm To Stock ???";
			}
			else
			{
				$msg = "You want to confirm ???";
			}
		}
		return response()->json(['success' => true, 'msg' => $msg]);
    }

    public function confirmTransferGroup(Request $request)
	{
        DB::beginTransaction();
        $response = TransferDocumentHelper::confirmTransferGroup($request);
        $result = json_decode($response->getContent());
        if ($result->success) {
            DB::commit();
        }
        else{
            DB::rollBack();
        }
        return $response;
		// dd($request->all());
	// 	$confirm_from_draft = QuotationConfig::where('section','warehouse_management_page')->first();
    //     if($confirm_from_draft)
    //     {
    //         $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
    //         foreach ($globalaccessForWarehouse as $val)
    //         {
    //             if($val['slug'] === "has_warehouse_account")
    //             {
    //                 $has_warehouse_account = $val['status'];
    //             }

    //         }
    //     }
    //     else
    //     {
    //         $has_warehouse_account = '';
    //     }

	// 	$po_group = PoGroup::find($request->id);
	// 	if($po_group->is_confirm == 1)
	// 	{
	// 		return response()->json(['success' => false]);
	// 	}

	// 	if($has_warehouse_account == 1)
	// 	{
	// 		$po_group_details = PoGroupDetail::select('purchase_order_id')->where('po_group_id',$request->id)->get();
	// 		foreach($po_group_details as $po_group_detail)
	// 		{
	// 			$po_details = PurchaseOrderDetail::where('po_id',$po_group_detail->purchase_order_id)->get();
	// 			foreach($po_details as $po_detail)
	// 			{
	// 				$po_detail->quantity_received = $po_detail->quantity;
	// 				$po_detail->save();
	// 			}
	// 		}
	// 	}
	// 	$po_group->is_confirm = 1;
	// 	$po_group->save();
	// 	$purchase_orders = PurchaseOrder::whereIn('id',PoGroupDetail::where('po_group_id',$request->id)->pluck('purchase_order_id'))->get();
	// 	foreach ($purchase_orders as $PO) {

	// 		if($PO->from_warehouse_id != null && $PO->supplier_id == null)
	// 		{

	// 		}
	// 		else
	// 		{
	// 			$PO->status = 15;
	// 			$PO->save();
	// 			// PO status history maintaining
	// 			$page_status = Status::select('title')->whereIn('id',[14,15])->pluck('title')->toArray();
	//             $poStatusHistory = new PurchaseOrderStatusHistory;
	//             $poStatusHistory->user_id    = Auth::user()->id;
	//             $poStatusHistory->po_id      = $PO->id;
	//             $poStatusHistory->status     = $page_status[0];
	//             $poStatusHistory->new_status = $page_status[1];
	//             $poStatusHistory->save();
	// 		}


	// 		$supplier_id = $PO->supplier_id;
	// 		$purchase_order_details = PurchaseOrderDetail::with('product.sellingUnits', 'order_product.get_order')->where('po_id',$PO->id)->whereNotNull('purchase_order_details.product_id')->get();
	// 		$manual_sup = Supplier::where('manual_supplier',1)->first();
	// 		$manual_supplier_id = $manual_sup != null ? $manual_sup->id : null;
	// 		foreach ($purchase_order_details as $p_o_d)
	// 		{
	// 			// /$p_o_d->product->unit_conversion_rate
	// 			$quantity_inv = $p_o_d->quantity_received;
	// 			$quantity_inv_2 = $p_o_d->quantity_received_2;
	// 			$decimal_places = $p_o_d->product->sellingUnits->decimal_places;
    //             if($decimal_places == 0)
    //             {
    //                 $quantity_inv = round($quantity_inv,0);
    //                 $quantity_inv_2 = round($quantity_inv_2,0);
    //             }
    //             elseif($decimal_places == 1)
    //             {
    //                 $quantity_inv = round($quantity_inv,1);
    //                 $quantity_inv_2 = round($quantity_inv_2,1);
    //             }
    //             elseif($decimal_places == 2)
    //             {
    //                 $quantity_inv = round($quantity_inv,2);
    //                 $quantity_inv_2 = round($quantity_inv_2,2);
    //             }
    //             elseif($decimal_places == 3)
    //             {
    //                 $quantity_inv = round($quantity_inv,3);
    //                 $quantity_inv_2 = round($quantity_inv_2,3);
    //             }
    //             else
    //             {
    //                 $quantity_inv = round($quantity_inv,4);
    //                 $quantity_inv_2 = round($quantity_inv_2,4);
    //             }

	// 			if($p_o_d->quantity == $quantity_inv)
	// 			{
	// 				$p_o_d->is_completed = 1;
	// 				$p_o_d->save();
	// 			}
	// 			if($p_o_d->order_product_id != null)
	// 			{
	// 				$order_product = $p_o_d->order_product;
    //                 $order         = $order_product->get_order;
	// 				if($order->primary_status !== 3 && $order->primary_status !== 17)
    //                 {
	// 					$order_product->status = 10;
	// 					$order_product->save();

	// 					$order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('quantity','!=',0)->where('status','!=',10)->count();
	//                     if($order_products_status_count == 0)
	//                     {
	//                         $order->status = 10;
	//                         $order->save();
	//                         // dd('here');
	//                          $order_history = new OrderStatusHistory;
	// 	                    $order_history->user_id = Auth::user()->id;
	// 	                    $order_history->order_id = @$order->id;
	// 	                    $order_history->status = 'DI(Importing)';
	// 	                    $order_history->new_status = 'DI(Waiting To Pick)';
	// 	                    $order_history->save();
	//                     }
    //                 }
	// 			}
	// 			if($quantity_inv != null && $quantity_inv != 0)
	// 			{


    //                 $stock = StockManagementIn::where('expiration_date',$p_o_d->expiration_date)->where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->first();
    //                 if($stock != null)
    //                 {
    //                     // new logic for TD to trace back to supplier
    //                     $res_stocks = TransferDocumentReservedQuantity::with('stock_m_out', 'spoilage_table')->where('pod_id',$p_o_d->id)->get();
    //                     if($res_stocks->count() > 0){
    //                         foreach($res_stocks as $res_stock){
    //                             if ($res_stock->reserved_quantity != null) {
    //                                 $qty_received = $res_stock->qty_received;
    //                                 $stock_out               = new StockManagementOut;
    //                                 $stock_out->title        = 'TD';
    //                                 $stock_out->smi_id       = $stock->id;
    //                                 $stock_out->p_o_d_id     = $p_o_d->id;
    //                                 $stock_out->product_id   = $p_o_d->product_id;
    //                                 $stock_out->quantity_in  = $qty_received;
    //                                 $stock_out->available_stock  = $qty_received;
    //                                 $stock_out->spoilage   = $res_stock->spoilage_table != null ? $res_stock->spoilage . ' (' . $res_stock->spoilage_table->title . ')' : null;
    //                                 $stock_out->created_by   = Auth::user()->id;
    //                                 $stock_out->warehouse_id = $PO->to_warehouse_id;
    //                                 $stock_out->supplier_id = @$res_stock->stock_m_out->supplier_id != null ? @$res_stock->stock_m_out->supplier_id : $manual_supplier_id;
    //                                 $stock_out->cost = @$p_o_d->product->selling_price;
    //                                 $stock_out->save();
    //                                 }
    //                         }
    //                         // new logic end
    //                     }else{
    //                         // old logic
    //                         $stock_out               = new StockManagementOut;
    //                         $stock_out->title        = 'TD';
    //                         $stock_out->smi_id       = $stock->id;
    //                         $stock_out->p_o_d_id     = $p_o_d->id;
    //                         $stock_out->product_id   = $p_o_d->product_id;
    //                         if($quantity_inv < 0)
    //                         {
    //                             $stock_out->quantity_out = $quantity_inv;
    //                             $stock_out->available_stock = $quantity_inv;
    //                         }
    //                         else
    //                         {
    //                             $stock_out->quantity_in  = $quantity_inv;
    //                             $stock_out->available_stock  = $quantity_inv;
    //                         }
    //                         $stock_out->created_by   = Auth::user()->id;
    //                         $stock_out->warehouse_id = $PO->to_warehouse_id;
    //                         $stock_out->supplier_id = $manual_supplier_id;
    //                         $stock_out->cost = @$p_o_d->product->selling_price;
    //                         $stock_out->save();
    //                     }


    //                     if($quantity_inv < 0)
    //                     {
    //                     //To find from which stock the order will be deducted
    //                             $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
    //                             if($find_stock->count() > 0)
    //                             {
    //                                 foreach ($find_stock as $out)
    //                                 {

    //                                     if(abs($stock_out->available_stock) > 0)
    //                                     {
    //                                             if($out->available_stock >= abs($stock_out->available_stock))
    //                                             {
    //                                                 $history_quantity = $stock_out->available_stock;
    //                                                 $stock_out->parent_id_in .= $out->id.',';
    //                                                 $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
    //                                                 $stock_out->available_stock = 0;
    //                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
    //                                             }
    //                                             else
    //                                             {
    //                                                 $history_quantity = $out->available_stock;
    //                                                 $stock_out->parent_id_in .= $out->id.',';
    //                                                 $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
    //                                                 $out->available_stock = 0;
    //                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
    //                                             }
    //                                             $out->save();
    //                                             $stock_out->save();
    //                                     }
    //                                 }
    //                             }
    //                     }
    //                     else
    //                     {
    //                         // $dummy_order = PurchaseOrder::createManualPo($stock_out);
    //                         $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
    //                         if($find_stock->count() > 0)
    //                         {
    //                             foreach ($find_stock as $out) {

    //                                 if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
    //                                 {
    //                                         if($stock_out->available_stock >= abs($out->available_stock))
    //                                         {
    //                                             $history_quantity = $out->available_stock;
    //                                             $out->parent_id_in .= $stock_out->id.',';
    //                                             $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
    //                                             $out->available_stock = 0;
    //                                             $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
    //                                         }
    //                                         else
    //                                         {
    //                                             $history_quantity = $stock_out->available_stock;
    //                                             $out->parent_id_in .= $out->id.',';
    //                                             $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
    //                                             $stock_out->available_stock = 0;
    //                                             $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
    //                                         }
    //                                         $out->save();
    //                                         $stock_out->save();
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //                 else
    //                 {
    //                     if($p_o_d->expiration_date == null)
    //                     {
    //                         $stock = StockManagementIn::where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->whereNull('expiration_date')->first();
    //                     }

    //                     if($stock == null)
    //                     {
    //                         $stock = new StockManagementIn;
    //                     }

    //                     $stock->title           = 'Adjustment';
    //                     $stock->product_id      = $p_o_d->product_id;
    //                     $stock->quantity_in     = $quantity_inv;
    //                     $stock->created_by      = Auth::user()->id;
    //                     $stock->warehouse_id    = $PO->to_warehouse_id;
    //                     $stock->expiration_date = $p_o_d->expiration_date;
    //                     $stock->save();

    //                     // new logic for TD to trace back to supplier
    //                     $res_stocks = TransferDocumentReservedQuantity::with('stock_m_out', 'spoilage_table')->where('pod_id',$p_o_d->id)->get();
    //                     if($res_stocks->count() > 0){
    //                         foreach($res_stocks as $res_stock){
    //                             if ($res_stock->reserved_quantity != null) {
    //                                 $qty_received = $res_stock->qty_received;
    //                                 $stock_out               = new StockManagementOut;
    //                                 $stock_out->title        = 'TD';
    //                                 $stock_out->smi_id       = $stock->id;
    //                                 $stock_out->p_o_d_id     = $p_o_d->id;
    //                                 $stock_out->product_id   = $p_o_d->product_id;
    //                                 $stock_out->quantity_in  = $qty_received;
    //                                 $stock_out->available_stock  = $qty_received;
    //                                 $stock_out->spoilage   = $res_stock->spoilage_table != null ? $res_stock->spoilage . ' (' . $res_stock->spoilage_table->title . ')' : null;
    //                                 $stock_out->created_by   = Auth::user()->id;
    //                                 $stock_out->warehouse_id = $PO->to_warehouse_id;
    //                                 $stock_out->cost = @$p_o_d->product->selling_price;
    //                                 $stock_out->supplier_id = @$res_stock->stock_m_out->supplier_id != null ? @$res_stock->stock_m_out->supplier_id : $manual_supplier_id;
    //                                 $stock_out->save();
    //                             }
    //                         }
    //                     }else{
    //                         // old logic
    //                         $stock_out               = new StockManagementOut;
    //                         $stock_out->title        = 'TD';
    //                         $stock_out->smi_id       = $stock->id;
    //                         $stock_out->p_o_d_id     = $p_o_d->id;
    //                         $stock_out->product_id   = $p_o_d->product_id;
    //                         if($quantity_inv < 0)
    //                         {
    //                             $stock_out->quantity_out = $quantity_inv;
    //                             $stock_out->available_stock = $quantity_inv;
    //                         }
    //                         else
    //                         {
    //                         $stock_out->quantity_in  = $quantity_inv;
    //                         $stock_out->available_stock  = $quantity_inv;
    //                         }
    //                         $stock_out->created_by   = Auth::user()->id;
    //                         $stock_out->warehouse_id = $PO->to_warehouse_id;
    //                         $stock_out->cost = @$p_o_d->product->selling_price;
    //                         $stock_out->supplier_id = $manual_supplier_id;
    //                         $stock_out->save();
    //                     }


    //                     if($quantity_inv < 0)
    //                     {
    //                     //To find from which stock the order will be deducted
    //                             $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
    //                             if($find_stock->count() > 0)
    //                             {
    //                                 foreach ($find_stock as $out)
    //                                 {

    //                                     if(abs($stock_out->available_stock) > 0 && abs($stock_out->available_stock) > 0)
    //                                     {
    //                                             if($out->available_stock >= abs($stock_out->available_stock))
    //                                             {
    //                                                 $history_quantity = $stock_out->available_stock;
    //                                                 $stock_out->parent_id_in .= $out->id.',';
    //                                                 $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
    //                                                 $stock_out->available_stock = 0;
    //                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
    //                                             }
    //                                             else
    //                                             {
    //                                                 $history_quantity = $out->available_stock;
    //                                                 $stock_out->parent_id_in .= $out->id.',';
    //                                                 $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
    //                                                 $out->available_stock = 0;
    //                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
    //                                             }
    //                                             $out->save();
    //                                             $stock_out->save();
    //                                     }
    //                                 }
    //                             }
    //                     }
    //                     else
    //                     {
    //                     // $dummy_order = PurchaseOrder::createManualPo($stock_out);
    //                     $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
    //                     if($find_stock->count() > 0)
    //                     {
    //                         foreach ($find_stock as $out) {

    //                             if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
    //                             {
    //                                     if($stock_out->available_stock >= abs($out->available_stock))
    //                                     {
    //                                         $history_quantity = $out->available_stock;
    //                                         $out->parent_id_in .= $stock_out->id.',';
    //                                         $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
    //                                         $out->available_stock = 0;
    //                                         $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
    //                                     }
    //                                     else
    //                                     {
    //                                         $history_quantity = $stock_out->available_stock;
    //                                         $out->parent_id_in .= $out->id.',';
    //                                         $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
    //                                         $stock_out->available_stock = 0;
    //                                         $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
    //                                     }
    //                                     $out->save();
    //                                     $stock_out->save();
    //                             }
    //                         }
    //                     }
    //                     }
    //                 }


	// 			// $warehouse_products = WarehouseProduct::where('warehouse_id',$PO->to_warehouse_id)->where('product_id',$p_o_d->product_id)->first();
	// 			// $warehouse_products->current_quantity += round($quantity_inv,3);
    // //             $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);
	// 			// $warehouse_products->save();

    //             $new_his = new QuantityReservedHistory;
    //             $re      = $new_his->updateTDCurrentQuantity($PO,$p_o_d,$quantity_inv,'add');
	// 			}
	// 			if($quantity_inv_2 != null && $quantity_inv_2 != 0)
	// 			{
	// 				$stock = StockManagementIn::where('expiration_date',$p_o_d->expiration_date_2)->where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->first();
	// 				if($stock != null)
	//                 {
	//                     $stock_out               = new StockManagementOut;
	//                     $stock_out->title        = 'TD';
	//                     $stock_out->smi_id       = $stock->id;
	//                     $stock_out->p_o_d_id     = $p_o_d->id;
	//                     $stock_out->product_id   = $p_o_d->product_id;
	//                     if($quantity_inv_2 < 0)
	//                     {
	//                         $stock_out->quantity_out = $quantity_inv_2;
	//                         $stock_out->available_stock = $quantity_inv_2;
	//                     }
	//                     else
	//                     {
	//                        $stock_out->quantity_in  = $quantity_inv_2;
	//                        $stock_out->available_stock  = $quantity_inv_2;
	//                     }
	//                     $stock_out->created_by   = Auth::user()->id;
	//                     $stock_out->warehouse_id = $PO->to_warehouse_id;
	//                     $stock_out->cost = @$p_o_d->product->selling_price;
	//                     $stock_out->supplier_id = $manual_supplier_id;
	//                     $stock_out->save();

	//                     if($quantity_inv_2 < 0)
	//                     {
	//                       //To find from which stock the order will be deducted
	//                             $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
	//                             if($find_stock->count() > 0)
	//                             {
	//                                 foreach ($find_stock as $out)
	//                                 {

	//                                     if(abs($stock_out->available_stock) > 0)
	//                                     {
	//                                             if($out->available_stock >= abs($stock_out->available_stock))
	//                                             {
	//                                             	$history_quantity = $out->available_stock;
	//                                                 $stock_out->parent_id_in .= $out->id.',';
	//                                                 $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
	//                                                 $stock_out->available_stock = 0;
	//                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
	//                                             }
	//                                             else
	//                                             {
	//                                             	$history_quantity = $out->available_stock;
	//                                                 $stock_out->parent_id_in .= $out->id.',';
	//                                                 $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
	//                                                 $out->available_stock = 0;
	//                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
	//                                             }
	//                                             $out->save();
	//                                             $stock_out->save();
	//                                     }
	//                                 }
	//                             }
	//                     }
	//                     else
	//                     {
	//                     	// $dummy_order = PurchaseOrder::createManualPo($stock_out);
	//                       $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
	//                       if($find_stock->count() > 0)
	//                       {
	//                           foreach ($find_stock as $out) {

	//                               if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
	//                               {
	//                                       if($stock_out->available_stock >= abs($out->available_stock))
	//                                       {
	//                                       	  $history_quantity = $out->available_stock;
	//                                           $out->parent_id_in .= $stock_out->id.',';
	//                                           $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	//                                           $out->available_stock = 0;
	//                                           $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
	//                                       }
	//                                       else
	//                                       {
	//                                       	  $history_quantity = $stock_out->available_stock;
	//                                           $out->parent_id_in .= $out->id.',';
	//                                           $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	//                                           $stock_out->available_stock = 0;
	//                                           $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
	//                                       }
	//                                       $out->save();
	//                                       $stock_out->save();
	//                               }
	//                           }
	//                       }
	//                     }

	//                 }
	//                 else
	//                 {
	//                 	if($p_o_d->expiration_date_2 == null)
	//                 	{
	// 	                    $stock = StockManagementIn::where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->whereNull('expiration_date')->first();
	//                 	}

	//                     if($stock == null)
	//                     {
	//                         $stock = new StockManagementIn;
	//                     }

	//                     $stock->title           = 'Adjustment';
	//                     $stock->product_id      = $p_o_d->product_id;
	//                     $stock->quantity_in     = $quantity_inv_2;
	//                     $stock->created_by      = Auth::user()->id;
	//                     $stock->warehouse_id    = $PO->to_warehouse_id;
	//                     $stock->expiration_date = $p_o_d->expiration_date_2;
	//                     $stock->save();

	//                     $stock_out               = new StockManagementOut;
	//                     $stock_out->title        = 'TD';
	//                     $stock_out->smi_id       = $stock->id;
	//                     $stock_out->p_o_d_id     = $p_o_d->id;
	//                     $stock_out->product_id   = $p_o_d->product_id;
	//                     if($quantity_inv_2 < 0)
	//                     {
	//                         $stock_out->quantity_out = $quantity_inv_2;
	//                         $stock_out->available_stock = $quantity_inv_2;
	//                     }
	//                     else
	//                     {
	//                        $stock_out->quantity_in  = $quantity_inv_2;
	//                        $stock_out->available_stock  = $quantity_inv_2;
	//                     }
	//                     $stock_out->created_by   = Auth::user()->id;
	//                     $stock_out->warehouse_id = $PO->to_warehouse_id;
	//                     $stock_out->cost = @$p_o_d->product->selling_price;
	//                     $stock_out->supplier_id = $manual_supplier_id;
	//                     $stock_out->save();

	//                     if($quantity_inv_2 < 0)
	//                     {
	//                       //To find from which stock the order will be deducted
	//                             $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock','>',0)->orderBy('id','asc')->get();
	//                             if($find_stock->count() > 0)
	//                             {
	//                                 foreach ($find_stock as $out)
	//                                 {

	//                                     if(abs($stock_out->available_stock) > 0)
	//                                     {
	//                                             if($out->available_stock >= abs($stock_out->available_stock))
	//                                             {
	//                                             	$history_quantity = $stock_out->available_stock;
	//                                                 $stock_out->parent_id_in .= $out->id.',';
	//                                                 $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
	//                                                 $stock_out->available_stock = 0;
	//                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
	//                                             }
	//                                             else
	//                                             {
	//                                             	$history_quantity = $out->available_stock;
	//                                                 $stock_out->parent_id_in .= $out->id.',';
	//                                                 $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
	//                                                 $out->available_stock = 0;
	//                                                 $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$p_o_d,abs($history_quantity));
	//                                             }
	//                                             $out->save();
	//                                             $stock_out->save();
	//                                     }
	//                                 }
	//                             }
	//                     }
	//                     else
	//                     {
	//                       // $dummy_order = PurchaseOrder::createManualPo($stock_out);
	//                       $find_stock = StockManagementOut::where('smi_id',$stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock','<',0)->get();
	//                       if($find_stock->count() > 0)
	//                       {
	//                           foreach ($find_stock as $out) {

	//                               if($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0)
	//                               {
	//                                       if($stock_out->available_stock >= abs($out->available_stock))
	//                                       {
	//                                       	  $history_quantity = $out->available_stock;
	//                                           $out->parent_id_in .= $stock_out->id.',';
	//                                           $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	//                                           $out->available_stock = 0;
	//                                           $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
	//                                       }
	//                                       else
	//                                       {
	//                                       	  $history_quantity = $stock_out->available_stock;
	//                                           $out->parent_id_in .= $out->id.',';
	//                                           $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	//                                           $stock_out->available_stock = 0;
	//                                           $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$p_o_d,round(abs($history_quantity),4));
	//                                       }
	//                                       $out->save();
	//                                       $stock_out->save();
	//                               }
	//                           }
	//                       }
	//                     }
	//                 }

	// 				// $warehouse_products = WarehouseProduct::where('warehouse_id',$PO->to_warehouse_id)->where('product_id',$p_o_d->product_id)->first();
	// 				// $warehouse_products->current_quantity += round($quantity_inv_2,3);
    //  //            	$warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);

	// 				// $warehouse_products->save();

    //                 $new_his = new QuantityReservedHistory;
    //                 $re      = $new_his->updateTDCurrentQuantity($PO,$p_o_d,$quantity_inv_2,'add');
	// 			}
	// 		}
	// 	}

	// 	$group_status_history = new PoGroupStatusHistory;
    //     $group_status_history->user_id = Auth::user()->id;
    //     $group_status_history->po_group_id = @$po_group->id;
    //     $group_status_history->status = 'Confirmed';
    //     $group_status_history->new_status = 'Closed Product Receiving Queue';
    //     $group_status_history->save();

	// 	return response()->json(['success' => true]);
	}

	public function completeProductReceivingQueue($id)
    {
		$po_group = PoGroup::find($id);
		$po_group_detail = $po_group->po_group_detail;
		$product_receiving_history = ProductReceivingHistory::with('get_user')->where('po_group_id',$id)->get();
		$status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();

		$group_detail = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*','po_groups.target_receive_date as datee','po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->count();

            // dd($group_detail);

        return $this->render('warehouse.home.complete-products-receiving',compact('po_group','id','product_receiving_history','status_history','group_detail'));
	}

	public function getDetailsOfPo($id)
    {
		$po_group = PoGroup::find($id);
		$all_record = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*', 'po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->whereNotNull('purchase_order_details.product_id')
            ->get();

		return Datatables::of($all_record)

		    ->addColumn('po_number',function($item){
			       return $item->ref_id !== null ? $item->ref_id : "--" ;
		        })
		    ->addColumn('order_warehouse',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->user->get_warehouse->warehouse_title : "--" ;
		        })
		    ->addColumn('order_no',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->ref_id : "--" ;
		        })
			->addColumn('supplier',function($item){
				if($item->supplier_id !== NULL)
                {
					$sup_name = Supplier::where('id',$item->supplier_id)->first();
					return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier_id).'"  >'.$sup_name->reference_name.'</a>';
				}
                else
                {
                    $sup_name = Warehouse::where('id',$item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
		            // return $sup_name->company != null ? $sup_name->company :"--" ;
                }

		        })

		    ->addColumn('reference_number',function($item){
		    	if($item->supplier_id !== NULL)
		    	{
		    		$sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
		            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
		        }
		        else
		        {
		        	return "N.A";
		        }

		        })

		    ->addColumn('prod_reference_number',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  >'.$product->refrence_code.'</a>';
		        })

		    ->addColumn('desc',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
				    return $product->short_desc != null ? $product->short_desc : '' ;
		        })

		    ->addColumn('kg',function($item){
		    	$product = Product::where('id',$item->product_id)->first();

				return $product->units->title != null ? $product->units->title : '';

		        })

		    ->addColumn('qty_ordered',function($item){
		    	if($item->order_product_id != null)
		    	{
			    	$order_product = OrderProduct::find($item->order_product_id);
			    	return $order_product->quantity;
		    	}
		    	else
		    	{
		    		return '--';
		    	}
		    })

		    ->addColumn('qty_inv',function($item){
		    	return $item->quantity;
		    })

		    ->addColumn('qty_receive',function($item){
		    	$quantity_received = $item->quantity_received != null ? $item->quantity_received : 0 ;

				$html_string = '<input type="number"  name="quantity_received" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received.'" class="fieldFocus" value="'. $quantity_received.'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date',function($item){
				$expiration_date = $item->expiration_date;

				$html_string = '<input type="date"  name="expiration_date" data-id="'.$item->id.'" class="fieldFocus" value="'.$expiration_date.'" disabled style="width:100%">';
		    	return $html_string;
			})

		    ->addColumn('quantity_received_2',function($item){
		    	$quantity_received_2 = $item->quantity_received_2 != null ? $item->quantity_received_2 : 0 ;

				$html_string = '<input type="number"  name="quantity_received_2" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_2.'" class="fieldFocus" value="'. $quantity_received_2.'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date_2',function($item){
				$expiration_date_2 = $item->expiration_date_2;

				$html_string = '<input type="date"  name="expiration_date_2" data-id="'.$item->id.'" class="fieldFocus" value="'.$expiration_date_2.'" disabled style="width:100%">';
		    	return $html_string;
			})

		    ->addColumn('goods_condition',function($item){
				$check = $item->good_condition;
				$html_string = '<div class="d-flex">
				<div class="custom-control custom-radio custom-control-inline">';
				$html_string .= '<input type="checkbox" class="condition custom-control-input" ' .($item->good_condition == "normal" ? "checked" : ""). ' id="n'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="normal">';

				$html_string .='<label class="custom-control-label" for="n'.$item->id.'">Normal</label>
			   </div><div class="custom-control custom-radio custom-control-inline">
				 <input type="checkbox" class="condition custom-control-input" ' .($item->good_condition == "problem" ? "checked" : ""). ' id="p'.$item->id.'" name="condition'.$item->id.'" data-id="'.$item->id.'" value="problem"><label class="custom-control-label" for="p'.$item->id.'">Problem</label></div></div>';
		    	return $html_string;
			})

		    ->addColumn('results',function($item){

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
		    })

		    ->addColumn('goods_type',function($item){
				$goods_types = ProductType::all();
				$html_string = '<div class="d-flex">';
				foreach ($goods_types as $type) {

				$html_string .= '<div class="custom-control custom-radio custom-control-inline">
				 <input type="checkbox" class="condition custom-control-input" ' .($item->good_type == $type->id ? "checked" : ""). ' name="condition'.$item->id.'" data-id="'.$item->id.'" id="'.$type->title.$item->id.'" value="'.$type->id.'">
				 <label class="custom-control-label" for="'.$type->title.$item->id.'">'.$type->title.'</label>
			   </div>';
			}

			   $html_string .= '</div>';
		        return $html_string;
			})

			->addColumn('goods_temp',function($item){
				$goods_temp = $item->temperature_c;

				$html_string = '<input type="text"  name="temperature_c" data-id="'.$item->id.'" data-fieldvalue="'.$goods_temp.'" class="fieldFocus" value="'.$goods_temp.'" disabled style="width:100%">';
		    	return $html_string;
			})
			->addColumn('checker',function($item){
				$checker = $item->checker;

				$html_string = '<input type="text"  name="checker" data-id="'.$item->id.'" data-fieldvalue="'.$checker.'" class="fieldFocus" value="'.$checker.'" disabled style="width:100%">';
		    	return $html_string;
		    })
		    ->addColumn('problem_found',function($item){
				$problem_found = $item->problem_found;

				$html_string = '<input type="text"  name="problem_found" data-id="'.$item->id.'" data-fieldvalue="'.$problem_found.'" class="fieldFocus" value="'.$problem_found.'" disabled style="width:100%">';
		    	return $html_string;
			})
			->addColumn('solution',function($item){
				$solution = $item->solution;

				$html_string = '<input type="text"  name="solution" data-id="'.$item->id.'" class="fieldFocus" value="'.$solution.'" disabled style="width:100%">';
		    	return $html_string;
			})
			->addColumn('changes',function($item){
				$authorized_changes = $item->authorized_changes;

				$html_string = '<input type="text"  name="authorized_changes" data-id="'.$item->id.'" class="fieldFocus" value="'.$authorized_changes.'" disabled style="width:100%">';
		    	return $html_string;
			})

		    ->rawColumns(['po_number','supplier','reference_number','prod_reference_number','desc','kg','qty_inv','qty_receive','quantity_received_2','goods_condition','results','goods_type','goods_temp','checker','problem_found','solution','changes','order_id','expiration_date','expiration_date_2'])

		    ->make(true);
    }

    public function getDetailsOfCompletedPoGroup($id)
    {
		$po_group = PoGroup::find($id);
		$all_record = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*', 'po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->whereNotNull('purchase_order_details.product_id')
			->get();

		return Datatables::of($all_record)

		    ->addColumn('po_number',function($item){
		    	// dd($item);
			        return $item->ref_id !== null ? $item->ref_id : "--" ;
		        })
		    ->addColumn('order_warehouse',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->user->get_warehouse->warehouse_title : "--" ;
		        })
		    ->addColumn('order_no',function($item){
		    		$order = Order::find(@$item->order_id);
			        return $order !== null ? $order->ref_id : "--" ;
		        })
			->addColumn('supplier',function($item){
				if($item->supplier_id !== NULL)
                {
					$sup_name = Supplier::where('id',$item->supplier_id)->first();
					return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier_id).'"  >'.$sup_name->reference_name.'</a>';
				}
                else
                {
                    $sup_name = Warehouse::where('id',$item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
		            // return $sup_name->company != null ? $sup_name->company :"--" ;
                }
		        })

		    ->addColumn('reference_number',function($item){
		    	if($item->supplier_id !== NULL)
                {
		    		$sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
		            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
		        }
                else
                {
                    return "N.A";
                }
		        })

		    ->addColumn('prod_reference_number',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  >'.$product->refrence_code.'</a>';
		        })

		    ->addColumn('desc',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
				    return $product->short_desc != null ? $product->short_desc : '' ;
		        })

		    ->addColumn('unit',function($item){
		    	$product = Product::where('id',$item->product_id)->first();

				return $product->units->title != null ? $product->units->title : '';

		        })

		    ->addColumn('qty_ordered',function($item){
		    	if($item->order_product_id != null)
		    	{
			    	$order_product = OrderProduct::find($item->order_product_id);
			    	return $order_product->quantity;
		    	}
		    	else
		    	{
		    		return '--';
		    	}
		    })

		    ->addColumn('qty_inv',function($item){
		    	return $item->quantity;
		    })

		    ->addColumn('qty_receive',function($item){
		    	$quantity_received = $item->quantity_received != null ? $item->quantity_received : 0 ;

				$html_string = '<input type="number"  name="quantity_received" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received.'" class="fieldFocus" value="'. $quantity_received.'" readonly disabled style="width:50%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date',function($item){
				$expiration_date = $item->expiration_date;

				$html_string = '<input type="date"  name="expiration_date" data-id="'.$item->id.'" class="fieldFocus" value="'.$expiration_date.'" disabled style="width:100%" data-fieldvalue="'.$expiration_date.'">';
		    	return $html_string;
			})

		    ->addColumn('quantity_received_2',function($item){
		    	$quantity_received_2 = $item->quantity_received_2 != null ? $item->quantity_received_2 : 0 ;

				$html_string = '<input type="number"  name="quantity_received_2" data-id="'.$item->id.'" data-fieldvalue="'.$quantity_received_2.'" class="fieldFocus" value="'. $quantity_received_2.'" readonly disabled style="width:50%">';
		    	return $html_string;
		    })

		    ->addColumn('expiration_date_2',function($item){
				$expiration_date_2 = $item->expiration_date_2;

				$html_string = '<input type="date"  name="expiration_date_2" data-id="'.$item->id.'" class="fieldFocus" value="'.$expiration_date_2.'" disabled style="width:100%" data-fieldvalue="'.$expiration_date_2.'">';
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
				$goods_type = ProductType::find($item->good_type);
				return $goods_type->title;
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

		    ->rawColumns(['po_number','supplier','reference_number','prod_reference_number','desc','unit','qty_ordered','qty_inv','qty_receive','quantity_received_2','goods_condition','results','goods_type','goods_temp','checker','problem_found','solution','changes','expiration_date','expiration_date_2'])

		    ->make(true);
    }

    public function savePoGroupDetailChanges(Request $request)
    {
        $po_detail = PurchaseOrderDetail::where('id',$request->pod_id)->first();
        $warehouse_id = $po_detail->PurchaseOrder->to_warehouse_id != null ? $po_detail->PurchaseOrder->to_warehouse_id : Auth::user()->get_warehouse->id;
        $po_detail_transfer = PoGroupDetail::where('po_group_id',$request->po_group_id)->first();

        $po = PurchaseOrder::where('id',$po_detail_transfer->purchase_order_id)->first();
        $manual_supplier = Supplier::where('manual_supplier',1)->first();

        foreach($request->except('pod_id','po_group_id') as $key => $value)
        {
          	if($value == ''){
              // $supp_detail->$key = null;
          	}
          	elseif($key == 'quantity_received')
          	{

          		// $sto = $po_detail->update_stock_card($po_detail,$value);
          		// if( $value > $po_detail->quantity ){
          		// 	return response()->json(['success' => false,'extra_quantity'=>$value-$po_detail->quantity]);
          		// }
          		if(true)
          		{
          			$decimal_places = ($po_detail->product->units != null ? $po_detail->product->units->decimal_places : 4);
                    $value = round($value,$decimal_places);

          			$group = PoGroup::find($request->po_group_id);
		            if($group->is_confirm == 1)
		            {
		            	$quantity_received = $value - $po_detail->quantity_received;
		            	$stock = StockManagementIn::where('expiration_date',$po_detail->expiration_date)->where('product_id',$po_detail->product_id)->where('warehouse_id',$warehouse_id)->first();
						if($stock != null)
		                {
							$stock_out               = new StockManagementOut;
							$stock_out->smi_id       = $stock->id;
							$stock_out->p_o_d_id     = $po_detail->id;
							$stock_out->product_id   = $po_detail->product_id;
		                    if($quantity_received < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received;
		                        $stock_out->available_stock = $quantity_received;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received;
		                       $stock_out->available_stock  = $quantity_received;
		                       $stock_out->supplier_id  = @$po_detail->supplier_id != null ? @$po_detail->supplier_id : $manual_supplier->id;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    $stock_out->save();

		                    if($quantity_received < 0)
		                    {
		                    	$dummy_order = Order::createManualOrder($stock_out,'Quantity received updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now());
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
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
		                                            }
		                                            else
		                                            {
		                                            	$history_quantity = $out->available_stock;
		                                                $stock_out->parent_id_in .= $out->id.',';
		                                                $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
		                                                $out->available_stock = 0;
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
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
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
		                                      }
		                                      else
		                                      {
		                                      	$history_quantity = $stock_out->available_stock;
		                                          $out->parent_id_in .= $out->id.',';
		                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
		                                          $stock_out->available_stock = 0;
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
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
		                	if($po_detail->expiration_date == null)
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
		                    $stock->expiration_date = $po_detail->expiration_date;
		                    $stock->save();

		                    $stock_out               = new StockManagementOut;
		                    $stock_out->smi_id       = $stock->id;
		                    $stock_out->p_o_d_id     = $po_detail->id;
		                    $stock_out->product_id   = $po_detail->product_id;
		                    if($quantity_received < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received;
		                        $stock_out->available_stock = $quantity_received;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received;
		                       $stock_out->available_stock  = $quantity_received;
		                       $stock_out->supplier_id  = @$po_detail->supplier_id != null ? @$po_detail->supplier_id : @$manual_supplier->id;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    $stock_out->save();

		                    if($quantity_received < 0)
		                    {
		                    	$dummy_order = Order::createManualOrder($stock_out,'Quantity received updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now());
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
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
		                                            }
		                                            else
		                                            {
		                                            	$history_quantity = $out->available_stock;
		                                                $stock_out->parent_id_in .= $out->id.',';
		                                                $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
		                                                $out->available_stock = 0;
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
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
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
		                                      }
		                                      else
		                                      {
		                                      	  $history_quantity = $stock_out->available_stock;
		                                          $out->parent_id_in .= $out->id.',';
		                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
		                                          $stock_out->available_stock = 0;
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
		                                      }
		                                      $out->save();
		                                      $stock_out->save();
		                              }
		                          }
		                      }
		                    }
		                }

		                $stc = $po_detail->update_stock_card_for_completed_td($po_detail,$value, $key);

						// $warehouse_products = WarehouseProduct::where('warehouse_id',Auth::user()->get_warehouse->id)->where('product_id',$po_detail->product_id)->first();
						// $warehouse_products->current_quantity += round($quantity_received,3);
						// $warehouse_products->save();
		            }


		          	$params['term_key']  = $key;
		            $params['old_value'] = $po_detail->$key;
		            $params['new_value'] = $value;
		            $params['ip_address'] = $request->ip();
		            $this->saveProductReceivingHistory($params, $po_detail->id,$request->po_group_id);
		            $po_detail->$key = $value;
	        	}

                // Getting TD Reserve Quantity for quantity updation
                // $res_stocks = TransferDocumentReservedQuantity::with('stock_m_out')->where('pod_id', $po_detail->id)->orderBy('id', 'DESC')->get();
                // if ($res_stocks->count() > 0) {
                //     $res_difference = $res_stocks->sum('reserved_quantity') - $value; //getting difference
                //     if ($res_difference > 0) {
                //         $res_stock = $res_stocks->first();
                //         $res_stock->reserved_quantity -= $res_difference; //subtracting difference stock with reserve
                //         $res_stock->save();

                //         //Adding difference from last stock
                //         $last_record = $res_stocks->sortByDesc('id')->first();
                //         if ($last_record) {
                //             $last_record->stock_m_out->available_stock += $res_difference;
                //             $last_record->stock_m_out->save();
                //         }
                //     }
                //     else if ($res_difference < 0) {
                //         $res_difference = $value - $res_stocks->sum('reserved_quantity'); //getting difference
                //         $res_stock = $res_stocks->first();
                //         $res_stock->reserved_quantity += $res_difference; //adding difference stock with reserve
                //         $res_stock->save();

                //         //subtracting difference from last stock
                //         $last_record = $res_stocks->sortByDesc('id')->first();
                //         if ($last_record) {
                //             $last_record->stock_m_out->available_stock -= $res_difference;
                //             $last_record->stock_m_out->save();
                //         }
                //     }
                // }
                //new logic ends here

	    	}
	    	elseif($key == 'quantity_received_2')
          	{
          		if(true)
          		{
          			$group = PoGroup::find($request->po_group_id);
		            if($group->is_confirm == 1)
		            {
		            	$quantity_received_2 = $value - $po_detail->quantity_received_2;
		            	// $quantity_received_2 = ($quantity_received_2/$po_detail->product->unit_conversion_rate);
		            	// $decimal_places = $po_detail->product->units->decimal_places;
               //          if($decimal_places == 0)
               //          {
               //              $quantity_received_2 = round($quantity_received_2,0);
               //          }
               //          elseif($decimal_places == 1)
               //          {
               //              $quantity_received_2 = round($quantity_received_2,1);
               //          }
               //          elseif($decimal_places == 2)
               //          {
               //              $quantity_received_2 = round($quantity_received_2,2);
               //          }
               //          elseif($decimal_places == 3)
               //          {
               //              $quantity_received_2 = round($quantity_received_2,3);
               //          }
               //          else
               //          {
               //              $quantity_received_2 = round($quantity_received_2,4);
               //          }
		            	$stock = StockManagementIn::where('expiration_date',$po_detail->expiration_date_2)->where('product_id',$po_detail->product_id)->where('warehouse_id',$warehouse_id)->first();
						if($stock != null)
		                {
							$stock_out               = new StockManagementOut;
							$stock_out->smi_id       = $stock->id;
							$stock_out->p_o_d_id     = $po_detail->id;
							$stock_out->product_id   = $po_detail->product_id;
		                    if($quantity_received_2 < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received_2;
		                        $stock_out->available_stock = $quantity_received_2;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received_2;
		                       $stock_out->available_stock  = $quantity_received_2;
		                       $stock_out->supplier_id  = @$po_detail->supplier_id != null ? @$po_detail->supplier_id : @$manual_supplier->id;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    $stock_out->save();

		                    if($quantity_received_2 < 0)
		                    {
		                    	$dummy_order = Order::createManualOrder($stock_out,'Quantity received 2 updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received 2 updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now());
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
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
		                                            }
		                                            else
		                                            {
		                                            	$history_quantity = $out->available_stock;
		                                                $stock_out->parent_id_in .= $out->id.',';
		                                                $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
		                                                $out->available_stock = 0;
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
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
		                                      	  $history_quantity = $stock_out->available_stock;
		                                          $out->parent_id_in .= $stock_out->id.',';
		                                          $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
		                                          $out->available_stock = 0;
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
		                                      }
		                                      else
		                                      {
		                                      	  $history_quantity = $out->available_stock;
		                                          $out->parent_id_in .= $out->id.',';
		                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
		                                          $stock_out->available_stock = 0;
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
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
		                    $stock_out->p_o_d_id     = $po_detail->id;
		                    $stock_out->product_id   = $po_detail->product_id;
		                    if($quantity_received_2 < 0)
		                    {
		                        $stock_out->quantity_out = $quantity_received_2;
		                        $stock_out->available_stock = $quantity_received_2;
		                    }
		                    else
		                    {
		                       $stock_out->quantity_in  = $quantity_received_2;
		                       $stock_out->available_stock  = $quantity_received_2;
		                       $stock_out->supplier_id  = @$po_detail->supplier_id != null ? @$po_detail->supplier_id : $manual_supplier->id;
		                    }
		                    $stock_out->created_by   = Auth::user()->id;
		                    $stock_out->warehouse_id = $warehouse_id;
		                    $stock_out->save();

		                    if($quantity_received_2 < 0)
		                    {
		                    	$dummy_order = Order::createManualOrder($stock_out,'Quantity received 2 updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now(), 'Quantity received 2 updated in Complete TD '.@$po_detail->po_id. ' by '.@Auth::user()->user_name. ' on '. Carbon::now());
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
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
		                                            }
		                                            else
		                                            {
		                                            	$history_quantity = $out->available_stock;
		                                                $stock_out->parent_id_in .= $out->id.',';
		                                                $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
		                                                $out->available_stock = 0;
		                                                $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($out,$stock_out,$po_detail,abs($history_quantity));
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
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
		                                      }
		                                      else
		                                      {
		                                      	  $history_quantity = $stock_out->available_stock;
		                                          $out->parent_id_in .= $out->id.',';
		                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
		                                          $stock_out->available_stock = 0;
		                                          $new_stock_out_history = (new StockOutHistory)->setHistoryForPO($stock_out,$out,$po_detail,abs($history_quantity));
		                                      }
		                                      $out->save();
		                                      $stock_out->save();
		                              }
		                          }
		                      }
		                    }
		                }

						// $warehouse_products = WarehouseProduct::where('warehouse_id',Auth::user()->get_warehouse->id)->where('product_id',$po_detail->product_id)->first();
						// $warehouse_products->current_quantity += round($quantity_received_2,3);
						// $warehouse_products->save();
		                $stc = $po_detail->update_stock_card_for_completed_td($po_detail,$value,$key);

		            }


		          	$params['term_key']  = $key;
		            $params['old_value'] = $po_detail->$key;
		            $params['new_value'] = $value;
		            $params['ip_address'] = $request->ip();
		            $this->saveProductReceivingHistory($params, $po_detail->id,$request->po_group_id);
		            $po_detail->$key = $value;
	        	}
	    	}
	    	elseif($key == 'expiration_date')
            {
                $value = str_replace("/","-",$request->expiration_date);
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
            // elseif($key == 'custom_invoice_number')
            // {
            // 	$po->invoice_number = $value;
            // 	$po->save();
            // }
	    	else
	    	{
            	$po_detail->$key = $value;
          	}
        }
        $po_detail->save();

        return response()->json(['success' => true]);
	}

	public function fullQtyAdd(Request $request)
	{
		$po_group_details = PoGroupDetail::select('purchase_order_id')->where('po_group_id',$request->id)->get();
		foreach($po_group_details as $po_group_detail)
		{
			$po_details = PurchaseOrderDetail::where('po_id',$po_group_detail->purchase_order_id)->get();
			foreach($po_details as $po_detail)
			{
				$po_detail->quantity_received = $po_detail->quantity;
				$po_detail->save();
			}
		}

		return response()->json(['success' => true]);
	}

	private function saveProductReceivingHistory($params = [], $pod_id,$po_group_id)
	{
		$product_receiving_history              = new ProductReceivingHistory;
		$product_receiving_history->po_group_id = $po_group_id;
		$product_receiving_history->pod_id      = $pod_id;

        if($params['term_key'] == 'quantity_received' || $params['term_key'] == 'quantity_received_2' || $params['term_key'] == 'expiration_date' || $params['term_key'] == 'expiration_date_2'){
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

    public function saveGoodsData(Request $request)
	{
		// dd($request->all());
		$group_detail = PurchaseOrderDetail::where('id',$request->id)->first();
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

	public function confirmPoGroup(Request $request)
	{
		$po_group = PoGroup::find($request->id);
		$po_group->is_confirm = 1;
		$po_group->save();
		$purchase_orders = PurchaseOrder::whereIn('id',PoGroupDetail::where('po_group_id',$request->id)->pluck('purchase_order_id'))->get();
		foreach ($purchase_orders as $PO) {
			if($PO->from_warehouse_id != null && $PO->supplier_id == null)
			{

			}
			else
			{
				$PO->status = 15;
				$PO->save();
				// PO status history maintaining
				$page_status = Status::select('title')->whereIn('id',[14,15])->pluck('title')->toArray();
	            $poStatusHistory = new PurchaseOrderStatusHistory;
	            $poStatusHistory->user_id    = Auth::user()->id;
	            $poStatusHistory->po_id      = $PO->id;
	            $poStatusHistory->status     = $page_status[0];
	            $poStatusHistory->new_status = $page_status[1];
	            $poStatusHistory->save();
			}


			$supplier_id = $PO->supplier_id;
			$purchase_order_details = PurchaseOrderDetail::where('po_id',$PO->id)->whereNotNull('purchase_order_details.product_id')->get();
			foreach ($purchase_order_details as $p_o_d)
			{
				$quantity_inv = $p_o_d->quantity_received/$p_o_d->product->unit_conversion_rate;
				$quantity_inv_2 = $p_o_d->quantity_received_2/$p_o_d->product->unit_conversion_rate;
				$decimal_places = $p_o_d->product->sellingUnits->decimal_places;
                if($decimal_places == 0)
                {
                    $quantity_inv = round($quantity_inv,0);
                    $quantity_inv_2 = round($quantity_inv_2,0);
                }
                elseif($decimal_places == 1)
                {
                    $quantity_inv = round($quantity_inv,1);
                    $quantity_inv_2 = round($quantity_inv_2,1);
                }
                elseif($decimal_places == 2)
                {
                    $quantity_inv = round($quantity_inv,2);
                    $quantity_inv_2 = round($quantity_inv_2,2);
                }
                elseif($decimal_places == 3)
                {
                    $quantity_inv = round($quantity_inv,3);
                    $quantity_inv_2 = round($quantity_inv_2,3);
                }
                else
                {
                    $quantity_inv = round($quantity_inv,4);
                    $quantity_inv_2 = round($quantity_inv_2,4);
                }

				if($p_o_d->quantity == $quantity_inv)
				{
					$p_o_d->is_completed = 1;
					$p_o_d->save();
				}
				if($p_o_d->order_product_id != null)
				{
					$p_o_d->order_product->status = 10;
					$p_o_d->order_product->save();

					$order_products_status_count = OrderProduct::where('order_id',$p_o_d->order_id)->where('is_billed','=','Product')->where('quantity','!=',0)->where('status','!=',10)->count();
                    if($order_products_status_count == 0)
                    {
                        $p_o_d->order_product->get_order->status = 10;
                        $p_o_d->order_product->get_order->save();
                        // dd('here');
                         $order_history = new OrderStatusHistory;
	                    $order_history->user_id = Auth::user()->id;
	                    $order_history->order_id = @$p_o_d->order_product->get_order->id;
	                    $order_history->status = 'DI(Importing)';
	                    $order_history->new_status = 'DI(Waiting To Pick)';
	                    $order_history->save();
                    }
				}
				if($quantity_inv != 0 && $quantity_inv != null)
            	{
				$stock = StockManagementIn::where('expiration_date',$p_o_d->expiration_date)->where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->first();
				if($stock != null)
                {
                    $stock_out               = new StockManagementOut;
                    $stock_out->smi_id       = $stock->id;
                    $stock_out->p_o_d_id     = $p_o_d->id;
                    $stock_out->product_id   = $p_o_d->product_id;
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
                    $stock_out->warehouse_id = $PO->to_warehouse_id;
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
                                                $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                $stock_out->available_stock = 0;
                                            }
                                            else
                                            {
                                                $stock_out->parent_id_in .= $out->id.',';
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
                                          $out->parent_id_in .= $stock_out->id.',';
                                          $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                          $out->available_stock = 0;
                                      }
                                      else
                                      {
                                          $out->parent_id_in .= $out->id.',';
                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                          $stock_out->available_stock = 0;
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
                	if($p_o_d->expiration_date == null)
                	{
	                    $stock = StockManagementIn::where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->whereNull('expiration_date')->first();
                	}

                    if($stock == null)
                    {
                        $stock = new StockManagementIn;
                    }

                    $stock->title           = 'Adjustment';
                    $stock->product_id      = $p_o_d->product_id;
                    $stock->quantity_in     = $quantity_inv;
                    $stock->created_by      = Auth::user()->id;
                    $stock->warehouse_id    = $PO->to_warehouse_id;
                    $stock->expiration_date = $p_o_d->expiration_date;
                    $stock->save();

                    $stock_out               = new StockManagementOut;
                    $stock_out->smi_id       = $stock->id;
                    $stock_out->p_o_d_id     = $p_o_d->id;
                    $stock_out->product_id   = $p_o_d->product_id;
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
                    $stock_out->warehouse_id = $PO->to_warehouse_id;
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
                                                $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                $stock_out->available_stock = 0;
                                            }
                                            else
                                            {
                                                $stock_out->parent_id_in .= $out->id.',';
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
                                          $out->parent_id_in .= $stock_out->id.',';
                                          $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                          $out->available_stock = 0;
                                      }
                                      else
                                      {
                                          $out->parent_id_in .= $out->id.',';
                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                          $stock_out->available_stock = 0;
                                      }
                                      $out->save();
                                      $stock_out->save();
                              }
                          }
                      }
                    }
                }

				// $warehouse_products = WarehouseProduct::where('warehouse_id',$PO->to_warehouse_id)->where('product_id',$p_o_d->product_id)->first();
				// $warehouse_products->current_quantity += round($quantity_inv,3);
				// $warehouse_products->save();

				  DB::beginTransaction();
	              try
	                {
	                  $new_his = new QuantityReservedHistory;
	                  $re      = $new_his->updateTDCurrentQuantity($PO,$p_o_d,$quantity_inv,'add');
	                  DB::commit();
	                }
	                catch(\Excepion $e)
	                {
	                  DB::rollBack();
	                }
				}
				if($quantity_inv_2 != 0 && $quantity_inv_2 != null)
				{
					$stock = StockManagementIn::where('expiration_date',$p_o_d->expiration_date_2)->where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->first();
					if($stock != null)
	                {
	                    $stock_out               = new StockManagementOut;
	                    $stock_out->smi_id       = $stock->id;
	                    $stock_out->p_o_d_id     = $p_o_d->id;
	                    $stock_out->product_id   = $p_o_d->product_id;
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
	                    $stock_out->warehouse_id = $PO->to_warehouse_id;
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
	                                                $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
	                                                $stock_out->available_stock = 0;
	                                            }
	                                            else
	                                            {
	                                                $stock_out->parent_id_in .= $out->id.',';
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
	                                          $out->parent_id_in .= $stock_out->id.',';
	                                          $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	                                          $out->available_stock = 0;
	                                      }
	                                      else
	                                      {
	                                          $out->parent_id_in .= $out->id.',';
	                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	                                          $stock_out->available_stock = 0;
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
	                	if($p_o_d->expiration_date_2 == null)
	                	{
		                    $stock = StockManagementIn::where('product_id',$p_o_d->product_id)->where('warehouse_id',$PO->to_warehouse_id)->whereNull('expiration_date')->first();
	                	}

	                    if($stock == null)
	                    {
	                        $stock = new StockManagementIn;
	                    }

	                    $stock->title           = 'Adjustment';
	                    $stock->product_id      = $p_o_d->product_id;
	                    $stock->quantity_in     = $quantity_inv_2;
	                    $stock->created_by      = Auth::user()->id;
	                    $stock->warehouse_id    = $PO->to_warehouse_id;
	                    $stock->expiration_date = $p_o_d->expiration_date_2;
	                    $stock->save();

	                    $stock_out               = new StockManagementOut;
	                    $stock_out->smi_id       = $stock->id;
	                    $stock_out->p_o_d_id     = $p_o_d->id;
	                    $stock_out->product_id   = $p_o_d->product_id;
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
	                    $stock_out->warehouse_id = $PO->to_warehouse_id;
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
	                                                $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
	                                                $stock_out->available_stock = 0;
	                                            }
	                                            else
	                                            {
	                                                $stock_out->parent_id_in .= $out->id.',';
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
	                                          $out->parent_id_in .= $stock_out->id.',';
	                                          $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	                                          $out->available_stock = 0;
	                                      }
	                                      else
	                                      {
	                                          $out->parent_id_in .= $out->id.',';
	                                          $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
	                                          $stock_out->available_stock = 0;
	                                      }
	                                      $out->save();
	                                      $stock_out->save();
	                              }
	                          }
	                      }
	                    }
	                }

					// $warehouse_products = WarehouseProduct::where('warehouse_id',$PO->to_warehouse_id)->where('product_id',$p_o_d->product_id)->first();
					// $warehouse_products->current_quantity += round($quantity_inv_2,3);
					// $warehouse_products->save();

					DB::beginTransaction();
		              try
		                {
		                  $new_his = new QuantityReservedHistory;
		                  $re      = $new_his->updateTDCurrentQuantity($PO,$p_o_d,$quantity_inv_2,'add');
		                  DB::commit();
		                }
		                catch(\Excepion $e)
		                {
		                  DB::rollBack();
		                }
				}
			}
		}

		$group_status_history = new PoGroupStatusHistory;
        $group_status_history->user_id = Auth::user()->id;
        $group_status_history->po_group_id = @$po_group->id;
        $group_status_history->status = 'Confirmed';
        $group_status_history->new_status = 'Closed Product Receiving Queue';
        $group_status_history->save();

		return response()->json(['success' => true]);
	}

	public function getProductSuppliersData($id)
    {
    	// dd($id);
        $query = SupplierProducts::with('supplier','product')->where('product_id',$id)->get();

         return Datatables::of($query)

            ->addColumn('action',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= 'd-none';
                }
                else
                {
                  $class= '';
                }
                $html_string = '<button type="button" style="cursor: pointer;" class="btn-xs btn-danger '.$class.'" data-prodisupid="'.$item->supplier->id.'" data-prodid="'.$item->product_id.'" name="delete_sup" id="delete_sup"><i class="fa fa-trash"></i></button>';
                return $html_string;
            })
            ->addColumn('company',function($item){
                return $item->supplier->company;
                // return  $html_string = '<a target="_blank" href="'.url('importing/get-supplier-detail/'.$item->supplier->id).'"  >'.$ref_no.'</a>';

            })
            ->addColumn('product_supplier_reference_no',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                }
                else
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="product_supplier_reference_no"  data-fieldvalue="'.$item->product_supplier_reference_no.'">'.($item->product_supplier_reference_no != NULL ? $item->product_supplier_reference_no : "--").'</span>
                <input type="text" style="width:100%;" name="product_supplier_reference_no" class="prodSuppFieldFocus d-none" value="'.$item->product_supplier_reference_no.'">';
                return $html_string;
            })
            ->addColumn('import_tax_actual',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                }
                else
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="import_tax_actual"  data-fieldvalue="'.$item->import_tax_actual.'">'.($item->import_tax_actual != NULL ? $item->import_tax_actual : "--").'</span>
                <input type="number" style="width:100%;" name="import_tax_actual" class="prodSuppFieldFocus d-none" value="'.$item->import_tax_actual.'">';
                return $html_string;
            })
            ->addColumn('gross_weight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                }
                else
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="gross_weight"  data-fieldvalue="'.$item->gross_weight.'">'.($item->gross_weight != NULL ? $item->gross_weight : "--").'</span>
                <input type="number" style="width:100%;" name="gross_weight" class="prodSuppFieldFocus d-none" value="'.$item->gross_weight.'">';
                return $html_string;
            })
            ->addColumn('freight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                }
                else
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="freight"  data-fieldvalue="'.$item->freight.'">'.($item->freight != NULL ? $item->freight : "--").'</span>
                <input type="number" style="width:100%;" name="freight" class="prodSuppFieldFocus d-none" value="'.$item->freight.'">';
                return $html_string;
            })
            ->addColumn('landing',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                }
                else
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="landing"  data-fieldvalue="'.$item->landing.'">'.($item->landing != NULL ? $item->landing : "--").'</span>
                <input type="number" style="width:100%;" name="landing" class="prodSuppFieldFocus d-none" value="'.$item->landing.'">';
                 return $html_string;
            })
            ->addColumn('buying_price',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                }
                else
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="buying_price"  data-fieldvalue="'.$item->buying_price.'">'.($item->buying_price != NULL ? $item->buying_price : "--").'</span>
                <input type="number" style="width:100%;" name="buying_price" class="prodSuppFieldFocus d-none" value="'.$item->buying_price.'">';
                return $html_string;
            })
            ->addColumn('leading_time',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                }
                else
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="leading_time"  data-fieldvalue="'.$item->leading_time.'">'.($item->leading_time != NULL ? $item->leading_time : "--").'</span>
                <input type="number" style="width:100%;" name="leading_time" class="prodSuppFieldFocus d-none" value="'.$item->leading_time.'">';
                return $html_string;
            })
            ->setRowId(function ($item) {
                    return $item->id;
            })
             // greyRow is a custom style in style.css file
            ->setRowClass(function ($item) {
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                    return $item->supplier_id == $checkLastSupp->supplier_id ? 'greyRow' : '';
                }
            })
            ->rawColumns(['action','company','product_supplier_reference_no','import_tax_actual','buying_price','freight','landing','leading_time','gross_weight'])
            ->make(true);

    }

 	public function exportGroupToPDFF(Request $request)
    {
		$data=$request->all();
		$check=PdfsStatus::where('user_id',Auth::user()->id)->where('group_id',$request->po_group_id)->first();
		if($check==null)
		{
			 $pdfStatus=new PdfsStatus();
			 $pdfStatus->user_id=Auth::user()->id;
			 $pdfStatus->group_id=$request->po_group_id;
			 $pdfStatus->status=1;
			 if($pdfStatus->save())
			 {


				 $group_to_pdf_job = (new ExportGroupToPDFJob($data,Auth::user()->id));
				 dispatch($group_to_pdf_job);
				 return response()->json(['success'=>true]);

			 }
			 return response()->json(['success'=>false]);
		}
		elseif($check->status==0 || $check->status==2)
		{
			 PdfsStatus::where('user_id',Auth::user()->id)->where('group_id',$request->po_group_id)->update(['status'=>1]);
			 $group_to_pdf_job = (new ExportGroupToPDFJob($data,Auth::user()->id));
			 dispatch($group_to_pdf_job);
			 return response()->json(['success'=>true]);
		}


		// $group_detail = PoGroupProductDetail::where('status',1)->where('po_group_id',$id);

		// if($request->po_group_supplier_id != null)
		// {
		// 	$group_detail = $group_detail->where('supplier_id',$request->po_group_supplier_id);
		// }


		// if($request->po_group_supplier_id != null)
		// {
		// 	$group_detail = $group_detail->where('product_id',$request->po_group_supplier_id);
		// }


		// $group_detail = $group_detail->get();


		/*$table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'open_product_receiving')->first();
		if($table_hide_columns == NULL)
		{
			$not_in_arr = [];
		}
		else{
		$not_in_arr = explode(',', $table_hide_columns->hide_columns);
		}*/



		// $pdf = PDF::loadView('warehouse.products.completed_group_print_pdf',compact('group_detail'))->setPaper('a4', 'landscape');
		// dd($pdf);
        // // making pdf name starts
        // $makePdfName = 'Group No-'.$request->po_group_id;
        // return $pdf->download($makePdfName.'.pdf');

    }
	public function getPdfStatus(Request $request)
	{

		$status=PdfsStatus::where('user_id',Auth::user()->id)->where('group_id',$request->group_id)->first();
		if($status!=null)
		{
			return response()->json(['status'=>$status->status]);
		}
		else
		{
			return response()->json(['status'=>0]);
		}
	}

    public function exportCompletedGroupToPDF(Request $request)
    {
    	// dd('here');
    	// $group_detail = DB::table('po_groups')
     //        ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
     //        ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
     //        ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
     //        ->select('po_groups.*','po_groups.target_receive_date as datee','po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$request->po_group_id)->where('purchase_order_details.quantity','!=',0)
     //        ->get();
            // dd($group_detail);
            // return view('warehouse.products.completed_group_print_pdf',compact('group_detail','id'));

        // $view = view('warehouse.products.completed_group_print_pdf',compact('group_detail'))->render();
        // return $view;
	   // return response()->json(['view'=>$view,'success'=>true]);
	   $data=$request->all();
	   $check=PdfsStatus::where('user_id',Auth::user()->id)->where('group_id',$request->po_group_id)->first();
	   if($check==null)
	   {
			$pdfStatus=new PdfsStatus();
			$pdfStatus->user_id=Auth::user()->id;
			$pdfStatus->group_id=$request->po_group_id;
			$pdfStatus->status=1;
			if($pdfStatus->save())
			{


				$group_to_pdf_job = (new ExportGroupToPDFJob($data,Auth::user()->id));
				dispatch($group_to_pdf_job);
				return response()->json(['success'=>true]);

			}
			return response()->json(['success'=>false]);
	   }
	   elseif($check->status==0 || $check->status==2)
	   {
			PdfsStatus::where('user_id',Auth::user()->id)->where('group_id',$request->po_group_id)->update(['status'=>1]);
			$group_to_pdf_job = (new ExportGroupToPDFJob($data,Auth::user()->id));
			dispatch($group_to_pdf_job);
			return response()->json(['success'=>true]);
	   }

	   //Commented becasue of queue
    	// $id = $request->po_group_id;
      	// $group_detail = PoGroupProductDetail::where('status',1)->where('po_group_id',$id)->get();

        // $pdf = PDF::loadView('warehouse.products.completed_group_print_pdf',compact('group_detail'))->setPaper('a4', 'landscape');
        //     // dd($pdf);

        // // making pdf name starts
        // $makePdfName = 'Group No-'.$request->po_group_id;
        // // return $pdf->stream(
        // //          $makePdfName.'.pdf',
        // //           array(
        // //             'Attachment' => 0
        // //           )
        // //         );
        // return $pdf->download($makePdfName.'.pdf');

    }


}
