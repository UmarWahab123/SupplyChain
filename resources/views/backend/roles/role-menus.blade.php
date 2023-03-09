@extends('backend.layouts.layout')
@section('title','View Roles')

@php
use App\Menu;
use App\RoleMenu;
@endphp

@section('content')

<div class="row">
  <div class="col-md-12">
    <a href="{{ url()->previous() }}" class="float-left pt-3">
    <span class="vertical-icons" title="Back">
    <img src="{{asset('public/icons/back.png')}}" width="27px">
    </span>
    </a>
    <ol class="breadcrumb" style="background-color:transparent; font-size: 20px; color: blue !important;">
        @if(Auth::user()->role_id == 1 || Auth::user()->role_id == 3 || Auth::user()->role_id == 4)
          <li class="breadcrumb-item"><a href="{{route('sales')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 2)
          <li class="breadcrumb-item"><a href="{{route('purchasing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 5)
          <li class="breadcrumb-item"><a href="{{route('importing-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 6)
          <li class="breadcrumb-item"><a href="{{route('warehouse-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 7)
          <li class="breadcrumb-item"><a href="{{route('account-recievable')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 9)
          <li class="breadcrumb-item"><a href="{{route('ecom-dashboard')}}">Home</a></li>
        @elseif(Auth::user()->role_id == 10)
          <li class="breadcrumb-item"><a href="{{route('roles-list')}}">Home</a></li>
        @endif
          <li class="breadcrumb-item active">Role Configuration</li>
      </ol>
  </div>
</div>


{{-- Content Start from here --}}
{{-- <div class="row mb-0">
	<div class="col-md-12 title-col">
		<div class="d-sm-flex justify-content-between">
			<h4 class="text-uppercase fontbold">Menus</h4>
			
		</div>
	</div>
</div> --}}
<div class="row align-items-center mb-3">
  <div class="col-md-8 title-col">
      <h4 class="maintitle">Role Confiugration <b>({{$role_name}})</b></h4>
  </div>    
</div>


<div class="row entriestable-row mt-2">
	<div class="@if(!$global_access->isEmpty()) col-6 @else col-6 @endif">

		<div class="ml-2">
		<div class="card">
			<h5 class="card-header">Menus Setting</h5>


			<div class="card-body">
			<ul style="list-style-type:none; " id="sortable" >

			@foreach($allBindedMenus as $menu)	
					<li class="sorable_li parent-checkbox-list" data-id="{{$menu->get_menus->id}}" >
						<div class="card card-primary pl-3 mt-1 @if(!$global_access->isEmpty()) col-12 @else col-8 @endif " style="cursor:grabbing">
							<div class="card-body py-1">
								<div class="form-check form-check-inline ">
									<input @if(in_array($menu->get_menus->id,$role_menus)) checked @endif value="{{$menu->get_menus->id}}" class="form-check-input parentcheckbox menus" type="checkbox" id="{{$menu->get_menus->id}}" data-id="{{$menu->get_menus->id}}" data-parent={{$menu->get_menus->id}}>
									<label class="form-check-label" for="{{$menu->get_menus->id}}">{{$menu->get_menus->title}}</label>
								</div>
								@if($menu->get_menus!=null)
								<ul style="list-style-type:none;"  class="child-checkbox-list">
									<li>@foreach($menu->get_menus->childs as $sub_menu)
										<div class="form-check custom-checkbox custom-control">
											<input data-parent="{{$menu->get_menus->id }}"  value="{{$sub_menu->id}}" class="form-check-input childcheckbox menus" type="checkbox" id="{{$sub_menu->id}}"  @if(in_array($sub_menu->id,$role_menus)) checked @endif />
											<label class="form-check-label" for="{{$sub_menu->id}}">{{$sub_menu->title}}</label>
										</div>@endforeach	
									</li>		
								</ul>
								@endif
							</div>
						</div>
					</li>
			@endforeach
			@foreach($allUnBindedMenus as $menu)	
					<li class="sorable_li parent-checkbox-list" data-id="{{$menu->id}}" >
						<div class="card card-primary pl-3  mt-1 @if(!$global_access->isEmpty()) col-12 @else col-8 @endif " style="cursor:grabbing">
							<div class="card-body py-1">
								<div class="form-check form-check-inline ">
									<input @if(in_array($menu->id,$role_menus)) checked @endif value="{{$menu->id}}" class="form-check-input parentcheckbox menus" data-id="{{$menu->id}}" type="checkbox" id="{{$menu->id}}" data-parent={{$menu->id}}>
									<label class="form-check-label" for="{{$menu->id}}">{{$menu->title}}</label>
								</div>
								@if($menu->childs!=null)
								<ul style="list-style-type:none;"  class="child-checkbox-list">
									<li>@foreach($menu->childs as $sub_menu)
										<div class="form-check custom-checkbox custom-control">
											<input data-parent="{{$menu->id }}"  value="{{$sub_menu->id}}" class="form-check-input childcheckbox menus" type="checkbox" id="{{$sub_menu->id}}"  @if(in_array($sub_menu->id,$role_menus)) checked @endif />
											<label class="form-check-label" for="{{$sub_menu->id}}">{{$sub_menu->title}}</label>
										</div>@endforeach	
									</li>		
								</ul>
								@endif
							</div>
						</div>
					</li>
			@endforeach
		</ul>
	<div class=" mt-4 float-right">
				<button class="btn  btn-primary save-menus-btn">Save</button>
			</div>
			</div>
		
		</div>
		
			</div>
			
		
	</div>
	@if(!$global_access->isEmpty())

	<div class="col-6 ">
		

			<div class="ml-2">
		<div class="card ">
		
            <h5 class="card-header">Role Access <small></small></h5>
		
			<div class="card-body">
				<ul style="list-style:none">
					@foreach($global_access as $ga)
					<li>
						<div class="form-check custom-checkbox custom-control">
							<input @if($ga->status==1) checked @endif value="{{$ga->id}}" class="form-check-input global-access" type="checkbox" id="id_{{$ga->id}}"  />
							<label class="form-check-label" for="id_{{$ga->id}}">{{$ga->title}}</label>
						</div>
					</li>
					@endforeach

				</ul>
			
			<div class=" mt-4 float-right">
				<button class="btn  btn-primary save-global-access-btn  ">Save</button>
			</div>
			</div>

			</div>
		</div>
		
	</div>
 @endif

