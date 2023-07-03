<?php

namespace App\Http\Controllers\Purchasing;

use App\ExportStatus;
use App\Exports\FilteredStockProductsExport;
use App\Exports\purchaseListExport;
use App\Exports\purchasingReportExport;
use App\Exports\purchasingReportMainExport;
use App\Http\Controllers\Controller;
use App\ImportFileHistory;
use App\ProductTypeTertiary;
use App\Imports\ProductQtyInBulkImport;
use App\Jobs\PurchaseListExpJob;
use App\Jobs\PurchasingReportGroupedJob;
use App\Jobs\PurchasingReportJob;
use App\Jobs\StockProductsExportjob;
use App\Jobs\StockCompletedProductsExportJob;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Country;
use App\Models\Common\OrderHistory;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\StockManagementIn;
use App\Models\Common\Supplier;
use App\Jobs\BulkStockAdjustmentJob;
use App\Models\Common\SupplierCategory;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Unit;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\User;
use App\Models\Sales\Customer;

use Auth;
use Carbon\Carbon;
use DB;
use Excel;
use File;
use Illuminate\Http\Request;
use Validate;
use Yajra\Datatables\Datatables;
use App\Helpers\QuantityReservedHistory;
use App\Variable;
use App\General;
use App\Notification;
use App\Models\Common\Configuration;
use App\Models\Common\ProductSecondaryType;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\QuotationConfig;
use App\TempStockAdjustment;
use App\Helpers\Datatables\PurchaseListDatatable;
use App\Helpers\ProductConfigurationHelper;

