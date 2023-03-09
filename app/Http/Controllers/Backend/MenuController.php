<?php

namespace App\Http\Controllers\Backend;

use App\Menu;
use App\Models\Common\Role;
use App\Models\Common\Status;
use App\RoleMenu;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\DataTables;
use Illuminate\Http\UploadedFile;


class MenuController extends Controller
{
    public function index(){
        $menus = Menu::where('parent_id', '=', 0)->get();
        $roles = Role::pluck('name','id')->all();
       
        return view('backend.roles.patent_menus',compact('menus','roles'));
    }

    public function getData(){
        $query = Menu::where('parent_id', 0)->get();
       // subMenus
        return Datatables::of($query)
            ->addColumn('action', function ($item) {
                $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>
                          <a href="javascript:void(0);" class="actionicon deleteIcon delete-menu" data-id="'.$item->id.'" data-menu_name="'.$item->title.'" title="Delete"><i class="fa fa-ban"></i></a>
                      </div>';
                return $html_string;
            })
            ->addColumn('title', function ($item) {
                if($item->parent_id == 0){
                    $html_string = '<div>'.'
                          <a href="'.route('sub-menus-list',$item->id).'" data-id="'.$item->id.'"  class="parent text-dark" style="color:blue"><b>'.$item->title.'</b></a>
                      </div>';
                }else{
                    $html_string = '<div>'.$item->title.'</div>';
                }
                return $html_string;
            })
            ->addColumn('url', function ($item) {
                return $item->url;
            })
            ->addColumn('route', function ($item) {
                return $item->route;
            })
            ->addColumn('icon', function ($item) {
                if($item->icon){
                    return '<img  style="background-color: #13436c;" name="'.$item->icon.'" src="'.asset("public/menu-icon/{$item->icon}").'" alt="" class="img-fluid">';
                }else{
                    return "No Icon Available";
                }
            })
            ->setRowId(function ($item) {
                return $item->id;
            })
            ->rawColumns(['action','title','icon'])
            ->make(true);
    }

    public function edit(Request $request){

        $validator = $request->validate([
            'title' => 'required',
        ]);

        $menu = Menu::find($request->editid);
        if($request['title']){
            $menu->title = $request['title'];
        }

        if($request->hasFile('icon')){
            $file = $request->file('icon');
            $destinationPath = public_path().'/menu-icon';
            $custom_file_name =$request->file('icon')->getClientOriginalName();
            $file->move($destinationPath,$custom_file_name);
            $menu->icon = $custom_file_name;
        }

        if($request['url']){
            $menu->url = $request['url'];
            $menu->route = NULL;

        }elseif($request['route']){
            $menu->route = $request['route'];
            $menu->url = NULL;

        }
        $menu->slug=$request['slug'];
        $menu->parent_id = $request->parent_id ? $request->parent_id : 0;
        $menu->save();

        return response()->json(['success' => true]);

    }

    public function store(Request $request)
    {
        $validator = $request->validate([
            'title' => 'required',
        ]);

        $menu_data = $request->except("role_id");
        $menu_data['parent_id'] = empty($menu_data['parent_id']) ? 0 : $menu_data['parent_id'];
        $menu_data['status'] = empty($menu_data['status']) ?  0 : 1;
        /*$menu_data['url'] = empty($menu_data['url']) ?  '#' : $menu_data['url'];
        $menu_data['route'] = empty($menu_data['route']) ?  '/' : $menu_data['route'];*/

        if(!empty($menu_data['url'])){
            $menu_data['route'] = NULL;

        }elseif(!empty($menu_data['route'])){
            $menu_data['url'] = NULL;

        }

        if($request->hasFile('icon')){
            $file = $request->file('icon');
            $destinationPath = public_path().'/menu-icon';
            $custom_file_name = $request->file('icon')->getClientOriginalName();
            $file->move($destinationPath,$custom_file_name);
            $menu_data['icon'] = $custom_file_name;
        }

        $created_menu = Menu::create($menu_data);

        return response()->json(['success' => true]);

    }

    public function show(){
        $menus = Menu::where('parent_id', '=', 0)->get();
        return view('menu.dynamicMenu',compact('menus'));
    }

    public function getSubData($id){
        $query = Menu::where('parent_id',$id)->get();
        return Datatables::of($query)
            ->addColumn('action', function ($item) {
                $html_string = '<div class="icons">'.'
                          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>
                          <a href="javascript:void(0);" class="actionicon deleteIcon delete-menu" data-id="'.$item->id.'" data-menu_name="'.$item->title.'" title="Delete"><i class="fa fa-ban"></i></a>
                      </div>';
                return $html_string;
            })
            ->addColumn('title', function ($item) {
                if($item->parent_id == 0){
                    $html_string = '<div>'.'
                          <a href="'.route('sub-menus-list',$item->id).'" data-id="'.$item->id.'"  class="parent" style="color:blue">'.$item->title.'</a>
                      </div>';
                }else{
                    $html_string = '<div>'.$item->title.'</div>';
                }
                return $html_string;
            })
            ->addColumn('url', function ($item) {
                return $item->url;
            })
            ->addColumn('route', function ($item) {
                return $item->route;
            })
            ->addColumn('icon', function ($item) {
                if($item->icon){
                    return '<img  style="background-color: #13436c;" name="'.$item->icon.'" src="'.asset("public/menu-icon/{$item->icon}").'" alt="" class="img-fluid">';
                }else{
                    return "No Icon Available";
                }
            })
            ->setRowId(function ($item) {
                return $item->id;
            })
            ->rawColumns(['action','title','icon'])
            ->make(true);
    }

    public function delete(Request $request){
        $menu =  Menu::find($request->id);
        $menu->forceDelete();
        RoleMenu::where('menu_id',$request->id)->delete();

        return response()->json(['success' => true]);
    }
    public function subMenus($id){
        $parent_menu = Menu::find($id);
        $menus = Menu::where('parent_id', '=', 0)->get();
       // $allMenus = Menu::pluck('title','id')->all();

        return $this->render('backend.roles.sub-menus',compact('id','menus', 'parent_menu'));
    }
    public function sortingMenuIndex()
    {
        //return view
    }
}
