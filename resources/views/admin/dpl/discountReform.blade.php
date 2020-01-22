<!--
/**
* created by Solinda
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
  @if($status= Session::get('error'))
    <div class="alert alert-warning">
        {{$status}}
    </div>
  @endif

  {!! Form::open(['url' => route('dpl.discountSplit'), 'id'=>'generate-sugg-no-form','files'=>true]) !!}
  {{ Form::hidden('suggest_no',$dpl['suggest_no'],array('id'=>'suggest_no')) }}
  {{ Form::hidden('notrx',$dpl['notrx'],array('id'=>'suggest_no')) }}
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
                        {{ Form::hidden('outlet_id',$dpl['dpl_outlet_id'],array('id'=>'outlet')) }}
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
                        {{ Form::select('distributor', $distributor_list, $header->distributor_id, array('class'=>'form-control', 'id'=>'distributor')) }}
                        <span class="default-value">{{-- $dpl['dpl_distributor_name'] --}}</span>
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
                @if($dpl->log_type == 'Reject' or substr($dpl->log_type,0,10)=='PO ditolak')
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
                @endif
                @if($header->dpl_no)
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
                            {{ 'G'.$header->dpl_no }}
                        </span>
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
                          <label for="distributor">@lang('dpl.attachSP')</label>
                        </div>
                      </div>
                      <div class="col-md-10">
                        <span class="default-value">
                          @if(Auth::user()->hasRole('SPV') or Auth::user()->hasRole('ASM'))
                            {{Form::file('filesp',array('class'=>'form-control'))}}
                          @endif
                          @if(isset($dpl->file_sp))
                            <a href="{{url('/download/'.$dpl->file_sp)}}" title="Download SP">File SP<i class="glyphicon glyphicon-download-alt"></i></a>
                          @endif
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <table id="cart" class="table table-hover table-condensed">
                      <thead>
                      <tr>
                        <th style="width:45%" class="text-center" rowspan="2">@lang('shop.Product')</th>
                        <th style="width:10%" class="text-center" rowspan="2">@lang('shop.Price')</th>
                        <th style="width:5%" class="text-center" rowspan="2">@lang('shop.uom')</th>
                        <th style="width:5%" class="text-center" rowspan="2">@lang('shop.qtyorder')</th>
                        <th style="width:5%" class="text-center" rowspan="2">@lang('shop.qtyavailable')</th>
                        <!--<th style="width:15%" class="text-center" rowspan="2">@lang('shop.SubTotal')</th>-->
                        <th style="width:10%" class="text-center" rowspan="2">@lang('dpl.discount')<br/>Distributor</th>
                        <th class="text-center" colspan="2">GPL</th>
                        @if($lines->count()>1)
                        <th rowspan="2">@lang('shop.select')</th>
                        @endif
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
                        <td>
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
                            {{ (int)$line->qty_request_primary }}
                        </td>
                        <td data-th="@lang('shop.qtyavailable')" class="text-center xs-only-text-left">
                            {{ (int)$line->qty_confirm_primary }}
                        </td>
                      <!--  <td data-th="@lang('shop.SubTotal')" class="xs-only-text-left text-right">
                            {{  number_format($line->amount,2) }}
                        </td>-->
                        <td data-th="Discount Distributor" class="xs-only-text-left text-center">
                          <div class="input-prepend input-group">
                            <input type="number" step="any" min="0" max="100" name="discount[{{$id}}]" id="discount-{{$id}}" class="form-control text-center" value="{{ $line->discount }}" style="min-width:80px;">
                            <span class="add-on input-group-addon">%</span>
                          </div>
                        </td>
                        <td data-th="Discount" class="xs-only-text-left text-center">
                          <div class="input-prepend input-group">
                            <input type="number" step="any"  min="0" max="100"  name="discount_gpl[{{$id}}]" id="discount-gpl-{{$id}}" class="form-control text-center" value="{{ $line->discount_gpl }}" style="min-width:80px;">
                            <span class="add-on input-group-addon">%</span>
                          </div>
                        </td>
                        <td data-th="Bonus" class="xs-only-text-left text-center">
                          <input type="number" name="bonus_gpl[{{$id}}]" id="bonus-gpl-{{$id}}" class="form-control text-center" value="{{ $line->bonus_gpl }}" style="min-width:80px;">
                        </td>
                        @if($lines->count()>1)
                        <td data-th="@lang('shop.select')">{{ Form::checkbox('lineid[]', $id, true) }}</td>
                        @endif
                      </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                    </tfoot>
                  </table>
                </div>
                <div class="form-group">
                  <div class="container-fluid">
                    <div class="row">
                      <div class="col-md-2">
                        &nbsp;
                      </div>
                      <div class="col-md-10">
                        <input type="submit" class="btn btn-primary" value="@lang('dpl.save')" name="Save">
                        @if($lines->count()>1)
                        <input type="submit" class="btn btn-warning" value="Split" name="Split">
                        @endif
                        <a href="{{ route('dpl.list') }}" class="btn btn-default">@lang('dpl.back')</a>
                        <a href="#" id="btn-dpl-cancel" class="btn btn-danger pull-right">@lang('dpl.rejectSuggestNo')</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  {{ Form::close() }}

@endsection
@section('js')

<script src="{{ asset('js/dpl.js') }}"></script>

@endsection
