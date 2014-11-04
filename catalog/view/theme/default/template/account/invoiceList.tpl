<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>
    <h1><?php echo $headingTitle; ?></h1>
    <?php if (isset($invoices)): ?>
        <?php foreach ($invoices as $invoice): ?>
            <div class="order-list" style="min-height: 60px">
                <div class="order-id" style="width:100%">
                    <b><?php echo $textInvoiceId; ?></b>&nbsp;<?php echo $invoice['invoiceId']; ?>
                    <?= $invoice['status'] ?>
                    <?php if (!empty($invoice['transaction'])): ?>
                        <a href="index.php?route=account/transaction" style="float: right; padding-right: 10px;">
                            <?= $textTransaction ?> #<?= $invoice['transaction']['customer_transaction_id'] ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="order-content">
                    <div><b><?= $textDateAdded ?></b> <?php echo $invoice['timeModified']; ?><br />
                        <b><?= $textItemsCount ?></b> <?php echo $invoice['itemsCount']; ?></div>
                    <div><b><?= $textShippingMethod ?></b> <?php echo $invoice['shippingMethod']; ?><br />
                        <b><?php echo $textTotal; ?></b> <?php echo $invoice['total']; ?></div>
                    <div> <b><?php echo $textPackage; ?></b> <?php echo $invoice['package_number']; ?> </div>
                    <div class="order-info" style="float: right;"><a href="<?php echo $invoice['href']; ?>" style="margin-right: 15px; margin-top: 10px;" class="button"><span><?php echo $buttonView; ?></span></a></div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php /*<div class="pagination"><?php echo $pagination; ?></div>*/ ?>
    <?php else: ?>
        <div class="content"><?= $textNoItems ?></div>
    <?php endif; ?>
    <?php echo $content_bottom; ?>
</div>
<?php echo $footer; ?>