<?php
namespace system\engine;

class CustomerZoneController extends CustomerController {
    public function __construct($registry) {
        parent::__construct($registry);
        if (!$this->getCurrentCustomer()->isLogged()) {
            $this->session->data['redirect'] = $this->selfUrl;
            
            $this->redirect($this->url->link('account/login', '', 'SSL'));
            throw new \Exception('Not logged in');
        }
    }
} 