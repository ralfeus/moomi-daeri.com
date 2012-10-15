<?php
class ControllerAccountTransaction extends Controller {
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->language->load('account/transaction');
        $this->load->model('account/transaction');
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->data['heading_title'] = $this->language->get('headingTitle');
    }

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/transaction', '', 'SSL');
			
	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}

        $this->setBreadcrumbs();

        $this->data['addCreditUrl'] = $this->url->link('account/addCredit', '', 'SSL');
        $this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_description'] = $this->language->get('column_description');
		$this->data['column_amount'] = $this->language->get('column_amount');
        $this->data['textCurrency'] = $this->language->get('CURRENCY');
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_empty'] = $this->language->get('text_empty');
		$this->data['textAddCredit'] = $this->language->get('ADD_CREDIT');
        $this->data['textInvoiceId'] = $this->language->get('INVOICE_ID');
        $this->data['textTransactionId'] = $this->language->get('TRANSACTION_ID');
        $this->data['total'] = $this->currency->format($this->customer->getBalance(), $this->customer->getBaseCurrency()->getCode(), 1);


        if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}		
		
		$this->data['transactions'] = array();
		
		$data = array(				  
			'sort'  => 'customer_transaction_id',
			'order' => 'DESC',
			'start' => ($page - 1) * 10,
			'limit' => 10
		);
		
		$transaction_total = $this->model_account_transaction->getTotalTransactions($data);
	
		$transactions = $this->model_account_transaction->getTransactions($data);
 		
    	foreach ($transactions as $transaction) {
			$this->data['transactions'][] = array(
				'amount'      => $this->currency->format($transaction['amount'], $transaction['currency_code'], 1),
                'currency_code' => $transaction['currency_code'],
                'date_added'  => $transaction['date_added'],
				'description' => $transaction['description'],
                'invoiceId' => $transaction['invoice_id'] ? $transaction['invoice_id'] : '',
                'invoiceUrl' => $this->url->link('account/invoice/showForm', 'invoiceId=' . $transaction['invoice_id'], 'SSL'),
                'transactionId' => $transaction['customer_transaction_id']
			);
		}	

		$pagination = new Pagination();
		$pagination->total = $transaction_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('account/transaction', 'page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/transaction.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/transaction.tpl';
		} else {
			$this->template = 'default/template/account/transaction.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);
						
		$this->response->setOutput($this->render());		
	}

    private function setBreadcrumbs()
    {
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
    }
}