@extends('users.layouts.layout')

@section('title','General Settings | Purchasing')

@section('content')
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}

</style>

{{-- Content Start from here --}}
<div class="row mb-3">
  <div class="col-md-8 title-col">
    <h3 class="maintitle text-uppercase fontbold">ALL SETTINGS</h3>
  </div>
  
</div>


<div class="row entriestable-row mt-3 mb-3">
  <div class="col-12">

    <div class="entriesbg bg-white custompadding customborder">

    <h4 class="maintitle text-uppercase fontbold">Show or Hide Columns</h4>
    <hr>
      <?php
        $checked = '';
        $i = 0;
        $setting_table_hide_columns_arr = []; 

        $table_arr = [
          2 => 'Reference Code',
          3 => 'HS Code',
          4 => 'Name',
          5 => 'Category',
          6 => 'Sub Category',
          7 => 'Short Desc ',
          8 => 'Picture',
          9 => 'Billed Unit'
        ];
        ksort($table_arr);

        $hidden_by_default = [];

        if($setting_table_hide_columns){
          $setting_table_hide_columns_arr = explode(',',$setting_table_hide_columns->hide_columns);
        }
        else{
          $setting_table_hide_columns_arr = $hidden_by_default;
        }        

      ?>

      <form class="table-col-form" method="POST" action="{{ route('save-column-toggle') }}">
      @csrf
      <input type="hidden" name="type" value="product">
      <div class="cols-hide">
        <div class="cut-polish-title">
          <h5 class="d-inline-block pr-3">Products</h5> 
          <div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input" value="check_all" name="check-all" id="check-all">
                <label class="custom-control-label" for="check-all">Check All</label>
          </div>
        </div>
        <p>Please check the columns you want to hide in your Products Listing Table.</p>
        <div class="row">
          @foreach($table_arr as $k => $val)
          <?php
          $i++;
          if(sizeof($setting_table_hide_columns_arr) > 0){
           if (in_array($k, $setting_table_hide_columns_arr)) {
            $checked = 'checked';
           }else{
            $checked = "";
           }
          }
          ?>
          <div class="col-md-3 mb-3">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" {{ $checked }} class="custom-control-input check" value="{{ $k }}" name="table_col[]" id="table-{{ $i }}">
              <label class="custom-control-label" for="table-{{ $i }}">{{ $val }}</label>
            </div>
          </div>
          @endforeach
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>

      </div> 
      </form> 
    </div>
    
  </div>
<!-- my code -->
  <div class="col-12 mt-2">

    <div class="entriesbg bg-white custompadding customborder">

    <h4 class="maintitle text-uppercase fontbold">Show or Hide Columns</h4>
    <hr>
      <?php
        $checked = '';
        $j = 100;
        $setting_table_hide_columns_arr_2 = []; 

        $table_arr_2 = [
          2 => 'Short Description',
          3 => 'Category',
          4 => 'Sub Category',
          8 => 'Date Delivery'
        ];
        ksort($table_arr_2);

        $hidden_by_default_2 = [];

        if($setting_table_hide_columns_2){
          $setting_table_hide_columns_arr_2 = explode(',',$setting_table_hide_columns_2->hide_columns);
        }
        else{
          $setting_table_hide_columns_arr_2 = $hidden_by_default_2;
        }        

      ?>

      <form class="table-col-form" method="POST" action="{{route('save-purchase-list-column-toggle')}}">
      @csrf
      <input type="hidden" name="type" value="purchase-list">
      <div class="cols-hide">
        <div class="cut-polish-title">
          <h5 class="d-inline-block pr-3">Purchase List</h5> 
          <div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input" value="check_all_2" name="check-all_2" id="check-all_2">
                <label class="custom-control-label" for="check-all_2">Check All</label>
          </div>
        </div>
        <p>Please check the columns you want to hide in your Products Listing Table.</p>
        <div class="row">
          @foreach($table_arr_2 as $kk => $val)
          <?php
          $j++;
          if(sizeof($setting_table_hide_columns_arr_2) > 0){
           if (in_array($kk, $setting_table_hide_columns_arr_2)) {
            $checked = 'checked';
           }else{
            $checked = "";
           }
          }
          ?>
          <div class="col-md-3 mb-3">
            <div class="custom-control custom-checkbox">
              <input type="checkbox" {{ $checked }} class="custom-control-input check_2" value="{{ $kk }}" name="table_col_2[]" id="table-{{ $j }}">
              <label class="custom-control-label" for="table-{{ $j }}">{{ $val }}</label>
            </div>
          </div>
          @endforeach
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary">Save</button>
        </div>

      </div> 
      </form> 
    </div>
    
  </div>
</div>

<!--  Content End Here -->

  <div class="modal" id="loader_modal" role="dialog">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">

      <div class="modal-body">
        <h3 style="text-align:center;">Please wait</h3>
        <p style="text-align:center;"><img src="{{ asset('public/uploads/gif/waiting.gif') }}"></p>
      </div>

      </div>
    </div>
  </div>



@endsection

@section('javascript')
<script type="text/javascript">
  $(function(e){

     @if(Session::has('successmsg'))
        toastr.success('Success!', "{{ Session::get('successmsg') }}" ,{"positionClass": "toast-bottom-right"});
     @endif

      $(document).on('click', '#check-all', function () {
        if(this.checked == true){
        $('.check').prop('checked', true);
      }else{
        $('.check').prop('checked', false);        
      }
    });

    $(document).on('click', '#check-all_2', function () {
        if(this.checked == true){
        $('.check_2').prop('checked', true);
      }else{
        $('.check_2').prop('checked', false);        
      }
    });

  });
</script>
@stop

