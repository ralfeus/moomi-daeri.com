<?php
class ModelCatalogWkallauctions extends Model {

	public function getAuctions(){
		
		$dat = $this->getDb()->query("SELECT cp.product_id,cp.image,a.name,a.min,a.max,a.start_date,a.end_date FROM wkauction a LEFT JOIN product cp ON (a.product_id=cp.product_id) WHERE a.isauction=1");
		return $dat->rows;
    }
}