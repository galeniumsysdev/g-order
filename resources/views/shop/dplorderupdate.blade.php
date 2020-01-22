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
            </div>
          <br>

            <div class="tabcard col-sm-12">
              <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="order">
                  <form class="form-horizontal" id="frmorder" action="{{route('order.updatePO')}}" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="header_id" value="{{$header->id}}">
                    <table id="cart" class="table table-hover table-condensed">
                				<thead>
                				<tr>
                					<th style="width:55%" class="text-center">@lang('shop.Product')</th>
                					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
                          <th style="width:7%" class="text-center">@lang('shop.uom')</th>
                					<th style="width:7%" class="text-center">@lang('shop.qtyorder')</th>
                					<th style="width:15%" class="text-center">@lang('shop.AmountPO')</th>
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
                					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center">
                            {{ number_format($line->list_price/$line->conversion_qty,0) }}
                            <input type="hidden" id="hrg-{{$id}}" value="{{$line->list_price/$line->conversion_qty}}">
                          </td>
                          <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center">
                              {{ $line->uom_primary }}
                              <input type="hidden" name="uom[{{$id}}]" value="{{ $line->uom_primary }}">
                          </td>

                					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left" >
                            @if($header->status==-99 and $header->fill_in==1)
                              <input type="number" class="form-control qty" step="1" name="qtyorder[{{$id}}]" value="{{ (float)$line->qty_request_primary }}" id="{{$id}}" size="5">
                              <input type="hidden" id="oldqty-{{$id}}" value="{{(float)$line->qty_request_primary}}">
                            @else
                              {{ $line->qty_request_primary }}
                            @endif
                					</td>
                					<td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right" id="amount-{{$id}}">
                            {{number_format($line->amount_confirm,0)}}
                          </td>
                				</tr>
                        @endforeach
                			</tbody>
                      <tfoot>
                        @if($header->currency=='IDR')
                          @php($curr= "Rp.")
                        @elseif($header->currency=='USD')
                          @php($curr= "$")
                        @endif
                        @php($colgab=4)
                				<tr>
                					<td colspan="{{$colgab}}" class="hidden-xs text-right" id="totprice1">@lang('shop.AmountPO')
                            <input type="hidden" id="totprice" value="{{(float)$header->amount_confirm}}">
                            <input type="hidden" id="curr" value="{{$curr}}">
                            <input type="hidden" id="tax" value="{{(float)$header->tax_amount}}">
                          </td>
                					<td class="text-right xs-only-text-center"><strong id="totprice2"><label class="visible-xs-inline">@lang('shop.AmountPO'):</label>
                            {{ $curr." ".number_format($header->amount_confirm,0) }}</strong>
                          </td>
                				</tr>
                        <tr>
                          <td colspan="{{$colgab}}" class="hidden-xs text-right">@lang('shop.Tax') </td>
                					<td class="text-right xs-only-text-center"><strong id="tottax"><label class="visible-xs-inline">@lang('shop.Tax'):</label>
                            {{ $curr." ".number_format($header->tax_amount,0) }}</strong>
                          </td>
                				</tr>
                        <tr>
                          <td colspan="{{$colgab}}" class="hidden-xs text-right">@lang('shop.Total') </td>
                					<td class="text-right xs-only-text-center"><strong id="totamount"><label class="visible-xs-inline">@lang('shop.Total'):</label>
                            {{ $curr." ".number_format(($header->amount_confirm+$header->tax_amount),0) }}</strong>
                          </td>
                				</tr>
                			</tfoot>
                		</table>
                    <div class="col-xs-12 col-sm-3">
                      <a href="{{URL::previous()}}" class="btn btn-warning btn-block" ><i class="fa fa-angle-left" style="color:#fff"></i>@lang('label.back')</a>
                    </div>
                    <div class="col-sm-6 hidden-xs"></div>
                    <div class="col-xs-12 col-sm-3">
                      @if($header->status==-99 and Auth::user()->customer_id==$header->customer_id and $header->fill_in==1)
                        <button type="submit" name="update" value="update" class="btn btn-success btn-block btnorder">@lang('label.save')&nbsp; <i class="fa fa-angle-right" style="color:#fff"></i></button>
                      @endif
                    </div>
                  </form>
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
<script type="text/javascript">
$(".qty").change(function(){
  var nil=$(this).val();
  var id = this.id;
  var old = $("#oldqty-"+id).val();
  var price = $("#hrg-"+id).val();
  var amount = price *nil;
  var oldamount = price * old;
  var npp = parseFloat($("#totprice").val())+(price*(nil-old));
  var tax = parseFloat($("#tax").val());
  if (tax!=0)
  {
    tax = 0.1 * npp;
  }
  var total = npp+tax;
  //alert("id:"+old+"-"+nil+"-"+price+"-"+npp);
  $("#amount-"+id).html(rupiah(amount));
  $("#tax").val(tax);
  $("#totprice").val(npp);
  $("#tottax").html("<label class='visible-xs-inline'>@lang('shop.Tax'):</label> "+$("#curr").val()+rupiah(tax));
  $("#totprice2").html("<label class='visible-xs-inline'>@lang('shop.AmountPO'):</label> "+$("#curr").val()+rupiah(npp));
  $("#totamount").html("<label class='visible-xs-inline'>@lang('shop.Total'):</label> "+$("#curr").val()+rupiah(total));
  return true;
})

</script>

@endsection
