<?php echo $header; ?>
<div id="content">
    <div class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
        <?php } ?>
    </div>

    <?php if ($error_warning) { ?>
        <div class="warning"><?php echo $error_warning; ?></div>
    <?php } ?>

    <div class="box" id="mpchanges_box">
        <div class="heading">
            <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
            <div class="buttons">
                <a onclick="location = '<?php echo $url_cancel; ?>';" class="button">
                    <?php echo $button_cancel; ?>
                </a>
            </div>
        </div>
        <div class="content">
            <table class="form" id="filter">
                <tr>
                    <td><?php echo $entry_store; ?></td>
                    <td>
                        <select name="store_id" class="filter_option">
                            <option value="0"><?php echo $store_default; ?></option>
                        </select>
                    </td>
                    <td rowspan="7">
                        <?php echo $text_filtered_products;?> <span id="product-total"></span>
                        <div class="scrollbox" id="check-all" style="height: 22px; width: 700px; background: #B7D7F5; cursor:pointer;"><input type="checkbox" checked="checked" name="change_all" value="true" /> Все товары</div>
                        <div class="scrollbox" id="filtered-products" style="height: 330px; width: 700px;"></div>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_manufacturer; ?></td>
                    <td>
                        <select name="manufacturer_id" class="filter_option">
                            <option value="0"><?php echo $option_all; ?></option>
                            <?php foreach ($manufacturers as $manufacturer) { ?>
                            <option value="<?php echo $manufacturer->getId(); ?>"><?php echo $manufacturer->getName(); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_name; ?></td>
                    <td>
                        <input type="text" name="name" value="" class="filter_option"/>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_model; ?></td>
                    <td>
                        <input type="text" name="model" value="" class="filter_option"/>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_category; ?></td>
                    <td>
                        <select name="category_id" class="filter_option">
                            <option value="0"><?php echo $option_all; ?></option>
                            <?php foreach ($categories as $category) { ?>
                            <option value="<?php echo $category['category_id']; ?>"><?php echo $category['name']; ?></option>
                            <?php } ?>
                        </select>
                        <input type="checkbox" id="filter_sub_category" name="filter_sub_category"/>
                        <label for="filter_sub_category"><?php echo $entry_subcategory; ?></label>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $entry_customer_group; ?></td>
                    <td>
                        <select name="customer_group" class="filter_option">
                            <option value="0"><?php echo $text_all; ?></option>
                            <?php foreach ($customer_groups as $customer_group) { ?>
                            <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><?php echo $label_price; ?></td>
                    <td>
                        <?php echo $label_price_from; ?>
                        <input type="text" size="4" id="filter_price_from" name="filter_price_from" class="filter_option"/>
                        <?php echo $label_price_to; ?>
                        <input type="text" size="4" id="filter_price_to" name="filter_price_to" class="filter_option"/>
                    </td>
                </tr>

                <tr>
                    <td><?php echo $label_round; ?></td>
                    <td>
                        <input type="text" size="3" id="filter_round" name="filter_round" value="0" class="filter_option"/><?php echo $label_round_decimal; ?>
                    </td>
                </tr>
            </table>
            <div class="vtabs">
                <a href="#prices">
                    <?php echo $tab_prices; ?>
                </a>
                <a href="#specials">
                    <?php echo $tab_specials; ?>
                </a>
                <a href="#add_specials">
                    <?php echo $tab_add_specials; ?>
                </a>
                <a href="#discounts" style="display: none;">
                    <?php echo $tab_discounts; ?>
                </a>
                <a href="#add_discounts" style="display: none;">
                    <?php echo $tab_add_discounts; ?>
                </a>
                <a href="#del_section" style="display: none;">
                    <?php echo $tab_del_section; ?>
                </a>
            </div>

            <div id="prices" class="vtabs-content">
                <form action="<?php echo $action_change_price; ?>" method="post" enctype="multipart/form-data" id="form_change_price">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_price; ?></td>
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
                                    <option value="percent"><?php echo $label_percent; ?></option>
                                    <option value="number"><?php echo $label_number; ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $entry_quantities; ?></td>
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
                                    <option value="number"><?php echo $label_number; ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div>
                                    <a class="button submit-form" data-form="form_change_price"><?php echo $button_change_price; ?></a>
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
                <form action="<?php echo $action_change_specials; ?>" method="post" enctype="multipart/form-data" id="form_change_specials">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_specials; ?></td>
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
                                    <option value="percent"><?php echo $label_percent; ?></option>
                                    <option value="number"><?php echo $label_number; ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div>
                                    <a class="button submit-form" data-form="form_change_specials"><?php echo $button_change_specials; ?></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="add_specials" class="vtabs-content">
                <form action="<?php echo $action_save_specials; ?>" method="post" enctype="multipart/form-data" id="form_save_specials">
                    <table class="form">
                        <tr>
                            <td colspan="3">
                                <table id="special" class="list">
                                    <thead>
                                    <tr>
                                        <td class="left"><?php echo $entry_customer_group; ?></td>
                                        <td class="right"><?php echo $entry_priority; ?></td>
                                        <td class="right"><?php echo $entry_price_diff; ?></td>
                                        <td class="left"><?php echo $entry_date_start; ?></td>
                                        <td class="left"><?php echo $entry_date_end; ?></td>
                                        <td>
                                            <div>
                                                <a class="button" onclick="addSpecials();"><?php echo $button_add_row; ?></a>
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
                                    <a class="button submit-form" data-form="form_save_specials"><?php echo $button_add_specials; ?></a>
                                </div>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>

            <div id="discounts" class="vtabs-content">
                <form action="<?php echo $action_change_discounts; ?>" method="post" enctype="multipart/form-data" id="form_change_discounts">
                    <table class="form">
                        <tr>
                            <td><?php echo $entry_discounts; ?></td>
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
                                    <option value="percent"><?php echo $label_percent; ?></option>
                                    <option value="number"><?php echo $label_number; ?></option>
                                </select>
                                <?php echo $label_quantity_prefix; ?>
                                <input type="text" name="discount_quantity" size="3" value="1"/>
                                <?php echo $label_quantity_postfix; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <div>
                                    <a class="button submit-form" data-form="form_change_discounts"><?php echo $button_change_discounts; ?></a>
                                </div>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>

            <div id="add_discounts" class="vtabs-content">
                <form action="<?php echo $action_save_discounts; ?>" method="post" enctype="multipart/form-data" id="form_save_discounts">
                    <table class="form">
                        <tr>
                            <td colspan="3">
                                <table id="discount" class="list">
                                    <thead>
                                    <tr>
                                        <td class="left"><?php echo $entry_customer_group; ?></td>
                                        <td class="right"><?php echo $entry_quantities; ?></td>
                                        <td class="right"><?php echo $entry_priority; ?></td>
                                        <td class="right"><?php echo $entry_price_diff; ?></td>
                                        <td class="left"><?php echo $entry_date_start; ?></td>
                                        <td class="left"><?php echo $entry_date_end; ?></td>
                                        <td>
                                            <div class="buttons">
                                                <a class="button" onclick="addDiscounts();"><?php echo $button_add_row; ?></a>
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
                                    <a class="button submit-form" data-form="form_save_discounts"><?php echo $button_add_discounts; ?></a>
                                </div>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>
            <div id="del_section" class="vtabs-content">
                <form action="<?php echo $action_del_elements; ?>" method="post" enctype="multipart/form-data" id="form_del_elements">
                    <table class="form">
                        <tr>
                            <td>
                                <input type="checkbox" name="del_product" id="del_product" /><label for="del_product"><?php echo $text_del_product; ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="del_special" id="del_special" /><label for="del_special"><?php echo $text_del_special; ?></label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="checkbox" name="del_discount" id="del_discount" /><label for="del_discount"><?php echo $text_del_discount; ?></label>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <div>
                                    <a class="button submit-form" data-form="form_del_elements"><?php echo $button_del_elements; ?></a>
                                </div>
                            </td>
                        </tr>

                    </table>
                </form>
            </div>
        </div>
    </div>
    <style>
        #filtered-products div:hover{background: lightgrey;}
        #filtered-products div{height:16px;}
    </style>

    <script>
        $(function(){
            $(".submit-form").live('click', function(){
                $.ajax({
                    url: $('#' + $(this).data('form')).attr('action'),
                    type: 'post',
                    data: $('#' + $(this).data('form') + ' input[type="text"], #' + $(this).data('form') + ' input[type="radio"], #' + $(this).data('form') + ' input[type="checkbox"]:checked, #' + $(this).data('form') + ' select, #filtered-products input[type="checkbox"]:checked, #check-all input[type="checkbox"]:checked').add($('#filter .filter_option, #filter input[type="checkbox"]:checked')),
                    dataType: 'json',
                    beforeSend: function() {
                        $('body').append(
                            '<div class="wait" style="position: absolute; top: 50%; left: 50%">' +
                            '   <img src="<?= HTTP_IMAGE ?>/ajax-loader.gif" alt="" />' +
                            '</div>'
                        );
                    },
                    complete: function() {
                        $('.wait').remove();
                    },
                    success: function(json) {
                        productTotal = parseInt(json['total']);
                        $('.success, .warning, .attention, .information, .error').remove();
                        $('#mpchanges_box').before('<div class="' + json['message']['type'] + '" style="display: none;">' + json['message']['message'] + '</div>');
                        $('.' + json['message']['type']).fadeIn('slow');
                        $('#filtered-products').html('');
                        check_all = $('input', '#check-all').prop('checked') ? 'checked="checked"' : '';
                        html = productHTML(json['products'], check_all);
                        $('#filtered-products').append(html);
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
            html += '      <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>';
        <?php } ?>
        html += '    </select></td>';
        html += '    <td class="right"><input type="text" name="product_special[' + special_row + '][priority]" value="" size="2" /></td>';
        html += '    <td class="right"><select name="product_special[' + special_row + '][price_diff]"><option value="-">-</option><option value="+">+</option><option value="*">*</option><option value="/">/</option><option value="=">=</option></select>';
        html += '    <input type="text" name="product_special[' + special_row + '][price]" value="" /></td>';
        html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_start]" value="" class="date" /></td>';
        html += '    <td class="left"><input type="text" name="product_special[' + special_row + '][date_end]" value="" class="date" /></td>';
        html += '    <td class="left"><a onclick="$(\'#special-row' + special_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
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
            html += '      <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>';
        <?php } ?>
        html += '    </select></td>';
        html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][quantity]" value="" size="2" /></td>';
        html += '    <td class="right"><input type="text" name="product_discount[' + discount_row + '][priority]" value="" size="2" /></td>';
        html += '    <td class="right"><select name="product_discount[' + discount_row + '][price_diff]"><option value="-">-</option><option value="+">+</option><option value="*">*</option><option value="/">/</option><option value="=">=</option></select>';
        html += '    <input type="text" name="product_discount[' + discount_row + '][price]" value="" /></td>';
        html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_start]" value="" class="date" /></td>';
        html += '    <td class="left"><input type="text" name="product_discount[' + discount_row + '][date_end]" value="" class="date" /></td>';
        html += '    <td class="left"><a onclick="$(\'#discount-row' + discount_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
        html += '  </tr>';
        html += '</tbody>';

        $('#discount').append(html);

        $('#discount-row' + discount_row + ' .date').datepicker({dateFormat: 'yy-mm-dd'});

        discount_row++;
    }

    function productHTML(products, check_all){
        html= '';
        index = 0;
        for (product in products){
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
            url: 'index.php?route=module/mpchanges/loadFilteredProducts&product_list=1&start=' + start + '&limit=' + limit + '&token=<?php echo $token; ?>',
            type: 'post',
            data: $('#filter .filter_option, #filter input[type="checkbox"]:checked'),
            dataType: 'json',
            success: function(json) {
                productTotal = parseInt(json['total']);
                $('#product-total').html(json['total']);
                check_all = $('input', '#check-all').prop('checked') ? 'checked="checked"' : '';
                html = productHTML(json['products'], check_all);
                $('#filtered-products').append(html);
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
    //--></script>
</div>
<?php echo $footer; ?>