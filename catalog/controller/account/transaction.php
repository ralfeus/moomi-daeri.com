<?php
use system\engine\CustomerZoneController;
use system\library\Messaging;
use system\library\Status;

class ControllerAccountTransaction extends CustomerZoneController {
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->language->load('account/transaction');
// ----- deposit modules START -----    
        @$this->language->load('account/multi_pay');
// ----- deposit modules END -----    
        $this->load->model('account/transaction');
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->data['heading_title'] = $this->language->get('headingTitle');
    }

    public function getCreditRequests()
    {
        //$this->load->library('system\library\Messaging');
        //$this->load->library('status');
        $addCreditRequests = Messaging::getInstance()->getSystemMessages(
            array(
                'systemMessageType' => SYS_MSG_ADD_CREDIT,
                'filterCustomerId' => array($this->customer->getId()),
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
                'status' => Status::getInstance($this->getRegistry())->getStatus($addCreditRequest['data']->status, $this->config->get('language_id'), true),
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
        $pagination->total = Messaging::getSystemMessagesCount(SYS_MSG_ADD_CREDIT, $this->customer->getId());
        $pagination->page = $this->parameters['creditRequestsPage'];
        $pagination->limit = 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->getUrl()->link(
            'account/transaction',
            'creditRequestsPage={page}&transactionsPage=' . $this->parameters['transactionsPage'], 'SSL');
        $this->data['creditRequestsPagination'] = $pagination->render();
    }

    public function getTransactions()
    {

        $this->data['addCreditUrl'] = $this->getUrl()->link('account/addCredit', '', 'SSL');
        $this->data['textTimeAdded'] = $this->language->get('TIME_ADDED');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textBalance'] = $this->language->get('BALANCE');
        $this->data['textExpenseAmount'] = $this->language->get('EXPENSE_AMOUNT');
        $this->data['textIncomeAmount'] = $this->language->get('INCOME_AMOUNT');
        $this->data['textCurrency'] = $this->language->get('CURRENCY');
        $this->data['text_total'] = $this->language->get('text_total');
        $this->data['text_empty'] = $this->language->get('text_empty');
        $this->data['textAddCredit'] = $this->language->get('ADD_CREDIT');
        $this->data['textInvoiceId'] = $this->language->get('INVOICE_ID');
        $this->data['textTransactionId'] = $this->language->get('TRANSACTION_ID');
// ----- deposit modules START -----    
      $this->data['text_my_finances'] = $this->language->get('text_my_finances');
			$this->data['text_deposit'] = $this->language->get('text_deposit');
			$this->data['text_transfer'] = $this->language->get('text_transfer');
// ----- deposit modules END -----    
        $this->data['total'] = $this->currency->format($this->customer->getBalance(), $this->customer->getBaseCurrency()->getCode(), 1);

        $data = array(
            'sort'  => 'customer_transaction_id',
            'order' => 'DESC',
            'start' => ($this->parameters['transactionsPage'] - 1) * 10,
            'limit' => 10
        );

        $transaction_total = $this->model_account_transaction->getTotalTransactions($data);
        $transactions = $this->model_account_transaction->getTransactions($data);

        $this->data['transactions'] = array();
        foreach ($transactions as $transaction) {
            $amount = -$transaction['amount'];
            $amountString = $this->currency->format($amount, $transaction['currency_code'], 1);
            $this->data['transactions'][] = array(
                'balance' => $this->currency->format($transaction['balance'], $this->customer->getBaseCurrency()->getCode(), 1),
                'expenseAmount'      => $amount < 0 ? $amountString : '',
                'incomeAmount'      => $amount >= 0 ? $amountString : '',
                'currency_code' => $transaction['currency_code'],
                'date_added'  => $transaction['date_added'],
                'description' => $transaction['description'],
                'invoiceId' => $transaction['invoice_id'] ? $transaction['invoice_id'] : '',
                'invoiceUrl' => $this->getUrl()->link('account/invoice/showForm', 'invoiceId=' . $transaction['invoice_id'], 'SSL'),
                'transactionId' => $transaction['customer_transaction_id']
            );
        }

        $pagination = new Pagination();
        $pagination->total = $transaction_total;
        $pagination->page = $this->parameters['transactionsPage'];
        $pagination->limit = 10;
        $pagination->text = $this->language->get('text_pagination');
        $pagination->url = $this->getUrl()->link(
            'account/transaction',
            'transactionsPage={page}&creditRequestsPage=' . $this->parameters['creditRequestsPage'], 'SSL');
        $this->data['transactionsPagination'] = $pagination->render();
    }

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->getUrl()->link('account/transaction', '', 'SSL');
			
	  		$this->redirect($this->getUrl()->link('account/login', '', 'SSL'));
    	}
        $this->getCreditRequests();
        $this->getTransactions();
        $this->setBreadcrumbs();


		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/transaction.tpl'))
            $this->template = $this->config->get('config_template') . '/template/account/transaction.tpl';
        else
            $this->template = 'default/template/account/transaction.tpl';
		$this->getResponse()->setOutput($this->render());
	}

    protected function initParameters()
    {
        $this->parameters['creditRequestsPage'] = empty($_REQUEST['creditRequestsPage']) ? 1 : $_REQUEST['creditRequestsPage'];
        $this->parameters['transactionsPage'] = empty($_REQUEST['transactionsPage']) ? 1 : $_REQUEST['transactionsPage'];
    }

    protected function setBreadcrumbs($breadcrumbs = [])
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->getUrl()->link('common/home'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_account'),
            'href'      => $this->getUrl()->link('account/account', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_transaction'),
            'href'      => $this->getUrl()->link('account/transaction', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
    }
}