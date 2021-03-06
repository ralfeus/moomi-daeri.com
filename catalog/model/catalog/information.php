<?php
use system\engine\Model;

class ModelCatalogInformation extends Model {
	public function getInformation($information_id) {
		$query = $this->getDb()->query("SELECT DISTINCT * FROM information i LEFT JOIN information_description id ON (i.information_id = id.information_id) LEFT JOIN information_to_store i2s ON (i.information_id = i2s.information_id) WHERE i.information_id = '" . (int)$information_id . "' AND id.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "' AND i2s.store_id = '" . (int)$this->getConfig()->get('config_store_id') . "' AND i.status = '1'");
	
		return $query->row;
	}
	
	public function getInformations() {
		$query = $this->getDb()->query("
			SELECT *
			FROM
				information AS i
				LEFT JOIN information_description AS id ON (i.information_id = id.information_id)
				LEFT JOIN information_to_store AS i2s ON (i.information_id = i2s.information_id)
			WHERE
				i.parent_node_id IS NULL
				AND id.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'
				AND i2s.store_id = '" . (int)$this->getConfig()->get('config_store_id') . "'
				AND i.status = '1' AND i.sort_order <> '-1'
			ORDER BY i.sort_order, LCASE(id.title) ASC");
		
		return $query->rows;
	}

	public function getFooterInformations() {
		$query = $this->getDb()->query("
			SELECT i.information_id, id.title
			FROM
				information AS i
				LEFT JOIN information_description AS id ON (i.information_id = id.information_id)
				LEFT JOIN information_to_store AS i2s ON (i.information_id = i2s.information_id)
			WHERE
				(i.parent_node_id IS NULL OR i.parent_node_id = 0)
				AND id.language_id = '" . (int)$this->getConfig()->get('config_language_id') . "'
				AND i2s.store_id = '" . (int)$this->getConfig()->get('config_store_id') . "'
				AND i.status = '1' AND i.sort_order <> '-1'
				AND i.information_id IN (4, 6, 3, 5, 10)");
		$sort = array(4, 6, 3, 5, 10);
		$result = array();
		foreach ($sort as $id) {
			foreach ($query->rows as $key => $row) {
				if ($row['information_id'] == $id) {
					$result[] = $row;
					unset($query->rows[$key]);
					break;
				}
			}
		}
		return $result;
	}

	public function getInformationLayoutId($information_id) {
		$query = $this->getDb()->query("SELECT * FROM information_to_layout WHERE information_id = '" . (int)$information_id . "' AND store_id = '" . (int)$this->getConfig()->get('config_store_id') . "'");
		 
		if ($query->num_rows) {
			return $query->row['layout_id'];
		} else {
			return $this->getConfig()->get('config_layout_information');
		}
	}	
}