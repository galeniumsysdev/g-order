@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
  <div class="panel panel-default">
      <div class="panel-heading">Distributor Cabang</div>
      <div class="panel-body">
          <form action="{{route('usercabang.store',$parent->id)}}" class="form-horizontal" method="post" role="form">
            {{ csrf_field() }}
            <div class="form-group">
              <label class="control-label col-sm-2" for="name">Distributor Pusat :</label>
              <div class="col-sm-10">
                <input type="text"  class="form-control" name = "dist_pusat" value="{{$parent->customer_name}}" readonly>
              </div>
            </div>
            <div class="form-group{{ $errors->has('customer_name') ? ' has-error' : '' }}">
              <label class="control-label col-sm-2" for="name">@lang('label.outlet') :</label>
              <div class="col-sm-10">
                <input type="text"  class="form-control" name = "customer_name" value="{{old('customer_name')}}">
                @if ($errors->has('customer_name'))
                    <span class="help-block">
                        <strong>{{ $errors->first('customer_name') }}</strong>
                    </span>
                @endif
              </div>
            </div>
            <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
              <label class="control-label col-sm-2" for="email">@lang('label.email') :</label>
              <div class="col-sm-10">
                <input type="text" class="form-control disabled" name="email" value="{{old('email')}}">
                @if ($errors->has('email'))
                    <span class="help-block">
                        <strong>{{ $errors->first('email') }}</strong>
                    </span>
                @endif
              </div>
            </div>
            <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                <label for="outlet" class="col-sm-2 control-label">*@lang('label.address')</label>

                <div class="col-sm-8">
                    <textarea id="address" rows="3" class="form-control" name="address" required>{{ old('address') }}</textarea>

                    @if ($errors->has('address'))
                        <span class="help-block">
                            <strong>{{ $errors->first('address') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group{{ $errors->has('province') ? ' has-error' : '' }}">
              <label for="province" class="col-sm-2 control-label">*@lang('label.province')</label>

              <div class="col-sm-8">
                  <select name="province" class="form-control" id="province" onchange="getListCity(this.value,{{old('city')}})" required>
                    <option value="">--</option>
                    @foreach($provinces as $province)
                      @if(old('province')==$province->id)
                        <option selected='selected' value="{{$province->id}}">{{$province->name}}</option>
                      @else
                        <option value="{{$province->id}}">{{$province->name}}</option>
                      @endif
                    @endforeach
                  </select>

                  @if ($errors->has('province'))
                      <span class="help-block">
                          <strong>{{ $errors->first('province') }}</strong>
                      </span>
                  @endif
              </div>
            </div>

            <div class="form-group{{ $errors->has('city') ? ' has-error' : '' }}">
                <label for="city" class="col-sm-2 control-label">*@lang('label.city_regency')</label>

                <div class="col-sm-8">
                  <select name="city" class="form-control" id="city" onchange="getListDistrict(this.value,{{old('district')}})" required>
                    <option value="">--</option>
                  </select>
                    <!--<input id="city" type="city" class="form-control" name="city" value="{{ old('city') }}" required>-->

                    @if ($errors->has('city'))
                        <span class="help-block">
                            <strong>{{ $errors->first('city') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('district') ? ' has-error' : '' }}">
                <label for="district" class="col-sm-2 control-label">*@lang('label.subdistrict')</label>

                <div class="col-sm-8">
                    <!--<input id="regency" type="regency" class="form-control" name="regency" value="{{ old('regency') }}" required>-->
                    <select name="district" class="form-control" id="district" onchange="getListSubdistrict(this.value,{{old('subdistricts')}})" required>
                      <option value="">--</option>
                    </select>

                    @if ($errors->has('district'))
                        <span class="help-block">
                            <strong>{{ $errors->first('district') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('subdistricts') ? ' has-error' : '' }}">
                <label for="subdistricts" class="col-sm-2 control-label">*@lang('label.urban_village')</label>

                <div class="col-sm-8">
                  <select name="subdistricts" class="form-control" id="subdistricts" required>
                    <option value="">--</option>
                  </select>
                    <!--<input id="districts" type="districts" class="form-control" name="districts" value="{{ old('districts') }}">-->

                    @if ($errors->has('subdistricts'))
                        <span class="help-block">
                            <strong>{{ $errors->first('subdistricts') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group{{ $errors->has('postal_code') ? ' has-error' : '' }}">
                <label for="postal_code" class="col-sm-2 control-label">@lang('label.postalcode')</label>

                <div class="col-sm-8">
                    <input id="postal_code" type="postal_code" class="form-control" name="postal_code" value="{{ old('postal_code') }}">

                    @if ($errors->has('postal_code'))
                        <span class="help-block">
                            <strong>{{ $errors->first('postal_code') }}</strong>
                        </span>
                    @endif
                </div>
            </div>


          <div class="form-group">
              <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary">
                      Create
                  </button>
              </div>
          </div>
          </form>
      </div>
  </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/register.js') }}"></script>
<script type="text/javascript">
$(document).ready(function() {  
  getListCity({{is_null(old('province'))?0:old('province')}},{{is_null(old('city'))?0:old('city')}});
  getListDistrict({{is_null(old('city'))?0:old('city')}},{{is_null(old('district'))?0:old('district')}});
  getListSubdistrict({{is_null(old('district'))?0:old('district')}},{{is_null(old('subdistricts'))?0:old('subdistricts')}});
});
</script>
@endsection
