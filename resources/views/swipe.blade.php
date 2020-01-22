@extends('layouts.navbar_product')
@section('css')
<style type="text/css">
.view-all {
  float: right;
  margin-top: -30px;
}
.row{
  margin-left: -15px;
  margin-right: -15px;
}

.thumbnail{
  margin-left: 0px;
  margin-right: 0px;
}
.thumbnail .caption{
  min-height:70px;
}
.owl-carousel .owl-item img{
  display:block;
  width: auto!important;
}
@media (max-width: 767px) {
  body{
    padding-top: 100px;
  }
}
</style>
<!-- Owl Stylesheets -->
<link rel="stylesheet" href="{{asset('assets/owlcarousel/assets/owl.carousel.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/owlcarousel/assets/owl.theme.default.min.css')}}">
@endsection
@section('content')
    <div class="container">
      @include('shop.carausel')
    </div>
    <!-- body -->
    @foreach($product_flexfields as $flexfield)
    @php ($price=0)
    @php ($disc=0)
    @php($flexfield->products = $flexfield->products->filter(function($p){
      return $p->getRealPrice(Auth::user()->id,$p->satuan_primary)>0;
    }))
    @if (!empty(Auth::user()->customer->masa_berlaku))
      @php($masaberlaku =Auth::user()->customer->masa_berlaku)
    @else
      @php($masaberlaku = date_create("2017-01-01"))
    @endif
    @if(Auth::user()->customer->ijin_pbf==0 or $masaberlaku < \Carbon\Carbon::now()->toDateString() )
      @php($flexfield->products = $flexfield->products->reject(function($p){
        return in_array($p->tipe_dot,array('MERAH','BIRU','HIJAU'));
      }))
    @elseif(Auth::user()->customer->ijin_pbf==2 and  $masaberlaku >= \Carbon\Carbon::now()->toDateString())
      @php($flexfield->products = $flexfield->products->filter(function($p){
        return $p->tipe_dot!='MERAH';
      }))
    @endif
    <div class="container">
      <br>
      <h4><strong>{{$flexfield->description}}</strong></h4>
      @if($flexfield->products->where('Enabled_Flag','Y')->count()>10)
      <p class="view-all"><a href="{{route('product.category',$flexfield->flex_value)}}">Lihat Semua &nbsp;</a><i class="fa fa-angle-right" aria-hidden="true"></i></p>
      @endif
      <legend></legend>
      <div class="row">
        <div class="large-12 columns">
          <div class="owl-carousel owl-theme owl-loaded owl-drag">
            @foreach($flexfield->products->where('Enabled_Flag','Y')->sortByDesc('pareto')->sortBy('title')->take(10) as $product)
            @if($product->getRealPrice(Auth::user()->id,$product->satuan_primary)!=0)
            <div class="item">
              <div class="thumbnail">
                @if($product->imagePath)
                  <img data-src="holder.js/100%x180" alt="No Image"  class="img product" style="height:100px;" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
                @else
                  <img data-src="holder.js/100%x180" alt="No Image" class="img product" style="height:100px;" src="" data-holder-rendered="true">
                @endif
                <legend></legend>
                <div class="caption">
                  <h4 style="text-align: center; margin-top: -2px;">
                    <a href="{{ route('product.detail',['id'=>$product->id])}}">{{$product->title}}</a>
                  </h4>
                </div>
                <div class="boxprice">
                @if(Auth::check())
                  <div class="input-group input-group-sm" style="width: 100%; margin-left: 8px;">
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
                  <div  id="lblhrg-{{$product->id}}" class="clearfix price">
                    @if(substr($product->itemcode,0,2)=="43")
											@php ($currency="$")
										@else
											@php ($currency="Rp.")
										@endif
										@if(Auth::user()->hasRole('Distributor'))
											@php ($disc = $product->getPrice(Auth()->user()->id,$product->satuan_secondary))
											@php ($uom = $product->satuan_secondary)
										@else
											@php ($disc = $product->getPrice(Auth()->user()->id,$product->satuan_primary))
											@php ($uom = $product->satuan_primary)
										@endif
										{{$currency." ".number_format($disc,2)."/".$uom}}
                  </div>
                  <div class="price coret" id="hrgcoret-{{$product->id}}">
                    @php ($price = $product->getRealPrice(Auth()->user()->id,$uom))

										@if($price!=$disc)
											{{$currency." ".number_format($price,2)}}/{{$uom}}

										@endif
                  </div>
                @endif
                </div>
                  @if(Auth::check())
                  <input type="hidden" id="hrg-{{$product->id}}" value="{{$price}}">
                  <input type="hidden" id="disc-{{$product->id}}" value="{{$disc}}">
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
  										@php ($vrate = $satuan->rate)
  										1{{$product->satuan_secondary." = ".(float)$product->getConversion($product->satuan_secondary)."/".$product->satuan_primary}}<br>
  									@endif
          				</div>
                  <div class="info-product" id="info-product2-{{$product->id}}" style="color:blue;font-weight:bold;text-align:center">
                    @if($product->getPromo())
                      @if($product->getPromo()->product_attr_value==$product->getPromo()->item_id)
                      {{"Buy ".$product->getPromo()->pricing_attr_value_from."+".$product->getPromo()->benefit_qty." ".$product->getPromo()->benefit_uom_code}}
                      @endif
                    @endif
                  </div>
                  @endif
              </div>
            </div>
            @endif
            @endforeach

          </div>
        </div>
      </div>

    </div>
    @endforeach
@endsection
@section('js')
<script src="{{asset('assets/vendors/jquery.min.js')}}"></script>
<script src="{{asset('assets/owlcarousel/owl.carousel.js')}}"></script>
<script src="{{ asset('js/myproduct.js') }}"></script>
    <script>
      var owl = $('.owl-carousel');
      owl.owlCarousel({
        margin: 10,
        loop: false,
        nav: true,
        dots:false,
        responsive: {
          0: {
            items: 1
          },
          320:{
            items:2
          },
          480: {
            items: 3
          },
          720: {
            items: 4
          },
          1000: {
            items: 5
          }
        }
      })
    </script>
@endsection
