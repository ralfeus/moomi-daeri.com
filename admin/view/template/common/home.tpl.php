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
            .attr('action', 'index.php?route=catalog/product&token=5e058e3be9584b442097be93e2edb2e1')
            .submit();
    return;
}

function resetFilter()
{
    $('#form')
            .attr('action', 'index.php?route=catalog/product&token=5e058e3be9584b442097be93e2edb2e1&resetFilter=1')
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
      <h1><img src="view/image/home.png" alt="" /> <?php echo $heading_title; ?> v.3.0</h1>
      <div style="display: none;" class='buttons'><a href="<?php echo $clear_cache ?>" class="button"><span>Clear Cache</span></a></div>
    </div>
    <div class="content">
      <div class="overview">
        <div class="dashboard-heading"><?php echo $text_overview; ?></div>
        <div class="dashboard-content">
          <table>
            <tr>
              <td><?php echo $text_total_sale; ?></td>
              <td><?php echo $total_sale; ?></td>
            </tr>
            <tr>
              <td><?php echo $text_total_sale_year; ?></td>
              <td><?php echo $total_sale_year; ?></td>
            </tr>
            <tr>
              <td><?php echo $text_total_order; ?></td>
              <td><?php echo $total_order; ?></td>
            </tr>
            <tr>
              <td><?php echo $text_total_customer; ?></td>
              <td><?php echo $total_customer; ?></td>
            </tr>
            <tr>
              <td><?php echo $text_total_customer_approval; ?></td>
              <td><?php echo $total_customer_approval; ?></td>
            </tr>
            <tr>
              <td><?php echo $text_total_review_approval; ?></td>
              <td><?php echo $total_review_approval; ?></td>
            </tr>
            <tr>
              <td><?php echo $text_total_affiliate; ?></td>
              <td><?php echo $total_affiliate; ?></td>
            </tr>
            <tr>
              <td><?php echo $text_total_affiliate_approval; ?></td>
              <td><?php echo $total_affiliate_approval; ?></td>
            </tr>
          </table>
        </div>
      </div>
      <div class="statistic">
        <div class="range"><?php echo $entry_range; ?>
          <select id="range" onchange="getSalesChart(this.value)">
            <option value="day"><?php echo $text_day; ?></option>
            <option value="week"><?php echo $text_week; ?></option>
            <option value="month"><?php echo $text_month; ?></option>
            <option value="year"><?php echo $text_year; ?></option>
          </select>
        </div>
        <div class="dashboard-heading"><?php echo $text_statistics; ?></div>
        <div class="dashboard-content">
          <div id="report" style="width: 390px; height: 170px; margin: auto;"></div>
        </div>
      </div>
      <div class="latest">
        <div class="box">
          <div class="heading">
            <h1><img src="view/image/product.png" alt="" /> <?php echo $heading_title; ?></h1>
          </div>
          <div class="content">
            <form action="" method="post" enctype="multipart/form-data" id="form">
              <table class="list">
                <thead>
                  <tr>
                    <td> Product Name </td>
                    <td> Model </td>
                    <td> Price </td>
                    <td> Korean Name </td>
                    <td> Username </td>
                    <td> Date added </td>
                    <td> Action </td>
                  </tr>
                </thead>
                <tbody>
                  <tr class="filter">
                    <!--<td></td>-->
                    <td></td>
                    <td></td>
                    <td></td>
                    <td><!--<input name="filterKoreanName" value="<?= $filterKoreanName ?>" />--></td>
                    <td>
                        <select name="filterUserNameId[]" multiple="true">
                            <?php foreach ($usernames as $key => $value):
                            $selected = in_array($key, $filterUserNameId) ? 'selected' : ''; ?>
                            <option value="<?= $key ?>" <?= $selected ?>><?= $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td>
                        <input name="filterDateAddedFrom" class="date" value="<?= $filterDateAddedFrom ?>" />
                        <input name="filterDateAddedTo" class="date" value="<?= $filterDateAddedTo ?>" />
                    </td>
                    <td align="right">
                        <a onclick="filter();" class="button"><?php echo $button_filter; ?></a>
                        <a onclick="resetFilter();" class="button"><?= $textResetFilter ?></a>
                    </td>
                  </tr>
                  <?php if (count($products) > 0) { ?>
                  <?php foreach ($products as $product) { ?>
                  <tr>
                    <!--<td class="center"><img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" style="padding: 1px; border: 1px solid #DDDDDD;" /></td>-->
                    <td class="left"><?php echo $product['name']; ?></td>
                    <td class="left"><?php echo $product['model']; ?></td>
                    <td class="left"><?php if ($product['special']) { ?>
                      <span style="text-decoration: line-through;"><?php echo $product['price']; ?></span><br/>
                      <span style="color: #b00;"><?php echo $product['special']; ?></span>
                      <?php } else { ?>
                      <?php echo $product['price']; ?>
                      <?php } ?></td>
                    <td class="right">
                      <?php echo $product['korean_name']; ?>  
                    </td>
                    <td class="right"> <?php if(!empty($product['user_name_page_url'])) { ?>
                      <a href="<?php echo $product['user_name_page_url']; ?>"><?php echo $product['user_name']; ?></a>
                      <?php } else { ?>
                      <?php echo $product['user_name']; } ?>
                    </td>
                    <td><?= $product['dateAdded'] ?></td>
                    <td class="right"><?php foreach ($product['action'] as $action) { ?>
                      [ <a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a> ]
                      <?php } ?>
                      <?php 
                        foreach ($product['link'] as $link) {
                          if(!empty($link['href'])){
                            echo "<br /><strong><a href='" . $link["href"] . "' target='_blank' style='text-decoration: none; color: black'>" . $link["text"] . "</a><strong>"; 
                          } 
                        } 
                      ?>
                    </td>
                  </tr>
                  <?php } ?>
                  <?php } else { ?>
                  <tr>
                    <td class="center" colspan="8"><?php echo $text_no_results; ?></td>
                  </tr>
                  <?php } ?>
                </tbody>
              </table>
            </form>
            <div class="pagination"><?php echo $pagination; ?></div>
          </div>
        </div>
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