<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 2/7/2016
 * Time: 7:34 PM
 */
namespace {
    require_once("system/engine/OpenCartBase.php");
}

namespace model\shipping {
    use model\sale\OrderItem;

    abstract class ShippingMethodBase extends \OpenCartBase {
        /**
         * @param string $destination
         * @param OrderItem[] $orderItems
         * @param array $ext
         * @return float
         */
        abstract function getCost($destination, $orderItems, $ext = null);

        abstract public function getMethodData($address);

        protected function getName($languageResource = null) {
            $this->load->language($languageResource);
            return $this->getLanguage()->get('headingTitle');
        }

        abstract function getQuote($address);

        public abstract function isEnabled();
    }
}