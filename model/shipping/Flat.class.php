<?php
namespace  model\shipping;
class Flat extends ShippingMethodBase {
  	public function getCost($destination, $orderItems, $ext = null)
    {
        return $this->config->get('flat_cost');
  	}

    public function getMethodData($address)
    {
        if ($this->config->get('flat_status'))
        {
            $sql = "
                SELECT gz.geo_zone_id, gz.name, gz.description
                FROM
                    setting AS s
                    JOIN geo_zone AS gz ON s.key = 'flat_geo_zone_id' AND value IN (gz.geo_zone_id, 0)
                    JOIN zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
                WHERE
                    ztgz.country_id = " . (int)$address['country_id'] . "
                    AND (ztgz.zone_id = " . (int)$address['zone_id'] . " OR ztgz.zone_id = 0)
            ";
            $query = $this->getDb()->query($sql);
            if ($query->row)
            {
                $query->row['code'] = 'flat.flat';
                $query->row['shippingMethodName'] = 'Flat Shipping';
                return array ($query->row);
            }
            else
                return null;
        }
        else
            return null;
    }

    public function getName($languageResource = null) {
        return parent::getName('shipping/flat');
    }

    public function getQuote($address) {
        $this->load->language('shipping/flat');

        $query = $this->getDb()->query("
		    SELECT *
		    FROM zone_to_geo_zone
		    WHERE
		        geo_zone_id = :geoZoneId
		        AND country_id = :countryId
		        AND zone_id IN (0, :zoneId)
            ", [
            ':geoZoneId' => $this->config->get('flat_geo_zone_id'),
            ':countryId' => $address['country_id'],
            ':zoneId' => $address['zone_id']
        ]);

        if (!$this->config->get('flat_geo_zone_id')) {
            $status = true;
        } else {
            $status = boolval($query->num_rows);
        }

        $methodData = array();

        if ($status) {
            $quote_data = array();

            $quote_data['flat'] = array(
                'code'         => 'flat.flat',
                'title'        => $this->language->get('text_description'),
                'cost'         => $this->config->get('flat_cost'),
                'tax_class_id' => $this->config->get('flat_tax_class_id'),
                'text'         => $this->currency->format($this->tax->calculate($this->config->get('flat_cost'), $this->config->get('flat_tax_class_id'), $this->config->get('config_tax')))
            );

            $methodData = array(
                'code'       => 'flat',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('flat_sort_order'),
                'error'      => false
            );
        }

        return $methodData;
    }

    public function isEnabled() {
        return $this->config->get('flat_status');
    }

    public function getSortOrder() {
        return $this->config->get('flat_sort_order');
    }
}
