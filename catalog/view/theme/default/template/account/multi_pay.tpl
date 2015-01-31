<?php echo $header; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>

<p><?php echo ($text_deposit_sel!='text_deposit_sel'?$text_deposit_sel:'Select payment merchant'); ?></p>
<div class="">
	<?php
	foreach ($payment_methods as $payment_method) {
		$code = $payment_method['code'];
		$icon = get($payment_method, 'icon', '<b>' .$code . '</b>');
		echo '<div> <a href="index.php?route=payment/cryptopay/deposit" target="_blank" class="-">' . $icon . '</a> </div>';
	}
?>
</div>
<?php echo $column_left; ?><?php echo $column_right; ?>