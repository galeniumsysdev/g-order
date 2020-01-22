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
      <div class="col-md-10 col-sm-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('outlet.importNewProduct')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-12">
                        <div class="default-value">
                          <label for="outlet">
                            [ <a href="{{route('outlet.downloadTemplateProduct')}}">Download Template</a> ]
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                {!! Form::open(['url' => route('outlet.importProductView'), 'files'=>'true']) !!}
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
                          <div class="form-label">
                            <label for="outlet">@lang('dpl.confirmation')</label>
                          </div>
                        </div>
                        <div class="col-md-10">
                          <span class="default-value">
                            <table id="import-product" class="table table-striped table-hover table-center-header">
                              <thead>
                                <tr>
                                  <th>@lang('outlet.productName')</th>
                                  <th>@lang('outlet.unit')</th>
                                  <th>@lang('outlet.price')</th>
                                  <th>@lang('outlet.generic')</th>
                                  <th></th>
                                </tr>
                              </thead>
                              <tbody>
                              @foreach ($data as $cell)
                                <tr>
                                  <td>{{ strtoupper($cell['nama_barang']) }}</td>
                                  <td>{{ strtoupper($cell['satuan']) }}</td>
                                  <td align="right">{{ number_format(floatval($cell['price']),0,',','.') }}</td>
                                  <td>{!! nl2br($cell['generik']) !!}</td>
                                  <td align="center">{!! $cell['exist'] !!}
                                </tr>
                              @endforeach
                              </tbody>
                            </table>
                            {!! Form::open(['url' => route('outlet.importProductProcess'), 'id'=>'form-product']) !!}
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

<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script
    src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
