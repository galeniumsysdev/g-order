
@extends('layouts.navbar_product')

@section('content')
<link rel="stylesheet"
  href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css">
<div class="container">
  <div class="row">
    <div class="col-sm-12">
      <div class="panel panel-default">
        <div class="panel-heading"><strong>CARI OUTLET</strong></div>
          <div class="panel-body">
            <div id="frmsearch" class="panel panel-default">
              <br>
              <form action="{{route('customer.listNoo')}}" class="form-horizontal" method="post" role="form">
                {{csrf_field()}}
                <div class="form-group">
                  <label class="control-label col-sm-2" for="email"><strong>@lang('label.outlet') :</strong></label>
                    <div class="col-sm-8">
                      <input type="text" data-provide="typeahead" autocomplete="off"  class="form-control mb-8 mr-sm-8 mb-sm-4" name="name" id="name" value="{{$request->name}}" >
                    </div>
                </div>
                <div class="form-group">
                  <label class="control-label col-sm-2" for="role"><strong>Role :</strong></label>
                    <div class="col-sm-4">
                      <select class="form-control" name="role" id="role">
                        <option value="">--</option>
                        @foreach($roles as $role)
                            <option value="{{$role->id}}">{{$role->display_name}}</option>
                        @endforeach
                      </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="needproduct"><strong>Divisi :</strong></label>
                    <div class="col-sm-10">
                      <input type="checkbox" class="form-check-input" name="psc_flag" id="psc_flag" value="1" {{$request->psc_flag=="1"?"checked":""}}> PSC &nbsp;
                      <input type="checkbox" class="form-check-input" name="pharma_flag" id="pharma_flag" value="1" {{$request->pharma_flag=="1"?"checked":""}}> Pharma (Non PSC)
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="category"><strong>@lang('label.category') :</strong></label>
                    <div class="col-sm-4">
                        <select class="form-control" name="category">
                          <option value="">--</option>
                          @foreach($categories as $category)
                          <option value="{{$category->id}}" {{$request->category==$category->id?"selected":""}}>{{$category->name}}</option>
                          @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="control-label col-sm-2" for="subcategorydc"><strong>@lang('label.categorydc') :</strong></label>
                    <div class="col-sm-8">
                        <select multiple class="form-control" name="subgroupdc[]" >
                          @foreach($subgroupdc as $sub)
                          @if($request->subgroupdc)
                            <option value="{{$sub->id}}" {{in_array($sub->id,$request->subgroupdc)?"selected":""}}>{{$sub->group."-".$sub->subgroup}}</option>
                          @else
                            <option value="{{$sub->id}}">{{$sub->group."-".$sub->subgroup}}</option>
                          @endif
                          @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-2">
                        <button type="submit" id="btn-search" class="btn btn-primary">
                            Search
                        </button>
                    </div>
                </div>
              </form>
            </div>
            @if($outlets)
            <div class="row">
  						<div class="col-md-12 table-responsive">
                <table class="table" id="table">
                  <thead>
                  <tr>
                    <th width="40%">@lang('label.outlet')</th>
                    <th width="5%">@lang('label.category')</th>
                    <th width="5%">Divisi</th>
                    <th width="5%">@lang('label.categorydc')</th>
                    <th width="5%">@lang('label.action')</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($outlets as $outlet)
                    <tr>
                      <td>{{$outlet->customer_name}}</td>
                      <td>{{$outlet->category_name}}</td>
                      <td>
                        @if($outlet->psc_flag=="1" and $outlet->pharma_flag=="1")
                          PSC, Pharma
                        @elseif($outlet->psc_flag=="1")
                            PSC
                        @elseif($outlet->pharma_flag=="1")
                            Pharma
                        @endif
                        </ul>
                      </td>
                      <td>{{$outlet->subdc}}</td>
                      <td>
                        <a href="{{route('customer.show',[$outlet->users->first()->id,0])}}"><button type="button" class="btn btn-sm btn-primary" name="edit"><span class="glyphicon glyphicon-pencil"></span> Edit</button></a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
              </div>
            </div>
            @endif

          </div>
      </div>
    </div>
  </div>
</div>
@endsection
@section('js')
<script src="{{ asset('js/moment-with-locales.js') }}"></script>
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="//cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>


  <script>
    $(document).ready(function() {
      var path = "{{ route('customer.searchoutlet') }}";
      $.get(path,
          function (data) {
              $('#name').typeahead({
                  source: data,
                  items: 10,
                  showHintOnFocus: 'all',
                  displayText: function (item) {
                      return item.customer_name;
                  },
                  afterSelect: function (item) {
                    $('#name').val(item.customer_name);
                  }
              });
            }, 'json');
      $('#table').DataTable({
        "searching": false,
      }
      );
      /*$('#name').typeahead({
          source:  function (query, process) {
          return $.get(path, { query: query }, function (data) {
                  return process(data);
              });
          }

      });*/
    });
  </script>
@endsection
