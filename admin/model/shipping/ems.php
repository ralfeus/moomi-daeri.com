<?php
require_once("ShippingMethodModel.php");
class ModelShippingEMS extends ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = array())
    {
        $this->log->write(print_r($orderItems, true));
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

    public function getName($languageResource = null)
    {
        return parent::getName('shipping/ems');
    }
}
