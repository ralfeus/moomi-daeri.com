<?php echo $header; ?>
<script type="text/javascript">
  
  var token = '<?php echo $token; ?>';
  function approveVotes() {
    var length = $("input[class^='checkboxVote']:checked").length;
    if(length > 0) {
      var vote_ids = new Array();
      $("input[class^='checkboxVote']:checked").each(function(index, item) {
        vote_ids.push($(item).val());
      });

      var url = "<?php echo $this->url->link('gallery/admin/approveVotes&token='. $token); ?>";
      var postdata = {
        arr : JSON.stringify(vote_ids)
      }
      
      $.post(url, postdata, function(response) {
        response = $.parseJSON(response);
        if(response['success']) {
          url = "<?php echo $this->url->link('gallery/admin/adminVote&token='. $token); ?>";
          window.location = url;
        }
        else {
          alert('Error. Please try later');
        }
      });
    }
  }

  function removeVotes() {
    var confirmResult = confirm("Do you really want to delete?");
    if(confirmResult) {
      var length = $("input[class^='checkboxVote']:checked").length;
	    if(length > 0) {
	      var vote_ids = new Array();
	      $("input[class^='checkboxVote']:checked").each(function(index, item) {
	        vote_ids.push($(item).val());
	      });

	      var url = "<?php echo $this->url->link('gallery/admin/removeVotes&token='. $token); ?>";
	      var postdata = {
	        arr : JSON.stringify(vote_ids)
	      }
	      
	      $.post(url, postdata, function(response) {
	        response = $.parseJSON(response);
	        if(response['success']) {
	          url = "<?php echo $this->url->link('gallery/admin/adminVote&token='. $token); ?>";
	          window.location = url;
	        }
	        else {
	          alert('Error. Please try later');
	        }
	      });
	    }
    }
  }
</script>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/product.png" alt="" /> <?php echo $heading_title_voting; ?></h1>
      <div class="buttons">
          <a onclick="javascript:approveVotes()" class="button"><?php echo $button_approve; ?></a>
          <a onclick="javascript:removeVotes();" class="button"><?php echo $button_delete; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td width="1" style="text-align: center;">
                <input type="checkbox" onclick="$('.checkboxVote').attr('checked', this.checked);" />
              </td>
              <td class="left" width="200">
                <?php echo $column_photo; ?>
              </td>
              <td class="left">
                <?php echo $column_photo_vote; ?>
              </td>
              <td class="left">
                <?php echo $column_photo_comment; ?>
              </td>
            </tr>
            <?php
              foreach ($votes as $index => $vote) {
              	$photoPath = '';
              	if($vote['image_path'] != '' && file_exists(DIR_IMAGE . substr($vote['image_path'], 1))) {
              		$photoPath =  HTTP_IMAGE . substr($vote['image_path'], 1);
              		$photoID = $vote['review_image_id'];
              		$photoType = 'review_image';
              	}
              	else {
              		$photoPath =  HTTP_IMAGE . $vote['path'];
              		$photoID = $vote['photo_id'];
              		$photoType = 'gallery_photo';
              	}
                echo "<tr>";
                echo '<td>';
                echo '	<input type="checkbox" class="checkboxVote" value="' . $vote['vote_id'] . '" />';
                echo '</td>';
                echo '<td><img src="' . $photoPath . '" width="150" /></td>';
                echo '<td><img src="' . HTTP_THEME_IMAGE . 'stars-' . $vote['stars'] . '.png" /></td>';
                echo '<td>' . $vote["comment"] . '</td>';
                echo "</tr>";
              }
            ?>
        </thead>
        </table>
      </form>
    </div>
  </div>
</div>
<?php echo $footer; ?>