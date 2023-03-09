<?php

namespace App\Jobs;

use Exception;
use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\Unit;
use App\Models\Common\Brand;
use App\ProductTypeTertiary;
use Illuminate\Bus\Queueable;
use App\Models\Common\Country;
use App\Models\Common\Product;
use App\Models\Common\Currency;
use App\Models\Common\Supplier;
use App\Models\Common\ProductType;
use App\Models\Common\TempProduct;
use App\Models\Common\ProductCategory;
use Illuminate\Queue\SerializesModels;
use App\Models\Common\CustomerCategory;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Common\ProductSecondaryType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;

class SupplierBulkProductsImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries=1;
    public $timeout=3600;
    protected $rows;
    protected $user_id;
    private $supplier_id;
    public  $response;
    public  $result;
    public  $error_msgs;
    protected $row_count = 0;

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
            $error_msgss = '';
            $error       = 0;
            $type_2_id = null;

            if ($rows->count() > 1)
            {
                $row1 = $rows->toArray();
                $remove = array_shift($row1);
                $remove = array_shift($row1);

                $increment = 3;
                foreach ($row1 as $row)
                {
                    if ($row['supplier'] == null)
                    {
                        $error = 1;
                        $html_string .= '<li>Supplier Reference name is empty on row <b>'.$increment.'</b> </li>';
                        continue;
                    }
                    else
                    {
                        $supplier = Supplier::find($this->supplier_id);
                        if ($supplier && strtolower(trim($supplier->reference_name)) != strtolower(trim($row['supplier'])) )
                        {
                            $error = 1;
                            $html_string .= '<li>Supplier Reference name is not matched with row <b>'.$increment.'</b> </li>';
                            continue;
                        }
                        else
                        {
                            $has_error = 1;
                            $status = 1;
                            $supplier_id = null;

                            #row[0]  => Our Reference #
                            #row[1]  => System Code
                            #row['supplier']  => Supplier
                            $supplier = Supplier::where('reference_name', '=', $row['supplier'])->first();
                            if ($supplier != null)
                            {
                                $supplier_id = $supplier->id;
                            }
                            else
                            {
                                $supplier_id = $row['supplier'];
                                $status = 0;
                            }

                            #row[3]  => Supplier Description
                            #row[4]  => Supplier Packing Unit
                            #row['order_qty_unit']  => Supplier Billing Unit to Packing Unit
                            if (!is_numeric($row['order_qty_unit']))
                            {
                                $row['order_qty_unit'] = null;
                            }
                            #row['m_o_q']  => Supplier MOQ (Minimum number of Buying Unit They Will Sell Us)
                            if (!is_numeric($row['m_o_q']))
                            {
                                $row['m_o_q'] = null;
                            }

                            #row['supplier_billed_unit']  => Supplier Billed Unit
                            $buying_unit = Unit::where('title', $row['supplier_billed_unit'])->first();
                            if ($buying_unit)
                            {
                                $buying_unit_id = $buying_unit->id;
                            }
                            else
                            {
                                $buying_unit_id = $row['supplier_billed_unit'];
                                $has_error = 1;
                                $status = 0;
                            }

                            #row['purchasing_price_euro']  => Buying Price
                            $buying_price = $row['purchasing_price_euro'];

                            if (!is_numeric($buying_price))
                            {
                                $buying_price = null;
                            }
                            #row['gross_weight']  => Gross Weight
                            if (!is_numeric($row['gross_weight']))
                            {
                                $row['freight'] = null;
                            }

                            #row['freight']  => Freight Per Billed Unit
                            if (!is_numeric($row['freight']) || $row['purchasing_price_euro'] == null)
                            {
                                $row['freight'] = null;
                            }
                            #row['landing']  => Landing Per Billed Unit
                            if (!is_numeric($row['landing']) || $row['purchasing_price_euro'] == null)
                            {
                                $row['landing'] = null;
                            }

                            #row['import_tax_actual']  => Import Tax Actual (updated)
                            $import_tax_actual = $row['import_tax_actual'];

                            if (!is_numeric($import_tax_actual) || $row['purchasing_price_euro'] == null)
                            {
                                $import_tax_actual = null;
                            }

                            #row['extra_cost_per_billed_unit']  => Extra Cost Per Billed Unit
                            if (!is_numeric($row['extra_cost_per_billed_unit']) || $row['purchasing_price_euro'] == null)
                            {
                                $row['extra_cost_per_billed_unit'] = null;
                            }

                            #row['extra_tax_thb']  => Extra Tax THB
                            if (!is_numeric($row['extra_tax_thb']) || $row['purchasing_price_euro'] == null)
                            {
                                $row['extra_tax_thb'] = null;
                            }

                            #row['selling_unit']  => Selling Unit
                            $selling_unit = Unit::where('title', $row['selling_unit'])->first();
                            if ($selling_unit != null)
                            {
                                $selling_unit_id = $selling_unit->id;
                            }
                            else
                            {
                                $selling_unit_id = $row['selling_unit'];
                                $has_error = 1;
                                $status = 0;
                            }

                            #row['unit_conversion_rate']  => Unit Conversion Rate
                            if ($row['unit_conversion_rate'] != null)
                            {
                                $unit_conversion_rate = $row['unit_conversion_rate'];
                            }
                            else
                            {
                                if ($buying_unit_id == $selling_unit_id)
                                {
                                    $unit_conversion_rate = 1;
                                }
                                else
                                {
                                    $unit_conversion_rate = null;
                                    $status = 0;
                                }
                            }
                            if (!is_numeric($unit_conversion_rate))
                            {
                                $unit_conversion_rate = null;
                            }

                            #row['expected_lead_time_in_days'] => Expected Lead Time (days)
                            if (!is_numeric($row['expected_lead_time_in_days']))
                            {
                                $row['expected_lead_time_in_days'] = null;
                            }

                            #row[18] => Suppliers Product Reference No.
                            #row[19] => Brand
                            #row['product_description'] => Product Description
                            if ($row['product_description'] == null)
                            {
                                $status = 0;
                            }
                            #row[21] => Avg Weight per piece or box
                            #row['stock_unit']  => Stock Unit
                            $stock_unit = Unit::where('title', $row['stock_unit'])->first();
                            if ($stock_unit != null)
                            {
                                $stock_unit_id = $stock_unit->id;
                            }
                            else
                            {
                                $stock_unit_id = null;
                                $has_error = 1;
                            }

                            #row[23] => MINIMUM STOCK
                            #row['primary_category'] => Primary Category
                            $primary_category = ProductCategory::where('title', $row['primary_category'])->where('parent_id', 0)->first();
                            if ($primary_category != null)
                            {
                                $primary_category_id = $primary_category->id;
                            }
                            else
                            {
                                $primary_category_id = $row['primary_category'];
                                $has_error = 1;
                                $status = 0;
                            }

                            #row['subcategory']  => Category id #
                            $category = ProductCategory::where('title', $row['subcategory'])->where('parent_id', $primary_category_id)->first();
                            if ($category != null)
                            {
                                $category_id = $category->id;
                            }
                            else
                            {
                                $category_id = $row['subcategory'];
                                $has_error = 1;
                                $status = 0;
                            }

                            #row['goods_type']  => Good Type
                            $type = ProductType::where('title', $row['goods_type'])->first();
                            if ($type != null)
                            {
                                $type_id = $type->id;
                            }
                            else
                            {
                                $type_id = $row['goods_type'];
                                $has_error = 1;
                                $status = 0;
                            }

                            $type_2 = ProductSecondaryType::where('title', $row['goods_type_2'])->first();
                            if ($type_2 != null)
                            {
                                $type_2_id = $type_2->id;
                            }
                            else
                            {
                                $type_2_id = $row['goods_type_2'];
                                // $has_error = 1;
                                // $status = 0;
                            }

                            $type_3 = ProductTypeTertiary::where('title', $row['goods_type_3'])->first();
                            if ($type_3 != null)
                            {
                                $type_3_id = $type_3->id;
                            }
                            else
                            {
                                $type_3_id = $row['goods_type_3'];
                                // $has_error = 1;
                                // $status = 0;
                            }
                            #row[27]  => Good Temprature C
                            #row[28]  => Size/Note

                            // creating an array of a fixed prices
                            $fixed_prices_array = array();
                            $custCats = CustomerCategory::where('is_deleted',0)->orderBy('id', 'ASC')->get();
                            // $i = 29;
                            foreach ($custCats as $cat) {
                                $cat_name = strtolower($cat->title)."_fixed_prices";
                                array_push($fixed_prices_array, $row[$cat_name]);
                                // $i++;
                            }

                            if($row['system_code'] == NULL)
                            {
                                $our_reference_number = NULL;
                            }
                            else
                            {
                                $our_reference_number = $row['our_reference_number'];
                            }
                            $new_data = new TempProduct([
                                'refrence_code'          => $our_reference_number,
                                'system_code'            => $row['system_code'],
                                'supplier_description'   => $row['supplier_description'],
                                'supplier_packaging'     => $row['ordering_unit'],
                                'billed_unit'            => $row['order_qty_unit'],
                                'm_o_q'                  => $row['m_o_q'],
                                'buying_price'           => $buying_price,
                                'gross_weight'           => $row['gross_weight'],
                                'freight'                => $row['freight'],
                                'landing'                => $row['landing'],
                                'import_tax_actual'      => $row['import_tax_actual'],
                                'extra_cost'             => $row['extra_cost_per_billed_unit'],
                                'extra_tax'              => $row['extra_tax_thb'],
                                'leading_time'           => $row['expected_lead_time_in_days'],
                                'p_s_r'                  => $row['suppliers_product_reference_no'],
                                'brand'                  => $row['brand'],
                                'short_desc'             => $row['product_description'],
                                'weight'                 => $row['avg_units_for_sales'],
                                'min_stock'              => $row['minimum_stock'],
                                'vat'                    => $row['vat'],
                                'primary_category'       => $primary_category_id,
                                'category_id'            => $category_id ,
                                'type_id'                => $type_id,
                                'type_2_id'              => $type_2_id,
                                'type_3_id'              => $type_3_id,
                                'product_temprature_c'   => $row['temprature_c'],
                                'product_notes'          => $row['note_two'],
                                'buying_unit'            => $buying_unit_id,
                                'selling_unit'           => $selling_unit_id,
                                'stock_unit'             => $stock_unit_id,
                                'unit_conversion_rate'   => $unit_conversion_rate,
                                'supplier_id'            => $supplier_id,
                                'hasError'               => $has_error,
                                'status'                 => $status,
                                'created_by'             => $user_id,
                                'fixed_prices_array'     => serialize($fixed_prices_array),
                                'order_qty_per_piece'    => $row['order_qty_per_piece'],
                            ]);

                            $new_data->save();
                            $this->row_count++;
                        }
                    }

                    $increment++;
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
                $error_msgss = "Products Move to Temporary Products Successfully, But Some Of Them Has Issues !!!";
                $success = 'hasError';
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
                $error_msgss = 'Products Move to Temporary Products Successfully !!!';
                $success = 'pass';
                $this->error_msgs = $html_string;
                $this->filterFunction($success);
            }

            ExportStatus::where('type','supplier_bulk_products_import')->update(['status'=>0,'last_downloaded'=>date('Y-m-d'),'exception'=>$html_string, 'error_msgs'=>$error_msgss]);
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
        ExportStatus::where('type','supplier_bulk_products_import')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException            = new FailedJobException();
        $failedJobException->type      = "supplier_bulk_products_import";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }

    protected function generateRandomString($length)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function doProductCompleted($id)
    {
        if($id)
        {
            $product = TempProduct::find($id);
            $missingPrams = array();

            if($product->short_desc == null)
            {
                $missingPrams[] = 'Short Description';
            }

            if($product->primary_category == null)
            {
                $missingPrams[] = 'Primary Category';
            }

            if($product->category_id == 0)
            {
                $missingPrams[] = 'Sub Category';
            }

            if($product->type_id == null)
            {
                $missingPrams[] = 'Product Type';
            }

            if($product->buying_unit == null)
            {
                $missingPrams[] = 'Billed Unit';
            }

            if($product->selling_unit == null)
            {
                $missingPrams[] = 'Selling Unit';
            }

            if($product->unit_conversion_rate == null)
            {
                $missingPrams[] = 'Unit Conversion Rate';
            }

            if($product->supplier_id == null)
            {
                $missingPrams[] = 'Supplier Id';
            }

            if(sizeof($missingPrams) == 0)
            {
                $product->status = 1;
                $product->save();
                $message = "completed";

                return response()->json(['success' => true, 'message' => $message]);
            }
            else
            {
                $message = implode(', ', $missingPrams);
                return response()->json(['success' => false, 'message' => $message]);
            }
        }
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
            $this->response = "Products Imported Successfully !!!";
            $this->result   = "false";
        }
        elseif($success == 'hasError')
        {
            $this->response = "Products Imported Successfully, But Some Of Them Has Issues !!!";
            $this->result   = "withissues";
        }
        elseif($success == 'invalid')
        {
            $this->response = "File you are uploading is not a valid file, please upload a valid file !!!";
            $this->result   = "true";
        }
    }

}
