<html>
<head>
    <link href="view/stylesheet/print.css" type="text/css" rel="stylesheet" />
</head>
<body onload="Print();">
    <div id="content">
        <div class="heading">
            <h1><img src="view/image/order.png" alt="" /><?= $headingTitle ?></h1>
        </div>
        <div class="box">
            <form action="" id="form" method="post" enctype="multipart/form-data">
                <table class="list">
                    <thead>
                        <tr>
                            <td><?= $textOrderId ?></td>
                            <td><?= $textItemImage ?></td>
                            <td><?= $textCustomer ?></td>
                            <td><?= $textSiteName ?></td>
                            <td><?= $textQuantity ?></td>
                            <td><?= $textAmount ?> (<?= $currencyCode ?>)</td>
                            <td><?= $textStatus ?></td>
                            <td style="width: auto;" "><?= $textComment ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['orderId'] ?></td>
                                <td>
                                    <?php if ($order['originalImagePath']):
                                        $href = $order['originalImagePath'];
                                    else:
                                        $href = $order['itemUrl'];
                                    endif; ?>
                                    <img src="<?= $order['imagePath'] ?>" />
                                </td>
                                <td style="white-space: nowrap;"><?= $order['customerNick'] ?></td>
                                <td><?= $order['siteName'] ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td><?= $order['amount'] ?></td>
                                <td id="status"><?= $order['status'] ?></td>
                                <td>
                                    <p>
                                        Private<br />
                                        <?= $order['comment'] ?>
                                    </p>
                                    <p>
                                        Public<br />
                                        <?= $order['publicComment'] ?>
                                    </p>
                                    <span style="color: red; "><?= $order['options'] ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</body>
<script type="text/javascript"><!--
function Print()
{
    document.body.offsetHeight;
    window.print();
};
//--></script>
</html>