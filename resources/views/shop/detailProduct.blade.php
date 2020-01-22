@extends('layouts.app')
@section('content')
<div class="container" id="product-section">
  <div class="row">
   <div class="col-md-6">
		 <img src="img/Caladine_Powder___Original_60_Gr.jpg" alt="Caladine_Powder___Original_60_Gr"   class="image-responsive"  />
   </div>

	 <div class="col-md-6">
		 <div class="row">
			 <div class="col-md-12">
				<h1>Kodak 'Brownie' Flash B Camera</h1>
			 </div>
		 </div>
		 <div class="row">
			 <div class="col-md-12">
			  <span class="label label-primary">Vintage</span>
			  <span class="monospaced">No. 1960140180</span>
			 </div>
			</div>

			<div class="row">
			 <div class="col-md-12 bottom-rule">
			  <h2 class="product-price">$129.00</h2>
			 </div>
			</div><!-- end row -->

			<div class="row add-to-cart">
			 <div class="col-md-5 product-qty">
			  <span class="btn btn-default btn-lg btn-qty">
			   <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
			  </span>

			  <input class="btn btn-default btn-lg btn-qty" value="1" />

			  <span class="btn btn-default btn-lg btn-qty">
			   <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
			  </span>
			 </div>

			 <div class="col-md-4">
			  <button class="btn btn-lg btn-brand btn-full-width">
			   Add to Cart
			  </button>
			 </div>
			</div><!-- end row -->

			<div class="row">
			 <div class="col-md-12 bottom-rule top-10"></div>
			</div><!-- end row -->

	 </div>

  </div><!-- end row -->
 </div><!-- end container -->
@endsection
