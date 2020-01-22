<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
		<link rel="stylesheet" href="product.css">
		<link href="https://fonts.googleapis.com/css?family=Quicksand:300,400,500,700&subset=latin-ext,vietnamese" rel="stylesheet">
	<link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Pacifico&subset=latin-ext,vietnamese" rel="stylesheet">
	<link rel='stylesheet prefetch' href='http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.css'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
	<link href="{{ asset('css/app2.css') }}" rel="stylesheet">
    
    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-default navbar-static-top">
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
                        <strong>G-ORDER</strong>
                    </a>
                </div>

                <form class="navbar-form navbar-left" role="search">
                  <div class="form-group">
                    <input type="text" class="form-control" placeholder="Search">
                  </div>
                  <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>
                </form>

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->                            

                        <li><a href="#"><strong>New Order</strong></a></li>

                        @if (Auth::guest())
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>Category Product</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                                <li><a href="#">Produk Suplemen</a></li>
                                <li><a href="#">Produk Anak</a></li>
                                <li><a href="#">Produk Kulit</a></li>
                                <li><a href="#">Produk Laxative</a></li>
                                <li><a href="#">Produk Feminine Hygiene</a></li>
                                <li><a href="#">Produk Dewasa</a></li>
                                <li><a href="#">JF The Skin Spesialist</a></li>
                                <li><a href="#">Produk Oilum</a></li>
                                <li><a href="#">Produk Caladine</a></li>
                                <li><a href="#">Produk Belsoap</a></li>
                                <li><a href="#">Produk Caladine Baby</a></li>
                                <li><a href="#">Promo Merdeka</a></li>
                              </ul>
                            </li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
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

                        @if (Auth::guest())
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>Uplaod</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                                <li><a href="#">Upload Stock</a></li>
                                <li><a href="#">Upload Other Product</a></li>
                              </ul>
                            </li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
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

                        <li><a href="#"><i class="fa fa-bell" aria-hidden="true"></i>&nbsp; <strong>Notification</strong></a></li>

                        <li><a href="#"><i class="fa fa-shopping-cart" aria-hidden="true"></i>&nbsp; <strong>Shopping-Cart</strong></a></li>

                        @if (Auth::guest())
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <strong><i class="fa fa-user" aria-hidden="true"></i>&nbsp; User</strong>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <div class="navbar-login">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <p class="text-center">
                                                        <span class="glyphicon glyphicon-user icon-size"></span>
                                                    </p>
                                                </div>
                                                <div class="col-lg-8">
                                                    <p class="text-left"><strong>Galenium Pharmasia</strong></p>
                                                    <p class="text-left small">galenium_pharmasia@gmail.com</p>
                                                    <p class="text-left">
                                                        <a href="#" class="btn btn-primary btn-block btn-sm">View Account</a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <div class="navbar-login navbar-login-session">
                                                <div class="links">
                                                    <p>
                                                        <li><a href="http://localhost:8000/" class="external-link"><strong>LOGOUT</strong></a></li>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>

                                <ul class="dropdown-menu" role="menu">
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

    <!-- Scripts -->
	<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src='http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.js'></script>

    <script src="{{ asset('js/app.js') }}"></script>
	<script src="{{ asset('js/index.js') }}"></script>
    @include('utils.adsblock')
</body>
</html>
