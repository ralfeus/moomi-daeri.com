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
    <h1><img src="view/image/module.png" alt="" /> <?php echo $heading_title; ?></h1>
    <div class="buttons"><a onclick="$('form').submit();" class="button"><?php echo $button_delete; ?></a></div></div>
  </div>
  <div class="content">
     <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
            <td width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
              <td class="left"><?php echo $entry_prod ?></td>
              <td class="left"><?php echo $entry_cus; ?></td>
                  
              <td class="left"><?php echo $entry_amt; ?></td>
              <td class="left"><?php echo $entry_dat; ?></td>
              <td class="left"><?php echo $entry_start; ?></td>
              <td class="left"><?php echo $entry_end; ?></td>
               <td class="center"><?php echo $entry_winner; ?></td>
                <td class="center"><?php echo $entry_sold; ?></td>
              
             
            </tr>
          </thead>
          <tbody>

            <?php if ($bids) { ?>
            <?php foreach ($bids as $bid) { ?>
            <tr>
             
            <td style="text-align: center;"><?php if ($bid['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $bid['id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $bid['id']; ?>" />
                <?php } ?></td>
              <td class="left"><?php echo $bid['product']; ?></td>
              <td class="left"><?php echo $bid['customer']; ?></td>
              <td class="left"><?php echo $bid['amount']; ?></td>
               <td class="left"><?php echo $bid['date']; ?></td>
              
               <td class="left"><?php echo $bid['auction_start']; ?></td>
              
               <td class="left"><?php echo $bid['auction_end']; ?></td>
               <td style="text-align: center;"><?php if ($bid['winner']=='1') { ?>
                <input type="checkbox"  value="1" checked="checked" />
                <?php } else { ?>
                <input type="checkbox"  value="0"/>
                <?php } ?>
              </td>
              <td style="text-align: center;"><?php if ($bid['sold']=='1') { ?>
                <input type="checkbox" value="1" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" value="0"/>
                <?php } ?>
              </td>
            </tr>
            <?php } } else { ?>
            <tr>
              <td class="center" colspan="8"><?php echo "no records founds"; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
       
  </div>
</div>



<?php echo $footer; ?>
