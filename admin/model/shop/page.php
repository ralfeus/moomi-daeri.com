<?php
class ModelShopPage extends Model {
  public function getAllPages() {
    $query = "SELECT * FROM " . DB_PREFIX . "page";
    $result = $this->db->query($query);
    return $result->rows;
  }

  public function getPages($data) {
    $query = "SELECT * FROM " . DB_PREFIX . "page ORDER BY " . $data['sort'] . " " . $data['order'] . " LIMIT " . $data['start'] . ", " . $data['limit'];
    $result = $this->db->query($query);
    return $result->rows;
  }

  public function getPage($page_id) {
    $query = "SELECT * FROM " . DB_PREFIX . "page WHERE page_id = " . $page_id;
    $result = $this->db->query($query);
    return $result->row;
  }

  public function editPage($page_id, $data) {
    $parent_id = isset($data['parent']) ? $data['parent'] : 'NULL';
    $parent_page_order = isset($data['parent_order']) ? $data['parent_order'] : 'NULL';
    $query = "UPDATE " . DB_PREFIX . "page SET parent_page_id = '".$parent_id."', parent_page_order = '".$parent_page_order."', page_name_en = '".$data['title']['en']."', page_content_en = '".$data['content']['en']."', page_name_ru = '".$data['title']['ru']."', page_content_ru = '".$data['content']['ru']."', page_name_jp = '".$data['title']['jp']."', page_content_jp = '".$data['content']['jp']."' WHERE page_id = " . $page_id;
    //print_r($query); die();
    $result = $this->db->query($query);
  }

  public function addPage($data) {
    $parent_id = isset($data['parent']) ? $data['parent'] : 'NULL';
    $parent_page_order = isset($data['parent_order']) ? $data['parent_order'] : 'NULL';
    $query = "INSERT INTO " . DB_PREFIX . "page(parent_page_id, parent_page_order, page_name_en, page_content_en, page_name_ru, page_content_ru, page_name_jp, page_content_jp) VALUES('".$parent_id."', '".$parent_page_order."', '".$data['title']['en']."', '".$data['content']['en']."', '".$data['title']['ru']."', '".$data['content']['ru']."', '".$data['title']['jp']."', '".$data['content']['jp']."')";
    $result = $this->db->query($query);
  }
}
?>