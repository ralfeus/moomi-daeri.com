<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $text_total; ?><b> <?php echo $total; ?></b>.</p>
    <div class="buttons">
        <div class="right"><a href="<?= $addCreditUrl ?>" class="button"><span><?= $textAddCredit ?></span></a></div>
    </div>
  <table class="list">
    <thead>
      <tr>
        <td><?= $textTransactionId ?></td>
        <td class="left"><?php echo $column_date_added; ?></td>
        <td class="left"><?= $textInvoiceId ?></td>
        <td class="left"><?php echo $column_description; ?></td>
        <td class="right"><?php echo $column_amount; ?></td>
        <td class="left"><?= $textCurrency ?></td>
      </tr>
    </thead>
    <tbody>
      <?php if ($transactions) { ?>
      <?php foreach ($transactions  as $transaction) { ?>
      <tr>
        <td><?= $transaction['transactionId'] ?></td>
        <td class="left"><?php echo $transaction['date_added']; ?></td>
        <td class="left"><a href="<?= $transaction['invoiceUrl'] ?>"><?= $transaction['invoiceId'] ?></a></td>
        <td class="left"><?php echo $transaction['description']; ?></td>
        <td class="right"><?php echo $transaction['amount']; ?></td>
        <td class="left"><?= $transaction['currency_code'] ?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="center" colspan="5"><?php echo $text_empty; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div class="pagination"><?php echo $pagination; ?></div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>