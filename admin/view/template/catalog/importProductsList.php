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
          <a id="importButton" onclick="<?= $importAction ?>Import();" class="button"><?= $textToggleImport ?></a>
          <a onclick="showProgress();" class="button"><?= $textViewImportStatus ?></a>
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
<div id="sourceSitesSelect" title="<?= $textSelectSourceSitesToImport ?>" style="visibility: collapse;">
<?php foreach ($sourceSites as $id => $name): ?>
    <input id="sourceSiteId" type="checkbox" value="<?= $id ?>" />&nbsp;<label for="sourceSiteId"><?= $name ?></label><br />
<?php endforeach; ?>
    <input type="button" onclick="performImport();" value="Start" />
</div>
<div id="importProgress" title="Import log" style="visibility: collapse;">
    <textarea
        style="width: 100%; height: 100%; box-sizing: border-box; -moz-box-sizing: border-box; -webkit-box-sizing: border-box;"
        id="logContent"></textarea>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
//    setInterval(updateProgress(), 300);
//    getStatus();

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

var globalIntervalId;
function getStatus() {
    var progress = null;
    $.ajax({
        url: "index.php?route=catalog/import/getStatus&token=<?= $token ?>",
        cache: false,
        dataType: 'json',
        async: false
    }).done(function(data) {
        if (data) {
            progress = data;
        }
    });
    return progress;
}

function showProgress() {
    var status = getStatus();
    $('#logContent').html(status.log);
    $('#importProgress').dialog({
        title: "Import is running: " + status.running,
        width: 790,
        height: 590,
        modal: true
    });
}

function performImport() {
    var sourceSites = '';
    $('#sourceSiteId:checked').each(function() {
        sourceSites += '&selectedItems[]=' + this.value;
    });
    $.ajax({
        url: "index.php?route=catalog/import/start&token=<?= $token ?>" + sourceSites,
        cache: false,
        dataType: 'html',
        complete: function() {
            $('#importButton')
                .attr('onclick', "stopImport();")
                .html('Stop Import');
            $('#sourceSitesSelect').dialog("close");
        }
    });
}

function startImport() {
    var status = getStatus();
    if (status.running) {
        stopImport();
    } else {
        $('#sourceSitesSelect').dialog();
    }
}

function stopImport() {
    $.ajax({
        url: "index.php?route=catalog/import/stop&token=<?= $token ?>",
        cache: false,
        complete: function() {
            $('#importButton')
                .attr('onclick', "startImport();")
                .html('Start Import');
        }
    })
}

function submitForm(action)
{
    if ($('[name^=selectedItems]:checked').length != 0)
    {
        $('#form')
            .attr('action', action)
            .submit();
    }
    else
        alert("<?= $textNoSelectedItems ?>");
}
//--></script> 
<?php echo $footer; ?>