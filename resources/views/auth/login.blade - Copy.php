@extends('layouts.home')

@section('content')
<link rel="stylesheet" href="{{ URL::to('css/login.css') }}">
<div class="container">
    <div class="row">
      <div class="container">
        @if($status= Session::get('status'))
          <div class="alert alert-info">
              {{$status}}
          </div>
        @endif
        @if($errors->has('credentials'))
        <div class="alert alert-info">
            {{ $errors->first('credentials') }}
        </div>
        @endif
        <div class="card card-container">
          <p id="profile-name" class="profile-name-card">@lang('label.login')</p>
                  <form class="form-horizontal" role="form" method="POST" action="{{ route('login') }}">
                      {{ csrf_field() }}

                      <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                          <!--<label for="email" class="form-control control-label">E-Mail Address</label>-->

                          <!--<div class="col-md-6">-->
                              <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="@lang('label.email')" required autofocus>

                              @if ($errors->has('email'))
                                  <span class="help-block">
                                      <strong>{{ $errors->first('email') }}</strong>
                                  </span>
                              @endif
                          <!--</div>-->
                      </div>

                      <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                          <!--<label for="password" class="col-md-4 control-label">Password</label>-->

                          <!--<div class="col-md-6">-->
                              <input id="password" type="password" class="form-control" name="password" placeholder="@lang('label.password')" required>

                              @if ($errors->has('password'))
                                  <span class="help-block">
                                      <strong>{{ $errors->first('password') }}</strong>
                                  </span>
                              @endif
                          <!--</div>-->
                      </div>

                      <div class="form-group">
                          <!--<div class="col-md-6 col-md-offset-4">-->
                              <div id="remember" class="checkbox">
                                  <label>
                                      <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> @lang('label.remember')
                                  </label>
                              </div>
                          <!--</div>-->
                      </div>

                      <div class="form-group">
                          <!--<div class="col-md-8 col-md-offset-4">-->
                              <button type="submit" class="btn btn-lg btn-primary btn-block btn-signin">
                                  @lang('label.login')
                              </button>

                              <a class="btn btn-link" href="{{ route('password.request') }}">
                                  @lang('label.forgotpassword')
                              </a>
                          <!--</div>-->
                      </div>
                    </form>
            </div>
        </div>
    </div>
</div>

@endsection
