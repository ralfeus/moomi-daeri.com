<?php if ($modules) { ?>
  <div id="column-left">
    <?php for ($i=0; $i<count($modules); $i++) { ?>
    <?php echo $modules[$i]; ?>
    <?php } ?>

    <div style="height: 250px;">
      <?php echo $text_our_holidays; ?>
      <div id="calendar"></div>
      <div>
        <div class="legendRect work"></div> <?php echo $text_workday; ?> <br />
        <div class="legendRect free"></div> <?php echo $text_holiday; ?> <br />
      </div>
    </div>

  <?php
  if($route == 'common/home') {
    $this->load->model('gallery/photo');

    $photos = $this->model_gallery_photo->getAllApprovedPhotos();
  ?>
  <script type="text/javascript">
  $(document).ready(function() {
  	$('#mycarousel').jcarousel({
  		start: 0,
  		scroll: 1,
      vertical: true,
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
        }
      ?>
    </ul>
  </div>
  <?php } ?>
  </div>
<?php } ?>
