<div class="gallery-image-holder js-gallery-holder" data-stream="<?php echo $stream?>" data-entry-id="<?php echo $entry_id; ?>">

	<h4>Edit current images</h4>
	<h6><strong>Max Filesize 2MB</strong></h6>
	<h6><strong>Allowed types PNG, JPG</strong></h6>

	<!-- cloneable -->
	<div class="gallery-image js-gallery-image hidden" data-id="">
		<span class="gallery-image-remove js-image-remove">x</span>
		<div><img src=""></div>
		<input class="js-caption-img" value="" type="text" name="caption[]" placeholder="Caption">	
	</div>
	<!-- /cloneable -->

	<?php foreach ($images as $key => $image): ?>

		<div class="gallery-image js-gallery-image" data-id="<?php echo $image['id']; ?>">
			<span class="gallery-image-remove js-image-remove">x</span>
			<div><img src="<? echo $image['path']; ?>"></div>
			<input class="js-caption-img" value="<?php echo $image['caption']; ?>" type="text" name="<?php echo $field; ?>[]" placeholder="Caption">	
		</div>

	<?php endforeach; ?>

</div>

<div class="dropzone-js dropzone-style btn blue">Add Image</div>