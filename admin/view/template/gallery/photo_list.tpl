<?php echo $header; ?>
<script type="text/javascript">
  var token = '<?php echo $token; ?>';
  function approvePhotos() {
    var credits = $('#nuberOfCredits').val();
    var length = $("input[class^='checkboxPhoto']:checked").length;
    if(length > 0) {
      var photo_ids = new Array();
      $("input[class^='checkboxPhoto']:checked").each(function(index, item) {
        photo_ids.push($(item).val());
      });

      var url = "<?php echo $this->url->link('gallery/admin/approvePhotos&token='. $token); ?>";
      var postdata = {
        arr : JSON.stringify(photo_ids),
        'credits' : credits
      }

      $.post(url, postdata, function(response) {
        response = $.parseJSON(response);
        if(response['success']) {
          url = "<?php echo $this->url->link('gallery/admin/&token='. $token); ?>";
          window.location = url;
        }
        else {
          alert('Error. Please try later');
        }
      });
    }
  }

  function removePhotos() {
    var confirmResult = confirm("Do you really want to delete?");
    if(confirmResult) {
      var length = $("input[class^='checkboxPhoto']:checked").length;
    if(length > 0) {
      var photo_ids = new Array();
      $("input[class^='checkboxPhoto']:checked").each(function(index, item) {
        photo_ids.push($(item).val());
      });

      var url = "<?php echo $this->url->link('gallery/admin/removePhotos&token='. $token); ?>";
      var postdata = {
        arr : JSON.stringify(photo_ids)
      }

      $.post(url, postdata, function(response) {
        response = $.parseJSON(response);
        if(response['success']) {
          url = "<?php echo $this->url->link('gallery/admin/&token='. $token); ?>";
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
      <h1><img src="view/image/product.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="flotedLeft marginLeft50 marginTop10">
        <?php echo $text_number_of_credits; ?>
        <select id="nuberOfCredits">
          <option value="0"> 0 </option>
          <option value="20"> 20 </option>
          <option value="40"> 40 </option>
          <option value="60"> 60 </option>
          <option value="80"> 80 </option>
          <option value="100"> 100  </option>
          <option value="120"> 120 </option>
          <option value="140"> 140 </option>
          <option value="160"> 160 </option>
          <option value="180"> 180 </option>
          <option value="200"> 200 </option>
        </select>
      </div>
      <div class="buttons">
          <a onclick="javascript:approvePhotos()" class="button"><?php echo $button_approve; ?></a>
          <a onclick="javascript:removePhotos();" class="button"><?php echo $button_delete; ?></a>
      </div>
    </div>
    <div class="content">
      <form action="" method="post" enctype="multipart/form-data" id="form">
        <table class="list">
          <thead>
            <tr>
              <td width="1" style="text-align: center;">
                <input type="checkbox" onclick="$('.checkboxPhoto').attr('checked', this.checked);" />
              </td>
              <td class="left" width="200">
                <?php echo $column_photo; ?>
              </td>
              <td class="left" width="200">
                <?php echo $column_user_nickname; ?>
              </td>
              <td class="left">
                <?php echo $column_photo_name; ?>
              </td>
              <td class="left">
                <?php echo $column_photo_description; ?>
              </td>
            </tr>
            <?php
              foreach ($photos as $index => $photo) {
                echo "<tr>";
                echo '<td><input type="checkbox" class="checkboxPhoto" value="' . $photo["photo_id"] . '" /></td>';
                echo '<td><img src="' . HTTP_IMAGE . $photo["path"] . '" width="200" height="150"/></td>';
                echo '<td>' . $photo["nickname"] . '</td>';
                echo '<td>' . $photo["name"] . '</td>';
                echo '<td>' . $photo["description"] . '</td>';
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