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
use Carbon\Carbon;

class invoicetableExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
{
    protected $query = null;
    protected $not_visible_arr = null;
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($query,$not_visible_arr)
    {
        $this->query = $query;
        $this->not_visible_arr = $not_visible_arr;
    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array {
        $data_array = [];
        $not_visible_arr = $this->not_visible_arr;

       if($item->in_status_prefix !== null || $item->in_ref_prefix !== null) {
        $ref_no = @$item->in_status_prefix.'-'.$item->in_ref_prefix.$item->in_ref_id;
       } else {
        $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->in_ref_id;
       }

       if($item->user !== null) {
           $username = @$item->user->name;
       } else {
           $username = '--';
       }

    //    if($item->customer->reference_number !== null) {
    //        $reference_no =
    //    } else {
    //        $reference_no = '--';
    //    }

       if($item->customer_id != null) {

        // $customer_billing = $customer_billing = $item->customer->getbilling->first();
        // if ($customer_billing && $customer_billing->show_title == 1) {
        //     $reference_name = $customer_billing->title;
        // }
        // else{
        // }
        if($item->customer['reference_name'] != null) {
            $reference_name = $item->customer['reference_name'];
        } else {
            $reference_name = $item->customer['first_name'].' '.$item->customer['last_name'];
        }
       } else {
           $reference_name = '--';
       }

       $tax_id = '--';
       $reference_address = '--';
       $customer_billing = $item->customer->getbilling->first();
        if ($customer_billing) {
            $tax_id = $customer_billing->tax_id != null ? $customer_billing->tax_id : '--';
            $reference_address = $customer_billing->title != null ? $customer_billing->title : '--';
        }


       if($item->customer->company !== null) {
            $company_name = $item->customer->company;
       } else {
           $company_name = '--';
       }

       if($item->status_prefix !== null || $item->ref_prefix !== null){
            $draft = @$item->status_prefix.'-'.$item->ref_prefix.$item->ref_id;
       } else {
        $draft = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
       }


       $html_string = '';
       if($item->primary_status == 2 )
       {
         $html_string .= $ref_no;
       }
       elseif($item->primary_status == 3)
       {
         if($item->ref_id == null){
           $ref_no = '-';
         }
         $html_string .= $ref_no;
       }
       elseif($item->primary_status == 1)
       {
         $html_string = $ref_no;
       }


       if(!$item->get_order_transactions->isEmpty()) {

         foreach($item->get_order_transactions as $key=>$ot)
         {
           if($key==0)
             {$payment_reference_no=$ot->get_payment_ref->payment_reference_no;}
           else
           {
             $payment_reference_no =  $ot->get_payment_ref->payment_reference_no;
           }
         }
       }
       else
       {
        $payment_reference_no = '--';
       }



       if(!$item->get_order_transactions->isEmpty())
       {
         $count = count($item->get_order_transactions);
         $received_date=Carbon::parse(@$item->get_order_transactions[$count - 1]->received_date)->format('d/m/Y');

       }
         else
         {
           $received_date = '--';
         }


         if($item->delivery_request_date != null) {
             $delivery_request_date = Carbon::parse($item->delivery_request_date)->format('d/m/Y');
         } else {
             $delivery_request_date = '--';
         }
         if($item->target_ship_date != null) {
             $target_ship_date = Carbon::parse($item->target_ship_date)->format('d/m/Y');
         } else {
             $target_ship_date = '--';
         }


         if($item->converted_to_invoice_on != null) {
            $converted_to_invoice_on = Carbon::parse($item->converted_to_invoice_on)->format('d/m/Y');
        } else {
            $converted_to_invoice_on = '--';
        }

        if( $item->payment_due_date != null) {
            $payment_due_date = Carbon::parse($item->payment_due_date)->format('d/m/Y');
        } else {
            $payment_due_date = '--';
        }

        $customer_note = $item->order_notes->where('type','customer')->first();
        if($customer_note != null) {
            $customer_note = $customer_note->note;
        } else {
            $customer_note = '--';
        }


        $warehouse_note = $item->order_notes->where('type','warehouse')->first();
        if($warehouse_note != null) {
            $warehouse_note = $warehouse_note->note;
        } else {
            $warehouse_note = '--';
        }


        if (!in_array('1',$not_visible_arr)) {
            array_push($data_array, $ref_no);
        }
        if (!in_array('2',$not_visible_arr)) {
            array_push($data_array, $username);
        }
        if (!in_array('3',$not_visible_arr)) {
            array_push($data_array, $item->customer->reference_number);
        }
        if (!in_array('4',$not_visible_arr)) {
            array_push($data_array, $reference_name);
        }
        if (!in_array('5',$not_visible_arr)) {
            array_push($data_array, $reference_address);
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($data_array, $company_name);
        }
        if (!in_array('7',$not_visible_arr)) {
            array_push($data_array, $tax_id);
        }
        if (!in_array('8',$not_visible_arr)) {
            array_push($data_array, $draft);
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($data_array, $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-1');
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($data_array, @$item->order_products != null ? @$item->getOrderTotalVat($item->id,0) : '--');
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($data_array, $item->order_products != null ? @$item->getOrderTotalVat($item->id,1) : '--');
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($data_array, $item->in_status_prefix.'-'.@$item->in_ref_prefix.$item->in_ref_id.'-2');
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($data_array, $item->order_products != null ? @$item->getOrderTotalVat($item->id,2) : '--');
        }
        if (!in_array('14',$not_visible_arr)) {
            array_push($data_array, $item->all_discount !== null ? number_format(floor($item->all_discount*100)/100,2,'.','') : '0.00');
        }
        if (!in_array('15',$not_visible_arr)) {
            array_push($data_array, round($item->sub_total_price,2));
        }
        if (!in_array('16',$not_visible_arr)) {
            array_push($data_array, round($item->total_amount,2));
        }
        if (!in_array('17',$not_visible_arr)) {
            array_push($data_array, $payment_reference_no);
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($data_array, $received_date);
        }
        if (!in_array('19',$not_visible_arr)) {
            array_push($data_array, $delivery_request_date);
        }
        if (!in_array('20',$not_visible_arr)) {
            array_push($data_array, $converted_to_invoice_on);
        }
        if (!in_array('21',$not_visible_arr)) {
            array_push($data_array, $payment_due_date);
        }
        if (!in_array('22',$not_visible_arr)) {
            array_push($data_array, $target_ship_date);
        }
        if (!in_array('23',$not_visible_arr)) {
            array_push($data_array, $customer_note);
        }
        if (!in_array('24',$not_visible_arr)) {
            array_push($data_array, $warehouse_note);
        }
        if (!in_array('25',$not_visible_arr)) {
            array_push($data_array, $item->memo != null ? @$item->memo : '--');
        }
        if (!in_array('26',$not_visible_arr)) {
            array_push($data_array, $item->statuses->title);
        }



       return $data_array;

    }

    public function headings(): array {
        $not_visible_arr = $this->not_visible_arr;

        $headings_array = [];
        if (!in_array('1',$not_visible_arr)) {
            array_push($headings_array, 'Order#');
        }
        if (!in_array('2',$not_visible_arr)) {
            array_push($headings_array, 'Sales Person');
        }
        if (!in_array('3',$not_visible_arr)) {
            array_push($headings_array, 'Customer #');
        }
        if (!in_array('4',$not_visible_arr)) {
            array_push($headings_array, 'Reference Name');
        }
        if (!in_array('5',$not_visible_arr)) {
            array_push($headings_array, 'Refernce Address');
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($headings_array, 'Company Name');
        }
        if (!in_array('7',$not_visible_arr)) {
            array_push($headings_array, 'Tax ID');
        }
        if (!in_array('8',$not_visible_arr)) {
            array_push($headings_array, 'Draft#');
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($headings_array, 'Inv.#');
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($headings_array, 'VAT Inv (-1)');
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($headings_array, 'VAT');
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($headings_array, 'Inv.#');
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($headings_array, 'Non VAT');
        }
        if (!in_array('14',$not_visible_arr)) {
            array_push($headings_array, 'Discount');
        }
        if (!in_array('15',$not_visible_arr)) {
            array_push($headings_array, 'Sub Total');
        }
        if (!in_array('16',$not_visible_arr)) {
            array_push($headings_array, 'Order Total');
        }
        if (!in_array('17',$not_visible_arr)) {
            array_push($headings_array, 'Payment Reference');
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($headings_array, 'Received Date');
        }
        if (!in_array('19',$not_visible_arr)) {
            array_push($headings_array, 'Delivery Date');
        }
        if (!in_array('20',$not_visible_arr)) {
            array_push($headings_array, 'Invoice Date');
        }
        if (!in_array('21',$not_visible_arr)) {
            array_push($headings_array, 'Due Date');
        }
        if (!in_array('22',$not_visible_arr)) {
            array_push($headings_array, 'Target Ship Date');
        }
        if (!in_array('23',$not_visible_arr)) {
            array_push($headings_array, 'Remark');
        }
        if (!in_array('24',$not_visible_arr)) {
            array_push($headings_array, 'Comment to Warehouse');
        }
        if (!in_array('25',$not_visible_arr)) {
            array_push($headings_array, 'Ref. PO#');
        }
        if (!in_array('26',$not_visible_arr)) {
            array_push($headings_array, 'Status');
        }

        return $headings_array;

    }

    // public function view(): View
    // {

    //     $query = $this->query;
    //     $not_visible_arr = $this->not_visible_arr;
    //     return view('users.exports.invoice-exp', ['query' => $query,'not_visible_arr' => $not_visible_arr]);
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // $event->sheet->getStyle('A1:V1')->applyFromArray([
                //     'font' => [
                //         'bold' => true
                //     ]
                // ]);

            },
        ];
    }


}
