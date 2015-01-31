<?php echo $header; ?>
<?php //echo $column_left; ?><?php //echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php //echo $text_message; ?>

  <?php // echo $payment_methods; ?>

  <div class="buttons">
    <div class="right"><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
  </div>
  <?php //echo $content_bottom; ?></div>
<?php //echo $footer; ?>