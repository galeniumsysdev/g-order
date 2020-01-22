var baseurl = window.Laravel.url; //$('#baseurl').val() ;
function addCart(id)
{
  if(window.Laravel.auth.user && parseFloat($('#qty-'+id).val())>0)
  {
    $('#addCart-'+id).hide();
    $.ajax({
             type: 'post',
             url: baseurl+'/add-to-cart/'+id,
             data: {
                 '_token': $('input[name=_token]').val(),
                 'id': id,
                 'satuan': $('#satuan-'+id).val(),
                 'qty': $('#qty-'+id).val(),
                 'hrg':$('#hrg-'+id).val(),
                 'disc': $('#disc-'+id).val(),
             },
             success: function(data) {
               if(data.result=="success")
               {
                 $('#addCart-'+id).html("");
                 $('#shopcart').html(data.totline) ;
                 $('#shopcart2').html(data.totline) ;
                 $('#addCart-'+id).html("<span class='btn btn-sm btn-info btn-block'><i class='fa fa-shopping-cart' aria-hidden='true'></i> "+$('#qty-'+id).val()+" "+$('#satuan-'+id).val()+"</span>");
               }else if(data.result=="exist"){
                 //alert("Item already exist in shopping cart");
                 swal ( "" ,  "Item already exist in shopping cart" ,  "error" );
               }else if(data.result=="errdist"){
                 //alert("Item already exist in shopping cart");
                 swal ( "" ,  "Distributor not exist for type "+data.jns ,  "error" );
               }
             }
         });
         $('#addCart-'+id).show();
  }else if(parseFloat($('#qty-'+id).val())<=0){
    swal ( "" ,  "Qty harus lebih besar dari 0" ,  "error" );
  }else{
      window.location.href =  baseurl+'/login';
  }
  return false;
}

function changeProduct(id){
  if(window.Laravel.auth.user)
  {
    var id = id.substr(4,40);

    var v_id = id.substr(0,36);
    var v_uom = id.substr(-3,3);
    var url=$('#rmv-'+id).attr("href").substr(0,40);

    $.ajax({
             type: 'post',
             url: baseurl+'/edit-item-cart/'+id,
             data: {
                 '_token': $('input[name=_token]').val(),
                 'product': v_id,
                 'satuan': $('#stn-'+id).val(),
                 'qty': $('#qty-'+id).val(),
                 'hrg': 0
             },
             success: function(data) {
               var v_id_new = v_id+"-"+$('#stn-'+id).val();
               //alert(v_id_new);
               //$('#totprice2').html("Total: "+data.subtotal) ;
               $('#totprice1').html("<label class='visible-xs-inline'>Subtotal: </label>"+data.subtotal+"</strong>") ;
               $('#tottax').html("<label class='visible-xs-inline'>Tax: </label>"+data.tax+"</strong>") ;
               $('#totamount').html("<label class='visible-xs-inline'>Total: </label>"+data.total+"</strong>") ;
               $('#totdisc').html("<label class='visible-xs-inline'>Discount: </label>"+data.disctot+"</strong>") ;
               $('#subtot-'+id).html(data.amount) ;
               $('#hrg-'+id).html(data.disc) ;
               $('#disc-'+id).html(data.price) ;

               if(v_uom!=$('#stn-'+id).val())
               {
                  $('#'+id).attr('id',v_id_new);
                  $('#subtot-'+id).attr('id',"subtot-"+v_id_new) ;
                  $('#hrg-'+id).attr('id',"hrg-"+v_id_new) ;
                  $('#qty-'+id).attr('id',"qty-"+v_id_new) ;
                  $('#stn-'+id).attr('id',"stn-"+v_id_new) ;
                  $('#rfs-'+id).attr('id',"rfs-"+v_id_new) ;
                  $('#rmv-'+id).attr("href", url+v_id_new);
                  $('#rmv-'+id).attr("id", "rmv-"+v_id_new);
                //  alert($('#qty-'+v_id_new).attr('id'));
               }

             },error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Status: " + textStatus); alert("Error: " + errorThrown);
                }
         });
  }else{
      window.location.href =  baseurl+'/login';
  }

  return false;
}

