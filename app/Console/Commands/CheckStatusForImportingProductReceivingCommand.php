<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\StatusCheckForCompleteProductsExport;
use App\FiltersForImportingReceivingProducts;
use App\ProductsReceivingRecordImporting;
use App\Models\Common\PoGroupProductDetail;
use App\Exports\ImportingProductReceivingRecord;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\SupplierProducts;
use App\Models\Common\Supplier;
use App\Models\Common\Warehouse;
use App\Models\Common\Product;
use DB;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;


class CheckStatusForImportingProductReceivingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:CheckStatusForImportingProductReceivingCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
      parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

      $status=StatusCheckForCompleteProductsExport::where('id',2)->first();
      if($status->status==1)
      {
      $this->info('*****************************************');
      $this->info('CSV Scheduler Started at '.date('Y-m-d H:i:s'));
      $this->info('******************************************');

        $request=FiltersForImportingReceivingProducts::first();
        $query = PoGroupProductDetail::where('status',1)->where('po_group_id',$request->po_group_id);
        $query = $query->get();
        $current_date = date("Y-m-d");
        $data=[]; 
        
        DB::table('products_receiving_record_importings')->truncate();
        $data=[];
        foreach($query as $item)
        {
          $occurrence = $item->occurrence;

          if($occurrence == 1)
          {
            $purchase_orders_ids =  PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
            $pod = PurchaseOrderDetail::select('po_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
                
            if($pod[0]->PurchaseOrder->ref_id !== null){
              $po_number = $pod[0]->PurchaseOrder->ref_id;
            }
            else{
              $po_number = "--";
            }
          }
          else
          {
            $po_number = '--';
          }

          if($occurrence == 1)
          {
            $purchase_orders_ids =  PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
            $pod = PurchaseOrderDetail::select('po_id','order_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
            $order = Order::find($pod[0]->order_id);
            $order_warehouse = $order !== null ? $order->user->get_warehouse->warehouse_title : "N.A" ;
          }
          else
          {
            $order_warehouse = '--';
          }

          if($occurrence == 1)
          {
            $purchase_orders_ids =  PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
            $pod = PurchaseOrderDetail::select('po_id','order_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
            $order = Order::find($pod[0]->order_id);
            if($order != null)
            {
              $order_no = null;
              if($order->primary_status == 3){
                $order_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
              } 
              elseif($order->primary_status == 2){
                $order_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              } 
              elseif($order->primary_status == 17){
                $order_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              }  
              else{
                $order_no = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              }
            }
            else
            {
              $order_no = "N.A";
            }
          }
          else
          {
            $order_no = '--';
          }


          if($item->supplier_id !== NULL)
          {
            $sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
            $reference_number = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
          }
          else
          {
            $reference_number = "N.A";
          } 

          if($item->supplier_id !== NULL)
          {                
            $sup_name = Supplier::where('id',$item->supplier_id)->first();
            $supplier = $sup_name->reference_name;
          }
          else
          {
            $sup_name = Warehouse::where('id',$item->from_warehouse_id)->first();
            $supplier = $sup_name->warehouse_title;
            // return $sup_name->company != null ? $sup_name->company :"--" ;
          }

          $product = Product::where('id',$item->product_id)->first();
          $prod_reference_number = $product->refrence_code;

          $brand = $product->brand != null ? $product->brand : '--' ;
          
          $desc = $product->short_desc != null ? $product->short_desc : '--' ;

          $type = $product->productType != null ? $product->productType->title : '--' ;
          
          $unit =  $product->units->title != null ? $product->units->title : '--';                          

          $qty_ordered = number_format($item->quantity_ordered,2,'.','');
          
          $qty = number_format($item->quantity_inv,2,'.','');

          $total_gross_weight = $item->total_gross_weight != null ? $item->total_gross_weight : 0 ;
          $pod_total_gross_weight = number_format($total_gross_weight,2,'.','');      

          $total_extra_cost = $item->total_extra_cost != null ? $item->total_extra_cost : 0 ;
          $pod_total_extra_cost = number_format($total_extra_cost,2,'.',''); 

          $total_extra_tax = $item->total_extra_tax != null ? $item->total_extra_tax : 0 ;
          $pod_total_extra_tax = number_format($total_extra_tax,2,'.','');

          $currency_code = $item->get_supplier->getCurrency->currency_code;
          $buying_price = $item->unit_price != null ?number_format($item->unit_price,2,'.','').' '.$currency_code: '' ;

          $currency_code = $item->get_supplier->getCurrency->currency_code;
          $total_buying_price = $item->unit_price != null ?number_format($item->unit_price*$item->quantity_inv,2,'.','').' '.$currency_code: '' ;
                       
          $currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0 ;

          $buying_price_in_thb = $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb,2,'.',''): '' ;
          
          $total_buying_price_in_thb = $item->unit_price_in_thb != null ? number_format(($item->unit_price_in_thb*$item->quantity_inv),2,'.',''): '' ;
          
          $import_tax_book = number_format($item->import_tax_book,2,'.','');

          $freight = $item->freight;
          $freight = number_format($freight,2,'.','');

          $landing = $item->landing;
          $landing = number_format($landing,2,'.','');

          $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
          $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;

          $import_tax = $item->import_tax_book;
          $total_price = $item->total_unit_price_in_thb;
          $book_tax = (($import_tax/100)*$total_price);
          $check_book_tax = (($po_group_import_tax_book*$total_buying_price_in_thb)/100);
          if($check_book_tax != 0)
          {                    
            $book_tax = number_format($book_tax,2,'.','');
          }
          else
          {
            $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$item->po_group_id)->count();
            $book_tax = (1/$count)* $item->total_unit_price_in_thb;
            $book_tax = number_format($book_tax,2,'.','');
          }

          $total_import_tax = $item->po_group->po_group_import_tax_book;
          $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
          $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;
          $import_tax = $item->import_tax_book;
          $total_price = $item->total_unit_price_in_thb;
          $book_tax = (($import_tax/100)*$total_price);
          $check_book_tax = (($po_group_import_tax_book*$total_buying_price_in_thb)/100);
          if($check_book_tax != 0)
          {                    
            $book_tax = round($book_tax,2);
          }
          else
          {
            $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$item->po_group_id)->count();
            $book_tax = (1/$count)* $item->total_unit_price_in_thb;
            $book_tax = round($book_tax,2);
          }
          $weighted = (($book_tax/$total_import_tax)*100);
          $weighted = number_format($weighted,2,'.','').'%';
              

          $total_import_tax = $item->po_group->po_group_import_tax_book;
          $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
          $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;
          $import_tax = $item->import_tax_book;
          $total_price = $item->total_unit_price_in_thb;
          $book_tax = (($import_tax/100)*$total_price);
          

          $check_book_tax = (($po_group_import_tax_book*$total_buying_price_in_thb)/100);


          if($check_book_tax != 0)
          {                    
            $book_tax = round($book_tax,2);
          }
          else
          {
            // $count = PurchaseOrderDetail::whereIn('po_id',PoGroupDetail::where('po_group_id',$item->po_group_id)->pluck('purchase_order_id'))->count();
            $count = PoGroupProductDetail::where('status',1)->where('po_group_id',$item->po_group_id)->count();
            $book_tax = (1/$count)* $item->total_unit_price_in_thb;
            $book_tax = round($book_tax,2);
          }

          $weighted_e = ($book_tax/$total_import_tax);
          $tax = $item->po_group->tax;
          $actual_tax =  number_format(($weighted_e*$tax),2,'.','');
              
          
          $actual_tax_percent = $item->actual_tax_percent;
          $actual_tax_percent = number_format($actual_tax_percent,2,'.','').'%';

          
          if($occurrence == 1)
          {
            $purchase_orders_ids =  PurchaseOrder::where('po_group_id',$item->po_group_id)->pluck('id')->toArray();
            $pod = PurchaseOrderDetail::select('po_id','order_id')->whereIn('po_id',$purchase_orders_ids)->where('product_id',$item->product_id)->get();
            $order = Order::find($pod[0]->order_id);
            if($order !== null){
              $customer = $order->customer->reference_name;  
            }else{
              $customer = "N.A";
            }
          }
          else
          {
            $customer = '--';
          }
           
          $data[] = [ 
          'po_no'                      => $po_number,
          'order_warehouse'            => $order_warehouse,
          'order_no'                   => $order_no,
          'sup_ref_no'                 => $reference_number,
          'supplier'                   => $supplier,
          'pf_no'                      => $prod_reference_number,
          'brand'                      => $brand,
          'description'                => $desc,
          'type'                       => $type,
          'customer'                   => $customer,
          'buying_unit'                => $unit,
          'qty_ordered'                => $qty_ordered,
          'qty_inv'                    => $qty,
          'total_gross_weight'         => $pod_total_gross_weight,
          'total_extra_cost_thb'       => $pod_total_extra_cost,
          'total_extra_tax_thb'        => $pod_total_extra_tax,
          'purchasing_price_eur'       => $buying_price,
          'total_purchasing_price'     => $total_buying_price,
          'currency_conversion_rate'   => $currency_conversion_rate,
          'purchasing_price_thb'       => $buying_price_in_thb,
          'total_purchasing_price_thb' => $total_buying_price_in_thb,
          'import_tax_book_percent'    => $import_tax_book,
          'freight_thb'                => $freight,
          'landing_thb'                => $landing,
          'book_percent_tax'           => $book_tax,
          'weighted_percent'           => $weighted,
          'actual_tax'                 => $actual_tax,
          'actual_tax_percent'         => $actual_tax_percent,
          'sub_row'                    => 0,
          
        
          ];


          if ($item->occurrence > 1) 
          {
            $all_ids = PurchaseOrder::where('po_group_id',$item->po_group_id)->where('supplier_id',$item->supplier_id)->pluck('id'); 

            $all_record = PurchaseOrderDetail::whereIn('po_id',$all_ids)->where('product_id',$item->product_id)->get();
            foreach ($all_record as $item) {
            //return $item->PurchaseOrder->ref_id !== null ? $item->PurchaseOrder->ref_id : "--" ;
            if($item->PurchaseOrder->ref_id !== null){
              $po_nos = $item->PurchaseOrder->ref_id;
            }else{
              $po_nos = "--";
            }
        
            $order = Order::find(@$item->order_id);
            $order_warehouses = $order !== null ? $order->user->get_warehouse->warehouse_title : "--" ;
                   
            $order = Order::find(@$item->order_id);
            //return $order !== null ? $order->ref_id : "--" ;
            if($order !== null){
              $order_nos = null;
              if($order->primary_status == 3){
                $order_nos = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
              } 
              elseif($order->primary_status == 2){
                $order_nos = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              } 
              elseif($order->primary_status == 17){
                $order_nos = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              }  
              else{
                $order_nos = @$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
              } 
            }
            else{
              $order_nos = "N.A";
            }
        

            if($item->PurchaseOrder->supplier_id !== NULL)
            { 
              $sup_name = Supplier::select('id','reference_name')->where('id',$item->PurchaseOrder->supplier_id)->first();
              $supplier_ref_names = $sup_name->reference_name;
            }
            else
            {
              $sup_name = Warehouse::where('id',$item->PurchaseOrder->from_warehouse_id)->first();
              $supplier_ref_names = $sup_name->warehouse_title;
            }
        

            if($item->PurchaseOrder->supplier_id !== NULL)
            {
              $sup_name = SupplierProducts::select('product_supplier_reference_no')->where('supplier_id',$item->PurchaseOrder->supplier_id)->where('product_id',$item->product_id)->first();
              $supplier_ref_nos = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
            }
            else
            {
              $supplier_ref_nos = "N.A";
            }

            $product_ref_nos = $item->product->refrence_code;
            $brand = $item->product->brand != null ? $item->product->brand : '--' ;
            $short_descs = $item->product->short_desc != null ? $item->product->short_desc : '--' ;
            $type = $item->product->productType != null ? $item->product->productType->title : '--' ;
            $buying_units = $item->product->units->title != null ? $item->product->units->title : '--';
        
            if($item->order_product_id != null)
            {
              $sup_name = OrderProduct::select('quantity')->where('id',$item->order_product_id)->first();
              $quantity_ordereds = $sup_name->quantity;
            }
            else
            {
              $quantity_ordereds = '--';
            }
        

            $quantity_invs = $item->quantity;
            $pod_total_gross_weights = $item->pod_total_gross_weight != null ? number_format($item->pod_total_gross_weight,3,'.',''): '' ;

            $total_extra_costs =  "--" ;

            $total_extra_tax  =  "--" ;

            $buying_prices = $item->pod_unit_price != null ? number_format($item->pod_unit_price,3,'.',''): '--' ;

            $total_buying_price_os = $item->pod_total_unit_price != null ? number_format($item->pod_total_unit_price,3,'.','').' EUR': '--' ;
        
            $currency_conversion_rates = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0 ;
        
            $unit_price_in_thbs = $item->unit_price_in_thb != null ?number_format($item->unit_price_in_thb,3,'.',''): '--' ;

            $total_buying_prices = $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb,3,'.',''): '--' ;

            $import_tax_books =  number_format($item->pod_import_tax_book,2,'.','');

            $freights = number_format($item->pod_freight,2,'.','');
           
            $landings = number_format($item->pod_landing,2,'.','');
           
            $book_taxs = number_format($item->pod_import_tax_book_price,2,'.','');
           
            $weighteds =   "--" ;
           
            $actual_taxs =   "--" ;
        
            $actual_tax_percents =  number_format($item->pod_actual_tax_percent,2,'.','').'%';
            
            $order = Order::find($item->order_id);
            if($order !== null){
            $customer = $order->customer->reference_name; 
            }else{
              $customer = "N.A";
            }

            $data[] = [ 
              'po_no'                      => $po_nos,
              'order_warehouse'            => $order_warehouses,
              'order_no'                   => $order_nos,
              'sup_ref_no'                 => $supplier_ref_nos,
              'supplier'                   => $supplier_ref_names,
              'pf_no'                      => $product_ref_nos,
              'brand'                      => $brand,
              'description'                => $short_descs,
              'type'                       => $type,
              'customer'                   => $customer,
              'buying_unit'                => $buying_units,
              'qty_ordered'                => $quantity_ordereds,
              'qty_inv'                    => $quantity_invs,
              'total_gross_weight'         => $pod_total_gross_weights,
              'total_extra_cost_thb'       => $total_extra_costs,
              'total_extra_tax_thb'        => $total_extra_tax,
              'purchasing_price_eur'       => $buying_price,
              'total_purchasing_price'     => $total_buying_price_os,
              'currency_conversion_rate'   => $currency_conversion_rates,
              'purchasing_price_thb'       => $unit_price_in_thbs,
              'total_purchasing_price_thb' => $total_buying_prices,
              'import_tax_book_percent'    => $import_tax_books,
              'freight_thb'                => $freights,
              'landing_thb'                => $landings,
              'book_percent_tax'           => $book_taxs,
              'weighted_percent'           => $weighteds,
              'actual_tax'                 => $actual_taxs,
              'actual_tax_percent'         => $actual_tax_percents,
              'sub_row'                    => 1,
              
            
              ];
                # code...
              }
          }
        }

        foreach (array_chunk($data,1500) as $t)  
        {
          DB::table('products_receiving_record_importings')->insert($t); 
        }        
        //return reponse()->json(['msg'=>'Table Done'])      
        $records = ProductsReceivingRecordImporting::get();
        $return = \Excel::store(new ImportingProductReceivingRecord($records), 'Importing-Product-Receiving-'.$request->po_group_id.'.xlsx','csv2');
        if($return)
        {
          StatusCheckForCompleteProductsExport::where('id',2)->update(['status'=>0,'last_downloaded'=>date('Y-m-d')]);
          $this->info('***********************************************');
          $this->info('***********************************************');
          $this->info('CSV Exporting Ended at '.date('Y-m-d H:i:s'));
          $this->info('***********************************************');
          $this->info('***********************************************');
          return response()->json(['msg'=>'File Saved']);
        } 
  
      }
    }
}
