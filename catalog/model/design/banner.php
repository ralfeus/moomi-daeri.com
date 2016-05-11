<?php
//class ModelDesignBanner extends Model {
//	public function getBanner($banner_id) {
//		$query = $this->getDb()->query("SELECT * FROM banner_image bi LEFT JOIN banner_image_description bid ON (bi.banner_image_id  = bid.banner_image_id) WHERE bi.banner_id = '" . (int)$banner_id . "' AND bid.language_id = '" . (int)$this->config->get('config_language_id') . "'");
//
//		return $query->rows;
//	}
//}