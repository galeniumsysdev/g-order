@extends('layouts.navbar_product')
@section('css')
<link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
<link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
@endsection
@section('content')
<div class="container">
  @if($status= Session::get('message'))
  <div class="alert alert-success">
    {{$status}}
  </div>
  @endif
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>@lang('shop.listtransaksi')</strong></div>
        <div class="panel-body" style="overflow-x:auto;">
          @if($request->jns==1)
          <form class="form-inline" method="post" action="{{route('order.listPO')}}">
          @elseif($request->jns==2)
          <form class="form-inline" method="post" action="{{route('order.listSO')}}">
          @endif
              {{ csrf_field() }}
              <input type="hidden" value="{{$request->jns}}" name="jns">
          <div  class="form-group">
            <select name="status" class="form-control">
              <option value="" {{$request->status==-3?"selected=selected":""}}>--@lang('shop.statustransaction')--</option>
              @foreach($liststatus as $state)
              <option value="{{$state->id}}" {{$request->status==$state->id?"selected=selected":""}}>{{$state->name}}</option>
              @if($state->id == 0 and Auth::user()->hasRole('Principal'))
                <option value="x" {{$request->status=='x'?"selected=selected":""}}>Menunggu Booked Oracle</option>
              @endif
              @endforeach
            </select>
          </div>
          <div  class="form-group">
          @if($request->jns==1)
            <input type="text" name="criteria" placeholder="@lang('shop.criteriapo')" class="form-control" value="{{$request->criteria}}">
          @elseif($request->jns==2)
            <input type="text" name="criteria" placeholder="@lang('shop.criteriaso')" class="form-control" value="{{$request->criteria}}">
          @endif
          </div>
          <div  class="form-group">
            <input type="text" value="{{$request->tglaw}}" name="tglaw" placeholder="@lang('shop.from') (yyyy-mm-dd)" class="date form-control">
          </div>
          <a href="#" id="swapcity" class="ppp"></a>
          <div  class="form-group">
            <input type="text" value="{{$request->tglak}}" name="tglak" placeholder="@lang('shop.to') (yyyy-mm-dd)" class="date form-control">
          </div>
          <div  class="form-group">
            <input type="submit" name="search" value="Search" class="btn btn-primary form-control">
            @if($request->jns==2  and Auth::user()->hasRole('Principal'))
              <input type="submit" name="excel" value="Create Excel" class="btn btn-success form-control">
            @endif
          </div>
        </form>
        <div>&nbsp;</div>
        @forelse($trx as $t)
        <div class="card">
          <div class="card-block">
            <h4 class="card-title"><a href="{{route('order.checkPO',$t->id)}}" class="card-link">{{$t->notrx}}</a></h4>

            <h6 class="card-subtitle mb-2 text-muted">
                @if($request->jns==1)
                  @lang('shop.supplier'): <strong>{{$t->distributor_name}}</strong>
                @elseif($request->jns==2)
                  Customer: <strong>{{$t->customer_name}}</strong>
                @endif
            </h6>
            <p class="card-text"> @lang('shop.orderdate'):
              <strong>{{date('d-M-Y',strtotime($t->tgl_order))}}</strong> | Amount: <strong>{{number_format($t->amount_confirm+$t->tax_amount,2)}}</strong> | Status: <strong class="text-success">
              @if($t->status==0 and $t->approve==1 and Auth::user()->hasRole('Principal'))
                Menunggu Booked dari Oracle
              @else
              {{$t->status_name}}
              @endif
              </strong>
            </p>
          </div>
        </div>
        @empty
        <div class="card">
          <div class="card-block text-center">@lang('label.notfound')
          </div>
        </div>
        @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>

<script type="text/javascript">
          $('.date').datetimepicker({
              format: "YYYY-MM-DD",
              
          });
</script>
@endsection
