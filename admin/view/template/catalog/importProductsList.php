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
                    <select name="filterSourceSiteId[]">
<?php foreach ($sourceSites as $id => $name):
    $selected = in_array($id, $filterSourceSiteId) ? 'selected' : '';
?>
                        <option value="<?= $id ?>" <?= $selected ?> ><?= $name ?></option>
<?php endforeach; ?>
                    </select>
                </td>
                <td />
                <td />
                <td><a onclick="filter();" class="button"><?= $textFilter; ?></a></td>
            </tr>
          </thead>
          <tbody>
<?php if ($products): ?>
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
                  <?= $textSource ?>:&nbsp;<?= $product->getSourcePrice() ?><br />
                  <?= $textLocal ?>:&nbsp;<?= $product->getLocalPrice() ?>
              </td>
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
$(document).ready(function() {
    $('.date').datepicker({dateFormat: 'yy-mm-dd'});

    $('[name=filterSourceSiteId\\[\\]]')
        .multiselect({
            noneSelectedText: "No filter",
            selectedList: 1
        })
        .multiselectfilter();
    // $('button.ui-multiselect').css('width', '110px');
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