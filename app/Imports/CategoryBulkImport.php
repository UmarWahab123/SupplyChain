<?php

namespace App\Imports;
use Auth;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\ProductCategory;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class CategoryBulkImport implements ToCollection ,WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {

        /*
        These are the rows of bulk categories and thier indexes
        $row[0] == category
        $row[1] == sub_category
        $row[2] == hs_code
        $row[3] == import_tax_book
        $row[4] == vat
        $row[5] == prefix
        $row[6] == resturant
        $row[7] == hotel
        $row[8] == retail
        $row[9] == private
        $row[10] == catering
        */
        if ($rows->count() > 1) {
            $row1 = $rows->toArray();
            $remove = array_shift($row1);
            foreach ($row1 as $row) {
                if ($row->filter()->isNotEmpty()) {
                    if ($row[0] != null) {
                        $checkIfExist = ProductCategory::where('title', $row[0])->where('parent_id', 0)->count();
                        if ($checkIfExist == 0) {
                            $product_cat = new ProductCategory();
                            $product_cat->parent_id = 0;
                            $product_cat->title     = $row[0];
                            $product_cat->save();

                            $getParentId = ProductCategory::find($product_cat->id);

                            $product_cat_sub = new ProductCategory;
                            $product_cat_sub->parent_id       = $getParentId->id;
                            $product_cat_sub->title           = $row[1];
                            $product_cat_sub->hs_code         = $row[2];
                            $product_cat_sub->import_tax_book = $row[3];
                            $product_cat_sub->vat             = $row[4];
                            $product_cat_sub->prefix          = $row[5];
                            $product_cat_sub->save();

                            $recentAddSub = ProductCategory::find($product_cat_sub->id);

                            $custCats = CustomerCategory::where('is_deleted',0)->orderBy('id', 'ASC')->get();

                            // $i is for row indexing
                            // $ct_id for customer type categories
                            // $ct_id = 1;
                            // for($i=6; $i<=10; $i++)
                            // {
                            $i = 6;
                            foreach ($custCats as $cat) {
                                $customerTypeCatMargin = new CustomerTypeCategoryMargin;
                                $customerTypeCatMargin->category_id      = $recentAddSub->id;
                                $customerTypeCatMargin->customer_type_id = $cat->id;
                                $customerTypeCatMargin->default_margin   = "Percentage";
                                $customerTypeCatMargin->default_value    = $row[$i];
                                $customerTypeCatMargin->save();
                                $i++;
                            }
                            // }
                        } else {
                            $getParentIdExist = ProductCategory::where('title', $row[0])->where('parent_id', 0)->first();
                            // check if their child exist with same name of $row[0]
                            $checkChild = ProductCategory::where('parent_id', $getParentIdExist->id)->where('title', $row[1])->first();
                            if ($checkChild == null) {
                                $product_cat_sub = new ProductCategory;
                                $product_cat_sub->parent_id = $getParentIdExist->id;
                                $product_cat_sub->title           = $row[1];
                                $product_cat_sub->hs_code         = $row[2];
                                $product_cat_sub->import_tax_book = $row[3];
                                $product_cat_sub->vat             = $row[4];
                                $product_cat_sub->prefix          = $row[5];
                                $product_cat_sub->save();
                                $recentAddSub = ProductCategory::find($product_cat_sub->id);
                            } else {
                                $overwriteChild = ProductCategory::find($checkChild->id);
                                $overwriteChild->parent_id       = $getParentIdExist->id;
                                $overwriteChild->title           = $row[1];
                                $overwriteChild->hs_code         = $row[2];
                                $overwriteChild->import_tax_book = $row[3];
                                $overwriteChild->vat             = $row[4];
                                $overwriteChild->prefix          = $row[5];
                                $overwriteChild->save();
                                $recentAddSub = ProductCategory::find($overwriteChild->id);
                            }

                            // check if category margins already exist or not
                            $custCats = CustomerCategory::where('is_deleted',0)->orderBy('id', 'ASC')->get();
                            $i = 6;
                            foreach ($custCats as $cat) {
                                $checkMarginSats = CustomerTypeCategoryMargin::where('category_id', $recentAddSub->id)->where('customer_type_id', $cat->id)->first();

                                if ($checkMarginSats) {
                                    $checkMarginSats->default_value    = $row[$i];
                                    $checkMarginSats->save();
                                } else {
                                    $customerTypeCatMargin = new CustomerTypeCategoryMargin;
                                    $customerTypeCatMargin->category_id      = $recentAddSub->id;
                                    $customerTypeCatMargin->customer_type_id = $cat->id;
                                    $customerTypeCatMargin->default_margin   = "Percentage";
                                    $customerTypeCatMargin->default_value    = $row[$i];
                                    $customerTypeCatMargin->save();
                                }
                                $i++;
                            }
                        }
                    }
                }
            }
        }
        else{
            return redirect()->route('product-categories-list')->with('errorMsg','File is Empty Please Upload Valid File!');
        }

    }

    public function startRow():int
    {
        return 1;
    }
}
