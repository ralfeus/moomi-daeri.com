<?php
use model\sale\CustomerDAO;
use model\sale\OrderItemDAO;
use model\setting\StoreDAO;

class ControllerSaleCustomer extends Controller {
	private $error = array();
    private $modelLocalisationCurrency;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('sale/customer');
        $this->modelLocalisationCurrency = $this->load->model('localisation/currency');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['error_warning'] = '';
        $this->data['success'] = '';
    }

    public function index() {
    	$this->getList();
  	}

    private function getCustomers()
    {
        foreach ($this->parameters as $key => $value)
        {
            if (strpos($key, 'filter') === false)
                continue;
            $data[$key] = $value;
        }
        unset($data['filterCustomerId']);
        $tmpResult = array();
        foreach (CustomerDAO::getInstance()->getCustomers($data) as $customer)
            if (!in_array($customer['customer_id'], $tmpResult))
                $tmpResult[$customer['customer_id']] = $customer['name'] . ' / ' . $customer['nickname'];
        natcasesort($tmpResult);
        return $tmpResult;
    }

    private function getCreditRequests($customer)
    {
        $this->load->library('Messaging');
        $this->load->library('Status');
        $addCreditRequests = Messaging::getSystemMessages(
            array(
                'systemMessageType' => SYS_MSG_ADD_CREDIT,
                'filterCustomerId' => array($customer['customer_id']),
                'start' => ($this->parameters['creditRequestsPage'] - 1 ) * 10,
                'limit' => 10
            )
        );
//        $this->log->write(print_r($addCreditRequests, true));
        foreach ($addCreditRequests as $addCreditRequest)
        {
            $this->data['requests'][] = array(
                'requestId' => $addCreditRequest['messageId'],
                'amount' => $addCreditRequest['data']->amount,
                'comment' => $addCreditRequest['data']->comment,
                'currency' => $addCreditRequest['data']->currency,
                'status' => Status::getStatus($addCreditRequest['data']->status, $this->config->get('language_id'), true),
                'statusId' => $addCreditRequest['data']->status,
                'timeAdded' => $addCreditRequest['timeAdded']
            );
        }

        /// Initialize interface
        $this->data['textAmount'] = $this->language->get('AMOUNT');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textRequestId'] = $this->language->get('REQUEST_ID');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textTimeAdded'] = $this->language->get('TIME_ADDED');

        $pagination = new Pagination();
        $pagination->total = Messaging::getSystemMessagesCount(SYS_MSG_ADD_CREDIT, $customer['customer_id']);
        $pagination->page = $this->parameters['creditRequestsPage'];
        $pagination->limit = 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link(
            'sale/customer/transaction',
            'creditRequestsPage={page}&transactionsPage=' . $this->parameters['transactionsPage'] .
                '&token=' . $this->parameters['token'] .
                '&customerId=' . $this->parameters['customerId'], 'SSL');
        $this->data['creditRequestsPagination'] = $pagination->render();
    }

    private function getTransactions($customer)
    {
        //$this->data['addCreditUrl'] = $this->url->link('account/addCredit', '', 'SSL');
        $this->data['textTimeAdded'] = $this->language->get('TIME_ADDED');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textBalance'] = $this->language->get('BALANCE');
        $this->data['textExpenseAmount'] = $this->language->get('EXPENSE_AMOUNT');
        $this->data['textIncomeAmount'] = $this->language->get('INCOME_AMOUNT');
        $this->data['textCurrency'] = $this->language->get('CURRENCY');
        $this->data['text_total'] = $this->language->get('TOTAL');
        $this->data['text_empty'] = $this->language->get('text_empty');
        $this->data['textAddCredit'] = $this->language->get('ADD_CREDIT');
        $this->data['textInvoiceId'] = $this->language->get('INVOICE_ID');
        $this->data['textTransactionId'] = $this->language->get('TRANSACTION_ID');
        $this->data['total'] = $this->currency->format($customer['balance'], $customer['base_currency_code'], 1);

        $data = array(
            'sort'  => 'customer_transaction_id',
            'order' => 'DESC',
            'start' => ($this->parameters['transactionsPage'] - 1) * 10,
            'limit' => 10
        );

        $transaction_total = CustomerDAO::getInstance()->getTotalTransactions($customer['customer_id']);
        $transactions = CustomerDAO::getInstance()->getTransactions($customer['customer_id'], $data['start'], $data['limit']);

        $this->data['transactions'] = array();
        foreach ($transactions as $transaction) {
            $actions = array();
            $actions[] = array(
                'text' => $this->language->get('DELETE'),
                'onclick' => "deleteTransaction(" . $transaction['customer_transaction_id'] . ");"
            );
            $amount = -$transaction['amount'];
            $amountString = $this->currency->format($amount, $transaction['currency_code'], 1);
            $this->data['transactions'][] = array(
                'actions' => $actions,
                'balance' => $this->currency->format(
                    $transaction['balance'], $transaction['balance_currency'] ? $transaction['balance_currency'] : $customer['base_currency_code'], 1),
                'expenseAmount'      => $amount < 0 ? $amountString : '',
                'incomeAmount'      => $amount >= 0 ? $amountString : '',
                'currency_code' => $transaction['currency_code'],
                'date_added'  => $transaction['date_added'],
                'description' => $transaction['description'],
                'invoiceId' => $transaction['invoice_id'] ? $transaction['invoice_id'] : '',
                'invoiceUrl' => $this->url->link('sale/invoice/showForm', 'invoiceId=' . $transaction['invoice_id'] . '&token=' . $this->parameters['token'], 'SSL'),
                'transactionId' => $transaction['customer_transaction_id']
            );
        }

        $pagination = new Pagination();
        $pagination->total = $transaction_total;
        $pagination->page = $this->parameters['transactionsPage'];
        $pagination->limit = 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->url->link(
            'sale/customer/transaction',
            'transactionsPage={page}&creditRequestsPage=' . $this->parameters['creditRequestsPage'] .
                '&token=' . $this->parameters['token'] .
                '&customerId=' . $this->parameters['customerId'], 'SSL');
        $this->data['transactionsPagination'] = $pagination->render();
    }

    protected function initParameters()
    {
        $this->parameters['filterApproved'] = isset($_REQUEST['filterApproved']) && is_numeric($_REQUEST['filterApproved'])? $_REQUEST['filterApproved'] : null;
        $this->parameters['filterCustomerGroupId'] = isset($_REQUEST['filterCustomerGroupId']) ? $_REQUEST['filterCustomerGroupId'] : null;
        $this->parameters['filterCustomerId'] = empty($_REQUEST['filterCustomerId']) ? array() : $_REQUEST['filterCustomerId'];
        $this->parameters['filterDateAdded'] = isset($_REQUEST['filterDateAdded']) ? $_REQUEST['filterDateAdded'] : null;
        $this->parameters['filterEmail'] = isset($_REQUEST['filterEmail']) ? $_REQUEST['filterEmail'] : null;
        $this->parameters['filterIp'] = isset($_REQUEST['filterIp']) ? $_REQUEST['filterIp'] : null;
        $this->parameters['filterName'] = isset($_REQUEST['filterName']) ? $_REQUEST['filterName'] : null;
        $this->parameters['filterNickname'] = isset($_REQUEST['filterNickname']) ? $_REQUEST['filterNickname'] : null;
        $this->parameters['filterStatus'] = isset($_REQUEST['filterStatus']) && is_numeric($_REQUEST['filterStatus']) ? $_REQUEST['filterStatus'] : null;
        $this->parameters['sort'] = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'name';
        $this->parameters['order'] = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC';
        $this->parameters['page'] = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;

        $this->parameters['baseCurrency'] = empty($_REQUEST['baseCurrency']) ? null : $_REQUEST['baseCurrency'];
        $this->parameters['customerId'] =
            empty($_REQUEST['customerId']) ? (empty($_REQUEST['customer_id']) ? array() : $_REQUEST['customer_id']) : $_REQUEST['customerId'];
        $this->parameters['creditRequestsPage'] = empty($_REQUEST['creditRequestsPage']) ? 1 : $_REQUEST['creditRequestsPage'];
        $this->parameters['storeId'] = empty($_REQUEST['storeId']) ? 0 : $_REQUEST['storeId'];
        $this->parameters['transactionsPage'] = empty($_REQUEST['transactionsPage']) ? 1 : $_REQUEST['transactionsPage'];
        $this->parameters['token'] = $this->session->data['token'];
    }
  
  	public function insert() {
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
      	  	CustomerDAO::getInstance()->addCustomer($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('sale/customer', $this->buildUrlParameterString($this->parameters), 'SSL'));
		}
    	$this->getForm();
  	} 
   
  	public function update() {
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			CustomerDAO::getInstance()->editCustomer($_REQUEST['customer_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('sale/customer', $this->buildUrlParameterString($this->parameters), 'SSL'));
		}
    
    	$this->getForm();
  	}   

  	public function delete() {
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $customer_id) {
				CustomerDAO::getInstance()->deleteCustomer($customer_id);
			}
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('sale/customer', $this->buildUrlParameterString($this->parameters), 'SSL'));
    	}
    	$this->getList();
  	}  
	
	public function approve() {
		if (!$this->user->hasPermission('modify', 'sale/customer')) {
			$this->error['warning'] = $this->language->get('error_permission');
		} elseif (isset($this->request->post['selected'])) {
			$approved = 0;
			
			foreach ($this->request->post['selected'] as $customer_id) {
				$customer_info = CustomerDAO::getInstance()->getCustomer($customer_id);
				
				if ($customer_info && !$customer_info['approved']) {
					CustomerDAO::getInstance()->approve($customer_id);
					
					$approved++;
				}
			} 
			
			$this->session->data['success'] = sprintf($this->language->get('text_approved'), $approved);	
			$this->redirect($this->url->link('sale/customer', $this->buildUrlParameterString($this->parameters), 'SSL'));
		}
		
		$this->getList();
	} 
    
  	private function getList() {
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = $this->request->get['limit'];
		} else {
			$limit = $this->config->get('config_admin_limit');
		}

		if (isset($this->request->get['filterCustomerId'])) {
			$filterCustomerId = $this->request->get['filterCustomerId'];
		} else {
			$filterCustomerId = null;
		}

		if (isset($this->request->get['filterEmail'])) {
			$filterEmail = $this->request->get['filterEmail'];
		} else {
			$filterEmail = null;
		}

		if (isset($this->request->get['filterCustomerGroupId'])) {
			$filterCustomerGroupId = $this->request->get['filterCustomerGroupId'];
		} else {
			$filterCustomerGroupId = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'c.customer_id';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		$url = '';
						
		if (isset($this->request->get['filterCustomerId'])) {
			$url .= '&filterCustomerId=' . $this->request->get['filterCustomerId'];
		}
		
		if (isset($this->request->get['filterEmail'])) {
			$url .= '&filterEmail=' . $this->request->get['filterEmail'];
		}
		
		if (isset($this->request->get['filterCustomerGroupId'])) {
			$url .= '&filterCustomerGroupId=' . $this->request->get['filterCustomerGroupId'];
		}
		
		if (isset($this->request->get['filterStatus'])) {
			$url .= '&filterStatus=' . $this->request->get['filterStatus'];
		}
		
		if (isset($this->request->get['filterApproved'])) {
			$url .= '&filterApproved=' . $this->request->get['filterApproved'];
		}

		if (isset($this->request->get['filterIp'])) {
			$url .= '&filterIp=' . $this->request->get['filterIp'];
		}			

		if (isset($this->request->get['filterDateAdded'])) {
			$url .= '&filterDateAdded=' . $this->request->get['filterDateAdded'];
		}
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}


		$this->data['approve'] = $this->url->link('sale/customer/approve', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['insert'] = $this->url->link('sale/customer/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/customer/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');

		$this->data['customers'] = array();
//        $data = $this->parameters;

        $data['start']           = ($page - 1) * $limit;
        $data['limit']           = $limit;
		$data['filterCustomerId']	  = $filterCustomerId;
		$data['filterEmail']	  = $filterEmail;
		$data['filterCustomerGroupId'] = $filterCustomerGroupId;
		$data['filterStatus']	  = $filterStatus;
		$data['filterApproved']	  = $filterApproved;
		$data['filterIp']	  = $filterIp;
		$data['filterDateAdded']	  = $filterDateAdded;
		$data['sort']            = $sort;
		$data['order']           = $order;
    
        $data = array_merge($data, $this->parameters);
        $this->data = array_merge($this->data, $this->parameters);

        $this->data['customersToFilterBy'] = $this->getCustomers();
		$customer_total = CustomerDAO::getInstance()->getTotalCustomers($data);
		$results = CustomerDAO::getInstance()->getCustomers($data);
        /** @var ModelSaleOrder $modelSaleOrder */
        $modelSaleOrder = $this->load->model('sale/order');
    	foreach ($results as $result) {
			$action = array();
		
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/customer/update', 'token=' . $this->parameters['token'] . '&customer_id=' . $result['customer_id'], 'SSL')
			);
            $action[] = array(
                'text' => $this->language->get('PURGE_CART'),
                'onclick' => "ajaxAction(this, '" . $this->url->link('sale/customer/purgeCart', 'token=' . $this->parameters['token'] . '&customerId=' . $result['customer_id'], 'SSL') . "')"
            );
            $action[] = array(
                'text' => $this->language->get('ORDER_ITEMS_HISTORY'),
                'onclick' => "ajaxAction(this, '" . $this->url->link('sale/customer/orderItemsHistory', 'token=' . $this->parameters['token'] . '&customerId=' . $result['customer_id'], 'SSL') . "')"
            );
            $action[] = array(
                'text' => $this->language->get('LOGON_AS_CUSTOMER'),
                'onclick' => 'showStores(this, \'#stores' . $result['customer_id'] . "')"
            );
            
			$this->data['customers'][] = array(
				'customer_id'    => $result['customer_id'],
                'highlighted' => $modelSaleOrder->getTotalOrders(array(
                        'filterCustomerId' => array($result['customer_id']),
                        'filterStatusId' => array(ORDER_STATUS_READY_TO_SHIP)
                    )),
				'name'           => $result['name'],
                'nickname'       => $result['nickname'],
				'email'          => $result['email'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'approved'       => ($result['approved'] ? $this->language->get('text_yes') : $this->language->get('text_no')),
				'ip'             => $result['ip'],
				'date_added'     => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'selected'       => isset($this->request->post['selected']) && in_array($result['customer_id'], $this->request->post['selected']),
				'action'         => $action
			);
		}	
					
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');	
		$this->data['text_select'] = $this->language->get('text_select');	
		$this->data['text_default'] = $this->language->get('text_default');		
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_limit'] = $this->language->get('text_limit');
		$this->data['text_nickname'] = $this->language->get('field_nickname');

		$this->data['textCustomerName'] = $this->language->get('NAME');
		$this->data['column_email'] = $this->language->get('column_email');
		$this->data['column_customer_group'] = $this->language->get('column_customer_group');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_approved'] = $this->language->get('column_approved');
		$this->data['column_ip'] = $this->language->get('column_ip');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_login'] = $this->language->get('column_login');
		$this->data['column_action'] = $this->language->get('column_action');		
		
		$this->data['button_approve'] = $this->language->get('button_approve');
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('FILTER');

        $this->data['urlCustomerLogin'] = $this->url->link('sale/customer/login', 'token=' . $this->parameters['token'], 'SSL');
        $this->data['urlSelf'] = $this->url->link($this->selfRoute, 'token=' . $this->parameters['token'], 'SSL');

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

			$this->data['limits'] = array();

			$this->data['limits'][] = array(
				'text'  => $this->config->get('config_admin_limit'),
				'value' => $this->config->get('config_admin_limit'),
				'href'  => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&limit=' . $this->config->get('config_admin_limit'). $url, 'SSL')
			);

			$this->data['limits'][] = array(
				'text'  => 150,
				'value' => 150,
				'href'  => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&limit=150' . $url, 'SSL')
			);

			$this->data['limits'][] = array(
				'text'  => 100,
				'value' => 100,
				'href'  => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&limit=100' . $url, 'SSL')
			);

			$this->data['limits'][] = array(
				'text'  => 50,
				'value' => 50,
				'href'  => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&limit=50' . $url, 'SSL')
			);

			$this->data['limits'][] = array(
				'text'  => 25,
				'value' => 25,
				'href'  => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&limit=25' . $url, 'SSL')
			);

		$url = '';
						
			if (isset($this->request->get['limit'])) {
				$url .= '&limit=' . $this->request->get['limit'];
			}

		if (isset($this->request->get['filterCustomerId'])) {
			$url .= '&filterCustomerId=' . $this->request->get['filterCustomerId'];
		}
		
		if (isset($this->request->get['filterEmail'])) {
			$url .= '&filterEmail=' . $this->request->get['filterEmail'];
		}
		
		if (isset($this->request->get['filterCustomerGroupId'])) {
			$url .= '&filterCustomerGroupId=' . $this->request->get['filterCustomerGroupId'];
		}
		
		if (isset($this->request->get['filterStatus'])) {
			$url .= '&filterStatus=' . $this->request->get['filterStatus'];
		}
		
		if (isset($this->request->get['filterApproved'])) {
			$url .= '&filterApproved=' . $this->request->get['filterApproved'];
		}

		if (isset($this->request->get['filterIp'])) {
			$url .= '&filterIp=' . $this->request->get['filterIp'];
		}			

		if (isset($this->request->get['filterDateAdded'])) {
			$url .= '&filterDateAdded=' . $this->request->get['filterDateAdded'];
		}
		
		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['sort_name'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.customer_id' . $url, 'SSL');
        $this->data['sort_nickname'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.nickname' . $url, 'SSL');
		$this->data['sort_email'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.email' . $url, 'SSL');
		$this->data['sort_customer_group'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=customer_group' . $url, 'SSL');
		$this->data['sort_status'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.status' . $url, 'SSL');
		$this->data['sort_approved'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.approved' . $url, 'SSL');
		$this->data['sort_ip'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.ip' . $url, 'SSL');
		$this->data['sort_date_added'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&sort=c.date_added' . $url, 'SSL');
		
		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $limit;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . '&page={page}', 'SSL');
		$this->data['pagination'] = $pagination->render();

		$this->load->model('sale/customer_group');
        $this->load->model('setting/store');

    	$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
		$this->data['stores'] = $this->model_setting_store->getStores();

		$this->data['filterEmail'] = $filterEmail;
		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		$this->data['limit'] = $limit;
    
//    $this->data = array_merge($this->data, $this->parameters);
				
		$this->template = 'sale/customer_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
  	}
  
  	private function getForm() {
    	$this->data['heading_title'] = $this->language->get('heading_title');
 
    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_select'] = $this->language->get('text_select');
    	$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_ip'] = $this->language->get('column_ip');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		
    	$this->data['entry_firstname'] = $this->language->get('entry_firstname');
    	$this->data['entry_lastname'] = $this->language->get('entry_lastname');
        $this->data['entry_nickname'] = $this->language->get('field_nickname');
    	$this->data['entry_email'] = $this->language->get('entry_email');
    	$this->data['entry_telephone'] = $this->language->get('entry_telephone');
    	$this->data['entry_fax'] = $this->language->get('entry_fax');
    	$this->data['entry_password'] = $this->language->get('entry_password');
    	$this->data['entry_confirm'] = $this->language->get('entry_confirm');
		$this->data['entry_newsletter'] = $this->language->get('entry_newsletter');
    	$this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_company'] = $this->language->get('entry_company');
		$this->data['entry_address_1'] = $this->language->get('entry_address_1');
		$this->data['entry_address_2'] = $this->language->get('entry_address_2');
		$this->data['entry_city'] = $this->language->get('entry_city');
		$this->data['entry_postcode'] = $this->language->get('entry_postcode');
		$this->data['entry_zone'] = $this->language->get('entry_zone');
		$this->data['entry_country'] = $this->language->get('entry_country');
		$this->data['entry_default'] = $this->language->get('entry_default');
		$this->data['entry_amount'] = $this->language->get('entry_amount');
		$this->data['entry_points'] = $this->language->get('entry_points');
 		$this->data['entry_description'] = $this->language->get('entry_description');
 
		$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');
    	$this->data['button_add_address'] = $this->language->get('button_add_address');
		$this->data['button_add_transaction'] = $this->language->get('button_add_transaction');
		$this->data['button_add_reward'] = $this->language->get('button_add_reward');
    	$this->data['button_remove'] = $this->language->get('button_remove');
	
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_address'] = $this->language->get('tab_address');
		$this->data['tab_transaction'] = $this->language->get('tab_transaction');
		$this->data['tab_reward'] = $this->language->get('tab_reward');
		$this->data['tab_ip'] = $this->language->get('tab_ip');
        $this->data['textBaseCurrency'] = $this->language->get('BASE_CURRENCY');

		$this->data['token'] = $this->session->data['token'];

		if (isset($_REQUEST['customer_id'])) {
			$this->data['customer_id'] = $_REQUEST['customer_id'];
		} else {
			$this->data['customer_id'] = 0;
		}

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

 		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}
        if (isset($this->error['nickname'])) {
            $this->data['error_nickname'] = $this->error['nickname'];
        } else {
            $this->data['error_nickname'] = '';
        }
 		if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}
		
 		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}
		
 		if (isset($this->error['password'])) {
			$this->data['error_password'] = $this->error['password'];
		} else {
			$this->data['error_password'] = '';
		}
		
 		if (isset($this->error['confirm'])) {
			$this->data['error_confirm'] = $this->error['confirm'];
		} else {
			$this->data['error_confirm'] = '';
		}
		
		if (isset($this->error['address_firstname'])) {
			$this->data['error_address_firstname'] = $this->error['address_firstname'];
		} else {
			$this->data['error_address_firstname'] = '';
		}

 		if (isset($this->error['address_lastname'])) {
			$this->data['error_address_lastname'] = $this->error['address_lastname'];
		} else {
			$this->data['error_address_lastname'] = '';
		}
		
		if (isset($this->error['address_address_1'])) {
			$this->data['error_address_address_1'] = $this->error['address_address_1'];
		} else {
			$this->data['error_address_address_1'] = '';
		}
		
		if (isset($this->error['address_city'])) {
			$this->data['error_address_city'] = $this->error['address_city'];
		} else {
			$this->data['error_address_city'] = '';
		}
		
		if (isset($this->error['address_postcode'])) {
			$this->data['error_address_postcode'] = $this->error['address_postcode'];
		} else {
			$this->data['error_address_postcode'] = '';
		}
		
		if (isset($this->error['address_country'])) {
			$this->data['error_address_country'] = $this->error['address_country'];
		} else {
			$this->data['error_address_country'] = '';
		}
		
		if (isset($this->error['address_zone'])) {
			$this->data['error_address_zone'] = $this->error['address_zone'];
		} else {
			$this->data['error_address_zone'] = '';
		}
		
		$url = '';
		
		if (isset($_REQUEST['filter_name'])) {
			$url .= '&filter_name=' . $_REQUEST['filter_name'];
		}
        if (isset($_REQUEST['filter_nickname'])) {
            $url .= '&filter_nickname=' . $_REQUEST['filter_nickname'];
        }
		if (isset($_REQUEST['filter_email'])) {
			$url .= '&filter_email=' . $_REQUEST['filter_email'];
		}
		
		if (isset($_REQUEST['filter_customer_group_id'])) {
			$url .= '&filter_customer_group_id=' . $_REQUEST['filter_customer_group_id'];
		}
		
		if (isset($_REQUEST['filter_status'])) {
			$url .= '&filter_status=' . $_REQUEST['filter_status'];
		}
		
		if (isset($_REQUEST['filter_approved'])) {
			$url .= '&filter_approved=' . $_REQUEST['filter_approved'];
		}	
		
		if (isset($_REQUEST['filter_date_added'])) {
			$url .= '&filter_date_added=' . $_REQUEST['filter_date_added'];
		}

		if (isset($_REQUEST['sort'])) {
			$url .= '&sort=' . $_REQUEST['sort'];
		}

		if (isset($_REQUEST['order'])) {
			$url .= '&order=' . $_REQUEST['order'];
		}
						
		if (isset($_REQUEST['page'])) {
			$url .= '&page=' . $_REQUEST['page'];
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);

		if (!isset($_REQUEST['customer_id'])) {
			$this->data['action'] = $this->url->link('sale/customer/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/customer/update', 'token=' . $this->session->data['token'] . '&customer_id=' . $_REQUEST['customer_id'] . $url, 'SSL');
		}
		  
    	$this->data['cancel'] = $this->url->link('sale/customer', 'token=' . $this->session->data['token'] . $url, 'SSL');

    	if (isset($_REQUEST['customer_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$customer_info = CustomerDAO::getInstance()->getCustomer($_REQUEST['customer_id']);
    	} else {
            $customer_info = null;
        }

          if (isset($this->request->post['baseCurrency']))
              $selectedCurrency = $this->request->post['baseCurrency'];
          else
              $selectedCurrency = $customer_info['base_currency_code'];

          $this->data['currencies'] = array();
          foreach ($this->modelLocalisationCurrency->getCurrencies() as $currency)
          {
              $this->data['currencies'][] = array(
                  'currencyCode' => $currency['code'],
                  'name' => $currency['title'],
                  'selected' => ($currency['code'] == $selectedCurrency) ? "selected=\"true\"" : ""
              );
          }

    	if (isset($this->request->post['firstname'])) {
      		$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (isset($customer_info)) { 
			$this->data['firstname'] = $customer_info['firstname'];
		} else {
      		$this->data['firstname'] = '';
    	}

    	if (isset($this->request->post['lastname'])) {
      		$this->data['lastname'] = $this->request->post['lastname'];
    	} elseif (isset($customer_info)) { 
			$this->data['lastname'] = $customer_info['lastname'];
		} else {
      		$this->data['lastname'] = '';
    	}
        if (isset($this->request->post['nickname'])) {
            $this->data['nickname'] = $this->request->post['nickname'];
        } elseif (isset($customer_info)) {
            $this->data['nickname'] = $customer_info['nickname'];
        } else {
            $this->data['nickname'] = '';
        }
    	if (isset($this->request->post['email'])) {
      		$this->data['email'] = $this->request->post['email'];
    	} elseif (isset($customer_info)) { 
			$this->data['email'] = $customer_info['email'];
		} else {
      		$this->data['email'] = '';
    	}

    	if (isset($this->request->post['telephone'])) {
      		$this->data['telephone'] = $this->request->post['telephone'];
    	} elseif (isset($customer_info)) { 
			$this->data['telephone'] = $customer_info['telephone'];
		} else {
      		$this->data['telephone'] = '';
    	}

    	if (isset($this->request->post['fax'])) {
      		$this->data['fax'] = $this->request->post['fax'];
    	} elseif (isset($customer_info)) { 
			$this->data['fax'] = $customer_info['fax'];
		} else {
      		$this->data['fax'] = '';
    	}

    	if (isset($this->request->post['newsletter'])) {
      		$this->data['newsletter'] = $this->request->post['newsletter'];
    	} elseif (isset($customer_info)) { 
			$this->data['newsletter'] = $customer_info['newsletter'];
		} else {
      		$this->data['newsletter'] = '';
    	}
		
		$this->load->model('sale/customer_group');
			
		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();

    	if (isset($this->request->post['customer_group_id'])) {
      		$this->data['customer_group_id'] = $this->request->post['customer_group_id'];
    	} elseif (isset($customer_info)) { 
			$this->data['customer_group_id'] = $customer_info['customer_group_id'];
		} else {
      		$this->data['customer_group_id'] = $this->config->get('config_customer_group_id');
    	}
		
    	if (isset($this->request->post['status'])) {
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (isset($customer_info)) { 
			$this->data['status'] = $customer_info['status'];
		} else {
      		$this->data['status'] = 1;
    	}

    	if (isset($this->request->post['password'])) { 
			$this->data['password'] = $this->request->post['password'];
		} else {
			$this->data['password'] = '';
		}
		
		if (isset($this->request->post['confirm'])) { 
    		$this->data['confirm'] = $this->request->post['confirm'];
		} else {
			$this->data['confirm'] = '';
		}
		
		$this->load->model('localisation/country');
		
		$this->data['countries'] = $this->model_localisation_country->getCountries();
			
		if (isset($this->request->post['address'])) { 
      		$this->data['addresses'] = $this->request->post['address'];
		} elseif (isset($_REQUEST['customer_id'])) {
			$this->data['addresses'] = CustomerDAO::getInstance()->getAddresses($_REQUEST['customer_id']);
		} else {
			$this->data['addresses'] = array();
    	}
		
		$this->data['ips'] = array();
    	
		if (!empty($customer_info)) {
			$results = CustomerDAO::getInstance()->getIpsByCustomerId($_REQUEST['customer_id']);
		
			foreach ($results as $result) {
				$this->data['ips'][] = array(
					'ip'         => $result['ip'],
					'total'      => CustomerDAO::getInstance()->getTotalCustomersByIp($result['ip']),
					'date_added' => date('d/m/y', strtotime($result['date_added'])),
					'filter_ip'  => HTTPS_SERVER . 'index.php?route=sale/customer&token=' . $this->session->data['token'] . '&filter_ip=' . $result['ip']
				);
			}
		}		
		
		$this->template = 'sale/customer_form.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render());
	}
			 
  	private function validateForm() {
    	if (!$this->user->hasPermission('modify', 'sale/customer')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

    	if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
      		$this->error['firstname'] = $this->language->get('error_firstname');
    	}

    	if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
      		$this->error['lastname'] = $this->language->get('error_lastname');
    	}
        if ((utf8_strlen($this->request->post['nickname']) < 1) || (utf8_strlen($this->request->post['nickname']) > 32)) {
            $this->error['nickname'] = $this->language->get('error_nickname');
        }
		if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
      		$this->error['email'] = $this->language->get('error_email');
    	}
		
		$customer_info = CustomerDAO::getInstance()->getCustomerByEmail($this->request->post['email']);
		
		if (!isset($_REQUEST['customer_id'])) {
			if ($customer_info) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		} else {
			if ($customer_info && ($_REQUEST['customer_id'] != $customer_info['customer_id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}
		
    	if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
      		$this->error['telephone'] = $this->language->get('error_telephone');
    	}

    	if ($this->request->post['password'] || (!isset($_REQUEST['customer_id']))) {
      		if ((utf8_strlen($this->request->post['password']) < 4) || (utf8_strlen($this->request->post['password']) > 20)) {
        		$this->error['password'] = $this->language->get('error_password');
      		}
	
	  		if ($this->request->post['password'] != $this->request->post['confirm']) {
	    		$this->error['confirm'] = $this->language->get('error_confirm');
	  		}
    	}

		if (isset($this->request->post['address'])) {
			foreach ($this->request->post['address'] as $key => $value) {
				if ((utf8_strlen($value['firstname']) < 1) || (utf8_strlen($value['firstname']) > 32)) {
					$this->error['address_firstname'][$key] = $this->language->get('error_firstname');
				}
				
				if ((utf8_strlen($value['lastname']) < 1) || (utf8_strlen($value['lastname']) > 32)) {
					$this->error['address_lastname'][$key] = $this->language->get('error_lastname');
				}	
				
				if ((utf8_strlen($value['address_1']) < 3) || (utf8_strlen($value['address_1']) > 128)) {
					$this->error['address_address_1'][$key] = $this->language->get('error_address_1');
				}
			
				if ((utf8_strlen($value['city']) < 2) || (utf8_strlen($value['city']) > 128)) {
					$this->error['address_city'][$key] = $this->language->get('error_city');
				} 
	
				$this->load->model('localisation/country');
				
				$country_info = $this->model_localisation_country->getCountry($value['country_id']);
						
				if ($country_info && $country_info['postcode_required'] && (utf8_strlen($value['postcode']) < 2) || (utf8_strlen($value['postcode']) > 10)) {
					$this->error['address_postcode'][$key] = $this->language->get('error_postcode');
				}
			
				if ($value['country_id'] == '') {
					$this->error['address_country'][$key] = $this->language->get('error_country');
				}
				
				if ($value['zone_id'] == '') {
					$this->error['address_zone'][$key] = $this->language->get('error_zone');
				}	
			}
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

  	private function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/customer')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}	
	  	 
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
  	} 
	
	public function login() {
		$customer = CustomerDAO::getInstance()->getCustomer($this->parameters['customerId']);
				
		if ($customer) {
			$token = md5(mt_rand());
			CustomerDAO::getInstance()->editToken($this->parameters['customerId'], $token);

			$store = StoreDAO::getInstance()->getStore($this->parameters['storeId']);
			if ($store) {
				$this->redirect($store['url'] . 'index.php?route=account/login&token=' . $token);
			} else { 
				$this->redirect(HTTP_CATALOG . 'index.php?route=account/login&token=' . $token);
			}
		} else {
			$this->load->language('error/not_found');
			$this->document->setTitle($this->language->get('heading_title'));
			$this->data['heading_title'] = $this->language->get('heading_title');
			$this->data['text_not_found'] = $this->language->get('text_not_found');

			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => false
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('error/not_found', 'token=' . $this->session->data['token'], 'SSL'),
				'separator' => ' :: '
			);
		
			$this->template = 'error/not_found.tpl';
			$this->children = array(
				'common/header',
				'common/footer'
			);
		
			$this->getResponse()->setOutput($this->render());
		}
	}

    public function orderItemsHistory() {
        $orderItems = OrderItemDAO::getInstance()->getOrderItems(
            array('filterCustomerId' => array($this->parameters['customerId'])), null, true);
        $this->data = array();
        $this->data['events'] = array();
        foreach ($orderItems as $orderItem) {
            $orderItemHistory = OrderItemDAO::getInstance()->getOrderItemHistory($orderItem->getId());
//            $this->log->write(print_r($orderItemHistory, true));
            foreach ($orderItemHistory as $orderItemHistoryEntry)
                $this->data['events'][] = array(
                    'orderId' => $orderItem->getOrderId(),
                    'orderItemId' => $orderItem->getId(),
                    'eventDate' => $orderItemHistoryEntry['date_added'],
                    'statusName' => $orderItemHistoryEntry['name']
                );
        }

        $this->data['textOrderId'] = $this->language->get('ORDER_ID');
        $this->data['textOrderItemId'] = $this->language->get('ORDER_ITEM_ID');
        $this->data['textEventDate'] = $this->language->get('DATE');
        $this->data['textStatusName'] = $this->language->get('STATUS');
        $this->template = 'sale/customerOrderItemsHistory.php';
        $result = $this->render();
        $json = array('content' => $result);
//        $this->log->write(print_r($json, true));
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function purgeCart()
    {
        CustomerDAO::getInstance()->purgeCart($this->parameters['customerId']);
        $customer = CustomerDAO::getInstance()->getCustomer($this->parameters['customerId']);
        $json = array('success' => sprintf($this->language->get('SUCCESS_CART_PURGED'), $customer['nickname']));
        $this->getResponse()->setOutput(json_encode($json));
    }
		
	public function zone() {
		$output = '<option value="">' . $this->language->get('text_select') . '</option>'; 
		
		$this->load->model('localisation/zone');
		
		$results = $this->model_localisation_zone->getZonesByCountryId($_REQUEST['country_id']);
		
		foreach ($results as $result) {
			$output .= '<option value="' . $result['zone_id'] . '"';

			if (isset($_REQUEST['zone_id']) && ($_REQUEST['zone_id'] == $result['zone_id'])) {
				$output .= ' selected="selected"';
			}

			$output .= '>' . $result['name'] . '</option>';
		}

		if (!$results) {
			$output .= '<option value="0">' . $this->language->get('text_none') . '</option>';
		}

		$this->getResponse()->setOutput($output);
	}
	
	public function transaction() {
        $this->load->library('Transaction');
        $customer = CustomerDAO::getInstance()->getCustomer($this->parameters['customerId']);
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->user->hasPermission('modify', 'sale/customer')) {
                if ($this->request->post['action'] == 'add')
                {
                    if ($this->request->post['amount'] < 0)
                    {
                        Transaction::addCredit(
                            $this->parameters['customerId'],
                            -$this->request->post['amount'],
                            $customer['base_currency_code'],
                            $this->registry,
                            $this->request->post['description']);
                        $this->data['success'] = $this->language->get('SUCCESS_CREDIT_ADDED');
                    }
                    elseif ($this->request->post['amount'] > 0)
                    {
                        Transaction::addTransaction(
                            0,
                            $this->parameters['customerId'],
                            $this->request->post['amount'],
                            $customer['base_currency_code'],
                            $this->request->post['description']);
                        $this->data['success'] = $this->language->get('SUCCESS_PAYMENT_ADDED');
                    }
                }
                elseif ($this->request->post['action'] == 'delete')
                {
                    $modelSaleTransaction = $this->load->model('sale/transaction');
                    $transaction = $modelSaleTransaction->getTransaction($this->request->post['transactionId']);
                    if ($transaction['invoice_id'] != 0)
                    {
                        $this->data['error_warning'] = $this->language->get('ERROR_RELATED_INVOICE_EXISTS');
                    }
                    elseif ($transaction['currency_code'] != $customer['base_currency_code'])
                    {
                        $this->data['error_warning'] = $this->language->get('ERROR_TRANSACTION_AND_CUSTOMER_CURRENCY_DONT_MATCH');
                    }
                    else
                    {
                        Transaction::deleteTransaction($this->request->post['transactionId']);
                        $this->data['success'] = sprintf(
                            $this->language->get('SUCCESS_TRANSACTION_DELETED'), $this->request->post['transactionId']);
                    }
                }
                $customer = CustomerDAO::getInstance()->getCustomer($this->parameters['customerId']);
            }
            else
                $this->data['error_warning'] = $this->language->get('error_permission');
        }
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !$this->user->hasPermission('modify', 'sale/customer')) {
			$this->data['error_warning'] = $this->language->get('error_permission');
		}
		
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_balance'] = $this->language->get('text_balance');
		
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_amount'] = $this->language->get('column_amount');
        $this->data['textAction'] = $this->language->get('ACTIONS');
        $this->data['textInvoiceId'] = $this->language->get('INVOICE_ID');
        $this->data['textTransactionId'] = $this->language->get('TRANSACTION_ID');

        $this->getTransactions($customer);
        $this->getCreditRequests($customer);
        $this->template = 'sale/customerTransaction.php';
        $this->getResponse()->setOutput($this->render());
        return;

		if (isset($_REQUEST['page'])) {
			$page = $_REQUEST['page'];
		} else {
			$page = 1;
		}  
		
		$this->data['transactions'] = array();
			
		$results = CustomerDAO::getInstance()->getTransactions($_REQUEST['customer_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
            $actions = array();
            $actions[] = array(
                'text' => $this->language->get('DELETE'),
                'onclick' => "deleteTransaction(" . $result['customer_transaction_id'] . ");"
            );

        	$this->data['transactions'][] = array(
                'transactionId' => $result['customer_transaction_id'],
                'actions' => $actions,
				'amount'      => $this->currency->format($result['amount'], $result['currency_code'], 1),
				'description' => $result['description'],
        		'date_added'  => $result['date_added'],
                'invoiceId' => $result['invoice_id'] ? $result['invoice_id'] : '',
                'invoiceUrl' => $this->url->link('sale/invoice/showForm', 'invoiceId=' . $result['invoice_id'] . '&token=' . $this->session->data['token'], 'SSL')
        	);
      	}			
		
		$this->data['balance'] = $this->currency->format(
            CustomerDAO::getInstance()->getCustomerBalance($_REQUEST['customer_id']), $customer['base_currency_code'], 1);
		
		$transaction_total = CustomerDAO::getInstance()->getTotalTransactions($_REQUEST['customer_id']);
			
		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('sale/customer/transaction', 'token=' . $this->session->data['token'] . '&customer_id=' . $_REQUEST['customer_id'] . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();

		$this->template = 'sale/customer_transaction.tpl';		
		
		$this->getResponse()->setOutput($this->render());
	}
			
	public function reward() {
        if (!$this->user->hasPermission('access', 'sale/customer_deposit'))
        {
            $this->data['error_fatal'] = $this->language->get('error_deposit_permission');
        }
        else
        {
            $this->data['error_fatal'] = '';
            if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->user->hasPermission('modify', 'sale/customer')) {
                CustomerDAO::getInstance()->addReward($_REQUEST['customer_id'], $this->request->post['description'], $this->request->post['points']);

                $this->data['success'] = $this->language->get('text_success');
            } else {
                $this->data['success'] = '';
            }




            $this->data['text_no_results'] = $this->language->get('text_no_results');
            $this->data['text_balance'] = $this->language->get('text_balance');

            $this->data['column_date_added'] = $this->language->get('column_date_added');
            $this->data['column_description'] = $this->language->get('column_description');
            $this->data['column_points'] = $this->language->get('column_points');

            if (isset($_REQUEST['page'])) {
                $page = $_REQUEST['page'];
            } else {
                $page = 1;
            }

            $this->data['rewards'] = array();

            $results = CustomerDAO::getInstance()->getRewards($_REQUEST['customer_id'], ($page - 1) * 10, 10);

            foreach ($results as $result) {
                $this->data['rewards'][] = array(
                    'points'      => $result['points'],
                    'description' => $result['description'],
                    'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added']))
                );
            }

            $this->data['balance'] = CustomerDAO::getInstance()->getRewardTotal($_REQUEST['customer_id']);

            $reward_total = CustomerDAO::getInstance()->getTotalRewards($_REQUEST['customer_id']);

            $pagination = new Pagination();
            $pagination->total = $reward_total;
            $pagination->page = $page;
            $pagination->limit = 10;
            $pagination->text = $this->language->get('text_pagination');
            $pagination->url = $this->url->link('sale/customer/reward', 'token=' . $this->session->data['token'] . '&customer_id=' . $_REQUEST['customer_id'] . '&page={page}', 'SSL');

            $this->data['pagination'] = $pagination->render();
        }
		$this->template = 'sale/customer_reward.tpl';		
		
		$this->getResponse()->setOutput($this->render());
	}

	public function autocomplete() {
		$json = array();
		
		if (isset($_REQUEST['filter_name'])) {
			$data = array(
				'filter_name' => $_REQUEST['filter_name'],
				'start'       => 0,
				'limit'       => 20
			);
		
			$results = CustomerDAO::getInstance()->getCustomers($data);
			
			foreach ($results as $result) {
				$json[] = array(
					'customer_id'    => $result['customer_id'], 
					'name'           => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
					'customer_group' => $result['customer_group'],
					'firstname'      => $result['firstname'],
					'lastname'       => $result['lastname'],
                    'nickname'       => $result['nickname'],
					'email'          => $result['email'],
					'telephone'      => $result['telephone'],
					'fax'            => $result['fax'],
					'address'        => CustomerDAO::getInstance()->getAddresses($result['customer_id'])
				);					
			}
		}

		$sort_order = array();
	  
		foreach ($json as $key => $value) {
			$sort_order[$key] = $value['nickname'];
		}

		array_multisort($sort_order, SORT_ASC, $json);

		$this->getResponse()->setOutput(json_encode($json));
	}		
	
	public function address() {
		$json = array();
		
		if (isset($_REQUEST['address_id']) && $_REQUEST['address_id']) {
			$json = CustomerDAO::getInstance()->getAddress($_REQUEST['address_id']);
		}

		$this->getResponse()->setOutput(json_encode($json));
	}
}
?>