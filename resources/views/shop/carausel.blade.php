<div id="carousel-example-generic" class="carousel slide" data-ride="carousel">

  <!-- Wrapper for slides -->
  <div class="carousel-inner">
    @php ($i=0)
    @foreach($banners as $banner)
    @php ($i+=1)
    <div class="item {{$i==1?'active':''}}">
      <img src="{{asset($banner->image_path)}}" alt="Solusi Kemudahan Perawatan Kesehatan Anda" class="box">
      <div class="carousel-caption">
      	<h3>{{$banner->teks}}</h3>
      </div>
    </div>
    @endforeach


  <!-- Controls -->
	  <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev">
		  <span class="fa fa-angle-left" aria-hidden="true"></span>
	  </a>
	  <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next">
		  <span class="fa fa-angle-right" aria-hidden="true"></span>
	  </a>
  </div><!-- Carousel -->
</div>  
