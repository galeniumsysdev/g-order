@extends('layouts.tempAdminSB')
@section('content')
<h3>Edit SubGroup Datacenter</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif

<form action="{{route('SubgroupDatacenter.update',$subgroup->id)}}" method="post" role="form">
  {{method_field('PATCH')}}
  {{csrf_field()}}

  <div class="form-group">
    <label for="name">Name of Group</label>
    <input type="text" class="form-control" name="name" id="" placeholder="Name.." value="{{ old('name')?old('name'):$subgroup->name }}">
    @if ($errors->has('name'))
        <span class="help-block">
            <strong>{{ $errors->first('name') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group">
    <label for="display_name">Display name</label>
    <input type="text" class="form-control" name="display_name" id="" value="{{$subgroup->display_name}}" placeholder="Display name">
  </div>
  <div class="form-group">
    <label for="display_name">Group name</label>
    <select class="form-control" name="groupdc" required>
      <option value="">Pilih Salah Satu</option>
      @foreach($groups as $group)
      <option value="{{$group->id}}" {{$subgroup->group_id==$group->id?'selected':''}}>{{$group->name}}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group">
    <label for="status">Status</label>
    @if($subgroup->enabled_flag=="1")
     <input type="checkbox"  name="status" value="1" checked="checked"> Active<br>
     @else
     <input type="checkbox"  name="status" value="1"> Active<br>
     @endif
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
   <a href='{{route("SubgroupDatacenter.index")}}'><button type="button" class="btn btn-warning">Back to list</button></a>
</form>
@endsection
