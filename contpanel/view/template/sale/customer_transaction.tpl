<?php if ($error_warning) { ?>
<div class="error"><?php echo $error_warning; ?></div>
<?php } ?>
<?php if ($success) { ?>
<div class="success"><?php echo $success; ?></div>
<?php } ?>
<table class="list">
  <thead>
    <tr>
      <td><?= $textTransactionId ?></td>
      <td class="left"><?php echo $column_date_added; ?></td>
      <td><?= $textInvoiceId ?></td>
      <td class="left"><?php echo $column_description; ?></td>
      <td class="right"><?php echo $column_amount; ?></td>
      <td class="right"><?= $textAction ?></td>
    </tr>
  </thead>
  <tbody>
    <?php if ($transactions) { ?>
    <?php foreach ($transactions as $transaction) { ?>
    <tr>
      <td><?= $transaction['transactionId'] ?></td>
      <td class="left"><?php echo $transaction['date_added']; ?></td>
      <td><a href="<?= $transaction['invoiceUrl'] ?>"><?= $transaction['invoiceId'] ?></a></td>
      <td class="left"><?php echo $transaction['description']; ?></td>
      <td class="right"><?php echo $transaction['amount']; ?></td>
      <td class="right">
          <?php foreach ($transaction['actions'] as $action): ?>
            [ <a onclick="<?= $action['onclick'] ?>"><?= $action['text'] ?></a> ]
          <?php endforeach; ?>
      </td>
    </tr>
    <?php } ?>
    <tr>
      <td class="right" colspan="4"><b><?php echo $text_balance; ?></b></td>
      <td class="right"><b><?php echo $balance; ?></b></td>
    </tr>
    <?php } else { ?>
    <tr>
      <td class="center" colspan="3"><?php echo $text_no_results; ?></td>
    </tr>
    <?php } ?>
  </tbody>
</table>
<div class="pagination"><?php echo $pagination; ?></div>