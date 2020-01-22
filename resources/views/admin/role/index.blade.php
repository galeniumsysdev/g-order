@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Roles</h2>
        </div>
        <div class="pull-right">
            <a href="{{route('role.create')}}" class="btn btn-success">Create New Role</a>
            <a href="{{route('role.rptpdf')}}" class="btn btn-info">Report</a>
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
  @forelse($roles as $role)
  <tr>
      <td>{{$role->name}}</td>
      <td>{{$role->display_name}}</td>
      <td>{{$role->description}}</td>
      <td><a class="btn btn-info btn-sm" href="{{route('role.edit',$role->id)}}"><span class="glyphicon glyphicon-pencil"></span></a>
        <!--
        {!! Form::open(['method' => 'DELETE','route' => ['role.destroy', $role->id],'style'=>'display:inline']) !!}

            {!! Form::button( '<i class="fa fa-trash-o"></i>', ['type' => 'submit','class' => 'btn btn-sm btn-danger'] ) !!}

          	{!! Form::close() !!}-->
      </td>
  </tr>
  @empty
  <tr><td colspan="4">No Roles</td></tr>
  @endforelse
</tbody>
</table>
@endsection
@section('js')
<!--<script src="//code.jquery.com/jquery-1.12.3.js"></script>-->
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<!--<link rel="stylesheet"
    href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">-->
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
    <script>
      $(document).ready(function() {
        $('#table').DataTable();
    } );
     </script>
@endsection
