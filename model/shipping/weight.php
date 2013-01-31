<?php
require_once("ShippingMethodModel.php");
class ModelShippingWeight extends ShippingMethodModel
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

    public function getMethodData($address)
    {
        $sql = "
            SELECT gz.geo_zone_id, gz.name, gz.description
            FROM
                " . DB_PREFIX . "setting AS s
                JOIN " . DB_PREFIX . "geo_zone AS gz ON `key` = CONCAT('weight_', gz.geo_zone_id, '_status') AND value = 1
                JOIN " . DB_PREFIX . "zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
            WHERE
                ztgz.country_id = " . (int)$address['country_id'] . "
                AND ztgz.zone_id IN (" . (int)$address['zone_id'] . ", 0)
        ";
        $query = $this->db->query($sql);
        if ($query->row)
        {
            $query->row['code'] = 'weight.weight_' . $query->row['geo_zone_id'];
            $query->row['shippingMethodName'] = 'Weight based shipping';
            return $query->row;
        }
        else
            return null;
    }

    public function getName($languageResource = null)
    {
        return parent::getName('shipping/weight');
    }

    public function getQuote($address)
    {
        $methodData = $this->getMethodData($address);
        $methodData['quote'] = array(

        );
        return $methodData;
    }
}
