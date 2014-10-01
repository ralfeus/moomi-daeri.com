<?php
################################################################################################
# Auction Bids Opencart 1.5.1.x From Webkul  http://webkul.com 	#
################################################################################################
class ControllerCatalogWkauctionbids extends Controller {
	
	private $error = array(); 
	
	public function index() {
		$this->language->load('catalog/wkauction_bids');
    	
		$this->document->setTitle($this->language->get('heading_title')); 
		
		$this->load->model('catalog/wkauction_bids');
		
		//$this->document->addScript('admin/view/javascript/eventmanager/jquery-ui-1.8.20.custom.min.js');
		//$this->document->addStyle('admin/view/c/default/stylesheet/wkslide_tweet.css');
		$this->getList();
  	}
  /*	
    public function insert() {
    	$this->language->load('catalog/wkauction_bids');

    	$this->document->setTitle($this->language->get('heading_title')); 
		
		$this->load->model('catalog/wkauction_bids');
		
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_eventmanager->addProduct($this->request->post);
	  		
			$this->session->data['success'] = $this->language->get('text_success');
	  
			$this->redirect($this->url->link('catalog/wkauction_bids', 'token=' . $this->session->data['token'], 'SSL'));
    	}
	
    	$this->getForm();
  	}

        
        
	
  	public function insertEvent(){
			
			$this->load->model('catalog/wkauction_bids');
			//echo 'yyyyy'.$this->request->post['nk_name'];
			$name=$this->request->post['wk_entry_name1'];
			$date=$this->request->post['wk_entry_date'];
			$desc=$this->request->post['wk_entry_desc'];
			
			$this->model_catalog_eventmanager->addEvent($name,$date,$desc);
	
			$this->getList();
			
	
	}
	public function weditEvent(){
			
			$this->load->model('catalog/wkauction_bids');
			//echo 'yyyyy'.$this->request->post['nk_name'];
			$id=$this->request->get['id'];
			$name=$this->request->post['wk_entry_name1'];
			$date=$this->request->post['wk_entry_date'];
			$desc=$this->request->post['wk_entry_desc'];
			
			$this->model_catalog_eventmanager->editEvent($id,$name,$date,$desc);
	
			$this->getList();
			
	
	}
  	
	protected function getForm() {
	
		
		$this->data['heading_title'] = $this->language->get('heading_title');		
		$this->data['entry_name'] = $this->language->get('entry_name');		
		$this->data['entry_date'] = $this->language->get('entry_date');		
		$this->data['entry_desc'] = $this->language->get('entry_desc');		
		$this->data['button_save'] = $this->language->get('button_save');	
                $this->data['button_cancel'] = $this->language->get('button_cancel');	
		
	         $config_data = array(
				
				'wk_entry_name1',
				'wk_entry_date',
				'wk_entry_desc'										
		);
		
		
		foreach ($config_data as $conf) {
			if (isset($this->request->post[$conf])) {
				
				$this->data[$conf] = $this->request->post[$conf];
			} else {
				$this->data[$conf] = $this->config->get($conf);
				
			}
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/wkauction_bids', 'token=' . $this->session->data['token'] , 'SSL'),
      		'separator' => ' :: '
   		);
									
		
		$this->data['cancel'] = $this->url->link('catalog/wkauction_bids', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['action'] = $this->url->link('catalog/wkauction_bids/insertEvent', 'token=' . $this->session->data['token'],'SSL');
		

		if (isset($this->request->get['id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
		
		          $product_info = $this->model_catalog_eventmanager->getEvent($this->request->get['id']);
		           foreach ($product_info as $result) {
				$this->data['wk_entry_name1'] = $result['name'];
				$this->data['wk_entry_date'] = $result['date'];
				$this->data['wk_entry_desc'] = $result['descs'];
				
		           }
		          $this->data['action'] = $this->url->link('catalog/wkauction_bids/weditEvent', 'token=' . $this->session->data['token']. '&id=' . $result['id'],'SSL');
    	       }

		$this->data['token'] = $this->session->data['token'];
		
		
		if (isset($this->request->post['wk_entry_name1'])) {
      		$this->data['wk_entry_name1'] = $this->request->post['wk_entry_name1'];
    	} 

		if (isset($this->request->post['wk_entry_date'])) {
      		$this->data['wk_entry_date'] = $this->request->post['wk_entry_date'];
    	} 
		
		if (isset($this->request->post['wk_entry_desc'])) {
      		$this->data['wk_entry_desc'] = $this->request->post['wk_entry_desc'];
    	} 
		$this->load->model('design/layout');
		
		$this->data['layouts'] = $this->model_design_layout->getLayouts();
										
		$this->template = 'catalog/events_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	} */
	protected function getList() {
		$this->language->load('catalog/wkauction_bids');
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('catalog/wkauction_bids', 'token=' . $this->session->data['token'] , 'SSL'),       		
      		'separator' => ' :: '
   		);
		
