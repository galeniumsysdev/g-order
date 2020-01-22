function readURL(input) {
  if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
          $('#img_avatar').attr('src', e.target.result);
          $('#img_profile').attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
  }
}
function changeProfile() {
        $('#fileavatar').click();
    }
function upload() {
        var file_data = $('#fileavatar').prop('files')[0];
			//	alert("{{asset('uploads/loader.gif')}}");
        var form_data = new FormData();
        form_data.append('avatar', file_data);
        $.ajaxSetup({
            headers: {'X-CSRF-Token': $('meta[name=_token]').attr('content')}
        });
				  $('#img_avatar').attr('src', "{{asset('/uploads/loader.gif')}}");
        $.ajax({
            url: "{{url('/profile')}}", // point to server-side PHP script
            data: form_data,
            type: 'POST',
            contentType: false,       // The content type used when sending data to the server.
            cache: false,             // To unable request pages to be cached
            processData: false,
            success: function (data) {
                if (data.fail) {
                    $('#img_profile').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
										$('#img_avatar').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
                    alert(data.errors['avatar']);
                }
                else {
                    filename = data.filename;
                    $('#img_profile').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+filename);
										$('#img_avatar').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+filename);
                }
            },
            error: function (xhr, status, error) {
                alert(xhr.responseText);
                $('#img_profile').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
								$('#img_avatar').attr('src', "{{asset('/uploads/avatars/')}}"+'/'+data.filename);
            }
        });
    }

$("#fileavatar").change(function(){
	if ($(this).val() != '') {
		upload();
	  //readURL(this);

	 // });
	}

});
