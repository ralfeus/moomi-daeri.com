<?php
use system\engine\CustomerZoneController;
use system\library\Messaging;
use system\library\Status;

/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 10.9.12
 * Time: 13:44
 * To change this template use File | Settings | File Templates.
 */
class ControllerAccountCreditRequest extends CustomerZoneController {
    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->takeSessionVariables();
        //$this->load->library("system\library\Messaging");
        //$this->load->library("system\library\Status");
    }

    public function getList() {
        $addCreditRequests = Messaging::getInstance()->getSystemMessages(
            array(
                'systemMessageType' => SYS_MSG_ADD_CREDIT,
                'filterCustomerId' => array($this->customer->getId())
            )
        );
//        $this->log->write(print_r($addCreditRequests, true));
        foreach ($addCreditRequests as $addCreditRequest)
        {
//            if ($addCreditRequest['senderId'] != $this->customer->getId())
//                continue;
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
        $this->setBreadcrumps();
        $this->data['textAmount'] = $this->language->get('AMOUNT');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textRequestId'] = $this->language->get('REQUEST_ID');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textTimeAdded'] = $this->language->get('TIME_ADDED');
        $templateName = '/template/account/creditHistory.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $templateName))
            $this->template = $this->config->get('config_template') . $templateName;
        else
            $this->template = 'default' . $templateName;
        $this->children = array(
            'common/footer',
            'common/header',
            'common/column_right',
            'common/column_left',
            'common/content_top',
			'common/content_bottom'
        );
        $this->getResponse()->setOutput($this->render());
    }

    protected function setBreadcrumps()
    {
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', '', 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('HEADING_TITLE'),
            'href' => '',
            'separator' => ' :: '
        );
    }
}
