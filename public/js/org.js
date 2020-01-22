var baseurl = window.Laravel.url;

$(document).ready(function(){
  if($('#email-spv').length){
    $('#change-email').hide();
    $.get(window.Laravel.url+"/dpl/ajax/asmspv/",
      function (data) {
        $('#email-spv').typeahead({
            source: data,
            items: 10,
            showHintOnFocus: 'all',
            displayText: function (item) {
                return item.name+'-'+item.email;
            },
            afterSelect: function (item) {
              $('#email-spv').val(item.email);
              //$('#user-name').val(item.name);
              if($('#user-id').val()=="")
              {
                $('#user-id').val(item.id);
              }
              $('#email-spv').attr('readonly','readonly');
              //$('#user-name').attr('readonly','readonly');
                $('#change-email').show();
            }
        });
      }, 'json');
      $('#change-email').click(function(){
          $(this).hide();
        //  $('#user-name').removeAttr('readonly').val('');
          $('#email-spv').removeAttr('readonly').val('');
        //  $('#user-name').val('');
          //$('#user-id').val('');
      });

  }

  if($('#user-id').val()!='')
  {
    $('#change-email').show();
    $('#email-spv').attr('readonly','readonly');
  }
});
