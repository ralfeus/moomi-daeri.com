<?php
use model\total\CreditDAO;
use model\total\TotalBaseDAO;

class ModelTotalCredit extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        CreditDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        CreditDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
    }
	
	public function confirm($order_info, $order_total) {
        CreditDAO::getInstance()->confirm($order_info, $order_total);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        CreditDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}