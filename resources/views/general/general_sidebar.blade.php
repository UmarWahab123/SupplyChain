<aside class="sidebar color-white sidebarin desktop__sidebar" style="width: 70px;">
<div class="sidebarbg" style="width: 70px;"></div>
<nav class="navbar sidebarnav navbar-expand-sm">


<!-- Links -->
  <ul class="menu list-unstyled">
  <li class="nav-item acitve">
      <a class="nav-link dashboardlink" href="javascript:void(0)">
        <i class="fa fa-bars dashboardIcon mydashicon"></i>
        <!-- <img src="{{asset('public/site/assets/backend/img/dashboard.png')}}" alt="" class="img-fluid"> -->
        <span class="mydash"></span></a>
    </li>
    <li class="nav-item d-none">
      <a class="nav-link" href="{{url('admin')}}" title="Dashboard">
        <img src="{{asset('public/site/assets/backend/img/dashboard-icon.png')}}" alt="" class="img-fluid">
         <span>Admin Dashboard</span></a>
    </li>

<?php
use App\Menu;
use App\RoleMenu;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
$menus=RoleMenu::with('parent.rollmenusdata','parent.childs')->where('role_id',Auth::user()->role_id)->groupby('parent_id')->orderBy('order','asc')->get();
$slugs=Menu::whereNotNull('slug')->pluck('slug');
$global_counters=[];
foreach($slugs as $slug)
{
    switch ($slug) {
        case "completeProducts":
            $global_counters['completeProducts'] =Product::where('status', 1)->count('id');
            break;
        case "incompleteProducts":
            $global_counters['incompleteProducts']= Product::where('status', 0)->count('id');
            break;
        case "inquiryProducts":
            $global_counters['inquiryProducts']=OrderProduct::where('is_billed', 'Inquiry')->count('id');
            break;
        case "cancelledOrders":
            if(Auth::user()->role_id == 9)
            {
                $global_counters['cancelledOrders']=Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
            }
            else
            {
                $global_counters['cancelledOrders']=Order::select('id')->where('primary_status', 17)->count('id');
            }
            break;
        case "deactivatedProducts":
            $global_counters['deactivatedProducts']=Product::where('status', 2)->count('id');
            break;
        case "ecommerceProducts":
            $global_counters['ecommerceProducts']=Product::where('ecommerce_enabled', 1)->count('id');
            break;
        case "EcomCancelledOrders":
            $global_counters['EcomCancelledOrders']=Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
            break;
    }
}
?>

<?php
    foreach($menus as $menu)
      {
        $single_parent=$menu->parent;
        if(!$single_parent->childs->isEmpty())
        {
          $parent_menu=$menu->parent;
          // $child_menus_ids=RoleMenu::where('role_id',Auth::user()->role_id)->where('parent_id',$menu->parent_id)->pluck('menu_id')->toArray();

          $child_menus_ids = $parent_menu->rollmenusdata->where('role_id',Auth::user()->role_id)->pluck('menu_id')->toArray();
          // dd($child_menus_ids, $child_menus_ids1);
          // $child_menus=Menu::where('parent_id',$menu->parent_id)->whereIn('id',$child_menus_ids)->get();
          $child_menus=$parent_menu->childs->whereIn('id',$child_menus_ids);

          // dd($child_menus,$child_menus1);


  ?>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbardrop{{$parent_menu->id}}" data-toggle="dropdown" title="{{$parent_menu->title}}">
            @if(!empty($parent_menu->icon)) <img src="{{asset("public/menu-icon/".$parent_menu->icon)}}" alt="" class="img-fluid"> @endif <span>{{$parent_menu->title}}

              </span>
            </a>

            <div class="dropdown-menu">
        <?php

          foreach($child_menus as $ch) { ?>
            <a class="dropdown-item" id="item_no_{{@$ch->id}}" href="@if(empty($ch->url)) {{route($ch->route)}} @else {{url($ch->url)}} @endif">
             @if(!empty($ch->icon))
             <img src="{{asset("public/menu-icon/".$ch->icon)}}" alt="" width='20' class="img-fluid">
             @endif {{$ch->title}}
              @if(@$ch->slug!=null)
          <span class="linkcounter sub-linkcounter" style="font-size:11px">{{@$global_counters[$ch->slug]}}</span>
          @endif
              </a>
          <?php  }
        ?>
            </div>
          </li>


<?php

  }
  else
  {

    ?>
    <li class="nav-item">
    <a class="nav-link" id="item_no_{{@$single_parent->id}}" href="@if(empty($single_parent->url)) {{route($single_parent->route)}} @else {{url($single_parent->url)}} @endif" title="{{$single_parent->title}}">
      <img src="{{asset("public/menu-icon/".$single_parent->icon)}}" alt="" class="img-fluid">
      <span>{{$single_parent->title}}
      @if($single_parent->slug!=null)
        <span class="linkcounter sub-linkcounter" style="font-size:11px">{{$global_counters[$single_parent->slug]}}</span>
        @endif
      </span></a>
  </li>

 <?php }}
