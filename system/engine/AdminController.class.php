<?php
namespace system\engine;

class AdminController extends \Controller{
    protected function getFilterParameters() {
        $result = parent::getFilterParameters();
        $result['token'] = $this->parameters['token'];
        return $result;
    }
} 