@extends('layouts.navbar_product')
@section('content')
<div class="container">
@include('shop.carausel')
</div>
<div class="container">

  @forelse($distributors->chunk(6) as $distchunk)
    <div class="row">
      <h2>Distributor</h2>
      @foreach($distchunk as $distributor)
        <div class="col-sm-6 col-md-2 col-xs-6">
          <div class="thumbnail">
            @if($distributor->avatar!="default.jpg")
              <img data-src="holder.js/100%x100" alt="100%x100" style="height: 100px; width: 100%; display: block;" src="{{ asset('/uploads/avatars').'/'.$distributor->avatar }}" data-holder-rendered="true">
            @else
              <img data-src="holder.js/100%x100" alt="100%x100" style="height: 100px; width: 100%; display: block;" src="{{ asset('img/e-order1.png') }}" data-holder-rendered="true">
            @endif
            <div class="caption">
              <a href="{{route('product.buy')}}?dist={{$distributor->id}}">{{ $distributor->customer_name }}</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @empty
      <div class="row"><h2 align="center">No Product</h2></div>
  @endforelse
</div>
<div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/myproduct.js') }}"></script>
@endsection
