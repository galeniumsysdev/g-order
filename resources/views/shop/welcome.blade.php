@extends('layouts.navbar_product')

@section('content')
<link rel="stylesheet" href="{{ asset('css/OwlCarousel2-2.2.1/docs/assets/css/docs.theme.min.css') }}">

<!-- Owl Stylesheets -->
<link rel="stylesheet" href="{{ asset('css/OwlCarousel2-2.2.1/dist/assets/owl.carousel.min.css') }}">
<link rel="stylesheet" href="{{ asset('css/OwlCarousel2-2.2.1/dist/assets/owl.theme.default.min.css') }}">
<div class="container">
  @include('shop.carausel')
</div>

<div class="row">
	<div class="large-12 columns">
		<br>
		<p>Produk Categori 1</p>
		<p class="view-produk">Lihat Semua</p>
		<hr>

		<!--Categori 1-->
		<div class="owl-carousel owl-theme">
      <div class="item">
              <h4>1</h4>
            </div>
            <div class="item">
              <h4>2</h4>
            </div>
            <div class="item">
              <h4>3</h4>
            </div>
            <div class="item">
              <h4>4</h4>
            </div>
            <div class="item">
              <h4>5</h4>
            </div>
            <div class="item">
              <h4>6</h4>
            </div>
            <div class="item">
              <h4>7</h4>
            </div>
            <div class="item">
              <h4>8</h4>
            </div>
            <div class="item">
              <h4>9</h4>
            </div>
            <div class="item">
              <h4>10</h4>
            </div>
            <div class="item">
              <h4>11</h4>
            </div>
            <div class="item">
              <h4>12</h4>
            </div>
		</div>
	</div>
</div>

@endsection
@section('js')
<script>
$(document).ready(function() {
$('.owl-carousel').owlCarousel({
loop: true,
margin: 10,
responsiveClass: true,
responsive: {
0: {
items: 2,
nav: true,
loop: false,
},
600: {
items: 3,
nav: false,
loop: false,
},
1000: {
items: 5,
nav: true,
loop: false,
margin: 20
}
}
})
})
</script>
	<!-- vendors -->
	<script src="{{ asset('assets/vendors/highlight.js') }}"></script>
	<script src="{{ asset('assets/js/app.js') }}"></script>
	<script src="{{ asset('assets/vendors/jquery.min.js') }}"></script>
	<script src="{{ asset('assets/owlcarousel/owl.carousel.js') }}"></script>
@endsection
