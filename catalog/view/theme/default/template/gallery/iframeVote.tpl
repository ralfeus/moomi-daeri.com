<style type="text/css">
	body {
		margin: 0;
		background-color: #575757;
		color: #FFFFFF;
	}
	form {
		margin: 0;
	}
</style>
<script type="text/javascript" src="catalog/view/javascript/jquery/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="catalog/view/javascript/smart_stars.js"></script>
<script type='text/javascript'>
	jQuery(document).ready(function() {
		SmartStars.init('stars1', document.getElementById('f1').t1, 1, 5, 'catalog/view/theme/default/image/star1.gif', 'catalog/view/theme/default/image/star2.gif');

		if(getCookie("moomedaeri_photo_" + "<?= $photo_id ?>") == 1 && getCookie("photo_type_" + "<?= $photo_id ?>") == '<?= $photo_type ?>') {
      setMessage("<?= $message_vote_success; ?>");
    }
	});
	function sendVote() {
		var stars = $('#t1').val();
		var comment = $('#comment').val();
		var postdata = {
			'photoID' : <?= $photo_id ?>,
			'photoType' : '<?= $photo_type ?>', 
			'stars' : stars,
			'comment' : comment
		}
		var url = "<?php echo $this->url->link('gallery/photo/addVote'); ?>";
		$.post(url, postdata, function(response) {
			response = $.parseJSON(response);
			if(response['success']) {
				document.cookie = "moomedaeri_photo_"+response['photo_id']+"=1";
				document.cookie = "photo_type_"+response['photo_id']+"="+response['photo_type'];
				setMessage(response['message']);
			}
		});
	}

	function getCookie(c_name) {
    var i,x,y,ARRcookies=document.cookie.split(";");
    for (i=0;i<ARRcookies.length;i++) {
      x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
      y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
      x=x.replace(/^\s+|\s+$/g,"");
      if (x==c_name) {
        return unescape(y);
      }
    }
  }

  function setMessage(message) {
    $('#f1').html(message);
  }

</script>
<table cellspacing="0", cellpadding="0">
	<tr>
		<td>
				<img src="<?= $image ?>" width="<?= $image_width ?>" height="<?= $image_height ?>" />
		</td>
		<td style="width: 200px; padding: 5px">
			<?php
				if($photo_name != '') {
					echo $text_photo_name.": " . $photo_name."<br /><br />";
				}
				if($photo_description != '') {
					echo $text_photo_description.": " . $photo_description."<br /><br />";
				}
			?>
			<form id="f1"> 
				<span id="stars1"></span><br />
				<input type="hidden" id="t1" name="stars" />
				Comment:
				<textarea id="comment" style="height: 60px;"></textarea><br />
				<input type="button" value="Send" onclick="javascript:sendVote()" />
			</form>
		</td>
	<tr>
</table>


