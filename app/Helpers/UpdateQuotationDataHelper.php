<?php

namespace App\Helpers;


class UpdateQuotationDataHelper {
    
    public static function checkingDiscount($draft_quotation_product) {
      $unit_price_with_discount = $draft_quotation_product->unit_price_with_discount;
      if($draft_quotation_product->discount != null && $draft_quotation_product->discount != 0)
      {
        $unit_price_with_discount = $draft_quotation_product->unit_price * (100 - $draft_quotation_product->discount)/100;
      }
      else
      {
        $unit_price_with_discount = $draft_quotation_product->unit_price;
      }
    }

    public static function checkingdiscount2($unit_price_with_vat) {
      if(@$discount !== null)
      {
        $percent_value = $discount / 100;
        $dis_value = $unit_price_with_vat * $percent_value;
        $tpwt = $unit_price_with_vat - @$dis_value;

        $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
        $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
        $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
      }
      else
      {
        $tpwt = $unit_price_with_vat;
      }
    }

    private function checkingVat($draft_quotation_product, $item_unit_price,$result,$value,$total_price,$vat_amount_total_over_item) {
      $draft_quotation_product->total_price = number_format($result,2,'.','');
      $vat = $draft_quotation_product->vat;
      $vat_amountt = @$item_unit_price * ( @$vat / 100 );
      $vat_amount = number_format($vat_amountt,4,'.','');
      $vat_amount_total_over_item = $vat_amount * $value;
      $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');

      if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
      {
        $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
      }
      else
      {
        $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
      }

      return $unit_price_with_vat;
    }


    public static function UpdatingDiscountAndVat($draft_quotation_product,$item_unit_price,$value,$vat_amount_total_over_item) {
      if($draft_quotation_product->product_id == null )
                {
                  $total_price = $item_unit_price*$value;
                  $discount = $draft_quotation_product->discount;

                 if($discount != null){
                  $dis = $discount / 100;
                     $discount_value = $dis * $total_price;
                      $result = $total_price - $discount_value;
                  }else{
                    $result = $total_price;
                  }

                  $unit_price_with_vat = (new UpdateQuotationDataHelper)->checkingVat($draft_quotation_product, $item_unit_price,$result,$value,$total_price,$vat_amount_total_over_item);

                  (new UpdateQuotationDataHelper)->checkingdiscount2($unit_price_with_vat);

                $draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
              }
              else
              {
                  $total_price = $item_unit_price*$value;
                 $discount = $draft_quotation_product->discount;
                if($discount != null){
                  $dis = $discount / 100;
                     $discount_value = $dis * $total_price;
                      $result = $total_price - $discount_value;
                }else{
                  $result = $total_price;
                }

                $unit_price_with_vat = (new UpdateQuotationDataHelper)->checkingVat($draft_quotation_product, $item_unit_price,$result,$value,$total_price,$vat_amount_total_over_item);

                (new UpdateQuotationDataHelper)->checkingdiscount2($unit_price_with_vat);

                $draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
              }
              
    }

}