<div class="buttons">
  <div class="right">
    <input type="button" value="<?= $button_confirm ?>" id="button-confirm" class="button" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({ 
		type: 'get',
		url: 'index.php?route=payment/deposit/confirm',
		success: function() {
			window.location = '<?= $continue ?>';
		}		
	});
});
//--></script> 
