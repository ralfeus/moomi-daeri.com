<?php
require_once("ShippingMethodModel.php");
class ModelShippingEMS extends Model implements ShippingMethodModel
{
  	public function getCost($destination, $weight)
    {
        $cost = 0;
        $rates = explode(',', $this->config->get($destination . '_rate'));

        foreach ($rates as $rate) {
            $data = explode(':', $rate);

            if ($data[0] >= $weight)
            {
                if (isset($data[1]))
                    $cost = (float)$data[1];
                break;
            }
        }
		return $cost;
  	}
}
?>