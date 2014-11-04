<?php echo $header; ?>
<div class="container"><?php echo $column_left; ?><?php echo $column_right; ?>
  <div id="content"><?php echo $content_top; ?>
    <div class="breadcrumb">
      <?php foreach ($breadcrumbs as $breadcrumb) { ?>
      <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
      <?php } ?>
    </div>
    <h1><?php echo $heading_title; ?>
      <?php if ($weight) { ?>
      &nbsp;(<?php echo $weight; ?>)
      <?php } ?>
    </h1>
    <?php if ($attention) { ?>
    <div class="error"><?php echo $attention; ?></div>
    <?php } ?>    
    <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>
    <?php if ($error_warning) { ?>
    <div class="error"><?php echo $error_warning; ?></div>
    <?php } ?>
    <div class="buttons">
      <div class="left"><a onclick="$('#basket').submit();" class="button"><span><?php echo $button_update; ?></span></a></div>
      <div class="right"><a class="button" onclick="checkout()"><span><?php echo $button_checkout; ?></span></a></div>
      <!--div class="right"><a href="<?= $urlCheckoutSelected ?>" class="button"><span><?php echo $textCheckoutSelected; ?></span></a></div-->
      <div class="center"><a href="<?php echo $continue; ?>" class="button"><span><?php echo $button_shopping; ?></span></a></div>
    </div>
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="basket">
      <div class="cart-info">
        <table>
          <thead>
            <tr>
              <td class="remove"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);"/></td>
              <td class="image"><?php echo $column_image; ?></td>
              <td class="name"><?php echo $column_name; ?></td>
              <td class="model"><?php echo $column_model; ?></td>
              <td class="quantity"><?php echo $column_quantity; ?></td>
              <td class="price"><?php echo $column_price; ?></td>
              <td class="total"><?php echo $column_total; ?></td>
            </tr>
          </thead>
          <tbody>
<?php $currentSupplierId = null;
foreach ($products as $product):
    if ($product['supplierId'] != $currentSupplierId):
        $currentSupplierId = $product['supplierId']; ?>
            <tr><td colspan="7"><?= $textBrand ?>: <?= $suppliers[$currentSupplierId]['name'] ?></td></tr>
            <tr>
                <td colspan="6"><?= $suppliers[$currentSupplierId]['textShippingCost'] ?></td>
                <td colspan="1"><?= $suppliers[$currentSupplierId]['shippingCostFormatted'] ?></td>
            </tr>
    <?php endif; ?>
            <tr>
              <td class="checkbox"><input type="checkbox" name="selected[]" value="<?= $product['key'] ?>"/></td>
              <td class="image">
                <?php if ($product['thumb']) { ?>
                  <a href="<?php echo $product['href']; ?>"><img src="<?php echo $product['thumb']; ?>" alt="<?php echo $product['name']; ?>" title="<?php echo $product['name']; ?>" /></a>
                <?php } ?>
              </td>
              <td class="name"><a href="<?php echo $product['href']; ?>"><?php echo $product['name']; ?></a>
                <?php if (!$product['stock']) { ?>
                <span class="stock">***</span>
                <?php } ?>
                <div>
                  <?php foreach ($product['option'] as $option) { ?>
                  - <small><?php echo $option['name']; ?>: <?php echo $option['value']; ?></small><br />
                  <?php } ?>
                </div>
                <?php if ($product['reward']) { ?>
                <small><?php echo $product['reward']; ?></small>
                <?php } ?></td>
              <td class="model"><?php echo $product['model']; ?></td>
              <td class="quantity"><input type="text" name="quantity[<?php echo $product['key']; ?>]" value="<?php echo $product['quantity']; ?>" size="3" />
                &nbsp;
                <input type="image" src="catalog/view/theme/default/image/update.png" alt="<?php echo $button_update; ?>" title="<?php echo $button_update; ?>" />
                &nbsp;
                <a href="<?php echo $product['remove']; ?>">
                <img src="catalog/view/theme/default/image/remove.png" alt="<?php echo $button_remove; ?>" title="<?php echo $button_remove; ?>" />
                </a>
              </td>
              <td class="price"><?php echo $product['price']; ?></td>
              <td class="total"><?php echo $product['total']; ?></td>
            </tr>
<?php endforeach; ?>
<?php foreach ($vouchers as $vouchers): ?>
            <tr>
              <td class="image"></td>
              <td class="name"><?php echo $vouchers['description']; ?></td>
              <td class="model"></td>
              <td class="quantity"><input type="text" name="" value="1" size="1" disabled="disabled" />
              &nbsp;<a href="<?php echo $vouchers['remove']; ?>"><img src="catalog/view/theme/default/image/remove.png" alt="<?php echo $button_remove; ?>" title="<?php echo $button_remove; ?>" /></a></td>
              <td class="price"><?php echo $vouchers['amount']; ?></td>
              <td class="total"><?php echo $vouchers['amount']; ?></td>
            </tr>
<?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </form>
    <div class="cart-module">
      <?php foreach ($modules as $module) { ?>
      <?php echo $module; ?>
      <?php } ?>
    </div>
    <div class="cart-total">
      <table>
        <?php foreach ($totals as $total) { ?>
        <tr>
          <td colspan="5"></td>
          <td class="right"><b><?php echo $total['title']; ?>:</b></td>
          <td class="right"><?php echo $total['text']; ?></td>
        </tr>
        <?php } ?>
      </table>
    </div>
    <?php echo $content_bottom; ?></div>
</div>
<script type="text/javascript"><!--
$('.cart-module .cart-heading').bind('click', function() {
	if ($(this).hasClass('active')) {
		$(this).removeClass('active');
	} else {
		$(this).addClass('active');
	}
		
	$(this).parent().find('.cart-content').slideToggle('slow');
});

function checkout()
{
    $('#basket').attr('action', '<?= $urlCheckout ?>');
    $('#basket').submit();
}
//--></script> 
<?php echo $footer; ?>