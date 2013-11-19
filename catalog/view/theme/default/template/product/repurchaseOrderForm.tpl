<?php
    echo $header;
    echo $column_right;
?>
<script src="catalog/view/javascript/ajaxfileupload.js" type="text/javascript"></script>
<div id="content">
    <div class="breadcrumb">
        <?php
            foreach ($breadcrumbs as $breadcrumb):
                echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a> <?php
            endforeach;
        ?>
    </div>
    <h1><?php echo $heading_title; ?></h1>
    <form method="post" enctype="multipart/form-data" id="order">
        <div class="buttons">
            <div class="right"><a onclick="addOrderItem();" class="button"><span><?php echo $button_add; ?></span></a></div>
            <div class="right"><a id='addToCart' onclick="addOrdersToCart();" class="button"><span><?= $textAddToCart ?></span></a></div>
        </div>
        <table class="form">
            <tbody id="repurchase_order_items">
                <tr class="repurchase-order-item">
                    <td>
                        <div id='box'>
                            <table width="100%">
                                <tr><td colspan="5">
                                    <?php echo $entry_item_url; ?><br/>
                                    <input id="itemUrl" style="width: 100%;"/>
                                </td></tr>
                                <tr>
                                    <td colspan="4">
                                        <?php echo $entry_image_path; ?><br />
                                        <input id="imagePath" style="width: 100%" onblur="downloadImage(this);" /><br />
                                        <input type="file" id="imageFile" name="imageFile" value="File" onchange="ajaxFileUpload(this)"/>
                                    </td>
                                    <td class="right" rowspan="4">
                                        <a href="<?= HTTP_IMAGE ?>/no_image.jpg" target="_blank">
                                            <img id="image" src="<?= HTTP_IMAGE ?>/no_image.jpg" alt="Not an image" style="max-height: 240px; max-width: 320px;" />
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <input type="radio" id='whoBuys' value='<?= REPURCHASE_ORDER_SHOP_BUYS_OPTION_VALUE_ID ?>' checked="true" disabled="true"/>&nbsp;<?= $textShopBuys ?><br />
                                        <input type="radio" id='whoBuys' value='<?= REPURCHASE_ORDER_CUSTOMER_BUYS_OPTION_VALUE_ID ?>' disabled="true"/>&nbsp;<?= $textCustomerBuys ?><br />
                                    </td>
                                    <td colspan="2">
                                        <?= $textItemName ?><br />
                                        <input id="itemName" style="width: 100%;" />
                                    </td>
                                    <td>
                                        <?= $textShopName ?><br />
                                        <input id="shopName" style="width: 100%;" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="25%">
                                        <?php echo $entry_size; ?><br />
                                        <input id="size" />
                                    </td>
                                    <td width="25%">
                                        <?php echo $entry_color; ?><br />
                                        <input id="color" />
                                    </td>
                                    <td width="25%">
                                        <?php echo $entry_quantity; ?><br />
                                        <input id="quantity" value="1" />
                                    </td>
                                    <td width="25%">
                                        <?= $textApproximatePrice ?>
                                        <input id="price" />
                                    </td>
                                </tr>
                                <tr><td colspan="4">
                                    <?= $textComment ?><br />
                                    <textarea id="comment" style="width: 100%"></textarea>
                                </td></tr>
                            </table>
                        </div>
                    </td>
                    <td class="buttons" valign="top" width="1">
                        <input type="hidden" name="[action]" />
                        <div class="right"><a onclick="deleteOrderItem(this.parentNode.parentNode.parentNode);" class="button"><span><?php echo $button_delete; ?></span></a></div>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>
<?php echo $footer; ?>

<script type="text/javascript">//<!--
var templateOrderItem;

$().ready(function(){
    var firstOrderItem = $('.repurchase-order-item:first');
    templateOrderItem = firstOrderItem.clone();
    templateOrderItem.find('input').each(function(i) {
        this.name = this.name.replace(/order_items\[\w+\]/, "");
        if ((this.name.indexOf("fee") == -1) && (this.name.indexOf("total") == -1))
            this.disabled = false;
    });
    $('input[name$="[action]"', templateOrderItem).val('add');
    if (firstOrderItem.find('input#order_items[new0][item_url]') != null)
    {
        firstOrderItem.remove();
        addOrderItem();
    }
});

function addOrderItem()
{
    var newOrderItem = templateOrderItem.clone();
    var index = (new Date).getTime();
    newOrderItem.find('input').each(function(i) {
        this.id += index;
    });
    $('.form').append(newOrderItem);
    $(newOrderItem).hide();
    $(newOrderItem).fadeIn();
}

