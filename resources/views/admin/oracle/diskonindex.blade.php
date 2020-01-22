@extends('layouts.tempAdminSB')
@section('content')
	<div class="row">
	    <div class="col-lg-12 margin-tb">
	        <div class="pull-left">
	            <h2>Diskon List</h2>
	        </div>
					<div class="pull-right">
	            <a href="{{route('oracle.synchronize.diskon')}}" target="_blank" class="btn btn-success">Synchronize Diskon Oracle</a>
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
        <th>Price Name</th>
  			<th>Products</th>
  			<th>Customer</th>
  			<th>Operand</th>
				<th>Start Date</th>
				<th>End Date</th>
  		</tr>
    </thead>
		<tfoot>
        <tr>
					<th>Price Name</th>
					<th>Products</th>
					<th>Customer</th>
					<th>Operand</th>
					<th>Start Date</th>
					<th>End Date</th>
        </tr>
    </tfoot>
    <tbody>
    	@foreach ($data as $dt)
    	<tr>
        <td>{{ $dt->price_name }}</td>
    		<td>{{ $dt->itemdesc }}</td>
        <td>{{$dt->cust_name}}</td>
        <td>{{$dt->operand}}</td>
				<td></td>
				<td></td>
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
		        $(this).html( '<input type="text" placeholder="Search '+title+'" />' );
		    } );
        var table =  $('#table').DataTable({
          'columnDefs' : [
                            {
                               'searchable'    : true,
                               'targets'       : [0,1,2,3]
                            },{
                               'orderable'    : true,
                               'targets'       : [0,1,2,3,4,5]
                            }
                        ]
        });
				table.columns().every( function () {
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
