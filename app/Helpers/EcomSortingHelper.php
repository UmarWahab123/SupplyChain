<?php
namespace App\Helpers;
/**
 * 
 */
class EcomSortingHelper
{
	public static function EcomDashboardSorting($request, $query)
	{
		$sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
		if ($request['column_name'] == 'order_no') 
		{
			$query->orderBy('id', $sort_order);
		}
		elseif ($request['column_name'] == 'reference_name') 
		{
			$query->leftJoin('customers as c', 'c.id', '=', 'orders.customer_id')->orderBy('c.reference_name', $sort_order);
		}
		elseif ($request['column_name'] == 'order_total') 
		{
			$query->orderBy('orders.total_amount', $sort_order);
		}
		elseif ($request['column_name'] == 'delivery_date') 
		{
			$query->orderBy('orders.delivery_request_date', $sort_order);
		}
		elseif ($request['column_name'] == 'due_date') 
		{
			$query->orderBy('orders.payment_due_date', $sort_order);
		}
		elseif ($request['column_name'] == 'type') 
		{
			$query->orderBy('orders.payment_due_date', $sort_order);
		}
		else
		{
			$query->orderBy('orders.id', 'DESC');
		}
		return $query;
	}
}