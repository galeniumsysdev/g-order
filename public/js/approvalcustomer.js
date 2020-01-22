var baseurl = $('#baseurl').val() ;
$('#approve-customer').on('click',function(){
  $('#pesan').html("");
  var notif = $('#notif_id').val();
  $.ajax({
           type: 'post',
           url: baseurl+'/approveOutlet',
           data: {
               '_token': $('input[name=_token]').val(),
               'id': $("#customer_id").val(),
               'notif_id':notif
           },
           success: function(data) {
             if(data.status==403)
             {
               alert('You dont have permission')
             }else{
               alert(data.message);
               $('#pesan').html("<div class='alert alert-info'>"+data.message+"</div>");
               $('#status').html("");
               $('#status').append("<label for='statue' class='control-label col-sm-2'>Status: </label>");
               $('#status').append("<div class='col-sm-10'><p class='form-control'>Approve</p></div>");
              }
           }
       });
  return false;
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
               url: baseurl+'/rejectOutlet',
               data: {
                   '_token': $('input[name=_token]').val(),
                   'id': $("#customer_id").val(),
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

if($('#customer-number').length){
  $('#change-custnum').hide();
  $('#data-oracle').hide();
    $.get(window.Laravel.url+'/customer/searchOracleOutlet',
        function (data) {
            $('#customer-number').typeahead({
                source: data,
                items: 10,
                showHintOnFocus: 'all',
                displayText: function (item) {
                    return item.customer_number+"-"+item.customer_name;
                },
                afterSelect: function (item) {
                    $('#customer-number').val(item.customer_number);
                    $('#customer-name').val(item.customer_name);
                    $('#customer-id').val(item.oracle_customer_id);
                    $('#customer-number').attr('readonly','readonly');
                    $('#data-oracle').show();
                    $('#change-custnum').show();
                    $('#cust-name').text(item.customer_name);
                }
            });
    }, 'json');
}

$('#change-custnum').click(function(){
    $(this).hide();
    $('#customer-number').removeAttr('readonly').val('');
    $('#customer-number').val('');
    $('#customer-name').val();
    $('#customer-id').val();
    $('#data-oracle').hide();
    $('#cust-name').text($("#old-cust-name").val());
});
