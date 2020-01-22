@extends('layouts.home')

@section('content')

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel='stylesheet prefetch' href='http://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.0.1/sweetalert.min.css'>
<style>
input.invalid {
  background-color: #ffdddd;
}
.tab {
  display: none;
}
button {
  background-color: #4CAF50;
  color: #ffffff;
  border: none;
  padding: 10px 20px;
  font-size: 17px;
  font-family: Raleway;
  cursor: pointer;
}
button:hover {
  opacity: 0.8;
}
#prevBtn {
  background-color: #bbbbbb;
}
/* Make circles that indicate the steps of the form: */
.step {
  height: 15px;
  width: 15px;
  margin: 0 2px;
  background-color: #bbbbbb;
  border: none;
  border-radius: 50%;
  display: inline-block;
  opacity: 0.5;
}
.step.active {
  opacity: 1;
}
/* Mark the steps that are finished and valid: */
.step.finish {
  background-color: #4CAF50;
}
</style>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-primary">
                <div class="panel-heading panel-blue">@lang('label.register')</div>
                <div class="panel-body">
                    <form class="form-horizontal" id="regForm" role="form" method="POST" action="{{ route('register') }}">
                        {{ csrf_field() }}
                       <div class="form-horizontal col-sm-10">
                        <div class="tab">
                        <div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
                            <label for="name" class="col-sm-4 control-label">*@lang('label.outlet')</label>

                            <div class="col-sm-8">
                                <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                            <label for="email" class="col-sm-4 control-label">*@lang('label.email')</label>

                            <div class="col-sm-8">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        </div>
                        <div class="tab">
                        <div class="form-group{{ $errors->has('address') ? ' has-error' : '' }}">
                            <label for="outlet" class="col-sm-4 control-label">*@lang('label.address')</label>

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
                          <label for="province" class="col-sm-4 control-label">*@lang('label.province')</label>

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
                            <label for="city" class="col-sm-4 control-label">*@lang('label.city')</label>

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
                            <label for="district" class="col-sm-4 control-label">*@lang('label.regency')</label>

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

						            <div class="form-group{{ $errors->has('districts') ? ' has-error' : '' }}">
                            <label for="subdistricts" class="col-sm-4 control-label">*@lang('label.state')</label>

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
                            <label for="postal_code" class="col-sm-4 control-label">*@lang('label.postalcode')</label>

                            <div class="col-sm-8">
                                <input id="postal_code" type="postal_code" class="form-control" name="postal_code" value="{{ old('postal_code') }}" required>

                                @if ($errors->has('postal_code'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('postal_code') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        </div>
                        <div class="tab">
                        <!--<div class="form-group{{ $errors->has('contact_person') ? ' has-error' : '' }}">
                           <label for="contact_person" class="col-sm-4 control-label">*@lang('label.cp')</label>

                           <div class="col-sm-8">
                               <input id="contact_person" type="contact_person" class="form-control" name="contact_person" value="{{ old('contact_person') }}">
                               @if ($errors->has('contact_person'))
                                   <span class="help-block">
                                       <strong>{{ $errors->first('contact_person') }}</strong>
                                   </span>
                               @endif
                           </div>
                        </div>-->
                        <div class="form-group{{ $errors->has('HP_1') ? ' has-error' : '' }}">
                           <label for="HP_1" class="col-sm-4 control-label">*@lang('label.hp1')</label>

                           <div class="col-sm-8">
                               <div class="row">
                                   <div class="col-xs-2 country-code">
                                       <input id="HP_1" type="HP_1" class="form-control" name="ext_HP1" fieldset disabled placeholder="+62" required>
                                   </div>
                                   <div class="col-xs-10">
                                       <input id="HP_1" type="HP_1" class="form-control" name="HP_1" value="{{ old('HP_1') }}" required>
                                   </div>
                               </div>

                               @if ($errors->has('HP_1'))
                                   <span class="help-block">
                                       <strong>{{ $errors->first('HP_1') }}</strong>
                                   </span>
                               @endif
                           </div>
                         </div>
<!--
                        <div class="form-group{{ $errors->has('HP_2') ? ' has-error' : '' }}">
                           <label for="HP_2" class="col-sm-4 control-label">@lang('label.hp2')</label>

                           <div class="col-sm-8">
                               <div class="row">
                                   <div class="col-xs-2 country-code">
                                       <input id="HP_2" type="HP_2" class="form-control" name="ext_HP2" fieldset disabled placeholder="+62" required>
                                   </div>
                                   <div class="col-xs-10">
                                       <input id="HP_2" type="HP_2" class="form-control" name="HP_2" value="{{ old('HP_2') }}">
                                   </div>
                               </div>

                               @if ($errors->has('HP_2'))
                                   <span class="help-block">
                                       <strong>{{ $errors->first('HP_2') }}</strong>
                                   </span>
                               @endif
                           </div>
                         </div>

                        <div class="form-group{{ $errors->has('no_tlpn') ? ' has-error' : '' }}">
                           <label for="no_tlpn" class="col-sm-4 control-label">@lang('label.phone')</label>

                           <div class="col-sm-8">
                               <div class="row">
                                   <div class="col-xs-2 country-code">
                                       <input id="no_tlpn" type="no_tlpn" class="form-control" name="extnotelp" fieldset disabled placeholder="+62" required>
                                   </div>
                                   <div class="col-xs-10">
                                       <input id="no_tlpn" type="no_tlpn" class="form-control" name="no_tlpn" value="{{ old('no_tlpn') }}">
                                   </div>
                               </div>

                               @if ($errors->has('no_tlpn'))
                                   <span class="help-block">
                                       <strong>{{ $errors->first('no_tlpn') }}</strong>
                                   </span>
                               @endif
                           </div>
                         </div>-->

                      <!--  <div class="form-group{{ $errors->has('NPWP') ? ' has-error' : '' }}">
                           <label for="NPWP" class="col-sm-4 control-label">@lang('label.npwp')</label>

                           <div class="col-sm-8">
                               <input id="NPWP" type="NPWP" class="form-control" name="NPWP" value="{{ old('NPWP') }}">

                               @if ($errors->has('NPWP'))
                                   <span class="help-block">
                                       <strong>{{ $errors->first('NPWP') }}</strong>
                                   </span>
                               @endif
                           </div>
                        </div>-->

                        <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                           <label for="kategori" class="col-sm-4 control-label">*@lang('label.category')</label>

                           <div class="col-sm-8">
                             <select class="form-control" name="category" required>
                               @forelse($categories as $category)
                                 @if(old('category')==$category->name)
                                   <option selected='selected' value="{{$category->id}}">{{$category->name}}</option>
                                 @else
                                   <option value="{{$category->id}}">{{$category->name}}</option>
                                 @endif
                               @empty
                               <tr><td colspan="4">No Category</td></tr>
                               @endforelse
                             </select>
                               @if ($errors->has('kategori'))
                                   <span class="help-block">
                                       <strong>{{ $errors->first('kategori') }}</strong>
                                   </span>
                               @endif
                           </div>
                        </div>

                        <div class="form-group{{ $errors->has('psc') ? ' has-error' : '' }}">
                           <label for="PSY" class="col-sm-4 control-label">*@lang('label.needproduct')</label>

                           <div class="col-sm-8">
                               <div class="checkbox">
                                 <label>
                                   @if( old('psc')=="1")
                                     <input type="checkbox" id="blankCheckbox" value="1" name="psc" checked="checked">
                                   @else
                                     <input type="checkbox" id="blankCheckbox" value="1" name="psc">
                                   @endif
                                   <strong><a href="#" class="external-psc">Personal Skin Care (PSC)</a>
                                 </strong><div style="font-size:8pt">(Caladine, Oilum, v-mina, Bellsoap, JFSulfur)</div></label>
                               </div>


                               <div class="checkbox">
                                 <label>
                                   @if( old('pharma')=="1")
                                     <input type="checkbox" id="blankCheckbox" value="1" name="pharma" checked="checked">
                                   @else
                                     <input type="checkbox" id="blankCheckbox" value="1" name="pharma">
                                   @endif
                                   <strong><a href="#" class="external-pharma">Pharma (NON PSC)</a>
                                 </strong></label>
                               </div>

                               @if ($errors->has('psc'))
                                   <span class="help-block">
                                       <strong>{{ $errors->first('psc') }}</strong>
                                   </span>
                               @endif
                           </div>
                        </div>



                        <!--<div class="form-group">
                           <label for="img" class="col-sm-4 control-label">Photo</label>

                           <div class="col-sm-8">
                                 <img src="" style="width:70px; height:70px; float:left; border-radius:50%; margin-right:25px; border-style: solid;" id="imgphoto" >
                               <input type="file" name="imgphoto" id="fileavatar" value="" accept="image/*">
                               <span style="font-size:8pt">File type: jpeg,jpg,png</span>
                           </div>
                        </div>-->
                       </div>
                       </div/>

                        <!-- Circles which indicates the steps of the form: -->
                        <div class="form-group">
                            <div class="col-sm-12">
                              <div style="text-align:center;margin-top:40px;">
                                <span class="step"></span>
                                <span class="step"></span>
                                <span class="step"></span>                              
                              </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                              <!--<div style="overflow:auto;">
                                 <div style="float:right;">-->
                                   <button type="button" id="prevBtn" onclick="nextPrev(-1)">Previous</button>
                                   <button type="button" id="nextBtn" onclick="nextPrev(1)" style="float:right;">Next</button>
                                <!-- </div>
                               </div>
                                <button type="submit" class="btn btn-primary">
                                    @lang('label.register')
                                </button>-->
                            </div>
                        </div>
                        <div class="col-sm-12 has-error"><span class="help-block">* @lang('label.mandatoryfield')</span></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
    function readURL(input) {
      if (input.files && input.files[0]) {
          var reader = new FileReader();
          reader.onload = function (e) {
              $('#imgphoto').attr('src', e.target.result);
          }
          reader.readAsDataURL(input.files[0]);
      }
    }
    $("#fileavatar").change(function(){
      if ($(this).val() != '') {
          readURL(this);
      }
    });
</script>
<script src="{{ asset('js/register.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#name').keyup(function(){
          $("#name").val(($("#name").val()).toUpperCase());
        });

      getListCity({{is_null(old('province'))?0:old('province')}},{{is_null(old('city'))?0:old('city')}});
      getListDistrict({{is_null(old('city'))?0:old('city')}},{{is_null(old('district'))?0:old('district')}});
      getListSubdistrict({{is_null(old('district'))?0:old('district')}},{{is_null(old('subdistricts'))?0:old('subdistricts')}});
    });
</script>

@endsection
