(function(){

	$holder = null
	stream = null
	entryId = null

	function updateImage(val, response)
	{
		$img = $('.js-gallery-image').first().clone();

		// clear caption
		$img.find('.js-caption-img').val('');

		//update ids 
		id = response['id'];
		$img.attr('data-id', id)

		// set image
		$img.find('img').first().attr('src', response['path']);

		$img.removeClass('hidden');

		$('.js-gallery-holder').append($img);
	};

	function deleteImage(element)
	{
		if (confirm('Are you sure you wish to delete this image?'))
		{
			index = $('.js-image-remove').index(element)

			image = $($('.js-gallery-image')[index])

			id = image.data('id')

			request = $.post("streams_core/public_ajax/field/gallery/delete_image", {id : id})

			request.done(function(){
				image.remove()
			});
		}
	};

	function uploadError()
	{
		alert('There was an error uploading the image');
	}

	$(function(){

		$holder = $('.js-gallery-holder')
		stream = $holder.data('stream')
		entryId = $holder.data('entry-id')


		$(".dropzone-js").dropzone({ 
			paramName: 'userfile',
			url: "streams_core/public_ajax/field/gallery/upload_image",
			previewsContainer: false,
			success: updateImage,
			maxFilesize: 2,
			acceptedFiles: 'image/jpeg,image/png,image/jpg',
			params: { stream: stream, entry_id: entryId },
			error: uploadError
		});

		$(document).on('click', '.js-image-remove', function(){
			deleteImage(this)
		})

	});

})();