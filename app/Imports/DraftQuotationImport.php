<?php

namespace App\Imports;
use App\Models\Common\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Common\Order\DraftQuotationProduct;
use App\Models\Common\Order\DraftQuotation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\ToCollection;

class DraftQuotationImport implements ToCollection,WithHeadingRow,WithStartRow
{
    protected $order_id;
    protected $customer_id;
    protected $errors;
    protected $count = 0;
    public function __construct($order_id,$customer_id)
    {
        $this->order_id=$order_id;
        $this->customer_id=$customer_id;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        
        $this->count++;
        $row = $rows->toArray();
        if ($rows->count() > 1) { 
            $remove = array_shift($row);
	        $i = 2;
            if(array_key_exists('quotation_file', $row[0])) {
            foreach($row as $_row){
                $draft_quotation_id=(array_key_exists('draft_quotation_id', $_row)) ? $_row['draft_quotation_id'] : null;
                $pf = (array_key_exists('pf', $_row)) ? $_row['pf'] : $_row['reference_no'];
                if ($draft_quotation_id == null || $draft_quotation_id == '') {
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['refrence_number' => $pf, 'id' => ['id' => $this->order_id],'from_bulk' => true]);
                    $result = app('App\Http\Controllers\Sales\OrderController')->addByRefrenceNumber($request);
        
                    
                    if ($result->original['success']) {
                        $draft_quotation_id = $result->original['getColumns']['id'];
                    }
                    else{
                        $this->errors .= $result->original['successmsg'];
                    }
                }
                if ($this->errors == null) {
                    $QuantityOrder = (array_key_exists('qtyordered', $_row)) ? $_row['qtyordered'] : $_row['quantity_ordered'];
                    $QuantityOrder = (int) filter_var($QuantityOrder, FILTER_SANITIZE_NUMBER_INT);

                    $number_of_pieces = (array_key_exists('piecesordered', $_row)) ? $_row['piecesordered'] : $_row['pieces_ordered'];
                    $number_of_pieces = (int) filter_var($number_of_pieces, FILTER_SANITIZE_NUMBER_INT);
                  
                    if($pf == 'N.A' || $pf == '') {
                        $draft_quotation_item = DraftQuotationProduct::where('id',$draft_quotation_id)->first(['quantity','discount','unit_price','number_of_pieces','unit_price_with_vat','vat']);
                    }
                    else{
                        $product_id=Product::where('refrence_code',$pf)->pluck('id')->toArray();
                        $product_id=$product_id[0];
                        $draft_quotation_item = DraftQuotationProduct::where(['id'=>$draft_quotation_id,'product_id'=>$product_id])->first(['quantity','discount','unit_price','number_of_pieces','unit_price_with_vat','vat']);
                    }
                    

                    $old_quantity=$draft_quotation_item['quantity'];
                    $old_vat=$draft_quotation_item['vat'];
                    $old_unit_price_with_vat=$draft_quotation_item['unit_price_with_vat'];
                    $old_number_of_pieces=$draft_quotation_item['number_of_pieces'];
                    $old_price_without_vat=$draft_quotation_item['unit_price'];
                    $old_discount=$draft_quotation_item['discount'];

                    if($old_quantity!=$QuantityOrder){
                        $request = new \Illuminate\Http\Request();
                            $request->replace(['draft_quotation_id' => $draft_quotation_id, 'quantity' => $QuantityOrder, 'old_value' => $old_quantity]);
                            app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }




                    if((is_numeric($_row['vat'])) && $old_vat!=$_row['vat']){
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['draft_quotation_id' => $draft_quotation_id, 'vat' => $_row['vat'], 'old_value' => $old_vat]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }


                    $unit_price_with_vat = (array_key_exists('unit_pricevat', $_row)) ? $_row['unit_pricevat'] : $_row['unit_price_vat'];
                    if((is_numeric($unit_price_with_vat))&&($old_unit_price_with_vat != $unit_price_with_vat)){
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['draft_quotation_id' => $draft_quotation_id, 'unit_price_with_vat' => $unit_price_with_vat, 'old_value' => $old_unit_price_with_vat]);

                    app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }

                    $piecesordered = (array_key_exists('piecesordered', $_row)) ? $_row['piecesordered'] : $_row['pieces_ordered'];
                    if(is_numeric($old_number_of_pieces))
                    {

                    if($number_of_pieces!=$old_number_of_pieces){
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['draft_quotation_id' => $draft_quotation_id, 'number_of_pieces' => $piecesordered, 'old_value' => $old_number_of_pieces]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
                    }

                    $default_price_wo_vat = (array_key_exists('default_price_wo_vat', $_row)) ? $_row['default_price_wo_vat'] : $_row['unit_price'];
                    if(is_numeric($old_price_without_vat)&&($old_price_without_vat!=$default_price_wo_vat)){
                    $request = new \Illuminate\Http\Request();
                    $request->replace(['draft_quotation_id' => $draft_quotation_id, 'unit_price' => $default_price_wo_vat, 'old_value' => $old_price_without_vat]);
                    app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }

                    if(is_numeric($_row['discount'])&&($old_discount!=$_row['discount'])){
                        $request = new \Illuminate\Http\Request();
                        $request->replace(['draft_quotation_id' => $draft_quotation_id, 'discount' => $_row['discount'], 'old_value' => $old_discount]);
                        app('App\Http\Controllers\Sales\OrderController')->UpdateQuotationData($request);
                    }
                } 
        }  
        } else {
            throw new \ErrorException('Please Upload Valid File');
        }
    } else {
        throw new \ErrorException('Please Dont Upload Empty File');
    }
    }

    public function getErrors()
    {
        return response()->json(['msg' => $this->errors]);
    }

     public function getRowCount():int
    {
        return $this->count;
    }
    public function startRow():int
    {
        return 2;
    }

}


