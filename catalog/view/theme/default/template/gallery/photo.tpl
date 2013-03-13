<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
	<?php echo $content_top; ?>
	<div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
    <?php } ?>
	</div>
	<h1><?php echo $heading_title; ?></h1>
	<br />
	<div id="error" align="center" class="red font14"><?= isset($galery_photo_error) ? $galery_photo_error : '' ?></div>
	<form id="uploadFile" action="index.php?route=gallery/photo/uploadPhoto" enctype="multipart/form-data" method="post">
		<table>
			<tr>
				<td><?php echo $galery_photo_name; ?><span class="red">*</span> </td>
				<td> <input id="photoName" name="galery_photo_name" class="width300" type="text" value="<?= isset($galery_photo_post_name) ? $galery_photo_post_name : '' ?>" /> </td>
			</tr>
			<tr>
				<td> <?php echo $galery_photo_description; ?> </td>
				<td> <input id="photoDescription" name="galery_photo_description" class="width300" type="text" value="<?= isset($galery_photo_post_description) ? $galery_photo_post_description : '' ?>" onkeyup="checkLetters()" /> </td>
			</tr>
			<tr>
				<td>
					&nbsp;
				</td>
				<td>
					<input id="photoFile" type="file" id="imageFile" name="imageFile" />
				</td>
			</tr>
			<tr>
				<td colspan="2" align="right">
					<a class="button" href="javascript: checkFields();"><span>Добавить фото</span></a>
				</td>
			</tr>
		</table>
	</form>
</div>
<script type="text/javascript">
	function checkFields() {
		var name = $('#photoName').val();
		if(name == '') {
			$('#error').html('<?php echo $galery_photo_name_empty; ?>');
		}
		else{
			$('#error').html('');
			var path = $('#photoFile').val();
			if(path == '') {
				$('#error').html('<?php echo $galery_photo_file_empty; ?>');
			}
			else {
				$('#error').html('');
				$('#uploadFile').submit();
			}	
		}
	}

	function checkLetters() {
		var desc = $('#photoDescription').val();
		if(desc.length > 100){
			$('#photoDescription').val(desc.substr(0, 100));
			$('#error').html('<?php echo $galery_photo_description_to_long; ?>');
		}
		else {
			$('#error').html('');
		}
	}

</script>

<?php echo $footer; ?>