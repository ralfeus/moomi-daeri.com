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

        $query = $this->getDb()->query("
            SELECT * FROM setting 
            WHERE store_id = :storeId AND `group` = :group
            ", [':group' => $group, ':storeId' => $store_id]
        );

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
        $this->getCache()->deleteAll("/^setting\\./");
    }
    public function updateSettings($group, $data, $store_id = 0) {
        $this->deleteSettings($group, $store_id);

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $serialized = 1;
                $value = serialize($value);
            } else {
                $serialized = 0;
            }
            $this->getDb()->query("
                INSERT INTO setting 
                SET 
                    store_id = :storeId, 
                    `group` = :group, 
                    `key` = :key, 
                    `value` = :value,
                    serialized = :serialized
                ", [
                ':storeId' => $store_id,
                ':group' => $group,
                ':key' => $key,
                ':value' => $value,
                ':serialized' => $serialized
            ]);
        }
        $this->getCache()->deleteAll("/^setting\\./");
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
        $this->getCache()->deleteAll("/^setting\\./");
    }

    public function deleteSettings($group, $store_id = 0) {
        $this->getDb()->query("
            DELETE FROM setting 
            WHERE store_id = :storeId AND `group` = :group
            ", [
            ':storeId' => $store_id,
            ':group' => $group
        ]);
        $this->getCache()->deleteAll("/^setting\\./");
    }
}