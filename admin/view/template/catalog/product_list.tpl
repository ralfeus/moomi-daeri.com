<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="error"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/product.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
          <a onclick="$('#form').attr('action', '<?php echo $enable; ?>'); $('#form').submit();" class="button"><?php echo $button_enable; ?></a>
          <a onclick="$('#form').attr('action', '<?php echo $disable; ?>'); $('#form').submit();" class="button"><?php echo $button_disable; ?></a>
          <a onclick="location = '<?php echo $insert; ?>'" class="button"><?php echo $button_insert; ?></a>
          <a onclick="$('#form').attr('action', '<?php echo $copy; ?>'); $('#form').submit();" class="button"><?php echo $button_copy; ?></a>
          <a onclick="$('#form').submit();" class="button"><?= $button_delete ?></a>
<!--          <a onclick="$('#form').attr('action', '<?php echo $copyDescription; ?>'); $('#form').submit();" class="button"><?php echo $button_copyDescription; ?></a>
          <a onclick="$('#form').attr('action', '<?php echo $copyDescriptionAll; ?>'); $('#form').submit();" class="button"><?php echo $button_copyDescriptionAll; ?></a>
          <a onclick="$('#form').attr('action', '<?php echo $removeDescriptionAll; ?>'); $('#form').submit();" class="button"><?php echo $button_removeDescriptionAll; ?></a>
