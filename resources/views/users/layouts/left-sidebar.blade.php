<aside class="sidebar color-white sidebarin" style="width: 70px;">
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

    @php
use App\Menu;
use App\RoleMenu;
@endphp
<?php
    foreach($menus as $menu)
      {
        $single_parent=Menu::where('id',$menu)->first();
        if(!$single_parent->childs->isEmpty())
        {
          $parent_menu=Menu::where('id',$menu)->first();
          $child_menus_ids=RoleMenu::where('role_id',Auth::user()->role_id)->where('parent_id',$menu)->pluck('menu_id')->toArray();
          $child_menus=Menu::where('parent_id',$menu)->whereIn('id',$child_menus_ids)->get();
          
          
  ?>
            <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbardrop2" data-toggle="dropdown" title="Reports">
              <img src="{{asset("uploads/menu-icon/".$parent_menu->icon)}}" alt="" class="img-fluid"> <span>{{$parent_menu->title}}</span>
            </a>
            
            <div class="dropdown-menu">
        <?php

          foreach($child_menus as $ch) { ?>
            <a class="dropdown-item" href="@if(empty($ch->url)) {{route($ch->route)}} @else {{url($ch->url)}} @endif"> 
              <img src="{{asset("uploads/menu-icon/".$ch->icon)}}" alt="" width='20' class="img-fluid"> {{$ch->title}}</a>
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
    <a class="nav-link" href="@if(empty($single_parent->url)) {{route($single_parent->route)}} @else {{url($single_parent->url)}} @endif" title="Sales Dashboard">
      <img src="{{asset("uploads/menu-icon/".$single_parent->icon)}}" alt="" class="img-fluid">
      <span>{{$single_parent->title}}</span></a>
  </li>

 <?php }}
?>


  </ul>
</nav>
</aside>

