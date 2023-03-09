<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Common\Company;
use App\Models\Common\Configuration;
use App\Models\Common\Country;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\UserDetail;
use App\Models\Sales\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use PDF;
use Yajra\Datatables\Datatables;


class DraftInvoicesController extends Controller
{
    public function index()
    {  
        $customers = Customer::where('status',1)->get();
    	return view('sales.home.draft_invoice_dashboard',compact('customers'));
    }

    public function pendingDraftInvoices()
    {
        return view('users.purchasing.pending-draft-invoices');
    }

    public function getPendingDraftInvoicesData()
    {
      $query = Order::with('customer')->where('status', 1)->where('primary_status', 1)->orderBy('id', 'ASC')->get();

        return Datatables::of($query)

        ->addColumn('customer', function ($item) { 
            if($item->customer_id != null){
                if($item->customer['company'] != null)
                {
                    $html_string = $item->customer['company'];
                }
                else
                {
                    $html_string = $item->customer['first_name'].' '.$item->customer['last_name'];
                }
            }
            else
            {
                $html_string = 'N.A';
            }
            return $html_string; 

        })

        ->addColumn('number_of_products', function($item) {

            $html_string = $item->order_products->count();
            return $html_string;  
        
        })
        ->addColumn('ref_id', function($item) { 
    
            return ($item->user_ref_id !== null ? $item->user_ref_id : $item->ref_id);

        })
        ->addColumn('payment_term', function($item) { 
    
            return ($item->customer->credit_term);

        })
        ->addColumn('invoice_date', function($item) { 
    
            return Carbon::parse(@$item->updated_at)->format('d/m/Y');


        })
        ->addColumn('total_amount', function($item) { 
    
            return ($item->total_amount);

        })
        ->addColumn('action', function ($item) { 
        $html_string = '<a href="'.route('get-draft-invoices-pending-product-details', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon"><i class="fa fa-eye"></i></a>'; 
       
        return $html_string;         
        })
         ->rawColumns(['action', 'customer', 'number_of_products'])
         ->make(true);

    }

    public function getDraftInvoicesData()
    {
      // $query = Order::with('customer')->where('primary_status', 2)->orderBy('id', 'ASC')->get();
        $query = Order::with('customer')->whereHas('order_products', function($q){
            $q->where('order_products.product_id','!=',null);
        })->where('primary_status',2)->orderBy('id', 'DESC')->get();

        return Datatables::of($query)

        ->addColumn('customer', function ($item) { 
            if($item->customer_id != null){
                if($item->customer['reference_name'] != null)
                {
                    $html_string = $item->customer['reference_name'];
                }
                else
                {
                    $html_string = $item->customer['first_name'].' '.$item->customer['last_name'];
                }
            }
            else
            {
                $html_string = 'N.A';
            }           
            return $html_string;         
        })
        ->addColumn('customer_ref_no',function($item){
            return $item->customer->reference_number;
        })
        ->addColumn('target_ship_date',function($item){
            return Carbon::parse(@$item->target_ship_date)->format('d/m/Y');
        })
        ->addColumn('status',function($item){
            $html = '<span class="sentverification">'.$item->statuses->title.'</span>';
            return $html;
        })
        ->addColumn('number_of_products', function($item) {

            $html_string = $item->order_products->count();
            return $html_string;  
        
        })
        ->addColumn('ref_id', function($item) { 
            return ($item->user_ref_id !== null ? $item->user_ref_id : $item->ref_id);
        })
        ->addColumn('payment_term', function($item) { 
            return ($item->customer->getpayment_term->title);
        })
        ->addColumn('invoice_date', function($item) { 
            return Carbon::parse(@$item->updated_at)->format('d/m/Y');
        })
        ->addColumn('total_amount', function($item) { 
            return '$'.number_format($item->total_amount,2,'.',',');
        })
        ->addColumn('action', function ($item) { 
        $html_string = '<a href="'.route('get-completed-draft-invoices', ['id' => $item->id]).'" title="View Detail" class="actionicon viewIcon"><i class="fa fa-eye"></i></a>'; 
        // $html_string = '<a href="'.route('get-draft-invoices-product-details', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon"><i class="fa fa-eye"></i></a>'; 
       
        return $html_string;         
        })
        ->rawColumns(['action', 'customer', 'number_of_products','status'])
        ->make(true);
    }

    public function getDraftInvoicesProductsDetails($id)
    {
        $order_invoice = Order::find($id);
        if($order_invoice->billing_address_id != null)
        {
          $billing_address = CustomerBillingDetail::where('id',$order_invoice->billing_address_id)->first();
        }
        $company_info = Company::where('id',$order_invoice->user->company_id)->first();
        $total_products = $order_invoice->order_products->count('id'); 
        $sub_total =0 ;
        $query = OrderProduct::where('order_id',$id)->get();
        foreach ($query as  $value) {
            if($value->product_id != null)
            {
                $product = Product::where('id',$value->product_id)->first();
                $sub_total += $value->quantity * $product->selling_price;
            }
            
        }
          return view('users.purchasing.completed-quotation-products-details', compact('order_invoice','total_products','sub_total','id','company_info','billing_address'));
    }

    public function exportDraftToPDF(Request $request, $id)
    {

        $order = Order::with('customer')->where('status', 7)->where('primary_status', 2)->where('id',$id)->first();

        $getOrderDetail = OrderProduct::with('product','get_order','product.def_or_last_supplier','product.units','product.supplier_products')->where('order_id',$id)->get();

        $pdf = PDF::loadView('users.purchasing.invoice',compact('order','getOrderDetail'));

        // making pdf name starts
        $makePdfName='Draft Invoice-'.$id.'';
        // $makePdfName='PO '.($mainObj->user_ref_id != null ? $mainObj->user_ref_id  : $mainObj->ref_id);
        // making pdf name ends
        
        return $pdf->download($makePdfName.'.pdf');

    }

    public function getPendingDraftInvoicesProductsDetails($id)
      {
          $order_invoice = Order::find($id);
          $total_products = $order_invoice->order_products->count('id'); 
          $sub_total =0 ;
          $query = OrderProduct::where('order_id',$id)->get();
          foreach ($query as  $value) {
              $product = Product::where('id',$value->product_id)->first();
              $sub_total += $value->quantity * $product->selling_price;
          }
          return view('users.purchasing.completed-quotation-products-details-2', compact('order_invoice','total_products','sub_total'));
      }

    public function getProductsData($id)
    {
        $query = OrderProduct::with('product','get_order','product.def_or_last_supplier','product.units','product.supplier_products')->where('order_id', $id)->orderBy('id', 'ASC')->get();
         return Datatables::of($query)
                        
           
            ->addColumn('refrence_code',function($item){
                if($item->product_id != null)
                {
                    return $item->product->refrence_code;
                }
                else
                {
                    return "N.A";
                }
                
            })
            ->addColumn('po_no',function($item){
                $po_no = PurchaseOrderDetail::where('order_product_id',@$item->id)->pluck('po_id')->first();
                if($po_no)
                {
                    $html_string = '<a href="'.route('get-purchase-order-detail', ['id' => $po_no]).'" title="View Products">'."PO-".$po_no.'</a>'; 
       
                    return $html_string;
                }
                else
                {
                    return "N.A";
                }
                
            })
            ->addColumn('hs_code',function($item){
                if($item->product_id != null)
                {
                    return $item->product->hs_code;
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('name',function($item){
                if($item->product_id != null)
                {
                    return $item->product->name;
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('category_id',function($item){
                if($item->product_id != null)
                {
                    return ($item->product->productSubCategory->title !== null ? $item->product->productSubCategory->title : "N.A");
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('buying_unit',function($item){
                // return 4;
                return ($item->product && $item->product->units !== null ? $item->product->units->title : "N.A");
                // return ($item->product->units !== null ? $item->product->units->title : "N.A");             
            })
            ->addColumn('supplier',function($item){
                if($item->product_id != null)
                {
                    return ($item->product->def_or_last_supplier !== null ? $item->product->def_or_last_supplier->first_name.' '.$item->product->def_or_last_supplier->last_name : "N.A");     
                }
                else
                {
                    return "N.A";
                }
            })
            ->addColumn('quantity',function($item){
                 return ($item->quantity !== null ? $item->quantity : "N.A");
            })
             ->addColumn('unit_price',function($item){
                if($item->product_id != null)
                {
                    $html_string ='<span class="unit-price-'.$item->id.'"">'.number_format($item->product->selling_price,2,'.',',').'</span>';
                    return $html_string;
                }
                else
                {
                    return $item->unit_price;
                }
                
            })
            ->addColumn('total_price',function($item){
                if($item->product_id != null)
                {
                    $total_price = $item->product->selling_price * $item->quantity;
                    $html_string ='<span class="total-price total-price-'.$item->id.'"">'.number_format($total_price,2,'.',',').'</span>';
                    return $html_string;
                }
                else
                {
                    return $item->unit_price * $item->quantity;
                }
            })
             ->setRowId(function ($item) {
                    return $item->id;
                })
             // yellowRow is a custom style in style.css file
             ->setRowClass(function ($item) {
                    if($item->product == null){
                    return  'yellowRow';
                  }
                    // return $item->product->status != 0 ? 'alert-success' : 'yellowRow';
                })
            ->rawColumns(['action','quantity','unit_price','total_price','po_no'])
            ->make(true);
    
    }

    public function getPendingInvoiceProductsData($id)
    {
        // dd($id);

        // dd("helllo");
        $query = OrderProduct::with('product','get_order','product.productType','product.def_or_last_supplier','product.units','product.supplier_products')->where('order_id', $id)->orderBy('id', 'ASC')->get();
        // dd($query->get());
         return Datatables::of($query)
                        
            
            ->addColumn('refrence_code',function($item){
                return $item->product->refrence_code;
            })
            ->addColumn('hs_code',function($item){
                return $item->product->hs_code;
            })
            ->addColumn('name',function($item){
                return $item->product->name;
            })
            ->addColumn('product_type',function($item){
                return ($item->product->productType->title !== null ? $item->product->productType->title : "N.A");
            })
            ->addColumn('buying_unit',function($item){
                // return 4;
                return ($item->product->units !== null ? $item->product->units->title : "N.A");             
            })
            ->addColumn('supplier',function($item){
                return ($item->product->def_or_last_supplier !== null ? $item->product->def_or_last_supplier->first_name.' '.$item->product->def_or_last_supplier->last_name : "N.A");              
            })
            ->addColumn('quantity',function($item){
                 return ($item->quantity !== null ? $item->quantity : "N.A");
            })
             ->addColumn('unit_price',function($item){
                $html_string ='<span class="unit-price-'.$item->id.'"">'.$item->product->selling_price.'</span>';
                return $html_string;
            })
            ->addColumn('total_price',function($item){
                $total_price = $item->product->selling_price * $item->quantity;
                $html_string ='<span class="total-price total-price-'.$item->id.'"">'.$total_price.'</span>';
                return $html_string;
            })
             ->setRowId(function ($item) {
                    return $item->id;
                })
             // yellowRow is a custom style in style.css file
             ->setRowClass(function ($item) {
                    return $item->product->status != 0 ? 'alert-success' : 'yellowRow';
                })
            ->rawColumns(['action','quantity','unit_price','total_price'])
            ->make(true);
    
    }

}
