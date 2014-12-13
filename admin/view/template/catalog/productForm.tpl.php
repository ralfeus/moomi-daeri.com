<?= $header ?>
<div id="content">
    <div class="breadcrumb">
<?php foreach($breadcrumbs as $breadcrumb) { ?>
    <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php } ?>
    </div>
<?php if($error_warning) { ?>
	<div class="error"><?= $error_warning ?></div>
<?php } ?>

<div class="box">
	<div class="heading">
        <h1><img src="view/image/product.png" alt="" /> <?= $heading_title ?></h1>

        <div class="buttons">
            <a onclick="image_upload()" class="button"><?= $textImageManager ?></a>
            <a onclick="$('#form').submit();" class="button"><?= $button_save ?></a>
            <a onclick="location = '<?= $cancel ?>';" class="button"><?= $button_cancel ?></a>
        </div>
    </div>

	<div class="content">
		<div id="tabs" class="htabs">
            <a href="#tab-general"><?= $tab_general ?></a>
            <a href="#tab-data"><?= $tab_data ?></a>
            <a href="#tab-links"><?= $tab_links ?></a>
            <a href="#tab-attribute"><?= $tab_attribute ?></a>
            <a href="#tab-option"><?= $tab_option ?></a>
            <a href="#tab-create-option"><?= $tab_creat_option ?></a>
            <a href="#tab-discount"><?= $tab_discount ?></a>
            <a href="#tab-special"><?= $tab_special ?></a>
            <a href="#tab-image"><?= $tab_image ?></a>
            <a href="#tab-reward"><?= $tab_reward ?></a>
            <a href="#tab-design"><?= $tab_design ?></a>
            <?php if($this->config->get('wk_auction_timezone_set')){ ?>
		<a href="#tab-auction"><?= $tab_auction ?></a>
            <?php } ?>
        </div>

