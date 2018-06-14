<?php
use model\total\TaxDAO;

class ModelTotalTax extends \system\engine\Model {
	public function getTotal(&$total_data, &$total, &$taxes) {
		TaxDAO::getInstance()->getTotal($total_data, $total, $taxes);
	}
}