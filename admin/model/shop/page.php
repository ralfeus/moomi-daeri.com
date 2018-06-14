<?php
class ModelShopPage extends \system\engine\Model {
  public function getAllPages() {
    $query = "SELECT * FROM page";
    $result = $this->db->query($query);
    return $result->rows;
  }

  public function getPages($data) {
    $query = "SELECT * FROM page ORDER BY " . $data['sort'] . " " . $data['order'] . " LIMIT " . $data['start'] . ", " . $data['limit'];
    $result = $this->db->query($query);
    return $result->rows;
  }

  public function getPage($page_id) {
    $query = "SELECT * FROM page WHERE page_id = " . $page_id;
    $result = $this->db->query($query);
    return $result->row;
  }

  public function editPage($page_id, $data) {
    $parent_id = isset($data['parent']) ? $data['parent'] : 'NULL';
    $parent_page_order = isset($data['parent_order']) ? $data['parent_order'] : 'NULL';
    $query = "UPDATE page SET parent_page_id = '".$parent_id."', parent_page_order = '".$parent_page_order."', page_name_en = '" . $this->db->escape($data['title']['en']) . "', page_content_en = '" . $this->db->escape($data['content']['en']) . "', page_name_ru = '" . $this->db->escape($data['title']['ru']) . "', page_content_ru = '" . $this->db->escape($data['content']['ru']) . "', page_name_jp = '" . $this->db->escape($data['title']['jp']) . "', page_content_jp = '" . $this->db->escape($data['content']['jp']) . "' WHERE page_id = " . $page_id;
    //print_r($query); die();
    $result = $this->db->query($query);
  }

  public function addPage($data) {
    $parent_id = isset($data['parent']) ? $data['parent'] : 'NULL';
    $parent_page_order = isset($data['parent_order']) ? $data['parent_order'] : 'NULL';
    $query = "INSERT INTO page(parent_page_id, parent_page_order, page_name_en, page_content_en, page_name_ru, page_content_ru, page_name_jp, page_content_jp) VALUES('".$parent_id."', '".$parent_page_order."', '" . $this->db->escape($data['title']['en']) . "', '" . $this->db->escape($data['content']['en']) . "', '" . $this->db->escape($data['title']['ru']) . "', '" . $this->db->escape($data['content']['ru']) . "', '" . $this->db->escape($data['title']['jp']) . "', '" . $this->db->escape($data['content']['jp']) . "')";
    $result = $this->db->query($query);
  }
}
?>