function getPrice(id){
  var v_uom = $('#satuan-'+id).val();
  var v_itemcode;
  $('#lblhrg-'+id).html("<i class='fa fa-spinner fa-pulse fa-2x fa-fw'></i>");
  $('#addCart-'+id).hide();
  $.get(baseurl+'/getPrice?product='+id+'&uom='+v_uom,function(data){
    v_itemcode = data.itemcode;
    if(v_uom!=data.uomprimary || data.konversi!=1)
    {
      $('#info-product-'+id).html('1 '+v_uom +' = '+data.konversi+' '+data.uomprimary);
    }else{
      $('#info-product-'+id).html('');
    }
    if(v_itemcode.substr(0,2)=='43')
    {
      $('#lblhrg-'+id).html('$ '+rupiah(data.diskon)+'/'+v_uom);
      if(data.price!=data.diskon)
      {
          $('#hrgcoret-'+id).html('$ '+rupiah(data.price)+'/'+v_uom);
      }else{
          $('#hrgcoret-'+id).html('');
      }

    }else{
      $('#lblhrg-'+id).html('Rp. '+rupiah(data.diskon)+'/'+v_uom);
      if(data.price!=data.diskon)
      {
        $('#hrgcoret-'+id).html('Rp. '+rupiah(data.price)+'/'+v_uom);
      }else{
        $('#hrgcoret-'+id).html('');
      }
    }
    $('#hrg-'+id).val(data.price);
    $('#disc-'+id).val(data.diskon);
  });
      $('#addCart-'+id).show();
}

function rupiah(nStr) {
   nStr += '';
   x = nStr.split('.');
   x1 = x[0];
   x2 = x.length > 1 ? '.' + x[1] : '';
   var rgx = /(\d+)(\d{3})/;
   while (rgx.test(x1))
   {
      x1 = x1.replace(rgx, '$1' + ',' + '$2');
   }
   return  x1 + x2;
}

function validateNumber(event) {
    var key = window.event ? event.keyCode : event.which;
    if (event.keyCode === 8 || event.keyCode === 46) {
        return true;
    } else if ( key < 48 || key > 57 ) {
        return false;
    } else {
        return true;
    }
};

function isNumberKey(evt)
{
  var charCode = (evt.which) ? evt.which : evt.keyCode;
  if (charCode != 46 && charCode > 31
    && (charCode < 48 || charCode > 57))
     return false;

  return true;
}

function isNumberCheck(field) {
    var regExpr = new RegExp("^\d*\.?\d*$");
    //var regExpr = new RegExp("^\d+(\.\d*)?$");
    if (!regExpr.test(field.value)) {
      // Case of error
      field.value = "";
    }
}

function inputreason()
{
  var label =  $('#reject_PO').text()
  if (label=="Reject")
  {
      var reason = prompt("Please input your reason for reject the PO?", "");
  }else{
    var reason = prompt("Masukkan alasan tolak PO?", "");
  }
  if (reason != null ) {//click ok
    if (reason != ""){
      $('#alasanreject').val(reason);
      return true;
    }else{
        alert("Reason must be filled");
        return false;
    }

  }
  return false;
}

/*
$('#reject_PO').on('click',function(){
  var label =  $('#reject_PO').text()
  if (label=="Reject")
  {
      var reason = prompt("Please input your reason for reject the PO?", "");
  }else{
    var reason = prompt("Masukkan alasan tolak PO?", "");
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
});*/

$('#coupon_no').blur(function(){
  /*var path = {{route('DPLController@suggestNoValidation')}};
    $.get(path+"/"+this.val,function(data){

    });*/

    $.ajax({
             type: 'get',
             url: baseurl+'/dpl/suggestno/validation/'+window.Laravel.customerid+'/'+$(this).val(),
             success: function(data) {
               if(data.valid){
                 swal ( "No Kupon DPL Valid!" ,  "" ,  "success" )
               }else{
                 //alert("nomor dpl tidak valid");

                 swal ( "No Kupon DPL tidak valid!" ,  "" ,  "error" )

                 $('#coupon_no').val('');
               }
             }
         });
});

function changeUomOrder(id){
    var v_uom = $('#uom-'+id).val();
    $.ajax({
             type: 'post',
             url: baseurl+'/ajax/changeOrderUom',
             data: {
                 '_token': $('input[name=_token]').val(),
                 'id': id,
                 'satuan': $('#uom-'+id).val(),
             },
             success: function(data) {
               if(data.result=="success")
               {
                 $("#ord-"+id).html(data.qtyorder);
                 $("#hrg-"+id).html(rupiah(data.price));

               }

             },error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Status: " + textStatus); alert("Error: " + errorThrown);
                }
         });
  return false;

}
