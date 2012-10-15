<?php echo $header; ?><?php echo $column_right; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php endforeach; ?>
    </div>
    <h1><?php echo $headingTitle; ?></h1>
    <form id="form" method="post">
        <table class="list">
            <thead><tr class="product-filter">
                <td style="width: 670px;"/>
                <td>
                    <select name="filterStatusId[]" multiple="true">
                        <?php foreach ($statuses as $status):
                            if (in_array($status['status_id'], $filterStatusId))
                                $selected = "selected=\"selected\"";
                            else
                                $selected = ""; ?>
                            <option value="<?= $status['status_id'] ?>" <?= $selected ?>><?= $status['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><a onclick="filter();" class="button"><span><?= $textFilter; ?></span></a></td>
            </tr></thead>
        </table>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-list">
                    <div class="order-id"><b><?= $textOrderItemId ?>:&nbsp;</b> #<?php echo $order['orderItemId']; ?></div>
                    <div class="order-status">
                        <b><?php echo $textStatus; ?>:&nbsp;</b>
                        <span id="orderStatus<?= $order['orderItemId'] ?>"><?= $order['statusName'] ?></span>
                    </div>
                    <div class="order-content">
                        <table>
                            <tr>
                                <td rowspan="3"><a href="<?= $order['itemUrl'] ?>"><image src="<?= $order['imagePath'] ?>" /></a></td>
                                <td style="white-space: nowrap;">
                                    <b><?php echo $textTimeAdded; ?></b> <?php echo $order['timeAdded']; ?><br />
                                </td>
                                <td rowspan="3">
                                    <a href="<?= $order['itemUrl'] ?>" style="text-decoration: none;">
                                        <?php foreach ($order['options'] as $option): ?>
                                            &nbsp;<small> - <?= $option['name'] ?>: <?= substr($option['value'], 0, 60) . (strlen($option['value']) > 60 ? '...' : '') ?></small>
                                            <br />
                                        <?php endforeach; ?>
                                    </a>
                                </td>
                                <?php if ($order['statusId'] == REPURCHASE_ORDER_ITEM_STATUS_OFFER): ?>
                                    <td><a id="accept<?= $order['orderItemId'] ?>" onclick="accept(<?= $order['orderItemId'] ?>);" class="button"><span><?= $textAccept ?></span></a></td>
                                <?php endif; ?>
                            </tr>
                            <tr>
                                <td><b><?php echo $textQuantity; ?>:&nbsp;</b> <?php echo $order['quantity']; ?></td>
                                <?php if ($order['statusId'] == REPURCHASE_ORDER_ITEM_STATUS_OFFER): ?>
                                    <td><a id="reject<?= $order['orderItemId'] ?>" onclick="reject(<?= $order['orderItemId'] ?>);" class="button"><span><?= $textReject ?></span></a></td>
                                <?php endif; ?>
                            </tr>
                            <tr><td>
                                <b><?php echo $textTotal; ?>:&nbsp;</b> <?php echo $order['total']; ?><br />
                            </td></tr>
                            <tr><td colspan="2"><b><?= $textComment ?>:</b> <?= $order['comment'] ?></td></tr>
                        </table>
                    </div>
                </div>
                <?php endforeach; ?>
            <!--div class="pagination"><?php echo $pagination; ?></div-->
        <?php else: ?>
            <div class="content"><?php echo $textNoItems; ?></div>
        <?php endif; ?>
    </form>
</div>
<script type="text/javascript">//<!--
$(document).ready(function() {
    $('[name=filterStatusId\\[\\]]').multiselect({
        noneSelectedText: "-- No filter --",
        selectedList: 3
    });
});

function accept(orderId)
{
    respondToOffer(orderId, 'accept');
}

function filter()
{
    $('#form').attr('action', 'index.php?route=account/repurchaseOrders');
    $('#form').submit();
}

function reject(orderId)
{
    respondToOffer(orderId, 'reject');
}

function respondToOffer(orderId, response)
{
    $.ajax({
        url: 'index.php?route=account/repurchaseOrders/' + response + '&orderId=' + orderId,
        dataType: 'json',
        beforeSend: function() {
            $('.button#accept' + orderId).attr('disabled', true);
            $('.button#reject' + orderId).attr('disabled', true);
            $('.button#' + response + orderId).after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('.button#accept' + orderId).attr('disabled', false);
            $('.button#reject' + orderId).attr('disabled', false);
            $('.wait').remove();
        },
        success: function(json) {
            $('#orderStatus' + orderId).text(json['newStatusName']);
            $('.button#accept' + orderId).remove();
            $('.button#reject' + orderId).remove();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert(jqXHR.responseText);
        }
    })
}
//--></script>
<?php echo $footer; ?>