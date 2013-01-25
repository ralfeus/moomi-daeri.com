<script type="text/javascript">
  $(document).ready(function() {
    $('#mycarousel2').jcarousel({
      start: 0,
      scroll: 1,
      visible: 5,
      auto: 2,
      wrap: "last",
      itemFallbackDimension: 0,
      initCallback: removeButton,
    });
    jQuery('.nailthumb-container').nailthumb({width:180,height:180});
  });
  function removeButton() {
    $('.jcarousel-prev-horizontal').css('top', '90px');
    $('.jcarousel-next-horizontal').css('top', '90px');
  }
</script>
<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
    <div class="box-product">
      <ul id='mycarousel2' class='jcarousel-skin-tango'>
      <?php foreach ($products as $product) { ?>
      <li>
        <?php if ($product['thumb']) { ?>
        <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
        <?php } ?>
        <div class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a></div>
        <?php if ($product['price']) { ?>
        <div class="price">
          <?php if (!$product['special']) { ?>
          <?php echo $product['price']; ?>
          <?php } else { ?>
          <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
          <?php } ?>
        </div>
        <?php } ?>
        <?php if ($product['rating']) { ?>
        <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
        <?php } ?>
        <!--div class="cart"><a onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button"><span><?php echo $button_cart; ?></span></a></div-->
      </li>
      <?php } ?>
      </ul>
    </div>
  </div>
</div>
