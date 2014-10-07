<?php
use model\total\TotalBaseDAO;
use model\total\VoucherDAO;

class ModelTotalVoucher extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        VoucherDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$total_data, &$total, &$taxes, $chosenOnes = false) {
        VoucherDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
	}
	
	public function confirm($order_info, $order_total) {
        VoucherDAO::getInstance()->confirm($order_info, $order_total);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        VoucherDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}