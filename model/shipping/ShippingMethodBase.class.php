<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 2/7/2016
 * Time: 7:34 PM
 */
namespace model\shipping;

use model\extension\ExtensionBase;
use model\sale\OrderItem;
use system\exception\NotImplementedException;

abstract class ShippingMethodBase extends ExtensionBase {
    protected $name;
    /**
     * @param string $shippingMethodCode
     * @return string[]
     * @throws NotImplementedException
     */
    public function getAddress($shippingMethodCode) {
        throw new NotImplementedException("getAddress() isn't implemented in class " . self::class);
    }

    /**
     * @param string $destination
     * @param OrderItem[] $orderItems
     * @param array $ext
     * @return float
     */
    abstract function getCost($destination, $orderItems, $ext = null);

    abstract public function getMethodData($address);

    protected function getNameByResource($languageResource) {
        $this->load->language($languageResource);
        return $this->getLanguage()->get('headingTitle');
    }

    public abstract function getName();

    abstract function getQuote($address);
    public abstract function getSortOrder();
}
