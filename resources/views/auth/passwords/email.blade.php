@extends('layouts.app')

@section('content')
    <div style="min-height: 100vh;overflow: hidden;">
        <header id="header" class="header pt-3 pb-3 header__custom_color">
            <div class="row align-items-center w-100">
              <div class="col-7 logo">
                @if($sys_logos->server == null )
                <div class="d-flex align-items-center" style="margin-left: 3rem;">
                <img src="{{asset('public/img/logo_white.png')}}" class="img-fluid d-block" style="max-height: 45px;">
                <p class="text-white m-0 font-weight-bold ml-3" style="font-size: 32px;">SUPPLYCHAIN</p>
                </div>
                @elseif ($sys_logos->logo != null)
                  <img src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" class="img-fluid d-block" style="max-height: 45px;margin-left: 3rem;">
                @else
                  <img src="{{asset('public/img/logo.png')}}" class="img-fluid d-block" style="max-height: 45px;">
                @endif
              </div>
            </div>
        </header>
        <section class="login-section position-relative">
            <div class="row w-100">
                <div class="col-lg-7 col-md-9 col-sm-12 col-xl-5 login-form-col text-center themeColor mirror__effect" style="">
                  <div class="login-form-bg bg-white p-md-4 p-3">
                    <div class="d-flex justify-content-center mb-5">
                    @if($sys_logos->server == null)
                    <img src="{{asset('public/img/supplychain_logo_hd.png')}}" class="img-fluid d-block" style=" width: 80%">
                    @elseif ($sys_logos->logo != null)
                      <img src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" class="img-fluid d-block" style=" width: 80%">
                    @else
                      <img src="{{asset('public/img/logo.png')}}" class="img-fluid d-block" style="max-height: 45px;">
                    @endif
                    </div>
                    <div class="card-header mb-2"><h5>{{ __('Reset Password') }}</h5></div>
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif
                    <form class="ml-md-5 ml-sm-4 ml-3 mr-md-5 mr-sm-4 mr-3 text-left email_reset @if (app('request')->input('type') == 'username') d-none @endif" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group fg-col mb-4 d-flex align-items-center">
                        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>
                      <div class="border input-group rounded">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus style="box-shadow: 5px 5px 5px rgba(0,0,0,0.3);">

                                @error('email')
                                    <span class="invalid-feedback mt-2" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                      </div>
                    </div>
                    <div class="form-group mb-1 mt-2">
                        <button type="submit" class="btn btn-primary"  style="width: 300px;
                        border-radius: 0px !important;
                        padding: 5px 0px;
                        font-size: 18px;
                        box-shadow: 5px 5px 5px rgba(0,0,0,0.3);
                        margin-left: 220px;
                        ">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                </form>
                @error('email')
                Try with Username <a class="try_with_username" href="javascript:void(0)"><b>Click Here</b></a>
                @enderror
                <form class="Admin_Request_Form mt-4 @if (app('request')->input('type') != 'username') d-none @endif">
                    {{-- if email not found, user name should be entered to request the password change to admin --}}
                    <div class="form-group fg-col mb-4 d-flex align-items-center">
                        <label for="username" class="col-md-4 col-form-label text-md-right">User Name</label>
                      <div class="border input-group rounded">
                        <input id="username" type="text" class="form-control" name="username" required style="box-shadow: 5px 5px 5px rgba(0,0,0,0.3);">
                      </div>
                    </div>
                    <div class="form-group mb-1 mt-2">
                        <button type="submit" class="btn btn-primary btn_request_admin"  style="width: 350px;
                        border-radius: 0px !important;
                        padding: 5px 0px;
                        font-size: 18px;
                        box-shadow: 5px 5px 5px rgba(0,0,0,0.3);
                        margin-left: 120px;
                        ">
                            Request Admin to Change Password
                        </button>
                    </div>
                </form>
                <div class="mb-1 mt-4 d-flex align-items-center justify-content-center">
                  {{-- <p class="m-0"> <strong class="" style="color: black;">Powered by :</strong> <span style="text-decoration: underline;font-weight: bold">Supplychain Wholesale Management.</span></p> --}}
                  <p class="m-0"> <strong class="" style="color: black;">Powered by :</strong> <span style="text-decoration: underline;font-weight: bold"><a href="https://www.wholesalerfactory.com">Supplychain Wholesale Management.</a></span></p>
                </div>
                  </div>
                </div>
              </div>
        </section>
        <footer class="text-center pt-3 pb-3 text-white">
            <div class="container footer-container ">
              <div class="d-flex justify-content-between align-items-center footer-main-div ">
                 @if($sys_logos->server == null)
                  <div class="d-flex align-items-center item1 suply-whole-sale-manag" style="margin-left: 3rem;">
                      <img src="{{asset('public/img/logo_white.png')}}" class="img-fluid d-block footer-logo" style="max-height: 25.51px;margin-right: 5px;">
                      {{-- <span style="text-decoration: underline;font-weight: bold;">Supplychain Wholesale Management.</span> --}}
                      <span style="text-decoration: underline;font-weight: bold;"><a href="https://www.wholesalerfactory.com">Supplychain Wholesale Management.</a></span>
                  </div>
                  @endif
                  <div class="item2 mrgn-left">
                      <img src="{{asset('public/img/phone.png')}}" class="img-fluid footer-logo2" style="width: 25.51px;margin-right: 5px;">
                      <strong>02-133-1124 ext.2</strong>
                  </div>
                <div class="item3 mrgn-left">
                  <img src="{{asset('public/img/email.png')}}" class="img-fluid footer-logo3" style="width: 25.51px;margin-right: 5px;">
                  <strong>support@agsth.com</strong>
                </div>
                <div class="item4 mrgin-left-item4">
                  <img src="{{asset('public/img/location.png')}}" class="img-fluid footer-logo4" style="width: 25.51px;margin-right: 5px;">
                  <strong>Find Location</strong>
                </div>
              </div>
            </div>
        </footer>
    </div>
    {{-- <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}

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
    <script>
        $(document).on('click', '.try_with_username', function() {
            var query_string = '?type=username';
            location.href = location.href + query_string;
        })
        $(document).on('submit', '.Admin_Request_Form', function(e) {
            e.preventDefault();
            $.ajax({
                beforeSend: function(){
                    $('#loader_modal').modal('show');
                },
                url: '{{ route("password.admin-request") }}',
                method: 'get',
                data: $(this).serialize(),
                success: function(data) {
                    if (data.success) {
                        toastr.success('Success!', 'Email has been send to Admin.', {"positionClass": "toast-bottom-right"});
                    }
                    else{
                        toastr.error('Error!', 'Please Enter Correct Username', {"positionClass": "toast-bottom-right"});
                    }
                    $('#loader_modal').modal('hide');
                }
            })
        })
    </script>
@endsection
