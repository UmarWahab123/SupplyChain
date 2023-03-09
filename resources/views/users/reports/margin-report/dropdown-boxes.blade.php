

{{--Filters start here--}}
<div class="row mb-0 filters_div">
  <div class="col-md-12 title-col">
    <div class=" row d-sm-flex justify-content-between">

      <div class="col-md-3 col-12">
        <?php
          $url =  \Request::path();
        ?>
        <label>Primary Filter</label>
        <select class="font-weight-bold form-control-lg form-control state-tags" id="dynamic_select" name="primary_filter" >
          <option value="" disabled="">Primary Filter</option>
          <option value="{{ url('/margin-report') }}" {{ ($url == 'margin-report' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_office'] != null ? @$global_terminologies['margin_report_by_office'] : 'Office' }}</option>
          <option value="{{ url('/margin-report-2') }}" {{ ($url == 'margin-report-2' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_product_name'] != null ? @$global_terminologies['margin_report_by_product_name'] : 'Product Name' }}</option>
          <option value="{{ url('/margin-report-3') }}" {{ ($url == 'margin-report-3' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_sales_persons'] != null ? @$global_terminologies['margin_report_by_sales_persons'] : 'Sales Persons' }}</option>
          <option value="{{ url('/margin-report-4') }}" {{ ($url == 'margin-report-4' || $url == 'margin-report-8' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_product_category'] != null ? @$global_terminologies['margin_report_by_product_category'] : 'Region' }}</option>
          <option value="{{ url('/margin-report-5') }}" {{ ($url == 'margin-report-5' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_customer'] != null ? @$global_terminologies['margin_report_by_customer'] : 'Customer' }}</option>
          <option value="{{ url('/margin-report-6') }}" {{ ($url == 'margin-report-6' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_customer_type'] != null ? @$global_terminologies['margin_report_by_customer_type'] : 'Customer Type' }}</option>
          @if(@$server == null)
          <option value="{{ url('/margin-report-9') }}" {{ ($url == 'margin-report-9' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_product_type'] != null ? @$global_terminologies['margin_report_by_product_type'] : 'Product Type' }}</option>
          @endif

          @if (in_array('product_type_2', $product_detail_section))
          <option value="{{ url('/margin-report-11') }}" {{ ($url == 'margin-report-11' )? "selected='true'":" " }}>@if(!array_key_exists('product_type_2', $global_terminologies)) Type 2 @else {{$global_terminologies['product_type_2']}} @endif</option>
          @endif

          @if (in_array('product_type_3', $product_detail_section))
          <option value="{{ url('/margin-report-product-type-3') }}" {{ ($url == 'margin-report-product-type-3' )? "selected='true'":" " }}>@if(!array_key_exists('product_type_3', $global_terminologies)) Type 3 @else {{$global_terminologies['product_type_3']}} @endif</option>
          @endif

          <option value="{{ url('/margin-report-10') }}" {{ ($url == 'margin-report-10' )? "selected='true'":" " }}>{{@$global_terminologies['margin_report_by_supplier'] != null ? @$global_terminologies['margin_report_by_supplier'] : 'Supplier' }}</option>
          <!-- <option value="{{ url('/margin-report-7') }}" {{ ($url == 'margin-report-7' )? "selected='true'":" " }}>Preorder / Stock</option> -->
        </select>
      </div>

      <div class="col-md-2 col-12">
        <div class="form-group">
          <label>From Date</label>
          <input type="text" placeholder="From Date" name="from_date" class="form-control font-weight-bold" id="from_date" autocomplete="off">
        </div>
      </div>

      <div class="col-md-2 col-12">
        <div class="form-group">
          <label>To Date</label>
          <input type="text" placeholder="To Date" name="to_date" class="form-control font-weight-bold" id="to_date" autocomplete="off">
        </div>
      </div>

      <div class="col-md-5 col-12" style="">
      <div class="form-group">
       <label><b style="visibility: hidden;">Reset</b></label>
      <div class="input-group-append ml-3">
        <!-- <button class="btn recived-button apply_date rounded" type="button">Apply Dates</button>   -->
        <span class="apply_date common-icons mr-4" title="Apply Date">
          <img src="{{asset('public/icons/date_icon.png')}}" width="27px">
        </span>

        <span class="common-icons reset-btn" id="reset-btn" title="Reset">
            <img src="{{asset('public/icons/reset.png')}}" width="27px">
          </span>
      </div>
      </div>
    </div>

      <div class="col-2">
        <label style="visibility: hidden;">nothing</label>
        <div class="input-group-append">
          <!-- <button class="btn recived-button reset-btn rounded" type="reset">Reset</button>   -->
        </div>
      </div>


    </div>
  </div>
</div>
{{--Filters ends here--}}
