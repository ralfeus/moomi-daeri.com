<style type="text/css">
#multi_display a {
	padding-left:10px;
	font:inherit;
	background:none;
	border:none;
	color:#333;
}
#multi_display a:hover {
	text-decoration:none;
	background:none;
	border:none;	
}
#multi_display .ui-icon {
	margin-left:-10px;
}
#multi_display ul {
	overflow:visible;
	heigh:auto;
	background:none;
	border:none;
	margin:0;
	padding:0;
}
#multi_display li {
	display:block;
}


</style>

<div class="box">
  <div class="box-heading"><?php echo $heading_title; ?></div>
  <div class="box-content">
    <div id="multi_display"  class="box-category">
    	<?php echo $categories; ?>
  	</div>
  </div>
</div>
<script type="text/javascript"><?php echo $scripts; ?></script>







