<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb): ?>
        <?= $breadcrumb['separator'] ?><a href="<?php echo $breadcrumb['href']; ?>"><?= $breadcrumb['text'] ?></a>
    <?php endforeach; ?>
    </div>
    <h1><?= $headingTitle ?></h1>
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
            <a onclick="recalculateShipping()" class="button"><?= $textRecalculateShipping ?></a>
            <a onclick="submitForm('<?= $invoiceUrl ?>');" class="button"><?= $textInvoice ?></a>
            <a onclick="$('#form').attr('target', '_blank'); submitForm('<?= $print ?>');" class="button"><?= $textPrint ?></a>
            </div>
        </div>
      <div class="pagination"><?php echo $pagination; ?></div>
<script type="text/javascript">//<!--
function topsclr() {
    document.getElementById("content_scroll").scrollLeft = document.getElementById("topscrl").scrollLeft;
}

function bottomsclr() {
    document.getElementById("topscrl").scrollLeft = document.getElementById("content_scroll").scrollLeft;
}
window.onload = function() {
    document.getElementById("topfake").style.width = document.getElementById("content_scroll").scrollWidth + "px";
    document.getElementById("topscrl").style.display = "block";
    document.getElementById("topscrl").onscroll = topsclr;
    document.getElementById("content_scroll").onscroll = bottomsclr;
};
//--></script>      <div id="topscrl">
        <div id="topfake"></div>
      </div>
        <div id="content_scroll">
        <form action="" id="form" method="post" enctype="multipart/form-data">
            <input type="hidden" id="page" name="page" />
            <table class="list">
                <thead>
                    <tr>
                        <td style="width: 1px; text-align: center;"><input type="checkbox" onclick="selectAll(this);" /></td>
                        <td style="width: 1px; text-align: center;"><?= $textOrderId ?> / <?= $textUnderlyingOrderId ?></td>
                        <td style="width: 1px; text-align: center;"><?= $textItem ?></td>
                        <td style="width: 1px; text-align: center;"><?= $textCustomer ?></td>
                        <td style="width: 1px; text-align: center;"><?= $textShopName ?> / <?= $textSiteName ?></td>
                        <td style="width: 1px; text-align: center;">
                            <?= preg_replace('/\s/', '&nbsp;', $textPricePerItem) ?><br />
                            <?= preg_replace('/\s/', '&nbsp;', $textQuantity) ?><br />
                            <?= preg_replace('/\s/', '&nbsp;', $textShipping) ?><br />
                            <?= preg_replace('/\s/', '&nbsp;', $textAmount) ?>&nbsp;(<?= $currencyCode ?>)
                        </td>
                        <td style="width: 1px; text-align: center;"><?= $textStatus ?></td>
                        <td style="width: auto; text-align: center;" "><?= $textComment ?></td>
                        <td style="width: 1px; text-align: center;"><?= $textActions ?></td>
                    </tr>
                    <tr class="filter">
                        <td />
                        <td><input name="filterOrderId" value="<?= $filterOrderId ?>" size="3" onkeydown="filterKeyDown(event);" /></td>
                        <td>
                            <input name="filterItemName" value="<?= $filterItemName ?>" onkeydown="filterKeyDown(event)" />
                        </td>
                        <td>
                            <select name="filterCustomerId[]" multiple="true">
                            <?php foreach ($customers as $customer):
                                $selected = in_array($customer['id'], $filterCustomerId) ? "selected=\"selected\"" : ""; ?>
                                <option value="<?= $customer['id'] ?>" <?= $selected ?>><?= $customer['name'] ?></option>
                            <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input name="filterShopName" value="<?= $filterShopName ?>" onkeydown="filterKeyDown(event)" />
                            <input name="filterSiteName" value="<?= $filterSiteName ?>" onkeydown="filterKeyDown(event)" />
                        </td>
                        <td><input name="filterAmount"  size="9" value="<?= $filterAmount; ?>" onkeydown="filterKeyDown(event);" placeholder="Amount filter"/></td>
                        <td>
                            <select name="filterStatusId[]" multiple="true">
                                <?php foreach ($statuses as $status):
                                    if (in_array($status['statusId'], $filterStatusId) || in_array($status['statusId'], $filterStatusIdDateSet))
                                        $selected = "selected=\"selected\"";
                                    else
                                        $selected = ""; ?>
                                    <option value="<?= $status['statusId'] ?>" <?= $selected ?>><?= $status['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input name="filterStatusSetDate" class="date" value="<?= $filterStatusSetDate ?>" onkeydown="filterKeyDown(event)" />
                        </td>
                        <td />
                        <td><a onclick="filter();" class="button"><?= $textFilter; ?></a></td>
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
                            <td><?= $order['orderId'] ?> / <?= $order['underlyingOrderId'] ?></td>
                            <td>
                                <input
                                    onkeydown="itemNameKeyDown(event, this, <?= $order['orderId'] ?>)"
                                    value="<?= $order['itemName'] ?>"
                                    />
                                <a href="<?= $order['originalImagePath'] ?>" target="_blank">
                                    <img src="<?= $order['imagePath'] ?>" title="<?= $order['hint'] ?>"/>
                                </a>
                            </td>
                            <td style="white-space: nowrap;"><a href="<?= $order['customerUrl'] ?>"><?= $order['customerNick'] ?></a></td>
                            <td>
                                <input
                                    onkeydown="shopNameKeyDown(event, this, <?= $order['orderId'] ?>)"
                                    value="<?= $order['shopName'] ?>"
                                    /><br />
                                <a href="<?= $order['itemUrl'] ?>"><?= $order['siteName'] ?></a>
                            </td>
                            <td>
                                <input id="whiteprice_<?= $order['orderId'] ?>"
                                       value="<?= $order['whiteprice'] ?>"
                                       onkeyup="price_<?= $order['orderId'] ?>.value=this.value"
                                       onkeydown="changeWhitePrice(event, this, <?= $order['orderId'] ?>)"
                                    />
                                <input id="price_<?= $order['orderId'] ?>"
                                       class="repCalculator"
                                       style="width: 70%"
                                       onkeydown="changePrice(event, this, <?= $order['orderId'] ?>)"
                                       value="<?= $order['price'] ?>"
                                    />
                                <input
                                        onkeydown="quantityKeyDown(event, this, <?= $order['orderId'] ?>)"
                                        value="<?= $order['quantity'] ?>"
                                        />
                                <input id="shipping_<?= $order['orderId'] ?>"
                                    onkeydown="changeShipping(event, this, <?= $order['orderId'] ?>)"
                                    value="<?= $order['shipping'] ?>"
                                    />
                                <input id="total_<?= $order['orderId'] ?>"
                                       onkeydown="amountKeyDown(event, this, <?= $order['orderId'] ?>)"
                                       value="<?= $order['amount'] ?>"
                                    />
                            </td>
                            <td id="status"><?= $order['status'] ?></td>
                            <td>
                                Private<br />
                                <input
                                        alt="<?php echo $order['privateComment']; ?>"
                                        onblur="saveComment(<?= $order['orderId'] ?>, this, true);"
                                        onkeydown="if (event.keyCode == 13) saveComment(<?= $order['orderId'] ?>, this, true);"
                                        value="<?php echo $order['privateComment']; ?>"/><br />
                                Public <span style="color: red; background-color: yellow">(this comment will see the customer)</span><br />
                                <input
                                        alt="<?php echo $order['comment']; ?>"
                                        onblur="saveComment(<?= $order['orderId'] ?>, this, false);"
                                        onkeydown="if (event.keyCode == 13) saveComment(<?= $order['orderId'] ?>, this, false);"
                                        value="<?php echo $order['comment']; ?>"/><br />
                                <span style="color: red; "><?= $order['options'] ?></span>
                            </td>
                            <td>
                            <?php foreach ($order['actions'] as $action): ?>
                                [&nbsp;<a
                                    <?= !empty($action['href']) ? 'href="' .  $action['href'] . '"' : '' ?>
                                    <?= !empty($action['onclick']) ? 'onclick="' . $action['onclick'] . '"' : '' ?>
                                    target="_blank"><?= preg_replace('/\s/', '&nbsp;', $action['text']) ?></a>&nbsp;]
                            <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>
        <div class="pagination"><?= $pagination ?></div>
    </div>
</div>
    <script type="text/javascript">
	$(function() { $(".repCalculator").calculator({showOn: 'opbutton', buttonImageOnly: true, buttonImage: 'view/image/calculator.png'}); });
    </script> 
<script type="text/javascript">//<!--
$(document).ready(function() {
    $('.date').datepicker({dateFormat: 'yy-mm-dd'});
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

function changePrice(event, sender, orderId)
{
    if (event.keyCode == 13)
        setProperty(orderId, sender, 'price');
}

function changeWhitePrice(event, sender, orderId)
{
    if (event.keyCode == 13)
        setProperty(orderId, sender, 'whiteprice');
}

function changeShipping(event, sender, orderId)
{
    if (event.keyCode == 13)
        setProperty(orderId, sender, 'shipping');
}

function amountKeyDown(event, sender, orderId)
{
    if (event.keyCode == 13)
        setProperty(orderId, sender, 'amount');
}

function filter() {
    $('#form').attr('action', 'index.php?route=sale/repurchaseOrders&token=<?= $token; ?>');
    $('#form').submit();
}

function filterKeyDown(e) {
    if (e.keyCode == 13)
        filter();
}

function imageManager(orderId, imageElement) {
    $('#dialog').remove();

    $('#content').prepend(
        '<input id="image" type="hidden" />' +
        '<div id="dialog" style="padding: 3px 0px 0px 0px;">' +
            '<iframe src="<?= $urlImageManager ?>" ' +
            '   style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" ' +
            '   frameborder="no" scrolling="auto">' +
            '</iframe>' +
        '</div>');

    $('#dialog').dialog({
        title: 'Image Manager',
        close: function (event, ui) {
            if ($('#image').attr('value')) {
                $.ajax({
                    url: '<?= $urlImageChange ?>&value='.replace(/&amp;/g, '&') + encodeURIComponent($('#image').attr('value')) + '&orderId=' + orderId,
                    dataType: 'json',
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("saveComment(): " + jqXHR.responseText);
                    },
                    success: function(data) {
                        if (data['image'])
                        imageElement.attr('src', data['image']);
                    }
                });
            }
            $('#image').remove();
        },
        bgiframe: false,
        width: 800,
        height: 400,
        resizable: false,
        modal: false
    });
};


function itemNameKeyDown(event, sender, orderId) {
    if (event.keyCode == 13) {
        setProperty(orderId, sender, 'itemName');
    }
}

function quantityKeyDown(event, sender, orderId)
{
    if (event.keyCode == 13) {
      setProperty(orderId, sender, 'quantity');
    }
}

function recalculateShipping() {
    var selectedItems = '';
    $('#selectedItems\\[\\]:checked').each(function() {
        selectedItems += '&selectedItems[]=' + this.value;
    });
    if (!selectedItems) {
        alert("<?= $textNoSelectedItems ?>");
        return;
    }
    $.ajax({
        url: 'index.php?route=sale/repurchaseOrders/recalculateShipping&token=<?= $token ?>' + selectedItems,
        dataType: 'json',
        success: function(json) {
            if (json['error']) {
                alert("setProperty(): " + json['error']);
            }
            for (var i = 0; i < json.length; i++) {
                $('#price_'+json[i]['itemId']).val(json[i]['price']);
                $('#shipping_' + json[i]['itemId']).val(json[i]['shipping']);
                $('#total_'+json[i]['itemId']).val(json[i]['total']);
            }
//            $('#selectedItems\\[\\]:checked').removeAttr('checked');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert(jqXHR.responseText);
        }
    });
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

function selectAll(control) {
    $('input[name*=\'selectedItems\']').attr('checked', control.checked);
}

function setProperty(orderId, sender, propName) {
    $.ajax({
        url: 'index.php?route=sale/repurchaseOrders/setProperty&token=<?= $this->session->data['token'] ?>&orderId=' +
                orderId + '&propName=' + propName + '&value=' + sender.value,
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
          if (json['error']) {
            alert("setProperty(): " + json['error']);
          }
          else {
            $('#price_'+json['itemId']).val(json['price']);
            $('#whiteprice_'+json['itemId']).val(json['whiteprice']);
            $('#total_'+json['itemId']).val(json['total'])
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("setProperty(): " + jqXHR.responseText);
        }
    });
}

function shopNameKeyDown(event, sender, orderId) {
    if (event.keyCode == 13) {
        setProperty(orderId, sender, 'shopName');
    }
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