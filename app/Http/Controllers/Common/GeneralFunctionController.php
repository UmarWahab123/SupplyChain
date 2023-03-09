<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Company;
use App\Models\Common\Country;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\OrderAttachment;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\ProductCategory;
use App\Models\Common\State;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Order\Order;
use Auth;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;


class GeneralFunctionController extends Controller
{
    public function filterState()
    {
      if(isset($_GET['country_id']))
      {
        $country_id=$_GET['country_id'];
        $states = State::where(['country_id'=>$country_id])->orderBy('name','ASC')->get();
      // dd($states);
         $sta = null;
        if(@$states != null){
          $sta = '<option disabled="true">Select District</option>';
          foreach(@$states as $state){
            $sta .= '<option value='.$state->id.'>'.$state->name.'</option>'; 
          }
        }
        return response()->json(['states'=>$sta,$states]);
 	    }
	 }

    public function filterSubCategory()
    {
        if(isset($_GET['category_id']))
        {
           $category_id=$_GET['category_id'];
           $sub_cat = ProductCategory::where(['parent_id'=>$category_id])->orderBy('title','ASC')->get();
           return response()->json($sub_cat);
    }
   }

	// public function sort_column_display(Request $request){
 //    	$check_type = ColumnDisplayPreference::where('type', $request->type)->first();
 //    	if($check_type){
 //    		$sortColumn = ColumnDisplayPreference::find($check_type->id);
 //    	}else{
 //    		$sortColumn = new ColumnDisplayPreference;
 //    	}
 //        $sortColumn->user_id = $this->user->id;
 //    	$sortColumn->type = $request->type;
 //    	$sortColumn->display_order = $request->order;
 //    	$sortColumn->updated_by = Auth::user()->id;
 //    	$sortColumn->save(); 
 //    }
    public function toggleTableColumnDisplay(Request $request)
    {
      if($request->type != null)
      {
        $column_hidden = $request->column_id;
      }
      else
      {
        $column_hidden = -1;
      }

      if($request->type == 'customer_sale_report')
      {
        $check_type = TableHideColumn::where('user_id', $this->user->id)->where('year',$request->year)->where('type', $request->type)->first();
        if($check_type)
        {
          $hideColumn = TableHideColumn::find($check_type->id);
          $arr = $hideColumn->hide_columns;
          $arr = explode(',', $arr);
          $contains = in_array($column_hidden, $arr);
          if($contains == true)
          {
            $var = $this->remove_element($arr,$column_hidden);
            $columns = implode(',',@$var);
          }
          else
          {
            $columns = @$hideColumn->hide_columns.','.@$column_hidden;
          }
        }
        else
        {
          $hideColumn = new TableHideColumn;
          $columns = $request->column_id;
        }
        $hideColumn->user_id = $this->user->id;
        $hideColumn->type = $request->type;
        $hideColumn->year = $request->year;
        $hideColumn->hide_columns = $columns;
        $hideColumn->updated_by = $this->user->id;;
        $hideColumn->save();
      }
      else
      {
        $check_type = TableHideColumn::where('user_id', $this->user->id)->where('type', $request->type)->first();
        if($check_type)
        {
          $hideColumn = TableHideColumn::find($check_type->id);
          $arr = $hideColumn->hide_columns;
          $arr = explode(',', $arr);
          $contains = in_array($column_hidden, $arr);
          if($contains == true)
          {
            $var = $this->remove_element($arr,$column_hidden);
            $columns = implode(',',@$var);
          }
          else
          {
            $columns = @$hideColumn->hide_columns.','.@$column_hidden;
          }
        }
        else
        {
          $hideColumn = new TableHideColumn;
          $columns = $request->column_id;
        }
        $hideColumn->user_id = $this->user->id;
        $hideColumn->type = $request->type;
        $hideColumn->hide_columns = trim($columns,",");
        $hideColumn->updated_by = $this->user->id;;
        $hideColumn->save(); 
      }

      $prevArray = explode(',', @$hideColumn->hide_columns);
      $newAaar = array();
      if(count($prevArray) > 0){
        foreach($prevArray as $val){
          $newAaar[] = (int)$val;
        }
      }

      return response()->json([
        "success"  => true,
        "cols_arr" => @$newAaar,
      ]);
    }

    public function remove_element($array,$value) {
      foreach (array_keys($array, $value) as $key) {
         unset($array[$key]);
      }  
       return $array;
     }
    
