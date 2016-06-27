<?php
class ModelLocalisationCurrency extends Model {
	public function addCurrency($data) {
		$this->getDb()->query("
		    INSERT INTO currency
		    (title, code, symbol_left, symbol_right, decimal_place, value, status, date_modified)
		    VALUES (:title, :code, :symbolLeft, :symbolRight, :decimalPlace, :value, :status, NOW())
		    ", [
				':title' => $data['title'],
				':code' => $data['code'],
				':symbolLeft' => $data['symbol_left'],
				':symbolRight' => $data['symbol_right'],
				':decimalPlace' => $data['decimal_place'],
				':value' => $data['value'],
				':status' => $data['status']
			]
        );
//        $currencyId = $this->getDb()->getLastId();
        /// Implemented by trigger on DB side
//        $this->getDb()->query("
//            INSERT INTO currency_history
//            SET
//                date_added = NOW(),
//                currency_id = $currencyId,
//                rate = '" . $this->getDb()->escape($data['value']) . "'
//       ");

		$this->getCache()->delete('currency');
	}
	
	public function editCurrency($currency_id, $data) {
		$this->getDb()->query("
		    UPDATE currency
            SET
                title = :title,
                code = :code,
                symbol_left = :symbolLeft,
                symbol_right = :symbolRight,
                decimal_place = :decimalPlace,
                value = :value,
                status = :status,
                date_modified = NOW()
            WHERE currency_id = :currencyId
			", [
				':title' => $data['title'],
				':code' => $data['code'],
				':symbolLeft' => $data['symbol_left'],
				':symbolRight' => $data['symbol_right'],
				':decimalPlace' => $data['decimal_place'],
				':value' => $data['value'],
				':status' => $data['status'],
				':currencyId' => $currency_id
			]
        );
        /// Implemented by trigger on DB side
//        $this->getDb()->query("
//            INSERT INTO currency_history
//            SET
//                date_added = NOW(),
//                currency_id = " . (int)$currency_id . ",
//                rate = '" . $this->getDb()->escape($data['value']) . "'
//       ");

		$this->getCache()->delete('currency');
	}
	
	public function deleteCurrency($currency_id) {
		$this->getDb()->query("DELETE FROM currency WHERE currency_id = :currencyId", [ ':currencyId' => $currency_id ]);
	
		$this->getCache()->delete('currency');
	}

	public function getCurrency($currency_id) {
		$query = $this->getDb()->query("SELECT DISTINCT * FROM currency WHERE currency_id = :currencyId", [ ':currencyId' => $currency_id ]);
	
		return $query->row;
	}
	
	public function getCurrencyByCode($currency) {
		$query = $this->getDb()->query("SELECT DISTINCT * FROM currency WHERE code = :code", [ ':code' => $currency ]);
	
		return $query->row;
	}
		
	public function getCurrencies($data = array()) {
		if ($data) {
			$sql = "SELECT * FROM currency";

			$sort_data = array(
				'title',
				'code',
				'value',
				'date_modified'
			);	
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY title";	
			}
			
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
			
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}				

				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}	
			
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
			
			$query = $this->getDb()->query($sql);
	
			return $query->rows;
		} else {
			$currency_data = $this->getCache()->get('currency');

			if (!$currency_data) {
				$currency_data = array();
				
				$query = $this->getDb()->query("SELECT * FROM currency ORDER BY title ASC");
	
				foreach ($query->rows as $result) {
      				$currency_data[$result['code']] = array(
        				'currency_id'   => $result['currency_id'],
        				'title'         => $result['title'],
        				'code'          => $result['code'],
						'symbol_left'   => $result['symbol_left'],
						'symbol_right'  => $result['symbol_right'],
						'decimal_place' => $result['decimal_place'],
						'value'         => $result['value'],
						'status'        => $result['status'],
						'date_modified' => $result['date_modified']
      				);
    			}	
			
				$this->getCache()->set('currency', $currency_data);
			}
			
			return $currency_data;			
		}
	}	

