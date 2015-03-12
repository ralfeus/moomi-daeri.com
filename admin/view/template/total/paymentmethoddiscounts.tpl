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
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/total.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
		<table class="form">
          
         
          <tr>
            <td><?php echo $entry_sort_order; ?></td>
            <td><input type="text" name="paymentmethoddiscounts_sort_order" value="<?php echo $paymentmethoddiscounts_sort_order; ?>" size="1" /></td>
          </tr>
		   <tr>
		   <td><?php echo $entry_status; ?></td>
		  <td><select name="paymentmethoddiscounts_status">
                <?php if ($paymentmethoddiscounts_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select></td>
			</tr>  
       </table>
        <table id="discounts" class="list">
          <thead>
            <tr>
              <td class="left"><?php echo $entry_payment; ?></td>
              <td class="left"><?php echo $entry_tax_class; ?></td>
              <td class="left"><?php echo $entry_skidka; ?></td>			  
              <td></td>
            </tr>
          </thead>
          <?php $discount_row = 0; ?>
          <?php foreach ($paymentmethoddiscounts_discount as $discount) { ?>
          <tbody id="discount-row<?php echo $discount_row; ?>">
            <tr>
              <td class="left">
               <select name="paymentmethoddiscounts_discount[<?php echo $discount_row; ?>][paymentmethod]">
                  <?php foreach ($extensions as $extension) { ?>
                  <?php if ($extension['code'] == $discount['paymentmethod']) { ?>
                  <option value="<?php echo $extension['code']; ?>" selected="selected"><?php echo $extension['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $extension['code']; ?>"><?php echo $extension['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
                </td>
              <td class="left">
			  <select name="paymentmethoddiscounts_discount[<?php echo $discount_row; ?>][tax_class_id]">
                  <option value="0"><?php echo $text_none; ?></option>
                  <?php foreach ($tax_classes as $tax_class) { ?>
                  <?php if ($tax_class['tax_class_id'] == $discount['tax_class_id']) { ?>
                  <option value="<?php echo $tax_class['tax_class_id']; ?>" selected="selected"><?php echo $tax_class['title']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
			  </td>
              <td class="left">
				
			   <select name="paymentmethoddiscounts_discount[<?php echo $discount_row; ?>][znak]">
                <?php if ($discount['znak']) { ?>
                <option value="0" >+</option>
                <option value="1" selected="selected">-</option>
                <?php } else { ?>
                <option value="0" selected="selected">+</option>
                <option value="1" >-</option>
                <?php } ?>
              </select>
			   &nbsp;
			  <input
                      name="paymentmethoddiscounts_discount[<?= $discount_row ?>][number]"
                      value="<?= $discount['number'] ?>"
                      style="width: 50px" />
			   &nbsp;
			   <select name="paymentmethoddiscounts_discount[<?php echo $discount_row; ?>][mode]">
                <?php if ($discount['mode']) { ?>
                <option value="0"><?php echo $text_ed; ?></option>
                <option value="1" selected="selected"><?php echo $text_percent; ?></option>
                <?php } else { ?>
                <option value="0" selected="selected"><?php echo $text_ed; ?></option>
                <option value="1"><?php echo $text_percent; ?></option>
                <?php } ?>
              </select>
			  </td>
              <td class="left"><a onclick="$('#discount-row<?php echo $discount_row; ?>').remove();" class="button"><?php echo $button_remove; ?></a></td>
            </tr>
          </tbody>
          <?php $discount_row++; ?>
          <?php } ?>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td class="left"><a onclick="addDiscount();" class="button"><?php echo $button_add_discount; ?></a></td>
            </tr>
          </tfoot>
        </table>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
var discount_row = <?php echo $discount_row; ?>;

function addDiscount() {
    html  = '<tbody id="discount-row' + discount_row + '">';
    html += '<tr>';
    html += '<td class="left">';
    html += '<select name="paymentmethoddiscounts_discount[' + discount_row + '][paymentmethod]">';
    <?php foreach ($extensions as $extension) { ?>
    html += '<option value="<?php echo $extension['code']; ?>"><?php echo $extension['name']; ?></option>';
	<?php } ?>
    html += '</select>';
    html += '</td>';
    html += '<td class="left">';
	html += '<select name="paymentmethoddiscounts_discount[' + discount_row + '][tax_class_id]">';
    html += '<option value="0"><?php echo $text_none; ?></option>';
    <?php foreach ($tax_classes as $tax_class) { ?>
    html += '<option value="<?php echo $tax_class['tax_class_id']; ?>"><?php echo $tax_class['title']; ?></option>';
    <?php } ?>
    html += '</select>';
	html += '</td>';
    html += '<td class="left">';
	html += '<select name="paymentmethoddiscounts_discount[' + discount_row + '][znak]">';
    html += '<option value="0" selected="selected">+</option>';
    html += '<option value="1" >-</option>';
    html += '</select>';
	html += '&nbsp;';
	html += '<input name="paymentmethoddiscounts_discount[' + discount_row + '][number]" value="" />';
	html += '&nbsp;';
	html += '<select name="paymentmethoddiscounts_discount[' + discount_row + '][mode]">';
    html += '<option value="0" selected="selected"><?php echo $text_ed; ?></option>';
    html += '<option value="1"><?php echo $text_percent; ?></option>';
    html += '</select>';
	html += '</td>';
    html += '<td><select name="paymentmethoddiscounts_discount[' + discount_row + '][status]">';
    html += '<option value="1"><?php echo $text_enabled; ?></option>';
    html += '<option value="0" selected="selected"><?php echo $text_disabled; ?></option>';
    html += '</select></td>';
	html += '<td class="left"><a onclick="$(\'#discount-row' + discount_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
    html += '</tr>';
    html += '</tbody>'; 
	
	$('#discounts tfoot').before(html);
	
	discount_row++;
}
//--></script>
<?php echo $footer; ?> 