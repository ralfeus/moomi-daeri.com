<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div id="loginza" class="box-content">
	<?php if (!$logged) { ?>
		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="login_module">
			<b><?php echo $entry_email; ?></b><br />
			<input type="text" name="email" />
			<br />
			<b><?php echo $entry_password; ?></b><br />
			<input type="password" name="password" />
			<div style="margin: 12px 0 12px 80px; text-align: left;"><a onclick="$('#login_module').submit();" class="button"><span><?php echo $button_login; ?></span></a></div>
			
			<script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>
			<a href="http://loginza.ru/api/widget?token_url=<? echo $action_loginza; ?>" class="loginza">
				<img src="http://loginza.ru/img/sign_in_button_gray.gif" alt="Войти через loginza"/>
			</a>
			
			  <?php if ($redirect) { ?>
			<input type="hidden" name="redirect" value="<?php echo $redirect; ?>" />
			<?php } ?>
		</form>
		<br />
		<ul>
			<li><a href="<?php echo $account_create; ?>"><?php echo $text_account_create; ?></a></li>
			<li><a href="<?php echo $forgotten; ?>"><?php echo $text_forgotten; ?></a><br /></li>
		</ul>
	<?php } else { ?>
		<p>
			<b><?php echo $text_my_account; ?></b>
			<ul>
				<li><a href="<?php echo $information; ?>"><?php echo $text_information; ?></a></li>
				<li><a href="<?php echo $password; ?>"><?php echo $text_password; ?></a></li>
				<li><a href="<?php echo $address; ?>"><?php echo $text_address; ?></a></li>
			</ul>
		</p>
		<p>
			<b><?php echo $text_my_orders; ?></b>
			<ul>
				<li><a href="<?php echo $history; ?>"><?php echo $text_history; ?></a></li>
			</ul>
		</p>
		<div style="margin: 12px 0 12px 40px; text-align: left;"><a href="<?php echo $logout; ?>" class="button"><span><?php echo $button_logout; ?></span></a></div>
	<? } ?>
  </div>
</div>