<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
  <div class="top">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center">
      <h1><?php echo $heading_title; ?></h1>
    </div>
  </div>
  <div  class="middle" style=" width:auto; float:left">
    <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>

<h2><?php echo $text_my_account; ?></h2>

  <div class="content">
    <ul>
      <div style="float: left; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $edit; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/edit1.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $text_edit; ?>
          <br/>
        </a>
      </div>
      <div style="float: right; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $password; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/password.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $text_password; ?>
          <br/>
        </a>
      </div>
      <div style="float: left; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $address; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/delivery.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $text_address; ?>
          <br/>
        </a>
      </div>
      <div  style="float: right; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $wishlist; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/wishlist.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $text_wishlist; ?>
          <br/>
        </a>
      </div>
    </ul>
  </div>


  <h2><?php echo $text_my_orders; ?></h2>
  <div class="content">
    <ul>
      <div style="float: left; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $order; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/orders.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $text_order; ?>
          <br/>
        </a>
      </div>
      <div style="float: right; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $orderItems; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/orders.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $textOrderItems; ?>
          <br/>
        </a>
      </div>
      <div style="float: left; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $repurchaseOrders; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/return.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $textRepurchaseOrders; ?>
          <br/>
        </a>
      </div>
      <div style="float: right; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $invoice; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/orders.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $text_invoices; ?>
          <br/>
        </a>
      </div>
      <div style="float: left; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $reward; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/reward.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $text_reward; ?>
          <br/>
        </a>
      </div>
      <div style="float: right; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $addCreditUrl; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/reward.png" 
            style="float: left; margin-right: 8px;"
          />
          <?php echo $textAddCredit; ?>
          <br/>
        </a>
      </div>
      <div style="float: left; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $transaction; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/trans.png" 
            style="text-decoration:none;float: left; margin-right: 8px;"
          />
          <?php echo $text_transaction; ?>
          <br/>
        </a>
      </div>
    </ul>
  </div>
  <h2><?php echo $text_my_newsletter; ?></h2>
  <div class="content">
    <ul>
      <div style="float: left; width: 250px; margin-bottom: 10px; padding: 5px;">
        <a href="<?php echo $newsletter; ?>" style="text-decoration:none;font-weight: bold;">
          <img src="/catalog/view/theme/default/image/newsletter.png" 
            style="text-decoration:none;float: left; margin-right: 8px;"
          />
          <?php echo $text_newsletter; ?>
          <br/>
        </a>
      </div>
    </ul>
  </div>

  </div>
  <div class="bottom" style="width:580px; float:left;">
    <div class="left"></div>
    <div class="right"></div>
    <div class="center"></div>
  </div>
</div>
<?php echo $footer; ?> 