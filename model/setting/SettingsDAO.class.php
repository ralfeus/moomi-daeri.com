<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 2/8/2016
 * Time: 8:26 AM
 */

namespace model\setting;
use model\DAO;

class SettingsDAO extends DAO {
    /**
     * @param string $group
     * @param string $key
     * @param int $storeId
     * @return mixed
     */
    public function getSetting($group, $key, $storeId = 0) {
        $query = $this->getDb()->query("
            SELECT *
            FROM setting
            WHERE
              store_id = :storeId
              AND `group` = :group
              AND `key` = :key
            ", [
            ':storeId' => $storeId,
            ':group' => $group,
            ':key' => $key
        ]);
        if ($query->rows) {
            if ($query->row['serialized']) {
                return unserialize($query->row['value']);
            } else {
                return $query->row['value'];
            }
        } else {
            return null;
        }
    }

    public function getSettings($group, $store_id = 0) {
        $data = array();

        $query = $this->getDb()->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");

        foreach ($query->rows as $result) {
            if (!$result['serialized']) {
                $data[$result['key']] = $result['value'];
            } else {
                $data[$result['key']] = unserialize($result['value']);
            }
        }

        return $data;
    }

    public function updateSetting($group, $key, $value, $storeId = 0) {
        $this->getDb()->query("
            INSERT INTO setting
            SET
              store_id = :storeId,
              `group` = :group,
              `key` = :key,
              value = :value
            ON DUPLICATE KEY UPDATE
              value = :value
            ", [
            ':storeId' => $storeId,
            ':group' => $group,
            ':key' => $key,
            ':value' => $value
        ]);
    }
    public function updateSettings($group, $data, $store_id = 0) {
        $this->deleteSettings($group, $store_id);

        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $this->getDb()->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
            } else {
                $this->getDb()->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int)$store_id . "', `group` = '" . $this->db->escape($group) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(serialize($value)) . "', serialized = '1'");
            }
        }
    }

    public function deleteSetting($group, $key, $storeId = 0) {
        $this->getDb()->query("
            DELETE FROM setting
            WHERE
              store_id = :storeId
              AND `group` = :group
              AND `key` = :key
            ", [
            ':storeId' => $storeId,
            ':group' => $group,
            ':key' => $key
        ]);
    }

    public function deleteSettings($group, $store_id = 0) {
        $this->getDb()->query("DELETE FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `group` = '" . $this->db->escape($group) . "'");
    }
}