</div>


<!--  Content End Here -->


@endsection

@section('javascript')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>
    var ids = JSON.parse("{{ json_encode($ids) }}");
    var order = JSON.parse("{{ json_encode($ids) }}");
    var update = false;
    var beforeOrder = [];
	
    $(function() {
        var listId = null;
		$('.parentcheckbox').each(function(){
			beforeOrder.push($(this).data('id'));
		 })
		 console.log(beforeOrder);
		 $('.parentcheckbox').on('change',function(){
			 beforeOrder=[];
			 $('.parentcheckbox').each(function(){
				beforeOrder.push($(this).data('id'));
			});
		 	console.log(beforeOrder);
		 })
        $("#sortable").sortable({			
            update: function(event, ui) {
				ids = [];
				order=[];
				update=true;
                $('#sortable .sorable_li').each(function(i, el) {
                    ids.push($(this).data("id"));
				});			
            },
		});
        $("#sortable").disableSelection();
    });

</script>
<script type="text/javascript">
	$(function(e) {		
		$(document).on('click', '.save-menus-btn', function(e) {
			// console.log(ids);
			// console.log(JSON.parse("{{ json_encode($ids) }}"));
			var order=JSON.parse("{{ json_encode($ids) }}");
			var menus=[];
			var parents=[];
			if(update==false)
			ids=beforeOrder;
			$.each($('.menus:checked'), function(){
				menus.push($(this).val());
				parents.push($(this).data('parent'));
				// if($(this).prop('checked'))
				// is_checked.push(1);
				// else
				// is_checked.push(0);		
           });
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
				}
			});
			$.ajax({
				url: "{{ route('add-role-menu',['role_id'=>$role_id]) }}",
				method: 'get',
				data: {menus:menus,parents:parents,ids:ids,order:order,update:update},
				beforeSend: function() {
					  $('#loader_modal').modal({
						backdrop: 'static',
						keyboard: false
					});
					$("#loader_modal").modal('show');
					$('.save-menus-btn').text('Please wait...');
					//$('.save-btn').addClass('disabled');
					$('.save-menus-btn').attr('disabled', true);
				},
				success: function(result) {
					$("#loader_modal").modal('hide');
					$('.save-menus-btn').text('Save');
					//$('.save-btn').attr('disabled', false);
					$('.save-menus-btn').removeAttr('disabled');
					if (result.success === true) {
						toastr.success('Success!', 'Menus updated successfully', {
							"positionClass": "toast-bottom-right"
						});
						setTimeout(() => {
							window.location.reload();
						}, 2000);
					}
				},
			});
		});
		$(document).on('click', '.save-global-access-btn', function(e) {
			var menus=[];
			
			$.each($('.global-access:checked'), function(){
				menus.push($(this).val());				
           });
			$.ajaxSetup({
				headers: {
					'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
				}
			});
			$.ajax({
				url: "{{ route('add-role-access',['role_id'=>$role_id]) }}",
				method: 'get',
				data: {menus:menus},
				beforeSend: function() {
					$('.save-global-access-btn').text('Please wait...');
					//$('.save-btn').addClass('disabled');
					$('.save-global-access-btn').attr('disabled', true);
				},
				success: function(result) {
					$('.save-global-access-btn').text('Save');
					//$('.save-btn').attr('disabled', false);
					$('.save-global-access-btn').removeAttr('disabled');
					if (result.success === true) {
						toastr.success('Success!', 'Menus updated successfully', {
							"positionClass": "toast-bottom-right"
						});
						setTimeout(() => {
							window.location.reload();
						}, 2000);
					}
				},
			});
		});

	});

	$(function () {
    $(".parentcheckbox").change(function () {
        $(this).parentsUntil('.parent-checkbox-list').siblings('ul').find(".childcheckbox").prop('checked', this.checked);
	});
	
	//var numberOfChildCheckBoxes = $('.childcheckbox').length;
    $(".childcheckbox").change(function () {
		//var numberOfChildCheckBoxes = $(this).parentsUntil('.child-checkbox-list').find('.childcheckbox').length;
		var checkedChildCheckBox = $(this).parentsUntil('.child-checkbox-list').find('.childcheckbox:checked').length;
  if (checkedChildCheckBox > 0) {
  // 		$(this).parentsUntil('.parent-checkbox-list').prev('li').find(".parentcheckbox")
		// .prop('checked', this.checked);
		//alert('test');
		$(this).parentsUntil('.parent-checkbox-list').find(".parentcheckbox")
		 .prop('checked', true);
	}
  else{
 $(this).parentsUntil('.parent-checkbox-list').find(".parentcheckbox")
		 .prop('checked', false);
		 //alert('else');
}
	});

});

</script>
@endsection
