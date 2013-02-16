<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content">
	<?php echo $content_top; ?>
	<h1 style="display: none;">
			<?php echo $heading_title; ?>
	</h1>
	<div style="height: 250px;">
		<p style="float: left;"> Our Holidays: &nbsp;</p>
		<div id="calendar" style="float: left"></div>
		<div>
			<div class="legendRect work"></div> workday <br />
			<div class="legendRect free"></div> holiday <br />
		</div>
	</div>
	<?php echo $content_bottom; ?>
</div>
<?php echo $footer; ?>