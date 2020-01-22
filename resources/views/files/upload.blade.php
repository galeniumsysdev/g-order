@extends('layouts.navbar_product')

@section('content')
<div class="container">
    <div class="row">
      @if($status= Session::get('message'))
      <div class="alert alert-success">
        {{$status}}
      </div>
      @elseif($files)
      <div class="alert alert-success">
        @lang('pesan.alreadyupload')
      </div>
      @endif

			<div class="col-md-10 col-sm-offset-1">
					<div class="panel panel-default">
						<div class="panel-heading"><strong>Upload Files CMO</strong></div>
						<div class="panel-body" style="overflow-x:auto;">
								<div id="frmsearch" class="panel panel-default">

								    {!! Form::open(array('url' => '/handleUpload', 'files' => true,'class' => 'form-horizontal')) !!}
										{!! Form::token() !!}
										<div class="form-group">
											<label for="period" class="col-xs-4 col-md-2 control-label">Period</label>
											<div class="col-xs-8 col-md-10" >
                        <p class="form-control-static">{{$period}}</p>
								        <input type="hidden" name="period" value="{{$periodint}}" readonly>
											</div>
										</div>
										<div class="form-group {{ $errors->has('filepdf') ? ' has-error' : '' }}">
											<label for="filepdf" class="col-xs-4 col-md-2 control-label">*File PDF&nbsp;<i class="fa fa-file-pdf-o" aria-hidden="true"></i></label>
											<div class="col-xs-8 col-md-10" >
                        @if($files)
                          <a href="{{ url('/downloadCMO/'.$files->file_pdf) }}" download="{{ $files->file_pdf }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-download" aria-hidden="true"></i> Download
                          </a>
                        @else
								                <input type="file" accept="application/pdf" name="filepdf">
                                @if ($errors->has('filepdf'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('filepdf') }}</strong>
                                    </span>
                                @endif
                        @endif
											</div>
										</div>
										<div class="form-group {{ $errors->has('fileexcel') ? ' has-error' : '' }}">
											<label for="fileexcel" class="col-xs-4 col-md-2 control-label">*File Excel&nbsp;<i class="fa fa-file-excel-o" aria-hidden="true"></i></label>
											<div class="col-xs-8 col-md-10" >
                        @if($files)
                        <a href="{{ url('/downloadCMO/'.$files->file_excel) }}" download="{{ $files->file_excel }}" class="btn btn-warning btn-sm">
                          <i class="fa fa-download" aria-hidden="true"></i> Download
                        </a>

                        @else
								                <input type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" name="fileexcel" >
                                @if ($errors->has('fileexcel'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('fileexcel') }}</strong>
                                    </span>
                                @endif
                        @endif
											</div>
										</div>
                    @if(!$files and $filereject)
                    <div class="form-group">
											<label for="period" class="col-xs-4 col-md-2 control-label">Reason Reject</label>
											<div class="col-xs-8 col-md-8" >
                        <textarea class="form-control"> {{$filereject->keterangan}}</textarea>
                        <font color="red">File CMO versi ({{$filereject->version}}), ditolak oleh Yasa Mitra Persana. Harap Upload ulang kembali File CMO!!!</font>
											</div>
										</div>
                    @endif

										<div class="form-group">
												<div class="col-md-10 col-md-offset-2">
                          @if(!$files)
  													<button type="submit" name="upload" class="btn btn-primary">
  															@lang('label.upload')
  													</button>

                          @endif
											</div>
										</div>
								    {!! Form::close() !!}

									<br>
									<!--<a href="http://localhost:8000/viewAlldownloadfile"><strong>DETAIL FILES</strong></a>-->
							</div>
						</div>
					</div>
				</div>
		</div>
</div>
@endsection
