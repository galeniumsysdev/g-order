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

  {!! Form::open(['url' => route('dpl.generateExec'), 'id'=>'generate-sugg-no-form']) !!}
  <div class="container">
    <div class="row">
      <div class="col-md-10 col-sm-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('dpl.dplSuggestNo')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">Outlet</label>
                        </div>
                      </div>
                      <div class="col-md-10 product-container">
                        {{ Form::text('outlet','',array('class'=>'form-control','id'=>'outlet','autocomplete'=>'off')) }}
                        {{ Form::button('X', array('class'=>'btn btn-link btn-remove text-danger change-outlet','id'=>'change-outlet')) }}
                        {{ Form::hidden('outlet_id','',array('class'=>'form-control','id'=>'outlet-id')) }}
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group" id="block-alamat" >
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">@lang('label.address')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::text('alamat','',array('class'=>'form-control','id'=>'alamat')) }}
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">Kowil MR</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::text('kowil_mr','',array('class'=>'form-control','id'=>'kowil_mr','required'=>'required')) }}
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
                        {{ Form::submit(Lang::get('dpl.generate'),array('class'=>'btn btn-primary')) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  {{ Form::close() }}
@endsection
@section('js')

<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
