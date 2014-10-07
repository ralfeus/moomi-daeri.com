<?php if($error_warning) { ?>
	<div class="error"><?php echo $error_warning; ?></div>
<?php } ?>

<?php if($success) { ?>
	<div class="success"><?php echo $success; ?></div>
<?php } ?>

<form method="post" enctype="multipart/form-data" id="product_option_form">
	<table class="form">
		<tr>
			<td><span class="required">*</span> <?php echo $entry_option_name; ?></td>
			<td>
				<?php foreach($languages as $language) { ?>
					<input type="text" name="option_description[<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($option_description[$language['language_id']]) ? $option_description[$language['language_id']]['name'] : ''; ?>" />

					<img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /><br />

					<?php if(isset($error_name[$language['language_id']])) { ?>
						<span class="error"><?php echo $error_name[$language['language_id']]; ?></span><br />
					<?php } ?>
				<?php } ?>
			</td>
		</tr>

		<tr>
			<td><?php echo $entry_type; ?></td>
			<td>
				<select name="type" id="option_type">
					<optgroup label="<?php echo $text_choose; ?>">
						<?php if($type == 'select') { ?>
							<option value="select" selected><?php echo $text_option_select; ?></option>
						<?php } else { ?>
							<option value="select"><?php echo $text_option_select; ?></option>
						<?php } ?>

						<?php if($type == 'radio') { ?>
							<option value="radio" selected><?php echo $text_radio; ?></option>
						<?php } else { ?>
							<option value="radio"><?php echo $text_radio; ?></option>
						<?php } ?>

						<?php if($type == 'checkbox') { ?>
							<option value="checkbox" selected><?php echo $text_checkbox; ?></option>
						<?php } else { ?>
							<option value="checkbox"><?php echo $text_checkbox; ?></option>
						<?php } ?>

						<?php if($type == 'image') { ?>
							<option value="image" selected><?php echo $text_image; ?></option>
						<?php } else { ?>
							<option value="image"><?php echo $text_image; ?></option>
						<?php } ?>
					</optgroup>

					<optgroup label="<?php echo $text_input; ?>">
						<?php if($type == 'text') { ?>
							<option value="text" selected><?php echo $text_text; ?></option>
						<?php } else { ?>
							<option value="text"><?php echo $text_text; ?></option>
						<?php } ?>

						<?php if($type == 'textarea') { ?>
							<option value="textarea" selected><?php echo $text_textarea; ?></option>
						<?php } else { ?>
							<option value="textarea"><?php echo $text_textarea; ?></option>
						<?php } ?>
					</optgroup>

					<optgroup label="<?php echo $text_file; ?>">
						<?php if($type == 'file') { ?>
							<option value="file" selected><?php echo $text_file; ?></option>
						<?php } else { ?>
							<option value="file"><?php echo $text_file; ?></option>
						<?php } ?>
					</optgroup>

					<optgroup label="<?php echo $text_date; ?>">
						<?php if($type == 'date') { ?>
							<option value="date" selected><?php echo $text_date; ?></option>
						<?php } else { ?>
							<option value="date"><?php echo $text_date; ?></option>
						<?php } ?>

						<?php if($type == 'time') { ?>
							<option value="time" selected><?php echo $text_time; ?></option>
						<?php } else { ?>
							<option value="time"><?php echo $text_time; ?></option>
						<?php } ?>

						<?php if($type == 'datetime') { ?>
							<option value="datetime" selected><?php echo $text_datetime; ?></option>
						<?php } else { ?>
							<option value="datetime"><?php echo $text_datetime; ?></option>
						<?php } ?>
					</optgroup>
				</select>
			</td>
		</tr>

		<tr>
			<td><?php echo $entry_sort_order; ?></td>
			<td><input type="text" name="sort_order" value="<?php echo $sort_order; ?>" size="1" /></td>
		</tr>
	</table>

	<table id="option-value" class="list">
		<thead>
			<tr>
				<td class="left"><span class="required">*</span> <?php echo $entry_value; ?></td>
				<td class="left"><?php echo $entry_image; ?></td>
				<td class="right"><?php echo $entry_sort_order; ?></td>
				<td></td>
			</tr>
		</thead>

		<?php $option_value_row = 0; ?>

		<?php foreach($option_values as $option_value) { ?>
			<tbody id="option-value-row<?php echo $option_value_row; ?>">
				<tr>
					<td class="left">
						<input type="hidden" name="option_value[<?php echo $option_value_row; ?>][option_value_id]" value="<?php echo $option_value['option_value_id']; ?>" />

						<?php foreach($languages as $language) { ?>
							<input type="text" name="option_value[<?php echo $option_value_row; ?>][option_value_description][<?php echo $language['language_id']; ?>][name]" value="<?php echo isset($option_value['option_value_description'][$language['language_id']]) ? $option_value['option_value_description'][$language['language_id']]['name'] : ''; ?>" />

							<img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" /><br />

							<?php if(isset($error_option_value[$option_value_row][$language['language_id']])) { ?>
								<span class="error"><?php echo $error_option_value[$option_value_row][$language['language_id']]; ?></span>
							<?php } ?>
						<?php } ?>
					</td>
					<td class="left">
						<div class="image">
							<img src="<?php echo $option_value['thumb']; ?>" alt="" id="option_value_thumb<?php echo $option_value_row; ?>" />

							<input type="hidden" name="option_value[<?php echo $option_value_row; ?>][image]" value="<?php echo $option_value['image']; ?>" id="option_value_image<?php echo $option_value_row; ?>"  /><br />

							<a onclick="option_value_image_upload('option_value_image<?php echo $option_value_row; ?>', 'option_value_thumb<?php echo $option_value_row; ?>');"><?php echo $text_browse; ?></a>
							&nbsp;&nbsp;|&nbsp;&nbsp;
							<a onclick="$('#option_value_thumb<?php echo $option_value_row; ?>').attr('src', '<?php echo $no_image; ?>'); $('#option_value_image<?php echo $option_value_row; ?>').attr('value', '');"><?php echo $text_clear; ?></a>
						</div>
					</td>
					<td class="right">
						<input type="text" name="option_value[<?php echo $option_value_row; ?>][sort_order]" value="<?php echo $option_value['sort_order']; ?>" size="1" />
					</td>
					<td class="left">
						<a onclick="$('#option-value-row<?php echo $option_value_row; ?>').remove();" class="button">
							<?php echo $button_remove; ?>
						</a>
					</td>
				</tr>
			</tbody>

			<?php $option_value_row++; ?>
		<?php } ?>

		<tfoot>
			<tr>
				<td colspan="3"></td>
				<td class="left"><a onclick="addCreatedOptionValue();" class="button"><?php echo $button_add_option_value; ?></a></td>
			</tr>
		</tfoot>
	</table>
</form>

<a id="create_option_button" onclick="return false;" class="button"><?php echo $text_create_option; ?></a>