<form action="<?= $action ?>" method="post" enctype="multipart/form-data" id="form">
       <div id="tab-general">
          <div id="languages" class="htabs">
            <?php foreach ($languages as $language) { ?>
            <a href="#language<?= $language['language_id'] ?>"><img src="view/image/flags/<?= $language['image'] ?>" title="<?= $language['name'] ?>" /> <?= $language['name'] ?></a>
            <?php } ?>
          </div>
          <?php foreach ($languages as $language) { ?>
          <div id="language<?= $language['language_id'] ?>">
            <table class="form">
              <tr>
                <td><span class="required">*</span> <?= $entry_name ?></td>
                <td><input type="text" name="product_description[<?= $language['language_id'] ?>][name]" maxlength="255" size="100" value="<?= isset($product_description[$language['language_id']]) ? $product_description[$language['language_id']]['name'] : '' ?>" />
                  <?php if (isset($error_name[$language['language_id']])) { ?>
                  <span class="error"><?= $error_name[$language['language_id']] ?></span>
                  <?php } ?></td>
              </tr>
              <tr>
                <td><?= $entry_seo_h1 ?></td>
                <td><input type="text" name="product_description[<?= $language['language_id'] ?>][seo_h1]" maxlength="255" size="100" value="<?= isset($product_description[$language['language_id']]) ? $product_description[$language['language_id']]['seo_h1'] : '' ?>" /></td>
              </tr>
              <tr>
                <td><?= $entry_seo_title ?></td>
                <td><input type="text" name="product_description[<?= $language['language_id'] ?>][seo_title]" maxlength="255" size="100" value="<?= isset($product_description[$language['language_id']]) ? $product_description[$language['language_id']]['seo_title'] : '' ?>" /></td>
              </tr>
              <tr>
                <td><?= $entry_meta_keyword ?></td>
                <td><input type="text" name="product_description[<?= $language['language_id'] ?>][meta_keyword]" maxlength="255" size="100" value="<?= isset($product_description[$language['language_id']]) ? $product_description[$language['language_id']]['meta_keyword'] : '' ?>" /></td>
              </tr>
              <tr>
                <td><?= $entry_meta_description ?></td>
                <td><textarea name="product_description[<?= $language['language_id'] ?>][meta_description]" cols="100" rows="2"><?= isset($product_description[$language['language_id']]) ? $product_description[$language['language_id']]['meta_description'] : '' ?></textarea></td>
              </tr>
              <tr>
                <td><?= $entry_description ?></td>
                <td><textarea name="product_description[<?= $language['language_id'] ?>][description]" id="description<?= $language['language_id'] ?>"><?= isset($product_description[$language['language_id']]) ? $product_description[$language['language_id']]['description'] : '' ?></textarea></td>
              </tr>
              <tr>
                <td><?= $entry_tag ?></td>
                <td><input type="text" name="product_tag[<?= $language['language_id'] ?>]" value="<?= isset($product_tag[$language['language_id']]) ? $product_tag[$language['language_id']] : '' ?>" size="80" /></td>
              </tr>
            </table>
          </div>
          <?php } ?>
        </div>
        <div id="tab-data">
          <table class="form">
            <tr>
              <td><span class="required">*</span> <?= $entry_model ?></td>
              <td><input type="text" name="model" value="<?= $model ?>" />
                <?php if ($error_model) { ?>
                <span class="error"><?= $error_model ?></span>
                <?php } ?></td>
            </tr>
            <tr>
                <td><?= $textKoreanName ?></td>
                <td><input type="text" name="koreanName" value="<?= $koreanName ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_sku ?></td>
              <td><input type="text" name="sku" value="<?= $sku ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_upc ?></td>
              <td><input type="text" name="upc" value="<?= $upc ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_location ?></td>
              <td><input type="text" name="location" value="<?= $location ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_price ?></td>
              <td><input type="text" id="Calculator" name="price" value="<?= $price ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_tax_class ?></td>
              <td><select name="tax_class_id">
                  <option value="0"><?= $text_none ?></option>
                  <?php foreach ($tax_classes as $tax_class) { ?>
                  <?php if ($tax_class['tax_class_id'] == $tax_class_id) { ?>
                  <option value="<?= $tax_class['tax_class_id'] ?>" selected="selected"><?= $tax_class['title'] ?></option>
                  <?php } else { ?>
                  <option value="<?= $tax_class['tax_class_id'] ?>"><?= $tax_class['title'] ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_quantity ?></td>
              <td><input type="text" name="quantity" value="<?= $quantity ?>" size="2" /></td>
            </tr>
            <tr>
              <td><?= $entry_minimum ?></td>
              <td><input type="text" name="minimum" value="<?= $minimum ?>" size="2" /></td>
            </tr>
            <tr>
              <td><?= $entry_subtract ?></td>
              <td><select name="subtract">
                  <?php if ($subtract) { ?>
                  <option value="1" selected="selected"><?= $text_yes ?></option>
                  <option value="0"><?= $text_no ?></option>
                  <?php } else { ?>
                  <option value="1"><?= $text_yes ?></option>
                  <option value="0" selected="selected"><?= $text_no ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_stock_status ?></td>
              <td><select name="stock_status_id">
                  <?php foreach ($stock_statuses as $stock_status) { ?>
                  <?php if ($stock_status['stock_status_id'] == $stock_status_id) { ?>
                  <option value="<?= $stock_status['stock_status_id'] ?>" selected="selected"><?= $stock_status['name'] ?></option>
                  <?php } else { ?>
                  <option value="<?= $stock_status['stock_status_id'] ?>"><?= $stock_status['name'] ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_shipping ?></td>
              <td><?php if ($shipping) { ?>
                <input type="radio" name="shipping" value="1" checked="checked" />
                <?= $text_yes ?>
                <input type="radio" name="shipping" value="0" />
                <?= $text_no ?>
                <?php } else { ?>
                <input type="radio" name="shipping" value="1" />
                <?= $text_yes ?>
                <input type="radio" name="shipping" value="0" checked="checked" />
                <?= $text_no ?>
                <?php } ?></td>
            </tr>
            <tr>
              <td><?= $entry_keyword ?></td>
              <td><input type="text" name="keyword" value="<?= $keyword ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_image ?></td>
              <td><div class="image"><img src="<?= $thumb ?>" alt="" id="thumb" /><br />
                  <input type="hidden" name="image" value="<?= $image ?>" id="image" />
                  <a onclick="image_upload('image', 'thumb');"><?= $text_browse ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb').attr('src', '<?= $no_image ?>'); $('#image').attr('value', '');"><?= $text_clear ?></a></div></td>
            </tr>
            <tr>
                <td><?= $entry_image_description ?></td>
                <td><textarea name="image_description" id="image_description"><?= isset($image_description) ? $image_description : '' ?></textarea></td>
            </tr>
            <tr>
              <td><?= $entry_date_available ?></td>
              <td><input type="text" name="date_available" value="<?= $date_available ?>" size="12" class="date" /></td>
            </tr>
            <tr>
              <td><?= $entry_dimension ?></td>
              <td><input type="text" name="length" value="<?= $length ?>" size="4" />
                <input type="text" name="width" value="<?= $width ?>" size="4" />
                <input type="text" name="height" value="<?= $height ?>" size="4" /></td>
            </tr>
            <tr>
              <td><?= $entry_length ?></td>
              <td><select name="length_class_id">
                  <?php foreach ($length_classes as $length_class) { ?>
                  <?php if ($length_class['length_class_id'] == $length_class_id) { ?>
                  <option value="<?= $length_class['length_class_id'] ?>" selected="selected"><?= $length_class['title'] ?></option>
                  <?php } else { ?>
                  <option value="<?= $length_class['length_class_id'] ?>"><?= $length_class['title'] ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_weight ?></td>
              <td><input type="text" name="weight" value="<?= $weight ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_weight_class ?></td>
              <td><select name="weight_class_id">
                  <?php foreach ($weight_classes as $weight_class) { ?>
                  <?php if ($weight_class['weight_class_id'] == $weight_class_id) { ?>
                  <option value="<?= $weight_class['weight_class_id'] ?>" selected="selected"><?= $weight_class['title'] ?></option>
                  <?php } else { ?>
                  <option value="<?= $weight_class['weight_class_id'] ?>"><?= $weight_class['title'] ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_status ?></td>
              <td><select name="status">
                  <?php if ($status) { ?>
                  <option value="1" selected="selected"><?= $text_enabled ?></option>
                  <option value="0"><?= $text_disabled ?></option>
                  <?php } else { ?>
                  <option value="1"><?= $text_enabled ?></option>
                  <option value="0" selected="selected"><?= $text_disabled ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_sort_order ?></td>
              <td><input type="text" name="sort_order" value="<?= $sort_order ?>" size="2" /></td>
            </tr>
            <tr>
              <td><?= $this->language->get('entry_commission') ?></td>
              <td><input type="text" name="affiliate_commission" value="<?= $affiliate_commission ?>" size="4" /></td>
            </tr>
            <tr>
          </table>
        </div>
        <div id="tab-links">
          <table class="form">
            <tr>
              <td><?= $entry_manufacturer ?></td>
              <td><select name="manufacturer_id">
                  <option value="0"><?= $text_none ?></option>
                  <?php foreach ($manufacturers as $manufacturer):
                    $selected = ($manufacturer->getId() == $manufacturer_id) ? " selected" : ""; ?>
                    <option value="<?= $manufacturer->getId() ?>" <?= $selected ?>><?= $manufacturer->getName() ?></option>
                  <?php endforeach; ?>
                </select></td>
            </tr>
            <tr>
                <td><?= $entry_supplier ?></td>
                <td>
                    <select name="supplier_id">
                        <option value="0" selected="selected"><?= $text_none ?></option>
<?php foreach ($suppliers as $supplier):
    $selected = ($supplier->getId() == $supplier_id) ? "selected" : ""; ?>
                        <option value="<?= $supplier->getId() ?>" <?= $selected ?>><?= $supplier->getName() ?></option>
<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?= $textSupplierUrl ?></td>
                <td><input type="text" name="supplierUrl" value="<?= $supplierUrl ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_main_category ?></td>
              <td><select name="main_category_id">
                <option value="0" selected="selected"><?= $text_none ?></option>
                <?php foreach ($categories as $category) { ?>
                <?php if ($category['category_id'] == $main_category_id) { ?>
                <option value="<?= $category['category_id'] ?>" selected="selected"><?= $category['name'] ?></option>
                <?php } else { ?>
                <option value="<?= $category['category_id'] ?>"><?= $category['name'] ?></option>
                <?php } ?>
                <?php } ?>
              </select></td>
            </tr>
            <tr>
              <td><?= $entry_store ?></td>
              <td><div class="scrollbox">
                  <?php $class = 'even'; ?>
                  <div class="<?= $class ?>">
                    <?php if (in_array(0, $product_store)) { ?>
                    <input type="checkbox" name="product_store[]" value="0" checked="checked" />
                    <?= $text_default ?>
                    <?php } else { ?>
                    <input type="checkbox" name="product_store[]" value="0" />
                    <?= $text_default ?>
                    <?php } ?>
                  </div>
                  <?php foreach ($stores as $store) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?= $class ?>">
                    <?php if (in_array($store['store_id'], $product_store)) { ?>
                    <input type="checkbox" name="product_store[]" value="<?= $store['store_id'] ?>" checked="checked" />
                    <?= $store['name'] ?>
                    <?php } else { ?>
                    <input type="checkbox" name="product_store[]" value="<?= $store['store_id'] ?>" />
                    <?= $store['name'] ?>
                    <?php } ?>
                  </div>
                  <?php } ?>
                </div></td>
            </tr>
            <tr>
              <td><?= $entry_category ?></td>
                <td>
                    <table class="categories">
                     <a href="javascript:void(0);" id="checkAll"><?= $textSelectAll ?></a>
                    &nbsp;/&nbsp;
                    <a href="javascript:void(0);" id="uncheckAll"><?= $textUnselectAll ?></a>
                     &nbsp;/&nbsp;
                     <a href="javascript:void(0);" id="collapseAll"><?= $textCollapseAll ?></a>
                    &nbsp;/&nbsp;
                    <a href="javascript:void(0);" id="expandAll"><?= $textExpandAll ?></a>
                        <?php echo $categoriesParent; ?>
                    </table>
                </td>

            </tr>
            <tr>
              <td><?= $entry_download ?></td>
              <td><div class="scrollbox">
                  <?php $class = 'odd'; ?>
                  <?php foreach ($downloads as $download) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?= $class ?>">
                    <?php if (in_array($download['download_id'], $product_download)) { ?>
                    <input type="checkbox" name="product_download[]" value="<?= $download['download_id'] ?>" checked="checked" />
                    <?= $download['name'] ?>
                    <?php } else { ?>
                    <input type="checkbox" name="product_download[]" value="<?= $download['download_id'] ?>" />
                    <?= $download['name'] ?>
                    <?php } ?>
                  </div>
                  <?php } ?>
                </div></td>
            </tr>
            <tr>
              <td><?= $entry_related ?></td>
              <td><input type="text" name="related" value="" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td><div class="scrollbox" id="product-related">
                  <?php $class = 'odd'; ?>
                  <?php foreach ($product_related as $product_related) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div id="product-related<?= $product_related['product_id'] ?>" class="<?= $class ?>"> <?= $product_related['name'] ?><img src="view/image/delete.png" />
                    <input type="hidden" name="product_related[]" value="<?= $product_related['product_id'] ?>" />
                  </div>
                  <?php } ?>
                </div></td>
            </tr>
          </table>
        </div>
        <div id="tab-attribute">
          <table id="attribute" class="list">
            <thead>
              <tr>
                <td class="left"><?= $entry_attribute ?></td>
                <td class="left"><?= $entry_text ?></td>
                <td></td>
              </tr>
            </thead>
            <?php $attribute_row = 0; ?>
            <?php foreach ($product_attributes as $product_attribute) { ?>
            <tbody id="attribute-row<?= $attribute_row ?>">
              <tr>
                <td class="left"><input type="text" name="product_attribute[<?= $attribute_row ?>][name]" value="<?= $product_attribute['name'] ?>" />
                  <input type="hidden" name="product_attribute[<?= $attribute_row ?>][attribute_id]" value="<?= $product_attribute['attribute_id'] ?>" /></td>
                <td class="left"><?php foreach ($languages as $language) { ?>
                  <textarea name="product_attribute[<?= $attribute_row ?>][product_attribute_description][<?= $language['language_id'] ?>][text]" cols="40" rows="5"><?= isset($product_attribute['product_attribute_description'][$language['language_id']]) ? $product_attribute['product_attribute_description'][$language['language_id']]['text'] : '' ?></textarea>
                  <img src="view/image/flags/<?= $language['image'] ?>" title="<?= $language['name'] ?>" /><br />
                  <?php } ?></td>
                <td class="left"><a onclick="$('#attribute-row<?= $attribute_row ?>').remove();" class="button"><?= $button_remove ?></a></td>
              </tr>
            </tbody>
            <?php $attribute_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="left"><a onclick="addAttribute();" class="button"><?= $button_add_attribute ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div id="tab-option">
          <div id="vtab-option" class="vtabs">
            <?php $option_row = 0; ?>
            <?php foreach ($product_options as $product_option) { ?>
            <a href="#tab-option-<?= $option_row ?>" id="option-<?= $option_row ?>"><?= $product_option['name'] ?>&nbsp;<img src="view/image/delete.png" alt="" onclick="$('#vtabs a:first').trigger('click'); $('#option-<?= $option_row ?>').remove(); $('#tab-option-<?= $option_row ?>').remove(); return false;" /></a>
            <?php $option_row++; ?>
            <?php } ?>
            <span id="option-add">
            <input name="option" value="" style="width: 130px;" />
            &nbsp;<img src="view/image/add.png" alt="<?= $button_add_option ?>" title="<?= $button_add_option ?>" /></span></div>
          <?php $option_row = 0; ?>
          <?php $option_value_row = 0; ?>
          <?php foreach ($product_options as $product_option) { ?>
          <div id="tab-option-<?= $option_row ?>" class="vtabs-content">
            <input type="hidden" name="product_option[<?= $option_row ?>][product_option_id]" value="<?= $product_option['product_option_id'] ?>" />
            <input type="hidden" name="product_option[<?= $option_row ?>][name]" value="<?= $product_option['name'] ?>" />
            <input type="hidden" name="product_option[<?= $option_row ?>][option_id]" value="<?= $product_option['option_id'] ?>" />
            <input type="hidden" name="product_option[<?= $option_row ?>][type]" value="<?= $product_option['type'] ?>" />
            <table class="form">
              <tr>
                <td><?= $entry_required ?></td>
                <td><select name="product_option[<?= $option_row ?>][required]">
                    <?php if ($product_option['required']) { ?>
                    <option value="1" selected="selected"><?= $text_yes ?></option>
                    <option value="0"><?= $text_no ?></option>
                    <?php } else { ?>
                    <option value="1"><?= $text_yes ?></option>
                    <option value="0" selected="selected"><?= $text_no ?></option>
                    <?php } ?>
                  </select></td>
              </tr>
              <?php if ($product_option['type'] == 'text') { ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $product_option['option_value'] ?>" /></td>
              </tr>
              <?php } ?>
              <?php if ($product_option['type'] == 'textarea') { ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><textarea name="product_option[<?= $option_row ?>][option_value]" cols="40" rows="5"><?= $product_option['option_value'] ?></textarea></td>
              </tr>
              <?php } ?>
              <?php if ($product_option['type'] == 'file') { ?>
              <tr style="display: none;">
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $product_option['option_value'] ?>" /></td>
              </tr>
              <?php } ?>
              <?php if ($product_option['type'] == 'date') { ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $product_option['option_value'] ?>" class="date" /></td>
              </tr>
              <?php } ?>
              <?php if ($product_option['type'] == 'datetime') { ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $product_option['option_value'] ?>" class="datetime" /></td>
              </tr>
              <?php } ?>
              <?php if ($product_option['type'] == 'time') { ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $product_option['option_value'] ?>" class="time" /></td>
              </tr>
              <?php } ?>
            </table>
            <?php if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') { ?>
            <table id="option-value<?= $option_row ?>" class="list">
              <thead>
                <tr>
                  <td class="left"><?= $entry_option_value ?></td>
                  <td class="right"><?= $entry_quantity ?></td>
                  <td class="left"><?= $entry_subtract ?></td>
                  <td class="right"><?= $entry_price ?></td>
                  <td class="right"><?= $entry_option_points ?></td>
                  <td class="right"><?= $entry_weight ?></td>
                  <td></td>
                </tr>
              </thead>
              <?php foreach ($product_option['product_option_value'] as $product_option_value) { ?>
              <tbody id="option-value-row<?= $option_value_row ?>">
                <tr>
                  <td class="left"><select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][option_value_id]">
                    </select>
                    <input type="hidden" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][product_option_value_id]" value="<?= $product_option_value['product_option_value_id'] ?>" /></td>
                  <td class="right"><input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][quantity]" value="<?= $product_option_value['quantity'] ?>" size="3" /></td>
                  <td class="left"><select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][subtract]">
                      <?php if ($product_option_value['subtract']) { ?>
                      <option value="1" selected="selected"><?= $text_yes ?></option>
                      <option value="0"><?= $text_no ?></option>
                      <?php } else { ?>
                      <option value="1"><?= $text_yes ?></option>
                      <option value="0" selected="selected"><?= $text_no ?></option>
                      <?php } ?>
                    </select></td>
                  <td class="right"><select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][price_prefix]">
                      <?php if ($product_option_value['price_prefix'] == '+') { ?>
                      <option value="+" selected="selected">+</option>
                      <?php } else { ?>
                      <option value="+">+</option>
                      <?php } ?>
                      <?php if ($product_option_value['price_prefix'] == '-') { ?>
                      <option value="-" selected="selected">-</option>
                      <?php } else { ?>
                      <option value="-">-</option>
                      <?php } ?>
                    </select>
                    <input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][price]" value="<?= $product_option_value['price'] ?>" size="5" /></td>
                  <td class="right"><select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][points_prefix]">
                      <?php if ($product_option_value['points_prefix'] == '+') { ?>
                      <option value="+" selected="selected">+</option>
                      <?php } else { ?>
                      <option value="+">+</option>
                      <?php } ?>
                      <?php if ($product_option_value['points_prefix'] == '-') { ?>
                      <option value="-" selected="selected">-</option>
                      <?php } else { ?>
                      <option value="-">-</option>
                      <?php } ?>
                    </select>
                    <input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][points]" value="<?= $product_option_value['points'] ?>" size="5" /></td>
                  <td class="right"><select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][weight_prefix]">
                      <?php if ($product_option_value['weight_prefix'] == '+') { ?>
                      <option value="+" selected="selected">+</option>
                      <?php } else { ?>
                      <option value="+">+</option>
                      <?php } ?>
                      <?php if ($product_option_value['weight_prefix'] == '-') { ?>
                      <option value="-" selected="selected">-</option>
                      <?php } else { ?>
                      <option value="-">-</option>
                      <?php } ?>
                    </select>
                    <input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][weight]" value="<?= $product_option_value['weight'] ?>" size="5" /></td>
                  <td class="left"><a onclick="$('#option-value-row<?= $option_value_row ?>').remove();" class="button"><?= $button_remove ?></a></td>
                </tr>
              </tbody>
              <?php $option_value_row++; ?>
              <?php } ?>
              <tfoot>
                <tr>
                  <td colspan="6"></td>
                  <td class="left"><a onclick="addOptionValue('<?= $option_row ?>');" class="button"><?= $button_add_option_value ?></a></td>
                </tr>
              </tfoot>
            </table>
            <?php } ?>
          </div>
          <?php $option_row++; ?>
          <?php } ?>
          <script type="text/javascript"><!--
          <?php $option_row = 0; ?>
          <?php $option_value_row = 0; ?>		  
		  <?php foreach ($product_options as $product_option) { ?>
          <?php if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') { ?>
		  <?php foreach ($product_option['product_option_value'] as $product_option_value) { ?>
		  $('select[name=\'product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][option_value_id]\']').load('index.php?route=catalog/product/option&token=<?= $token ?>&option_id=<?= $product_option['option_id'] ?>&option_value_id=<?= $product_option_value['option_value_id'] ?>');
		  <?php $option_value_row++; ?>
		  <?php } ?>
		  <?php } ?>
		  <?php $option_row++; ?>
          <?php } ?>
		  //--></script> 
        </div>
        <div id="tab-discount">
          <table id="discount" class="list">
            <thead>
              <tr>
                <td class="left"><?= $entry_customer_group ?></td>
                <td class="right"><?= $entry_quantity ?></td>
                <td class="right"><?= $entry_priority ?></td>
                <td class="right"><?= $entry_price ?></td>
                <td class="left"><?= $entry_date_start ?></td>
                <td class="left"><?= $entry_date_end ?></td>
                <td></td>
              </tr>
            </thead>
            <?php $discount_row = 0; ?>
            <?php foreach ($product_discounts as $product_discount) { ?>
            <tbody id="discount-row<?= $discount_row ?>">
              <tr>
                <td class="left"><select name="product_discount[<?= $discount_row ?>][customer_group_id]">
                    <?php foreach ($customer_groups as $customer_group) { ?>
                    <?php if ($customer_group['customer_group_id'] == $product_discount['customer_group_id']) { ?>
                    <option value="<?= $customer_group['customer_group_id'] ?>" selected="selected"><?= $customer_group['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?= $customer_group['customer_group_id'] ?>"><?= $customer_group['name'] ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
                <td class="right"><input type="text" name="product_discount[<?= $discount_row ?>][quantity]" value="<?= $product_discount['quantity'] ?>" size="2" /></td>
                <td class="right"><input type="text" name="product_discount[<?= $discount_row ?>][priority]" value="<?= $product_discount['priority'] ?>" size="2" /></td>
                <td class="right"><input type="text" style="width=75%" name="product_discount[<?= $discount_row ?>][price]" value="<?= $product_discount['price'] ?>" /></td>
                <td class="left"><input type="text" name="product_discount[<?= $discount_row ?>][date_start]" value="<?= $product_discount['date_start'] ?>" class="date" /></td>
                <td class="left"><input type="text" name="product_discount[<?= $discount_row ?>][date_end]" value="<?= $product_discount['date_end'] ?>" class="date" /></td>
                <td class="left"><a onclick="$('#discount-row<?= $discount_row ?>').remove();" class="button"><?= $button_remove ?></a></td>
              </tr>
            </tbody>
            <?php $discount_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="6"></td>
                <td class="left"><a onclick="addDiscount();" class="button"><?= $button_add_discount ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div id="tab-special">
          <table id="special" class="list">
            <thead>
              <tr>
                <td class="left"><?= $entry_customer_group ?></td>
                <td class="right"><?= $entry_priority ?></td>
                <td class="right"><?= $entry_price ?></td>
                <td class="left"><?= $entry_date_start ?></td>
                <td class="left"><?= $entry_date_end ?></td>
                <td></td>
              </tr>
            </thead>
            <?php $special_row = 0; ?>
            <?php foreach ($product_specials as $product_special) { ?>
            <tbody id="special-row<?= $special_row ?>">
              <tr>
                <td class="left"><select name="product_special[<?= $special_row ?>][customer_group_id]">
                    <?php foreach ($customer_groups as $customer_group) { ?>
                    <?php if ($customer_group['customer_group_id'] == $product_special['customer_group_id']) { ?>
                    <option value="<?= $customer_group['customer_group_id'] ?>" selected="selected"><?= $customer_group['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?= $customer_group['customer_group_id'] ?>"><?= $customer_group['name'] ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
                <td class="right"><input type="text" name="product_special[<?= $special_row ?>][priority]" value="<?= $product_special['priority'] ?>" size="2" /></td>
                <td class="right"><input style="width: 75%" type="text" class="specCalculator" name="product_special[<?= $special_row ?>][price]" value="<?= $product_special['price'] ?>" /></td>
                <td class="left"><input type="text" name="product_special[<?= $special_row ?>][date_start]" value="<?= $product_special['date_start'] ?>" class="date" /></td>
                <td class="left"><input type="text" name="product_special[<?= $special_row ?>][date_end]" value="<?= $product_special['date_end'] ?>" class="date" /></td>
                <td class="left"><a onclick="$('#special-row<?= $special_row ?>').remove();" class="button"><?= $button_remove ?></a></td>
              </tr>
            </tbody>
            <?php $special_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="5"></td>
                <td class="left"><a onclick="addSpecial();" class="button"><?= $button_add_special ?></a></td>
              </tr>
            </tfoot>
          </table>
    <script type="text/javascript">
	$(function() { $(".specCalculator").calculator({showOn: 'opbutton', buttonImageOnly: true, buttonImage: 'view/image/calculator.png'}); });
    </script> 

        </div>
        <div id="tab-image">
          <table id="images" class="list">
            <thead>
              <tr>
                <td class="left"><?= $entry_image ?></td>
                <td class="right"><?= $entry_sort_order ?></td>
                <td></td>
              </tr>
            </thead>
            <?php $image_row = 0; ?>
            <?php foreach ($product_images as $product_image) { ?>
            <tbody id="image-row<?= $image_row ?>">
              <tr>
                <td class="left"><div class="image"><img src="<?= $product_image['thumb'] ?>" alt="" id="thumb<?= $image_row ?>" />
                    <input type="hidden" name="product_image[<?= $image_row ?>][image]" value="<?= $product_image['image'] ?>" id="image<?= $image_row ?>" />
                    <br />
                    <a onclick="image_upload('image<?= $image_row ?>', 'thumb<?= $image_row ?>');"><?= $text_browse ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#thumb<?= $image_row ?>').attr('src', '<?= $no_image ?>'); $('#image<?= $image_row ?>').attr('value', '');"><?= $text_clear ?></a></div></td>
                <td class="right"><input type="text" name="product_image[<?= $image_row ?>][sort_order]" value="<?= $product_image['sort_order'] ?>" size="2" /></td>
                <td class="left"><a onclick="$('#image-row<?= $image_row ?>').remove();" class="button"><?= $button_remove ?></a></td>
              </tr>
            </tbody>
            <?php $image_row++; ?>
            <?php } ?>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="left"><a onclick="addImage();" class="button"><?= $button_add_image ?></a></td>
              </tr>
            </tfoot>
          </table>
        </div>
        <div id="tab-reward">
          <table class="form">
            <tr>
              <td><?= $entry_points ?></td>
              <td><input type="text" name="points" value="<?= $points ?>" /></td>
            </tr>
          </table>
          <table class="list">
            <thead>
              <tr>
                <td class="left"><?= $entry_customer_group ?></td>
                <td class="right"><?= $entry_reward ?></td>
              </tr>
            </thead>
            <?php foreach ($customer_groups as $customer_group) { ?>
            <tbody>
              <tr>
                <td class="left"><?= $customer_group['name'] ?></td>
                <td class="right"><input type="text" name="product_reward[<?= $customer_group['customer_group_id'] ?>][points]" value="<?= isset($product_reward[$customer_group['customer_group_id']]) ? $product_reward[$customer_group['customer_group_id']]['points'] : '' ?>" /></td>
              </tr>
            </tbody>
            <?php } ?>
          </table>
        </div>

 <?php if($this->config->get('wk_auction_timezone_set')){ ?>
	    <div id="tab-auction">
	    <table id="auction" class="form">
		    <tr>
		    <td><?= $entry_isacution ?></td>
		    <td class="left">
			<input type="radio" name="isauction" value="1" <?php if($isauction) echo " checked" ?> /><label>Yes</label>
			<input type="radio" name="isauction" value="0" <?php if(!$isauction) echo " checked" ?> /><label>No</label>
		    </td>
		    </tr>  
		<tr>
		    <td><?= $entry_auction ?></td>
		    <td class="left"><input type="text" name="auction_name" value="<?php echo $auction_name;?>" />
		    </td>
		    </tr>  
		    <tr>
		    <td ><?= $entry_min ?></td>
		    <td class="left"><input type="text" name="auction_min" value="<?php echo $auction_min;?>" />
		    </tr>
		    <tr>
		    <td><?= $entry_max ?></td>
		    <td class="left"><input type="text" name="auction_max" value="<?php echo $auction_max;?>" />
		</tr>
		    <tr>
		    <td><?= $entry_sdate ?></td>
		<td><input class="dates1"  type="text" name="auction_start" value="<?php echo $auction_start;?>" />
		</tr>
		    <tr>
		    <td><?= $entry_date ?></td>
		<td><input class="dates1"  type="text" name="auction_end" value="<?php echo $auction_end;?>" />
		</tr>
	    </table>
	    </div>
    <script type="text/javascript">
	$(function() { $(".dates1").datetimepicker({ dateFormat: "yy-mm-dd", timeFormat: "hh:mm:ss" }); });
    </script> 
<?php } ?>


        <div id="tab-design">
          <table class="list">
            <thead>
              <tr>
                <td class="left"><?= $entry_store ?></td>
                <td class="left"><?= $entry_layout ?></td>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="left"><?= $text_default ?></td>
                <td class="left"><select name="product_layout[0][layout_id]">
                    <option value=""></option>
                    <?php foreach ($layouts as $layout) { ?>
                    <?php if (isset($product_layout[0]) && $product_layout[0] == $layout['layout_id']) { ?>
                    <option value="<?= $layout['layout_id'] ?>" selected="selected"><?= $layout['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?= $layout['layout_id'] ?>"><?= $layout['name'] ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
              </tr>
            </tbody>
            <?php foreach ($stores as $store) { ?>
            <tbody>
              <tr>
                <td class="left"><?= $store['name'] ?></td>
                <td class="left"><select name="product_layout[<?= $store['store_id'] ?>][layout_id]">
                    <option value=""></option>
                    <?php foreach ($layouts as $layout) { ?>
                    <?php if (isset($product_layout[$store['store_id']]) && $product_layout[$store['store_id']] == $layout['layout_id']) { ?>
                    <option value="<?= $layout['layout_id'] ?>" selected="selected"><?= $layout['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?= $layout['layout_id'] ?>"><?= $layout['name'] ?></option>
                    <?php } ?>
                    <?php } ?>
                  </select></td>
              </tr>
            </tbody>
            <?php } ?>
          </table>
        </div>
        <div id="tab-create-option"><?= $create_option_block ?></div>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script> 
<script type="text/javascript"><!--
CKEDITOR.replace('image_description', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>'
});

<?php foreach ($languages as $language) { ?>
CKEDITOR.replace('description<?= $language['language_id'] ?>', {
	filebrowserBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserImageBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserFlashBrowseUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserImageUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>',
	filebrowserFlashUploadUrl: 'index.php?route=common/filemanager&token=<?= $token ?>'
});
<?php } ?>
//--></script> 
<script type="text/javascript"><!--
$('input[name=\'related\']').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?= $token ?>&filter_name=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.product_id
					}
				}));
			}
		});
		
	}, 
	select: function(event, ui) {
		$('#product-related' + ui.item.value).remove();
		
		$('#product-related').append('<div id="product-related' + ui.item.value + '">' + ui.item.label + '<img src="view/image/delete.png" /><input type="hidden" name="product_related[]" value="' + ui.item.value + '" /></div>');

		$('#product-related div:odd').attr('class', 'odd');
		$('#product-related div:even').attr('class', 'even');
				
		return false;
	}
});

$('#product-related div img').live('click', function() {
	$(this).parent().remove();
	
	$('#product-related div:odd').attr('class', 'odd');
	$('#product-related div:even').attr('class', 'even');	
});
//--></script> 
<script type="text/javascript"><!--
var attribute_row = <?= $attribute_row ?>;

function addAttribute() {
	html  = '<tbody id="attribute-row' + attribute_row + '">';
    html += '  <tr>';
	html += '    <td class="left"><input type="text" name="product_attribute[' + attribute_row + '][name]" value="" /><input type="hidden" name="product_attribute[' + attribute_row + '][attribute_id]" value="" /></td>';
	html += '    <td class="left">';
	<?php foreach ($languages as $language) { ?>
	html += '<textarea name="product_attribute[' + attribute_row + '][product_attribute_description][<?= $language['language_id'] ?>][text]" cols="40" rows="5"></textarea><img src="view/image/flags/<?= $language['image'] ?>" title="<?= $language['name'] ?>" /><br />';
    <?php } ?>
	html += '    </td>';
	html += '    <td class="left"><a onclick="$(\'#attribute-row' + attribute_row + '\').remove();" class="button"><?= $button_remove ?></a></td>';
    html += '  </tr>';	
    html += '</tbody>';
	
	$('#attribute tfoot').before(html);
	
	attributeautocomplete(attribute_row);
	
	attribute_row++;
}

$.widget('custom.catcomplete', $.ui.autocomplete, {
	_renderMenu: function(ul, items) {
		var self = this, currentCategory = '';
		
		$.each(items, function(index, item) {
			if (item.category != currentCategory) {
				ul.append('<li class="ui-autocomplete-category">' + item.category + '</li>');
				
				currentCategory = item.category;
			}
			
			self._renderItem(ul, item);
		});
	}
});

function attributeautocomplete(attribute_row) {
	$('input[name=\'product_attribute[' + attribute_row + '][name]\']').catcomplete({
		delay: 0,
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=catalog/attribute/autocomplete&token=<?= $token ?>&filter_name=' +  encodeURIComponent(request.term),
				dataType: 'json',
				success: function(json) {	
					response($.map(json, function(item) {
						return {
							category: item.attribute_group,
							label: item.name,
							value: item.attribute_id
						}
					}));
				}
			});
		}, 
		select: function(event, ui) {
			$('input[name=\'product_attribute[' + attribute_row + '][name]\']').attr('value', ui.item.label);
			$('input[name=\'product_attribute[' + attribute_row + '][attribute_id]\']').attr('value', ui.item.value);
			
			return false;
		}
	});
}

