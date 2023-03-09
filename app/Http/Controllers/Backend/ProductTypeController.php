<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Common\ProductType;
use App\Models\Common\ProductSecondaryType;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use App\ProductTypeTertiary;

class ProductTypeController extends Controller
{
    public function index(){
    	// dd('hello');
    	return $this->render('backend.productType.index');
    }

    public function getData()
    {
        $query = ProductType::all();

        return Datatables::of($query)


        ->addColumn('action', function ($item) {
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>
                      </div>';
            return $html_string;
            })
        ->addColumn('created_at', function ($item) {
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
            })
        ->addColumn('updated_at', function ($item) {
            return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';
            })
        	 ->setRowId(function ($item) {
             return $item->id;
         })
            ->rawColumns(['action','updated_at','created_at'])
            ->make(true);
    }
    public function getSecondaryTypes()
    {
        $query = ProductSecondaryType::orderBy('title','asc');

        return Datatables::of($query)


        ->addColumn('action', function ($item) {
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon-secondary" title="Edit"><i class="fa fa-pencil"></i></a>
                      </div>';
            return $html_string;
            })
        ->addColumn('created_at', function ($item) {
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
            })
        ->addColumn('updated_at', function ($item) {
            return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';
            })
             ->setRowId(function ($item) {
             return $item->id;
         })
            ->rawColumns(['action','updated_at','created_at'])
            ->make(true);
    }

    public function getTertiaryTypes() {
        $query = ProductTypeTertiary::orderBy('title','asc');

        return Datatables::of($query)


        ->addColumn('action', function ($item) {
            $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon-tertiary" title="Edit"><i class="fa fa-pencil"></i></a>
                      </div>';
            return $html_string;
            })
        ->addColumn('created_at', function ($item) {
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
            })
        ->addColumn('updated_at', function ($item) {
            return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';
            })
             ->setRowId(function ($item) {
             return $item->id;
         })
            ->rawColumns(['action','updated_at','created_at'])
            ->make(true);
    }

    public function add(Request $request){

    	$validator = $request->validate([
    		'title' => 'required|unique:types,title',
        ]);

    	$productType = new ProductType;
    	$productType->title = $request->title;
    	$productType->save();

    	return response()->json(['success' => true]);

    }

    public function addSecondaryType(Request $request){

        $validator = $request->validate([
            'title' => 'required|unique:types,title',
        ]);

        $productType = new ProductSecondaryType;
        $productType->title = $request->title;
        $productType->save();

        return response()->json(['success' => true]);

    }

    public function addProductType3(Request $request) {
        $validator = $request->validate([
            'title' => 'required|unique:types,title',
        ]);

        $productType3 = new ProductTypeTertiary;
        $productType3->title = $request->title;
        $productType3->save();

        return response()->json(['success' => true]);
    }

    public function edit(Request $request){
        // dd($request->all());
        $validator = $request->validate([
            'title' => 'required|unique:types',
        ]);
        if($request->check_secondary == "true")
        {
           $productType = ProductSecondaryType::find($request->editid);
           $productType->title = $request['title'];
           $productType->save();
           return response()->json(['success' => true,'secondary' => true]);
        } elseif($request->check_tertiary == "true") {
            $productType = ProductTypeTertiary::find($request->editid);
            $productType->title = $request['title'];
            $productType->save();
            return response()->json(['success' => true,'tertiary' => true]);
        } else {
            $productType = ProductType::find($request->editid);
            $productType->title = $request['title'];
            $productType->save();
            return response()->json(['success' => true, 'secondary' => false]);
        }

     }
}
