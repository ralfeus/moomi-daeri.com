<?= $header ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb)
            echo $breadcrumb['separator'] . "<a href=\"" . $breadcrumb['href'] . "\">" . $breadcrumb['text'] . "</a>";
        ?>
    </div>
    <?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">$notification</div>";
    ?>
    <?php if (!isset($notifications['error'])): ?>
        <div class="box">
            <div class="heading">
                <table><tr>
                    <td><h1><?= $headingTitle ?></h1></td>
                    <td valign="center"><h2 id="invoiceStatus"><?= $status ?></h2></td>
                </tr></table>
            </div>
            <div class="buttons"><div class="right">
                <?php if ($statusId == IS_AWAITING_CUSTOMER_CONFIRMATION): ?>
                    <a id="confirm" class="button" onclick="confirm();"><span><?= $textConfirm ?></span></a>
                <?php endif; ?>
            </div></div>
            <div class="content">
                <form action="<?= $submit_action ?>" method="post" enctype="multipart/form-data" id="form">
                    <table class="list">
                        <thead>
                            <tr>
                                <td><?= $textOrderItemId ?></td>
                                <td><?= $textOrderId ?></td>
                                <td><?= $textItemImage ?></td>
                                <td><?= $textItemName ?></td>
                                <td><?= $textPrice ?> / <?= $textQuantity ?></td>
                                <td><?= $textShippingCost ?></td>
                                <td><?= $textSubtotal ?></td>
                                <td><?= $textComment ?></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $orderItem): ?>
                                <tr>
                                    <td><?= $orderItem['id']; ?></td>
                                    <td><?= $orderItem['order_id'] ?></td>
                                    <td><img src="<?= $orderItem['image_path'] ?>" /></td>
                                    <td>
                                        <table height="100%" width="100%" class="list">
                                            <tr valign="center"><td><?php echo $orderItem['model'] . "&nbsp;/&nbsp;" . $orderItem['name']; ?></td></tr>
                                            <tr><td><span style="color: red; "><?= $orderItem['options'] ?></span></td></tr>
                                        </table>
                                    </td>
                                    <td><?= $orderItem['price'] ?> / <?= $orderItem['quantity'] ?></td>
                                    <td><?= $orderItem['shipping'] ?></td>
                                    <td><?= $orderItem['subtotal'] ?></td>
                                    <td><?= $orderItem['comment'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="6"><b><div class="right"><?= $textTotal ?></div></b></td>
                            <td colspan="2"><b><?= $total ?></b></td>
                        </tr>
                        </tfoot>
                    </table>
                    <table class="list">
                        <tr>
                            <td><?= $textShippingAddress ?></td>
                            <td><?= $textWeight ?></td>
                        </tr>
                        <tr>
                            <td rowspan="9"><?= $shippingAddress ?></td>
                            <td><input name="totalWeight" value="<?= $totalWeight ?>" disabled="true" /></td>
                        </tr>
                        <tr>
                            <td><?= $textShippingMethod ?></td>
                            <td><?= $textPackageNumber ?></td>
                        </tr>
                        <tr>
                            <td><input name="shippingMethod" value="<?= $shippingMethod ?>" disabled="true" /></td>
                            <td><input value="<?= $packageNumber ?>" disabled="true" /></td>
                        </tr>
                        <tr>
                            <td><?= $textShippingCost ?></td>
                            <td><?= $textComment ?></td>
                        </tr>
                        <tr>
                            <td><input name="shippingCost" value="<?= $shippingCost ?>" disabled="true" /></td>
                            <td rowspan="5"><textarea name="comment" disabled="true" style="height: 100%; width: 100%"><?= $comment ?></textarea></td>
                        </tr>
                        <tr><td><?= $textDiscount ?></td></tr>
                        <tr><td><input name="discount" value="<?= $discount ?>" disabled="true" /></td></tr>
                        <tr><td><?= $textGrandTotal ?></td></tr>
                        <tr><td><input name="grandTotal" value="<?= $grandTotal ?>" disabled="true" /></td></tr>
                    </table>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>
<script type="text/javascript">// <!--
function confirm()
{
    $.ajax({
        url: 'index.php?route=account/invoice/confirm&invoiceId=<?= $invoiceId ?>',
        dataType: 'json',
        beforeSend: function() {
            $('.button#confirm').attr('disabled', true);
            $('.button#confirm').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('.button#confirm').attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('#invoiceStatus').text(json['newStatus']);
            $('.button#confirm').remove();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('recalculateShipping():\n' + jqXHR.responseText);
        }
    })
}
//--></script>
<?php echo $footer; ?>