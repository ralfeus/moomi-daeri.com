<?php
class ModelShopAction extends Model {
  public function getAllActions() {
    $query = "SELECT * FROM " . DB_PREFIX . "action";
    $result = $this->db->query($query);
    return $result->rows;
  }

  public function addAction($data) {
    $query = "INSERT INTO " . DB_PREFIX . "action(name, customer_group_id, start_date, finish_date, jsonImages, jsonUrls, publish) VALUES('" . $this->db->escape($data['name']) . "', '" . $this->db->escape($data['customer_group_id'])."', '" . $this->db->escape($data['start_date']) . "', '" . $this->db->escape($data['finish_date']) . "', '" . $this->db->escape($data['jsonImages']) . "', '" . $this->db->escape($data['jsonUrls']) . "', 1)";
    $result = $this->db->query($query);
    return $result;
  }

  public function getAction($data) {
    $query = "SELECT * FROM " . DB_PREFIX . "action WHERE id=". (int)$data['action_id'];
    $result = $this->db->query($query);
    return $result->rows;
  }

  public function deleteActions($in) {
    $query = "DELETE FROM " . DB_PREFIX . "action WHERE id IN (".$in.")";
    $result = $this->db->query($query);
    return $result;
  }

  public function updateAction($data) {
    $query = "UPDATE " . DB_PREFIX . "action SET name='".$data['name']."', customer_group_id='".(int)$data['customer_group_id']."', start_date='".$data['start_date']."', finish_date='".$data['finish_date']."', jsonUrls='".$data['jsonUrls']."' WHERE id =".$data['action_id']."";
    $result = $this->db->query($query);
    return $result;
  }
}
?>