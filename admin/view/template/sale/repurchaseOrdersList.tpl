<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?= $breadcrumb['separator'] ?><a href="<?php echo $breadcrumb['href']; ?>"><?= $breadcrumb['text'] ?></a>
        <?php endforeach; ?>
    </div>
    <h1><?php echo $headingTitle; ?></h1>
    <?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">" . nl2br(print_r($notification, true)) . "</div>";
    ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/order.png" alt="" /><?= $headingTitle ?></h1>
            <div class="buttons">
                <?php foreach ($statuses as $status): ?>
                <a onclick="submitStatus('<?= $status['statusId'] ?>');" class="button"><?= $status['name'] ?></a>
                <?php endforeach; ?>
                <a onclick="submitForm('<?= $invoiceUrl ?>');" class="button"><?= $textInvoice ?></a>
                <a onclick="$('#form').attr('target', '_blank'); submitForm('<?= $print ?>');" class="button"><?= $textPrint ?></a>
            </div>
        </div>
        <form action="" id="form" method="post" enctype="multipart/form-data">
            <table class="list">
                <thead>
                    <tr>
                        <td style="text-align: center;"><input type="checkbox" onclick="selectAll(this);" /></td>
                        <td><?= $textOrderId ?></td>
                        <td><?= $textItemImage ?></td>
                        <td><?= $textCustomer ?></td>
                        <td><?= $textSiteName ?></td>
                        <td><?= $textWhoOrders ?></td>
                        <td><?= $textQuantity ?></td>
                        <td><?= $textAmount ?> (<?= $currencyCode ?>)</td>
                        <td><?= $textStatus ?></td>
                        <td style="width: auto;" "><?= $textComment ?></td>
                        <td><?= $textActions ?></td>
                    </tr>
                    <tr class="filter">
                        <td />
                        <td><input name="filterOrderId" value="<?= $filterOrderId ?>" size="3" onkeydown="filterKeyDown(event);" /></td>
                        <td />
                        <td>
                            <select name="filterCustomerId[]" multiple="true">
                                <?php foreach ($customers as $customer):
                            if (in_array($customer['id'], $filterCustomerId))
                                $selected = "selected=\"selected\"";
                            else
                                $selected = ""; ?>
                                <option value="<?= $customer['id'] ?>" <?= $selected ?>><?= $customer['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input name="filterSiteName" value="<?= $filterSiteName ?>" onkeydown="filterKeyDown(event)" /></td>
                        <td>
                            <select name="filterWhoOrders" multiple="true">
                                <option
                                    <?= $filterWhoOrders ? "" : "selected" ?>
                                    value="">-- No filter --</option>
                                <option
                                    <?= $filterWhoOrders == REPURCHASE_ORDER_CUSTOMER_BUYS_OPTION_VALUE_ID ? "selected" : ""?>
                                    value="<?= REPURCHASE_ORDER_CUSTOMER_BUYS_OPTION_VALUE_ID ?>">Customer</option>
                                <option
                                    <?= $filterWhoOrders == REPURCHASE_ORDER_SHOP_BUYS_OPTION_VALUE_ID ? "selected" : ""?>
                                    value="<?= REPURCHASE_ORDER_SHOP_BUYS_OPTION_VALUE_ID ?>">Shop</option>
                            </select>
                        </td>
                        <td />
                        <td><input name="filterAmount"  size="9" value="<?= $filterAmount; ?>" onkeydown="filterKeyDown(event);"/></td>
                        <td>
                            <select name="filterStatusId[]" multiple="true">
                                <?php foreach ($statuses as $status):
                                    if (in_array($status['statusId'], $filterStatusId))
                                        $selected = "selected=\"selected\"";
                                    else
                                        $selected = ""; ?>
                                    <option value="<?= $status['statusId'] ?>" <?= $selected ?>><?= $status['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td />
                        <td align="right"><a onclick="filter();" class="button"><?= $textFilter; ?></a></td>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td style="text-align: center;">
                                <input
                                        type="checkbox"
                                        id="selectedItems[]"
                                        name="selectedItems[]"
                                        value="<?= $order['orderId'] ?>" />
                                        <!--?= $order['selected'] ?-->
                            </td>
                            <td><?= $order['orderId'] ?></td>
                            <td>
                                <?php if ($order['originalImagePath']):
                                    $href = $order['originalImagePath'];
                                else:
                                    $href = $order['itemUrl'];
                                endif; ?>
                                <a href="<?= $href ?>" target="_blank"><img src="<?= $order['imagePath'] ?>" /></a>
                            </td>
                            <td style="white-space: nowrap;"><a href="<?= $order['customerUrl'] ?>"><?= $order['customerNick'] ?></a></td>
                            <td><a href="<?= $order['itemUrl'] ?>"><?= $order['siteName'] ?></a></td>
                            <td><?= $order['whoOrders'] ?></td>
                            <td><?= $order['quantity'] ?></td>
                            <td>
                                <input
                                    type="text"
                                    onkeydown="amountKeyDown(event, this, <?= $order['orderId'] ?>)"
                                    size = "9"
                                    value="<?= $order['amount'] ?>"
                                />
                            </td>
                            <td id="status"><?= $order['status'] ?></td>
                            <td>
                                Private<br />
                                <input
                                        alt="<?php echo $order['comment']; ?>"
                                        onblur="saveComment(<?= $order['orderId'] ?>, this, true);"
                                        onkeydown="if (event.keyCode == 13) saveComment(<?= $order['orderId'] ?>, this, true);"
                                        value="<?php echo $order['comment']; ?>"/><br />
                                Public<br />
                                <input
                                        alt="<?php echo $order['publicComment']; ?>"
                                        onblur="saveComment(<?= $order['orderId'] ?>, this, false);"
                                        onkeydown="if (event.keyCode == 13) saveComment(<?= $order['orderId'] ?>, this, false);"
                                        value="<?php echo $order['publicComment']; ?>"/><br />
                                <span style="color: red; "><?= $order['options'] ?></span>
                            </td>
                            <td>
                                <?php foreach ($order['actions'] as $action): ?>
                                    [&nbsp;<a href="<?= $action['href'] ?>" target="_blank"><?= preg_replace('/\s/', '&nbsp;', $action['text']) ?></a>&nbsp;]
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<script type="text/javascript">//<!--
$(document).ready(function() {
    $('[name=filterCustomerId\\[\\]]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();

    $('[name=filterStatusId\\[\\]]').multiselect({
        noneSelectedText: "-- No filter --",
        selectedList: 3
    });

    $('[name=filterWhoOrders]').multiselect({
        multiple: false,
        selectedList: 1
    });
    $('button.ui-multiselect').css('width', '110px');
});


function amountKeyDown(event, sender, orderId)
{
    if (event.keyCode == 13)
        setAmount(orderId, sender);
}

function filter() {
    $('#form').attr('action', 'index.php?route=sale/repurchaseOrders&token=<?= $token; ?>');
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

function selectAll(control)
{
    $('input[name*=\'selectedItems\']').attr('checked', control.checked);
}

function setAmount(orderId, sender)
{
    $.ajax({
        url: 'index.php?route=sale/repurchaseOrders/setAmount&token=<?= $this->session->data['token'] ?>&orderId=' + orderId + '&amount=' + sender.value,
        dataType: 'json',
        beforeSend: function() {
            $(sender).attr('disabled', true);
            $(sender).after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('.wait').remove();
            $(sender).attr('disabled', false);
        },
        success: function(json) {
            if (json['error'])
                alert("setAmount(): " + json['error']);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("setAmount(): " + jqXHR.responseText);
        }
    });
}

function submitForm(action)
{
    if ($('#selectedItems\\[\\]:checked').length != 0)
    {
        $('#form').attr('action', action);
        $('#form').submit();
    }
    else
        alert("<?= $textNoSelectedItems ?>");
}

function submitStatus(statusId)
{
    var selectedItems = '';
    $('#selectedItems\\[\\]:checked').each(function() {
        selectedItems += '&selectedItems[]=' + this.value;
    });
    if (!selectedItems)
    {
        alert("<?= $textNoSelectedItems ?>");
        return;
    }
    $.ajax({
        url: 'index.php?route=sale/repurchaseOrders/setStatus&token=<?= $this->session->data['token'] ?>&statusId=' + statusId + selectedItems,
        dataType: 'json',
        success: function(json) {
            $('#selectedItems\\[\\]:checked').parent().parent().find('#status').text(json['newStatusName']);
            $('#selectedItems\\[\\]:checked').removeAttr('checked');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert(jqXHR.responseText);
        }
    });
}
//--></script>
<?php echo $footer; ?>