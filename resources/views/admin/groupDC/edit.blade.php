@extends('layouts.tempAdminSB')
@section('content')
<h3>Edit Group Datacenter</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif

<form action="{{route('GroupDataCenter.update',$group->id)}}" method="post" role="form">
  {{method_field('PATCH')}}
  {{csrf_field()}}

  <div class="form-group">
    <label for="name">Name of Group</label>
    <input type="text" class="form-control" name="name" id="" placeholder="Name.." value="{{ old('name')?old('name'):$group->name }}">
    @if ($errors->has('name'))
        <span class="help-block">
            <strong>{{ $errors->first('name') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group">
    <label for="display_name">Display name</label>
    <input type="text" class="form-control" name="display_name" id="" value="{{$group->display_name}}" placeholder="Display name">
  </div>
  <div class="form-group">
    <label for="status">Status</label>
    @if($group->enabled_flag=="1")
     <input type="checkbox"  name="status" value="1" checked="checked"> Active<br>
     @else
     <input type="checkbox"  name="status" value="1"> Active<br>
     @endif
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
   <a href='{{route("GroupDataCenter.index")}}'><button type="button" class="btn btn-warning">Back to list</button></a>
</form>
@endsection
