<?php if (isset($_SERVER['HTTP_USER_AGENT']) && !strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?= $direction ?>" lang="<?= $lang ?>" xml:lang="<?= $lang ?>">
<head>
<title><?= $title ?></title>
<base href="<?= $base ?>" />
<?php if ($description) { ?>
<meta name="description" content="<?= $description ?>" />
<?php } ?>
<?php if ($keywords) { ?>
<meta name="keywords" content="<?= $keywords ?>" />
<?php } ?>
<?php if ($icon) { ?>
<link href="<?= $icon ?>" rel="icon" />
<?php } ?>
<?php foreach ($links as $link) { ?>
<link href="<?= $link['href'] ?>" rel="<?= $link['rel'] ?>" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/stylesheet.css" />
<?php foreach ($styles as $style) { ?>
<link rel="<?= $style['rel'] ?>" type="text/css" href="<?= $style['href'] ?>" media="<?= $style['media'] ?>" />
<?php } ?>

<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="http://code.jquery.com/ui/1.10.0/jquery-ui.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/external/jquery.cookie.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery.multiselect.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/jquery.multiselect.css" />
<script type="text/javascript" src="catalog/view/javascript/jquery/nailthumb/jquery.nailthumb.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/nailthumb/jquery.nailthumb.1.1.min.css" />
<link rel="stylesheet" href="catalog/view/theme/default/stylesheet/carousel/tango/skin.css" type="text/css" />
<script type="text/javascript" src="catalog/view/javascript/jquery.jcarousel.min.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/tooltip.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/wkauction.style.css" />
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/wkauction/wkallauctions.css" />
<script type="text/javascript" src="catalog/view/javascript/wkproduct_auction/countdown.js"></script>
<script type="text/javascript" src="catalog/view/javascript/wkproduct_auction/jquery.countdown.js"></script>
<script type="text/javascript" src="catalog/view/javascript/wkproduct_auction/jquery.quick.pagination.min.js"></script>
<!--[if IE]>
<script type="text/javascript" src="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4-iefix.js"></script>
<![endif]-->
<script type="text/javascript" src="catalog/view/javascript/jquery/tabs.js"></script>
<link rel="stylesheet" href="catalog/view/javascript/jquery/treeview/jquery.treeview.css" />
<script type="text/javascript" src="catalog/view/javascript/jquery/treeview/jquery.treeview.js"></script>
    <script type="text/javascript">
    	$("#navigation").treeview({
		    persist: "location",
		    collapsed: true,
		    unique: true
      });
    </script>

<script type="text/javascript">
  var warningMesssage = '<?= $text_no_select_images ?>';
</script>
<script type="text/javascript" src="catalog/view/javascript/common.js"></script>

<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/ui-lightness/jquery-ui-1.8.23.custom.css" />
<?php foreach ($scripts as $script) { ?>
<script type="text/javascript" src="<?= $script ?>"></script>
<?php } ?>
<!--[if IE 7]>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/ie7.css" />
<![endif]-->
<!--[if lt IE 7]>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/ie6.css" />
<script type="text/javascript" src="catalog/view/javascript/DD_belatedPNG_0.0.8a-min.js"></script>
<script type="text/javascript">
DD_belatedPNG.fix('#logo img');
</script>
<![endif]-->
<?= $google_analytics ?>
    <!-- Start SiteHeart code -->
    <script>
    (function(){
    var widget_id = 732840;
    _shcp =[{widget_id : widget_id}];
    var lang =(navigator.language || navigator.systemLanguage
    || navigator.userLanguage ||"en")
    .substr(0,2).toLowerCase();
    var url ="widget.siteheart.com/widget/sh/"+ widget_id +"/"+ lang +"/widget.js";
    var hcc = document.createElement("script");
    hcc.type ="text/javascript";
    hcc.async =true;
    hcc.src =("https:"== document.location.protocol ?"https":"http")
    +"://"+ url;
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(hcc, s.nextSibling);
    })();
    </script>
    <!-- End SiteHeart code -->
<!-- RedHelper --
<script id="rhlpscrtg" type="text/javascript" charset="utf-8" async="async"
        src="https://web.redhelper.ru/service/main.js?c=moomidae">
</script>
<!--/Redhelper -->
<script language="javascript">//<!--
    var actionCheckboxChecked = '';
    var holidays = [[2013,01,01,'New Years Day'],[2013,03,14,'Pongal'],[2013,02,25,'Christmas Day']];
    $(document).ready(function() {
      var url = "<?= $this->url->link('shop/admin/getAllHolidaysForCalendar') ?>";
      $.post(url, function(response) {
        response = $.parseJSON(response);
        holidays = response['holidays'];

        $("#calendar").datepicker({
          showOn: "calendar",
          //$.datepicker.regional['ru'],
          beforeShowDay: setHoliDays
        });

        $('.ui-datepicker-inline').css('width', '210px');
//        $('.ui-datepicker-inline').addClass('width210');
      });
      var currentUrl = "<?= isset($this->request->get['route']) ?  $this->request->get['route'] : ''?>";
      if(currentUrl == 'common/home' || 'information/specaction' || currentUrl == '') {
        var action_url = "<?= $this->url->link('shop/admin/hasAction') ?>";
        $.post(action_url, function(response) {
          response = $.parseJSON(response);
          if(response['result'] == 1) {
            var cookie =  getCookie('MooMiDae_action_show');
            //console.log("--------");
            //console.log(cookie);
            if(cookie == null && !cookie) {
              var fancy_url = "<?= $this->url->link('shop/admin/showAction') ?>";
              $.fancybox({
                'width'             : 420,
                'height'            : 455,
                'autoScale'         : true,
                'transitionIn'      : 'none',
                'transitionOut'     : 'none',
                'type'              : 'iframe',
                'href'              : fancy_url,
                'onClosed'          : onCloseAction
              });
            }
          }
        });
      }
    });

    function checkboxOnClick() {
      actionCheckboxChecked = $('#action_checkbox', frames[0].document).attr('checked');
      //alert(actionCheckboxChecked);
    }

    function onCloseAction() {
      if(actionCheckboxChecked == 'checked') {
        setCookie('MooMiDae_action_show', false, 1);
      }
      //alert(actionCheckboxChecked);
    }

    function getCookie(c_name) {
      var c_value = document.cookie;
      var c_start = c_value.indexOf(" " + c_name + "=");
      if (c_start == -1)
        {
        c_start = c_value.indexOf(c_name + "=");
        }
      if (c_start == -1)
        {
        c_value = null;
        }
      else
        {
        c_start = c_value.indexOf("=", c_start) + 1;
        var c_end = c_value.indexOf(";", c_start);
        if (c_end == -1)
        {
      c_end = c_value.length;
      }
      c_value = unescape(c_value.substring(c_start,c_end));
      }
      return c_value;
    }

    function setCookie(c_name, value, exdays) {
      var exdate=new Date();
      exdate.setDate(exdate.getDate() + exdays);
      var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
      document.cookie=c_name + "=" + c_value;
    }

    function setHoliDays(date) {
       for (i = 0; i < holidays.length; i++) {
         if (date.getFullYear() == holidays[i][0]
            && date.getMonth() == holidays[i][1] - 1
              && date.getDate() == holidays[i][2]) {
            return [true, 'holiday', holidays[i][3]];
         }
       }
      return [true, ''];
    }
//-->
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
		$("div.header-top").slideUp("slow");
	
	});	
	
	// Collapse Panel
	$("#close").click(function(){
		$("div.header-top").slideDown("slow");	
	});		
	
	// Switch buttons from "Log In | Register" to "Close Panel" on click
	$("#toggle a").click(function () {
		$("#toggle a").toggle();
	});		
		
});

