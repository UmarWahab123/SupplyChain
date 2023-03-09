<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ExportStatus;
use App\Exports\purchaseListExport;
use App\Variable;
use App\FailedJobException;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Warehouse;
use Illuminate\Http\Request;

class PurchaseListExpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $tsd;
    protected $supply_from_exp;
    protected $supply_to_exp;
    protected $date_filter_exp1;
    protected $date_filter_exp2;
    protected $sort_order;
    protected $column_name;
    protected $search_value;
    public $tries=1;
    public $timeout=1500;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sort_order,$column_name,$supply_from_exp,$supply_to_exp,$date_filter_exp1,$date_filter_exp2,$tsd,$user_id, $search_value)
    {
        $this->user_id = $user_id;
        $this->tsd = $tsd;
        $this->supply_from_exp = $supply_from_exp;
        $this->supply_to_exp = $supply_to_exp;
        $this->date_filter_exp1 = $date_filter_exp1;
        $this->date_filter_exp2 = $date_filter_exp2;
        $this->sort_order = $sort_order;
        $this->column_name = $column_name;
        $this->search_value = $search_value;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $user_id = $this->user_id;
       $tsd = $this->tsd;
       $supply_from_exp = $this->supply_from_exp;
       $supply_to_exp = $this->supply_to_exp;
       $date_filter_exp1 = $this->date_filter_exp1;
       $date_filter_exp2 = $this->date_filter_exp2;
       $sort_order = $this->sort_order;
       $column_name = $this->column_name;
       $search_value = $this->search_value;

        try{

            $vairables=Variable::select('slug','standard_name','terminology')->get();
            $getWarehouses = Warehouse::where('status',1)->get();

            $global_terminologies=[];
            foreach($vairables as $variable)
            {
                if($variable->terminology != null)
                {
                    $global_terminologies[$variable->slug]=$variable->terminology;
                }else{
                    $global_terminologies[$variable->slug]=$variable->standard_name;
                }
            }


            $query = OrderProduct::with('product','get_order')->whereHas('get_order', function($q){
            $q->where('primary_status',2);
            $q->where('status',7);
        })->where('order_products.status',7)->where('order_products.quantity','!=',0)->where('order_products.product_id','!=',null)->select('order_products.*');



        if($supply_from_exp != '')
        {
            $Stype = explode('-', $supply_from_exp);
            if($Stype[0] == 's')
            {
                $query->where('order_products.supplier_id', $Stype[1]);
                // dd($query->get());
            }
            if($Stype[0] == 'w')
            {
                $query->where('order_products.from_warehouse_id', $Stype[1]);
            }
        }

        if($supply_to_exp != '')
        {
            $query->where('order_products.warehouse_id', $supply_to_exp);
        }

        if($date_filter_exp1 != '')
        {
            $date = str_replace("/","-",$date_filter_exp1);
            $date =  date('Y-m-d',strtotime($date));

            $query->whereHas('get_order', function($q) use($date) {
                $q->where('orders.target_ship_date','>=', $date);
            });
        }

        if($date_filter_exp2 != '')
        {
            $date = str_replace("/","-",$date_filter_exp2);
            $date =  date('Y-m-d',strtotime($date));

            $query->whereHas('get_order', function($q) use($date) {
                $q->where('orders.target_ship_date','<=', $date);
            });
        }

           $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','purchase_list')->where('user_id',$user_id)->first();
        //    dd($not_visible_columns);
            if($not_visible_columns!=null)
            {
              $non_visible_arr = explode(',',$not_visible_columns->hide_columns);
            }
            else
            {
              $non_visible_arr=[];
            }

        /***********/
        $current_date = date("Y-m-d");
        $request_data = new Request();
        $request_data->replace(['sort_order' => $sort_order, 'column_name' => $column_name]);
        $query = OrderProduct::PurchaseListSorting($request_data, $query, $getWarehouses);

        if($search_value != null) {
            $query->where(function($q) use ($search_value) {
                $q->whereHas('product', function($r) use($search_value) {
                    $r->where('refrence_code', 'LIKE', '%' . $search_value . '%');
                })
                ->orWhereHas('get_order',function($s) use($search_value) {
                    $s->whereHas('customer', function($ss) use($search_value) {
                        $ss->where('reference_name','LIKE', '%' . $search_value . '%');
                    });
                })
                ->orWhereHas('product', function($t) use($search_value) {
                    $t->where('short_desc', 'LIKE','%'.$search_value.'%');
                })
                ->orWhereHas('get_order', function ($u) use($search_value) {
                    $d = substr($search_value, 1);
                    $u->where('ref_id', 'LIKE','%'.$d.'%');
                })
                ->orWhereHas('get_order', function ($v) use($search_value) {
                    $v->whereHas('user', function($vv) use ($search_value) {
                        $vv->where('name', 'LIKE', '%' . $search_value . '%');
                    });
                })
                ->orWhereHas('product', function ($w) use($search_value) {
                    $w->whereHas('productCategory', function($ww) use ($search_value) {
                        $ww->where('title', 'LIKE', '%' . $search_value . '%');
                    });
                })
                ->orWhereHas('product', function ($x) use($search_value) {
                    $x->whereHas('productSubCategory',function($xx) use($search_value) {
                        $xx->where('title', 'LIKE', '%' . $search_value . '%');
                    });
                })
                ->orWhereHas('product', function ($y) use($search_value) {
                    $y->whereHas('units', function($yy) use($search_value) {
                        $yy->where('title', 'LIKE', '%' . $search_value . '%');
                    });
                })
                ->orWhereHas('order_product_note', function ($z) use($search_value) {
                    $z->where('note', 'LIKE','%'.$search_value.'%');
                });
            });
        }

        $query = $query->get();

          $return = \Excel::store(new purchaseListExport($query,$non_visible_arr,$global_terminologies,$tsd,$getWarehouses), 'purchase-list-export.xlsx');

            if($return)
            {
             ExportStatus::where('user_id',$user_id)->where('type','purchase_list_export')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
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
            ExportStatus::where('type','purchase_list_export')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
            $failedJobException=new FailedJobException();
            $failedJobException->type="Complete Products Export";
            $failedJobException->exception=$exception->getMessage();
            $failedJobException->save();
        }
}
