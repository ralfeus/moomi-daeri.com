<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $text_total; ?><b> <?php echo $total; ?></b>.</p>
  
<?php // ----- deposit modules START ----- ?>    
  <div class="buttons">
				<div class="right">
					<a href="?route=account/multi_pay/deposit" class="button">
					<?php echo ($text_deposit!='text_deposit'?$text_deposit:'Deposit'); ?></a>
					<a href="?route=account/multi_pay/transfer" class="button">
					<?php echo ($text_transfer!='text_transfer'?$text_transfer:'Transfer'); ?></a>
				</div>
			</div>
<?php // ----- deposit modules END ----- ?>    
      
    <div class="buttons">
        <div class="right"><a href="<?= $addCreditUrl ?>" class="button"><span><?= $textAddCredit ?></span></a></div>
    </div>
    <table class="list">
        <thead>
            <tr>
                <td class="left"><?= $textRequestId ?></td>
                <td><?= $textAmount ?></td>
                <td><?= $textTimeAdded ?></td>
                <td><?= $textStatus ?></td>
                <td><?= $textComment ?></td>
            </tr>
        </thead>
        <tbody>
<?php foreach ($requests as $request): ?>
            <tr>
                <td><?= $request['requestId'] ?></td>
                <td style="white-space: nowrap;">
                    <input
                            alt="<?= $request['amount'] ?>"
                            onblur="saveAmount(<?= $request['requestId'] ?>, this)"
                            onkeydown="if (event.keyCode == 13) saveAmount(<?= $request['requestId'] ?>, this)"
                            size=3
                            value="<?= $request['amount'] ?>"
                    <?= $request['statusId'] != ADD_CREDIT_STATUS_PENDING ? "disabled" : "disabled" ?> />
                    <?= $request['currency'] ?>
                </td>
                <td style="white-space: nowrap;"><?= $request['timeAdded'] ?></td>
                <td id="status"><?= $request['status'] ?></td>
                <td><?= $request['comment'] ?></td>
            </tr>
<?php endforeach; ?>
        </tbody>
    </table>
    <div class="pagination"><?= $creditRequestsPagination; ?></div>

    <table class="list">
    <thead>
      <tr>
        <td><?= $textTransactionId ?></td>
        <td class="left"><?= $textTimeAdded ?></td>
        <td class="left"><?= $textInvoiceId ?></td>
        <td class="left"><?= $textComment ?></td>
          <td><?= $textIncomeAmount ?></td>
          <td><?= $textExpenseAmount ?></td>
        <td class="left"><?= $textCurrency ?></td>
          <td><?= $textBalance ?></td>
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
        <td class="right"><?php echo $transaction['incomeAmount']; ?></td>
          <td><?= $transaction['expenseAmount'] ?></td>
        <td class="left"><?= $transaction['currency_code'] ?></td>
          <td><?= $transaction['balance'] ?></td>
      </tr>
      <?php } ?>
      <?php } else { ?>
      <tr>
        <td class="center" colspan="5"><?php echo $text_empty; ?></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  <div class="pagination"><?= $transactionsPagination; ?></div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>