$('#attribute tbody').each(function(index, element) {
	attributeautocomplete(index);
});
//--></script> 
<script type="text/javascript"><!--	
var option_row = <?= $option_row ?>;

$('input[name=\'option\']').catcomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/option/autocomplete&token=<?= $token ?>&filter_name=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						category: item.category,
						label: item.name,
						value: item.option_id,
						type: item.type
					}
				}));
			}
		});
	}, 
	select: function(event, ui) {
		html  = '<div id="tab-option-' + option_row + '" class="vtabs-content">';
		html += '	<input type="hidden" name="product_option[' + option_row + '][product_option_id]" value="" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][name]" value="' + ui.item.label + '" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][option_id]" value="' + ui.item.value + '" />';
		html += '	<input type="hidden" name="product_option[' + option_row + '][type]" value="' + ui.item.type + '" />';
		html += '	<table class="form">';
		html += '	  <tr>';
		html += '		<td><?= $entry_required ?></td>';
		html += '       <td><select name="product_option[' + option_row + '][required]">';
		html += '	      <option value="1"><?= $text_yes ?></option>';
		html += '	      <option value="0"><?= $text_no ?></option>';
		html += '	    </select></td>';
		html += '     </tr>';
		
		if (ui.item.type == 'text') {
			html += '     <tr>';
			html += '       <td><?= $entry_option_value ?></td>';
			html += '       <td><input type="text" name="product_option[' + option_row + '][option_value]" value="" /></td>';
			html += '     </tr>';
		}
		
		if (ui.item.type == 'textarea') {
			html += '     <tr>';
			html += '       <td><?= $entry_option_value ?></td>';
			html += '       <td><textarea name="product_option[' + option_row + '][option_value]" cols="40" rows="5"></textarea></td>';
			html += '     </tr>';						
		}
		 
		if (ui.item.type == 'file') {
			html += '     <tr style="display: none;">';
			html += '       <td><?= $entry_option_value ?></td>';
			html += '       <td><input type="text" name="product_option[' + option_row + '][option_value]" value="" /></td>';
			html += '     </tr>';			
		}
						
		if (ui.item.type == 'date') {
			html += '     <tr>';
			html += '       <td><?= $entry_option_value ?></td>';
			html += '       <td><input type="text" name="product_option[' + option_row + '][option_value]" value="" class="date" /></td>';
			html += '     </tr>';			
		}
		
		if (ui.item.type == 'datetime') {
			html += '     <tr>';
			html += '       <td><?= $entry_option_value ?></td>';
			html += '       <td><input type="text" name="product_option[' + option_row + '][option_value]" value="" class="datetime" /></td>';
			html += '     </tr>';			
		}
		
		if (ui.item.type == 'time') {
			html += '     <tr>';
			html += '       <td><?= $entry_option_value ?></td>';
			html += '       <td><input type="text" name="product_option[' + option_row + '][option_value]" value="" class="time" /></td>';
			html += '     </tr>';			
		}
		
		html += '  </table>';
			
		if (ui.item.type == 'select' || ui.item.type == 'radio' || ui.item.type == 'checkbox' || ui.item.type == 'image') {
			html += '  <table id="option-value' + option_row + '" class="list">';
			html += '  	 <thead>'; 
			html += '      <tr>';
			html += '        <td class="left"><?= $entry_option_value ?></td>';
			html += '        <td class="right"><?= $entry_quantity ?></td>';
			html += '        <td class="left"><?= $entry_subtract ?></td>';
			html += '        <td class="right"><?= $entry_price ?></td>';
			html += '        <td class="right"><?= $entry_option_points ?></td>';
			html += '        <td class="right"><?= $entry_weight ?></td>';
			html += '        <td></td>';
			html += '      </tr>';
			html += '  	 </thead>';
			html += '    <tfoot>';
			html += '      <tr>';
			html += '        <td colspan="6"></td>';
			html += '        <td class="left"><a onclick="addOptionValue(' + option_row + ');" class="button"><?= $button_add_option_value ?></a></td>';
			html += '      </tr>';
			html += '    </tfoot>';
			html += '  </table>';
			html += '</div>';	
		}
		
		$('#tab-option').append(html);
		
		$('#option-add').before('<a href="#tab-option-' + option_row + '" id="option-' + option_row + '">' + ui.item.label + '&nbsp;<img src="view/image/delete.png" alt="" onclick="$(\'#vtab-option a:first\').trigger(\'click\'); $(\'#option-' + option_row + '\').remove(); $(\'#tab-option-' + option_row + '\').remove(); return false;" /></a>');
		
		$('#vtab-option a').tabs();
		
		$('#option-' + option_row).trigger('click');		
		
		$('.date').datepicker({dateFormat: 'yy-mm-dd'});
		$('.datetime').datetimepicker({
			dateFormat: 'yy-mm-dd',
			timeFormat: 'h:m'
		});	
			
		$('.time').timepicker({timeFormat: 'h:m'});	
				
		option_row++;
		
		return false;
	}
});
//--></script> 
<script type="text/javascript"><!--		
var option_value_row = <?= $option_value_row ?>;

