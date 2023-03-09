<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Product;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Warehouse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;


class UpdateStockCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user_id;
    protected $role_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id,$role_id)
    {
        $this->request=$data;
        $this->user_id=$user_id;
        $this->role_id=$role_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try
        {
            $request=$this->request;
            $user_id=$this->user_id;
            $role_id=$this->role_id;

            $warehouses = Warehouse::where('id',$role_id)->get();
            $html = '<table style="width:100%;text-align:center;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Quantity In</th>
                        <th>Parent ID</th>
                        <th>Available Stock</th>
                    </tr>
                </thead>
                <tbody>';
                foreach ($warehouses as $warehouse) {
                    $products = Product::where('brand_id','!=',$warehouse->id)->orWhereNull('brand_id')->get();

                    foreach ($products as $product) {
                        # code...

                        $stock_card = StockManagementIn::where('warehouse_id',$warehouse->id)->where('product_id',$product->id)->orderBy('expiration_date','DESC')->get();
                        // dd($stock_card);
                        if($stock_card->count() > 0)
                        {
                            foreach ($stock_card as $card) {
                           $stock_out_in = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_in');
                           $stock_out_out = \DB::table('stock_management_outs')->where('smi_id',$card->id)->sum('quantity_out');

                           // sum(\DB::raw('sales - products_total_cost')),
                           // $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->whereNotNull('quantity_in')->orderBy(\DB::raw('CONVERT(quantity_in,DECIMAL)'),'DESC')->get();
                           $stock_out_in_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_in')->orderBy('id','desc')->get();

                           $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','desc')->get();

                           // dd($stock_out_in + $stock_out_out);
                           $in_stock = $stock_out_in - abs($stock_out_out);


                           foreach ($stock_out_in_query as $value) {
                            $value->available_stock = null;
                            $value->save();
                            if($in_stock != 0 && $in_stock > 0)
                            {
                                if($in_stock >= abs($value->quantity_in))
                                {
                                    $value->available_stock = $value->quantity_in;
                                    $in_stock = $in_stock - abs($value->quantity_in); 
                                    $value->save();
                                }

                                elseif($in_stock < abs($value->quantity_in))
                                {
                                    $value->available_stock = abs($in_stock);
                                    $in_stock = 0;
                                    $value->save();
                                    break;
                                }
                              }

                           }
                           if($in_stock < 0)
                           {
                            foreach ($stock_out_out_query as $value) {
                             if($in_stock < 0)
                              {
                                if(abs($in_stock) >= abs($value->quantity_out))
                                {
                                    $value->available_stock = $value->quantity_out;
                                    $in_stock = $in_stock + abs($value->quantity_out); 
                                    $value->save();
                                }

                                elseif(abs($in_stock) < abs($value->quantity_out))
                                {
                                    $value->available_stock = $in_stock;
                                    $in_stock = 0;
                                    $value->save();
                                    break;
                                }
                              }
                           }
                        }
                           // $stock_out_out_query = StockManagementOut::where('smi_id',$card->id)->where('product_id',$product->id)->whereNotNull('quantity_out')->orderBy('id','asc')->get();
                       //     if(round(($stock_out_in+$stock_out_out),2) != 0 || ($stock_out_in == 0 && $stock_out_out == 0))
                       //     {
                       //     foreach ($stock_out_in_query as $stock) {
                       //          $html .= '
                       //                  <tr>
                       //                      <td>'.$stock->id.'</td>
                       //                      <td>'.$stock->quantity_in.'</td>
                       //                      <td>'.$stock->parent_id_in.'</td>
                       //                      <td>'.$stock->available_stock.'</td>
                       //                  </tr>';
                       //     }

                       //     $html .= '
                       //              <tr>
                       //                  <td><b>ID</b></td>
                       //                  <td><b>Quantity Out</b></td>
                       //                  <td><b>Parent ID</b></td>
                       //                  <td><b>Available Stock</b></td>
                       //              </tr>';

                       //     foreach ($stock_out_out_query as $stock) {
                       //          $html .= '
                       //                  <tr>
                       //                      <td>'.$stock->id.'</td>
                       //                      <td>'.$stock->quantity_out.'</td>
                       //                      <td>'.$stock->parent_id_in.'</td>
                       //                      <td>'.$stock->available_stock.'</td>
                       //                  </tr>';
                       //     }
                       // }
                            
                        }
                        }

                        $product->brand_id = $warehouse->id;
                        $product->save();

                        \DB::table('products')
                        ->where('id', $product->id)
                        ->update(['brand_id' => $warehouse->id]);
        }

                }

                $html .= '</tbody></table>';
                $done = true;
              $filename = 'done';

                // return $html;

                if($done)
                {
                    ExportStatus::where('user_id',$user_id)->where('type','update_old_record')->update(['status'=>0,'last_downloaded'=>'28-12-2020','file_name'=>$filename]);
                   return response()->json(['msg'=>'File Saved','html' => $html]);
                }
            }

        catch(Exception $e) {
        $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
                $this->failed($e);
            }
    }

    public function failed($exception)
    {
       
        ExportStatus::where('type','update_old_record')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Update Old Data";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
       
    }
}
