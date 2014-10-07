<p><?php echo $text_total; ?><b> <?php echo $total; ?></b>.</p>
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
<?php if (!empty($requests) && is_array($requests)):
    foreach ($requests as $request): ?>
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
    <?php endforeach;
else: ?>
    <tr><td class="center" colspan="5"><?php echo $text_empty; ?></td></tr>
<?php endif; ?>
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
    <td class="right"><?= $textAction ?></td>
  </tr>
</thead>
<tbody>
<?php if (!empty($transactions) && is_array($transactions)):
    foreach ($transactions  as $transaction): ?>
  <tr>
    <td><?= $transaction['transactionId'] ?></td>
    <td class="left"><?php echo $transaction['date_added']; ?></td>
    <td class="left"><a href="<?= $transaction['invoiceUrl'] ?>"><?= $transaction['invoiceId'] ?></a></td>
    <td class="left"><?php echo $transaction['description']; ?></td>
    <td class="right"><?php echo $transaction['incomeAmount']; ?></td>
    <td><?= $transaction['expenseAmount'] ?></td>
    <td class="left"><?= $transaction['currency_code'] ?></td>
    <td><?= $transaction['balance'] ?></td>
    <td class="right">
        <?php foreach ($transaction['actions'] as $action): ?>
      [ <a onclick="<?= $action['onclick'] ?>"><?= $action['text'] ?></a> ]
        <?php endforeach; ?>
    </td>
  </tr>
    <?php endforeach;
else: ?>
  <tr><td class="center" colspan="8"><?php echo $text_empty; ?></td></tr>
<?php endif; ?>
</tbody>
</table>
<div class="pagination"><?= $transactionsPagination; ?></div>
