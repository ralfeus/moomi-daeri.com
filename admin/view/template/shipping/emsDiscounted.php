<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb): ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php endforeach; ?>
  </div>
  <?php if ($error_warning): ?>
  <div class="error"><?php echo $error_warning; ?></div>
  <?php endif; ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/shipping.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <div class="vtabs"><a href="#tab-general"><?php echo $tab_general; ?></a>
        <?php foreach ($geo_zones as $geo_zone) { ?>
        <a href="#tab-geo-zone<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></a>
        <?php } ?>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general" class="vtabs-content">
          <table class="form">
            <tr>
              <td><?php echo $entry_status; ?></td>
              <td><select name="emsDiscounted_status">
                  <option value="1" <?= $emsDiscounted_status ? 'selected' : '' ?>><?= $text_enabled ?></option>
                  <option value="0" <?= $emsDiscounted_status ? '' : 'selected' ?>><?= $text_disabled ?></option>
              </select></td>
            </tr>
              <tr>
                  <td><?= $textDiscountAmount ?></td>
                  <td><input type="text" name="emsDiscounted_discountAmount" value="<?= $emsDiscounted_discountAmount ?>" size="1" />&#37;</td>
              </tr>
            <tr>
              <td><?= $entry_sort_order ?></td>
              <td><input type="text" name="emsDiscounted_sortOrder" value="<?php echo $emsDiscounted_sortOrder; ?>" size="1" /></td>
            </tr>
          </table>
        </div>
        <?php foreach ($geo_zones as $geo_zone) { ?>
        <div id="tab-geo-zone<?php echo $geo_zone['geo_zone_id']; ?>" class="vtabs-content">
          <table class="form">
            <tr>
              <td><?php echo $entry_status; ?></td>
              <td>
                <select name="emsDiscounted_<?= $geo_zone['geo_zone_id'] ?>_status">
                  <option value="1" <?= ${'emsDiscounted_' . $geo_zone['geo_zone_id'] . '_status'} ? 'selected' : '' ?>><?= $text_enabled ?></option>
                  <option value="0" <?= ${'emsDiscounted_' . $geo_zone['geo_zone_id'] . '_status'} ? '' : 'selected' ?>><?= $text_disabled ?></option>
                </select>
              </td>
            </tr>
          </table>
        </div>
        <?php } ?>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$('.vtabs a').tabs(); 
//--></script> 
<?php echo $footer; ?> 