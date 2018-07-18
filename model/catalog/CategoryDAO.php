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
use model\localization\DescriptionCollection;

class CategoryDAO extends DAO {
    public function addCategory($data) {
        $this->getDb()->query("INSERT INTO category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW(), date_added = NOW()"
            . ", affiliate_commission = '" . (float)$data['affiliate_commission'] . "'"
        );

        $category_id = $this->getDb()->getLastId();

        if (isset($data['image'])) {
            $this->getDb()->query("UPDATE category SET image = '" . $this->getDb()->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
        }

        foreach ($data['category_description'] as $language_id => $value) {
            $this->getDb()->query("INSERT INTO category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->getDb()->escape($value['name']) . "', meta_keyword = '" . $this->getDb()->escape($value['meta_keyword']) . "', meta_description = '" . $this->getDb()->escape($value['meta_description']) . "', description = '" . $this->getDb()->escape($value['description']) . "', seo_title = '" . $this->getDb()->escape($value['seo_title']) . "', seo_h1 = '" . $this->getDb()->escape($value['seo_h1']) . "'");
        }

        if (isset($data['category_store'])) {
            foreach ($data['category_store'] as $store_id) {
                $this->getDb()->query("INSERT INTO category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['category_layout'])) {
            foreach ($data['category_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->getDb()->query("INSERT INTO category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }

        if ($data['keyword']) {
            $this->getDb()->query("INSERT INTO url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->getDb()->escape($data['keyword']) . "'");
        }

        $this->getCache()->delete('category');
    }

    public function editCategory($category_id, $data) {
        $this->getDb()->query("UPDATE category SET parent_id = '" . (int)$data['parent_id'] . "', `top` = '" . (isset($data['top']) ? (int)$data['top'] : 0) . "', `column` = '" . (int)$data['column'] . "', sort_order = '" . (int)$data['sort_order'] . "', status = '" . (int)$data['status'] . "', date_modified = NOW()"
            . ", affiliate_commission = '" . (float)$data['affiliate_commission'] . "'" .
            " WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['image'])) {
            $this->getDb()->query("UPDATE category SET image = '" . $this->getDb()->escape($data['image']) . "' WHERE category_id = '" . (int)$category_id . "'");
        }

        $this->getDb()->query("DELETE FROM category_description WHERE category_id = '" . (int)$category_id . "'");

        foreach ($data['category_description'] as $language_id => $value) {
            $this->getDb()->query("INSERT INTO category_description SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->getDb()->escape($value['name']) . "', meta_keyword = '" . $this->getDb()->escape($value['meta_keyword']) . "', meta_description = '" . $this->getDb()->escape($value['meta_description']) . "', description = '" . $this->getDb()->escape($value['description']) . "', seo_title = '" . $this->getDb()->escape($value['seo_title']) . "', seo_h1 = '" . $this->getDb()->escape($value['seo_h1']) . "'");
        }

        $this->getDb()->query("DELETE FROM category_to_store WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['category_store'])) {
            foreach ($data['category_store'] as $store_id) {
                $this->getDb()->query("INSERT INTO category_to_store SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->getDb()->query("DELETE FROM category_to_layout WHERE category_id = '" . (int)$category_id . "'");

        if (isset($data['category_layout'])) {
            foreach ($data['category_layout'] as $store_id => $layout) {
                if ($layout['layout_id']) {
                    $this->getDb()->query("INSERT INTO category_to_layout SET category_id = '" . (int)$category_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
                }
            }
        }

        $this->getDb()->query("DELETE FROM url_alias WHERE query = 'category_id=" . (int)$category_id. "'");

        if ($data['keyword']) {
            $this->getDb()->query("INSERT INTO url_alias SET query = 'category_id=" . (int)$category_id . "', keyword = '" . $this->getDb()->escape($data['keyword']) . "'");
        }

        $this->getCache()->delete('category');
    }

    public function deleteCategory($category_id) {
        $this->getDb()->query("DELETE FROM category WHERE category_id = '" . (int)$category_id . "'");
        $this->getDb()->query("DELETE FROM category_description WHERE category_id = '" . (int)$category_id . "'");
        $this->getDb()->query("DELETE FROM category_to_store WHERE category_id = '" . (int)$category_id . "'");
        $this->getDb()->query("DELETE FROM category_to_layout WHERE category_id = '" . (int)$category_id . "'");
        $this->getDb()->query("DELETE FROM url_alias WHERE query = 'category_id=" . (int)$category_id . "'");

        $query = $this->getDb()->query("SELECT category_id FROM category WHERE parent_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $this->deleteCategory($result['category_id']);
        }

        $this->getCache()->delete('category');
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
            FROM 
                category AS c
                LEFT JOIN category_description AS cd ON c.category_id = cd.category_id
            WHERE c.category_id = :categoryId AND cd.language_id = :languageId
SQL
            , [':categoryId' => $categoryId, ':languageId' => $this->getLanguage()->getId() ]
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
                $query->row['affiliate_commission'],
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
                ", array('i:' . $this->getConfig()->get('config_language_id'), 'i:' . $this->getConfig()->get('config_store_id'))
            );

            foreach ($query->rows as $row) {
                $data['by_id'][$row['category_id']] = $row;
                $data['by_parent'][$row['parent_id']][] = $row;
            }
        }

        return ((isset($data[$type]) && isset($data[$type][$id])) ? $data[$type][$id] : array());
    }

    public function getCategories($parent_id = 0, $depth = PHP_INT_MAX) {
        if (!$depth) {
            return [];
        }
        $category_data = $this->getCache()->get('category.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$parent_id);

        if (!$category_data) {
            $category_data = array();

            $query = $this->getDb()->query("
                SELECT * 
                FROM 
                    category AS c 
                    LEFT JOIN category_description AS cd ON c.category_id = cd.category_id 
                WHERE 
                    c.parent_id = :parentId
                    AND cd.language_id = :languageId
                ORDER BY c.sort_order, cd.name ASC
            ", [
                ':parentId' => $parent_id,
                ':languageId' => (int)$this->getConfig()->get('config_language_id')
            ]);

            foreach ($query->rows as $result) {
                $category_data[] = array(
                    'category_id' => $result['category_id'],
                    'name' => $result['name'],
                    'path'        => $this->getPath($result['category_id'], $this->getConfig()->get('config_language_id')),
                    'status'  	  => $result['status'],
                    'sort_order'  => $result['sort_order']
                );

                $category_data = array_merge($category_data, $this->getCategories($result['category_id'], $depth - 1));
            }

            $this->getCache()->set('category.' . (int)$this->getConfig()->get('config_language_id') . '.' . (int)$parent_id, $category_data);
        }

        return $category_data;
    }

    private function getPath($category_id, $languageId) {
        $query = $this->getDb()->query("
            SELECT name, parent_id 
            FROM 
                category AS c 
                LEFT JOIN category_description AS cd ON c.category_id = cd.category_id 
            WHERE 
                c.category_id = :categoryId
                AND cd.language_id = :languageId 
            ORDER BY c.sort_order, cd.name ASC
        ", [
            ':categoryId' => $category_id,
            ':languageId' => $languageId
        ]);

        if ($query->row['parent_id']) {
            return $this->getPath($query->row['parent_id'], $languageId) . $this->getLanguage()->get('text_separator') . $query->row['name'];
        } else {
            return $query->row['name'];
        }
    }

    public function getCategoryDescriptions($category_id) {
        $descriptionCollection = new DescriptionCollection();

        $query = $this->getDb()->query("SELECT * FROM category_description WHERE category_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $descriptionCollection->addDescription(new Description(
                $result['language_id'],
                $result['name'],
                $result['description'],
                $result['meta_description'],
                $result['meta_keyword'],
                $result['seo_title'],
                $result['seo_h1']
            ));
        }

        return $descriptionCollection;
    }

    public function getCategoryStores($category_id) {
        $category_store_data = array();

        $query = $this->getDb()->query("SELECT * FROM category_to_store WHERE category_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $category_store_data[] = $result['store_id'];
        }

        return $category_store_data;
    }

    public function getCategoryLayouts($category_id) {
        $category_layout_data = array();

        $query = $this->getDb()->query("SELECT * FROM category_to_layout WHERE category_id = '" . (int)$category_id . "'");

        foreach ($query->rows as $result) {
            $category_layout_data[$result['store_id']] = $result['layout_id'];
        }

        return $category_layout_data;
    }

    public function getTotalCategories() {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM category");

        return $query->row['total'];
    }

//    public function getTotalCategoriesByImageId($image_id) {
//        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM category WHERE image_id = '" . (int)$image_id . "'");
//
//        return $query->row['total'];
//    }

    public function getTotalCategoriesByLayoutId($layout_id) {
        $query = $this->getDb()->query("SELECT COUNT(*) AS total FROM category_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

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
            ':languageId' => $this->getConfig()->get('config_language_id')
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
                $row['affiliate_commission'],
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
        $category_data = $this->getCache()->get('category.all.' . $this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id'));

        if (!$category_data || !is_array($category_data)) {
            $query = $this->getDb()->query("SELECT * FROM category c LEFT JOIN category_description cd ON (c.category_id = cd.category_id) LEFT JOIN category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->getConfig()->get('config_store_id') . "'  ORDER BY c.parent_id, c.sort_order, cd.name");

            $category_data = array();
            foreach ($query->rows as $row) {
                $category_data[$row['parent_id']][$row['category_id']] = $row;
            }

            $this->getCache()->set('category.all.' . $this->getConfig()->get('config_language_id') . '.' . (int)$this->getConfig()->get('config_store_id'), $category_data);
        }

        return $category_data;
    }
}