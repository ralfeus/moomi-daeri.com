<table class="list">
    <thead>
        <tr>
            <td><?= $textOrderId ?></td>
            <td><?= $textOrderItemId ?></td>
            <td><?= $textEventDate ?></td>
            <td><?= $textStatusName ?></td>
        </tr>
    </thead>
    <tbody>
<?php foreach ($events as $event): ?>
        <tr>
            <td><?= $event['orderId'] ?></td>
            <td><?= $event['orderItemId'] ?></td>
            <td><?= $event['eventDate'] ?></td>
            <td><?= $event['statusName'] ?></td>
        </tr>
<?php endforeach; ?>
    </tbody>
</table>