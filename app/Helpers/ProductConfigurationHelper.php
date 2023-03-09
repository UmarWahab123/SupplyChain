<?php

namespace App\Helpers;

use App\QuotationConfig;

class ProductConfigurationHelper
{
    public static function getProductConfigurations()
    {
        $product_detail_page = QuotationConfig::where('section', 'product_detail_page')->first();
        // dd($product_detail_page);
        $product_detail_section = [];
        if ($product_detail_page) {
            $globalaccessForConfig = unserialize($product_detail_page->print_prefrences);
            // dd($globalaccessForConfig);
            foreach ($globalaccessForConfig as $key => $value) {
                if($value['status'] == 1) {
                    array_push($product_detail_section, $value['slug']);
                }
            }
        }

        return $product_detail_section;
    }
}
