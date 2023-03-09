<?php

namespace App\Http\Controllers\Backend;

use App\Variable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;

class VariableController extends Controller
{
    public function index()
    {
        $sections = Variable::select('section')->groupBy('section')->get();
        return view('backend.variables.index',compact('sections'));
    }

    public function getData(Request $request)
    {
        $query = Variable::all();
        if($request->section_filter != null)
        {
            $query = $query->where('section',$request->section_filter);
        }
        return Datatables::of($query)
        ->addColumn('action', function ($item) {
            $html_string = '<div class="icons">'.'
                <!-- <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a> -->
                    <a href="javascript:void(0);" class="actionicon deleteIcon delete-variable" data-id="'.$item->id.'" data-variable_name="'.$item->slug.'" title="Delete"><i class="fa fa-ban"></i></a>
                </div>';
            return $html_string;
        })
        ->addColumn('section', function ($item) {

            return $item->section;
            
        })
        ->addColumn('slug', function ($item) {
            
            if($item->page!=null)
            return $item->page;
            return ucwords(str_replace("_"," ",$item->slug));
            
        })
        ->editColumn('terminology', function ($item) {
            $html_string = '
            <span class="m-l-15 inputDoubleClick" id="terminology"  data-fieldvalue="'.@$item->terminology.'">';
            $html_string .= $item->terminology != NULL ? $item->terminology : "--";
            $html_string .= '</span>';

            $html_string .= '<input type="text" style="width:80%;" name="terminology" class="fieldFocus d-none" value="'.$item->terminology .'">';
            return $html_string;
        })
        ->addColumn('page', function ($item) {
            return $item->standard_name;
        })
        ->setRowId(function ($item) {
            return $item->id;
        })
        ->rawColumns(['action','slug','terminology'])
        ->make(true);
    }

    public function edit(Request $request)
    {
        $variable =  Variable::find($request->t_id);
        
        foreach ($request->except('t_id', 'old_value') as $key => $value) 
        {
            $variable->terminology = $value;
            $variable->save();
        }
        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $variable =  Variable::find($request->id);
        $variable->forceDelete();

        return response()->json(['success' => true]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'slug' => 'required|unique:variables',
        ]);

        $input = $request->all();
        $variable = Variable::create($input);
        return response()->json(['success' => true]);
    }
}
