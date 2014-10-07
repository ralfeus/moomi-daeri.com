<?php echo $header; ?>

<script type="text/javascript">
  $(document).ready(function() {

    $("#actionStart").datepicker({dateFormat: 'yy-mm-dd'});
    $("#actionEnd").datepicker({dateFormat: 'yy-mm-dd'});

  });

  function submitForm() {
    $('#form').submit();
  }

</script>
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
      <form action="<?php echo $action_add_url; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="form">
          <tr>
            <td><?php echo $entry_action_name; ?></td>
            <td>
              <input id="actionName" name="actionName" type="text" maxlength="64" size="100" value="<?php echo $action['name'] ?>" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_customer_group; ?></td>
            <td>
              <select id="customer_group" name="customer_group">
                <option value=""><?php echo $entry_all_groups; ?></option>
                <?php foreach ($customer_groups as $customer_group) {
                  $selected = '';
                  if($customer_group['customer_group_id'] == $action['customer_group_id']){
                    $selected = 'selected="selected"';
                  }
                  echo '<option value="' . $customer_group['customer_group_id'] . '" ' . $selected . '>' . $customer_group['name'] . '</option>';
                } ?>
              </select>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_action_start; ?></td>
            <td>
              <input id="actionStart" name="actionStart" type="text" readonly="readonly" value="<?php echo $action['start_date']; ?>" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_action_end; ?></td>
            <td>
              <input id="actionEnd" name="actionEnd" type="text" readonly="readonly" value="<?php echo $action['finish_date']; ?>" />
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_img_ru; ?></td>
            <td>
              <?php if(!isset($action_images['ru'])) { ?>
                <input id="image_ru" name="image[ru]" type="file"/>
              <?php } else { ?>
                <img src="<?php echo HTTP_SERVER. "view/image/actions/" . $action_images['ru'] ?>" alt="" />
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_url_ru; ?></td>
            <td><input id="url_ru" type="text" name="url[ru]" maxlength="255" size="100" value="<?php echo isset($action_urls['ru']) ? $action_urls['ru'] : ''; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_img_en; ?></td>
            <td>
              <?php if(!isset($action_images['en'])) { ?>
              <input id="image_en" name="image[en]" type="file"/>
              <?php } else { ?>
                <img src="<?php echo HTTP_SERVER. "view/image/actions/" . $action_images['en'] ?>" alt="" />
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_url_en; ?></td>
            <td><input id="url_en" type="text" name="url[en]" maxlength="255" size="100" value="<?php echo isset($action_urls['en']) ? $action_urls['en'] : ''; ?>" /></td>
          </tr>
          <tr>
            <td><?php echo $entry_img_jp; ?></td>
            <td>
              <?php if(!isset($action_images['jp'])) { ?>
              <input id="image_jp" name="image[jp]" type="file"/>
              <?php } else { ?>
                <img src="<?php echo HTTP_SERVER. "view/image/actions/" . $action_images['jp'] ?>" alt="" />
              <?php } ?>
            </td>
          </tr>
          <tr>
            <td><?php echo $entry_url_jp; ?></td>
            <td><input id="url_jp" type="text" name="url[jp]" maxlength="255" size="100" value="<?php echo isset($action_urls['jp']) ? $action_urls['jp'] : ''; ?>" /></td>
          </tr>
        </table>
      </form>
    </div>
  </div>
</div>

<?php echo $footer; ?>