@extends(Auth::user()->hasRole('IT Galenium')?'layouts.tempAdminSB':'layouts.navbar_product')
@section('css')
<style>
#password + .glyphicon {
   cursor: pointer;
   pointer-events: all;
	 right:10px;
 }
</style>
@endsection
@section('content')
<!--<link href='//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' rel='stylesheet'/>-->
<div class="container">
	<legend><strong>My Profile</strong></legend>
	@if ($message = Session::get('message'))
		<div class="alert alert-success">
			<p>{{ $message }}</p>
		</div>
	@endif
	@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
  @endif
	@if ($errors = Session::get('error'))
		<div class="alert alert-warning">
			<p>{{ $errors }}</p>
		</div>
	@endif

	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<img src="{{asset('/uploads/avatars/'.$customer->avatar)}}" style="width:120px; height:120px; float:left; border-radius:50%; margin-right:25px; border-style: solid;" id="img_avatar">
			<h4><strong><u>Company</u></strong> : {{ $customer->name }}</h4>
			<form id="upload-profile" enctype="multipart/form-data"  method="POST">
				{{csrf_field()}}
				<input type="file" accept="image/*" name="avatar" id="fileavatar" style="display: none">
				<a href="javascript:changeProfile()" style="text-decoration: none;"><i
				class="glyphicon glyphicon-edit"></i> @lang('label.changeimage')</a>&nbsp;&nbsp;
				<!--<input type="submit" class=" pull-left btn btn-sm btn-primary">-->
			</form>
		</div>
	</div>
</div>

<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			<form class="form-horizontal" id="form-profile" action="{{route('profile.updateprofile')}}"  method="POST">
				{{csrf_field()}}
			<div class="form-inline row">
				<label class="col-sm-3 col-form-label" for="email"><strong>Email</strong> </label>
				<div class="col-sm-8">
					<input type="text" name="email" value="{{ $customer->email }}" class="form-control" style="width:100%" placeholder="Tax Number">
				</div>
			</div>


					@if(!is_null(Auth::user()->customer_id))
					<div class="form-inline row">
						<label class="col-sm-3 col-form-label" for="category"><strong>Category</strong> </label>
						<div class="col-sm-4">
							<input type="text" name="kategori" value="{{$customer->tipeoutlet}}" class="form-control form-control-sm" placeholder="Category" readonly>
						</div>
					</div>
					<div class="form-inline row">
						<label class="col-sm-3 col-form-label" for="tax"><strong>Tax ID</strong> </label>
						<div class="col-sm-4">
							<input type="text" name="tax" value="{{$customer->tax_reference}}" class="form-control form-control-sm" placeholder="Tax Number">
						</div>
					</div>
						@if($customer->psc_flag=="1" or $customer->pharma_flag=="1")
						<div class="form-inline row">
							<label class="col-sm-3 col-form-label" for="product"><strong>Product</strong> </label>
							<div class="col-sm-4">

									@if($customer->psc_flag=="1")
									  <input class="form-check-input" type="checkbox" id="inlineCheckbox1" value="1" checked disabled> PSC
										<input type="hidden" name="psc_flag" value="1">
									@else
										<input class="form-check-input" type="checkbox" id="inlineCheckbox1" name="psc_flag" value="1"> PSC
									@endif


									@if($customer->pharma_flag=="1")
									    <input class="form-check-input" type="checkbox" id="inlineCheckbox1"  value="1" checked disabled> Pharma
											<input type="hidden" name="pharma_flag" value="1">
									@else
									    <input class="form-check-input" type="checkbox" id="inlineCheckbox1" name="pharma_flag" value="1"> Pharma
									@endif
							</div>
						</div>

						@endif
					@endif
					<div class="form-inline row">
						<label class="col-sm-3 col-form-label" for="current_password"><strong>@lang('label.currentpass')</strong> </label>
						<div class="col-sm-8">
							<input type="password" id="password" name="current_pswd" class="form-control form-control-sm" placeholder="@lang('label.currentpass')" style="width:100%">
							<span class="glyphicon glyphicon-eye-open form-control-feedback" id="current_pswd"></span>
						</div>
					</div>
					<div class="form-inline row">
						<label class="col-sm-3 col-form-label" for="new_pswd"><strong>@lang('label.newpassword')</strong> </label>
						<div class="col-sm-8">
							<input type="password" id="password" name="new_pswd" class="form-control form-control-sm" placeholder="@lang('label.newpassword')" style="width:100%">
							<span class="glyphicon glyphicon-eye-open form-control-feedback" id="new_pswd"></span>
						</div>
					</div>
					<div class="form-inline row">
						<label class="col-sm-3 col-form-label" for="confirm_new_pswd"><strong>@lang('label.newconfpass')</strong></label>
						<div class="col-sm-8">
							<input type="password" id="password" name="confirm_new_pswd" class="form-control form-control-sm" placeholder="@lang('label.newconfpass')" style="width:100%">
							<span class="glyphicon glyphicon-eye-open form-control-feedback" id="confirm_new_pswd"></span>
						</div>
					</div>

						<div>
							<div class="col-sm-3">
								@if(!is_null(Auth::user()->customer_id))
								<button type="submit" name="Save" value="updateprofile" class="btn btn-sm btn-success">@lang('label.save')</button>
								@endif
								<button type="submit" name="Save" value="ChangePassword" class="btn btn-sm btn-info">Change Password</button>
							</div>
						</div>
		    </form>
			@if(!is_null(Auth::user()->customer_id))
				<!--UNTUK ALAMAT-->
				<h3><strong><u>Address</u></strong></h3>
				@if($customer_sites->count()>=1)
				<table id="address-list" class="display responsive nowrap" width="100%">
					<thead>
						<tr>
							<th>@lang('label.address')</th>
							<th>@lang('label.city_regency')</th>
							<th>@lang('label.subdistrict')</th>
							<th>@lang('label.postalcode')</th>
							<th>@lang('label.function')</th>
							<th class="col-sm-2">@lang('label.action')</th>
						</tr>
					</thead>
					<tbody>
						@foreach($customer_sites as $site)
						<tr>
							<td>{{$site->address1}}</td>
							<td>{{$site->city}}</td>
							<td>{{$site->state}}</td>
							<td>{{$site->postalcode}}</td>
							<td>
								@if($site->site_use_code=="BILL_TO")
								@lang('shop.BillTo')
								@else
								@lang('shop.ShipTo')
								@endif
							</td>
							<td><a class="btn btn-info btn-sm" href="{{route('profile.edit_address',$site->id)}}"><span class="glyphicon glyphicon-pencil"></span></a>
							{!! Form::open(['method' => 'DELETE','route' => ['profile.removeaddress', $site->id],'style'=>'display:inline']) !!}
							<!--  {!! Form::submit('Delete', ['class' => 'btn btn-sm btn-danger']) !!}-->
							{!! Form::button( '<i class="fa fa-trash-o"></i>', ['type' => 'submit','class' => 'btn btn-sm btn-danger'] ) !!}

							{!! Form::close() !!}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				@endif

				<div class="actions">
					<a class="btn btn-default" href="add_address">
						<span class="fa fa-plus" aria-hidden="true"></span>&nbsp;
						Add Address
					</a>
				</div>

				<!--UNTUK CONTACT-->
				<h3><strong><u>Contact</u></strong></h3>
				@if($customer_contacts->count()>=1)
				<table id="contact-list" class="display responsive nowrap" width="100%">
					<thead>
						<tr>
						<th>Tipe</th>
						<th>Kontak</th>
						<th>Nama Kontak</th>
						<th class="col-sm-2">Actions</th>
						</tr>
					</thead>
					<tbody>
						@foreach($customer_contacts as $contact)
						<tr>
						<td>{{$contact->contact_type}}</td>
						<td>{{$contact->contact}}</td>
						<td>{{$contact->contact_name}}</td>
						<td><a class="btn btn-info btn-sm" href="#"><span class="glyphicon glyphicon-pencil"></span></a>
						{!! Form::open(['method' => 'DELETE','route' => ['profile.removecontact', $contact->id],'style'=>'display:inline']) !!}
						<!--  {!! Form::submit('Delete', ['class' => 'btn btn-sm btn-danger']) !!}-->
						{!! Form::button( '<i class="fa fa-trash-o"></i>', ['type' => 'submit','class' => 'btn btn-sm btn-danger'] ) !!}

						{!! Form::close() !!}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
				@endif
				<div class="actions">
					<a class="btn btn-default" href="add_contact">
						<span class="fa fa-plus" aria-hidden="true"></span>&nbsp;
						Add Contact
					</a>
				</div>
			@endif
		</div>
	</div>
	<legend></legend>