/*	public function updateCurrencies() {
		if (extension_loaded('curl')) {
			$data = array();
			
			$query = $this->getDb()->query("SELECT * FROM currency WHERE code != '" . $this->getDb()->escape($this->config->get('config_currency')) . "' AND date_modified < '" .  $this->getDb()->escape(date('Y-m-d H:i:s', strtotime('-1 day'))) . "'");

			foreach ($query->rows as $result) {
				$data[] = $this->config->get('config_currency') . $result['code'] . '=X';
			}	
			
			$curl = curl_init();
			
			curl_setopt($curl, CURLOPT_URL, 'http://download.finance.yahoo.com/d/quotes.csv?s=' . implode(',', $data) . '&f=sl1&e=.csv');
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			
			$content = curl_exec($curl);
			
			curl_close($curl);
			
			$lines = explode("\n", trim($content));
				
			foreach ($lines as $line) {
				$currencyCode = utf8_substr($line, 4, 3);
				$value = utf8_substr($line, 11, 6);
				
				if ((float)$value)
                {
					$currency = $this->getCurrencyByCode($currencyCode);
                    $currency['value'] = $value;
                    $this->editCurrency($currency['currency_id'], $currency);
				}
			}

			$this->getCache()->delete('currency');
		}
	}
*/
	/**
	 * @param bool $force
	 * @throws CacheNotInstalledException
	 * @return void
	 */
	public function updateCurrencies($force = false) {
		if (extension_loaded('curl')) {
			$currencies = array();
			
			if ($force) {
				$query = $this->getDb()->query("
					SELECT * 
					FROM currency 
					WHERE code != :code
					", [ ':code' => $this->getConfig()->get('config_currency') ]);
			} else {
				$query = $this->getDb()->query("
					SELECT * 
					FROM currency 
					WHERE code != :code AND date_modified < :dateModified
					", [
						':code' => $this->getConfig()->get('config_currency'),
						':dateModified' => date('Y-m-d H:i:s', strtotime('-1 day'))
					]
				);
			}
			
			foreach ($query->rows as $result) {
				$currencies[$result['code']] = $result['value'];
			}	

			if ($currencies) {

//				$xml_data = $this->getCache()->get('currencies');

//				if (empty($xml_data)) {
				
					$curl = curl_init();
					
					curl_setopt($curl, CURLOPT_URL, 'http://www.cbr.ru/scripts/XML_daily.asp');
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
					
					$content = curl_exec($curl);

					curl_close($curl);

					if ($content) {

						$xml_data = json_decode(
							json_encode(	
								(array)simplexml_load_string($content)
							),	true
						);

						file_put_contents(
							DIR_CACHE . 'cache.currencies.' . strtotime('tomorrow'), 
							serialize($xml_data)
						);

					} else {
						$this->getLogger()->write('Automatic currency update failed: Link is broken or empty data feed!');
						return;
					}

//				}
				
				$date_modified = date('Y-m-d H:i:s');
					
				if ( isset($xml_data['@attributes']) && isset($xml_data['@attributes']['Date']) ) {
					$date_modified = date('Y-m-d H:i:s', strtotime($xml_data['@attributes']['Date']) );
				} 
					
				if ( isset($xml_data['Valute']) ) {
					$base_currency = 'RUB';
//					$default_currency = $this->getDb()->escape($this->config->get('config_currency'));
					$default_currency = 'KRW';  // default currency in store KRW
					$def_val = 0; $def_nom = 0;
					
					foreach ($xml_data['Valute'] as $Valute) {
						if ($Valute['CharCode'] == $default_currency) { 

							$def_val = floatval(
								str_replace(',', '.', $Valute['Value'])
							);
							$def_nom = floatval(
								str_replace(',', '.', $Valute['Nominal'])
							);
						}
					}

					foreach ($xml_data['Valute'] as $Valute) {

						if ($currencies[$Valute['CharCode']] <> $base_currency) { 
							if ($Valute['CharCode'] <> $default_currency) { 
								if ( isset($currencies[$Valute['CharCode']]) && isset($Valute['Value'])) {
									$valt = floatval(
										str_replace(',', '.', $Valute['Value'])
									);
									$nomt = floatval(
										str_replace(',', '.', $Valute['Nominal'])
									);
									$value = ($def_val*$nomt*1.05)/($def_nom*$valt);
									if ($value) {
										$this->getDb()->query("UPDATE currency SET value = '" . $value . "', date_modified = '" .  $this->getDb()->escape($date_modified) . "' WHERE code = '" . $this->getDb()->escape($Valute['CharCode']) . "'");
									}
									unset($currencies[$Valute['CharCode']]);
								} 
							}
						}							
						if (empty($currencies)) {
							break;
						}
					}
					
					$value_rur = $def_val*1.05/$def_nom;

					$this->getDb()->query("
						UPDATE currency 
						SET 
							value = :valueRUR,
							date_modified = :dateModified
						WHERE code = 'RUB'
						", [ ':valueRUR' => $value_rur, ':dateModified' => $date_modified ]
					);

					$this->getDb()->query("
						UPDATE currency 
						SET 
							value = 1, 
							date_modified = :dateModified 
						WHERE code = :code
						", [ ':dateModified' => $date_modified, ':code' => $this->getConfig()->get('config_currency')]
					);
				} else {
					$this->getLogger()->write('Automatic currency update failed: Unable to parse data feed!');
				}
			}
		}
	}
	
	public function getTotalCurrencies() {
		$query = $this->getDb()->query("SELECT COUNT(*) AS total FROM currency");
		
		return $query->row['total'];
	}
}