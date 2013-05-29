<div class="action_image">
  <a href="#" onclick="javascript: parent.window.location.href='<?php echo $action_url; ?>'">
    <img src="<?php echo $action_image ?>" width="400" height="400" />
  </a>
</div>
<hr />
<input type="checkbox" id="action_checkbox" onclick="window.parent.checkboxOnClick()" /> <?php echo $action_message; ?>
<?php

?>