class PurchasingController extends Controller
{
    protected $user;
    protected $global_terminologies=[];
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
        $extra_space_for_select2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies,'sys_name' => $sys_name,'sys_logos' => $sys_logos,'sys_color' => $sys_color,'sys_border_color' => $sys_border_color,'btn_hover_border' => $btn_hover_border,'current_version' => $current_version,'dummy_data' => $dummy_data,'extra_space' => $extra_space_for_select2, 'product_detail_section' => $product_detail_section]);
    }
    public function index()
    {
        // copied from appserviceprovider
        $targetShipDate='';
        $globalAccessConfig3 = QuotationConfig::where('section','target_ship_date')->first();
        if($globalAccessConfig3!=null)
        {
            $targetShipDate=unserialize($globalAccessConfig3->print_prefrences);
        }
        else
        {
            $targetShipDate=null;
        }
        //
        $setting_table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'purchase_list')->first();
        $display_purchase_list = ColumnDisplayPreference::where('type', 'purchase_list')->where('user_id', Auth::user()->id)->first();
        // $suppliersFilter = Supplier::where('status',1)->orderBy('reference_name')->get();
        $warehouseFilter = Warehouse::where('status',1)->orderBy('warehouse_title','ASC')->get();
        $purchaseOrdersW = PurchaseOrder::with('PoSupplier')->where('status',12)->orderBy('ref_id','ASC')->get();
        $purchaseOrdersD = PurchaseOrder::with('PoSupplier')->where('status',13)->orderBy('ref_id','ASC')->get();
        $getWarehouses = Warehouse::where('status',1)->get();

        // Getting those suppliers who are listed in purchase list table
        $suppliersFilter = OrderProduct::whereHas('get_order', function($q){
            $q->where('primary_status',2);
            $q->where('status',7);
        })->where('order_products.status',7)->where('order_products.quantity','!=',0)->where('order_products.product_id','!=',null)->join('suppliers as s', 's.id', '=', 'order_products.supplier_id')->distinct()->select('s.id', 's.reference_name')->orderBy('s.reference_name')->get();

        return $this->render('users.products.purchasing-list',compact('setting_table_hide_columns','display_purchase_list','suppliersFilter','warehouseFilter','purchaseOrdersW','purchaseOrdersD','getWarehouses','targetShipDate'));
    }

    public function getPosOfSelecedSupplier(Request $request)
    {

        if ($request->supplier_id != null) {
            $supplier_id = explode('-', $request->supplier_id)[1];
            $waiting_pos = PurchaseOrder::with('PoSupplier')->where('supplier_id', $supplier_id)->where('status',12)->orderBy('ref_id','ASC')->get();
            $shipping_pos = PurchaseOrder::with('PoSupplier')->where('supplier_id', $supplier_id)->where('status',13)->orderBy('ref_id','ASC')->get();
        }
        else {
            $waiting_pos = PurchaseOrder::with('PoSupplier')->where('status',12)->orderBy('ref_id','ASC')->get();
            $shipping_pos = PurchaseOrder::with('PoSupplier')->where('status',13)->orderBy('ref_id','ASC')->get();
        }


        $html = '<option value="" disabled="" selected="">Choose PO</option>';
        if(@$waiting_pos->count() > 0)
        {
            $wc_label = (!array_key_exists('waiting_confrimation', $this->global_terminologies)) ? "Waiting Confirmation" : $this->global_terminologies['waiting_confrimation'];
            $html .= '<optgroup label="'.$wc_label.'">';
            foreach($waiting_pos as $pow)
            {
                $html .= '<option value="'.$pow->id.'"> (PO# '.$pow->ref_id.') '.$pow->PoSupplier->reference_name.' </option>';
            }
            $html .= '</optgroup>';
        }
        if(@$shipping_pos->count() > 0)
        {
            $html .= '<optgroup label="Waiting Shipping Info">';
            foreach($shipping_pos as $pod)
            {
                $html .= '<option value="'.$pod->id.'"> (PO# '.$pod->ref_id.') '.$pod->PoSupplier->reference_name.' </option>';
            }
            $html .= '</optgroup>';
        }
        return response()->json(['html' =>$html]);
    }

    public function getPurchaseListData(Request $request)
    {
        $query = OrderProduct::with('product.productCategory','order_product_note','get_order.user','get_order.customer','product.productSubCategory','product.supplier_products.supplier','product.units','product.sellingUnits','product.warehouse_products'
        )->whereHas('get_order', function($q){
            $q->where('primary_status',2);
            $q->where('status',7);
        })->where('order_products.status',7)->where('order_products.quantity','!=',0)->where('order_products.product_id','!=',null)->select('order_products.*');
        $getWarehouses = Warehouse::where('status',1)->orderBy('warehouse_title')->get();

        if($request->supply_from_filter != '')
        {
            $Stype = explode('-', $request->supply_from_filter);
            if($Stype[0] == 's')
            {
                $query->where('order_products.supplier_id', $Stype[1]);
            }
            if($Stype[0] == 'w')
            {
                $query->where('order_products.from_warehouse_id', $Stype[1]);
            }
        }

        if($request->supply_to_filter != '')
        {
            $query->where('order_products.warehouse_id', $request->supply_to_filter);
        }


        if($request->date_delivery_filter_from != '')
        {
            $date = str_replace("/","-",$request->date_delivery_filter_from);
            $date =  date('Y-m-d',strtotime($date));

            $query->whereHas('get_order', function($q) use($date) {
                $q->where('orders.target_ship_date','>=', $date);
            });
        }

        if($request->date_delivery_filter_to != '')
        {
            $date = str_replace("/","-",$request->date_delivery_filter_to);
            $date =  date('Y-m-d',strtotime($date));

            $query->whereHas('get_order', function($q) use($date) {
                $q->where('orders.target_ship_date','<=', $date);
            });
        }

        $query = OrderProduct::PurchaseListSorting($request, $query, $getWarehouses);
        $dt =  Datatables::of($query);
        $add_columns = ['remarks', 'supply_to', 'supply_from', 'bill_unit', 'quantity', 'pieces', 'delivery_date', 'target_ship_date', 'purchase_date', 'supplier_product_ref', 'refrence_code', 'reference_name', 'category_id', 'primary_category', 'short_desc', 'ref_id', 'sale', 'checkbox'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $getWarehouses){
                return PurchaseListDatatable::returnAddColumn($column, $item, $getWarehouses);
            });
        }

        $filter_columns = ['refrence_code', 'reference_name', 'short_desc', 'ref_id', 'sale', 'primary_category', 'category_id', 'bill_unit', 'remarks'];
        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column){
                return PurchaseListDatatable::returnFilterColumn($column, $item, $keyword);
            });
        }

        if($getWarehouses->count() > 0)
        {
            foreach ($getWarehouses as $warehouse)
            {
                $dt->addColumn($warehouse->warehouse_title.'available', function($item) use ($warehouse){
                //   $warehouse_product = $item->product->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                  $warehouse_product = $item->product;
                  if ($warehouse_product != null) {
                    $warehouse_product = $warehouse_product->warehouse_products->where('warehouse_id',$warehouse->id)->first();
                    $available_qty = ($warehouse_product->available_quantity != null) ? $warehouse_product->available_quantity: 0;
                    return round($available_qty, 3);
                  }
                  else{
                    return 0;
                  }
                });
            }
        }

        $dt->setRowId(function ($item) {
            return @$item->id;
        });

        $dt->rawColumns(['checkbox', 'pieces', 'ref_id', 'short_desc', 'primary_category', 'category_id', 'reference_name', 'refrence_code', 'purchase_date', 'delivery_date', 'quantity', 'supply_from', 'supply_to', 'remarks','sale','supplier_product_ref','bill_unit','target_ship_date']);
        return $dt->make(true);
    }

    public function exportPurchaseList(Request $request)
    {
         $status = ExportStatus::where('type','purchase_list_export')->where('user_id', Auth::user()->id)->first();
         if($status == null)
          {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'purchase_list_export';
            $new->status  = 1;
            $new->save();
            PurchaseListExpJob::dispatch($request->sort_order, $request->column_name,$request->supply_from_filter_exp,$request->supply_to_filter_exp,$request->date_delivery_filter_exp1,$request->date_delivery_filter_exp2,$request->tsd_exp,Auth::user()->id, $request->search_value);
            return response()->json(['msg'=>"file is exporting.",'status'=>1,'recursive'=>true]);
          }

      elseif($status->status == 1)
      {
        return response()->json(['msg'=>"File is ready to download.",'status'=>2]);
      }
      elseif($status->status == 0 || $status->status == 2)
      {
        ExportStatus::where('type','purchase_list_export')->update(['status'=>1,'exception'=>null,'user_id'=>Auth::user()->id]);

        PurchaseListExpJob::dispatch($request->sort_order, $request->column_name,$request->supply_from_filter_exp,$request->supply_to_filter_exp,$request->date_delivery_filter_exp1,$request->date_delivery_filter_exp2,$request->tsd_exp,Auth::user()->id, $request->search_value);

        return response()->json(['msg'=>"File is donwloaded.",'status'=>1,'exception'=>null]);
      }
    }

    public function recursiveStatusCheckPurchaseList()
    {
      $status=ExportStatus::where('type','purchase_list_export')->first();
      return response()->json(['msg'=>"File is now getting prepared",'status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name]);
    }

      public function checkStatusFirstTimeForPurchaseList()
      {
          //dd('here');
        $status=ExportStatus::where('type','purchase_list_export')->where('user_id',Auth::user()->id)->first();
        if($status!=null)
        {
          return response()->json(['status'=>$status->status]);
        }
        else
        {
          return response()->json(['status'=>0]);
        }

      }

    public function getPurchaseListProdNote(Request $request)
    {
        // dd($request->all());
        if($request->id != NULL)
        {
            $purchase_list_notes = OrderProductNote::where('order_product_id',$request->id)->orderBy('id','ASC')->get();
        }
        else
        {
            $purchase_list_notes = OrderProductNote::where('pod_id',$request->pod_id)->orderBy('id','ASC')->get();
        }

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
                <td><a href="javascript:void(0);" data-id="'.$note->id.'" id="delete-compl-note" class="delete_po_detail_note actionicon" title="Delete Note"><i class="fa fa-trash" style="color:red;"></i></a></td>
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

    public function addPurchaseListProdNote(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'note_description' => 'required'
        ]);
        if ($request['note_description'] != null || $request['note_description'] != '') {
            $purchase_list  = new OrderProductNote;
            $purchase_list->order_product_id = $request['purchase_list_id'];
            $purchase_list->pod_id = $request['pod_id'];
            $purchase_list->note = $request['note_description'];
            $purchase_list->save();
            return response()->json(['success'=>true,'id' => $request['pod_id']]);
        }

    }

    public function deletePurchaseListProdNote(Request $request)
    {
        $purchase_list  = OrderProductNote::where('id', $request->note_id)->first();
        $purchase_list->delete();
        return response()->json(['success'=>true]);
    }

    public function orderProductSupplierSave(Request $request)
    {
        $Stype = explode('-', $request->supplier_id);
        $orderProductSupplier = OrderProduct::where('id', $request->order_product_id)->first();
        $old_value = $orderProductSupplier->from_warehouse_id != null ? $orderProductSupplier->from_warehouse->warehouse_title : ($orderProductSupplier->supplier_id != null ? $orderProductSupplier->from_supplier->reference_name : '--');
        $order = Order::where('id',$orderProductSupplier->order_id)->first();
        if($Stype[0] == 's')
        {
            $w_user_id = @$orderProductSupplier->get_order->user_created->warehouse_id;

            DB::beginTransaction();
            try
            {
              $new_his = new QuantityReservedHistory;
              $re      = $new_his->updateReservedQuantity($orderProductSupplier,'Reserved delete because of changing SUPPLY FROM from PO','subtract');
              DB::commit();
            }
            catch(\Excepion $e)
            {
              DB::rollBack();
            }

            $orderProductSupplier->supplier_id = $Stype[1];
            $orderProductSupplier->from_warehouse_id = null;
            $orderProductSupplier->user_warehouse_id = @$orderProductSupplier->get_order->from_warehouse_id;
            $orderProductSupplier->is_warehouse = 0;
            $orderProductSupplier->save();

            DB::beginTransaction();
            try
            {
              $new_his = new QuantityReservedHistory;
              $re      = $new_his->updateReservedQuantity($orderProductSupplier,'Reserved Quantity by changing SUPPLY FROM from PO','add');
              DB::commit();
            }
            catch(\Excepion $e)
            {
              DB::rollBack();
            }
        }
        else if($Stype[0] == 'w')
        {
            DB::beginTransaction();
            try
            {
              $new_his = new QuantityReservedHistory;
              $re      = $new_his->updateReservedQuantity($orderProductSupplier,'Reserved delete because of changing SUPPLY FROM from PO','subtract');
              DB::commit();
            }
            catch(\Excepion $e)
            {
              DB::rollBack();
            }

            // $orderProductSupplier->supplier_id = null;
            // $orderProductSupplier->from_warehouse_id = $Stype[1];
            // $orderProductSupplier->user_warehouse_id = $Stype[1];
            // $orderProductSupplier->is_warehouse = 1;

            $user_warehouse = ($orderProductSupplier->user_warehouse_id != null ? $orderProductSupplier->user_warehouse_id : @Auth::user()->get_warehouse->id);
            $orderProductSupplier->from_warehouse_id = $Stype[1];
            $orderProductSupplier->user_warehouse_id = $order->from_warehouse_id;
            $orderProductSupplier->is_warehouse = 1;
            $orderProductSupplier->supplier_id = null;

            $orderProductSupplier->save();

            DB::beginTransaction();
            try
            {
              $new_his = new QuantityReservedHistory;
              $re      = $new_his->updateReservedQuantity($orderProductSupplier,'Reserved Quantity by changing SUPPLY FROM from PO','add');
              DB::commit();
            }
            catch(\Excepion $e)
            {
              DB::rollBack();
            }
        }

        $orderProductSupplier->save();
        $waiting_to_pick = false;
        $user_warehouse = ($orderProductSupplier->user_warehouse_id != null ? $orderProductSupplier->user_warehouse_id : @Auth::user()->get_warehouse->id);
        if($user_warehouse == $orderProductSupplier->from_warehouse_id)
        {
            $orderProductSupplier->status = 10;
            $orderProductSupplier->save();
            $waiting_to_pick = true;
        }
        else
        {
            $orderProductSupplier->status = 7;
            $orderProductSupplier->save();
        }

        $order_status = $order->order_products->where('is_billed','=','Product')->min('status');

        $order->status = $order_status;
        $order->save();

        $order_history = new OrderHistory;
        $order_history->user_id = Auth::user()->id;
        $order_history->reference_number = @$orderProductSupplier->product->refrence_code;
        $order_history->old_value = @$old_value;
        $order_history->column_name = "Supply From";
        $order_history->new_value = @$Stype[1];
        $order_history->order_id = @$orderProductSupplier->order_id;
        $order_history->save();

        if($orderProductSupplier->supplier_id != null)
        {
            $msg = "Supplier Assigned Successfully";
            return response()->json(['success' => true, 'msg' => $msg]);
        }
        else
        {
            $msg = "Warehouse Assigned Successfully";
            return response()->json(['success' => true, 'msg' => $msg,'waiting_to_pick' => $waiting_to_pick]);
        }

    }

    public function orderProductWarehouseSave(Request $request)
    {
        $orderProductSupplier = OrderProduct::where('id', $request->order_product_id)->first();
        $orderProductSupplier->warehouse_id = $request->warehouses_id;
        $orderProductSupplier->save();

        return response()->json([
            'success' => true
        ]);
    }
    public function getTempStockAdjustmentData(Request $request)
    { 
        $user_id = $request->user_id;
        $query = TempStockAdjustment::query();
        $query->where('user_id',$user_id);

        $dt = Datatables::of($query);

        $add_columns = ['PF#','supplier_name','customer_name','adjace1','expiration_date1','adjace2','expiration_date2','adjace3','expiration_date3'];
        $suppliers = Supplier::where('status', 1)->select('id','reference_name')->orderBy('reference_name')->get();
        $customers = Customer::where('status', 1)->select('id','reference_name')->get();
        
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $suppliers, $customers) {
                return TempStockAdjustment::returnAddColumn($column, $item, $suppliers, $customers);
            });
        }

        // $edit_columns = ['company', 'reference_name', 'country', 'state', 'email', 'city', 'tax_id'];

        // foreach ($edit_columns as $column) {
        //     $dt->editColumn($column, function ($item) use ($column) {
        //         return Supplier::returnEditColumn($column, $item);
        //     });
        // }

        // $filter_columns = ['supplier_nunmber', 'country'];

        // foreach ($filter_columns as $column) {
        //     $dt->filterColumn($column, function ($item, $keyword) use ($column) {
        //         return Supplier::returnFilterColumn($column, $item, $keyword);
        //     });
        // }

        $dt->rawColumns(['PF#','supplier_name','customer_name','adjace1','expiration_date1','adjace2','expiration_date2','adjace3','expiration_date3']);
        return $dt->make(true);
    }
    public function updateCustomerOrSupplierName(Request $request)
    { 
        // dd($request->all());
        if($request->type == "supplier"){
            
           $data = TempStockAdjustment::find($request->id);
            if ($data) {
                $incompleteRows = $data->incomplete_rows;
                $incompleteRows[15] = $request->selected_name;
                $data->incomplete_rows = $incompleteRows;
                $data->save();
             return response()->json(['success' => true,'successMsg' => "Supplier Name Change Successfully."]);
            }

        }else{

            $data = TempStockAdjustment::find($request->id);
            if ($data) {
                $incompleteRows = $data->incomplete_rows;
                $incompleteRows[16] = $request->selected_name;
                $data->incomplete_rows = $incompleteRows;
                $data->save();
             return response()->json(['success' => true,'successMsg' => "Customer Name Change Successfully."]);
            }
        }
        
    }
    public function saveRemarksInOrderProd(Request $request)
    {
        $order_product = OrderProduct::where('id', $request->op_id)->first();
            foreach($request->except('op_id') as $key => $value)
            {
                if($value != '')
                {
                    $order_product->$key = $value;
                }
                else
                {
                    $order_product->$key = NULL;
                }
            }
        $order_product->save();

        $new_remarks = $order_product->remarks;

        return response()->json(['success' => true, 'remarks' => $new_remarks]);
    }

    public function bulkUploadQuantity(Request $request)
    {
        $suppliers = Supplier::where('status',1)->orderBy('reference_name')->get();
        $warehouses = Warehouse::where('status',1)->orderBy('warehouse_title')->get();
        $primary_category = ProductCategory::where('parent_id',0)->orderBy('title')->get();
        $types = ProductType::orderBy('title')->get();
        $types_2 = ProductSecondaryType::orderBy('title','asc')->get();
        return $this->render('users.products.add-bulk-quantity',compact('suppliers','primary_category','warehouses','types','types_2'));
    }

    public function getFilteredStockProdExcel(Request $request)
    {
        // dd(date('Y-m-d-H:i:s'));

        $name='Test';
        $statusCheck=ExportStatus::where('type','stock_bulk_upload')->where('user_id',Auth::user()->id)->first();
        $data=$request->all();
        if($statusCheck==null)
        {
            $new=new ExportStatus();
            $new->type='stock_bulk_upload';
            $new->user_id=Auth::user()->id;
            $new->status=1;
            if($new->save())
            {
                // if($request->suppliers == null && $request->primary_category == null)
                // {
                //     $name = 'All';
                // }
                // else
                // {
                //     $name = 'Filtered';
                // }
                StockProductsExportJob::dispatch($name,$data,Auth::user()->id);
                return response()->json(['status'=>1]);
            }

        }
        else if($statusCheck->status==0 || $statusCheck->status==2 || $statusCheck->status==3)
        {

            ExportStatus::where('type','stock_bulk_upload')->where('user_id',Auth::user()->id)->update(['status'=>1,'exception'=>null]);
            // if($request->suppliers == null && $request->primary_category == null)
            // {
            //     $name = 'All';
            // }
            // else
            // {
            //     $name = 'Filtered';
            // }
            StockProductsExportJob::dispatch($name,$data,Auth::user()->id);
            return response()->json(['status'=>1]);

        }
        {
            return response()->json(['msg'=>'Export already being prepared','status'=>2]);
        }
        // $data=$request->all();
        // if($request->suppliers == null && $request->primary_category == null)
        // {
        //     $name = 'All';
        // }
        // else
        // {
        //     $name = 'Filtered';
        // }
        // // $complete_products_export_job = (new CompleteProductsExportJob($data,Auth::user()->id));
        // // dispatch($complete_products_export_job);
        // $stock_proudcts_export_job=new StockProductsExportJob($name,$data);
        // dispatch($stock_proudcts_export_job);
        // return response()->json(['success'=>true]);
        // return Excel::save(new FilteredStockProductsExport($request->warehouses,$request->suppliers,$request->primary_category, $request->sub_category, $request->types), $name.' Products Data Set.xlsx');
    }
    //get completed products excel exports
    public function getCompletedStockProdExcel(Request $request)
    {
        $name='Test';
        $statusCheck=ExportStatus::where('type','stock_completed_products_download')->where('user_id',Auth::user()->id)->first();
        $data=TempStockAdjustment::where('user_id',Auth::user()->id)->get();
        // dd($data[0]->incomplete_rows[15]);
        // dd($data->pluck('product_id')->toArray());
        if($statusCheck==null)
        {
            $new=new ExportStatus();
            $new->type='stock_completed_products_download';
            $new->user_id=Auth::user()->id;
            $new->status=1;
            if($new->save())
            {
                StockCompletedProductsExportJob::dispatch($name,$data,Auth::user()->id);
                return response()->json(['status'=>1]);
            }

        }
        else if($statusCheck->status==0 || $statusCheck->status==2 || $statusCheck->status==3)
        {

            ExportStatus::where('type','stock_completed_products_download')->where('user_id',Auth::user()->id)->update(['status'=>1,'exception'=>null]);
            StockCompletedProductsExportJob::dispatch($name,$data,Auth::user()->id);
            return response()->json(['status'=>1]);

        }
        {
            return response()->json(['msg'=>'Export already being prepared','status'=>2]);
        }
      
    }

    public function recursiveStatusCheck()
    {
        $status=ExportStatus::where('user_id',Auth::user()->id)->where('type','stock_bulk_upload')->first();
        // $last_downloaded=$status->file_name;
        // $last_downloaded=Carbon::parse($last_downloaded)->format('Y-m-d-H-i-s');
        return response()->json(['msg'=>"File Created!",'status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name]);
    }
    public function completedProdRecursiveStatusCheck()
    {
        $status=ExportStatus::where('user_id',Auth::user()->id)->where('type','stock_completed_products_download')->first();
        // $last_downloaded=$status->file_name;
        // $last_downloaded=Carbon::parse($last_downloaded)->format('Y-m-d-H-i-s');
        return response()->json(['msg'=>"File Created!",'status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name]);
    }
    public function checkStatusFirstTimeForStockAdjustment()
    {
        $status=ExportStatus::where('type','stock_bulk_upload')->where('user_id',Auth::user()->id)->first();
        if($status!=null)
        {
          return response()->json(['status'=>$status->status]);
        }
        else
        {
          return response()->json(['status'=>0]);
        }

    }
    // public function checkStatusFirstTimeForSoldProducts()
    // {
    //     $status=ExportStatus::where('type','stock_bulk_upload')->where('user_id',Auth::user()->id)->first();
    //     if($status!=null)
    //     {
    //       return response()->json(['status'=>$status->status]);
    //     }
    //     else
    //     {
    //       return response()->json(['status'=>0]);
    //     }

    // }
    public function bulkCompletedProdMoveToinventory()
    {
        //remove auth user temp stock record first
        $tempRow = TempStockAdjustment::where('user_id',Auth::user()->id)->pluck('incomplete_rows')->toArray();
        $rows = $tempRow;
        $removeTempStock = TempStockAdjustment::where('user_id', Auth::user()->id)->get();
         foreach ($removeTempStock as $tempStock) {
            $tempStock->delete();
        }
        BulkStockAdjustmentJob::dispatch($rows, Auth::user()->id, true);
        // ImportFileHistory::insertRecordIntoDb(Auth::user()->id,'Stock Adjustments',$request->file('excel'));
        return redirect()->back()->with('successmsg','Stock Adjusted Successfully!');
    }

    public function bulkUploadProdQty(Request $request)
    {
        //remove auth user temp stock record first
        $tempStockAdjustment = TempStockAdjustment::where('user_id',Auth::user()->id)->get();
        foreach ($tempStockAdjustment as $tempStockRecord) {
            $tempStockRecord->delete();
        }
        $validator = $request->validate([
            'excel' => 'required|mimes:xlsx'
        ]);
        Excel::import(new ProductQtyInBulkImport(),$request->file('excel'));
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id,'Stock Adjustments',$request->file('excel'));

        return redirect()->back()->with('successmsg','Stock Adjusted Successfully!');
    }

    public function purchasingReport(Request $request, $redirection = null, $type = null)
    {
        if($redirection != null)
        {
            $redirect_request = $redirection;
        }
        else
        {
            $redirect_request = "NULL";
        }

        if($type != null)
        {
            $type = $type;
        }
        else
        {
            $type = "NULL";
        }

        $parentCat = ProductCategory::where('parent_id',0)->orderBy('title')->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name')->get();
        $bonded_warehouses = Warehouse::where('status',1)->where('is_bonded',1)->get();
        $products = Product::where('status',1)->get();
        $product_types = ProductType::all();
        $product_types_2 = ProductSecondaryType::orderBy('title','asc')->get();
        $product_types_3 = ProductTypeTertiary::orderBy('title','asc')->get();
        $units_total = Unit::all();
        return $this->render('users.purchasing.purchasing-report',compact('parentCat','suppliers','bonded_warehouses','products','units_total','redirect_request','type','product_types','product_types_2', 'product_types_3'));
    }

    public function purchasingReportGrouped(Request $request)
    {
        $parentCat = ProductCategory::with('get_Child')->where('parent_id',0)->orderBy('title')->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name')->get();
        $bonded_warehouses = Warehouse::where('status',1)->where('is_bonded',1)->get();
        $products = Product::where('status',1)->get();
        $units_total = Unit::all();
        return $this->render('users.purchasing.purchasing-report-grouped',compact('parentCat','suppliers','bonded_warehouses','products','units_total'));
    }

    public function getPoDataForReportFooter(Request $request)
    {
        $query = PurchaseOrderDetail::select('purchase_order_details.*','products.id','products.total_buy_unit_cost_price','supplier_products.freight','supplier_products.landing','supplier_products.import_tax_actual')->join('products','products.id','=','purchase_order_details.product_id')->join('supplier_products','supplier_products.product_id','=','products.id');
        $query->with('PurchaseOrder')->whereHas('PurchaseOrder', function($q){
            $q->whereIn('purchase_orders.status', [13,14,15]);
            $q->whereNotNull('purchase_orders.supplier_id');
        })->where('purchase_order_details.is_billed','=', 'Product');

        if($request->prod_category != null)
        {
            $product_ids = Product::where('category_id', $request->prod_category)->where('status',1)->pluck('id');
            $query->whereIn('purchase_order_details.product_id',$product_ids);
        }

        if($request->product_id != null)
        {
            $query->where('purchase_order_details.product_id', $request->product_id);
        }
        if($request->product_type != null)
        {
            $query->whereHas('product',function($p) use ($request){
                $p->where('type_id',$request->product_type);
            });
        }
        if($request->product_type_2 != null)
        {
            $query->whereHas('product',function($p) use ($request){
                $p->where('type_id_2',$request->product_type_2);
            });
        }

        if($request->filter != null)
        {
            if($request->filter == 'stock')
            {
                $query = $query->whereIn('purchase_order_details.product_id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
            }
            elseif($request->filter == 'reorder')
            {
                $product_ids = Product::where('min_stock','>',0)->where('status',1)->pluck('id');
                $query->whereIn('purchase_order_details.product_id',$product_ids);
            }
        }

        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-m-d',strtotime($date));
            $query->whereHas('PurchaseOrder', function($q) use($date){
                $q->where('purchase_orders.confirm_date', '>=', $date);
            });
        }
        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            $query->whereHas('PurchaseOrder', function($q) use($date){
                $q->where('purchase_orders.confirm_date', '<=', $date);
            });
        }
        if($request->supplier != null)
        {
            $id = $request->supplier;
            $query->whereHas('PurchaseOrder', function($q) use($id){
                $q->where('purchase_orders.supplier_id',$id);
            });
        }

        $qty_sum              = (clone $query)->sum('purchase_order_details.quantity');
        $pod_unit_price       = (clone $query)->sum('purchase_order_details.pod_unit_price');
        $pod_total_unit_price = (clone $query)->sum('purchase_order_details.pod_total_unit_price');

        $unit_price_in_thb    = (clone $query)->sum('purchase_order_details.unit_price_in_thb');
        $pod_freight          = (clone $query)->sum('purchase_order_details.pod_freight');
        $pod_landing          = (clone $query)->sum('purchase_order_details.pod_landing');
        $pod_total_extra_cost = (clone $query)->sum('purchase_order_details.pod_total_extra_cost');

        $total_unit_cost      = (clone $query)->sum('products.total_buy_unit_cost_price');
        $total_allocation     = (clone $query)->sum('supplier_products.import_tax_actual');

        $freight_p_b_unit     = (clone $query)->sum('supplier_products.freight');
        $landing_p_b_unit     = (clone $query)->sum('supplier_products.landing');

        $unit_price_in_thb_t  = $unit_price_in_thb + $pod_freight + $pod_landing + $pod_total_extra_cost;

        $total_amount_thb     = 0;
        if($query->count() > 0)
        {
            foreach ($query->get() as $value)
            {
                $total_amount_thb += ($value->unit_price_in_thb * $value->quantity);
            }
        }


        return response()->json([
            "qty_sum"           => $qty_sum,
            'freight_p_b_unit'  => floatval($freight_p_b_unit),
            'landing_p_b_unit'  => floatval($landing_p_b_unit),
            'total_allocation'  => floatval($total_allocation),
            'total_unit_cost'   => floatval($total_unit_cost),
            'unit_euro'         => $pod_unit_price,
            'total_amount_euro' => $pod_total_unit_price,
            'unit_cost_thb'     => $unit_price_in_thb_t,
            'total_amount_thb'  => $total_amount_thb
        ]);
    }

    public function getPoDataForReport(Request $request)
    {
        $query = PurchaseOrderDetail::select('purchase_order_details.*');

        if($request->hit_check != null && $request->hit_check != '' && $request->hit_check != 'on-supplier')
        {
            $statuses = [14];
        }
        else
        {
            $statuses = [13,14,15];
        }
        if($request->status == 'all')
        {
            array_push($statuses, 40);
        }

        $query->with('PurchaseOrder:id,ref_id,confirm_date,supplier_id,invoice_number,invoice_date,po_group_id','PurchaseOrder.PoSupplier:id,reference_name,country',
        'product:id,refrence_code,short_desc,selling_unit,buying_unit,total_buy_unit_cost_price,vat,type_id,type_id_2,type_id_3,min_stock,unit_conversion_rate,primary_category,weight',
        'product.sellingUnits:id,title',
        'product.supplier_products:id,landing,freight,product_id,import_tax_actual',
        'product.units:id,title',
        'PurchaseOrder.po_group.po_group_product_details',
        'product.productType',
        'product.productType2',
        'product.productType3',
        'PurchaseOrder.PoSupplier.getcountry:id,name',
        'product.productCategory:id,title')
        ->where('purchase_order_details.is_billed','=', 'Product');

        if($request->status == 40)
        {
            $query->whereHas('PurchaseOrder',function($q) {
                $q->where('purchase_orders.status', 40);
            });
        }
        else
        {
            $query->whereHas('PurchaseOrder', function($q) use($statuses) {
                $q->whereIn('purchase_orders.status', $statuses);
                $q->whereNotNull('purchase_orders.supplier_id');
            });
        }
        if($request->prod_category != null)
        {
            $product_ids = Product::where('category_id', $request->prod_category)->where('status',1)->pluck('id');
            $query->whereIn('product_id',$product_ids);
        }

        if($request->product_id != null)
        {
            $query->where('purchase_order_details.product_id', $request->product_id);
        }
        if($request->product_type != null)
        {
            $query->whereHas('product',function($p) use ($request){
                $p->where('type_id',$request->product_type);
            });
        }
        if($request->product_type_2 != null)
        {
            $query->whereHas('product',function($p) use ($request){
                $p->where('type_id_2',$request->product_type_2);
            });
        }

        if($request->product_type_3 != null)
        {
            $query->whereHas('product',function($p) use ($request){
                $p->where('type_id_3',$request->product_type_3);
            });
        }

        if($request->filter != null)
        {
            if($request->filter == 'stock')
            {
                $query = $query->whereIn('product_id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
            }
            elseif($request->filter == 'reorder')
            {
                $product_ids = Product::where('min_stock','>',0)->where('status',1)->pluck('id');
                $query->whereIn('product_id',$product_ids);
            }
        }

        if($request->from_date != null)
        {
            $date = str_replace("/","-",$request->from_date);
            $date =  date('Y-m-d',strtotime($date));
            $query->whereHas('PurchaseOrder', function($q) use($date){
                $q->where('purchase_orders.confirm_date', '>=', $date);
            });
        }

        if($request->to_date != null)
        {
            $date = str_replace("/","-",$request->to_date);
            $date =  date('Y-m-d',strtotime($date));
            $query->whereHas('PurchaseOrder', function($q) use($date){
                $q->where('purchase_orders.confirm_date', '<=', $date);
            });
        }

        if($request->supplier != null)
        {
            $id = $request->supplier;
            $query->whereHas('PurchaseOrder', function($q) use($id){
                $q->where('purchase_orders.supplier_id',$id);
            });
        }

        $query = PurchaseOrderDetail::doSortby($request, $query);

        $dt = Datatables::of($query);
        $add_columns = ['vat', 'sum_cost_amount', 'cost_unit_thb', 'total_cost', 'cost_unit', 'sum_qty', 'unit', 'buying_unit', 'short_desc', 'product_type_2', 'product_type_3', 'product_type', 'refrence_code', 'confirm_date', 'supplier', 'ref_id', 'custom_line_number', 'custom_invoice_number', 'seller_price', 'import_tax_actual', 'landing', 'freight', 'minimum_stock', 'supplier_invoice', 'supplier_invoice_date', 'vat_amount_euro', 'vat_amount_thb', 'unit_price_before_vat_euro', 'unit_price_before_vat_thb', 'unit_price_after_vat_euro', 'unit_price_after_vat_thb', 'discount_percent', 'sub_total_euro', 'sub_total_thb', 'total_amount_sfter_vat_euro', 'total_amount_sfter_vat_thb', 'conversion_rate', 'qty_into_stock', 'country', 'category', 'avg_weight'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return PurchaseOrderDetail::returnAddColumnPurchasingReportDetail($column, $item);
            });
        }

        $filter_columns = ['short_desc', 'ref_id', 'refrence_code', 'minimum_stock'];
        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrderDetail::returnFilterColumnPurchasingReportDetail($column, $item, $keyword);
            });
        }

        $dt->rawColumns(['ref_id','supplier', 'confirm_date','refrence_code','short_desc','unit','sum_qty','cost_unit','total_cost','vat','freight','landing','import_tax_actual','seller_price','custom_invoice_number','custom_invoice_number','product_type','product_type_2','product_type_3', 'country', 'category', 'avg_weight']);
        return $dt->make(true);
    }

    public function getPoDataForGroupedReport(Request $request)
    {

        $query = PurchaseOrderDetail::select(DB::raw('SUM(purchase_order_details.quantity) AS TotalQuantity,
          SUM(purchase_order_details.pod_total_unit_price) AS GrandTotalUnitPrice'),'purchase_order_details.*')->whereIn('po.status', [13,14,15])->whereNotNull('po.supplier_id')->where('purchase_order_details.is_billed','=', 'Product')->groupBy('purchase_order_details.product_id');
        $query->join('purchase_orders AS po','po.id','=','purchase_order_details.po_id');

        if ($request->prod_category != null) {
            $id_split = explode('-', $request->prod_category);
            // $id_split = $id_split[1];
            if ($id_split[0] == "pri") {
                $product_ids = Product::where('primary_category', $id_split[1])->where('status',1)->pluck('id');
                $query->whereIn('product_id',$product_ids);
            }
            else
            {
                $product_ids = Product::where('category_id', $id_split[1])->where('status',1)->pluck('id');
                $query->whereIn('product_id',$product_ids);
            }
        }

        if($request->product_id != null)
        {
            $query->where('purchase_order_details.product_id', $request->product_id);
        }

        if($request->filter != null)
        {
            if($request->filter == 'stock')
            {
                $query = $query->whereIn('product_id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
            }
            elseif($request->filter == 'reorder')
            {
                $product_ids = Product::where('min_stock','>',0)->where('status',1)->pluck('id');
                $query->whereIn('product_id',$product_ids);
            }
        }

        $from_date = NULL;
        $to_date   = NULL;
        if($request->from_date != null)
        {
            $from_date = str_replace("/","-",$request->from_date);
            $from_date =  date('Y-m-d',strtotime($from_date));
            $query->where('po.confirm_date', '>=', $from_date);
        }

        if($request->to_date != null)
        {
            $to_date = str_replace("/","-",$request->to_date);
            $to_date =  date('Y-m-d',strtotime($to_date));
            $query->where('po.confirm_date', '<=', $to_date);
        }

        if($request->supplier != null)
        {
            $id = $request->supplier;
            $query->whereHas('PurchaseOrder', function($q) use($id){
                $q->where('purchase_orders.supplier_id',$id);
            });
        }

        $query = PurchaseOrderDetail::PurchasingReportGroupedSorting($request, $query);

        $category_id  = $request->prod_category;
        $supplier_id  = $request->supplier;
        $filter_value = $request->filter;
        $from_date    = $from_date;
        $to_date      = $to_date;

        $dt =  Datatables::of($query);
        $add_columns = ['total_cost', 'cost_unit', 'sum_qty', 'unit', 'buying_unit', 'short_desc', 'refrence_code', 'action'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $category_id, $supplier_id, $filter_value, $from_date, $to_date) {
                return PurchaseOrderDetail::returnAddColumnPurchasingReportGrouped($column, $item, $category_id, $supplier_id, $filter_value, $from_date, $to_date);
            });
        }

        $filter_columns = ['short_desc', 'refrence_code'];
        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PurchaseOrderDetail::returnFilterColumnPurchasingReportGrouped($column, $item, $keyword);
            });
        }

        $dt->rawColumns(['action','refrence_code','short_desc','unit','sum_qty','cost_unit','total_cost']);
        return $dt->make(true);
    }

    public function purchasingReportMain()
    {
        $parentCat = ProductCategory::where('parent_id',0)->orderBy('title')->get();
        $suppliers = Supplier::where('status',1)->orderBy('reference_name')->get();
        $bonded_warehouses = Warehouse::where('status',1)->where('is_bonded',1)->get();
        $products = Product::where('status',1)->get();
        return $this->render('users.purchasing.purchasing-report-main',compact('parentCat','suppliers','bonded_warehouses','products'));
    }

    public function getPoDataMainForReport(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $supplier_id = $request->supplier;


          $products = Product::select(DB::raw(
     'SUM(CASE WHEN po.status="13" OR po.status="14" OR po.status="15" THEN pod.quantity END) AS QuantityText,
      SUM(CASE WHEN (po.status="13" OR po.status="14" OR po.status="15") THEN pod.pod_total_unit_price END) AS TotalAmount,
      SUM(CASE WHEN (po.status="13" OR po.status="14" OR po.status="15") THEN pod.pod_total_unit_price END)/SUM(CASE WHEN po.status="13" OR po.status="14" OR po.status="15" THEN pod.quantity END) AS avg_unit_price'),
        'products.refrence_code',
        'products.buying_unit',
        'products.short_desc',
        'pod.product_id',
        'products.id',
        'products.category_id',
        'products.brand',
        'products.total_buy_unit_cost_price',
        'products.primary_category')->groupBy('pod.product_id');
        $products->join('purchase_order_details AS pod','pod.product_id','=','products.id');
        $products->join('purchase_orders AS po','po.id','=','pod.po_id');
        $products->where('pod.is_billed','=', 'Product');

        if($request->prod_category != null){
        $products = $products->where('products.category_id',$request->prod_category);
        }

        if($request->product_id != null)
        {
        $products = $products->where('products.id',$request->product_id);
        }

        if($request->filter != null)
        {
            if($request->filter == 'stock')
            {
                $products = $products->whereIn('products.id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
            }
            elseif($request->filter == 'reorder')
            {
                $product_ids = Product::where('min_stock','>',0)->where('status',1)->pluck('id');
                $products->whereIn('products.id',$product_ids);
            }
        }

        if($request->from_date != null)
        {
            $from_date = str_replace("/","-",$request->from_date);
            $from_date =  date('Y-m-d',strtotime($from_date));
            $products = $products->where('po.confirm_date', '>=', $from_date);
        }
        if($request->to_date != null)
        {
            $to_date = str_replace("/","-",$request->to_date);
            $to_date =  date('Y-m-d',strtotime($to_date));
             $products = $products->where('po.confirm_date', '<=', $to_date);
        }
        if($supplier_id != null)
        {
        $products = $products->where('products.supplier_id',$supplier_id);
        }

        $to_get_totals = (clone $products)->get();
        $products = $products->with('units');


        return Datatables::of($products)

        ->addColumn('view', function ($item) use ($from_date,$to_date,$supplier_id){
             $supplier_id == '' ? $supplier_id = 'NA' : '';
             $from_date == '' ? $from_date = 'NoDate' : '';
             $to_date == '' ? $to_date = 'NoDate' : '';
            $html_string = '<a target="_blank" href="'.url('get-purchasing-report-detail/'.$supplier_id.'/'.$item->id.'/'.$from_date.'/'.$to_date).'" class="actionicon" style="cursor:pointer" title="View history" data-id='.$item->product_id.'><i class="fa fa-history"></i></a>';
            return $html_string;
        })
        ->editColumn('refrence_code', function ($item){
          $html_string = '<a href="'.url('get-product-detail/'.$item->product_id).'" target="_blank" title="View Detail"><b>'.$item->refrence_code.'</b></a>';
          return $html_string;
        })
        ->editColumn('short_desc', function ($item) {
            return $item->product_id !== null ? $item->short_desc : 'N.A';
        })
        ->addColumn('buying_unit', function ($item) {
            return $item->product_id !== null ? $item->units->title : 'N.A';
        })
        ->addColumn('brand', function ($item){
          return @$item->brand != null ? @$item->brand : '--';
        })
        ->addColumn('total_quantity', function ($item) {
          return number_format($item->QuantityText,2);
        })
        ->addColumn('total_buy_unit_cost_price', function ($item) {
            return $item->total_buy_unit_cost_price !== null ? ($item->total_buy_unit_cost_price !== null ? number_format((float) $item->total_buy_unit_cost_price, 3, '.', ',') : '--') : 'N.A';
        })
        ->addColumn('avg_unit_price', function ($item) {
            return number_format($item->avg_unit_price,2);
        })
        ->addColumn('total_amount', function ($item) {
          return number_format($item->TotalAmount,2);
          })
        ->setRowId(function ($item) {
            return $item->id;
        })
        ->rawColumns(['view','refrence_code','short_desc','buying_unit','brand','total_quantity','total_buy_unit_cost_price','avg_unit_price','total_amount'])
        ->with([
          'total_quantity'=>$to_get_totals->sum('QuantityText'),
          'avg_unit_price'=>$to_get_totals->sum('avg_unit_price'),
          'total_amount'=>$to_get_totals->sum('TotalAmount'),
        ])
        ->make(true);
    }

    public function exportPurchasingReportMain(Request $request){
        $from_date_exp = $request->from_date_exp;
        $to_date_exp = $request->to_date_exp;
        $supplier_id = $request->supplier_filter_exp;


          $products = Product::select(DB::raw(
     'SUM(CASE WHEN po.status="13" OR po.status="14" OR po.status="15" THEN pod.quantity END) AS QuantityText,
      SUM(CASE WHEN (po.status="13" OR po.status="14" OR po.status="15") THEN pod.pod_total_unit_price END) AS TotalAmount,
      SUM(CASE WHEN (po.status="13" OR po.status="14" OR po.status="15") THEN pod.pod_total_unit_price END)/SUM(CASE WHEN po.status="13" OR po.status="14" OR po.status="15" THEN pod.quantity END) AS avg_unit_price'),
        'products.refrence_code',
        'products.buying_unit',
        'products.short_desc',
        'pod.product_id',
        'products.id',
        'products.category_id',
        'products.brand',
        'products.total_buy_unit_cost_price',
        'products.primary_category')->groupBy('pod.product_id');
        $products->join('purchase_order_details AS pod','pod.product_id','=','products.id');
        $products->join('purchase_orders AS po','po.id','=','pod.po_id');
        $products->where('pod.is_billed','=', 'Product');

        if($request->product_category_exp != null){
        $products = $products->where('products.category_id',$request->product_category_exp);
        }

        if($request->product_id_filter_exp != null)
        {
        $products = $products->where('products.id',$request->product_id_filter_exp);
        }

        if($request->filter_dropdown_exp != null)
        {
            if($request->filter_dropdown_exp == 'stock')
            {
                $products = $products->whereIn('products.id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
            }
            elseif($request->filter_dropdown_exp == 'reorder')
            {
                $product_ids = Product::where('min_stock','>',0)->where('status',1)->pluck('id');
                $products->whereIn('products.id',$product_ids);
            }
        }

        if($request->from_date_exp != null)
        {
            $from_date_exp = str_replace("/","-",$request->from_date_exp);
            $from_date_exp =  date('Y-m-d',strtotime($from_date_exp));
            $products = $products->where('po.confirm_date', '>=', $from_date_exp);
        }
        if($request->to_date_exp != null)
        {
            $to_date_exp = str_replace("/","-",$request->to_date_exp);
            $to_date_exp =  date('Y-m-d',strtotime($to_date_exp));
             $products = $products->where('po.confirm_date', '<=', $to_date_exp);
        }
        if($supplier_id != null)
        {
        $products = $products->where('products.supplier_id',$supplier_id);
        }

        $to_get_totals = (clone $products)->get();
        $products = $products->with('units');

        $query = $products->get();
        /***********/
        $current_date = date("Y-m-d");
        return \Excel::download(new purchasingReportMainExport($query), 'Purchasing Report'.$current_date.'.xlsx');
    }


    public function exportPurchasingReport(Request $request)
    {
        $data=$request->all();
        $status=ExportStatus::where('type','purchasing_report_detail')->first();
        if($status==null)
        {
            $new=new ExportStatus();
            $new->user_id=Auth::user()->id;
            $new->type='purchasing_report_detail';
            $new->status=1;
            $new->save();
            PurchasingReportJob::dispatch($data,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'recursive'=>true]);
        }
        elseif($status->status==1)
        {
            return response()->json(['msg'=>"File is already being prepared",'status'=>2]);
        }
        elseif($status->status==0 || $status->status==2)
        {
            ExportStatus::where('type','purchasing_report_detail')->update(['status'=>1,'exception'=>null,'user_id'=>Auth::user()->id]);
            PurchasingReportJob::dispatch($data,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'exception'=>null]);
        }
    }

    public function recursiveExportStatusPurchasingReport()
    {
        $status=ExportStatus::where('type','purchasing_report_detail')->first();
        return response()->json(['msg'=>"File Created!",'status'=>$status->status,'exception'=>$status->exception]);
    }

    public function checkStatusForFirstTimePurchasingReport()
    {
        $status=ExportStatus::where('type','purchasing_report_detail')->where('user_id',Auth::user()->id)->first();
        if($status!=null)
        {
          return response()->json(['status'=>$status->status]);
        }
        else
        {
          return response()->json(['status'=>0]);
        }
    }

    public function exportPurchasingReportGrouped(Request $request)
    {
        $data = $request->all();
        $status = ExportStatus::where('type','purchasing_report_detail_grouped')->first();
        if($status == null)
        {
            $new          = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'purchasing_report_detail_grouped';
            $new->status  = 1;
            $new->save();
            PurchasingReportGroupedJob::dispatch($data,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'recursive'=>true]);
        }
        elseif($status->status==1)
        {
            return response()->json(['msg'=>"File is already being prepared",'status'=>2]);
        }
        elseif($status->status==0 || $status->status==2)
        {
            ExportStatus::where('type','purchasing_report_detail_grouped')->update(['status'=>1,'exception'=>null,'user_id'=>Auth::user()->id]);
            PurchasingReportGroupedJob::dispatch($data,Auth::user()->id);
            return response()->json(['msg'=>"File is now getting prepared",'status'=>1,'exception'=>null]);
        }
    }

    public function recursiveExportStatusPurchasingReportGrouped()
    {
        $status=ExportStatus::where('type','purchasing_report_detail_grouped')->first();
        return response()->json(['msg'=>"File Created!",'status'=>$status->status,'exception'=>$status->exception]);
    }

    public function checkStatusForFirstTimePurchasingReportGrouped()
    {
        $status=ExportStatus::where('type','purchasing_report_detail_grouped')->where('user_id',Auth::user()->id)->first();
        if($status!=null)
        {
          return response()->json(['status'=>$status->status]);
        }
        else
        {
          return response()->json(['status'=>0]);
        }
    }

    public function recursiveImportStatusCheck()
    {
        $status=ExportStatus::where('user_id',Auth::user()->id)->where('type','stock_bulk_upload')->first();
        // $last_downloaded=$status->file_name;
        // $last_downloaded=Carbon::parse($last_downloaded)->format('Y-m-d-H-i-s');
        return response()->json(['msg'=>"File Created!",'status'=>$status->status,'exception'=>$status->exception,'file_name'=>$status->file_name]);
    }

}
