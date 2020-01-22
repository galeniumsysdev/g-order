<!-- 
/**
* created by WK Productions
*/ 
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/outletproduct.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('outlet.stockTrxDetail')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <h4>
              @lang('outlet.productName') : {{ $title }}<br/>
              @lang('outlet.stock') : {{ $last_stock }}
            </h4>
            <br/>
            <div class="table-responsive">
              <table id="detail-list" class="display responsive nowrap" width="100%">
                <thead>
                  <tr>
                    <th>@lang('outlet.type')</th>
                    <th>@lang('outlet.qty')</th>
                    <th>@lang('outlet.batchNo')</th>
                    <th>@lang('outlet.trxDate')</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($stock as $cell)
                    <tr class="{{ $cell['class'] }}">
                      <td>{!! $cell['event'] !!}</td>
                      <td align="center">{!! $cell['qty'] !!}</td>
                      <td align="center">{{ $cell['batch'] }}</td>
                      <td align="center">{{ $cell['trx_date'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <a href="/outlet/product/list" class="btn btn-default">@lang('label.back')</a>
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
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
