<?= $header ?><?= $column_left ?><?= $column_right ?>
<script type="text/javascript">
  jQuery(document).ready(function() {
    jQuery('.nailthumb-container').nailthumb({width:150,height:150});
    var marginRight = 200;
    $("a.iframe").each(function(index, value) {
      var iframeType = $(this).attr('iframe-type');
      var width = 0;
      var height = 0;
      if(iframeType == 'horizont') {
        width = parseInt(840);
        height = parseInt(480);
      }
      else{
        width = parseInt(680);
        height = parseInt(640);
      }
      $(this).fancybox({
        'autoScale' : false,
        'transitionIn' : 'none',
        'transitionOut' : 'none',
        'type' : 'iframe',
        'width' : width,
        'height' : height,
        'overlayShow' : true,
        'margin' : 0,
        'padding' : 0,
        onComplete : function() {
          var position = $('#fancybox-right').position();
          mRight = parseInt($('#fancybox-right').css('margin-right'));
          if(mRight == 0) {
            $('#fancybox-right').css('margin-right', marginRight + 'px');
          }
        }
      });
    });
  });
</script>
<div id="content">
    <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb): ?>
        <?= $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php endforeach; ?>
    </div>
    <table width="100%">
    	<tr>
    		<td width="300">
    			<h1><?= $heading_title ?></h1>
    		</td>
        <?php if ($this->customer->isLogged()) : ?>
        <td>
          <p class="marginBottom20">
            <?php echo $text_gifts_for_photos; ?>
          </p>
        </td>
    		<td align="right">
    			<p class="marginBottom20">
            <a class="button" href="<?= $this->url->link('gallery/photo/addPhoto') ?>">
              <span>
                <?= $gallery_add_photo ?>
              </span>
            </a>
          </p>
    		</td>
      <?php endif; ?>
    	</tr>
    </table>
    <div class="content">

<?php
  if(isset($images) && count($images) >0) {
?>
    <table>
<?php
    $imgRows = intval(count($images) / GALLERY_COLS);
    for ($i = 0; $i <= $imgRows; $i++) {
      if($i == $imgRows) {
        $endCol = count($images) - $imgRows * GALLERY_COLS;
      }
      else {
        $endCol = GALLERY_COLS;
      }
      echo '<tr>';
      for ($j = 0; $j < $endCol; $j++) {
        $currentIndex = $i*GALLERY_COLS + $j;
        $imagePath = $images[$currentIndex]['path'];
        $iframeType = $images[$currentIndex]['iframe_type'];
        $avgVote = isset($images[$currentIndex]['avg_vote']) ? $images[$currentIndex]['avg_vote'] : '';
        echo '<td style="vertical-align: top;">';
        echo '  <a class="iframe" rel="gallery1" data-width="640" data-height="480" iframe-type="' . $iframeType . '" href="' . $this->url->link('gallery/photo/showLargePhoto&photo_id=' . $images[$currentIndex]['photo_id'] . '&photo_type=' . $images[$currentIndex]['photo_type']) . '">';
        echo '    <div class="nailthumb-container">';
        echo '      <img src="' . $imagePath . '" width="150" alt="" />';
        echo '    </div>';
        echo '  </a>';
        if($avgVote != '') {
          $avgVote = round($avgVote);
          echo '  <img src="' . HTTP_THEME_IMAGE . 'stars-' . $avgVote . '.png" />';
        }
        echo '</td>';
      }
      echo '</tr>';
    }
?>

    </table>
<?php
  }
?>
      <div class="pagination"><?php echo $pagination; ?></div>
    </div>
</div>
<?= $footer ?>