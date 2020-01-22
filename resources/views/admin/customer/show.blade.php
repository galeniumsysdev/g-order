@extends('layouts.navbar_product')

@section('content')
<div class="container">
    <div class="row" >
        <div id="pesan">
        </div>
            <div class="panel panel-primary">
                <div class="panel-heading">Customer</div>
                <div class="panel-body">
                      <form action="{{route('customer.update',$user->id)}}" class="form-horizontal" method="post" role="form">
                          {{method_field('PATCH')}}
                          {{csrf_field()}}
                          <input type="hidden" value="{{$customer->id}}" id="customer_id">
                          <input type="hidden" value="{{$notif_id}}" id="notif_id">
                          <input type="hidden" value="{{url('/')}}" id="baseurl">
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="name">@lang('label.outlet') :</label>
                            <div class="col-sm-10">
                              <p class="form-control" id="cust-name">{{$customer->customer_name}}</p>
                              <input type="hidden" id="old-cust-name" value="{{$customer->customer_name}}">
                            </div>
                          </div>
                          <div class="form-group">
                            <label class="control-label col-sm-2" for="email">@lang('label.email') :</label>
                            <div class="col-sm-10">
                              <p class="form-control">{{$email}}</p>
                            </div>
                          </div>
                          <div class="tabcard">
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active"><a href="#personal" aria-controls="Personal" role="tab" data-toggle="tab">@lang('label.personal')</a></li>
                                <li role="presentation"><a href="#address" aria-controls="Address" role="tab" data-toggle="tab">@lang('label.address')</a></li>
                                <li role="presentation"><a href="#contact" aria-controls="Contact" role="tab" data-toggle="tab">@lang('label.contact')</a></li>
                            </ul>
                            <div class="tab-content">
                                <div role="tabpanel" class="tab-pane active" id="personal">
                                  <div class="form-group">
                                    <label class="control-label col-sm-2" for="npwp">@lang('label.npwp') :</label>
                                    <div class="col-sm-10">
                                      <p class="form-control">{{$customer->tax_reference}}</p>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <label class="control-label col-sm-2" for="pscpharma">@lang('label.needproduct') :</label>
                                    <div class="col-sm-10">
                                         <input type="checkbox"  name="pharma_flag" value="1" disabled="disabled" {{$customer->pharma_flag=="1"?"checked=checked":""}} > Non PSC/Pharma<br>
                                          <input type="checkbox"  name="psc_flag" value="1" disabled="disabled" {{$customer->psc_flag=="1"?"checked=checked":""}}> PSC<br>
                                    </div>
                                  </div>
                                  @if(isset($categoryoutlet))
                                  <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                                      <label for="kategori" class="control-label col-sm-2">@lang('label.category') :</label>

                                      <div class="col-sm-10">
                                          <p class="form-control">{{$categoryoutlet}}</p>
                                      </div>
                                  </div>
                                  @endif
                                  @if(isset($subgroupname))
                                  <div class="form-group{{ $errors->has('kategori') ? ' has-error' : '' }}">
                                      <label for="kategori" class="control-label col-sm-2">@lang('label.categorydc') :</label>

                                      <div class="col-sm-10">
                                        <p class="form-control">{{$groupdc."-".$subgroupname}}</p>
                                      </div>
                                  </div>
                                  @endif
                                  @if(Auth::user()->hasRole('Principal'))
                                    <div class="form-group{{ $errors->has('c_number') ? ' has-error' : '' }}">
                                        <label for="kategori" class="control-label col-sm-2">Oracle Customer No. :</label>

                                        <div class="col-sm-10">
                                          <div class="input-group col-sm-10">
                                            <input type="text" name="c_number" id="customer-number" class="form-control" value="{{$customer->customer_number}}" autocomplete="off">
                                            <span class="input-group-addon" id="change-custnum">
                                              <i class="fa fa-times" aria-hidden="true"></i>
                                            </span>
                                            <input type="hidden" name="c_id" id="customer-id" class="form-control" value="{{$customer->oracle_customer_id}}">
                                          </div>
                                        </div>
                                    </div>
                                    <div class="form-group" id='data-oracle'>
                                        <label for="c_name" class="control-label col-sm-2">Oracle Customer Name. :</label>

                                        <div class="col-sm-10">
                                          <input type="text" name="c_name" id="customer-name" class="form-control" value="{{$customer->customer_name}}" readonly='readonly'>
                                        </div>
                                    </div>
                                  @endif
                                  <div class="form-group">
                                    <label class="control-label col-sm-2" for="ijin_pbf">Berijin :</label>
                                    <div class="col-sm-4">
                                       @foreach($dataijin as $key=>$value)
                                        <input type="radio"  name="ijin_pbf" value="{{$key}}" {{old('ijin_pbf')?old('ijin_pbf'):$customer->ijin_pbf==$key?"checked=checked":""}}> {{$value}}<br>
                                       @endforeach
                                    </div>
                                  </div>

                                  <div class="form-group" id="ijin_flag">
                                    <label class="control-label col-sm-2" for="noijin">No Ijin :</label>
                                    <div class="col-sm-4 {{$errors->has('noijin')?'has-error':''}}" >
                                      <input type="text" name="noijin" class="form-control" value="{{old('noijin')?old('noijin'):$customer->no_ijin}}">
                                      @if ($errors->has('noijin'))
                                          <span class="help-block">
                                              <strong>{{ $errors->first('noijin') }}</strong>
                                          </span>
                                      @endif
                                    </div>
                                    <label class="control-label col-sm-2" for="expdate">Masa Berlaku :</label>
                                    <div class="input-group col-sm-3 date {{$errors->has('masaberlaku')?'has-error':''}}" id="datetimepicker1">
                                      <input type="text" name="masaberlaku" class="form-control" value="{{is_null(old('masaberlaku')?old('masaberlaku'):$customer->masa_berlaku)?'':(old('masaberlaku')?old('masaberlaku'):date_format(date_create($customer->masa_berlaku),'%d %F %Y'))}}" autocomplete="off" id="masaberlaku">
                                      <span class="input-group-addon">
                                          <span class="glyphicon glyphicon-calendar"></span>
                                      </span>
                                      @if ($errors->has('masaberlaku'))
                                          <div><label class="help-block text-danger">
                                              <strong>{{ $errors->first('masaberlaku') }}</strong>
                                          </label></div>
                                      @endif

                                    </div>
                                  </div>

                                  <div class="form-group" id="status">
                                      @if(is_null($outletdist->approval))
                                      <div class="col-sm-12">
                                        <!--<button type="button" name="save" value="reject" id="reject-customer" class="btn btn-warning">@lang('label.reject')</button>
                                        <button type="button" name="save" value="approve" id="approve-customer" class="btn btn-primary">@lang('label.approve')</button>-->
                                        @if(Auth::user()->hasRole('Principal'))
                                        <button type="submit" name="save" value="save" id="save-customer" class="btn btn-primary">@lang('label.save')</button>
                                        @endif
                                      </div>

                                      @elseif($outletdist->approval)
                                        <label for="statue" class="control-label col-sm-2">Status: </label>
                                        <div class="col-sm-10"><p class="form-control">Approve</p></div>
                                      @elseif(!$outletdist->approval)
                                          <label for="statue" class="control-label col-sm-2">Status: </label>
                                          <div class="col-sm-10"><p class="form-control">Tolak: {{$outletdist->keterangan}}</p></div>
                                      @endif
                                  </div>
                                </div>

                                <div role="tabpanel" class="tab-pane" id="address">
                                  <div class="table-responsive">
                                    <table id="alamat-table" class="table table-striped">
                                      <thead>
                                        <tr>
                                          <th width="60%">@lang('label.address')</th>
                                          <th width="10%">@lang('label.city_regency')</th>
                                          <th width="5%">@lang('label.urban_village')</th>
                                          <th width="5%">@lang('label.postalcode')</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @forelse($customer_sites as $customer_site)
                                          <tr>
                                            <td>{{$customer_site->address1}}</td>
                                            <td>{{$customer_site->city}}</td>
                                            <td>{{$customer_site->state}}</td>
                                            <td>{{$customer_site->postalcode}}</td>
                                          </tr>
                                        @empty
                                          <tr><td colspan="5">No data</td></tr>
                                        @endforelse
                                      </tbody>
                                    </table>
                                    <!--
                                    <div class="pull-right">
                                       <a href="#" class="btn btn-success">@lang('label.addaddress')</a>
                                     </div>-->

                                  </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="contact">
                                  <div class="table-responsive">
                                    <table id="contact-table" class="table table-striped">
                                      <thead>
                                        <tr>
                                          <th width="30%">@lang('label.cp')</th>
                                          <th width="20%">@lang('label.type')</th>
                                          <th width="30%">Data</th>
                                        </tr>
                                      </thead>
                                      <tbody>
                                        @forelse($customer_contacts as $cc)
                                        <tr>
                                          <td>{{$cc->contact_name}}</td>
                                          <td>{{$cc->contact_type}}</td>
                                          <td>{{$cc->contact}}</td>
                                        </tr>
                                        @empty
                                          <tr><td colspan="4">No data</td></tr>
                                        @endforelse
                                      </tbody>
                                    </table>

                                  </div>
                                </div>



                            </div>
                          </div>
                      </form>
                </div>
            </div>
        </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="{{ asset('js/approvalcustomer.js') }}"></script>
<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
<link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
@endsection
