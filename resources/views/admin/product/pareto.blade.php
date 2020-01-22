@extends('layouts.tempAdminSB')
@section('content')
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<h3>Pareto Product</h3>
<table class="table" id="table">
  <thead>
  <tr>
    <th></th>
    <th>Kode</th>
    <th>Nama</th>
    <th>Satuan Primary</th>
    <th>Kategori</th>
    <th>Action</th>
  </tr>
</thead>
<tbody>
  @forelse($products as $product)
  <tr>
      <td><img src="{{asset('img/'.$product->imagePath)}}" style="width:30px;height:30px;"></td>
      <td>{{$product->itemcode}}</td>
      <td>{{$product->title}}</td>
      <td>{{$product->satuan_primary}}</td>
      <td>
        {{$product->categories->first()->description}}
      </td>
      <td>
        <!--<a class="btn btn-info btn-sm" href="{{route('product.master',$product->id)}}">Remove</a>-->
        {!! Form::open(['method' => 'DELETE','route' => ['product.destroyPareto', $product->id],'style'=>'display:inline']) !!}
            {!! Form::button( '<i class="fa fa-trash-o"></i>', ['type' => 'submit','class' => 'btn btn-sm btn-danger','title'=>'remove from pareto products'] ) !!}
          	{!! Form::close() !!}
      </td>
  </tr>
  @empty
  <tr><td colspan="4">Tidak ada product</td></tr>
  @endforelse
</tbody>
</table>
<legend><strong>Tambah Product Pareto</strong></legend>
  <form class="form-horizontal" action="{{route('product.updatePareto')}}" method="post" role="form" >
       {{csrf_field()}}

     <div class="form-group">
       <label for="name" class="control-label col-sm-2">Product</label>
       <div class="col-sm-4 input-group">
         <input type="text" data-provide="typeahead" name="cari" value="" autocomplete="off" class="form-control" id="cari-pareto" required>
         <!--<button class="btn btn-link btn-remove text-danger change-product" id="change-product-pareto">X</button>-->
         <span class="input-group-btn" id="change-product-pareto">
           <button class="btn btn-secondary" type="button">X</button>
         </span>
         <input type="hidden" name="idpareto" value="" id="id-pareto" required>
       </div>
     </div>

     <button type="submit" class="btn btn-success">Tambah Product Pareto</button>

   </form>
@endsection
@section('js')
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <script>
      $(document).ready(function() {
        var path = "{{ route('product.getAjaxProduct') }}";
        $('#change-product-pareto').hide();
        $('#change-product-pareto').click(function(){
            $(this).hide();
            var product_container = $(this).closest('.product-container');
            $('#cari-pareto').removeAttr('readonly').val('');
            $('#id-pareto').val('');
        })
        $.get(path,
            function (data) {
                $('#cari-pareto').typeahead({
                    source: data,
                    items: 10,
                    showHintOnFocus: 'all',
                    displayText: function (item) {
                        return item.title;
                    },
                    afterSelect: function (item) {
                       $('#cari-pareto').val(item.title);
                        $('#cari-pareto').attr('readonly','readonly');
                        $('#change-product-pareto').show();
                        $('#id-pareto').val(item.id);
                    }
                });
              }, 'json');

        $('#table').DataTable({
          'columnDefs' : [     // see https://datatables.net/reference/option/columns.searchable
                            {
                               'searchable'    : true,
                               'targets'       : [1,2,3,4]
                            },
                            {
                              'orderable'     : false,
                              'targets'       : [0,5],
                            }
                        ],
          'order'         : [[ 1, "asc" ]]
        });
    } );
     </script>
@endsection
