<?php
use system\engine\Controller;

class ControllerCheckoutAddToOrder extends \system\engine\Controller {
	private $modelCheckoutOrder;
	
	public function __construct($registry)
	{
		parent::__construct($registry);
//		$this->log->write("Instantiating ControllerCheckoutAddToOrder");
		$this->load->language('checkout/add_to_order');
		
		$this->data['labels']['field_model'] = $this->language->get('field_model');
		$this->data['labels']['field_name'] = $this->language->get('field_name');
		$this->data['labels']['field_price'] = $this->language->get('field_price');
		$this->data['labels']['field_quantity'] = $this->language->get('field_quantity');
		$this->data['labels']['field_total'] = $this->language->get('field_total');
		$this->data['labels']['button_continue'] = $this->language->get('CONFIRM');
	}
	
	public function confirm() {
		$this->modelCheckoutOrder = $this->load->model('checkout/order');
        $orderItems = array();
		foreach ($this->cart->getProducts(true) as $orderItem) {
			$orderItems[] = array(
				'product_id'	=> $orderItem['product_id'],
				'name'			=> $orderItem['name'],
				'model'			=> $orderItem['model'],
				'quantity'		=> $orderItem['quantity'],
				'price'			=> $orderItem['price'],
				'total'			=> $orderItem['total'],
				'tax'			=> 0,
				'options'		=> $orderItem['option'],
				'download'		=> $orderItem['download']
			);
		}
		$this->modelCheckoutOrder->addOrderItems($this->request->request['order_id'], $orderItems);
		$this->cart->clear(true);
//        $json = array();
//        $json['output'] = $this->render();
//        $this->getResponse()->setOutput(json_encode($json));
	}
	
	public function index()
	{
		foreach ($this->cart->getProducts(true) as $order_item)
		{
			$this->data['order_items'][] = array(
				'href'		=> $this->url->link('product/product', 'product_id=' . $order_item['product_id']),
				'model'		=> $order_item['model'],
				'name'		=> $order_item['name'],
				'options'	=> $order_item['option'],
				'price'		=> $this->currency->format($order_item['price']),
				'quantity'	=> $order_item['quantity'],
				'total'		=> $this->currency->format($order_item['total'])
			);
			
		}
		if (!$this->parameters['order_id'])
			return;
		$this->data['order_id'] = $this->parameters['order_id'];
		$this->data['labels']['text_add_to_order'] = sprintf($this->language->get('text_add_to_order'), $this->parameters['order_id']);
        $this->data['urlConfirm'] = $this->url->link('checkout/add_to_order/confirm', 'order_id=' . $this->parameters['order_id'], 'SSL');
        $this->data['urlSuccess'] = $this->url->link('checkout/success', '', 'SSL');

		$templateFileName = '/template/checkout/add_to_order.tpl';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateFileName))
			$this->template = $this->config->get('config_template') . $templateFileName;
		else
			$this->template = 'default' . $templateFileName;
//		$this->log->write(print_r($this->data, true));
		$json['output'] = $this->render();
//		$this->log->write(print_r($json, true));
		$this->getResponse()->setOutput(json_encode($json));
	}

    protected function initParameters()
    {
        $this->parameters['order_id'] = empty($_REQUEST['order_id']) ? null : $_REQUEST['order_id'];
//        $this->log->write(print_r($this->parameters, true));
    }
}