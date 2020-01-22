@extends('layouts.navbar_product')
@section('css')
<link rel="stylesheet" href="{{ URL::to('css/login.css') }}">
@endsection
@section('content')
	<div class="container">
    <div class="card card-container">
      <h1 align="center">Opps...Sorry, we're down for maintenance</h1>
      <div class="imgcontainer">
        <img src="{{ URL::to('img/503.jpg') }}" alt="Avatar" class="avatar">
      </div>
      @if(!empty($exception->getMessage()))
      <p><strong>{{ $exception->getMessage() }}
        @if(!empty($exception->retryAfter))
        please try again in {{$exception->retryAfter}} second
        @endif
      </strong></p>
      @endif
    </div>
  </div>
@endsection
