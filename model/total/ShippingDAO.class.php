<?php
namespace model\total;

class ShippingDAO extends TotalBaseDAO
{
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        $this->getOrderExtensionTotal($totalData, $total, $taxes, $orderId, 'shipping', $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false)
    {
		if ($this->cart->hasShipping() && isset($this->session->data['shipping_method'])) {
			$totalData[] = array(
				'code'       => 'shipping',
        		'title'      => $this->session->data['shipping_method']['title'],
        		'text'       => $this->currency->format($this->session->data['shipping_method']['cost']),
        		'value'      => $this->session->data['shipping_method']['cost'],
				'sort_order' => $this->config->get('shipping_sort_order')
			);

			if ($this->session->data['shipping_method']['tax_class_id']) {
				$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);
				
				foreach ($tax_rates as $tax_rate) {
					if (!isset($taxes[$tax_rate['tax_rate_id']])) {
						$taxes[$tax_rate['tax_rate_id']] =  $tax_rate['amount'];
					} else {
						$taxes[$tax_rate['tax_rate_id']] +=  $tax_rate['amount'];
					}
				}
			}
			
			$total += $this->session->data['shipping_method']['cost'];
		}			
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'shipping');
    }
}