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
                internal_model = ?,
                shipping_cost = ?
            ", array(
                "i:" . $data['supplierGroupId'],
                's:' . $data['name'],
                's:' . $data['internalModel'],
                'd:' . $data['shippingCost']
            )
        );
//		$supplier_id = $this->getDb()->getLastId();

		$this->getCache()->deleteAll('/^suppliers\./');
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
                internal_model = ?,
                shipping_cost = ?
            WHERE supplier_id = ?
            ", array(
                'i:' . $data['supplierGroupId'],
                's:' . $data['name'],
                's:' . $data['internalModel'],
                'd:' . $data['shippingCost'],
                "i:$supplierId"
            )
        );

		$this->getCache()->deleteAll('/^suppliers\./');
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
     * @param int $supplierId
     * @return float
     */
    public function getShippingCost($supplierId) {
        return
            $this->getDb()->queryScalar(<<<SQL
                SELECT shipping_cost
                FROM supplier
                WHERE supplier_id = ?
SQL
                , array("i:$supplierId")
            );
    }

    /**
     * @param string $supplierId
     * @return Supplier
     */
    public function getSupplier($supplierId) {
		$query = $this->getDb()->query("SELECT DISTINCT * FROM supplier WHERE supplier_id = ?", array("i:$supplierId"));

		return
            new Supplier(
                $query->row['supplier_group_id'],
                $query->row['supplier_id'],
                $query->row['internal_model'],
                $query->row['name'],
                $query->row['shipping_cost']
            );
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
     * @return Supplier[]
     */
    public function getSuppliers($data = array()) {
        if (is_null($this->getCache()->get('suppliers.' . md5(serialize($data))))) {
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
            $result = array();
			foreach ($query->rows as $supplierEntry) {
                $result[] = new Supplier(
                    $supplierEntry['supplier_group_id'],
                    $supplierEntry['supplier_id'],
                    $supplierEntry['internal_model'],
                    $supplierEntry['name'],
                    $supplierEntry['shipping_cost']
                );
            }
            $this->getCache()->set('suppliers.' . md5(serialize($data)), $result);
            return $result;
		} else {
			return $this->getCache()->get('suppliers.' . md5(serialize($data)));
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