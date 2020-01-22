jQuery(document).ready(function($){

$('.external-psc').on('click',function(){

var getLink = $(this).attr('href');

swal({
  title: 'Produk PSC',
  text: '( Caladine, Oilum, V-Mina, Bellsoap, JFSulfur )',
  html: true,
},function(){

window.open(getLink,'_self')

});

return false;

});

});

//sweetalert untuk pharma
jQuery(document).ready(function($){

$('.external-pharma').on('click',function(){

var getLink = $(this).attr('href');

swal({
  title: 'Produk Pharma',
  text: '( ACNE FELDIN, BIODERM CREAM, DERMAFOOT, MELAVITA Ceam, MESONTA 5 gr, MYCOSTOP Tab, MYCOTRAZOLE)',
  html: true,
},function(){

window.open(getLink,'_self')

});

return false;

});

});