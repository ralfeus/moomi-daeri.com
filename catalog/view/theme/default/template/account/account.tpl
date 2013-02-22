<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
    <?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">" . nl2br(print_r($notification, true)) . "</div>";
    ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
      <div class="box-heading"><h2><?php echo $text_my_account; ?></h2></div>
      <div class="box-content">
        <ul>
          <li><a href="<?php echo $edit; ?>"><?php echo $text_edit; ?></a></li>
          <li><a href="<?php echo $password; ?>"><?php echo $text_password; ?></a></li>
          <li><a href="<?php echo $address; ?>"><?php echo $text_address; ?></a></li>
          <li><a href="<?php echo $wishlist; ?>"><?php echo $text_wishlist; ?></a></li>
        </ul>
      </div>
  </div>
  <div class="box">
      <div class="box-heading"><h2><?php echo $text_my_orders; ?></h2></div>
      <div class="box-content">
        <ul>
          <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
          <li><a href="<?= $orderItems ?>"><?= $textOrderItems ?></a></li>
          <li><a href="<?= $repurchaseOrders ?>"><?= $textRepurchaseOrders ?></a></li>
          <li><a href="<?= $invoice ?>"><?= $text_invoices ?></a></li>
          <!--li><a href="<?php echo $download; ?>"><?php echo $text_download; ?></a></li-->
          <?php /*<li><a href="<?php echo $reward; ?>"><?php echo $text_reward; ?></a></li>*/ ?>
          <li><a href="<?php echo $transaction; ?>"><?php echo $text_transaction; ?></a></li>
          <li><a href="<?= $addCreditUrl ?>"><?= $textAddCredit ?></a></li>
          <!--li><a href="<?= $creditHistoryUrl ?>"><?= $textCreditHistory ?></a></li-->
        </ul>
      </div>
  </div>
  <div class="box">
      <div class="box-heading"><h2><?php echo $text_my_newsletter; ?></h2></div>
      <div class="box-content">
        <ul>
          <li><a href="<?php echo $newsletter; ?>"><?php echo $text_newsletter; ?></a></li>
        </ul>
      </div>
  </div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?> 