<!--
/**
* created by WK Productions
*/
-->
@extends('layouts.navbar_product')
@section('content')
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
          <div class="panel-heading"><strong>@lang('dpl.discountForm')</strong></div>
          <div class="panel-body" style="overflow-x:auto;">
            <div class="panel panel-default">
              <div class="form-wrapper">
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">SPV</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::hidden('mr',$dpl['dpl_mr_name'],array('id'=>'mr')) }}
                        <span class="default-value">{{ $dpl['dpl_mr_name'] }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="outlet">Outlet</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        {{ Form::hidden('outlet',$dpl['dpl_outlet_name'],array('id'=>'outlet')) }}
                        <span class="default-value">{{ $dpl['dpl_outlet_name'] }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">Distributor</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $header->distributor_name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">@lang('dpl.suggestNo')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $dpl->suggest_no }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                @if($dpl->log_type == 'Reject')
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">@lang('dpl.rejectedBy')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $dpl->reject_by }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">@lang('dpl.reason')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{!! $dpl->reason !!}</span>
                      </div>
                    </div>
                  </div>
                </div>
                @else
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">@lang('dpl.approvedBy')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">{{ $dpl->approver_name }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">@lang('dpl.dplNo')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">
                          @if($header->dpl_no)
                          {{ 'G'.$header->dpl_no }}
                          @endif
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                @if(isset($dpl->file_sp))
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        <div class="form-label">
                          <label for="distributor">@lang('dpl.attachSP')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">
                          <a href="{{url('/download/'.$dpl->file_sp)}}" title="Download SP">File SP<i class="glyphicon glyphicon-download-alt"></i></a>
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                <div class="form-group">
                  <table id="cart" class="table table-hover table-condensed">
                      <thead>
                      <tr>
                        <th style="width:45%" class="text-center" rowspan="2">@lang('shop.Product')</th>
                        <th style="width:10%" class="text-center" rowspan="2">@lang('shop.Price')</th>
                        <th style="width:5%" class="text-center" rowspan="2">@lang('shop.uom')</th>
                        <th style="width:5%" class="text-center" rowspan="2">@lang('shop.qtyorder')</th>
                        <th style="width:15%" class="text-center" rowspan="2">@lang('shop.SubTotal')</th>
                        <th style="width:10%" class="text-center" rowspan="2">@lang('dpl.discount')<br/>Distributor</th>
                        <th class="text-center" colspan="2">GPL</th>
                      </tr>
                      <tr>
                        <th style="width:10%" class="text-center">@lang('dpl.discount')</th>
                        <th style="width:10%" class="text-center">@lang('dpl.bonus')</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php ($totamount = 0)
                      @foreach($lines as $line)
                        @php ($id  = $line->line_id)
                      <tr>
                        <td >
                          <div class="row">
                            <div class="col-sm-2 hidden-xs"><img src="{{ asset('img//'.$line->imagePath) }}" alt="..." class="img-responsive"/></div>
                            <div class="col-sm-10">
                              <h4 >{{ $line->title }}</h4>
                            </div>
                          </div>
                        </td>
                        <td data-th="@lang('shop.Price')" class="xs-only-text-left text-center" >{{ number_format($line->list_price/$line->conversion_qty,2) }}</td>
                        <td data-th="@lang('shop.uom')" class="xs-only-text-left text-center" >{{ $line->uom_primary }}</td>
                        <td data-th="@lang('shop.qtyorder')" class="text-center xs-only-text-left">
                            {{ $line->qty_request_primary }}
                        </td>
                        <td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                            {{  number_format($line->amount,2) }}
                        </td>
                        <td data-th="Discount Distributor" class="xs-only-text-left text-center">
                          {{ ($line->discount) ? $line->discount : 0 }} %
                        </td>
                        <td data-th="Discount" class="xs-only-text-left text-center">
                          {{ ($line->discount_gpl) ? $line->discount_gpl : 0 }} %
                        </td>
                        <td data-th="Bonus" class="xs-only-text-left text-center">
                          {{ ($line->bonus_gpl) ? $line->bonus_gpl : 0 }}
                        </td>
                      </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                    </tfoot>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
