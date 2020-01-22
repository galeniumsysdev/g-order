var baseurl = window.Laravel.url;

function getListCity(id,old){
  $.get(baseurl+'/ajax/getCity?id='+id,function(data){
      //console.log(data);
        $('#city').empty();
        $('#city').append('<option value="" selected=selected>--</option>');
        $('#district').empty();
        $('#district').append('<option value="" selected=selected>--</option>');
        $('#subdistricts').empty();
        $('#subdistricts').append('<option value="" selected=selected>--</option>');
      $.each(data,function(index,subcatObj){
        if (subcatObj.id==old)
        {
          $('#city').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.name+'</option>');
        }else{
          $('#city').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
        }
      });
      $( "#city" ).prop( "disabled", false );

  });
}

function getListDistrict(id,old){

  $.get(baseurl+'/ajax/getDistrict?id='+id,function(district,status){
      //console.log(data);
        $('#district').empty();
        $('#district').append('<option value="" selected=selected>--</option>');
        $('#subdistricts').empty();
        $('#subdistricts').append('<option value="" selected=selected>--</option>');

          $.each(district,function(index,subcatObj){
            if (subcatObj.id==old)
            {
              $('#district').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.name+'</option>');
            }else{
              $('#district').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
            }
          });        
      $( "#district" ).prop( "disabled", false );

  });
}

function getListSubdistrict(id,old){
  $.get(baseurl+'/ajax/getSubdistrict?id='+id,function(subdistrict,status1){
      //console.log(data);
        $('#subdistricts').empty();
        $('#subdistricts').append('<option value="" selected=selected>--</option>');
        if(status1=="success"){
          $.each(subdistrict,function(index,subcatObj){
            if (subcatObj.id==old)
            {
              $('#subdistricts').append('<option value="'+subcatObj.id+'" selected=selected>'+subcatObj.name+'</option>');
            }else{
              $('#subdistricts').append('<option value="'+subcatObj.id+'">'+subcatObj.name+'</option>');
            }
          });
        }else{
          alert(status1);
        }
      $( "#subdistricts" ).prop( "disabled", false );
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
