<center><h2>Report NOO</h2></center>
<table>
  <tr><td colspan="11" style="text-align:center"><strong>Parameter</strong></td></tr>
  @if($request->distributor)
  <tr>
    <td colspan="2">Distributor</td>
    <td>{{$request->distributor}}</td>
  </tr>
  @endif
  @if($request->city)
  <tr>
    <td colspan="2">City</td>
    <td>{{$request->city}}</td>
  </tr>
  @endif
  @if($request->channel)
  <tr>
    <td colspan="2">Channel</td>
    <td>{{$request->channel}}</td>
  </tr>
  @endif
  @if($request->divisi)
  <tr>
    <td colspan="2">Divisi</td>
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
<!DOCTYPE html>
<html>
<head>
  <title>Report NOO</title>
</head>
<body>
  <table>
    <thead>
      <tr>
        <td><strong>NO.</strong></td>
        <td><strong>NAMA OUTLET</strong></td>
        <td><strong>PROVINCE</strong></td>
        <td><strong>AREA</strong></td>
        <td><strong>KOORDINAT</strong></td>
        <td><strong>CHANNEL</strong></td>
        <td><strong>DIVISI</strong></td>
        <td><strong>ASPS</strong></td>
        <td><strong>ASPM</strong></td>
        <td><strong>RSPM</strong></td>
        <td><strong>TGL. REGISTER</strong></td>
        <td><strong>ID CUSTOMER</strong></td>
        <td><strong>DISTRIBUTOR</strong></td>
        <td><strong>STATUS</strong></td>
      </tr>
    </thead>
    <tbody>
      @php($i=0)
        @foreach($customers as $c)
        @php($i+=1)
        @if($c->distributor->count())
        @php($rowgab=$c->distributor->count())
        @else
        @php($rowgab=1)
        @endif
        <tr>
          <td rowspan="{{$rowgab}}" valign="middle">{{$i}}</td>
          <td>{{ $c->customer_name }}</td>
          <td>{{ $c->province }}</td>
          <td>{{ $c->city }}</td>
          <td>
            @if(isset ($c->longitude))
            {{ $c->longitude.','.$c->langitude }}
            @endif
          </td>
          <td>{{ $c->subgroup_name }}</td>
          <td>
            @if($c->psc_flag=="1" and $c->psc_flag=="1")
              PSC, Pharma
            @elseif($c->psc_flag=="1")
                PSC
            @elseif($c->pharma_flag=="1")
                Pharma
            @endif
          </td>
          <td>{{$c->asps_kode}}</td>
          <td>{{$c->aspm_kode}}</td>
          <td>{{$c->rspm_kode}}</td>
          <td>{{ $c->created_at }}</td>
          <td>{{$c->id}}</td>
          <td>@if($c->distributor->count())
            {{$c->distributor->first()->customer_name}}
            @endif
          </td>
          <td>{{ $c->Status }}</td>
        </tr>
        @if($rowgab>1)
          @php($lineno=0)
          @foreach ($c->distributor as $dist)
            @if($lineno!=0)
            <tr>
              <td></td>
              <td>{{ $c->customer_name }}</td>
              <td>{{ $c->province }}</td>
              <td>{{ $c->city }}</td>
              <td>
                @if(isset ($c->longitude))
                {{ $c->longitude.','.$c->langitude }}
                @endif
              </td>
              <td>{{ $c->subgroup_name }}</td>
              <td>
                @if($c->psc_flag=="1" and $c->psc_flag=="1")
                  PSC, Pharma
                @elseif($c->psc_flag=="1")
                    PSC
                @elseif($c->pharma_flag=="1")
                    Pharma
                @endif
              </td>
              <td>{{$c->asps_kode}}</td>
              <td>{{$c->aspm_kode}}</td>
              <td>{{$c->rspm_kode}}</td>
              <td>{{ $c->created_at }}</td>
              <td>{{$c->id}}</td>
              <td>{{$dist->customer_name}}</td>
              <td>{{ $c->Status }}</td>
            </tr>
            @endif
            @php($lineno+=1)
          @endforeach
        @endif
        @endforeach

    </tbody>
  </table>
</body>
</html>
