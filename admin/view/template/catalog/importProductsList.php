<?= $header ?>
<div id="content">
    <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb): ?>
        <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php endforeach; ?>
    </div>
    <h1><?= $headingTitle ?></h1>
<?php foreach ($notifications as $class => $notification)
    echo "<div class=\"$class\">" . nl2br(print_r($notification, true)) . "</div>";
?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/error.png" alt="" /> <?= $headingTitle ?></h1>
      <div class="buttons">
          <a id="startParsing" class="button">Loading...</a>
          <a onclick="submitForm('<?= $urlSyncSelected ?>');" class="button"><?= $textUpdateSelected ?></a>
          <a onclick="submitForm('<?= $urlDeleteSelected ?>');" class="button"><?= $textDeleteSelected ?></a>
          <a onclick="submitForm('<?= $urlSyncAll ?>');" class="button"><?= $textUpdateAll ?></a>
          <a onclick="submitForm('<?= $urlDeleteAll ?>');" class="button"><?= $textDeleteAll ?></a>
      </div>
    </div>
    <div class="content">
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selectedItems\']').attr('checked', this.checked);" /></td>
              <td><?= $textId ?></td>
              <td><?= $textProductId ?></td>
              <td><?= $textImage ?></td>
              <td><?= $textItem ?></td>
              <td><?= $textSourceSite ?></td>
              <td><?= $textPrice ?></td>
              <td><?= $textStatus ?></td>
              <td><?= $textTimeModified ?></td>
              <td><?= $textActions ?></td>
            </tr>
            <tr class="filter">
                <td />
                <td />
                <td />
                <td />
                <td><input type="text" name="filterItem" value="<?= $filterItem ?>" /></td>
                <td>
                    <select name="filterSourceSiteId[]" multiple="true">
<?php foreach ($sourceSites as $id => $name):
    $selected = in_array($id, $filterSourceSiteId) ? 'selected' : '';
?>
                        <option value="<?= $id ?>" <?= $selected ?> ><?= $name ?></option>
<?php endforeach; ?>
                    </select>
                </td>
                <td />
                <td>
                    <select name="filterIsActive" multiple="true">
                        <option>No filter</option>
                        <option value="1" <?= $filterIsActive === '1' ? 'selected' :  '' ?>>Active</option>
                        <option value="0" <?= $filterIsActive === '0' ? 'selected' :  '' ?>>Inactive</option>
                    </select>
                </td>
                <td />
                <td><a onclick="filter();" class="button"><?= $textFilter; ?></a></td>
            </tr>
          </thead>
          <tbody>
<?php if (isset($products) && is_array($products)): ?>
    <?php foreach ($products as $product): ?>
            <tr>
              <td><input type="checkbox" name="selectedItems[]" value="<?= $product->getId() ?>"</td>
              <td><?= $product->getId() ?></td>
              <td><a href="<?= $product->localProductUrl ?>"><?= $product->getLocalProductId() ?></a></td>
              <td><img src="<?= $product->getThumbnailUrl() ?>" /></td>
              <td>
                <a href="<?= $product->getSourceUrl() ?>" target="_blank"><?= $product->getName() ?></a>
                <?php /*<p><?= $product->getDescription() ?></p>*/ ?>
              </td>
              <td><?= $product->getSourceSite()->getName() ?></td>
              <td>
                <table>
                  <tbody>
                    <tr>
        <?php if ($product->getSourcePrice()->getPromoPrice()): ?>
                      <td rowspan="2"><?= $textSource ?>:</td>
                      <td><strike><?= $product->getSourcePrice()->getPrice() ?></strike></td>
                    </tr>
                    <tr>
                      <td style="color: red;"><?= $product->getSourcePrice()->getPromoPrice() ?></td>
        <?php else: ?>
                      <td><?= $textSource ?>:</td>
                      <td><?= $product->getSourcePrice()->getPrice() ?> </td>
        <?php endif; ?>
                    </tr>
        <?php if ($product->getLocalPrice()) : ?>
                    <tr>
            <?php if ($product->getLocalPrice()->getPromoPrice()): ?>
                      <td rowspan="2"><?= $textLocal ?>:</td>
                      <td><strike><?= $product->getLocalPrice()->getPrice() ?></strike></td>
                    </tr>
                    <tr>
                      <td style="color:red;"><?= $product->getLocalPrice()->getPromoPrice() ?></td>
            <?php else: ?>
                      <td><?= $textLocal ?>:</td>
                      <td><?= $product->getLocalPrice()->getPrice() ?> </td>
            <?php endif; ?>
                    </tr>
        <?php endif; ?>
                  </tbody>
                </table>
              </td>
              <td><?= $product->getIsActive() ? "Active" : "Inactive" ?></td>
              <td><?= $product->getTimeModified() ?></td>
              <td>
        <?php foreach ($product->actions as $action): ?>
                [ <a href="<?= $action->href ?>"><?= $action->text ?></a> ]
        <?php endforeach; ?>
              </td>
            </tr>
    <?php endforeach; ?>
<?php else: ?>
            <tr>
              <td class="center" colspan="9"><?= $textNoItems ?></td>
            </tr>
<?php endif; ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?= $pagination ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
function parserStatus() {
    $.ajax({
        url: "index.php?route=catalog/import/parser&" + ("" + location).match(/token=[^&]+/)[0],
        cache: false,
        dataType: 'json'
        }).done(function(data) {
            if (data.status) {
                $('#startParsing').html("Parsing is running - It was started at " + data.stime);
            } else {
                $('#startParsing').html('Start parsing');
            }
        });
}
$(document).ready(function() {

    $('#startParsing').click(function() {
        $.ajax({
            url: "index.php?route=catalog/import/parser&a=run&" + ("" + location).match(/token=[^&]+/)[0],
            cache: false,
            dataType: 'html'
            }).done(function(data) {
                parserStatus();
            });
    });

    setInterval(parserStatus, 60000*5);
    parserStatus();

    $('.date').datepicker({dateFormat: 'yy-mm-dd'});

    $('[name=filterSourceSiteId\\[\\]]')
        .multiselect({
            noneSelectedText: "No filter",
            selectedList: 1
        })
        .multiselectfilter();
    $('[name=filterIsActive]')
        .multiselect({
            multiple: false,
            noneSelectedText: "No filter",
            selectedList: 1
        });

    // $('button.ui-multiselect').css('width', '110px');
    $('div.ui-multiselect-menu').css('width', '400px');
});

$('#form input').keydown(function(e) {
    if (e.keyCode == 13)
        filter();
});

function filter() {
    $('#form').submit();
}

function selectAll(control)
{
    $('input[name*=\'selectedItems\']').attr('checked', control.checked);
}

function submitForm(action)
{
    if ($('[name^=selectedItems]:checked').length != 0)
    {
        $('#form').attr('action', action);
        $('#form').submit();
    }
    else
        alert("<?= $text_no_selected_items ?>");
}
//--></script> 
<?php echo $footer; ?>