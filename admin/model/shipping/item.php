<?php
require_once("ShippingMethodModel.php");
class ModelShippingItem extends Model implements ShippingMethodModel
{
  	public function getCost($destination, $orderItems)
    {
        $query = $this->db->query("
            SELECT *
            FROM " . DB_PREFIX . "zone_to_geo_zone
            WHERE
                geo_zone_id = '" . (int)$this->config->get('item_geo_zone_id') . "'
                AND country_id = '" . (int)$address['country_id'] . "'
                AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')
        ");

        if (!$this->config->get('item_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }
        $this->log->write($this->config->get('item_cost'));
        $this->log->write(print_r($orderItems, true));
        $cost = 0;
//        if ($status)
            foreach ($orderItems as $orderItem)
                $cost += $this->config->get('item_cost') * $orderItem['quantity'];

        return $cost;
  	}
}
