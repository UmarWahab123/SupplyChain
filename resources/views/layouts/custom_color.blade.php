<style>
    .sidebarbg{
    background:{{$sys_color->system_color}} !important; 

  }
  
  /* .sidebarnav ul .nav-link{
    border-top: 1px solid {{$sys_color->system_color}};
  } */
  .btn, form input[type="submit"], .btn-bg:focus, .btn:focus, form input[type="submit"]:focus, form input[type="submit"]:active {
    border: 1px solid {{$sys_border_color}};
    background-color:{{$sys_color->system_color}};
    color: {{$sys_color->bg_txt_color}};
}
.btn-bg:hover, .btn:hover, form input[type="submit"]:hover {
    background: {{$sys_color->btn_hover_color}};
    color: {{$sys_color->btn_hover_txt_color}};
    border-color:  {{$btn_hover_border}};
}
/* .btn-bg:focus, .btn:focus, form input[type="submit"]:focus, form input[type="submit"]:active {
    background: {{$sys_color->btn_hover_color}};
    color: {{$sys_color->btn_hover_txt_color}};
} */

.sidebarin .dropdown-menu, .sidebarnav .dropdown.show .nav-link:hover, .sidebarnav .dropdown.show .nav-link:focus, .sidebarnav li:hover .nav-link, .sidebarnav li:focus .nav-link, .sidebarnav .nav-link:hover, .sidebarnav .nav-link:focus, .sidebarnav .nav-item.active a {
    background-color:{{$sys_color->system_color}};  
  }
  /* border-bottom-color: {{$sys_color->btn_hover_color}}; */
.sidebarnav .dropdown.show .nav-link:hover, .sidebarnav .dropdown.show .nav-link:focus, .sidebarnav li:hover .nav-link, .sidebarnav li:focus .nav-link, .sidebarnav .nav-link:hover, .sidebarnav .nav-link:focus, .sidebarnav .nav-item.active a, .usercol > a:hover, .userlist .dropdown-item:hover {
    background-color: {{$sys_color->btn_hover_color}};
    border-color: {{$btn_hover_border}};   
    color: {{$sys_color->btn_hover_txt_color}};
}


.sidebarnav ul .nav-link {
    border-bottom-color: {{$sys_color->system_color}};
    border-top-color:  {{$sys_border_color}};
    color: {{$sys_color->bg_txt_color}};
}
.sidebarin .sidebarnav .dropdown-item {
    border-bottom-color: {{$sys_color->system_color}};
}

.sidebarin .sidebarnav .dropdown-item:hover {
    background: {{$sys_color->btn_hover_color}};
    color: {{$sys_color->btn_hover_txt_color}};
}

.sidebarnav .dropdown-menu, .userlist .dropdown-menu {
    background: {{$sys_color->system_color}};
    color: {{$sys_color->bg_txt_color}};
}

.sidebarnav .dropdown-item:hover, .sidebarnav .dropdown-item:focus {
    background: {{$sys_color->btn_hover_color}};
    color: {{$sys_color->btn_hover_txt_color}};
}

.prof-dropdown .username:before { 
    background:{{$sys_color->system_color}};
    color: {{$sys_color->bg_txt_color}};
}
.paginate_button.current {
    background: {{$sys_color->system_color}} !important;
    color: {{$sys_color->bg_txt_color}} !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {

    background: {{$sys_color->btn_hover_color}} !important;
    color: {{$sys_color->btn_hover_txt_color}} !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
    color: {{$sys_color->btn_hover_txt_color}} !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    color: {{$sys_color->bg_txt_color}} !important;
}
a.btn:not([href]):not([tabindex]) {
    color: {{$sys_color->bg_txt_color}} !important;
}
a.btn:not([href]):not([tabindex]):hover {
    color: {{$sys_color->btn_hover_txt_color}} !important;
}
.tickIcon:hover, .editIcon:hover {
    color:{{$sys_color->btn_hover_txt_color}};
    background-color:{{$sys_color->btn_hover_color}};
}
.tickIcon, .editIcon{
    color: {{$sys_color->system_color}};
    border-color: {{$sys_border_color}};
}
.viewIcon {
    color:{{$sys_color->system_color}};;
    border-color: {{$sys_color->system_color}};
}
.viewIcon:hover {
    color: {{$sys_color->btn_hover_txt_color}};
    border-color:  {{$sys_color->btn_hover_color}};
    background-color: {{$sys_color->btn_hover_color}};
}
#create-quotation:hover {
    color: {{$sys_color->btn_hover_txt_color}};
}
.linkcounter {
    background: {{$sys_border_color}};
}
.logo .lg-logo, .logo .sm-logo{
    max-height: 40px;
}

.spinloader i { position: relative; z-index: 10;}
.entriestable > thead {
    background: {{$sys_color->system_color}} !important;
    color: white !important;
}
table.dataTable thead th {
    color: white !important;
    vertical-align: middle !important;
}
.dt-button
{
    background: {{$sys_color->system_color}} !important;
    background-color: {{$sys_color->system_color}} !important;
    color: white !important;
    border-radius: 5px !important;
    box-shadow: 3px 3px 3px rgba(0,0,0,0.3) !important;
}
.dataTables_length
{
    padding-left: 20px;
}

.dataTables_length > label > select
{
    box-shadow: 3px 3px 3px rgba(0,0,0,0.3);
}

.dataTables_filter > label > input
{
    box-shadow: 3px 3px 3px rgba(0,0,0,0.3);
}

.paginate_button
{
    display: none !important;
}

.paging_listbox
{
    margin-left: 10px !important;
    margin-right: 10px !important;
    width: 100px !important;
    height: 35px !important;
    display: flex !important;
    justify-content: space-between !important;
}

.paging_listbox > select
{
    box-shadow: 3px 3px 3px rgba(0,0,0,0.3) !important;
    border: 0px !important;
    text-align: center !important;
    height: 30px !important;
    margin-top: -2px !important;
}
.form-group > input {
    box-shadow: 3px 3px 3px rgba(0,0,0,0.3) !important;
}
 .scrollbar::-webkit-scrollbar
{
    width: 10px;
    background-color: transparent;
}
 
.scrollbar::-webkit-scrollbar-thumb
{
    border-radius: 10px;
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
    background-color: {{$sys_color->system_color}} !important;
}

.dashboard-boxes-shadow{
    box-shadow: 0 0 15px rgba(0,0,0,0.3);
}

.dashboard-boxes-font{
    font-size: 16px;
}

.dashboard-boxes-bracket-text-font{
    font-size: 16px;
}

.dashboard-boxes-img{
    height: 14px;
    width: 14px;
}

.box3{border-top:5px solid #6AA84F;}
.box2{border-top:5px solid #3D85C6;}
.box1{border-top:5px solid #F1C232;}
.box5{border-top:5px solid #A64D79;}
.box4{border-top:5px solid #CC0000;}
</style>