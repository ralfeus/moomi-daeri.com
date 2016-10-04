<?php
/** @var \model\catalog\Product $model */
?>
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
<?php foreach ($languages as $language): ?>
            <a href="#language<?= $language['language_id'] ?>"><img src="view/image/flags/<?= $language['image'] ?>" title="<?= $language['name'] ?>" /> <?= $language['name'] ?></a>
<?php endforeach; ?>
          </div>
<?php foreach ($languages as $language): ?>
          <div id="language<?= $language['language_id'] ?>">
            <table class="form">
              <tr>
                <td><span class="required">*</span> <?= $entry_name ?></td>
                <td><input type="text" class="language<?= $language['language_id'] ?>" name="product_description[<?= $language['language_id'] ?>][name]" maxlength="255" size="100" value="<?= is_null($model->getDescriptions()->getDescription($language['language_id'])) ? '' : $model->getDescriptions()->getDescription($language['language_id'])->getName() ?>" />
                  <span style="font-size: 10px;"><?= $text_last ?></span><span id="summar<?= $language['language_id'] ?>"></span>


                  <?php if (isset($error_name[$language['language_id']])) { ?>
                  <span class="error"><?= $error_name[$language['language_id']] ?></span>
                  <?php } ?></td>
              </tr>
              <tr>
                <td><?= $entry_seo_h1 ?></td>
                <td><input type="text" name="product_description[<?= $language['language_id'] ?>][seo_h1]" maxlength="255" size="100" value="<?= is_null($model->getDescriptions()->getDescription($language['language_id'])) ? '' : $model->getDescriptions()->getDescription($language['language_id'])->getSeoH1() ?>" /></td>
              </tr>
              <tr>
                <td><?= $entry_seo_title ?></td>
                <td><input type="text" name="product_description[<?= $language['language_id'] ?>][seo_title]" maxlength="255" size="100" value="<?= is_null($model->getDescriptions()->getDescription($language['language_id'])) ? '' : $model->getDescriptions()->getDescription($language['language_id'])->getSeoTitle() ?>" /></td>
              </tr>
              <tr>
                <td><?= $entry_meta_keyword ?></td>
                <td><textarea name="product_description[<?= $language['language_id'] ?>][meta_keyword]" cols="100" rows="2"><?= is_null($model->getDescriptions()->getDescription($language['language_id'])) ? '' : $model->getDescriptions()->getDescription($language['language_id'])->getMetaKeyword()?></textarea></td>
              </tr>
              <tr>
                <td><?= $entry_meta_description ?></td>
                <td><textarea name="product_description[<?= $language['language_id'] ?>][meta_description]" cols="100" rows="2"><?= is_null($model->getDescriptions()->getDescription($language['language_id'])) ? '' : $model->getDescriptions()->getDescription($language['language_id'])->getMetaDescription() ?></textarea></td>
              </tr>
              <tr>
                <td><?= $entry_description ?></td>
                <td><textarea name="product_description[<?= $language['language_id'] ?>][description]" id="description<?= $language['language_id'] ?>"><?= is_null($model->getDescriptions()->getDescription($language['language_id'])) ? '' : $model->getDescriptions()->getDescription($language['language_id'])->getDescription() ?></textarea></td>
              </tr>
              <tr>
                <td><?= $entry_tag ?></td>
                <td><input type="text" name="product_tag[<?= $language['language_id'] ?>]" value="<?= $model->getTags()[$language['language_id']] ?>" size="80" /></td>
              </tr>
            </table>
          </div>
