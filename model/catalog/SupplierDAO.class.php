<?php
namespace model\catalog;

use model\DAO;
class SupplierDAO extends DAO {
    /**
     * @param array $data
     * @return void
     */
    public function addSupplier($data) {
        $this->getDb()->query("
            INSERT INTO supplier
            SET
                supplier_group_id = ?,
                name = ?,
                internal_model = ?
            ", array("i:" . $data['supplier_group_id'], 's:' . $data['name'], 's:' . $data['internal_model'])
        );
//		$supplier_id = $this->getDb()->getLastId();

		$this->cache->delete('supplier');
    }

    /**
     * @param int $supplierId
     * @param array $data
     * @return void
     */
    public function editSupplier($supplierId, $data) {
		$this->getDb()->query("
		    UPDATE supplier
		    SET
                supplier_group_id = ?,
		        name = ?,
                internal_model = ?
            WHERE supplier_id = ?
            ", array('i:' . $data['supplier_group_id'], 's:' . $data['name'], 's:' . $data['internal_model'], "i:$supplierId")
        );

		$this->cache->delete('supplier');
    }

    /**
     * @param int $supplierId
     * @return void
     */
    public function deleteSupplier($supplierId) {
		$this->getDb()->query("DELETE FROM supplier WHERE supplier_id = ?", array("i:$supplierId"));

		$this->cache->delete('supplier');
    }

    /**
     * @param string $supplierId
     * @return array
     */
    public function getSupplier($supplierId) {
		$query = $this->getDb()->query("SELECT DISTINCT * FROM supplier WHERE supplier_id = ?", array("i:$supplierId"));

		return $query->row;
    }

    /**
     * @param string $supplierName
     * @return array
     */
    public function getSupplierByName($supplierName) {
        $query = $this->getDb()->query("SELECT DISTINCT * FROM supplier WHERE name = ?", array("s:$supplierName"));

        return $query->row;
    }

    /**
     * @param array $data
     * @return array
     */
    public function getSuppliers($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM supplier";

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

			$query = $this->getDb()->query($sql);

			return $query->rows;
		} else {
			$supplier_data = $this->cache->get('supplier');

			if (!$supplier_data) {
				$query = $this->getDb()->query("SELECT * FROM supplier ORDER BY name");

				$supplier_data = $query->rows;

				$this->cache->set('supplier', $supplier_data);
			}

			return $supplier_data;
		}
    }

    /**
     * @return int
     */
    public function getTotalSuppliers() {
		return $this->getDb()->queryScalar("SELECT COUNT(*) AS total FROM supplier");
    }

    /**
     * @param int $supplierGroupId
     * @return int
     */
    public function getTotalSuppliersBySupplierGroupId($supplierGroupId) {
        return $this->getDb()->queryScalar("SELECT COUNT(*) as total FROM supplier WHERE supplier_group_id = ?", array("i:$supplierGroupId"));
    }
}
?>