<?php
class ModelPaymentWebmoneyWMU extends Model {
	public function getMethod($address, $total) {
		$this->load->language('payment/webmoney_wmu');
		
		if ($this->config->get('webmoney_wmu_status')) {
		$query = $this->db->query("SELECT * FROM zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('webmoney_wmu_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
			
			if (!$this->config->get('webmoney_wmu_geo_zone_id')) {
				$status = TRUE;
			} elseif ($query->num_rows) {
				$status = TRUE;
			} else {
				$status = FALSE;
			}
		} else {
			$status = FALSE;
		}
		
		$method_data = array();
		
		if ($status) {
			$method_data = array(
				'code'         => 'webmoney_wmu',
				'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('webmoney_wmu_sort_order')
			);
		}
		return $method_data;
	}
}
?>