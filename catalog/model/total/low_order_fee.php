<?php
use model\total\LowOrderFeeDAO;
use model\total\TotalBaseDAO;

class ModelTotalLowOrderFee extends TotalBaseDAO
{
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        LowOrderFeeDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $orderId = null, $chosenOnes = false) {
        LowOrderFeeDAO::getInstance()->getTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        LowOrderFeeDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}