<?php echo $header; ?>
<link rel="stylesheet" href="view/javascript/bonusloto/css/style.css"/>
<script type="text/javascript" src="view/javascript/bonusloto/color_picker/js/colorpicker.js"></script>
<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-timepicker-addon.js"></script>
<link rel="stylesheet" href="view/javascript/bonusloto/color_picker/css/colorpicker.css"/>
<link rel="stylesheet" href="view/javascript/bonusloto/chekboks/css/style.css"/>
<style type="text/css">
.ext-view-off { display: none; }
table.form > tbody > tr > td:first-child {width: 120px;}
</style>
<script type="text/javascript">
function toggleView(tableId, tag, classgame) {
    var elems = $("#" + tableId + " " + tag );
    elems.toggleClass("ext-view-off",true);
    var elems = $("#" + tableId + " " + tag + "." + classgame);
    elems.toggleClass("ext-view-off",false);
}
</script>
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
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
<!-- tabs -->
<div id="tabs" class="htabs">
        <a  href="#tab_general"><?php echo $tab_general; ?></a>
        <a  href="#tab_graph"><?php echo $tab_graph; ?></a>
        <a  href="#tab_data"><?php echo $tab_data; ?></a>
        <a  href="#tab_about"><?php echo $tab_about; ?></a>
</div>
<!-- /tabs -->
<!-- bonusloto -->
<!-- tab_general -->
    <div id="tab_general">
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
      <table class="form">
        <tr>
          <td><?php echo $entry_bonusloto_start_lototron; ?></td>
              <?php if ($bonusloto_start_lototron != '1') { ?>
	  <td width="100px"><div style="background-color: rgb(75, 248, 75);width:100px;height: 50px; float: left;"></div><input type="hidden" name="bonusloto_start_lototron" value="0" />
              <?php } else { ?>
	  <td width="100px"><div style="background-color: rgb(250, 24, 24);width:100px;height: 50px; float: left;"></div><input type="hidden" name="bonusloto_start_lototron" value="0" />
              <?php } ?>
	  <a id="stoplototron" onclick="stoplototron();" class="button" style="margin: 10px;"><?php echo $button_stop_lototron; ?></a><div id="result_lototron"></div></td>
        </tr>
        <tr>
          <td><?php echo $entry_bonusloto_timezone; ?></td>
	  <td>
		<select  name="bonusloto_timezone">
	        <?php foreach (timezone_identifiers_list() as $timezone) { ?>
		<?php if ($bonusloto_timezone == $timezone ){ ?>
                	<option value="<?php echo $timezone; ?>" selected="selected"><?php echo $timezone; ?></option>
		<?php } else { ?>
                	<option value="<?php echo $timezone; ?>" ><?php echo $timezone; ?></option>
		<?php } ?>
		<?php } ?>
		</select>
	   </td>
        </tr>
        <tr>
          <td><?php echo $entry_bonusloto_game; ?></td>
          <td style="float:left">
<!-- tabs games -->
<div id="games" class="htabs">
        <a  onClick="toggleView('game', 'td','ext-view-game-data')" ><?php echo $tab_games_time; ?></a>
        <a  onClick="toggleView('game', 'td','ext-view-game-type')" ><?php echo $tab_games_type; ?></a>
        <a  onClick="toggleView('game', 'td','ext-view-game-req')" ><?php echo $tab_games_req; ?></a>
        <a  onClick="toggleView('game', 'td','ext-view-game-vip')" ><?php echo $tab_games_vip; ?></a>
