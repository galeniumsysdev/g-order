<!DOCTYPE html>
<html>
<head>
    <title>Role</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

</head>
<body style="background-color:#fff">
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>Order Product</strong></div>
          <div class="panel-body">
            <table class="table-condensed">
              <tr valign="top">
                <td>No.Trx</td>
                <td>{{$header->notrx}}</td>
                <td>Nomor PO</td>
                <td>{{$header->customer_po}}</td>
              </tr>
              <tr valign="top">
                <td>Distributor</td>
                <td>{{$header->distributor_name}}</td>
                <td>Customer</td>
                <td>{{$header->customer_name}}</td>
              </tr>
              <tr valign="top">
                <td>Tanggal Order</td>
                <td>{{date('d-M-Y',strtotime($header->tgl_order))}}</td>
                <td rowspan="2" valign="top">Alamat Pengiriman</td>
                <td rowspan="2">  {{$header->ship_to_addr}}</td>
              </tr>
              <tr valign="top">
                <td>Status</td>
                <td>{{$header->status_name}}</td>
              </tr>
            </table>
              <br>
            <table id="cart" class="table table-hover table-condensed">
      				<thead>
      				<tr>
      					<th style="width:45%" class="text-center">@lang('shop.Product')</th>
      					<th style="width:10%" class="text-center">@lang('shop.Price')</th>
                <th style="width:5%" class="text-center">@lang('shop.uom')</th>
      					<th class="text-center">@lang('shop.qtyorder')</th>
          			<th style="width:20%" class="text-center">@lang('shop.SubTotal')</th>
      				</tr>
      			</thead>
      			<tbody>
              @php ($totamount = 0)
              @foreach($lines as $line)
                @php ($id  = $line->line_id)
      				<tr>
      					<td >
      						<div class="row">
      							<div class="col-sm-10">
      								{{ $line->title }}
      							</div>
      						</div>
      					</td>
      					<td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" >{{ number_format($line->unit_price,2) }}</td>
                <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center" >{{ $line->uom }}</td>
      					<td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left">
                    {{ $line->qty_request }}
      					</td>

      					<td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                    {{  number_format($line->amount_confirm,2) }}
                </td>
      				</tr>
                @endforeach
      			</tbody>
            <tfoot>
      				<tr>
      					<td colspan="4" class="text-right">SubTotal: </td>
      					<td class="text-right"><strong id="totprice2">
                  @if($header->currency=='IDR')
                  @php($curr = "Rp.")
                  @elseif($header->currency=='USD')
                  @php($curr = "$")
                  @endif
                  {{ $curr.number_format($header->amount_confirm,2) }}</strong></td>
      				</tr>
              <tr>
      					<td colspan="4" class="text-right">Tax: </td>
      					<td class="text-right"><strong id="totprice2">
                  {{ $curr.number_format($header->tax_amount,2) }}</strong></td>
      				</tr>
              <tr>
      					<td colspan="4" class="text-right">Total: </td>
      					<td class="text-right"><strong id="totprice2">
                  {{ $curr.number_format($header->total_amount,2) }}</strong></td>
      				</tr>
      			</tfoot>
      		</table>
          </div>
        </div>
    </div>
  </div>
</div>

</body>
</html>
