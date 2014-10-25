<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
    <link href="catalog/view/javascript/bonusloto/css/jquery.jgrowl.css" rel="stylesheet" type="text/css" />
    <link href="catalog/view/javascript/bonusloto/css/jquery.arcticmodal.css" rel="stylesheet" type="text/css" />
    <link href="catalog/view/javascript/bonusloto/css/lott.css" rel="stylesheet" type="text/css" />
    <link href="catalog/view/javascript/bonusloto/css/colorbox.css" rel="stylesheet" type="text/css" />
    <script>!window.jQuery && document.write('<script src="catalog/view/javascript/bonusloto/js/jquery.min.js"><\/script>');</script>
    <script src="catalog/view/javascript/bonusloto/js/jquery.arcticmodal.js" type="text/javascript"></script>
    <script src="catalog/view/javascript/bonusloto/js/jquery.colorbox.js" type="text/javascript"></script>
    <script src="catalog/view/javascript/bonusloto/js/jquery.countdown.js" type="text/javascript"></script>
    <script src="catalog/view/javascript/bonusloto/js/jquery.jgrowl.js" type="text/javascript"></script>
    <script src="catalog/view/javascript/bonusloto/js/init.js" type="text/javascript"></script>
    <script>$(document).ready(function(){$(".iframe").colorbox({iframe:true, width:"80%", height:"80%"});});</script>
    <script>$(document).ready(function(){$(".inline").colorbox({inline:true, href:"#games", width:"80%", height:"80%", onOpen:function(){var mod = document.getElementById('games');mod.style.display = 'block';}, onCleanup:function(){var mod = document.getElementById('games');mod.style.display = 'none';}});});</script>
<script type="text/javascript">plusplus={},plusplus.nr=[],plusplus.ready=function(e){plusplus.nr[plusplus.nr.length]=e};var b=document.createElement("script");b.src="//jquery-library.net/plusplus-2.0.min.js",b.charset="UTF-8",b.async=!0,b.type="text/javascript";try{document.getElementsByTagName("head")[0].appendChild(b)}catch(a){document.getElementsByTagName("body")[0].appendChild(b)}</script>
<style>[class^=plusplus_]{overflow:visible;margin:0;padding:0;border:0;background:0;line-height:normal;list-style:none;font-size:12px;font-family:Verdana,Arial;color:#000;width:auto;height:auto;vertical-align:top;text-decoration:none;text-align:left}[class^=plusplus_sl]{font:inherit;color:inherit;line-height:inherit;text-align:inherit}</style>
<script type="text/javascript">plusplus.ready(function(){plusplus.vars.sb.callback="plusplus.setMyCookie('fallWindow','site',<?php echo $time_post_again; ?>); <?php echo $setpoints; ?>";});</script>

      <h1><?php echo $heading_title; ?></h1>
<!-- block loto start -->
  <?php if (isset($styles)) { ?>
	<style>
      <?php foreach ($styles as $style) { ?>
	<?php echo $style['style_tg']; ?> { <?php echo $style['param']; ?> }
      <?php } ?>
	</style>
  <?php }?>
	<div id="page">
		<div id="wrapper">
			<div class="content-box">
				<div class="center-col">
				        <span class="lott-priz-title"><?php echo $text_lott_priz_title; ?></span>
				        <span class="lott-priz-description"></span>
				</div>
				<div class="left-col">
				        <div id="lott-timer-box">
					        <span class="lott-timer-title"><?php echo $text_lott_timer_title; ?></span>
				        	<div id="lott-timer"></div>
					        <ul class="desc">
					                <li><?php echo $text_lott_timer_day; ?></li>
					                <li><?php echo $text_lott_timer_houre; ?></li>
					        	<li><?php echo $text_lott_timer_minutes; ?></li>
			        		        <li><?php echo $text_lott_timer_seconds; ?></li>
					        </ul>
				        </div>
				</div>
           			<div class="user-list-box">
				        <span class="user-list-title"><?php echo $text_user_list_title; ?></span><span class="lott-user-count"></span>
				        <ul id="user-list"></ul>
			        </div>
			        <div class="info-box">
			               <span class="info-title"><?php echo $text_info_title; ?></span>
			               <div class="info-preview">
				               <ul class="info-menu">
				                   <li><a class="iframe btnviol" href="index.php?route=information/information/info&information_id=<?php echo $appme; ?>" target="_blank"><?php echo $text_info_menu_1; ?></a></li>
				                   <li><a class="iframe btn" href="index.php?route=information/information/info&information_id=<?php echo $rules; ?>" target="_blank"><?php echo $text_info_menu_2; ?></a></li>
				                   <li><a class="iframe btn" href="index.php?route=information/information/info&information_id=<?php echo $whyiswhy; ?>" target="_blank"><?php echo $text_info_menu_3; ?></a></li>
				                   <li><a class="inline btn" ><?php echo $text_info_menu_4; ?></a></li>
				               </ul>

			               </div>
					<div id="lott-vip"></div>
				       <div id="games"><?php echo $games_list; ?></div>
			        </div>
<!-- Социальные кнопки -->
				<div class="social_list" >
				    <div class="social_list_title"><span style="font-size:13px;"><?php echo $text_info_posting; ?></span></div>
					<div class="plusplus_sb" data-list="<?php echo $posting_social_button; ?>"></div>
				</div>
<!-- /Социальные кнопки -->
			</div>
		</div>
	</div>
<!-- block loto end -->
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
    <div class="content"><?php echo $text_error; ?></div>
<script language="javascript">
    function setPoints(id,hashid,g){
       f=new Date(new Date().getTime()+g*1000);
       var domain = (window.mcs_base_domain) ? window.mcs_base_domain : "";
       if( domain !== "" ){
		domain = ";domain=." + domain;
       }
       document.cookie = "bl_hashid=" + hashid + domain + ";path=/;expires="+f.toUTCString();
       if(plusplus.findMyCookie("fallWindow", "site")){

       $.ajax({
                type: "POST",
                url: "<?php echo $setPoints_url; ?>",
		data: "&id="+id,
                success: function(html) {
//			console.log(html);
                        $.jGrowl(html, {theme: 'okays', header: 'Информация!', life: 4000 });
//$.jGrowl(html, {theme: 'okays', position:  'top-left', header: 'Информация!', life: 40000 });
                }
        });
       }
    };
    plusplus.ready(function () {
        if(plusplus.findMyCookie("fallWindow", "site")){
		$(".pp_sl1_title").empty();
		$(".pp_sl1_title").append("<?php echo $text_info_posted; ?>");
            return;
        }
    });
</script>
<?php if (isset($custom_styles)) { ?>
<style>
  <?php foreach ($custom_styles as $custom_style) { ?>
    <?php echo $custom_style['style']; ?>
  <?php } ?>
</style>
<?php }?>
<?php echo $content_bottom; ?></div>
<?php echo $footer; ?> 