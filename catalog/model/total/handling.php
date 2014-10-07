<?php
use model\total\TotalDAO;

class ModelTotalHandling extends TotalDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        $this->getOrderExtensionTotal($totalData, $total, $taxes, $orderId, 'handling');
    }

    public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
		if (($this->cart->getSubTotal($chosenOnes) < $this->config->get('handling_total'))
            && ($this->cart->getSubTotal($chosenOnes) > 0))
        {
			$this->load->language('total/handling');
		 	
			$totalData[] = array(
				'code'       => 'handling',
        		'title'      => $this->language->get('text_handling'),
        		'text'       => $this->currency->format($this->config->get('handling_fee')),
        		'value'      => $this->config->get('handling_fee'),
				'sort_order' => $this->config->get('handling_sort_order')
			);

			if ($this->config->get('handling_tax_class_id')) {
				$tax_rates = $this->tax->getRates($this->config->get('handling_fee'), $this->config->get('handling_tax_class_id'));
				
				foreach ($tax_rates as $tax_rate) {
					if (!isset($taxes[$tax_rate['tax_rate_id']])) {
						$taxes[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					} else {
						$taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
					}
				}
			}
			
			$total += $this->config->get('handling_fee');
		}
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'credit');
    }
}