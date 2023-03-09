<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class AccountPayableTableExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithEvents
{

    protected $query = null;
    protected $not_visible_arr = null;

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

        if($item->target_receive_date !== null) {
            $target_ship_date = Carbon::parse($item->target_receive_date)->format('d/m/Y');
        } else {
            $target_ship_date = '--';
        }

        if($item->ref_id !== null) {
            $po = $item->ref_id;
        } else {
            $po = '--';
        }

        if($item->created_at !== null) {
            $created_at = $item->created_at;
        } else {
            $created_at = '--';
        }

        if($item->invoice_date !== null) {
            $invoice_date = $item->invoice_date;
        } else {
            $invoice_date = '--';
        }

        if($item->supplier_id !== null) {
            $supplier_id = $item->PoSupplier->reference_name;
        } else {
            $supplier_id = $item->PoWarehouse->warehouse_title;
            // $supplier_id = '--';
        }

        if($item->invoice_number !== null) {
            $invoice_number = $item->invoice_number;
        } else {
            $invoice_number = '--';
        }

        if($item->memo !== null) {
            $memo = $item->memo;
        } else {
            $memo = '--';
        }

       
        if($item->PoSupplier !== null) {
            $supplier_currency = $item->PoSupplier->getCurrency->currency_code;
        } else {
            $supplier_currency = '--';
        }

        

        if($item->payment_terms_id !== null) {
            $payment_terms = $item->pOpaymentTerm->title;
            // $payment_terms = '--';
        } else {
            $payment_terms = '--';
        }

        if($item->payment_due_date !== null) {
            $payment_due_date = Carbon::parse($item->payment_due_date)->format('d/m/Y');
        } else {
            $payment_due_date = '--';
        }

        if($item->total !== null) {
            $po_total = number_format($item->total,2,'.',',');
        } else {
            $po_total = '--';
        }

        if($item->total_with_vat !== null) {
            $total_with_vat = number_format($item->total_with_vat,2,'.',',');
        } else {
            $total_with_vat = '--';
        }

        if($item->exchange_rate !== null && $item->exchange_rate !== 00) {
            $payment_exchange_rate =  number_format((1 / $item->exchange_rate),2,'.',',');
        } else {
            $payment_exchange_rate = '--';
        }


        if($item->payment_exchange_rate != null && $item->payment_exchange_rate != 0)
        {
          $exchange = (1 / $item->payment_exchange_rate);
        }
        else
        {
          $exchange = 0;
        }



        if($item->total_in_thb !== null) {
            $total_in_thb = number_format($item->total_in_thb,2,'.',',');
        } else {
            $total_in_thb = '--';
        }


        if($item->purchaseOrderTransaction !== null) {
            $amount_paid = $item->purchaseOrderTransaction->sum('total_received');
        } else {
            $amount_paid = 0;
        }
        

        if($item->payment_exchange_rate !== null && $item->payment_exchange_rate != 0) {
            $total = (1 / @$item->payment_exchange_rate) * $item->total;
        } else {
            $total = 0 ;
        }
        $received = number_format($total,2,'.','');


        if($item->payment_exchange_rate !== null && $item->payment_exchange_rate != 0) {
          $total = (1 / @$item->payment_exchange_rate) * $item->total;
        } else {
          $total = 0 ;
        }
        $difference = number_format(($item->total_in_thb - $total),2,'.',',');


        if (!in_array('1',$not_visible_arr)) {
            array_push($data_array, $target_ship_date);
        }
        if (!in_array('2',$not_visible_arr)) {
            array_push($data_array, $po);
        }
        if (!in_array('3',$not_visible_arr)) {
            array_push($data_array, $created_at);
        }
        if (!in_array('4',$not_visible_arr)) {
            array_push($data_array, $invoice_date);
        }
        if (!in_array('5',$not_visible_arr)) {
            array_push($data_array, $supplier_id);
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($data_array, $invoice_number);
        }
        if (!in_array('7',$not_visible_arr)) {
            array_push($data_array, $memo);
        }
        if (!in_array('8',$not_visible_arr)) {
            array_push($data_array, $supplier_currency);
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($data_array, $payment_terms);
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($data_array, $payment_due_date);
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($data_array, $po_total);
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($data_array, $total_with_vat);
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($data_array, $payment_exchange_rate);
        }
        if (!in_array('14',$not_visible_arr)) {
            array_push($data_array, $total_in_thb);
        }
        if (!in_array('15',$not_visible_arr)) {
            array_push($data_array, $amount_paid);
        }
        if (!in_array('16',$not_visible_arr)) {
            array_push($data_array, $exchange);
        }
        if (!in_array('17',$not_visible_arr)) {
            array_push($data_array, $received);
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($data_array, $difference);
        }


        return $data_array;
        

    }




    public function headings(): array {
        $not_visible_arr = $this->not_visible_arr;

        $headings_array = [];
        if (!in_array('1',$not_visible_arr)) {
            array_push($headings_array, 'Target Ship Date');
        }
        if (!in_array('2',$not_visible_arr)) {
            array_push($headings_array, 'PO #');
        }
        if (!in_array('3',$not_visible_arr)) {
            array_push($headings_array, 'PO Date');
        }
        if (!in_array('4',$not_visible_arr)) {
            array_push($headings_array, 'Invoice Date');
        }
        if (!in_array('5',$not_visible_arr)) {
            array_push($headings_array, 'Supplier #');
        }
        if (!in_array('6',$not_visible_arr)) {
            array_push($headings_array, 'Supplier Invoice Number');
        }
        if (!in_array('7',$not_visible_arr)) {
            array_push($headings_array, 'Memo');
        }
        if (!in_array('8',$not_visible_arr)) {
            array_push($headings_array, 'Supplier Currency');
        }
        if (!in_array('9',$not_visible_arr)) {
            array_push($headings_array, 'Payment Term');
        }
        if (!in_array('10',$not_visible_arr)) {
            array_push($headings_array, 'Payment Due Date');
        }
        if (!in_array('11',$not_visible_arr)) {
            array_push($headings_array, 'Total Amount (W/O VAT)');
        }
        if (!in_array('12',$not_visible_arr)) {
            array_push($headings_array, 'Invoice Exg rate');
        }
        if (!in_array('13',$not_visible_arr)) {
            array_push($headings_array, 'Posted Amount');
        }
        if (!in_array('14',$not_visible_arr)) {
            array_push($headings_array, 'Payment Reference');
        }
        if (!in_array('15',$not_visible_arr)) {
            array_push($headings_array, 'Paid Amount');
        }
        if (!in_array('16',$not_visible_arr)) {
            array_push($headings_array, 'Payment Exchange Rate');
        }
        if (!in_array('17',$not_visible_arr)) {
            array_push($headings_array, 'TotalPayment');
        }
        if (!in_array('18',$not_visible_arr)) {
            array_push($headings_array, 'Difference');
        }

        return $headings_array;
        
    }



    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
               

            },
        ];
    }


}
