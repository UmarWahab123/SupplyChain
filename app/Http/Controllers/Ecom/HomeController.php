<?php

namespace App\Http\Controllers\Ecom;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Sales\Customer;
use App\Models\Common\Order\Order;
use Auth;
use DB;
use App\Models\Common\Order\OrderStatusHistory;
use App\User;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Carbon;
use App\Models\Common\Order\OrderProduct;
use App\QuotationConfig;
use App\Models\Common\WarehouseProduct;
use App\Models\Common\Product;
use App\Helpers\MyHelper;
use App\Helpers\EcomSortingHelper;
use App\Helpers\Datatables\EcomDashboardDatatable;

class HomeController extends Controller
{
    public function getDashboard()
    {
      $invoices_total = Order::select('id','total_amount')->where('primary_status',3)->where('ecommerce_order',1)->get();
      $admin_total_invoices = 0;
      foreach ($invoices_total as  $sales_order)
      {
        $admin_total_invoices += $sales_order->total_amount;
      }

      $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->where('ecommerce_order',1)->get();
      $admin_total_sales_draft = 0;
      foreach ($admin_orders_draft as  $sales_order)
      {
        $admin_total_sales_draft += $sales_order->total_amount;
      }
      $salesDraft_ecom = Order::select('id')->where('ecommerce_order',1)->where('primary_status', 2)->count('id');
      $Invoice1 = Order::where('dont_show',0)->where('primary_status', 3)->count('id');

        $Invoice_ecom = Order::whereHas('user',function($q){
            $q->Where('ecommerce_order',1);
        })->where('primary_status', 3)->count('id');
        $salesDraft = Order::select('id')->where('dont_show',0)->where('primary_status', 2)->count('id');
      return $this->render('ecom.home.dashboard',compact('admin_total_sales_draft', 'admin_total_invoices','salesDraft_ecom','Invoice1','Invoice_ecom','salesDraft'));
    }

