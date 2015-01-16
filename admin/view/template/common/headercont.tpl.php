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
<link rel="stylesheet" type="text/css" href="view/stylesheet/jquery.checkboxtree.css" />
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
<script type="text/javascript" src="view/javascript/jquery/jquery.checkboxtree.js"></script>
<script type="text/javascript" src="view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript">
    $(function  () {
        $('#Calculator').calculator({showOn: 'opbutton', buttonImageOnly: true, buttonImage: 'view/image/calculator.png'});
    });
</script> 
<script type="text/javascript">
$(function(){
  $.fn.scrollToTop=function(){
    $(this).hide().removeAttr("href");
    if($(window).scrollTop()!="0"){
        $(this).fadeIn("slow")
  }
  var scrollDiv=$(this);
  $(window).scroll(function(){
    if($(window).scrollTop()=="0"){
    $(scrollDiv).fadeOut("slow")
    }else{
    $(scrollDiv).fadeIn("slow")
  }
  });
    $(this).click(function(){
      $("html, body").animate({scrollTop:0},"slow")
    })
  }
});
$(function() {$("#toTop").scrollToTop();});
</script>
<script type="text/javascript">
$(document).ready(function() {
	// Expand Panel
	$("#open").click(function(){
		$("div#menu").slideUp("fast");
	
	});	
	
	// Collapse Panel
	$("#close").click(function(){
		$("div#menu").slideDown("fast");	
	});		
	
	// Switch buttons from "Log In | Register" to "Close Panel" on click
	$("#toggle a").click(function () {
		$("#toggle a").toggle();
	});		
		
});
</script>
<script>
  (function($){$.fn.extend({limit:function(limit,element){var interval,f;var self=$(this);$(this).focus(function(){interval=window.setInterval(substring,255)});$(this).blur(function(){clearInterval(interval);substring()});substringFunction="function substring(){ var val = $(self).val();var length = val.length;if(length > limit){$(self).val($(self).val().substring(0,limit));}";if(typeof element!='undefined')substringFunction+="if($(element).html() != limit-length){$(element).html((limit-length<=0)?'0':limit-length);}";substringFunction+="}";eval(substringFunction);substring()}})})(jQuery);
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
  <a id="toTop"></a>
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
            <li><a class="parent" href="<?php echo $product; ?>"><?php echo $text_product; ?></a>
              <ul>
                  <li><a href="<?php echo $product; ?>&resetFilter=1"><?php echo $text_reset_filter_product; ?></a></li>
              </ul>
            </li>
            <li><a class="parent"><?php echo $text_attribute; ?></a>
              <ul>
                <li><a href="<?php echo $attribute; ?>"><?php echo $text_attribute; ?></a></li>
                <li><a href="<?php echo $attribute_group; ?>"><?php echo $text_attribute_group; ?></a></li>
              </ul>
            </li>
          <li><a href="<?php echo $option; ?>"><?php echo $text_option; ?></a></li>
        </ul>
      </li>
      <li id="sale"><a class="top"><?php echo $text_sale; ?></a>
        <ul>
          <li><a href="<?php echo $order; ?>"><?php echo $text_order; ?></a></li>
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
  			<div id="toggle">
				<a id="open" class="open"></a>
				<a id="close" style="display: none;" class="close"></a>			
			</div>
  <?php } ?>
</div>
