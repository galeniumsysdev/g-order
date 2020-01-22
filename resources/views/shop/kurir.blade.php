@extends('layouts.navbar_product')

@section('content')
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<div class="container">
    <div class="row">
        <div class="col-md-10 col-sm-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading"><strong>Pengiriman Surat Jalan</strong></div>
                <div class="panel-body" style="overflow-x:auto;">
                  <div id="frmsearch" class="panel panel-default">
                    <form method="post" action="{{route('order.searchShippingOracle')}}">
                      {{ csrf_field() }}
                      <div class="form-group">
                        <label for="name" class="col-md-2 control-label">@lang('shop.deliveryno')/ Airwaybill No</label>
                          <div class="col-md-10" >
                            <input type="text" name="nosj" value="{{$sjnumber}}" class="form-control" required>
                          </div>
                      </div>
                      <div class="form-group">&nbsp;</div>
                      <div class="form-group">
                          <div class="sol-sm-12 text-center">
                            <button type="submit" id="btn-search" class="btn btn-primary">
                                Search
                            </button>
                          </div>
                      </div>
                    </form>
                  </div>
                  @if(!empty($ship))
                    @foreach($ship->groupBy('deliveryno') as $key => $delivery)
                      <form class="form-horizontal" method="post" action="{{route('order.shipconfirmcourier')}}">
                        {{ csrf_field() }}
                        <div class="form-group">
                          <label for="name" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                            <div class="col-md-4" >
                              <input type="text" name="nosj" value="{{$key}}" class="form-control" readonly>
                            </div>
                              <label for="name" class="col-md-2 control-label">Airway No</label>
                            <div class="col-md-4" >
                              <input type="text" name="airwayno" value="{{$delivery->first()->waybill}}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                          <label for="name" class="col-md-2 control-label">Customer</label>
                            <div class="col-md-10" >
                              <input type="text" class="form-control" value="{{$ship->first()->customer_name}}" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                          <label for="name" class="col-md-2 control-label">@lang('shop.ShipTo')</label>
                            <div class="col-md-10" >
                              <textarea class="form-control" readonly>{{$ship->first()->ship_to_addr}}</textarea>
                            </div>
                        </div>
                        @if(!is_null($delivery->first()->tgl_terima_kurir))
                        <div class="form-group">
                          <label for="name" class="col-md-2 control-label">@lang('shop.date_confirm')</label>
                            <div class="col-md-4" >
                              <input type="text" name="tglkonfirm" value="{{$delivery->first()->tgl_terima_kurir}}" class="form-control" readonly>
                            </div>
                        </div>
                        @endif
                        <div class ="table"><br>
                          <table  id="cart" class="table table-hover table-condensed">
                            <thead>
                              <tr>
                                <th width="45%">@lang('shop.Product')</th>
                                <th>@lang('shop.uom')</th>
                                <th>@lang('shop.qtyship')</th>
                              </tr>
                            </thead>
                            <tbody>
                              @foreach($delivery as $detailkirim)
                              <tr>
                                <td data-th="@lang('shop.Product')">{{$detailkirim->title}}</td>
                                <td data-th="@lang('shop.uom')">{{$detailkirim->uom_primary}}</td>
                                <td data-th="@lang('shop.qtyship')">{{$detailkirim->qty_shipping+$detailkirim->qty_backorder}}</td>
                              </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                        @if(is_null($delivery->first()->tgl_terima_kurir))
                        <div class="form-group">
                            <div class="col-sm-12 text-center">
                              <button type="submit" name="btnterima" value="confirm" class="btn btn-success">
                                  @lang('shop.finish_shipping')
                              </button>
                            </div>
                        </div>
                        @endif
                      </form>
                    @endforeach
                  @endif


                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('js')
@endsection
