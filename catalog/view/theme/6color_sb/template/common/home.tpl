<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
<?php if (isset($welcome)): ?>
  <div class="top">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center">
      <h1><?php echo $heading_title; ?></h1>
    </div>
  </div>
  <div class="middle">
    <div><?php echo $welcome; ?></div>
  </div>
  <div class="bottom">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center"></div>
  </div>
<?php endif; ?>
<?php if (isset($modules)):
    foreach ($modules as $module): ?>
  <?= ${$module['code']}; ?>
    <?php endforeach; ?>
<?php endif; ?>
</div>
<?php echo $footer; ?> 