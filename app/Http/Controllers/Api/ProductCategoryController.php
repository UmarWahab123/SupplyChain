<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\ProductCategory;

class ProductCategoryController extends Controller
{
    public function getCategoriesThrougApi()
    {
      $categories = ProductCategory::all();
      return response()->json(['success' => true, 'categories' => $categories]);
    }
}
