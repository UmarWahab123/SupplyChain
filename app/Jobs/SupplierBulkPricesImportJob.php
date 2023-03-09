<?php

namespace App\Jobs;

use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\Models\Common\Supplier;
use App\ProductHistory;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;

class SupplierBulkPricesImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $supplier_id;
    public  $response;
    public  $result;
    public  $error_msgs;
    protected $rows;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($rows, $supplier_id,$user_id)
    {
        $this->rows        = $rows;
        $this->supplier_id = $supplier_id;
        $this->user_id     = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $rows        = $this->rows;
            $supplier_id = $this->supplier_id;
            $user_id     = $this->user_id;

            $html_string = '';
            $error = 0;
            if ($rows->count() > 1) 
            {
                $row1 = $rows->toArray();
                $remove = array_shift($row1);
                $remove = array_shift($row1);

                $increment = 3;
                foreach ($row1 as $row) 
                {
                    if(array_key_exists("system_code", $row))
                    {
                        if($row['system_code'] == null)
                        {
                            $error = 1;
                            $html_string .= '<li>PF# is empty on row <b>'.$increment.'</b> </li>';
                            continue;
                        }
                        else
                        {
                            $product  = Product::where('refrence_code', $row['system_code'])->first();
                            if($product)
                            {
                                $supplier = Supplier::where('reference_name', $row['supplier'])->count();
                                $supplier_product = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $this->supplier_id)->first();
                                // this is the price of after conversion for THB
                                if($supplier_product != null && $supplier != 0) 
                                {
                                    $old_price_value = $supplier_product->buying_price;
                                    $supplier_conv_rate_thb = $supplier_product->supplier->getCurrency->conversion_rate;
                                    $importTax = $supplier_product->import_tax_actual != null  ? $supplier_product->import_tax_actual : @$product->import_tax_book;

                                    if (is_numeric($row['purchasing_price_euro'])) 
                                    {
                                        $supplier_product->buying_price = $row['purchasing_price_euro'];
                                        $supplier_product->buying_price_in_thb = ($row['purchasing_price_euro'] / $supplier_conv_rate_thb);
                                        // this condition will only execute if this is the default supplier of product
                                        if($product->supplier_id == $this->supplier_id)
                                        {
                                            $price_calculation = $supplier_product->defaultSupplierProductPriceCalculation($product->id, $product->supplier_id, $row['purchasing_price_euro'], $supplier_product->freight, $supplier_product->landing, $supplier_product->extra_cost, $importTax, $supplier_product->extra_tax);
                                        }
                                    }
                                    else
                                    {
                                        $error = 1;
                                        $html_string .= '<li>Invalid Price on row <b>'.$increment.'</b> </li>';
                                        $increment++;
                                        continue;
                                    }

                                    if (is_numeric($row['expected_lead_time_in_days'])) 
                                    {
                                        $supplier_product->leading_time = $row['expected_lead_time_in_days'];
                                    }
                                    $supplier_product->save();

                                    if (is_numeric($row['purchasing_price_euro'])) 
                                    {
                                        $product_history              = new ProductHistory;
                                        $product_history->user_id     = $user_id;
                                        $product_history->product_id  = $product->id;
                                        $product_history->column_name = "Purchasing Price Update From Bulk Prices Upload"." (Supplier - ".$supplier_product->supplier->reference_name." )";
                                        $product_history->old_value   = $old_price_value;
                                        $product_history->new_value   = $row['purchasing_price_euro'];
                                        $product_history->save();
                                    }
                                }
                                else
                                {
                                    $error = 1;
                                    $html_string .= '<li>Supplier Reference name is not matched with row <b>'.$increment.'</b> </li>';
                                    $increment++;
                                    continue;
                                }
                            }
                            else
                            {
                                $error = 1;
                                $html_string .= '<li>There is no such item exist in the system PF# <b>'.$row['system_code'].'</b> on row <b>'.$increment.'</b> </li>';
                                $increment++;
                                continue;
                            }
                        }

                        $increment++;   
                    }
                    else
                    {
                        $error = 3;
                        $html_string .= '<li>PF# column not Found !!! </li>';
                        break;
                    }
                }
            }
            else
            {
                $success = 'fail';
                $error_msgss = 'File is Empty Please Upload Valid File !!!';
                $error = 2;
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }

            if($error == 1)
            {
                $success = 'hasError';
                $error_msgss = "Prices Updated Successfully, But Some Of Them Has Issues !!!";
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }
            elseif($error == 2)
            {
                $error_msgss = 'File is Empty Please Upload Valid File !!!';
                $success = 'fail';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }
            elseif($error == 3)
            {
                $error_msgss = 'File you are uploading is not a valid file, please upload a valid file !!!';
                $success = 'invalid';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }
            else
            {
                if($error == 0)
                {
                    $html_string = '';
                }
                $error_msgss = 'Prices Updated Successfully !!!';
                $success = 'pass';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }

            ExportStatus::where('type','supplier_bulk_prices_import')->update(['status'=>0,'last_downloaded'=>date('Y-m-d'),'exception'=>$html_string, 'error_msgs'=>$error_msgss]);
            return response()->json(['msg'=>'File Saved']);

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
        ExportStatus::where('type','supplier_bulk_prices_import')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException            = new FailedJobException();
        $failedJobException->type      = "supplier_bulk_prices_import";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }

    public function filterFunction($success = null)
    {
        if($success == 'fail')
        {
            $this->response = "File is Empty Please Upload Valid File !!!";
            $this->result   = "true";
        }
        elseif($success == 'pass')
        {
            $this->response = "Prices Updated Successfully !!!";
            $this->result   = "false";
        }
        elseif($success == 'hasError')
        {
            $this->response = "Prices Updated Successfully, But Some Of Them Has Issues !!!";
            $this->result   = "withissues";
        }
        elseif($success == 'invalid')
        {
            $this->response = "File you are uploading is not a valid file, please upload a valid file !!!";
            $this->result   = "true";
        }
    }
}
