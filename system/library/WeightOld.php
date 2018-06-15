<?php
namespace system\library;
use system\engine\Registry;

final class WeightOld {
	private $weights = array();

	/**
	 * Weight constructor.
	 * @param Registry $registry
	 */
	public function __construct($registry) {
		$db = $registry->get('db');
		$config = $registry->get('config');
		/** @var Cache $cache */
		$cache = $registry->get('cache');
		
		$this->weights = $cache->get('weights.' . $config->get('config_language_id'));
		if (is_null($this->weights)) {
			$weight_class_query = $db->query("
			SELECT * 
			FROM 
				weight_class AS wc 
				LEFT JOIN weight_class_description AS wcd ON (wc.weight_class_id = wcd.weight_class_id) 
			WHERE wcd.language_id = :languageId
			", [':languageId' => $config->get('config_language_id')]
			);

			foreach ($weight_class_query->rows as $result) {
				$this->weights[$result['weight_class_id']] = array(
					'weight_class_id' => $result['weight_class_id'],
					'title' => $result['title'],
					'unit' => $result['unit'],
					'value' => $result['value']
				);
			}
			$cache->set('weights.' . $config->get('config_language_id'), $this->weights);
		}
  	}
	  
  	public function convert($value, $from, $to) {
		if ($from == $to) {
      		return $value;
		}
		
		if (!isset($this->weights[$from]) || !isset($this->weights[$to])) {
			return $value;
		} else {			
			$from = $this->weights[$from]['value'];
			$to = $this->weights[$to]['value'];
		
			return $value * ($to / $from);
		}
  	}

	public function format($value, $weight_class_id, $decimal_point = '.', $thousand_point = ',') {
		if (isset($this->weights[$weight_class_id])) {
    		return number_format($value, 2, $decimal_point, $thousand_point) . $this->weights[$weight_class_id]['unit'];
		} else {
			return number_format($value, 2, $decimal_point, $thousand_point);
		}
	}
	
	public function getUnit($weight_class_id) {
		if (isset($this->weights[$weight_class_id])) {
    		return $this->weights[$weight_class_id]['unit'];
		} else {
			return '';
		}
	}	
}