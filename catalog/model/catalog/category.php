<?php
use system\engine\Model;

class ModelCatalogCategory extends \system\engine\Model {
	public function getCategory($category_id) {
		return $this->getCategories((int)$category_id, 'by_id');
	}

	public function getCategories($id = 0, $type = 'by_parent') {
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

	public function getCategoriesByParentId($category_id) {
		$category_data = array();

		$categories = $this->getCategories((int)$category_id);

		foreach ($categories as $category) {
			$category_data[] = $category['category_id'];

			$children = $this->getCategoriesByParentId($category['category_id']);

			if ($children) {
				$category_data = array_merge($children, $category_data);
			}
		}

		return $category_data;
	}

    /**
     * @param int $category_id
     * @return int
     */
	public function getCategoryLayoutId($category_id) {
		$query = $this->getDb()->query("
		    SELECT *
		    FROM category_to_layout
		    WHERE category_id = ? AND store_id = ?
		    ", array('i:' . $category_id, 'i:' . $this->config->get('config_store_id'))
        );

		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->config->get('config_layout_category');
		}
	}

	public function getTotalCategoriesByCategoryId($parent_id = 0) {
		return count($this->getCategories((int)$parent_id));
	}
}
?>