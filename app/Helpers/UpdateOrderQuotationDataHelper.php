<?php

namespace App\Helpers;

class UpdateOrderQuotationDataHelper {
    
    public static function checking_discount_and_vat($discount, $total_price, $order_product, $value) {
        // dd($order_product);
        if($discount != null)
        {
          $dis = $discount / 100;
          $discount_value = $dis * $total_price;
          $result = $total_price - $discount_value;
        }
        else
        {
          $result = $total_price;
        }
        $order_product->total_price = round($result,2);
        $vat = $order_product->vat;
        $vat_amountt = @$item_unit_price * ( @$vat / 100 );
        $vat_amount = number_format($vat_amountt,4,'.','');
        $vat_amount_total_over_item = $vat_amount * $value;
        $order_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
        if($order_product->vat !== null && $order_product->unit_price_with_vat !== null)
        {
          $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
        }
        else
        {
          $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
        }
        if(@$discount !== null)
        {
          $percent_value = $discount / 100;
          $dis_value = $unit_price_with_vat * $percent_value;
          $tpwt = $unit_price_with_vat - @$dis_value;

          $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
          $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
          $order_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
        }
        else
        {
          $tpwt = $unit_price_with_vat;
        }

        // return $discount;
    }

}