@extends('layouts.navbar_product')
@section('content')
<!--tidak ke oracle-->
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  @if($status= Session::get('message'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif
  @if($status= Session::get('error'))
  <div class=" alert alert-danger">{{Session::get('error')}}</div>
  @endif
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Order Product</strong></div>
        <div class="panel-body" style="overflow-x:auto;">

            <div class="form-horizontal col-sm-6">

                  <br>
              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">No.Trx</label>
                <div class="col-md-10 ">
                    <label class="form-class">{{$header->notrx}}&nbsp;
                      @if(isset($header->file_po))
                      <a href="{{url('/download/'.$header->file_po)}}" title="@lang('shop.download') file PO"><i class="glyphicon glyphicon-download-alt"></i></a>
                      @endif
                      @if($header->status==0)
                      &nbsp;
                      <a href="{{route('order.checkPO',$header->id)}}?print=yes" class="btn btn-primary">@lang('shop.print') PO</a>
                      @endif
                    </label>
                </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">Distributor</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->distributor_name}}" readonly>
                  </div>
              </div>

              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">@lang('shop.orderdate')</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" value="{{date('d-M-Y',strtotime($header->tgl_order))}}" readonly>
                </div>
              </div>
              <div class="form-group">
                <label for="subject" class="col-md-2 control-label">Status</label>
                <div class="col-md-10">
                    <input type="text" class="form-control" value="{{$header->status_name}}" readonly>
                </div>
              </div>

            </div>
            <div class="form-horizontal col-sm-6">
              <div class="form-group"><br class="hidden-xs">
                <label for="name" class="col-md-2 control-label">@lang('shop.Po_num')</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->customer_po}}" readonly>
                  </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">Customer</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->customer_name}}" readonly>
                  </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">@lang('shop.ShipTo')</label>
                  <div class="col-md-10" >
                    <textarea class="form-control" readonly>{{$header->ship_to_addr}}</textarea>
                  </div>
              </div>
            </div>
          <br>
          <div class="col-sm-12">
            <div class="tabcard col-sm-12">
              @if($deliveryno and $header->status>=2)
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active" ><a href="#order" aria-controls="Order" role="tab" data-toggle="tab"><strong>Order</strong></a></li>
                    <li role="presentation"><a href="#shipping" aria-controls="Shipping" role="tab" data-toggle="tab"><strong>Shipping</strong></a></li>
                </ul>
              @endif
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="order">
                @if(Auth::user()->customer_id==$header->distributor_id)
                  <form class="form-horizontal" action="{{route('order.approvalSO')}}" method="post">
                @elseif(Auth::user()->customer_id==$header->customer_id)
                  <form class="form-horizontal" action="{{route('order.cancelPO')}}" method="post">
                @endif
                    {{ csrf_field() }}
                    <input type="hidden" name="header_id" value="{{$header->id}}">
                    @if(empty($deliveryno) and $header->status>=1)
                      <div class="form-group">
                        <label for="subject" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                        <div class="col-md-10 ">
                            <input type="text" name="deliveryno" value="" class="form-control" required>
                        </div>
                      </div>
                    @endif

                    <table id="cart" class="table table-hover table-condensed">
                				<thead>
                				<tr>
                					<th style="width:45%" class="text-center">@lang('shop.Product')</th>
                					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
                          <th style="width:7%" class="text-center">@lang('shop.uom')</th>
                					<th class="text-center">@lang('shop.qtyorder')</th>
                          @if($header->status>=0)
                              @if(Auth::user()->customer_id==$header->distributor_id or
                               (Auth::user()->customer_id==$header->customer_id and $header->status>=1)
                               )
                                <th style="width:7%" class="text-center">@lang('shop.qtyavailable')</th>
                              @endif
                              @if($header->status>1 or ($header->status=1 and Auth::user()->customer_id==$header->distributor_id))
                                <th style="width:7%" class="text-center">@lang('shop.qtyship')</th>
                              @endif
                              @if(Auth::user()->customer_id==$header->customer_id
                                or (Auth::user()->customer_id==$header->distributor_id and $header->status>1 )
                                )
                                <th style="width:7%" class="text-center">@lang('shop.qtyreceive')</th>
                              @endif
                          @endif

                					<th style="width:15%" class="text-center">@lang('shop.SubTotal')</th>
                				</tr>
                			</thead>
                			<tbody>
                        @foreach($lines as $line)
                          @php ($id  = $line->line_id)
                				<tr>
                					<td>
                						<div class="row">
                							<div class="col-sm-2 hidden-xs"><img src="{{ asset('img//'.$line->imagePath) }}" alt="..." class="img-responsive"/></div>
                							<div class="col-sm-10">
                								<h4 >{{ $line->title }}</h4>
                							</div>
                						</div>
                					</td>
                					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" id="hrg-{{$id}}">
                            {{ number_format($line->unit_price/$line->conversion_qty,2) }}
                          </td>
                          <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center">
                              {{ $line->uom_primary }}
                              <input type="hidden" name="uom[{{$id}}]" value="{{ $line->uom_primary }}">
                          </td>
                					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left" id="ord-{{$id}}">
                              {{ $line->qty_request_primary }}
                					</td>
                          @if($header->status>=0)
                              @if(Auth::user()->customer_id==$header->distributor_id or
                               (Auth::user()->customer_id==$header->customer_id and $header->status>=1)
                               )
                                <td data-th="@lang('shop.qtyavailable')"  class="text-center xs-only-text-left" id="avl-{{$id}}">
                                  @if($header->status>=1)
                                  {{number_format($line->qty_confirm_primary,2)}}
                                  @else
                                    <input type="number" name="qtyshipping[{{$id}}]" class="form-control" value="{{(float)$line->qty_request_primary}}">
                                  @endif
                                </td>
                              @endif
                              @if($header->status>1 or ($header->status=1 and Auth::user()->customer_id==$header->distributor_id))
                                <td data-th="@lang('shop.qtyship')"  class="text-center xs-only-text-left">
                                  @if($header->status==1)
                                    <input type="number" name="qtyshipping[{{$id}}]" value="{{(float)$line->qty_confirm_primary}}" class="form-control"  id="ship-{{$id}}">
                                  @else
                                    {{$line->qty_shipping_primary}}
                                  @endif
                                </td>
                              @endif
                              @if(Auth::user()->customer_id==$header->distributor_id and $header->status>1)
                              <td  data-th="@lang('shop.qtyreceive')"  class="text-center xs-only-text-left">
                                  {{number_format($line->qty_accept,2)}}
                              </td>
                              @endif
                              @if(Auth::user()->customer_id==$header->customer_id)
                                <td  data-th="@lang('shop.qtyreceive')"  class="text-center xs-only-text-left">
                                  @if($header->status>=2  or $deliveryno->count()>1 )
                                    {{number_format($line->qty_accept,2)}}
                                  @elseif($header->status == 0 )
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_request_primary}}" class="form-control">
                                  @elseif($header->status==1)
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_confirm_primary}}" class="form-control">
                                  @endif
                                </td>
                              @endif
                          @endif

                					<td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                            {{number_format($line->amount_confirm,2)}}
                          </td>
                				</tr>
                        @endforeach
                			</tbody>
                      <tfoot>
                				<tr class="visible-xs">
                					<td class="text-center" ><strong class="totprice" id="totprice1">Total
                          @if($header->currency=='IDR')
                            Rp.
                          @elseif($header->currency=='USD')
                            $
                          @endif
                          {{number_format($header->amount_confirm+$header->tax_amount,2)}}
                          </strong>
                          </td>
                				</tr>
                				<tr class="hidden-xs">
                          @if($header->status<=0)
                					     <td colspan="5" class="hidden-xs text-right">SubTotal: </td>
                          @elseif($header->status==1)
                               <td colspan="6" class="hidden-xs text-right">SubTotal: </td>
                          @elseif($header->status>1)
                               <td colspan="7" class="hidden-xs text-right">SubTotal: </td>
                          @endif
                					<td class="hidden-xs text-right"><strong id="totprice2">
                            @if($header->currency=='IDR')
                            Rp.
                            @elseif($header->currency=='USD')
                            $
                            @endif
                            {{ number_format($header->amount_confirm,2) }}</strong></td>
                				</tr>
                        <tr class="hidden-xs">

                          @if($header->status<=0)
                               <td colspan="5" class="hidden-xs text-right">
                          @elseif($header->status==1)
                               <td colspan="6" class="hidden-xs text-right">
                          @elseif($header->status>1)
                               <td colspan="7" class="hidden-xs text-right">
                          @endif
                          Tax </td>
                					<td class="hidden-xs text-right"><strong id="totprice2">
                            @if($header->currency=='IDR')
                            Rp.
                            @elseif($header->currency=='USD')
                            $
                            @endif
                            {{ number_format($header->tax_amount,2) }}</strong></td>
                				</tr>
                        <tr class="hidden-xs">
                          @if($header->status<=0)
                               <td colspan="5" class="hidden-xs text-right">
                          @elseif($header->status==1)
                               <td colspan="6" class="hidden-xs text-right">
                          @elseif($header->status>1)
                               <td colspan="7" class="hidden-xs text-right">
                          @endif
                          Total </td>
                					<td class="hidden-xs text-right"><strong id="totprice2">
                            @if($header->currency=='IDR')
                            Rp.
                            @elseif($header->currency=='USD')
                            $
                            @endif

                            {{ number_format(($header->amount_confirm+$header->tax_amount),2) }}</strong></td>
                				</tr>
                        @if($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
                          <tr>
                  					<td>
                              <button type="submit" name="approve" value="reject" class="btn btn-warning text-left" style="min-width:200px"><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject')
                            </td>
                  					<td colspan="4" class="hidden-xs"></td>
                  					<td><button type="submit" name="approve" value="approve" class="btn btn-success btn-block text-right">@lang('label.approve')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button></td>
                  				</tr>
                        @else
                          <tr style="border-top-style:hidden;">
                          <td class="text-left">
                            @if(Auth::user()->customer_id==$header->customer_id and $header->status==0)
                            <button type="submit" name="batal" value="batal" class="btn btn-warning" style="min-width:200px;"><i class="fa fa-angle-left" style="color:#fff"></i> @lang('label.cancel')</button>
                            @else
                            <a href="{{URL::previous()}}" class="btn btn-warning" style="min-width:200px;"><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
                            @endif
                          </td>

                          @if(Auth::user()->customer_id==$header->customer_id and $header->status<=0)
                            <td colspan="4" class="hidden-xs"></td>
                          @elseif($header->status<2)
                            <td colspan="5" class="hidden-xs"></td>
                          @else
                            <td colspan="6" class="hidden-xs"></td>
                          @endif

                          @if($header->status>=0 )
                          <td>

                            @if(Auth::user()->customer_id==$header->customer_id and $header->status>=0 and $header->status<4)
                               @if($header->status==2 or $deliveryno->count()>1)
                               @else
                               <button type="submit" name="terima" value="terima" class="btn btn-success btn-block">@lang('shop.Receive')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                              @endif
                            @elseif($header->status==1 and Auth::user()->customer_id==$header->distributor_id and (Auth::user()->hasRole('Distributor') or Auth::user()->hasRole('Distributor Cabang')))
                              <button type="submit" name="kirim" value="kirim" class="btn btn-success btn-block">@lang('shop.Send')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                            @endif
                          </td>
                          @endif
                        </tr>
                        @endif
                			</tfoot>
                		</table>

                  </form>
                </div>
                @if(isset($deliveryno))
                  <div role="tabpanel" class="tab-pane" id="shipping">
                    <div class="panel-group" id="accordion">

                      @foreach($deliveryno as $key => $delivery)
                      <div class="panel panel-default">
                        <div class="panel-heading">
                          <h6 class="panel-title kirim-panel">
                            Delivery No:<a data-toggle="collapse" data-parent="#accordion" href="#{{$key}}">{{$key}}</a>
                            <p class="pull-right">Date: {{$delivery->first()->tgl_kirim}}</p>
                          </h6>
                        </div>
                        <div id="{{$key}}" class="panel-collapse collapse">
                          <div class="panel-body">
                            <table class="table">
                              <thead>
                                <tr>
                                  <th style="width:45%;" class="text-center">@lang('shop.Product')</th>
                                  <th style="width:10%;" class="text-center">@lang('shop.uom')</th>
                                  <th style="width:10%;" class="text-center">@lang('shop.qtyship')</th>
                                  <th style="width:10%;" class="text-center">@lang('shop.qtyreceive')</th>
                                </tr>
                              </thead>
                              <tbody>
                                @foreach($delivery as $detail)
                                <tr>
                                  <td>{{$detail->product->title}}</td>
                                  <td style="text-align:center">{{$detail->uom_primary}}</td>
                                  <td style="text-align:center">{{(float)$detail->qty_shipping}}</td>
                                  <td style="text-align:center">{{(float)$detail->qty_accept}}</td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>
                          </div>
                        </div>
                      </div>
                      @endforeach
                      @if($header->status ==2 )
                        <div class="panel panel-default">
                          <div class="panel-heading">
                            <h6 class="panel-title kirim-panel">
                              <a data-toggle="collapse" data-parent="#accordion" href="#New-Delivery">New Delivery:</a>
                            </h6>
                          </div>
                          <div id="New-Delivery" class="panel-collapse collapse in">
                            <div class="panel-body">
                              <div class="form-group">
                                <label for="subject" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                                <div class="col-md-10 ">
                                    <input type="text" name="deliveryno" value="" class="form-control" required>
                                </div>
                              </div>
                              <table class="table">
                                <thead>
                                  <tr>
                                    <th style="width:45%;" class="text-center">@lang('shop.Product')</th>
                                    <th style="width:10%;" class="text-center">@lang('shop.uom')</th>
                                    <th style="width:10%;" class="text-center">@lang('shop.qtyship')</th>
                                    <th style="width:10%;" class="text-center">@lang('shop.qtyreceive')</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  @foreach($lines as $newdelivery)
                                  @if($newdelivery->qty_shipping < $newdelivery->qty_confirm)
                                  <tr>
                                    <td>{{$newdelivery->title}}</td>
                                    <td style="text-align:center">{{$newdelivery->uom_primary}}</td>
                                    <td style="text-align:center">
                                      @if(Auth::user()->customer_id ==$header->distributor_id)
                                        <input type="number" name="qtyshipping[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_confirm - intval($newdelivery->qty_shipping)}}" class="form-control">
                                      @else
                                        0
                                      @endif

                                    </td>
                                    <td style="text-align:center">
                                      @if(Auth::user()->customer_id ==$header->customer_id)
                                        <input type="number" name="qtyreceive[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_confirm - intval($newdelivery->qty_accept)}}" class="form-control">
                                      @endif

                                    </td>
                                  </tr>
                                  @endif
                                  @endforeach
                                </tbody>
                              </table>
                            </div>
                          </div>
                        </div>
                      @endif
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
</div>
@endsection
@section('js')

<script src="{{ asset('js/myproduct.js') }}"></script>

@endsection
