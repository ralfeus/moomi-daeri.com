<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <?php if ($error_warning) { ?>
  <div class="error"><?php echo $error_warning; ?></div>
  <?php } ?>
<!-- KBA -->
    <?php if($this->request->get['token'] == 1) {?>
	<h2><?php echo $text_edit_your_acc_plz; ?></h2>
	<script type="text/javascript"><!--
	    $(document).ready(function(){
		alert('<?php echo $text_edit_your_acc_plz; ?>');
	    });
	//--></script>
    <?php } ?>
<!-- /KBA -->
    
  <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="edit">
    <h2><?php echo $text_your_details; ?></h2>
    <div class="content">
      <table class="form">
        <tr>
          <td><span class="required">*</span> <?php echo $entry_firstname; ?></td>
          <td><input type="text" name="firstname" value="<?php echo $firstname; ?>" />
            <?php if ($error_firstname) { ?>
            <span class="error"><?php echo $error_firstname; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_lastname; ?></td>
          <td><input type="text" name="lastname" value="<?php echo $lastname; ?>" />
            <?php if ($error_lastname) { ?>
            <span class="error"><?php echo $error_lastname; ?></span>
            <?php } ?></td>
        </tr>
          <tr>
              <td><span class="required">*</span> <?php echo $entry_nickname; ?></td>
              <td><input type="text" name="nickname" value="<?php echo $nickname; ?>" />
                  <?php if ($error_nickname) { ?>
                  <span class="error"><?php echo $error_nickname; ?></span>
                  <?php } ?></td>
          </tr>
          <tr>
          <td><span class="required">*</span> <?php echo $entry_email; ?></td>
          <td><input type="text" name="email" value="<?php echo $email; ?>" />
            <?php if ($error_email) { ?>
            <span class="error"><?php echo $error_email; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><span class="required">*</span> <?php echo $entry_telephone; ?></td>
          <td><input type="text" name="telephone" value="<?php echo $telephone; ?>" />
            <?php if ($error_telephone) { ?>
            <span class="error"><?php echo $error_telephone; ?></span>
            <?php } ?></td>
        </tr>
        <tr>
          <td><?php echo $entry_fax; ?></td>
          <td><input type="text" name="fax" value="<?php echo $fax; ?>" /></td>
        </tr>
      </table>
    </div>
    <h2><?= $textAccountDetails ?></h2>
    <div class="content">
        <table class="form">
            <tr>
                <td><span class="required">*</span> <?= $textBaseCurrency ?></td>
                <td>
                    <select name="baseCurrency">
                        <?php foreach ($currencies as $currency): ?>
                            <option value="<?= $currency['currencyCode'] ?>" <?= $currency['selected'] ?>><?= $currency['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
    </div>
    <div class="buttons">
      <div class="left"><a href="<?php echo $back; ?>" class="button"><span><?php echo $button_back; ?></span></a></div>
      <div class="right"><a onclick="$('#edit').submit();" class="button"><span><?php echo $button_continue; ?></span></a></div>
    </div>
  </form>
  <?php echo $content_bottom; ?></div>
    <?php if (isset($confirmationRequired) && $confirmationRequired): ?>
        <div id="confirmationDialog" title="Confirmation"><?= $confirmationRequestText ?></div>
        <script type="text/javascript"><!--
        $(document).ready(function(){
            $('#confirmationDialog').dialog({
                buttons: [{
                    "Yes": function() {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'confirm',
                            value: true
                        }).appendTo('#edit');
                        $('#edit').submit();
                    },
                    "No": function() { $(this).dialog('close'); }
                }]
            });
        });
        //--></script>
    <?php endif; ?>
<?php echo $footer; ?>