$(function(){
 $(window).scroll(function() {
  var top = $(document).scrollTop();
  if (top > 180) {
    $('.links').addClass('linksfixed'); 
  }
  else {
    $('.links').removeClass('linksfixed');
  }
  if($(window).scrollTop()=="0"){
    $('#button-back').fadeOut("slow");
  }
  else {
    $('#button-back').fadeIn("slow");
  }
 });
});
</script>
<script>
function addBookmark() {
if (document.all) window.external.addFavorite('http://moomidae.com', 'MooMi-DaeRi - ALL from KOREA!');
}
</script>
<script>
document.write('<script src="//sharebutton.net/plugin/sharebutton.php?type=vertical&u=' + encodeURIComponent(document.location.href) + '"></scr' + 'ipt>');
</script>
<style type="text/css">
  .width180 {
    width: 180px;
  }
  .ui-datepicker td.holiday a, .ui-datepicker td.holiday a:hover {
     background: none #FFEBAF;
     border: 1px solid #BF5A0C;
   }
   .legendRect {
      width: 10px;
      height: 10px;
      border: solid 1px;
      float: left;
      margin-right: 5px;
      margin-left: 10px;
      margin-top: 3px;
    }
    .work {
      background: none #dfeffc;
      border: 1px solid #c5dbec;
    }
    .free {
      background: none #FFEBAF;
      border: 1px solid #BF5A0C;
    }
