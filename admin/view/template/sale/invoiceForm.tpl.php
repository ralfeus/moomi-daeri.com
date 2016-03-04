<?= $header; ?>
<div id="content">
    <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb):
            echo $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php endforeach; ?>
    </div>
<?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">" . nl2br($notification) . "</div>";
?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/order.png" alt="" /> <?= $headingTitle ?> <?= isset($invoiceId) ? "#$invoiceId" : "" ?></h1>
        </div>
        <div class="content">
            <form action="<?= $submitAction ?>" method="post" enctype="multipart/form-data" id="form">
                <table class="list">
                    <tr>
                        <td><?= $textShippingAddress ?></td>
                        <td><?= $textWeight ?></td>
                    </tr>
                    <tr>
                        <td><?= $shippingAddress ?></td>
                        <td>
                            <table>
                                <tr><td>
                                        <input id="totalWeight" name="totalWeight" value="<?= $totalWeight ?>" <?= $readOnly ?> />
                                        <a id="buttonRecalculate" class="button" onclick="recalculateShipping()"><?= $buttonRecalculateShippingCost ?></a>
                                </td></tr>
                                <tr><td><?= $textShippingMethod ?></td></tr>
                                <tr><td>
                                    <select name="shippingMethod" onchange="recalculateShipping()" <?= $readOnly ?>>
<?php foreach ($shippingMethods as $possibleShippingMethod): ?>
                                        <option
                                                value="<?= $possibleShippingMethod['code'] ?>"
                                                <?= $possibleShippingMethod['code'] == $shippingMethod ? "selected" : '' ?>>
                                            <?= $possibleShippingMethod['shippingMethodName'] ?>
                                        </option>
<?php endforeach; ?>
                                    </select>
                                </td></tr>
                                <tr><td><?= $textShippingCost ?></td></tr>
                                <tr><td>
                                    <input id="shippingCost" name="shippingCost" value="<?= $shippingCost ?>" disabled="true" />
                                    <input id="shippingCostRaw" type="hidden" value="0" />
                                </td></tr>
                                <tr><td><?= $textDiscount ?></td></tr>
                                <tr><td>
                                    <input name="discount" value="<?= $discount ?>" <?= $readOnly ?> />
                                    <a id="buttonSaveDiscount" onclick="saveDiscount()"><img src="view/image/checkmark.png" /></a>
                                </td></tr>
                                <tr>
                                    <td><?= $textGrandTotal ?></td>
                                    <td><?= $textTotalCustomerCurrency ?></td>
                                </tr>
                                <tr>
                                    <td><input id="grandTotal" name="grandTotal" value="<?= $grandTotal ?>" disabled="true" /></td>
                                    <td><input name="totalCustomerCurrency" value="<?= $totalCustomerCurrency ?>" disabled="true" /></td>
                                </tr>
                                <tr><td><?= $textPackageNumber ?></td></tr>
                                <tr><td>
                                    <input name="packageNumber" value="<?= $packageNumber?>" alt="<?= $packageNumber ?>"/>
                                    <a id="buttonSavePackageNumber" onclick="saveTextField('packageNumber')"><img src="view/image/checkmark.png" /></a>
                                </td></tr>
                                <tr><td><?= $textShippingDate ?></td></tr>
                                <tr><td>
                                    <input name="shippingDate" value="<?= $shippingDate?>" alt="<?= $shippingDate ?>" class="date" eadonly="readonly" /><!-- r -->
                                    <a id="buttonSaveShippingDate" onclick="saveTextField('shippingDate')"><img src="view/image/checkmark.png" /></a>
                                </td></tr>
                                <tr><td><?= $textComment ?></td></tr>
                                <tr><td>
                                    <input name="comment" value="<?= $comment ?>" />
                                    <?php if (!empty($invoiceId)): ?>
                                    <a id="buttonSaveComment" onclick="saveTextField('comment')"><img src="view/image/checkmark.png" /></a>
                                    <?php endif; ?>
                                </td></tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table class="list">
                    <thead>
                        <tr>
                            <td class="right"><?= $textOrderItemId ?></td>
                            <td class="right"><?= $textOrderId ?></td>
                            <td class="right"><?= $textItemImage ?></td>
                            <td class="right"><?= $textItemName ?></td>
                            <td class="right"><?= $textPrice ?></td>
                            <td class="right"><?= $textQuantity ?></td>
                            <td class="right"><?= $textShipping ?></td>
                            <td class="right"><?= $textSubtotal ?></td>
                            <td class="right"><?= $textSubtotalCustomerCurrency ?></td>
                            <td class="left"><?= $textComment ?></td>
                        </tr>
                    </thead>
                    <tbody>
