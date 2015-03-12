<?php
namespace model\total;

use model\sale\OrderDAO;

class PaymentMethodDiscountsDAO extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false) {
        $order = OrderDAO::getInstance()->getOrder($orderId);
    }

    public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        //print_r($this->config->get('paymentmethoddiscounts_discount'));
        //print_r($this->getSession()->data);

        if(isset($this->getSession()->data['payment_method'])){

            $paymentMethod = $this->getSession()->data['payment_method']['code'];

            //$this->load->model('checkout/order');
            //$order=$this->model_checkout_order->getOrder($this->getSession()->data['order_id']);
//print_r($order);

            $discount = $this->getPaymentMethodDiscountByCode($paymentMethod);
            $this->load->language('total/paymentmethoddiscounts');

            $number=0;

            if($discount['znak']){
                if($discount['mode']) $number = -$total*$discount['number']/100; // -%
                else  $number = -$discount['number']; // -ed
            }else{
                if($discount['mode']) $number =  $total*$discount['number']/100; // +%
                else  $number =  $discount['number']; // +ed
            }

            $totalData[] = array(
                'code'       => 'paymentmethoddiscounts',
                'title'      => ($discount['znak']?$this->language->get('text_skidka'):$this->language->get('text_nacenka')).sprintf($this->language->get('text_paymentmethoddiscounts'), $paymentMethod),
                'text'       => $this->getCurrency()->format($number),
                'value'      => $number,
                'sort_order' => $this->config->get('paymentmethoddiscounts_sort_order')
            );
//print_r($total_data);
            if ($discount['tax_class_id']) {
                $tax_rates = $this->tax->getRates($number, $discount['tax_class_id']);

                foreach ($tax_rates as $tax_rate) {
                    if (!isset($taxes[$tax_rate['tax_rate_id']])) {
                        $taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
                    } else {
                        $taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                    }
                }
            }

            $total += $number;
        }
    }

    public function updateOrderTotal($orderId, $totalData) {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'paymentmethoddiscounts');
    }

    /**
     * @param string $paymentMethodCode
     * @return array
     */
    private function getPaymentMethodDiscountByCode($paymentMethodCode) {
        $discounts = $this->config->get('paymentmethoddiscounts_discount');
        foreach($discounts as $discount) {
            if ($discount['paymentmethod'] == $paymentMethodCode) {
                return $discount;
            }
        }
        return array();
    }
}