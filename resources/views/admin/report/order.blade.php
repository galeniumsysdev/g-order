@extends('layouts.navbar_product')
@section('css')
<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
<link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
<link href="{{ asset('css/outletproduct.css') }}" rel="stylesheet">
@endsection
@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-10 col-sm-offset-1">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Report Order</strong></div>

        <div class="panel-body" style="overflow-x:auto;">
          <div id="frmsearch" class="panel panel-default">
            <br>
            <form class="form-horizontal" role="form" method="POST" action="{{route('report.orderexcel')}}" id="reportNOO">
                {{ csrf_field() }}
                <div class="form-group">
                  <label for="tgl_kirim" class="col-sm-2 control-label">*Period :</label>
                  <div class="col-sm-8">
                  <div class='col-sm-4'>
                      <div class="form-group">
                          <!--<div class='input-group date' id='datetimepicker6'>
                              <input type='text' name="tglaw" class="form-control" required/>
                              <span class="input-group-addon">
                                  <span class="glyphicon glyphicon-calendar"></span>
                              </span>
                          </div>-->
                            {{ Form::text('tglaw', date('Y-m-d'), array('class'=>'form-control','autocomplete'=>'off', 'id'=>'datetimepicker6', 'required'=>'required')) }}
                      </div>
                  </div>
                  <div class="col-sm-2 hidden-xs"><center>s.d</center></div>
                  <div class='col-sm-4'>
                      <div class="form-group">
                        {{ Form::text('tglak', date('Y-m-d'), array('class'=>'form-control','autocomplete'=>'off', 'id'=>'datetimepicker7', 'required'=>'required')) }}
                          <!--<div class='input-group date' id='datetimepicker7'>
                              <input type='text' name="tglak"  class="form-control" required/>
                              <span class="input-group-addon">
                                  <span class="glyphicon glyphicon-calendar"></span>
                              </span>
                          </div>-->
                      </div>
                  </div>
                </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-2" for="distributor"><strong>@lang('shop.supplier') :</strong></label>
                    <div class="col-sm-8">
                      <div class="input-group col-sm-12">
                        <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="distributor" id="distributor" value="" >
                        <span class="input-group-addon" id="change-dist">
                          <i class="fa fa-times" aria-hidden="true"></i>
                        </span>
                        <input type="hidden" name="dist_id" id="dist_id">
                    </div>
                    </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-2" for="outlet"><strong>Customer :</strong></label>
                    <div class="col-sm-8">
                      <div class="input-group col-sm-12">
                        <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="outlet" id="outlet" value="" >
                        <span class="input-group-addon" id="change-outlet">
                          <i class="fa fa-times" aria-hidden="true"></i>
                        </span>
                      </div>
                      <input type="hidden" name="outlet_id" id="outlet_id">
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="needproduct"><strong>Divisi :</strong></label>
                    <div class="col-sm-10">
                      <input type="checkbox" class="form-check-input" name="psc_flag" id="psc_flag" value="1" > PSC &nbsp;
                      <input type="checkbox" class="form-check-input" name="pharma_flag" id="pharma_flag" value="1" > Pharma (Non PSC)
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="needproduct"><strong>Channel :</strong></label>
                    <div class="col-sm-6">
                      <select name="channel" class="form-control">
                        <option value="">---</option>
                        @foreach($channels as $channel)
                          <option value="{{$channel->id}}">{{$channel->name}}</option>
                        @endforeach
                      </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="needproduct"><strong>@lang('label.province') :</strong></label>
                    <div class="col-sm-6">
                      <select name="propinsi" class="form-control">
                        <option value="">---</option>
                        @foreach($provinces as $p)
                          <option value="{{$p->id}}">{{$p->name}}</option>
                        @endforeach
                      </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-2">
                        <button type="submit" name="excelrpt" id="btn-search" class="btn btn-success">
                          <i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel
                        </button>
                    </div>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


@endsection
@section('js')
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>-->

<script type="text/javascript">
    $(function () {
      if($('#datetimepicker6, #datetimepicker7').length){
          $('#datetimepicker6, #datetimepicker7').datetimepicker({
              format: "YYYY-MM-DD",
              locale: "en",
              maxDate:"{{date('Y-m-d')}}"
          });
      }
    });
</script>

<script>
  $(document).ready(function() {
    $('#change-outlet').hide();
    $('#change-dist').hide();
    /*typeahead distributor*/
    var path2 = "{{ route('customer.searchDistributor') }}";
    $.get(path2,
        function (data) {
            $('#distributor').typeahead({
                source: data,
                items: 10,
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.customer_name;
                },
                afterSelect: function (item) {
                  $('#distributor').val(item.customer_name);
                  $('#dist_id').val(item.id);
                  $('#change-dist').show();
                  $('#distributor').attr('readonly','readonly');
                }
            });
          }, 'json');
      $('#change-dist').click(function(){
          $(this).hide();
          $('#distributor').removeAttr('readonly').val('');
          $('#dist_id').val('');
      });
    var path = "{{ route('customer.searchOutletDistributor') }}";
    $.get(path,
        function (data) {
            $('#outlet').typeahead({
                source: data,
                items: 10,
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.customer_name;
                },
                afterSelect: function (item) {
                  $('#outlet').val(item.customer_name);
                  $('#outlet_id').val(item.id);
                  $('#change-outlet').show();
                  $('#outlet').attr('readonly','readonly');
                }
            });
          }, 'json');
      $('#change-outlet').click(function(){
          $(this).hide();
          $('#outlet').removeAttr('readonly').val('');
          $('#outlet_id').val('');
      });


  });
</script>
@endsection
