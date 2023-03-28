<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductSaleReportByMonthExport implements ShouldAutoSize, FromQuery, WithHeadings, WithMapping
{
   	protected $query;
   	protected $months;
   	protected $not_visible_arr;
   	protected $global_terminologies;
   	public function __construct($query, $months, $not_visible_arr, $global_terminologies)
    {
        $this->query = $query;
        $this->months = $months;
        $this->global_terminologies = $global_terminologies;
        $this->not_visible_arr = $not_visible_arr;
    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array 
    {
    	$not_visible_arr = $this->not_visible_arr;
    	$months = $this->months;
    	$data_array = [];
    	$i = 4;

    	if (!in_array('0', $not_visible_arr)) {
		  array_push($data_array, @$item->product->refrence_code);
		}

		if (!in_array('1', $not_visible_arr)) {
		  array_push($data_array, ($item->product != null) ? $item->product->brand : '--');
		}

		if (!in_array('2', $not_visible_arr)) {
		  array_push($data_array, ($item->product != null) ? $item->product->short_desc : '--');
		}

		if (!in_array('3', $not_visible_arr)) {
		  array_push($data_array, (@$item->product->sellingUnits != null) ? @$item->product->sellingUnits->title : '--');
		}

		foreach($months as $month)
		{
			if ($month == 'Jan') { 	
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->jan_totalAmount != null ? round($item->jan_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Feb') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->feb_totalAmount != null ? round($item->feb_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Mar') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->mar_totalAmount != null ? round($item->mar_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Apr') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->apr_totalAmount != null ? round($item->apr_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'May') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->may_totalAmount != null ? round($item->may_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Jun') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->jun_totalAmount != null ? round($item->jun_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Jul') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->jul_totalAmount != null ? round($item->jul_totalAmount,2) : '0.00');
			  	} 	
			}
			if ($month == 'Aug') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->aug_totalAmount != null ? round($item->aug_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Sep') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->sep_totalAmount != null ? round($item->sep_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Oct') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->oct_totalAmount != null ? round($item->oct_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Nov') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->nov_totalAmount != null ? round($item->nov_totalAmount,2) : '0.00');
			  	}
			}
			if ($month == 'Dec') {
				if (!in_array($i, $not_visible_arr)) {
			  		array_push($data_array, $item->dec_totalAmount != null ? round($item->dec_totalAmount,2) : '0.00');
			  	}
			}
			$i++;
		}

		return $data_array;
    }

     public function headings(): array
    {
        $months = $this->months;
        $global_terminologies = $this->global_terminologies;
        $not_visible_arr = $this->not_visible_arr;
        $i = 4;
    	$heading_array = [];

    	if (!in_array('0', $not_visible_arr)) {
		  array_push($heading_array, $global_terminologies['our_reference_number']);
		}

		if (!in_array('1', $not_visible_arr)) {
		  array_push($heading_array, $global_terminologies['brand']);
		}

		if (!in_array('2', $not_visible_arr)) {
		  array_push($heading_array, $global_terminologies['product_description']);
		}

		if (!in_array('3', $not_visible_arr)) {
		  array_push($heading_array, 'Selling Unit');
		}

		foreach($months as $mon)
		{
			if (!in_array($i, $not_visible_arr)) {
			  array_push($heading_array, $mon);
			}
			$i++;
		}

		return $heading_array;

    }
}
