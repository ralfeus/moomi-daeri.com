<?php
namespace model\total;

class TaxDAO extends TotalBaseDAO {
	public function getTotal(&$total_data, &$total, &$taxes, $chosenOnes = false) {
		foreach ($taxes as $key => $value) {
			if ($value > 0) {
                $total_data[] = array(
					'code'       => 'tax',
					'title'      => $this->tax->getRateName($key), 
					'text'       => $this->getCurrency()->format($value),
					'value'      => $value,
					'sort_order' => $this->config->get('tax_sort_order')
				);

				$total += $value;
			}
		}
	}

    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false) {
        // TODO: Implement getOrderTotal() method.
    }

    public function updateOrderTotal($orderId, $totalData) {
        // TODO: Implement updateOrderTotal() method.
    }
}