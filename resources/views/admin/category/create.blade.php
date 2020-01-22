@extends('layouts.tempAdminSB')
@section('content')
<h3>Create New Category Outlet</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<form action="{{route('CategoryOutlet.store')}}" method="post" role="form">
       {{csrf_field()}}

     <div class="form-group">
       <label for="name">Name of Category</label>
       <input type="text" class="form-control" name="name" id="" placeholder="Name of role" value="{{ old('name') }}">
       @if ($errors->has('name'))
           <span class="help-block">
               <strong>{{ $errors->first('name') }}</strong>
           </span>
       @endif
     </div>
     <div class="form-group">
       <label for="status">Status</label>
        <input type="checkbox"  name="status" value="Y"> Active<br>
     </div>
     <button type="submit" class="btn btn-primary">Submit</button>
   </form>
@endsection
