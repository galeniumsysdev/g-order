<center><h2>REKAP DPL</h2></center>
<table>
  <tr><td colspan="2"><strong>Parameter</strong></td></tr>
  <tr>
    <td>Period</td>
    <td>{{ $request->trx_in_date }}</td>
  </tr>
  @if(!is_null($request->asm_id))
    <tr>
      <td>ASM</td>
      <td>{{ $request->asm }}</td>
    </tr>
  @endif
  @if(!is_null($request->spv_id))
    <tr>
      <td>SPV</td>
      <td>{{ $request->spv }}</td>
    </tr>
  @endif
  @if(!is_null($request->dist_id))
    <tr>
      <td>Distributor</td>
      <td>{{ $request->distributor }}</td>
    </tr>
  @endif
</table>
<table border="1">
  <thead>
    <tr>
      <th rowspan="2">No.</th>
      <th rowspan="2">No. DPL</th>
      <th rowspan="2">Tanggal DPL</th>
      <th rowspan="2">Nama Outlet / DR</th>
      <th rowspan="2">Nama MR / SPV</th>
      <th rowspan="2">Nama Produk</th>
      <th rowspan="2">Sales In Unit</th>
      <th rowspan="2">HNA</th>
      <th rowspan="2">Total</th>
      <th colspan="3" align="center">Kondisi Discount</th>
      <th rowspan="2">Distributor</th>
      <th rowspan="2">No. Transaksi</th>
      <th rowspan="2">Status</th>
    </tr>
    <tr>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th></th>
      <th>GPL Bonus</th>
      <th>GPL (%)</th>
      <th>Distributor</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @php($no=0)
    @php($tmpheader=null)
    @foreach($datalist as $key => $data)
    @php($tmpheader=$key)
    @php($no+=1)
    @php($lineno=0)
      <tr>
        <td style="vertical-align:middle">{{ $no }}</td>
        <td style="vertical-align:middle">{{ $key }}</td>
        <td style="vertical-align:middle">{{ $data->first()->created_at }}</td>
        <td style="vertical-align:middle">{{ $data->first()->customer_name }}</td>
        <td style="vertical-align:middle">{{ $data->first()->spv_name }}</td>
    @foreach($data as $detail)
    @php($lineno+=1)
        <td style="vertical-align:middle">{{ $detail->nm_product }}</td>
        <td style="vertical-align:middle">{{ $detail->qty_request_primary }}</td>
        <td style="vertical-align:middle">{{ $detail->hna }}</td>
        <td style="vertical-align:middle">{{ $detail->total }}</td>
        <td style="vertical-align:middle">{{ $detail->gpl_bonus }}</td>
        <td style="vertical-align:middle">{{ $detail->gpl_discount }}</td>
        <td style="vertical-align:middle">{{ $detail->disc_distributor }}</td>
        <td style="vertical-align:middle">{{ $detail->distributor }}</td>
        <td style="vertical-align:middle">{{ $detail->deliveryno }}</td>
        @if($lineno==1)
          <td>{{$detail->status}}</td>
        @else
          <td></td>
        @endif
      </tr>
        @if($lineno!=$data->count())
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        @endif

    @endforeach
    @endforeach
  </tbody>
</table>
