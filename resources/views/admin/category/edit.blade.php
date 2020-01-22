@extends('layouts.tempAdminSB')
@section('content')
<h3>Edit Category</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
@if($errors->any())
  <div class="alert alert-info">
      {{$errors->first()}}
  </div>
@endif
<form action="{{route('CategoryOutlet.update',$category->id)}}" method="post" role="form">  
  {{csrf_field()}}

  <div class="form-group">
    <label for="name">Name of Category</label>
    <input type="text" class="form-control" name="name" id="" placeholder="Name of role" value="{{ old('name')?old('name'):$category->name }}">
    @if ($errors->has('name'))
        <span class="help-block">
            <strong>{{ $errors->first('name') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group">
    <label for="status">Status</label>
    @if($category->enable_flag=="Y")
     <input type="checkbox"  name="status" value="Y" checked="checked"> Active<br>
     @else
     <input type="checkbox"  name="status" value="Y"> Active<br>
     @endif
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
   <a href='{{route("CategoryOutlet.index")}}'><button type="button" class="btn btn-warning">Back to list</button></a>
</form>
@endsection
