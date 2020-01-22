@extends('layouts.tempAdminSB')
@section('content')
<div class="panel panel-default">
    <div class="panel-heading">Cari Diskon</div>
    <div class="panel-body">
      <form action="{{route('product.diskonIndex')}}" class="form-horizontal" method="post" role="form">
        {{ csrf_field() }}
        <div class="form-group">
          <label class="control-label col-sm-2" for="price">Price Name</label>
          <div class="col-sm-8">
            <select name = "price_headers_id" class="form-control">
              <option value="">--</option>
              @foreach($price as $p)
                <option value="{{$p->list_header_id}}">{{$p->name}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2" for="customer"><strong>Customer Name :</strong></label>
            <div class="col-sm-8">
              <div class="input-group col-sm-12">
                <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="customer" id="customer" value="" >
                <span class="input-group-addon" id="change-cust">
                  <i class="fa fa-times" aria-hidden="true"></i>
                </span>
                <input type="hidden" name="cust_id" id="cust_id">
            </div>
            </div>
        </div>
        <div class="form-group">
          <label class="control-label col-sm-2" for="produk"><strong>Product Name :</strong></label>
            <div class="col-sm-8">
              <div class="input-group col-sm-12">
                <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="product" id="produk" value="" >
                <span class="input-group-addon" id="change-product">
                  <i class="fa fa-times" aria-hidden="true"></i>
                </span>
                <input type="hidden" name="product_id" id="product_id">
              </div>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-12">
                <button type="submit" class="btn btn-primary">
                    @lang('label.search')
                </button>
                &nbsp;<a href="{{route('oracle.synchronize.diskon')}}" target="_blank" class="btn btn-success">Synchronize Diskon Oracle</a>
            </div>
        </div>
      </form>
    </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script>
  $(document).ready(function() {
    $('#change-cust').hide();
    $('#change-product').hide();
    var path = "{{ route('oracle.searchProduct') }}";
    $.get(path,
        function (data) {
            $('#produk').typeahead({
                source: data,
                items: 10,
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.title;
                },
                afterSelect: function (item) {
                  $('#produk').val(item.title);
                  $('#product_id').val(item.id);
                  $('#change-product').show();
                  $('#produk').attr('readonly','readonly');
                }
            });
          }, 'json');
      $('#change-product').click(function(){
          $(this).hide();
          $('#produk').removeAttr('readonly').val('');
          $('#product_id').val('');
      });
    /*typeahead customer*/
    var path2 = "{{ route('customer.oracle.searchCustomer') }}";
    $.get(path2,
        function (data) {
            $('#customer').typeahead({
                source: data,
                items: 10,
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.customer_name;
                },
                afterSelect: function (item) {
                  $('#customer').val(item.customer_name);
                  $('#cust_id').val(item.id);
                  $('#change-cust').show();
                  $('#customer').attr('readonly','readonly');
                }
            });
          }, 'json');
      $('#change-cust').click(function(){
          $(this).hide();
          $('#customer').removeAttr('readonly').val('');
          $('#cust_id').val('');
      });



  });
</script>
@endsection
