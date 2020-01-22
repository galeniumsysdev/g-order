@extends('layouts.tempAdminSB')
@section('content')
<h3>Create New Roles</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<form action="{{route('role.store')}}" method="post" role="form">
       {{csrf_field()}}

     <div class="form-group">
       <label for="name">Name of role</label>
       <input type="text" class="form-control" name="name" id="name" value ="{{ old('name') }}" placeholder="Name of role">
       @if ($errors->has('name'))
           <span class="help-block with-errors">
               <strong>{{ $errors->first('name') }}</strong>
           </span>
       @endif
     </div>
       <div class="form-group">
       <label for="display_name">Display name</label>
       <input type="text" class="form-control" name="display_name" id="display_name" value="{{ old('display_name') }}" placeholder="Display name">
     </div>
       <div class="form-group">
       <label for="description">Description</label>
       <input type="text" class="form-control" name="description" id="description" value="{{ old('description') }}" placeholder="Description">
     </div>

       <div class="form-group text-left">
           <h3>Permissions</h3>
           <select name="permission[]" class="form-control" multiple size="7">
           @foreach($permissions as $permission)
   		<!--//<input type="checkbox"   name="permission[]" value="{{$permission->id}}" > {{isset($permission->display_name)?$permission->display_name:$permission->name}} <br>-->
             <option value="{{$permission->id}}">{{isset($permission->display_name)?$permission->display_name:$permission->name}}</option>
           @endforeach
         </select>
     </div>
     <button type="submit" class="btn btn-primary">Submit</button>
   </form>
@endsection
