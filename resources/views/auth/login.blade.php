@php
use Milon\Barcode\DNS1D;
@endphp
@extends('layouts.app')

@section('content')
<div style="min-height: 100vh;overflow: hidden;">
<header id="header" class="header pt-3 pb-3 header__custom_color">
  <!-- <div class="container"> -->
    <div class="row align-items-center w-100">
      <div class="col-7 logo">

        <div class="d-flex align-items-center" style="margin-left: 3rem;">
        <img src="{{asset('public/img/logo_white.png')}}" class="img-fluid d-block" style="max-height: 45px;">
        <p class="text-white m-0 font-weight-bold ml-3" style="font-size: 32px;">SUPPLYCHAIN</p>
        </div>

      </div>
      <div class="col-5 loginlink">
        <ul class="list-unstyled mb-0 d-flex pull-right headings-color">
          <!-- <li class="pr-3 font-weight-bold">
            <a href="http://localhost/supplychain/login">Login</a>
          </li> -->
          <!-- <li class="pr-3">|</li>
           <li>
            <a href="http://localhost/supplychain/register">Signup</a>
          </li> -->
        </ul>
      </div>
    </div>
  <!-- </div> -->
</header><!-- /header -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="{{asset('public/site/assets/jquerysession.js')}}"></script>
@if(Request::url() === 'http://wholesale.d11u.com/login' || Request::url() === 'https://wholesale.d11u.com/login')
@php $class = 'testing_color';
$btn_class = 'red_btn'
@endphp
@else
@php
$btn_class = '';
$class = '';
@endphp
@endif
@if(Request::url() === 'http://wholesale.d11u.com/login' || Request::url() === 'https://wholesale.d11u.com/login')
<section class="login-section position-relative testing">
@else
<section class="login-section position-relative">
@endif
  <!-- <div class="container"> -->
    <div class="row w-100">
      <div class="col-lg-7 col-md-9 col-sm-12 col-xl-5 login-form-col text-center themeColor mirror__effect" style="">
        <div class="login-form-bg bg-white p-md-4 p-3">
          <!-- <div class="login-form-header text-white {{$class}}">
            <h1 class="font-weight-bold mb-2 text-uppercase">dashboard</h1>
            <p class="mb-0 ml-auto mr-auto position-relative text-uppercase"><span class="position-relative {{$btn_class}}">Login</span></p>
          </div> -->
          <div class="d-flex justify-content-center mb-5">
          @if(@$sys_logos->logo != null)
            <img src="{{asset('public/uploads/logo/'.$sys_logos->logo)}}" class="img-fluid d-block" style=" height: 131px; max-width: 80%;">
          @else
            <img src="{{asset('public/img/logo.png')}}" class="img-fluid d-block" style="height: 131px; max-width: 80%;">
          @endif
          </div>
          <p class="sign-to-account mb-md-5 mb-4 text-danger" id="sessionMsg" style="display: none;">Your session was expired due to Inactivity!</p>

          <script type="text/javascript">
            if($.session.get("myVar") == 'session'){
              $('#sessionMsg').show();
              $.session.clear();
            }
          </script>
          <!-- <p class="sign-to-account mb-md-5 mb-4">Please Sign in your account</p> -->
          <form class="ml-md-5 ml-sm-4 ml-3 mr-md-5 mr-sm-4 mr-3 text-left" method="POST" action="{{ route('login') }}">
          @csrf
          <div class="form-group fg-col mb-4 d-flex align-items-center">
            <label class="mr-3" style="font-size: 18px">Username: </label>

            <div class="border input-group rounded">
              <!-- <div class="input-group-prepend">
                <div class="bg-transparent border-0 input-group-text pr-0" style="width:35px;">
                  <em class="fa fa-user"></em>
                </div>
              </div> -->
               <input type="text" name="email" class="border-0 form-control @error('email') is-invalid @enderror" id="email" value="{{old('email')}}" required autocomplete="email" placeholder="Username" autofocus style="box-shadow: 5px 5px 5px rgba(0,0,0,0.3);">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

            </div>
          </div>
          <div class="form-group fg-col mb-4 d-flex align-items-center">
            <label class="mr-3" style="font-size: 18px">Password: </label>
            <div class="border input-group rounded">
             <!--  <div class="input-group-prepend">
                <div class="input-group-text bg-transparent border-0 pr-0" style="width:35px;">
                  <em class="fa fa-lock "></em>
                </div>
              </div> -->
              <input id="password" type="password" name="password"  class="border-0 form-control @error('password') is-invalid @enderror" required autocomplete="current-password" placeholder="Password" style="box-shadow: 5px 5px 5px rgba(0,0,0,0.3);">

                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror

            </div>
          </div>
          <!-- <div class="form-group mb-4">
             <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                <label class="custom-control-label text-capitalize" for="remember">remember me</label>
              </div>
          </div> -->

          <div class="form-group mb-1 mt-5">
            <input type="submit" class="btn btn-block text-uppercase {{$btn_class}} mx-auto login__btn" value="login" style="width: 100px;
              border-radius: 0px !important;
              padding: 5px 0px;
              font-size: 18px;
              box-shadow: 5px 5px 5px rgba(0,0,0,0.3);
              ">
            <div class="forgetpassrd mt-3 text-center">

              <p>Forget Password?
              @if (Route::has('password.request'))
            <a href="{{route('password.request')}}" class="font-weight-bold">Click Here</a>
            @endif
        </p>
            </div>
          </div>
          <div class="form-group mb-1 mt-4 d-flex align-items-center justify-content-center">
            <!-- <i class="fa fa-qrcode" aria-hidden="true" style="font-size: 70px;margin-right: 15px;"></i> -->
            {{-- <img src="{{asset('public/img/qr_code.png')}}" class="img-fluid" style="width: 51.13px;margin-right: 15px;"> --}}
            @php $cod = DNS2D::getBarcodeHTML(config('app.app_barcode'),'QRCODE',3,3,'black', true); @endphp
           <div style="margin-right: 15px;">{!! $cod !!}</div>
            <p class="m-0">Scan for POS integrations.</p>
          </div>
          <div class="form-group mb-1 mt-4 d-flex align-items-center justify-content-center">
            <p class="m-0"> <strong class="" style="color: black;">Powered by :</strong> <span style="text-decoration: underline;font-weight: bold"><a href="https://www.wholesalerfactory.com" target="_blank">{{env('APP_URL')}}</a></span></p>
          </div>
          </form>

        </div>
      </div>
    </div>
    <!-- <div class="row w-100">
      <div class="col-lg-5 col-md-7 login-form-col text-center themeColor" style="margin-top: 1rem;margin-left: 6rem;">
        <div class="login-form-bg bg-white p-md-4 p-3">
        </div>
      </div>
    </div> -->
  <!-- </div> -->
