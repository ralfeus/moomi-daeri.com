<?php
require_once("ShippingMethodModel.php");
class ModelShippingFree extends ShippingMethodModel
{
  	public function getCost($destination, $orderItems, $ext = null)
    {
        return 0;
  	}

    public function getMethodData($address)
    {
        if ($this->config->get('free_status'))
        {
            $sql = "
                SELECT gz.geo_zone_id, gz.name, gz.description
                FROM
                    setting AS s
                    JOIN geo_zone AS gz ON s.key = 'free_geo_zone_id' AND value IN (gz.geo_zone_id, 0)
                    JOIN zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
                WHERE
                    ztgz.country_id = " . (int)$address['country_id'] . "
                    AND (ztgz.zone_id = " . (int)$address['zone_id'] . " OR ztgz.zone_id = 0)
            ";
            $query = $this->getDb()->query($sql);
            if ($query->row)
            {
                $query->row['code'] = 'free.free';
                $query->row['shippingMethodName'] = 'Free shipping';
                return array($query->row);
            }
            else
                return null;
        }
        else
            return null;
    }

    public function getName($languageResource = null)
    {
        return parent::getName('shipping/free');
    }

    public function getQuote($address)
    {
        $methodData = $this->getMethodData($address);
        $methodData['quote'] = array(

        );
        return $methodData;
    }
}