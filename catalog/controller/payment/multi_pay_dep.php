<?php
//trigger_error ( $this->session->data['redirect'] = $this->url->link(_url($_GET), 'SSL'), E_USER_NOTICE );

use system\engine\Controller;

define("ABR_DESCR", '<b>DEPOSITE</b>: ');

class ControllerPaymentMultiPayDep extends \system\engine\Controller {

	public function index() {
		$this->load->helper('multi_pay');
		// берется из стадартного языка  $this->load->language('payment/multi_pay_dep');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		// страницу окончания создания заказа свою грузим
		$this->data['action'] = $this->url->link('payment/multi_pay_dep/pay');
	
		// добавили этот шаблон в общую папку
		$this->template = _tpl('/template/common/payment_confirm.tpl', $this->config->get('config_template'));
				
		$this->render();
	}

    private function store_bill($order_id, $result) {
		// созранить полученные данные о счете
		// так как у нас врутненний депозит - ничего не надо сохранять
		// а если бы была внешняя служба то примерно так: 
		//$this->db->query('insert ignore into '.DB_PREFIX.'cryptopay_bill set order_id='
		//		.$order_id.', bill_id="'.$result.'"');
	}

	// созддать счет в платежной службе
    private function make_bill($info) {
		// так как у нас с депозита внутреннего, то просто вернем order_id
		return $info['order_id'];
	}
	
	// поиск созданного счета через данный способ оплаты для данного заказа
    private function found_bill($order_id, $order_info) {
		// так как это оплата с депозиита в магазине то проверяем по стату заказа
		
		return $order_info['order_status_id']==0?false: array( 'bill_id' => $order_id);
	}

		
	// вызывается после нажатия Оплатить или Подтвердить заказ
    public function pay() {
		$this->load->helper('multi_pay');

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link(_url($_GET), 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}
        //сохраняем id заказа
        $order_id = _g($_GET,'order_id', _g($this->session->data,'order_id'));
		if (!$order_id) $this->redirect($this->url->link('account/order'));

        // проверим, может быть мы уже создавали счет для оплаты этого заказа?
		// так как у нас оплата с депозита внутреннего то просто проверим статус заказа
		// - если он уже = 1 - значит заказ создан из корзины-заказа
		// - если = 0 - то создадим заказ с помощью confirm

		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id);
		$bill_row = $this->found_bill($order_id, $order_info);
        if($bill_row) {
			// да - счет создан, просто его вернем
            $result=$bill_row['bill_id'];
			$curr = $this->config->get('config_currency');
			//загружаем модель корзины-заказа
			$price = $order_info['total'];
			// trigger_error ( ' cryptoPay bill_id exist - $result' . json_encode($result), E_USER_NOTICE );
        } else {
			
			// подтверждаем заказ (если он уже есть то там будет это проигнорировано)
			$this->load->model('payment/multi_pay_tools');
			$this->model_payment_multi_pay_tools->order_confirm($order_id, '', $order_info);
			// поновой подгрузим данные из заказа - они поменялись
			$order_info = $this->model_checkout_order->getOrder($order_id);
		
			$order_status_id = (int)$order_info['order_status_id'];
			//trigger_error ( '$order_info' . json_encode($order_info), E_USER_NOTICE );
			if ( $order_status_id != 0 && $order_status_id != 1) {
				// если заказ не новый и не в ожидании то пропустим
				//trigger_error ( ' заказ не новый - пропустим' . $order_status_id, E_USER_NOTICE );
				$this->language->load('account/multi_pay_order');
				$this->session->data['note'] = _note($this->language->get('note_cant_pay'),'warning');
				$this->redirect($this->url->link('account/order/info', '&order_id='.$order_id, 'SSL'));
			}
			// пришло сюда - значит создаем счет на оплату
			// через АПИ
			// тут все в глобальной и так $curr = $order_info['currency_code'];
			$curr = $this->config->get('config_currency');
			$price = $order_info['total'];
			//$price = $this->currency->convert($order_info['total'], $curr);
			$info = array(
					'order_id' => $order_id,
					'price' => $price,
					'curr' => $curr
					);
            $result = $this->make_bill($info);
			//trigger_error ( ' make_bill result: ' . json_encode($result), E_USER_NOTICE );

            // сохраняем полученный от платежной службы bill_id
			$this->store_bill($order_id, $result);

			// записываем запись в историю заказ что создан счет
			// но так как унас с депозита то ничего не делаем
        }
		
        //редирект на страницу оплаты
        // например так $this->redirect('http://cryptopay.in/shop/bill/show/'.$result);
		// но так как у нас депозит - то на сраницу счета
		// со сразу деллаем вызов уведомления - как будо пришла оплата
		// тут цена уже сконвертирована в валюту магазина
		$this->callback($order_id, $price, $curr, $order_info);
    }
	
	
    // функция на которую приходит уведомление от сервиса
	public function callback($order_id, $price, $curr, $order_info) {
		// так как это с депозита то функция - приватная
		// тут мы сами делаем проверку - хватает ли на счету средств
		
		// тут остаток на депозите в валюте глобальной
		// надо сконвертировать цену заказа в валюту магазина
		//$curr_global = $this->config->get('config_currency');
		//$total = $this->currency->convert($price, $curr, $curr_global);
		$total = $price;
		$this->language->load('payment/multi_pay_dep');
		
		$this->load->model('account/transaction');
		$deposite = $transaction_total = $this->model_account_transaction->getTotalAmount();
		// trigger_error ( ' ' . $total . ' > ' . $deposite, E_USER_NOTICE );
		if ($total > $deposite) {
			// не хватает депозита - игнорируем
			$this->session->data['note'] = _note($this->language->get('note dep not enough'),'warning');
		} elseif ((int)$order_info['order_status_id']!=1) {
			// заказ не встадии ожидания оплаты
			$this->language->load('account/multi_pay_order');
			$this->session->data['note'] = _note($this->language->get('note_cant_pay'),'warning');
		} else {
			// заказ оплачен!
			// поменяем и статус заказа
			$new_status_id = 2; // Обработка
			
			$this->load->model('payment/multi_pay_tools');
			$this->language->load('payment/multi_pay_tools');
			$note_status = ABR_DESCR . sprintf($this->language->get('bill_payed'), '+');
			$this->model_payment_multi_pay_tools->order_bill_update(
					$order_id
					,$order_info
					,$new_status_id
					,$note_status
					,true
					);
			// теперь запись в депозит занесем
			$this->load->model('payment/multi_pay_tools');
			$rec_id = $this->model_payment_multi_pay_tools->update_deposit($order_info['customer_id'],
					-$total, $this->language->get('Paid order №') .$order_id . ': ' . $price . $curr . '<-');
			
			// сообщим об успехе
			$this->session->data['note'] = _note($this->language->get('note order payed'),'success');
		}

		// так как платежной службы нет то перенаправим сразу на карточку заказа
		$this->redirect($this->url->link('account/order/info', '&order_id='.$order_id, 'SSL'));
	}

}
?>