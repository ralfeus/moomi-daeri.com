<?= $header ?><?= $column_right ?>
<div id="content">
<?php $dataCart == 0; ?>
    <div class="breadcrumb">
      <?php foreach ($breadcrumbs as $breadcrumb): ?>
        <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
      <?php endforeach; ?>
    </div>
    <h1><?= $headingTitle ?></h1>
    <form id="form" method="post" action="<?= $urlRepurchaseOrders ?>">
      <table class="header">
        <thead>
          <tr>
            <td><?= $textFilterBy ?></td>
            <td class="right"><a onclick="filter();" class="button"><span><?= $textFilter; ?></span></a></td>
          </tr>
          <tr class="product-filter">
            <td>
              <?= $textOrderItemId ?>
              <input name="filterOrderId" value="<?= $filterOrderId ?>" size="3"/>
            </td>
            <td class="right" style="width: 100%;">
              <?= $textStatus ?>
              <select name="filterStatusId[]" multiple="true">
                <?php foreach ($statuses as $statusId => $status):
                  if (in_array($statusId, $filterStatusId))
                    $selected = "selected=\"selected\"";
                  else
                    $selected = ""; ?>
                    <option value="<?= $statusId ?>" <?= $selected ?>><?= $status ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
        </thead>
      </table>
      
      <?php if (!empty($orders)):
        foreach ($orders as $order): ?>
          <div class="order-list" style="	padding-bottom: 20px; -webkit-border-radius: 7px; -moz-border-radius: 7px; -khtml-border-radius: 7px; border-radius: 7px; border: 1px solid #DBDEE1;">
            <div class="order-id" style="width: 50%; float: left; margin-bottom: 2px;"><b style="padding-left: 10px;"><?= $textOrderItemId ?>:&nbsp;</b> #<?= $order['orderItemId'] ?></div>
              <div class="order-status" style="width: 50%; float: right; text-align: right; margin-bottom: 2px;">
                <b><?= $textStatus ?>:&nbsp;</b>
                <span id="orderStatus" style="padding-right: 10px;"><?= $order['statusName'] ?></span>
              </div>
              <div class="order-content<?= $order['orderItemId'] ?>" style="padding: 10px 0px; width: 100%; ">
           
                <?php 
                $dataCart[$order['orderItemId']] = array(
                  "quantity" => $order['quantity'],
                  "price" => $order['price'],
                  "order_product_id" => $option['order_product_id'],
                  "itemName" => $textOrderItemId + $order['orderItemId'],
                  "itemUrl" => $order['itemUrl'],
                  "imageUrl" => $order['imagePath'],
                  "whoBuys" => $option['value'],
                  "color" => $order['options']['14970']['value'],
                  "size" => $order['options']['14971']['value'],
                  "comment" => $order['comment'],
                  "shopName" => $order['shopName']);
                ?>
                
                <table>
                  <tr>
                    <td rowspan="7">
                      <a href="<?= $order['imageUrl'] ?>" target="_blank">
                        <image src="<?= $order['imagePath'] ?>" title="<?= $order['hint'] ?>"/>
                      </a>
                    </td>
                    <td style="white-space: nowrap;">
                      <b><?= $textTimeAdded ?></b> <?= $order['timeAdded'] ?><br />
                    </td>
                    <td rowspan="6">
                      <a href="<?= $order['itemUrl'] ?>" style="text-decoration: none;">
                        <?php foreach ($order['options'] as $option): ?>
                          &nbsp;<small> - <?= $option['name'] ?>: <?= substr($option['value'], 0, 60) . (strlen($option['value']) > 60 ? '...' : '') ?></small>
                          <br />
                        <?php endforeach; ?>
                      </a>
                    </td>
                  </tr>
                  <tr>
                    <td><b><?= $textPrice ?>:&nbsp;</b><?= $order['price'] ?></td>
                  </tr>
                  <tr><td><b><?= $textQuantity ?>:&nbsp;</b> <?= $order['quantity'] ?></td></tr>
                  <tr><td><b><?= $textSubtotal ?>:&nbsp;</b><?= $order['subtotal'] ?></td></tr>
                  <tr><td><b><?= $textShipping ?>:&nbsp;</b><?= $order['shipping'] ?><br />
                  <tr><td><b><?= $textTotal ?>:&nbsp;</b><?= $order['total'] ?><br /></td></tr>
                  <tr><td colspan="2"><b><?= $textComment ?>:</b> <?= $order['comment'] ?></td></tr>
                </table>
              <div id="buttoms" style="width: 100%; display: block; padding-top: 15px;">
                <?php if ($order['textAccept']): ?>
                  <a id="accept<?= $order['orderItemId'] ?>" style="position: relative; float: left;" onclick="accept(<?= $order['orderItemId'] ?>);" class="button">
                    <span><?= $order['textAccept'] ?></span>
                  </a>
                <?php endif; ?>
                <?php if ($order['statusId'] == REPURCHASE_ORDER_ITEM_STATUS_OFFER): ?>
                  <a id="reject<?= $order['orderItemId'] ?>" style="position: relative; float: left;" onclick="reject(<?= $order['orderItemId'] ?>);" class="button">
                    <span><?= $textReject ?></span>
                  </a>
                <?php endif; ?>
                <a id="addToCart<?= $order['orderItemId'] ?>" 
                  style="position: relative; float: right; right: 10px;" 
                  onclick="addOrderToCartAgent(<?= $order['orderItemId'] ?>,
                  <?= $dataCart[$order['orderItemId']]['quantity'] ?>,
                  '<?= $dataCart[$order['orderItemId']]['price'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['itemName'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['itemUrl'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['imageUrl'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['whoBuys'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['color'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['size'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['comment'] ?>',
                  '<?= $dataCart[$order['orderItemId']]['shopName'] ?>'
                  );" 
                  class="button">
                  <span><?= $textAddToCart ?></span>
                </a>

              </div>
            </div>
          </div>
          <br />
        <?php endforeach; ?>
        <div class="pagination"><?= $pagination ?></div>
      <?php else: ?>
        <div class="content"><?= $textNoItems ?></div>
      <?php endif; ?>
    </form>
