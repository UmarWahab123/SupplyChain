<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;

class receivedIntoStockExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithEvents
{
    protected $query = null;
    protected $global_terminologies = null;
    protected $blade = null;

    public function __construct($query, $global_terminologies, $blade)
    {
        $this->query = $query;
        $this->global_terminologies = $global_terminologies;
        $this->blade = $blade;
    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array
    {
        $blade = $this->blade;
        $item_id = '--';
        if($item->ref_id !== null){
            $item_id = $item->ref_id;
        }

        $group = $item->p_o_group != NULL ? $item->p_o_group->po_group->ref_id  : "N.A";

        $supplier_id = $item->supplier_id !== null ? @$item->PoSupplier->reference_name : @$item->PoWarehouse->warehouse_title;

        $supplier_ref = $item->supplier_id !== null ? $item->PoSupplier->reference_number : '--';

        $date = $item->confirm_date !== null ? Carbon::parse($item->confirm_date)->format('d/m/Y') : '--';

        $po_total = $item->total !== null ? number_format($item->total,3,'.',',') : '--';

        $target_receive_date = $item->target_receive_date !== null ? Carbon::parse($item->target_receive_date)->format('d/m/Y') : '--';
        $invoice_date = $item->invoice_date !== null ? Carbon::parse($item->invoice_date)->format('d/m/Y') : '--';

        $payment_due_date = $item->payment_due_date !== null ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : '--';

        $invoice_number = $item->invoice_number !== null ? $item->invoice_number : '---';

         if($item->po_notes->count() > 0)
         {
            $note = $item->po_notes->first()->note;
        }
        else
        {
            $note = '---';
        }

        $getCust = PurchaseOrderDetail::with('customer')->where('order_id', '!=', NULL)->where('po_id',$item->id)->get()->groupBy('customer_id');
                $html_string_customer = '';

                if($getCust->count() > 1)
                {
                    $customers = '';
                    $i = 0;
                    foreach ($getCust as $cust) {
                        if ($i < 3) {
                            $customers .= $cust[0]->customer->reference_name . ',';
                        }
                        else{
                            break;
                        }
                        $i++;
                    }
                    $html_string_customer .=  $customers;
                }
                elseif($getCust->count() == 1)
                {
                    foreach ($getCust as $value)
                    {
                        if($value != Null)
                        {
                            $html_string_customer = @$value[0]->customer->reference_name;
                        }
                    }
                }
                elseif($getCust->count() == 0)
                {
                    $html_string_customer = "--";
                }


        $to_warehouse_id = $item->to_warehouse_id !== null ? $item->ToWarehouse->warehouse_title : '--';

        if($item->exchange_rate !== null && $item->exchange_rate != 0)
        {
        $exchange_rate = (1 / $item->exchange_rate);
        $exchange_rate = number_format($exchange_rate,4,'.',',');
        }
        else
        {
        $exchange_rate = '--';
        }

        $data_array = [];
        array_push($data_array, $item_id);
        if ($blade == 'received_into_stock' || $blade == 'dispatch_from_supplier')
        {
            array_push($data_array, $group);
        }
        array_push($data_array, $supplier_id);
        array_push($data_array, $invoice_number);
        array_push($data_array, $exchange_rate);
        array_push($data_array, $html_string_customer);
        array_push($data_array, $date);
        array_push($data_array, $po_total);
        array_push($data_array, $invoice_date);
        array_push($data_array, $target_receive_date);
        array_push($data_array, $payment_due_date);
        array_push($data_array, $note);
        array_push($data_array, $to_warehouse_id);
        return $data_array;
    }

    public function headings(): array
    {
        $global_terminologies = $this->global_terminologies;
        $blade = $this->blade;

        $headings_array = [];
        array_push($headings_array, 'PO #');
        if ($blade == 'received_into_stock' || $blade == 'dispatch_from_supplier')
        {
            array_push($headings_array, 'Group #');
        }
        array_push($headings_array, $global_terminologies['supply_from']);
        array_push($headings_array, 'Supplier Invoice Number');
        array_push($headings_array, 'Exchange Rate');
        array_push($headings_array, 'Customers');
        array_push($headings_array, 'Confirm Date');
        array_push($headings_array, 'PO Total');
        array_push($headings_array, 'Invoice Date');
        array_push($headings_array, 'Target Received Date');
        array_push($headings_array, $global_terminologies['payment_due_date']);
        array_push($headings_array, 'Note');
        array_push($headings_array, 'To Warehouse');
        return $headings_array;
    }


    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:S1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

            },
        ];
    }
}
