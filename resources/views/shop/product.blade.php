	@extends('layouts.navbar_product')

	@section('content')
		<link rel="stylesheet" href="{{ URL::to('css/bootstrap_ms.css') }}">
		<link rel="stylesheet" href="{{ URL::to('css/displayproduct.css') }}">

		<div class="container">
			@include('shop.carausel')
		</div>

	@foreach($product_flexfields as $flexfield)
		<div class="container container-product">
			<!--JUDUL KATEGORI-->
			<div class="row" style="margin-left:15px; margin-right:15px;">
				<br>
				<h4><strong>{{$flexfield->description}}</strong></h4>
				@if($flexfield->products->count()>10)
				<p class="font-product"><a href="{{route('product.category',$flexfield->flex_value)}}">Lihat Semua &nbsp;</a><i class="fa fa-angle-right" aria-hidden="true"></i></p>
				@endif
				<legend></legend>
			</div>

			<div id="carousel-container">
				<div class="profile-rotater">
					@foreach($flexfield->products->take(10) as $product)
						@php ($price=0)
						@php ($disc=0)
						<div class="col-md-2 col-ms-6 column productbox">
							<!--GAMBAR PRODUK-->
							@if($product->imagePath)
								<div class="productbox1">
									<img data-src="holder.js/100%x180" alt="No Image"  class="img product" style="height:80px;" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
								</div>
							@else
							<div class="productbox1">
								<img data-src="holder.js/100%x180" alt="No Image" class="img product" style="height:80px;" src="" data-holder-rendered="true">
							</div>
							@endif

							<!--TITLE-->
							<div class="productboxTitle">
								<div class="producttitle"><a href="{{ route('product.detail',['id'=>$product->id])}}">{{$product->title}}</a></div>
							</div>

							<!--SATUAN-->
							<div class="productboxSatuan">
								@if (Auth::check())
									<div class="input-group   input-group-sm" style="width:110%">
										<input type="text" class="form-control input-sm" name="qty" id="qty-{{$product->id}}" value=1 style="text-align:right;width:40%"  onkeypress="return isNumberKey(event)" >
										<select name="satuan" class="form-control input-sm" style="width:50%;" id="satuan-{{$product->id}}" onChange="getPrice('{{$product->id}}')">
											@foreach($product->uom as $satuan)
												@php ($vrate =1)
												@if(Auth::user()->hasRole('Distributor'))
													<option value="{{$satuan->uom_code}}" {{$product->satuan_secondary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
													@else
													<option value="{{$satuan->uom_code}}" {{$product->satuan_primary==$satuan->uom_code?'selected':''}}>{{$satuan->uom_code}}</option>
												@endif
											@endforeach
										</select>
									</div>
								@endif
							</div>

							<!--PRICE-->
							<div class="productboxPrice">
								<div class="productprice">
									<div class="pull-right"></div>
									<div class="pricetext" id="lblhrg-{{$product->id}}">
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
									<input type="hidden" id="hrg-{{$product->id}}" value="{{$price}}">
									<input type="hidden" id="disc-{{$product->id}}" value="{{$disc}}">
								</div>
							</div>

							<!--ADD TO CART-->
							<div class="productboxCart">
								<div class ="clearfix" id="addCart-{{$product->id}}">
									<!--<a href="{{ route('product.addToCart',['id'=>$product->id])}}" class="btn btn-sm btn-success center-block" role="button">Add to cart</a>-->
									<a onclick="addCart('{{$product->id}}');return false;" href="#"  class="btn btn-sm btn-success center-block" role="button"  id="addCart2-{{$product->id}}">@lang('shop.AddToCart')</a>									
								</div>

							</div>

							<!--DIV CLASS DISTRIBUTOR-->
							<div class="productboxDist">
								<div class="info-product" id="info-product-{{$product->id}}">
									@if(Auth::user()->hasRole('Distributor'))
										@php ($vrate = $satuan->rate)
										1{{$product->satuan_secondary." = ".(float)$vrate."/".$product->satuan_primary}}<br>
									@endif
								</div>
								<div class="info-product" id="info-product2-{{$product->id}}" style="color:blue;font-weight:bold;text-align:center">
			            @if($product->getpromo)
				            @if($product->getpromo->product_attr_value==$product->getpromo->item_id)
				            {{"Buy ".$product->getpromo->pricing_attr_value_from."+".$product->getpromo->benefit_qty." ".$product->getpromo->benefit_uom_code}}
				            @endif
			            @endif
			          </div>
							</div>
						</div>
							<!--</div>-->
						<!--</div>-->
					@endforeach
				</div>
			</div>
		</div>
	@endforeach
	@endsection
	@section('js')

	<script src="{{ asset('js/myproduct.js') }}"></script>
	<script src="{{ asset('assets/vendors/highlight.js') }}"></script>
	@endsection
