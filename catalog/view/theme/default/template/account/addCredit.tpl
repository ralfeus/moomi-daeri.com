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
            <tr>
                <td><?= $textAmount ?></td>
                <td><input name="amount" value="<?= $amount ?>" /></td>
                <td>
                    <select name="currency">
                        <?php foreach ($currencies as $currency): ?>
                            <option value="<?= $currency['code'] ?>" <?= $currency['selected'] ?>><?= $currency['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?= $textComment ?></td>
                <td colspan="2">
                    <input name="comment" value="<?= $comment ?>" type="text" multiline="true" />
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <div class="buttons"><div class="right">
                        <a class="button" onclick="$('#form').submit();"><span><?= $textSubmit ?></span></a></div>
                    </div>
                </td>
            </tr>
        </table>
    </form>
</div>
<?= $footer ?>