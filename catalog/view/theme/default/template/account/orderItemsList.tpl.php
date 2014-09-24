<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="error"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="catalog/view/theme/default/image/order.png" alt="" /> <?php echo $headingTitle; ?></h1>
	</div>
    </div>
    <div class="content">
      <form action="<?= $urlFormAction ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
                <td style="width: 1px;"><input type="checkbox" onclick="selectAll(this);" /></td>
                <td style="width: 1px;">
                    <?php if ($sort == 'order_item_id')
                        $sort_class = 'class="' . strtolower($order) . '"';
                    else
                        $sort_class = ""; ?>
                    <a href="<?php echo $sort_order_item_id; ?>" <?php echo $sort_class; ?>><?php echo $textOrderItemId; ?></a>
                </td>
				<td style="width: 1px;">
					<?php if ($sort == 'order_id')
						$sort_class = 'class="' . strtolower($order) . '"';
					else
						$sort_class = ''; ?>
					<a href="<?= $sort_order_id ?>" <?= $sort_class ?>><?= $textOrderId ?></a>
				</td>
                <td style="width: 300px;"><?= $textItem ?></td>
                <td style="width: 1px;"><?= $textPrice ?> / <?= $textQuantity ?></td>
                <td style="width: 1px;"><?= $textStatus ?></td>
                <td>Comment</td>
			    <td>Action</td>
            </tr>
            <tr class="filter">
                <td />
                <td><input name="filterOrderItemId" value="<?= $filterOrderItemId ?>" onkeydown="filterKeyDown(event);" /></td>
                <td><input name="filterOrderId" value="<?= $filterOrderId ?>" onkeydown="filterKeyDown(event);" /></td>
                <td><input name="filterItem" value="<?= $filterItem ?>" onkeydown="filterKeyDown(event)" /></td>
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
                <td />
                <td align="right"><a onclick="filter();" class="button"><span><?php echo $button_filter; ?></span></a></td>
            </tr>
          </thead>
          <tbody>
<?php if ($order_items): ?>
    <?php foreach ($order_items as $order_item): ?>
					<tr>
                        <td style="text-align: center;">
                            <input type="checkbox" id="selectedItems[]" name="selectedItems[]" value="<?php echo $order_item['id']; ?>" />
                        </td>
                        <td><?php echo $order_item['id']; ?></td>
						<td><a href="<?= $order_item['order_url'] ?>"><?= $order_item['order_id'] ?></a></td>
                        <td>
                            <table class="layout">
                                <tr valign="center">
                                    <td rowspan="3"><img src="<?php echo $order_item['image_path']; ?>" /></td>
                                    <td><?php echo $order_item['model'] . "&nbsp;/&nbsp;" . $order_item['name']; ?></td>
                                </tr>
                                <tr><td><span style="color: red; "><?php echo $order_item['options']; ?></span></td></tr>
                                <tr valign="center"><td><?php echo $order_item['name_korean']; ?></td></tr>
                            </table>
                        </td>
                        <td><?= $order_item['price'] ?> / <?= $order_item['quantity'] ?></td>
                        <td><?php echo $order_item['status']; ?></td>
                        <td>
                            <textarea
                                    alt="<?= $order_item['publicComment'] ?>"
                                    onblur="saveComment(<?= $order_item['id'] ?>, this, false);"
                                    style="width: 100%; height: 100%;"><?= $order_item['publicComment'] ?></textarea>


                        </td>
                        <td>
        <?php foreach ($order_item['action'] as $action):
            if ($action['href']): ?>
                                    [<a href="<?= $action['href'] ?>" target="_blank"><?= $action['text'] ?></a>]
            <?php else: ?>
                                    [<?= $action['text'] ?>]
            <?php endif;
        endforeach; ?>
                        </td>
					</tr>
    <?php endforeach;
else: ?>
				<tr>
				  <td class="center" colspan="11"><?php echo $text_no_results; ?></td>
				</tr>
<?php endif; ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
	$('.date').datepicker({dateFormat: 'yy-mm-dd'});
    $("#filterStatusId\\[\\]").multiselect({
        noneSelectedText: "-----------",
        selectedList: 1
    });
    $('button.ui-multiselect').css('width', '90px');
});

function filter() {
    $('#form').submit();
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
        url: 'index.php?route=account/orderItems/saveComment',
        data: {
            orderItemId: orderItemId,
            comment: control.value,
            private: isPrivate
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

function selectAll(control)
{
	$('input[name*=\'selectedItems\']').attr('checked', control.checked);
}

function submitForm(action)
{
    if ($('#selectedItems\\[\\]:checked').length != 0)
    {
        $('#form').attr('action', action);
        $('#form').submit();
    }
    else
        alert("<?= $text_no_selected_items ?>");
}
//--></script> 
<?php echo $footer; ?>