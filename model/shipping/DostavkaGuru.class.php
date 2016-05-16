<?php
namespace  model\shipping;
use model\setting\SettingsDAO;
use system\helper\WebClient;

class DostavkaGuru extends ShippingMethodBase {
    private $intermediateZoneId = 26; // EMS - сборная самовывоз
    private $intermediateShippingMethod = 'ems';
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
        $rates = explode(',', SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'intermediateZoneRate'));
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
        $this->getLoader()->language('shipping/dostavkaGuru');

        $methodData = array();
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
                $methodData['dostavkaGuru_' . $issuePoint->id] = array(
                    'issuePoint' => $issuePoint,
                    'code'         => 'dostavkaGuru.dostavkaGuru_' . $issuePoint->id,
                    'description'  => $geoZone['description'],
//						'title'        => $query->row['name'] . '  (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->getConfig()->get('config_weight_class_id')) . ')',
                    'shippingMethodName' => $this->getName() . ' ' . $issuePoint->address,
                    'tax_class_id' => SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruTaxClassId')
                );
            }
        }

        return $methodData;
    }

    public function getName() {
        return $this->getNameByResource('shipping/dostavkaGuru');
    }

    public function getQuote($address) {
        $methodData = $this->getMethodData($address);
        foreach ($methodData as $key => $value) {
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
            $russiaCost = $this->getRussiaShippingCost($value['issuePoint'], $weight);
            $textRussiaCost = "<br />" . $this->getLanguage()->get('RUSSIA_DELIVERY_COST') . ": $russiaCost RUR";
            $methodData[$key]['cost'] = $cost;
            $methodData[$key]['text'] = $this->currency->format(
                $this->tax->calculate($cost, SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruTaxClassId'), $this->getConfig()->get('config_tax')));
            $methodData[$key]['title'] = $value['issuePoint']->address . ' ' .
                $this->weight->format($weight, $this->getConfig()->get('config_weight_class_id')) .
                $textRussiaCost;
        }
        $quoteData = array();

        if ($methodData) {
            $quoteData = array(
                'code'       => 'dostavkaGuru',
                'title'      => $this->language->get('text_title'),
                'quote'      => $methodData,
                'sort_order' => $this->getConfig()->get('dostavkaGuruSortOrder'),
                'error'      => false
            );
        }

        return $quoteData;
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
        return boolval($this->getConfig()->get('dostavkaGuruStatus'));
    }

    public function getSortOrder() {
        return SettingsDAO::getInstance()->getSetting('dostavkaGuru', 'dostavkaGuruSortOrder');
    }
}

