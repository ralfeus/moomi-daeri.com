<?php
require_once("ShippingMethodModel.php");
class ModelShippingFlat extends Model implements ShippingMethodModel
{
  	public function getCost($destination, $weight)
    {
        return $this->config->get('flat_cost');
  	}
}
?>