function addOptionValue(option_row) {	
	html  = '<tbody id="option-value-row' + option_value_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][option_value_id]"></select><input type="hidden" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][product_option_value_id]" value="" /></td>';
	html += '    <td class="right"><input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][quantity]" value="" size="3" /></td>'; 
	html += '    <td class="left"><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][subtract]">';
	html += '      <option value="1"><?= $text_yes ?></option>';
	html += '      <option value="0"><?= $text_no ?></option>';
	html += '    </select></td>';
	html += '    <td class="right"><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][price_prefix]">';
	html += '      <option value="+">+</option>';
	html += '      <option value="-">-</option>';
	html += '    </select>';
	html += '    <input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][price]" value="" size="5" /></td>';
	html += '    <td class="right"><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][points_prefix]">';
	html += '      <option value="+">+</option>';
	html += '      <option value="-">-</option>';
	html += '    </select>';
	html += '    <input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][points]" value="" size="5" /></td>';	
	html += '    <td class="right"><select name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][weight_prefix]">';
	html += '      <option value="+">+</option>';
	html += '      <option value="-">-</option>';
	html += '    </select>';
	html += '    <input type="text" name="product_option[' + option_row + '][product_option_value][' + option_value_row + '][weight]" value="" size="5" /></td>';
	html += '    <td class="left"><a onclick="$(\'#option-value-row' + option_value_row + '\').remove();" class="button"><?= $button_remove ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#option-value' + option_row + ' tfoot').before(html);

	$('select[name=\'product_option[' + option_row + '][product_option_value][' + option_value_row + '][option_value_id]\']').load('index.php?route=catalog/product/option&token=<?= $token ?>&option_id=' + $('input[name=\'product_option[' + option_row + '][option_id]\']').attr('value'));
	
	option_value_row++;
}
//--></script> 
<script type="text/javascript"><!--
var discount_row = <?= $discount_row ?>;

