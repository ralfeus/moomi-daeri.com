<?php
namespace model\total;

class RewardDAO extends TotalBaseDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        $this->getOrderExtensionTotal($totalData, $total, $taxes, $orderId, 'reward', $chosenOnes);
    }

    public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
		if (isset($this->session->data['reward'])) {
			$this->load->language('total/reward');
			
			$points = $this->customer->getRewardPoints();
			
			if ($this->session->data['reward'] <= $points) {
				$discount_total = 0;
				
				$points_total = 0;
				
				foreach ($this->cart->getProducts($chosenOnes) as $product) {
					if ($product['points']) {
						$points_total += $product['points'];
					}
				}	
				
				$points = min($points, $points_total);
		
				foreach ($this->cart->getProducts($chosenOnes) as $product) {
					$discount = 0;
					
					if ($product['points']) {
						$discount = $product['total'] * ($this->session->data['reward'] / $points_total);
						
						if ($product['tax_class_id']) {
							$tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);
							
							foreach ($tax_rates as $tax_rate) {
								if ($tax_rate['type'] == 'P') {
									$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
								}
							}	
						}
					}
					
					$discount_total += $discount;
				}
			
				$totalData[] = array(
					'code'       => 'reward',
        			'title'      => sprintf($this->language->get('text_reward'), $this->session->data['reward']),
	    			'text'       => $this->currency->format(-$discount_total),
        			'value'      => -$discount_total,
					'sort_order' => $this->config->get('reward_sort_order')
      			);

				$total -= $discount_total;
			} 
		}
	}
	
	public function confirm($order_info, $order_total) {
		$this->load->language('total/reward');
		
		$points = 0;
		
		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');
		
		if ($start && $end) {  
			$points = substr($order_total['title'], $start, $end - $start);
		}	
		
		if ($points) {
			$this->getDb()->query("INSERT INTO customer_reward SET customer_id = '" . (int)$order_info['customer_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', description = '" . $this->getDb()->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "', points = '" . (float)-$points . "', date_added = NOW()");
		}
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'reward');
    }
}