</div>
@endsection
@section('js')
<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script type="text/javascript">
$(document).ready(function(){
	if($('#contact-list').length){
  	$('#contact-list').DataTable({
			 "paging":   false,
			 "searching": false,
		});
      window.setTimeout(function(){
          $(window).resize();
      },2000);
  }
	if($('#address-list').length){
  	$('#address-list').DataTable({
			"paging":   false,
			"searching": false,
		});
      window.setTimeout(function(){
          $(window).resize();
      },2000);
  }

});
</script>
	<script type="text/javascript">
		$('#password + .glyphicon').on('click', function() {
		  $(this).toggleClass('glyphicon-eye-close').toggleClass('glyphicon-eye-open'); // toggle our classes for the eye icon
			var v_input=$("input[name="+$(this).attr("id")+"]");
			if (v_input.attr("type") == "password") {
		    v_input.attr("type", "text");
		  } else {
		    v_input.attr("type", "password");
		  }
		});
		function changeProfile() {
		$('#fileavatar').click();
		}
		function upload() {
		var file_data = $('#fileavatar').prop('files')[0];
		//	alert("{{asset('uploads/loader.gif')}}");
		var form_data = new FormData();
		form_data.append('avatar', file_data);
		$.ajaxSetup({
		headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')}
		});
		$('#img_avatar').attr('src', "{{asset('/uploads/loader.gif')}}");
		$.ajax({
		url: "{{url('/profile')}}", // point to server-side PHP script
		data: form_data,
		type: 'POST',
		contentType: false,       // The content type used when sending data to the server.
		cache: false,             // To unable request pages to be cached
		processData: false,
		success: function (data) {
		if (data.fail) {
		$('#img_profile').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
		$('#img_profile2').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
		$('#img_avatar').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
		alert(data.errors['avatar']);
		}
		else {
		filename = data.filename;
		$('#img_profile').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+filename);
		$('#img_profile2').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
		$('#img_avatar').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+filename);
		}
		},
		error: function (xhr, status, error) {
		alert(xhr.responseText);
		$('#img_profile').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
		$('#img_profile2').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
		$('#img_avatar').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
		}
		});
		}
		$("#fileavatar").change(function(){
		if ($(this).val() != '') {
		upload();
		//readURL(this);
		// });
		}
		});
	</script>
@endsection
