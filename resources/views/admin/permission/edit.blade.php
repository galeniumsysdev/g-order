@extends('layouts.tempAdminSB')
@section('content')
<h3>Edit Permission</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<form action="{{route('permission.update',$permission->id)}}" method="post" role="form">
  {{method_field('PATCH')}}
  {{csrf_field()}}

	<div class="form-group">
		<label for="name">Name of Permission</label>
		<input type="text" class="form-control" name="name" id="" placeholder="Name of role" value="{{$permission->name}}">
    @if ($errors->has('name'))
        <span class="help-block">
            <strong>{{ $errors->first('name') }}</strong>
        </span>
    @endif
	</div>
    <div class="form-group">
		<label for="display_name">Display name</label>
		<input type="text" class="form-control" name="display_name" id="" value="{{$permission->display_name}}" placeholder="Display name">
	</div>
    <div class="form-group">
		<label for="description">Description</label>
		<input type="text" class="form-control" name="description" id="" placeholder="Description" value="{{$permission->description}}">
	</div>

	<button type="submit" class="btn btn-primary">Submit</button>
</form>
@endsection
