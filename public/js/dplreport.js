$(document).ready(function() {
    $('#change-dist').hide();
    $('#change-asm').hide();
    $('#change-spv').hide();
    if($('#trx-in-date, #trx-out-date').length){
        $('#trx-in-date, #trx-out-date').datetimepicker({
            format: "MMMM YYYY",
            locale: "en"
        });
    }

    if($('.date-range').length){
        $('.date-range').datetimepicker({
            format: "MMMM YYYY",
            locale: "en"
        });
    }
    if($('#distributor').length){
      var path2 = window.Laravel.url+"/customer/searchDistributor/PHARMA";
      $.get(path2,
          function (data) {
              $('#distributor').typeahead({
                  source: data,
                  items: 10,
                  showHintOnFocus: 'all',
                  displayText: function (item) {
                      return item.customer_name;
                  },
                  afterSelect: function (item) {
                    $('#distributor').val(item.customer_name);
                    $('#dist_id').val(item.id);
                    $('#change-dist').show();
                    $('#distributor').attr('readonly','readonly');
                  }
              });
            }, 'json');
        $('#change-dist').click(function(){
            $(this).hide();
            $('#distributor').removeAttr('readonly').val('');
            $('#dist_id').val('');
        });
    }

    if($('#asm').length){
      $.get(window.Laravel.url+"/dpl/ajax/asmspv/ASM",
        function (data) {
          if(data.length==1)
          {
            $('#asm').val(data[0].name);
            $('#asm').attr('readonly','readonly');
            $('#asm-id').val(data[0].id);
          }
          $('#asm').typeahead({
              source: data,
              items: 10,
              showHintOnFocus: 'all',
              displayText: function (item) {
                  return item.name;
              },
              afterSelect: function (item) {
                $('#asm').val(item.name);
                $('#asm-id').val(item.id);
                $('#asm').attr('readonly','readonly');
                $('#change-asm').show();
                getspv();
              }
          });
        }, 'json');
        $('#change-asm').click(function(){
            $(this).hide();
            $('#asm').removeAttr('readonly').val('');
            $('#asm-id').val('');
            getspv();
        });
    }
    if($('#spv').length){
      getspv();
      $('#change-spv').click(function(){
          $(this).hide();
          $('#spv').removeAttr('readonly').val('');
          $('#spv-id').val('');
      });
    }

    function getspv(){
      $.get(window.Laravel.url+"/dpl/ajax/asmspv/SPV",{id:$("#asm-id").val()},
        function (data) {
          $("#spv").typeahead("destroy");
          $('#spv').typeahead({
              source: data,
              items: 10,
              showHintOnFocus: 'all',
              displayText: function (item) {
                  return item.name;
              },
              afterSelect: function (item) {
                $('#user-name').val(item.name);
                $('#user-id').val(item.id);
                $('##user-name').attr('readonly','readonly');                
              }
          });
        },'json');
    }
    // ==============
});
