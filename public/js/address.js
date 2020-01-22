$(document).ready(function() {
  var path = window.Laravel.url+"/ajax/typeaheadProvince";
  $.get(path,
      function (data) {
          $('#province-typeahead').typeahead({
              source: data,
              items: 10,
              showHintOnFocus: 'true',
              displayText: function (item) {
                  return item.name;
              },
              afterSelect: function (item) {
                $('#province-typeahead').val(item.name);
                $('#province-id').val(item.id);
              }
          });
        }, 'json');
$.get(window.Laravel.url+'/ajax/typeaheadCity',function(data){
      $('#city-name').typeahead({
          source: data,
          items: 10,
          showHintOnFocus: 'true',
          displayText: function (item) {
              return item.name;
          },
        });
      }, 'json');
});
