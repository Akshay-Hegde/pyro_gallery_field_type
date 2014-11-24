<div class="gallery-image-holder js-gallery-holder" data-stream="<?php echo $stream?>" data-entry-id="<?php echo $entry_id; ?>">

	<h4>Edit current images</h4>

	<?php foreach ($images as $key => $image): ?>

		<div class="gallery-image js-gallery-image" data-id="<?php echo $image['id']; ?>">
			<span class="gallery-image-remove js-image-remove">x</span>
			<div><img src="<? echo $image['path']; ?>"></div>
			<!--<input class="js-caption-img" value="<?php echo $image['caption']; ?>" type="text" name="caption-<?php echo $image['id']; ?>" placeholder="Caption">-->	
		</div>

	<?php endforeach; ?>

</div>

<div class="dropzone-js dropzone-style btn blue">Add Image</div>