<?php
namespace  model\shipping;
class Item extends ShippingMethodBase
{
  	public function getCost($destination, $orderItems, $ext = null)
    {
        $cost = 0;
        foreach ($orderItems as $orderItem)
            $cost += $this->config->get('item_cost') * $orderItem->getQuantity();

        return $cost;
  	}

    public function getMethodData($address)
    {
        if ($this->config->get('item_status'))
        {
            $sql = "
                SELECT gz.geo_zone_id, gz.name, gz.description
                FROM
                    setting AS s
                    JOIN geo_zone AS gz ON s.key = 'item_geo_zone_id' AND value IN (gz.geo_zone_id, 0)
                    JOIN zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
                WHERE
                    ztgz.country_id = " . (int)$address['country_id'] . "
                    AND (ztgz.zone_id = " . (int)$address['zone_id'] . " OR ztgz.zone_id = 0)
            ";
            $query = $this->getDb()->query($sql);
            if ($query->row)
            {
                $query->row['code'] = 'item.item';
                $query->row['shippingMethodName'] = 'Per item';
                return array($query->row);
            }
            else
                return null;
        }
        else
            return null;
    }

    public function getName()
    {
        return $this->getNameByResource('shipping/item');
    }

    public function getQuote($address) {
        $this->load->language('shipping/item');

        $query = $this->getDb()->query("
            SELECT *
            FROM zone_to_geo_zone
            WHERE
                geo_zone_id = :geoZoneId
                AND country_id = :countryId
                AND zone_id IN (0, :zoneId)
            ", [
            ':geoZoneId' => $this->config->get('item_geo_zone_id'),
            ':countryId' => $address['country_id'],
            ':zoneId' => $address['zone_id']
        ]);

        if (!$this->config->get('item_geo_zone_id')) {
            $status = true;
        } else {
            $status = boolval($query->num_rows);
        }

        $methodData = array();

        if ($status) {
            $quote_data = array();

            $quote_data['item'] = array(
                'code'         => 'item.item',
                'title'        => $this->language->get('text_description'),
                'cost'         => $this->config->get('item_cost') * $this->cart->countProducts(),
                'tax_class_id' => $this->config->get('item_tax_class_id'),
                'text'         => $this->currency->format($this->tax->calculate($this->config->get('item_cost') * $this->cart->countProducts(), $this->config->get('item_tax_class_id'), $this->config->get('config_tax')))
            );

            $methodData = array(
                'code'       => 'item',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('item_sort_order'),
                'error'      => false
            );
        }

        return $methodData;
    }

    public function isEnabled() {
        return $this->config->get('item_status');
    }

    public function getSortOrder() {
        return $this->config->get('item_sort_order');
    }
}
