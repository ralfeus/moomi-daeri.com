<?php
/** @var \model\catalog\Manufacturer[] $manufacturers */
/** @var \model\catalog\Option[] $options */
/** @var \model\catalog\Supplier[] $suppliers */
?>
<?= $header ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
        <?php } ?>
    </div>

    <?php if ($error_warning) { ?>
        <div class="warning"><?= $error_warning ?></div>
    <?php } ?>

    <div class="box" id="mpchanges_box">
        <div class="heading">
            <h1><img src="view/image/module.png" alt="" /> <?= $heading_title ?></h1>
            <div class="buttons">
                <a onclick="location = '<?= $url_cancel ?>';" class="button">
                    <?= $button_cancel ?>
                </a>
            </div>
        </div>
        <div class="content">
            <table class="form" id="filter">
                <tr>
                    <td><label for="store_id"<?= $entry_store ?></label></td>
                    <td>
                        <select id="store_id" name="store_id" class="filter_option">
                            <option value="0"><?= $store_default ?></option>
                        </select>
                    </td>
                    <td rowspan="7">
                        <?php echo $text_filtered_products;?> <span id="product-total"></span>
                        <div class="scrollbox" id="check-all" style="height: 22px; width: 700px; background: #B7D7F5; cursor:pointer;"><input type="checkbox" checked="checked" name="change_all" value="true" /> Все товары</div>
                        <div class="scrollbox" id="filtered-products" style="height: 330px; width: 700px;"></div>
                    </td>
                </tr>
                <tr>
                    <td><?= $entry_manufacturer ?></td>
                    <td>
                        <select name="manufacturer_id" class="filter_option">
                            <option value="0"><?= $option_all ?></option>
                            <?php foreach ($manufacturers as $manufacturer) { ?>
                            <option value="<?= $manufacturer->getId() ?>"><?= $manufacturer->getName() ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="supplierId"><?= $textSupplier ?></label></td>
                    <td>
                        <select id="supplierId" name="supplierId" class="filter_option">
                            <option value="0"><?= $option_all ?></option>
<?php foreach ($suppliers as $supplier): ?>
                            <option value="<?= $supplier->getId() ?>"><?= $supplier->getName() ?></option>
<?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?= $entry_name ?></td>
                    <td><input type="text" name="name" value="" class="filter_option"/></td>
                </tr>
                <tr>
                    <td><label for="filterKoreanName"><?= $textKoreanName ?></label></td>
                    <td><input type="text" id="filterKoreanName" name="filterKoreanName" value="" class="filter_option"/></td>
                </tr>
                <tr>
                    <td><?= $entry_model ?></td>
                    <td>
                        <input type="text" name="model" value="" class="filter_option"/>
                    </td>
                </tr>
                <tr>
                    <td><?= $entry_category ?></td>
                    <td>
                        <select name="category_id" class="filter_option">
                            <option value="0"><?= $option_all ?></option>
                            <?php foreach ($categories as $category) { ?>
                            <option value="<?= $category['category_id'] ?>"><?= $category['name'] ?></option>
                            <?php } ?>
                        </select>
                        <input type="checkbox" id="filter_sub_category" name="filter_sub_category"/>
                        <label for="filter_sub_category"><?= $entry_subcategory ?></label>
                    </td>
                </tr>
                <tr>
                    <td><?= $entry_customer_group ?></td>
                    <td>
                        <select name="customer_group" class="filter_option">
                            <option value="0"><?= $text_all ?></option>
                            <?php foreach ($customer_groups as $customer_group) { ?>
                            <option value="<?= $customer_group['customer_group_id'] ?>"><?= $customer_group['name'] ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?= $label_price ?></td>
                    <td>
                        <?= $label_price_from ?>
                        <input type="text" size="4" id="filter_price_from" name="filter_price_from" class="filter_option"/>
                        <?= $label_price_to ?>
                        <input type="text" size="4" id="filter_price_to" name="filter_price_to" class="filter_option"/>
                    </td>
                </tr>

                <tr>
                    <td><?= $label_round ?></td>
                    <td>
                        <input type="text" size="3" id="filter_round" name="filter_round" value="0" class="filter_option"/><?= $label_round_decimal ?>
                    </td>
                </tr>
                <tr>
                    <td><label for="filterDateAddedFrom" for="filterDateAddedTo"><?= $textDateAdded ?></label></td>
                    <td>
                        <input type="text" id="filterDateAddedFrom" name="filterDateAddedFrom" value="" class="filter_option date"/>&nbsp;-&nbsp;
                        <input type="text" id="filterDateAddedTo" name="filterDateAddedTo" value="" class="filter_option date"/>
                    </td>
                </tr>
                <tr>
                    <td><label for="filterEnabled"><?= $textEnabled ?></label></td>
                    <td>
                        <select id="filterEnabled" name="filterEnabled" class="filter_option">
                            <option></option>
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </td>
                </tr>
            </table>
            <div class="vtabs">
                <a href="#prices">
                    <?= $tab_prices ?>
                </a>
                <a href="#specials">
                    <?= $tab_specials ?>
                </a>
                <a href="#add_specials">
                    <?= $tab_add_specials ?>
                </a>
                <a href="#discounts" style="display: none;">
                    <?= $tab_discounts ?>
                </a>
                <a href="#add_discounts" style="display: none;">
                    <?= $tab_add_discounts ?>
                </a>
                <a href="#del_section" style="display: none;">
                    <?= $tab_del_section ?>
                </a>
                <a href="#options"><?= $textOptions ?></a>
            </div>

            <div id="prices" class="vtabs-content">
                <form action="<?= $action_change_price ?>" method="post" enctype="multipart/form-data" id="form_change_price">
                    <table class="form">
                        <tr>
                            <td><?= $entry_price ?></td>
                            <td colspan="2">
                                <select name="price_diff">
                                    <option value="-">-</option>
                                    <option value="+">+</option>
                                    <option value="*">*</option>
                                    <option value="/">/</option>
                                    <option value="=">=</option>
                                </select>
                                <input type="text" name="manufacturer_price" value=""/>
                                <select name="change_type">
                                    <option value="percent"><?= $label_percent ?></option>
                                    <option value="number"><?= $label_number ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?= $entry_quantities ?></td>
                            <td colspan="2">
                                <select name="quantities_diff">
                                    <option value="-">-</option>
                                    <option value="+">+</option>
                                    <option value="*">*</option>
                                    <option value="/">/</option>
                                    <option value="=">=</option>
                                </select>
                                <input type="text" name="manufacturer_quantities" value=""/>
                                <select name="change_type_quantities">
                                    <option value="number"><?= $label_number ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <a class="button submit-form" data-form="form_change_price"><?= $button_change_price ?></a>
                                </div>
                            </td>
                            <td colspan="3">
                                <input type="checkbox" name="change_special" id="change_special" /><label for="change_special"><?php echo $text_change_special?></label>
                                <input type="checkbox" name="change_discount" id="change_discount" /><label for="change_discount"><?php echo $text_change_discount?></label>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="specials" class="vtabs-content">
                <form action="<?= $action_change_specials ?>" method="post" enctype="multipart/form-data" id="form_change_specials">
                    <table class="form">
                        <tr>
                            <td><?= $entry_specials ?></td>
                            <td colspan="2">
                                <select name="price_diff">
                                    <option value="-">-</option>
                                    <option value="+">+</option>
                                    <option value="*">*</option>
                                    <option value="/">/</option>
                                    <option value="=">=</option>
                                </select>
                                <input type="text" name="manufacturer_price" value=""/>
                                <select name="change_type">
                                    <option value="percent"><?= $label_percent ?></option>
                                    <option value="number"><?= $label_number ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div>
                                    <a class="button submit-form" data-form="form_change_specials"><?= $button_change_specials ?></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="add_specials" class="vtabs-content">
                <form action="<?= $action_save_specials ?>" method="post" enctype="multipart/form-data" id="form_save_specials">
                    <table class="form">
                        <tr>
                            <td colspan="3">
                                <table id="special" class="list">
                                    <thead>
                                    <tr>
                                        <td class="left"><?= $entry_customer_group ?></td>
                                        <td class="right"><?= $entry_priority ?></td>
                                        <td class="right"><?= $entry_price_diff ?></td>
                                        <td class="left"><?= $entry_date_start ?></td>
                                        <td class="left"><?= $entry_date_end ?></td>
                                        <td>
                                            <div>
                                                <a class="button" onclick="addSpecials();"><?= $button_add_row ?></a>
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div>
                                    <a class="button submit-form" data-form="form_save_specials"><?= $button_add_specials ?></a>
                                </div>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>

            <div id="discounts" class="vtabs-content">
                <form action="<?= $action_change_discounts ?>" method="post" enctype="multipart/form-data" id="form_change_discounts">
                    <table class="form">
                        <tr>
                            <td><?= $entry_discounts ?></td>
                            <td colspan="2">
                                <select name="price_diff">
                                    <option value="-">-</option>
                                    <option value="+">+</option>
                                    <option value="*">*</option>
                                    <option value="/">/</option>
                                    <option value="=">=</option>
                                </select>
                                <input type="text" name="manufacturer_price" value=""/>
                                <select name="change_type">
                                    <option value="percent"><?= $label_percent ?></option>
                                    <option value="number"><?= $label_number ?></option>
                                </select>
                                <?= $label_quantity_prefix ?>
                                <input type="text" name="discount_quantity" size="3" value="1"/>
                                <?= $label_quantity_postfix ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div>
                                    <a class="button submit-form" data-form="form_change_discounts"><?= $button_change_discounts ?></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="add_discounts" class="vtabs-content">
                <form action="<?= $action_save_discounts ?>" method="post" enctype="multipart/form-data" id="form_save_discounts">
                    <table class="form">
                        <tr>
                            <td colspan="3">
                                <table id="discount" class="list">
                                    <thead>
                                    <tr>
                                        <td class="left"><?= $entry_customer_group ?></td>
                                        <td class="right"><?= $entry_quantities ?></td>
                                        <td class="right"><?= $entry_priority ?></td>
                                        <td class="right"><?= $entry_price_diff ?></td>
                                        <td class="left"><?= $entry_date_start ?></td>
                                        <td class="left"><?= $entry_date_end ?></td>
                                        <td>
                                            <div class="buttons">
                                                <a class="button" onclick="addDiscounts();"><?= $button_add_row ?></a>
                                            </div>
                                        </td>
                                    </tr>
                                    </thead>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div>
                                    <a class="button submit-form" data-form="form_save_discounts"><?= $button_add_discounts ?></a>
                                </div>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>
            <div id="del_section" class="vtabs-content">
                <form action="<?= $action_del_elements ?>" method="post" enctype="multipart/form-data" id="form_del_elements">
                    <table class="form">
                        <tr>
                            <td>
                                <input type="checkbox" name="del_product" id="del_product" /><label for="del_product"><?= $text_del_product ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="del_special" id="del_special" /><label for="del_special"><?= $text_del_special ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="del_discount" id="del_discount" /><label for="del_discount"><?= $text_del_discount ?></label>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div>
                                    <a class="button submit-form" data-form="form_del_elements"><?= $button_del_elements ?></a>
                                </div>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>
            <div id="options" class="vtabs-content">
                <form action="#" method="post" enctype="multipart/form-data" id="formOptions">
                    <input id="optionOperationAddOption" name="optionOperation" type="radio" value="AddOption" title="<?= $textAddOption ?>" />
                    <label for="optionOperationAddOption"><?= $textAddOption ?></label>
                    <input id="optionOperationDelOption" name="optionOperation" type="radio" value="DelOption" title="<?= $textDelOption ?>" />
                    <label for="optionOperationDelOption"><?= $textDelOption ?></label>
                    <input id="optionOperationAddOptionValue" name="optionOperation" type="radio" value="AddValue" title="<?= $textAddOptionValue ?>" />
                    <label for="optionOperationAddOptionValue"><?= $textAddOptionValue ?></label>
                    <input id="optionOperationDelOptionValue" name="optionOperation" type="radio" value="DelValue" title="<?= $textDelOptionValue ?>" />
                    <label for="optionOperationDelOptionValue"><?= $textDelOptionValue ?></label>
                    <br />
                    <label for="options"><?= $textOptions ?></label><br />
                    <select id="options" title="<?= $textOptions ?>" onchange="reloadOptionValues(this)">
                        <?php foreach ($options as $option): ?>
                            <option value="<?= $option->getId() ?>" content="<?= $option->getType() ?>"><?= $option->getName() ?></option>
                        <?php endforeach; ?>
                    </select>
                    <br />
                    <table id="optionValuesSection" style="display: none">
                        <tr><td><label for="optionValues"><?= $textOptionValues ?></label><br /></td></tr>
                        <tr id="singleValue"><td>
                            <input class="optionValue" title="<?= $textOptionValues ?>" />
                        </td></tr>
                        <tr id="multiValue">
                            <td>
                                <select class="optionValue" title="<?= $textOptionValues ?>"></select>
                            </td>
                            <td>
                                <label for="price"><?= $textOptionValuePrice ?></label>
                                <input type="text" id="price" title="<?= $textOptionValuePrice ?>" />
                            </td>
                            <td>
                                <label for="weight"><?= $textOptionValueWeight ?></label>
                                <input type="text" id="weight" title="<?= $textOptionValueWeight ?>" />
                            </td>
                        </tr>
                    </table>
                </form>
                <a class="button" onclick="setOptions()"><?= $textExecute ?></a>
            </div>
        </div>
    </div>
    <style>
        #filtered-products div:hover{background: lightgrey;}
        #filtered-products div{height:16px;}
    </style>
    <div id="wait" style="display: none;">
        <p id="message"></p>
        <img src="<?= HTTP_IMAGE ?>ajax-loader.gif" />
    </div>
<script>
    $(function(){
        $(".submit-form").live('click', function(){
            $.ajax({
                url: $('#' + $(this).data('form')).attr('action'),
                type: 'post',
                data: $('#' + $(this).data('form') + ' input[type="text"], #' + $(this).data('form') + ' input[type="radio"], #' + $(this).data('form') + ' input[type="checkbox"]:checked, #' + $(this).data('form') + ' select, #filtered-products input[type="checkbox"]:checked, #check-all input[type="checkbox"]:checked').add($('#filter .filter_option, #filter input[type="checkbox"]:checked')),
                dataType: 'json',
                success: function(json) {
                    productTotal = parseInt(json['total']);
                    $('.success, .warning, .attention, .information, .error').remove();
                    $('#mpchanges_box').before('<div class="' + json['message']['type'] + '" style="display: none;">' + json['message']['message'] + '</div>');
                    $('.' + json['message']['type']).fadeIn('slow');
                    $('#filtered-products').html('');
                    check_all = $('input', '#check-all').prop('checked') ? 'checked="checked"' : '';
                    html = productHTML(json['products'], check_all);
                    $('#filtered-products').append(html);
                },
                error: function() {
                    $.blockUI({message: 'Internal server error has occurred. Click to continue'});
                    $('.blockOverlay').attr('title','Click to continue').click($.unblockUI);
                }
            });
        })
        $('.vtabs a').tabs();
    });
</script>
<script type="text/javascript"><!--
var special_row = 1;
var discount_row = 1;
var productTotal = 0;
function addSpecials() {
    html  = '<tbody id="special-row' + special_row + '">';
    html += '  <tr>';
    html += '    <td class="left"><select name="product_special[' + special_row + '][customer_group_id]">';
    <?php foreach ($customer_groups as $customer_group) { ?>
        html += '      <option value="<?= $customer_group['customer_group_id'] ?>"><?= $customer_group['name'] ?></option>';
    <?php } ?>
    html += '    </select></td>';
    html += '    <td class="right"><input type="text" name="product_special[' + special_row + '][priority]" value="" size="2" /></td>';
    html += '    <td class="right"><select name="product_special[' + special_row + '][price_diff]"><option value="-">-</option><option value="+">+</option><option value="*">*</option><option value="/">/</option><option value="=">=</option></select>';
    html += '    <input type="text" name="product_special[' + special_row + '][price]" value="" /></td>';
    html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_start]" value="" class="date" /></td>';
    html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_end]" value="" class="date" /></td>';
    html += '    <td class="left"><a onclick="$(\'#special-row' + special_row + '\').remove();" class="button"><?= $button_remove ?></a></td>';
    html += '  </tr>';
    html += '</tbody>';

    $('#special').append(html);

    $('#special-row' + special_row + ' .date').datepicker({dateFormat: 'yy-mm-dd'});

    special_row++;
}

