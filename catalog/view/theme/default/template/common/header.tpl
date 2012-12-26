<?php if (isset($_SERVER['HTTP_USER_AGENT']) && !strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6')) echo '<?xml version="1.0" encoding="UTF-8"?>'. "\n"; ?>
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
<?php if ($icon) { ?>
<link href="<?php echo $icon; ?>" rel="icon" />
<?php } ?>
<?php foreach ($links as $link) { ?>
<link href="<?php echo $link['href']; ?>" rel="<?php echo $link['rel']; ?>" />
<?php } ?>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/stylesheet.css" />
<?php foreach ($styles as $style) { ?>
<link rel="<?php echo $style['rel']; ?>" type="text/css" href="<?php echo $style['href']; ?>" media="<?php echo $style['media']; ?>" />
<?php } ?>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/jquery-ui-1.8.16.custom.min.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/ui/themes/ui-lightness/jquery-ui-1.8.16.custom.css" />
<script type="text/javascript" src="catalog/view/javascript/jquery/ui/external/jquery.cookie.js"></script>
<script type="text/javascript" src="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery.multiselect.js"></script>
<link rel="stylesheet" type="text/css" href="catalog/view/theme/default/stylesheet/jquery.multiselect.css" />
<!--[if IE]>
<script type="text/javascript" src="catalog/view/javascript/jquery/fancybox/jquery.fancybox-1.3.4-iefix.js"></script>
<![endif]--> 
<script type="text/javascript" src="catalog/view/javascript/jquery/tabs.js"></script>
<script type="text/javascript" src="catalog/view/javascript/common.js"></script>
<?php foreach ($scripts as $script) { ?>
<script type="text/javascript" src="<?php echo $script; ?>"></script>
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
<?php echo $google_analytics; ?>
<!-- BEGIN JIVOSITE CODE {literal} -->
<script type="text/javascript">
(function() {
    var widget_id = '15225';
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    s.src = '//code.jivosite.com/script/widget/'+widget_id;
    var ss = document.getElementsByTagName('script')[0];
    ss.parentNode.insertBefore(s, ss);
})();
</script>
<!-- {/literal} END JIVOSITE CODE -->
</head>
<body>
<div id="container">
<div id="header"
    <?php if ($logo): ?>
    style="background: url('<?= $logo ?>')"
    <?php endif; ?>
    >
    <table style="width: 100%; height: 150px;">
        <tbody>
            <tr>
                <td style="width: 100%;"></td>
                <td style="vertical-align: bottom;">
                    <span id="span-selectors">
                        <form id="selectors" action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
                            <table>
                                <tbody>
                                    <tr>
<?php foreach ($languages as $language): ?>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <div class="language-selector">
                                                <img
                                                    src="image/flags/<?= $language['image'] ?>"
                                                    alt="<?= $language['name'] ?>"
                                                    title="<?= $language['name']; ?>"
                                                    onclick="changeLanguage('<?= $language['code'] ?>')" />
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
                            <input type="hidden" name="language_code" value="" />
                            <input type="hidden" name="currency_code" value="" />
                            <input type="hidden" name="redirect" value="<?= $redirect ?>" />
                        </form>
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
    <div id="cart">
        <div class="heading">
            <h4><?php echo $text_cart; ?></h4>
            <a><span id="cart_total"><?php echo $text_items; ?></span></a>
        </div>
        <div class="content"></div>
    </div>
    <div id="search">
        <div class="button-search"></div>
        <?php if ($filter_name) { ?>
        <input type="text" name="filter_name" value="<?php echo $filter_name; ?>" />
        <?php } else { ?>
        <input type="text" name="filter_name" value="<?php echo $text_search; ?>" onclick="this.value = '';" onkeydown="this.style.color = '#000000';" />
        <?php } ?>
    </div>
    <div id="welcome">
        <?php if (!$logged) { ?>
        <?php echo $text_welcome; ?>
        <?php } else { ?>
        <?php echo $text_logged; ?>
        <?php } ?>
    </div>
    <div class="links">
        <a class="button" href="<?php echo $home; ?>"><span><?php echo $text_home; ?></span></a>
        <a class="button" href="<?php echo $wishlist; ?>" id="wishlist_total"><span><?php echo $text_wishlist; ?></span></a>
        <a class="button" href="<?php echo $account; ?>"><span><?php echo $text_account; ?></span></a>
        <a class="button" href="<?php echo $cart; ?>"><span><?php echo $text_cart; ?></span></a>
        <a class="button" href="<?php echo $checkout; ?>"><span><?php echo $text_checkout; ?></span></a>
        <a class="button" href="<?php echo $repurchase_order; ?>"><span><?php echo $text_repurchase_order; ?></span></a><br >        <a class="button" href="<?= $urlGallery ?>"><span><?= $textGallery ?></span></a>
    </div>
</div>
<?php if ($categories) { ?>
<div id="menu">
  <ul>
    <?php foreach ($categories as $category) { ?>
    <li><?php if ($category['active']) { ?>
	<a href="<?php echo $category['href']; ?>" class="active"><?php echo $category['name']; ?></a>
	<?php } else { ?>
	<a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
	<?php } ?>

      <?php if ($category['children']) { ?>
      <div>
        <?php for ($i = 0; $i < count($category['children']);) { ?>
        <ul>
          <?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
          <?php for (; $i < $j; $i++) { ?>
          <?php if (isset($category['children'][$i])) { ?>
          <li><a href="<?php echo $category['children'][$i]['href']; ?>"><?php echo $category['children'][$i]['name']; ?></a></li>
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
<?php } ?>
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