</div>

<script type="text/javascript">//<!--
$(document).ready(function() {
  $('[name=filterStatusId\\[\\]]').multiselect({
    noneSelectedText: "-- No filter --",
    selectedList: 3
  });
  $('button.ui-multiselect').css('width', '110px');
});

function accept(orderId)
{
  respondToOffer(orderId, 'accept');
}

function filter()
{
//    $('#form').attr('action', 'index.php?route=account/repurchaseOrders');
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
    beforeSend: function()
    {
      $('.button#accept' + orderId).attr('disabled', true);
      $('.button#reject' + orderId).attr('disabled', true);
      $('.button#' + response + orderId).after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
    },
    complete: function()
    {
      $('.button#accept' + orderId).attr('disabled', false);
      $('.button#reject' + orderId).attr('disabled', false);
      $('.wait').remove();
    },
    success: function(json)
    {
      $('#orderStatus' + orderId).text(json['newStatusName']);
      $('.button#accept' + orderId).remove();
      $('.button#reject' + orderId).remove();
    },
    error: function(jqXHR, textStatus, errorThrown) {
      alert(jqXHR.responseText);
    }
  })
}

function addOrderToCartAgent($indexAgent,$indexQuantity,$indexPrice,$indexName,$indexItemUrl,$indexImageUrl,$indexWhoBuys,$indexColor,$indexSize,$indexComment,$indexShopName)
{
  $jsIndexName = "Order ID:" + $indexName;
  $('.order-content'+$indexAgent).each(function() {
    $.ajax({
      url: 'index.php?route=checkout/cart/update',
      type: 'post',
      data: {
        'quantity': $indexQuantity,
        'itemPrice': '',
        'product_id': '8608',
        'option[103067]': $jsIndexName,
        'option[14968]': $indexItemUrl,
        'option[14967]': $indexImageUrl,
        'option[18518]': $indexWhoBuys,
        'option[14970]': $indexColor,
        'option[14971]': $indexSize,
        'option[14969]': $indexComment,
        'option[103066]': $indexShopName
      },
      dataType: 'json',
      beforeSend: function()
      {
        $('#addToCart'+$indexAgent).attr('disabled', true);
        $('#addToCart'+$indexAgent).after('<span class="wait">&nbsp;<img src="catalog/view/theme/default/image/loading.gif" alt="" /></span>');
      },
      complete: function()
      {
        $('.wait').remove();
        $('#addToCart'+$indexAgent).attr('disabled', false);
      },
      error: function(jqXHR, textStatus, errorThrown) {
        alert(jqXHR.responseText);
      },
      success: function(json)
      {
        $('.success, .warning, .attention, .information, .error').remove();
        if (json['error']) {
          if (json['error']['warning'])
          {
            $('#notification').html('<div class="error" style="display: none;">' + json['error']['warning'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
            $('.warning').fadeIn('slow');
          }
          for (i in json['error']) {
            $('#option-' + i).after('<span class="error">' + json['error'][i] + '</span>');
          }
        }
        if (json['success'])
        {
          $('#notification').html('<div class="success" style="display: none;">' + json['success'] + '<img src="catalog/view/theme/default/image/close.png" alt="" class="close" /></div>');
          $('.success').fadeIn('slow');
          $('#cart_total_data').html(json['total_data']);
          $('html, body').animate({ scrollTop: 0 }, 'slow');
        }
      }
    });
  });
}

//--></script>
<?= $footer ?>