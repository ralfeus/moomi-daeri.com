<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="error"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/shipping.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="javascript:submitForm()" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_parent_page; ?></td>
            <td>
              <select id="parent" name="parent">
                <option value=""><?php echo $entry_no_parent; ?></option>
                <?php foreach ($allPages as $page) {
                  $selected = '';
                  if($page['page_id'] == $parent){
                    $selected = 'selected="selected"';
                  }
                  echo '<option value="' . $page['page_id'] . '" ' . $selected . '>' . $page['page_name_' . $this->language->get('code')] . '</option>';
                } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_parent_order; ?></td>
            <td>
              <input id="parent_order" name="parent_order" type="text" value="<?php echo isset($parent_order) ? $parent_order : ''; ?>" style="width: 40px"/>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_title_en; ?></td>
            <td><input id="title_en" type="text" name="title[en]" maxlength="255" size="100" value="<?php echo isset($title['en']) ? $title['en'] : ''; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_content_en; ?></td>
            <td><textarea name="content[en]" id="content_en"><?php echo isset($content['en']) ? $content['en'] : ''; ?></textarea></td>
          </tr>
          <tr>
            <td><?php echo $entry_title_ru; ?></td>
            <td><input id="title_ru" type="text" name="title[ru]" maxlength="255" size="100" value="<?php echo isset($title['ru']) ? $title['ru'] : ''; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_content_ru; ?></td>
            <td><textarea name="content[ru]" id="content_ru"><?php echo isset($content['ru']) ? $content['ru'] : ''; ?></textarea></td>
          </tr>
          <tr>
            <td><?php echo $entry_title_jp; ?></td>
            <td><input id="title_jp" ype="text" name="title[jp]" maxlength="255" size="100" value="<?php echo isset($title['jp']) ? $title['jp'] : ''; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_content_jp; ?></td>
            <td><textarea name="content[jp]" id="content_jp"><?php echo isset($content['jp']) ? $content['jp'] : ''; ?></textarea></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('content_<?php echo $language['code']; ?>', {
  filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>',
  filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?php echo $token; ?>'
});
<?php } ?>
//--></script>
<script type="text/javascript"><!--
function submitForm() {
  var title_en = $('#title_en').val();
  if(title_en == '') {
    $('#title_en').css('border', '1px solid red');
    alert('Please enter title for new page');
    return;
  }
  else {
    $('#title_en').css('border', '');
  }

  var title_ru = $('#title_ru').val();
  if(title_ru == '') {
    $('#title_ru').css('border', '1px solid red');
    alert('Please enter title for new page');
    return;
  }
  else {
    $('#title_ru').css('border', '');
  }

  var title_jp = $('#title_jp').val();
  if(title_jp == '') {
    $('#title_jp').css('border', '1px solid red');
    alert('Please enter title for new page');
    return;
  }
  else {
    $('#title_jp').css('border', '');
  }

  $('#form').submit();
}
function image_upload(field, thumb) {
  $('#dialog').remove();

  $('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');

  $('#dialog').dialog({
    title: '<?php echo $text_image_manager; ?>',
    close: function (event, ui) {
      if ($('#' + field).attr('value')) {
        $.ajax({
          url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).val()),
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

<?php echo $footer; ?>