</div>
<!-- /tabs games -->
        <table id="game" class="list">
          <thead>
            <tr>
              <td class="left ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip">№</td>
              <td class="left ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip"><?php echo $entry_game_status; ?></td>
              <td class="left ext-view-game-data "><span class="required">*</span> <?php echo $entry_game_data; ?></td>
              <td class="left ext-view-game-data "><span class="required">*</span> <?php echo $entry_game_time; ?></td>
              <td class="left ext-view-game-type"><span class="required">*</span> <?php echo $entry_game_type; ?></td>
              <td class="left ext-view-game-type"><span class="required">*</span> <?php echo $entry_game_prize; ?></td>
              <td class="left ext-view-game-type"><span class="required">*</span> <?php echo $entry_game_code; ?></td>
              <td class="left ext-view-game-req"><span class="required">*</span> <?php echo $entry_game_requir; ?></td>
              <td class="left ext-view-game-req"><span class="required">*</span> <?php echo $entry_game_requir_val; ?></td>
              <td class="left ext-view-game-vip"><span class="required"></span> <?php echo $entry_game_status_vip; ?></td>
              <td class="left ext-view-game-vip"><span class="required"></span> <?php echo $entry_game_vip_count; ?></td>
              <td class="left ext-view-game-vip"><span class="required"></span> <?php echo $entry_game_vip_val; ?></td>
              <td class="left ext-view-game-vip"><span class="required"></span> <?php echo $entry_game_vip_product; ?></td>
              <td class="left ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip"><?php echo $entry_game_do; ?></td>
            </tr>
          </thead>
          <?php $game_row = 0; ?>
          <?php foreach ($games as $game) { ?>
          <tbody id="game-row<?php echo $game_row; ?>">
	    <tr>
	      <td class="right ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip"><?php echo $game_row; ?></td>
              <td class="left ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip">
                  <?php if (isset($game['status']) and ($game['status'] =='on')) { ?>
				<div class="switch checkbox1"><input name="bonusloto_game[<?php echo $game_row; ?>][status]" type="checkbox" checked><label><i></i></label></div>		
                  <?php } else { ?>
				<div class="switch checkbox1"><input name="bonusloto_game[<?php echo $game_row; ?>][status]" type="checkbox"><label><i></i></label></div>
                  <?php } ?>
              </td>
              <td class="right ext-view-game-data"><input class="date" type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_data]" value="<?php if (isset($game['game_data'])) echo $game['game_data']; ?>" size="10" /></td>
              <td class="right ext-view-game-data"><input class="time" type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_time]" value="<?php if (isset($game['game_time'])) echo $game['game_time']; ?>" size="10" /></td>
              <td class="right ext-view-game-type">
		<select onload="changeStyle(this.value,<?php echo $game_row; ?>)" onchange="changeStyle(this.value,<?php echo $game_row; ?>)" name="bonusloto_game[<?php echo $game_row; ?>][game_type]">
                  <?php if ($game['game_type'] == '1' ){ ?>
                  <option value="1" selected="selected"><?php echo $text_game_type_cupon; ?></option>
                  <option value="2" ><?php echo $text_game_type_point; ?></option>
                  <option value="3" ><?php echo $text_game_type_product; ?></option>
                  <option value="4" ><?php echo $text_game_type_other; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } elseif ($game['game_type'] == '2' ) { ?>
                  <option value="2" selected="selected"><?php echo $text_game_type_point; ?></option>
                  <option value="1" ><?php echo $text_game_type_cupon; ?></option>
                  <option value="3" ><?php echo $text_game_type_product; ?></option>
                  <option value="4" ><?php echo $text_game_type_other; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } elseif ($game['game_type'] == '3' ) { ?>
                  <option value="3" selected="selected"><?php echo $text_game_type_product; ?></option>
                  <option value="1" ><?php echo $text_game_type_cupon; ?></option>
                  <option value="2" ><?php echo $text_game_type_point; ?></option>
                  <option value="4" ><?php echo $text_game_type_other; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } elseif ($game['game_type'] == '4' ) { ?>
                  <option value="4" selected="selected"><?php echo $text_game_type_other; ?></option>
                  <option value="1" ><?php echo $text_game_type_cupon; ?></option>
                  <option value="2" ><?php echo $text_game_type_point; ?></option>
                  <option value="3" ><?php echo $text_game_type_product; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <option value="1" ><?php echo $text_game_type_cupon; ?></option>
                  <option value="2" ><?php echo $text_game_type_point; ?></option>
                  <option value="3" ><?php echo $text_game_type_product; ?></option>
                  <option value="4" ><?php echo $text_game_type_other; ?></option>
                  <?php } ?>
                </select>
	      </td>
                  <?php if ($game['game_type'] == '1' ){ ?>
              <td class="right ext-view-game-type">
		<input class="coupon_name <?php echo $game_row; ?>" type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_name]" value="<?php if (isset($game['game_prize_name'])) echo $game['game_prize_name']; ?>" size="20" />
		<input class="coupon_id <?php echo $game_row; ?>" type="hidden" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_id]" value="<?php if (isset($game['game_prize_id'])) echo $game['game_prize_id']; ?>" />
	      </td>
              <td class="right ext-view-game-type"><input type="text" class="coupon_code <?php echo $game_row; ?>" name="bonusloto_game[<?php echo $game_row; ?>][game_code]" value="<?php if (isset($game['game_code'])) echo $game['game_code']; ?>" size="15" /></td>
                  <?php } elseif ($game['game_type'] == '2' ) { ?>
              <td class="right ext-view-game-type">
		<input class="point_name <?php echo $game_row; ?>" type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_name]" value="<?php if (isset($game['game_prize_name'])) echo $game['game_prize_name']; ?>" size="20" />
		<input class="point_id <?php echo $game_row; ?>" type="hidden" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_id]" value="<?php if (isset($game['game_prize_id'])) echo $game['game_prize_id']; ?>" />
	      </td>
              <td class="right ext-view-game-type"><input type="text" class="point_code <?php echo $game_row; ?>" name="bonusloto_game[<?php echo $game_row; ?>][game_code]" value="<?php if (isset($game['game_code'])) echo $game['game_code']; ?>" size="15" /></td>
                  <?php } elseif ($game['game_type'] == '3' ) { ?>
              <td class="right ext-view-game-type">
		<input class="product_name <?php echo $game_row; ?>" type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_name]" value="<?php if (isset($game['game_prize_name'])) echo $game['game_prize_name']; ?>" size="20" />
		<input class="product_id <?php echo $game_row; ?>" type="hidden" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_id]" value="<?php if (isset($game['game_prize_id'])) echo $game['game_prize_id']; ?>" />
	      </td>
              <td class="right ext-view-game-type"><input type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_code]" value="<?php if (isset($game['game_code'])) echo $game['game_code']; ?>" size="15" /></td>
                  <?php } else { ?>
              <td class="right ext-view-game-type">
		<input class="<?php echo $game_row; ?>" type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_name]" value="<?php if (isset($game['game_prize_name'])) echo $game['game_prize_name']; ?>" size="20" />
		<input class="<?php echo $game_row; ?>" type="hidden" name="bonusloto_game[<?php echo $game_row; ?>][game_prize_id]" value="<?php if (isset($game['game_prize_id'])) echo $game['game_prize_id']; ?>" />
	      </td>
              <td class="right ext-view-game-type"><input type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_code]" value="<?php if (isset($game['game_code'])) echo $game['game_code']; ?>" size="15" /></td>
                  <?php } ?>
              <td class="right ext-view-game-req">
		<select  name="bonusloto_game[<?php echo $game_row; ?>][game_requir]">
                  <?php if ($game['game_requir'] == '1' ){ ?>
                  <option value="1" selected="selected"><?php echo $text_game_requir_cash; ?></option>
                  <option value="2" ><?php echo $text_game_requir_point; ?></option>
                  <option value="3" ><?php echo $text_game_requir_product; ?></option>
                  <option value="4" ><?php echo $text_game_requir_post; ?></option>
                  <option value="5" ><?php echo $text_game_requir_point_buy; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } elseif ($game['game_requir'] == '2' ) { ?>
                  <option value="2" selected="selected"><?php echo $text_game_requir_point; ?></option>
                  <option value="1" ><?php echo $text_game_requir_cash; ?></option>
                  <option value="3" ><?php echo $text_game_requir_product; ?></option>
                  <option value="4" ><?php echo $text_game_requir_post; ?></option>
                  <option value="5" ><?php echo $text_game_requir_point_buy; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } elseif ($game['game_requir'] == '3' ) { ?>
                  <option value="3" selected="selected"><?php echo $text_game_requir_product; ?></option>
                  <option value="1" ><?php echo $text_game_requir_cash; ?></option>
                  <option value="2" ><?php echo $text_game_requir_point; ?></option>
                  <option value="4" ><?php echo $text_game_requir_post; ?></option>
                  <option value="5" ><?php echo $text_game_requir_point_buy; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } elseif ($game['game_requir'] == '4' ) { ?>
                  <option value="4" selected="selected"><?php echo $text_game_requir_post; ?></option>
                  <option value="1" ><?php echo $text_game_requir_cash; ?></option>
                  <option value="2" ><?php echo $text_game_requir_point; ?></option>
                  <option value="3" ><?php echo $text_game_requir_product; ?></option>
                  <option value="5" ><?php echo $text_game_requir_point_buy; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } elseif ($game['game_requir'] == '5' ) { ?>
                  <option value="5" selected="selected"><?php echo $text_game_requir_point_buy; ?></option>
                  <option value="1" ><?php echo $text_game_requir_cash; ?></option>
                  <option value="2" ><?php echo $text_game_requir_point; ?></option>
                  <option value="3" ><?php echo $text_game_requir_product; ?></option>
                  <option value="4" ><?php echo $text_game_requir_post; ?></option>
                  <option value="0" ><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <option value="1" ><?php echo $text_game_requir_cash; ?></option>
                  <option value="2" ><?php echo $text_game_requir_point; ?></option>
                  <option value="3" ><?php echo $text_game_requir_product; ?></option>
                  <option value="4" ><?php echo $text_game_requir_post; ?></option>
                  <option value="5" ><?php echo $text_game_requir_point_buy; ?></option>
                  <?php } ?>
                </select>
	      </td>
              <td class="right ext-view-game-req">
		<input type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_requir_val]" value="<?php if (isset($game['game_requir_val'])) echo $game['game_requir_val']; ?>" size="15" />
	      </td>
              <td class="left ext-view-game-vip">
                  <?php if (isset($game['game_status_vip']) and ($game['game_status_vip'] =='on')) { ?>
				<div class="switch checkbox1"><input name="bonusloto_game[<?php echo $game_row; ?>][game_status_vip]" type="checkbox" checked><label><i></i></label></div>		
                  <?php } else { ?>
				<div class="switch checkbox1"><input name="bonusloto_game[<?php echo $game_row; ?>][game_status_vip]" type="checkbox"><label><i></i></label></div>
                  <?php } ?>
              </td>
              <td class="right ext-view-game-vip">
		<input type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_vip_count]" value="<?php if (isset($game['game_vip_count'])) echo $game['game_vip_count']; ?>" size="10" />
	      </td>
              <td class="right ext-view-game-vip">
		<input type="text" name="bonusloto_game[<?php echo $game_row; ?>][game_vip_buy_val]" value="<?php if (isset($game['game_vip_buy_val'])) echo $game['game_vip_buy_val']; ?>" size="10" />
		<input type="hidden" name="bonusloto_game[<?php echo $game_row; ?>][game_vip_id]" value="<?php if (isset($game['game_vip_id'])) echo $game['game_vip_id']; ?>" />
	      </td>
              <td class="right ext-view-game-vip">
		<?php if ((isset($game['game_vip_id'])) && ($game['game_vip_id'] != '')) { ?>
		<a href="<?php echo $game_url_edit_product . $game['game_vip_id']; ?>"><?php echo $text_game_vip_edit; ?></a>
		<?php } ?>
	      </td>
              <td class="left ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip"><a onclick="$('#game-row<?php echo $game_row; ?>').remove();" class="button"><?php echo $button_remove; ?></a></td>
            </tr>
          </tbody>
          <?php $game_row++; ?>
          <?php } ?>
          <tfoot>
          </tfoot>
        </table>
		<a onclick="addGame();" class="button"><?php echo $button_add_game; ?></a>
	  </td>
        </tr>
        <tr>
        <tr>
          <td><?php echo $entry_bonusloto_greeting_text; ?></td>
          <td><textarea name="bonusloto_greeting_text" rows="10" style="width: 540px;"><?php echo $bonusloto_greeting_text; ?></textarea></td>
        </tr>
        <tr>
          <td><span class="required">*</span><?php echo $entry_bonusloto_cron; ?></td>
          <td>
	<table class="list">
          <thead>
            <tr>
              <td class="left"><?php echo $entry_bonusloto_start_url; ?></td>
            </tr>
          </thead>
            <tr>
              <td class="left"><?php echo $bonusloto_start_url; ?><input type="hidden" name="bonusloto_start_pass" value="<?php echo $bonusloto_start_pass; ?>" /></td>
            </tr>
            <tr>
              <td class="left"><div style="display:inline-block;"><?php echo $entry_bonusloto_cron_wget_path; ?></div><div style="display:inline-block;"><input type="text" name="bonusloto_cron_wget_path" value="<?php echo $bonusloto_cron_wget_path; ?>" /></div></td>
            </tr>
	</table>
	<table class="list" id="mainSettings" >
          <thead>
            <tr>
              <td class="left"><?php echo $entry_cron_status; ?></td>
              <td class="left"><?php echo $entry_cron_log; ?></td>
              <td class="left"><?php echo $entry_cron_value; ?></td>
              <td class="left"><?php echo $entry_cron_test; ?></td>
              <td class="left"><?php echo $entry_cron_question; ?></td>
            </tr>
          </thead>
	<tr>
		<td align="center" style="width: 45px;">

                  <?php if (isset($CronEnabled) and ($CronEnabled =='on')) { ?>
				<div class="switch checkbox1"><input name="CronEnabled" type="checkbox" checked><label><i></i></label></div>		
                  <?php } else { ?>
				<div class="switch checkbox1"><input name="CronEnabled" type="checkbox"><label><i></i></label></div>
                  <?php } ?>
		</td>
		<td  align="center" style="width: 45px;">
                  <?php if (isset($LogEnabled) and ($LogEnabled =='on')) { ?>
				<div class="switch checkbox1"><input name="bonusloto_cron_log" type="checkbox" checked><label><i></i></label></div>		
                  <?php } else { ?>
				<div class="switch checkbox1"><input name="bonusloto_cron_log" type="checkbox"><label><i></i></label></div>
                  <?php } ?>

		<div style="position: absolute;margin-left: -3px;"><a id="CronLogView"  href="<?php echo $cronlog; ?>"><?php echo $button_cron_log; ?></a></div>
		</td>

		<td class="left" style="width: 440px;">
		   <div id="PeriodicOptions">
	        	<div id="CronSelector"></div>
        		<input type="hidden" name="PeriodicCronValue" value="">
			Generated cron entry: <span id="CronGenEntry"></span>
		   </div>
		</td>
		<td style="width: 165px;">
			<a id="TestCronAvailablity" class="button" href="<?php echo $test_cron_av; ?>"><?php echo $button_test_cron; ?></a>
		</td>
		<td>
			<div class="well"><i class="icon-question-sign"></i><?php echo $entry_cron_question_well; ?> </div>  
		</td>
	</tr>
	</table>
	  </td>
        </tr>
        <tr>
          <td><span class="required">*</span><?php echo $entry_bonusloto_rotater_time; ?></td>
          <td><input type="text" name="bonusloto_rotater_time" value="<?php echo $bonusloto_rotater_time; ?>" /></td>
        </tr>
        <tr>
          <td><?php echo $entry_bonusloto_posting_status; ?></td>
          <td><select name="bonusloto_posting">
              <?php if ($bonusloto_posting == '1') { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
          <td><?php echo $entry_bonusloto_posting_button; ?></td>
          <td>
		<table>
		<tr><td><img id="vkShare"></td><td><img id="odklShare"></td><td><img id="fbShare"></td><td><img id="twShare"></td><td><img id="mailruShare"></td><td><img id="yaruShare"></td></tr>
		<tr>
			<td align="center"><input type="checkbox" <?php if ($bonusloto_posting_vk == 'vkShare') { echo "checked";} ?> 		name="bonusloto_posting_vk" value="vkShare" /></td>
			<td align="center"><input type="checkbox" <?php if ($bonusloto_posting_odk == 'odklShare') { echo "checked";} ?> 	name="bonusloto_posting_odk" value="odklShare" /></td>
			<td align="center"><input type="checkbox" <?php if ($bonusloto_posting_fb == 'fbShare') { echo "checked";} ?> 		name="bonusloto_posting_fb" value="fbShare" /></td>
			<td align="center"><input type="checkbox" <?php if ($bonusloto_posting_tw == 'twShare') { echo "checked";} ?> 		name="bonusloto_posting_tw" value="twShare" /></td>
			<td align="center"><input type="checkbox" <?php if ($bonusloto_posting_mail == 'mailruShare') { echo "checked";} ?> 	name="bonusloto_posting_mail" value="mailruShare" /></td>
			<td align="center"><input type="checkbox" <?php if ($bonusloto_posting_ya == 'yaruShare') { echo "checked";} ?> 	name="bonusloto_posting_ya" value="yaruShare" /></td>
		</table>
	  </td>
        </tr>
        <tr>
          <td><?php echo $entry_bonusloto_posting_time; ?></td>
          <td><input type="text" name="bonusloto_posting_time" value="<?php echo $bonusloto_posting_time; ?>" /></td>
        </tr>
        <tr>
          <td><?php echo $entry_bonusloto_posting_points; ?></td>
          <td><input type="text" name="bonusloto_posting_points" value="<?php echo $bonusloto_posting_points; ?>" /></td>
        </tr>
        <tr>
          <td><?php echo $entry_keyword; ?></td>
          <td><input type="text" name="keyword" value="<?php echo $keyword; ?>" /></td>
        </tr>
        <tr>
          <td><?php echo $entry_status; ?></td>
          <td><select name="bonusloto_status">
              <?php if ($bonusloto_status) { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select></td>
        </tr>
    </table>
    </div>
<!-- /tab_general -->
<!-- tab_graph -->
    <div id="tab_graph">
      <table class="form">
        <tr>
          <td><?php echo $entry_bonusloto_images_countdown; ?></td>
          <td><table cols="2" width="40%"><tr><td><select id="images_count_text" ></select></td><td><img id="images_count"></td></tr></table>
	  <input id="bonusloto_images_countdown" type="hidden" name="bonusloto_images_countdown" value="<?php echo $bonusloto_images_countdown; ?>" /></td>
        </tr>
	<tr>
          <td><?php echo $entry_bonusloto_background; ?></td>
          <td><input id="colorpickerField1" type="text" name="bonusloto_count_background" size="13" value="<?php echo $bonusloto_count_background; ?>"></td>
        </tr>
	<tr>
          <td><?php echo $entry_bonusloto_background_img; ?></td>
          <td class="left">
              <div class="image"><img src="<?php echo $bonusloto_background_img['thumb']; ?>" alt="" id="b-c-thumb" />
                  <input type="hidden" name="bonusloto_background_img[image]" value="<?php echo $bonusloto_background_img['image']; ?>" id="b-c-image"  />
                  <br />
                  <a onclick="image_upload('b-c-image', 'b-c-thumb');"><?php echo $text_browse; ?></a>&nbsp;&nbsp;|&nbsp;&nbsp;<a onclick="$('#b-c-thumb').attr('src', '<?php echo $no_image; ?>'); $('#b-c-image').attr('value', '');"><?php echo $text_clear; ?></a></div>
	  </td>
        </tr>
	<tr>
          <td><?php echo $entry_bonusloto_social_background; ?></td>
          <td><input id="colorpickerField2" type="text" name="bonusloto_social_background" size="13" value="<?php echo $bonusloto_social_background; ?>"></td>
        </tr>
	<tr>
          <td><?php echo $entry_bonusloto_social_color_title; ?></td>
          <td><input id="colorpickerField3" type="text" name="bonusloto_social_color_title" size="13" value="<?php echo $bonusloto_social_color_title; ?>"></td>
        </tr>
	<tr>
          <td><?php echo $entry_bonusloto_social_border_color; ?></td>
          <td><input id="colorpickerField4" type="text" name="bonusloto_social_border_color" size="13" value="<?php echo $bonusloto_social_border_color; ?>"></td>
        </tr>
	<tr>
          <td><?php echo $entry_bonusloto_custom_style; ?></td>
          <td><textarea name="bonusloto_custom_style" rows="5" style="width: 240px;"><?php echo $bonusloto_custom_style; ?></textarea></td>
        </tr>

        <tr>
            <td><?php echo $entry_headline_chars; ?></td>
            <td><input type="text" name="bonusloto_headline_chars" value="<?php echo $bonusloto_headline_chars; ?>" size="3" />
        </tr>
        <tr>
          <td><?php echo $entry_bonusloto_display; ?></td>
          <td><select name="bonusloto_display">
              <?php if ($bonusloto_display == '0') { ?>
              <option value="0" selected="selected"><?php echo $text_d_email; ?></option>
              <option value="1"><?php echo $text_d_name; ?></option>
              <?php } elseif ($bonusloto_display == '1') { ?>
              <option value="0"><?php echo $text_d_email; ?></option>
              <option value="1" selected="selected"><?php echo $text_d_name; ?></option>
              <?php } else { ?>
              <option value="0" selected="selected"><?php echo $text_d_email; ?></option>
              <option value="1"><?php echo $text_d_name; ?></option>
              <?php } ?>
            </select></td>
        </tr>
        <tr>
            <td><?php echo $entry_bonusloto_gamer_count; ?></td>
          <td><select name="bonusloto_gamer_count">
              <?php if ($bonusloto_gamer_count == '1') { ?>
              <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
              <option value="0"><?php echo $text_disabled; ?></option>
              <?php } else { ?>
              <option value="1"><?php echo $text_enabled; ?></option>
              <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
              <?php } ?>
            </select></td>
        </tr>
      </table>
    </div>
<!-- /tab_graph -->
      </form>
<!-- /bonusloto  -->
<!-- tab_data -->
    <div id="tab_data">
      <?php if ($bonusloto_status) { ?>
      <form action="<?php echo $delete; ?>" method="post" enctype="multipart/form-data" id="list_form">
        <table class="list">
          <thead>
            <tr>
              <td width="1" align="center"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);" /></td>
              <td class="left"><?php echo $column_title; ?></td>
              <td class="left"><?php echo $column_status; ?></td>
              <td class="left"><?php echo $column_email; ?></td>
            </tr>
          </thead>
          <tbody>
            <?php if ($bonuslotos) { ?>
            <?php $class = 'odd'; ?>
            <?php foreach ($bonuslotos as $bonusloto_story) { ?>
            <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
            <tr class="<?php echo $class; ?>">
              <td align="center"><?php if ($bonusloto_story['selected']) { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $bonusloto_story['bonusloto_id']; ?>" checked="checked" />
                <?php } else { ?>
                <input type="checkbox" name="selected[]" value="<?php echo $bonusloto_story['bonusloto_id']; ?>" />
                <?php } ?></td>
              <td class="left"><?php echo $bonusloto_story['title']; ?></td>
              <td class="left"><?php echo $bonusloto_story['status']; ?></td>
              <td class="left"><?php echo $bonusloto_story['email']; ?></td>
            </tr>
            <?php } ?>
            <?php } else { ?>
            <tr class="even">
              <td class="center" colspan="5"><?php echo $text_no_results; ?></td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </form>
      <div class="buttons"><a onclick="$('#list_form').submit();" class="button"><span><?php echo $button_delete; ?></span></a></div>
      <?php } else { ?>
      <div style="text-align: center;"><?php echo $text_module_disabled; ?></div>
      <?php } ?>
    </div>
<!-- /tab_data -->

<!-- tab_about -->
    <div id="tab_about">
        <table class="list">
          <tbody>
            <tr>
              <td class="left">Версия модуля "Бонус-лотереи" <?php echo $bonus_version; ?></td>
            </tr>
            <tr>
              <td class="left"><?php echo $text_about; ?></td>
            </tr>
          </tbody>
        </table>
    </div>
<!-- /tab_about -->	
    </div>
  </div>
</div>

<script type="text/javascript"><!--
var game_row = <?php echo $game_row; ?>;

function addGame() {	
	html  = '<tbody id="game-row' + game_row + '">';
	html += '  <tr>';
	html += '  <td class="right ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip">' + game_row + '</td>';
	html += '  <td class="left ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip">';
	html += '	<div class="switch checkbox1">';
	html += '		<input name="bonusloto_game[' + game_row + '][status]" type="checkbox">';
	html += '		<label><i></i></label>';
	html += '	</div>';
    	html += '  </td>';
	html += '    <td class="right ext-view-game-data"><input class="date" type="text" name="bonusloto_game[' + game_row + '][game_data]" value="" size="10" /></td>';
	html += '    <td class="right ext-view-game-data"><input class="time" type="text" name="bonusloto_game[' + game_row + '][game_time]" value="" size="10" /></td>';
	html += '    <td class="left ext-view-game-type"><select onchange="changeStyle(this.value,' + game_row + ')" name="bonusloto_game[' + game_row + '][game_type]">';
    html += '      <option value="1" ><?php echo $text_game_type_cupon; ?></option>';
    html += '      <option value="2" ><?php echo $text_game_type_point; ?></option>';
    html += '      <option value="3" ><?php echo $text_game_type_product; ?></option>';
    html += '      <option value="4" ><?php echo $text_game_type_other; ?></option>';
    html += '      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
	html += '    <td class="right ext-view-game-type">';
	html += '    <input class="' + game_row + '" type="text" name="bonusloto_game[' + game_row + '][game_prize_name]" value="" size="20" />';
	html += '    <input class="' + game_row + '" type="hidden" name="bonusloto_game[' + game_row + '][game_prize_id]" value="" /></td>';
	html += '    <td class="right ext-view-game-type"><input type="text" name="bonusloto_game[' + game_row + '][game_code]" value="" size="15" /></td>';
	html += '    <td class="left ext-view-game-req"><select name="bonusloto_game[' + game_row + '][game_requir]">';
    html += '      <option value="1" ><?php echo $text_game_requir_cash; ?></option>';
    html += '      <option value="2" ><?php echo $text_game_requir_point; ?></option>';
    html += '      <option value="3" ><?php echo $text_game_requir_product; ?></option>';
    html += '      <option value="4" ><?php echo $text_game_requir_post; ?></option>';
    html += '      <option value="5" ><?php echo $text_game_requir_point_buy; ?></option>';
    html += '      <option value="0" selected="selected"><?php echo $text_disabled; ?></option>';
    html += '    </select></td>';
	html += '    <td class="right ext-view-game-req"><input type="text" name="bonusloto_game[' + game_row + '][game_requir_val]" value="" size="15" /></td>';

	html += '  <td class="left ext-view-game-vip">';
	html += '	<div class="switch checkbox1">';
	html += '		<input name="bonusloto_game[' + game_row + '][game_status_vip]" type="checkbox">';
	html += '		<label><i></i></label>';
	html += '	</div>';
    	html += '  </td>';
	html += '    <td class="right ext-view-game-vip"><input type="text" name="bonusloto_game[' + game_row + '][game_vip_count]" value="" size="10" /></td>';
	html += '    <td class="right ext-view-game-vip"><input type="text" name="bonusloto_game[' + game_row + '][game_vip_buy_val]" value="" size="10" /></td>';
	html += '    <td class="right"></td>';
    html += '    <td class="left ext-view-game-data ext-view-game-type ext-view-game-req ext-view-game-vip"><a onclick="$(\'#game-row' + game_row + '\').remove();" class="button"><?php echo $button_remove; ?></a></td>';
	html += '  </tr>';
	html += '</tbody>';
	
	$('#game tfoot').before(html);
	toggleView('game', 'td','ext-view-game-data');
	$('#game-row' + game_row + ' .date').datepicker({dateFormat: 'dd.mm.yy'});
	$('#game-row' + game_row + ' .time').timepicker({timeFormat: 'hh:mm:ss'});

	game_row++;
}

//--></script> 
<script type="text/javascript"><!--
$(document).ready(function () {
	$('.date').datepicker({dateFormat: 'dd.mm.yy'});
	$('.time').timepicker({timeFormat: 'hh:mm:ss'});
	$('.product_name').autocomplete({
		delay: 100,
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
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
			var row = this.className.replace(/[^0-9]/g, '');
			$('#game-row' + row + ' .product_name').attr('value', ui.item.label);
			$('#game-row' + row + ' .product_id').attr('value', ui.item.value);
			return false;
		},
		focus: function(event, ui) {
      		return false;
	   	}
	});
	$('.coupon_name').autocomplete(game_row, {
		delay: 100,
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=module/bonusloto/autocompleteCoupon&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
				dataType: 'json',
				success: function(json) {	
					response($.map(json, function(item) {
						return {
							label: item.name,
							value: item.code,
							id: item.coupon_id
						}
					}));
				}
			});
		}, 
		select: function(event, ui) {
			var row = this.className.replace(/[^0-9]/g, '');
			$('#game-row' + row + ' .coupon_name').attr('value', ui.item.label);
			$('#game-row' + row + ' .coupon_id').attr('value', ui.item.id);
			$('#game-row' + row + ' .coupon_code').attr('value', ui.item.value);
			return false;
		},
		focus: function(event, ui) {
      		return false;
	   	}
	});

});

