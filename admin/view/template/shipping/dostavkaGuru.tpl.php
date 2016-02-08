<?= $header ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="error"><?= $error_warning ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/shipping.png" alt="" /> <?= $heading_title ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?= $button_save ?></a><a onclick="location = '<?= $cancel ?>';" class="button"><?= $button_cancel ?></a></div>
    </div>
    <div class="content">
      <form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general" class="vtabs-content">
          <table class="form">
            <tr>
              <td><?= $entry_status ?></td>
              <td>
                <select name="dostavkaGuruStatus">
                  <option value="1" <?= $dostavkaGuruStatus ? "selected=\"selected\"" : "" ?>><?= $text_enabled ?></option>
                  <option value="0" <?= !$dostavkaGuruStatus ? "selected=\"selected\"" : "" ?>><?= $text_disabled ?></option>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_sort_order ?></td>
              <td><input type="text" name="dostavkaGuruSortOrder" value="<?= $dostavkaGuruSortOrder ?>" size="1" /></td>
            </tr>
              <tr>
                  <td><?= $entry_rate ?></td>
                  <td><textarea name="intermediateZoneRate" cols="40" rows="5"><?= $intermediateZoneRate ?></textarea></td>
              </tr>
          </table>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $footer ?>