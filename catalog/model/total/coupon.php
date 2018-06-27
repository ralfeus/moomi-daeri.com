<?php
use model\total\CouponDAO;
use model\total\TotalBaseDAO;

/**
 * Class ModelTotalCoupon
 * @deprecated
 */
class ModelTotalCoupon extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false) {
        CouponDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        CouponDAO::getInstance()->getTotal($totalData, $total, $taxes, $chosenOnes);
	}
	
	public function confirm($order_info, $order_total) {
        CouponDAO::getInstance()->confirm($order_info, $order_total);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        CouponDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}