     public function sortColumnDisplay(Request $request)
     {
        $user = Auth::user();
        $check_type = ColumnDisplayPreference::where('type', $request->type)->where('user_id', Auth::user()->id)->first();
        if($check_type)
        {
          $sortColumn = ColumnDisplayPreference::find($check_type->id);
        }
        else
        {
          $sortColumn = new ColumnDisplayPreference;
        }
          
        $sortColumn->user_id = $user->id;
        $sortColumn->type = $request->type;
        $sortColumn->display_order = $request->order;
        $sortColumn->updated_by = $user->id;
        $sortColumn->save(); 
      }

    public function getOrderDetail($id)
    {  

      $user = Auth::user();
      $layout = '';
      if($user->role_id == 1)
      {
        $layout = 'backend';
      }
      elseif ($user->role_id == 2) 
      {
        $layout = 'users';
      }
      elseif ($user->role_id == 3) 
      {
        $layout = 'sales';
      }
         elseif ($user->role_id == 4) 
        {
            $layout = 'sales';
        }
      elseif ($user->role_id == 5) 
      {
        $layout = 'importing';
      }
      elseif ($user->role_id == 6) 
      {
        $layout = 'warehouse';
      }

      $states = State::select('id','name')->orderby('name', 'ASC')->where('country_id',217)->get();

      $billing_address = null;
      $shipping_address = null;
      $order = Order::find($id);
      $company_info = Company::where('id',$order->user->company_id)->first();
      if($order->billing_address_id != null){
      $billing_address = CustomerBillingDetail::where('id',$order->billing_address_id)->first();
      }
      if($order->shipping_address_id){
      $shipping_address = CustomerBillingDetail::where('id',$order->shipping_address_id)->first();
      }
      $total_products = $order->order_products->count('id'); 
      $sub_total = 0 ;
      $sub_total_with_vat = 0;
      $query = OrderProduct::where('order_id',$id)->get();
      foreach ($query as  $value) {
          $sub_total += $value->quantity * $value->unit_price;
          $sub_total_with_vat += $value->total_price_with_vat;
      }
      $vat = $sub_total_with_vat - $sub_total;
      $grand_total = ($sub_total)-($order->discount)+($order->shipping)+($vat);
      $status_history = OrderStatusHistory::with('get_user')->where('user_id',$this->user->id)->where('order_id',$id)->get();
      $checkDocs = OrderAttachment::where('order_id',$order->id)->get()->count();
      $inv_note = OrderNote::where('order_id', $order->id)->first();
      return view('common.orders.order_detail', compact('layout','order','company_info','total_products','sub_total','grand_total','status_history','vat', 'id','checkDocs','inv_note','billing_address','shipping_address','states'));
    }

