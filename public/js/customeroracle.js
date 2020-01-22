var baseurl = window.Laravel.url;
if($('#masaberlaku').length){
    $('#datetimepicker1').datetimepicker({
        format: "DD MMMM YYYY",
        locale: "en"
    });
}
$(function() {
    $('#addMapping').on("show.bs.modal", function (e) {
      if($(e.relatedTarget).data('id')=="kombinasi"){
         $("#div-tipe").hide();
         getAllCategories();
         $('#mapping-value').empty();
         $("#div-category").show();
         $('#province-div').show();
         $('#myModalLabel').html('Add New Combination Mapping');
         $('#jenis').val('kombinasi');
       }else{
         $('#mapping-value').empty();
         $("#div-tipe").show();
         $("#div-category").hide();
         $('#province-div').hide();
         $('#myModalLabel').html('Add New Mapping');
       }
         $('#hidden-jenis').val($(e.relatedTarget).data('id'));
    });
});
function getvaluemapping(){
  var tipe=$("#mapping-type").val();
  if(tipe=="regencies")
  {
    $('#province-div').show();
    $('#mapping-value').empty();
    if($('#province').length){
      $('#province-area').val($('#province').val());
      $.get(baseurl+'/ajax/getCity',{id:$('#province').val()},function(data){
          $.each(data,function(index,subcatObj){
              $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
          });
      });
    }else{
      $.get(baseurl+'/ajax/getCity',function(data){
          $.each(data,function(index,subcatObj){
              $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
          });
      });
    }
  }else if(tipe=="category_outlets"){
    $('#province-div').hide();
    $('#mapping-value').empty();
    $.get(baseurl+'/ajax/getCatOutlet?id='+$('#customer_id').val(),function(data){
        //console.log(data);
          $('#mapping-value').empty();
        $.each(data,function(index,subcatObj){
            $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        });
    });

  }else{
    $('#mapping-value').empty();
    $('#province-div').hide();
  }
}

function getvalueregencies(){
  var province=$("#province-area").val();
    $('#province-div').show();
    $('#mapping-value').empty();
    $.get(baseurl+'/ajax/getCity',{id:province},function(data){
        $.each(data,function(index,subcatObj){
            $('#mapping-value').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        });
    });

}
function getAllCategories(){
  if(!$('#category-outlet option').length){
    $.get(baseurl+'/ajax/getCatOutlet',function(data){
        $.each(data,function(index,subcatObj){
            $('#category-outlet').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        });
    });

  }
}

$(document).ready(function() {
  if ($('input[type=radio][name=ijin_pbf]:checked').val()!=0) {
      $('#ijin_flag').show();
  }else{
      $('#ijin_flag').hide();
  }
  $('input[type=radio][name=ijin_pbf]').change(function() {
       if (this.value == 0) {
           $('#ijin_flag').hide();
       }
       else {
           $('#ijin_flag').show();
       }
   });
  $('#province-div').hide();
  var table=  $('#mapping-table').DataTable({
          "processing": true,
          //"serverSide": true,
          "ajax": baseurl+"/ajax/getMappingType/"+$("#customer_id").val(),
          "columns":[
              { "data": "id" , "orderable":false, "searchable":false, "name":"ID" },
              { "data": "data" },
              { "data": "name" }
          ],
          "columnDefs": [
            { "width": "15", "targets": 0 ,}
          ]
       });
   var table2=  $('#kombinasi-table').DataTable({
           "processing": true,
           //"serverSide": true,
           "ajax": baseurl+"/ajax/getMappngInclude/"+$("#customer_id").val(),
           "columns":[
               { "data": "id" , "orderable":false, "searchable":false, "name":"ID" },
               { "data": "category" },
               { "data": "province" },
               { "data": "regency" },
           ],
           "columnDefs": [
             { "width": "15", "targets": 0 ,}
           ]
        });

$('#check-all').click(function() {
    if ($(this).prop('checked')) {
        $('.chk-mapping').prop('checked', true);
    } else {
        $('.chk-mapping').prop('checked', false);
    }
});

$('#check-all1').click(function() {
    if ($(this).prop('checked')) {
        $('.chk-mapping1').prop('checked', true);
    } else {
        $('.chk-mapping1').prop('checked', false);
    }
});

if($('#dist-table').length){
  $('#dist-table').DataTable();
    window.setTimeout(function(){
        $(window).resize();
    },2000);
}


$('#frm-addmapping').on('submit', function(event){
        event.preventDefault();
        $('#button_action').val('add');
        var form_data = $(this).serialize();
        console.log("data"+form_data);
        $.ajax({
            url:baseurl+"/ajax/addMappingType",
            method:"POST",
            data:form_data,
            dataType:"json",
            success:function(data)
            {
              //console.log(data);
                if(data.error.length)
                {
                    var error_html = '';
                    for(var count = 0; count < data.error.length; count++)
                    {
                        error_html += '<div class="alert alert-danger">'+data.error[count]+'</div>';
                    }
                    $('#form_output').html("error"+error_html);
                }
                else
                {
                  var tmp=$('#hidden-jenis').val();
                    $('#form_output').html(data.success);
                    if (tmp =="kombinasi"){
                      $('#kombinasi-table').DataTable().ajax.reload();
                    }else{
                      $('#frm-addmapping')[0].reset();
                      $('#mapping-value').empty();
                      $('#province-div').hide();
                      $('#mapping-table').DataTable().ajax.reload();
                    }
                }
            }
        });
    });

});
