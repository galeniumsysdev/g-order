@extends('layouts.tempAdminSB')
@section('content')
<h3>Add Flexvalue</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif

<form action="{{route('flexvalue.store')}}" method="post" role="form">
  {{csrf_field()}}

  <div class="form-group">
    <label for="name">Group</label>
    <select name="master" class="form-control" required="required">      
      @foreach($group as $key=>$g)
      <option value="{{$key}}">{{$g}}</option>
      @endforeach
    </select>
    @if ($errors->has('master'))
        <span class="help-block">
            <strong>{{ $errors->first('master') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group">
    <label for="name">Name</label>
    <input type="text" class="form-control" name="name" id="" placeholder="Name.." value="{{ old('name')?old('name'):'' }}" required="required">
    @if ($errors->has('name'))
        <span class="help-block">
            <strong>{{ $errors->first('name') }}</strong>
        </span>
    @endif
  </div>
  <div class="form-group">
    <label for="status">Status</label>
    @if(old('enabled_flag')=="Y")
     <input type="checkbox"  name="status" value="Y" checked="checked"> Active<br>
     @else
     <input type="checkbox"  name="status" value="Y"> Active<br>
     @endif
  </div>
  <button type="submit" class="btn btn-primary">Submit</button>
   <a href='{{route("flexvalue.index")}}'><button type="button" class="btn btn-warning">Back to list</button></a>
</form>
@endsection
