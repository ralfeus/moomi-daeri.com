<?= $labels['text_add_to_order'] ?>
<div class="checkout-product">
  <table>
    <thead>
      <tr>
        <td class="name"><?= $labels['field_name'] ?></td>
        <td class="model"><?= $labels['field_model'] ?></td>
        <td class="quantity"><?= $labels['field_quantity'] ?></td>
        <td class="price"><?= $labels['field_price'] ?></td>
        <td class="total"><?= $labels['field_total'] ?></td>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($order_items as $order_item): ?>
		<tr>
			<td class="name"><a href="<?= $order_item['href'] ?>"><?= $order_item['name'] ?></a>
				<?php foreach ($order_item['options'] as $option): ?>
					<br />
					&nbsp;<small> - <?= $option['name'] ?>:&nbsp;<?= $option['option_value'] ?></small>
				<?php endforeach; ?>
			</td>
			<td class="model"><?php echo $order_item['model']; ?></td>
			<td class="quantity"><?php echo $order_item['quantity']; ?></td>
			<td class="price"><?php echo $order_item['price']; ?></td>
			<td class="total"><?php echo $order_item['total']; ?></td>
		</tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<div class="buttons">
  <div class="right"><a id="buttonConfirm" class="button" onclick="confirm()"><span><?= $labels['button_continue'] ?></span></a></div>
</div>
<script type="text/javascript">//<!--
function confirm() {
	$.ajax({ 
		url: '<?= preg_replace("/&amp;/", "&", $urlConfirm) ?>',
        beforeSend: function() {
            $('#buttonConfirm').attr('disabled', true);
            $('#buttonConfirm').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        done: function() {
            $('.wait').remove();
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(xhr.responseText);
        },
		success: function() {
			location = '<?= $urlSuccess ?>';
		}	
	}); 
}
//--></script> 