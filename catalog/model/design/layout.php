<?php
//class ModelDesignLayout extends \system\engine\Model {
//	public function getLayout($route) {
//		$query = $this->getDb()->query("SELECT * FROM layout_route WHERE '" . $this->getDb()->escape($route) . "' LIKE CONCAT(route, '%') AND store_id = '" . (int)$this->config->get('config_store_id') . "' ORDER BY route ASC LIMIT 1");
//
//		if ($query->num_rows) {
//			return $query->row['layout_id'];
//		} else {
//			return 0;
//		}
//	}
//}