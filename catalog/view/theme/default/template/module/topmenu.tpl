<div id="topmenu" style="display:none"><?php if ($tmcategories) {  echo $tmcategories; } ?></div>

<script type="text/javascript">
<!--
$(document).ready(function(){
	if (document.getElementById('menu'))	{
		var topmenu = $("#topmenu");
		var themenu = $("#menu ul");
		themenu.empty();
		topmenu.css("display", "block");
		topmenu.appendTo(document.getElementById('menu'));
	}
});
//-->
</script>
