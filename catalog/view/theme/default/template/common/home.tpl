<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
	<?php echo $content_top; ?>
	<h1 style="display: none;">
			<?php echo $heading_title; ?>
	</h1>
<?php echo $content_bottom; ?>

	<div id="carousel" style="margin: 10px 10px 20px;">
<?php if($route == 'common/home' || $route == '') {
  $this->load->model('gallery/photo');
  $photos = $this->model_gallery_photo->getAllApprovedPhotos();?>
    
  <script type="text/javascript">
    $(document).ready(function() {
      $('#mycarousel').jcarousel({
        start: 0,
        scroll: 1,
        vertical: false,
        visible: 4,
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
    <ul id='mycarousel' class='jcarousel-skin-tango' style="width: 140px;">
      <?php foreach ($photos as $photo) {
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
<?php } ?>
</div>
  <div id="galeryWrap" style="position: relative; bottom: 240px; left: -150px;">
    <a class="rev-gallery" href="index.php?route=product/gallery"></a>
    <?php if ($isSaler && $showDownload) { ?>
      <div class="downImg"><a class="button" onclick="downloadImages()"><span><?php echo $text_button_download; ?></span></a></div>
      <form id="downloadForm" action="<?php echo $this->url->link('common/home/downloadImages') ?>" method="post">
        <input id="products" name="products" type="hidden" value="" />
      </form>
    <?php } ?>
  </div>
</div>
<?php echo $footer; ?>