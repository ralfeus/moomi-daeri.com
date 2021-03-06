<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content" style="margin-left: 10px">
  <?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
    <?php } ?>
  </div>
  <h1 align="center"><?php echo $heading_title; ?>
    <?php if ($hot): ?>
      <span class="product-hot">NEW</span>
    <?php endif; ?>
  </h1>
  <div class="product-info">
    <div class="left">
      <div class="description">
        <?php if ($manufacturer) { ?>
          <span><?php echo $text_manufacturer; ?></span>
          <a href="<?php echo $manufacturers; ?>">
            <?php echo $manufacturer; ?>
          </a>
          <br />
        <?php } ?>
        <span><?php echo $text_model; ?></span>
        <b><?php echo $model; ?></b>
        <br />
        <div id="attribute"><?php echo "$textWeight: $weight"; ?></div>
        
      </div>
      <?php if ($price) { ?>
        <div class="price">
          <?php echo $text_price; ?>
          <?php if (!$special) { ?>
            <span class="price-to"><?php echo $price; ?></span>
          <?php } else { ?>
            <span class="price-old"><?php echo $price; ?></span>
            <span class="price-new">&nbsp;&nbsp;<?php echo $special; ?></span>
          <?php } ?>
          <br />
        </div>
      <?php } ?>
      <div class="cart">
        <div><?php echo $text_qty; ?>
          <input type="text" name="quantity" size="2" value="<?php echo $minimum; ?>" />
          <input type="hidden" name="product_id" size="2" value="<?php echo $product_id; ?>" />
          &nbsp;&nbsp;<a onclick="addToWishList('<?php echo $product_id; ?>');" class="button">
            <span><?php echo $button_wishlist; ?></span>
          </a>
          &nbsp;&nbsp;<a id="button-cart" class="button"><span><?php echo $button_cart; ?></span></a>
        </div>
        <?php if ($minimum > 1) { ?>
          <div class="minimum"><?php echo $text_minimum; ?></div>
        <?php } ?>
      </div>
      <?php if ($thumb || $images) { ?>
        <?php if ($thumb) { ?>
          <div class="image">
            <a href="<?php echo $popup; ?>" 
              title="<?php echo $heading_title; ?>" class="fancybox" 
              rel="fancybox">
              <img src="<?php echo $thumb; ?>" 
                title="<?php echo $heading_title; ?>" 
                alt="<?php echo $heading_title; ?>" 
                id="image" 
              />
            </a>
          </div>
        <?php } ?>
        <?php if ($images) { ?>
          <div class="image-additional">
            <?php foreach ($images as $image) { ?>
              <a href="<?php echo $image['popup']; ?>" 
                title="<?php echo $heading_title; ?>" class="fancybox" 
                rel="fancybox">
                <img src="<?php echo $image['thumb']; ?>" 
                  title="<?php echo $heading_title; ?>" 
                  alt="<?php echo $heading_title; ?>" 
                />
              </a>
            <?php } ?>
          </div>
        <?php } ?>
      <?php } ?>
    
    </div>
    <div class="right">
      <?php if ($options) { ?>
        <div class="options">
          <h2>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $text_option; ?></h2>
          <br />
          <?php foreach ($options as $option) { ?>
            <?php if ($option['type'] == 'select') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b>
                <br />
                <select name="option[<?php echo $option['product_option_id']; ?>]">
                  <option value=""><?php echo $text_select; ?></option>
                  <?php foreach ($option['option_value'] as $option_value) { ?>
                    <option value="<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
                      <?php if ($option_value['price']) { ?>
                        (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
                      <?php } ?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'radio') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b>
                <br />
                <?php foreach ($option['option_value'] as $option_value) { ?>
                  <input type="radio" 
                    name="option[<?php echo $option['product_option_id']; ?>]" 
                    value="<?php echo $option_value['product_option_value_id']; ?>" 
                    id="option-value-<?php echo $option_value['product_option_value_id']; ?>" 
                  />
                  <label for="option-value-<?php echo $option_value['product_option_value_id']; ?>">
                    <?php echo $option_value['name']; ?>
                    <?php if ($option_value['price']) { ?>
                      (<?php echo $option_value['price_prefix']; ?>
                      <?php echo $option_value['price']; ?>)
                    <?php } ?>
                  </label>
                  <br />
                <?php } ?>
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'checkbox') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b>
                <br />
                <?php foreach ($option['option_value'] as $option_value) { ?>
                  <input type="checkbox" 
                    name="option[<?php echo $option['product_option_id']; ?>][]" 
                    value="<?php echo $option_value['product_option_value_id']; ?>" 
                    id="option-value-<?php echo $option_value['product_option_value_id']; ?>" 
                  />
                  <label for="option-value-<?php echo $option_value['product_option_value_id']; ?>">
                    <?php echo $option_value['name']; ?>
                    <?php if ($option_value['price']) { ?>
                      (<?php echo $option_value['price_prefix']; ?>
                      <?php echo $option_value['price']; ?>)
                    <?php } ?>
                  </label>
                  <br />
                <?php } ?>
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'image') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b>
                <br />
                <table class="option-image">
                  <?php foreach ($option['option_value'] as $option_value) { ?>
                    <tr>
                      <td style="width: 1px;">
                        <input type="radio" 
                          name="option[<?php echo $option['product_option_id']; ?>]" 
                          value="<?php echo $option_value['product_option_value_id']; ?>" 
                          id="option-value-<?php echo $option_value['product_option_value_id']; ?>" 
                        />
                      </td>
                      <td>
                        <label for="option-value-<?php echo $option_value['product_option_value_id']; ?>">
                          <img src="<?php echo $option_value['image']; ?>" 
                            alt="<?php echo $option_value['name'] . ($option_value['price'] ? ' ' . $option_value['price_prefix'] . $option_value['price'] : ''); ?>" 
                          />
                        </label>
                      </td>
                      <td>
                        <label for="option-value-<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
                          <?php if ($option_value['price']) { ?>
                            (<?php echo $option_value['price_prefix']; ?>
                            <?php echo $option_value['price']; ?>)
                          <?php } ?>
                        </label>
                      </td>
                    </tr>
                  <?php } ?>
                </table>
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'text') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b><br />
                <input type="text" 
                  name="option[<?= $option['product_option_id'] ?>]" 
                  value="<?= $option['option_value'] ?>" 
                  size="100"
                />
              </div>
              <br />
            <?php } ?>
            
            <?php if ($option['type'] == 'file') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;" >
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b><br />
                <a id="button-option-<?php echo $option['product_option_id']; ?>" 
                  class="button">
                  <span><?php echo $button_upload; ?></span>
                </a>
                <input type="hidden" 
                  name="option[<?php echo $option['product_option_id']; ?>]" 
                  value="" 
                />
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'date') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b><br />
                <input type="text" 
                  name="option[<?php echo $option['product_option_id']; ?>]" 
                  value="<?php echo $option['option_value']; ?>" 
                  class="date" 
                />
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'datetime') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b><br />
                <input type="text" 
                  name="option[<?php echo $option['product_option_id']; ?>]" 
                  value="<?php echo $option['option_value']; ?>" 
                  class="datetime" 
                />
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'time') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option" style="margin-left: 20px;">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b><br />
                <input type="text" 
                  name="option[<?php echo $option['product_option_id']; ?>]" 
                  value="<?php echo $option['option_value']; ?>" 
                  class="time" 
                />
              </div>
              <br />
            <?php } ?>
            <?php if ($option['type'] == 'textarea') { ?>
              <div id="option-<?php echo $option['product_option_id']; ?>" class="option">
                <?php if ($option['required']) { ?>
                  <span class="required">*</span>
                <?php } ?>
                <b><?php echo $option['name']; ?>:</b><br />
                <textarea name="option[<?php echo $option['product_option_id']; ?>]" 
                  cols="40" 
                  rows="5"
                  style="width: 98%;">
                  <?php echo $option['option_value']; ?>
                </textarea>
              </div>
              <br />
            <?php } ?>
          <?php } ?>
        </div>
      <?php } ?>


  <div id="tabs" class="htabs">
    <a href="#tab-description"><?php echo $tab_description; ?></a>
    <?php if ($attribute_groups) { ?>
      <a href="#tab-attribute"><?php echo $tab_attribute; ?></a>
    <?php } ?>
    <?php if ($review_status) { ?>
      <a href="#tab-review"><?php echo $tab_review; ?></a>
    <?php } ?>
    <?php if ($products) { ?>
      <a href="#tab-related"><?php echo $tab_related; ?> 
        (<?php echo count($products); ?>)
      </a>
    <?php } ?>
  </div>
  <div id="tab-description" class="tab-content">
    <?php echo $description; ?>
  </div>
  <?php if ($review_status) { ?>
    <div id="tab-review" class="tab-content">
      <div id="review"></div>
      <h2 id="review-title"><?php echo $text_write; ?></h2>
      <b><?php echo $entry_name; ?></b><br />
      <input type="text" name="name" value="" />
      <br />
      <br />
      <b><?php echo $entry_review; ?></b>
      <textarea name="text" cols="40" rows="8" style="width: 98%;"></textarea>
      <span style="font-size: 11px;"><?= $text_note ?></span><br />
      <br />
      <table>
        <thead>
          <tr>
            <td>
              <?= $textAttachPicture ?><br />
              <input type="file" id="imageFile" name="imageFile" onchange="ajaxFileUpload(this)"/>
            </td>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>
              <input type="hidden" name="imageFilePath[]" />
            </td>
          </tr>
          <tr>
            <td>
              <input type="hidden" name="imageFilePath[]" />
            </td>
          </tr>
          <tr>
            <td>
              <input type="hidden" name="imageFilePath[]" />
            </td>
          </tr>
          <tr>
            <td>
              <input type="hidden" name="imageFilePath[]" />
            </td>
          </tr>
          <tr>
            <td>
              <input type="hidden" name="imageFilePath[]" />
            </td>
          </tr>
        </tbody>
      </table>
      <b><?php echo $entry_rating; ?></b> 
      <span><?php echo $entry_bad; ?></span>&nbsp;
      <input type="radio" name="rating" value="1" />&nbsp;
      <input type="radio" name="rating" value="2" />&nbsp;
      <input type="radio" name="rating" value="3" />&nbsp;
      <input type="radio" name="rating" value="4" />&nbsp;
      <input type="radio" name="rating" value="5" />&nbsp;
      <span><?php echo $entry_good; ?></span>
        
        <?php /*<br />
        <br />
        <b><?php echo $entry_captcha; ?></b><br />
        <input type="text" name="captcha" value="" />
        <br />
        <img src="index.php?route=product/product/captcha" alt="" id="captcha" /><br /> */ ?>
        
      <br />
      <div class="buttons">
        <div class="right">
          <a id="button-review" class="button">
            <span><?php echo $button_continue; ?></span>
          </a>
        </div>
      </div>
    </div>
  <?php } ?>
      <?php if ($review_status) { ?>
        <div class="review">
          <div>
            <img src="catalog/view/theme/default/image/stars-<?php echo $rating; ?>.png" alt="<?php echo $reviews; ?>" />
            &nbsp;&nbsp;
            <a onclick="$('a[href=\'#tab-review\']').trigger('click');">
              <?php echo $reviews; ?>
            </a>
            &nbsp;&nbsp;|&nbsp;&nbsp;
            <a onclick="$('a[href=\'#tab-review\']').trigger('click');">
              <?php echo $text_write; ?>
            </a>
          </div>
          <div class="share"><!-- AddThis Button BEGIN -->
            <div class="addthis_default_style">
              <a class="addthis_button_compact">
                <?php echo $text_share; ?>
              </a> 
              <a class="addthis_button_email"></a>
              <a class="addthis_button_print"></a> 
              <a class="addthis_button_facebook"></a> 
              <a class="addthis_button_twitter"></a>
            </div>
            <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js"></script>
                <!-- AddThis Button END -->
          </div>
        </div>
      <?php } ?>

    </div>
  </div>
      <div id="image_description" style="border: 0px; text-align: center;" class="tab-content">
    <?php echo $image_description; ?>
  </div>


  <?php if ($products) { ?>
    <div id="tab-related" class="tab-content">
      <div class="box-product">
        <?php foreach ($products as $product) { ?>
          <div>
            <?php if ($product['thumb']) { ?>
              <div class="image">
                <a href="<?php echo $product['href']; ?>">
                  <img src="<?php echo $product['thumb']; ?>" 
                    alt="<?php echo $product['name']; ?>" 
                  />
                </a>
              </div>
            <?php } ?>
            <div class="name">
              <a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
            </div>
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
              <div class="rating">
                <img src="catalog/view/theme/default/image/stars-<?php echo $product['rating']; ?>.png" 
                  alt="<?php echo $product['reviews']; ?>" 
                />
              </div>
            <?php } ?>
            <a onclick="addToCart('<?php echo $product['product_id']; ?>');" class="button">
              <span><?php echo $button_cart; ?></span>
            </a>
          </div>
        <?php } ?>
      </div>
    </div>
  <?php } ?>
  <?php if ($tags) { ?>
    <div class="tags"><b><?php echo $text_tags; ?></b>
      <?php foreach ($tags as $tag) { ?>
        <a href="<?php echo $tag['href']; ?>"><?php echo $tag['tag']; ?></a>,
      <?php } ?>
    </div>
  <?php } ?>
  <?php echo $content_bottom; ?>
