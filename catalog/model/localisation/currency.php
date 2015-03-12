<?php
class ModelLocalisationCurrency extends Model {
	public function getCurrencyByCode($currency) {
		$query = $this->getDb()->query("SELECT DISTINCT * FROM currency WHERE code = '" . $this->getDb()->escape($currency) . "'");
	
		return $query->row;
	}
	
	public function getCurrencies($onlyEnabled = false) {
		$currency_data = $this->cache->get('currency');

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
			
			$this->cache->set('currency', $currency_data);
		}
        if ($onlyEnabled)
        {
            $tmpCurrencies = array();
            foreach ($currency_data as $currency)
                if ($currency['status'])
                    $tmpCurrencies[] = $currency;
            $currency_data = $tmpCurrencies;
        }

		return $currency_data;
	}	
}
?>