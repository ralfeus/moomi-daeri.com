<?php
final class Cart extends OpenCartBase {
    private $suppliers = array();

    public function __construct($registry) {
        parent::__construct($registry);
		$this->config = $registry->get('config');
		$this->customer = $registry->get('customer');
		$this->session = $registry->get('session');
		$this->tax = $registry->get('tax');
		$this->weight = $registry->get('weight');

		if (!isset($this->session->data['cart']) || !is_array($this->session->data['cart'])) {
      		$this->session->data['cart'] = array();
    	}
        if (!isset($this->session->data['selectedCartItems']))
            $this->session->data['selectedCartItems'] = null;
	}
	      
  	public function getProducts($chosenOnes = false) {
//        $this->log->write(print_r($this->session->data['cart'], true));
		$product_data = array();
		
    	foreach ($this->session->data['cart'] as $key => $cartItem) {
            if ($chosenOnes && is_array($this->session->data['selectedCartItems']) && !in_array($key, $this->session->data['selectedCartItems']))
                continue;
      		$product = explode(':', $key);
      		$product_id = $product[0];
			$stock = true;

			// Options
      		if (isset($product[1])) {
        		$options = unserialize(base64_decode($product[1]));
      		} else {
        		$options = array();
      		}
			
      		$product_query = $this->getDb()->query("
      		    SELECT *
      		    FROM
      		        product p
      		        LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
                WHERE
                    p.product_id = '" . (int)$product_id . "'
                    AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    AND p.date_available <= '" . date('Y-m-d H:00:00') . "' AND p.status = '1'
            ");
      	  	
			if ($product_query->num_rows) {
      			$option_price = 0;
				$option_points = 0;
				$option_weight = 0;

      			$option_data = array();
      
      			foreach ($options as $product_option_id => $option_value) {
					$option_query = $this->getDb()->query("
					    SELECT po.product_option_id, po.option_id, od.name, o.type
					    FROM
					        product_option po
					        LEFT JOIN `option` o ON (po.option_id = o.option_id)
					        LEFT JOIN option_description od ON (o.option_id = od.option_id)
                        WHERE
                            po.product_option_id = '" . (int)$product_option_id . "'
                            AND po.product_id = '" . (int)$product_id . "'
                            AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'
                    ");

					if ($option_query->num_rows) {
						if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio' || $option_query->row['type'] == 'image') {
							$option_value_query = $this->getDb()->query("
							    SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix
							    FROM
							        product_option_value pov
							        LEFT JOIN option_value ov ON (pov.option_value_id = ov.option_value_id)
							        LEFT JOIN option_value_description ovd ON (ov.option_value_id = ovd.option_value_id)
                                WHERE
                                    pov.product_option_value_id = '" . (int)$option_value . "'
                                    AND pov.product_option_id = '" . (int)$product_option_id . "'
                                    AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                            ");
							//$this->log->write(print_r($option_value_query, true));
							if ($option_value_query->num_rows) {
								if ($option_value_query->row['price_prefix'] == '+') {
									$option_price += $option_value_query->row['price'];
								} elseif ($option_value_query->row['price_prefix'] == '-') {
									$option_price -= $option_value_query->row['price'];
								}

								if ($option_value_query->row['points_prefix'] == '+') {
									$option_points += $option_value_query->row['points'];
								} elseif ($option_value_query->row['points_prefix'] == '-') {
									$option_points -= $option_value_query->row['points'];
								}
															
								if ($option_value_query->row['weight_prefix'] == '+') {
									$option_weight += $option_value_query->row['weight'];
								} elseif ($option_value_query->row['weight_prefix'] == '-') {
									$option_weight -= $option_value_query->row['weight'];
								}
								
								if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cartItem->quantity))) {
									$stock = false;
								}
								
								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => $option_value,
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => $option_value_query->row['option_value_id'],
									'name'                    => $option_query->row['name'],
									'option_value'            => $option_value_query->row['name'],
									'type'                    => $option_query->row['type'],
									'quantity'                => $option_value_query->row['quantity'],
									'subtract'                => $option_value_query->row['subtract'],
									'price'                   => $option_value_query->row['price'],
									'price_prefix'            => $option_value_query->row['price_prefix'],
									'points'                  => $option_value_query->row['points'],
									'points_prefix'           => $option_value_query->row['points_prefix'],									
									'weight'                  => $option_value_query->row['weight'],
									'weight_prefix'           => $option_value_query->row['weight_prefix']
								);								
							}
						} elseif ($option_query->row['type'] == 'checkbox' && is_array($option_value)) {
							foreach ($option_value as $product_option_value_id) {
								$option_value_query = $this->getDb()->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM product_option_value pov LEFT JOIN option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
								
								if ($option_value_query->num_rows) {
									if ($option_value_query->row['price_prefix'] == '+') {
										$option_price += $option_value_query->row['price'];
									} elseif ($option_value_query->row['price_prefix'] == '-') {
										$option_price -= $option_value_query->row['price'];
									}

									if ($option_value_query->row['points_prefix'] == '+') {
										$option_points += $option_value_query->row['points'];
									} elseif ($option_value_query->row['points_prefix'] == '-') {
										$option_points -= $option_value_query->row['points'];
									}
																
									if ($option_value_query->row['weight_prefix'] == '+') {
										$option_weight += $option_value_query->row['weight'];
									} elseif ($option_value_query->row['weight_prefix'] == '-') {
										$option_weight -= $option_value_query->row['weight'];
									}
									
									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $cartItem->quantity))) {
										$stock = false;
									}
									
									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $product_option_value_id,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'option_value'            => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $option_value_query->row['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix']
									);								
								}
							}						
						} elseif ($option_query->row['type'] == 'text' || $option_query->row['type'] == 'textarea' || $option_query->row['type'] == 'file' || $option_query->row['type'] == 'date' || $option_query->row['type'] == 'datetime' || $option_query->row['type'] == 'time') {
							$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_option_value_id' => '',
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => '',
								'name'                    => $option_query->row['name'],
								'option_value'            => $option_value,
								'type'                    => $option_query->row['type'],
								'quantity'                => '',
								'subtract'                => '',
								'price'                   => '',
								'price_prefix'            => '',
								'points'                  => '',
								'points_prefix'           => '',								
								'weight'                  => '',
								'weight_prefix'           => ''
							);						
						}
					}
      			} 
			
				if ($this->customer->isLogged()) {
					$customer_group_id = $this->customer->getCustomerGroupId();
				} else {
					$customer_group_id = $this->config->get('config_customer_group_id');
				}
				
				$price = !empty($cartItem->price) ? $cartItem->price : $product_query->row['price'];
				
				// Product Discounts
				$discount_quantity = 0;
				
				foreach ($this->session->data['cart'] as $key_2 => $cartItem2) {
					$product_2 = explode(':', $key_2);
					
					if ($product_2[0] == $product_id) {
						$discount_quantity += is_object($cartItem2) ? $cartItem2->quantity : $cartItem2;
					}
				}
				
				$product_discount_query = $this->getDb()->query("
				    SELECT price
                    FROM product_discount
                    WHERE
                        product_id = '" . (int)$product_id . "'
                        AND customer_group_id = '" . (int)$customer_group_id . "'
                        AND quantity <= '" . (int)$discount_quantity . "'
                        AND ((date_start = '0000-00-00' OR date_start < '" . date('Y-m-d H:00:00') . "')
                        AND (date_end = '0000-00-00' OR date_end > '" . date('Y-m-d H:00:00', strtotime('+1 hour')) . "'))
                    ORDER BY quantity DESC, priority ASC, price ASC
                    LIMIT 1");
				
				if ($product_discount_query->num_rows) {
					$price = $product_discount_query->row['price'];
				}
				
				// Product Specials
				$product_special_query = $this->getDb()->query("
				    SELECT price
				    FROM product_special
				    WHERE
				        product_id = '" . (int)$product_id . "'
				        AND customer_group_id = '" . (int)$customer_group_id . "'
				        AND ((date_start = '0000-00-00' OR date_start < '" . date('Y-m-d H:00:00') . "')
				        AND (date_end = '0000-00-00' OR date_end > '" . date('Y-m-d H:00:00', strtotime('+1 hour')) . "'))
                    ORDER BY priority ASC, price ASC
                    LIMIT 1
                ");
			
				if ($product_special_query->num_rows) {
					$price = $product_special_query->row['price'];
				}						
		
				// Reward Points
				$query = $this->getDb()->query("SELECT points FROM product_reward WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$customer_group_id . "'");
				
				if ($query->num_rows) {	
					$reward = $query->row['points'];
				} else {
					$reward = 0;
				}
				
				// Downloads		
				$download_data = array();     		
				
				$download_query = $this->getDb()->query("SELECT * FROM product_to_download p2d LEFT JOIN download d ON (p2d.download_id = d.download_id) LEFT JOIN download_description dd ON (d.download_id = dd.download_id) WHERE p2d.product_id = '" . (int)$product_id . "' AND dd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
			
				foreach ($download_query->rows as $download) {
        			$download_data[] = array(
          				'download_id' => $download['download_id'],
						'name'        => $download['name'],
						'filename'    => $download['filename'],
						'mask'        => $download['mask'],
						'remaining'   => $download['remaining']
        			);
				}
				
				// Stock
				if (!$product_query->row['quantity'] || ($product_query->row['quantity'] < (is_object($cartItem) ? $cartItem->quantity : $cartItem)))
					$stock = false;

      			$product_data[$key] = array(
        			'key'             => $key,
        			'product_id'      => $product_query->row['product_id'],
        			'name'            => $product_query->row['name'],
        			'model'           => $product_query->row['model'],
					'shipping'        => $product_query->row['shipping'],
        			'image'           => $product_query->row['image'],
        			'option'          => $option_data,
					'download'        => $download_data,
        			'quantity'        => is_object($cartItem) ? $cartItem->quantity : $cartItem,
        			'minimum'         => $product_query->row['minimum'],
					'subtract'        => $product_query->row['subtract'],
					'stock'           => $stock,
        			'price'           => ($price + $option_price),
        			'total'           => ($price + $option_price) * (is_object($cartItem) ? $cartItem->quantity : $cartItem),
					'reward'          => $reward * (is_object($cartItem) ? $cartItem->quantity : $cartItem),
					'points'          => ($product_query->row['points'] + $option_points) * (is_object($cartItem) ? $cartItem->quantity : $cartItem),
					'tax_class_id'    => $product_query->row['tax_class_id'],
        			'weight'          => ($product_query->row['weight'] + $option_weight) * (is_object($cartItem) ? $cartItem->quantity : $cartItem),
        			'weight_class_id' => $product_query->row['weight_class_id'],
        			'length'          => $product_query->row['length'],
					'width'           => $product_query->row['width'],
					'height'          => $product_query->row['height'],
        			'length_class_id' => $product_query->row['length_class_id']					
      			);
			} else {
				$this->remove($key);
			}
    	}
//        $this->log->write(print_r($product_data, true));
		return $product_data;
  	}
		  
  	public function add($product_id, $price = 0, $qty = 1, $options = array()) {
    	if (!$options)
      		$key = (int)$product_id;
    	else
      		$key = (int)$product_id . ':' . base64_encode(serialize($options));

		if ((int)$qty && ((int)$qty > 0))
    		if (empty($this->session->data['cart'][$key]))
            {
                $this->session->data['cart'][$key] = new stdClass();
                $this->session->data['cart'][$key]->price = $this->currency->convert(
                    (double)$price, $this->customer->getBaseCurrency()->getCode(), $this->config->get('config_currency'));
      			$this->session->data['cart'][$key]->quantity = (int)$qty;
//                $this->session->data['cart'][$key]->selected = true;
            }
    		else
      			$this->session->data['cart'][$key]->quantity += (int)$qty;
//        $this->log->write(print_r($this->session->data['cart'], true));
  	}

  	public function update($key, $qty) {
    	if ((int)$qty && ((int)$qty > 0)) {
      		$this->session->data['cart'][$key]->quantity = (int)$qty;
    	} else {
	  		$this->remove($key);
		}
  	}

  	public function remove($key) {
		if (isset($this->session->data['cart'][$key])) {
     		unset($this->session->data['cart'][$key]);
  		}
	}
	
  	public function clear($chosenOnes = false) {
        if (!$chosenOnes || !is_array($this->session->data['selectedCartItems']))
		    $this->session->data['cart'] = array();
        else
        {
            $newCart = array();
            foreach ($this->session->data['cart'] as $key => $cartItem)
                if (!in_array($key, $this->session->data['selectedCartItems']))
                    $newCart[$key] = $cartItem;
            $this->session->data['cart'] = $newCart;
            $this->session->data['selectedCartItems'] = null;
        }
  	}
	
  	public function getWeight($chosenOnes = false) {
		$weight = 0;
	
    	foreach ($this->getProducts($chosenOnes) as $product) {
			if ($product['shipping']) {
      			$weight += $this->weight->convert($product['weight'], $product['weight_class_id'], $this->config->get('config_weight_class_id'));
			}
		}
	
		return $weight;
	}
	
  	public function getSubTotal($chosenOnes = false) {
		$total = 0;
		
		foreach ($this->getProducts($chosenOnes) as $product) {
			$total += $product['total'];
		}

		return $total;
  	}
	
	public function getTaxes($chosenOnes = false) {
		$tax_data = array();
		
		foreach ($this->getProducts($chosenOnes) as $product) {
			if ($product['tax_class_id']) {
				$tax_rates = $this->tax->getRates($product['total'], $product['tax_class_id']);
				
				foreach ($tax_rates as $tax_rate) {
					if (!isset($tax_data[$tax_rate['tax_rate_id']])) {
						$tax_data[$tax_rate['tax_rate_id']] = $tax_rate['amount'];
					} else {
						$tax_data[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
					}
				}
			}
		}
		
		return $tax_data;
  	}

  	public function getTotal($chosenOnes = false) {
		$total = 0;
		
		foreach ($this->getProducts($chosenOnes) as $product) {
			$total += $this->tax->calculate($product['total'], $product['tax_class_id'], $this->config->get('config_tax'));
		}

		return $total;
  	}
  	
	public function getTotalRewardPoints() {
		$total = 0;
		
		foreach ($this->getProducts() as $product) {
			$total += $product['reward'];
		}

		return $total;
  	}
	  	
  	public function countProducts($chosenOnes = false) {
		$product_total = 0;
			
		$products = $this->getProducts($chosenOnes);
			
		foreach ($products as $product) {
			$product_total += $product['quantity'];
		}		
					
		return $product_total;
	}
	  
  	public function hasProducts() {
    	return count($this->session->data['cart']);
  	}
  
  	public function hasStock() {
		$stock = true;
		
		foreach ($this->getProducts() as $product) {
			if (!$product['stock']) {
	    		$stock = false;
			}
		}
		
    	return $stock;
  	}
  
  	public function hasShipping() {
		$shipping = false;
		
		foreach ($this->getProducts() as $product) {
	  		if ($product['shipping']) {
	    		$shipping = true;
				
				break;
	  		}		
		}
		
		return $shipping;
	}
	
  	public function hasDownload() {
		$download = false;
		
		foreach ($this->getProducts() as $product) {
	  		if ($product['download']) {
	    		$download = true;
				
				break;
	  		}		
		}
		
		return $download;
	}
}
?>