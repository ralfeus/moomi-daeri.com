<?php
use model\total\SubtotalDAO;
use model\total\TotalBaseDAO;

class ModelTotalSubTotal extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        SubtotalDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        SubtotalDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        SubtotalDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}