</style>

<!-- AdPacks -->
<style>
#adpacks-wrapper{font-family: Arial, Helvetica;width:280px;position: fixed;_position:absolute;bottom: 0;right: 20px;z-index: 9999;background: #eaeaea;padding: 10px;-moz-box-shadow: 0 0 15px #444;-webkit-box-shadow: 0 0 15px #444;box-shadow: 0 0 15px #444;}
body .adpacks{background:#fff;padding:15px;margin:15px 0 0;border:3px solid #eee;}
body .one .bsa_it_ad{background:transparent;border:none;font-family:inherit;padding:0;margin:0;}
body .one .bsa_it_ad .bsa_it_i{display:block;padding:0;float:left;margin:0 10px 0 0;}
body .one .bsa_it_ad .bsa_it_i img{padding:0;border:none;}
body .one .bsa_it_ad .bsa_it_t{padding: 0 0 6px 0; font-size: 11px;}
body .one .bsa_it_p{display:none;}
body #bsap_aplink,body #bsap_aplink:hover{display:block;font-size:9px;margin: -15px 0 0 0;text-align:right;}
body .one .bsa_it_ad .bsa_it_d{font-size: 11px;}
body .one{overflow: hidden}
</style>
</head>
<body>
<div class="header-top-wrapper">
  <div class="header-top">
    <div id="span-selectors">
      <form id="selectors" action="<?= $action ?>" method="post" enctype="multipart/form-data">
        <table align="center" cellpadding="0" cellspacing="0" border="0">
          <tr valign="middle">
            <td width="150"">
              <table>
                <div class="login-header"><?= (!$logged) ? $text_welcome_guest_left :$text_logged_customer_left ?></div>
                <div class="logout-header"><?= (!$logged) ? $text_welcome_guest_right :$text_logged_customer_right ?></div>
              </table>
            </td>
            <td width="400">
              <div id="search">
                <?php if ($filter_name): ?>
                  <input type="text" name="filter_name" value="<?= $filter_name ?>" />
                <?php else: ?>
                  <input type="text" name="filter_name" value="<?= $textSearchPrompt ?>" onclick="this.value = '';" onkeydown="this.style.color = '#000000';" />
                <?php endif; ?>
              </div>
              <div class="styled-select-wrapper">
                <select class="styled-select">
                  <option>All Categories</option>
                </select>
              </div>
              <div class="button-search"></div>
            </td>
            <td style="width: 180px; padding-left: 100px;">
              <table height="40" cellpadding="0" cellspacing="1">
                <tr>
                  <?php foreach ($languages as $language): ?>
                    <td style="width: 40px;text-align: center; vertical-align: middle;">
                      <input type="hidden" name="language_code" value="" />
                      <input type="hidden" name="currency_code" value="" />
                      <input type="hidden" name="redirect" value="<?php echo $redirect ?>" />
                      <div class="language-selector">
                        <a href="index.php?<?= $languagelessQuery ?>&amp;language=<?= $language['code'] ?>">
                          <img
                            src="image/flags/<?= $language['image'] ?>"
                            alt="<?= $language['name'] ?>"
                            title="<?= $language['name'] ?>"
                          />
                        </a>
                      </div>
                    </td>
                  <?php endforeach; ?>
                </tr>
              </table>
            </td>
            <td style="width: 128px;">
              <table height="20" cellpadding="0" cellspacing="1">
                <tr>
                  <?php foreach ($currencies as $currency): ?>
                    <td class="currency-selector">
                      <?php
                        if ($currency['code'] == $currency_code):
                          $boldBegin = "<b>"; $boldEnd = "</b>";
                          $onClick = "";
                        else:
                          $boldBegin = ""; $boldEnd = "";
                          $onClick = 'onclick="changeCurrency(\'' . $currency['code'] . '\')"';
                        endif;
                        $symbol = $currency['symbol_left'] ? $currency['symbol_left'] : $currency['symbol_right'];
                      ?>
                      <a title="<?= $currency['title'] ?>" <?= $onClick ?>>
                        <img 
                          src="image/currency/<?php echo $currency['code'] ?>.png"
                          alt="<?= $currency['title'] ?>"
                          title="<?= $currency['title'] ?>"
                        />
                      </a>
                    </td>
                  <?php endforeach; ?>
                </tr>
              </table>
            </td>
            <td width="150" align="center">
              <table>
                <div class="contact-header">Contact:<br />
                  <a href="mailto:moomidae@gmail.com">moomidae@gmail.com</a>
                </div>
              </table>
            </td>
          </tr>
        </table>
      </form>
    </div>	
	</div>
	<div id="toggle">
    <a id="open" class="open"></a>
    <a id="close" style="display: none;" class="close"></a>	
  </div>
  <!-- The tab on top -->	
	<div class="header-top-tab-wrapper"></div>
  <div class="header-top-tab">
		<div class="login">
	    	<div class="left">&nbsp;</div>
            <div><?= (!$logged) ? $text_welcome_guest_left :$text_logged_customer_left ?></div>
	    	<div class="right">&nbsp;</div>
		</div> 
	</div> <!-- / top -->
  <div id="hello-top" class="bottom">
    <?= (!$logged) ? $text_welcome_help :$text_logged_help ?></div>
  </div>
<div id="container">
  <div id="cart-success"></div>
  <a id="toTop" title="<?= $text_totop ?>"></a>
  <a id="button-back" title="<?= $text_back ?>" onclick="javascript:history.back();"></a>
  <div id="header"
    <?php if ($logo): ?>
      style="background: url('<?php echo $logo; ?>');"
    <?php endif; ?>
  >
    <a href="<?= $home ?>" title="<?= $text_home ?>"></a>
    <!--  <div id="welcome"><?= (!$logged) ? $text_welcome :$text_logged ?></div>-->
    <a class="bookmark" href="javascript:void(0);" title="<?= $text_favorites ?>" onclick="var url=window.document.location; var title=window.document.title; function bookmark(a) {a.href = url; a.rel = 'sidebar'; a.title = title; return true;} bookmark(this); window.external.AddFavorite(location.href,document.title); return false;"></a>
    <div class="links">
      <a class="specaction" href="index.php?route=information/specaction"></a>
      <a class="agent" href="index.php?route=product/repurchase"><span><?= $text_repurchase_order ?></span></a>
      <a class="info" href="index.php?route=shop/admin/showPage&page_id=15"><span><?= $textShoppingGuide ?></span></a>        
      <!--		  <a class="account" href="<?= $account ?>"><span><?= $text_account ?></span></a> -->
      <a class="wishlist_total" href="<?= $wishlist ?>" id="wishlist_total"><span><?= $text_wishlist ?></span></a>
		  <a class="cart" href="<?= $cart ?>" id="cart_total_data"><span><?= $text_cart ?></span></a>
				  
<!--          <table style="display: none;">
              <tr><td>
                                   
                  <a class="button" href="<?= $checkout ?>"><span><?= $text_checkout ?></span></a>
                  <a class="button" href="<?= $repurchase_order ?>"><span><?= $text_repurchase_order ?></span></a>
              </td></tr>
              <tr><td>
                  <a class="button" href="<?= $urlGallery ?>"><span><?= $textGallery ?></span></a>
                  <a class="buttonPink" href="<?= $urlShoppingGuide ?>"><span><?= $textShoppingGuide ?></span></a>
              </td></tr>
          </table>-->
    </div>
  </div>
  <div id="content_left" style="float: left; width: 1000px;">
  <div id="notification"></div>
  <script type="text/javascript">//<!--
  function changeCurrency(code)
  {
    $('input[name=currency_code]').attr('value', code);
    $('#selectors').submit();
  }

  function changeLanguage(code)
  {
    $('input[name=language_code]').attr('value', code);
    $('#selectors').submit();
  }
  function showHelloTopOn() {
    $('#hello-top').show();
  }
  function showHelloTopOff() {
    $('#hello-top').hide();
  }
//--></script>
