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
  <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {!!$status!!}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('outlet.addProduct')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div id="tabs" class="simple-tabs">
              <div class="form-wrapper">
                {!! Form::open(['url' => route('outlet.submitProduct'), 'class'=>'form-product']) !!}
                {{ Form::hidden('id', $product->id) }}
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="trx-in-date">@lang('outlet.productName')</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          {{ Form::text('product_name', $product->title, array('class'=>'form-control','autocomplete'=>'off', 'id'=>'product-name', 'required'=>'required')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="product-name-in">@lang('outlet.unit')</label>
                          </div>
                        </div>
                        <div class="col-md-4 product-container">
                          {{ Form::text('product_unit', $product->unit, array('class'=>'form-control','autocomplete'=>'off', 'id'=>'product-unit', 'required'=>'required')) }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="qty-in">@lang('outlet.price')</label>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="input-prepend input-group">
                            <span class="add-on input-group-addon">Rp</span>
                            {{ Form::number('product_price', $product->price, array('class'=>'form-control','autocomplete'=>'off', 'id'=>'product-price', 'min'=>0, 'required'=>'required')) }}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="container-fluid">
                      <div class="row">
                        <div class="col-md-2">
                          <div class="form-label">
                            <label for="product-name-in">@lang('outlet.generic')</label>
                          </div>
                        </div>
                        <div class="col-md-4 product-container">
                          {{ Form::textarea('product_generic', $product->generic, array('class'=>'form-control','id'=>'product-generic','rows'=>5)) }}
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
                        <div class="col-md-4">
                          {{ Form::submit('Submit', array('class'=>'btn btn-primary')) }}
                          <a href="{{ route('outlet.listProductStock') }}" class="btn btn-default">@lang('label.back')</a>
                        </div>
                      </div>
                    </div>
                  </div>
                {{ Form::close() }}
              </div>
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
<script src="{{ asset('js/outletproduct.js') }}"></script>

@endsection
