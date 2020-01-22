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
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {!!$status!!}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('outlet.reportStock')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div>
              <div class="form-wrapper">
                {!! Form::open(['url' => route('outlet.downloadStockView')]) !!}
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="trx-in-date">@lang('dpl.date')</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          {{ Form::text('start_date', date('d M Y', strtotime('-1 month')), array('class'=>'form-control date-range','autocomplete'=>'off', 'id'=>'start-date', 'required'=>'required')) }}
                          {{ Form::text('end_date', date('d M Y'), array('class'=>'form-control date-range','autocomplete'=>'off', 'id'=>'end-date', 'required'=>'required')) }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="trx-in-date">Nama Barang</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          {{ Form::text('product_name','',array('class'=>'form-control','id'=>'product-name')) }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="trx-in-date">Generik</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          {{ Form::text('generic','',array('class'=>'form-control','id'=>'generic')) }}
                        </div>
                      </div>
                    </div>
                  </div>

                  @if(Auth::user()->hasRole('Principal'))
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="product-name-in">@lang('outlet.outletName')</label>
                          </div>
                        </div>
                        <div class="col-md-4 outlet-container">
                          {{ Form::text('outlet_name', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'outlet-name')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="qty-in">@lang('label.province')</label>
                          </div>
                        </div>
                        <div class="col-md-4 province-container">
                          {{ Form::text('province', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'province')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="qty-in">Area</label>
                          </div>
                        </div>
                        <div class="col-md-4 area-container">
                          {{ Form::text('area', '', array('class'=>'form-control','autocomplete'=>'off', 'id'=>'area', 'disabled'=>'disabled')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  @endif
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          &nbsp;
                        </div>
                        <div class="col-md-4">
                          {{ Form::submit('Submit', array('class'=>'btn btn-primary')) }}
                          <a href="{{ route('outlet.listProductStock') }}" class="btn btn-default">@lang('label.back')</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <legend></legend>
                {{ Form::close() }}
              </div>
            </div>
            @if($data)
            <div class="table-responsive">
              <table id="report-list" class="display responsive nowrap" width="100%">
                <thead>
                  <tr>
                    <th rowspan="2" class="text-center">@lang('outlet.outletName')</th>
                    <th rowspan="2" class="text-center">@lang('outlet.productName')</th>
                    <th rowspan="2" class="text-center">@lang('outlet.batchNo')</th>
                    <th colspan="4" class="text-center">@lang('outlet.qty')</th>
                    <th rowspan="2" class="text-center">@lang('outlet.unitPrice')</th>
                    <th rowspan="2" class="text-center">Value</th>
                  </tr>
                  <tr>
                    <th class="text-center">Beg</th>
                    <th class="text-center">In</th>
                    <th class="text-center">Out</th>
                    <th class="text-center">End</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($data['table'] as $result)
                    <tr>
                      <td>{{ $result['outlet_name'] }}</td>
                      <td>{{ $result['title'] }}</td>
                      <td>{!! $result['batch'] !!}</td>
                      <td align="right">{{ $result['begin'] }}</td>
                      <td align="right">{{ $result['in'] }}</td>
                      <td align="right">{{ $result['out'] }}</td>
                      <td align="right">{{ $result['end'] }}</td>
                      <td align="right">{{ number_format($result['unit_price'],0,',','.') }}</td>
                      <td align="right">{{ number_format($result['value_price'],0,',','.') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div style="text-align:center;">
              {!! Form::open(['url' => route('outlet.downloadStockProcess')]) !!}
              {{ Form::hidden('start_date', $data['start_date']) }}
              {{ Form::hidden('end_date', $data['end_date']) }}
              {{ Form::hidden('outlet_name', $data['outlet_name']) }}
              {{ Form::hidden('province', $data['province']) }}
              {{ Form::hidden('area', $data['area']) }}
              <br>{{ Form::submit('Download Report Stock', array('class'=>'btn btn-success')) }}
              {{ Form::close() }}
            </div>
            @endif
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
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
