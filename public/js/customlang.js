function changeLanguage(language) {
  $.ajax({
      url:window.Laravel.url+"/language",
      type:"POST",
      headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
      dataType: "json",
      data: {locale:language},
      success: function(data){

      },
      error: function(data){
        var errors = data.responseJSON;
        console.log(errors);
        $("#alert-success").html("errors:"+errors);
      },
      beforeSend: function(data){

      },
      complete: function(data){
        window.location.reload(true);
      }
    });
};





$(document).ready(function(){
  $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  /*$("#enLang").click(function(){
    changeLanguage("en");
	   //alert("en")
  });
  $("#idLang").click(function(){
    changeLanguage("id");
    //alert("id");
  });*/

});