function addDiscounts() {
    html  = '<tbody id="discount-row' + discount_row + '">';
    html += '  <tr>';
    html += '    <td class="left"><select name="product_discount[' + discount_row + '][customer_group_id]">';
    <?php foreach ($customer_groups as $customer_group) { ?>
        html += '      <option value="<?= $customer_group['customer_group_id'] ?>"><?= $customer_group['name'] ?></option>';
    <?php } ?>
    html += '    </select></td>';
    html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][quantity]" value="" size="2" /></td>';
    html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][priority]" value="" size="2" /></td>';
    html += '    <td class="right"><select name="product_discount[' + discount_row + '][price_diff]"><option value="-">-</option><option value="+">+</option><option value="*">*</option><option value="/">/</option><option value="=">=</option></select>';
    html += '    <input type="text" name="product_discount[' + discount_row + '][price]" value="" /></td>';
    html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_start]" value="" class="date" /></td>';
    html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_end]" value="" class="date" /></td>';
    html += '    <td class="left"><a onclick="$(\'#discount-row' + discount_row + '\').remove();" class="button"><?= $button_remove ?></a></td>';
    html += '  </tr>';
    html += '</tbody>';

    $('#discount').append(html);

    $('#discount-row' + discount_row + ' .date').datepicker({dateFormat: 'yy-mm-dd'});

    discount_row++;
}

