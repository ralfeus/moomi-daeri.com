<?php
use system\engine\CustomerZoneController;
use system\library\Messaging;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 29.7.12
 * Time: 21:25
 * To change this template use File | Settings | File Templates.
 */
class ControllerAccountAddCredit extends CustomerZoneController
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->language('account/addCredit');
        $this->takeSessionVariables();
    }

    public function create()
    {
        if ($this->validateInput())
        {
            //$this->load->library("system\library\Messaging");
            Messaging::submitSystemMessage(
                $this->customer->getId(),
                0,
                SYS_MSG_ADD_CREDIT,
                array(
                    'amount' => $this->request->request['amount'],
                    'currency' => $this->request->request['currency'],
                    'comment' => $this->request->request['comment'],
                    'status' => ADD_CREDIT_STATUS_PENDING
                )
            );
            $this->session->data['notifications']['success'] = $this->language->get('SUCCESS_ADD_CREDIT_REQUEST_SENT');
            $this->redirect($this->url->link('account/account', '', 'SSL'));
        }
        else
            $this->index();
    }

    public function index()
    {
        $this->log->write(print_r($this->request->request, true));
        $this->data['amount'] = isset($this->request->request['amount'])
            ? $this->request->request['amount'] : '';
        $this->data['comment'] = isset($this->request->request['comment'])
            ? $this->request->request['comment'] : '';
        $modelLocalisationCurrency = $this->load->model('localisation/currency');
        $selected_currency =
            isset($this->data['currency'])
                ? $this->data['currency']
                : $this->customer->getBaseCurrency()->getCode();

        $this->data['currencies'] = array();
        foreach ($modelLocalisationCurrency->getCurrencies() as $currency)
        {
            $this->data['currencies'][] = array(
                'code' => $currency['code'],
                'name' => $currency['title'],
                'selected' => ($currency['code'] == $selected_currency) ? "selected=\"true\"" : ""
            );
        }

        /// Initialize interface
        $this->data['action'] = $this->url->link('account/addCredit/create', '', 'SSL');
        $this->data['textAmount'] = $this->language->get('AMOUNT');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textSubmit'] = $this->language->get('SUBMIT');
        $this->setBreadcrumbs();
        $templateName = '/template/account/addCredit.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName)) {
            $this->template = $this->config->get('config_template') . $templateName;
        } else {
            $this->template = 'default' . $templateName;
        }

        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/footer',
            'common/header'
        );

        $this->getResponse()->setOutput($this->render());
    }

    protected function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();
    }

    private function validateInput()
    {
        if (!is_numeric($this->request->request['amount']))
            $this->data['notifications']['error']['amount'] = $this->language->get('ERROR_WRONG_NUMBER_FORMAT');

        return !isset($this->data['notifications']['error']);
    }
}
