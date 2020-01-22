@extends('layouts.tempAdminSB')
@section('content')
	<div class="row">
	    <div class="col-lg-12 margin-tb">
	        <div class="pull-left">
	            <h2>Customer Yasa Non Oracle</h2>
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
        <th>Customer ID</th>
  			<th>Customer Name</th>
  			<th>Email</th>
  			<th>Roles</th>
  			<th>Action</th>
  		</tr>
    </thead>
    <tbody>
    	@foreach ($customers as $cust)
    	<tr>
        <td>{{ $cust->id }}</td>
    		<td>{{ $cust->customer_name }}</td>
    		@if($cust->users->count()>0)
          <td>{{$cust->user->email}}</td>
          <td>@if(!empty($cust->user->roles))
    				@foreach($cust->user->roles as $v)
    					<label class="label label-success">{{ $v->display_name }}</label>
    				@endforeach
    			@endif</td>

        @else
          <td>-</td>
          <td>-</td>
        @endif

    		<td>
					@if($cust->users->count()>0)
    			<a class="btn btn-info" href="{{route('customer.show',['id'=>$cust->user->id,'notif_id'=>null])}}">Show</a>
					@endif
					@if($cust->users->count()>0 and Auth::user()->can('SuperUser'))
						<a class="btn btn-warning" href="{{route('users.logOnAs',$cust->user->id)}}" title="Log On As"><i class="fa fa-sign-in" aria-hidden="true"></i></a>
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
                               'targets'       : [0,1,2,3]
                            },{
                               'orderable'    : true,
                               'targets'       : [0,1,2,3]
                            }
                        ]
        });
    } );
     </script>
@endsection
