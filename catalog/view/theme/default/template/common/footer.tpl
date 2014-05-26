<div id="footer" style="display: none;">
  <?php if ($informations) { ?>
  <div class="column">
    <h3><?php echo $text_information; ?></h3>
    
      <?php foreach ($informations as $information) { ?>
      <a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a><br />
      <?php } ?>
    
  </div>
  <?php } ?>
  <div class="column">
    <h3><?php echo $text_service; ?></h3>
    
      <a href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a><br />
       <?php /* <a href="<?php echo $return; ?>"><?php echo $text_return; ?></a> */ ?><br />
      <a href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a><br />
    
  </div>
  <div class="column">
    <h3><?php echo $text_extra; ?></h3>
    
      <a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a><br />
      <a href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a><br />
      <a href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a><br />
      <a href="<?php echo $special; ?>"><?php echo $text_special; ?></a><br />
    
  </div>
  <div class="column">
    <h3><?php echo $text_account; ?></h3>
    
      <a href="<?php echo $account; ?>"><?php echo $text_account; ?></a><br />
      <a href="<?php echo $order; ?>"><?php echo $text_order; ?></a><br />
      <a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a><br />
      <a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a><br />
    
  </div>


<div id="powered"><?php echo $powered; ?></div></div>
</div>

<div class="footer-wrapper">
<div class="footer-top"></div>
	<div id="footer">
  <?php if ($informations) { ?>
  <div class="column">
    <h3 class="footer-info"><?php echo $text_information; ?></h3>
    
      <?php foreach ($informations as $information) { ?>
      <a class="footer-info-a" href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a><br /><br />
      <?php } ?>
    
  </div>
  <?php } ?>
  <div class="column">
    <h3 class="footer-service"><?php echo $text_service; ?></h3>
    
      <a class="footer-service-a" href="<?php echo $contact; ?>"><?php echo $text_contact; ?></a><br />
       <?php /* <a href="<?php echo $return; ?>"><?php echo $text_return; ?></a> */ ?><br /><br /><br />
      <a class="footer-service-a" href="<?php echo $sitemap; ?>"><?php echo $text_sitemap; ?></a><br />
    
  </div>
  <div class="column">
    <h3 class="footer-extra"><?php echo $text_extra; ?></h3>
    
      <a class="footer-extra-a" href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a><br /><br />
      <a class="footer-extra-a" href="<?php echo $voucher; ?>"><?php echo $text_voucher; ?></a><br /><br />
      <a class="footer-extra-a" href="<?php echo $affiliate; ?>"><?php echo $text_affiliate; ?></a><br /><br />
      <a class="footer-extra-a" href="<?php echo $special; ?>"><?php echo $text_special; ?></a><br />
    
  </div>
  <div class="column">
    <h3 class="footer-account"><?php echo $text_account; ?></h3>
    
      <a class="footer-account-a" href="<?php echo $account; ?>"><?php echo $text_account; ?></a><br /><br />
      <a class="footer-account-a" href="<?php echo $order; ?>"><?php echo $text_order; ?></a><br /><br />
      <a class="footer-account-a" href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a><br /><br />
      <a class="footer-account-a" href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a><br />
    
  </div>	
	</div>
</div>
<?= $yandexCounterCode ?>
</body></html>