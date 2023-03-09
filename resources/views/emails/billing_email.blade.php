<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <style type="text/css">
    	.parentDiv
    	{
    		width: 600px;
    		border: 1px solid #eee;
    		box-shadow: 0px 0px 15px 0px rgba(0,0,0,0.3);
    		margin-left: 100px;
    	}
    	.headerDiv
    	{
    		background: #073763;
    		height: 10px;
    		width: 100%;
    		padding-bottom: 30px
    	}
    	.bodyDiv
    	{
    		border: 3px solid #073763;
    		margin-bottom: 20px;
    	}
    	.marginDiv{
    		margin: 20px;
    	}
    	table
    	{
    		width: 100%;
    	}
    	body{
    		text-decoration-color: black;
    	}
    	td, .total-values-div{
    		padding-left: 20px;
    		padding-top: 10px;
    		padding-bottom: 10px;
    	}
    	img{
    		padding-top: 10px;
    		height: 50px;
    	}
    </style>
</head>
<body>
	<div class="parentDiv">
		<div class="headerDiv">
			
		</div>
		<div class="marginDiv">
			<div>
			<p>To whom it may concern</p>
			<p>The email notifies you that <b>{!! $company_info->company_name !!}</b> payment due date on <b>{!! $billing->official_launch_date !!}</b>. <br>Please proceed to do billing within the specified time period.</p>
		</div>
		<div class="bodyDiv">
			<div style="text-align: center;width: 100%">
				<img src="{{$message->embed(asset('public/uploads/logo/'.@$company_info->logo))}}" alt="logo">
			</div>
			<table>
				<tr>
					<td width="30%">Customer name :</td>
					<td>{!! $company_info->company_name !!}</td>
				</tr>
				<tr>
					<td width="30%">Billing types :</td>
					@if($billing->type == 'annual')
						<td>Annual</td>
					@else
						<td>Monthly</td>
					@endif
				</tr>
				<tr>
					<td width="30%">Launch date :</td>
					<td>{!! $billing->official_launch_date !!}</td>
				</tr>
				<tr>
					@if($billing->type == 'annual')
						<td width="30%">Total users allowed :</td>
						<td>{!! $billing->total_users_allowed !!}</td>
					@else
						<td width="30%">Monthly price per user :</td>
						<td>{!! $billing->monthly_price_per_user !!} {!! $billing->currency != null ? $billing->currency->currency_symbol : '--' !!}</td>
					@endif
				</tr>
				@if($billing->type == 'monthly')
				<tr>
					<td width="30%">Number of Active users :</td>
					<td>{!! $active_users !!}</td>
				</tr>
				@endif
				<tr>
					@if($billing->type == 'annual')
						<td width="30%">Current annual fee :</td>
						<td>{!! $billing->current_annual_fee !!} {!! $billing->currency != null ? $billing->currency->currency_symbol : '--' !!}</td>
					@else
						<td width="30%">Number of free users :</td>
						<td>{!! $billing->no_of_free_users !!}</td>
					@endif
				</tr>
				@if($billing->type == 'monthly')
					<tr>
						<td width="30%">Amount due for users :</td>
						<td>{!! $ammountDue !!} {!! $billing->currency != null ? $billing->currency->currency_symbol : '--' !!}</td>
					</tr>
				@endif
			</table>
			<div class="total-values-div">Total :@if($billing->type == 'annual') {!! $billing->current_annual_fee !!} @else {!! $ammountDue !!} @endif {!! $billing->currency != null ? $billing->currency->currency_symbol : '--' !!}</div>
		</div>
		<div>
			For more information : <a href="{{url('admin/billing-configuration')}}">Go to Billing Configurations Page</a>
		</div>
		</div>
	</div>
</body>
</html>
