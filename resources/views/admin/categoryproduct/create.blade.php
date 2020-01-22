@extends('layouts.tempAdminSB')
@section('content')
<h3>Create Category Product</h3>
@if($status= Session::get('message'))
  <div class="alert alert-info">
      {{$status}}
  </div>
@endif
<form action="{{route('CategoryProduct.store')}}" method="post" role="form">
       {{csrf_field()}}
       <div class="form-group">
         <label for="name">Kode</label>
         <input type="text" class="form-control" name="code" id="" placeholder="Kode Kategori Product" value="{{ old('code') }}">
         @if ($errors->has('code'))
             <span class="help-block">
                 <strong>{{ $errors->first('code') }}</strong>
             </span>
         @endif
       </div>
     <div class="form-group">
       <label for="name">Nama Kategori Product</label>
       <input type="text" class="form-control" name="name" id="" placeholder="Nama Kategori Product" value="{{ old('name') }}">
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
         <option value="Pharma">Pharma</option>
         <option value="PSC">PSC</option>
         <option value="INTERNATIONAL">INTERNATIONAL</option>
         <option value="TollIn">Toll-In</option>
       </select>
       @if ($errors->has('parent'))
           <span class="help-block">
               <strong>{{ $errors->first('parent') }}</strong>
           </span>
       @endif
     </div>
     <div class="form-group">
       <label for="status">Status</label>
        <input type="checkbox"  name="status" value="Y"> Active<br>
     </div>
     <button type="submit" class="btn btn-primary">Submit</button>
   </form>
@endsection
