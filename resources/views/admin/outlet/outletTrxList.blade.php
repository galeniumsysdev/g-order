<!--
/**
* created by WK Productions
*/
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  <link href="{{ asset('css/outletproduct.css') }}" rel="stylesheet">
  <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
  <link href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
  <link href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css" rel="stylesheet">
  <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('outlet.trxList')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="filter-form">
              <div class="container">
                <div class="row">
                  <div class="row col-md-2">@lang('outlet.period')</div>
                  <div class="col-md-2">{{ Form::text('start_date',date('d F Y',strtotime('-1 month')),array('class'=>'form-control date-range','id'=>'start-date-trx')) }}</div>
                  <div class="col-md-2">{{ Form::text('end_date',date('d F Y'),array('class'=>'form-control date-range','id'=>'end-date-trx')) }}</div>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <div class="row col-md-2">@lang('outlet.productName')</div>
                  <div class="col-md-4">{{ Form::text('product_name','',array('class'=>'form-control','id'=>'product-name')) }}</div>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <div class="row col-md-2">@lang('outlet.generic')</div>
                  <div class="col-md-4">{{ Form::text('generic','',array('class'=>'form-control','id'=>'generic')) }}</div>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <div class="row col-md-2">{{ Form::button(Lang::get('label.search'),array('class'=>'btn btn-primary','id'=>'btn-filter')) }}</div>
                </div>
              </div>
              <br/>
            </div>
            <div class="table-responsive">
              <table id="trx-list" class="stripe row-border order-column" width="100%">
                <thead>
                  <tr>
                    <th>@lang('outlet.trxDate')</th>
                    <th>@lang('outlet.productName')</th>
                    <th>@lang('outlet.generic')</th>
                    <th>@lang('outlet.transaction')</th>
                    <th>@lang('outlet.qty')</th>
                    <th>@lang('outlet.batchNo')</th>
                    <th>@lang('outlet.ExpDate')</th>
                    <!--<th>@lang('outlet.deliveryNo')</th>-->
                  </tr>
                </thead>
                <tbody>
                  @foreach ($data as $key=>$trx)
                    <tr class="{{ $trx['class'] }}">
                      <td align="center">{{ \Carbon\Carbon::parse($trx['trx_date'])->format('d-M-Y') }}</td>
                      <td>{{ $trx['title'] }}</td>
                      <td>{!! nl2br($trx['generic']) !!}</td>
                      <td align="center">{!! $trx['event'] !!}</td>
                      <td align="center">{!! $trx['qty'] !!}</td>
                      <td align="center">{{ $trx['batch'] }}</td>
                      <td align="center">{{ \Carbon\Carbon::parse($trx['exp_date'])->format('d-M-Y') }}</td>
                      <!--<td align="center">{{ $trx['deliveryorder_no'] }}</td>-->
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('js')

<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