-->
      </div>
    </div>
    <div class="content">
      <form action="<?= $delete ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td style="width: 1px; text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
              <td class="center"><?php echo $column_image; ?></td>
              <td class="left"><?php if ($sort == 'pd.name') { ?>
                <a href="<?php echo $sort_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_name; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_name; ?>"><?php echo $column_name; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'p.model') { ?>
                <a href="<?php echo $sort_model; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_model; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_model; ?>"><?php echo $column_model; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'p.price') { ?>
                <a href="<?php echo $sort_price; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_price; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_price; ?>"><?php echo $column_price; ?></a>
                <?php } ?></td>
              <td class="right" style="width: 100px"><?php if ($sort == 'a.text') { ?>
                <a href="<?php echo $sort_korean_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_korean_name; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_korean_name; ?>"><?php echo $column_korean_name; ?></a>
                <?php } ?></td>
              <td class="right"><?php if ($sort == 'u.username') { ?>
                <a href="<?php echo $sort_user_name; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_user_name; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_user_name; ?>"><?php echo $column_user_name; ?></a>
                <?php } ?></td> 
              <td class="left"><?php if ($sort == 'p.status') { ?>
                <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                <?php } ?></td>
			        <td class="right"><?php if ($sort == 'p.manufacturer') { ?>
                <a href="<?php echo $sort_manufacturer; ?>" class="<?php echo strtolower($order); ?>"><?php echo $columnManufacturer; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_manufacturer; ?>"><?php echo $columnManufacturer; ?></a>
                <?php } ?></td>	
			  <td style="width: 1px" class="right"><?php if ($sort == 'p.supplier') { ?>
                <a href="<?php echo $sort_supplier; ?>" class="<?php echo strtolower($order); ?>"><?php echo $columnSupplier; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_supplier; ?>"><?php echo $columnSupplier; ?></a>
                <?php } ?></td>
              <td style="width: 1px; white-space: nowrap;"><?= $textDateAdded ?></td>
              <td class="right"><?php echo $column_action; ?></td>
            </tr>
            <tr class="filter">
                <td></td>
                <td></td>
                <td><input name="filterName" value="<?= $filterName ?>" /></td>
                <td><input name="filterModel" value="<?= $filterModel ?>" /></td>
                <td><input name="filterPrice" value="<?= $filterPrice ?>" /></td>
                <td><input name="filterKoreanName" value="<?= $filterKoreanName ?>" /></td>
                <td>
                    <select name="filterUserNameId[]" multiple="true">
                        <?php foreach ($usernames as $key => $value):
                        $selected = in_array($key, $filterUserNameId) ? 'selected' : ''; ?>
                        <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="filterStatus" multiple="true">
                        <option>-- No filter --</option>
                        <option value="0" <?= $filterStatus === "0" ? "selected" : "" ?>>Disabled</option>
                        <option value="1" <?= $filterStatus === "1" ? "selected" : "" ?>>Enabled</option>
                    </select>
                </td>
                <td>
                    <select name="filterManufacturerId[]" multiple="true">
                        <?php foreach ($manufacturers as $key => $value):
                        $selected = in_array($key, $filterManufacturerId) ? 'selected' : ''; ?>
                        <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="filterSupplierId[]" multiple="true">
                        <?php foreach ($suppliers as $key => $value):
                        $selected = in_array($key, $filterSupplierId) ? 'selected' : ''; ?>
                        <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input name="filterDateAddedFrom" class="date" value="<?= $filterDateAddedFrom ?>" />
                    <input name="filterDateAddedTo" class="date" value="<?= $filterDateAddedTo ?>" />
                </td>
                <td align="right">
                    <a onclick="filter();" class="button"><?php echo $button_filter; ?></a>
                    <a onclick="resetFilter();" class="button"><?= $textResetFilter ?></a>
                </td>
            </tr>
          </thead>
          <tbody>
            <?php if ($products) { ?>
            <?php foreach ($products as $product) { ?>
            <tr>
              <td style="text-align: center;"><?php if ($product['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $product['product_id']; ?>" />
                <?php } ?></td>
              <td class="center"><img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="padding: 1px; border: 1px solid #DDDDDD;" /></td>
              <td class="left"><?php echo $product['name']; ?></td>
              <td class="left"><?php echo $product['model']; ?></td>
              <td class="left"><?php if ($product['special']) { ?>
                <span style="text-decoration: line-through;"><?php echo $product['price']; ?></span><br/>
                <span style="color: #b00;"><?php echo $product['special']; ?></span>
                <?php } else { ?>
                <?php echo $product['price']; ?>
                <?php } ?></td>
              <td class="right">
                <?php echo $product['korean_name']; ?>  
              </td>
              <td class="right"> <?php if(!empty($product['user_name_page_url'])) { ?>
                <a href="<?php echo $product['user_name_page_url']; ?>"><?php echo $product['user_name']; ?></a>
                <?php } else { ?>
                <?php echo $product['user_name']; } ?>
              </td>
              <td class="left"><?php echo $product['status']; ?></td>
      			  <td class="right"> <?php if(!empty($product['manufacturer_page_url'])) { ?>
                <a href="<?php echo $product['manufacturer_page_url']; ?>"><?php echo $product['manufacturer']; ?></a>
                <?php } else { ?>
                <?php echo $product['manufacturer']; } ?>
              </td>
			  <td class="right"><?php echo $product['supplier']; ?></td>
              <td><?= $product['dateAdded'] ?></td>
              <td class="right"><?php foreach ($product['action'] as $action) { ?>
                [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                <?php } ?>
                <?php 
                  foreach ($product['link'] as $link) {
                    if(!empty($link['href'])){
                      echo "<br /><strong><a href='" . $link["href"] . "' target='_blank' style='text-decoration: none; color: black'>" . $link["text"] . "</a><strong>"; 
                    } 
                  } 
                ?>
              </td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr>
              <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<?php if (isset($notifications['confirm'])): ?>
<div id="dialog-confirm" title="<?=$notifications['confirm']['title'] ?>"><?= $notifications['confirm']['text'] ?></div>
<script type="text/javascript">//<!--
$(document).ready(function() {
    $( "#dialog-confirm" ).dialog({
        resizable: true,
        height: 255,
        width: 415,
        modal: true,
        buttons: {
            Yes: function() {
                document.location = "<?= $notifications['confirm']['urlYes'] ?>";
            },
            No: function() {
                $( this ).dialog( "close" );
            }
        }
    });
});
//--></script>
<?php endif; ?>
<script type="text/javascript"><!--
$(document).ready(function() {
    $('.date').datepicker({dateFormat: 'yy-mm-dd'});
    $('[name="filterUserNameId[]"]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();
    $('[name="filterManufacturerId[]"]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();
    $('[name=filterStatus]').multiselect({
        multiple: false,
        noneSelectedText: "-- No filter --",
        selectedList: 1
    }).multiselectfilter();
    $('[name="filterSupplierId[]"]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();
    $('button.ui-multiselect').css('width', '110px');
});

function filter() {
    $('#form')
            .attr('action', 'index.php?route=catalog/product&token=<?= $token ?>')
            .submit();
    return;
}

function resetFilter()
{
    $('#form')
            .attr('action', 'index.php?route=catalog/product&token=<?= $token ?>&resetFilter=1')
            .submit();
}
//--></script> 
<script type="text/javascript"><!--
$('#form input').keydown(function(e) {
	if (e.keyCode == 13) {
		filter();
	}
});
//--></script> 
<?= $footer ?>