//--></script> 
<script type="text/javascript"><!--
function changeStyle(val,row){
		$('input[name="bonusloto_game[' + row + '][game_prize_name]"]').removeClass("coupon_name product_name point_name");
		$('input[name="bonusloto_game[' + row + '][game_prize_id]"]').removeClass("product_id coupon_id point_id");
		$('input[name="bonusloto_game[' + row + '][game_code]"]').removeClass("coupon_code point_code");
    switch (val) {
    case '1':
		$('input[name="bonusloto_game[' + row + '][game_prize_name]"]').addClass("coupon_name");
		$('input[name="bonusloto_game[' + row + '][game_prize_id]"]').addClass("coupon_id");
		$('input[name="bonusloto_game[' + row + '][game_code]"]').addClass("coupon_code");
        break;
    case '2':
		$('input[name="bonusloto_game[' + row + '][game_prize_name]"]').addClass("point_name");
		$('input[name="bonusloto_game[' + row + '][game_prize_id]"]').addClass("point_id");
		$('input[name="bonusloto_game[' + row + '][game_code]"]').addClass("point_code");
        break;
    case '3':
		$('input[name="bonusloto_game[' + row + '][game_prize_name]"]').addClass("product_name");
		$('input[name="bonusloto_game[' + row + '][game_prize_id]"]').addClass("product_id");
        break;
    default:
		$('input[name="bonusloto_game[' + row + '][game_prize_name]"]').removeClass("coupon_name product_name point_name");
		$('input[name="bonusloto_game[' + row + '][game_prize_id]"]').removeClass("product_id coupon_id point_id");
		$('input[name="bonusloto_game[' + row + '][game_code]"]').removeClass("coupon_code point_code");
    }
	$('.date').datepicker({dateFormat: 'dd.mm.yy'});
	$('.time').timepicker({timeFormat: 'hh:mm:ss'});
	$('.product_name').autocomplete({
		delay: 100,
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
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
			var row = this.className.replace(/[^0-9]/g, '');
			$('#game-row' + row + ' .product_name').attr('value', ui.item.label);
			$('#game-row' + row + ' .product_id').attr('value', ui.item.value);
			return false;
		},
		focus: function(event, ui) {
      		return false;
	   	}
	});
	$('.coupon_name').autocomplete(game_row, {
		delay: 100,
		source: function(request, response) {
			$.ajax({
				url: 'index.php?route=module/bonusloto/autocompleteCoupon&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
				dataType: 'json',
				success: function(json) {	
					response($.map(json, function(item) {
						return {
							label: item.name,
							value: item.code,
							id: item.coupon_id
						}
					}));
				}
			});
		}, 
		select: function(event, ui) {
			var row = this.className.replace(/[^0-9]/g, '');
			$('#game-row' + row + ' .coupon_name').attr('value', ui.item.label);
			$('#game-row' + row + ' .coupon_id').attr('value', ui.item.id);
			$('#game-row' + row + ' .coupon_code').attr('value', ui.item.value);
			return false;
		},
		focus: function(event, ui) {
      		return false;
	   	}
	});

}
//--></script> 
<script  type="text/javascript">
    var base = [
    {   src : "",
        name : "",
        wh : "",
        text : "<?php echo $text_format_timer; ?>"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits2.jpg",
        name : "digits2.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 1 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits2_blue.jpg",
        name : "digits2_blue.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 2 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits2_green.jpg",
        name : "digits2_green.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 3 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits2_orange.jpg",
        name : "digits2_orange.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 4 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits2_purple.jpg",
        name : "digits2_purple.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 5 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits2_red.jpg",
        name : "digits2_red.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 6 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits2_yellow.jpg",
        name : "digits2_yellow.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 7 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits_inverted.jpg",
        name : "digits_inverted.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 8 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits_transparent.jpg",
        name : "digits_transparent.png",
        wh : "53x77",
        text : "<?php echo $text_timer; ?> № 9 digits 53x77"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits_transparent.jpg",
        name : "digits_transparent-w43.png",
        wh : "43x62",
        text : "<?php echo $text_timer; ?> № 10 digits 43x62"
    },
    {   src : "view/javascript/bonusloto/img/scr_digits_transparent-w46.jpg",
        name : "digits_transparent-w46.png",
        wh : "46x67",
        text : "<?php echo $text_timer; ?> № 11 digits 46x67"
    }
    ]
    var select = document.getElementById('images_count_text');
    for (var k=0; k< base.length; k++)  {
           select.options[k] = new Option(base[k]['text'], k);
		if(base[k]['name']+"|"+base[k]['wh'] == document.getElementById('bonusloto_images_countdown').value) {
			select.options[k].selected = true;
			var kSelect = k;
		}
    }
    function init() {
        var i = this.selectedIndex || kSelect;
        var img =  document.getElementById('images_count');
        var src =  base[i]['src'];
        img.src = src;
	var valueNew =  document.getElementById('bonusloto_images_countdown');
        valueNew = base[i]['name']+"|"+base[i]['wh'];
	$('#bonusloto_images_countdown').val(valueNew);
    }
    select.addEventListener('change', init);
    init();
