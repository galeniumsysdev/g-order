@extends('layouts.navbar_product')
@section('css')
<link href="{{ asset('css/table.css') }}" rel="stylesheet">
@endsection
@section('content')
<!--tidak ke oracle-->
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
                <label for="name" class="col-md-2 control-label">@lang('shop.supplier')</label>
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
                    @if($header->status==-2 and !is_null($header->alasan_tolak))
                    <input type="text" class="form-control" value="{{$header->status_name. ' (alasan: '.$header->alasan_tolak.')'}}" readonly>
                    @else
                    <input type="text" class="form-control" value="{{$header->status_name}}" readonly>
                    @endif
                </div>
              </div>
              @if($header->suggest_no)
              <div class="form-group">
                <label for="name" class="col-md-2 control-label">@lang('shop.suggestiondpl')</label>
                  <div class="col-md-10" >
                    <input type="text" class="form-control" value="{{$header->suggest_no}}" readonly>
                  </div>
              </div>
              @endif
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
                    <textarea class="form-control" rows="2" readonly>{{$header->ship_to_addr}}</textarea>
                  </div>
              </div>
              @if($header->dpl_no)
                <div class="form-group">
                  <label for="name" class="col-md-2 control-label">DPL No</label>
                    <div class="col-md-10" >
                      <input type="text" class="form-control" name="dpl_no" value="{{'G'.$header->dpl_no}}" readonly>
                    </div>
                </div>
              @endif
            </div>
          <br>

            <div class="tabcard col-sm-12">
              @if(($deliveryno->count()>0 and $header->status>=2)
            and (!($deliveryno->count()==1 and $header->status>2)))
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active" ><a href="#order" aria-controls="Order" role="tab" data-toggle="tab"><strong>Order</strong></a></li>
                    <li role="presentation"><a href="#shipping" aria-controls="Shipping" role="tab" data-toggle="tab"><strong>Shipping</strong></a></li>
                </ul>
              @endif
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="order">
                @if(Auth::user()->customer_id==$header->distributor_id)
                  <form class="form-horizontal" id="frmorder" action="{{route('order.approvalSO')}}" method="post">
                @elseif(Auth::user()->customer_id==$header->customer_id)
                  <form class="form-horizontal" id="frmorder" action="{{route('order.cancelPO')}}" method="post">
                @endif
                    {{ csrf_field() }}
                    <input type="hidden" name="header_id" value="{{$header->id}}">
                    @if ((($deliveryno->count()<=1 and ($header->status>=1 and $header->distributor_id == Auth::user()->customer_id))
                    or ($deliveryno->count()<=1  and $header->status>= 0 and $header->status<= 4 and $header->customer_id == Auth::user()->customer_id))
                    and $header->status!= 2 )
                      <div class="form-group {{ $errors->has('deliveryno') ? ' has-error' : '' }}">
                        <label for="subject" class="col-md-2 control-label">@lang('shop.deliveryno')</label>
                        <div class="col-md-4 ">
                          @if($deliveryno->count()==1)
                            <input type="text" name="deliveryno" value="{{$deliveryno->first()[0]->deliveryno}}" class="form-control" required readonly>
                          @else
                            <input type="text" name="deliveryno" value="" class="form-control" >
                          @endif
                          @if ($errors->has('deliveryno'))
                              <span class="help-block">
                                  <strong>{{ $errors->first('deliveryno') }}</strong>
                              </span>
                          @endif
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
                          @if($header->dpl_no and $header->status>=-2)
                          <th style="width:5%" class="text-center">Disc Distributor(%)</th>
                          <th style="width:5%" class="text-center">Disc GPL(%)</th>
                          <th style="width:5%" class="text-center">Bonus GPL</th>
                          @endif
                          @if($header->status>=0)
                              @if(Auth::user()->customer_id==$header->distributor_id and ($header->dpl_no) and $header->status==0)
                                <th style="width:7%" class="text-center">@lang('shop.qtyavailable')</th>
                              @endif
                              @if($header->status>1 or ($header->status==1 and Auth::user()->customer_id==$header->distributor_id))
                                <th style="width:7%" class="text-center">@lang('shop.qtyship')</th>
                              @endif
                              @if(Auth::user()->customer_id==$header->customer_id
                                or (Auth::user()->customer_id==$header->distributor_id and $header->status>1 )
                                )
                                <th style="width:7%" class="text-center">@lang('shop.qtyreceive')</th>
                              @endif
                          @endif

                					<th style="width:15%" class="text-center">@lang('shop.AmountPO')</th>
                          @if($header->status>1 and ($header->qty_accept!=0 and $header->qty_accept<$header->qty_request) or ($header->qty_shipping!=0 and $header->qty_shipping<$header->qty_confirm) )
                            <th style="width:20%" class="text-center">@lang('shop.amountreceive')</th>
                          @endif
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
                            {{ number_format($line->list_price/$line->conversion_qty,2) }}
                          </td>
                          <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center">
                              {{ $line->uom_primary }}
                              <input type="hidden" name="uom[{{$id}}]" value="{{ $line->uom_primary }}">
                          </td>

                					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left" id="ord-{{$id}}">
                              {{ $line->qty_request_primary }}
                					</td>
                          @if($header->dpl_no)
                          <td data-th="Disc Distributor(%)" class="xs-only-text-left text-center">{{$line->discount}}</td>
                          <td data-th="Disc GPL(%)" class="xs-only-text-left text-center">{{$line->discount_gpl}}</td>
                          <td data-th="Bonus GPL" class="xs-only-text-left text-center">{{(int)$line->bonus_gpl." ".$line->uom_primary}}</td>
                          @endif
                          @if($header->status>=0)
                              @if(Auth::user()->customer_id==$header->distributor_id and $header->status==0 and ($header->dpl_no) )
                                <td data-th="@lang('shop.qtyavailable')"  class="text-center xs-only-text-left" id="avl-{{$id}}">
                                    <input type="text" name="qtyshipping[{{$id}}]" class="form-control" value="{{(float)$line->qty_request_primary+$line->bonus_gpl}}">
                                </td>
                              @elseif(Auth::user()->customer_id==$header->distributor_id and $header->status==0)
                               <input type="hidden" name="qtyshipping[{{$id}}]" class="form-control" value="{{(float)$line->qty_request_primary+$line->bonus_gpl}}">
                              @endif
                              @if($header->status>1 or ($header->status==1 and Auth::user()->customer_id==$header->distributor_id))
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
                                  {{number_format($line->qty_accept_primary,2)}}
                              </td>
                              @elseif(Auth::user()->customer_id==$header->customer_id)
                                <td  data-th="@lang('shop.qtyreceive')"  class="text-center xs-only-text-left">

                                  @if (($header->status>=2  or $deliveryno->count()>1 )
                                    and !($header->status==3 and $deliveryno->count()==1 )
                                     )
                                    {{number_format($line->qty_accept_primary,2)}}
                                  @elseif($header->status == 0 )
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_request_primary}}" min="0" class="form-control">
                                  @elseif($header->status==1)
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_confirm_primary}}" min="0" class="form-control">
                                  @elseif($header->status==3)
                                    <input type="number" name="qtyreceive[{{$id}}]" value="{{(float)$line->qty_shipping_primary}}" min="0" class="form-control">
                                  @endif
                                </td>
                              @endif
                          @endif

                					<td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                            {{number_format($line->amount_confirm,2)}}
                          </td>
                          @if($header->status>1 and ($header->qty_accept!=0 and $header->qty_accept<$header->qty_request) or ($header->qty_shipping!=0 and $header->qty_shipping<$header->qty_confirm) )
                            <td data-th="@lang('shop.amountreceive')" class="xs-only-text-left text-right">
                              {{number_format($line->amount_accept,2)}}
                            </td>
                          @endif
                				</tr>
                        @endforeach
                			</tbody>
                      <tfoot>
                        @if($header->currency=='IDR')
                          @php($curr= "Rp.")
                        @elseif($header->currency=='USD')
                          @php($curr= "$")
                        @endif
                        @if($header->status< 0)
                             @php($colgab=4)
                        @elseif($header->status==0 and isset($header->dpl_no) and $header->distributor_id==Auth::user()->customer_id)
                                  @php($colgab=5)
                        @elseif($header->status==0 and is_null($header->dpl_no) and $header->distributor_id==Auth::user()->customer_id)
                              @php($colgab=4)
                        @elseif($header->status==0)
                             @php($colgab=5)
                        @elseif($header->status==1)
                             @php($colgab=5)
                        @elseif($header->status>1)
                             @php($colgab=6)
                        @endif
                        @if($header->dpl_no)
                          @php($colgab=$colgab+3)
                        @endif

                				<tr>
                					<td colspan="{{$colgab}}" class="hidden-xs text-right">@lang('shop.AmountPO') </td>
                					<td class="text-right xs-only-text-center"><strong id="totprice2"><label class="visible-xs-inline">@lang('shop.AmountPO'):</label>
                            {{ $curr." ".number_format($header->amount_confirm,2) }}</strong></td>
                          @if($header->status>1 and ($header->qty_accept!=0 and $header->qty_accept<$header->qty_request) or ($header->qty_shipping!=0 and $header->qty_shipping<$header->qty_confirm) )
                          <td class="text-right xs-only-text-center hidden-xs"><strong id="totprice2">
                            <label class="visible-xs-inline">SubTotal Terima/Kirim:</label>
                            {{ $curr." ".number_format($header->amount_accept,2) }}</strong>
                          </td>
                          @endif
                				</tr>
                        <tr>
                          <td colspan="{{$colgab}}" class="hidden-xs text-right">@lang('shop.Tax') </td>
                					<td class="text-right xs-only-text-center"><strong id="totprice2"><label class="visible-xs-inline">@lang('shop.Tax'):</label>
                            {{ $curr." ".number_format($header->tax_amount,2) }}</strong>
                          </td>
                          @if($header->status>1 and ($header->qty_accept!=0 and $header->qty_accept<$header->qty_request) or ($header->qty_shipping!=0 and $header->qty_shipping<$header->qty_confirm) )
                          <td class="text-right xs-only-text-center hidden-xs"><strong id="totprice2">
                            <label class="visible-xs-inline">@lang('shop.Tax') Terima/Kirim:</label>
                            @if($header->tax_amount==0)
                            @php($taxkirim=0)
                            @else
                            @php($taxkirim=$header->amount_accept*0.1)
                            @endif
                            {{ $curr." ".number_format($taxkirim,2) }}
                            </strong>
                          </td>
                          @endif
                				</tr>
                        <tr>
                          <td colspan="{{$colgab}}" class="hidden-xs text-right">@lang('shop.Total') </td>
                					<td class="text-right xs-only-text-center"><strong id="totprice2"><label class="visible-xs-inline">@lang('shop.Total'):</label>
                            {{ $curr." ".number_format(($header->amount_confirm+$header->tax_amount),2) }}</strong>
                          </td>
                          @if($header->status>1 and ($header->qty_accept!=0 and $header->qty_accept<$header->qty_request) or ($header->qty_shipping!=0 and $header->qty_shipping<$header->qty_confirm) )
                          <td class="text-right xs-only-text-center hidden-xs"><strong id="totprice2">
                            <label class="visible-xs-inline">@lang('shop.Total') Terima/Kirim:</label>
                            {{ $curr." ".number_format($header->amount_accept+$taxkirim,2) }}
                            </strong>
                          </td>
                          @endif
                				</tr>

                			</tfoot>
                		</table>
                    @if(($header->status>=0 and $header->status < 2 and Auth::user()->customer_id==$header->customer_id)
                       or ($header->status==3 and $deliveryno->count()==1 and Auth::user()->customer_id==$header->customer_id)
                       )
                       <div class="form-group">
                         <label for="note" class="col-md-2 control-label">Note</label>
                         <div class="col-md-10 ">
                             {{ Form::textarea('note','',array('class'=>'form-control','rows'=>3)) }}
                         </div>
                       </div>
                       <div>&nbsp;</div>
                    @endif
                    <div class="col-xs-12 col-sm-3">
                        @if($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
                          <!--<button type="submit" name="approve" value="reject" class="btn btn-block btn-warning btnorder" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject') PO-->
                          <input type="hidden" id="alasanreject" value="" name="alasan">
                          <button type="submit" onclick="return inputreason();" name="approve" value="reject" class="btn btn-warning btn-block btnorder" >
                            <i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.reject') PO
                          </button>
                        @elseif($header->status==0 and Auth::user()->customer_id==$header->customer_id)
                          <button type="submit"  name="batal" value="batal" class="btn btn-block btn-warning btnorder" ><i class="fa fa-angle-left" style="color:#fff"></i> @lang('shop.cancelpo')</button>
                        @elseif($header->status==1 or ($header->status==3 and $deliveryno->count()==1))
                          <a href="{{URL::previous()}}" class="btn btn-warning btn-block" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
                        @endif

                    </div>
                    <div class="col-sm-6 hidden-xs"></div>
                    <div class="col-xs-12 col-sm-3">
                      @if($header->status>=0 )
                        @if(($header->status>=0 and $header->status < 2 and Auth::user()->customer_id==$header->customer_id)
                           or ($header->status==3 and $deliveryno->count()==1 and Auth::user()->customer_id==$header->customer_id)
                           )
                          <button type="submit" name="terima" value="terima" class="btn btn-success btn-block btnorder">@lang('shop.Receive')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==0 and Auth::user()->customer_id==$header->distributor_id)
                          <button type="submit" name="approve" value="approve" class="btn btn-success btn-block btnorder">@lang('label.approve')&nbsp;PO <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @elseif($header->status==1 and Auth::user()->customer_id==$header->distributor_id and (Auth::user()->hasRole('Distributor') or Auth::user()->hasRole('Distributor Cabang')))
                          <button type="submit" name="kirim" value="kirim" class="btn btn-success btn-block btnorder">@lang('shop.Send')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                        @endif
                      @endif
                    </div>
                  </form>
                </div>
                @if(isset($deliveryno))
                  <div role="tabpanel" class="tab-pane" id="shipping">
                    <div class="panel-group" id="accordion">
                      @php($karakter_ubah = array("(", ")"))
                      @foreach($deliveryno as $key => $delivery)
                        <div class="panel panel-default">
                          <form action="{{route('order.cancelPO')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="header_id" value="{{$header->id}}">
                            <div class="panel-heading">
                              <h6 class="panel-title kirim-panel">
                                Delivery No:<a data-toggle="collapse" data-parent="#accordion" href="#{{str_replace($karakter_ubah,"",$key)}}">{{$key}}</a>
                                <input type="hidden" name="deliveryno" value="{{$key}}">
                                @if($delivery->first()->tgl_kirim)
                                <p class="pull-right">Date: {{$delivery->first()->tgl_kirim}}</p>
                                @endif
                              </h6>
                            </div>
                            @if(Auth::user()->customer_id==$header->customer_id and $delivery->sum('qty_accept')==0)
                            <div id="{{str_replace($karakter_ubah,"",$key)}}" class="panel-collapse collapse in">
                            @else
                            <div id="{{str_replace($karakter_ubah,"",$key)}}" class="panel-collapse collapse">
                            @endif
                              <div class="panel-body">
                                <table class="table">
                                  <thead>
                                    <tr>
                                      <th style="width:45%;" class="text-center">@lang('shop.Product')</th>
                                      <th style="width:10%;" class="text-center">@lang('shop.uom')</th>
                                      <th style="width:10%;" class="text-center">@lang('shop.qtyship')</th>
                                      <th style="width:10%;" class="text-center">@lang('shop.qtyreceive')</th>
                                      <th style="width:10%;" class="text-center">@lang('shop.qtybackorder')</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($delivery as $detail)
                                    <tr>
                                      <td>{{$detail->product->title}}</td>
                                      <td style="text-align:center">{{$detail->uom_primary}}</td>
                                      <td style="text-align:center">{{(float)$detail->qty_shipping+$detail->qty_backorder}}</td>
                                      <td style="text-align:center">
                                        @if(Auth::user()->customer_id==$header->customer_id and is_null($detail->qty_accept))
                                          <input type="number" class="form-control input-sm" value="{{(float)$detail->qty_shipping}}" name="qtyreceive[{{$detail->line_id}}][{{$detail->id}}]" min="0">
                                        @else
                                          {{(float)$detail->qty_accept}}
                                        @endif
                                      </td>
                                      <td style="text-align:center">
                                        {{(float)$detail->qty_backorder}}
                                      </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                                @if($delivery->first()->keterangan)
                                <div class="col-md-2 form-label">
                                    <label for="note">Note</label>
                                </div>
                                <div class="col-md-10">
                                  <span class="default-value">
                                    {{ Form::textarea('note',$delivery->first()->keterangan,array('class'=>'form-control','rows'=>3,'readonly'=>'readonly')) }}
                                  </span>
                                </div>
                                @endif
                                @if(Auth::user()->customer_id==$header->customer_id and $delivery->sum('qty_accept')==0 and is_null($delivery->first()->tgl_terima))
                                <div class="col-md-2 form-label">
                                    <label for="note">Note</label>
                                </div>
                                <div class="col-md-10">
                                  <span class="default-value">
                                    {{ Form::textarea('note','',array('class'=>'form-control','rows'=>3)) }}
                                  </span>
                                </div>
                                <div class="col-xs-4 col-sm-2 pull-right">
                                  <button type="submit" name="terima" value="terima" class="btn btn-success btn-block btnorder">@lang('shop.Receive')&nbsp;</button>
                                </div>
                                @endif
                              </div>
                            </div>
                          </form>
                        </div>

                      @endforeach

                      @if($header->status >1 and  $header->status< 3)
                        <div class="panel panel-default">
                          <form action="{{Auth::user()->customer_id==$header->customer_id?route('order.cancelPO'):route('order.approvalSO')}}" method="post">
                            {{ csrf_field() }}
                            <input type="hidden" name="header_id" value="{{$header->id}}">
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
                                    @if((floatval($newdelivery->qty_shipping_primary) < floatval($newdelivery->qty_confirm_primary) and Auth::user()->customer_id ==$header->distributor_id)
                                      or (floatval($newdelivery->qty_accept_primary) < (floatval($newdelivery->qty_request_primary)+intval($newdelivery->bonus_gpl)) and floatval($newdelivery->qty_shipping_primary) < (floatval($newdelivery->qty_request_primary)+intval($newdelivery->bonus_gpl)) and Auth::user()->customer_id ==$header->customer_id)
                                       )
                                    <tr>
                                      <td>{{$newdelivery->title}}</td>
                                      <td style="text-align:center">{{$newdelivery->uom_primary}}</td>
                                      <td style="text-align:center">
                                        @if(Auth::user()->customer_id ==$header->distributor_id)
                                          <input type="number" name="qtyshipping[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_confirm_primary - floatval($newdelivery->qty_shipping_primary)}}" min="0" class="form-control input-sm">
                                        @else
                                          0
                                        @endif

                                      </td>
                                      <td style="text-align:center">
                                        @if(Auth::user()->customer_id ==$header->customer_id)
                                          @if(is_null($newdelivery->qty_confirm_primary))
                                            <input type="number" name="qtyreceive[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_request_primary - floatval($newdelivery->qty_accept_primary)}}" min="0" class="form-control input-sm">
                                          @elseif(is_null($newdelivery->qty_shipping_primary))
                                            <input type="number" name="qtyreceive[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_confirm_primary - floatval($newdelivery->qty_accept_primary)}}" min="0" class="form-control input-sm">
                                          @elseif($newdelivery->qty_shipping_primary<$newdelivery->qty_confirm_primary)
                                            <input type="number" name="qtyreceive[{{$newdelivery->line_id}}]" value="{{(float)$newdelivery->qty_confirm_primary - floatval($newdelivery->qty_shipping_primary)}}" min="0" class="form-control input-sm">
                                          @endif
                                        @endif

                                      </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                  </tbody>
                                </table>
                                @if(Auth::user()->customer_id==$header->customer_id and $header->status!=4)
                                  <div class="col-md-2 form-label">
                                      <label for="note">Note</label>
                                  </div>
                                  <div class="col-md-10">
                                    <span class="default-value">
                                      {{ Form::textarea('note','',array('class'=>'form-control','rows'=>3)) }}
                                    </span>
                                  </div>
                                @endif
                                <div class="col-xs-4 col-sm-2 pull-right">
                                  @if(Auth::user()->customer_id==$header->customer_id and $header->status!=4)
                                  <button type="submit" name="terima" value="terima" class="btn btn-success btn-block">@lang('shop.Receive')&nbsp;</button>
                                  @elseif(Auth::user()->customer_id==$header->distributor_id and $header->status==2)
                                  <button type="submit" name="kirim" value="kirim" class="btn btn-success btn-block">@lang('shop.Send')&nbsp;</button>
                                  @endif
                                </div>

                              </div>
                            </div>
                          </form>
                        </div>
                      @endif
                    </div>
                  </div>
                @endif
              </div>
            </div>

            @if(($header->status>=2 and $header->status<=4
              and ($lines->sum('qty_shipping_primary')!=0 or $lines->sum('qty_accept_primary')!=0 )
              and (!($header->status==3 and $deliveryno->count()==1))
              ) or ($header->status< 0))

              <div class="col-xs-12 col-sm-3">
                    <a href="{{URL::previous()}}" class="btn btn-warning btn-block" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
              </div>
            @endif

        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')

<script src="{{ asset('js/myproduct.js') }}"></script>

@endsection
