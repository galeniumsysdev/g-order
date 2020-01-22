var baseurl = $('#baseurl').val() ;
if($('#masaberlaku').length){
    $('#datetimepicker1').datetimepicker({
        format: "DD MMMM YYYY",
        locale: "en"
    });
}
function ubah(old){
    var cat_id =$('#groupdc').val();
    $.get(baseurl+'/ajax-subcat?cat_id='+cat_id,function(data){
        //console.log(data);
          $('#subgroupdc').empty();
        $.each(data,function(index,subcatObj){
          if (subcatObj.id==old)
          {
            $('#subgroupdc').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.display_name+'</option>');
          }else{
            $('#subgroupdc').append('<option value="'+subcatObj.id+'">'+subcatObj.display_name+'</option>');
          }

        });

  	});
}
function checkrole(){
  //var pscflag=$("#psc_flag").length;

  if ($("#psc_flag").is(':checked')){
    $('#divkategoridc').show();
  }else{
    $('#divkategoridc').hide();
  }
}

function add_dist_table(id,name){
  var outletid = $('#user_id').val();
  if ($('#language').val()=="id")
  {
      var msg = "Apakah anda yakin ingin menambahkan distributor "+name+"? Jika anda klik OK, maka sistem akan mengirimkan notifikasi langsung ke penyalur.";
  }else{
      var msg = "Are you sure to add distributor "+name+"? If you click OK then system will send notification to distributor.";
  }
  if (confirm(msg) == true) {
    $.get(baseurl+'/tambahDistributor/'+id+"/"+outletid,function(data){
      //  location.reload();
          $('#listdistributor').append("<tr>");
          $('#listdistributor').append("<td> "+data.customer_name+"</td>");
          $('#listdistributor').append("<td>-</td>");
          $('#listdistributor').append("</tr>");
          var search = $('#search_text').val();
          load_data(search,outletid);
    }).fail(function(){
      alert("error!");
      // Handle error here
    });
  };
  return false;
}

function load_data(query,id)
 {
   $.get(baseurl+'/searchCustomer/'+query+"/"+id,function(data){
       //console.log(data);
         $('#add_distributor').html(data);
   });

 }

 $('#reject-customer').on('click',function(){
   $('#pesan').html("");
   var notif = $('#notif_id').val();
   //alert(notif);
   var label =  $('#reject-customer').text()
   if (label=="Reject")
   {
       var reason = prompt("Please input your reason for reject?", "");
   }else{
     var reason = prompt("Masukkan alasan tolak?", "");
   }

   if (reason != null ) {//click ok
     if (reason != ""){
       $.ajax({
                type: 'post',
                url: baseurl+'/rejectbyGPL',
                data: {
                    '_token': $('input[name=_token]').val(),
                    'id': $("#user_id").val(),
                    'alasan': reason,
                    'notif_id':notif
                },
                success: function(data) {
                    $('#pesan').html("<div class='alert alert-info'>"+data.message+"</div>");
                    $('#status').html("");
                    $('#status').append("<label for='statue' class='control-label col-sm-2'>Status: </label>");
                    $('#status').append("<div class='col-sm-10'><p class='form-control'>Tolak: "+reason+"</p></div>");
                    $('#divdistributor').hide();
                    $('#listdistributor').hide();

                }
            });
     }else{
         alert("Reason must be filled");
     }

   }
   return false;
 });



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
    checkrole();
    if($('#listdistributor').length){
      	$('#listdistributor').DataTable({
                  "fnDrawCallback": function(oSettings) {
                      if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                          $(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                      }
                  }
              });
          window.setTimeout(function(){
              $(window).resize();
          },2000);
      }
    $('#search_text').keyup(function(){
      var search = $(this).val();
      var id = $('#user_id').val();
     //alert(search);
      if(search != '')
      {
       load_data(search,id);
      }
      else
      {
       load_data('',id);
      }
   });


  });

$('#psc_flag').on('change',function(){
  checkrole();
});

$('#reject-customer').on('click',function(){
  $('#pesan').html("");
  var notif = $('#notif_id').val();
  //alert(notif);
  var label =  $('#reject-customer').text()
  if (label=="Reject")
  {
      var reason = prompt("Please input your reason for reject?", "");
  }else{
    var reason = prompt("Masukkan alasan tolak?", "");
  }

  if (reason != null ) {//click ok
    if (reason != ""){
      $.ajax({
               type: 'post',
               url: baseurl+'/rejectbyGPL',
               data: {
                   '_token': $('input[name=_token]').val(),
                   'id': $("#user_id").val(),
                   'alasan': reason,
                   'notif_id':notif
               },
               success: function(data) {
                   $('#pesan').html("<div class='alert alert-info'>"+data.message+"</div>");
                   $('#status').html("");
                   $('#status').append("<label for='statue' class='control-label col-sm-2'>Status: </label>");
                   $('#status').append("<div class='col-sm-10'><p class='form-control'>Tolak: "+reason+"</p></div>");
               }
           });
    }else{
        alert("Reason must be filled");
    }

  }
  return false;
});

function activedist(id)
{
  $('#distributor_id').val(id);
  $('#action').val('active');
  $( "#formcustomer" ).attr('action',baseurl+ "/customer/inactiveDistributor");
  $( "#formcustomer" ).submit();
}

function inactivedist(id){
  $('#distributor_id').val(id);
  $('#action').val('inactive');
   $( "#formcustomer" ).attr('action',baseurl+ "/customer/inactiveDistributor");
   $( "#formcustomer" ).submit();
}


/*$('#edit-customer').on('click',function(){
  $.ajax({
      dataType: 'json',
      type:'PUT',
      url: form_action,
      data:{title:title, description:description}
      }).done(function(data){
        getPageData();
        $(".modal").modal('hide');
        toastr.success('Item Updated Successfully.', 'Success Alert', {timeOut: 5000});
   });
});*/
