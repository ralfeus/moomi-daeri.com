<?php
use model\total\TotalBaseDAO;
use model\total\TotalDAO;

class ModelTotalTotal extends TotalBaseDAO
{
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        TotalDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false)
    {
        TotalDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $chosenOnes);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        TotalDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}