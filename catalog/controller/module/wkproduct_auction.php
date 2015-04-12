<?php
################################################################################################
#  Product auction Module for Opencart 1.5.1.x from webkul http://webkul.com      #
################################################################################################
?><?php

class ControllerModuleWkproductauction extends Controller {
	protected function index() {
		//LOAD LANGUAGE
		$this->language->load('module/wkproduct_auction');
		//SET TITLE
      		$this->data['heading_title'] = $this->language->get('heading_title');
      		$this->data['entry_auction'] = $this->language->get('entry_auction');
      		$this->data['entry_winner'] = $this->language->get('entry_winner');
      		$this->data['entry_time_left'] = $this->language->get('entry_time_left');
      		$this->data['entry_bids'] = $this->language->get('entry_bids');
      		$this->data['entry_min_price'] = $this->language->get('entry_min_price');
      		$this->data['entry_max_price'] = $this->language->get('entry_max_price');
      		$this->data['entry_start_time'] = $this->language->get('entry_start_time');
      		$this->data['entry_close_time'] = $this->language->get('entry_close_time');
      		$this->data['entry_your_price'] = $this->language->get('entry_your_price');
      		$this->data['entry_thnaks'] = $this->language->get('entry_thnaks');
      		$this->data['entry_no_bids'] = $this->language->get('entry_no_bids');
      		$this->data['entry_bids_error'] = $this->language->get('entry_bids_error');
      		$this->data['entry_ammount_error'] = $this->language->get('entry_ammount_error');
      		$this->data['entry_login_error'] = $this->language->get('entry_login_error');
      		$this->data['entry_ammount_less_error'] = $this->language->get('entry_ammount_less_error');
      		$this->data['entry_ammount_range_error'] = $this->language->get('entry_ammount_range_error');
      		
		//LOAD MODEL FILES
		$this->load->model('module/wkproduct_auction');
	    $this->document->addScript('catalog/view/javascript/wkproduct_auction/countdown.js');  
	    $this->document->addStyle('catalog/view/theme/default/stylesheet/wkauction.style.css');
        $this->load->model('catalog/product');
		$results = array();
		$this->data['winner']=0;
		$this->data['timeout']=0;
		$this->data['auction_id']='';
		$this->data['min']='';
		$this->data['max']='';
		$this->data['end']='';
		$this->data['start']='';
		if(isset($this->request->get['product_id'])){
		$results = $this->model_module_wkproduct_auction->getAuction($this->request->get['product_id']);
               }
        
        date_default_timezone_set($this->config->get('wk_auction_timezone_set'));
		//date_default_timezone_set(TIMEZONE);		
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
         		$this->data['base'] = $this->config->get('config_ssl');
      		} else {
         		$this->data['base'] = $this->config->get('config_url');
      		}
		
	        $count=0;
	        foreach ($results as $result) {
				
	            $this->data['auction_id']=$result['id'];
				$this->data['min']=$this->currency->format($result['min']);
				$this->data['max']=$this->currency->format($result['max']);
				$this->data['end']=$result['end_date'];
				$this->data['start']=$result['start_date'];
          	     $dat = date('Y-m-d H:i:s');
          	     
				  if($result['start_date']<=$dat && $result['end_date']>=$dat)
				   {
				   	//print_r($dat);
          	     	//die();
				   	$this->data['timeout']=1;
                   }
                   elseif ($count==0){
                   $this->model_module_wkproduct_auction->updateAuctionbids($this->data['auction_id']);
					 }
                   
                   $count=$count+1;
		}
		
		$bids=array();	
		
		if(isset($this->data['auction_id'])){

			$bids = $this->model_module_wkproduct_auction->getAuctionbids($this->data['auction_id']);
		}
        $this->data['my_bids']=array();
		foreach ($bids as $bid) {
				
	            $this->data['my_bids'][]=array(
	            	'user_id' =>$bid['user_id'],
                    'auction_id' =>$bid['auction_id'],
                    'user_bid' =>$bid['user_bid'],
                    'winner'=>$bid['winner'],
	            );
	           

				
          	
		}

		$this->id = 'wk_contact';


		//CHOOSE TEMPLATE
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/module/wkproduct_auction.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/module/wkproduct_auction.tpl';
		} else {
			$this->template = 'default/template/module/wkproduct_auction.tpl';
		}

		//RENDER
		$this->render();
	}

	public function wkauctionbids(){

		$this->load->model('module/wkproduct_auction');
		$this->language->load('module/wkproduct_auction');
		if(isset($this->request->post['auction']) AND isset($this->request->post['bids'])){
				
			$data = $this->request->post;
			$left = $data['left'];
         		$right = $data['right'];
         		$value = $data['value'];
			$result = $this->model_module_wkproduct_auction->wkauctionbids_viewbids($data['auction']);

		        $text_main = "<div class='bids' style='font-size:11px;'>".$this->language->get('entry_no_bids')."</div>";
		        if($result){
		        	$text = '';
			        foreach ($result as $row) {
			              $ubi=$row['user_bid']*$value;
				          $ubi=round($ubi, 2);
				          $ubid="";
				          $nickname = $row['nickname'];
				          if($left){
				          	$ubid=$left.$ubi;
				           }else{
				         	 $ubid=$ubi.$right;
				           }
				                
				          $text=$text."<div class=\"bids\" title=\"" . $nickname . "\">$ubid , Bid by ".substr($nickname,0,10)."</div>";
			          	}
			          }
		         
		          if(isset($text)){
		          	$text_main = $text;
		          }
		          $json['success'] = $text_main;
				 
		    }

		    if($this->customer->getId()){
			    if(isset($this->request->post['amount']) AND isset($this->request->post['auction'])){
			    	$data = $this->request->post;
			    	$user = (int)$this->customer->getId();
			    	$result = $this->model_module_wkproduct_auction->wkauctionbids_insertbids($data,$user);
			    	$json['success'] = $result;

			   	}
			  }

	     	$this->getResponse()->setOutput(json_encode($json));
	    
	}
}
?>
