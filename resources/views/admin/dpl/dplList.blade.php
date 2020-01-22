<!--
/**
* created by WK Productions
*/
-->
@extends('layouts.navbar_product')
@section('content')
  <link href="{{ asset('font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
  <link href="{{ asset('css/table.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dpl.css') }}" rel="stylesheet">
  <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
  <link href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
  @if($status= Session::get('msg'))
    <div class="alert alert-info">
        {{$status}}
    </div>
  @endif

  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <div class="panel panel-default">
          <div class="panel-heading"><strong>@lang('dpl.suggestNoList')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="row">
              {!! Form::open(['url' => route('dpl.list'), 'class'=>'form-horizontal']) !!}
              <div class="col-md-2">
                <div class="form-label">
                  <label for="outlet">Period</label>
                </div>
              </div>
              <div class="col-md-2">
                {{ Form::text('period', $period,array('id'=>'period_dpl','class'=>'form-control input-sm','style'=>'text-align:right')) }}
              </div>
              <div class="col-md-1">
                {{ Form::submit('Search', array('class'=>'btn btn-sm btn-primary','id'=>'search-btn-dpl')) }}
              </div>
              {{ Form::close() }}
            </div>
            <div class="table-responsive">
              <table id="dpl-list" class="display responsive nowrap" width="100%">
                <thead>
                  <tr>
                    <th>@lang('dpl.suggestNo')</th>
                    <th></th>
                    <th>@lang('dpl.trxNo')</th>
                    <th>@lang('dpl.lastApprover')</th>
                    <th>@lang('dpl.dplNo')</th>
                    <th>SPV</th>
                    <th>Outlet</th>
                    <th>Distributor</th>
                    <th>@lang('dpl.poStatus')</th>
                    <th>@lang('label.action')</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($dpl as $list)
                  <tr>
                    <td class="text-center">
                      @if($list->notrx)
                        <a href="{{ route('dpl.discountView',$list->suggest_no) }}">{{ $list->suggest_no }}</a>
                      @else
                        {{ $list->suggest_no }}
                      @endif
                    </td>
                    <td class="text-center" width="10">
                      @if($list->notrx)
                        <a href="{{ route('dpl.dplHistory',$list->suggest_no) }}"><i class="fa fa-history" aria-hidden="true"></i></a>
                      @endif
                    </td>
                    <td>
                      {{ $list->notrx }}
                    </td>
                    <td>{{ $list->dpl_appr_role }}</td>
                    <td class="text-center">
                      @if($list->dpl_no)
                        {{ 'G'.$list->dpl_no }}
                      @endif
                    </td>
                    <td>{{ isset($list->dpl_mr_code)?$list->dpl_mr_code:$list->dpl_mr_name }}</td>
                    <td width="200">{{ $list->dpl_outlet_name }}</td>
                    <td width="200">{{ $list->dpl_distributor_name }}</td>
                    <td>
                      @if($list->status_po)
                      {!! $list->status_po !!}
                      @endif
                    </td>
                    <td class="text-center">
                      {!! $list->btn_discount !!}
                      {!! $list->btn_confirm !!}
                      {!! $list->btn_dpl_no !!}
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
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
<link rel="stylesheet" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="//cdn.datatables.net/responsive/2.2.0/css/responsive.dataTables.min.css">
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
<script src="{{ asset('js/dpl.js') }}"></script>
<script type="text/javascript">
$('#period_dpl').datetimepicker({
    format: "MMM YYYY",
    locale: "en",
});
</script>
@endsection
