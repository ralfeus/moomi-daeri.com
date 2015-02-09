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
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/product.png" alt="" /> <?php echo $option_name; ?> (type: <?php echo $option_type; ?>)</h1>
      <div class="buttons">
        <a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a>
        <a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <table id="option-value" class="list">
          <thead>
            <tr>
              <td class="left"><span class="required">*</span> <?php echo $entry_value; ?></td>
              <td class="right"><?php echo $entry_sort_order; ?></td>
           </tr>
          </thead>
          <tbody>
            <tr>
              <td class="left">
                <input type="hidden" name="option_value_id" value="<?php echo $option_value['option_value_id']; ?>" />
                <input type="hidden" name="option_type" value="<?php echo $option_type; ?>" />
                <?php foreach ($languages as $language) { ?>
                  <input type="text" name="option_value[option_value_description][<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($option_value['option_value_description'][$language['language_id']]['name']) ? $option_value['option_value_description'][$language['language_id']]['name'] : ''; ?>" />
                  <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /><br />
                  <?php if (isset($error_option_value[$language['language_id']])) { ?>
                    <span class="error"><?php echo $error_option_value[$language['language_id']]; ?></span>
                  <?php } ?>
                <?php } ?>
              </td>
              <td class="right"><input type="text" name="sort_order" value="<?php echo $option_value['sort_order']; ?>" size="1" /></td>
            </tr>
          </tbody>
       </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>