function addOrdersToCart()
{
    var imagePath;
    $('.repurchase-order-item').each(function() {
        var currentOrder = this;
        $.ajax({
            url: 'index.php?route=checkout/cart/update',
            type: 'post',
            data: {
                'quantity': $(this).find('[id^="quantity"]').val(),
                'itemPrice': $(this).find('[id^="price"]').val(),
                'product_id': '<?= REPURCHASE_ORDER_PRODUCT_ID ?>',
                'option[<?= REPURCHASE_ORDER_ITEM_NAME_OPTION_ID ?>]': $(this).find('[id^="itemName"]').val(),
                'option[<?= REPURCHASE_ORDER_ITEM_URL_OPTION_ID ?>]': $(this).find('[id^="itemUrl"]').val(),
                'option[<?= REPURCHASE_ORDER_IMAGE_URL_OPTION_ID ?>]': $(this).find('[id^="imagePath"]').val(),
                'option[<?= REPURCHASE_ORDER_WHO_BUYS_OPTION_ID ?>]': $(this).find('[id^="whoBuys"]:checked').val(),
                'option[<?= REPURCHASE_ORDER_COLOR_OPTION_ID ?>]': $(this).find('[id^="color"]').val(),
                'option[<?= REPURCHASE_ORDER_SIZE_OPTION_ID ?>]': $(this).find('[id^="size"]').val(),
                'option[<?= REPURCHASE_ORDER_COMMENT_OPTION_ID ?>]': $(this).find('[id^="comment"]').val(),
                'option[<?= REPURCHASE_ORDER_SHOP_NAME_OPTION_ID ?>]': $(this).find('[id^="shopName"]').val()
            },
            dataType: 'json',
            beforeSend: function()
            {
                $('#addToCart').attr('disabled', true);
                $('#addToCart').after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
            },
            complete: function()
            {
                $('.wait').remove();
                $('#addToCart').attr('disabled', false);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            },
            success: function(json) {
                $('.success, .warning, .attention, .information, .error').remove();

                if (json['error']) {
                    if (json['error']['warning']) {
                        $('#notification').html('<div class="error" style="display: none;">' + json['error']['warning'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
                        $('.warning').fadeIn('slow');
                    }

                    for (i in json['error']) {
                        $('#option-' + i).after('<span class="error">' + json['error'][i] + '</span>');
                    }
                }

                if (json['success']) {
                    deleteOrderItem(currentOrder);
                    $('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
                    $('.success').fadeIn('slow');
                    $('#cart_total').html(json['total']);
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                }
            }
        });
    });
}

function ajaxFileUpload(fileObject)
{
    var orderElement = fileObject.parentElement;
    $.ajaxFileUpload({
        url:'index.php?route=product/repurchase/uploadImage',
        secureuri:false,
        fileElementId: fileObject.id,
        dataType: 'json',
        success: function (data, status) {
            if(typeof(data.error) != 'undefined')
            {
                if(data.error != '')
                {
                    alert(data.error);
                }else
                {
                    alert(data.msg);
                }
            }
            $(orderElement).find('[id^="imagePath"]').val(data['filePath']);
            $(orderElement.parentElement).find('img').attr('src', '<?= HTTP_IMAGE ?>' + data['filePath']);
        },
        error: function (data, status, e) {
            alert(e);
        }
    });

    return false;
}

function deleteOrderItem(orderItem)
{
    $(orderItem).fadeOut();
    $(orderItem).remove();
}

function downloadImage(urlInput)
{
    if (/https?:\/\/([\w\-\.]+)/.test(urlInput.value))
    {
        $.ajax({
            url: 'index.php?route=product/repurchase/downloadImage&url=' + encodeURI(urlInput.value),
            dataType: 'json',
            beforeSend: function()
            {
                $(urlInput).attr('disabled', true);
                $(urlInput).after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
            },
            complete: function()
            {
                $('.wait').remove();
                $(urlInput).attr('disabled', false);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert(jqXHR.responseText);
            },
            success: function(json) {
                if (!json['error'])
                {
                    urlInput.value = json['filePath'];
                    $(urlInput.parentElement.parentElement).find('img#image').attr('title', json['warning']);
                    if (json['warning'])
                    {
                        $(urlInput.parentElement.parentElement).find('img#image').attr('src', '<?= HTTP_IMAGE ?>/no_image.jpg');
                        $(urlInput.parentElement.parentElement).find('img#image').parent().attr('href', json['filePath']);
                    }
                    else
                    {
                        $(urlInput.parentElement.parentElement).find('img#image').attr('src', '<?= HTTP_IMAGE ?>' + json['filePath']);
                        $(urlInput.parentElement.parentElement).find('img#image').parent().attr('href', '<?= HTTP_IMAGE ?>' + json['filePath']);
                    }
                }
                else
                    alert(json['error']);
            }
        });
    }
}
//--></script>