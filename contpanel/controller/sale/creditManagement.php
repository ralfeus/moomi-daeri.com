<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 30.7.12
 * Time: 11:22
 * To change this template use File | Settings | File Templates.
 */
class ControllerSaleCreditManagement extends Controller
{
    private $modelSaleCustomer;

    public function __construct($registry)
    {
        parent::__construct($registry);
//        $this->log->write("Starting");
        $this->takeSessionVariables();
        $this->load->library("Messaging");
        $this->load->language('sale/creditManagement');
        $this->document->setTitle($this->language->get('HEADING_TITLE'));
        $this->data['headingTitle'] = $this->language->get('HEADING_TITLE');
        $this->modelSaleCustomer = $this->load->model('sale/customer');
    }

    public function accept()
    {
//        $this->log->write("Starting");
        $request = Messaging::getSystemMessage($this->request->request['requestId']);
        $request['data']->status = ADD_CREDIT_STATUS_ACCEPTED;
        $this->log->write(print_r($request, true));
        Messaging::updateSystemMessage($request['messageId'], $request['data']);
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
        foreach ($this->modelSaleCustomer->getCustomers($data) as $customer)
            if (!in_array($customer['customer_id'], $tmpResult))
                $tmpResult[$customer['customer_id']] = $customer['name'] . ' / ' . $customer['nickname'];
        natcasesort($tmpResult);
        return $tmpResult;
    }

    public function index()
    {
        $data = array();
        $data['systemMessageType'] = SYS_MSG_ADD_CREDIT;
        $data = array_merge($data, $this->parameters);
        $addCreditRequests = Messaging::getSystemMessages($data);
//        $this->log->write(print_r($addCreditRequests, true));
        $this->data['customersToFilterBy'] = $this->getCustomers();
        $this->data['requests'] = array();
        foreach ($addCreditRequests as $addCreditRequest)
        {
            $customer = $this->modelSaleCustomer->getCustomer($addCreditRequest['senderId']);
            $actions = array();
            if ($addCreditRequest['data']->status == ADD_CREDIT_STATUS_PENDING)
            {
                $actions['accept'] = array(
                        'text' => $this->language->get('ACCEPT'),
                        'onclick' => 'acceptRequest(' . $addCreditRequest['messageId'] . ', this)'
                );
                $actions['reject'] = array(
                        'text' => $this->language->get('REJECT'),
                        'onclick' => 'rejectRequest(' . $addCreditRequest['messageId'] . ', this)'
                );
            }
            $this->data['requests'][] = array(
                'requestId' => $addCreditRequest['messageId'],
                'actions' => $actions,
                'amount' => $addCreditRequest['data']->amount,
                'comment' => $addCreditRequest['data']->comment,
                'currency' => $addCreditRequest['data']->currency,
                'customerName' => $customer['lastname'] . ' ' . $customer['firstname'] . ' / ' . $customer['nickname'],
                'customerUrl' => $this->url->link(
                    'sale/customer/update',
                    'token=' . $this->session->data['token'] . '&customer_id=' . $addCreditRequest['senderId'],
                    'SSL'),
                'status' => $this->load->model('localisation/requestStatus')->getStatus($addCreditRequest['data']->status),
                'statusId' => $addCreditRequest['data']->status,
                'timeAdded' => $addCreditRequest['timeAdded']
            );
        }
        $this->data = array_merge($this->data, $this->parameters);

        /// Initialize interface
        $this->setBreadcrumps();
        $this->data['textActions'] = $this->language->get('ACTIONS');
        $this->data['textAmount'] = $this->language->get('AMOUNT');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textCustomer'] = $this->language->get('CUSTOMER');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textPaymentMethod'] = $this->language->get('PAYMENT_METHOD');
        $this->data['textRequestId'] = $this->language->get('REQUEST_ID');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textTimeAdded'] = $this->language->get('TIME_ADDED');
        $this->data['urlSelf'] = $this->url->link($this->selfRoute, 'token=' . $this->parameters['token'], 'SSL');
        $this->template = 'sale/creditManagement.tpl';
        $this->children = array(
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render());
    }

    protected function initParameters()
    {
        $this->parameters['amount'] = empty($_REQUEST['amount']) ? null : $_REQUEST['amount'];
        $this->parameters['comment'] = empty($_REQUEST['comment']) ? null : $_REQUEST['comment'];
        $this->parameters['filterCustomerId'] = empty($_REQUEST['filterCustomerId']) ? array() : $_REQUEST['filterCustomerId'];
        $this->parameters['requestId'] = empty($_REQUEST['requestId']) ? null : $_REQUEST['requestId'];
        $this->parameters['token'] = $this->session->data['token'];
    }

    public function reject()
    {
        $request = Messaging::getSystemMessage($this->request->request['requestId']);
        $request['data']->status = ADD_CREDIT_STATUS_REJECTED;
//        $this->log->write(print_r($request, true));
        Messaging::updateSystemMessage($request['messageId'], $request['data']);
    }

    public function saveAmount()
    {
//        $this->log->write(print_r($this->parameters, true));
        if ($this->validateInput())
        {
            $request = Messaging::getSystemMessage($this->parameters['requestId']);
            $request['data']->amount = $this->parameters['amount'];
            Messaging::updateSystemMessage($request['messageId'], $request['data']);
        }
    }

    public function saveComment()
    {
//        $this->log->write(print_r($this->parameters, true));
        $request = Messaging::getSystemMessage($this->parameters['requestId']);
        $request['data']->comment = $this->parameters['comment'];
        Messaging::updateSystemMessage($request['messageId'], $request['data']);
    }

    protected function setBreadcrumps()
    {
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('HEADING_TITLE'),
            'href' => '',
            'separator' => ' :: '
        );
    }

    private function validateInput()
    {
        return
            is_numeric($this->parameters['requestId'])
            & is_numeric($this->parameters['amount']);
    }
}