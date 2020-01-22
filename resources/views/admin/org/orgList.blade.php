@extends(Auth::user()->hasRole('IT Galenium')?'layouts.tempAdminSB':'layouts.navbar_product')
@section('content')
<div class="container-fluid">
  <div class="row">
      <div class="col-lg-12 margin-tb">
          <div class="pull-left">
              <h2>Organization Structure</h2>
          </div>
          <div class="pull-right">
              <a href="{{route('org.create')}}"  class="btn btn-success">Add Org Code</a>
          </div>
      </div>
  </div>
  @if ($message = Session::get('success'))
    <div class="alert alert-success">
      <p>{{ $message }}</p>
    </div>
  @endif

  <table class="table" id="table">
    <thead>
    <tr>
      <th width="10%">Code</th>
      <th width="30%">Email</th>
      <th width="50%">Name</th>
      <th width="50%">Direct Supervision</th>
      <th width="10%">Action</th>
    </tr>
  </thead>
  <tbody>
    @forelse($users as $user)
    <tr>
        <td>{{$user->user_code}}</td>
        <td>{{$user->email}}</td>
        <td>{{$user->description}}</td>
        <td>{{$user->sup_name}}</td>
        <td><a class="btn btn-info btn-sm" href="{{route('org.setting',$user->id)}}">Setting</a></td>
    </tr>
    @empty
    <tr><td colspan="4">No User</td></tr>
    @endforelse
  </tbody>
  </table>
</div>
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
