<?php
require_once("ShippingMethodModel.php");
class ModelShippingItem extends ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = null)
    {
        $cost = 0;
        foreach ($orderItems as $orderItem)
            $cost += $this->config->get('item_cost') * $orderItem['quantity'];

        return $cost;
  	}

    public function getMethodData($address)
    {
        if ($this->config->get('item_status'))
        {
            $sql = "
                SELECT gz.geo_zone_id, gz.name, gz.description
                FROM
                    " . DB_PREFIX . "setting AS s
                    JOIN " . DB_PREFIX . "geo_zone AS gz ON s.key = 'item_geo_zone_id' AND value IN (gz.geo_zone_id, 0)
                    JOIN " . DB_PREFIX . "zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
                WHERE
                    ztgz.country_id = " . (int)$address['country_id'] . "
                    AND (ztgz.zone_id = " . (int)$address['zone_id'] . " OR ztgz.zone_id = 0)
            ";
            $query = $this->db->query($sql);
            if ($query->row)
            {
                $query->row['code'] = 'item.item';
                $query->row['shippingMethodName'] = 'Per item';
                return $query->row;
            }
            else
                return null;
        }
        else
            return null;
    }

    public function getName($languageResource = null)
    {
        return parent::getName('shipping/item');
    }

    public function getQuote($address)
    {
        $methodData = $this->getMethodData($address);
        $methodData['quote'] = array(

        );
        return $methodData;
    }
}
