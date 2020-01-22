@extends('layouts.tempAdminSB')
@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Category Outlet</h2>
        </div>
        <div class="pull-right">
            <a href="{{route('CategoryOutlet.create')}}" class="btn btn-success">Create New Category</a>
        </div>
    </div>
</div>
<table class="table" id="table">
  <thead>
  <tr>
    <th width="80%">Name</th>
    <th width="10%">Status</th>
    <th width="10%">Action</th>
  </tr>
</thead>
<tbody>
  @forelse($categories as $category)
  <tr>
      <td>{{$category->name}}</td>
      <td>@if($category->enable_flag=="Y")
          Active
          @else
          Inactive
          @endif
      </td>
      <td><a class="btn btn-info btn-sm" href="{{route('CategoryOutlet.edit',$category->id)}}">Edit</a></td>
      <!--<td>  <form action="{{route('CategoryOutlet.destroy',$category->id)}}"  method="POST">
         {{csrf_field()}}
         {{method_field('DELETE')}}
         <input class="btn btn-sm btn-danger" type="submit" value="Delete">
       </form>
     </td>-->
  </tr>
  @empty
  <tr><td colspan="4">No Category</td></tr>
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
