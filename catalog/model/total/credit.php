<?php
use model\total\TotalDAO;

class ModelTotalCredit extends TotalDAO {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        $this->getOrderExtensionTotal($totalData, $total, $taxes, $orderId, 'credit');
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
//        $this->log->write($total);
		if ($this->config->get('credit_status')) {
			$this->load->language('total/credit');
		 
			$balance = $this->currency->convert(
                $this->customer->getBalance(),
                $this->customer->getBaseCurrency()->getCode(),
                $this->config->get('config_currency'));
			
			if ((float)$balance) {
				if ($balance > $total) {
					$credit = $total;	
				} else {
					$credit = $balance;	
				}
				
				if ($credit > 0) {
					$totalData[] = array(
						'code'       => 'credit',
						'title'      => $this->language->get('text_credit'),
						'text'       => $this->currency->format(-$credit),
						'value'      => -$credit,
						'sort_order' => $this->config->get('credit_sort_order')
					);
					
					$total -= $credit;
				}
			}
		}
	}
	
	public function confirm($order_info, $order_total) {
		$this->load->language('total/credit');
		
		if ($order_info['customer_id']) {
			$this->db->query("
			    INSERT INTO customer_transaction
			    SET
			        customer_id = '" . (int)$order_info['customer_id'] . "',
			        order_id = '" . (int)$order_info['order_id'] . "',
			        description = '" . $this->db->escape(sprintf($this->language->get('text_order_id'), (int)$order_info['order_id'])) . "',
			        amount = '" . (float)$order_total['value'] . "',
			        date_added = NOW()
            ");
            /// Update customer's balance
            $this->db->query("
                UPDATE customer
                SET
                    balance = balance - " . (float)$order_total['value'] . "
                WHERE customer_id = " . (int)$order_info['customer_id']
            );
		}
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'credit');
    }
}