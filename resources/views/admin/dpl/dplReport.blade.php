@extends('layouts.navbar_product')
@section('content')
<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">

<div class="container">
  <div class="row">
    <div class="col-md-10 col-sm-offset-1">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>REPORT DPL</strong></div>

        <div class="panel-body">
          <div id="frmsearch" class="panel panel-default">
            <br>
            <form class="form-horizontal" role="form" method="POST" action="{{ route('dpl.reportdownload') }}" id="reportNOO">
                {{ csrf_field() }}
                <div class="form-group" style="margin-bottom:5px;">
                  <label for="tgl_kirim" class="col-sm-2 control-label" style="margin-left:15px;">Periode :</label>
	               	<div class="col-sm-8">
		                <div class='col-sm-4'>
		                    <div class="form-group">
                          <div class="col-md-12">
                            {{ Form::text('trx_in_date', date('F Y'), array('class'=>'form-control','autocomplete'=>'off', 'id'=>'trx-in-date', 'required'=>'required')) }}
                          </div>
		                    </div>
		                </div>
	                </div>
                </div>
                <legend></legend>

                <div class="form-group">
                  <label class="control-label col-sm-2" for="distributor" style="margin-left:15px;"><strong>ASM :</strong></label>
                    <div class="col-sm-8" style="margin-left:15px; margin-right:15px;">
                        <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="asm" id="asm" value="" >
                        <span class="input-group-addon" id="change-asm">
                          <i class="fa fa-times" aria-hidden="true"></i>
                        </span>
                        <input type="hidden" name="asm_id" id="asm-id">
                    </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-sm-2" for="distributor" style="margin-left:15px;"><strong>SPV :</strong></label>
                    <div class="col-sm-8" style="margin-left:15px; margin-right:15px;">
                        <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="spv" id="spv" value="" >
                        <span class="input-group-addon" id="change-spv">
                          <i class="fa fa-times" aria-hidden="true"></i>
                        </span>
                        <input type="hidden" name="spv_id" id="spv-id">
                    </div>
                </div>

                <div class="form-group">
                  <label class="control-label col-sm-2" for="distributor" style="margin-left:15px;"><strong>Distributor :</strong></label>
                    <div class="col-sm-8" style="margin-left:15px; margin-right:15px;">
                        <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="distributor" id="distributor" value="" >
                        <span class="input-group-addon" id="change-dist">
                          <i class="fa fa-times" aria-hidden="true"></i>
                        </span>
                        <input type="hidden" name="dist_id" id="dist_id">
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-2" style="text-align:center;">
                        <button type="submit" name="excelrpt" id="btn-search" class="btn btn-success">
                          <i class="fa fa-file-excel-o" aria-hidden="true"></i>&nbsp;<strong>Download Report DPL
                        </strong></button>
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
<script src="{{ asset('js/dplreport.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>

@endsection
