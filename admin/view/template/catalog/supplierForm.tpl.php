<?= $header ?>
<div id="content">
  <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb): ?>
    <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php endforeach; ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="error"><?= $error_warning ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/shipping.png" alt="" /> <?= $heading_title ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?= $button_save ?></a><a onclick="location = '<?= $cancel ?>';" class="button"><?= $button_cancel ?></a></div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs"><a href="#tab-general"><?= $tab_general ?></a></div>
      <form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general">
          <table class="form">
            <tr>
              <td><span class="required">*</span> <?= $entry_name ?></td>
              <td><input type="text" name="name" value="<?= $name ?>" maxlength="255" size="100" />
                <?php if ($error_name): ?>
                    <span class="error"><?= $error_name ?></span>
                <?php endif; ?></td>
            </tr>
            <tr>
                <td><?= $entry_supplier_group ?></td>
                <td>
                    <select name="supplierGroupId">
                        <option value="0"><?= $text_none ?></option>
<?php foreach ($supplierGroups as $supplierGroup): ?>
    <?php if ($supplierGroup['supplier_group_id'] == $supplierGroupId): ?>
                        <option value="<?= $supplierGroup['supplier_group_id'] ?>" selected="selected"><?= $supplierGroup['name'] ?></option>
    <?php else: ?>
                        <option value="<?= $supplierGroup['supplier_group_id'] ?>"><?= $supplierGroup['name'] ?></option>
    <?php endif; ?>
<?php endforeach; ?>
                    </select>
                </td>
            </tr>
              <tr>
                  <td><?= $textShippingCost ?></td>
                  <td>
                      <input type="text" name="shippingCost" value="<?= $shippingCost ?>" maxlength="255" size="100" />
<?php if ($error_shippingCost): ?>
                      <span class="error"><?= $error_shippingCost ?></span>
<?php endif; ?>
                  </td>
              </tr>

              <tr>
                <td><?= $entry_internal_model ?></td>
                <td><input type="text" name="internalModel" value="<?= $internalModel ?>" maxlength="255" size="100" />
            </td>
            </tr>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description<?= $language['language_id'] ?>', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>'
});
<?php } ?>
//--></script> 
<script type="text/javascript"><!--
function image_upload(field, thumb) {
	$('#dialog').remove();
	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?= $token ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	
	$('#dialog').dialog({
		title: '<?= $text_image_manager ?>',
		close: function (event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: 'index.php?route=common/filemanager/image&token=<?= $token ?>&image=' + encodeURIComponent($('#' + field).val()),
					dataType: 'text',
					success: function(data) {
						$('#' + thumb).replaceWith('<img src="' + data + '" alt="" id="' + thumb + '" />');
					}
				});
			}
		},	
		bgiframe: false,
		width: 800,
		height: 400,
		resizable: false,
		modal: false
	});
};
//--></script> 
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
$('#languages a').tabs();
//--></script> 
<?= $footer ?>