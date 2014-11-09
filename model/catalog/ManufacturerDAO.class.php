<?php
namespace model\catalog;

use model\DAO;

class ManufacturerDAO extends DAO {
    /**
     * @param int $manufacturerId
     * @param string $columnName
     * @return mixed
     */
    private function getSingleValue($manufacturerId, $columnName) {
        return $this->getDb()->queryScalar("SELECT $columnName FROM manufacturer WHERE manufacturer_id = ?", array("i:$manufacturerId"));
    }

    public function addManufacturer($data) {
        $this->getDb()->query("INSERT INTO manufacturer SET name = '" . $this->getDb()->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "'");

        $manufacturer_id = $this->getDb()->getLastId();

        if (isset($data['image'])) {
            $this->getDb()->query("UPDATE manufacturer SET image = '" . $this->getDb()->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        foreach ($data['manufacturer_description'] as $language_id => $value) {
            $this->getDb()->query("INSERT INTO manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', meta_keyword = '" . $this->getDb()->escape($value['meta_keyword']) . "', meta_description = '" . $this->getDb()->escape($value['meta_description']) . "', description = '" . $this->getDb()->escape($value['description']) . "', seo_title = '" . $this->getDb()->escape($value['seo_title']) . "', seo_h1 = '" . $this->getDb()->escape($value['seo_h1']) . "'");
        }

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->getDb()->query("INSERT INTO manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if ($data['keyword']) {
            $this->getDb()->query("INSERT INTO url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->getDb()->escape($data['keyword']) . "'");
        }

        $this->getCache()->deleteAll('/^manufacturers\./');
    }

    public function editManufacturer($manufacturer_id, $data) {
        $this->getDb()->query("UPDATE manufacturer SET name = '" . $this->getDb()->escape($data['name']) . "', sort_order = '" . (int)$data['sort_order'] . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        if (isset($data['image'])) {
            $this->getDb()->query("UPDATE manufacturer SET image = '" . $this->getDb()->escape($data['image']) . "' WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        }

        $this->getDb()->query("DELETE FROM manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        foreach ($data['manufacturer_description'] as $language_id => $value) {
            $this->getDb()->query("INSERT INTO manufacturer_description SET manufacturer_id = '" . (int)$manufacturer_id . "', language_id = '" . (int)$language_id . "', meta_keyword = '" . $this->getDb()->escape($value['meta_keyword']) . "', meta_description = '" . $this->getDb()->escape($value['meta_description']) . "', description = '" . $this->getDb()->escape($value['description']) . "', seo_title = '" . $this->getDb()->escape($value['seo_title']) . "', seo_h1 = '" . $this->getDb()->escape($value['seo_h1']) . "'");
        }

        $this->getDb()->query("DELETE FROM manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        if (isset($data['manufacturer_store'])) {
            foreach ($data['manufacturer_store'] as $store_id) {
                $this->getDb()->query("INSERT INTO manufacturer_to_store SET manufacturer_id = '" . (int)$manufacturer_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->getDb()->query("DELETE FROM url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id. "'");

        if ($data['keyword']) {
            $this->getDb()->query("INSERT INTO url_alias SET query = 'manufacturer_id=" . (int)$manufacturer_id . "', keyword = '" . $this->getDb()->escape($data['keyword']) . "'");
        }

        $this->getCache()->deleteAll('/^manufacturers\./');
    }

    public function deleteManufacturer($manufacturer_id) {
        $this->getDb()->query("DELETE FROM manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->getDb()->query("DELETE FROM manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->getDb()->query("DELETE FROM manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");
        $this->getDb()->query("DELETE FROM url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturer_id . "'");

        $this->getCache()->deleteAll('/^manufacturers\./');
    }

    /**
     * @param int $manufacturerId
     * @return int
     */
    public function getAfcId($manufacturerId) {
        return $this->getSingleValue($manufacturerId, 'afc_id');
    }

    /**
     * @param int $manufacturerId
     * @return string[]
     */
    public function getDescription($manufacturerId) {
        $recordSet = $this->getDb()->query(<<<SQL
            SELECT language_id, description
            FROM manufacturer_description
            WHERE manufacturer_id = ?
SQL
            , array("i:$manufacturerId")
        );
        $result = array();
        foreach ($recordSet->rows as $descriptionEntry) {
            $result[$descriptionEntry['language_id']] = $descriptionEntry['description'];
        }
        return $result;
    }

    /**
     * @param int $manufacturerId
     * @return string
     */
    public function getImagePath($manufacturerId) {
        return $this->getSingleValue($manufacturerId, 'image');
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
        $query = $this->getDb()->query("SELECT DISTINCT *, (SELECT keyword FROM url_alias WHERE query = 'manufacturer_id=" . (int)$manufacturerId . "') AS keyword FROM manufacturer WHERE manufacturer_id = '" . (int)$manufacturerId . "'");

        return $query->row;
    }

    /**
     * @param array $data
     * @return Manufacturer[]
     * @throws \CacheNotInstalledException
     */
    public function getManufacturers($data = array()) {
        if (is_null($this->getCache()->get('manufacturers.' . md5(serialize($data))))) {
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

            $query = $this->getDb()->query($sql);
            $result = array();
            foreach ($query->rows as $manufacturerEntry) {
                $result[] = new Manufacturer(
                    $manufacturerEntry['manufacturer_id']);
            }
            $this->getCache()->set('manufacturers.' . md5(serialize($data)), $result);
            return $result;
        } else {
            return $this->getCache()->get('manufacturers.' . md5(serialize($data)));
        }
    }

    public function getManufacturerStores($manufacturer_id) {
        $manufacturer_store_data = array();

        $query = $this->getDb()->query("SELECT * FROM manufacturer_to_store WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        foreach ($query->rows as $result) {
            $manufacturer_store_data[] = $result['store_id'];
        }

        return $manufacturer_store_data;
    }

    /**
     * @param int $manufacturerId
     * @return string
     */
    public function getName($manufacturerId) {
        return $this->getSingleValue($manufacturerId, 'name');
    }

    /**
     * @param int $manufacturerId
     * @return int
     */
    public function getSortOrder($manufacturerId) {
        return $this->getSingleValue($manufacturerId, 'sort_order');
    }

    public function getTotalManufacturersByImageId($image_id) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM manufacturer WHERE image_id = '" . (int)$image_id . "'");

        return $query->row['total'];
    }

    public function getTotalManufacturers() {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM manufacturer");

        return $query->row['total'];
    }

    public function getManufacturerDescriptions($manufacturer_id) {
        $manufacturer_description_data = array();

        $query = $this->getDb()->query("SELECT * FROM manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

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
