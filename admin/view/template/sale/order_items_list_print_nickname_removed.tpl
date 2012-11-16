<html>
<head>
    <link rel="stylesheet" type="text/css" href="view/stylesheet/stylesheet.css" />
</head>
<body onLoad="Print()">
<div id="content">
    <?php if ($error_warning) { ?>
    <div class="error"><?php echo $error_warning; ?></div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="success"><?php echo $success; ?></div>
    <?php } ?>
    <div class="box">
        <p>
            <?= $storeName ?><br />
            <?= $storeAddress ?><br />
            <?= $storePhone ?><br />
        </p>
        <div class="heading">
            <h1><img src="view/image/order.png" alt="" /> <?php echo $heading_title; ?></h1>
        </div>
        <div class="content">
            <form action="" method="post" enctype="multipart/form-data" id="form">

                <table class="list">
                    <thead>
                    <tr>
                        <td class="right"><?php echo $column_order_id; ?></td>
                        <td class="right"><?php echo $column_order_item_id; ?></td>
                        <td class="right">
                            <?php echo $column_item_image; ?>
                        </td>
                        <td class="right">
                            <?php echo $column_item; ?>
                        </td>
                        <td class="right">
                            <?php echo $column_quantity; ?>
                        </td>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($order_items): ?>
                    <?php foreach ($order_items as $order_item): ?>
                    <tr>
                        <td class="right"><?php echo $order_item['order_id']; ?></td>
                        <td class="right"><?php echo $order_item['id']; ?></td>
                        <td class="right"><img src="<?php echo $order_item['image_path']; ?>" /></td>
                        <td class="right">
                            <table height="100%" width="100%">
                                <tr valign="center"><td><?php echo $order_item['name']; ?></td></tr>
                                <tr valign="center"><td><?php echo $order_item['name_korean']; ?></td></tr>
                                <tr><td><?php echo $order_item['options']; ?></td></tr>
                            </table>
                        </td>
                        <td class="right"><?php echo $order_item['quantity']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td class="center" colspan="11"><?php echo $text_no_results; ?></td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript"><!--
function Print()
{
    document.body.offsetHeight;
    window.print();
};
//--></script>
</body>
</html>