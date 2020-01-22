@extends('layouts.navbar_product')


@section('content')
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">
<div class="container">
    <div class="row">
        <div class="col-md-10 col-sm-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Notifications</div>

                <div class="panel-body" style="overflow-x:auto;">
                    <div id="frmsearch" class="panel panel-default">
                      <form class="form-horizontal" role="form" method="POST" action="{{route('notif.search')}}">
                          {{ csrf_field() }}

                          <div class="form-group">
                              <label for="name" class="col-md-2 control-label">Type</label>

                              <div class="col-md-6" >
                                  <select name="tipe" class="form-control">
                                    <option value="" >--</option>
                                    <option value="Register Outlet" {{$request->tipe=="Register Outlet"?"selected=selected":""}}>Register Outlet</option>
                                    <option value="Reject Outlet" {{$request->tipe=="Reject Outlet"?"selected=selected":""}}>Reject Outlet</option>
                                  </select>
                              </div>
                          </div>
                          <div class="form-group">
                              <label for="subject" class="col-md-2 control-label">Subject</label>

                              <div class="col-md-6">
                                  <input id="subject" type="text" class="form-control" name="subject" value="{{ $request->subject }}">
                              </div>
                          </div>
                          <div class="form-group">
                              <label for="tgl_kirim" class="col-md-2 control-label">Date Sent</label>

                              <div class="col-md-4">
                                  <!--<input id="tgl_aw_kirim" type="text" class="form-control" name="tgl_aw_kirim" value="{{ old('tgl_aw_kirim') }}">-->
                                  <input id="tgl_aw_kirim" type="text" class="date form-control" name="tgl_aw_kirim" value="{{ $request->tgl_aw_kirim }}">
                              </div>
                              <div class="col-md-4">
                                  <input id="tgl_ak_kirim" type="text" class="date form-control" name="tgl_ak_kirim" value="{{ $request->tgl_ak_kirim }}">
                              </div>
                          </div>
                          <div class="form-group">
                              <label for="status_read" class="col-md-2 control-label">Status</label>

                              <div class="col-md-2">
                                  <select name="status_read" class="form-control">
                                    <option value="">All</option>
                                    <option value="0" {{$request->status_read=='0'?"selected=selected":""}}>Unread</option>
                                    <option value="1" {{$request->status_read=='1'?"selected=selected":""}}>Read</option>
                                  </select>
                              </div>
                          </div>
                          <div class="form-group">
                            <div class="col-md-6 col-md-offset-2">
                                <button type="submit" id="btn-search" class="btn btn-primary">
                                    Search
                                </button>
                            </div>
                          </div>

                        </form>
                    </div>
                  <table class="table table-sm" id="table">
                    <thead>
                    <tr>
                      <th>To</th>
                      <th>Type</th>
                      <th>Subject</th>
                      <th>Sent</th>
                      <th>Read</th>
                    </tr>
                  </thead>
                  <tbody>
                    @forelse($notifications as $notif)
                    <tr>
                        <td>{{Auth::User()->name}}</td>
                        <td>{{$notif->data['tipe']}}</td>
                        <td>
                        @if($notif->type=="App\\Notifications\\NewoutletDistributionNotif")
                          <a href="{{route('customer.show',[$notif->data['outlet']['id'],$notif->id] )}}">{{$notif->data['subject']}}</a>
                        @elseif($notif->type=="App\\Notifications\\MarketingGaleniumNotif")
                          <a href="{{route('customer.show',[$notif->data['user']['id'],$notif->id] )}}">{{$notif->data['subject']}}</a>
                        @endif
                        </td>
                        <td>{{$notif->created_at }}</td>
                        <td>{{$notif->read_at }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4">@lang("label.notfound")</td></tr>
                    @endforelse
                  </tbody>
                  </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>
<script type="text/javascript">

    $('.date').datepicker({
       format: 'yyyy-mm-dd',
       defaultDate: new Date()
     });


</script>
@endsection
