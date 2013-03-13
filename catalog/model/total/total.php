<?php
require_once('ModelTotal.php');
class ModelTotalTotal extends ModelTotal
{
    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false)
    {
        $this->getTotal($totalData, $total, $taxes, $chosenOnes);
    }

	public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false)
    {
		$this->load->language('total/total');
	 
		$totalData[] = array(
			'code'       => 'total',
			'title'      => $this->language->get('text_total'),
			'text'       => $this->currency->format(max(0, $total)),
			'value'      => max(0, $total),
			'sort_order' => $this->config->get('total_sort_order')
		);
	}

    public function updateOrderTotal($orderId, $totalData)
    {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'total');
        $this->db->query("
            UPDATE `" . DB_PREFIX . "order`
            SET total = " . $totalData['value'] . "
            WHERE order_id = " . (int)$orderId
        );
    }
}