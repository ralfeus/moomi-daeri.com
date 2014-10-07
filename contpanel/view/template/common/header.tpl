<?= '<?xml version="1.0" encoding="UTF-8"?>' . "\n" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $lang; ?>" xml:lang="<?php echo $lang; ?>">
<head>
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
<?php if ($description) { ?>
<meta name="description" content="<?php echo $description; ?>" />
<?php } ?>
<?php if ($keywords) { ?>
<meta name="keywords" content="<?php echo $keywords; ?>" />
<?php } ?>
<?php foreach ($links as $link) { ?>
<link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="view/stylesheet/stylesheet.css" />
<link rel="stylesheet" type="text/css" href="view/stylesheet/jquery.multiselect.css" />
<link rel="stylesheet" type="text/css" href="view/stylesheet/jquery.calculator.css" />
<link rel="stylesheet" type="text/css" href="view/stylesheet/jquery.multiselect.filter.css" />
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script type="text/javascript" src="view/javascript/jquery/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
<script type="text/javascript" src="view/javascript/jquery/ui/external/jquery.bgiframe-2.1.2.js"></script>
<script type="text/javascript" src="view/javascript/jquery/tabs.js"></script>
<script type="text/javascript" src="view/javascript/jquery/superfish/js/superfish.js"></script>
<script type="text/javascript" src="view/javascript/jquery/jquery.multiselect.js"></script>
<script type="text/javascript" src="view/javascript/jquery/jquery.multiselect.filter.js"></script>
<script type="text/javascript" src="view/javascript/jquery/jquery.plugin.js"></script>
<script type="text/javascript" src="view/javascript/jquery/jquery.calculator.js"></script>
<script type="text/javascript">
    $(function  () {
        $('#Calculator').calculator({showOn: 'opbutton', buttonImageOnly: true, buttonImage: 'view/image/calculator.png'});
    });
</script> 
<?php foreach ($scripts as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
<?php } ?>
<script type="text/javascript">
//-----------------------------------------
// Confirm Actions (delete, uninstall)
//-----------------------------------------
$(document).ready(function(){
    $(".scrollbox").each(function(i) {
        if ($(this).attr('id') == null) {
            $(this).attr('id', 'scrollbox_' + i);
            sbox = '#' + $(this).attr('id');
            $(this).after(
                '<span>' +
                    '<a onclick="$(\'' + sbox + ' :checkbox\').attr(\'checked\', \'checked\');" style="text-decoration: underline;">' +
                        '<?= $textSelectAll; ?>' +
                    '</a>' +
                    '&nbsp;/&nbsp;' +
                    '<a onclick="$(\'' + sbox + ' :checkbox\').removeAttr(\'checked\');" style="text-decoration: underline;">' +
                        '<?= $textUnselectAll ?>' +
                    '</a>' +
                '</span>');
        }
    });

    // Confirm Delete
    $('#form').submit(function(){
        if ($(this).attr('action').indexOf('delete',1) != -1) {
            if (!confirm('<?php echo $text_confirm; ?>')) {
                return false;
            }
        }
    });

    // Confirm Uninstall
    $('a').click(function(){
        if ($(this).attr('href') != null && $(this).attr('href').indexOf('uninstall', 1) != -1) {
            if (!confirm('<?php echo $text_confirm; ?>')) {
                return false;
            }
        }
    });
});
//--></script>
<?= $googleAnalyticsScript ?>
</head>
<body>
<div id="container">
<div id="header">
  <div class="div1">
    <div class="div2"><img src="view/image/logo.png" title="<?php echo $heading_title; ?>" onclick="location = '<?php echo $home; ?>'" /></div>
    <?php if ($logged) { ?>
    <div class="div3"><img src="view/image/lock.png" alt="" style="position: relative; top: 3px;" />&nbsp;<?php echo $logged; ?></div>
    <?php } ?>
  </div>
  <?php if ($logged) { ?>
  <div id="menu">
    <ul class="left" style="display: none;">
      <li id="catalog"><a class="top"><?php echo $text_catalog; ?></a>
        <ul>
          <li><a href="<?php echo $category; ?>"><?php echo $text_category; ?></a></li>
          <li>
              <a class="parent" href="<?php echo $product; ?>"><?php echo $text_product; ?></a>
              <ul>
                  <li><a href="<?= $urlImportProducts ?>"><?= $textImportProducts ?></a></li>
              </ul>
          </li>
          <li><a class="parent"><?php echo $text_attribute; ?></a>
            <ul>
              <li><a href="<?php echo $attribute; ?>"><?php echo $text_attribute; ?></a></li>
              <li><a href="<?php echo $attribute_group; ?>"><?php echo $text_attribute_group; ?></a></li>
            </ul>
          </li>
          <li><a href="<?php echo $option; ?>"><?php echo $text_option; ?></a></li>
          <li><a href="<?php echo $manufacturer; ?>"><?php echo $text_manufacturer; ?></a></li>
          <li>
            <a class="parent"><?php echo $text_supplier; ?></a>
            <ul>
                <li><a href="<?php echo $supplier; ?>"><?php echo $text_supplier; ?></a></li>
                <li><a href="<?php echo $supplier_group; ?>"><?php echo $text_supplier_group; ?></a></li>
            </ul>
          </li>
          <li><a href="<?php echo $download; ?>"><?php echo $text_download; ?></a></li>
          <li><a href="<?php echo $review; ?>"><?php echo $text_review; ?></a></li>
          <li><a href="<?php echo $information; ?>"><?php echo $text_information; ?></a></li>
        </ul>
      </li>
      <li id="sale"><a class="top"><?php echo $text_sale; ?></a>
        <ul>
		  <li><a class="parent" href="<?php echo $order_items; ?>"><?php echo $text_order_items; ?></a>
			<ul>
				<li><a href="<?= $order_items_processing ?>"><?= $text_order_items_processing ?></a></li>
                <li><a href="<?= $repurchaseOrders ?>"><?= $textRepurchaseOrders ?></a></li>
			</ul>
		  </li>
        </ul>
      </li>
    </ul>
    <ul class="right">
      <li id="store"><a onClick="window.open('<?php echo $store; ?>');" class="top"><?php echo $text_front; ?></a>
        <ul>
          <?php foreach ($stores as $stores) { ?>
          <li><a onClick="window.open('<?php echo $stores['href']; ?>');"><?php echo $stores['name']; ?></a></li>
          <?php } ?>
        </ul>
      </li>
      <li id="store"><a class="top" href="<?php echo $logout; ?>"><?php echo $text_logout; ?></a></li>
    </ul>
    <script type="text/javascript"><!--
$(document).ready(function() {
	$('#menu > ul').superfish({
		hoverClass	 : 'sfHover',
		pathClass	 : 'overideThisToUse',
		delay		 : 0,
		animation	 : {height: 'show'},
		speed		 : 'normal',
		autoArrows   : false,
		dropShadows  : false,
		disableHI	 : false, /* set to true to disable hoverIntent detection */
		onInit		 : function(){},
		onBeforeShow : function(){},
		onShow		 : function(){},
		onHide		 : function(){}
	});

	$('#menu > ul').css('display', 'block');
});

function getURLVar(urlVarName) {
	var urlHalves = String(document.location).toLowerCase().split('?');
	var urlVarValue = '';

	if (urlHalves[1]) {
		var urlVars = urlHalves[1].split('&');

		for (var i = 0; i <= (urlVars.length); i++) {
			if (urlVars[i]) {
				var urlVarPair = urlVars[i].split('=');

				if (urlVarPair[0] && urlVarPair[0] == urlVarName.toLowerCase()) {
					urlVarValue = urlVarPair[1];
				}
			}
		}
	}

	return urlVarValue;
}

$(document).ready(function() {
	route = getURLVar('route');

	if (!route) {
		$('#dashboard').addClass('selected');
	} else {
		part = route.split('/');

		url = part[0];

		if (part[1]) {
			url += '/' + part[1];
		}

		$('a[href*=\'' + url + '\']').parents('li[id]').addClass('selected');
	}
});
//--></script>
  </div>
  <?php } ?>
</div>
