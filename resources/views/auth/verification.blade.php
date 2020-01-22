@extends('layouts.navbar_product')

@section('content')

<div class="container">
    <div class="row">
      @if($status= Session::get('status'))
        <div class="alert alert-info">
            {{$status}}
        </div>
      @endif
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">@lang("label.welcome")</div>
                <div class="panel-body">
                  @if ($errors->has('longitude'))
                      <span class="help-block">
                          <strong>{{ $errors->first('longitude') }}</strong>
                      </span>
                  @elseif ($errors->has('langitude'))
                    <span class="help-block">
                        <strong>{{ $errors->first('langitude') }}</strong>
                    </span>
                  @endif
                    <form class="form-horizontal" method="POST" action="{{ route('register2') }}">
                        {{ csrf_field() }}
                        <input type="hidden" name="token1" value="{{$data['api_token']}}">
                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-md-4 control-label">* @lang("label.email")</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ $data['email'] }}" required readonly="readonly">

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="col-md-4 control-label">*@lang("label.newpassword")</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control" name="password" required>
                                <div style="font-size:8pt; color:red;">@lang('label.pwdminlength')</div>
                                @if ($errors->has('password'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password-confirm" class="col-md-4 control-label">* @lang("label.newconfpass")</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
								                <div style="font-size:8pt; color:red;">@lang('label.newconminlength')</div>
                                @if ($errors->has('password_confirmation'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password-confirm" class="col-md-4 control-label">* @lang('label.outlet_position'):</label>
                            <div class="col-md-6">
                              &nbsp;
                            </div>
                        </div>
                        <div class="form-group">
            							<div class="row" align="center">
                            <input id="pac-input" class="controls" type="text" placeholder="Search Box">
            								<div class="col-md-8 col-md-offset-1" id ="map"></div>
            								<input type="hidden" name="langitude" value="" id="langitude_txt">
            								<input type="hidden" name="longitude" value="" id="longitude_txt">
            							</div>
                        </div>
						<br>
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button type="submit" id="btnlogin" class="btn btn-primary">
                                    @lang("label.login")
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')

<script crossorigin="anonymous" integrity="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s=" src="https://code.jquery.com/jquery-3.1.0.min.js">
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDh9yEKw9W4sFrlTFFw_cZjvnAYSeMSa2w&language=id&libraries=places"
  async="" defer=""></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="{{asset('js/maps.js')}}"></script>
@endsection
