<?php
namespace model\catalog;

use model\DAO;
class SupplierDAO extends DAO {
    /**
     * @param int $supplierId
     * @param string $columnName
     * @return mixed
     */
    private function getSingleValue($supplierId, $columnName) {
        return $this->getDb()->queryScalar("SELECT $columnName FROM supplier WHERE supplier_id = ?", array("i:$supplierId"));
    }

    /**
     * @param Supplier $supplier
     * @return int
     */
    public function addSupplier($supplier) {
        $this->getDb()->query("
            INSERT INTO supplier
            SET
                supplier_group_id = ?,
                name = ?,
                internal_model = ?,
                shipping_cost = ?,
                free_shipping_threshold = ?
            ", array(
                'i:' . $supplier->getGroupId(),
                's:' . $supplier->getName(),
                's:' . $supplier->getInternalModel(),
                'd:' . $supplier->getShippingCost(),
                'd:' . $supplier->getFreeShippingThreshold(),
            )
        );
        $newSupplierId = $this->getDb()->getLastId();
        $this->getDb()->query("
            INSERT INTO supplier_to_manufacturer
            SET
                supplier_id = ?,
                manufacturer_id = ?
            ", array(
                "i:$newSupplierId",
                'i:' . $supplier->getRelatedManufacturer()->getId()
            )
        );
		$this->getCache()->deleteAll('/^suppliers\./');
        return $newSupplierId;
    }

    /**
     * @param int $supplierId
     * @return void
     */
    public function deleteSupplier($supplierId) {
		$this->getDb()->query("DELETE FROM supplier WHERE supplier_id = ?", array("i:$supplierId"));

        $this->getCache()->deleteAll('/^suppliers\./');
    }

    /**
     * @param int $supplierId
     * @return float
     */
    public function getFreeShippingThreshold($supplierId) {
        return $this->getSingleValue($supplierId, 'free_shipping_threshold');
    }

    /**
     * @param int $supplierId
     * @return int
     */
    public function getGroupId($supplierId) {
        return $this->getSingleValue($supplierId, 'supplier_group_id');
    }

    /**
     * @param int $supplierId
     * @return string
     */
    public function getInternalModel($supplierId) {
        return $this->getSingleValue($supplierId, 'internal_model');
    }

    /**
     * @param int $supplierId
     * @return string
     */
    public function getName($supplierId) {
        return $this->getSingleValue($supplierId, 'name');
    }

    /**
     * @param $supplierId
     * @return Manufacturer
     */
    public function getRelatedManufacturer($supplierId) {
        $relatedManufacturerId = $this->getDb()->queryScalar("
            SELECT manufacturer_id
            FROM supplier_to_manufacturer
            WHERE supplier_id = ?
            ", array("i:$supplierId")
        );
        return ManufacturerDAO::getInstance()->getManufacturer($relatedManufacturerId, true);
    }

    /**
     * @param int $supplierId
     * @return float
     */
    public function getShippingCost($supplierId) {
        return $this->getSingleValue($supplierId, 'shipping_cost');
    }

    /**
     * @param string $supplierId
     * @param bool $shallow Defines whether whole object data should be extracted from the database or just stub
     * @return Supplier
     */
    public function getSupplier($supplierId, $shallow = false) {
        if ($shallow) {
             return new Supplier($supplierId);
        } else {
            $query = $this->getDb()->query("SELECT * FROM supplier WHERE supplier_id = ?", array("i:$supplierId"));
            return
                new Supplier(
                    $query->row['supplier_id'],
                    $query->row['supplier_group_id'],
                    $query->row['internal_model'],
                    $query->row['name'],
                    $query->row['shipping_cost'],
                    $query->row['free_shipping_threshold']
                );
        }
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
                    $supplierEntry['supplier_id'],
                    $supplierEntry['supplier_group_id'],
                    $supplierEntry['internal_model'],
                    $supplierEntry['name'],
                    $supplierEntry['shipping_cost'],
                    $supplierEntry['free_shipping_threshold']
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

    /**
     * @param Supplier $supplier
     * @return void
     */
    public function saveSupplier($supplier) {
        $this->getDb()->query("
		    UPDATE supplier
		    SET
                supplier_group_id = ?,
		        name = ?,
                internal_model = ?,
                shipping_cost = ?,
                free_shipping_threshold = ?
            WHERE supplier_id = ?
            ", array(
                'i:' . $supplier->getGroupId(),
                's:' . $supplier->getName(),
                's:' . $supplier->getInternalModel(),
                'd:' . $supplier->getShippingCost(),
                'd:' . $supplier->getFreeShippingThreshold(),
                'i:' . $supplier->getId()
            )
        );
        $this->getDb()->query("
            INSERT INTO supplier_to_manufacturer
            SET
                supplier_id = ?,
                manufacturer_id = ?
            ON DUPLICATE KEY UPDATE
                manufacturer_id = ?
            ", array(
                "i:" . $supplier->getId(),
                'i:' . $supplier->getRelatedManufacturer()->getId(),
                'i:' . $supplier->getRelatedManufacturer()->getId()
            )
        );

        $this->getCache()->deleteAll('/^suppliers\./');
    }
}