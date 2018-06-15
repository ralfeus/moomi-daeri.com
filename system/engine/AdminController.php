<?php
namespace system\engine;

use system\library\User;

class AdminController extends \system\engine\Controller {
    protected function getFilterParameters() {
        $result = parent::getFilterParameters();
        $result['token'] = $this->parameters['token'];
        return $result;
    }

    /**
     * @return User
     */
    protected function getUser() {
        return $this->getRegistry()->get('user');
    }
} 