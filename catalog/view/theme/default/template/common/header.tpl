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
<!--[if IE]>
<script type="text/javascript" src="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4-iefix.js"></script>
<![endif]-->
<script type="text/javascript" src="catalog/view/javascript/jquery/tabs.js"></script>
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
<?php
  echo $google_analytics;

  function unset_query_string_var($varname,$query_string) {
    $query_array = array();
    parse_str($query_string,$query_array);
    unset($query_array[$varname]);
    $query_string = http_build_query($query_array);
    return $query_string;
  }

?>
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
    var holiDays = [[2013,01,01,'New Years Day'],[2013,03,14,'Pongal'],[2013,02,25,'Christmas Day']];
    $(document).ready(function() {
      var url = "<?= $this->url->link('shop/admin/getAllHolidaysForCalendar') ?>";
      $.post(url, function(response) {
        response = $.parseJSON(response);
        holiDays = response['holidays'];

        $("#calendar").datepicker({
          showOn: "calendar",
          //$.datepicker.regional['ru'],
          beforeShowDay: setHoliDays
        });

        //$('.ui-datepicker-inline').css('width', '180px');
        $('.ui-datepicker-inline').addClass('width180');
      });
      var currentUrl = "<?= $_GET['route'] ?>";
      if(currentUrl == 'common/home' || currentUrl == '') {
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
       for (i = 0; i < holiDays.length; i++) {
         if (date.getFullYear() == holiDays[i][0]
            && date.getMonth() == holiDays[i][1] - 1
              && date.getDate() == holiDays[i][2]) {
            return [true, 'holiday', holiDays[i][3]];
         }
       }
      return [true, ''];
    }
//-->
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
</head>
<body>
<div class="header-top-wrapper">
	<div class="header-top">
<div id="span-selectors">
                          <form id="selectors" action="<?= $action ?>" method="post" enctype="multipart/form-data">
                              <table width="100%" cellpadding="0" cellspacing="0" border="0">
                                  <tr>

								  <td width="128">
								  <table height="20" cellpadding="0" cellspacing="1"><tr>
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
                                                  <?= $boldBegin ?><?= $symbol ?> <?= $boldEnd ?>
                                              </a>
                                          </td>
  <?php endforeach; ?>
</tr></table></td>
  
  <td>
      <div id="search">
          <div class="button-search"></div>
<?php if ($filter_name): ?>
          <input type="text" name="filter_name" value="<?= $filter_name ?>" />
<?php else: ?>
          <input type="text" name="filter_name" value="<?/*= $text_search */?>" onclick="this.value = '';" onkeydown="this.style.color = '#000000';" />
<?php endif; ?>
		<div class="styled-select-wrapper">
		<select class="styled-select">
		  <option>All Categories</option>
		  <option>All Categories</option>
		</select>
		</div>
      </div>   
  <td>
  
  

  <?php foreach ($languages as $language): ?>
                                          <td style="width: 40px;text-align: center; vertical-align: middle;">
                                            <input type="hidden" name="language_code" value="" />
                                            <input type="hidden" name="currency_code" value="" />
                                            <input type="hidden" name="redirect" value="<?php echo $redirect ?>" />
                                              <div class="language-selector">
                                                <a href="index.php?<?= unset_query_string_var('language', $_SERVER['QUERY_STRING']) ?>&amp;language=<?= $language['code'] ?>">
                                                  <img
                                                      src="image/flags/<?php echo $language['image'] ?>"
                                                      alt="<?php echo $language['name'] ?>"
                                                      title="<?= $language['name'] ?>" />
                                                </a>
                                              </div>
                                          </td>
  <?php endforeach; ?>  
<td width="210"><div class="contact-header">Contact:<br />moomidae@gmail.com</div><td>
                                  </tr>
                              </table>
                              <!--<input type="hidden" name="language_code" value="" />
                              <input type="hidden" name="currency_code" value="" />
                              <input type="hidden" name="redirect" value="<?php echo $redirect ?>" />-->
                          </form>
                      </div>	
	</div>
</div>

<div id="container">
  <div id="header"
<?php if ($logo): ?>
      style="background: url('<?php echo $logo; ?>');"
<?php endif; ?>
      >
	  
      <table style="display: none;width: 100%; height: 150px;">
          <tbody>
              <!--<tr>
                <td colspan="2" style=""></td>
              </tr>-->
              <tr>
                  <td style="width: 100%;"></td>
                  <td style="vertical-align: bottom;">
                      <div id="span-selectors">
                          <form id="selectors" action="<?= $action ?>" method="post" enctype="multipart/form-data">
                              <table style="border: 4px solid #40db4b">
                                  <tbody>
                                      <tr>
  <?php foreach ($languages as $language): ?>
                                          <td style="text-align: center; vertical-align: middle;">
                                            <input type="hidden" name="language_code" value="" />
                                            <input type="hidden" name="currency_code" value="" />
                                            <input type="hidden" name="redirect" value="<?php echo $redirect ?>" />
                                              <div class="language-selector">
                                                <a href="index.php?<?= unset_query_string_var('language', $_SERVER['QUERY_STRING']) ?>&amp;language=<?= $language['code'] ?>">
                                                  <img
                                                      src="image/flags/<?php echo $language['image'] ?>"
                                                      alt="<?php echo $language['name'] ?>"
                                                      title="<?= $language['name'] ?>" />
                                                </a>
                                              </div>
                                          </td>
  <?php endforeach; ?>
                                      </tr>
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
                                                  <?= $boldBegin ?><?= $symbol ?> <?= $boldEnd ?>
                                              </a>
                                          </td>
  <?php endforeach; ?>
                                      </tr>
                                  </tbody>
                              </table>
                              <!--<input type="hidden" name="language_code" value="" />
                              <input type="hidden" name="currency_code" value="" />
                              <input type="hidden" name="redirect" value="<?php echo $redirect ?>" />-->
                          </form>
                      </div>
                  </td>
              </tr>
          </tbody>
      </table>
      <div id="cart">
          <div class="heading">
              <h4><?= $text_cart ?></h4>
              <a><span id="cart_total"><?= $text_items ?></span></a>
          </div>
          <div class="content"></div>
      </div>

      <div id="welcome"><?= (!$logged) ? $text_welcome :$text_logged ?></div>
      <div class="links">
	  			  <a class="account" href="<?= $account ?>"><span><?= $text_account ?></span></a> 
				  
                  <a class="home" href="<?= $home ?>"><span><?= $text_home ?></span></a>
                  <a class="wishlist_total" href="<?= $wishlist ?>" id="wishlist_total"><span><?= $text_wishlist ?></span></a>
				  <a class="cart" href="<?= $cart ?>" id="cart_total_data>"<span><?= $text_cart ?></span></a>
				  
          <table style="display: none;">
              <tr><td>
                                   
                  <a class="button" href="<?= $checkout ?>"><span><?= $text_checkout ?></span></a>
                  <a class="button" href="<?= $repurchase_order ?>"><span><?= $text_repurchase_order ?></span></a>
              </td></tr>
              <tr><td>
                  <a class="button" href="<?= $urlGallery ?>"><span><?= $textGallery ?></span></a>
                  <a class="buttonPink" href="<?= $urlShoppingGuide ?>"><span><?= $textShoppingGuide ?></span></a>
              </td></tr>
          </table>
      </div>
  </div>
<?php /*if ($categories) { ?>
  <div id="menu">
    <ul>
      <?php foreach ($categories as $category) { ?>
      <li><?php if ($category['active']) { ?>
  	<a href="<?= $category['href'] ?>" class="active"><?= $category['name'] ?></a>
  	<?php } else { ?>
  	<a href="<?= $category['href'] ?>"><?= $category['name'] ?></a>
  	<?php } ?>

        <?php if ($category['children']) { ?>
        <div>
          <?php for ($i = 0; $i < count($category['children']);) { ?>
          <ul>
            <?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
            <?php for (; $i < $j; $i++) { ?>
            <?php if (isset($category['children'][$i])) { ?>
            <li><a href="<?= $category['children'][$i]['href'] ?>"><?= $category['children'][$i]['name'] ?></a></li>
            <?php } ?>
            <?php } ?>
          </ul>
          <?php } ?>
        </div>
        <?php } ?>
      </li>
      <?php } ?>
    </ul>
  </div>
<?php }*/ ?>
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
//--></script>