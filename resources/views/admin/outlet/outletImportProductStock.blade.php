<!--
/**
* created by WK Productions
*/
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('outlet.importStock')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="default-value">
                          <label for="outlet">
                            [ <a href="{{route('outlet.downloadTemplateStock')}}">Download Template</a> ]
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                {!! Form::open(['url' => route('outlet.importProductStockView'), 'files'=>'true']) !!}
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="file-import">@lang('outlet.chooseFile')</label>
                          </div>
                        </div>
                        <div class="col-md-10">
                          {{ Form::file('file_import', array('class'=>'form-control','id'=>'file-import'))}}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          &nbsp;
                        </div>
                        <div class="col-md-10">
                          {{ Form::submit('Submit', array('class'=>'btn btn-primary', 'id'=>'btn-import')) }}
                          <a href="{{ route('outlet.listProductStock') }}" class="btn btn-default">@lang('label.back')</a>
                        </div>
                      </div>
                    </div>
                  </div>
                {{ Form::close() }}
                @if ($data)
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label pull-left">
                          <label for="outlet">@lang('dpl.confirmation')</label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-12">
                        <span class="default-value">
                          <div class="table-responsive">
                            <table id="import-stock" class="display responsive" width="100%">
                              <thead>
                                <tr>
                                  <th>@lang('outlet.productName')</th>
                                  <th>@lang('outlet.lastStock')</th>
                                  <th>@lang('outlet.currentStock')</th>
                                  <th>@lang('outlet.unit')</th>
                                  <th>@lang('outlet.batchNo')</th>
                                  <th>@lang('outlet.ExpDate')</th>
                                  <th>ID</th>
                                </tr>
                              </thead>
                              <tbody>
                              @foreach ($data as $cell)
                                <tr>
                                  <td>{{ $cell['nama_barang'] }}</td>
                                  <td>{{ $cell['last_stock'] }}</td>
                                  <td>{{ $cell['stock'] }}</td>
                                  <td>{{ $cell['satuan'] }}</td>
                                  <td>{{ (string)$cell['batch'] }}</td>
                                  <td>{{ !is_null($cell['exp._datecth2017_01_31'])?date_format(date_create($cell['exp._datecth2017_01_31']),'Y-m-d'):null}}</td>
                                  <td>{{ $cell['id'] }}</td>
                                </tr>
                              @endforeach
                              </tbody>
                            </table>
                          </div>
                          {!! Form::open(['url' => route('outlet.importProductStockProcess')]) !!}
                          {{ Form::hidden('data',$data) }}
                          {{ Form::submit(Lang::get('outlet.execute'), array('class'=>'btn btn-primary')) }}
                          {{ Form::close() }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
              </div>
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
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
