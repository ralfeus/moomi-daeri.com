<?php foreach ($modules as $module) { ?>
<?php echo $module; ?>
<?php } ?>
<?php if ($isSaler && $showDownload) { ?>
<div style="width: 100%; text-align: right"><a class="button" onclick="downloadImages()"><span><?php echo $text_button_download; ?></span></a></div>
<form id="downloadForm" action="<?php echo $this->url->link('common/home/downloadImages') ?>" method="post">
  <input id="products" name="products" type="hidden" value="" />
</form>
<?php } ?>