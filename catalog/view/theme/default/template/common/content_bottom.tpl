<?php
  $i = 0;
  foreach ($modules as $module) {
    echo $module;
    if ($i == 0) {
      if ($isSaler && $showDownload) {
        echo '<div style="width: 100%; text-align: right;"><a class="button" onclick="downloadImages()"><span>' . $text_button_download . '</span></a></div>';
      }
    }
    $i++;
  }
?>
<?php if ($isSaler && $showDownload) { ?>
<div style="width: 100%; text-align: right;"><a class="button" onclick="downloadImages()"><span><?php echo $text_button_download; ?></span></a></div>
<form id="downloadForm" action="<?php echo $this->url->link('common/home/downloadImages') ?>" method="post">
  <input id="products" name="products" type="hidden" value="" />
</form>
<?php } ?>