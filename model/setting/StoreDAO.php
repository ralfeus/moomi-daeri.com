<?php
namespace model\setting;

use model\DAO;

class StoreDAO extends DAO {
    public function addStore($data) {
        $this->getDb()->query("INSERT INTO store SET name = '" . $this->getDb()->escape($data['config_name']) . "', `url` = '" . $this->getDb()->escape($data['config_url']) . "', `ssl` = '" . $this->getDb()->escape($data['config_ssl']) . "'");

        $this->cache->delete('store');

        return $this->getDb()->getLastId();
    }

    public function editStore($store_id, $data) {
        $this->getDb()->query("UPDATE store SET name = '" . $this->getDb()->escape($data['config_name']) . "', `url` = '" . $this->getDb()->escape($data['config_url']) . "', `ssl` = '" . $this->getDb()->escape($data['config_ssl']) . "' WHERE store_id = '" . (int)$store_id . "'");

        $this->cache->delete('store');
    }

    public function deleteStore($store_id) {
        $this->getDb()->query("DELETE FROM store WHERE store_id = '" . (int)$store_id . "'");

        $this->cache->delete('store');
    }

    public function getStore($store_id) {
        $query = $this->getDb()->query("SELECT DISTINCT * FROM store WHERE store_id = '" . (int)$store_id . "'");

        return $query->row;
    }

    public function getStores() {
        return $this->getDb()->query("SELECT * FROM store ORDER BY url")->rows;
    }

    public function getTotalStores() {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM store");

        return $query->row['total'];
    }

    public function getTotalStoresByLayoutId($layout_id) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_layout_id' AND `value` = '" . (int)$layout_id . "' AND store_id != '0'");

        return $query->row['total'];
    }

    public function getTotalStoresByLanguage($language) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_language' AND `value` = '" . $this->getDb()->escape($language) . "' AND store_id != '0'");

        return $query->row['total'];
    }

    public function getTotalStoresByCurrency($currency) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_currency' AND `value` = '" . $this->getDb()->escape($currency) . "' AND store_id != '0'");

        return $query->row['total'];
    }

    public function getTotalStoresByCountryId($country_id) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_country_id' AND `value` = '" . (int)$country_id . "' AND store_id != '0'");

        return $query->row['total'];
    }

    public function getTotalStoresByZoneId($zone_id) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_zone_id' AND `value` = '" . (int)$zone_id . "' AND store_id != '0'");

        return $query->row['total'];
    }

    public function getTotalStoresByCustomerGroupId($customer_group_id) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_customer_group_id' AND `value` = '" . (int)$customer_group_id . "' AND store_id != '0'");

        return $query->row['total'];
    }

    public function getTotalStoresByInformationId($information_id) {
        $account_query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_account_id' AND `value` = '" . (int)$information_id . "' AND store_id != '0'");

        $checkout_query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_checkout_id' AND `value` = '" . (int)$information_id . "' AND store_id != '0'");

        return ($account_query->row['total'] + $checkout_query->row['total']);
    }

    public function getTotalStoresByOrderStatusId($order_status_id) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM setting WHERE `key` = 'config_order_status_id' AND `value` = '" . (int)$order_status_id . "' AND store_id != '0'");

        return $query->row['total'];
    }
}