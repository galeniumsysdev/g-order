<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
      <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Galenium Markeplace for order">
    <meta name="author" content="Pt. Solusi Integrasi Persada">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Simple Responsive Admin</title>
	<!-- BOOTSTRAP STYLES-->
    <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet" />
     <!-- FONTAWESOME STYLES-->
    <link href="{{asset('font-awesome/css/font-awesome.css')}}" rel="stylesheet" />
        <!-- CUSTOM STYLES-->
    <link href="{{asset('css/sb-admin.css')}}" rel="stylesheet">
     <!-- Custom FONTS-->
    <link href="{{asset('font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css">
    <link rel='stylesheet prefetch' href='http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.css'>
    @yield('css')
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
  <div id="app">
  <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{{ url('/') }}"><img class="img-header" src="{{ URL::to('img/logoe1.png') }}"/></a>
            </div>
            <!-- Top Menu Items -->
            <ul class="nav navbar-right top-nav">
              <!--  <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> <b class="caret"></b></a>
                    <ul class="dropdown-menu message-dropdown">
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading">
                                            <strong>John Smith</strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading">
                                            <strong>John Smith</strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-preview">
                            <a href="#">
                                <div class="media">
                                    <span class="pull-left">
                                        <img class="media-object" src="http://placehold.it/50x50" alt="">
                                    </span>
                                    <div class="media-body">
                                        <h5 class="media-heading">
                                            <strong>John Smith</strong>
                                        </h5>
                                        <p class="small text-muted"><i class="fa fa-clock-o"></i> Yesterday at 4:32 PM</p>
                                        <p>Lorem ipsum dolor sit amet, consectetur...</p>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <li class="message-footer">
                            <a href="#">Read All New Messages</a>
                        </li>
                    </ul>
                </li>-->
                <notification class="dropdown-toggle" :email="{{json_encode(Auth::user()->email)}}" :count="{{json_encode(count(Auth::user()->unreadNotifications))}}" :notif="{{json_encode(Auth::user()->unreadNotifications->take(5))}}"></notification>
                <!--<li class="dropdown">

                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i> <b class="badge">{{json_encode(count(Auth::user()->unreadNotifications))}}</b></a>
                    <ul class="dropdown-menu alert-dropdown">
                      @foreach(Auth::user()->unreadNotifications as $notif)
                        <li>

                        </li>
                      @endforeach
                        <li class="divider"></li>
                        <li>
                            <a href="#">View All</a>
                        </li>
                    </ul>
                </li>-->
                <li class="dropdown">

                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i>
                      @if (Auth::guest())
                        <b>Not login</b>
                      @else
                        {{ Auth::user()->name }}
                      @endif
                      <b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{{route('profile.index')}}"><i class="fa fa-fw fa-user"></i> Profile</a>
                        </li>
                        <!--<li>
                            <a href="#"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa fa-fw fa-gear"></i> Settings</a>
                        </li>-->
                        <li class="divider"></li>
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
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                @include('layouts.menuadmin')
            </div>
            <!-- /.navbar-collapse -->
        </nav>

        <div id="page-wrapper">

            <div class="container-fluid">
                <!-- Page Heading -->
                  @yield('content')
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->

        </div>
        <!-- /#page-wrapper -->
      </div>
    </div>
     <!-- /. WRAPPER  -->
    <!-- SCRIPTS -AT THE BOTOM TO REDUCE THE LOAD TIME-->
    <!-- JQUERY SCRIPTS -->
    <script type="text/javascript">
    function redirectToHome() {
      window.location.href = "{{url('/')}}"+'/home';
    }
    </script>
    <script src="{{asset('js/jquery.js')}}"></script>
      <!-- BOOTSTRAP SCRIPTS -->
    <!--<script src="{{asset('js/bootstrap.min.js')}}"></script>-->
    <script src="http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @yield('js')

</body>
</html>
