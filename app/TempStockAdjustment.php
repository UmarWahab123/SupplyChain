<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Models\Common\Supplier;

class TempStockAdjustment extends Model
{
    protected $casts = [
        'incomplete_rows' => 'array',
    ];
    
    public static function returnAddColumn($column, $item,$suppliers,$customers)
    {

        switch ($column) {
            case 'PF#':
                return $item->product_id !== null ? $item->product_id : '--';
                break;
            case 'supplier_name':
                $supplierName = $item->incomplete_rows[15];
                $supplierExists = $suppliers->contains('reference_name', $supplierName);
                $html_string = '<div class="'.($supplierName == null || !$supplierExists  ? 'customer__dropdown' : '').'">';
                $html_string .= '<select class="stock-supplier-name " data-row-id="'.$item->id.'" name="supplier_name">';
                $html_string .= ' <option value="" selected="" disabled="">Choose Supplier</option>';
                if ($suppliers->count() > 0) {
                    foreach ($suppliers as $supplier) {
                        $isSelected = ($supplier->reference_name === $supplierName) ? 'selected' : '';
                        $html_string .= '<option value="' . $supplier->reference_name . '" ' . $isSelected . '>' . $supplier->reference_name . '</option>';
                    }
                }
                $html_string .= '</select></div>';
                

                if (($item->incomplete_rows[18] > 0 || $item->incomplete_rows[21] > 0 || $item->incomplete_rows[24] > 0) && !$supplierName) {
                    return $html_string; 
                }else if (!$supplierExists){
                    return $html_string;
                }else{
                    return $html_string; 
                }
                break;
            case 'customer_name':
                $customerName = $item->incomplete_rows[16];
                $customerExists = $customers->contains('reference_name', $customerName);
                $html_string = '<div class="'.($customerName == null || !$customerExists ? 'customer__dropdown' : '').'">';
                $html_string .= '<select style="border: 1px solid red;" data-row-id="'.$item->id.'" class="stock-customer-name" name="customer_name">';
                $html_string .= ' <option value="" selected="" disabled="">Choose Customer</option>';
                if ($customers->count() > 0) {
                    foreach ($customers as $customer) {
                        $isSelected = $customer->reference_name != null ? (($customer->reference_name === $customerName) ? 'selected' : '') : '';
                        $html_string .= '<option value="' . $customer->reference_name . '" ' . $isSelected . '>' . $customer->reference_name . '</option>';
                    }
                }
                $html_string .= '</select></div>';
                
                if (($item->incomplete_rows[18] < 0 || $item->incomplete_rows[21] < 0 || $item->incomplete_rows[24] < 0) && $customerName == null) {
                 return $html_string;
                }else if (!$customerExists){
                 return $html_string;
                }else{
                 return $html_string; 
                }
                break;
            case 'adjace1':
                return $item->incomplete_rows[18] !== null ? $item->incomplete_rows[18]: '--';
                break;

            case 'expiration_date1':
                return $item->incomplete_rows[19] !== null ? $item->incomplete_rows[19]: '--';
                break;
            case 'adjace2':
                return $item->incomplete_rows[21] !== null ? $item->incomplete_rows[21]: '--';
                break;
            case 'expiration_date2':
                return $item->incomplete_rows[22] !== null ? $item->incomplete_rows[22]: '--';
                break;
            case 'adjace3':
                return $item->incomplete_rows[24] !== null ? $item->incomplete_rows[24]: '--';
                break;
            case 'expiration_date3':
                return $item->incomplete_rows[25] !== null ? $item->incomplete_rows[25]: '--';
                break;
        }
    }
}
