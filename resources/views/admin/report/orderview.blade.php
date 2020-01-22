<center><h2>Report Order</h2></center>
<table>
  <tr><td colspan="2">Parameter</td></tr>
  <tr>
    <td>Period</td>
    <td>{{date_format(date_create($request->tglaw),'d-M-Y') }} s.d {{date_format(date_create($request->tglak),'d-M-Y')}}</td>
  </tr>
  @if(isset($request->distributor))
  <tr>
    <td>Distributor</td>
    <td>{{$request->distributor}}</td>
  </tr>
  @endif
  @if(isset($request->outlet))
  <tr>
    <td>Outlet</td>
    <td>{{$request->outlet}}</td>
  </tr>
  @endif
  @if(isset($request->propinsi))
  <tr>
    <td>Propinsi</td>
    <td>{{$namapropinsi}}</td>
  </tr>
  @endif
  @if(isset($request->channel))
  <tr>
    <td>Channel</td>
    <td>{{$request->channel}}</td>
  </tr>
  @endif
  @if($request->divisi)
  <tr>
    <td>Divisi</td>
    <td>{{$request->divisi}}
      @if($request->psc_flag=="1" and $request->pharma_flag=="1")
        PSC/PHARMA
      @elseif($request->psc_flag=="1")
        PSC
      @elseif($request->pharma_flag=="1")
        PHARMA
      @endif
    </td>
  </tr>
  @endif
</table>
<table border="1">
  <thead>
    <tr>
      <th rowspan="2">#</th>
      <th rowspan="2">No.trx</th>
      <th rowspan="2">Nama Outlet</th>
      <th rowspan="2">Channel</th>
      <th rowspan="2">Alamat</th>
      <th rowspan="2">Distributor</th>
      <th rowspan="2">Divisi</th>
      <th rowspan="2">Tgl Order</th>
      <th colspan="4" align="center">Order</th>
      <th rowspan="2">Tgl Confirm Distributor</th>
      <th rowspan="2">Tgl Confirm Outlet Terima</th>
      <th colspan="4" align="center">DO (Release PO)</th>
      <th rowspan="2">Service Level</th>
      <th rowspan="2">Lead Time</th>
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
      <th>Product</th>
      <th>Qty</th>
      <th>Harga</th>
      <th>Value</th>
      <th></th>
      <th></th>
      <th>No SJ</th>
      <th>Qty</th>
      <th>Value</th>
      <th>Tgl Kirim</th>
      <th></th>
      <th></th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    @php($no=0)
    @php($tmpheader=null)
    @foreach($datalist->groupBy('id') as $key => $data)
    @php($no+=1)
    @php($groupheader=$data->count())
      <tr>
        @if($key<>$tmpheader)
        @php($tmpheader=$key)
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$no}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->notrx}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->customer_name}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->channel}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->alamat}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->distributor_name}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->divisi}}</td>
        <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$data->first()->tgl_order}}</td>

        @endif
        @php($lineno=0)
        @php($tmpline=null)
        @php($tmpline2=null)
        @foreach($data as $detail)
          @if($lineno>0)
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
          @endif
          @if($detail->line_id<>$tmpline)
            @php($tmpline=$detail->line_id)
            @php($groupline=$data->where('line_id',$tmpline)->count())
            <td rowspan="{{$groupline}}" style="vertical-align:middle">{{$detail->title}}</td>
            <td rowspan="{{$groupline}}" style="vertical-align:middle" align="right">{{$detail->qty_request_primary}}</td>
            <td rowspan="{{$groupline}}" style="vertical-align:middle" align="right">{{number_format($detail->unit_price_primary,2)}}</td>
            <td rowspan="{{$groupline}}" style="vertical-align:middle" align="right">{{number_format($detail->amount,2)}}</td>
          @else
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          @endif
          @if($lineno==0)
            <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$detail->tgl_approve}}</td>
          @else
            <td></td>
          @endif

          <td>{{$detail->tgl_terima}}</td>
          <td>{{$detail->deliveryno}}</td>
          <td align="right">{{$detail->qty_shipping}}</td>
          <td align="right">{{number_format($detail->qty_shipping*$detail->unit_price_primary,2)}}</td>
          <td>{{$detail->tgl_kirim}}</td>
          @if($detail->line_id<>$tmpline2)
            @php($tmpline2=$detail->line_id)
            <td rowspan="{{$groupline}}" align="center" style="vertical-align:middle">
              @if(isset($detail->tgl_kirim))
              {{($data->where('line_id',$detail->line_id)->sum('qty_shipping')/$detail->qty_request_primary)*100}}%
              @endif
            </td>
          @else
            <td></td>
          @endif

          <td>@if(isset($detail->tgl_terima))
                {{$detail->lead_time}}
              @endif
          </td>
          @if($lineno>0) </tr>@endif

          @if($lineno==0)
          <td rowspan="{{$groupheader}}" style="vertical-align:middle">{{$detail->status_name}}</td>
          @endif
          @php($lineno+=1)
        @endforeach
      </tr>



    @endforeach


  </tbody>
</table>
