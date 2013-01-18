<?php
require_once("ShippingMethodModel.php");
class ModelShippingWeight extends Model implements ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = array())
    {
        $cost = 0;
        $rates = explode(',', $this->config->get($destination . '_rate'));
        if (empty($ext['weight']))
        {
            $totalWeight = 0;
            foreach ($orderItems as $orderItem)
                $totalWeight +=
                    $this->weight->convert(
                        $orderItem['weight'],
                        $orderItem['weight_class_id'],
                        $this->config->get('config_weight_class_id')) * $orderItem['quantity'];
        }
        else
            $totalWeight = $ext['weight'];

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
