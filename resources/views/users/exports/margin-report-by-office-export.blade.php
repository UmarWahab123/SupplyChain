<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
	<table>
		<thead>
			<tr>
				@if(!in_array('0',$not_visible_arr))
				<th style="font-weight: bold;">
				@if($filter == 'product')
				 	PF#
				@elseif($filter == 'sales')
					Sales Person
				@elseif($filter == 'office')
					Office
				@elseif($filter == 'product_category')
					Product Category
				@elseif($filter == 'customer')
					Customer Ref #
				@elseif($filter == 'customer_type')
					Customer Types
				@elseif($filter == 'product_type')
					Product Type
				@elseif($filter == 'product_type 2')
					@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif
				@elseif($filter == 'supplier')
					Supplier
				@endif
				</th>
				@endif

				@if($filter == 'customer')
					@if(!in_array('1',$not_visible_arr))
					<th style="font-weight: bold;">Customers</th>
					@endif
					@if(!in_array('2',$not_visible_arr))
					<th style="font-weight: bold;">VAT Out</th>
					@endif
					@if(!in_array('3',$not_visible_arr))
					<th style="font-weight: bold;">Sales</th>
					@endif
					@if(!in_array('4',$not_visible_arr))
					<th style="font-weight: bold;">% Sales</th>
					@endif
					@if(!in_array('5',$not_visible_arr))
					<th style="font-weight: bold;">VAT In</th>
					@endif
					@if(!in_array('6',$not_visible_arr))
					<th style="font-weight: bold;">{{$global_terminologies['net_price']}}</th>
					@endif
					@if(!in_array('7',$not_visible_arr))
					<th style="font-weight: bold;">GP</th>
					@endif
					@if(!in_array('8',$not_visible_arr))
					<th style="font-weight: bold;">% GP</th>
					@endif
					@if(!in_array('9',$not_visible_arr))
					<th style="font-weight: bold;">Margin</th>
					@endif
				@else
					@if(!in_array('1',$not_visible_arr))
					<th style="font-weight: bold;">VAT Out</th>
					@endif
					@if(!in_array('2',$not_visible_arr))
					<th style="font-weight: bold;">Sales</th>
					@endif
					@if(!in_array('3',$not_visible_arr))
					<th style="font-weight: bold;">% Sales</th>
					@endif
					@if(!in_array('4',$not_visible_arr))
					<th style="font-weight: bold;">VAT In</th>
					@endif
					@if(!in_array('5',$not_visible_arr))
					<th style="font-weight: bold;">{{$global_terminologies['net_price']}}</th>
					@endif
					@if(!in_array('6',$not_visible_arr))
					<th style="font-weight: bold;">GP</th>
					@endif
					@if(!in_array('7',$not_visible_arr))
					<th style="font-weight: bold;">% GP</th>
					@endif
					@if(!in_array('8',$not_visible_arr))
					<th style="font-weight: bold;">Margin</th>
					@endif
				@endif
			</tr>
		</thead>
		<tbody>
			@foreach($records as $item)
			<tr>
			@if($filter == 'customer')
				@if(!in_array('0',$not_visible_arr))
				<td>{{$item->office}}</td>
				@endif
				@if(!in_array('1',$not_visible_arr))
				<td>{{$item->customers}}</td>
				@endif
				@if(!in_array('2',$not_visible_arr))
				<td>{{$item->vat_out}}</td>
				@endif
				@if(!in_array('3',$not_visible_arr))
				<td>{{$item->sales}}</td>
				@endif
				@if(!in_array('4',$not_visible_arr))
				<td>{{$item->percent_sales}}%</td>
				@endif
				@if(!in_array('5',$not_visible_arr))
				<td>{{$item->vat_in}}</td>
				@endif
				@if(!in_array('6',$not_visible_arr))
				<td>{{$item->cogs}}</td>
				@endif
				@if(!in_array('7',$not_visible_arr))
				<td>{{$item->gp}}</td>
				@endif
				@if(!in_array('8',$not_visible_arr))
				<td>{{$item->percent_gp}}%</td>
				@endif
				@if(!in_array('9',$not_visible_arr))
				<td>{{$item->margins}}%</td>
				@endif
			@else
				@if(!in_array('0',$not_visible_arr))
				<td>{{$item->office}}</td>
				@endif
				@if(!in_array('1',$not_visible_arr))
				<td>{{$item->vat_out}}</td>
				@endif
				@if(!in_array('2',$not_visible_arr))
				<td>{{$item->sales}}</td>
				@endif
				@if(!in_array('3',$not_visible_arr))
				<td>{{$item->percent_sales}}%</td>
				@endif
				@if(!in_array('4',$not_visible_arr))
				<td>{{$item->vat_in}}</td>
				@endif
				@if(!in_array('5',$not_visible_arr))
				<td>{{$item->cogs}}</td>
				@endif
				@if(!in_array('6',$not_visible_arr))
				<td>{{$item->gp}}</td>
				@endif
				@if(!in_array('7',$not_visible_arr))
				<td>{{$item->percent_gp}}%</td>
				@endif
				@if(!in_array('8',$not_visible_arr))
				<td>{{$item->margins}}%</td>
				@endif
			@endif
			</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
