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
<div style="width: 185px: overflow: hidden;">
  <ul id='mycarousel' class='jcarousel-skin-tango' style="width: 140px;">
  	<?php 
      foreach ($photos as $photo) {
        echo "
        <li>
          <a class='gallerie1' rel='gallerie1' href='".$this->url->link('product/gallery')."' title=''>
            <div class='nailthumb-container' >
              <img src='".$photo['path']."' width='140' />
            </div>
          </a>
        </li>";
      }
      //print_r($photo); die();
    ?>
    <!--<li>
    	<a class='gallerie1' rel='gallerie1' href='http://localhost/moomidaeri/image/gallery/50f86af9a63ba_Hydrangeas.jpg' title=''>
        <div class="nailthumb-container" style="margin-bottom: 5px; height: 140;">
      	  <img src='http://localhost/moomidaeri/image/gallery/50f86af9a63ba_Hydrangeas.jpg' width='140' />
        </div>
      </a>
    </li>
    <li>
      <a class='gallerie1' rel='gallerie1' href='http://localhost/moomidaeri/image/gallery/50f29a0e2554e_2012-04-28%2014.50.19.jpg' title=''>
        <div class="nailthumb-container" style="margin-bottom: 5px;">
      	  <img src='http://localhost/moomidaeri/image/gallery/50f29a0e2554e_2012-04-28%2014.50.19.jpg' width='140' />
        </div>
      </a>
    </li>
    <li>
      <a class='gallerie1' rel='gallerie1' href='http://localhost/moomidaeri/image/gallery/50f86af9a63ba_Hydrangeas.jpg' title=''>
        <div class="nailthumb-container" style="margin-bottom: 5px;">
          <img src='http://localhost/moomidaeri/image/gallery/50f86af9a63ba_Hydrangeas.jpg' width='140' />
        </div>
      </a>
    </li>-->
  </ul>
</div>
<?php } ?> 
<?php } ?> 
</div>
