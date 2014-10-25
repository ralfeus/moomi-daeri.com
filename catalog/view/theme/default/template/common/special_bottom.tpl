<?php if ($modules) { ?>
<div id="bottom-action">
    <?php foreach ($modules as $module) { ?>
    <?php echo $module; ?>
    <?php } ?>

<?php } ?>


<?php if ($isSaler && $showDownload) { ?>
<div style="width: 100%; text-align: right;"><a class="button" onclick="downloadImages()"><span>' . $text_button_download . '</span></a></div>
<div class="downImg"><a class="button" onclick="downloadImages()"><span><?php echo $text_button_download; ?></span></a></div>
<form id="downloadForm" action="<?php echo $this->url->link('common/home/downloadImages') ?>" method="post">
  <input id="products" name="products" type="hidden" value="" />
</form>
<?php } ?>

<div class="calend">
  <?php echo $text_our_holidays; ?>
  <div id="calendar"></div>
  <div>
    <div class="legendRect work"></div> <?php echo $text_workday; ?> <br />
    <div class="legendRect free"></div> <?php echo $text_holiday; ?> <br />
  </div>
</div>

<?php
  if($route == 'information/specaction') {
    $this->load->model('gallery/photo');
    $photos = $this->model_gallery_photo->getAllApprovedPhotos();
?>
<script type="text/javascript">
  $(document).ready(function() {
  	$('#mycarousel').jcarousel({
  		start: 0,
  		scroll: 1,
        vertical: false,
  		visible: 2,
  		auto: 2,
  		wrap: "last",
      itemFallbackDimension: 0,
      initCallback: resizeItems,
  	});
    jQuery('.nailthumb-container').nailthumb({width:140,height:140});
  });

  function resizeItems() {
    $('.jcarousel-item-vertical').css('height', '140px');
  }
</script>
  
<a class="rev-gallery" href="index.php?route=product/gallery"></a>
  
<div style="margin-top: 20px; width: 185px: overflow: hidden;">
  <ul id='mycarousel' class='jcarousel-skin-tango' style="width: 140px;">
 	<?php
      foreach ($photos as $photo) {
        echo "
          <li>
            <div class='nailthumb-container'>
              <a class='gallerie1' rel='gallerie1' href='".$this->url->link('product/gallery')."' title=''>
                <img src='".$photo['path']."' width='140' alt='Gallery' />
              </a>
            </div>
          </li>";
        }?>
  </ul>
</div>
  <?php } ?>
</div>