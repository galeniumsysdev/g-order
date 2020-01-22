@extends('layouts.tempAdminSB')
@section('content')
<h3>Create New Permission</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<form action="{{route('permission.store')}}" method="post" role="form">
       {{csrf_field()}}

     <div class="form-group">
       <label for="name">Name of permission</label>
       <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" placeholder="Name of permission">
       @if ($errors->has('name'))
           <span class="help-block with-errors">
               <strong>{{ $errors->first('name') }}</strong>
           </span>
       @endif
     </div>
       <div class="form-group">
       <label for="display_name">Display name</label>
       <input type="text" class="form-control" name="display_name" value="{{ old('display_name') }}" id="display_name" placeholder="Display name">
     </div>
       <div class="form-group">
       <label for="description">Description</label>
       <input type="text" class="form-control" name="description" value="{{ old('description') }}" id="description" placeholder="Description">
     </div>
     <button type="submit" class="btn btn-primary">Submit</button>
   </form>
@endsection
