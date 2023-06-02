<?php

namespace App\Models\Common\PurchaseOrders;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Supplier;
use Carbon\Carbon;
use App\Models\Common\Warehouse;


class PurchaseOrder extends Model
{
    protected $fillable = ['ref_id','status','total','total_in_thb','total_with_vat','vat_amount_total','total_quantity','total_gross_weight','total_import_tax_book','total_import_tax_book_price','','created_by','supplier_id','payment_due_date','target_receive_date','confirm_date','to_warehouse_id','from_warehouse_id','memo','payment_terms_id','transfer_date','invoice_date','exchange_rate','invoice_number','total_vat_actual','total_vat_actual_price','total_with_vat_in_thb','total_vat_actual_price_in_thb'];
    // protected $with = ["PurchaseOrderDetail"];

    public function PurchaseOrderDetail()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'po_id', 'id');
    }

    public function poStatusHistory()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\PurchaseOrderStatusHistory', 'po_id', 'id');
    }

    public function createdBy()
    {
    	return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function pOpaymentTerm()
    {
        return $this->belongsTo('App\Models\Common\PaymentTerm','payment_terms_id','id');
    }

    public function po_notes(){
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrderNote', 'po_id', 'id');
    }

    public function po_documents(){
        return $this->hasMany('App\Models\Common\PurchaseOrderDocument', 'po_id', 'id');
    }

    public function PoSupplier()
    {
    	return $this->belongsTo('App\Models\Common\Supplier', 'supplier_id', 'id');
    }

    public function PoWarehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'from_warehouse_id', 'id');
    }

    public function ToWarehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'to_warehouse_id', 'id');
    }

    public function p_o_group(){
        return $this->belongsTo('App\Models\Common\PoGroupDetail', 'id', 'purchase_order_id');
    }

    public function p_o_statuses(){
        return $this->belongsTo('App\Models\Common\Status', 'status', 'id');
    }

    public function po_group(){
        return $this->belongsTo('App\Models\Common\PoGroup', 'po_group_id', 'id');
    }
    public function po_detail()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'po_id', 'id')->where('order_id', '!=', NULL);
    }
    public function purchaseOrderTransaction()
    {
        return $this->hasOne('App\PurchaseOrderTransaction', 'po_id', 'id');
    }
    public static function createManualPo($stock_out, $user_id = null)
    {
        if($stock_out->supplier_id != null){
            $supplier = Supplier::find($stock_out->supplier_id);
        }else{
            $supplier = Supplier::where('manual_supplier',1)->first();
        }
        if($supplier != null)
        {
            $purchaseOrder = PurchaseOrder::create([
                    'ref_id'                        => 'Manual-PO',
                    'status'                        => 40,
                    'total'                         => 0,
                    'total_with_vat'                => 0,
                    'vat_amount_total'              => 0,
                    'total_quantity'                => $stock_out->quantity_in,
                    'total_gross_weight'            => 0,
                    'total_import_tax_book'         => 0,
                    'total_import_tax_book_price'   => 0,
                    'total_vat_actual'              => 0,
                    'total_vat_actual_price'        => 0,
                    'total_vat_actual_price_in_thb' => 0,
                    'supplier_id'                   => $supplier->id,
                    'from_warehouse_id'             => $stock_out->warehouse_id,
                    'created_by'                    => $user_id ?? @auth()->user()->id,
                    'memo'                          => null,
                    'payment_terms_id'              => null,
                    'payment_due_date'              => $stock_out->created_at,
                    'target_receive_date'           => $stock_out->created_at,
                    'confirm_date'                  => $stock_out->created_at,
                    'to_warehouse_id'               => $stock_out->warehouse_id,
                    'invoice_date'                  => $stock_out->created_at,
                    'exchange_rate'                 => 1,
            ]);
            $purchaseOrder->ref_id = $purchaseOrder->ref_id.''.$purchaseOrder->id;
            $purchaseOrder->save();
            $purchaseOrderDetail = PurchaseOrderDetail::create([
                'po_id'                             => $purchaseOrder->id,
                'order_id'                          => NULL,
                'customer_id'                       => NULL,
                'order_product_id'                  => NULL,
                'product_id'                        => $stock_out->product_id,
                'billed_desc'                       => null,
                'is_billed'                         => 'Product',
                'created_by'                        => $user_id ?? @auth()->user()->id,
                'pod_import_tax_book'               => 0,
                'pod_vat_actual'                    => 0,
                'pod_unit_price'                    => 0,
                'pod_unit_price_with_vat'           => 0,
                'last_updated_price_on'             => null,
                'pod_gross_weight'                  => 0,
                'quantity'                          => $stock_out->quantity_in,
                'quantity_received'                          => $stock_out->quantity_in,
                'pod_total_gross_weight'            => 0,
                'pod_total_unit_price'              => 0,
                'pod_total_unit_price_with_vat'     => 0,
                'discount'                          => 0,
                'pod_import_tax_book_price'         => 0,
                'pod_vat_actual_price'              => 0,
                'pod_vat_actual_price_in_thb'       => 0,
                'pod_vat_actual_total_price'        => 0,
                'pod_vat_actual_total_price_in_thb' => 0,
                'warehouse_id'                      => $stock_out->warehouse_id,
                'supplier_packaging'                => null,
                'billed_unit_per_package'           => null,
                'desired_qty'                       => null,
                'currency_conversion_rate'          => 1,
            ]);
            $stock_out->po_id = $purchaseOrder->id;
            $stock_out->p_o_d_id = $purchaseOrderDetail->id;
            $stock_out->save();
        }
        return true;
    }

    public static function doSortPickInstruction($request, $query) {
        if($request['sortbyvalue'] == 1)
        {
          $sort_order     = 'DESC';
        } else
        {
          $sort_order     = 'ASC';
        }

        if($request['sortbyparam'] == 'td')
        {
          $query->orderBy('ref_id', $sort_order);
        }
        elseif($request['sortbyparam'] == 'supply_to') {
            $query->select('purchase_orders.*')->leftJoin('warehouses', 'warehouses.id', '=', 'purchase_orders.to_warehouse_id')->orderBy('warehouses.warehouse_title', $sort_order);
        }
        elseif($request['sortbyparam'] == 'confirm_date') {
            $query->orderBy($request['sortbyparam'], $sort_order);
        }
        elseif($request['sortbyparam'] == 'transfer_date') {
            $query->orderBy($request['sortbyparam'], $sort_order);
        }
        else {
            $query = $query->orderBy('transfer_date','DESC');
        }

        return $query;

    }

    public static function doSort($request,$query) {
        if($request['sortbyvalue'] == 1)
        {
          $sort_order     = 'DESC';
        } else
        {
          $sort_order     = 'ASC';
        }

        if($request['sortbyparam'] == 'target_receive_date')
        {
          $query->orderBy($request['sortbyparam'], $sort_order);
        }
        elseif($request['sortbyparam'] == 'po_id')
        {
          $query->orderBy('id', $sort_order);
        }
        elseif($request['sortbyparam'] == 'created_at')
        {
          $query->orderBy($request['sortbyparam'], $sort_order);
        }
        elseif($request['sortbyparam'] == 'invoice_date')
        {
          $query->orderBy($request['sortbyparam'], $sort_order);
        }
        elseif($request['sortbyparam'] == 'supplier')
        {
          $query->select('purchase_orders.*')->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')->orderBy('suppliers.reference_name', $sort_order);
        }
        elseif($request['sortbyparam'] == 'invoice_number')
        {
          $query->orderBy($request['sortbyparam'], $sort_order);
        }
        elseif($request['sortbyparam'] == 'memo')
        {
          $query->orderBy($request['sortbyparam'], $sort_order);
        }
        elseif($request['sortbyparam'] == 'supplier_currency')
        {
          $query->select('purchase_orders.*')->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')->leftJoin('currencies', 'currencies.id', '=', 'suppliers.currency_id')->orderBy('currencies.currency_code', $sort_order);
        }
        elseif($request['sortbyparam'] == 'payment_terms')
        {
          $query->select('purchase_orders.*')->leftJoin('payment_terms', 'payment_terms.id', '=', 'purchase_orders.payment_terms_id')->orderBy('payment_terms.title', $sort_order);
        }
        elseif($request['sortbyparam'] == 'payment_due_date')
        {
          $query->orderBy($request['sortbyparam'], $sort_order);
        }
        elseif($request['sortbyparam'] == 'po_total')
        {
          $query->orderBy(\DB::raw('total+0'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'po_total_with_vat')
        {
          $query->orderBy(\DB::raw('total_with_vat+0'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'po_exchange_rate')
        {
          $query->orderBy(\DB::raw('exchange_rate+0'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'po_total_in_thb')
        {
          $query->orderBy(\DB::raw('total_in_thb+0'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'amount_paid')
        {
          $query->select('purchase_orders.*')->leftJoin('purchase_order_transactions', 'purchase_order_transactions.po_id', '=', 'purchase_orders.id')->orderBy(\DB::raw('purchase_order_transactions.total_received+0'), $sort_order);

        }
        // elseif($request['sortbyparam'] == 'exchange_rate')
        // {
        //   $query->orderBy(\DB::raw('CASE WHEN payment_exchange_rate != null and payment_exchange_rate != 0 THEN (1/payment_exchange_rate)+0 END'), $sort_order);
        // }
        else if($request['sortbyparam'] == 'order_no')
        {
            $query->orderBy('ref_id', $sort_order);
        }
        else if($request['sortbyparam'] == 'supplier_reference_no')
        {
            $query->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.reference_no', $sort_order);
        }
        else if($request['sortbyparam'] == 'supplier_reference_name')
        {
            $query->leftJoin('suppliers', 'purchase_orders.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.reference_name', $sort_order);
        }
        else if($request['sortbyparam'] == 'order_total')
        {
            $query->orderBy(\DB::raw('total+0'), $sort_order);
        }
        else if($request['sortbyparam'] == 'memo')
        {
            $query->sorderBy('memo', $sort_order);
        }
        else {
            $query->orderBy('id','DESC');
        }
        return $query;
    }

    public static function doSortBy($request,$query)
    {
        if($request['sortbyparam'] == 'po_number')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->orderBy('ref_id', $sort);
        }
        else if($request['sortbyparam'] == 'supply_from')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->select('purchase_orders.*','suppliers.reference_name')->join('suppliers','suppliers.id','=','purchase_orders.supplier_id')->orderBy('suppliers.reference_name',$sort);
        }
        else if($request['sortbyparam'] == 'supplier_invoice_number')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->orderBy('invoice_number', $sort);
        }
        else if($request['sortbyparam'] == 'confirm_date')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->orderBy('confirm_date', $sort);
        }
        else if($request['sortbyparam'] == 'payment_due_date')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->orderBy('payment_due_date', $sort);
        }
        else if($request['sortbyparam'] == 'invoice_date')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->orderBy('invoice_date', $sort);
        }
        else if($request['sortbyparam'] == 'target_receiving_date')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->orderBy('target_receive_date', $sort);
        }
        else if($request['sortbyparam'] == 'po_total')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->orderBy(\DB::raw('total+0'), $sort);
        }
        else if($request['sortbyparam'] == 'warehouse')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->select('purchase_orders.*','warehouses.warehouse_title')->join('warehouses','warehouses.id','=','purchase_orders.to_warehouse_id')->orderBy('warehouses.warehouse_title',$sort);
        }
        else if($request['sortbyparam'] == 'group')
        {
            $sort = $request['sortbyvalue'] == '1' ? 'DESC' : 'ASC';
            $query->select('purchase_orders.*')->leftJoin('po_groups','po_groups.id','=','purchase_orders.po_group_id')->orderBy('po_groups.ref_id',$sort);
        } else if($request['sortbyparam'] == 'exchange_rate') {
            $sort = $request['sortbyvalue'] == '2' ? 'DESC' : 'ASC';
            $query->orderBy(\DB::raw('exchange_rate+0'), $sort);
        }
        else
        {
            $column_name = 'id';
            $sort = 'DESC';
            $query->orderBy($column_name,$sort);
        }
        return $query;
    }

    public static function TransferDashboardSorting($request, $query)
    {
        $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
        if ($request['column_name'] == 'td_no')
        {
            $query->orderBy('ref_id', $sort_order);
        }
        elseif ($request['column_name'] == 'transfer_date')
        {
            $query->orderBy('transfer_date', $sort_order);
        }
        elseif ($request['column_name'] == 'received_date')
        {
            $query->orderBy('target_receive_date', $sort_order);
        }
        elseif ($request['column_name'] == 'supply_from')
        {
            $query->join('warehouses as w', 'purchase_orders.from_warehouse_id', '=', 'w.id')->orderBy('w.warehouse_title', $sort_order);
        }
        elseif ($request['column_name'] == 'to_warehouse')
        {
            $query->join('warehouses as w', 'purchase_orders.to_warehouse_id', '=', 'w.id')->orderBy('w.warehouse_title', $sort_order);
        }
        elseif ($request['column_name'] == 'created_date')
        {
            $query->orderBy('confirm_date', $sort_order);
        }
        else
        {
            $query->orderBy('id','DESC');
        }
        return $query;
    }

    public static function returnAddColumnWaitingConfirm($column, $item) {
        switch ($column) {
            case 'to_warehouse':
                return $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : '--';
                break;

            case 'customer':
                $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', NULL)->where('po_id',$item->id)->get()->groupBy('customer_id');
                $html_string = '';
                if($getCust->count() > 1)
                {
                    $customers = '';
                    $i = 0;
                    foreach ($getCust as $cust) {
                        if ($i < 3) {
                            $customers .= $cust[0]->customer->reference_name . '<br>';
                        }
                        else{
                            break;
                        }
                        $i++;
                    }
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="'.$item->id.'" class=" d-block show-po-cust mr-2 font-weight-bold" title="View Customers">'.$customers.' ...</a> ';
                }
                elseif($getCust->count() == 1)
                {
                    foreach ($getCust as $value)
                    {
                        if($value != Null)
                        {
                            $html_string = @$value[0]->customer->reference_name;
                        }
                    }
                }
                elseif($getCust->count() == 0)
                {
                    $html_string = "---";
                }

                return $html_string;
                break;

            case 'note':
                if($item->po_notes->count() > 0)
                {
                    $note = $item->po_notes->first()->note;
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#note-modal" data-id="'.$item->id.'" class="d-block show-po-note mr-2 font-weight-bold" title="View Notes">'.mb_substr($note, 0, 30).' ...</a> ';
                }
                else
                {
                    $html_string = '---';
                }
                return $html_string;
                break;

            case 'payment_due_date':
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'confirm_date':
                return $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';
                break;

            case 'supplier_ref':
                return $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';
                break;

            case 'supplier':
                return $item->supplier_id !== null ? @$item->PoSupplier->reference_name : @$item->PoWarehouse->warehouse_title;
                break;

            case 'action':
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
                return $html_string;
                break;

            case 'checkbox':
                if($item->status == 13 || $item->status == 12)
                {
                    $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                        <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                        <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                    </div>';
                }
                else
                {
                    $html_string = 'N.A';
                }
                return $html_string;
                break;

            case 'exchange_rate':
                if($item->exchange_rate !== null && $item->exchange_rate != 0)
                {
                $exchange_rate = (1 / $item->exchange_rate);
                $exchange_rate = number_format($exchange_rate,4,'.',',');
                }
                else
                {
                $exchange_rate = '--';
                }
                return $exchange_rate;
                break;

            case 'po_total':
                return $item->total !== null ? number_format($item->total,3,'.',',') : '--';
                break;
        }
    }

    public static function returnEditColumnWaitingConfirm($column, $item) {
        switch ($column) {
            case 'ref_id':
                $item_id = '--';
                if($item->ref_id != null){
                    $item_id = $item->ref_id;
                }
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';
                return $html_string;
                break;

            case 'invoice_number':
                $html = $item->invoice_number !== null ? $item->invoice_number : '--';
                return $html;
                break;

            case 'invoice_date':
                $html = $item->invoice_date !== null ? Carbon::parse($item->invoice_date)->format('d/m/Y'): '--';
                return $html;
                break;


        }
    }

    public static function returnFilterColumnWaitingConfirm($column, $item, $keyword) {

        switch ($column) {
            case 'to_warehouse':
                $wID =  Warehouse::where('status',1)->where('warehouse_title','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('to_warehouse_id',$wID);
                break;

            case 'customer':
                $item->whereHas('PurchaseOrderDetail',function($q) use ($keyword) {
                    $q->whereHas('customer',function($qq) use ($keyword) {
                        $qq->where('reference_name','LIKE',"%$keyword%");
                    });
                });
                break;

            case 'note':
                $sID =  PurchaseOrderNote::where('note','LIKE', "%$keyword%")->pluck('po_id')->toArray();
                $item->whereIn('id',$sID);
                break;

            case 'supplier':
                $sID =  Supplier::where('reference_name','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('supplier_id',$sID);
                break;
        }
    }

    public static function returnAddColumnShipping($column, $item) {
        switch ($column) {
            case 'to_warehouse':
                return $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : '--';
                break;

            case 'customer':
                $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', NULL)->where('po_id',$item->id)->get()->groupBy('customer_id');

                $html_string = '';

                if($getCust->count() > 1)
                {
                    $customers = '';
                    $i = 0;
                    foreach ($getCust as $cust) {
                        if ($i < 3) {
                            $customers .= $cust[0]->customer->reference_name . '<br>';
                        }
                        else{
                            break;
                        }
                        $i++;
                    }
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="'.$item->id.'" class=" d-block show-po-cust mr-2 font-weight-bold" title="View Customers">'.$customers.' ...</a> ';
                }
                elseif($getCust->count() == 1)
                {
                    foreach ($getCust as $value)
                    {
                        if($value != Null)
                        {
                            $html_string = @$value[0]->customer->reference_name;
                        }
                    }
                }
                elseif($getCust->count() == 0)
                {
                    $html_string = "---";
                }

                return $html_string;
                break;

            case 'note':
                if($item->po_notes->count() > 0)
                {
                    $note = $item->po_notes->first()->note;
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#note-modal" data-id="'.$item->id.'" class="d-block show-po-note mr-2 font-weight-bold" title="View Notes">'.mb_substr($note, 0, 30).' ...</a> ';
                }
                else
                {
                    $html_string = '---';
                }
                return $html_string;
                break;

            case 'payment_due_date':
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'po_total':
                return $item->total !== null ? number_format($item->total,3,'.',',') : '--';
                break;

            case 'confirm_date':
                return $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';
                break;

            case 'supplier_ref':
                return $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';
                break;

            case 'supplier':
                return $item->supplier_id !== null ? @$item->PoSupplier->reference_name : @$item->PoWarehouse->warehouse_title;
                break;

            case 'action':
                $html_string = '
                <a href="'.url('get-purchase-order-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
               return $html_string;
                break;

            case 'checkbox':
                if($item->status == 13)
                {
                    $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                        <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                        <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                    </div>';
                }
                else
                {
                    $html_string = 'N.A';
                }
                    return $html_string;
                break;

            case 'exchange_rate':
                if($item->exchange_rate !== null && $item->exchange_rate != 0)
                {
                $exchange_rate = (1 / $item->exchange_rate);
                $exchange_rate = number_format($exchange_rate,4,'.',',');
                }
                else{
                $exchange_rate = '--';
                }
                return $exchange_rate;
                break;
        }
    }


    public static function returnEditColumnShipping($column, $item) {
        switch ($column) {
            case 'ref_id':
                $item_id = '--';
                if($item->ref_id != null){
                    $item_id = $item->ref_id;
                }
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';
                return $html_string;
                break;

            case 'invoice_number':
                $html = $item->invoice_number !== null ? $item->invoice_number : '---';
                return $html;
                break;
            case 'invoice_date':
                $html = $item->invoice_date !== null ? Carbon::parse($item->invoice_date)->format('d/m/Y'): '--';
                return $html;
                break;
        }
    }

    public static function returnFilterColumnShipping($column, $item, $keyword) {
        switch ($column) {
            case 'supplier':
                $sID =  Supplier::where('reference_name','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('supplier_id',$sID);
                break;

            case 'note':
                $sID =  PurchaseOrderNote::where('note','LIKE', "%$keyword%")->pluck('po_id')->toArray();
                $item->whereIn('id',$sID);
                break;

            case 'customer':
                $item->whereHas('PurchaseOrderDetail',function($q) use ($keyword) {
                    $q->whereHas('customer',function($qq) use ($keyword) {
                        $qq->where('reference_name','LIKE',"%$keyword%");
                    });
                });
                break;

            case 'to_warehouse':
                $wID =  Warehouse::where('status',1)->where('warehouse_title','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('to_warehouse_id',$wID);
                break;
        }
    }


    public static function returnAddColumnDispatchFromSupplier($column, $item) {
        switch ($column) {
            case 'to_warehouse':
                return $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : '--';
                break;

            case 'customer':
                $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', NULL)->where('po_id',$item->id)->get()->groupBy('customer_id');

                $html_string = '';

                if($getCust->count() > 1)
                {
                    $customers = '';
                    $i = 0;
                    foreach ($getCust as $cust) {
                        if ($i < 3) {
                            $customers .= $cust[0]->customer->reference_name . '<br>';
                        }
                        else{
                            break;
                        }
                        $i++;
                    }
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="'.$item->id.'" class="d-block show-po-cust mr-2 font-weight-bold" title="View Customers">'.$customers.' ...</a> ';
                }
                elseif($getCust->count() == 1)
                {
                    foreach ($getCust as $value)
                    {
                        if($value != Null)
                        {
                            $html_string = @$value[0]->customer->reference_name;
                        }
                    }
                }
                elseif($getCust->count() == 0)
                {
                    $html_string = "---";
                }

                return $html_string;
                break;

            case 'note':
                if($item->po_notes->count() > 0)
                {
                    $note = $item->po_notes->first()->note;
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#note-modal" data-id="'.$item->id.'" class="d-block show-po-note mr-2 font-weight-bold" title="View Notes">'.mb_substr($note, 0, 30).' ...</a> ';
                }
                else
                {
                    $html_string = '---';
                }
                return $html_string;
                break;

            case 'payment_due_date':
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'po_total':
                return $item->total !== null ? number_format($item->total,3,'.',',') : '--';
                break;

            case 'confirm_date':
                return $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';
                break;

            case 'supplier_ref':
                return $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';
                break;

            case 'supplier':
                return $item->supplier_id !== null ? @$item->PoSupplier->reference_name : @$item->PoWarehouse->warehouse_title;
                break;

            case 'group_number':
                return $item->po_group_id != null ? $item->po_group->ref_id  : "N.A";
                break;

            case 'action':
                $html_string = '
                <a href="'.url('get-purchase-order-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
               return $html_string;
                break;

            case 'checkbox':
                if($item->status == 13 || $item->status == 14)
                {
                    $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                              </div>';
                }
                else{
                    $html_string = 'N.A';
                }
                return $html_string;
                break;

            case 'exchange_rate':
                if($item->exchange_rate !== null && $item->exchange_rate != 0)
                {
                $exchange_rate = (1 / $item->exchange_rate);
                $exchange_rate = number_format($exchange_rate,4,'.',',');
                }
                else{
                $exchange_rate = '--';
                }
                return $exchange_rate;
                break;

        }
    }

    public static function returnEditColumnDispatchFromSupplier($column, $item) {
        switch ($column) {
            case 'ref_id':
                $item_id = '--';
                if($item->ref_id !== null){
                    $item_id = $item->ref_id;
                }
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';
                return $html_string;
                break;

            case 'invoice_number':
                $html = $item->invoice_number !== null ? $item->invoice_number : '---';
                return $html;
                break;
            case 'invoice_date':
                $html = $item->invoice_date !== null ? Carbon::parse($item->invoice_date)->format('d/m/Y'): '--';
                return $html;
                break;
        }
    }

    public static function returnFilterColumnDispatchFromSupplier($column, $item, $keyword) {
        switch ($column) {
            case 'supplier':
                $sID =  Supplier::where('reference_name','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('supplier_id',$sID);
                break;

            case 'customer':
                $item->whereHas('PurchaseOrderDetail',function($q) use ($keyword) {
                    $q->whereHas('customer',function($qq) use ($keyword) {
                        $qq->where('reference_name','LIKE',"%$keyword%");
                    });
                });
                break;

            case 'to_warehouse':
                $wID =  Warehouse::where('status',1)->where('warehouse_title','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('to_warehouse_id',$wID);
                break;

            case 'note':
                $sID =  PurchaseOrderNote::where('note','LIKE', "%$keyword%")->pluck('po_id')->toArray();
                $item->whereIn('id',$sID);
                break;
        }
     }

     public static function returnAddColumnReceivedIntoStock($column, $item) {
        switch ($column) {
            case 'to_warehouse':
                return $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : '--';
                break;

            case 'customer':
                $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', NULL)->where('po_id',$item->id)->get()->groupBy('customer_id');

                $html_string = '';

                if($getCust->count() > 1)
                {
                    $customers = '';
                    $i = 0;
                    foreach ($getCust as $cust) {
                        if ($i < 3) {
                            $customers .= $cust[0]->customer->reference_name . '<br>';
                        }
                        else{
                            break;
                        }
                        $i++;
                    }
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="'.$item->id.'" class="d-block show-po-cust mr-2 font-weight-bold" title="View Customers">'.$customers.' ...</a> ';
                }
                elseif($getCust->count() == 1)
                {
                    foreach ($getCust as $value)
                    {
                        if($value != Null)
                        {
                            $html_string = @$value[0]->customer->reference_name;
                        }
                    }
                }
                elseif($getCust->count() == 0)
                {
                    $html_string = "---";
                }

                return $html_string;
                break;

            case 'note':
                if($item->po_notes->count() > 0)
                {
                    $note = $item->po_notes->first()->note;
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#note-modal" data-id="'.$item->id.'" class="d-block show-po-note mr-2 font-weight-bold" title="View Notes">'.mb_substr($note, 0, 30).'</a> ';
                }
                else
                {
                    $html_string = '---';
                }
                return $html_string;
                break;

            case 'payment_due_date':
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'po_total':
                return $item->total !== null ? number_format($item->total,3,'.',',') : '--';
                break;

            case 'confirm_date':
                return $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';
                break;

            case 'supplier_ref':
                return $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';
                break;

            case 'supplier':
                return $item->supplier_id !== null ? @$item->PoSupplier->reference_name : @$item->PoWarehouse->warehouse_title;
                break;

            case 'group_number':
                return $item->p_o_group != NULL ? $item->p_o_group->po_group->ref_id  : "N.A";
                break;

            case 'action':
                $html_string = '
                <a href="'.url('get-purchase-order-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
               return $html_string;
                break;

            case 'checkbox':
                if($item->status == 13 || $item->status == 14 || $item->status == 15)
                {
                    $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                                <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                              </div>';
                }
                else{
                    $html_string = 'N.A';
                }
                return $html_string;
                break;

            case 'exchange_rate':
                if($item->exchange_rate !== null && $item->exchange_rate != 0)
                {
                $exchange_rate = (1 / $item->exchange_rate);
                $exchange_rate = number_format($exchange_rate,4,'.',',');
                }
                else{
                $exchange_rate = '--';
                }
                return $exchange_rate;
                break;
        }
    }

    public static function returnEditColumnReceivedIntoStock($column, $item) {
        switch ($column) {
            case 'ref_id':
                $item_id = '--';
                if($item->ref_id !== null){
                    $item_id = $item->ref_id;
                }
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';
                return $html_string;
                break;

            case 'invoice_number':
                $html = $item->invoice_number !== null ? $item->invoice_number : '---';
                return $html;
                break;
            case 'invoice_date':
                $html = $item->invoice_date !== null ? Carbon::parse($item->invoice_date)->format('d/m/Y'): '--';
                return $html;
                break;
        }
    }

    public static function returnFilterColumnReceivedIntoStock($column, $item, $keyword) {
        switch ($column) {
            case 'supplier':
                $sID =  Supplier::where('reference_name','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('supplier_id',$sID);
                break;

            case 'customer':
                $item->whereHas('PurchaseOrderDetail',function($q) use ($keyword) {
                    $q->whereHas('customer',function($qq) use ($keyword) {
                        $qq->where('reference_name','LIKE',"%$keyword%");
                    });
                });
                break;

            case 'to_warehouse':
                $wID =  Warehouse::where('status',1)->where('warehouse_title','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('to_warehouse_id',$wID);
                break;

            case 'note':
                $sID =  PurchaseOrderNote::where('note','LIKE', "%$keyword%")->pluck('po_id')->toArray();
                $item->whereIn('id',$sID);
                break;
        }
     }

     public static function returnAddColumnAllPos($column, $item) {
        switch ($column) {
            case 'to_warehouse':
                return $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : '--';
                break;

            case 'customer':
                $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', NULL)->where('po_id',$item->id)->get()->groupBy('customer_id');

                $html_string = '';

                if($getCust->count() > 1)
                {
                    $customers = '';
                    $i = 0;
                    foreach ($getCust as $cust) {
                        if ($i < 3) {
                            $customers .= $cust[0]->customer->reference_name . '<br>';
                        }
                        else{
                            break;
                        }
                        $i++;
                    }
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#customer-modal" data-id="'.$item->id.'" class="d-block show-po-cust mr-2 font-weight-bold" title="View Customers">'.$customers.' ...</a> ';
                }
                elseif($getCust->count() == 1)
                {
                    foreach ($getCust as $value)
                    {
                        if($value != Null)
                        {
                            $html_string = @$value[0]->customer->reference_name;
                        }
                    }
                }
                elseif($getCust->count() == 0)
                {
                    $html_string = "---";
                }

                return $html_string;
                break;

            case 'note':
                if($item->po_notes->count() > 0)
                {
                    $note = $item->po_notes->first()->note;
                    $html_string = '<a href="javascript:void(0)" data-toggle="modal" data-target="#note-modal" data-id="'.$item->id.'" class="d-block show-po-note mr-2 font-weight-bold" title="View Notes">'.mb_substr($note, 0, 30).' ...</i></a> ';
                }
                else
                {
                    $html_string = '---';
                }
                return $html_string;
                break;

            case 'payment_due_date':
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'po_total':
                return $item->total !== null ? number_format($item->total,3,'.',',') : '--';
                break;

            case 'confirm_date':
                return $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';
                break;

            case 'supplier_ref':
                return $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';
                break;

            case 'supplier':
                return $item->supplier_id !== null ? @$item->PoSupplier->reference_name : @$item->PoWarehouse->warehouse_title;
                break;

            case 'action':
                $html_string = '
                <a href="'.url('get-purchase-order-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>';
               return $html_string;
                break;

            case 'checkbox':
                if($item->status == 13)
                {
                    $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                        <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="product_check_'.$item->id.'">
                        <label class="custom-control-label" for="product_check_'.$item->id.'"></label>
                    </div>';
                }
                else
                {
                    $html_string = 'N.A';
                }
                    return $html_string;
                break;
        }
    }

    public static function returnEditColumnAllPos($column, $item) {
        switch ($column) {
            case 'ref_id':
                $item_id = '--';
                if($item->ref_id !== null){
                    $item_id = $item->ref_id;
                }
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';
                return $html_string;
                break;
            case 'invoice_date':
                $html = $item->invoice_date !== null ? Carbon::parse($item->invoice_date)->format('d/m/Y'): '--';
                return $html;
                break;
        }
    }

    public static function returnFilterColumnAllPos($column, $item, $keyword) {
        switch ($column) {
            case 'supplier':
                $sID =  Supplier::where('reference_name','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('supplier_id',$sID);
                break;

            case 'customer':
                $item->whereHas('PurchaseOrderDetail',function($q) use ($keyword) {
                    $q->whereHas('customer',function($qq) use ($keyword) {
                        $qq->where('reference_name','LIKE',"%$keyword%");
                    });
                });
                break;

            case 'to_warehouse':
                $wID =  Warehouse::where('status',1)->where('warehouse_title','LIKE', "%$keyword%")->pluck('id')->toArray();
                $item->whereIn('to_warehouse_id',$wID);
                break;

            case 'note':
                $sID =  PurchaseOrderNote::where('note','LIKE', "%$keyword%")->pluck('po_id')->toArray();
                $item->whereIn('id',$sID);
                break;
        }
     }

     public static function returnAddColumnAccountPayable($column, $payment_types, $item) {

        switch ($column) {
            case 'actions':
                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="oi_'.$item->id.'">
                                    <label class="custom-control-label" for="oi_'.$item->id.'"></label>
                                </div>';
                    return $html_string;
                break;

            case 'created_at':
                return $item->created_at != NULL ? Carbon::parse($item->created_at)->format('d/m/Y') : "--";
                break;

            case 'payment_reference_no':
                $html = '<input type="text" class="form-control" name="po_payment_reference_no" id="oi_payment_reference_no_'.$item->id.'">';
                return $html;
                break;

            case 'po_exchange_rate':
                return ($item->exchange_rate !== null && $item->exchange_rate !== 0) ? number_format((1 / $item->exchange_rate),2,'.',',') : '--';
                break;

            case 'invoice_date':
                return $item->invoice_date !== null ? $item->invoice_date : '--';
                break;

            case 'amount_paid':
                $amount_paid = $item->purchaseOrderTransaction ?$item->purchaseOrderTransaction->total_received:0;
                return @$amount_paid;
                break;

            case 'total_received':
                if($item->payment_exchange_rate !== null && $item->payment_exchange_rate != 0)
                {
                    $total = (1 / @$item->payment_exchange_rate) * $item->total;
                }
                else
                {
                    $total = 0 ;
                }
                $received = number_format($total,2,'.','');
                $html = '<input type="number" name="po_total_received" class="fieldFocusTotalReceived" id="po_total_received_'.$item->id.'" value='.@$received.' data-id="'.@$item->id.'">';
                return $html;
                break;

            case 'difference':
                if($item->payment_exchange_rate !== null && $item->payment_exchange_rate != 0)
                {
                  $total = (1 / @$item->payment_exchange_rate) * $item->total;
                }
                else
                {
                  $total = 0 ;
                }
                $received = number_format(($item->total_in_thb - $total),2,'.',',');

                $html = '<span id="po_difference_'.$item->id.'">'.@$received.'</span>';
                return $html;
                break;

            case 'received_date':
                $html = '<input type="date" class="form-control" name="po_received_date" id="po_received_date_'.$item->id.'">';
                return $html;
                break;

            case 'payment_method':
                $html_string = '<select name="po_payment_method" id="po_payment_method_'.$item->id.'"  class="select-common  form-control oi_payment_method ">';
                $html_string .= '<option value="" >Select</option>';
                foreach ($payment_types as $type) {
                $html_string .= '<option value="'.$type->id.'" >'.$type->id.'</option>';
                }
                $html_string .= '</select>';
                return $html_string;
                break;

            case 'exchange_rate':
                if($item->payment_exchange_rate !== null && $item->payment_exchange_rate != 0)
                {
                    $exchange = (1 / $item->payment_exchange_rate);
                }
                else
                {
                    $exchange = 0;
                }
                 $html_string = '<span class="m-l-15 inputDoubleClick" id="payment_exchange_rate"  data-fieldvalue="'.@$item->exchange_rate.'">'.$exchange.'</span>
                <input type="tel" autocomplete="nope" style="width:100%;" name="payment_exchange_rate" class="fieldFocus d-none" value="'.@$exchange.'" data-id="'.@$item->id.'">';
              return $html_string;
                break;

            case 'payment_due_date':
                return $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'po_total_in_thb':
                return $item->total_in_thb !== null ? number_format($item->total_in_thb,2,'.',',') : '--';
                break;

            case 'po_total_with_vat':
                return $item->total_with_vat !== null ? number_format($item->total_with_vat,2,'.',',') : '--';
                break;

            case 'po_total':
                return $item->total !== null ? number_format($item->total,2,'.',',') : '--';
                break;

            case 'total_received':
                $due = $item->total_in_thb - $item->total_paid;
                $due = round($due,2);
                $html = '<input type="number" class="form-control" name="oi_total_received" id="oi_total_received_'.$item->id.'" value="'.$due.'">';
                return $html;
                break;

            case 'supplier':
                return $item->supplier_id !== null ? '
                 <a href="'.url('get-supplier-detail/'.$item->PoSupplier->id).'" title="View Detail" target="_blank"><b>'.@$item->PoSupplier->reference_name.'</b></a>' : @$item->PoWarehouse->warehouse_title;
                break;

            case 'po_id':
                $item_id = $item->ref_id !== null ? $item->ref_id : '--';
                if ($item->status == 27) {
                    $item_id = $item->p_o_statuses->parent->prefix . $item_id;
                    $html_string = '
                    <a href="'.route('get-supplier-credit-note-detail', $item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';
                    return $html_string;
                    break;
                }
                $html_string = '
                 <a href="'.url('get-purchase-order-detail/'.$item->id).'" title="View Detail"><b>'.$item_id.'</b></a>';
                 return $html_string;
                break;

            case 'target_ship_date':
                return $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
                break;

            case 'payment_terms':
                return $item->payment_terms_id !== null ? @$item->pOpaymentTerm->title : '--';
                break;

            case 'supplier_currency':
                return $item->PoSupplier !== null ? @$item->PoSupplier->getCurrency->currency_code : '--';
                break;
        }
     }

     public static function returnEditColumnAccountPayable($column, $payment_types, $item) {
        switch ($column) {
            case 'invoice_number':
                $html = $item->invoice_number !== null ? $item->invoice_number : '--';
                return $html;
                break;

            case 'memo':
                return $item->memo !== null ?$item->memo : '--';
                break;
        }
     }

     public static function returnFilterColumnAccountPayable($column, $item, $keyword) {
        switch ($column) {
            case 'po_id':
                $item->where('ref_id','LIKE', "%$keyword%");
                break;

            case 'supplier':
                $item->whereHas('PoSupplier',function($q) use ($keyword){
                    $q->where('reference_name','LIKE', "%$keyword%");
                });
                break;

            case 'supplier_currency':
             $item->whereHas('PoSupplier', function($q) use ($keyword) {
                $q->whereHas('getCurrency', function($qq) use ($keyword) {
                    $qq->where('currency_code','LIKE', "%$keyword%");
                });
             });
             break;

            case 'payment_terms':
                $item->whereHas('pOpaymentTerm', function($q) use ($keyword) {
                    $q->where('title', 'LIKE', "%$keyword%");
                });
                break;


        }
     }


     public static function returnColumnSupplierOrders($column, $item) {
        switch ($column) {
            case 'action':
                $html_string = '<a href="'.url('supplier-transaction-detail/'.$item->id).'" class="actionicon" title="View Detail"><i class="fa fa-eye"></i></a>';
                return @$html_string;
                break;

            case 'total_not_due':
                $date = date('Y-m-d H:i:s');
                if(Auth::user()->role_id == 3)
                {
                  $order_ids = $item->supplier_po->where('status',15)->where('created_by',Auth::user()->id)->pluck('id')->toArray();
                }
                else
                {
                  $order_ids = $item->supplier_po->where('status',15);
                }
                $total_not_due = @$order_ids->sum('total_paid');
                return number_format($total_not_due,2,'.',',');

                break;

            case 'total_due':
                $date = date('Y-m-d H:i:s');
                if(Auth::user()->role_id == 2)
                {
                $orders = $item->supplier_po->where('status',15)->where('created_by',Auth::user()->id);
                }
                else
                {
                $orders = $item->supplier_po->where('status',15);
                }
              $total = $orders->sum('total_in_thb');
              $total_not_due = $item->supplier_po->where('status',15)->sum('total_paid');

              $total_due = $total-$total_not_due;
              return number_format($total_due,2,'.',',');
                break;

            case 'total':
                $date = date('Y-m-d H:i:s');
                if(Auth::user()->role_id == 2)
                {
                $total = $item->supplier_po->where('status',15)->where('created_by',Auth::user()->id)->sum('total_in_thb');
                }
                else
                {
                $total = $item->supplier_po->where('status',15)->sum('total_in_thb');
                }
                $total = number_format($total , 2);
                return  $total;
                break;

            case 'supplier_company':
                $html_string = '<a target="_blank" href="'.url('sales/get-supplier-detail/'.$item->id).'" title="View Detail"><b>'.($item->reference_name !== null ? $item->reference_name : "N.A").'</b></a>';
                return  $html_string;
                break;
        }
     }

}


