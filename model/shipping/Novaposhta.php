<?php
namespace  model\shipping;
class Novaposhta extends ShippingMethodBase {
  	public function getCost($destination, $orderItems, $ext = array()) {
//        $this->log->write(print_r($orderItems, true));
        $cost = 0;
        $rates = explode(',', $this->getConfig()->get($destination . '_rate'));
        if (empty($ext['weight'])) {
            $totalWeight = 0;
            foreach ($orderItems as $orderItem) {
                $totalWeight +=
                    $this->weight->convert(
                        $orderItem->getWeight(),
                        $orderItem->getWeightClassId(),
                        $this->getConfig()->get('config_weight_class_id')) * $orderItem->getQuantity();
            }
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
        $this->log->write($cost);
		return $cost;
  	}

    public function getMethodData($address)
    {
//        $this->log->write(print_r($address, true));
        $sql = "
            SELECT gz.geo_zone_id, gz.name, gz.description
            FROM
                setting AS s
                JOIN geo_zone AS gz ON `key` = concat('novaposhta_', gz.geo_zone_id, '_status') AND value = 1
                JOIN zone_to_geo_zone AS ztgz ON gz.geo_zone_id = ztgz.geo_zone_id
            WHERE
                ztgz.country_id = " . (int)$address['country_id'] . "
                AND ztgz.zone_id IN (" . (int)$address['zone_id'] . ", 0)
        ";
        $this->log->write($sql);
        $query = $this->getDb()->query($sql);
        if ($query->row)
        {
            $result = array();
            foreach ($query->rows as $row)
            {
                $row['code'] = 'novaposhta.novaposhta_' . $row['geo_zone_id'];
                $row['shippingMethodName'] = 'novaposhta Shipping ' . $row['name'];
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }

    public function getName()
    {
        return $this->getNameByResource('shipping/novaposhta');
    }

    public function getQuote($address) {
		$this->getLoader()->language('shipping/novaposhta');

		$quote_data = array();

		$query = $this->getDb()->query("SELECT * FROM geo_zone ORDER BY name");

		foreach ($query->rows as $result) {
			if ($this->getConfig()->get('novaposhta_' . $result['geo_zone_id'] . '_status')) {
				$query = $this->getDb()->query("SELECT * FROM zone_to_geo_zone WHERE geo_zone_id = '" . (int)$result['geo_zone_id'] . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

				if ($query->num_rows) {
					$status = true;
				} else {
					$status = false;
				}
			} else {
				$status = false;
			}

			if ($status) {
				$cost = '';
				$weight = $this->cart->getWeight(true);

				$rates = explode(',', $this->getConfig()->get('novaposhta_' . $result['geo_zone_id'] . '_rate'));

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
					$quote_data['novaposhta_' . $result['geo_zone_id']] = array(
						'code'         => 'novaposhta.novaposhta_' . $result['geo_zone_id'],
                        'description'  => $result['description'],
						'title'        => $result['name'] . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->getConfig()->get('config_weight_class_id')) . ')',
						'cost'         => $cost,
						'tax_class_id' => $this->getConfig()->get('novaposhta_tax_class_id'),
						'text'         => $this->currency->format($this->tax->calculate($cost, $this->getConfig()->get('novaposhta_tax_class_id'), $this->getConfig()->get('config_tax')))
					);
				}
			}
		}

		$method_data = array();

		if ($quote_data) {
      		$method_data = array(
        		'code'       => 'novaposhta',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->getConfig()->get('novaposhta_sort_order'),
        		'error'      => false
      		);
		}

		return $method_data;
  	}

	public function isEnabled() {
		return $this->getConfig()->get('novaposhta_status');
	}

	public function getSortOrder() {
		return $this->getConfig()->get('novaposhta_sort_order');
	}
}
