<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
	<?php echo $content_top; ?>
	<div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
    <?php } ?>
	</div>
	<h1><?php echo $heading_title; ?></h1>
  <div id="success" style="color: green; font-size: 16px;"><?php echo $success ;?></div>
  <div id="success" class="red font14"><?php echo $razmer ;?></div>
	<br />
	<div id="error" align="center" class="red font14"><?= isset($galery_photo_error) ? $galery_photo_error : '' ?></div>
	<form id="uploadFile" action="index.php?route=gallery/photo/uploadPhoto" enctype="multipart/form-data" method="post">
		<table style="width: 100%;">
			<tr>
				<td>
					&nbsp;
				</td>
				<td colspan="2" align="right">
					<a class="button" href="javascript: checkFields();"><span>Добавить фото</span></a>
				</td>
			</tr>
			<tr>
				<td colspan="2"> <strong> <?php echo $galery_text_max_photo_size; ?> </strong> 
       &nbsp;<input id="size" name="size" type="hidden"/></td>
			</tr>
			<tr>
				<td><?php echo $galery_photo_name; ?></td>
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
					<input id="photoFile" type="file" name="imageFile"/>
     </td>
     </tr>
     <tr>
     				<td>
					&nbsp;
				</td>
<td>
<img alt="" id="image_preview" class="thumb" src="" style="width: 200px"/>

<script type="text/javascript">
$('#photoFile').change(function() {
    var input = $(this)[0];
  var size = input.files[0].size/(1024*1024);
    document.getElementById('size').value = size;
  if ( input.files && input.files[0] ) {
    if ( input.files[0].type.match('image.*') ) {
      var reader = new FileReader();
      reader.onload = function(e) { $('#image_preview').attr('src', e.target.result); }
      reader.readAsDataURL(input.files[0]);
    }
  }

});
</script>

				</td>
			</tr>
		</table>
	</form>
</div>
<script type="text/javascript">
	function checkFields() {
			var path = $('#photoFile').val();
      var size = $('#size').val();
      if (size > 1) {
				$('#error').html('<?php echo $galery_max_size; ?>');        
      } else {
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