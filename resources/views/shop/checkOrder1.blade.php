@extends('layouts.navbar_product')
@section('css')
<link href="{{ asset('css/table.css') }}" rel="stylesheet">
@endsection
@section('content')
  <!--untuk ke oracle-->
  @if($status= Session::get('message'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif
<!--<link rel="stylesheet"href="//codeorigin.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />-->
<div class="container">
  <div class="row">

    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>Order Product</strong></div>


        <div class="panel-body">
          <!--<div class="panel panel-default">-->
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
                <label for="name" class="col-md-2 control-label">@lang('shop.supplier')</label>
                  <div class="col-md-10">
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
                  @if($header->status==0 and $header->approve==1 and Auth::user()->customer_id==$header->distributor_id)
                    <input type="text" class="form-control" value="Menunggu Booked Oracle" readonly>
                  @else

                    @if($header->status==-2 and !is_null($header->alasan_tolak))
                    <input type="text" class="form-control" value="{{$header->status_name. ' (alasan: '.$header->alasan_tolak.')'}}" readonly>
                    @else
                    <input type="text" class="form-control" value="{{$header->status_name}}" readonly>
                    @endif
                  @endif
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
                    <textarea class="form-control">{{$header->ship_to_addr}}</textarea>
                  </div>
              </div>
            </div>
          <!--</div>-->
          <div class="col-sm-12">
            <div class="tabcard col-sm-12">
              @if(($deliveryno->count()>0 and $header->status>1) or ($deliveryno->count()==1 and $header->status>2))
              <ul class="nav nav-tabs" role="tablist">
                  <li role="presentation" class="active" ><a href="#order" aria-controls="Order" role="tab" data-toggle="tab"><strong>Order</strong></a></li>
                  <li role="presentation"><a href="#shipping" aria-controls="Shipping" role="tab" data-toggle="tab"><strong>Shipping</strong></a></li>
              </ul>
              @endif
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="order">
                  @if($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
                    <form class="form-horizontal" action="{{route('order.approvalSO')}}" method="post" id="reportNOO">
                  @elseif(Auth::user()->customer_id==$header->customer_id)
                    <form class="form-horizontal" action="{{route('order.cancelPO')}}" method="post">
                  @endif
                    {{ csrf_field() }}
                      <input type="hidden" name="header_id" value="{{$header->id}}">
                      <table  class="table" id="tbl-order">
                  			<thead>
                  				<tr>
                  					<th style="width:35%;" class="text-center">@lang('shop.Product')</th>
                            <th style="width:10%" class="text-center">@lang('shop.listprice')</th>
                  					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
                            <th style="width:5%" class="text-center">@lang('shop.uom')</th>
                  					<th class="text-center">@lang('shop.qtyorder')</th>
                            <!--
                            @if($header->status>0 or ($header->status==0 and Auth::user()->customer_id==$header->distributor_id ))
                              <th class="text-center">@lang('shop.qtyavailable')</th>
                            @endif
                          -->
                            @if($header->status>1)
                              <th class="text-center">@lang('shop.qtyship')</th>
                            @endif
                            @if(Auth::user()->customer_id==$header->customer_id and  $header->status==2)
                              <th class="text-center">@lang('shop.qtyreceive')</th>
                            @elseif($header->status>=2)
                                <th class="text-center">@lang('shop.qtyreceive')</th>
                            @endif
                  					<th style="width:20%" class="text-center">@lang('shop.AmountPO')</th>
                            @if($header->status>1)
                              <th class="text-center">@lang('shop.amountreceive')</th>
                            @endif
                  				</tr>
                  			</thead>
                  			<tbody>
                          @php ($totamount=0)
                          @php($taxtotal=0)
                          @foreach($lines as $line)
                            @php ($id  = $line->line_id)
                            @if($header->status<2)
                              @php($uom= $line->uom)
                              @php ($listprice=$line->list_price)
                              @php ($unitprice=$line->unit_price)
                              @php ($uom=$line->uom)
                              @php($qtyrequest= $line->qty_request)
                              @php($qtyconfirm= $line->qty_confirm)
                              @php($qtykirim= $line->qty_shipping)
                              @php($qtyterima= $line->qty_accept)
                            @else
                              @php ($uom = $line->uom_primary)
                              @php ($listprice=$line->list_price/$line->conversion_qty)
                              @php ($unitprice=$line->unit_price/$line->conversion_qty)
                              @php ($qtyrequest= $line->qty_request_primary)
                              @php ($qtyconfirm= $line->qty_confirm_primary)
                              @php ($qtykirim= $line->qty_shipping_primary)
                              @php ($qtyterima= $line->qty_accept_primary)
                            @endif
                  				<tr>
                  					<td>
                  						<div class="row">
                  							<div class="col-sm-2 hidden-xs"><img src="{{ asset('img//'.$line->imagePath) }}" alt="..." class="img-responsive"/></div>
                  							<div class="col-sm-10">
                  								{{ $line->title }}
                  							</div>
                  						</div>
                  					</td>
                            <td data-th="@lang('shop.listprice')" class="xs-only-text-left text-center">
                              {{ number_format($listprice,2) }}
                            </td>
                  					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" >
                              {{ number_format($unitprice,2) }}
                            </td>
                            <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center" >
                                {{ $uom}}
                                <input type="hidden" name="uom[{{$id}}]" value="{{ $uom }}">
                            </td>
                  					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left">
                                @if($header->status>0)
                                {{ (float)$qtyconfirm }}
                                @else
                                {{ (float)$qtyrequest }}
                                @endif
                  					</td>

                            @if($header->status>0 or (Auth::user()->customer_id==$header->distributor_id and $header->status==0))
                              <!--<td data-th="@lang('shop.qtyavailable')" class="text-center xs-only-text-left">-->
                                @if(Auth::user()->customer_id==$header->distributor_id and $header->status==0)
                                  <input type="hidden" name="qtyshipping[{{$id}}]" id="qty-{{$id}}" class="form-control text-center" value="{{ $line->qty_request }}" style="min-width:80px;">
                                @endif
                              <!--</td>-->
                            @endif

                            @if($header->status>1)
                              <td data-th="@lang('shop.qtyship')" class="text-center xs-only-text-left">
                                {{(float)$qtykirim}}
                              </td>
                            @endif
                            @if($header->status>=2)
                                <td data-th="@lang('shop.qtyreceive')" class="text-center xs-only-text-left">
                                  {{(float)$qtyterima}}
                                </td>
                            @endif
                  					<td data-th="@lang('shop.AmountPO')" class="xs-only-text-left text-right">
                              {{number_format($line->amount_confirm,2)}}
                            </td>
                            @if($header->status>1)
                            <td data-th="@lang('shop.amountreceive')" class="xs-only-text-left text-right">
                              {{number_format($line->amount_accept,2)}}
                            </td>
                            @endif
                  				</tr>
                            @endforeach
                            @php($total = $taxtotal+$totamount)
                  			</tbody>
                        <tfoot>
                          @if($header->status<0 or ($header->status==0 and Auth::user()->customer_id==$header->customer_id ))
                               @php ($colgab = 4)
                          @elseif($header->status==1 or ($header->status==0 and Auth::user()->customer_id==$header->distributor_id) )
                               @php ($colgab = 4)

                          @else
                                @php ($colgab = 6)
                          @endif
                          @if($header->currency=='IDR')
                            @php($curr = "Rp")
                          @elseif($header->currency=='USD')
                            @php($curr = "$")
                          @endif
                  				<tr>
                            <td> Discount Reguler: Rp. {{number_format($header->disc_reg,2)}}</td>
            					      <td colspan="{{$colgab}}" style="text-align:right">
                              <strong>SubTotal: {{$curr}}</strong>
                            </td>
                  					<td style="text-align:right"><strong id="totprice2">
                              {{ number_format($header->amount_confirm,2) }}</strong>
                            </td>
                            @if($header->status>1)
                            <td style="text-align:right"><strong>
                              {{ number_format($header->amount_accept,2) }}</strong>
                            </td>
                            @endif
                  				</tr>
                          <tr>
                            <td>Discount Promo: Rp. {{number_format($header->disc_product,2)}}</td>
                            <td colspan="{{$colgab}}" style="text-align:right">
                              <strong>@lang('shop.Tax'): {{$curr}}</strong>
                            </td>
                  					<td style="text-align:right"><strong id="taxprice">
                              {{ number_format($header->tax_amount,2) }}</strong>
                            </td>
                            @if($header->status>1)
                            <td style="text-align:right"><strong>
                              @if($header->tax_amount==0)
                                @php($taxaccept=0)
                              @else
                                @php($taxaccept=round(0.1*$header->amount_accept))
                              @endif
                              {{number_format($taxaccept,2)}}
                            </strong>
                            </td>
                            @endif
                  				</tr>
                          <tr>
                            <td>Total Discount   : Rp. {{number_format( ($header->disc_product+$header->disc_reg),2)}}</td>
                            <td colspan="{{$colgab}}" style="text-align:right">
                              <strong>Total: {{$curr}}<strong>
                            </td>
                  					<td style="text-align:right"><strong id="total">
                              {{ number_format($header->amount_confirm+$header->tax_amount,2) }}</strong></td>
                              @if($header->status>1)
                              <td style="text-align:right"><strong>
                                {{ number_format($taxaccept+$header->amount_accept,2) }}</strong></td>
                              @endif
                  				</tr>
                  			</tfoot>
                  		</table>
                      <div id="btnorder">
                      <div class="col-sm-3 col-xs-6" style="padding-bottom:10px;">
                        @if($header->status==0 and $header->approve==0 and Auth::user()->customer_id==$header->distributor_id)
                        <input type="hidden" id="alasanreject" value="" name="alasan">
                        <button type="submit" onclick="return inputreason();" name="approve" value="reject" class="btn btn-warning btn-block" >
                          <i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject')
                        </button>
                        @else
                            <a href="{{URL::previous()}}" class="btn btn-warning btn-block" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
                        @endif

                      </div>
                        <div class="col-sm-6 hidden-xs"></div>
                      <div class="col-sm-3 col-xs-6" style="padding-bottom:10px;">
                        @if($header->status==0 and $header->approve==0 and Auth::user()->customer_id==$header->distributor_id)
                          <button type="submit" name="approve" value="approve" class="btn btn-success btn-block text-right">@lang('label.approve')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==0 and $header->approve==1 and Auth::user()->customer_id==$header->distributor_id)
                          <button type="submit" name="createExcel" value="Create Excel" class="btn btn-success btn-block">@lang('shop.createexcel')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==0 and Auth::user()->customer_id==$header->customer_id)
                          <button type="submit" name="batal" value="batal" class="btn btn-warning btn-block">@lang('label.cancel') <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @endif
                      </div>
                      </div>
                    </form>

                </div>
                @if(isset($deliveryno))
                <div role="tabpanel" class="tab-pane" id="shipping">
                  <div class="panel-group" id="accordion">

                    @foreach($deliveryno as $key => $delivery)
                    <div class="panel panel-default">
                      <form action="{{route('order.cancelPO')}}" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="header_id" value="{{$header->id}}">
                        <input type="hidden" name="deliveryno" value="{{$key}}">
                      <div class="panel-heading">
                        <h6 class="panel-title kirim-panel">
                          Delivery No:<a data-toggle="collapse" data-parent="#accordion" href="#{{$key}}">{{$key}}</a>
                          <p class="pull-right">Date: {{$delivery->first()->tgl_kirim}}</p>
                        </h6>
                      </div>
                      @if(Auth::user()->customer_id==$header->customer_id and intval($delivery->sum('qty_accept'))==0)
                      <div id="{{$key}}" class="panel-collapse collapse in">
                      @else
                      <div id="{{$key}}" class="panel-collapse collapse">
                      @endif
                        <div class="panel-body">
                          <table class="table">
                            <thead>
                              <tr>
                                <th style="width:45%;" class="text-center">@lang('shop.Product')</th>
                                <th style="width:10%;" class="text-center">@lang('shop.uom')</th>
                                <th style="width:10%;" class="text-center">@lang('shop.qtyship')</th>
                                <th style="width:10%;" class="text-center">@lang('shop.qtyreceive')</th>
                                @if($delivery->sum('qty_backorder')>0)
                                <th style="width:10%;" class="text-center">@lang('shop.qtybackorder')</th>
                                @endif
                              </tr>
                            </thead>
                            <tbody>
                              @foreach($delivery as $detail)
                              <tr>
                                <td>{{$detail->product->title}}</td>
                                <td style="text-align:center">{{$detail->uom_primary}}</td>
                                <td style="text-align:center">{{(float)$detail->qty_shipping+$detail->qty_backorder}}</td>
                                <td style="text-align:center">
                                  @if(Auth::user()->customer_id==$header->customer_id and is_null($detail->tgl_terima))
                                    <input type="number" class="form-control input-sm" value="{{(float)$detail->qty_shipping}}" name="qtyreceive[{{$detail->line_id}}][{{$detail->id}}]">
                                  @else
                                    {{(float)$detail->qty_accept}}
                                  @endif
                                </td>
                                @if($delivery->sum('qty_backorder')>0)
                                  <td style="text-align:center">
                                    {{(float)$detail->qty_backorder}}
                                  </td>
                                @endif
                              </tr>
                              @endforeach
                            </tbody>
                          </table>

                          @if(Auth::user()->customer_id==$header->customer_id and
                            is_null($delivery->where('deliveryno','=',$key)->first()->tgl_terima) and
                              intval($delivery->sum('qty_accept'))==0
                            )
                          <div class="form-group">
                            <label for="name" class="col-md-2 control-label">Keterangan</label>
                              <div class="col-md-10">
                                <textarea class="form-control" rows="2" name="note"></textarea>
                              </div>
                          </div>
                          <div class="col-xs-4 col-sm-2 pull-right">
                            <button type="submit" name="terima" value="terima" class="btn btn-success btn-block btnorder">@lang('shop.Receive')&nbsp;</button>
                          </div>
                          @elseif(isset($delivery->first()->tgl_terima) )
                            <div class="form-group">
                              <label for="name" class="col-md-2 control-label">Keterangan</label>
                                <div class="col-md-10">
                                  <textarea class="form-control" rows="2" name="note" readonly="readonly">{{$delivery->first()->keterangan}}</textarea>
                                </div>
                            </div>
                            <div class="form-group">
                              <label for="name" class="col-md-2 control-label">Tgl Terima</label>
                                <div class="col-md-10">
                                  {{$delivery->first()->tgl_terima}}
                                </div>
                            </div>
                          @endif
                        </div>
                      </div>
                    </form>
                    </div>
                    @endforeach
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