		//$this->data['insert'] = $this->url->link('catalog/wkauction_bids/insert', 'token=' . $this->session->data['token'], 'SSL');
		//$this->data['copy'] = $this->url->link('catalog/eventmanager/copy', 'token=' . $this->session->data['token'] . $url, 'SSL');	
		$this->data['delete'] = $this->url->link('catalog/wkauction_bids/delete', 'token=' . $this->session->data['token'] , 'SSL');
    	
		$this->data['bids'] = array();

		$data1 = array();
		
		//$product_total = $this->model_catalog_eventmanager->getTotalEvents($data1);
		
		$results = $this->model_catalog_wkauction_bids->getBids($data1);
		
		$this->data['heading_title'] = $this->language->get('heading_title');		
		$this->data['entry_name'] = $this->language->get('entry_name');		
		$this->data['entry_prod'] = $this->language->get('entry_prod');		
		$this->data['entry_amt'] = $this->language->get('entry_amt');	
		$this->data['entry_cus'] = $this->language->get('entry_cus');	
		$this->data['entry_dat'] = $this->language->get('entry_dat');		
		$this->data['entry_start'] = $this->language->get('entry_start');	
		$this->data['entry_end'] = $this->language->get('entry_end');		
		$this->data['button_insert'] = $this->language->get('button_insert');		
		$this->data['button_delete'] = $this->language->get('button_delete');		
		$this->data['entry_winner'] = $this->language->get('entry_winner');
		$this->data['entry_sold'] = $this->language->get('entry_sold');
	        foreach ($results as $result) {
			/*$action = array();
			
				$action[] = array(
					'text' => $this->language->get('text_edit'),
					'href' => $this->url->link('catalog/wkauction_bids/update', 'token=' . $this->session->data['token'] . '&id=' . $result['id'], 'SSL')
				);*/
				
	       
      		$this->data['bids'][] = array(
				
				'selected'=>False,
				'id' => $result['id'],
				'product' => $result['name'],
				'customer' => $result['firstname'].' '.$result['lastname'],
				'date'       => $result['date'],
				'auction_start' => $result['start_date'],
				'auction_end'  => $result['end_date'],
				'amount'     => $result['user_bid'],
				'winner'     => $result['winner'],
				'sold'     => $result['sold']
			);
          	
		}
		
		
 		$this->data['token'] = $this->session->data['token'];
		
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
		
		$this->data['sort_name'] = $this->url->link('catalog/wkauction_bids', 'token=' . $this->session->data['token'] , 'SSL');
		
		/*$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = "Showing";
		$pagination->url = $this->url->link('catalog/wkauction_bids', 'token=' . $this->session->data['token'] . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();*/
		
		$this->template = 'catalog/wkauction_bidslist.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
  	}
/*
  	public function update() {
    	$this->language->load('catalog/wkauction_bids');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/eventmanager');
	
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_eventmanager->editEvent($this->request->get['id'], $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');
			
			$url = '';
			

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('catalog/eventmanager', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

    	$this->getForm();
  	}
*/
  	
  	public function delete() {
    	$this->language->load('catalog/wkauction_bids');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('catalog/wkauction_bids');
		
		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $id) {
				$this->model_catalog_wkauction_bids->deleteBid($id);
	  		}

			$this->session->data['success'] = $this->language->get('text_success');
			
			$url='';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->redirect($this->url->link('catalog/wkauction_bids', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}

    	$this->getList();
  	}
  	/*
	private function validateForm() { 
		if (!$this->user->hasPermission('modify', 'catalog/eventmanager')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		
			
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
						
		if (!$this->error) {
				return true;
		} else {
			return false;
		}
		}*/
	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'catalog/wkauction_bids')) {
      		$this->error['warning'] = $this->language->get('error_permission');  
    	}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}
  	
}
?>