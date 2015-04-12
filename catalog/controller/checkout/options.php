<?php

/**
 * class options
 *
 * Provides checkout options 
 *
 * @author:
*/
class ControllerCheckoutOptions extends Controller
{
	private $orderModel;

	/**
	 * options constructor
	 *
	 * @param 
	 */
	function __construct($registry) 
	{
		parent::__construct($registry);
		$this->log->write("Instantiating ControllerCheckoutOptions");
		$this->orderModel = $this->load->model('account/order');
		$this->load->language('checkout/options');
		
		$this->data['labels']['button_continue'] = $this->language->get('button_continue');
		$this->data['labels']['date_added'] = $this->language->get('field_date_added');
		$this->data['labels']['new_order'] = $this->language->get('text_new_order');
		$this->data['labels']['order_id'] = $this->language->get('field_order_id');
		$this->data['labels']['shipping_address'] = $this->language->get('field_shipping_address');
		$this->data['labels']['shipping_method'] = $this->language->get('field_shipping_method');
        $this->data['textOrderTotal'] = $this->language->get('TOTAL');
	}
	
	/**
	 * index
	 */
	public function index()
	{
		$orders = $this->orderModel->getOrders();
		foreach ($orders as $order)
		{
			if ($order['order_status_id'] == 2)
			{
				$fullOrder = $this->orderModel->getOrder($order['order_id']);
				$this->data['open_orders'][] = array(
					'date_added'			=> $order['date_added'],
					'order_id'				=> $order['order_id'],
					'order_items_quantity'	=> $this->orderModel->getTotalOrderProductsByOrderId($order['order_id']),
                    'orderTotal' => $this->currency->format($order['total']),
					'shipping_address'		=> sprintf(
							"%s %s<br />%s<br />%s<br />%s<br />%s<br />%s<br />%s", 
							$fullOrder['shipping_firstname'], $fullOrder['shipping_lastname'],
							$fullOrder['shipping_company'],
							$fullOrder['shipping_address_1'],
							$fullOrder['shipping_address_2'],
							$fullOrder['shipping_postcode'],
							$fullOrder['shipping_city'],
							$fullOrder['shipping_country']),
					'shipping_method'		=>\Shipping::getName($fullOrder['shipping_method'], $this->registry)
				);
			}
		}
		
		$templateFileName = '/template/checkout/options.tpl';
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateFileName))
			$this->template = $this->config->get('config_template') . $templateFileName;
		else
			$this->template = 'default' . $templateFileName;
			
		$json['output'] = $this->render();
		//$this->log->write(print_r($json, true));
		$this->getResponse()->setOutput(json_encode($json));
	}
}