function productHTML(products, check_all){
    html= '';
    index = 0;
    for (var product in products){
        var discount = "&nbsp;";
        var special = "&nbsp;";

        if (products[product]['discount'] == '1'){
            discount = 'D';
        }

        if (products[product]['special'] == '1'){
            special = 'S';
        }

        rowType = (index % 2 == 0) ? 'odd' : 'even';
        html += '<div class="' + rowType + '" style="cursor:pointer;">';
        html += '<input type="checkbox" ' + check_all + ' name="product_to_change[]" value="'+products[product]['product_id']+'" />[<span style="width: 10px;">'+discount+'</span>][<span style="width: 10px;">'+special+'</span>] ' + products[product]['name'];
        html += '<span style="float:right; margin:0 3px; min-width: 25px;">';
        html += products[product]['quantity'];
        html += '</span>';

        html += '<span style="float:right; margin:0 3px; width: 100px;">';
        html += products[product]['price'];
        html += '</span>';
        html += '</div>';
        index++;
    }
    return html;
}

function load_products(start, limit){
    $.ajax({
        url: 'index.php?route=module/mpchanges/loadFilteredProducts&product_list=1&start=' + start + '&limit=' + limit + '&token=<?= $token ?>',
        type: 'post',
        data: $('#filter .filter_option, #filter input[type="checkbox"]:checked'),
        dataType: 'json',
        beforeSend: function() {
            $.blockUI({message: $('#wait')});
        },
        success: function(json) {
            productTotal = parseInt(json['total']);
            $('#product-total').html(json['total']);
            check_all = $('input', '#check-all').prop('checked') ? 'checked="checked"' : '';
            html = productHTML(json['products'], check_all);
            $('#filtered-products').append(html);
            $.unblockUI();
        },
        error: function() {
            $.blockUI({message: 'Internal server error has occurred. Click to continue'});
            $('.blockOverlay').attr('title','Click to continue').click($.unblockUI);
        }
    });
}

