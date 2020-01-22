@extends('layouts.tempAdminSB')
@section('content')
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<h3>Product</h3>
  <form class="form-horizontal" action="{{route('product.update',$product->id)}}" enctype="multipart/form-data" method="post" role="form" >
       {{csrf_field()}}

     <div class="form-group">
       <label for="name">Kode</label>
      <label class="form-control" name="kode">{{ $product->itemcode }}</label>
     </div>
     <div class="form-group">
       <label for="display_name">Nama</label>
       <input type="text" class="form-control" name="nama" value="{{$product->title}}">
       @if ($errors->has('nama'))
           <span class="help-block">
               <strong>{{ $errors->first('nama') }}</strong>
           </span>
       @endif
     </div>

     <div class="form-group">
       <label for="description">Category</label>
       <select name="category" class="form-control">
         <option value="">--Pilih Salah Satu --</option>
         @foreach($categories as $cat)
         <option value="{{$cat->flex_value}}" {{$cat->flex_value==$product->flex_value?'selected':''}}>{{$cat->description}}</option>
         @endforeach
       </select>
     </div>
     <div class="form-group">
       <label for="description">Satuan</label>
       <label class="form-control" name="satuan" size="5" >{{$product->satuan_primary}}</label>
     </div>
     <div class="form-group">
       <label for="description">Description (English)</label>
       <textarea class="form-control" id="en_descr" name="en_descr">{{old('en_descr')?old('en_descr'):$product->description_en}}</textarea>
     </div>
     <div class="form-group">
       <label for="messageArea">Description (Indonesia)</label>
       <textarea class="form-control" id="id_descr" name="id_descr">{{old('id_descr')?old('id_descr'):$product->description}}</textarea>
     </div>
     <div class="form-group">
       <label for="messageArea">Nama Generik</label>
       <textarea class="form-control" id="generik" name="generik">{{old('generik')?old('generik'):$product->long_description}}</textarea>
     </div>
     <div class="form-group">
         <label for="imageInput">File Image</label>
         <input data-preview="#preview" name="input_img" type="file" id="imageInput">
         @if ($errors->has('input_img'))
             <span class="help-block">
                 <strong>{{ $errors->first('input_img') }}</strong>
             </span>
         @endif
    </div>
     <div class="thumbnail">
           <img id="profile-img-tag" data-src="holder.js/100%x180" alt="100%x180" style="height: 180px; width: 180px; display: block;" src="{{ asset('img/'.$product->imagePath) }}" data-holder-rendered="true">
     </div>
     <div class="form-group">
       <label for="description">Tipe Dot</label>
       <select name="dot_obat" class="form-control">         
         @foreach($tipedot as $key=>$tipe)
         <option value="{{$key}}" {{$product->tipe_dot==$key?'selected':''}}>{{$tipe}}</option>
         @endforeach
       </select>
     </div>
     <div class="form-group">
       <label for="pareto" class="control-label col-sm-2" style="text-align:left!important;padding-left:0px;">Pareto Product</label>
       <div class="col-sm-10">
         <input type="radio" name="pareto" value="1" {{$product->pareto=="1"?'checked=checked':''}}>Yes&nbsp;&nbsp;
         <input type="radio" name="pareto" value="0" {{$product->pareto=="0"?'checked=checked':''}}>No
         @if ($errors->has('pareto'))
             <span class="help-block">
                 <strong>{{ $errors->first('pareto') }}</strong>
             </span>
         @endif
       </div>

     </div>
     <div class="form-group">
       <label for="Publisher" class="control-label col-sm-2" style="text-align:left!important;padding-left:0px;">Publish Product</label>
       <div class="col-sm-10">
         <input type="radio" name="enabledflag" value="Y" {{$product->enabled_flag=="Y"?'checked=checked':''}}>Yes&nbsp;&nbsp;
         <input type="radio" name="enabledflag" value="N" {{$product->enabled_flag=="N"?'checked=checked':''}}>No
         @if ($errors->has('pareto'))
             <span class="help-block">
                 <strong>{{ $errors->first('pareto') }}</strong>
             </span>
         @endif
       </div>
     </div>
      <div class="form-group">
     <input type="hidden" name="itemid" value="">
     <button type="submit" class="btn btn-primary">Save Product</button>
     <a href="{{route('product.show')}}"><button type="button" class="btn btn-warning">Master Product</button></a>
   </div>
   </form>
@endsection
@section('js')
   <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
   <script src="{{ asset('vendor/unisharp/laravel-ckeditor/ckeditor.js') }}"></script>
	<script>
	    CKEDITOR.replace( 'id_descr' );
      CKEDITOR.replace( 'en_descr' );
      function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
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