<?php endforeach; ?>
        </div>
         <div id="tab-data">
          <table class="form">
            <tr>
              <td><span class="required">*</span> <?= $entry_model ?></td>
              <td><input type="text" style="width: 800px;" class="textmodel" name="model" value="<?= $model->getModel() ?>" />
                <span style="font-size: 10px;"><?= $text_last ?></span><span id="summarmodel"></span>
                <?php if ($error_model) { ?>
                <span class="error"><?= $error_model ?></span>
                <?php } ?></td>
            </tr>
            <tr>
                <td><?= $textKoreanName ?></td>
                <td><input type="text" style="width: 800px;" name="koreanName" value="<?= $model->getKoreanName() ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_sku ?></td>
              <td><input type="text" name="sku" value="<?= $model->getSku() ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_upc ?></td>
              <td><input type="text" name="upc" value="<?= $model->getUpc() ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_location ?></td>
              <td><input type="text" name="location" value="<?= $model->getLocation() ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_price ?></td>
              <td><input type="text" id="Calculator" name="price" value="<?= $model->getPrice() ?>" /></td>
            </tr>
            <?php /*<tr>
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
            </tr> */ ?>
            <tr>
              <td><?= $entry_quantity ?></td>
              <td><input type="text" name="quantity" value="<?= $model->getQuantity() ?>" size="2" /></td>
            </tr>
            <tr>
              <td><?= $entry_minimum ?></td>
              <td><input type="text" name="minimum" value="<?= $model->getMinimum() ?>" size="2" /></td>
            </tr>
            <tr>
              <td><?= $entry_subtract ?></td>
              <td><select name="subtract">
                  <?php if ($model->getSubtract()) { ?>
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
                  <?php if ($stock_status['stock_status_id'] == $model->getStockStatusId()) { ?>
                  <option value="<?= $stock_status['stock_status_id'] ?>" selected="selected"><?= $stock_status['name'] ?></option>
                  <?php } else { ?>
                  <option value="<?= $stock_status['stock_status_id'] ?>"><?= $stock_status['name'] ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_shipping ?></td>
              <td><?php if ($model->getShipping()) { ?>
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
              <td><input type="text" name="keyword" value="<?= $model->getKeyword() ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_image ?></td>
              <td>
                <div class="image">
                  <img src="<?= $model->getThumb() ?>" alt="" id="thumb" />
                </div><br />
                  <a onclick="image_upload('image', 'thumb');"><?= $text_browse ?></a>
                  &nbsp;&nbsp;|&nbsp;&nbsp;
                  <a onclick="$('#thumb').attr('src', '<?= $no_image ?>'); $('#image').attr('value', '');"><?= $text_clear ?></a>
              </td>
            </tr>
            <tr>
              <td><?= $entry_image_path ?></td>
              <td>
                  <label style="float: left">/image/</label>
                  <div style="overflow: hidden">
                      <input type="text" name="image" value="<?= $model->getImagePath() ?>" id="image" style="width: 100%"/>
                  </div>
              </td>
            </tr>
            <tr>
                <td><?= $entry_image_description ?></td>
                <td><textarea name="image_description" id="image_description"><?= $model->getImageDescription() ?></textarea></td>
            </tr>
            <tr>
              <td><?= $entry_date_available ?></td>
              <td><input type="text" name="date_available" value="<?= $model->getDateAvailable() ?>" size="12" class="date" /></td>
            </tr>
            <tr>
              <td><?= $entry_dimension ?></td>
              <td><input type="text" name="length" value="<?= $model->getDimension()->getLength() ?>" size="4" />
                <input type="text" name="width" value="<?= $model->getDimension()->getWidth() ?>" size="4" />
                <input type="text" name="height" value="<?= $model->getDimension()->getHeight() ?>" size="4" /></td>
            </tr>
            <tr>
              <td><?= $entry_length ?></td>
              <td><select name="length_class_id">
                  <?php foreach ($length_classes as $length_class) { ?>
                  <?php if ($length_class['length_class_id'] == $model->getDimension()->getUnit()->getId()) { ?>
                  <option value="<?= $length_class['length_class_id'] ?>" selected="selected"><?= $length_class['title'] ?></option>
                  <?php } else { ?>
                  <option value="<?= $length_class['length_class_id'] ?>"><?= $length_class['title'] ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
            </tr>
            <tr>
              <td><?= $entry_weight ?></td>
              <td><input type="text" name="weight" value="<?= $model->getWeight()->getWeight() ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_weight_class ?></td>
              <td><select name="weight_class_id">
                  <?php foreach ($weight_classes as $weight_class) { ?>
                  <?php if ($weight_class['weight_class_id'] == $model->getWeight()->getUnit()->getId()) { ?>
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
                  <?php if ($model->getStatus()) { ?>
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
              <td><input type="text" name="sort_order" value="<?= $model->getSortOrder() ?>" size="2" /></td>
            </tr>
            <tr>
              <td><?= $this->language->get('entry_commission') ?></td>
              <td><input type="text" name="affiliate_commission" value="<?= $model->getAffiliateCommission() ?>" size="4" /></td>
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
                    $selected = ($manufacturer->getId() == $model->getManufacturer()->getId()) ? " selected" : ""; ?>
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
    $selected = ($supplier->getId() == $model->getSupplier()->getId()) ? "selected" : ""; ?>
                        <option value="<?= $supplier->getId() ?>" <?= $selected ?>><?= $supplier->getName() ?></option>
<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?= $textSupplierUrl ?></td>
                <td><input type="text" style="width: 800px;" name="supplierUrl" value="<?= $model->getSupplierUrl() ?>" /></td>
            </tr>
            <tr>
              <td><?= $entry_main_category ?></td>
              <td>
                <select name="main_category_id">
                  <option value="0" selected="selected"><?= $text_none ?></option>
<?php foreach ($categories as $category): ?>
                  <option
                      value="<?= $category['category_id'] ?>"
                      <?= $model->isMainCategory($category['category_id']) ? 'selected="selected"' : '' ?>>
                      <?= $category['name'] ?>
                  </option>
<?php endforeach; ?>
                </select>
              </td>
            </tr>
            <tr>
              <td><?= $entry_store ?></td>
              <td><div class="scrollbox">
                  <?php $class = 'even'; ?>
                  <div class="<?= $class ?>">
                    <?php if (in_array(0, $model->getStores())) { ?>
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
                    <?php if (in_array($store['store_id'], $model->getStores())) { ?>
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
              <td style="width: 250px;">&nbsp;</td>
 
            </tr>
            <tr>
              <td><?= $entry_download ?></td>
              <td>
                <div class="scrollbox">
<?php $class = 'odd'; ?>
<?php foreach ($downloads as $download): ?>
    <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?= $class ?>">
                    <input
                        type="checkbox"
                        id="product_download_<?= $download['download_id'] ?>" name="product_download[]"
                        value="<?= $download['download_id'] ?>"
                        <?= in_array($download['download_id'], $model->getDownloads()) ? 'checked="checked"' : '' ?> />
                    <label for="product_download_<?= $download['download_id'] ?>"><?= $download['name'] ?></label>
                  </div>
<?php endforeach; ?>
                </div></td>
            </tr>
            <tr>
              <td><?= $entry_related ?></td>
              <td><input type="text" name="related" value="" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td>
                <div class="scrollbox" id="product-related">
<?php $class = 'odd'; ?>
<?php foreach ($model->getRelated() as $relatedProduct): ?>
    <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div id="product-related<?= $relatedProduct->getId() ?>" class="<?= $class ?>"> <?= $relatedProduct->getName() ?>
                    <img src="view/image/delete.png" />
                    <input type="hidden" name="product_related[]" value="<?= $relatedProduct->getId() ?>" />
                  </div>
<?php endforeach; ?>
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
            <?php foreach ($model->getAttributes() as $product_attribute) { ?>
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
<?php foreach ($model->getOptions() as $productOption): ?>
            <a href="#tab-option-<?= $option_row ?>" id="option-<?= $option_row ?>">
                <?= $productOption->getOption()->getName() ?>&nbsp;
                <img src="view/image/delete.png" alt="" 
                     onclick="$('#vtabs').find('a:first').trigger('click'); $('#option-<?= $option_row ?>').remove(); $('#tab-option-<?= $option_row ?>').remove(); return false;" />
            </a>
            <?php $option_row++; ?>
<?php endforeach; ?>
            <span id="option-add">
            <input name="option" value="" style="width: 130px;" />
            &nbsp;<img src="view/image/add.png" alt="<?= $button_add_option ?>" title="<?= $button_add_option ?>" /></span></div>
          <?php $option_row = 0; ?>
          <?php $option_value_row = 0; ?>
<?php foreach ($model->getOptions() as $productOption): ?>
          <div id="tab-option-<?= $option_row ?>" class="vtabs-content">
            <input type="hidden" name="product_option[<?= $option_row ?>][product_option_id]" value="<?= $productOption->getId() ?>" />
            <input type="hidden" name="product_option[<?= $option_row ?>][name]" value="<?= $productOption->getOption()->getName() ?>" />
            <input type="hidden" name="product_option[<?= $option_row ?>][option_id]" value="<?= $productOption->getOption()->getId() ?>" />
            <input type="hidden" name="product_option[<?= $option_row ?>][type]" value="<?= $productOption->getType() ?>" />
            <table class="form">
              <tr>
                <td><?= $entry_required ?></td>
                <td><select name="product_option[<?= $option_row ?>][required]">
                    <?php if ($productOption->isRequired()) { ?>
                    <option value="1" selected="selected"><?= $text_yes ?></option>
                    <option value="0"><?= $text_no ?></option>
                    <?php } else { ?>
                    <option value="1"><?= $text_yes ?></option>
                    <option value="0" selected="selected"><?= $text_no ?></option>
                    <?php } ?>
                  </select></td>
              </tr>
    <?php if ($productOption->getType() == 'text'): ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $productOption->getValue() ?>" /></td>
              </tr>
    <?php endif; ?>
    <?php if ($productOption->getType() == 'textarea'): ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><textarea name="product_option[<?= $option_row ?>][option_value]" cols="40" rows="5"><?= $productOption->getValue() ?></textarea></td>
              </tr>
    <?php endif; ?>
    <?php if ($productOption->getType() == 'file'): ?>
              <tr style="display: none;">
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $productOption->getValue() ?>" /></td>
              </tr>
    <?php endif; ?>
    <?php if ($productOption->getType() == 'date'): ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $productOption->getValue() ?>" class="date" /></td>
              </tr>
    <?php endif; ?>
    <?php if ($productOption->getType() == 'datetime'): ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $productOption->getValue() ?>" class="datetime" /></td>
              </tr>
    <?php endif; ?>
    <?php if ($productOption->getType() == 'time'): ?>
              <tr>
                <td><?= $entry_option_value ?></td>
                <td><input type="text" name="product_option[<?= $option_row ?>][option_value]" value="<?= $productOption->getValue() ?>" class="time" /></td>
              </tr>
    <?php endif; ?>
            </table>
    <?php if ($productOption->getOption()->isMultiValueType()): ?>
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
        <?php foreach ($productOption->getValue() as $productOptionValue): ?>
              <tbody id="option-value-row<?= $option_value_row ?>">
                <tr>
                  <td class="left">
                    <select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][option_value_id]"></select>
                    <input type="hidden" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][product_option_value_id]" value="<?= $productOptionValue->getId() ?>" /></td>
                  <td class="right"><input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][quantity]" value="<?= $productOptionValue->getQuantity() ?>" size="3" /></td>
                  <td class="left">
                    <select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][subtract]">
            <?php if ($productOptionValue->getSubtract()): ?>
                    <option value="1" selected="selected"><?= $text_yes ?></option>
                    <option value="0"><?= $text_no ?></option>
            <?php else: ?>
                    <option value="1"><?= $text_yes ?></option>
                    <option value="0" selected="selected"><?= $text_no ?></option>
            <?php endif; ?>
                    </select>
                  </td>
                  <td class="right">
                    <select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][price_prefix]">
                      <option value="+" <?= $productOptionValue->getPrice() >= 0 ? 'selected="selected"' : '' ?>>+</option>
                      <option value="+" <?= $productOptionValue->getPrice() < 0 ? 'selected="selected"' : '' ?>>-</option>
                    </select>
                    <input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][price]" value="<?= $productOptionValue->getPrice() ?>" size="5" />
                  </td>
                  <td class="right">
                    <select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][points_prefix]">
                      <option value="+" <?= $productOptionValue->getPoints() >= 0 ? 'selected="selected"' : '' ?>>+</option>
                      <option value="+" <?= $productOptionValue->getPoints() < 0 ? 'selected="selected"' : '' ?>>-</option>
                    </select>
                    <input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][points]" value="<?= $productOptionValue->getPoints() ?>" size="5" /></td>
                  <td class="right">
                    <select name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][weight_prefix]">
                      <option value="+" <?= $productOptionValue->getWeight() >= 0 ? 'selected="selected"' : '' ?>>+</option>
                      <option value="+" <?= $productOptionValue->getWeight() < 0 ? 'selected="selected"' : '' ?>>-</option>
                    </select>
                    <input type="text" name="product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][weight]" value="<?= $productOptionValue->getWeight() ?>" size="5" /></td>
                  <td class="left"><a onclick="$('#option-value-row<?= $option_value_row ?>').remove();" class="button"><?= $button_remove ?></a></td>
                </tr>
              </tbody>
              <?php $option_value_row++; ?>
        <?php endforeach; ?>
              <tfoot>
                <tr>
                  <td colspan="6"></td>
                  <td class="left"><a onclick="addOptionValue('<?= $option_row ?>');" class="button"><?= $button_add_option_value ?></a></td>
                </tr>
              </tfoot>
            </table>
    <?php endif; ?>
          </div>
          <?php $option_row++; ?>
<?php endforeach; ?>
          <script type="text/javascript"><!--
          <?php $option_row = 0; ?>
          <?php $option_value_row = 0; ?>		  
<?php foreach ($model->getOptions() as $productOption): ?>
    <?php if ($productOption->getOption()->isMultiValueType()): ?>
        <?php foreach ($productOption->getValue() as $productOptionValue): ?>
		  $('select[name=\'product_option[<?= $option_row ?>][product_option_value][<?= $option_value_row ?>][option_value_id]\']')
              .load('index.php?route=catalog/product/option&token=<?= $token ?>&option_id=<?= $productOption->getOption()->getId() ?>&option_value_id=<?= $productOptionValue->getOptionValue()->getId() ?>');
            <?php $option_value_row++; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php $option_row++; ?>
<?php endforeach; ?>
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
            <?php foreach ($model->getDiscounts() as $product_discount) { ?>
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
            <?php foreach ($model->getSpecials() as $product_special) { ?>
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
              <td><input type="text" name="points" value="<?= $model->getPoints() ?>" /></td>
            </tr>
          </table>
          <table class="list">
            <thead>
              <tr>
                <td class="left"><?= $entry_customer_group ?></td>
                <td class="right"><?= $entry_reward ?></td>
              </tr>
            </thead>
<?php foreach ($customer_groups as $customer_group): ?>
            <tbody>
              <tr>
                <td class="left"><?= $customer_group['name'] ?></td>
                <td class="right">
                  <input type="text" name="product_reward[<?= $customer_group['customer_group_id'] ?>][points]"
                         value="<?= isset($model->getRewards()[$customer_group['customer_group_id']]) ? $model->getRewards()[$customer_group['customer_group_id']]['points'] : '' ?>" />
                </td>
              </tr>
            </tbody>
<?php endforeach; ?>
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
<?php foreach ($layouts as $layout): ?>
                  <option
                    value="<?= $layout['layout_id'] ?>"
                    <?= isset($model->getLayouts()[0]) && $model->getLayouts()[0] == $layout['layout_id'] ? 'selected="selected"' : '' ?>>
                    <?= $layout['name'] ?></option>
<?php endforeach; ?>
                  </select>
                </td>
              </tr>
            </tbody>
<?php foreach ($stores as $store): ?>
            <tbody>
              <tr>
                <td class="left"><?= $store['name'] ?></td>
                <td class="left"><select name="product_layout[<?= $store['store_id'] ?>][layout_id]">
                  <option value=""></option>
    <?php foreach ($layouts as $layout): ?>
                  <option
                    value="<?= $layout['layout_id'] ?>"
                    <?= isset($model->getLayouts()[$store['store_id']]) && $model->getLayouts()[$store['store_id']] == $layout['layout_id']
                      ? 'selected="selected"' : '' ?>>
                    <?= $layout['name'] ?>
                  </option>
    <?php endforeach; ?>
                  </select>
                </td>
              </tr>
            </tbody>
<?php endforeach; ?>
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
  html += '      <option value="0"><?= $text_no ?></option>';
	html += '      <option value="1"><?= $text_yes ?></option>';
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

<script type="text/javascript">
$(document).ready(function() {
  $('#tree1').checkboxTree({
    initializeChecked: 'expanded', 
    initializeUnchecked: 'collapsed'
  });	

  $('#checkAll').click(function(){
    $('#tree1').checkboxTree('checkAll');
  });

  $('#uncheckAll').click(function(){
    $('#tree1').checkboxTree('uncheckAll');
  });

  $('#expandAll').click(function(){
    $('#tree1').checkboxTree('expandAll');
  });
  $('#collapseAll').click(function(){
    $('#tree1').checkboxTree('collapseAll');
  });
});
</script>
  <script> 
$(document).ready(function(){				
$('.language1').limit('255','#summar1');
$('.language2').limit('255','#summar2');
$('.language3').limit('255','#summar3');
$('.language4').limit('255','#summar4');
$('.language5').limit('255','#summar5');
$('.textmodel').limit('64','#summarmodel');
});	
  </script> 

<?= $footer ?>