?>





  </ul>
</nav>
</aside>
<aside class="mob__sidebar">
  <p class="m-0">
  <a class="btn btn-primary" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample"
  style="background: transparent;
    color: black;
    border: none;padding: 5px 0px 0px 5px;"
  >
    <i class="fa fa-bars dashboardIcon mydashicon"></i>
  </a>
</p>
<div class="collapse" id="collapseExample">
  <div class="card card-body">
    <div class="row">
      @if(Auth::user()->role_id != 5)
      <div class="col-12 search-padding topsearch-col align-items-center">
        <div class="border rounded input-group custom-input-group autosearch">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="search" class="form-control pl-1" id="header_prod_search_mobile" tabindex="0" name="prod_name" placeholder="Search Products">
          <span id="purchase_loader_product_mobile"></span>

        </div>
        <p id="myIdd_mobile" class="m-0"></p>
      </div>
      <div class="col-12 search-padding topsearch-col align-items-center">
        <div class="border rounded input-group custom-input-group autosearch">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="search" class="form-control pl-1" id="header_orders_search_mobile" name="header_orders_search" placeholder="Search Orders">
          <span id="purchase_loader_product2_mobile"></span>
        </div>
        <p id="myIdd2_mobile" class="m-0"></p>
      </div>
      @if(Auth::user()->role_id != 6 && Auth::user()->role_id != 9)
      <div class="col-12 search-padding topsearch-col align-items-center">
        <div class="border rounded input-group custom-input-group autosearch">
          <div class="input-group-prepend">
          <span class="input-group-text fa fa-search"></span>
          </div>
          <input type="search" class="form-control pl-1" id="header_po_search_mobile" name="header_po_search" placeholder="Search PO">
          <span id="purchase_loader_product3_mobile"></span>
        </div>
        <p id="myIdd3_mobile" class="m-0"></p>
      </div>
      @endif
      @endif
    </div>
    @if(Auth::user()->role_id != 5 && Auth::user()->role_id != 7 && Auth::user()->role_id != 9)
    <div class="row mt-2">
      <div class="col-12 d-flex search-padding">
        @if(Auth::user()->role_id != 2 && Auth::user()->role_id != 6)
        <a class="btn recived-button custom-create-po-btn tablet-header_buttons" id="create_po_id" target="_blank" href="{{ route('product-order-invoice') }}" style="display:block; width: 130px;" id="create-quotation" title="Create Quotation">
          <span>Create Quotation</span></a>
        @endif
        @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 6)
        <a class="btn recived-button custom-create-po-btn ml-1 tablet-header_buttons" target="_blank" href="{{ route('create-purchase-order-direct') }}" style="display:block; width: 130px;" id="create-quotation" title="Create Purchase Orders">
          <span>Create PO</span></a>
        @endif
      </div>
    </div>
    @endif
    <div class="row mt-2">
      <div class="col-12 d-flex search-padding">
      @if(Auth::user()->role_id != 5 && Auth::user()->role_id != 7 && Auth::user()->role_id != 9)
        @if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4)
        <a class="btn recived-button custom-create-po-btn tablet-header_buttons" target="_blank" href="{{ route('create-transfer-document') }}" style="display:block; width: 116px;" id="create_transfer_doc" title="Create Transfer Document">
          <span>Create Transfer</span></a>
        @endif
      @endif
        @if(auth()->user()->parent_id != null)
          <div>
            <select class="select__user_type" style="height: 29px; width: 106px; margin-left: 5%; margin-top: 2%;">
              <option selected="" disabled>Select User Type</option>
              <option value="1" {{auth()->user()->role_id == '1' ? 'selected' : ''}}>Admin</option>
              <option value="2" {{auth()->user()->role_id == '2' ? 'selected' : ''}}>Purchasing</option>
              <option value="3" {{auth()->user()->role_id == '3' ? 'selected' : ''}}>Sales</option>
              <option value="4" {{auth()->user()->role_id == '4' ? 'selected' : ''}}>Sales Coordinator</option>
              <option value="5" {{auth()->user()->role_id == '5' ? 'selected' : ''}}>Logistics</option>
              <option value="6" {{auth()->user()->role_id == '6' ? 'selected' : ''}}>Warehouse</option>
              <option value="7" {{auth()->user()->role_id == '7' ? 'selected' : ''}}>Accounting</option>
              <option value="9" {{auth()->user()->role_id == '9' ? 'selected' : ''}}>Ecommerce Manager</option>
              <option value="10" {{auth()->user()->role_id == '10' ? 'selected' : ''}}>Super Admin</option>
            </select>
          </div>
        @endif
      </div>
    </div>
    <div class="row mt-4 table_divider_div" style="border-radius: 5px; padding: 0.8%; margin-right: 0px; margin-left: 0px; background-color:{{$sys_color->system_color}};"></div>
    <ul class="menu list-unstyled row phone__sidebar_li">
