<?php
namespace  model\shipping;
class Parcelforce_48 extends ShippingMethodBase {
  	public function getCost($destination, $orderItems, $ext = null) {
        return 0;
  	}

    public function getMethodData($address) {
        if ($this->getConfig()->get('parcelforce_48_status'))
        {
            $query = $this->getDb()->query("
                SELECT gz.geo_zone_id, gz.name, gz.description
                FROM
                    setting AS s
                    JOIN geo_zone AS gz ON s.key = 'parcelforce_48_geo_zone_id' AND value IN (gz.geo_zone_id, 0)
                    JOIN zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
                WHERE
                    ztgz.country_id = :countryId
                    AND ztgz.zone_id IN (0, :zoneId)
                ", [
                ':countryId' => $address['country_id'],
                ':zoneId' => $address['zone_id']
            ]);
            if ($query->row) {
                $query->row['code'] = 'parcelforce_48.parcelforce_48';
                $query->row['shippingMethodName'] = 'Parcelforce 48 Shipping';
                return array($query->row);
            }
            else
                return null;
        }
        else
            return null;
    }

    public function getName() {
        return $this->getNameByResource('shipping/parcelforce_48');
    }

    function getQuote($address) {
        $this->getLoader()->language('shipping/parcelforce_48');

        $query = $this->getDb()->query("
            SELECT *
            FROM zone_to_geo_zone
            WHERE
              geo_zone_id = :geoZoneId
              AND country_id = :countryId
              AND zone_id IN (0, :zoneId)
            ", [
            ':geoZoneId' => $this->getConfig()->get('parcelforce_48_geo_zone_id'),
            ':countryId' => $address['country_id'],
            ':zoneId' => $address['zone_id']
        ]);

        if (!$this->getConfig()->get('parcelforce_48_geo_zone_id')) {
            $status = true;
        } else {
            $status = boolval($query->num_rows);
        }

        $methodData = array();

        if ($status) {
            $cost = 0;
            $weight = $this->cart->getWeight();
            $sub_total = $this->cart->getSubTotal();

            $rates = explode(',', $this->getConfig()->get('parcelforce_48_rate'));

            foreach ($rates as $rate) {
                $data = explode(':', $rate);

                if ($data[0] >= $weight) {
                    if (isset($data[1])) {
                        $cost = $data[1];
                    }

                    break;
                }
            }

            $rates = explode(',', $this->getConfig()->get('parcelforce_48_insurance'));

            foreach ($rates as $rate) {
                $data = explode(':', $rate);

                if ($data[0] >= $sub_total) {
                    if (isset($data[1])) {
                        $insurance = $data[1];
                    }

                    break;
                }
            }

            $quote_data = array();

            if ((float)$cost) {
                $text = $this->language->get('text_description');

                if ($this->getConfig()->get('parcelforce_48_display_weight')) {
                    $text .= ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->getConfig()->get('config_weight_class_id')) . ')';
                }

                if ($this->getConfig()->get('parcelforce_48_display_insurance') && (float)$insurance) {
                    $text .= ' (' . $this->language->get('text_insurance') . ' ' . $this->currency->format($insurance) . ')';
                }

                if ($this->getConfig()->get('parcelforce_48_display_time')) {
                    $text .= ' (' . $this->language->get('text_time') . ')';
                }

                $quote_data['parcelforce_48'] = array(
                    'code'         => 'parcelforce_48.parcelforce_48',
                    'title'        => $text,
                    'cost'         => $cost,
                    'tax_class_id' => $this->getConfig()->get('parcelforce_48_tax_class_id'),
                    'text'         => $this->currency->format($this->tax->calculate($cost, $this->getConfig()->get('parcelforce_48_tax_class_id'), $this->getConfig()->get('config_tax')))
                );

                $methodData = array(
                    'code'       => 'parcelforce_48',
                    'title'      => $this->language->get('text_title'),
                    'quote'      => $quote_data,
                    'sort_order' => $this->getConfig()->get('parcelforce_48_sort_order'),
                    'error'      => false
                );
            }
        }

        return $methodData;
    }

    public function isEnabled() {
        return $this->getConfig()->get('free_status');
    }

    public function getSortOrder() {
        return $this->getConfig()->get('free_sort_order');
    }
}