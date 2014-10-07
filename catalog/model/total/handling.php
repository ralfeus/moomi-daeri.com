<?php
use model\total\HandlingDAO;
use model\total\TotalBaseDAO;

class ModelTotalHandling extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        HandlingDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

    public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        HandlingDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        HandlingDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}