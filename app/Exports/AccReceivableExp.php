<?php

namespace App\Exports;

// use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Carbon\Carbon;

class AccReceivableExp implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{

	protected $query = null;
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($query)
    {
        $this->query = $query;
    }

    // public function view(): View
    // {
        
    // 	$query = $this->query;
    // 	// dd($query);
    //     return view('users.exports.acc-receivable-exp', ['query' => $query]);
    // }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function headings() : array
    {
       return [
            'Invoice Date ',
            'Delivery Date',
            'Invoice',
            'Sales Person',
            'Company Name',
            'Reference Name',
            'Inv.#',
            'VAT Inv (-1)',
            'VAT',
            'Inv.#',
            'Non VAT  Inv (-2)',
            'Sub Total',
            'Order Total',
            'Total Amount Paid',
            'Total Amount Due',
            'Payment Due Date'
       ];
    }

    public function map($item) : array
    {
        if($item->primary_status == 3)
        {
          if($item->in_status_prefix !== null || $item->in_ref_prefix !== null){
            $ref_no = @$item->in_status_prefix."-".$item->in_ref_prefix.$item->in_ref_id;
          }
          else{
            $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
          }
        }
        else
        {
          if($item->status_prefix !== null || $item->ref_prefix !== null){
            $ref_no = @$item->in_status_prefix."-".$item->in_ref_prefix.$item->in_ref_id;
          }
          else{
            $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id;
          }
        }

        if(@$item->in_status_prefix !== null)
        {
          $ref_id_vat = $item->in_status_prefix."-".@$item->in_ref_prefix.$item->in_ref_id."-1";
        }
        else
        {
          $ref_id_vat = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id."-1";
        }

        if(@$item->in_status_prefix !== null)
        {
          $reference_id_vat_2 = $item->in_status_prefix."-".@$item->in_ref_prefix.$item->in_ref_id."-2";
        }
        else
        {
          $reference_id_vat_2 = @$item->customer->primary_sale_person->get_warehouse->order_short_code.@$item->customer->CustomerCategory->short_code.@$item->ref_id."-2";
        }
        $amount_paid = $item->get_order_transactions->sum("total_received");
        $amount_due = $item->total_amount-$amount_paid;

        return [
            $item->converted_to_invoice_on != NULL ? Carbon::parse($item->converted_to_invoice_on)->format("d/m/Y") : "N.A",
            $item->delivery_request_date != NULL ? Carbon::parse($item->delivery_request_date)->format("d/m/Y") : "N.A",
            $ref_no,
            $item->user != null ? @$item->user->name : "N.A",
            $item->customer !== null ? $item->customer->company : "N.A",
            $item->customer !== null ? $item->customer->reference_name : "N.A",
            $ref_id_vat,
            @$item->order_products != null ? round((@$item->getOrderTotalVatAccounting($item->id,0) - @$item->getOrderTotalVatAccounting($item->id,1)),2) : '--',
            @$item->order_products != null ? @$item->getOrderTotalVatAccounting($item->id,1) : '--',
            @$reference_id_vat_2,
            @$item->order_products != null ? @$item->getOrderTotalVatAccounting($item->id,2) : '--',
            $item->order_products != null ? round($item->order_products->sum('total_price'),2) : "N.A",
            $item->total_amount != null ? round($item->total_amount,2) : "N.A",
            round(@$amount_paid,2),
            round(@$amount_due,2),
            $item->payment_due_date != NULL ? Carbon::parse($item->payment_due_date)->format("d/m/Y") : "N.A"
        ];
    }
    
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
