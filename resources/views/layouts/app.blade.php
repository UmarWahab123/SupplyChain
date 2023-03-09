<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{$sys_name}}</title>

    <!-- Scripts -->
    <script src="{{ asset('public/js/app.js') }}" defer></script>

    <!-- jquery latest scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('public/css/app.css') }}" rel="stylesheet">

    <meta charset="utf-8">

    @if ($sys_logos->favicon != null)
    <link rel="shortcut icon" href="{{asset('public/uploads/logo/'.$sys_logos->favicon)}}">
    @else
    <link rel="shortcut icon" href="{{asset('public/img/logo-icon.png')}}">
    @endif
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Login</title>
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!-- <link href="login.css" rel="stylesheet" type="text/css"> -->
<link href="{{asset('public/css/login.css')}}" rel="stylesheet" type="text/css">

<!-- jQuery UI latest cdn -->
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

{{-- Toastr Plugin --}}
<link href="{{asset('public/site/assets/backend/css/toastr.min.css')}}" rel="stylesheet">
<script  src="{{asset('public/site/assets/backend/js/toastr.min.js')}}"></script>

    <style>
    footer {
        background: {{$sys_color->system_color}} !important;
    }
    .login-section:before {
        box-shadow: none;
        /*background:  {{$sys_color->system_color}};*/
    }
    .login-form-header {
    background: {{$sys_color->system_color}};
    box-shadow: none;
    border-radius: 0.66666rem;
}
.login-form-header p span {
    background: {{$sys_color->system_color}};

}.btn {

    background: {{$sys_color->system_color}};
    color: #ffffff;

}
.btn:hover, .btn:focus {
    background: {{$sys_color->btn_hover_color}};
    color: {{$sys_color->btn_hover_txt_color}};
    border-color: {{$btn_hover_border}};
}
.red_btn {
    background: {{$sys_color->system_color}} !important;
}
.header__custom_color
{
    background: {{$sys_color->system_color}} !important;
}
.login-section
{
    background-image: url("{{ $sys_color->login_background != null ? asset('public/uploads/logo/'.$sys_color->login_background) : 'https://thumbs.dreamstime.com/b/asian-food-background-various-cooking-ingredients-rustic-background-top-view-banner-concept-chinese-thai-66582124.jpg'}}");
    background-size: cover;
    background-repeat: no-repeat;
    background-size: 100% 100%;
}
.login-form-bg
{
    border: 0.3rem solid {{$sys_color->system_color}};
    padding: 45px 15px 90px 15px !important;
}
.mirror__effect {
  position: relative;

}
.mirror__effect:after,
.mirror__effect:before {
  content: "";
  position: absolute;
  display: block;
  width: calc(100% - 30px);
  height: 50%;
  bottom: -52%;
  border-radius: 1.5rem;
      border: 0.3rem solid {{$sys_color->system_color}};
      opacity: 0.5;
      padding: 0px !important;
}
.mirror__effect:after {
  background: inherit;
  transform: scaleY(-1);
}
.mirror__effect:before {
  z-index: 1;
  background: linear-gradient(to bottom, rgba(255, 255, 255, 0.5), #fff);
}
</style>
</head>
<body class="loginBody">
    <div id="app">
        <!-- <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    {{ config('app.name', 'Laravel') }}
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">

                    <ul class="navbar-nav mr-auto">

                    </ul>


                    <ul class="navbar-nav ml-auto">

                        @guest
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav> -->

        <main class="">
            @yield('content')
        </main>
    </div>
</body>
</html>
