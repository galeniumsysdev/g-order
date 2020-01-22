@extends('layouts.tempAdminSB')
@section('content')
<div class="row" >
    <div id="pesan">
      @if($status= Session::get('success'))
			<div class="alert alert-info">
				{{$status}}
			</div>
			@endif
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">Mapping Outlet {{$dists->count()==1?':'.$dists->first()->customer_name:''}}</div>
        <div class="panel-body">
          <div class="tabcard">
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#mapping" aria-controls="mapping" role="tab" data-toggle="tab">Mapping</a></li>
                <li role="presentation"><a href="#del-mapping" aria-controls="del-mapping" role="tab" data-toggle="tab">Delete Mapping</a></li>
            </ul>
            <form action="{{route('customer.remappingOutlet',$id)}}" class="form-horizontal" method="post" id="frm-mapping" role="form">
              {{method_field('PATCH')}}
                {{csrf_field()}}
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="mapping">
                  <br>
                  <div class="table-responsive">
                    <table id="tblmapping" class="table table-striped">
                      <thead>
                        <tr>
                          <th>Distributor Name</th>
                          <th>Outlet Name</th>
                          <th>Province</th>
                          <th>City</th>
                          <th>PSC/Pharma</th>
                          <th>Category</th>
                          <th>Mapped</th>
                        </tr>
                      </thead>
                          @foreach($dists as $dist)
                          @php($name = $dist->customer_name)
                          @php($dist_id = $dist->id)
                          <tr>
                          <td>{{$name}}</td>
                            @php($i=0)
                            @if($dist->mapping->count()==0)
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @endif
                            @foreach($dist->mapping as $map)
                              @php($i+=1)
                              @if($map->ada=="T")
                                <input type="hidden" name="insertarray[{{$dist_id}}][]" value="{{$map->id}}">
                              @endif
                              <td>{{$map->customer_name.'('.$map->id.')'}}</td>
                              <td>{{$map->sites->where('site_use_code','SHIP_TO')->pluck('province')->unique()}}</td>
                              <td>{{$map->sites->where('site_use_code','SHIP_TO')->pluck('city')->unique()}}</td>
                              <td>
                                @if($map->psc_flag=="1" and $map->pharma_flag=="1")
                                  Pharma, PSC
                                @elseif($map->psc_flag=="1")
                                  PSC
                                @elseif($map->pharma_flag=="1")
                                  Pharma
                                @else

                                @endif
                              </td>
                              <td>{{$map->cat_name}}</td>
                              <td>{{$map->ada}}</td>
                              </tr>
                              @if($dist->mapping->count()!=$i)
                              <tr><td>{{$name}}</td>
                              @endif

                            @endforeach
                          </tr>
                          @endforeach
                      <tbody>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div role="tabpanel" class="tab-pane" id="del-mapping">
                  <br>
                  <h6>Data ini saat ini sudah termapping, dan akan dihapus karena tidak sesuai dengan master mapping!</h6>
                  <div class="table-responsive">
                    <table id="tbl-del-mapping" class="table table-striped">
                      <thead>
                        <tr>
                          <th>Distributor Name</th>
                          <th>Outlet Name</th>
                          <th>Province</th>
                          <th>City</th>
                          <th>PSC/Pharma</th>
                          <th>Category</th>
                        </tr>
                      </thead>
                      <tbody>
                        @foreach($dists as $deldist)
                          @if($deldist->delete_mapping->count()>0)
                          @php($delname = $deldist->customer_name)
                          @php($delid = $deldist->id)
                            <tr>
                              <td>{{$delname}}</td>
                              @php($i=0)
                              @foreach($deldist->delete_mapping as $delete)
                              @php($i+=1)
                              <input type='hidden' name="delarray[{{$delid}}][]" value="{{$delete->id}}">
                                <td>{{$delete->customer_name.'('.$delete->id.')'}}</td>
                                <td>{{$delete->sites->where('site_use_code','SHIP_TO')->pluck('province')->unique()}}</td>
                                <td>{{$delete->sites->where('site_use_code','SHIP_TO')->pluck('city')->unique()}}</td>
                                <td>
                                @if($delete->psc_flag=="1" and $delete->pharma_flag=="1")
                                  Pharma, PSC
                                @elseif($delete->psc_flag=="1")
                                  PSC
                                @elseif($delete->pharma_flag=="1")
                                  Pharma
                                @else

                                @endif
                              </td>
                                @if($delete->categoryOutlet)
					<td>{{$delete->categoryOutlet->name}}</td>
				    @else
					<td></td>
				    @endif
                              @if($deldist->delete_mapping->count()!=$i)
                              <tr><td>{{$delname}}</td>
                              @endif
                              @endforeach
                          @endif
                        @endforeach
                      </tbody>
                    </table>
                  </div>
                </div>
            </div>
            <br>
            <button type="submit" class="btn btn-sm btn-success"  class="add-mapping" data-toggle="modal" data-target="#addMapping"> ReMapping Outlet-Distributor</button>
          </form>
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
<script type="text/javascript">
if($('#tblmapping').length){
  $('#tblmapping').DataTable();
    window.setTimeout(function(){
        $(window).resize();
    },2000);
  }
if($("#tbl-del-mapping").length){
  $('#tbl-del-mapping').DataTable();
    window.setTimeout(function(){
        $(window).resize();
    },2000);
  }
$(document).ready(function (){
  $('#frm-mapping').on('submit', function(e){
        var form = this;
         var table = $('#tblmapping').DataTable();
         var table2 = $('#tbl-del-mapping').DataTable();

        // Encode a set of form elements from all pages as an array of names and values
        var params = table.$('input').serializeArray();
        var params2 = table2.$('input').serializeArray();
        //console.log(params+params2);
        // Iterate over all form elements
        $.each(params, function(){
           // If element doesn't exist in DOM
           if(!$.contains(document, form[this.name])){
              // Create a hidden element
              $(form).append(
                 $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', this.name)
                    .val(this.value)
              );
           }
        });
        $.each(params2, function(){
           // If element doesn't exist in DOM
           if(!$.contains(document, form[this.name])){
              // Create a hidden element
              $(form).append(
                 $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', this.name)
                    .val(this.value)
              );
           }
        });
     });
   });
</script>

@endsection
