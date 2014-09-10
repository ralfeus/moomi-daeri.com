<?php 
class ModelPaymentPPStandard extends Model {
  	public function getMethod($address, $total) {
		$this->load->language('payment/pp_standard');
		
		$query = $this->db->query("SELECT * FROM zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('pp_standard_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
		
		if ($this->config->get('pp_standard_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('pp_standard_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}	
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'pp_standard',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('pp_standard_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
?>