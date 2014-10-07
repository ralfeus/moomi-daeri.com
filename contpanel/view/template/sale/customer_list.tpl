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
      <h1><img src="view/image/customer.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('form').attr('action', '<?php echo $approve; ?>'); $('form').submit();" class="button"><?php echo $button_approve; ?></a><a onclick="location = '<?php echo $insert; ?>'" class="button"><?php echo $button_insert; ?></a><a onclick="$('form').attr('action', '<?php echo $delete; ?>'); $('form').submit();" class="button"><?php echo $button_delete; ?></a></div>
    </div>
    <div class="content">
      <form action="<?= $urlSelf ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
              <td class="left"><?= $textCustomerName ?></td>
              <td class="left"><?php if ($sort == 'c.email') { ?>
                <a href="<?php echo $sort_email; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_email; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_email; ?>"><?php echo $column_email; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'customer_group') { ?>
                <a href="<?php echo $sort_customer_group; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_customer_group; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_customer_group; ?>"><?php echo $column_customer_group; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'c.status') { ?>
                <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'c.approved') { ?>
                <a href="<?php echo $sort_approved; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_approved; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_approved; ?>"><?php echo $column_approved; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'c.ip') { ?>
                <a href="<?php echo $sort_ip; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_ip; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_ip; ?>"><?php echo $column_ip; ?></a>
                <?php } ?></td>
              <td class="left"><?php if ($sort == 'c.date_added') { ?>
                <a href="<?php echo $sort_date_added; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_added; ?></a>
                <?php } else { ?>
                <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?></a>
                <?php } ?></td>
              <td class="right"><?php echo $column_action; ?></td>
            </tr>
          </thead>
          <tbody>
            <tr class="filter">
              <td></td>
              <td>
                  <select name="filterCustomerId[]" multiple="true">
                      <?php foreach ($customersToFilterBy as $key => $value):
                      $selected = in_array($key, $filterCustomerId) ? 'selected' : ''; ?>
                      <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                      <?php endforeach; ?>
                  </select>
              </td>
              <td><input type="text" name="filterEmail" value="<?= $filterEmail ?>" /></td>
              <td><select name="filterCustomerGroupId">
                  <option />
                  <?php foreach ($customer_groups as $customer_group) { ?>
                  <?php if ($customer_group['customer_group_id'] == $filterCustomerGroupId) { ?>
                  <option value="<?php echo $customer_group['customer_group_id']; ?>" selected="selected"><?php echo $customer_group['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $customer_group['customer_group_id']; ?>"><?php echo $customer_group['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select></td>
              <td><select name="filterStatus">
                    <option />
                    <option value="1" "<?= $filterStatus ? 'selected' : '' ?>"><?= $text_enabled ?></option>
                    <option value="0" "<?= (!is_null($filterStatus) && !$filterStatus) ? 'selected' : '' ?>"><?= $text_disabled ?></option>
                </select></td>
              <td><select name="filterApproved">
                  <option />
                  <?php if ($filterApproved) { ?>
                  <option value="1" selected="selected"><?php echo $text_yes; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_yes; ?></option>
                  <?php } ?>
                  <?php if (!is_null($filterApproved) && !$filterApproved) { ?>
                  <option value="0" selected="selected"><?php echo $text_no; ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_no; ?></option>
                  <?php } ?>
                </select></td>
              <td><input type="text" name="filterIp" value="<?= $filterIp ?>" /></td>
              <td><input type="text" name="filterDateAdded" value="<?= $filterDateAdded ?>" size="12" id="date" /></td>
              <td align="right"><a onclick="filter();" class="button"><?php echo $button_filter; ?></a></td>
            </tr>
<?php if ($customers):
    foreach ($customers as $customer): ?>
            <tr <?= $customer['highlighted'] ? 'class="highlight"' : '' ?>>
              <td style="text-align: center;">
                <input type="checkbox" name="selected[]" value="<?= $customer['customer_id'] ?>" <?= $customer['selected'] ? 'checked="checked"' : '' ?>/>
              </td>
              <td class="left">
                  <?= $customer['name'] ?><br />
                  <?= $customer['nickname'] ?>
              </td>
              <td class="left"><?php echo $customer['email']; ?></td>
              <td class="left"><?php echo $customer['customer_group']; ?></td>
              <td class="left"><?php echo $customer['status']; ?></td>
              <td class="left"><?php echo $customer['approved']; ?></td>
              <td class="left"><?php echo $customer['ip']; ?></td>
              <td class="left"><?php echo $customer['date_added']; ?></td>
              <td class="right">
        <?php foreach ($customer['action'] as $action): ?>
                [&nbsp;<a
                    <?= !empty($action['href']) ? 'href="' . $action['href'] . '"' : '' ?>
                    <?= !empty($action['onclick']) ? 'onclick="' . $action['onclick'] . '"' : '' ?>><?= str_replace(' ', '&nbsp;', $action['text']) ?></a>&nbsp;]
        <?php endforeach; ?>
                  <select
                          id="stores<?= $customer['customer_id'] ?>"
                          style="display: none"
                          onchange="((this.value !== '') ? storeLogon(<?= $customer['customer_id'] ?>, this.value) : null); this.value = '';">
                      <option value=""><?= $text_select ?></option>
                      <option value="0"><?= $text_default ?></option>
            <?php foreach ($stores as $store): ?>
                      <option value="<?= $store['store_id'] ?>"><?= $store['name'] ?></option>
            <?php endforeach; ?>
                  </select>
              </td>
            </tr>
    <?php endforeach;
else: ?>
            <tr>
              <td class="center" colspan="9"><?php echo $text_no_results; ?></td>
            </tr>
<?php endif; ?>
          </tbody>
        </table>
      </form>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
    $('.date').datepicker({dateFormat: 'yy-mm-dd'});
    $("#filter_status_id\\[\\]").multiselect({
        noneSelectedText: "-- No filter --",
        selectedList: 3
    });
    $('[name=filterCustomerId\\[\\]]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();
    $('button.ui-multiselect').css('width', '250px');
});

function filter() {
    $('#form').submit();
}

function ajaxAction(sender, url)
{
    var senderObject = sender;
    $.ajax({
        url: url,
        dataType: 'json',
        beforeSend: function() {
            $('.success, .error').remove();
            $(sender).after('<span id="wait"><img src="view/image/loading.gif" alt="" /></span>');
        },
        complete: function() {
            $('#wait').remove();
        },
        success: function(json) {
            if (json['success'])
                $('.breadcrumb').after('<div class="success">' + json['message'] + '</div>');
            if (json['content'])
            {
                $('body').append('<div id="popup">' + json['content'] + '</div>');
                $('#popup').dialog({
                    height: 600,
                    modal: true,
                    width: 800
                });
            }
        },
        error: function()
        {
            $('.breadcrumb').after('<div class="error">Error</div>');
        }
    });
}

function showStores(sender, storesSelector) {
    $(sender).replaceWith($(storesSelector));
    $(storesSelector).show();
}

function storeLogon(customerId, storeId) {
    window.open('<?= $urlCustomerLogin ?>&customerId='.replace('&amp;', '&') + customerId + '&storeId=' + storeId);
}
//--></script>
<?php echo $footer; ?>