</section>

<footer class="text-center pt-3 pb-3 text-white {{$btn_class}}">
    <div class="container footer-container ">
      <div class="d-flex justify-content-between align-items-center footer-main-div ">
         @if($sys_logos->server == null)
          <div class="d-flex align-items-center item1 suply-whole-sale-manag" style="margin-left: 3rem;">
              <img src="{{asset('public/img/logo_white.png')}}" class="img-fluid d-block footer-logo" style="max-height: 25.51px;margin-right: 5px;">
              <span style="text-decoration: underline;font-weight: bold;"><a href="#">Supplychain Wholesale Management.<a></a></span>
          </div>
          @endif
          <div class="item2 mrgn-left">
              <img src="{{asset('public/img/phone.png')}}" class="img-fluid footer-logo2" style="width: 25.51px;margin-right: 5px;">
              <strong>------------</strong>
          </div>
        <div class="item3 mrgn-left">
          <img src="{{asset('public/img/email.png')}}" class="img-fluid footer-logo3" style="width: 25.51px;margin-right: 5px;">
          <strong>No email found !</strong>
        </div>
        <div class="item4 mrgin-left-item4">
          <img src="{{asset('public/img/location.png')}}" class="img-fluid footer-logo4" style="width: 25.51px;margin-right: 5px;">
          <strong>Find Location</strong>
        </div>
      </div>
    </div>
  </footer>



<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<!-- <script src="assets/js/jquery-3.3.1.slim.min.js"></script> -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <script>
    var msg = '{{Session::get('alert')}}';
    var exist = '{{Session::has('alert')}}';
    if(exist){
      alert(msg);
    }
  </script>
@endsection
