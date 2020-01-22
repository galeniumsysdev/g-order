@extends('layouts.tempAdminSB')
@section('content')
<h3>Tambah Banner</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
  <form class="form" action="{{route('banner.update',$banner->id)}}" enctype="multipart/form-data" method="post" role="form" >
    {{method_field('PATCH')}}
       {{csrf_field()}}
     <div class="form-group">
       <label for="teks">Caption Teks</label>
       <input type="text" class="form-control" name="caption" value="{{$banner->teks}}" placeholder"Teks yang mau ditampilkan.." required>
       @if ($errors->has('caption'))
           <span class="help-block">
               <strong>{{ $errors->first('caption') }}</strong>
           </span>
       @endif
     </div>
     <div class="form-group">
         <label for="imageInput">File Gambar</label>
         <input data-preview="#preview" name="input_img" type="file" id="imageInput">
         @if ($errors->has('input_img'))
             <span class="help-block">
                 <strong>{{ $errors->first('input_img') }}</strong>
             </span>
         @endif
    </div>
     <div class="thumbnail">
     <img id="profile-img-tag" data-src="holder.js/100%x180"  style="max-height:300px; width: 100%; display: block;" src="{{asset($banner->image_path)}}" data-holder-rendered="true">
     </div>
     <div class="form-group">
         <label for="publish">Publish Banner</label>&nbsp;
         <input type="checkbox" name="publish" value="Y" {{$banner->publish_flag=="Y"?'checked=checked':''}}> Ya
    </div>

      <div class="form-group">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
   </form>
@endsection
@section('js')
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
	<script>
	     function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
              $(".thumbnail").show();
                $('#profile-img-tag').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
      }
    $("#imageInput").change(function(){
        readURL(this);
    });
	</script>
@endsection
