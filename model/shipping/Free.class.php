<?php
namespace  model\shipping;
class Free extends ShippingMethodBase {
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

    public function getName()
    {
        return $this->getNameByResource('shipping/free');
    }

    public function getQuote($address) {
        $this->load->language('shipping/free');

        $query = $this->getDb()->query("
            SELECT *
            FROM zone_to_geo_zone
            WHERE
              geo_zone_id = :geoZoneId
              AND country_id = :countryId
              AND zone_id IN (0, :zoneId)
            ", [
            ':geoZoneId' => $this->config->get('free_geo_zone_id'),
            ':countryId' => $address['country_id'],
            ':zoneId' => $address['zone_id']
        ]);

        if (!$this->config->get('free_geo_zone_id')) {
            $status = true;
        } else {
            $status = boolval($query->num_rows);
        }

        if ($this->cart->getSubTotal() < $this->config->get('free_total')) {
            $status = false;
        }

        $methodData = array();

        if ($status) {
            $quote_data = array();

            $quote_data['free'] = array(
                'code'         => 'free.free',
                'title'        => $this->language->get('text_description'),
                'cost'         => 0.00,
                'tax_class_id' => 0,
                'text'         => $this->currency->format(0.00)
            );

            $methodData = array(
                'code'       => 'free',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('free_sort_order'),
                'error'      => false
            );
        }

        return $methodData;
    }

    public function isEnabled() {
        return $this->config->get('free_status');
    }

    public function getSortOrder() {
        return $this->config->get('free_sort_order');
    }
}