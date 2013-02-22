<?= $header ?><?= $column_left ?><?= $column_right ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb)
            echo $breadcrumb['separator'] . "<a href=\"" . $breadcrumb['href'] . "\">" . $breadcrumb['text'] . "</a>";
        ?>
    </div>
    <?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">" . nl2br(print_r($notification, true)) . "</div>";
    ?>
    <form id="form" action="<?= $action ?>" method="post">
        <table class="form">
            <thead>
                <tr>
                    <td><?= $textRequestId ?></td>
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
        </table>
    </form>
</div>
<?= $footer ?>