</script>
<script type="text/javascript"><!--
$(function () {
    var tabContainers = $('div.tabs > div'); 
    tabContainers.hide().filter(':first').show(); 
    $('div.tabs ul.tabNavigation a').click(function () {
        tabContainers.hide(); 
        tabContainers.filter(this.hash).show(); 
        $('div.tabs ul.tabNavigation a').removeClass('selected'); 
        $(this).addClass('selected'); 
        return false;
    }).filter(':first').click();
});
//--></script>
<script type="text/javascript">
$(document).ready(function(){
  $('#colorpickerField1,#colorpickerField2,#colorpickerField3,#colorpickerField4').ColorPicker({
	color: '#CCCCCC',
	onSubmit: function(hsb, hex, rgb, e1) {
		$(e1).val(hex);
	},
	onBeforeShow: function () {
		$(this).ColorPickerSetColor(this.value);
	}
  })
  .bind('keyup', function(){
	$(this).ColorPickerSetColor(this.value);
  });
});
</script>
<script type="text/javascript">
function stoplototron(){
       $.ajax({
                type: "GET",
                url: "<?php echo $stop_lototron; ?>",
                success: function(html) {
                        $("#result_lototron").empty();
                        $("#result_lototron").append(html);
                }
        });
}
</script>
<script type="text/javascript">
$(document).ready(function(){$("#CronLogView").colorbox({iframe:true, width:"80%", height:"65%"});});
$(document).ready(function(){$("#TestCronAvailablity").colorbox({iframe:true, width:"80%", height:"65%"});});
$(document).ready(function() {	
$('#CronSelector').cron({
		initial: "<?php if(!empty($PeriodicCronValue)) echo $PeriodicCronValue; else echo "* * * * *";  ?>",
		useGentleSelect: false,
		onChange: function() { 
			$('input[name="PeriodicCronValue"').val($(this).cron("value"));
			$('#CronGenEntry').text($(this).cron("value"));		 
		},
		customValues: {
		        "2 Minutes" : "*/2 * * * *",
		        "5 Minutes" : "*/5 * * * *",
		        "10 Minutes" : "*/10 * * * *"
		}
});
});
</script>
<script type="text/javascript"><!--
function image_upload(field, thumb) {

	$('#dialog').remove();
	
	$('#content').prepend('<div id="dialog" style="padding: 3px 0px 0px 0px;"><iframe src="index.php?route=common/filemanager&token=<?php echo $token; ?>&field=' + encodeURIComponent(field) + '" style="padding:0; margin: 0; display: block; width: 100%; height: 100%;" frameborder="no" scrolling="auto"></iframe></div>');
	
	$('#dialog').dialog({
		title: '<?php echo $text_image_manager; ?>',
		close: function (event, ui) {
			if ($('#' + field).attr('value')) {
				$.ajax({
					url: 'index.php?route=common/filemanager/image&token=<?php echo $token; ?>&image=' + encodeURIComponent($('#' + field).attr('value')),
					dataType: 'text',
					success: function(data) {
						$('#b-c-thumb').replaceWith('<img src="' + data + '" alt="" id="' + thumb + '" />');
					}
				});
			}
		},	
		bgiframe: false,
		width: 960,
		height: 520,
		resizable: false,
		modal: false,
		dialogClass: 'dlg'
	});
};
//--></script> 
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
$('#games a').tabs(); 
$('#languages a').tabs(); 
//--></script> 
<?php echo $footer; ?>