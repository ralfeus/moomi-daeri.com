<?php
require_once("ShippingMethodModel.php");
class ModelShippingPickup extends ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = null)
    {
        return 0;
  	}

    public function getName($languageResource = null)
    {
        return parent::getName('shipping/pickup');
    }
}