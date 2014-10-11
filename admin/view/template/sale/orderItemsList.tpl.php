<style>
tr.couponed td {
    background-color: rgb(255, 255, 203) !important;
}
</style>
<?= $header ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
    <?php } ?>
  </div>
<?php foreach ($notifications as $class => $notification)
    echo "<div class=\"$class\">$notification</div>";
?>
    <?php if ($error_warning) { ?>
  <div class="error"><?= $error_warning ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?= $success ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/order.png" alt="" /> <?= $heading_title ?></h1>
	  <div class="buttons">
          <?php foreach ($statuses[GROUP_ORDER_ITEM_STATUS] as $status):
            if ($status['settable']): ?>
                <a onclick="submitForm('<?= $status['set_status_url'] ?>');" class="button"><?= $status['name'] ?></a>
            <?php endif;
          endforeach; ?>
          <a onclick="submitForm('<?= $invoice ?>');" class="button"><?= $button_invoice ?></a>
		  <a onclick="$('#form').attr('target', '_blank'); submitForm('<?= $print ?>');" class="button"><?= $button_print ?></a>
          <a onclick="$('#form').attr('target', '_blank'); submitForm('<?= $printWithoutNick ?>');" class="button"><?= $textPrintWithoutNick ?></a>
	  </div>
    </div>
    <div class="content">
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
                <td style="text-align: center;"><input type="checkbox" onclick="selectAll(this);" /></td>
                <td class="right" style="width: 40px;">
                    <?php if ($sort == 'order_item_id')
                        $sort_class = 'class="' . strtolower($order) . '"';
                    else
                        $sort_class = ""; ?>
                    <a href="<?= $sort_order_item_id ?>" <?= $sort_class ?>><?= $column_order_item_id ?></a>
                </td>
				<td style="width: 40px;">
					<?php if ($sort == 'order_id')
						$sort_class = 'class="' . strtolower($order) . '"';
					else
						$sort_class = ''; ?>
					<a href="<?= $sort_order_id ?>" <?= $sort_class ?>><?= $field_order_id ?></a>
				</td>
                <td class="left"><?= "$column_customer_nick/<br />$column_customer" ?></td>
                <td class="right"><?= $column_item_image ?></td>
                <td class="right"><?= $column_item_name ?></td>
                <td class="left">
<?php $class = $sort == 'supplier_name' ? 'class="' . strtolower($order) . '"' : ''; ?>
                    <a href="<?= $sort_supplier ?>" <?= $class ?>"><?= $column_supplier ?></a>
                </td>
                <td class="right">
                    <?= $column_price ?>&nbsp;/
                    <?= $column_quantity ?>&nbsp;/
                    <?= $columnWeight ?>
                </td>
                <td class="left"><?= $column_status ?></td>
              <td class="left">Comment</td>
			  <td class="left">Action</td>
            </tr>
            <tr class="filter">
                <td />
                <td><input name="filterOrderItemId" value="<?= $filterOrderItemId ?>" onkeydown="filterKeyDown(event);" /></td>
                <td><input name="filterOrderId" value="<?= $filterOrderId ?>" onkeydown="filterKeyDown(event);" /></td>
                <td>
                    <select id="customer" name="filterCustomerId[]" multiple="true">
                        <?php foreach ($customers as $key => $value):
                            $selected = in_array($key, $filterCustomerId) ? 'selected' : '';
                        ?>
                            <option value="<?= $key ?>" <?= $selected ?> ><?= $value['nickname_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td />
                <td><input name="filterItem" value="<?= $filterItem ?>" onkeydown="filterKeyDown(event)" /></td>
                <td>
                    <select name="filterSupplierId[]" multiple="true">
                        <?php foreach ($suppliers as $key => $value):
                        $selected = in_array($key, $filterSupplierId) ? 'selected' : ''; ?>
                        <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td />
                <td>
                    <select name="filterStatusId[]" id="filterStatusId[]" multiple="true">
                        <optgroup label="Product orders">
                            <?php foreach ($statuses[GROUP_ORDER_ITEM_STATUS] as $status): ?>
                            <?php if (in_array($status['id'], $filterStatusId))
                                    $selected = "selected=\"selected\"";
                                else
                                    $selected = ""; ?>
                            <option value="<?= $status['id'] ?>" <?= $selected ?>><?= $status['name'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="Agent orders">
                            <?php foreach ($statuses[GROUP_REPURCHASE_ORDER_ITEM_STATUS] as $status): ?>
                            <?php if (in_array($status['id'], $filterStatusId))
                                    $selected = "selected=\"selected\"";
                                else
                                    $selected = ""; ?>
                            <option value="<?= $status['id'] ?>" <?= $selected ?>><?= $status['name'] ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </td>
                <td><input name="filterComment" value="<?= $filterComment ?>" onkeydown="filterKeyDown(event)" /></td>
                <td align="right"><a onclick="filter();" class="button"><?= $button_filter ?></a></td>
            </tr>
          </thead>
          <tbody>
<?php if ($order_items): ?>
    <?php foreach ($order_items as $order_item): ?>
					<tr <?php if ($order_item['cuoponed']) { ?> class="couponed" <?php } ?> >
                        <td style="text-align: center;">
                            <input type="checkbox" id="selectedItems[]" name="selectedItems[]" value="<?= $order_item['id'] ?>" />
                        </td>
                        <td class="right" ><?= $order_item['id'] ?></td>
						<td><a target="_blank" href="<?= $order_item['order_url'] ?>" <?php if($order_item['isOrderReady']) { echo 'class="boldRed"'; } ?>><?= $order_item['order_id'] ?></a></td>
                        <td class="left"><?= $order_item['customer_nick'] . "/<br />" . $order_item['customer_name'] ?></td>
                        <td class="right"><img src="<?= $order_item['image_path'] ?>" /></td>
                        <td class="left">
                            <table height="100%" width="100%" class="list">
                                <tr valign="center"><td><?= $order_item['model'] . "&nbsp;/&nbsp;" . $order_item['name'] ?></td></tr>
                                <tr><td><span style="color: red; "><?= $order_item['options'] ?></span></td></tr>
                                <tr valign="center"><td><?= $order_item['name_korean'] ?></td></tr>
                            </table>
                        </td>
					    <td class="left"><?= $order_item['supplier_name'] ?></td>
                        <td class="right">
                            <?= $order_item['price'] ?>
                            <input
                                alt="<?= $order_item['quantity'] ?>"
                                onblur="saveQuantity(<?= $order_item['id'] ?>, this)"
                                onkeydown="if (event.keyCode == 13) saveQuantity(<?= $order_item['id'] ?>, this)"
                                size="2"
                                value="<?= $order_item['quantity'] ?>" />
                            <?= $order_item['weight'] ?>
                        </td>
                        <td class="left"><?= $order_item['status'] ?></td>
                        <td class="left">
                            Private<br />
                            <input
                                    alt="<?= $order_item['comment'] ?>"
                                    onblur="saveComment(<?= $order_item['id'] ?>, this, true);"
                                    onkeydown="if (event.keyCode == 13) saveComment(<?= $order_item['id'] ?>, this, true);"
                                    value="<?= $order_item['comment'] ?>"/><br />
                            Public<br />
                            <input
                                    alt="<?= $order_item['publicComment'] ?>"
                                    onblur="saveComment(<?= $order_item['id'] ?>, this, false);"
                                    onkeydown="if (event.keyCode == 13) saveComment(<?= $order_item['id'] ?>, this, false);"
                                    value="<?= $order_item['publicComment'] ?>"/>

                        </td>
                        <td class="right">
                            <?php foreach ($order_item['action'] as $action):
                                if ($action['href']): ?>
                                    [<a href="<?= $action['href'] ?>" target="_blank"><?= $action['text'] ?></a>]
                                <?php else: ?>
                                    [<?= $action['text'] ?>]
                                <?php endif;
                            endforeach; ?>
                        </td>
					</tr>
    <?php endforeach; ?>
<?php else: ?>
				<tr>
				  <td class="center" colspan="11"><?= $text_no_results ?></td>
				</tr>
<?php endif; ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?= $pagination ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
	$('.date').datepicker({dateFormat: 'yy-mm-dd'});
    $("#filterStatusId\\[\\]").multiselect({
        noneSelectedText: "No filter",
        selectedList: 3
    });
    $('[name=filterCustomerId\\[\\]]')
            .multiselect({
                noneSelectedText: "No filter",
                selectedList: 1
            })
            .multiselectfilter();
    $('[name=filterSupplierId\\[\\]]')
            .multiselect({
                noneSelectedText: "No filter",
                selectedList: 1
            })
            .multiselectfilter();
    $('button.ui-multiselect').css('width', '110px');
});

function filter() {
    $('#form')
            .attr('action', 'index.php?route=sale/order_items&token=<?= $token ?>')
            .submit();
    return;
}

function filterKeyDown(e) {
    if (e.keyCode == 13)
        filter();
}

function saveComment(orderItemId, control, isPrivate) {
    if (control.value == control.alt)
        return;
    var tempHandler = control.onblur;
    control.onblur = null;
    $(control).effect("bounce");
    $.ajax({
        url: 'index.php?route=sale/order_items/save_comment',
        data: {
            token: '<?= $token ?>',
            orderItemId: orderItemId,
            comment: control.value,
            private: Number(isPrivate)
        },
        beforeSend: function() {
            $(control).after('<div class="wait"><img src="view/image/loading.gif" alt="" /></div>');
        },
        complete: function() {
            $('.wait').remove();
            control.onblur = tempHandler;
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("saveComment(): " + jqXHR.responseText);
        },
        success: function() {
                control.alt = control.value;
        }
    });
}

function saveQuantity(orderItemId, control) {
    if (control.value == control.alt)
        return;
    var tempHandler = control.onblur;
    control.onblur = null;
    $.ajax({
        url: 'index.php?route=sale/order_items/saveQuantity&token=<?= $token ?>&orderItemId=' + orderItemId + '&quantity=' + encodeURIComponent(control.value),
        beforeSend: function() {
            $(control).attr("disabled", true);
            $(control).after('<div class="wait"><img src="view/image/loading.gif" alt="" /></div>');
        },
        complete: function() {
            $(control).attr("disabled", false);
            $('.wait').remove();
            control.onblur = tempHandler;
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("saveQuantity(): " + jqXHR.responseText);
        },
        success: function() {
            control.alt = control.value;
        }
    });
}

function selectAll(control) {
	$('input[name*=\'selectedItems\']').attr('checked', control.checked);
}

function submitForm(action) {
    if ($('#selectedItems\\[\\]:checked').length != 0)
    {
        $('#form').attr('action', action);
        $('#form').submit();
    }
    else
        alert("<?= $text_no_selected_items ?>");
}

$(document).ready(function() {

    $('a.commissionAction').click(function() {
        eval($(this).attr('data-onclick'));
        return false;
    });

});

function commissionAction(action, id) {

	$.get(
		'index.php?route=sale/order_items/commission&token=<?= $token ?>&order_product_id=' + id + '&action=' + action,
		function(data) {
			if (typeof data.error !== "undefined") {
				alert(data.error);
			} else if (typeof data.success !== "undefined") {
				alert(data.success);
				$a = $('a[data-onclick="commissionAction(\'' + action + '\', ' + id + ')"]');
				$a.html(data.text);
				$a.attr('data-onclick', 'commissionAction(\'' + data.action + '\', ' + id + ')');
			}
		},
	'json');
	return false;

}

//--></script>
<?= $footer ?>