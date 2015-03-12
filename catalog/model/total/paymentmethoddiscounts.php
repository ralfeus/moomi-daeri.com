<?php
use model\total\PaymentMethodDiscountsDAO;
use model\total\TotalBaseDAO;

class ModelTotalPaymentMethodDiscounts extends TotalBaseDAO {
    /**
     * @param $total_data
     * @param $total
     * @param $taxes
     * @param bool $chosenOnes
     */
    public function getTotal(&$total_data, &$total, &$taxes, $chosenOnes = false) {
        PaymentMethodDiscountsDAO::getInstance()->getTotal($total_data, $total, $taxes, $chosenOnes);
	}

    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false) {
        PaymentMethodDiscountsDAO::getInstance()->getOrderTotal($totalData, $total, $taxes, $orderId, $chosenOnes);
    }

    public function updateOrderTotal($orderId, $totalData) {
        PaymentMethodDiscountsDAO::getInstance()->updateOrderTotal($orderId, $totalData);
    }
}