    public function getDraftInvoices(Request $request)
    {
      $query = Order::select('orders.id','orders.status_prefix','orders.ref_prefix','orders.ref_id','orders.in_status_prefix','orders.in_ref_prefix','orders.in_ref_id','orders.user_id','orders.customer_id','orders.total_amount','orders.payment_terms_id','orders.memo','orders.primary_status','orders.status','orders.converted_to_invoice_on','orders.payment_due_date','orders.payment_image','orders.manual_ref_no','orders.is_vat','orders.created_at','orders.delivery_request_date','orders.delivery_note','orders.order_note_type')->groupBy('orders.id')->with('customer','customer.primary_sale_person','customer.primary_sale_person.get_warehouse','customer.CustomerCategory','statuses','order_products','user','order_notes','get_order_transactions','get_order_transactions.get_payment_ref')->where('orders.ecommerce_order',1)->where('orders.primary_status', 2);
      if($request->dosortby == 1)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 1);
        });
      }
      else if($request->dosortby == 2)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2);
        });
      }
      else if($request->dosortby == 3)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3);
        });
      }
      else if($request->dosortby == 6)
      {
        $query = $query->where(function($q){
         $q->where('orders,primary_status', 1)->where('orders.status', 6);
        });
      }
      else if($request->dosortby == 7)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 7);
        });
      }
      else if($request->dosortby == 8)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 8);
        });
      }
      else if($request->dosortby == 9)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 9);
        });
      }
      else if($request->dosortby == 10)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 10);
        });
      }
      else if($request->dosortby == 11)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3)->where('orders.status', 11);
        });
      }
      else if($request->dosortby == 24)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3)->where('orders.status', 24);
        });
      }
      else if($request->dosortby == 32)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3)->where('orders.status', 32);
        });
      }

       if($request->orders_status != null){

         if($request->orders_status == 'all'){
          $query->whereIn('orders.status', [34,35])->where('orders.ecommerce_order', 1);
         }else{
          $query->where('orders.status', $request->orders_status)->where('orders.ecommerce_order', 1);
         }

        }
        
      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        $query = $query->whereDate('orders.delivery_request_date', '>=', $date);
      }
      if($request->to_date != null)
      {
        $date = str_replace("/","-",$request->to_date);
        $date =  date('Y-m-d',strtotime($date));
        $query = $query->whereDate('orders.delivery_request_date', '<=', $date);
      }


      if($request->input_keyword != null)
        {
          $result = $request->input_keyword;
              if (strstr($result,'-'))
              {
                $query = $query->where(function($q) use ($result){
                 $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%");
                });

              }
              else
              {
                $resultt = preg_replace("/[^0-9]/", "", $result );
                $query = $query->where(function($q) use ($resultt){
                  $q->where('in_ref_id',$resultt)->orWhere('ref_id',$resultt);
                });
              }
        }

        $query = EcomSortingHelper::EcomDashboardSorting($request, $query);

        $dt =  Datatables::of($query);
        $add_columns = ['action', 'order_note', 'order_type', 'total_amount', 'discount', 'due_date', 'invoice_date', 'sub_total_2', 'reference_id_vat_2', 'vat_1', 'sub_total_1', 'reference_id_vat', 'ref_id', 'received_date','payment_reference_no', 'sales_person', 'number_of_products', 'status', 'created_at', 'target_ship_date', 'customer_ref_no', 'customer', 'inv_no', 'checkbox'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return EcomDashboardDatatable::returnAddColumnEcom($column, $item);
            });
        }

        $filter_columns = ['ref_id', 'sales_person', 'customer_ref_no', 'customer'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return EcomDashboardDatatable::returnFilterColumnEcom($column, $item, $keyword);
            });
        }

            $dt->rawColumns(['action','inv_no','order_type','order_note','ref_id','sales_person', 'customer', 'number_of_products','status','customer_ref_no','checkbox','total_amount','reference_id_vat','comment_to_warehouse','created_at','customer_company','remark','due_date']);
            $dt->with('post',$query->get()->sum('total_amount'));
            return $dt->make(true);
    }

    public function getPaymentImage(Request $request)
    {
      $prod_images = order::where('id', $request->prod_id)->first();
      $image_path = config("app.ecom_img_url")."/public/uploads/orders/$prod_images->payment_image";
      $html_string ='';
      if($prod_images->payment_image != null){
      $html_string .= '<div class="col-6 col-sm-3 gemstoneImg mb-3" id="prod-image-'.$prod_images->id.'">
      <figure>

      <a href="'.$image_path.'" target="_blank">
      <img style="width: 200px;
      height: 150px;" src="'.$image_path.'">
      </a>
      </figure>
      </div>';
      }else{

      $html_string .= '<div class="col-6 col-sm-3 gemstoneImg mb-3" id="prod-image-'.$prod_images->id.'">
      <figure>
      <a href="'.url('public/uploads/placeholder.png').'" target="_blank">
      <img style="height: auto; width: 450px;" src="'.url('public/uploads/placeholder.png').'">
      </a>
      </figure>
      </div>';

      }

        return $html_string;
    }

    public function CheckOrderStatus(){
      dd('here is status order');
       // $counter = 0;
      // $check_order = Order::where('primary_status', 2)->where('status', 34)->where('ecommerce_order', 1)->get();
      // foreach($check_order as $check){
      //   $is_order_exist = OrderStatusHistory::where('order_id', $check->id)->where('new_status', 'On Hold')->count();
      //   // $counter[] = $ch->order_id;
      //   if($is_order_exist == 0){
      //       $status_history             = new OrderStatusHistory;
      //       $status_history->user_id    = $check->user_id;
      //       $status_history->order_id   = $check->id;
      //       $status_history->status     = 'Created';
      //       $status_history->new_status = 'On Hold';
      //       $status_history->created_at = $check->created_at;
      //       $status_history->save();
      //   }
      // }
    }


    public function CancelOrder(Request $request)
    {
      $base_link  = config('app.ecom_url');
      $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
      $check_status = unserialize($ecommerceconfig->print_prefrences);
      $default_warehouse_id=  $check_status['status'][5];

      foreach($request->quotations as $quot){
        $order = Order::find($quot);
        $order_quantity = OrderProduct::whereNotNull('product_id')->where('order_id', $order->id)->get();
        foreach ($order_quantity as $value) {
          $product = Product::find($value->product_id);
                if($product->ecom_selling_unit){
                       // $new_reserve_qty = $value->quantity * $product->selling_unit_conversion_rate;
                       $new_reserve_qty = $value->quantity;

                    }else{
                       // $new_reserve_qty = $value->quantity * $product->unit_conversion_rate;
                       $new_reserve_qty = $value->quantity;


                    }

          // $warehouse_products = WarehouseProduct::where('warehouse_id',$default_warehouse_id)->where('product_id', $value->product_id)->first();
          // $my_helper =  new MyHelper;
          // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

          $warehouse_products = WarehouseProduct::where('warehouse_id',$default_warehouse_id)->where('product_id', $value->product_id)->first();

          $warehouse_products->ecommerce_reserved_quantity -= round($new_reserve_qty,3);

          $warehouse_products->available_quantity = $warehouse_products->current_quantity - ($warehouse_products->reserved_quantity+$warehouse_products->ecommerce_reserved_quantity);
          $warehouse_products->save();

        }

        $order->previous_primary_status = $order->primary_status;
        $order->previous_status = $order->status;
        $order->primary_status = 17;
        $order->status = 18;
        $order->save();
        // $uri = $base_link."/api/updateorderstatus/".$order->ecommerce_order_no."/".$order->primary_status."/".$order->status;
         // dd($uri);
        // $test =  $this->sendRequest($uri);


          // $check_order = Order::where('id', $quot)->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->first();
          // $status_history             = new OrderStatusHistory;
          // $status_history->user_id    = Auth::user()->id;
          // $status_history->order_id   = $check_order->id;
          // $status_history->status     = 'Ready to Pick';
          // $status_history->new_status = 'Cancelled';
          // $status_history->save();
      }
      return response()->json(['success' => true]);
    }


    //  public function ProceedInvoiceOrder(Request $request){

    //   //dd($request->all());
    //   //dd($request->check_id);
    //   if($request->check_id == 1){
    //     foreach($request->quotations as $quot){
    //     $order = Order::find($quot);
    //     return response()->json(['status' => $order->status]);
    //   }

    //   }else{
    //       foreach($request->quotations as $quot){
    //       $order = Order::find($quot);
    //       $order->primary_status = 2;
    //       $order->status = 35;
    //       $order->save();

    //       // $check_order = Order::where('id', $quot)->where('primary_status', 2)->where('status', 35)->where('ecommerce_order', 1)->first();
    //       // $status_history             = new OrderStatusHistory;
    //       // $status_history->user_id    = Auth::user()->id;
    //       // $status_history->order_id   = $check_order->id;
    //       // $status_history->status     = 'On Hold';
    //       // $status_history->new_status = 'Ready to Pick';
    //       // $status_history->save();
    //   }
    //   return response()->json(['success' => true]);

    //   }

    // }

     public function ProceedInvoiceOrder(Request $request){
      // dd($request->all());
      $base_link  = config('app.ecom_url');
      if($request->check_id == 1){
        foreach($request->quotations as $quot){
        $order = Order::find($quot);
        return response()->json(['status' => $order->status]);
      }

      }else{
          foreach($request->quotations as $quot){
          $order = Order::find($quot);
          $order->primary_status = 2;
          $order->status = 35;
          $order->save();
          foreach ($order->order_products as $prod) {
            $prod->status = 35;
            $prod->save();
          }
          $uri = $base_link."/api/updateorderstatus/".$order->ecommerce_order_no."/".$order->primary_status."/".$order->status;
         // dd($uri);
         $test =  $this->sendRequest($uri);
         // dd($test);
      }
      return response()->json(['success' => true]);

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

    public function getInvoiceEcom(){

        $admin_orders_draft = Order::select('id','total_amount')->where('primary_status',2)->where('ecommerce_order',1)->get();
           $admin_total_sales_draft = 0;
        foreach ($admin_orders_draft as  $sales_order)
        {
          $admin_total_sales_draft += $sales_order->total_amount;
        }


      $invoices_total = Order::select('id','total_amount')->where('primary_status',3)->where('ecommerce_order',1)->get();
           $admin_total_invoices = 0;
        foreach ($invoices_total as  $sales_order)
        {
          $admin_total_invoices += $sales_order->total_amount;
        }
        $salesDraft_ecom = Order::select('id')->where('ecommerce_order',1)->where('primary_status', 2)->count('id');
        $Invoice1 = Order::where('dont_show',0)->where('primary_status', 3)->count('id');

        $Invoice_ecom = Order::whereHas('user',function($q){
            $q->Where('ecommerce_order',1);
        })->where('primary_status', 3)->count('id');
        $salesDraft = Order::select('id')->where('dont_show',0)->where('primary_status', 2)->count('id');

      return $this->render('ecom.home.invoice-dashboard',compact('admin_total_invoices', 'admin_total_sales_draft','salesDraft_ecom','Invoice1','Invoice_ecom','salesDraft'));
    }

    public function getCompletedQuotationsData(Request $request){
        $ids = array_merge($this->user->customer->pluck('id')->toArray(),$this->user->secondary_customer->pluck('id')->toArray());

          $query = Order::select(DB::raw('sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
          END) AS vat_total_amount,
          sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
          END) AS vat_amount_price,
          sum(CASE
          WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
          END) AS not_vat_total_amount,
          sum(CASE
          WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
          END) AS all_discount'),
          'orders.id','orders.status_prefix','orders.ref_prefix','orders.ref_id','orders.in_status_prefix','orders.in_ref_prefix','orders.in_ref_id','orders.user_id','orders.customer_id','orders.total_amount','orders.delivery_request_date','orders.payment_terms_id','orders.memo','orders.primary_status','orders.status','orders.converted_to_invoice_on','orders.payment_due_date','orders.manual_ref_no','orders.is_vat')->groupBy('op.order_id')->with('customer','customer.primary_sale_person','customer.primary_sale_person.get_warehouse','customer.CustomerCategory','statuses','order_products','user','customer.getpayment_term','order_notes','get_order_transactions','get_order_transactions.get_payment_ref')->where('orders.ecommerce_order',1)->whereIn('orders.customer_id', $ids);

            $query->leftJoin('order_products as op','op.order_id','=','orders.id');
    //   }

      if($request->dosortby == 1)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 1);
        });
      }
      else if($request->dosortby == 2)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 2);
        });
      }
      else if($request->dosortby == 3)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 3);
        });
      }
      else if($request->dosortby == 6)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 1)->where('orders.status', 6);
        });
      }
      else if($request->dosortby == 7)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 2)->where('orders.status', 7);
        });
      }
      else if($request->dosortby == 8)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 2)->where('orders.status', 8);
        });
      }
      else if($request->dosortby == 9)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 2)->where('orders.status', 9);
        });
      }
      else if($request->dosortby == 10)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 2)->where('orders.status', 10);
        });
      }
      else if($request->dosortby == 11)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 3)->where('orders.status', 11);
        });
      }
      else if($request->dosortby == 24)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 3)->where('orders.status', 24);
        });
      }
      else if($request->dosortby == 32)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 3)->where('orders.status', 32);
        });
      }

    //   if($request->selecting_customer != null)
    //   {
    //     $query = $query->where('customer_id', $request->selecting_customer);
    //   }
    //   if($request->selecting_customer_group != null)
    //   {
    //     $query = $query->whereHas('customer',function($q) use ($request){
    //       $q->where('category_id',@$request->selecting_customer_group);
    //     });
    //   }
    //   if($request->selecting_sale != null)
    //   {
    //     $query = $query->where('user_id', $request->selecting_sale);
    //     // $query = $query->whereIn('customer_id',User::where('id',$request->selecting_sale)->first()->customer->pluck('id'));
    //   }
      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        if($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24)
        {
          if($request->date_type == '2'){
            $query = $query->where('orders.delivery_request_date', '>=', $date);
          }
          if($request->date_type == '1'){
            $query = $query->where('orders.converted_to_invoice_on', '>=', $date.' 00:00:00');
          }

        }
        else
        {
          $query = $query->where('orders.delivery_request_date', '>=', $date);
        }
      }
      if($request->to_date != null)
      {
        $date = str_replace("/","-",$request->to_date);
        $date =  date('Y-m-d',strtotime($date));
        if($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24)
        {
          if($request->date_type == '1'){
            $query = $query->where('orders.converted_to_invoice_on', '<=', $date.' 23:59:59');
          }
          if($request->date_type == '2'){
            $query = $query->where('orders.delivery_request_date', '<=', $date);
          }
        }
        else
        {
          $query = $query->where('orders.delivery_request_date', '<=', $date);
        }
      }
      if(@$request->is_paid == 11 || @$request->is_paid == 24)
      {
        $query = $query->where('orders.status',@$request->is_paid);
      }

      if($request->dosortby == 3)
      {
        $query = $query->orderBy('converted_to_invoice_on','DESC');
        // dd($query->get());
      }
      else
      {
        $query = $query->orderBy('orders.id','DESC');
      }

      if($request->input_keyword != null)
        {

          $result = $request->input_keyword;

              if (strstr($result,'-'))
              {
                $query = $query->where(function($q) use ($result){
                 $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%");
                });
                // $query = $query->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%");

              }
              else
              {
                $resultt = preg_replace("/[^0-9]/", "", $result );
                $query = $query->where(function($q) use ($resultt){
                  $q->where('in_ref_id',$resultt)->orWhere('ref_id',$resultt);
                });
              }
              // $query = $query->where('status',11)->where('total_amount','!=',0);
        }

        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {
              // dd($item);

                    $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="quot_'.$item->id.'">
                                    <label class="custom-control-label" for="quot_'.$item->id.'"></label>
                                </div>';
                    return $html_string;
                })
            ->addColumn('inv_no', function($item) {
              // dd($item->customer->primary_sale_person->get_warehouse->order_short_code);
              if($item->in_status_prefix !== null || $item->in_ref_prefix !== null || $item->in_ref_id !== null)
              {
                if($item->is_vat == 1)
                {
                  if($item->manual_ref_no !== null)
                  {
                    if($item->in_ref_id == $item->manual_ref_no)
                    {
                      $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id;
                    }
                    else
                    {
                      $ref_no = $item->in_ref_id;
                    }
                  }
                  else
                  {
                    $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id;
                  }
                }
                else
                {
                  $ref_no = @$item->in_status_prefix.'-'.$item->in_ref_prefix.$item->in_ref_id;
                }
              }
              else
              {
                $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->in_ref_id;
              }
              $html_string = '<a href="'.route('get-completed-invoices-details', ['id' => $item->id]).'" title="View Detail"><b>'.$ref_no.'</b></a>';
              return $html_string;
            })

            ->addColumn('customer', function ($item) {
              if($item->customer_id != null)
              {
                if($item->customer['reference_name'] != null)
                {
                  $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.$item->customer['reference_name'].'</b></a>';
                }
                else
                {
                  $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'. $item->customer['first_name'].' '.$item->customer['last_name'].'</a>';
                }
              }
              else{
                $html_string = 'N.A';
              }

              return $html_string;
            })

            ->filterColumn('customer', function( $query, $keyword ) {
             $query->whereHas('customer', function($q) use($keyword){
                    $q->where('reference_name','LIKE', "%$keyword%");
                });
            })

             ->addColumn('customer_company',function($item){
              $html_string ='<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.@$item->customer->company.'</b></a>';
              return $html_string;

            })
            ->addColumn('target_ship_date',function($item){
              return @$item->target_ship_date != null ?  Carbon::parse($item->target_ship_date)->format('d/m/Y'): '--';
            })
            ->addColumn('delivery_date',function($item){
              return @$item->delivery_request_date != null ?  Carbon::parse($item->delivery_request_date)->format('d/m/Y'): '--';
            })

            ->addColumn('remark',function($item){
              $customer_note = $item->order_notes->where('type','customer')->first();
              return @$customer_note != null ? '<span title="'.@$customer_note->note.'">'.@$customer_note->note.'</span>' : '--';
            })

            ->addColumn('comment_to_warehouse',function($item){
              $warehouse_note = $item->order_notes->where('type','warehouse')->first();
              return @$warehouse_note != null ? '<span title="'.@$warehouse_note->note.'">'.@$warehouse_note->note.'</span>' : '--';
            })

            ->addColumn('memo',function($item){
              return @$item->memo != null ? @$item->memo : '--';
            })

            ->addColumn('status',function($item){
              $html = '<span class="sentverification">'.@$item->statuses->title.'</span>';
              return $html;
            })

            ->addColumn('number_of_products', function($item) {
              $html_string = $item->order_products->count();
              return $html_string;
            })

            ->addColumn('sales_person', function($item) {

              return $item->user_id !== null ? @$item->user->name : '--';
              return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
            })
            ->addColumn('payment_reference_no',function($item){
              if(!$item->get_order_transactions->isEmpty())
              {
                $html='';
                foreach($item->get_order_transactions as $key => $ot)
                {
                  if($key==0)
                  {
                    $html.=$ot->get_payment_ref->payment_reference_no;
                  }
                  else
                  {
                    $html.=','.$ot->get_payment_ref->payment_reference_no;
                  }
                }
                return $html;
              }
              else
              {
                return '--';
              }
            })
            ->addColumn('received_date',function($item){

              if(!$item->get_order_transactions->isEmpty())
              {
                $count = count($item->get_order_transactions);
                $html=Carbon::parse(@$item->get_order_transactions[$count - 1]->received_date)->format('d/m/Y');

                // foreach($item->get_order_transactions as $key => $ot)
                // {
                //   if($key==0)
                //   {
                //     $html.=Carbon::parse(@$ot->received_date)->format('d/m/Y');
                //   }
                //   // else
                //   // {
                //   //   $html.=','.Carbon::parse(@$ot->received_date)->format('d/m/Y');
                //   // }
                // }
                return $html;
              }
              else
              {
                return '--';
              }
              return 'Date';
            })
            ->filterColumn('sales_person', function( $query, $keyword ) {
              $query->whereHas('customer', function($q) use($keyword){
                $q->whereHas('primary_sale_person', function($q) use($keyword){
                    $q->where('name','LIKE', "%$keyword%");
                });
              });
            },true )

            ->addColumn('ref_id', function($item) {
              // dd($item->customer->primary_sale_person->get_warehouse->order_short_code);
              if($item->status_prefix !== null || $item->ref_prefix !== null)
              {
                $ref_no = @$item->status_prefix.'-'.$item->ref_prefix.$item->ref_id;
              }
              else
              {
                $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
              }

              $html_string = '';
              if($item->primary_status == 2  )
              {
                $html_string .= '<a href="'.route('get-completed-draft-invoices', ['id' => $item->id]).'" title="View Products"><b>'.$ref_no.'</b></a>';
              }
              elseif($item->primary_status == 3)
              {
                if($item->ref_id == null){
                  $ref_no = '-';
                }
                $html_string .= $ref_no;
              }
              elseif($item->primary_status == 1)
              {
                $html_string = '<a href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products"><b>'.$ref_no.'</b></a>';
              }
              return $html_string;
              })

            ->filterColumn('ref_id', function( $query, $keyword ) {
              $result = $keyword;
              if (strstr($result,'-'))
              {
                $query = $query->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");
              }
              else
              {
                $resultt = preg_replace("/[^0-9]/", "", $result );
                $query = $query->orWhere('ref_id',$resultt)->orWhere('in_ref_id',$resultt);
              }

            })

            ->addColumn('reference_id_vat', function($item) {

              if($item->is_vat == 1)
              {
                if($item->manual_ref_no !== null)
                {
                  if($item->in_ref_id == $item->manual_ref_no)
                  {
                    $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1';
                  }
                  else
                  {
                    $ref_no = $item->in_ref_id.'-1';
                  }
                }
                else
                {
                  $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1';
                }
              }
              else
              {
                $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1';
              }
              return $ref_no;
            })
            ->addColumn('sub_total_1', function($item) {
              return $item->vat_total_amount !== null ? number_format($item->vat_total_amount,2,'.',',') : '0.00';
              return @$item->order_products != null ? @$item->getOrderTotalVat($item->id,0) : '--';
            })

            ->addColumn('vat_1', function($item) {
              return $item->vat_amount_price !== null ? number_format($item->vat_amount_price,2,'.',',') : '0.00';
              return @$item->order_products != null ? @$item->getOrderTotalVat($item->id,1) : '--';
            })

            ->addColumn('reference_id_vat_2', function($item) {
              if($item->is_vat == 1)
              {
                if($item->manual_ref_no !== null)
                {
                  if($item->in_ref_id == $item->manual_ref_no)
                  {
                    $ref_no = @$item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2';
                  }
                  else
                  {
                    $ref_no = $item->in_ref_id.'-2';
                  }
                }
                else
                {
                  $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2';
                }
              }
              else
              {
                $ref_no = $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2';
              }
              return $ref_no;
            })

            ->addColumn('sub_total_2', function($item) {
              return $item->not_vat_total_amount !== null ? number_format($item->not_vat_total_amount,2,'.',',') : '0.00';
              return @$item->order_products != null ? @$item->getOrderTotalVat($item->id,2) : '--';
            })

            ->addColumn('payment_term', function($item) {
              return ($item->customer->getpayment_term !== null ? $item->customer->getpayment_term->title : '--');
            })

            ->addColumn('invoice_date', function($item) {
              return Carbon::parse(@$item->converted_to_invoice_on)->format('d/m/Y');
            })

            ->addColumn('due_date', function($item) {
              return @$item->payment_due_date != null ? Carbon::parse(@$item->payment_due_date)->format('d/m/Y') : '--';
            })

            ->addColumn('discount', function($item) {
            return $item->all_discount !== null ? number_format(floor($item->all_discount*100)/100,2,'.',',') : '0.00';
              $item_level_discount = 0;
              $values = OrderProduct::where('order_id',$item->id)->get();

              foreach ($values as  $value) {
                if($value->discount != 0)
                {
                    if($value->discount == 100)
                  {
                    if($value->is_retail == 'pieces')
                    {
                      if($item->primary_status == 3){
                        $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                      }else{
                        $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                      }
                    }
                    else
                    {
                      if($item->primary_status == 3)
                      {
                        $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                      }
                      else{
                        $discount_full =  $value->unit_price_with_vat * $value->quantity;
                      }
                    }
                      $item_level_discount += $discount_full;
                  }
                  else
                  {
                    $item_level_discount += ($value->total_price / ((100 - $value->discount)/100)) - $value->total_price;
                  }
                }

              }

              return number_format(floor($item_level_discount*100)/100, 2, '.', ',');
              // return "ABC";
            })

            ->addColumn('total_amount', function($item) {
              return number_format(floor($item->total_amount*100)/100,2,'.',',');
              // return $html_string = '<span class="total_id">'.number_format($item->total_amount,2,'.',',').'</span>';
            })

            ->addColumn('action', function ($item) {
              // $html_string = '<a href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
              $html_string = '';
              /*if($item->primary_status == 2  )
              {
                $html_string .= '<a href="'.route('get-completed-draft-invoices', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
              }
              elseif($item->primary_status == 3)
              {
                $html_string .= '<a href="'.route('get-completed-invoices-details', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
              }
              elseif($item->primary_status == 1)
              {
                $html_string = '<a href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
              }*/

              if($item->primary_status == 1 &&  Auth::user()->role_id != 7)
              {
                $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
              }

              // if($item->primary_status == 2 && $item->status == 7)
              // {
              //   $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="'.$item->id.'" title="Void"><i class="fa fa-times"></i></a>';
              // }
              return $html_string;
            })

            ->rawColumns(['action','inv_no','ref_id','sales_person', 'customer', 'number_of_products','status','customer_ref_no','checkbox','total_amount','reference_id_vat','comment_to_warehouse','customer_company','remark','due_date'])
            ->with('post',$query->get()->sum('total_amount'))
            ->make(true);
    }
    public function getInvoicesData(Request $request){
      // dd($request->all());
          $query = Order::select('orders.id','orders.status_prefix','orders.ref_prefix','orders.ref_id','orders.in_status_prefix','orders.in_ref_prefix','orders.in_ref_id','orders.user_id','orders.customer_id','orders.total_amount','orders.delivery_request_date','orders.payment_terms_id','orders.memo','orders.primary_status','orders.status','orders.converted_to_invoice_on','orders.payment_due_date','orders.payment_image','orders.manual_ref_no','orders.is_vat','orders.created_at','orders.delivery_note','orders.order_note_type')->groupBy('orders.id')->with('customer','customer.primary_sale_person','customer.primary_sale_person.get_warehouse','customer.CustomerCategory','statuses','order_products','user','customer.getpayment_term','order_notes','get_order_transactions','get_order_transactions.get_payment_ref')->where('orders.ecommerce_order',1)->where('orders.primary_status', 3);

    //   }
       //dd($query->get()->count());
      if($request->dosortby == 1)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 1);
        });
      }
      else if($request->dosortby == 2)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2);
        });
      }
      else if($request->dosortby == 3)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3);
        });
      }
      else if($request->dosortby == 6)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 1)->where('orders.status', 6);
        });
      }
      else if($request->dosortby == 7)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 7);
        });
      }
      else if($request->dosortby == 8)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 8);
        });
      }
      else if($request->dosortby == 9)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 9);
        });
      }
      else if($request->dosortby == 10)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 2)->where('orders.status', 10);
        });
      }
      else if($request->dosortby == 11)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3)->where('orders.status', 11);
        });
      }
      else if($request->dosortby == 24)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3)->where('orders.status', 24);
        });
      }
      else if($request->dosortby == 32)
      {
        $query = $query->where(function($q){
         $q->where('orders.primary_status', 3)->where('orders.status', 32);
        });
      }

      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        // if($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24)
        // {
        //   if($request->date_type == '2'){
        //     $query = $query->where('orders.delivery_request_date', '>=', $date);
        //   }
        //   if($request->date_type == '1'){
        //     $query = $query->where('orders.converted_to_invoice_on', '>=', $date.' 00:00:00');
        //   }

        // }
        // else
        // {
          $query = $query->whereDate('orders.created_at', '>=', $date);
        // }
      }
      if($request->to_date != null)
      {
        $date = str_replace("/","-",$request->to_date);
        $date =  date('Y-m-d',strtotime($date));
        // if($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24)
        // {
        //   if($request->date_type == '1'){
        //     $query = $query->where('orders.converted_to_invoice_on', '<=', $date.' 23:59:59');
        //   }
        //   if($request->date_type == '2'){
        //     $query = $query->where('orders.delivery_request_date', '<=', $date);
        //   }
        // }
        // else
        // {
          $query = $query->whereDate('orders.created_at', '<=', $date);
        // }
      }
    //   if(@$request->is_paid == 11 || @$request->is_paid == 24)
    //   {
    //     $query = $query->where('orders.status',@$request->is_paid);
    //   }

      // if($request->dosortby == 3)
      // {
      //   $query = $query->orderBy('converted_to_invoice_on','DESC');
      //   // dd($query->get());
      // }
      // else
      // {
      //   $query = $query->orderBy('orders.id','DESC');
      // }


      if($request->input_keyword != null)
        {
          // $query = $query->where('in_ref_id', $request->input_keyword);

          $result = $request->input_keyword;
          // dd( $result);
            // dd($query->pluck('status'));

              if (strstr($result,'-'))
              {
                $query = $query->where(function($q) use ($result){
                 $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%");
                });

              }
              else
              {
                $resultt = preg_replace("/[^0-9]/", "", $result );
                $query = $query->where(function($q) use ($resultt){
                  $q->where('in_ref_id',$resultt)->orWhere('ref_id',$resultt);
                });
              }
              // $query = $query->where('status',11)->where('total_amount','!=',0);
        }
        $query = EcomSortingHelper::EcomDashboardSorting($request, $query);
        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {
              // dd($item);

                    $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="quot_'.$item->id.'">
                                    <label class="custom-control-label" for="quot_'.$item->id.'"></label>
                                </div>';
                    return $html_string;
                })

            ->addColumn('customer', function ($item) {
              if($item->customer_id != null)
              {
                if($item->customer['reference_name'] != null)
                {
                  $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.$item->customer['reference_name'].'</b></a>';
                }
                else
                {
                  $html_string = '<a target="_blank" href="'.url('sales/get-customer-detail/'.@$item->customer_id).'">'. $item->customer['first_name'].' '.$item->customer['last_name'].'</a>';
                }
              }
              else{
                $html_string = 'N.A';
              }

              return $html_string;
            })

            ->filterColumn('customer', function( $query, $keyword ) {
             $query->whereHas('customer', function($q) use($keyword){
                    $q->where('reference_name','LIKE', "%$keyword%");
                });
            })


            ->addColumn('delivery_date',function($item){
              return @$item->delivery_request_date != null ?  Carbon::parse($item->delivery_request_date)->format('d/m/Y'): '--';
            })



            ->addColumn('status',function($item){
              $html = '<span class="sentverification">'.@$item->statuses->title.'</span>';
              return $html;
            })


              ->addColumn('sales_person', function ($item) {
               $html_string = '<div class="d-flex justify-content-center text-center">';

                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#images-modal" data-id="'.$item->id.'" class="fa fa-camera d-block payment_img mr-2" title="Payment Image"></a> </div>';



                return $html_string;
            })

            ->filterColumn('sales_person', function( $query, $keyword ) {
              $query->whereHas('customer', function($q) use($keyword){
                $q->whereHas('primary_sale_person', function($q) use($keyword){
                    $q->where('name','LIKE', "%$keyword%");
                });
              });
            },true )

            ->addColumn('ref_id', function($item) {
              // dd($item->customer->primary_sale_person->get_warehouse->order_short_code);
              if($item->in_status_prefix !== null || $item->in_ref_prefix !== null)
              {
                $ref_no = @$item->in_status_prefix.'-'.$item->in_ref_prefix.$item->in_ref_id;
              }
              else
              {
                $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->in_ref_id;
              }

              $html_string = '';
              if($item->primary_status == 2  )
              {
                $html_string .= '<a href="'.route('get-completed-draft-invoices', ['id' => $item->id]).'" title="View Products"><b>'.$ref_no.'</b></a>';
              }
              elseif($item->primary_status == 3)
              {
                if($item->in_ref_id == null){
                  $ref_no = '-';
                }
                $html_string .= '<a href="'.route('get-completed-invoices-details', ['id' => $item->id]).'" title="View Products"><b>'.$ref_no.'</b></a>';
              }
              elseif($item->primary_status == 1)
              {
                $html_string = '<a href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products"><b>'.$ref_no.'</b></a>';
              }
              return $html_string;
              })

            ->filterColumn('in_ref_id', function( $query, $keyword ) {
              $result = $keyword;
              if (strstr($result,'-'))
              {
                $query = $query->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");
              }
              else
              {
                $resultt = preg_replace("/[^0-9]/", "", $result );
                $query = $query->orWhere('in_ref_id',$resultt)->orWhere('in_ref_id',$resultt);
              }

            })


            ->addColumn('due_date', function($item) {
              return @$item->payment_due_date != null ? Carbon::parse(@$item->payment_due_date)->format('d/m/Y') : '--';
            })

            ->addColumn('discount', function($item) {
            return $item->all_discount !== null ? number_format(floor($item->all_discount*100)/100,2,'.',',') : '0.00';
              $item_level_discount = 0;
              $values = OrderProduct::where('order_id',$item->id)->get();

              foreach ($values as  $value) {
                if($value->discount != 0)
                {
                    if($value->discount == 100)
                  {
                    if($value->is_retail == 'pieces')
                    {
                      if($item->primary_status == 3){
                        $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                      }else{
                        $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                      }
                    }
                    else
                    {
                      if($item->primary_status == 3)
                      {
                        $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                      }
                      else{
                        $discount_full =  $value->unit_price_with_vat * $value->quantity;
                      }
                    }
                      $item_level_discount += $discount_full;
                  }
                  else
                  {
                    $item_level_discount += ($value->total_price / ((100 - $value->discount)/100)) - $value->total_price;
                  }
                }

              }

              return number_format(floor($item_level_discount*100)/100, 2, '.', ',');
              // return "ABC";
            })

            ->addColumn('total_amount', function($item) {
              return number_format(floor($item->total_amount*100)/100,2,'.',',');
            })

             ->addColumn('order_type', function($item) {
                if($item->order_note_type !== null){
                   if($item->order_note_type == 1){
                    $note_type = 'Self Pick';
                    }else{
                      $note_type = 'Delivery';
                    }
                  }else {
                    $note_type = '--';
                  }

              return $note_type;
            })

              ->addColumn('order_note', function($item) {
              return @$item->delivery_note != null ? wordwrap(@$item->delivery_note,50,"<br>\n") : '--';
            })

            ->addColumn('action', function ($item) {
              $html_string = '';

              if($item->primary_status == 1 &&  Auth::user()->role_id != 7)
              {
                $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
              }

              return $html_string;
            })

            ->rawColumns(['action','inv_no','ref_id','order_type','order_note','sales_person', 'customer', 'number_of_products','status','customer_ref_no','checkbox','total_amount','reference_id_vat','comment_to_warehouse','customer_company','remark','due_date'])
            ->with('post',$query->get()->sum('total_amount'))
            ->make(true);


    }
    public function ContactUs(){
      return ('welcome to contact us');
    }
}
