<?php

namespace App\Imports;

use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrdersHistory;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\ProductHistory;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkProductPurchaseOrder implements ToCollection ,WithStartRow, WithHeadingRow
{
    private $po_id;
    private $supplier_id;
    public $response;
    public $result;
    public $error_msgs;
    public $sub_total;
    public $total;
    public $vat_total;
    /**
    * @param Collection $collection
    */

    public function __construct($po_id,$supplier_id)
    {
        $this->po_id = $po_id;
        $this->supplier_id = $supplier_id;
    }

    public function collection(Collection $rows)
    {
        # these columns we will use for import by (header names)
        #row[pf]           = PF#
        #row[qty_inv]      = QTY INV
        #row[unit_price]   = UNIT PRICE
        #row[discount]     = Discount
        #row[gross_weight] = Gross Weight
        // dd($rows);
        $html_string = '';
        $error = 0;
        
        if($rows[0]->has('create_direct_po')) {
        if($rows->count() > 1)
        {
            $row1 = $rows->toArray();
            // $remove = array_shift($row1);
            $html_string = '<ol>';
            $increment = 1;
            foreach ($row1 as $row) 
            {
                $row['qty_inv'] = abs($row['qty_inv']);
                if ($increment != 1) {
                    // this is the row id from purchase_order_detail table
                    $row_id = intval($row['row_id']);
                    if(array_key_exists("pf", $row))
                    {
                        // For PF# no
                        if($row['pf'] == null)
                        {
                            $error = 1;
                            $html_string .= '<li>PF# is empty on row <b>'.$increment.'</b> </li>';
                            continue;
                        }
                        else
                        {
                            $getProduct = Product::where('refrence_code',$row['pf'])->first();
                            if($getProduct)
                            {
                                $checkSUpplier = SupplierProducts::where('product_id',$getProduct->id)->where('supplier_id',$this->supplier_id)->first();
                                if($checkSUpplier)
                                {
                                    // do nothing
                                }
                                else
                                {
                                    $error = 1;
                                    $html_string .= '<li>Selected Supplier Dosen\'t provide us this product, PF# <b>'.$row['pf'].'</b> on row <b>'.$increment.'</b> </li>';
                                    $increment++;
                                    continue;
                                }
                            }
                            else
                            {
                                $error = 1;
                                $html_string .= '<li>There is no such item exist in the system PF# <b>'.$row['pf'].'</b> on row <b>'.$increment.'</b> </li>';
                                $increment++;
                                continue;
                            }
                        }
                    }
                    else
                    {
                        $error = 3;
                        $html_string .= '<li>PF# column not Found !!! </li>';
                        break;
                    }

                    // For QTY inv
                    // if($row[1] != null)
                    if(array_key_exists("qty_inv", $row))
                    {
                        if($row['qty_inv'] != null)
                        {
                            if(!is_numeric($row['qty_inv']))
                            {
                                $row['qty_inv'] = null;
                            }
                        }
                    }
                    else
                    {
                        $row['qty_inv'] = null;
                    }

                    if(array_key_exists("purchasing_vat", $row))
                    {
                        if($row['purchasing_vat'] != null)
                        {
                            if(!is_numeric($row['purchasing_vat']))
                            {
                                $row['purchasing_vat'] = null;
                            }
                        }
                    }
                    else
                    {
                        $row['purchasing_vat'] = null;
                    }
                    // For Unit Price
                    // if($row[2] != null)
                    if(array_key_exists("unit_price", $row))
                    {
                        if($row['unit_price'] != null)
                        {
                            if(!is_numeric($row['unit_price']))
                            {
                                $row['unit_price'] = null;
                            }
                        }
                    }
                    else
                    {
                        $row['unit_price'] = null;
                    }

                    if(array_key_exists("unit_price_vat", $row))
                    {
                        if($row['unit_price_vat'] != null)
                        {
                            if(!is_numeric($row['unit_price_vat']))
                            {
                                $row['unit_price_vat'] = null;
                            }
                        }
                    }
                    else
                    {
                        $row['unit_price_vat'] = null;
                    }
                    // For Gross weigh
                    // if($row[1] != null)
                    if(array_key_exists("gross_weight", $row))
                    {
                        if($row['gross_weight'] != null)
                        {
                            if(!is_numeric($row['gross_weight']))
                            {
                                $row['gross_weight'] = null;
                            }
                        }
                    }
                    else
                    {
                        $row['gross_weight'] = null;
                    }
                    // For Gross weigh
                    // if($row[1] != null)
                    if(array_key_exists("discount", $row))
                    {
                        if($row['discount'] != null)
                        {
                            if(!is_numeric($row['discount']))
                            {
                                $row['discount'] = null;
                            }
                        }
                    }
                    else
                    {
                        $row['discount'] = null;
                    }

                    $draft_po = DraftPurchaseOrder::find($this->po_id);
                    $old_pod_unit_price_with_vat = $draft_po->pod_unit_price_with_vat;
                    $old_pod_unit_price = $draft_po->pod_unit_price;

                    // this if is for getting exchange rate of supplier
                    if($draft_po->exchange_rate != NULL)
                    {
                        $supplier_conv_rate_thb = $draft_po->exchange_rate;
                    }
                    else
                    {
                        $supplier_conv_rate_thb = $draft_po->getSupplier->getCurrency->conversion_rate;
                    }


                    // New scenarion added for a same item multiple time but different quantities start here
                    if($row_id != NULL && is_numeric($row_id))
                    {
                        $podGetById = DraftPurchaseOrderDetail::find($row_id);
                        
                        if($podGetById)
                        {
                            $old_qty  = $podGetById->quantity;
                            $old_grss = $podGetById->pod_gross_weight;
                            $gettingProdSuppData = SupplierProducts::where('product_id',$getProduct->id)->where('supplier_id',$this->supplier_id)->first();

                            if($gettingProdSuppData)
                            {   
                                $old_price_value = $gettingProdSuppData->buying_price;
                            }

                            // if quantity available
                            if($row['qty_inv'] != null && is_numeric($row['qty_inv']))
                            {
                                if($gettingProdSuppData->billed_unit == null || $gettingProdSuppData->billed_unit == 0)
                                {
                                    $gettingProdSuppData->billed_unit = 1;
                                    $gettingProdSuppData->save();
                                    $podGetById->desired_qty        = ($row['qty_inv'] / $gettingProdSuppData->billed_unit);
                                }
                                else
                                {
                                    $podGetById->desired_qty        = ($row['qty_inv'] / $gettingProdSuppData->billed_unit);
                                }
                            }

                            if($row['purchasing_vat'] != null && is_numeric($row['purchasing_vat']))
                            {
                                $request = new \Illuminate\Http\Request();
                                $request->replace(['rowId' => $row_id, 'draft_po_id' => $podGetById->po_id,'pod_vat_actual' => $row['purchasing_vat'], 'old_value' => $podGetById->pod_vat_actual]);
                                app('App\Http\Controllers\Purchasing\PurchaseOrderController')->SaveDraftPoVatActual($request);
                            }

                            if($row['gross_weight'] !== NULL)
                            {
                                $gettingProdSuppData->gross_weight  = $row['gross_weight'];
                                $gettingProdSuppData->save();
                                $podGetById->pod_gross_weight       = $row['gross_weight'];
                                $podGetById->pod_total_gross_weight = ($row['gross_weight'] * $row['qty_inv']);
                                $col_name = "Gross Weight (Added Through Bulk Import)";
                                $this->makePoHistory(Auth::user()->id,$podGetById->getProduct->refrence_code,$podGetById->order_id,$podGetById->order_product_id,$col_name,$old_grss,$row['gross_weight'],$podGetById->po_id);
                            }
                            else
                            {
                                $podGetById->pod_gross_weight       = $gettingProdSuppData->gross_weight;
                                $podGetById->pod_total_gross_weight = ($gettingProdSuppData->gross_weight * $row['qty_inv']);
                            }

                            if($row['discount'] != NULL && (is_numeric($row['discount'])))
                            {
                                if($row['discount'] > 100)
                                {
                                    $podGetById->discount = 100;
                                }
                                else
                                {
                                    $podGetById->discount   = $row['discount'];
                                }
                            }
                            // this will replace the item price with the updated one on excel
                            if($row['unit_price'] != null && is_numeric($row['unit_price']))
                            {
                                $request = new \Illuminate\Http\Request();
                                $request->replace(['rowId' => $row_id, 'draft_po_id' => $podGetById->po_id,'unit_price' => $row['unit_price'], 'old_value' => $podGetById->pod_unit_price]);
                                app('App\Http\Controllers\Purchasing\PurchaseOrderController')->UpdateDraftPoUnitPrice($request);

                                // $podGetById->pod_unit_price          = $row['unit_price'];
                                // $podGetById->pod_total_unit_price    = ($row['unit_price'] * $row['qty_inv']);
                                // $podGetById->last_updated_price_on = Carbon::now()->format('Y-m-d');
                                // if($podGetById->pod_unit_price != $row['unit_price'])
                                // {
                                //     $podGetById->last_updated_price_on = Carbon::now()->format('Y-m-d');
                                // }
                            }
                            else
                            {
                                $pod_unit_price = $gettingProdSuppData->buying_price;
                                $pod_vat_value  = $podGetById->pod_vat_actual;
                                $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
                                $vat_amount     = number_format($vat_amount,4,'.','');

                                $podGetById->pod_unit_price       = $gettingProdSuppData->buying_price;
                                $podGetById->pod_total_unit_price = ($gettingProdSuppData->buying_price * $row['qty_inv']);

                                //to update vat values
                                $podGetById->pod_unit_price_with_vat       = number_format($gettingProdSuppData->buying_price + $vat_amount,3,'.','');
                                $podGetById->pod_total_unit_price_with_vat = number_format($podGetById->pod_unit_price_with_vat * $podGetById->quantity,3,'.','');
                                $podGetById->pod_vat_actual_price          = $vat_amount;
                                $podGetById->pod_vat_actual_total_price    = number_format($vat_amount * $podGetById->quantity,3,'.','');
                                $podGetById->last_updated_price_on = Carbon::parse($getProduct->last_price_updated_date)->format('Y-m-d');
                            }

                            if($row['unit_price_vat'] != null && is_numeric($row['unit_price_vat']) && $old_pod_unit_price_with_vat != $row['unit_price_vat'])
                            {
                                $request = new \Illuminate\Http\Request();
                                $request->replace(['rowId' => $row_id, 'draft_po_id' => $podGetById->po_id,'pod_unit_price_with_vat' => $row['unit_price_vat'], 'old_value' => $podGetById->pod_unit_price_with_vat]);
                                app('App\Http\Controllers\Purchasing\PurchaseOrderController')->UpdateDraftPoUnitPriceVat($request);
                            }
                            $podGetById->quantity   = $row['qty_inv'];
                            $podGetById->save();
                            $col_name = "QTY Inv (Added Through Bulk Import)";
                            $this->makePoHistory(Auth::user()->id,$podGetById->getProduct->refrence_code,$podGetById->order_id,$podGetById->order_product_id,$col_name,$old_qty,$row['qty_inv'],$podGetById->po_id);
                        }
                        else
                        {
                            $error = 1;
                            $html_string .= '<li>Row ID not found on EXCEL row # <b>'.$increment.'</b> </li>';
                            $increment++;
                            continue;
                        }
                    }
                    else
                    {
                        $old_value  = '';
                        $p_order_id = '';
                        $p_op_id    = '';

                        $new_item                          = new DraftPurchaseOrderDetail;
                        $new_item->pod_import_tax_book     = $getProduct->import_tax_book;
                        if($row['purchasing_vat'] != null && is_numeric($row['purchasing_vat']))
                        {
                            $new_item->pod_vat_actual          = $row['purchasing_vat'];
                        }
                        else
                        {
                            $new_item->pod_vat_actual          = $getProduct->vat;
                        }
                        $new_item->quantity                = $row['qty_inv'];

                        $gettingProdSuppData               = SupplierProducts::where('product_id',$getProduct->id)->where('supplier_id',$this->supplier_id)->first();

                        if($gettingProdSuppData)
                        {   
                            $old_price_value = $gettingProdSuppData->buying_price;
                        }

                        if($row['unit_price'] == null)
                        {
                            $pod_unit_price = $gettingProdSuppData->buying_price;
                            $pod_vat_value  = $new_item->pod_vat_actual;
                            $vat_amount     = $pod_unit_price * ( $pod_vat_value / 100 );
                            $vat_amount     = number_format($vat_amount,4,'.','');

                            $new_item->pod_unit_price       = $gettingProdSuppData->buying_price;
                            $new_item->pod_total_unit_price = ($gettingProdSuppData->buying_price * $row['qty_inv']);

                            //to update vat values
                            $new_item->pod_unit_price_with_vat       = number_format($gettingProdSuppData->buying_price + $vat_amount,3,'.','');
                            $new_item->pod_total_unit_price_with_vat = number_format($new_item->pod_unit_price_with_vat * $row['qty_inv'],3,'.','');
                            $new_item->pod_vat_actual_price          = $vat_amount;
                            $new_item->pod_vat_actual_total_price    = number_format($vat_amount * $row['qty_inv'],3,'.','');

                            $new_item->last_updated_price_on = Carbon::parse(@$getProduct->last_price_updated_date)->format('Y-m-d');
                        }
                        elseif($row['unit_price'] != null && is_numeric($row['unit_price']))
                        {
                            $new_item->pod_unit_price          = $row['unit_price'];
                            $new_item->pod_total_unit_price    = ($row['unit_price'] * $row['qty_inv']);

                            if($old_price_value != $row['unit_price'])
                            {
                                $new_item->last_updated_price_on    = Carbon::now()->format('Y-m-d');
                            }
                            else
                            {
                                // $new_item->last_updated_price_on = Carbon::parse(@$getProduct->last_price_updated_date)->format('Y-m-d');
                                $new_item->last_updated_price_on = Carbon::now()->format('Y-m-d');
                            }
                        }

                        // // if quantity available
                        if($row['qty_inv'] != null && is_numeric($row['qty_inv']))
                        {
                            if($gettingProdSuppData->billed_unit == null || $gettingProdSuppData->billed_unit == 0)
                            {
                                $gettingProdSuppData->billed_unit = 1;
                                $gettingProdSuppData->save();
                                $new_item->desired_qty        = ($row['qty_inv'] / $gettingProdSuppData->billed_unit);
                            }
                            else
                            {
                                $new_item->desired_qty        = ($row['qty_inv'] / $gettingProdSuppData->billed_unit);
                            }
                        }

                        if($row['gross_weight'] !== NULL)
                        {
                            $gettingProdSuppData->gross_weight  = $row['gross_weight'];
                            $gettingProdSuppData->save();
                            $new_item->pod_gross_weight       = $row['gross_weight'];
                            $new_item->pod_total_gross_weight = ($row['gross_weight'] * $row['qty_inv']);
                        }
                        else
                        {
                            $new_item->pod_gross_weight       = $gettingProdSuppData->gross_weight;
                            $new_item->pod_total_gross_weight = ($gettingProdSuppData->gross_weight * $row['qty_inv']);
                        }

                        if($row['discount'] != NULL && (is_numeric($row['discount'])))
                        {
                            if($row['discount'] > 100)
                            {
                                $new_item->discount = 100;
                            }
                            else
                            {
                                $new_item->discount   = $row['discount'];
                            }
                        }
                        $new_item->quantity                = $row['qty_inv'];
                        $new_item->po_id                   = $this->po_id;
                        $new_item->product_id              = $getProduct->id;
                        $new_item->supplier_packaging      = @$gettingProdSuppData->supplier_packaging;
                        $new_item->billed_unit_per_package = @$gettingProdSuppData->billed_unit;
                        $new_item->warehouse_id            = Auth::user()->get_warehouse->id;
                        $new_item->save();

                        if($row['unit_price_vat'] != null && is_numeric($row['unit_price_vat']))
                        {
                            $request = new \Illuminate\Http\Request();
                            $request->replace(['rowId' => $new_item->id, 'draft_po_id' => $new_item->po_id,'pod_unit_price_with_vat' => $row['unit_price_vat'], 'old_value' => $new_item->pod_unit_price_with_vat]);
                            app('App\Http\Controllers\Purchasing\PurchaseOrderController')->UpdateDraftPoUnitPriceVat($request);
                        }

                        // $request = new \Illuminate\Http\Request();
                        // $request->replace(['rowId' => $new_item->id, 'draft_po_id' => $new_item->po_id,'unit_price' => $new_item->pod_unit_price, 'old_value' => '--']);
                        // app('App\Http\Controllers\Purchasing\PurchaseOrderController')->UpdateDraftPoUnitPrice($request);

                        $col_name = 'Added Through Bulk Import';
                        $old      = '';
                        $new      = 'New Item';
                        $this->makePoHistory(Auth::user()->id,$getProduct->refrence_code,$p_order_id,$p_op_id,$col_name,$old,$new,$this->po_id);
                    }
                }
                // New scenarion added for a same item multiple time but different quantities ends here
                $increment++;
            }
            $html_string .= '</ol>';

            // getting sub total
            $total_gross_weight = 0;
            $sub_total = 0 ;
            $total_item_product_quantities = 0;
            $total_import_tax_book = 0;
            $total_import_tax_book_price = 0;

            $total_vat_actual = 0;
            $total_vat_actual_price = 0;
            // $query     = DraftPurchaseOrderDetail::where('po_id',$this->po_id)->get();
            // foreach ($query as  $value)
            // {
            //     $unit_price = $value->pod_unit_price;
            //     $sub = $value->quantity * $unit_price - (($value->quantity * $unit_price) * ($value->discount / 100));
            //     $value->pod_total_unit_price = $sub;
            //     $value->save();
            //     $sub_total += $sub;

            //     $total_gross_weight = ($value->quantity * $value->pod_gross_weight) + $total_gross_weight;

            //     $total_item_product_quantities = $total_item_product_quantities + $value->quantity;

            //     $total_import_tax_book = $total_import_tax_book + $value->pod_import_tax_book;

            //     $total_import_tax_book_price = $total_import_tax_book_price + $value->pod_import_tax_book_price;

            //     $total_vat_actual = $total_vat_actual + $value->pod_vat_actual;

            //     $total_vat_actual_price = $total_vat_actual_price + $value->pod_vat_actual_price;
            // }

            $po_totoal_change = DraftPurchaseOrder::find($this->po_id);
            // $po_totoal_change->total_gross_weight = $total_gross_weight;
            // $po_totoal_change->total_quantity = $total_item_product_quantities;
            // $po_totoal_change->total_import_tax_book = $total_import_tax_book;
            // $po_totoal_change->total_import_tax_book_price = $total_import_tax_book_price;

            // $po_totoal_change->total_vat_actual = $total_vat_actual;
            // $po_totoal_change->total_vat_actual_price = $total_vat_actual_price;
            // $po_totoal_change->total = number_format($sub_total, 3, '.', '');
            // $po_totoal_change->save();

            /*calulation through a function*/
            $objectCreated = new DraftPurchaseOrderDetail;
            $grandCalculations = $objectCreated->grandCalculationForDraftPoTD($po_totoal_change->id);

            $this->sub_total = $grandCalculations['sub_total'];
            $this->total = $grandCalculations['total_w_v'];
            $this->vat_total = $grandCalculations['vat_amout'];
        }
        else
        {
            $success = 'fail';
            $error = 2;
            $html_string .= 'Please Dont Upload Empty File.'; 
            $this->error_msgs = $html_string;
            $this->filterFunction($success);
        }
    } else {
        $success = 'invalid';
            $error = 4;
            $html_string .= 'Please Upload Valid File.'; 
            $this->error_msgs = $html_string;
            $this->filterFunction($success);
    }

        if($error == 1)
        {
            $success = 'hasError';
            $this->error_msgs = $html_string;
            $this->filterFunction($success);
        }
        elseif($error == 2)
        {
            $success = 'fail';
            $this->error_msgs = $html_string;
            $this->filterFunction($success);
        }
        elseif($error == 3)
        {
            $success = 'redirect';
            $this->error_msgs = $html_string;
            $this->filterFunction($success);
        } elseif($error == 4) {
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
            $success = 'pass';
            $this->error_msgs = $html_string;
            $this->filterFunction($success);
        }
    }

    public function startRow():int
    {
        return 1;
    }

    public function filterFunction($success = null)
    {
        if($success == 'fail')
        {
            $this->response = "File is Empty Please Upload Valid File !!!";
            $this->result   = "true";
        } elseif($success == 'invalid') {
            $this->response = "Please Upload a Valid File";
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
        elseif($success == 'redirect')
        {
            $this->response = "Import File Dosen\"t have PF# column, please Import valid file !!!";
            $this->result   = "true";
        }
    }

    public function makePoHistory($auth,$ref_no,$order_id,$op_id,$col_name,$old_val,$new_val,$po_id)
    {
        $order_history                   = new PurchaseOrdersHistory;
        $order_history->user_id          = $auth;
        $order_history->reference_number = $ref_no;
        $order_history->order_id         = $order_id;
        $order_history->order_product_id = $op_id;
        $order_history->type             = "DRAFT";
        $order_history->column_name      = $col_name;
        $order_history->old_value        = $old_val;
        $order_history->new_value        = $new_val;
        $order_history->po_id            = $po_id;
        $order_history->save();
    }

}
