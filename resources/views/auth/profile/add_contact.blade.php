@extends('layouts.navbar_product')

@section('content')

<div class="container">
	<legend><strong>@lang('label.addcontact')</strong></legend>

	<div class="row">
    <div class="col-md-4">
      <form class="form-horizontal" role="form" method="post" action="{{route('profile.contact')}}">
				{{ csrf_field() }}
        <fieldset>

				<div class="container">
		          <div class="form-group{{ $errors->has('cp') ? ' has-error' : '' }}">
		            <label class="col-sm-2 control-label" for="textinput">@lang('label.cp')</label>
		            <div class="col-sm-10">
		              <input type="text" name="cp" placeholder="Nama Kontak" class="form-control" value="{{old('cp')}}" required>
									@if ($errors->has('cp'))
											<span class="help-block with-errors">
													<strong>{{ $errors->first('cp') }}</strong>
											</span>
									@endif
		            </div>
		          </div>
				</div>


				<div class="container">
		          <div class="form-group{{ $errors->has('tipe_kontak') ? ' has-error' : '' }}">
		            <label class="col-sm-2 control-label" for="textinput">@lang('label.type')</label>
		            <div class="col-sm-10">
		              <!--<input type="text" placeholder="Tipe" class="form-control">-->
									<select name="tipe_kontak" class="form-control" placeholder="Tipe">
										<option value="">......</option>
										<option value="Phone" {{old('tipe_kontak')=="Phone"?"selected=selected":""}}>Phone</option>
										<option value="HP" {{old('tipe_kontak')=="HP"?"selected=selected":""}}>HP</option>
										<option value="Fax" {{old('tipe_kontak')=="Fax"?"selected=selected":""}}>Fax</option>
										<!--<option value="Fax">Email</option>-->
									</select>
									@if ($errors->has('tipe_kontak'))
											<span class="help-block with-errors">
													<strong>{{ $errors->first('tipe_kontak') }}</strong>
											</span>
									@endif

		            </div>
		          </div>
				</div>

				<div class="container">
		          <div class="form-group{{ $errors->has('no_tlpn') ? ' has-error' : '' }}">
		            <label class="col-sm-2 control-label" for="textinput">@lang('label.contact')</label>
		            <div class="col-sm-10">
									<!--<input type="text" name="data-kontak" placeholder="Number" class="form-control" required>-->
									<div class="row" id="no_tlpn">
									<div class="col-xs-2 country-code">
											<input type="no_tlpn" class="form-control" name="extnotelp" fieldset disabled placeholder="+62" required>
									</div>
									<div class="col-xs-10">
											<input type="no_tlpn" class="form-control" name="no_tlpn" value="{{ old('no_tlpn') }}">
											@if ($errors->has('no_tlpn'))
													<span class="help-block with-errors">
															<strong>{{ $errors->first('no_tlpn') }}</strong>
													</span>
											@endif
									</div>
								</div>
		            </div>
		          </div>
				</div>

          <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
              <div class="pull-right">
              	<button type="submit" class="btn btn-success">@lang('label.save')</button>
                <a href="{{URL::previous()}}"><button type="button" class="btn btn-danger">@lang('label.cancel')</button></a>
              </div>
            </div>
          </div>

        </fieldset>
      </form>
    </div><!-- /.col-lg-12 -->
</div><!-- /.row -->
<div class="container">
	<legend></legend>
</div>
</div>
@endsection
