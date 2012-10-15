<table class="list">
	<tr>
		<td><input type="radio" name="order_to_add" value="0" checked="true" /></td>
		<td colspan="2"><?= $labels['new_order'] ?></td>
	</tr>
	<?php if (isset($open_orders)):	?>
		<?php foreach ($open_orders as $order): ?>
			<tr>
				<td><input type="radio" name="order_to_add" value="<?= $order['order_id'] ?>" /></td>
				<td valign="top">
					<b><?= $labels['order_id'] ?>:</b>&nbsp;#<?= $order['order_id'] ?><br />
					<b><?= $labels['date_added'] ?>:</b>&nbsp;<?= $order['date_added'] ?><br />
                    <b><?= $textOrderTotal ?>:</b>&nbsp;<?= $order['orderTotal'] ?>
				</td>
				<td valign="top">
					<b><?= $labels['shipping_method'] ?>:</b>&nbsp;<?= $order['shipping_method'] ?><br />
					<b><?= $labels['shipping_address'] ?></b><br /><?= $order['shipping_address'] ?>
				</td>				
			</tr>
		<?php endforeach; ?>
	<?php endif; ?>
</table>
<div class="buttons">
  <div class="right"><a id="button-options" class="button"><span><?= $labels['button_continue'] ?></span></a></div>
</div>