<?php

$menus=RoleMenu::with('parent.rollmenusdata','parent.childs')->where('role_id',Auth::user()->role_id)->groupby('parent_id')->orderBy('order','asc')->get();
$slugs=Menu::whereNotNull('slug')->pluck('slug');
$global_counters=[];
foreach($slugs as $slug)
{
    switch ($slug) {
        case "completeProducts":
            $global_counters['completeProducts'] =Product::where('status', 1)->count('id');
            break;
        case "incompleteProducts":
            $global_counters['incompleteProducts']= Product::where('status', 0)->count('id');
            break;
        case "inquiryProducts":
            $global_counters['inquiryProducts']=OrderProduct::where('is_billed', 'Inquiry')->count('id');
            break;
        case "cancelledOrders":
            if(Auth::user()->role_id == 9)
            {
                $global_counters['cancelledOrders']=Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
            }
            else
            {
                $global_counters['cancelledOrders']=Order::select('id')->where('primary_status', 17)->count('id');
            }
            break;
        case "deactivatedProducts":
            $global_counters['deactivatedProducts']=Product::where('status', 2)->count('id');
            break;
        case "ecommerceProducts":
            $global_counters['ecommerceProducts']=Product::where('ecommerce_enabled', 1)->count('id');
            break;
        case "EcomCancelledOrders":
            $global_counters['EcomCancelledOrders']=Order::select('id')->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->count('id');
            break;
    }
}
?>

<?php
    foreach($menus as $menu)
      {
        $single_parent=$menu->parent;
        if(!$single_parent->childs->isEmpty())
        {
          $parent_menu=$menu->parent;
          // $child_menus_ids=RoleMenu::where('role_id',Auth::user()->role_id)->where('parent_id',$menu->parent_id)->pluck('menu_id')->toArray();

          $child_menus_ids = $parent_menu->rollmenusdata->where('role_id',Auth::user()->role_id)->pluck('menu_id')->toArray();
          // dd($child_menus_ids, $child_menus_ids1);
          // $child_menus=Menu::where('parent_id',$menu->parent_id)->whereIn('id',$child_menus_ids)->get();
          $child_menus=$parent_menu->childs->whereIn('id',$child_menus_ids);

          // dd($child_menus,$child_menus1);


  ?>
            <li class="nav-item dropdown col-4">
            <a class="nav-link sidebar__btn btn dropdown-toggle" href="#" id="navbardrop{{$parent_menu->id}}" data-toggle="dropdown" title="{{$parent_menu->title}}">

            <span>{{$parent_menu->title}}

              </span>
            </a>

            <div class="dropdown-menu">
        <?php

          foreach($child_menus as $ch) { ?>
            <a class="dropdown-item phone_dropdown__item" id="item_no_{{@$ch->id}}" href="@if(empty($ch->url)) {{route($ch->route)}} @else {{url($ch->url)}} @endif">
             {{$ch->title}}
              @if($ch->slug!=null)
          <!-- <span class="linkcounter sub-linkcounter" style="font-size:11px">{{$global_counters[$ch->slug]}}</span> -->
          @endif
              </a>
          <?php  }
        ?>
            </div>
          </li>


<?php

  }
  else
  {

    ?>
    <li class="nav-item col-4">
    <a class="nav-link sidebar__btn btn" id="item_no_{{@$single_parent->id}}" href="@if(empty($single_parent->url)) {{route($single_parent->route)}} @else {{url($single_parent->url)}} @endif" title="{{$single_parent->title}}">

      <span>{{$single_parent->title}}
      @if($single_parent->slug!=null)
        <!-- <span class="linkcounter sub-linkcounter" style="font-size:11px">{{$global_counters[$single_parent->slug]}}</span> -->
        @endif
      </span></a>
  </li>

 <?php }}
?>





  </ul>
  </div>
</div>
</aside>

