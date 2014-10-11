<?php
use model\total\CouponDAO;
use model\total\LocalShippingDAO;
use model\total\TotalBaseDAO;

class ModelTotalLocalShipping extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false) {
        LocalShippingDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        LocalShippingDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
	}
	
    public function updateOrderTotal($orderId, $totalData)
    {
        LocalShippingDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}