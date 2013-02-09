<?= $header ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb):
            echo $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
        <?php endforeach; ?>
    </div>
    <?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">$notification</div>";
    ?>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/order.png" alt="" /> <?= $headingTitle ?></h1>
        </div>
        <div class="content">
            <form action="" method="post" enctype="multipart/form-data" id="form">
                <table class="list">
                    <thead>
                        <tr>
                            <td class="right"><?= $textInvoiceId ?></td>
                            <td class="left"><?= $textCustomer ?></td>
                            <td class="right"><?= $textShippingMethod ?></td>
                            <td class="right"><?= $textWeight ?></td>
                            <td class="right"><?= $textSubtotal ?></td>
                            <td class="right"><?= $textShippingCost ?></td>
                            <td class="right"><?= $textTotal ?></td>
                            <td class="right"><?= $textTotalCustomerCurrency ?></td>
                            <td class="right"><?= $textStatus ?></td>
                            <td class="right"><?= $textAction ?></td>
                        </tr>
                        <tr class="filter">
                            <td />
                            <td>
                                <select name="filterCustomerId[]" multiple="true">
                                    <?php foreach ($customers as $key => $value):
                                    $selected = in_array($key, $filterCustomerId) ? 'selected' : ''; ?>
                                    <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td /><td /><td /><td /><td /><td /><td />
                            <td align="right"><a onclick="filter();" class="button"><?= $textFilter ?></a></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($invoices)):
                            foreach ($invoices as $invoice): ?>
                                <tr>
                                    <td class="right"><?= $invoice['invoiceId']; ?></td>
                                    <td>
                                        <a href="index.php?route=sale/customer/update&token=<?= $this->session->data['token'] ?>&customer_id=<?= $invoice['customerId'] ?>">
                                            <?= $invoice['customer'] ?>
                                        </a>
                                    </td>
                                    <td class="right"><?= $invoice['shippingMethod'] ?></td>
                                    <td class="right"><?= $invoice['weight'] ?></td>
                                    <td class="right"><?= $invoice['subtotal'] ?></td>
                                    <td class="right"><?= $invoice['shippingCost'] ?></td>
                                    <td class="right"><?= $invoice['total'] ?></td>
                                    <td class="right"><?= $invoice['totalCustomerCurrency'] ?></td>
                                    <td class="right"><?= $invoice['status'] ?></td>
                                    <td class="right">
                                        <?php foreach ($invoice['action'] as $action):
                                            $href = empty($action['href']) ? '' :  'href="' . $action['href'] . '"';
                                            $onclick = empty($action['onclick']) ? '' : 'onclick="' . $action['onclick'] . '"'; ?>
                                            [<a <?= $href ?> <?= $onclick ?>><?= $action['text'] ?></a>]
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                        else: ?>
                            <tr><td colspan="7"><?= $text_no_entries ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                </table>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">//<!--
$(document).ready(function() {
    $('[name=filterCustomerId\\[\\]]')
        .multiselect({
            noneSelectedText: "No filter",
            selectedList: 1
        })
        .multiselectfilter();
    $('button.ui-multiselect').css('width', '110px');
});

function confirmDeletion(url)
{
    if (!confirm('The invoice has transaction associated. Are you sure you want to delete both?'))
        return false;
    window.location = url;
}

function filter() {
    $('#form').attr('action', 'index.php?route=sale/invoice&token=<?= $token ?>');
    $('#form').submit();
    return;
}
//--></script>
<?= $footer ?>