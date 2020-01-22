@extends('layouts.tempAdminSB')
@section('content')

<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Group Datacenter</h2>
        </div>
        <div class="pull-right">
            <a href="{{route('GroupDataCenter.create')}}" class="btn btn-success">Create New Group</a>
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
    <th width="5%">ID</th>
    <th width="30%">Name</th>
    <th width="45%">Display Name</th>
    <th width="10%">Status</th>
    <th width="10%">Action</th>
  </tr>
</thead>
<tbody>
  @forelse($groups as $group)
  <tr>
      <td>{{$group->id}}</td>
      <td>{{$group->name}}</td>
      <td>{{$group->display_name}}</td>

      <td>@if($group->enabled_flag=="1")
          Active
          @else
          Inactive
          @endif
      </td>
      <td>
        <a class="btn btn-info btn-sm" title="edit" href="{{route('GroupDataCenter.edit',$group->id)}}"><span class="glyphicon glyphicon-pencil"></span></a>
        @if($group->subgroupdatacenter->count()==0)
          {!! Form::open(['method' => 'DELETE','route' => ['GroupDataCenter.destroy', $group->id],'style'=>'display:inline']) !!}
              <!--  {!! Form::submit('Delete', ['class' => 'btn btn-sm btn-danger']) !!}-->
              {!! Form::button( '<i class="fa fa-trash-o"></i>', ['type' => 'submit','class' => 'btn btn-sm btn-danger'] ) !!}

            	{!! Form::close() !!}
        @endif
        <!--
        <div class="btn-group">
          <a class="btn btn-primary" href="#">Action</a>
          <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
            <span class="fa fa-caret-down" title="Toggle dropdown menu"></span>
          </a>
          <ul class="dropdown-menu">
            <li><a href="{{route('GroupDataCenter.edit',$group->id)}}"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
            <li><a id="{{$group->id}}" class="delete-banner" href="#" ><i class="fa fa-trash-o fa-fw"></i> Delete</a></li>
          </ul>
        </div>-->
      </td>
  </tr>
  @empty
  <tr><td colspan="4">No Group Datacenter</td></tr>
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
        $('#table').DataTable({
          'columnDefs' : [
                            {
                               'searchable'    : true,
                               'targets'       : [1,3]
                            },
                            {
                              'orderable'     : false,
                              'targets'       : [4],
                            }
                        ]
        });
    } );
     </script>
@endsection
