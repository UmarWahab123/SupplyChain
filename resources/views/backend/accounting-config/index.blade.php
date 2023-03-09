@extends('backend.layouts.layout')
@section('title','Quotattion Config')
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
          <li class="breadcrumb-item active">Accounting Configuration</li>
      </ol>
  </div>
</div>

{{-- Content Start from here --}}
<div class="row">
    <div class="col-md-12">
        <h3>ACCOUNTING CONFIGURAIONS</h3>
    </div>
</div>
<div class="row entriestable-row mt-2">
    <div class="col-6">
        <div class="bg-white card">
            <h5 class="card-header">Account Receivable Payment No Settings </h5>
            <div class="card-body">
                <div class="ml-2">
                    <p>Auto Run Payment Reference No. </p>

                    <ul style="list-style:none">

                        <li>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input @if($auto_run_payment_ref_no && $auto_run_payment_ref_no->display_prefrences==1) checked @endif value="1" type="checkbox" class="form-check-input " name="auto_run_payment_ref_no">Enable/Disable Auto Run Payment Reference No.
                                </label>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class=" float-right">
                    <button class="btn  btn-primary btn_saveAutoRefNo">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>



@endsection



@section('javascript')
    <script>
        $(document).on('click','.btn_saveAutoRefNo',function(){
            $.ajax({
                url:"{{ route('save-auto-run-payment-ref-no') }}",
                data:{auto_run_payment_ref_no:$("input[name='auto_run_payment_ref_no']:checked").val()},
                method:'get',
                beforeSend:function(){
                    $('.btn_saveAutoRefNo').prop('disabled',true);
                    $('.btn_saveAutoRefNo').html('Please Wait...');
                    $('#loader_modal').modal({
                        backdrop: 'static',
                        keyboard: false
                        });
                    $("#loader_modal").modal('show');
                },
                success:function(result){
                    if (result.success === true) {
                        $('.btn_saveAutoRefNo').prop('disabled',false);
                        $('.btn_saveAutoRefNo').html('Save');
                        toastr.success('Success!', 'Accounting Confiugration updated successfully', {
                            "positionClass": "toast-bottom-right"
                        });
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    }
                    else
                    toastr.error('Success!', 'Something went wrongf', {
                            "positionClass": "toast-bottom-right"
                    });
                    $("#loader_modal").modal('hide');
                },
                error:function(){
                    $('.btn_saveAutoRefNo').prop('disabled',false);
                    $('.btn_saveAutoRefNo').html('Save');
                     toastr.error('Success!', 'Something went wrongf', {
                            "positionClass": "toast-bottom-right"
                    });
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
        });
    </script>
@endsection
