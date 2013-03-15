<?php echo $header; ?>
<div id="content_page"><?php echo $content_top; ?>
  <!-- <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div> -->
  <?php
    if($page_id != '') {
      if($pages[0]['parent_page_id'] != '' && $pages[0]['parent_page_id'] != 0) {
        echo '<a href="' . $this->url->link('shop/admin/showPage', 'page_id=' . $pages[0]['parent_page_id']) . '"> <<< ' . $pages[0]['parent_page_title'] . '</a>';
      }
      else {
        echo '<a href="' . $this->url->link('shop/admin/showPage') . '"> <<< ' . $text_back_to_root . '</a>';
      }
    }

  ?>
  <h1><?php echo $heading_title; ?></h1>
  <?php
    if($page_id == '') {
      echo '<ul>';
      foreach ($pages as $page) {
        echo '<li>';
        echo '<a href="' . $this->url->link('shop/admin/showPage', 'page_id=' . $page['page_id']) . '">' . $page['page_title'] . '</a>';
        echo '</li>';
      }
      echo '</ul>';
    }
    else {
      if(count($children) > 0) {
        echo '<ul>';
        foreach ($children as $child) {
          echo '<li>';
          echo '<a href="' . $this->url->link('shop/admin/showPage', 'page_id=' . $child['page_id']) . '">' . $child['page_title'] . '</a>';
          echo '</li>';
        }
        echo '</ul>';
      }
      echo '<div class="cnt_page">';
      echo $pages[0]['page_content'];
      echo '</div>';
    }
  ?>

</div>
<?php echo $footer; ?>