function addDiscount() {
	html  = '<tbody id="discount-row' + discount_row + '">';
	html += '  <tr>'; 
    html += '    <td class="left"><select name="product_discount[' + discount_row + '][customer_group_id]">';
    <?php foreach ($customer_groups as $customer_group) { ?>
    html += '      <option value="<?= $customer_group['customer_group_id'] ?>"><?= $customer_group['name'] ?></option>';
    <?php } ?>
    html += '    </select></td>';		
    html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][quantity]" value="" size="2" /></td>';
    html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][priority]" value="" size="2" /></td>';
	html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][price]" value="" /></td>';
    html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_start]" value="" class="date" /></td>';
	html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_end]" value="" class="date" /></td>';
	html += '    <td class="left"><a onclick="$(\'#discount-row' + discount_row + '\').remove();" class="button"><?= $button_remove ?></a></td>';
	html += '  </tr>';	
    html += '</tbody>';
	
	$('#discount tfoot').before(html);
		
	$('#discount-row' + discount_row + ' .date').datepicker({dateFormat: 'yy-mm-dd'});
	
	discount_row++;
}
//--></script> 
<script type="text/javascript"><!--
var special_row = <?= $special_row ?>;

function addSpecial() {
	html  = '<tbody id="special-row' + special_row + '">';
	html += '  <tr>'; 
    html += '    <td class="left"><select name="product_special[' + special_row + '][customer_group_id]">';
    <?php foreach ($customer_groups as $customer_group) { ?>
    html += '      <option value="<?= $customer_group['customer_group_id'] ?>"><?= $customer_group['name'] ?></option>';
    <?php } ?>
    html += '    </select></td>';		
    html += '    <td class="right"><input type="text" name="product_special[' + special_row + '][priority]" value="" size="2" /></td>';
	html += '    <td class="right"><input style="width: 75%" type="text"class="specCalculator" name="product_special[' + special_row + '][price]" value="" /></td>';
    html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_start]" value="" class="date" /></td>';
	html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_end]" value="" class="date" /></td>';
	html += '    <td class="left"><a onclick="$(\'#special-row' + special_row + '\').remove();" class="button"><?= $button_remove ?></a></td>';
	html += '  </tr>';
    html += '</tbody>';
	
	$('#special tfoot').before(html);
 
	$('#special-row' + special_row + ' .date').datepicker({dateFormat: 'yy-mm-dd'});
	
    $('#special-row' + special_row + ' .specCalculator').calculator({showOn: 'opbutton', buttonImageOnly: true, buttonImage: 'view/image/calculator.png'});

	special_row++;
}
//--></script> 
<script type="text/javascript"><!--
function image_upload(field, thumb) {
	$('#dialog').remove();
	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?= $token ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	
	$('#dialog').dialog({
		title: '<?= $text_image_manager ?>',
		close: function (event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: 'index.php?route=common/filemanager/image&token=<?= $token ?>&image=' + encodeURIComponent($('#' + field).attr('value')),
					dataType: 'text',
					success: function(text) {
						$('#' + thumb).replaceWith('<img src="' + text + '" alt="" id="' + thumb + '" />');
					}
				});
			}
		},	
		bgiframe: false,
		width: 1000,
		height: 550,
		resizable: false,
		modal: false
	});
};
//--></script> 
<script type="text/javascript"><!--
var image_row = <?= $image_row ?>;

