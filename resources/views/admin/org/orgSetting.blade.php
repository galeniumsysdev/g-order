@extends(Auth::user()->hasRole('IT Galenium')?'layouts.tempAdminSB':'layouts.navbar_product')
@section('content')
<div class="container-fluid">
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Organization Structure</h2>
        </div>
    </div>
</div>
<div class="row" >
<input type="hidden" value="{{$user_id}}" name="orgid" id="orgid">
<div class="tabcard col-sm-12">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active" ><a href="#personal" aria-controls="Personal" role="tab" data-toggle="tab"><strong>User</strong></a></li>
        <li role="presentation"><a href="#area" aria-controls="Address" role="tab" data-toggle="tab"><strong>Area</strong></a></li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="personal">
          {!! Form::open(['url' => '/Organization/'.$user_id.'/setting/save']) !!}
          <table class="table" id="table">
            <tr>
              <td>Code</td>
              <td>:</td>
              <td>{{ Form::text('user_code', $org['user_code'], array('class'=>'form-control')) }}</td>
            </tr>
            <tr>
              <td>Role</td>
              <td>:</td>
              <td>{{ Form::select('role', $role_list, $org->role_id, array('class'=>'form-control','id'=>'role')) }}</td>
            </tr>
            <tr>
              <td>Email</td>
              <td>:</td>
              <td>
                <div class="input-group sm-12">
                  {{ Form::text('email',$org->email, array('class'=>'form-control','id'=>'email-spv','autocomplete'=>'off','aria-describedby'=>"change-email")) }}
                  <span class="input-group-addon" id="change-email"><i class="fa fa-times" aria-hidden="true"></i></span>
                </div>
              {{Form::hidden('user_id',$org->userid, array('class'=>'form-control','id'=>'user-id')) }}
              @if ($errors->has('email'))
                  <span class="help-block">
                      <strong>{{ $errors->first('email') }}</strong>
                  </span>
              @endif
            </td>
            </tr>
            <tr>
              <td>Description</td>
              <td>:</td>
              <td>{{ Form::text('user_name',$org->description, array('class'=>'form-control','id'=>'user-name','required'=>'required')) }}
                @if ($errors->has('user_name'))
                    <span class="help-block">
                        <strong>{{ $errors->first('user_name') }}</strong>
                    </span>
                @endif
              </td>
            </tr>
            <tr>
              <td>Direct Supervisor</td>
              <td>:</td>
              <td>{{ Form::select('directsup', $users_sup_list, $org['directsup_user_id'], array('class'=>'form-control')) }}</td>
            </tr>
            <tr>
              <td colspan="3">
                {{ Form::submit('Save',array('class'=>'btn btn-primary')) }}
                <a href="/Organization" class="btn btn-default">Back</a>
              </td>
            </tr>
          </table>
          {{ Form::close() }}
        </div>
        <div role="tabpanel" class="tab-pane" id="area">
          <br>
          {!! Form::open(['url' => '/Organization/'.$user_id.'/area/delete']) !!}
          <div class="table-responsive">
            <table id="mapping-table" class="display responsive"  width="100%">
              <thead>
                <tr>
                  <th width="15px"><input type="checkbox" name="all" id="check-all">All</th>
                  <th width="45%">Province</th>
                  <th width="50%">City</th>
                </tr>
              </thead>
              <tbody>

              </tbody>
            </table>
            <hr>
            <div class="pull">
                <button type="button" class="btn btn-sm btn-success"  class="add-mapping" data-toggle="modal" data-target="#addMapping"> Add New Mapping</button>
                <button class="btn btn-sm btn-danger" name="action_mapping" value="delete">Delete</button>
            </div>
          </div>
          {{ Form::close() }}
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="addMapping" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form data-toggle="validator" id="frm-addmapping"  method="POST">
          {{csrf_field()}}
          <input type="hidden" name="code" value ="{{$org['user_code']}}">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title" id="myModalLabel">Add Area</h4>
        </div>
        <div class="modal-body">
          <span id="form_output"></span>
          <div class="form-group" id="province-div">
            <label class="control-label" for="title">Province:</label>
            <select name="Provinces" id="province" class="form-control" onchange="getvalueregencies()">
              <option value="-">Pilih Salah Satu</option>
              @foreach($provinces as $p)
              <option value="{{$p->id}}">{{$p->name}}</option>
              @endforeach
            </select>
            <div class="help-block with-errors"></div>
          </div>
          <div class="form-group">
            <label class="control-label" for="title">City:</label>
            <select name="value[]" id="mapping-value" class="form-control" multiple>
            </select>
            <div class="help-block with-errors"></div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="form-group">
             <input type="hidden" name="button_action" id="button_action" value="" />
            <button type="submit" name="add" value="add" class="btn crud-submit btn-success">Submit</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          </div>
        </div>
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
<script src="{{ asset('js/bootstrap3-typeahead.min.js') }}"></script>
<script src="{{ asset('js/ui/1.12.1/jquery-ui.js') }}"></script>
<script src="{{ asset('js/org.js') }}"></script>
<script type="text/javascript">
var table=  $('#mapping-table').DataTable({
        "processing": true,
        //"serverSide": true,
        "ajax": window.Laravel.url+"/ajax/orgArea/"+$("#orgid").val(),
        "columns":[
            { "data": "id" , "orderable":false, "searchable":false, "name":"ID" },
            { "data": "province" },
            { "data": "city" }
        ],
        "columnDefs": [
          { "width": "15", "targets": 0 ,}
        ]
     });

     function getvalueregencies(){
       var province=$("#province").val();
         $('#province-div').show();
         $('#mapping-value').empty();
         $.get(window.Laravel.url+'/ajax/getCity',{id:province},function(data){
             $.each(data,function(index,subcatObj){
                 $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
             });
         });

     }

     $('#frm-addmapping').on('submit', function(event){
             event.preventDefault();
             $('#button_action').val('add');
             var form_data = $(this).serialize();
             console.log("data"+form_data);
             $.ajax({
                 url:baseurl+"/ajax/addAreaDPL",
                 method:"POST",
                 data:form_data,
                 dataType:"json",
                 success:function(data)
                 {
                     if(data.error.length > 0)
                     {
                         var error_html = '';
                         for(var count = 0; count < data.error.length; count++)
                         {
                             error_html += '<div class="alert alert-danger">'+data.error[count]+'</div>';
                         }
                         $('#form_output').html(error_html);
                     }
                     else
                     {
                         $('#form_output').html(data.success);
                         $('#frm-addmapping')[0].reset();
                         $('#mapping-value').empty();
                         $('#mapping-table').DataTable().ajax.reload();
                     }
                 }
             })
         });
</script>
@endsection
