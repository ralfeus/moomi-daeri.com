<?php
use system\engine\Registry;
use system\library\Cache;

final class Length {
	private $lengths = array();

	/**
	 * Length constructor.
	 * @param Registry $registry
	 */
	public function __construct($registry) {
		$db = $registry->get('db');
		$config = $registry->get('config');
		/** @var Cache $cache */
		$cache = $registry->get('cache');

		$this->lengths = $cache->get('lengths.' . $config->get('config_language_id'));
		if (is_null($this->lengths)) {

			$length_class_query = $db->query("
			SELECT * 
			FROM 
				length_class AS lc 
				LEFT JOIN length_class_description AS lcd ON (lc.length_class_id = lcd.length_class_id) 
				WHERE lcd.language_id = :languageId
			", [":languageId" => $config->get('config_language_id')]
			);

			foreach ($length_class_query->rows as $result) {
				$this->lengths[$result['length_class_id']] = array(
					'length_class_id' => $result['length_class_id'],
					'title' => $result['title'],
					'unit' => $result['unit'],
					'value' => $result['value']
				);
			}
			$cache->set('lengths.' . $config->get('config_language_id'), $this->lengths);
		}
	}
	  
  	public function convert($value, $from, $to) {
		if ($from == $to) {
      		return $value;
		}
		
		if (isset($this->lengths[$from])) {
			$from = $this->lengths[$from]['value'];
		} else {
			$from = 0;
		}
		
		if (isset($this->lengths[$to])) {
			$to = $this->lengths[$to]['value'];
		} else {
			$to = 0;
		}		
		
      	return $value * ($to / $from);
  	}

	public function format($value, $length_class_id, $decimal_point = '.', $thousand_point = ',') {
		if (isset($this->lengths[$length_class_id])) {
    		return number_format($value, 2, $decimal_point, $thousand_point) . $this->lengths[$length_class_id]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}
}