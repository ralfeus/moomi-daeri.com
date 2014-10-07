<?php
use model\total\ShippingDAO;
use model\total\TotalBaseDAO;

class ModelTotalShipping extends TotalBaseDAO
{
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        ShippingDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false)
    {
        ShippingDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        ShippingDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}