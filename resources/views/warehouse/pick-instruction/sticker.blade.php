<!DOCTYPE html>
<html>
<head>
	<title>Sticker</title>
	<style type="text/css">
		*{
			margin: 0;
			padding: 0;
		}
		@page { size: 6cm 10cm landscape; }
	</style>
</head>
<?php
 	use Carbon\Carbon;
	$count = $ordersProducts->count();
	$i = 1;
	foreach ($ordersProducts as $item){
?>
<body style="border:2px solid black;padding: 10px;">
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td style="font-weight: bold;text-align:left;text-transform: uppercase;">{{@Auth::user()->getCompany->company_name}}
				</td>

				<td align="right">
					<span>CO.LTD</span>
				</td>
				<tr>
					<table style="width: 100%;border: 2px solid black;border-collapse: collapse;">
						<tbody>
							<tr>
								<td colspan="2" style="border-bottom: 1px solid #aaa;text-align: center;padding: 5px 5px;">{{$customer->reference_name}}</td>
							</tr>
							<tr>
								<td style="border-bottom: 1px solid #aaa;padding: 5px 5px;" align="center" width="30%">
									<?php
									$unit = $item->product != null ? $item->product->sellingUnits->title : 'N.A';
									?>
									@if($item->is_retail == 'qty')
										@if($item->qty_shipped != null)
										<span>{{$item->qty_shipped}} {{$unit}}</span>
										@else
										<span></span> {{$unit}}
										@endif
									@else
										@if($item->pcs_shipped != null)
										<span>{{$item->pcs_shipped}} pc</span>
										@else
										<span></span> pc
										@endif
									@endif
								</td>
								<td style="border-bottom: 1px solid #aaa;padding: 5px 5px;" width="70%">{{@$item->product != null ? $item->product->short_desc : 'N.A'}}</td>
							</tr>
							<tr>
								<td colspan="2" style="padding: 5px 0;">
									<span style="visibility: hidden;">Empty Td</span>
								</td>
							</tr>
						</tbody>
					</table>
				</tr>
			</tr>
		</tbody>
	</table>

	<table style="width: 100%;position: absolute;bottom: 20px;">
		<tbody>
			<tr>
				<td>Delivery Date: {{@$order->delivery_request_date != null ? Carbon::parse($order->delivery_request_date)->format('D, M d,Y') : '--'}}</td>
				<td align="right">Page {{$i}} of {{$count}}</td>
			</tr>
		</tbody>
	</table>
</body>
<?php
	$i++;
}
?>
</html>
