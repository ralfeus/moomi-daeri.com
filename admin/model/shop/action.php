<?php
class ModelShopAction extends Model {
  public function getAllActions() {
    $query = "SELECT * FROM " . DB_PREFIX . "action";
    $result = $this->db->query($query);
    return $result->rows;
  }
}
?>