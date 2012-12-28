<?php
require_once("ShippingMethodModel.php");
class ModelShippingFlat extends Model implements ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = null)
    {
        return $this->config->get('flat_cost');
  	}
}