</div>

<script type="text/javascript"><!--
$('.fancybox').fancybox({cyclic: true});
//--></script>

<script type="text/javascript"><!--
$('#button-cart').bind('click', function() {
    $.ajax({
        url: 'index.php?route=checkout/cart/update',
        type: 'post',
        data: $('.product-info input[type=\'text\'], .product-info input[type=\'hidden\'], .product-info input[type=\'radio\']:checked, .product-info input[type=\'checkbox\']:checked, .product-info select, .product-info textarea'),
        dataType: 'json',
        success: function(json) {
            $('.success, .warning, .attention, .information, .error').remove();

            if (json['error']) {
                if (json['error']['warning']) {
                    $('#notification').html('<div class="error" style="display: none;">' + json['error']['warning'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');

                    $('.warning').fadeIn('fast');
                }

                for (i in json['error']) {
                    $('#option-' + i).after('<span class="error">' + json['error'][i] + '</span>');
                }
            }

			if (json['success']) {
				$('#cart-success').after('<div class="success" style="display: none;">' + json['success'] + '</div>');
				
				$('.success').fadeIn(1000).delay(2000).fadeOut(1500);

				$('#cart_total_data').html(json['total_data']);
            }
        }
    });
});
//--></script>

<?php if ($options) { ?>
  <script type="text/javascript" src="catalog/view/javascript/jquery/ajaxupload.js"></script>
  <script src="catalog/view/javascript/ajaxfileupload.js" type="text/javascript"></script>
  <?php foreach ($options as $option) { ?>
    <?php if ($option['type'] == 'file') { ?>
      <script type="text/javascript"><!--
        new AjaxUpload('#button-option-<?php echo $option['product_option_id']; ?>', {
          action: 'index.php?route=product/product/upload',
          name: 'file',
          autoSubmit: true,
          responseType: 'json',
          onSubmit: function(file, extension) {
            $('#button-option-<?php echo $option['product_option_id']; ?>').after('<img src="catalog/view/theme/default/image/loading.gif" class="loading" style="padding-left: 5px;" />');
          },
          onComplete: function(file, json) {
            $('.error').remove();

            if (json.success) {
              alert(json.success);

              $('input[name=\'option[<?php echo $option['product_option_id']; ?>]\']').attr('value', json.file);
            }

            if (json.error) {
              $('#option-<?php echo $option['product_option_id']; ?>').after('<span class="error">' + json.error + '</span>');
            }

            $('.loading').remove();
          }
        });
//--> </script>
    <?php } ?>
  <?php } ?>
<?php } ?>
<script type="text/javascript"><!--
$('#review .pagination a').live('click', function() {
    $('#review').slideUp('slow');
    $('#review').load(this.href);
    $('#review').slideDown('slow');

    return false;
});

$('#review').load('index.php?route=product/product/review&product_id=<?php echo $product_id; ?>');

$('#button-review').bind('click', function() {
    var counter = 0;
    var args = {
        name: $('input[name=\'name\']').val(),
        text: $('textarea[name=\'text\']').val(),
        rating: $('input[name=\'rating\']:checked').val() ? $('input[name=\'rating\']:checked').val() : '',
        captcha: $('input[name=\'captcha\']').val()
    };
    $('[name=\'imageFilePath[]\'][value]').each(function(){
        args['imageFilePath[' + counter++ + ']'] = this.value;
    });
    $.ajax({
        type: 'POST',
        url: 'index.php?route=product/product/write&product_id=<?php echo $product_id; ?>',
        dataType: 'json',
        data: args,
        beforeSend: function() {
            $('.success, .warning').remove();
            $('#button-review').attr('disabled', true);
            $('#review-title').after('<div class="warning"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
        },
        complete: function() {
            $('#button-review').attr('disabled', false);
            $('.warning').remove();
        },
        success: function(data) {
            if (data.error) {
                $('#review-title').after('<div class="error">' + data.error + '</div>');
            }

            if (data.success) {
                $('#review-title').after('<div class="success">' + data.success + '</div>');

                $('input[name=\'name\']').val('');
                $('textarea[name=\'text\']').val('');
                $('input[name=\'rating\']:checked').attr('checked', '');
                $('input[name=\'captcha\']').val('');
            }
        }
    });
});
//--></script>
<script type="text/javascript"><!--
$('#tabs a').tabs();
//--></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript"><!--
if ($.browser.msie && $.browser.version == 6) {
    $('.date, .datetime, .time').bgIframe();
}

$('.date').datepicker({dateFormat: 'yy-mm-dd'});
$('.datetime').datetimepicker({
    dateFormat: 'yy-mm-dd',
    timeFormat: 'h:m'
});
$('.time').timepicker({timeFormat: 'h:m'});

function ajaxFileUpload(fileObject)
{
    var orderElement = fileObject.parentElement;
    $.ajaxFileUpload({
        url:'index.php?route=product/product/uploadImage',
        secureuri: false,
        fileElementId: fileObject.id,
        dataType: 'json',
        success: function (data, status) {
            if(typeof(data.error) != 'undefined')
            {
                if(data.error != '')
                {
                    alert(data.error);
                }else
                {
                    alert(data.msg);
                }
            }
            var firstEmptySlot = $('input[name=\'imageFilePath[]\']:not([value]):first');
            firstEmptySlot.val(data['filePath']);
            firstEmptySlot.next().attr('src', '<?= HTTP_IMAGE ?>' + data['filePath']);
            if ($('input[name=\'imageFilePath[]\']:not([value])').length == 0)
                $('input[type=file]').attr('disabled', true);
        },
        error: function (data, status, e) {
            alert(e);
        }
    });

    return false;
}
//--></script>
<?php echo $footer; ?>