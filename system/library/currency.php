<?php
final class Currency extends OpenCartBase
{
  	private $code;
  	private $currencies = array();
  
  	public function __construct($registry) {
        parent::__construct($registry);
		$this->language = $registry->get('language');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "currency");

    	foreach ($query->rows as $result) {
      		$this->currencies[$result['code']] = array(
        		'currency_id'   => $result['currency_id'],
        		'title'         => $result['title'],
        		'symbol_left'   => $result['symbol_left'],
        		'symbol_right'  => $result['symbol_right'],
        		'decimal_place' => $result['decimal_place'],
        		'value'         => $result['value']
      		); 
    	}
		
		if (isset($this->request->get['currency']) && (array_key_exists($this->request->get['currency'], $this->currencies))) {
			$this->set($this->request->get['currency']);
    	} elseif ((isset($this->session->data['currency'])) && (array_key_exists($this->session->data['currency'], $this->currencies))) {
      		$this->set($this->session->data['currency']);
    	} elseif ((isset($this->request->cookie['currency'])) && (array_key_exists($this->request->cookie['currency'], $this->currencies))) {
      		$this->set($this->request->cookie['currency']);
    	} else {
      		$this->set($this->config->get('config_currency'));
    	}
  	}

    /**
     * Sets base (default) currency
     * @param \Currency $currency
     * @return void
     */
    public function set($currency) {
    	$this->code = $currency;

    	if ((!isset($this->session->data['currency'])) || ($this->session->data['currency'] != $currency)) {
      		$this->session->data['currency'] = $currency;
    	}

    	if ((!isset($this->request->cookie['currency'])) || ($this->request->cookie['currency'] != $currency)) {
	  		setcookie('currency', $currency, time() + 60 * 60 * 24 * 30, '/', $this->request->server['HTTP_HOST']);
    	}
  	}

  	public function format($number, $currency = '', $value = '', $format = true) {
		if ($currency && $this->has($currency)) {
      		$symbol_left   = $this->currencies[$currency]['symbol_left'];
      		$symbol_right  = $this->currencies[$currency]['symbol_right'];
      		$decimal_place = $this->currencies[$currency]['decimal_place'];
    	} else {
      		$symbol_left   = $this->currencies[$this->code]['symbol_left'];
      		$symbol_right  = $this->currencies[$this->code]['symbol_right'];
      		$decimal_place = $this->currencies[$this->code]['decimal_place'];
			
			$currency = $this->code;
    	}

    	if ($value) {
      		$value = $value;
    	} else {
      		$value = $this->currencies[$currency]['value'];
    	}

    	if ($value) {
      		$value = $number * $value;
    	} else {
      		$value = $number;
    	}

    	$string = '';

    	if (($symbol_left) && ($format)) {
      		$string .= $symbol_left;
    	}

		if ($format) {
			$decimal_point = $this->language->get('decimal_point');
		} else {
			$decimal_point = '.';
		}
		
		if ($format) {
			$thousand_point = $this->language->get('thousand_point');
		} else {
			$thousand_point = '';
		}
		
    	$string .= number_format(round($value, (int)$decimal_place), (int)$decimal_place, $decimal_point, $thousand_point);

    	if (($symbol_right) && ($format)) {
      		$string .= $symbol_right;
    	}

    	return $string;
  	}
	
    public function convert($value, $from, $to, $date = null)
    {
        if ($date != null)
        {
            $query = $this->db->query("
                SELECT ch.*, c.code
                FROM
                    " . DB_PREFIX . "currency_history AS ch
                    JOIN " . DB_PREFIX . "currency AS c on c.currency_id = ch.currency_id
                    JOIN
                    (
                        SELECT currency_id, max(date_added) AS last_date_added
                        FROM " . DB_PREFIX . "currency_history AS ch1
                        WHERE date_added <= '" . $this->db->escape($date) . "'
                        GROUP BY currency_id
                    ) AS lrm ON lrm.currency_id = ch.currency_id AND lrm.last_date_added = ch.date_added
                WHERE c.code in ('" . $this->db->escape($from) . "', '" . $this->db->escape($to) . "')
            ");
            if ($query->rows[0]['code'] == $from)
            {
                $fromValue = $query->rows[0]['rate'];
                $toValue = $query->rows[1]['rate'];
            }
            else
            {
                $fromValue = $query->rows[1]['rate'];
                $toValue = $query->rows[0]['rate'];
            }
        }
        else
        {
            if (isset($this->currencies[$from]))
                $fromValue = $this->currencies[$from]['value'];
            else
                $fromValue = 0;

            if (isset($this->currencies[$to]))
                $toValue = $this->currencies[$to]['value'];
            else
                $toValue = 0;
        }
		
		return round(
            $value * ($toValue / $fromValue),
            $this->getDecimalPlace($to) ? $this->getDecimalPlace($to) : 2);
  	}
	
  	public function getId($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['currency_id'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['currency_id'];
		} else {
			return 0;
		}
  	}

    public function getName($currency = '')
    {
        if (!$currency)
            return $this->currencies[$this->code]['title'];
        elseif (isset($this->currencies[$currency]))
            return $this->currencies[$currency]['title'];
        else
            return '';
    }
	
	public function getSymbolLeft($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['symbol_left'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['symbol_left'];
		} else {
			return '';
		}
  	}
	
	public function getSymbolRight($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['symbol_right'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['symbol_right'];
		} else {
			return '';
		}
  	}
	
	public function getDecimalPlace($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['decimal_place'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['decimal_place'];
		} else {
			return 0;
		}
  	}
	
  	public function getCode() {
    	return $this->code;
  	}
  
  	public function getValue($currency = '') {
		if (!$currency) {
			return $this->currencies[$this->code]['value'];
		} elseif ($currency && isset($this->currencies[$currency])) {
			return $this->currencies[$currency]['value'];
		} else {
			return 0;
		}
  	}
    
  	public function has($currency) {
    	return isset($this->currencies[$currency]);
  	}
}
?>