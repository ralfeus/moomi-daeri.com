<?php
class ModelCatalogWkallauctions extends Model {

	public function getAuctions(){
		
		$dat = $this->db->query("SELECT cp.product_id,cp.image,a.name,a.min,a.max,a.start_date,a.end_date FROM " . DB_PREFIX . "wkauction a LEFT JOIN " . DB_PREFIX . "product cp ON (a.product_id=cp.product_id) WHERE a.isauction=1");
		return $dat->rows;
}
}
?>