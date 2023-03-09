<?php

namespace App\Jobs;

use App\ExportStatus;
use App\Exports\invoicetableExport;
use App\FailedJobException;
use App\Models\Common\Order\Order;
use App\Models\Common\TableHideColumn;
use App\Models\Sales\Customer;
use App\User;
use DB;
use File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Auth;

class InvoiceSaleExpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $role_id;
    protected $dosortbyx;
    protected $customer_id_select;
    protected $from_datex;
    protected $to_datex;
    protected $selecting_salex;
    protected $typex;
    protected $is_paidx;
    protected $date_radio_exp;
    protected $input_keyword_exp;
    protected $className;
    protected $selecting_customer_groupx;
    public $tries=1;
    public $timeout=1500;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($dosortbyx,$customer_id_select,$from_datex,$to_datex,$selecting_salex,$typex,$is_paidx,$date_radio_exp,$input_keyword_exp,$user_id,$role_id,$className,$selecting_customer_groupx)
    {
      $this->user_id = $user_id;
      $this->role_id = $role_id;
      $this->dosortbyx = $dosortbyx;
      $this->customer_id_select = $customer_id_select;
      $this->from_datex = $from_datex;
      $this->to_datex = $to_datex;
      $this->selecting_salex = $selecting_salex;
      $this->typex = $typex;
      $this->is_paidx = $is_paidx;
      $this->date_radio_exp = $date_radio_exp;
      $this->input_keyword_exp = $input_keyword_exp;
      $this->className = $className;
      $this->selecting_customer_groupx = $selecting_customer_groupx;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      $user_id=$this->user_id;
      $role_id =$this->role_id;
      $dosortbyx = $this->dosortbyx;
      $customer_id_select = $this->customer_id_select;
      $from_datex = $this->from_datex;
      $to_datex = $this->to_datex;
      $selecting_salex = $this->selecting_salex;
      $typex = $this->typex;
      $className = $this->className;
      $selecting_customer_groupx = $this->selecting_customer_groupx;
      $is_paidx = $this->is_paidx;
      $date_radio_exp = $this->date_radio_exp;
      $input_keyword_exp = $this->input_keyword_exp;
      try{

      $authUser = User::find($user_id);
      if($role_id == 4)
      {
        $warehouse_id = $authUser->warehouse_id;
        $ids = User::select('id')->where('warehouse_id',$warehouse_id)->where(function($query){
          $query->where('role_id',4)->orWhere('role_id',3);
        })->whereNull('parent_id')->pluck('id')->toArray();
        $all_customer_ids = array_merge(Customer::whereIn('primary_sale_id',$ids)->pluck('id')->toArray(),Customer::whereIn('secondary_sale_id',$ids)->pluck('id')->toArray());

        // $query = Order::select('id','status_prefix','ref_prefix','ref_id','in_status_prefix','in_ref_prefix','in_ref_id','user_id','customer_id','total_amount','delivery_request_date','payment_terms_id','memo','primary_status','status','converted_to_invoice_on','payment_due_date')->with('customer','customer.primary_sale_person','customer.primary_sale_person.get_warehouse','customer.CustomerCategory','statuses','order_products','user','customer.getpayment_term','order_notes')->whereIn('customer_id', $all_customer_ids);
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
          WHEN 1 THEN op.total_price
          END) AS sub_total_price,
          sum(CASE
          WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
          END) AS all_discount'),
          'orders.id','orders.status_prefix','orders.ref_prefix','orders.ref_id','orders.in_status_prefix','orders.in_ref_prefix','orders.in_ref_id','orders.user_id','orders.customer_id','orders.total_amount','orders.delivery_request_date','orders.payment_terms_id','orders.memo','orders.primary_status','orders.status','orders.converted_to_invoice_on','orders.payment_due_date','orders.manual_ref_no','orders.is_vat','orders.dont_show', 'orders.target_ship_date')->groupBy('op.order_id')->with(['customer','customer.primary_sale_person','customer.primary_sale_person.get_warehouse','customer.CustomerCategory','statuses','order_products','user','customer.getpayment_term','order_notes','get_order_transactions','get_order_transactions.get_payment_ref',
          'customer.getbilling' => function ($q){
            $q->where('is_default', 1);
        }])->whereIn('orders.customer_id', $all_customer_ids);
        $query = $query->leftJoin('order_products as op','op.order_id','=','orders.id');
      }
      else if($role_id == 1 || $role_id == 2 || $role_id == 7 || $role_id == 8 || $role_id == 11)
      {
        // $query = Order::select('id','status_prefix','ref_prefix','ref_id','in_status_prefix','in_ref_prefix','in_ref_id','user_id','customer_id','total_amount','delivery_request_date','payment_terms_id','memo','primary_status','status','converted_to_invoice_on','payment_due_date');
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
          WHEN 1 THEN op.total_price
          END) AS sub_total_price,
          sum(CASE
          WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
          END) AS all_discount'
        ),'orders.id','orders.status_prefix','orders.ref_prefix','orders.ref_id','orders.in_status_prefix','orders.in_ref_prefix','orders.in_ref_id','orders.user_id','orders.customer_id','orders.total_amount','orders.delivery_request_date','orders.payment_terms_id','orders.memo','orders.primary_status','orders.status','orders.converted_to_invoice_on','orders.payment_due_date','orders.manual_ref_no','orders.is_vat','orders.dont_show', 'orders.target_ship_date')->groupBy('op.order_id')->with(['customer','customer.primary_sale_person','customer.primary_sale_person.get_warehouse','customer.CustomerCategory','statuses','order_products','user','customer.getpayment_term','order_notes','get_order_transactions','get_order_transactions.get_payment_ref',
        'customer.getbilling' => function ($q){
            $q->where('is_default', 1);
        }]);
        $query = $query->leftJoin('order_products as op','op.order_id','=','orders.id');
      }
      else
      {
        $ids = array_merge($authUser->customer->pluck('id')->toArray(), $authUser->user_customers_secondary->pluck('id')->toArray());

            $query = Order::select(
                DB::raw('sum(CASE
        WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
        END) AS vat_total_amount,
        sum(CASE
        WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
        END) AS vat_amount_price,
        sum(CASE
        WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
        END) AS not_vat_total_amount,
        sum(CASE
        WHEN 1 THEN op.total_price
        END) AS sub_total_price,
        sum(CASE
        WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
        END) AS all_discount'),
                'orders.id',
                'orders.status_prefix',
                'orders.ref_prefix',
                'orders.ref_id',
                'orders.in_status_prefix',
                'orders.in_ref_prefix',
                'orders.in_ref_id',
                'orders.user_id',
                'orders.customer_id',
                'orders.total_amount',
                'orders.delivery_request_date',
                'orders.payment_terms_id',
                'orders.memo',
                'orders.primary_status',
                'orders.status',
                'orders.converted_to_invoice_on',
                'orders.payment_due_date',
                'orders.dont_show',
                'orders.target_ship_date'
            )->groupBy('op.order_id')->with(['customer', 'customer.primary_sale_person', 'customer.primary_sale_person.get_warehouse', 'customer.CustomerCategory', 'statuses', 'order_products', 'user', 'customer.getpayment_term', 'order_notes', 'get_order_transactions', 'get_order_transactions.get_payment_ref',
            'customer.getbilling' => function ($q){
                $q->where('is_default', 1)->where('status', 1)->where('title', '!=', 'Default Address');
            }
        ]);

            $query->leftJoin('order_products as op', 'op.order_id', '=', 'orders.id');
      }

      if($input_keyword_exp != null)
      {
        $result = $input_keyword_exp;
        if (strstr($result,'-'))
        {
          // $query = $query->where(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");
          $query = $query->where(function($q) use ($result){
            $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%");
          });

        }
        else
        {
          $resultt = preg_replace("/[^0-9]/", "", $result );
          // $query = $query->where('ref_id',$resultt)->orWhere('in_ref_id',$resultt);
          $query = $query->where(function($q) use ($resultt){
            $q->where('in_ref_id',$resultt)->orWhere('ref_id',$resultt);
          });
        }
      }

      if($dosortbyx == 1)
      {
        $query = $query->where(function($q){
         $q->where('primary_status', 1);
        });
      }
      else if($dosortbyx == 2)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 2);
        });
      }
      else if($dosortbyx == 3)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 3);
        });
      }
      else if($dosortbyx == 6)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 1)->where('orders.status', 6);
        });
      }
      else if($dosortbyx == 7)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 2)->where('orders.status', 7);
        });
      }
      else if($dosortbyx == 8)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 2)->where('orders.status', 8);
        });
      }
      else if($dosortbyx == 9)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 2)->where('orders.status', 9);
        });
      }
      else if($dosortbyx == 10)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 2)->where('orders.status', 10);
        });
      }
      else if($dosortbyx == 11)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 3)->where('orders.status', 11);
        });
      }
      else if($dosortbyx == 24)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 3)->where('orders.status', 24);
        });
      }
      else if($dosortbyx == 32)
      {
        $query = $query->where(function($q){
          $q->where('primary_status', 3)->where('orders.status', 32);
        });
      }
      // if($customer_id_select != null)
      // {
      //   $id_split = explode('-', $customer_id_select);
      //   $id_split = (int)$id_split[1];
      //   if ($className == 'parent') {
      //    $query = $query->whereHas('customer',function($q) use ($id_split){
      //       $q->where('category_id',@$id_split);
      //     });
      //   }
      //   else{
      //     $query = $query->where('customer_id', $id_split);
      //   }
      // }
      // if($selecting_customer_groupx != null)
      // {

      // }
      if($selecting_customer_groupx != null)
      {
        $id_split = explode('-', $selecting_customer_groupx);

        if ($id_split[0] == 'cat') {
          $query = $query->whereHas('customer',function($q) use ($id_split){
            $q->where('category_id', $id_split[1]);
          });
        }
        else{
          $query = $query->where('customer_id', $id_split[1]);
        }

        // $query = $query->whereHas('customer',function($q) use ($request){
        //   $q->where('category_id',@$request->selecting_customer_group);
        // });
      }
      if($selecting_salex != null)
      {
        $query = $query->where('user_id', $selecting_salex);
      }
      else if($role_id == 3)
      {
          $u_id = User::find($user_id);

          if($u_id->customer != null && $u_id->user_customers_secondary != null) {
            $query = $query->where(function($or) use ($selecting_salex,$u_id){
              $or->where('user_id', $u_id->id)->orWhereIn('customer_id',@$u_id->customer->pluck('id')->toArray())->orWhereIn('customer_id', @$u_id->user_customers_secondary->pluck('customer_id')->toArray());
          });
          } else {
            $query = $query->where(function($or) use ($u_id){
              $or->where('user_id', $u_id->id)->orWhereIn('customer_id', $u_id->user_customers_secondary->pluck('customer_id')->toArray());
          });
          }

      }
      else
      {
        $query = $query->where('orders.dont_show',0);
      }
      if($from_datex != null)
      {
        $date = str_replace("/","-",$from_datex);
        $date =  date('Y-m-d',strtotime($date));
        if($dosortbyx == 3 || $dosortbyx == 11 || $dosortbyx == 24)
        {
          if($date_radio_exp == '1')
          {
            $query = $query->where('orders.converted_to_invoice_on', '>=', $date.' 00:00:00');
          }
          if($date_radio_exp == '2')
          {
            $query = $query = $query->where('orders.delivery_request_date', '>=', $date);
          }
          if($date_radio_exp == '3')
          {
            $query = $query = $query->where('orders.target_ship_date', '>=', $date);
          }
        }
        else
        {
          $query = $query->where('orders.delivery_request_date', '>=', $date);
        }
      }
      if($to_datex != null)
      {
        $date = str_replace("/","-",$to_datex);
        $date =  date('Y-m-d',strtotime($date));
        if($dosortbyx == 3 || $dosortbyx == 11 || $dosortbyx == 24)
        {
          if ($date_radio_exp == '1')
          {
            $query =  $query->where('orders.converted_to_invoice_on', '<=', $date.' 23:59:59');
          }
          if($date_radio_exp == '2')
          {
            $query = $query = $query->where('orders.delivery_request_date', '<=', $date);
          }
          if($date_radio_exp == '3')
          {
            $query = $query = $query->where('orders.target_ship_date', '<=', $date);
          }
        }
        else
        {
          $query = $query->where('orders.delivery_request_date', '>=', $date);
        }
      }
      if(@$is_paidx == 11 || @$is_paidx == 24)
      {
        $query = $query->where('orders.status',@$is_paidx);
      }
      if($dosortbyx == 3)
      {
        $query = $query->orderBy('converted_to_invoice_on','DESC');
      }
      else
      {
        $query = $query->orderBy('orders.id','DESC');
      }

      // $query = $query->with('customer','customer.primary_sale_person','customer.primary_sale_person.get_warehouse','customer.CustomerCategory','statuses','order_products','user','customer.getpayment_term','order_notes');

      $current_date = date("Y-m-d");
      $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','my_invoices')->where('user_id', $authUser->id)->first();
      if($not_visible_columns)
      {
        $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
      }
      else
      {
        $not_visible_arr = [];
      }

      $query = $query;
      $file = storage_path('app/invoice-sale-export.xlsx');
      // dd($file);
      if(File::exists($file))
      {
        // dd('here');
          File::delete($file);
      }

      $return = \Excel::store(new invoicetableExport($query,$not_visible_arr),'invoice-sale-export.xlsx');

      if($return)
      {
        ExportStatus::where('user_id',$authUser->id)->where('type','invoice_sale_report')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s'),'exception' => $query->count()]);
        return response()->json(['msg'=>'File Saved']);
      }

      }

      catch(Exception $e) {
        $this->failed($e);
      }
      catch(MaxAttemptsExceededException $e) {
        $this->failed($e);
      }
    }

    public function failed( $exception)
    {
      ExportStatus::where('type','invoice_sale_report')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException            = new FailedJobException();
      $failedJobException->type      = "Complete Products Export";
      $failedJobException->exception = $exception->getMessage();
      $failedJobException->save();
    }
}