$('div', '#filtered-products').live('click', function(e){
    if (e.target.tagName != "INPUT"){
        $('input', $(this)).prop('checked', !$('input', $(this)).prop('checked'));
    }
});

$('#check-all').live('click', function(e){
    if (e.target.tagName != "INPUT"){
        $('input', $(this)).prop('checked', !$('input', $(this)).prop('checked'));
    }
    if ($('input', $(this)).prop('checked')){   $('input', '#filtered-products').prop('checked',true);
    }else{                                      $('input', '#filtered-products').prop('checked',false);}
});

start = 30;
limit = 30;

$('.filter_option, #filter input[type="checkbox"]').on('change', function(){
    if ($(this).attr('name') != 'customer_group' && $(this).attr('name') != 'filter_round') {
        $('#filtered-products').html('');
        start = 30;
        limit = 30;
        load_products(0, limit);
    }
});

load_products(0, limit);

$('#filtered-products').bind('scroll',function(){
    productCount = $('#filtered-products div').size();
    scrollTop = (productCount - 15) * 22;
    if ($(this).scrollTop() == scrollTop && start <= productTotal){
        load_products(start, limit);
        start += limit;
    }
})

$(document).ready(function() {
    $('.date').datepicker({dateFormat: 'yy-mm-dd'});
    reloadOptionValues($('select#options')[0]);
});
$('input[name="optionOperation"]').click(function() {
    if (this.value.indexOf('Value') != -1) {
        reloadOptionValues($('select#options')[0]);
        $('table#optionValuesSection').fadeIn();
    } else {
        $('table#optionValuesSection').fadeOut();
    }
});

