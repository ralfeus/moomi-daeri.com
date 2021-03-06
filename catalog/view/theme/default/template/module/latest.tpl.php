<?php if ($products) { ?>
<div class="box-latest">
  <div class="box-heading">
    <?php echo $heading_title; ?>: 
    <?php $sumCategories = count($listCategories); $i = 0;?>
    <?php foreach ($listCategories as $cat) { ?>
        <img class="separator" src="image/data/separator.png">
            <span><a href="<?php echo $cat['href']; ?>"><?php echo $cat['text']; ?></a></span>
    <?php $i++; ?>
    <?php if ($i == $sumCategories) { ?>
        <img class="separator" src="image/data/separator.png">
    <?php }?>
    <?php } ?>
  </div>
  <div class="box-content">
    <div class="box-product">
      <?php foreach ($products as $product) { ?>
      <div>
        <?php if ($product['thumb']) { ?>
        <div class="image"><a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" /></a></div>
        <?php } ?>
        <div class="name">
            <a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
            <?php if ($product['hot']): ?>
                <span class="product-hot">NEW</span>
            <?php endif; ?>
        </div>
        <?php if ($product['price']) { ?>
        <div class="price">
          <?php if (!$product['special']) { ?>
          <?php echo $product['price']; ?>
          <?php } else { ?>
          <span class="price-old"><?php echo $product['price']; ?></span> <span class="price-new"><?php echo $product['special']; ?></span>
          <?php } ?>
          <?php if ($isSaler) { ?>
          <input type="checkbox" id="<?php echo $product['product_id'] ?>" class="latest_checkbox" />
           <?php } ?>
        </div>
        <?php } ?>
        <?php if ($product['rating']) { ?>
        <div class="rating"><img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" alt="<?php echo $product['reviews']; ?>" /></div>
        <?php } ?>
      </div>
      <?php } ?>
    </div>
    <div class="pagination"><?php echo $pagination; ?></div>
  </div>
</div>
<?php } ?>
