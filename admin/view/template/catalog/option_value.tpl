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
      <h1><img src="view/image/product.png" alt="" /><?php echo $option_name; ?> (type: <?php echo $option_type; ?>)</h1>
      <div class="buttons">
        <a onclick="location = '<?php echo $insert; ?>'" class="button"><?php echo $button_insert; ?></a>
         <a onclick="$('#form').submit();" class="button"><?= $button_delete ?></a>
        <a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a>
      </div>
      <div class="limit"><b><?php echo $text_limit; ?></b>
        <select onchange="location = this.value;">
          <?php foreach ($limits as $limits) { ?>
            <?php if ($limits['value'] == $limit) { ?>
              <option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
            <?php } else { ?>
              <option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
            <?php } ?>
          <?php } ?>
        </select>
      </div>
    </div>
    <div class="content_value">
      <div class="pagination"><?php echo $pagination; ?></div>
      <script type="text/javascript">//<!--
        function topsclr() {
          document.getElementById("content_value_scroll").scrollLeft = document.getElementById("topscrl").scrollLeft;
        }
        function bottomsclr() {
          document.getElementById("topscrl").scrollLeft = document.getElementById("content_value_scroll").scrollLeft;
        }
        window.onload = function() {
          document.getElementById("topfake").style.width = document.getElementById("content_value_scroll").scrollWidth + "px";
          document.getElementById("topscrl").style.display = "block";
          document.getElementById("topscrl").onscroll = topsclr;
          document.getElementById("content_value_scroll").onscroll = bottomsclr;
        };//-->
      </script>
      <div id="topscrl">
        <div id="topfake"></div>
      </div>
      <div id="content_value_scroll">
        <form action="<?= $delete ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td style="width: 1px; text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
              <td style="text-align: center; width: 120px;" class="center"><?php echo $column_option_value; ?></td>
              <td style="text-align: center; width: 120px;" class="center"><?php echo $column_sort_value; ?></td>
              <td style="width: 20px; text-align: center;" class="right"><?php echo $column_action; ?></td>
           </tr>
          </thead>
          <tbody>
            <?php if ($option_values) { ?>
              <?php foreach ($option_values as $option_value) { ?>
                <tr>
                  <td style="text-align: center;">
                    <?php if ($option_value['selected']) { ?>
                      <input type="checkbox" name="selected[]" value="<?php echo $option_value['option_value_id']; ?>" checked="checked" />
                    <?php } else { ?>
                      <input type="checkbox" name="selected[]" value="<?php echo $option_value['option_value_id']; ?>" />
                    <?php } ?>
                  </td>
                  <td class="left">
                    <?php echo $option_value['name']; ?>
                  </td>
                  <td class="right">
                    <?php echo $option_value['sort_order']; ?>
                  </td>
                  <td class="center">
                    <?php foreach ($option_value['action'] as $action) { ?>
                      [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                    <?php } ?>
                  </td>
                </tr>
              <?php } ?>
            <?php } else { ?>
              <tr>
                <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
              </tr>
            <?php } ?>
          </tbody>            
        </table>
      </div>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>       
</div>
<?php echo $footer; ?>