<?php echo $header; ?>
<script type="text/javascript"><!--
$(document).ready(function() {
    $('.date').datepicker({dateFormat: 'yy-mm-dd'});
    $('[name="filterUserNameId[]"]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();
    $('[name="filterManufacturerId[]"]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();
    $('[name=filterStatus]').multiselect({
        multiple: false,
        noneSelectedText: "-- No filter --",
        selectedList: 1
    }).multiselectfilter();
    $('[name="filterSupplierId[]"]')
            .multiselect({
                noneSelectedText: "-- No filter --",
                selectedList: 1
            })
            .multiselectfilter();
    $('button.ui-multiselect').css('width', '110px');
});

function filter()
{
    $('#form')
            .attr('action', 'index.php?route=catalog/product&token=<?php echo $token; ?>')
            .submit();
    return;
}

function resetFilter()
{
    $('#form')
            .attr('action', 'index.php?route=catalog/product&token=<?php echo $token; ?>&resetFilter=1')
            .submit();
}
//--></script> 
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
<?php foreach ($notifications as $class => $notification)
    echo "<div class=\"$class\">$notification</div>";
?>

    <?php if ($error_install) { ?>
  <div class="error"><?php echo $error_install; ?></div>
  <?php } ?>
  <?php if ($error_image) { ?>
  <div class="error"><?php echo $error_image; ?></div>
  <?php } ?>
  <?php if ($error_image_cache) { ?>
  <div class="error"><?php echo $error_image_cache; ?></div>
  <?php } ?>
  <?php if ($error_cache) { ?>
  <div class="error"><?php echo $error_cache; ?></div>
  <?php } ?>
  <?php if ($error_download) { ?>
  <div class="error"><?php echo $error_download; ?></div>
  <?php } ?>
  <?php if ($error_logs) { ?>
  <div class="error"><?php echo $error_logs; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/home.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div style="display: none;" class='buttons'><a href="<?php echo $clear_cache ?>" class="button"><span>Clear Cache</span></a></div>
    </div>
    <div class="content">
      <div class="overview">
        </div>
      <div class="statistic">
      </div>
      <div class="latest">
      </div>
    </div>
  </div>
</div>
<!--[if IE]>
<script type="text/javascript" src="view/javascript/jquery/flot/excanvas.js"></script>
<![endif]--> 
<script type="text/javascript" src="view/javascript/jquery/flot/jquery.flot.js"></script> 
<script type="text/javascript"><!--
function getSalesChart(range) {
	$.ajax({
		type: 'GET',
		url: 'index.php?route=common/home/chart&token=<?php echo $token; ?>&range=' + range,
		dataType: 'json',
		async: false,
		success: function(json) {
			var option = {	
				shadowSize: 0,
				lines: { 
					show: true,
					fill: true,
					lineWidth: 1
				},
				grid: {
					backgroundColor: '#FFFFFF'
				},	
				xaxis: {
            		ticks: json.xaxis
				}
			}

			$.plot($('#report'), [json.order, json.customer], option);
		}
	});
}

getSalesChart($('#range').val());
//--></script> 
<?php echo $footer; ?>