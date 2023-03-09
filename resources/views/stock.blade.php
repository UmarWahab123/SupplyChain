<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<style>
table, td, th {
  border: 1px solid black;
}

table {
  border-collapse: collapse;
  width: 40%;
}

th {
  height: 50px;
}

tr:nth-child(even) {background-color: #f2f2f2;}
</style>

</head>
<body>
	<div style="  text-align: center;   font-size: 1.8em;    position:relative;   bottom:0.4em;">On every Page we Process 250 Products for Stock Count</div>
	<div style="  text-align: center;   font-size: 1.8em;    position:relative;   bottom:0.4em;">And Among Those 250 Show Only Those Which Has Mismatch</div>
	<?php 
		$products = App\Models\Common\Product::where('status',1)->paginate(250);
 ?>

			<table class="table dot-dash text-center">
            <thead class="dot-dash">
            <tr>
            	<th>Product</th>
            	<th>Bangkok <br>Current<br> Stock</th>
            	<th>Bangkok <br>Stock<br>History</th>
                
            	<th>Phuket <br>Current<br> Stock</th>
            	<th>Phuket <br>Stock<br>History</th>
                
            	<th>BCS <br>Current<br> Stock</th>
            	<th>BCS <br>Stock<br>History</th>
                
            </tr>
            </thead>
            <tbody>
		@foreach ($products as $product) 
			
        	<tr>
			<?php
			
		    $warehouse_product = App\Models\Common\WarehouseProduct::where('warehouse_id',1)->where('product_id',$product->id)->pluck('current_quantity')->first();
		    $stock_management_in = App\Models\Common\StockManagementOut::where('warehouse_id',1)->where('product_id',$product->id)->sum('quantity_in');
		    $stock_management_out = App\Models\Common\StockManagementOut::where('warehouse_id',1)->where('product_id',$product->id)->sum('quantity_out');

		    $warehouse_product2 = App\Models\Common\WarehouseProduct::where('warehouse_id',2)->where('product_id',$product->id)->pluck('current_quantity')->first();
		    $stock_management_in2 = App\Models\Common\StockManagementOut::where('warehouse_id',2)->where('product_id',$product->id)->sum('quantity_in');
		    $stock_management_out2 = App\Models\Common\StockManagementOut::where('warehouse_id',2)->where('product_id',$product->id)->sum('quantity_out');
            
		    
		    $warehouse_product3 = App\Models\Common\WarehouseProduct::where('warehouse_id',3)->where('product_id',$product->id)->pluck('current_quantity')->first();
		    $stock_management_out3 = App\Models\Common\StockManagementOut::where('warehouse_id',3)->where('product_id',$product->id)->sum('quantity_out');
		    $stock_management_in3 = App\Models\Common\StockManagementOut::where('warehouse_id',3)->where('product_id',$product->id)->sum('quantity_in');
			
			?>

		    @if(round($warehouse_product,2) !== round(($stock_management_in)+($stock_management_out),2) || round($warehouse_product2,2) !== round(($stock_management_in2)+($stock_management_out2),2) || round($warehouse_product3,2) !== round(($stock_management_in3)+($stock_management_out3),2))
		    		    	
        		<td>{{($product->refrence_code)}}</td>

        		<td>{{($warehouse_product != null ? round($warehouse_product,2) : 0)}}</td>
                <td>{{round(($stock_management_in)+($stock_management_out),2)}}</td>

        		<td>{{($warehouse_product2 != null ? round($warehouse_product2,2) : 0)}}</td>
                <td>{{round(($stock_management_in2)+($stock_management_out2),2)}}</td>

        		<td>{{($warehouse_product3 != null ? round($warehouse_product3,2) : 0)}}</td>
                <td>{{round(($stock_management_in3)+($stock_management_out3),2)}}</td>
		    @endif

		</tr>
	@endforeach
		</tbody></table>
		{{$products->links("pagination::bootstrap-4")}}
</body>
</html>