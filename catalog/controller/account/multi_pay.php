<?php 


class ControllerAccountMultiPay extends Controller {

	public function cancel_ok() {

		$this->load->helper('multi_pay');
		$customer_id = $this->customer->isLogged();
		if (!$customer_id) {
			$this->session->data['redirect'] = $this->url->link(_url($_GET), 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
		$order_id = _g($_GET,'order_id',_g($this->session->data,'order_id'));
		if (!$order_id) $this->redirect($this->url->link('account/order', '', 'SSL'));

		$this->load->model('account/order');
		$order_info = $this->model_account_order->getOrder($order_id);

		$this->language->load('account/multi_pay_cancel');
		$this->session->data['note'] = _note($this->language->get('note_order_cant_cancel'), 'warning');

		// надо взять инфо по заказу
		if ($order_info['order_status_id'] < 3) {
			// пользователь может сам отменить только заказы в ожидании и в обработке
			// хотя это можно задать в настройках
			if ($order_info['order_status_id'] == 2 ) {
				// этот заказ при отмене должен вернуть потраченный на него депозит
				$this->load->model('payment/multi_pay');
				$this->model_payment_multi_pay->cancel_order($order_id,
						$this->language->get('text_canceled_by'));
				$this->session->data['note'] = _note($this->language->get('note_order_canceled'), 'success');
			}
		}
		
		$this->redirect($this->url->link('account/order/info', '&order_id=' . $order_id , 'SSL'));
	}

	public function cancel() {
	
		$this->load->helper('multi_pay');
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link(_url($_GET), 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
		$order_id = _g($_GET,'order_id',_g($this->session->data,'order_id'));
		if (!$order_id) $this->redirect($this->url->link('account/order', '', 'SSL'));
	
		$this->session->data['order_id'] = $order_id;

		//подключаем файл перевода
		$this->language->load('account/account');
		$this->language->load('account/order');
		$this->language->load('account/multi_pay_cancel');
		
		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['breadcrumbs'] = array(); 

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => false
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
			'separator' => $this->language->get('text_separator')
		);
		$url = $this->url->link('account/order/info', '&order_id='.$order_id, 'SSL');
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_order'),
			'href'      => $url,
			'separator' => $this->language->get('text_separator')
		);

		foreach (array('heading_title', 'text_title', 'button_cancel')  as $key ) {
			$this->data[$key] = $this->language->get($key);
		}
		$this->data['heading_title'] = sprintf($this->data['heading_title'],
				'<a href="' . $url . '">' . $order_id . '</a>');

		$this->template = _tpl('/template/common/default.tpl', $this->config->get('config_template'));

		$this->children = array(
			//'common/column_left',
			//'common/column_right',
			'common/content_top',
			//'common/content_bottom',
			//'common/footer',
			'common/header'			
		);
		$this->data['column_left'] = '';
		$this->data['column_right'] = '';
		$this->data['content_bottom'] = '';
		$this->data['footer'] = '';

		$b = sprintf('<h3>' . $this->language->get('text_order') . '</h3>',
			'<a href="' . $url . '">' . $order_id . '</a>');
		$b .= $this->language->get('text_question');
		$b .= sprintf(
			'<div class="buttons">
				<div class="right"><a href="%s" class="button">%s</a> &nbsp;&nbsp;&nbsp;<a href="%s" class="button">%s</a></div>
			</div>'
			, $this->url->link('account/order/info', '&order_id='.$order_id, 'SSL')
			, $this->language->get('button_not_cancel')
			, $this->url->link('account/multi_pay/cancel_ok', '&order_id='.$order_id, 'SSL')
			, $this->language->get('button_cancel'));
		$this->data['body'] = $b;
		
		
		$this->response->setOutput($this->render());
	}
	
	// открыть выбор способов оплаты для данного заказа
	public function order() {
	
		$this->load->helper('multi_pay');
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link(_url($_GET), 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
	
		$order_id = _g($_GET,'order_id',_g($this->session->data,'order_id'));
		if (!$order_id) $this->redirect($this->url->link('account/order', '', 'SSL'));
		$this->session->data['order_id'] = $order_id;

		$this->language->load('account/multi_pay_order');
		$this->load->model('account/order');
		$order_info = $this->model_account_order->getOrder($order_id);
		$total = $order_info['total'];
		//trigger_error ( 'total:' . $total, E_USER_NOTICE );
		// если заказ с 0 оплатой или уже оплачен - то игнорируем
		if ($total==0 || $order_info['order_status_id'] !=1 ) {
			$this->session->data['note'] = _note($this->language->get('note_cant_pay'),'warning');
			$this->redirect($this->url->link('account/order/info', '&order_id=' .$order_id, 'SSL'));
		}

		//подключаем файл перевода
		$this->language->load('account/order');
		
		$this->load->model('account/order');
		$order_info = $this->model_account_order->getOrder($order_id);
		$this->load->model('payment/multi_pay');
		$payment_address_res = $this->model_payment_multi_pay->get_payment_address($order_info['customer_id'], $order_info['payment_address_1']);
		$payment_address = $payment_address_res->row;
		//trigger_error ( 'payment_address:' .json_encode($payment_address), E_USER_NOTICE );

		$method_data = $this->model_payment_multi_pay->get_payment_methods($payment_address, $total);
		//trigger_error ( 'method_data:' .json_encode($method_data), E_USER_NOTICE );
		
		if (empty($method_data)) {
			$this->data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		} else {
			$this->data['error_warning'] = '';
		}	

		$this->document->setTitle($this->language->get('heading_title_ord'));

		$this->data['breadcrumbs'] = array(); 

		$this->data['breadcrumbs'][] = array(
			'href'      => $this->url->link('common/home'),
			'text'      => $this->language->get('text_home'),
			'separator' => false
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
			'separator' => $this->language->get('text_separator')
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_order'),
			'href'      => $this->url->link('account/order/info', '&order_id='.$order_id, 'SSL'),
			'separator' => $this->language->get('text_separator')
		);

		$this->data['heading_title'] = $this->language->get('heading_title_ord');

		$this->template = _tpl('/template/common/default.tpl', $this->config->get('config_template'));

		$this->children = array(
			//'common/column_left',
			//'common/column_right',
			'common/content_top',
			//'common/content_bottom',
			//'common/footer',
			'common/header'			
		);

		$b = '';
		$b .= $this->data['error_warning'];
		$b .= '<h3>' . $this->language->get('text_subtitle') . '</h3>';
		$b .= $this->model_payment_multi_pay->payment_methods($method_data);
		$b .= '';
		$this->data['body'] = $b;
		$this->data['column_left'] = '';
		$this->data['column_right'] = '';
		$this->data['content_bottom'] = '';
		$this->data['footer'] = '';
		
		$this->response->setOutput($this->render());
	}

	public function deposit() {
		$this->load->helper('multi_pay');
	
		$customer_id = $this->customer->isLogged();
		if (!$customer_id) {
			// запомним ссылку для возврата
			$this->session->data['redirect'] = $this->url->link('account/multi_pay/deposit', '', 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
			
		$this->language->load('account/account');
		$this->language->load('account/multi_pay_to_dep');

		// Payment Methods
		$this->load->model('payment/multi_pay');
		$payment_address_res = $this->model_payment_multi_pay->get_customer_address($customer_id);
		$payment_address = $payment_address_res->row;
		
		//if ($payment_address->rows) {
		//	$payment_address = $payment_address->rows[1];
		//}
		
		// берем то что может пополнять депозит
		$to_deposit = true;
		$method_data = $this->model_payment_multi_pay->get_payment_methods($payment_address, 0, $to_deposit);

		if (empty($method_data)) {
			$this->data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		} else {
			$this->data['error_warning'] = '';
		}	

		if (isset($this->session->data['error'])) {
			$this->data['error'] = $this->session->data['error'];
		}

		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_transaction'),
			'href'      => $this->url->link('account/transaction', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);
		
		$this->children = array(
			//'common/column_left',
			//'common/column_right',
			'common/content_top',
			//'common/content_bottom',
			//'common/footer',
			'common/header'			
		);
		$this->data['column_left'] = '';
		$this->data['column_right'] = '';
		$this->data['content_bottom'] = '';
		$this->data['footer'] = '';

		$b = '<h4>' . $this->language->get('text_subtitle') . '</h4>';

		$b .= $this->model_payment_multi_pay->
				payment_methods($method_data, $to_deposit);
		 
		$mess = 'Товар Биткоин 01 добавлен в <a href="http://oc-demo.bit-moda.ru/index.php?route=checkout/cart">корзину</a>!<img src="catalog/view/theme/default/image/close.png" alt="" class="close">';
		//$this->data['error'] = $mess;
		//$this->data['success'] = $mess;
		//$this->data['warning'] = $mess;
		//$this->session->data['success'] = mess;
		//$this->session->data['warning'] = mess;
		//$this->session->data['error_warning'] = mess;
		//$this->session->data['notification'] = mess;
		if (isset($this->session->data['note'])) $this->data['note'] = $this->session->data['note'];

		$this->data['body'] = $b;

		$this->template = _tpl('/template/common/default.tpl', $this->config->get('config_template'));

		$this->document->setTitle($this->language->get('heading_title_to_dep'));
		$this->data['heading_title'] = $this->language->get('heading_title_to_dep');

		$this->response->setOutput($this->render());
	}

	public function transfer() {
		$this->load->helper('multi_pay');
	
		$customer_id = $this->customer->isLogged();
		if (!$customer_id) {
			// запомним ссылку для возврата
			$this->session->data['redirect'] = $this->url->link('account/multi_pay/transfer', '', 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
			
		$this->language->load('account/account');
		$this->language->load('account/multi_pay_transf');
		
		//getTotalAmount
		$total = $this->customer->getBalance();

		$amount = $email = '';
		$post = $this->request->server['REQUEST_METHOD'] == 'POST';
		if ($post) {
			$amount = (float)_g($_POST,'amount');
			if (empty($amount)) {
				$error = $this->language->get('amount is empty');
			} elseif ($total < $amount) {
				$error = $this->language->get('amount > total');
			}
			$email = _g($_POST,'email');
			if (empty($email)) {
				$error = $this->language->get('email is empty');
			}
		}
		if ($post and !isset($error)) {
			// найдем клиента
			$this->load->model('account/customer');			
			$row =  $this->model_account_customer->getCustomer($email);
			if (!$row) $row = $this->model_account_customer->getCustomerByEmail($email);
			
			if ($row) {
				$to_customer_id = $row['customer_id'];
				if ($to_customer_id == $customer_id) $error = $this->language->get('self');
				else {
					$this->load->model('payment/multi_pay_tools');			
					$this->model_payment_multi_pay_tools->
						update_deposit($customer_id, -$amount, $this->language->get('transfer to').' ['.$email.']');
					$this->model_payment_multi_pay_tools->
						update_deposit($to_customer_id, $amount, $this->language->get('transfer from').' ['.$customer_id.']');
					$success = $this->currency->format($amount) . ' '
					.$this->language->get('transfered to').' ['.$email.']';
					$total -= $amount;
				}
			} else {
				$error = '['.$email.'] '.$this->language->get('not found');
			}
		}
		//$this->data['success'] = $amount . '->'. $email;
		//$this->session->data['success'] = $amount . '->'. $email;
		//$this->data['success'] = $amount . '->'. $email;
		//$this->data['warning'] = $amount . '->'. $email;
		//} else {
		//	unset($this->data['success']);
		//}

		if (isset($error)) {
			$this->session->data['error'] = $error;
		}
		$this->data['breadcrumbs'] = array();
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),
			'separator' => false
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);
		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_transaction'),
			'href'      => $this->url->link('account/transaction', '', 'SSL'),
			'separator' => $this->language->get('text_separator')
		);
		
		$this->children = array(
			//'common/column_left',
			//'common/column_right',
			'common/content_top',
			//'common/content_bottom',
			//'common/footer',
			'common/header'			
		);
		$this->data['column_left'] = '';
		$this->data['column_right'] = '';
		$this->data['content_bottom'] = '';
		$this->data['footer'] = '';

		$b = '<h4>' . $this->language->get('text_subtitle') . '</h4>';
		
		if (isset($this->session->data['note'])) $this->data['note'] = $this->session->data['note'];

		$b .= '
			<h4>'.$this->language->get('Your balance').': '.$this->currency->format($total).'</h4>
			<form action="'.$this->url->link('account/multi_pay/transfer', '', 'SSL').'" method="post">
				<label>'.$this->language->get('txt_receiver').'</label>: 
				<input type="text" name="email" size="20" value="'.$email.'" ><br><br>
				<label>'.$this->language->get('txt_amount').'</label>: 
				<input type="text" name="amount" value="'.$amount.'"><br><br>
				<input type="submit" class="button">
			</form>&nbsp
			<a href="'.$this->url->link('account/account', '', 'SSL').'" class="button">'.$this->language->get('Cancel').'</a>
		';
		if (isset( $success )) $b .= '<br><br><div class="success">'.$success.'</div>';
		
		$this->data['body'] = $b;

		$this->template = _tpl('/template/common/default.tpl', $this->config->get('config_template'));

		$this->document->setTitle($this->language->get('heading_title_transf'));
		$this->data['heading_title'] = $this->language->get('heading_title_transf');

		$this->response->setOutput($this->render());
	}
	
}
?>