    public function getOrderProductDetail($id)
    {
        $query = OrderProduct::with('product','get_order','product.units','product.supplier_products')->where('order_id', $id)->orderBy('id', 'ASC');
        // dd($query->get());
         return Datatables::of($query)
                        
            ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon removeProduct" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';
                return $html_string;
            })
            ->addColumn('refrence_code',function($item){
              if($item->product == null)
              {
                return 'N.A';
              }

              else{
                $item->product->refrence_code ? $reference_code = $item->product->refrence_code : $reference_code = "N.A";
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'"  >'.$reference_code.'</a>';                
              }
            })            
            ->addColumn('description',function($item){
              if($item->product == null){
                return 'N.A';
              }
              else
              {
                return $item->product->short_desc;
              }
            })
            ->addColumn('number_of_pieces',function($item){
                $html = '<span class="inputDoubleClick">'.$item->number_of_pieces.'</span><input type="number" name="number_of_pieces" min="0" value="'.$item->number_of_pieces.'" class="number_of_pieces form-control input-height d-none" style="width:100%; border-radius:0px;">';
                return $html;
            })
            ->addColumn('sell_unit',function($item){
              if($item->product == null)
              {
                return 'N.A';
              }
              else{
                return $item->product->units ? $item->product->units->title : "N.A";
              }
            })
            
            
            ->addColumn('buying_unit',function($item){
              if($item->product == null){
                return 'N.A';
              }
              else{
                return ($item->product->units !== null ? $item->product->units->title : "N.A");             
              }
            })
            
            ->addColumn('quantity',function($item){
                $html = '<span class="inputDoubleClick">'.$item->quantity.'</span><input type="number" name="quantity" min="0" value="'.$item->quantity.'" class="quantity form-control input-height d-none" style="width:100%; border-radius:0px;">';
                return $html;
            })

            ->addColumn('exp_unit_cost',function($item){
                if($item->exp_unit_cost == null){
                    return "N.A";
                }
                else{ 
                 $html_string ='<span class="unit-price-'.$item->id.'"">'.number_format($item->exp_unit_cost, 2, '.', ',').'</span>';
                }
                return $html_string;
            })

            ->addColumn('margin',function($item){
                //margin is stored in draftqoutation product and we need to add % or $ based on Percentage or Fixed 
              if($item->product == null){
                return 'N.A';
              }
              if($item->margin == null){
                return "Fixed Price";
              }
              else{
                if(is_numeric($item->margin)){
                    return $item->margin.'%';
                  }
                  else{
                    return $item->margin;
                  }
                }
            })

            ->addColumn('unit_price',function($item){
                $star = '';
                if(is_numeric($item->margin)){
                    $product_margin = CustomerTypeProductMargin::where('product_id',$item->product->id)->where('customer_type_id',$item->get_order->customer->category_id)->where('is_mkt',1)->first();
                    if($product_margin){
                        $star = '*';
                    }
                }
               $html = '<span class="inputDoubleClick">'.$star.number_format($item->unit_price, 2, '.', ',').'</span><input type="number" name="unit_price" step="0.01" min="0" value="'.number_format($item->unit_price, 2, '.', ',').'" class="unit_price form-control input-height d-none" style="width:100%;  border-radius:0px;">';
                return $html;
                
            })

            ->addColumn('total_price',function($item){
                if($item->total_price == null){ return $total_price = "N.A"; }
                else{ 
                  $total_price = $item->total_price;
                }
                $html_string ='<span class="total-price total-price-'.$item->id.'"">'.number_format($total_price, 2, '.', ',').'</span>';
                return $html_string;
            })

            ->addColumn('vat',function($item){
              if($item->product == null){
                return 'N.A';
              }
              else{
                return $item->product->vat ? $item->product->vat.'%' : "N.A";
              }
            })
            
             
            ->addColumn('notes', function ($item) { 
                // check already uploaded images //
                $notes = OrderProductNote::where('order_product_id', $item->id)->count();

                if($notes > 0){  
                $html_string = '<div class="d-flex justify-content-center text-center">';
                $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
                }
                else{
                  $html_string = 'No Notes Found';
                }
                return $html_string;         
            })

            ->setRowId(function ($item) {
                    return $item->id;
                })
             // yellowRow is a custom style in style.css file
             ->setRowClass(function ($item) {
                    return $item->product != null ? 'alert-success' : 'yellowRow';
                })
            ->rawColumns(['action','refrence_code','number_of_pieces','quantity','unit_price','total_price','exp_unit_cost','notes'])
            ->make(true);    
    }

    public function getCompletedQuotProdNote(Request $request)
    {
        $compl_quot_notes = OrderProductNote::where('order_product_id',$request->compl_quot_id)->get();

        $html_string ='<div class="table-responsive">
                        <table class="table table-bordered text-center">
                        <thead class="table-bordered">
                        <tr>
                            <th>S.no</th>
                            <th>Description</th>
                        </tr>
                        </thead><tbody>';
                        if($compl_quot_notes->count() > 0){
                        $i = 0;
                        foreach($compl_quot_notes as $note){
                        $i++;   
        $html_string .= '<tr id="gem-note-'.$note->id.'">
                            <td>'.$i.'</td>
                            <td>'.$note->note.'</td>
                         </tr>';                
                        }   
                        }else{
                          return response()->json(['no_data'=>true]);
        $html_string .= '<tr>
                            <td colspan="4">No Note Found</td>
                         </tr>';            
                        }
                      

        $html_string .= '</tbody></table></div>';
        return $html_string;                

    }

    public function autocompleteFetchOrders(Request $request)
    {
        //  dd($request->all());
        $params = $request->except('_token');
        $detail = [];
        if($request->get('query'))
        {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $order_query  = Order::query();

            foreach ($search_box_value as $result)
            {
                $order_query = $order_query->orWhere('ref_id', 'LIKE',"%$result%");
            }

            $order_query  = $order_query->pluck('id')->toArray();

            if(! empty($order_query) || ! empty($category_query) )
            {
                $product_detail = Order::orderBy('id','ASC');

                if(! empty($order_query))
                {
                    $product_detail->where(function ($q) use ($order_query) {
                        $q->whereIn('id', $order_query);
                    });
                }               
                
                $detail = $product_detail->take(10)->get();
            }

            if(!empty($detail)){

                $output = '<ul class="dropdown-menu p-0" style="display:block; width:100%; margin-top:0; z-index:1000; max-height: 380px;overflow-y: scroll;">';
                    foreach($detail as $row)
                    {                                                   
                        $output .= '
                            <li><a target="_blank" href="'.route('get-order-detail', ['id' => $row->id]).'"  class="search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>'.$row->ref_id.'</a></li>
                            ';
                    }
                $output .= '</ul>';
                echo $output;
            }
            else{
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }

    }
    }
  
}
