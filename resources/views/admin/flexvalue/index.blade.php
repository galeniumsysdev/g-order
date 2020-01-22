@extends('layouts.tempAdminSB')
@section('content')

<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Flexvalue</h2>
        </div>
        <div class="pull-right">
            <a href="{{route('flexvalue.create')}}" class="btn btn-success">Create New Flexvalue</a>
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
    <th width="20%">Group</th>
    <th width="5%">ID</th>
    <th width="30%">Name</th>
    <th width="10%">Status</th>
    <th width="10%">Action</th>
  </tr>
</thead>
<tbody>
  @forelse($flexvalue as $flex)
  <tr>
      <td>{{$flex->master}}</td>
      <td>{{$flex->id}}</td>
      <td>{{$flex->name}}</td>
      <td>@if($flex->enabled_flag=="Y")
          Active
          @else
          Inactive
          @endif
      </td>
      <td>
        <a class="btn btn-info btn-sm" title="edit" href="{{route('flexvalue.show',[$flex->master,$flex->id])}}"><span class="glyphicon glyphicon-pencil"></span></a>
        @if($flex->master!="status_po")
          {!! Form::open(['method' => 'DELETE','route' => ['flexvalue.destroy', $flex->master,$flex->id],'style'=>'display:inline']) !!}
              <!--  {!! Form::submit('Delete', ['class' => 'btn btn-sm btn-danger']) !!}-->
              {!! Form::button( '<i class="fa fa-trash-o"></i>', ['type' => 'submit','class' => 'btn btn-sm btn-danger'] ) !!}

            	{!! Form::close() !!}
        @endif

      </td>
  </tr>
  @empty
  <tr><td colspan="4">No Data Found</td></tr>
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
