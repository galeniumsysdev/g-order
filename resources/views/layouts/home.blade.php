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
		<!--<link rel="stylesheet" href="product.css">-->
		<link href="https://fonts.googleapis.com/css?family=Quicksand:300,400,500,700&subset=latin-ext,vietnamese" rel="stylesheet">
  	<link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Pacifico&subset=latin-ext,vietnamese" rel="stylesheet">
  	<link rel='stylesheet prefetch' href='http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.css'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!--<link href="{{ asset('css/app20170913.css') }}" rel="stylesheet">-->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::to('css/mystyle.css') }}">
    <link rel="stylesheet" href="{{ URL::to('css/loading.css') }}">

    <!-- Scripts -->
    <script>
      window.Laravel = {
                   csrfToken: '{{csrf_token()}}',
                   url: "{{url('/')}}",
                   auth: {
                       user: '{{auth()->user()}}'
                   }
                }
    </script>
</head>
<body>
    <div>
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
                    @if(Auth::guest())
                      <a class="navbar-brand" href="{{ url('/') }}" style="color:#fff">
                    @else
                      @if(Auth::user()->hasRole('Marketing PSC')||Auth::user()->hasRole('Marketing Pharma')||Auth::user()->hasRole('Principal'))
                        <a class="navbar-brand" href="{{ url('/home') }}" style="color:#fff">
                      @else
                        <a class="navbar-brand" href="{{ url('/') }}" style="color:#fff">
                      @endif
                    @endif
                        <strong>
						<img class="img-header" src="{{ URL::to('img/logoe1.png') }}"/>
						</strong></a>
                    </a>
                </div>
                @if (!Auth::guest())
                <form method="post" action="{{route('product.search')}}" class="navbar-form navbar-left" role="search">
                   {{csrf_field()}}
                  <div class="form-group">
                    <input type="text" name="search_product" class="form-control" placeholder="Search">
                  </div>
                  <button type="submit" class="btn btn-default"><i class="fa fa-search" aria-hidden="true"></i></button>
                </form>
                @endif

                <div class="collapse navbar-collapse" id="app-navbar-collapse">
                    <!-- Left Side Of Navbar -->

                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right" >
                        <!-- Authentication Links -->

                        @if (Auth::guest())
                          @if(isset($product_flexfields))
                          <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>Category Product</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                            <ul class="dropdown-menu">
                              @foreach($product_flexfields as $flexfield)
                                <li><a href="{{route('product.category',$flexfield->flex_value_id)}}">{{$flexfield->description}}</a></li>
                              @endforeach
                            </ul>
                          </li>
                          @endif
                        <li class="dropdown">
                          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp; <strong>{{ app()->getLocale() }}</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                          <ul class="dropdown-menu">
                            <li id="LI_201">
                                <a href="#" id="idLang" onclick="changeLanguage('id');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/indonesia.png" alt="" width="20" height="20" id="IMG_203" /><span id="SPAN_204"><span id="SPAN_205">&nbsp; Indonesia (id)</span></span></a>
                            </li>
                            <li id="LI_489">
                              <a href="#" id="enLang" onclick="changeLanguage('en');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/united_kingdom.png" alt="" width="20" height="20" id="IMG_491" /><span id="SPAN_492"><span id="SPAN_493">&nbsp; English (en)</span></span></a>
                            </li>
                          </ul>
                        </li>
                        <li><a href="{{ route('login') }}"><strong><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp; @lang('label.login')</strong></a></li>
                        <li><a href="{{ route('register') }}"><strong><i class="fa fa-user-plus" aria-hidden="true"></i>&nbsp; @lang('label.register')</strong></a></li>
                        @else
                            @if(Auth::user()->can('Create PO'))
                                <li><a href="#"><strong>New Order</strong></a></li>

                                <li><a href="#"><i class="fa fa-shopping-cart" aria-hidden="true"></i>&nbsp; <strong>Shopping-Cart</strong></a></li>
                            @endif
                            @if (Auth::user()->can('POS'))
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><strong>Uplaod</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                                <li><a href="#">Upload Stock</a></li>
                                <li><a href="#">Upload Other Product</a></li>
                              </ul>
                            </li>
                            @endif
                            @if(Auth::user()->hasRole('Marketing Pharma'))

                            @endif
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-flag" aria-hidden="true"></i>&nbsp; <strong>{{ app()->getLocale() }}</strong>&nbsp; <i class="fa fa-caret-down" aria-hidden="true"></i></a>
                              <ul class="dropdown-menu">
                                <li id="LI_201">
                                    <a href="#" id="idLang" onclick="changeLanguage('id');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/indonesia.png" alt="" width="20" height="20" id="IMG_203" /><span id="SPAN_204"><span id="SPAN_205">&nbsp; Indonesia (id)</span></span></a>
                                </li>
                                <li id="LI_489">
                                  <a href="#" id="enLang" onclick="changeLanguage('en');return false;"><img src="https://images.apple.com/support/assets/images/layout/icons/flags/country/united_kingdom.png" alt="" width="20" height="20" id="IMG_491" /><span id="SPAN_492"><span id="SPAN_493">&nbsp; English (en)</span></span></a>
                                </li>
                              </ul>
                            </li>
                        <!--     {{--notification--}} -->
                            <!-- <notification :email="{!! json_encode(Auth::user()->email) !!}"></notification> -->
                            <notification :homeurl="lol" :email="{{json_encode(Auth::user()->email)}}" :count="{{json_encode(count(Auth::user()->unreadNotifications))}}" :notif="{{json_encode(Auth::user()->unreadNotifications->take(5))}}"></notification>
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="position:relative; padding-left:50px">
									                         <img id="img_profile2" src="{{asset('/uploads/avatars/'.Auth::user()->avatar) }}" style="width:32px; height:32px; position:absolute; top:8px; left:10px; border-radius:50%"><i class="#" aria-hidden="true"></i><strong>&nbsp; User</strong>
									<!--{{ Auth::user()->name }}-->
								                </a>
								                <ul class="dropdown-menu">
                                    <li>
                                        <div class="navbar-login">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" style="position:relative; padding-left:50px">
														<img id="img-profile" src="{{asset('/uploads/avatars/'.Auth::user()->avatar) }}" style="width:32px; height:32px; position:absolute; top:8px; left:10px; border-radius:50%">
														<!--{{ Auth::user()->name }}-->
													</a>
                                                </div>
                                                <div class="col-lg-8">
                                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
														<strong>{{ Auth::user()->name }}</strong>
													</a>

													<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
														{{ Auth::user()->email }}
													</a>
												</div>
                                            </div>
                                        </div>
                                    </li>
									<li class="divider"></li>
                                    <li>
                                        <div class="navbar-login navbar-login-session">
                                                <p class="text-left">
                                                    <a href="{{route('profile.index')}}" class="btn btn-black btn-sm">View Account</a>
                                                    <a href="{{ route('logout') }}" class="pull-right btn btn-black btn-sm"
                                                        onclick="event.preventDefault();
                                                                 document.getElementById('logout-form').submit();">
                                                        @lang('label.logout')
                                                    </a>
                                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                        {{ csrf_field() }}
                                                    </form>
                                                </p>
                                            </div>
                                        </div>
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
    <!--@yield('footer')-->
    @include('layouts.footer')

    <!-- Scripts -->

	<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src='http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.js'></script>
@yield('js')
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/customlang.js') }}"></script>
    <script src="{{ asset('js/index.js') }}"></script>
    <script src="{{ asset('js/sweetalert.js') }}"></script>
    @include('utils.adsblock')
</body>
</html>
