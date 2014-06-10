<div class="left">
  <h2><?php echo $text_new_customer; ?></h2>
  <p><?php echo $text_checkout; ?></p>
  <label for="register">
    <?php if ($account == 'register') { ?>
    <input type="radio" name="account" value="register" id="register" checked="checked" />
    <?php } else { ?>
    <input type="radio" name="account" value="register" id="register" />
    <?php } ?>
    <b><?php echo $text_register; ?></b></label>
  <br />
  <?php if ($guest_checkout) { ?>
  <label for="guest">
    <?php if ($account == 'guest') { ?>
    <input type="radio" name="account" value="guest" id="guest" checked="checked" />
    <?php } else { ?>
    <input type="radio" name="account" value="guest" id="guest" />
    <?php } ?>
    <b><?php echo $text_guest; ?></b></label>
  <br />
  <?php } ?>
  <br />
  <p><?php echo $text_register_account; ?></p>
  <a id="button-account" class="button"><span><?php echo $button_continue; ?></span></a><br />
  <br />
</div>
<div id="login" class="right">
  <h2><?php echo $text_returning_customer; ?></h2>
  <p><?php echo $text_i_am_returning_customer; ?></p>
  <b><?php echo $entry_email; ?></b><br />
  <input type="text" name="email" value="" />
  <br />
  <br />
  <b><?php echo $entry_password; ?></b><br />
  <input type="password" name="password" value="" />
  <br />
  <a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a><br />
  <br />
  <a id="button-login" class="button"><span><?php echo $button_login; ?></span></a><br />
  <br />
</div>
<!-- loginza -->
<div style="float: right; width: 360px;">
  <h2><?php echo $text_enter_with_loginza; ?></h2>
  <script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>






  <iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url=<? echo $action_loginza; ?>" 
style="width:359px;height:300px;" scrolling="no" frameborder="no"></iframe>
</div>
  <!-- loginza -->
<script type="text/javascript"><!--
$('#login input').keydown(function(e) {
  if (e.keyCode == 13) {
    $('#button-login').click();
  }
});
//--></script>   