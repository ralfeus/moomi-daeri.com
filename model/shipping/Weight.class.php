<?php
namespace  model\shipping;
class Weight extends ShippingMethodBase {
  	public function getCost($destination, $orderItems, $ext = array())
    {
        $cost = 0;
        $rates = explode(',', $this->config->get($destination . '_rate'));
        if (empty($ext['weight'])) {
            $totalWeight = 0;
            foreach ($orderItems as $orderItem) {
                $totalWeight +=
                    $this->weight->convert(
                        $orderItem->getWeight(),
                        $orderItem->getWeightClassId(),
                        $this->config->get('config_weight_class_id')) * $orderItem->getQuantity();
            }
        } else {
            $totalWeight = $ext['weight'];
        }

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
                setting AS s
                JOIN geo_zone AS gz ON `key` = CONCAT('weight_', gz.geo_zone_id, '_status') AND value = 1
                JOIN zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
            WHERE
                ztgz.country_id = " . (int)$address['country_id'] . "
                AND ztgz.zone_id IN (" . (int)$address['zone_id'] . ", 0)
        ";
        $query = $this->getDb()->query($sql);
        if ($query->row)
        {
            $result = array();
            foreach ($query->rows as $row)
            {
                $row['code'] = 'weight.weight_' . $row['geo_zone_id'];
                $row['shippingMethodName'] = 'Weight based shipping ' . $row['name'];
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }

    public function getName($languageResource = null) {
        return parent::getName('shipping/weight');
    }

    public function getQuote($address) {
        $this->load->language('shipping/weight');
        $quote_data = array();
        $query = $this->getDb()->query("SELECT * FROM geo_zone ORDER BY name");

        foreach ($query->rows as $result) {
            if ($this->config->get('weight_' . $result['geo_zone_id'] . '_status')) {
                $query = $this->getDb()->query("
                    SELECT *
                    FROM zone_to_geo_zone
                    WHERE
                        geo_zone_id = :geoZoneId
                        AND country_id = :countryId
                        AND zone_id IN (0, :zoneId)
                    ", [
                    ':geoZoneId' => $result['geo_zone_id'],
                    ':countryId' => $address['country_id'],
                    ':zoneId' => $address['zone_id']
                ]);

                $status = boolval($query->num_rows);
            } else {
                $status = false;
            }

            if ($status) {
                $cost = '';
                $weight = $this->cart->getWeight($this->session->data['selectedCartItems']);

                $rates = explode(',', $this->config->get('weight_' . $result['geo_zone_id'] . '_rate'));

                foreach ($rates as $rate) {
                    $data = explode(':', $rate);

                    if ($data[0] >= $weight) {
                        if (isset($data[1])) {
                            $cost = $data[1];
                        }

                        break;
                    }
                }

                if ((string)$cost != '') {
                    $quote_data['weight_' . $result['geo_zone_id']] = array(
                        'code'         => 'weight.weight_' . $result['geo_zone_id'],
                        'title'        => $result['name'] . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('config_weight_class_id')) . ')',
                        'cost'         => $cost,
                        'description'  => $result['description'],
                        'tax_class_id' => $this->config->get('weight_tax_class_id'),
                        'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get('weight_tax_class_id'), $this->config->get('config_tax')))
                    );
                }
            }
        }

        $method_data = array();

        if ($quote_data) {
            $method_data = array(
                'code'       => 'weight',
                'title'      => $this->language->get('text_title'),
                'quote'      => $quote_data,
                'sort_order' => $this->config->get('weight_sort_order'),
                'error'      => false
            );
        }

        return $method_data;
    }

    public function isEnabled() {
        return $this->config->get('weight_status');
    }

    public function getSortOrder() {
        return $this->config->get('weight_sort_order');
    }
}
