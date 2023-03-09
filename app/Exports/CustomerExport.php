<?php

namespace App\Exports;

use App\Models\Common\Product;
use Carbon;
// use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerExport implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
	protected $query = null;
    protected $row_color = null;
    protected $not_visible_arr = null;
    protected $global_terminologies = null;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query,$not_visible_arr,$global_terminologies)
    {
        $this->query = $query;
        $this->not_visible_arr = $not_visible_arr;
        $this->global_terminologies = $global_terminologies;
    }

    // public function view(): View
    // {

    //     $query = $this->query;
    //     $not_visible_arr = $this->not_visible_arr;
    //     $global_terminologies = $this->global_terminologies;


    //     return view('users.exports.allcustomers', ['query' => $query,'not_visible_arr'=>$not_visible_arr,'global_terminologies'=>$global_terminologies]);
    // }


    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($customer) : array {
        $not_visible_arr = $this->not_visible_arr;
        $data_array = [];
        $district = '';
        $city = '';
        $country = '';
        $address = '';
        $address_reference = '';
        $zip_code = '';
        $tax_id = '';
        if($customer->getbilling->count() >=1)
        {
            $district = @$customer->getbilling[0]->billing_city;
            $city = @$customer->getbilling[0]->getstate->name;
            $country = @$customer->getbilling[0]->getcountry->name;
            $address = @$customer->getbilling[0]->billing_address;
            $address_reference = @$customer->getbilling[0]->title;
            $zip_code = @$customer->getbilling[0]->billing_zip;
            $tax_id = @$customer->getbilling[0]->tax_id;
        }

        if (!in_array('2',$not_visible_arr)) {
            array_push($data_array, @$customer->reference_number);
        }
        if (!in_array('3',$not_visible_arr)) {
            array_push($data_array, @$customer->reference_name);
        }
        if (!in_array('4',$not_visible_arr)) {
            array_push($data_array, @$customer->company);
        }
        if (!in_array('5',$not_visible_arr)) {
            $customerAddress = @$customer->getbilling->where('is_default',1)->first();
            if($customerAddress)
            {
              $data = $customerAddress->billing_email !== null ? @$customerAddress->billing_email : 'N.A';
              array_push($data_array, $data);
            }
            else
            {
              array_push($data_array, 'N.A');
            }
            // array_push($data_array, @$customer->email);
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($data_array, @$customer->primary_sale_person->name);
        }
        if (!in_array('7',$not_visible_arr)) {
            $secondary_sales_person = '';
            if ($customer->CustomerSecondaryUser) {
               for($i=0;$i<count($customer->CustomerSecondaryUser);$i++)
               {
                $secondary_sales_person .= $customer->CustomerSecondaryUser[$i]->secondarySalesPersons->name;
                if(($i)!=(count($customer->CustomerSecondaryUser)-1)){
                    $secondary_sales_person .= ',';
                }
               }
            }
            array_push($data_array, $secondary_sales_person);
        }
        if (!in_array('8',$not_visible_arr)) {

            array_push($data_array, $district);
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($data_array, $city);
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($data_array, @$customer->CustomerCategory->title);
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($data_array, $country);
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($data_array, $address_reference);
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($data_array, $address);
        }
        if (!in_array('14',$not_visible_arr)) {
            array_push($data_array, $zip_code);
        }
        if (!in_array('15',$not_visible_arr)) {
            array_push($data_array, $tax_id);
        }


        if (!in_array('16',$not_visible_arr)) {
            array_push($data_array, @$customer->getpayment_term->title);
        }
        if (!in_array('17',$not_visible_arr)) {
            $created_at = $customer->created_at !== null ? Carbon\Carbon::parse(@$customer->created_at)->format('d/m/Y') : 'N.A';
            array_push($data_array, $created_at);
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($data_array, @$customer->get_total_draft_orders($customer->id));
        }
        if (!in_array('19',$not_visible_arr)) {
            $total = $customer->customer_orders()->whereIn('primary_status',[2,3])->sum('total_amount');
            $all_val = number_format($total,2,'.','');
            array_push($data_array, $all_val);
        }
        if (!in_array('20',$not_visible_arr)) {
            $last_order_date = $customer->last_order_date !== null ? Carbon\Carbon::parse(@$customer->last_order_date)->format('d/m/Y') : 'N.A';
            array_push($data_array, $last_order_date);
        }
        if (!in_array('21',$not_visible_arr)) {
            $status = null;
            if($customer->status == 1)
            {
                $status = "Completed";
            }
            elseif ($customer->status == 2)
            {
                $status = "Suspend";
            }
            else {
                $status = "Incomplete";
            }
            array_push($data_array, $status);
        }
        return $data_array;
    }

    public function headings(): array
    {
        $not_visible_arr = $this->not_visible_arr;

        $headings_array = [];
        if (!in_array('2',$not_visible_arr)) {
            array_push($headings_array, 'Customer #');
        }
        if (!in_array('3',$not_visible_arr)) {
            array_push($headings_array, 'Reference Name');
        }
        if (!in_array('4',$not_visible_arr)) {
            array_push($headings_array, $this->global_terminologies['company_name']);
        }
        if (!in_array('5',$not_visible_arr)) {
            array_push($headings_array, 'Email');
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($headings_array, 'Primary Sale Person');
        }
        if (!in_array('7',$not_visible_arr)) {
            array_push($headings_array, 'Secondary Sale Person');
        }
        if (!in_array('8',$not_visible_arr)) {
            array_push($headings_array, 'District');
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($headings_array, 'City');
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($headings_array, 'Classification');
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($headings_array, 'Country');
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($headings_array, 'Address Reference');
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($headings_array, 'Address');
        }
        if (!in_array('14',$not_visible_arr)) {
            array_push($headings_array, 'Zip Code');
        }
        if (!in_array('15',$not_visible_arr)) {
            array_push($headings_array, 'Tax ID');
        }
        if (!in_array('16',$not_visible_arr)) {
            array_push($headings_array, 'Payment Terms');
        }
        if (!in_array('17',$not_visible_arr)) {
            array_push($headings_array, 'Customer Since');
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($headings_array, 'Draft Orders');
        }
        if (!in_array('19',$not_visible_arr)) {
            array_push($headings_array, 'Total Orders');
        }
        if (!in_array('20',$not_visible_arr)) {
            array_push($headings_array, 'Last Order Date');
        }
        if (!in_array('21',$not_visible_arr)) {
            array_push($headings_array, 'Status');
        }


        return $headings_array;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:T1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

                // $event->sheet->getStyle('B1:F1')->applyFromArray([

                //     'fill' => [
                //     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //     'color' => ['argb' => 'FFFF33']
                //     ]
                // ]);

                // $event->sheet->getStyle('I1:J1')->applyFromArray([

                //     'fill' => [
                //     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //     'color' => ['argb' => 'FFFF33']
                //     ]
                // ]);

                // $event->sheet->getStyle('K1:M1')->applyFromArray([

                //     'fill' => [
                //     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //     'color' => ['argb' => 'FFFF33']
                //     ]
                // ]);

                // $event->sheet->getStyle('O1:Q1')->applyFromArray([

                //     'fill' => [
                //     'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //     'color' => ['argb' => 'FFFF33']
                //     ]
                // ]);

                // $query = $this->query;

                // $sheet_rows = 2;
                // $row_color = false;
                // $check_count = 0;

                // $i = 2;
                // foreach ($query as $key => $customer) {
                // if($row_color)
                // {
                //     $event->sheet->getStyle('A'.$i.':T'.$i.'')->applyFromArray([

                //         'fill' => [
                //         'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                //         'color' => ['argb' => 'E1DEDE']
                //         ]
                //     ]);

                // }
                // if($row_color)
                // {
                //     $row_color = false;
                //     $check_count++;

                // }
                // else
                // {
                //     $row_color = true;
                //     $check_count++;

                // }
                // $i++;
                // }

            },
        ];
    }

}
