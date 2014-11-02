<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($thumb || $description) { ?>
  <div class="category-info">
    <?php if ($thumb) { ?>
    <div class="image"><img src="<?php echo $thumb; ?>" alt="<?php echo $heading_title; ?>" /></div>
    <?php } ?>
    <?php if ($description) { ?>
    <?php echo $description; ?>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($categories) { ?>
  <h2><?php echo $text_refine; ?></h2>
  <div class="category-list">
    <?php if (count($categories) <= 5) { ?>
    <ul>
      <?php foreach ($categories as $category) { ?>
      <li><a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a></li>
      <?php } ?>
    </ul>
    <?php } else { ?>
    <?php for ($i = 0; $i < count($categories);) { ?>
    <ul>
      <?php $j = $i + ceil(count($categories) / 4); ?>
      <?php for (; $i < $j; $i++) { ?>
      <?php if (isset($categories[$i])) { ?>
      <li><a href="<?php echo $categories[$i]['href']; ?>"><?php echo $categories[$i]['name']; ?></a></li>
      <?php } ?>
      <?php } ?>
    </ul>
    <?php } ?>
    <?php } ?>
  </div>
  <?php } ?>
  <?php if ($products) { ?>
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
  <div class="product-filter">
    <div class="limit"><b><?php echo $text_limit; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($limits as $limits) { ?>
        <?php if ($limits['value'] == $limit) { ?>
        <option value="<?php echo $limits['href']; ?>" selected="selected"><?php echo $limits['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $limits['href']; ?>"><?php echo $limits['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
    <div class="sort"><b><?php echo $text_sort; ?></b>
      <select onchange="location = this.value;">
        <?php foreach ($sorts as $sorts) { ?>
        <?php if ($sorts['value'] == $sort . '-' . $order) { ?>
        <option value="<?php echo $sorts['href']; ?>" selected="selected"><?php echo $sorts['text']; ?></option>
        <?php } else { ?>
        <option value="<?php echo $sorts['href']; ?>"><?php echo $sorts['text']; ?></option>
        <?php } ?>
        <?php } ?>
      </select>
    </div>
    <div class="clear"></div>
    <?php if ($isSaler && $showDownload) { ?>
    <div class="percent100Right">
      <a class="button" onclick="downloadImages()">
        <span><?php echo $text_button_download ?></span>
      </a>
    </div>
    <?php } ?>
  </div>
  <div class="product-grid">
    <br />
    <?php foreach ($products as $product) { ?>
      <div>
        <?php if ($product['thumb']) { ?>
          <div class="image">
            <a href="<?php echo $product['href']; ?>">
              <img src="<?php echo $product['thumb']; ?>" 
                title="<?php echo $product['name']; ?>" 
                alt="<?php echo $product['name']; ?>" 
              />
            </a>
          </div>
        <?php } ?>
        <div class="name">
		      <a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
		      <?php if ($product['hot']): ?>
            <br />
            <span class="product-hot">NEW</span>
          <?php endif; ?>
        </div>
        <div class="description"><?php echo $product['description']; ?></div>
        <?php if ($product['price']) { ?>
          <div class="price">
            <?php if (!$product['special']) { ?>
              <?php echo $product['price']; ?>
            <?php } else { ?>
              <span class="price-old">
                <?php echo $product['price']; ?>
              </span>
              <span class="price-new">
                <?php echo $product['special']; ?>
              </span>
            <?php } ?>
          </div>
        <?php } ?>
        <?php if ($product['isSaler']) { ?>
          <div class="checkbox">
            <input type="checkbox" id="<?php echo $product['product_id'] ?>" class="latest_checkbox" />
          </div>
        <?php } ?>
        <?php if ($product['rating']) { ?>
          <div class="rating">
            <img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" 
              alt="<?php echo $product['reviews']; ?>" 
            />
          </div>
        <?php } ?>
      </div>
    <?php } ?>
  </div>
  <div class="pagination"><?php echo $pagination; ?></div>
  </div>
  <?php } ?>
  <?php if (!$categories && !$products) { ?>
  <div class="content"><?php echo $text_empty; ?></div>
  <div class="buttons">
    <div class="right">
      <a href="<?php echo $continue; ?>" class="button"><span><?php echo $button_continue; ?></span></a>
    </div>
  </div>
  <?php } ?>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>