<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 3/14/2016
 * Time: 10:38 AM
 */
namespace model\catalog;

use model\DAO;
use model\localization\Description;

class CategoryDAO extends DAO {
    public function addCategory($data) {
        $this->db->query("INSERT INTO category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()"
            . ", affiliate_commission = '" . (float)$data['affiliate_commission'] . "'"
        );

        $category_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
        }

        foreach ($data['category_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
        }

        if (isset($data['category_store'])) {
            foreach ($data['category_store'] as $store_id) {
                $this->db->query("INSERT INTO category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['category_layout'])) {
            foreach ($data['category_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->db->query("INSERT INTO category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }

        if ($data['keyword']) {
            $this->db->query("INSERT INTO url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('category');
    }

    public function editCategory($category_id, $data) {
        $this->db->query("UPDATE category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW()"
            . ", affiliate_commission = '" . (float)$data['affiliate_commission'] . "'" .
            " WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE category SET image = '" . $this->db->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
        }

        $this->db->query("DELETE FROM category_description WHERE category_id = '" . (int)$category_id . "'");

        foreach ($data['category_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
        }

        $this->db->query("DELETE FROM category_to_store WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['category_store'])) {
            foreach ($data['category_store'] as $store_id) {
                $this->db->query("INSERT INTO category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->db->query("DELETE FROM category_to_layout WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['category_layout'])) {
            foreach ($data['category_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->db->query("INSERT INTO category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }

        $this->db->query("DELETE FROM url_alias WHERE query = 'category_id=" . (int)$category_id. "'");

        if ($data['keyword']) {
            $this->db->query("INSERT INTO url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
        }

        $this->cache->delete('category');
    }

    public function deleteCategory($category_id) {
        $this->db->query("DELETE FROM category WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM category_description WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM category_to_store WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM category_to_layout WHERE category_id = '" . (int)$category_id . "'");
        $this->db->query("DELETE FROM url_alias WHERE query = 'category_id=" . (int)$category_id . "'");

        $query = $this->db->query("SELECT category_id FROM category WHERE parent_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $this->deleteCategory($result['category_id']);
        }

        $this->cache->delete('category');
    }

    public function getCategory($categoryId, $shallow = false) {
        if ($shallow) {
            return new Category($categoryId);
        }
        $query = $this->getDb()->query(<<<SQL
            SELECT DISTINCT 
                *, (
                    SELECT keyword 
                    FROM url_alias 
                    WHERE query = CONCAT('category_id=', :categoryId)
                ) AS keyword 
            FROM category 
            WHERE category_id = :categoryId
SQL
            , [':categoryId' => $categoryId]
        );
        if ($query->rows) {
            return new Category(
                $categoryId,
                $query->row['image'],
                $this->getCategory($query->row['parent_id']),
                $query->row['top'],
                $query->row['column'],
                $query->row['sort_order'],
                $query->row['status'],
                $query->row['date_added'],
                $query->row['date_modified'],
                $query->row['afc_id'],
                $query->row['affiliateCommission'],
                new Description(
                    $query->row['language_id'],
                    $query->row['name'],
                    $query->row['description'],
                    $query->row['meta_description'],
                    $query->row['meta_keyword'],
                    $query->row['seo_title'],
                    $query->row['seo_h1']
                )
            );
        } else {
            return null;
        }
    }

    public function getCategoriesParent($id = 0, $type = 'by_parent') {
        static $data = null;

        if ($data === null) {
            $data = array();

            $query = $this->getDb()->query("
			    SELECT *
			    FROM
			        category c
			        LEFT JOIN category_description cd ON (c.category_id = cd.category_id)
			        LEFT JOIN category_to_store c2s ON (c.category_id = c2s.category_id)
                WHERE
                    cd.language_id = ?
                    AND c2s.store_id = ?
                    AND c.status = '1' ORDER BY c.parent_id, c.sort_order, cd.name
                ", array('i:' . $this->config->get('config_language_id'), 'i:' . $this->config->get('config_store_id'))
            );

            foreach ($query->rows as $row) {
                $data['by_id'][$row['category_id']] = $row;
                $data['by_parent'][$row['parent_id']][] = $row;
            }
        }

        return ((isset($data[$type]) && isset($data[$type][$id])) ? $data[$type][$id] : array());
    }

    public function getCategories($parent_id = 0) {
        $category_data = $this->cache->get('category.' . (int)$this->config->get('config_language_id') . '.' . (int)$parent_id);

        if (!$category_data) {
            $category_data = array();

            $query = $this->db->query("SELECT * FROM category c LEFT JOIN category_description cd ON (c.category_id = cd.category_id) WHERE c.parent_id = '" . (int)$parent_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name ASC");

            foreach ($query->rows as $result) {
                $category_data[] = array(
                    'category_id' => $result['category_id'],
                    'name'        => $this->getPath($result['category_id'], $this->config->get('config_language_id')),
                    'status'  	  => $result['status'],
                    'sort_order'  => $result['sort_order']
                );

                $category_data = array_merge($category_data, $this->getCategories($result['category_id']));
            }

            $this->cache->set('category.' . (int)$this->config->get('config_language_id') . '.' . (int)$parent_id, $category_data);
        }

        return $category_data;
    }

    public function getPath($category_id) {
        $query = $this->db->query("SELECT name, parent_id FROM category c LEFT JOIN category_description cd ON (c.category_id = cd.category_id) WHERE c.category_id = '" . (int)$category_id . "' AND cd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY c.sort_order, cd.name ASC");

        if ($query->row['parent_id']) {
            return $this->getPath($query->row['parent_id'], $this->config->get('config_language_id')) . $this->language->get('text_separator') . $query->row['name'];
        } else {
            return $query->row['name'];
        }
    }

    public function getCategoryDescriptions($category_id) {
        $category_description_data = array();

        $query = $this->db->query("SELECT * FROM category_description WHERE category_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $category_description_data[$result['language_id']] = array(
                'seo_title'        => $result['seo_title'],
                'seo_h1'           => $result['seo_h1'],
                'name'             => $result['name'],
                'meta_keyword'     => $result['meta_keyword'],
                'meta_description' => $result['meta_description'],
                'description'      => $result['description']
            );
        }

        return $category_description_data;
    }

    public function getCategoryStores($category_id) {
        $category_store_data = array();

        $query = $this->db->query("SELECT * FROM category_to_store WHERE category_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $category_store_data[] = $result['store_id'];
        }

        return $category_store_data;
    }

    public function getCategoryLayouts($category_id) {
        $category_layout_data = array();

        $query = $this->db->query("SELECT * FROM category_to_layout WHERE category_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $category_layout_data[$result['store_id']] = $result['layout_id'];
        }

        return $category_layout_data;
    }

    public function getTotalCategories() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM category");

        return $query->row['total'];
    }

    public function getTotalCategoriesByImageId($image_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM category WHERE image_id = '" . (int)$image_id . "'");

        return $query->row['total'];
    }

    public function getTotalCategoriesByLayoutId($layout_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

        return $query->row['total'];
    }

    /**
     * @param int $parentCategoryId
     * @return Category[]
     */
    public function getCategoriesByParentId($parentCategoryId = 0) {
        $query = $this->getDb()->query(<<<SQL
            SELECT
                *, (
                    SELECT COUNT(parent_id)
                    FROM category
                    WHERE parent_id = c.category_id
                ) AS children
            FROM
                category AS c
                LEFT JOIN category_description AS cd ON c.category_id = cd.category_id
            WHERE
                c.parent_id = :parentCategoryId
                AND cd.language_id = :languageId
            ORDER BY c.sort_order, cd.name
SQL
            , [
            ':parentCategoryId' => $parentCategoryId,
            ':languageId' => $this->config->get('config_language_id')
        ]);

        $result = [];
        foreach ($query->rows as $row) {
            $result[] = new Category(
                $row['category_id'],
                $row['image'],
                $this->getCategory($row['parent_id'], true),
                $row['top'],
                $row['column'],
                $row['sort_order'],
                $row['status'],
                $row['date_added'],
                $row['date_modified'],
                $row['afc_id'],
                $row['affiliateCommission'],
                new Description(
                    $row['language_id'],
                    $row['name'],
                    $row['description'],
                    $row['meta_description'],
                    $row['meta_keyword'],
                    $row['seo_title'],
                    $row['seo_h1']
                )
            );
        }
        return $result;
    }

    public function getAllCategories() {
        $category_data = $this->cache->get('category.all.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'));

        if (!$category_data || !is_array($category_data)) {
            $query = $this->db->query("SELECT * FROM category c LEFT JOIN category_description cd ON (c.category_id = cd.category_id) LEFT JOIN category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  ORDER BY c.parent_id, c.sort_order, cd.name");

            $category_data = array();
            foreach ($query->rows as $row) {
                $category_data[$row['parent_id']][$row['category_id']] = $row;
            }

            $this->cache->set('category.all.' . $this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id'), $category_data);
        }

        return $category_data;
    }
}