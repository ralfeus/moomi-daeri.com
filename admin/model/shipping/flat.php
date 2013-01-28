<?php
require_once("ShippingMethodModel.php");
class ModelShippingFlat extends ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = null)
    {
        return $this->config->get('flat_cost');
  	}

    public function getName($languageResource = null)
    {
        return parent::getName('shipping/flat');
    }
}
