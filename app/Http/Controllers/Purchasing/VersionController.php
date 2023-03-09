<?php

namespace App\Http\Controllers\Purchasing;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Version;
use Yajra\Datatables\Datatables;
use Auth;

class VersionController extends Controller
{
    public function index()
    {
        $base_link = config('app.version_server');
        $base_key  = config('app.version_api_key');
        if($base_link != null && $base_key != null)
        {
            /*for current version*/
            $uri = $base_link."api/current/".$base_key;
            $curl = curl_init($uri);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($curl);
            curl_close($curl);
            if($response)
            {
                $version_detail = json_decode($response);
            }
            /*for current version*/

            /*for all version*/
            $uri = $base_link."api/all/".$base_key;
            $curl = curl_init($uri);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($curl);
            curl_close($curl);
            if($response)
            {
                $versions = json_decode($response);
            }
            /*for all version*/
        }

        return view('users.version.general-view',compact('versions','version_detail'));
    }
    public function viewVersions()
    {
        return view('users.version.index');
    }
    public function getVersionData()
    {
        $base_link = config('app.version_server');
        $base_key  = config('app.version_api_key');
        if($base_link != null && $base_key != null)
        {
            /*for all version*/
            $uri = $base_link."api/all/".$base_key;
            $curl = curl_init($uri);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($curl);
            curl_close($curl);

            if($response != "No project found.")
            {
                $query = json_decode($response);
            }
            else
            {
                $query = array();
            }
            /*for all version*/
        }
        else
        {
            $query = array();
        }

        // $query = Version::all();

        return Datatables::of($query)
            
        ->addColumn('action', function ($item) { 
            $html_string = '<div class="icons">'.'
                  <a href="'.url('view-version-detail/'.$item->id).'"  class="actionicon tickIcon view-icon"><i class="fa fa-eye"></i></a> 
                </div>';
                  // <a href="'.url('edit-version/'.$item->id).'" class="actionicon tickIcon edit-icon"><i class="fa fa-pencil"></i></a> 
                  // <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-version"><i class="fa fa-trash"></i></a>
            return $html_string;         
        })
        ->addColumn('version', function ($item) { 
            return $item->version;         
        })
            
        ->addColumn('status', function ($item) { 
            if ($item->is_publish != 0) {
				$html_string='<span  class="badge badge-success"> Published</span>';
			}
			else
			{
            $html_string='<a  href="javascript:void(0);" class="publish-version btn-version" data-id="'.$item->id.'" ><span  class="badge badge-info ">Not Published</span></a>';
			}
			
			return $html_string;       
        })
        	 
            ->rawColumns(['action','version','status'])
            ->make(true);
    }

    public function createVersion()
    {
        return view('users.version.create-version');
    }

    public function deleteVersion(Request $request)
    {
        $version = Version::find($request->id);
        $version->delete();
        return response()->json(['error' => false, 'successmsg' => 'Version has been deleted']);
    }

    public function addVersion(Request $request)
    {
        $all_versions=Version::where('version',$request->version)->count();
    	if ($all_versions != 0) {
    		return redirect()->route('add-version')->with('error', 'Sorry this version Already Exist.');
    	}
    	else
    	{
            $version = new Version;
            $version->version = $request->version;
            $version->title = $request->title;
            $version->feature = $request->features;
            $version->bugfix = $request->bugfixes;
            $version->is_publish = 0;
            $version->role_id = Auth::user()->role_id;
            $version->save();
            return redirect('/view-version')->with('success', 'Version Add sucessfully.');
        }
    }

    public function publishVersion(Request $request)
    {
        $version=Version::find($request->id);
    	$version->is_publish=1;
        $version->save();
        return response()->json(['error' => false, 'successmsg' => 'Version has been Published']);
    }

    public function editVersion($id)
    {
        $version = Version::find($id);
        return $this->render('users.version.edit-version',compact('version'));
    }

    public function updateVersion(Request $request)
    {
        $all_versions=Version::where('version',$request->version)->where('id','!=',$request->id)->count();
    	if ($all_versions != 0) {
            // return redirect('/admin/edit-version/'.$request->id)->with('error', 'Sorry this version Already Exist.');
            return redirect()->route('edit-version',$request->id)->with('error', 'Sorry this version Already Exist.');
    	}
    	else
    	{
    		$version=Version::find($request->id);
    		$version->version = $request->version;
            $version->title = $request->title;
            $version->feature = $request->features;
            $version->bugfix = $request->bugfixes;
    		$version->save();
    		return redirect('/view-version')->with('success', 'Version Updated sucessfully.');
    	}
    }

    public function viewVersion($id)
    {
        $base_link = config('app.version_server');
        $base_key  = config('app.version_api_key');
        if($base_link != null && $base_key != null)
        {
            /*for specific version*/
            $uri = $base_link."api/specific/".$base_key.'/'.$id;
            $curl = curl_init($uri);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($curl);
            curl_close($curl);
            if($response)
            {
                $version = json_decode($response);
            }
            /*for specific version*/
        }
        return $this->render('users.version.view-version',compact('version'));
    }

    public function showVersionDetail(Request $request)
    {
        $base_link = config('app.version_server');
        $base_key  = config('app.version_api_key');
        if($base_link != null && $base_key != null)
        {
            /*for specific version*/
            $uri = $base_link."api/specific/".$base_key.'/'.$request->id;
            $curl = curl_init($uri);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            $response = curl_exec($curl);
            curl_close($curl);
            if($response)
            {
                $version_detail = json_decode($response);
            }
            /*for specific version*/
        }

        $html_string = '<div class="col-lg-12">
            <h4>['.$version_detail->version.' ] '.$version_detail->title.'</h4>
        </div>
        <div class="col-lg-12">
            <hr/>
        </div>
        <div class="col-lg-12">
            <h4>Features</h4>
            <p>'.$version_detail->feature.'</p>
            <h4>Bug Fix</h4>
            <p>'.$version_detail->bug_fix.'</p>
        </div>';

        return response()->json(['html'=>$html_string]);
    }
}
