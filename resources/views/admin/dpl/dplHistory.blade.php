<!--
/**
* created by WK Productions
*/
-->
@extends('layouts.navbar_product')
@section('content')
  <link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-10 col-sm-offset-1">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('dpl.history') DPL #{{ $dpl[0]->suggest_no }}</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
              <div class="table-responsive">
                <table class="display responsive nowrap" width="100%" id="dpl-history">
                  <thead>
                    <tr>
                      <th>@lang('label.action')</th>
                      <th>@lang('dpl.doneBy')</th>
                      <th>@lang('dpl.role')</th>
                      <th>@lang('dpl.date')</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($dpl as $history)
                    <tr>
                      <td>{{ $history->type }}</td>
                      <td>{{ $history->name }}</td>
                      <td>{{ $history->role }}</td>
                      <td>{{ $history->created_at }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
                <div>
                  <a href="{{ route('dpl.list') }}" class="btn btn-default">@lang('dpl.back')</a>
                </div>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('js')

<script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
<script src="//cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
<script src="//cdn.datatables.net/responsive/2.2.0/js/dataTables.responsive.min.js"></script>
<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
