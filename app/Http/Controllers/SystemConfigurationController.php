<?php

namespace App\Http\Controllers;

use App\Helpers\SyetemConfigurations\SystemConfigurationHelper;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\SystemConfiguration;


class SystemConfigurationController extends Controller
{
    public function index()
    {
        return view('users.systemconfigurations.index');
    }

    public function create()
    {
        return view('users.systemconfigurations.create');
    }

    public function store(Request $request)
    {
        $conf = SystemConfigurationHelper::store($request);
        // return response()->json(['success'=>true]);
        if($conf)
        {
    		return redirect()->route('system-configurations')->with(['success'=>true], 'successmsg', 'New System Configuration added successfully');
        }
    }

    public function getSystemConfigurations()
    {
        $query = SystemConfiguration::with('users:id,user_name,name')->get();
        return Datatables::of($query)
            ->addColumn('action', function ($item) {
                $html_string = '<div class="icons">'.'
                              <a href="'.route('edit-system-configurations', ['id' => $item->id]).'" class="actionicon tickIcon"><i class="fa fa-pencil"></i></a>
                              <a href="'.route('delete-system-configurations', ['id' => $item->id]).'" class="actionicon deleteIcon"><i class="fa fa-ban"></i></a>
                          </div>';
                return $html_string;
                })
            ->addColumn('created_at', function ($item) {
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';
            })

        ->addColumn('updated_at', function ($item) {
        return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';
        })
                ->rawColumns(['content', 'action','created_at','updated_at'])
                    ->make(true);
    }

    public function editSystemConfigurations($id)
    {
        $system_conf = SystemConfigurationHelper::edit($id);
        return view('users.systemconfigurations.edit',compact('system_conf'));
    }

    public function updateSystemConfiguration(Request $request,$id)
    {
        $system_conf = SystemConfigurationHelper::updateConfiguration($request,$id);
        // return response()->json(['success'=>true]);
        if($system_conf)
        {
    		return redirect()->route('system-configurations')->with('successmsg', 'System Configuration updated successfully');
        }
    }

    public function deleteSystemConfigurations($id)
    {
        $system_configuration = SystemConfigurationHelper::deleteConfiguration($id);
        // return response()->json(['success'=>true]);

        if($system_configuration)
        {
            return redirect()->route('system-configurations')->with('successmsg', 'System Configuration Deleted successfully');
        }
    }
}
