<?php

namespace App\Exports;

// use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerSaleReportExport implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //
    }

    public function __construct($customers , $selected_year, $months, $not_visible_arr)
    {
        $this->customers = $customers;
        $this->selected_year = $selected_year;
        $this->months = $months;
        $this->not_visible_arr = $not_visible_arr;
    }

    public function query()
    {
        $query = $this->customers;
        return $query;
    }

    public function headings() : array
    {
        $selected_year = $this->selected_year;
        $months = $this->months;
        $not_visible_arr = $this->not_visible_arr;
        $customers = $this->customers;

        $heading_array = [];

        if(!in_array('0',$not_visible_arr))
        {
            array_push($heading_array, 'Customers');
        }
        $key=1;
        foreach($months as $mon)
        {
            if($mon == 'Dec')
            {
                $mon = 'Dece';
            }
            if(!in_array($key,$not_visible_arr))
            {
                array_push($heading_array, $mon);
            }
            $key++;
        }
        if(!in_array('13',$not_visible_arr))
        {
            array_push($heading_array, 'Grand Total');
        }
        if(!in_array('14',$not_visible_arr))
        {
            array_push($heading_array, 'Location Code');
        }
        if(!in_array('15',$not_visible_arr))
        {
            array_push($heading_array, 'Sales Person Code');
        }
        if(!in_array('16',$not_visible_arr))
        {
            array_push($heading_array, 'Payment Terms Code');
        }

        return $heading_array;
    }

    public function map($customer) : array
    {
        $selected_year = $this->selected_year;
        $months = $this->months;
        $not_visible_arr = $this->not_visible_arr;

        $data_array = [];

        
        if(@$customer->getYearWiseSale(@$customer->id , @$selected_year) > 0)
        {
            if(!in_array('0',$not_visible_arr))
            {
                array_push($data_array, @$customer->reference_name);
            }
            $key=1; 
            foreach($months as $mon)
            {
                if($mon == 'Dec')
                {
                    $mon = 'Dece';
                }
                if(!in_array($key,$not_visible_arr))
                {
                    array_push($data_array, @$customer->$mon !== null ? number_format($customer->$mon,2,'.','') : '0.00');
                }
                $key++;
            }
            if(!in_array('13',$not_visible_arr))
            {
                $data = @$customer->customer_orders_total !== null ? number_format(@$customer->customer_orders_total,2,'.','') : '0.00';
                array_push($data_array, $data);
            } 
            if(!in_array('14',$not_visible_arr))
            {
                $data = @$customer->customer_orders[0]->user->get_warehouse->location_code;
                array_push($data_array, $data);
            } 
            if(!in_array('15',$not_visible_arr))
            {
                array_push($data_array, @$customer->primary_sale_person->name);
            }
            if(!in_array('16',$not_visible_arr))
            {
                array_push($data_array, @$customer->getpayment_term->title);
            }
            
        }

        return $data_array;
    }

    // public function view(): View
    // {
        
    // 	$customers = $this->customers;
    //     $selected_year = $this->selected_year;
    //     $customers_ids = $this->customers;
    //     $months = $this->months;
    //     $not_visible_arr = $this->not_visible_arr;

    // 	// dd($months);
    //     return view('users.exports.customer-sale-report', ['customers' => $customers,
    // 												'selected_year' => $selected_year,
    // 												'customers_ids'=>$customers_ids,
    //                                                 'months'=> $months,
    //                                                 'not_visible_arr'=> $not_visible_arr]);
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
            	$event->sheet->getStyle('A1:Q1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

            	// $grand_total_row = $this->customers->count()+2;
            	// // dd($grand_total_row);
             //    $event->sheet->getStyle('A'.$grand_total_row.':Q'.$grand_total_row.'')->applyFromArray([
             //        'font' => [
             //            'bold' => true
             //        ]
             //    ]);
            },
        ];
    }
}