<?php foreach ($orderItems as $orderItem): ?>
                            <input name="selectedItems[]" type="hidden" value="<?= $orderItem['id'] ?>" />
                            <tr>
                                <td class="right"><?= $orderItem['id']; ?></td>
                                <td class="right"><?= $orderItem['order_id'] ?></td>
                                <td class="right"><img src="<?= $orderItem['image_path'] ?>" /></td>
                                <td class="right">
                                    <table height="100%" width="100%" class="list">
                                        <tr valign="center"><td><?php echo $orderItem['model'] . "&nbsp;/&nbsp;" . $orderItem['name']; ?></td></tr>
                                        <tr><td class="left"><span style="color: red;"><?= nl2br($orderItem['options']) ?></span></td></tr>
                                    </table>
                                </td>
                                <td class="right"><?= $orderItem['price'] ?></td>
                                <td class="right"><?= $orderItem['quantity'] ?></td>
                                <td class="right">
                                    <input
                                        id="shipping<?= $orderItem['id'] ?>"
                                        value="<?= $orderItem['shipping'] ?>"
                                        alt="<?= $orderItem['shipping'] ?>"
                                        onblur="checkChanged(this)"
                                        onkeypress="if (event.keyCode == 13) saveOrderItemShipping(<?= $orderItem['id'] ?>, this);"
                                        <?= $readOnly ?> />
                                </td>
                                <td class="right"><?= $orderItem['subtotal'] ?></td>
                                <td class="right"><?= $orderItem['subtotalCustomerCurrency'] ?></td>
                                <td class="left"><?= $orderItem['comment'] ?></td>
                            </tr>
<?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7"><div id="total" class="right"><?= $textTotal ?></div></td>
                            <td>
                                <?= $total ?>
                                <input type="hidden" name="total" value="<?= $totalRaw ?>" />
                            </td>
                            <td />
                        </tr>
                    </tfoot>
                </table>
                <div class="buttons">
                    <a class="button" onclick="$('#form').submit()"><?= $button_action ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">//<!--
$(document).ready(function() {
    $('.date').datepicker({dateFormat: 'yy-mm-dd'});
    if ($('#totalWeight').disabled)
        $('#buttonRecalculate').remove();
    if ($('[name=discount]').disabled)
        $('#buttonSaveDiscount').remove();
});

function checkChanged(actor) {
    if (actor.value != actor.alt) {
        actor.style.borderColor = "blue";
    } else {
        actor.style.border = null;
    }
}

function formatCurrencies(value) {
    var result;
    $.ajax({
        url: 'index.php?route=localisation/currency/format&token=<?= $this->session->data["token"] ?>',
        type: 'post',
        dataType: 'json',
        async: false,
        data: {
            value: value,
            customerCurrency: '<?= $customerCurrencyCode ?>'
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('formatCurrency():\n' + jqXHR.responseText);
        },
        success: function(data) {
            if (data['error'])
                alert(data['error']);
            else if (data['result'])
                result = data['result'];
        }
    });
    return result;
}

function recalculateShipping() {
    $.ajax({
        url: '<?= $shippingCostRoute ?>'.replace(/&amp;/g, '&'),
        type: 'post',
        dataType: 'json',
        data: {
            weight: $('#totalWeight').val(),
            method: $('[name=shippingMethod]').val()
        },
        beforeSend: function() {
            $('#buttonRecalculate').attr('disabled', true);
            $('#totalWeight').after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
        },
        success: function(data) {
            $('#shippingCostRaw').val(data['cost']);
            $('#shippingCost').val(formatCurrencies(data['cost'])['system']);
            recalculateTotal();
            $('#buttonRecalculate').attr('disabled', false);
            $('.wait').remove();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('recalculateShipping():\n' + jqXHR.responseText);
            $('#buttonRecalculate').attr('disabled', false);
            $('.wait').remove();
        }
    });
}

function recalculateTotal() {
    var totalCost = <?= $totalRaw ?> + Number($('#shippingCostRaw').val()) - Number($('[name=discount]').val());
    var currencyStrings = formatCurrencies(totalCost);
    $('#grandTotal').val(currencyStrings['system']);
    $('[name=totalCustomerCurrency]').val(currencyStrings['customer']);
}

function resetValue(element) {
    element.attr('value', element.attr('alt'));
}

function saveDiscount() {
    <?php if ($invoiceId): ?>
    $.ajax({
        url: 'index.php?route=sale/invoice/saveDiscount',
        data: {
            token: '<?= $this->session->data["token"] ?>',
            invoiceId: <?= $invoiceId ?>,
            discount: $('[name=discount]').val()
        },
        dataType: 'json',
        type: 'post'
    });
    <?php endif; ?>
    recalculateTotal();
}

function saveOrderItemShipping(orderItemId, actor) {
    if (actor.value == actor.alt)
        return false;
    if (!Number(actor.value)) {
        actor.style.borderColor = "red";
        return false;
    }
    $(actor).effect("bounce");
    $.ajax({
        url: 'index.php?route=sale/order_items/saveShipping',
        beforeSend: function() {
            $(actor).after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('.wait').remove();
            actor.style.borderColor = null;
        },
        data: {
            token: '<?= $this->session->data["token"] ?>',
            orderItemId: orderItemId,
            shipping: actor.value
        },
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
            alert('saveTextField():\n' + jqXHR.responseText);
        },
        type: 'post'
    });
}

function saveTextField(field)
{
<?php if ($invoiceId): ?>
    $.ajax({
        url: 'index.php?route=sale/invoice/saveTextField',
        beforeSend: function() {
            $('[name=' + field + ']').after('<span class="wait">&nbsp;<img src="view/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('.wait').remove();
        },
        data: {
            token: '<?= $this->session->data["token"] ?>',
            invoiceId: <?= $invoiceId ?>,
            data: $('[name=' + field + ']').val(),
            param: field
        },
        dataType: 'json',
        error: function(jqXHR, textStatus, errorThrown) {
            alert('saveTextField():\n' + jqXHR.responseText);
        },
        type: 'post'
    });
<?php endif; ?>
}
//--></script>
<?php echo $footer; ?>