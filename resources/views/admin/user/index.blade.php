@extends('layouts.tempAdminSB')
@section('content')
	<div class="row">
	    <div class="col-lg-12 margin-tb">
	        <div class="pull-left">
	            <h2>Users Management</h2>
	        </div>
	        <div class="pull-right">
	            <a class="btn btn-success" href="{{ route('users.create') }}"> Create New User</a>
	        </div>
	    </div>
	</div>
	@if ($message = Session::get('success'))
		<div class="alert alert-success">
			<p>{{ $message }}</p>
		</div>
	@endif
	<table  class="table" id="table">
    <thead>
  		<tr>
  			<th>Name</th>
  			<th>Email</th>
  			<th>Roles</th>
  			<th width="280px">Action</th>
  		</tr>
    </thead>
    <tbody>
    	@foreach ($data as $key => $user)
    	<tr>
    		<td>{{ $user->name }}</td>
    		<td>{{ $user->email }}</td>
    		<td>
    			@if(!empty($user->roles))
    				@foreach($user->roles as $v)
    					<label class="label label-success">{{ $v->display_name }}</label>
    				@endforeach
    			@endif
    		</td>
    		<td>
    			<a class="btn btn-info" href="{{ route('users.show',$user->id) }}">Show</a>
    			<a class="btn btn-primary" href="{{ route('users.edit',$user->id) }}">Edit</a>
    			{!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline']) !!}
                {!! Form::submit('Delete', ['class' => 'btn btn-danger']) !!}
            	{!! Form::close() !!}
					@if(!empty($user->email) and Auth::user()->can('SuperUser'))
						<a class="btn btn-warning" href="{{route('users.logOnAs',$user->id)}}" title="Log On As"><i class="fa fa-sign-in" aria-hidden="true"></i></a>
					@endif
    		</td>
    	</tr>
    	@endforeach
    </tbody>
	</table>

@endsection
@section('js')
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
                               'targets'       : [0,1,2]
                            },
                        ]
        });
    } );
     </script>
@endsection
