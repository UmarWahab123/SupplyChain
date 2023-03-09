<?php

namespace App\Models\WooCom;

use Illuminate\Database\Eloquent\Model;
use App\Models\Common\ProductCategory;
use App\Models\WooCom\WebEcomProductCategory;

class WebEcomProductCategory extends Model
{
    public function addCat($id)
    {
        $category = ProductCategory::find($id);
            if($category->parent_id != 0)
            {
                $sub = WebEcomProductCategory::where('web_category_id',$category->parent_id)->orderBy('id','desc')->first();
                if($sub)
                {
                    $parent_id = $sub->ecom_category_id;
                }
                else
                {
                    $data['success'] = false;
                    return $data;
                }
            }
            else
            {
                $parent_id = 0;
            }

            $data = [
                'name' => $category->title,
                'parent' => $parent_id
            ];

            $check_cat = \Codexshaper\WooCommerce\Facades\Category::where('name',$category->title)->first();
            // dd($check_cat);
            if(count($check_cat) == 0)
            {
                $category = \Codexshaper\WooCommerce\Facades\Category::create($data);

                if($category)
                {
                    $new_cat = new WebEcomProductCategory;
                    $new_cat->web_category_id = $id;
                    $new_cat->ecom_category_id = $category['id'];
                    // $new_cat->type      /       = 'Wordpress';
                    $new_cat->save();
                }
            }
            else
            {
                $check_cat_2 = WebEcomProductCategory::where('web_category_id',$id)->first();
                if($check_cat_2 == null)
                {
                    $new_cat = new WebEcomProductCategory;
                    $new_cat->web_category_id = $id;
                    $new_cat->ecom_category_id = $check_cat['id'];
                    // $new_cat->type             = 'Wordpress';
                    $new_cat->save();
                }
            }
            
            $data['success'] = true;
            return $data;
    }
}
