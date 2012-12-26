<?php
require_once("ShippingMethodModel.php");
class ModelShippingEMS extends Model implements ShippingMethodModel
{
  	public function getCost($destination, $orderItems)
    {
        $cost = 0; $totalWeight = 0;
        $rates = explode(',', $this->config->get($destination . '_rate'));

        foreach ($orderItems as $orderItem)
            $totalWeight +=
                $this->weight->convert(
                    $orderItem['weight'],
                    $orderItem['weight_class_id'],
                    $this->config->get('config_weight_class_id')) * $orderItem['quantity'];

        foreach ($rates as $rate) {
            $data = explode(':', $rate);
            if ($data[0] >= $totalWeight)
            {
                if (isset($data[1]))
                    $cost = (float)$data[1];
                break;
            }
        }
		return $cost;
  	}
}
