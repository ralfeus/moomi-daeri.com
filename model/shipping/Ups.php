<?php
namespace  model\shipping;
use DOMDocument;

class Ups extends ShippingMethodBase {
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

    public function getMethodData($address) {
//        $this->log->write(print_r($address, true));
        $sql = "
            SELECT gz.geo_zone_id, gz.name, gz.description
            FROM
                setting AS s
                JOIN geo_zone AS gz ON `key` = concat('ups_', gz.geo_zone_id, '_status') AND value = 1
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
                $row['code'] = 'ups.ups_' . $row['geo_zone_id'];
                $row['shippingMethodName'] = 'UPS Shipping ' . $row['name'];
                $result[] = $row;
            }
            return $result;
        }
        else
            return null;
    }

    public function getName()
    {
        return $this->getNameByResource('shipping/ups');
    }

	function getQuote($address) {
		$this->getLoader()->language('shipping/ups');

		$query = $this->getDb()->query("
            SELECT *
            FROM zone_to_geo_zone
            WHERE
              geo_zone_id = :geoZoneId
              AND country_id = :countryId
              AND zone_id IN (0, :zoneId)
            ", [
			':geoZoneId' => $this->getConfig()->get('ups_geo_zone_id'),
			':countryId' => $address['country_id'],
			':zoneId' => $address['zone_id']
		]);

		if (!$this->getConfig()->get('ups_geo_zone_id')) {
			$status = true;
		} else {
			$status = boolval($query->num_rows);
		}

		$methodData = array();

		if ($status) {
			$weight = $this->weight->convert($this->cart->getWeight(), $this->getConfig()->get('config_weight_class_id'), $this->getConfig()->get('ups_weight_class_id'));

			$weight = ($weight < 0.1 ? 0.1 : $weight);

			$length = $this->length->convert($this->getConfig()->get('ups_length'), $this->getConfig()->get('config_length_class_id'), $this->getConfig()->get('ups_length_class_id'));
			$width = $this->length->convert($this->getConfig()->get('ups_width'), $this->getConfig()->get('config_length_class_id'), $this->getConfig()->get('ups_length_class_id'));
			$height = $this->length->convert($this->getConfig()->get('ups_height'), $this->getConfig()->get('config_length_class_id'), $this->getConfig()->get('ups_length_class_id'));

			$service_code = array(
				// US Origin
				'US' => array(
					'01' => $this->language->get('text_us_origin_01'),
					'02' => $this->language->get('text_us_origin_02'),
					'03' => $this->language->get('text_us_origin_03'),
					'07' => $this->language->get('text_us_origin_07'),
					'08' => $this->language->get('text_us_origin_08'),
					'11' => $this->language->get('text_us_origin_11'),
					'12' => $this->language->get('text_us_origin_12'),
					'13' => $this->language->get('text_us_origin_13'),
					'14' => $this->language->get('text_us_origin_14'),
					'54' => $this->language->get('text_us_origin_54'),
					'59' => $this->language->get('text_us_origin_59'),
					'65' => $this->language->get('text_us_origin_65')
				),
				// Canada Origin
				'CA' => array(
					'01' => $this->language->get('text_ca_origin_01'),
					'02' => $this->language->get('text_ca_origin_02'),
					'07' => $this->language->get('text_ca_origin_07'),
					'08' => $this->language->get('text_ca_origin_08'),
					'11' => $this->language->get('text_ca_origin_11'),
					'12' => $this->language->get('text_ca_origin_12'),
					'13' => $this->language->get('text_ca_origin_13'),
					'14' => $this->language->get('text_ca_origin_14'),
					'54' => $this->language->get('text_ca_origin_54'),
					'65' => $this->language->get('text_ca_origin_65')
				),
				// European Union Origin
				'EU' => array(
					'07' => $this->language->get('text_eu_origin_07'),
					'08' => $this->language->get('text_eu_origin_08'),
					'11' => $this->language->get('text_eu_origin_11'),
					'54' => $this->language->get('text_eu_origin_54'),
					'65' => $this->language->get('text_eu_origin_65'),
					// next five services Poland domestic only
					'82' => $this->language->get('text_eu_origin_82'),
					'83' => $this->language->get('text_eu_origin_83'),
					'84' => $this->language->get('text_eu_origin_84'),
					'85' => $this->language->get('text_eu_origin_85'),
					'86' => $this->language->get('text_eu_origin_86')
				),
				// Puerto Rico Origin
				'PR' => array(
					'01' => $this->language->get('text_pr_origin_01'),
					'02' => $this->language->get('text_pr_origin_02'),
					'03' => $this->language->get('text_pr_origin_03'),
					'07' => $this->language->get('text_pr_origin_07'),
					'08' => $this->language->get('text_pr_origin_08'),
					'14' => $this->language->get('text_pr_origin_14'),
					'54' => $this->language->get('text_pr_origin_54'),
					'65' => $this->language->get('text_pr_origin_65')
				),
				// Mexico Origin
				'MX' => array(
					'07' => $this->language->get('text_mx_origin_07'),
					'08' => $this->language->get('text_mx_origin_08'),
					'54' => $this->language->get('text_mx_origin_54'),
					'65' => $this->language->get('text_mx_origin_65')
				),
				// All other origins
				'other' => array(
					// service code 7 seems to be gone after January 2, 2007
					'07' => $this->language->get('text_other_origin_07'),
					'08' => $this->language->get('text_other_origin_08'),
					'11' => $this->language->get('text_other_origin_11'),
					'54' => $this->language->get('text_other_origin_54'),
					'65' => $this->language->get('text_other_origin_65')
				)
			);

			$xml  = '<?xml version="1.0"?>';
			$xml .= '<AccessRequest xml:lang="en-US">';
			$xml .= '	<AccessLicenseNumber>' . $this->getConfig()->get('ups_key') . '</AccessLicenseNumber>';
			$xml .= '	<UserId>' . $this->getConfig()->get('ups_username') . '</UserId>';
			$xml .= '	<Password>' . $this->getConfig()->get('ups_password') . '</Password>';
			$xml .= '</AccessRequest>';
			$xml .= '<?xml version="1.0"?>';
			$xml .= '<RatingServiceSelectionRequest xml:lang="en-US">';
			$xml .= '	<Request>';
			$xml .= '		<TransactionReference>';
			$xml .= '			<CustomerContext>Bare Bones Rate Request</CustomerContext>';
			$xml .= '			<XpciVersion>1.0001</XpciVersion>';
			$xml .= '		</TransactionReference>';
			$xml .= '		<RequestAction>Rate</RequestAction>';
			$xml .= '		<RequestOption>shop</RequestOption>';
			$xml .= '	</Request>';
			$xml .= '   <PickupType>';
			$xml .= '       <Code>' . $this->getConfig()->get('ups_pickup') . '</Code>';
			$xml .= '   </PickupType>';

			if ($this->getConfig()->get('ups_country') == 'US' && $this->getConfig()->get('ups_pickup') == '11') {
				$xml .= '   <CustomerClassification>';
				$xml .= '       <Code>' . $this->getConfig()->get('ups_classification') . '</Code>';
				$xml .= '   </CustomerClassification>';
			}

			$xml .= '	<Shipment>';

			$xml .= '		<Shipper>';
			$xml .= '			<Address>';
			$xml .= '				<City>' . $this->getConfig()->get('ups_city') . '</City>';
			$xml .= '				<StateProvinceCode>'. $this->getConfig()->get('ups_state') . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . $this->getConfig()->get('ups_country') . '</CountryCode>';
			$xml .= '				<PostalCode>' . $this->getConfig()->get('ups_postcode') . '</PostalCode>';
			$xml .= '			</Address>';
			$xml .= '		</Shipper>';
			$xml .= '		<ShipTo>';
			$xml .= '			<Address>';
			$xml .= ' 				<City>' . $address['city'] . '</City>';
			$xml .= '				<StateProvinceCode>' . $address['zone_code'] . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . $address['iso_code_2'] . '</CountryCode>';
			$xml .= '				<PostalCode>' . $address['postcode'] . '</PostalCode>';

			if ($this->getConfig()->get('ups_quote_type') == 'residential') {
				$xml .= '				<ResidentialAddressIndicator />';
			}

			$xml .= '			</Address>';
			$xml .= '		</ShipTo>';
			$xml .= '		<ShipFrom>';
			$xml .= '			<Address>';
			$xml .= '				<City>' . $this->getConfig()->get('ups_city') . '</City>';
			$xml .= '				<StateProvinceCode>'. $this->getConfig()->get('ups_state') . '</StateProvinceCode>';
			$xml .= '				<CountryCode>' . $this->getConfig()->get('ups_country') . '</CountryCode>';
			$xml .= '				<PostalCode>' . $this->getConfig()->get('ups_postcode') . '</PostalCode>';
			$xml .= '			</Address>';
			$xml .= '		</ShipFrom>';

			$xml .= '		<Package>';
			$xml .= '			<PackagingType>';
			$xml .= '				<Code>' . $this->getConfig()->get('ups_packaging') . '</Code>';
			$xml .= '			</PackagingType>';

			$xml .= '		    <Dimensions>';
			$xml .= '				<UnitOfMeasurement>';
			$xml .= '					<Code>' . $this->getConfig()->get('ups_length_code') . '</Code>';
			$xml .= '				</UnitOfMeasurement>';
			$xml .= '				<Length>' . $length . '</Length>';
			$xml .= '				<Width>' . $width . '</Width>';
			$xml .= '				<Height>' . $height . '</Height>';
			$xml .= '			</Dimensions>';

			$xml .= '			<PackageWeight>';
			$xml .= '				<UnitOfMeasurement>';
			$xml .= '					<Code>' . $this->getConfig()->get('ups_weight_code') . '</Code>';
			$xml .= '				</UnitOfMeasurement>';
			$xml .= '				<Weight>' . $weight . '</Weight>';
			$xml .= '			</PackageWeight>';

			if ($this->getConfig()->get('ups_insurance')) {
				$xml .= '           <PackageServiceOptions>';
				$xml .= '               <InsuredValue>';
				$xml .= '                   <CurrencyCode>' . $this->currency->getCode() . '</CurrencyCode>';
				$xml .= '                   <MonetaryValue>' . $this->currency->format($this->cart->getTotal(), false, false, false) . '</MonetaryValue>';
				$xml .= '               </InsuredValue>';
				$xml .= '           </PackageServiceOptions>';
			}

			$xml .= '		</Package>';

			$xml .= '	</Shipment>';
			$xml .= '</RatingServiceSelectionRequest>';

			if (!$this->getConfig()->get('ups_test')) {
				$url = 'https://www.ups.com/ups.app/xml/Rate';
			} else {
				$url = 'https://wwwcie.ups.com/ups.app/xml/Rate';
			}

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

			$result = curl_exec($ch);
			curl_close($ch);

			$error_msg = '';
			$quote_data = array();

			if ($result) {
				$dom = new DOMDocument('1.0', 'UTF-8');
				$dom->loadXml($result);
				$rating_service_selection_response = $dom->getElementsByTagName('RatingServiceSelectionResponse')->item(0);
				$response = $rating_service_selection_response->getElementsByTagName('Response')->item(0);
				$response_status_code = $response->getElementsByTagName('ResponseStatusCode');

				if ($response_status_code->item(0)->nodeValue != '1') {
					$error = $response->getElementsByTagName('Error')->item(0);
					$error_msg = $error->getElementsByTagName('ErrorCode')->item(0)->nodeValue;
					$error_msg .= ': ' . $error->getElementsByTagName('ErrorDescription')->item(0)->nodeValue;
				} else {
					$rated_shipments = $rating_service_selection_response->getElementsByTagName('RatedShipment');

					foreach ($rated_shipments as $rated_shipment) {
						$service = $rated_shipment->getElementsByTagName('Service')->item(0);

						$code = $service->getElementsByTagName('Code')->item(0)->nodeValue;

						$total_charges = $rated_shipment->getElementsByTagName('TotalCharges')->item(0);

						$cost = $total_charges->getElementsByTagName('MonetaryValue')->item(0)->nodeValue;

						$currency = $total_charges->getElementsByTagName('CurrencyCode')->item(0)->nodeValue;

						if (!($code && $cost)) {
							continue;
						}

						if ($this->getConfig()->get('ups_' . strtolower($this->getConfig()->get('ups_origin')) . '_' . $code)) {
							$quote_data[$code] = array(
								'code'         => 'ups.' . $code,
								'title'        => $service_code[$this->getConfig()->get('ups_origin')][$code],
								'cost'         => $this->currency->convert($cost, $currency, $this->getConfig()->get('config_currency')),
								'tax_class_id' => $this->getConfig()->get('ups_tax_class_id'),
								'text'         => $this->currency->format($this->tax->calculate($this->currency->convert($cost, $currency, $this->currency->getCode()), $this->getConfig()->get('ups_tax_class_id'), $this->getConfig()->get('config_tax')))
							);
						}
					}
				}
			}

			$title = $this->language->get('text_title');

			if ($this->getConfig()->get('ups_display_weight')) {
				$title .= ' (' . $this->language->get('text_weight') . ' ' . $this->weight->format($weight, $this->getConfig()->get('ups_weight_class_id')) . ')';
			}

			function comparecost ($a, $b) {
				return $a['cost'] > $b['cost'];
			}
			uasort($quote_data, 'comparecost');

			$methodData = array(
				'code'       => 'ups',
				'title'      => $title,
				'quote'      => $quote_data,
				'sort_order' => $this->getConfig()->get('ups_sort_order'),
				'error'      => $error_msg
			);
		}

		return $methodData;
	}

	public function isEnabled() {
		return $this->getConfig()->get('ups_status');
	}

	public function getSortOrder() {
		return $this->getConfig()->get('ups_sort_order');
	}
}