function addImage() {
    html  = '<tbody id="image-row' + image_row + '">';
	html += '  <tr>';
	html += '    <td class="left"><div class="image"><img src="<?= $no_image ?>" alt="" id="thumb' + image_row + '" /><input type="hidden" name="product_image[' + image_row + '][image]" value="" id="image' + image_row + '" /><br /><a onclick="image_upload(\'image' + image_row + '\', \'thumb' + image_row + '\');"><?= $text_browse ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$(\'#thumb' + image_row + '\').attr(\'src\', \'<?= $no_image ?>\'); $(\'#image' + image_row + '\').attr(\'value\', \'\');"><?= $text_clear ?></a></div></td>';
	html += '    <td class="right"><input type="text" name="product_image[' + image_row + '][sort_order]" value="" /></td>';
	html += '    <td class="left"><a onclick="$(\'#image-row' + image_row  + '\').remove();" class="button"><?= $button_remove ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#images tfoot').before(html);
	
	image_row++;
}
//--></script> 
<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script> 
<script type="text/javascript"><!--
$('.date').datepicker({dateFormat: 'yy-mm-dd'});
$('.datetime').datetimepicker({
	dateFormat: 'yy-mm-dd',
	timeFormat: 'h:m'
});
$('.time').timepicker({timeFormat: 'h:m'});
//--></script> 
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
$('#languages a').tabs(); 
$('#vtab-option a').tabs();
//--></script>

