@extends('layouts.navbar_product')

@section('content')
<link rel="stylesheet" href="{{ URL::to('css/bootstrap_ms.css') }}">
<div class="container">
@include('shop.carausel')
</div>
<div class="container">
     {{csrf_field()}}
     <input type="hidden" id="baseurl" value="{{url('/')}}">
     @if(isset($nama_kategori))
      <h4>Category Product: {{$nama_kategori}}</h4><hr>
     @elseif(Auth::guest())
      <h4><strong>Top Product</strong></h4>
      <hr>
     @endif
  @forelse($products->chunk(6) as $productChunk)
    <div class="row">
      <div class="display-flex">
      @foreach($productChunk as $product)
        <div class="col-xs-6 col-ms-4 col-sm-4 col-md-2" >
          <div class="thumbnail">
            <a href="{{ route('product.detail',['id'=>$product->id])}}" title="{{ $product->title }}">
            @if($product->imagePath)
              <img data-src="holder.js/100%x180" alt="No Picture"  class="img product" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
            @else
              <img data-src="holder.js/100%x180" alt="No Picture" class="img product" src="" data-holder-rendered="true">
            @endif
            </a>
            <legend></legend>
			<div class="caption">
              <h4 style="text-align:center; margin-top:-2px; margin-bottom:-5px;"><a href="{{ route('product.detail',['id'=>$product->id])}}">{{ $product->title }}</a></h4>
            </div>
            <div class="boxprice">
              @if(Auth::check())
                <div class="input-group input-group-sm" style="width:100%; margin-left:8px;">
                    <input type="text" class="form-control" id="qty-{{$product->id}}" name="qty" value=1 size="2" style="text-align:right;width:50%">
                    <select name="satuan" class="form-control" style="width:40% " id="satuan-{{$product->id}}" onChange="getPrice('{{$product->id}}')">
                      @foreach($product->uom as $satuan)
                        @if(Auth::user()->hasRole('Distributor'))
                          <option value="{{$satuan->uom_code}}" {{$product->satuan_secondary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
                        @else
                          <option value="{{$satuan->uom_code}}" {{$product->satuan_primary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
                        @endif
                      @endforeach
                    </select>
                </div>
                <div class="clearfix price"  id="lblhrg-{{$product->id}}">
                  @if($product->item=="43")
                    @php ($currency="$ ")
                  @else
                    @php ($currency="Rp. ")
                  @endif
                  @if(Auth::user()->hasRole('Distributor'))
                    {{$currency.number_format($product->price_diskon,2)}}/{{$product->satuan_secondary}}
                  @else
                    {{$currency.number_format($product->price_diskon,2)}}/{{$product->satuan_primary}}
                  @endif

                </div>
                <div class="price coret" id="hrgcoret-{{$product->id}}">
                    @if($product->harga!=$product->price_diskon)
                      {{$currency.number_format($product->harga,2)}}
                    @endif
                </div>
              @endif
            </div>
              @if(Auth::check())
                <input type="hidden" id="hrg-{{$product->id}}" value="{{$product->harga}}">
                <input type="hidden" id="disc-{{$product->id}}" value="{{$product->price_diskon}}">
                <div class ="clearfix" id="addCart-{{$product->id}}">
                  @if((Auth::user()->can('Create PO')))
                    @if($polineexist->where('product_id',$product->id)->count()>0)
                       <span class="btn btn-sm btn-info btn-block"><i class="fa fa-shopping-cart" aria-hidden="true"></i> {{$polineexist->where('product_id',$product->id)->sum('qty_request')}}&nbsp; {{$polineexist->where('product_id',$product->id)->first()->uom}}</span>
                    @else
                       <a onclick="addCart('{{$product->id}}');return false;" href="#" class="btn btn-success btn-block"  role="button" id="addCart2-{{$product->id}}">@lang('shop.AddToCart')</a>
                    @endif
                  @endif
                </div>
                <div class="info-product" id="info-product-{{$product->id}}">
					@if(Auth::user()->hasRole('Distributor'))
						@php ($uom= $product->satuan_secondary)
						1{{$product->satuan_secondary." = ".(float)$product->rate."/".$product->satuan_primary}}<br>
					@endif

				</div>
          <div class="info-product" id="info-product2-{{$product->id}}" style="color:blue;font-weight:bold;text-align:center">
            @if($product->promo)
            @if($product->promo->product_attr_value==$product->promo->item_id)
            {{"Buy ".$product->promo->pricing_attr_value_from."+".$product->promo->benefit_qty." ".$product->promo->benefit_uom_code}}
            @endif
            @endif
          </div>
              @endif
          </div>
        </div>
      @endforeach
      </div>
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
