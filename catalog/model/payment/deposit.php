<?php 
class ModelPaymentDeposit extends Model {
  	public function getMethod($address, $total) {
		$this->load->language('payment/deposit');
		
		$status = true;
		
		$method_data = array();
	
		if ($status) {  
      		$method_data = array( 
        		'code'       => 'deposit',
        		'title'      => $this->language->get('text_title'),
				'sort_order' => $this->config->get('deposit_sort_order')
      		);
    	}
   
    	return $method_data;
  	}
}
?>