<script type="text/javascript"><!--
    $("#option_type").live('change', function() {
        if(this.value == 'select' || this.value == 'radio' || this.value == 'checkbox' || this.value == 'image') {
        	$('#option-value').show();
        } else {
        	$('#option-value').hide();
        }
    });

    var option_value_row = <?= $option_value_row ?>;

function addCreatedOptionValue() {
    html  = '<tbody id="option-value-row' + option_value_row + '">';
    html += '<tr>';
    html += '<td class="left"><input type="hidden" name="option_value[' + option_value_row + '][option_value_id]" value="" />';
    <?php foreach($languages as $language) { ?>
        html += '<input type="text" name="option_value[' + option_value_row + '][option_value_description][<?= $language['language_id'] ?>][name]" value="" /> <img src="view/image/flags/<?= $language['image'] ?>" title="<?= $language['name'] ?>" /><br />';
        <?php } ?>
    html += '</td>';
    html += '<td class="left"><div class="image"><img src="<?= $no_image ?>" alt="" id="option_value_thumb' + option_value_row + '" /><input type="hidden" name="option_value[' + option_value_row + '][image]" value="" id="option_value_image' + option_value_row + '" /><br /><a onclick="option_value_image_upload(\'option_value_image' + option_value_row + '\', \'option_value_thumb' + option_value_row + '\');"><?= $text_browse ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$(\'#option_value_thumb' + option_value_row + '\').attr(\'src\', \'<?= $no_image ?>\'); $(\'#option_value_image' + option_value_row + '\').attr(\'value\', \'\');"><?= $text_clear ?></a></div></td>';
    html += '<td class="right"><input type="text" name="option_value[' + option_value_row + '][sort_order]" value="" size="1" /></td>';
    html += '<td class="left"><a onclick="$(\'#option-value-row' + option_value_row + '\').remove();" class="button"><?= $button_remove ?></a></td>';
    html += '</tr>';
    html += '</tbody>';

    $('#option-value tfoot').before(html);

    option_value_row++;
}

function option_value_image_upload(field, thumb) {
    $('#dialog').remove();

    $('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?= $token ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');

    $('#dialog').dialog({
        title: '<?= $text_image_manager ?>',
    	close: function (event, ui) {
            if($('#' + field).attr('value')) {
                $.ajax({
                    url: 'index.php?route=common/filemanager/image&token=<?= $token ?>&image=' + encodeURIComponent($('#' + field).attr('value')),
                    dataType: 'text',
                    success: function(data) {
                        $('#' + thumb).replaceWith('<img src="' + data + '" alt="" id="' + thumb + '" />');
                    }
                });
            }
    	},
    	bgiframe: false,
    	width: 800,
    	height: 400,
    	resizable: false,
    	modal: false
    });
};

$("#create_option_button").live('click', function() {
    $.ajax({
        url: 'index.php?route=catalog/product/createOption&token=<?= $token ?>',
        type: 'POST',
        data: $("#product_option_form").serialize(),
        dataType: 'json',
        success: function(json) {
            $("#tab-create-option").html(json.data);
            $(".success").delay(2000).fadeOut(400);
        }
    });
});
//--></script>
<?= $footer ?>