function reloadOptionValues(sender) {
    $.ajax({
        url: '<?= str_replace('&amp;', '&', $urlGetOptionValues) ?>',
        type: 'post',
        dataType: 'json',
        data: {
            optionId: sender.value
        },
        success: function(json) {
            if ($.inArray(sender.selectedOptions[0].attributes['content'].value, ['select', 'radio']) > -1) {
                $('tr#singleValue').fadeOut();
                $('tr#multiValue').fadeIn();
                var selectOptions = $('select.optionValue');
                selectOptions.empty();
                for (var optionValueItem of json) {
                    selectOptions.append('<option value="' + optionValueItem.id + '">' + optionValueItem.text + '</option>');
                }
            } else if ($.inArray(sender.selectedOptions[0].attributes['content'].value, ['text', 'textarea']) > -1) {
                var optionValue = $('input.optionValue');
                optionValue.val(json);
                $('tr#multiValue').fadeOut();
                $('tr#singleValue').fadeIn();
            }
        },
        error: function() {
            $.blockUI({message: 'Internal server error has occurred. Click to continue'});
            $('.blockOverlay').attr('title','Click to continue').click($.unblockUI);
        }
    })
}

function setOptions() {
    var optionValue = $('.optionValue:visible');
    var intervalId;
    $.ajax({
        url: '<?= str_replace('&amp;', '&', $urlSetOption) ?>',
        type: 'post',
        dataType: 'json',
        data: $.merge(
            [
                {name: 'operation', value: $('input[name="optionOperation"]:checked').val()},
                {name: 'optionId', value: $('select#options').val()},
                {name: 'optionValue', value: optionValue.length ? optionValue.val() : ''},
                {name: 'optionValueType', value: optionValue.length ? (optionValue[0].nodeName == 'INPUT' ? 'single' : 'multi') : ''},
                {name: 'price', value: optionValue.length ? $('input#price').val() : ''},
                {name: 'weight', value: optionValue.length ? $('input#weight').val() : ''}
            ],
            $('.filter_option, input[name="change_all"], input[name="product_to_change\[\]"]').serializeArray()
        ),
        beforeSend: function() {
            intervalId = setInterval(function() {
                $.ajax({
                    url: '<?= str_replace('&amp;', '&', $urlGetProgress) ?>',
                    success: function(data) {
                        var wait = $('#wait');
                        var message = wait.find('#message');
                        message
                            .empty()
                            .append(data);
                        $.blockUI({message: wait});
                    }
                })
            }, 10000);
            $.blockUI({message: $('#wait')});
        },
        complete: function() {
            clearInterval(intervalId);
            $('#wait').find('#message').empty();
            $('.blockOverlay').attr('title','Click to continue').click($.unblockUI);

        },
        success: function() {
            $.blockUI({message: 'Option is set successfully. Click to continue'});
        },
        error: function() {
            $.blockUI({message: 'An error has occurred during option set. Click to continue'});
        }
    })
}
//--></script>
</div>
<?= $footer ?>