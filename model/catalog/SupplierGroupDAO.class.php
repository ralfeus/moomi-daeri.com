<?php
namespace model\catalog;

use model\DAO;

class SupplierGroupDAO extends DAO {
    public function addSupplierGroup($data) {
        $this->getDb()->query("
            INSERT INTO supplier_group
            SET
                name = ?
            ", array('s:' . $data['name'])
        );

        $this->cache->delete('supplier_group');
    }

    public function editSupplierGroup($supplierGroupId, $data) {
        $this->getDb()->query("
		    UPDATE supplier_group
		    SET
		        name = ?
            WHERE supplier_group_id = ?
            ", array('s:' . $data['name'], "i:$supplierGroupId")
        );

        $this->cache->delete('supplier_group');
    }

    public function deleteSupplierGroup($supplierGroupId) {
        $this->getDb()->query("DELETE FROM supplier_group WHERE supplier_group_id = ?", array("i:$supplierGroupId"));

        $this->cache->delete('supplier_group');
    }

    public function getSupplierGroup($supplierGroupId) {
        $query = $this->getDb()->query("SELECT DISTINCT * FROM supplier_group WHERE supplier_group_id = ?", array("i:$supplierGroupId"));

        return $query->row;
    }

    public function getSupplierGroupByName($supplierGroupName) {
        $query = $this->getDb()->query("SELECT DISTINCT * FROM supplier_group WHERE name = ?", array("s:$supplierGroupName"));

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

            $query = $this->getDb()->query($sql);

            return $query->rows;
        } else {
            $supplier_group_data = $this->cache->get('supplier_group');

            if (!$supplier_group_data) {
                $query = $this->getDb()->query("SELECT * FROM supplier_group ORDER BY name");

                $supplier_group_data = $query->rows;

                $this->cache->set('supplier_group', $supplier_group_data);
            }

            return $supplier_group_data;
        }
    }

    public function getTotalSupplierGroups() {
        return $this->getDb()->queryScalar("SELECT COUNT(*) AS total FROM supplier_group");
    }
}
