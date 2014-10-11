<?php
namespace system\engine;

class CustomerController extends \Controller {
    /**
     * @return \Cart
     */
    public function getCart() {
        return $this->registry->get('cart');
    }
} 