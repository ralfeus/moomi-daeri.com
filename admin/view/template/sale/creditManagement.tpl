<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <?= $breadcrumb['separator'] ?><a href="<?php echo $breadcrumb['href']; ?>"><?= $breadcrumb['text'] ?></a>
        <?php endforeach; ?>
    </div>
    <h1><?php echo $headingTitle; ?></h1>
    <?php foreach ($notifications as $class => $notification)
        echo "<div class=\"$class\">" . nl2br(print_r($notification, true)) . "</div>";
    ?>
    <table class="list">
        <thead>
            <tr>
                <td><?= $textRequestId ?></td>
                <td><?= $textCustomer ?></td>
                <td><?= $textAmount ?></td>
                <td><?= $textTimeAdded ?></td>
                <td><?= $textStatus ?></td>
                <td><?= $textComment ?></td>
                <td><?= $textActions ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($requests as $request): ?>
                <tr>
                    <td><?= $request['requestId'] ?></td>
                    <td style="white-space: nowrap;"><a href="<?= $request['customerUrl'] ?>"><?= $request['customerName'] ?></a></td>
                    <td style="white-space: nowrap;">
                        <input
                            alt="<?= $request['amount'] ?>"
                            onblur="saveAmount(<?= $request['requestId'] ?>, this)"
                            onkeydown="if (event.keyCode == 13) saveAmount(<?= $request['requestId'] ?>, this)"
                            size=3
                            value="<?= $request['amount'] ?>"
                            <?= $request['statusId'] != ADD_CREDIT_STATUS_PENDING ? "disabled" : "" ?> />
                        <?= $request['currency'] ?>
                    </td>
                    <td style="white-space: nowrap;"><?= $request['timeAdded'] ?></td>
                    <td id="status"><?= $request['status'] ?></td>
                    <td><?= $request['comment'] ?></td>
                    <td class="right">
                        <?php foreach ($request['actions'] as $action): ?>
                            [&nbsp;<a onclick="<?= $action['onclick'] ?>"><?= $action['text']; ?></a>&nbsp;]
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script type="text/javascript">//<!--
function acceptRequest(requestId, sender)
{
    $.ajax({
        url: 'index.php?route=sale/creditManagement/accept&token=<?= $this->session->data['token'] ?>&requestId=' + requestId,
        success: function() {
            $(sender.parentElement.parentElement).find('#status').text("<?= $this->load->model('localisation/requestStatus')->getStatus(ADD_CREDIT_STATUS_ACCEPTED) ?>");
            $(sender.parentElement).text("");
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('acceptRequest():\n' + jqXHR.responseText);
        }
    });
}

function rejectRequest(requestId, sender)
{
    $.ajax({
        url: 'index.php?route=sale/creditManagement/reject&token=<?= $this->session->data['token'] ?>&requestId=' + requestId,
        success: function() {
            $(sender.parentElement.parentElement).find('#status').text("<?= $this->load->model('localisation/requestStatus')->getStatus(ADD_CREDIT_STATUS_REJECTED) ?>");
            $(sender.parentElement).text("");
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert('acceptRequest():\n' + jqXHR.responseText);
        }
    });
}

function saveAmount(requestId, control) {
    if (control.value == control.alt)
        return;
    if (!isFinite(control.value))
    {
        $(control).backgroundColor('red');
        return;
    }
    var tempHandler = control.onblur;
    control.onblur = null;
    $.ajax({
        url: 'index.php?route=sale/creditManagement/saveAmount',
        data: {
            'token': '<?= $token ?>',
            'requestId': requestId,
            'amount': control.value
        },
        beforeSend: function() {
            $(control).attr("disabled", true);
            $(control).after('<div class="wait"><img src="view/image/loading.gif" alt="" /></div>');
        },
        complete: function() {
            $(control).attr("disabled", false);
            $('.wait').remove();
            control.onblur = tempHandler;
        },
        error: function(jqXHR, textStatus, errorThrown) {
            alert("saveQuantity(): " + jqXHR.responseText);
        },
        success: function() {
            control.alt = control.value;
        }
    });
}
//--></script>
<?php echo $footer; ?>