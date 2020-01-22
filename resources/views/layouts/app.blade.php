<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'G-Order') }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::to('img/logoe.jpg')}}" />
    <!-- Styles -->
    <!--<link rel="stylesheet" href="{{ asset('font-awesome/css/font-awesome4.7.0.min.css') }}">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::to('css/mystyle.css') }}">
</head>
<body>
    <div id="app">
      <div id="wrapper">
        <nav class="navbar navbar-default navbar-static-top header">
            <div class="container">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/') }}">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->
                    <ul class="nav navbar-nav">
                        &nbsp;
                    </ul>

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a href="{{ route('login') }}">@lang('label.login')</a></li>
                            <li><a href="{{ route('register') }}">@lang('label.register')</a></li>
                        @else
                        <!--<li><a href="{{ route('product.shoppingCart')}}">
                          <i class="fa fa-cart-plus" aria-hidden="true"></i>Shopping Cart
                          <span class="badge">{{ Session::has('cart')?Session::get('cart')->totalQty:"" }}</span>
                        </a>
                      </li>-->
                      <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> Notifications
                            <span class="badge">{{count(Auth::user()->unreadNotifications)}}</span>
                          </a>
                          <ul class="dropdown-menu alert-dropdown">
                            <li>
                              @forelse (Auth::user()->unreadNotifications->paginate(5)  as $notification)
                              @include('notifications.'.snake_case(class_basename($notification->type)))
                              @empty
                                no unread notification
                                @endforelse
                            </li>
                            @if(count(Auth::user()->unreadNotifications)>5)
                            <li class="divider"></li>
                            <li class="text-center"><a href="">Show All</a></li>
                            @endif


                          </ul>
                      </li>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                  <i class="fa fa-user" aria-hidden="true"></i>
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu" role="menu">
                                  <li><a href="#">My Account</a></li>
                                  <li role="separator" class="divider"></li>
                                    <li>
                                        <a href="{{ route('logout') }}"
                                            onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                            {{ csrf_field() }}
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        @yield('content')
      </div>
      <div class="row">
      <nav class="navbar navbar-default navbar-static-bottom alert alert-info">
        <div class="container">
          <center>&copy; 2017 Galenium Pharmasia Laboratories| Yasa Mitra Perdana | Solusi Integrasi Persada
            <br><div class="btn-group" role="group" aria-label="...">
              @if(Session::get('locale')=="id")
                <button type="button" onclick="changeLanguage('en')" class="btn btn-default" id="enLang">EN</button>
                <button type="button" onclick="changeLanguage('id')" class="btn btn-success" id="idLang" >ID</button>
              @else
                <button type="button" onclick="changeLanguage('en')" class="btn btn-success" id="enLang">EN</button>
                <button type="button" onclick="changeLanguage('id')" class="btn btn-default" id="idLang" >ID</button>
              @endif
          </div>
          </center>
        </div>
      </nav>
      <div>
    </div>
    <!-- Scripts -->
    @yield('js')
    <script src="{{ asset('js/customlang.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @include('utils.adsblock')
</body>
</html>
