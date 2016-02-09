<?php
namespace  model\shipping;
use model\setting\SettingsDAO;
use system\helper\WebClient;

class DostavkaGuru extends ShippingMethodBase {
	private $intermediateZoneId = 63; // EMS for Russia
	private $partnerId = 9999; // Temp ID
	private $key = '827ccb0eea8a706c4c34a16891f84e7b'; // Temp key

	public function getAddress($shippingMethodCode) {
		$pointId = explode('_', $shippingMethodCode)[1];
		$response = WebClient::getResponse(
			'http://api.dostavka.guru/client/pvz_list.php',
			'POST',
			[
				'partner_id' => $this->partnerId,
				'key' => $this->key,
				'script' => 'all_list',
				'pointID' => $pointId
			]);
		$xml = new \SimpleXMLElement($response);
		$result = [
			'address_1' => $xml->point->small_address->__toString(),
			'address_2' => '',
			'city' => $xml->point->city_name->__toString(),
			'postcode' => explode(',', $xml->point->address->__toString())[0]
		];
		return $result;
	}

	public function getCost($destination, $orderItems, $ext = array()) {
//        $this->log->write(print_r($orderItems, true));
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

	/**
	 * @param string[] $address
	 * @return IssuePoint[]
	 */
	private function getIssuePoints($address) {
		$response = WebClient::getResponse(
			'http://api.dostavka.guru/client/pvz_list.php',
			'POST',
			[
				'partner_id' => $this->partnerId,
				'key' => $this->key,
				'script' => 'all_list',
				'city_name' => $address['city']
			]);
		$xml = new \SimpleXMLElement($response);
		$result = [];
		foreach ($xml->xpath('point') as $point) {
			$issuePoint = new IssuePoint();
			$issuePoint->name = $point->name->__toString();
			$issuePoint->id = $point->code->__toString();
			$issuePoint->address = $point->address->__toString();
			$result[] = $issuePoint;
		}
		return $result;
	}

    public function getMethodData($address) {
//        $this->log->write(print_r($address, true));
        $sql = "
            SELECT gz.geo_zone_id, gz.name, gz.description
            FROM
                setting AS s
                JOIN geo_zone AS gz ON `key` = concat('dostavkaGuru_', gz.geo_zone_id, '_status') AND value = 1
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
                $row['code'] = 'dostavkaGuru.dostavkaGuru_' . $row['geo_zone_id'];
                $row['shippingMethodName'] = 'dostavkaGuru Shipping ' . $row['name'];
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }

    public function getName($languageResource = null) {
        return parent::getName('shipping/dostavkaGuru');
    }

    public function getQuote($address) {
		$this->load->language('shipping/dostavkaGuru');

		$quote_data = array();
		/// Assume the geo zone defined as intermediate exists
		$geoZone = $this->getDb()->query("
			SELECT *
			FROM geo_zone
			WHERE geo_zone_id = :intermediateZoneId
			", [
				":intermediateZoneId" => $this->intermediateZoneId
			]
		)->row;

		$issuePoints = $this->getIssuePoints($address);
		foreach ($issuePoints as $issuePoint) {
			$query = $this->getDb()->query("
				SELECT *
				FROM zone_to_geo_zone
				WHERE
					geo_zone_id = :intermediateZoneId
					AND country_id = :countryId
					AND zone_id IN (:zoneId, 0)
				", [
					':intermediateZoneId' => $this->intermediateZoneId,
					':countryId' => $address['country_id'],
					':zoneId' => $address['zone_id']
				]
			);

			if ($query->num_rows) {
				$cost = '';
				$weight = $this->cart->getWeight(true);

				$rates = explode(',', SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'intermediateZoneRate'));

				foreach ($rates as $rate) {
					$data = explode(':', $rate);

					if ($data[0] >= $weight) {
						if (isset($data[1])) {
							$cost = $data[1];
						}

						break;
					}
				}
				$russiaCost = $this->getRussiaShippingCost($issuePoint, $weight);
				$textRussiaCost = "<br />Russia delivery cost: $russiaCost RUR";

				if ((string)$cost != '') {
					$quote_data['dostavkaGuru_' . $issuePoint->id] = array(
						'code'         => 'dostavkaGuru.dostavkaGuru_' . $issuePoint->id,
                        'description'  => $geoZone['description'],
//						'title'        => $query->row['name'] . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->config->get('config_weight_class_id')) . ')',
						'title'        => $issuePoint->name . ' ' .
							$this->weight->format($weight, $this->config->get('config_weight_class_id')) .
							$textRussiaCost,
						'cost'         => $cost,
						'tax_class_id' => SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruTaxClassId'),
						'text'         => $this->currency->format($this->tax->calculate($cost, SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruTaxClassId'), $this->config->get('config_tax')))
					);
				}
			}
		}

		$method_data = array();

		if ($quote_data) {
      		$method_data = array(
        		'code'       => 'dostavkaGuru',
        		'title'      => $this->language->get('text_title'),
        		'quote'      => $quote_data,
				'sort_order' => $this->config->get('dostavkaGuruSortOrder'),
        		'error'      => false
      		);
		}

		return $method_data;
  	}

	/**
	 * @param IssuePoint $point
	 * @param float $weight
	 * @return float
	 */
	private function getRussiaShippingCost($point, $weight) {
		$response = WebClient::getResponse(
			'http://api.dostavka.guru/client/calc_guru_main_2_0.php',
			'POST',
			[
				'client' => $this->partnerId,
				'key' => $this->key,
				'method' => 'ПВЗ',
				'weight' => $weight,
//				'ocen_sum' => '0',
//				'nal_plat' => '1000',
				'point' => $point->id
			]
		);
		return preg_split('/::/', $response)[0];
	}

	public function isEnabled() {
		return $this->config->get('dostavkaGuruStatus');
	}
}

class IssuePoint {
	public $id;
	public $name;
	public $address;
}