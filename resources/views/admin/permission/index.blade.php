@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Permission</h2>
        </div>
        <div class="pull-right">
            <a href="{{route('permission.create')}}" class="btn btn-success">Create New Permission</a>
        </div>
    </div>
</div>
@if ($message = Session::get('message'))
  <div class="alert alert-success">
    <p>{{ $message }}</p>
  </div>
@endif
<table class="table" id="table">
  <thead>
    <tr>
      <th>Name</th>
      <th>Display Name</th>
      <th>Description</th>
      <th>Action</th>
    </tr>
  </thead>
  <tbody>
  @forelse($permissions as $permission)
  <tr>
      <td>{{$permission->name}}</td>
      <td>{{$permission->display_name}}</td>
      <td>{{$permission->description}}</td>
      <td><a class="btn btn-info btn-sm" href="{{route('permission.edit',$permission->id)}}"><span class="glyphicon glyphicon-pencil"></span></a>
        {!! Form::open(['method' => 'DELETE','route' => ['permission.destroy', $permission->id],'style'=>'display:inline']) !!}
            <!--  {!! Form::submit('Delete', ['class' => 'btn btn-sm btn-danger']) !!}-->
            {!! Form::button( '<i class="fa fa-trash-o"></i>', ['type' => 'submit','class' => 'btn btn-sm btn-danger'] ) !!}

          	{!! Form::close() !!}
      </td>
  </tr>
  @empty
  <tr><td colspan="4">No Permission</td></tr>
  @endforelse
  </tbody>
</table>
@endsection
@section('js')
<!--<script src="//code.jquery.com/jquery-1.12.3.js"></script>-->
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
    <script>
      $(document).ready(function() {
        $('#table').DataTable();
    } );
     </script>
@endsection
