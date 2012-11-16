<?php
class ModelCatalogSupplier extends Model {
    public function addSupplier($data) {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "supplier
            SET
                supplier_group_id = " . (int)$data['supplier_group_id'] . ",
                name = '" . $this->db->escape($data['name']) . "',
                internal_model = '" . $this->db->escape($data['internal_model']) . "'");
//		$supplier_id = $this->db->getLastId();

		$this->cache->delete('supplier');
    }

    public function editSupplier($supplier_id, $data) {
		$this->db->query("
		    UPDATE " . DB_PREFIX . "supplier
		    SET
                supplier_group_id = " . (int)$data['supplier_group_id'] . ",
		        name = '" . $this->db->escape($data['name']) . "',
                internal_model = '" . $this->db->escape($data['internal_model']) . "'
            WHERE supplier_id = '" . (int)$supplier_id . "'");

		$this->cache->delete('supplier');
    }

    public function deleteSupplier($supplier_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "supplier WHERE supplier_id = '" . (int)$supplier_id . "'");

		$this->cache->delete('supplier');
    }

    public function getSupplier($supplier_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "supplier WHERE supplier_id = '" . (int)$supplier_id . "'");

		return $query->row;
    }

    public function getSupplierByName($supplier_name)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "supplier WHERE name = '$supplier_name'");

        return $query->row;
    }

    public function getSuppliers($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM " . DB_PREFIX . "supplier";

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
			$supplier_data = $this->cache->get('supplier');

			if (!$supplier_data) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "supplier ORDER BY name");

				$supplier_data = $query->rows;

				$this->cache->set('supplier', $supplier_data);
			}

			return $supplier_data;
		}
    }

    public function getTotalSuppliers() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "supplier");

		return $query->row['total'];
    }

    public function getTotalSuppliersBySupplierGroupId($supplier_group_id)
    {
        $query = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "supplier WHERE supplier_group_id = $supplier_group_id");

        return $query->row['total'];
    }
}
?>