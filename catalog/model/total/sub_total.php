<?php
require_once('ModelTotal.php');
class ModelTotalSubTotal extends ModelTotal {
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        /// Calculate subtotal of the cart
        $tempTotalData = array();
        $tempTotal = 0;
        $tempTaxes = 0;
        $this->getTotal($tempTotalData, $tempTotal, $tempTaxes, $chosenOnes);

        /// Calculate subtotal of order items in the database
        $this->load->language('total/sub_total');

        $query = $this->db->query("
            SELECT sum(total) AS total
            FROM " . DB_PREFIX . "order_product
            WHERE order_id = " . (int)$orderId
        );
        if ($query->num_rows)
            $sub_total = $query->row['total'];
        else
            $sub_total = 0;

        $query = $this->db->query("
            SELECT sum(amount) AS total
            FROM " . DB_PREFIX . "voucher_history
            WHERE order_id = " . (int)$orderId
        );
        if ($query->num_rows)
            $sub_total += $query->row['total'];

        $tempOrderTotalData = array(
            'code'       => 'sub_total',
            'title'      => $this->language->get('text_sub_total'),
            'text'       => $this->currency->format($sub_total),
            'value'      => $sub_total,
            'sort_order' => $this->config->get('sub_total_sort_order')
        );
        $tempOrderTotal = $sub_total;

        /// Calculate total subtotal
        $tempTotalData[0]['value'] += $tempOrderTotalData['value'];
        $tempTotalData[0]['text'] = $this->currency->format($tempTotalData[0]['value']);
        $tempTotal += $tempOrderTotalData['value'];
        /// Pass total subtotal to reference variables
        $totalData[] = $tempTotalData[0];
        $total += $tempTotal;
        $taxes += $tempTaxes;
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
		$this->load->language('total/sub_total');

	    $sub_total = $this->cart->getSubTotal($chosenOnes);
		
		if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
			foreach ($this->session->data['vouchers'] as $voucher) {
				$sub_total += $voucher['amount'];
			}
		}
		
		$totalData[] = array(
			'code'       => 'sub_total',
			'title'      => $this->language->get('text_sub_total'),
			'text'       => $this->currency->format($sub_total),
			'value'      => $sub_total,
			'sort_order' => $this->config->get('sub_total_sort_order')
		);
		
		$total += $sub_total;
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'sub_total');
    }
}