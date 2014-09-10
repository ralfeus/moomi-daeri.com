<?php
class ModelCatalogSupplierGroup extends Model
{
    public function addSupplierGroup($data) {
        $this->db->query("
            INSERT INTO supplier_group
            SET
                name = '" . $this->db->escape($data['name']) . "'");

        $this->cache->delete('supplier_group');
    }

    public function editSupplierGroup($supplier_group_id, $data) {
        $this->db->query("
		    UPDATE supplier_group
		    SET
		        name = '" . $this->db->escape($data['name']) . "'
            WHERE supplier_group_id = '" . (int)$supplier_group_id . "'");

        $this->cache->delete('supplier_group');
    }

    public function deleteSupplierGroup($supplier_group_id) {
        $this->db->query("DELETE FROM supplier_group WHERE supplier_group_id = '" . (int)$supplier_group_id . "'");

        $this->cache->delete('supplier_group');
    }

    public function getSupplierGroup($supplier_group_id) {
        $query = $this->db->query("SELECT DISTINCT * FROM supplier_group WHERE supplier_group_id = '" . (int)$supplier_group_id . "'");

        return $query->row;
    }

    public function getSupplierGroupByName($supplier_group_name)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM supplier_group WHERE name = '$supplier_group_name'");

        return $query->row;
    }

    public function getSupplierGroups($data = array()) {
        if ($data) {
            $sql = "SELECT * FROM supplier_group";

            $sort_data = array(
                'name'
            );

            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                $sql .= " ORDER BY " . $data['sort'];
            } else {
                $sql .= " ORDER BY name";
            }

            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                $sql .= " DESC";
            } else {
                $sql .= " ASC";
            }

            if (isset($data['start']) || isset($data['limit'])) {
                if ($data['start'] < 0) {
                    $data['start'] = 0;
                }

                if ($data['limit'] < 1) {
                    $data['limit'] = 20;
                }

                $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
            }

            $query = $this->db->query($sql);

            return $query->rows;
        } else {
            $supplier_group_data = $this->cache->get('supplier_group');

            if (!$supplier_group_data) {
                $query = $this->db->query("SELECT * FROM supplier_group ORDER BY name");

                $supplier_group_data = $query->rows;

                $this->cache->set('supplier_group', $supplier_group_data);
            }

            return $supplier_group_data;
        }
    }

    public function getTotalSupplierGroups() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM supplier_group");

        return $query->row['total'];
    }
}
