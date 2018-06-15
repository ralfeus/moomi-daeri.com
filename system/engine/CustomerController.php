<?php
namespace system\engine;

use Cart;

class CustomerController extends Controller {
    /**
     * @return Cart
     */
    public function getCart() {
        return $this->registry->get('cart');
    }
} 