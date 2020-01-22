@extends('layouts.tempAdminSB')
@section('content')
<h3>Edit Category Product</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<form class="form-horizontal" action="{{ route('CategoryProduct.update',$category->flex_value) }}" method="post" role="form">
  {{csrf_field()}}
  {{method_field('PATCH')}}
       <div class="form-group">
         <label for="name">Kode</label>
         <input type="text" class="form-control" name="code" id="kode" placeholder="Kode Kategori Product" value="{{ old('code')?old('code'):$category->flex_value }}">
         @if ($errors->has('code'))
             <span class="help-block">
                 <strong>{{ $errors->first('code') }}</strong>
             </span>
         @endif
       </div>
     <div class="form-group">
       <label for="name">Nama Kategori Product</label>
       <input type="text" class="form-control" name="name" id="name" placeholder="Nama Kategori Product" value="{{ old('name')?old('name'):$category->description }}">
       @if ($errors->has('name'))
           <span class="help-block">
               <strong>{{ $errors->first('name') }}</strong>
           </span>
       @endif
     </div>
     <div class="form-group">
       <label for="name">Jenis Kategori Product</label>
       <select name="parent" class="form-control">
         <option value="">--Pilih Salah Satu--</option>
         <option value="PHARMA" {{$category->parent=="PHARMA"?'selected':''}}>Pharma</option>
         <option value="PSC" {{$category->parent=="PSC"?'selected':''}}>PSC</option>
         <option value="INTERNATIONAL" {{$category->parent=="INTERNATIONAL"?'selected':''}}>INTERNATIONAL</option>
         <option value="TollIn" {{$category->parent=="TollIn"?'selected':''}}>Toll-In</option>
       </select>
       @if ($errors->has('parent'))
           <span class="help-block">
               <strong>{{ $errors->first('parent') }}</strong>
           </span>
       @endif
     </div>
     <div class="form-group">
       <label for="status">Status</label>
        <input type="checkbox"  name="status" value="Y" {{$category->enabled_flag=="Y"?"checked='checked'":''}}> Active<br>
     </div>
     <button type="submit" class="btn btn-primary">Submit</button>
     <a href='{{URL::previous()}}'><button type="button" class="btn btn-warning">Cancel</button></a>
   </form>
@endsection
