<?php
namespace system\engine;

class CustomerZoneController extends \Controller{
    public function __construct($registry) {
        parent::__construct($registry);
        if (!$this->getCustomer()->isLogged()) {
            $this->session->data['redirect'] = $this->selfUrl;

            $this->redirect($this->url->link('account/login', '', 'SSL'));
        }
    }
} 