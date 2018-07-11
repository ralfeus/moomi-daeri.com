<?php
namespace system\engine;

use system\exception\NotLoggedInException;

class CustomerZoneController extends CustomerController {
    public function __construct($registry) {
        parent::__construct($registry);
        if (!$this->getCurrentCustomer()->isLogged()) {
            throw new NotLoggedInException($this->selfUrl);
        }
    }
} 