@extends('layouts.tempAdminSB')
@section('content')
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<h3>Banner</h3>
<div class="container">
  <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
    <div class="carousel-inner">
      @php ($i=0)
      @foreach($banners as $banner)
      @php($i+=1)
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
  </div>
</div>
<div class="container">
  <table class="table">
    <thead>
      <tr>
        <th>Image</th>
        <th>Teks</th>
        <th style="align:center">Publish</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      @foreach($banners as $b)
      <tr>
        <td>{{$b->image_path}}</td>
        <td>{{$b->teks}}</td>
        <td>
          @if($b->publish_flag=='Y')
            <i class="fa fa-check fa-lg"></i>
          @else
            <i class="fa fa-close fa-lg"></i>
          @endif
        </td>
        <td>
          <div class="btn-group">
            <a class="btn btn-primary" href="#">Action</a>
            <a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
              <span class="fa fa-caret-down" title="Toggle dropdown menu"></span>
            </a>
            <ul class="dropdown-menu">
              <li><a href="{{route('banner.edit',$banner->id)}}"><i class="fa fa-pencil fa-fw"></i> Edit</a></li>
              <li><a id="{{$banner->id}}" class="delete-banner" href="#" ><i class="fa fa-trash-o fa-fw"></i> Delete</a></li>

              @if($b->publish_flag=='Y')
                <li><a href="{{route('banner.publish',['N',$b->id])}}"><i class="fa fa-ban fa-fw"></i> Unpublish</a></li>
              @else
                <li><a href="{{route('banner.publish',['Y',$b->id])}}"><i class="fa fa-check fa-fw"></i> Publish</a></li>
              @endif
            </ul>
          </div>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>
<legend></legend>
<a href="{{route('banner.create')}}"><button type="button" class="btn btn-success"><i class="fa fa-plus fa-fw"></i>Tambah</button></a>

@endsection
@section('js')
<script>
$('.carousel').carousel({
	interval: 2500
})
jQuery(document).ready(function($){
  $('.delete-banner').on('click',function(e){
    var token = window.Laravel.csrfToken;
    var id = this.id;
    e.preventDefault()
    swal({
        title: "Delete Banner?",
        text: "Anda yakin akan menghapus banner ="+id+"?",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Delete",
        cancelButtonText: "Cancel",
        closeOnConfirm: false,
        closeOnCancel: true
      },
      function(isConfirm) {
        if (isConfirm) {
          //swal("Deleted!", "Your imaginary file has been deleted.", "success");
          $.ajax({
                url: "{{ url('/banner/destroy') }}" + "/"+id ,
                type: 'DELETE',
                dataType : "JSON",
                data: {_method: 'delete', _token :token},
                success: function(data) {
                  swal ('Deleted!', 'Banner berhasil dihapus.');
                  window.location.href="{{url('admin/banner')}}";
                }
          });

        }
      });

  });
});
</script>
@endsection
