<?php
require_once("ShippingMethodModel.php");
class ModelShippingPickup extends Model implements ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = null)
    {
        return 0;
  	}
}