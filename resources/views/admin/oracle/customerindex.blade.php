@extends('layouts.tempAdminSB')
@section('content')
	<div class="row">
	    <div class="col-lg-12 margin-tb">
	        <div class="pull-left">
	            <h2>Customer Oracle</h2>
	        </div>
					<div class="pull-right">
	            <a href="{{route('oracle.synchronize.customer')}}" target="_blank" class="btn btn-success">Synchronize Customer Oracle</a>
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
        <th>Customer Number</th>
  			<th>Customer Name</th>
  			<th>Email</th>
  			<th>Roles</th>
				<th>Register</th>
				<th>Invite Email</th>
  			<th>Action</th>
  		</tr>
    </thead>
		<tfoot>
        <tr>
					<th>Customer Number</th>
	  			<th>Customer Name</th>
	  			<th>Email</th>
	  			<th>Roles</th>
					<th>Register</th>
					<th>Invite Email</th>
					<th></th>
        </tr>
    </tfoot>
    <tbody>
    	@foreach ($customers as $cust)
    	<tr>
        <td>{{ $cust->customer_number }}</td>
    		<td>{{ $cust->customer_name }}</td>
    		@if($cust->users->count()>0)
          <td>{{$cust->user->email}}</td>
          <td>@if(!empty($cust->user->roles))
    				@foreach($cust->user->roles as $v)
    					<label class="label label-success">{{ $v->display_name }}</label>
    				@endforeach
    			@endif</td>
					<td>
						@if($cust->user->register_flag)
							Y
						@else
							N
				 		@endif
			    </td>
					<td>
						@if($cust->user->validate_flag)
							Y
						@else
							N
						@endif
					</td>
        @else
          <td>-</td>
          <td>-</td>
					<td>-</td>
					<td>-</td>
        @endif

    		<td>
    			<a class="btn btn-info" href="{{route('useroracle.show',$cust->id)}}">Show</a>
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
				$('#table tfoot th').each( function () {
		        var title = $(this).text();
						if (title!="")
		        $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
		    } );
        var tablecust =$('#table').DataTable({
          'columnDefs' : [
                            {
                               'searchable'    : true,
                               'targets'       : [0,1,2,3,4,5]
                            },{
                               'orderable'    : true,
                               'targets'       : [0,1,2,3]
                            }
                        ]
        });
				tablecust.columns().every( function () {
		        var that = this;
		        $( 'input', this.footer() ).on( 'keyup change', function () {
		            if ( that.search() !== this.value ) {
		                that
		                    .search( this.value )
		                    .draw();
		            }
		        } );
		    } );
    } );
     </script>
@endsection
