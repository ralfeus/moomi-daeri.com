<?php
namespace model\catalog;

use model\DAO;

class ManufacturerDAO extends DAO {
    public function addManufacturer($data) {
        $this->db->query("INSERT INTO manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

        $manufacturer_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        foreach ($data['manufacturer_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
        }

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if ($data['keyword']) {
            $this->db->query("INSERT INTO url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('manufacturer');
    }

    public function editManufacturer($manufacturer_id, $data) {
        $this->db->query("UPDATE manufacturer SET name = '" . $this->db->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE manufacturer SET image = '" . $this->db->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        $this->db->query("DELETE FROM manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        foreach ($data['manufacturer_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
        }

        $this->db->query("DELETE FROM manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->db->query("INSERT INTO manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->db->query("DELETE FROM url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id. "'");

        if ($data['keyword']) {
            $this->db->query("INSERT INTO url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('manufacturer');
    }

    public function deleteManufacturer($manufacturer_id) {
        $this->db->query("DELETE FROM manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->db->query("DELETE FROM manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->db->query("DELETE FROM manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->db->query("DELETE FROM url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

        $this->cache->delete('manufacturer');
    }

    /**
     * @param int $manufacturerId
     * @param bool $shallow
     * @return Manufacturer|array
     */
    public function getManufacturer($manufacturerId, $shallow = false) {
        if ($shallow) {
            return new Manufacturer($manufacturerId);
        }
        $query = $this->db->query("SELECT DISTINCT *, (SELECT keyword FROM url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturerId . "') AS keyword FROM manufacturer WHERE manufacturer_id = '" . (int)$manufacturerId . "'");

        return $query->row;
    }

    public function getManufacturers($data = array()) {
        if ($data) {
            $sql = "SELECT *, manufacturer_id AS id FROM manufacturer";

            $sort_data = array(
                'name',
                'sort_order'
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
            $manufacturer_data = $this->cache->get('manufacturer');

            if (!$manufacturer_data) {
                $query = $this->db->query("SELECT *, manufacturer_id AS id FROM manufacturer ORDER BY name");

                $manufacturer_data = $query->rows;

                $this->cache->set('manufacturer', $manufacturer_data);
            }

            return $manufacturer_data;
        }
    }

    public function getManufacturerStores($manufacturer_id) {
        $manufacturer_store_data = array();

        $query = $this->db->query("SELECT * FROM manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        foreach ($query->rows as $result) {
            $manufacturer_store_data[] = $result['store_id'];
        }

        return $manufacturer_store_data;
    }

    public function getTotalManufacturersByImageId($image_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM manufacturer WHERE image_id = '" . (int)$image_id . "'");

        return $query->row['total'];
    }

    public function getTotalManufacturers() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM manufacturer");

        return $query->row['total'];
    }

    public function getManufacturerDescriptions($manufacturer_id) {
        $manufacturer_description_data = array();

        $query = $this->db->query("SELECT * FROM manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        foreach ($query->rows as $result) {
            $manufacturer_description_data[$result['language_id']] = array(
                'seo_title'        => $result['seo_title'],
                'seo_h1'           => $result['seo_h1'],
                'meta_keyword'     => $result['meta_keyword'],
                'meta_description' => $result['meta_description'],
                'description'      => $result['description']
            );
        }

        return $manufacturer_description_data;
    }
}