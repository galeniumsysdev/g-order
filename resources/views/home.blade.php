@extends(Auth::user()->hasRole('IT Galenium')?'layouts.tempAdminSB':'layouts.navbar_product')
@section('css')
<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
<link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
@endsection
@section('content')
<!--<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">-->
<!--<div class="container">-->
    <div class="row">
        <div class="col-md-10 col-sm-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>NOTIFICATIONS</strong></div>

                <div class="panel-body" style="overflow-x:auto;">
                    <div id="frmsearch" class="panel panel-default">
                      <form class="form-horizontal" role="form" method="POST" action="{{route('notif.search')}}">
                          {{ csrf_field() }}

                        <div class="form-group">
              						<br>
              						<label for="name" class="col-md-2 control-label">@lang('label.type')</label>
              							<div class="col-md-10" >
              								<select name="tipe" class="form-control">
              									<option value="" >--</option>
                                @foreach($jnsnotif as $key=>$val)
                                  <option value="{{$val}}" {{$request->tipe==$val?"selected=selected":""}}>{{$val}}</option>
                                @endforeach
              									<!--<option value="Register Outlet" {{$request->tipe=="Register Outlet"?"selected=selected":""}}>Register Outlet</option>
              									<option value="Reject Outlet" {{$request->tipe=="Reject Outlet"?"selected=selected":""}}>Reject Outlet</option>
                                @if(Auth::User()->can('CheckStatusSO'))
                                <option value="New PO" {{$request->tipe=="New PO"?"selected=selected":""}}>New PO</option>
                                @endif
                              -->
              								</select>
              							</div>
              					</div>

                        <div class="form-group">
                          <label for="subject" class="col-md-2 control-label">Subject</label>
            							<div class="col-md-10">
            								<input id="subject" type="text" class="form-control" name="subject" value="{{ $request->subject }}">
            							</div>
            						</div>

                        <div class="form-group">
                          <label for="tgl_kirim" class="col-md-2 control-label">@lang('label.datefrom')</label><i class="fa fa-calendar" aria-hidden="true"></i>
            							<div class="col-md-4">
                              <!--<input id="tgl_aw_kirim" type="text" class="form-control" name="tgl_aw_kirim" value="{{ old('tgl_aw_kirim') }}">-->
                              <input id="tgl_aw_kirim" type="text" class="date form-control" name="tgl_aw_kirim" placeholder="yyyy-mm-dd" value="{{ $request->tgl_aw_kirim }}">
            							</div>
                        </div>

						            <a href="#" id="swapcity" class="ppp"></a>
							           <!--<i class="fa fa-exchange"></i>-->

						            <div class="form-group">
                          <label for="tgl_kirim" class="col-md-2 control-label">@lang('label.dateto')</label><i class="fa fa-calendar" aria-hidden="true"></i>
							            <div class="col-md-4">
                                <!--<input id="tgl_aw_kirim" type="text" class="form-control" name="tgl_aw_kirim" value="{{ old('tgl_aw_kirim') }}">-->
                                <input id="tgl_ak_kirim" type="text" class="date form-control" name="tgl_ak_kirim" placeholder="yyyy-mm-dd" value="{{ $request->tgl_ak_kirim }}">
                          </div>
                        </div>

                        <div class="form-group">
                            <label for="status_read" class="col-md-2 control-label">Status</label>
                              <div class="col-md-10">
                                <select name="status_read" class="form-control">
                                    <option value="">All</option>
                                    <option value="0" {{$request->status_read=='0'?"selected=selected":""}}>Unread</option>
									<option value="1" {{$request->status_read=='1'?"selected=selected":""}}>Read</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-10 col-md-offset-2">
                                <button type="submit" id="btn-search" class="btn btn-primary">
                                    Search
                                </button>
                            </div>
                        </div>
                      </form>
                    </div>

					<div class="row">
						<div class="col-md-12"></div>
						<div id="no-more-tables">
							<table class="table table-sm" id="table">
									<thead>
										<tr>
											<th><strong>@lang('label.to')</strong></th>
											<th><strong>@lang('label.type')</strong></th>
											<th><strong>Subject</strong></th>
											<th><strong>@lang('label.sent')</strong></th>
											<th><strong>@lang('label.read')</strong></th>
										</tr>
								  </thead>
								<tbody>
								@forelse($notifications as $notif)
									<tr>
										<td data-title="@lang('label.to')">{{Auth::User()->name}}</td>
										<td data-title="@lang('label.type')">{{$notif->data['tipe']}}</td>
										<td data-title="SUBJECT">
                      <a href="{{$notif->data['href']}}">{{$notif->data['subject']}}</a>
										</td>
										<td data-title="@lang('label.sent')">{{$notif->created_at }}</td>
										<td data-title="@lang('label.read')">
                      @if(is_null($notif->read_at))
                        -
                      @else
                        {{$notif->read_at}}
                      @endif
                    </td>
									</tr>
								@empty
								<tr><td colspan="4">@lang("label.notfound")</td></tr>
								@endforelse
								</tbody>
							</table>
						</div>
            <div class="pull-right">{{ $notifications->links() }}</div>
					</div>
				</div>
			</div>
		</div>
	<!--</div>-->
@endsection
@section('js')
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>

<script type="text/javascript">
          $('.date').datetimepicker({
              format: "YYYY-MM-DD",
          });
</script>

@endsection
