<?php
use model\total\RewardDAO;
use model\total\TotalBaseDAO;

class ModelTotalReward extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        RewardDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

    public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        RewardDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
	}
	
	public function confirm($order_info, $order_total) {
        RewardDAO::getInstance()->confirm($order_info, $order_total);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        RewardDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}