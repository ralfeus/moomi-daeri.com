<?php
class ModelCatalogProduct extends Model {
	public function addProduct($data) {
		$this->db->query("
		    INSERT INTO " . DB_PREFIX . "product
		    SET
		        model = '" . $this->db->escape($data['model']) . "',
		        user_id = '" . (int)$data['user_id'] . "',
		        sku = '" . $this->db->escape($data['sku']) . "',
		        upc = '" . $this->db->escape($data['upc']) . "',
		        location = '" . $this->db->escape($data['location']) . "',
		        quantity = 9999,
		        minimum = '" . (int)$data['minimum'] . "',
		        subtract = '" . (int)$data['subtract'] . "',
		        stock_status_id = '" . (int)$data['stock_status_id'] . "',
		        date_available = '" . $this->db->escape($data['date_available']) . "',
		        manufacturer_id = '" . (int)$data['manufacturer_id'] . "',
		        supplier_id = '" . (int)$data['supplier_id'] . "',
		        shipping = '" . (int)$data['shipping'] . "',
		        price = '" . (float)$data['price'] . "',
		        points = '" . (int)$data['points'] . "',
		        weight = '" . (float)$data['weight'] . "',
		        weight_class_id = '" . (int)$data['weight_class_id'] . "',
		        length = '" . (float)$data['length'] . "',
		        width = '" . (float)$data['width'] . "',
		        height = '" . (float)$data['height'] . "',
		        length_class_id = '" . (int)$data['length_class_id'] . "',
		        status = '" . (int)$data['status'] . "',
		        tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "',
		        sort_order = '" . (int)$data['sort_order'] . "',
		        date_added = NOW()");
		
		$product_id = $this->db->getLastId();
		
		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
		}
		
		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}

		if (isset($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
					
					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {				
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}
	
		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
				
					$product_option_id = $this->db->getLastId();
				
					if (isset($product_option['product_option_value'])) {
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . $this->db->escape($product_option_value['option_value_id']) . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						} 
					}
				} else { 
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "'");
				}
			}
		}
		
		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}

		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
		
		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}
		
		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}
		
		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
		} elseif (isset($data['product_category'][0])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
		}

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
			}
		}

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}
		
		foreach ($data['product_tag'] as $language_id => $value) {
			if ($value) {
				$tags = explode(',', $value);
				
				foreach ($tags as $tag) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_tag SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', tag = '" . $this->db->escape(trim($tag)) . "'");
				}
			}
		}
						
		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
						
		$this->cache->delete('product');
        return $product_id;
	}

    private function buildFilterString($data = array())
    {
        $filter = "";
        if (!empty($data['filterDateAddedFrom']))
            $filter .= ($filter ? " AND" : "") . " p.date_added > '" . $this->db->escape($data['filterDateAddedFrom']) . "'";
        if (!empty($data['filterDateAddedTo']))
            $filter .= ($filter ? " AND" : "") . " p.date_added < '" . date('Y-m-d', strtotime($data['filterDateAddedTo']) + 86400) . "'";
        if (!empty($data['filterLanguageId']))
            $filter .= ($filter ? " AND" : '') . " pd.language_id = " . (int)$data['filterLanguageId'];
        if (!empty($data['filterManufacturerId']) && is_array($data['filterManufacturerId']) && sizeof($data['filterManufacturerId']))
        {
            $iDSet = array();
            $filterManufacturer = array();
            foreach ($data['filterManufacturerId'] as $manufacturerId)
                if ($manufacturerId)
                    $iDSet[] = $manufacturerId;
                else
                    $filterManufacturer['null'] = "m.manufacturer_id IS NULL";
            if (sizeof($iDSet))
                $filterManufacturer['ids'] = "m.manufacturer_id IN (" . implode(', ', $iDSet) . ")";
            $filter .= ($filter ? " AND" : "") . ' (' . implode(' OR ', $filterManufacturer) . ')';
        }
        if (!empty($data['filterUserNameId']) && is_array($data['filterUserNameId']) && sizeof($data['filterUserNameId']))
        {
            $iDSet = array();
            $filterUserName = array();
            foreach ($data['filterUserNameId'] as $usernameId)
                if ($usernameId)
                    $iDSet[] = $usernameId;
                else
                    $filterUserName['null'] = "u.user_id IS NULL";
            if (sizeof($iDSet))
                $filterUserName['ids'] = "u.user_id IN (" . implode(', ', $iDSet) . ")";
            $filter .= ($filter ? " AND" : "") . ' (' . implode(' OR ', $filterUserName) . ')';
        }
        if (!empty($data['filterModel']))
            $filter .= ($filter ? " AND" : "") . " LCASE(p.model) LIKE '" . $this->db->escape(utf8_strtolower($data['filterModel'])) . "%'";
        if (!empty($data['filterName']))
            $filter .= ($filter ? " AND" : "") . " LCASE(pd.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filterName'])) . "%'";
        if (!empty($data['filterPrice']) && is_numeric($data['filterPrice']))
            $filter .= ($filter ? " AND" : "") . " p.price LIKE '" . $this->db->escape($data['filterPrice']) . "%'";
        if (!empty($data['filterKoreanName'])) 
            $filter .= ($filter ? " AND" : "") . " a.text LIKE '%" . $this->db->escape(utf8_strtolower($data['filterKoreanName'])) . "%'";
        if (isset($data['filterStatus']))
            $filter .= ($filter ? " AND" : "") . " p.status = '" . (int)$data['filterStatus'] . "'";
        if (!empty($data['filterSupplierId']))
            $filter .= ($filter ? " AND" : "") . " s.supplier_id IN (" . implode(', ', $data['filterSupplierId']) . ")";
        if (!empty($data['filter_category_id']))
            if (!empty($data['filter_sub_category']))
            {
                $implode_data = array();
                $implode_data[] = "category_id = '" . (int)$data['filter_category_id'] . "'";
                $this->load->model('catalog/category');
                $categories = $this->model_catalog_category->getCategories($data['filter_category_id']);
                foreach ($categories as $category)
                    $implode_data[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";

                $filter .= ($filter ? " AND" : "") . " (" . implode(' OR ', $implode_data) . ")";
            }
            else
                $filter .= ($filter ? " AND" : "") . " p2c.category_id = '" . (int)$data['filter_category_id'] . "'";

        return $filter;
    }
	
	public function editProduct($product_id, $data) {
		$this->db->query("
		    UPDATE " . DB_PREFIX . "product
		    SET
		        model = '" . $this->db->escape($data['model']) . "',
		        sku = '" . $this->db->escape($data['sku']) . "',
		        upc = '" . $this->db->escape($data['upc']) . "',
		        location = '" . $this->db->escape($data['location']) . /*"',
		        quantity = '" . (int)$data['quantity'] . */"',
		        minimum = '" . (int)$data['minimum'] . "',
		        subtract = '" . (int)$data['subtract'] . "',
		        stock_status_id = '" . (int)$data['stock_status_id'] . "',
		        date_available = '" . $this->db->escape($data['date_available']) . "',
		        manufacturer_id = '" . (int)$data['manufacturer_id']  . "',
		        supplier_id = '" . (int)$data['supplier_id'] . "',
		        shipping = '" . (int)$data['shipping'] . "',
		        price = '" . (float)$data['price'] . "',
		        points = '" . (int)$data['points'] . "',
		        weight = '" . (float)$data['weight'] . "',
		        weight_class_id = '" . (int)$data['weight_class_id'] . "',
		        length = '" . (float)$data['length'] . "',
		        width = '" . (float)$data['width'] . "',
		        height = '" . (float)$data['height'] . "',
		        length_class_id = '" . (int)$data['length_class_id'] . "',
		        status = '" . (int)$data['status'] . "',
		        tax_class_id = '" . $this->db->escape($data['tax_class_id']) . "',
		        sort_order = '" . (int)$data['sort_order'] . "',
		        date_modified = NOW()
            WHERE product_id = '" . (int)$product_id . "'
        ");

		if (isset($data['image'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($data['product_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', description = '" . $this->db->escape($value['description']) . "', seo_title = '" . $this->db->escape($value['seo_title']) . "', seo_h1 = '" . $this->db->escape($value['seo_h1']) . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_store'])) {
			foreach ($data['product_store'] as $store_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
			}
		}
	
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

		if (!empty($data['product_attribute'])) {
			foreach ($data['product_attribute'] as $product_attribute) {
				if ($product_attribute['attribute_id']) {
					$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
					
					foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {				
						$this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" .  $this->db->escape($product_attribute_description['text']) . "'");
					}
				}
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_option'])) {
			foreach ($data['product_option'] as $product_option) {
				if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");
				
					$product_option_id = $this->db->getLastId();
				
					if (isset($product_option['product_option_value'])) {
						foreach ($product_option['product_option_value'] as $product_option_value) {
							$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . $this->db->escape($product_option_value['option_value_id']) . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
						}
					}
				} else { 
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value = '" . $this->db->escape($product_option['option_value']) . "', required = '" . (int)$product_option['required'] . "'");
				}					
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
 
		if (isset($data['product_discount'])) {
			foreach ($data['product_discount'] as $product_discount) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_special'])) {
			foreach ($data['product_special'] as $product_special) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_download'])) {
			foreach ($data['product_download'] as $download_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		
		if (isset($data['product_category'])) {
			foreach ($data['product_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
			}		
		}

		if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
		} elseif (isset($data['product_category'])) {
			$this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

		if (isset($data['product_related'])) {
			foreach ($data['product_related'] as $related_id) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_reward'])) {
			foreach ($data['product_reward'] as $customer_group_id => $value) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

		if (isset($data['product_layout'])) {
			foreach ($data['product_layout'] as $store_id => $layout) {
				if ($layout['layout_id']) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout['layout_id'] . "'");
				}
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_tag WHERE product_id = '" . (int)$product_id. "'");
		
		foreach ($data['product_tag'] as $language_id => $value) {
			if ($value) {
				$tags = explode(',', $value);
			
				foreach ($tags as $tag) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_tag SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', tag = '" . $this->db->escape(trim($tag)) . "'");
				}
			}
		}
						
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
		
		if ($data['keyword']) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "url_alias SET query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape($data['keyword']) . "'");
		}
						
		$this->cache->delete('product');
	}
	
	public function copyProduct($product_id) {
		$query = $this->db->query("
		    SELECT DISTINCT *
		    FROM
		        " . DB_PREFIX . "product p
		        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
            WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ");
		
		if ($query->num_rows) {
			$data = $query->row;
			$data['keyword'] = '';
			$data['status'] = '0';
						
			$data = array_merge($data, array('product_attribute' => $this->getProductAttributes($product_id)));
			$data = array_merge($data, array('product_description' => $this->getProductDescriptions($product_id)));			
			$data = array_merge($data, array('product_discount' => $this->getProductDiscounts($product_id)));
			$data = array_merge($data, array('product_image' => $this->getProductImages($product_id)));
			
			$data['product_image'] = array();
			
			$results = $this->getProductImages($product_id);
			
			foreach ($results as $result) {
				$data['product_image'][] = $result['image'];
			}
						
			$data = array_merge($data, array('product_option' => $this->getProductOptions($product_id)));
			$data = array_merge($data, array('product_related' => $this->getProductRelated($product_id)));
			$data = array_merge($data, array('product_reward' => $this->getProductRewards($product_id)));
			$data = array_merge($data, array('product_special' => $this->getProductSpecials($product_id)));
			$data = array_merge($data, array('product_tag' => $this->getProductTags($product_id)));
			$data = array_merge($data, array('product_category' => $this->getProductCategories($product_id)));
			$data = array_merge($data, array('product_download' => $this->getProductDownloads($product_id)));
			$data = array_merge($data, array('product_layout' => $this->getProductLayouts($product_id)));
			$data = array_merge($data, array('product_store' => $this->getProductStores($product_id)));
			
			$this->addProduct($data);
		}
	}
	
	public function deleteProduct($product_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_tag WHERE product_id='" . (int)$product_id. "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$product_id. "'");
		
		$this->cache->delete('product');
	}
	
	public function getProduct($product_id) {
		$query = $this->db->query("
		    SELECT DISTINCT
		        *,
		        (
                    SELECT keyword
                    FROM " . DB_PREFIX . "url_alias
                    WHERE query = 'product_id=" . (int)$product_id . "'
                ) AS keyword
            FROM
                " . DB_PREFIX . "product p
                LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
            WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
        ");
				
		return $query->row;
	}
	
	public function getProducts($data = array()) {
        if (empty($data))
            $productData = $this->cache->get('product.' . (int)$this->config->get('config_language_id'));
        if (empty($productData))
        {
            $data['filterLanguageId'] = $this->config->get('config_language_id');
			$sql = "
			    SELECT p.*, pd.*, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
			    FROM
			        " . DB_PREFIX . "product AS p
			        LEFT JOIN " . DB_PREFIX . "product_description AS pd ON (p.product_id = pd.product_id)
			        LEFT JOIN " . DB_PREFIX . "supplier AS s ON p.supplier_id = s.supplier_id
                    LEFT JOIN " . DB_PREFIX . "manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
                    LEFT JOIN " . DB_PREFIX . "product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=" . ATTRIBUTE_LINK . ")
                    LEFT JOIN " . DB_PREFIX . "product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=" . ATTRIBUTE_KOREAN_NAME . ")
                    LEFT JOIN " . DB_PREFIX . "user AS u ON p.user_id = u.user_id";
			
			if (!empty($data['filter_category_id']))
				$sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";			

			$sql .= " WHERE " . $this->buildFilterString($data);
			$sql .= " GROUP BY p.product_id";
//print_r($sql); die();						
			$sort_data = array(
				'pd.name',
				'p.model',
				'p.price',
				'p.quantity',
				'p.status',
				'p.sort_order'
			);	
			
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];	
			} else {
				$sql .= " ORDER BY pd.name";	
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

			$query = $this->db->query($sql);
            $productData = $query->rows;
            if (empty($data))
                $this->cache->set('product.' . (int)$this->config->get('config_language_id'), $productData);
        }
        return $productData;
	}
	
	public function getProductsByCategoryId($category_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");
								  
		return $query->rows;
	} 
	
	public function getProductDescriptions($product_id) {
		$product_description_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_description_data[$result['language_id']] = array(
				'seo_title'        => $result['seo_title'],
				'seo_h1'           => $result['seo_h1'],
				'name'             => $result['name'],
				'description'      => $result['description'],
				'meta_keyword'     => $result['meta_keyword'],
				'meta_description' => $result['meta_description']
			);
		}
		
		return $product_description_data;
	}

	public function getProductAttributes($product_id) {
		$product_attribute_data = array();
		
		$product_attribute_query = $this->db->query("SELECT pa.attribute_id, ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY pa.attribute_id");
		
		foreach ($product_attribute_query->rows as $product_attribute) {
			$product_attribute_description_data = array();
			
			$product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");
			
			foreach ($product_attribute_description_query->rows as $product_attribute_description) {
				$product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
			}
			
			$product_attribute_data[] = array(
				'attribute_id'                  => $product_attribute['attribute_id'],
				'name'                          => $product_attribute['name'],
				'product_attribute_description' => $product_attribute_description_data
			);
		}
		
		return $product_attribute_data;
	}

	public function getProductUserNames($data = array())
  {
  	$filter = $this->buildFilterString($data);
    $sql = "
        SELECT DISTINCT m.manufacturer_id AS manufacturer_id, p.product_id, m.name AS manufacturer_name, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
        FROM
            " . DB_PREFIX . "product AS p
            LEFT JOIN " . DB_PREFIX . "product_description AS pd ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "supplier AS s ON p.supplier_id = s.supplier_id
            LEFT JOIN " . DB_PREFIX . "manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
            LEFT JOIN " . DB_PREFIX . "product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=43)
            LEFT JOIN " . DB_PREFIX . "product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=42)
            JOIN " . DB_PREFIX . "user AS u ON p.user_id = u.user_id" .
        (!empty($data['filter_category_id']) ? " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)" : '') .
        (!empty($filter) ? " WHERE $filter" : '') .
        " GROUP BY p.product_id
    ";

//    $this->log->write($sql);
    return $this->db->query($sql)->rows;
  }

  public function getProductManufacturers($data = array())
  {
    $filter = $this->buildFilterString($data);
    $sql = "
        SELECT DISTINCT m.manufacturer_id AS manufacturer_id, p.product_id, m.name AS manufacturer_name, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
        FROM
            " . DB_PREFIX . "product AS p
            LEFT JOIN " . DB_PREFIX . "product_description AS pd ON (p.product_id = pd.product_id)
            LEFT JOIN " . DB_PREFIX . "supplier AS s ON p.supplier_id = s.supplier_id
            LEFT JOIN " . DB_PREFIX . "manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
            LEFT JOIN " . DB_PREFIX . "product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=43)
            LEFT JOIN " . DB_PREFIX . "product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=42)
            LEFT JOIN " . DB_PREFIX . "user AS u ON p.user_id = u.user_id" .
        (!empty($data['filter_category_id']) ? " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)" : '') .
        (!empty($filter) ? " WHERE $filter" : '') .
        " GROUP BY p.product_id
    ";
//    $this->log->write($sql);
    return $this->db->query($sql)->rows;
  }
	
	public function getProductOptions($product_id) {
		$product_option_data = array();
		
		$product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		
		foreach ($product_option_query->rows as $product_option) {
			if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
				$product_option_value_data = array();	
				
				$product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
				
				foreach ($product_option_value_query->rows as $product_option_value) {
					$product_option_value_data[] = array(
						'product_option_value_id' => $product_option_value['product_option_value_id'],
						'option_value_id'         => $product_option_value['option_value_id'],
						'name'                    => $product_option_value['name'],
						'image'                   => $product_option_value['image'],
						'quantity'                => $product_option_value['quantity'],
						'subtract'                => $product_option_value['subtract'],
						'price'                   => $product_option_value['price'],
						'price_prefix'            => $product_option_value['price_prefix'],
						'points'                  => $product_option_value['points'],
						'points_prefix'           => $product_option_value['points_prefix'],						
						'weight'                  => $product_option_value['weight'],
						'weight_prefix'           => $product_option_value['weight_prefix']					
					);
				}
				
				$product_option_data[] = array(
					'product_option_id'    => $product_option['product_option_id'],
					'option_id'            => $product_option['option_id'],
					'name'                 => $product_option['name'],
					'type'                 => $product_option['type'],
					'product_option_value' => $product_option_value_data,
					'required'             => $product_option['required']
				);				
			} else {
				$product_option_data[] = array(
					'product_option_id' => $product_option['product_option_id'],
					'option_id'         => $product_option['option_id'],
					'name'              => $product_option['name'],
					'type'              => $product_option['type'],
					'option_value'      => $product_option['option_value'],
					'required'          => $product_option['required']
				);				
			}
		}	
		
		return $product_option_data;
	}
	
	public function getProductImages($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
		
		return $query->rows;
	}
	
	public function getProductDiscounts($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");
		
		return $query->rows;
	}
	
	public function getProductSpecials($product_id) {
		$query = "
		    SELECT *
		    FROM " . DB_PREFIX . "product_special AS p
		    WHERE product_id = '" . (int)$product_id . "'
		    ORDER BY priority, price
        ";
//		$this->log->write($query);
		return $this->db->query($query)->rows;
	}
	
	public function getProductRewards($product_id) {
		$product_reward_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
		}
		
		return $product_reward_data;
	}
		
	public function getProductDownloads($product_id) {
		$product_download_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_download_data[] = $result['download_id'];
		}
		
		return $product_download_data;
	}

	public function getProductStores($product_id) {
		$product_store_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $result) {
			$product_store_data[] = $result['store_id'];
		}
		
		return $product_store_data;
	}

    public function getProductSuppliers($data = array())
    {
        $filter = $this->buildFilterString($data);
        $sql = "
            SELECT DISTINCT s.supplier_id AS supplier_id, s.name AS supplier_name, p.product_id, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
            FROM
                " . DB_PREFIX . "product AS p
                LEFT JOIN " . DB_PREFIX . "product_description AS pd ON (p.product_id = pd.product_id)
                LEFT JOIN " . DB_PREFIX . "supplier AS s ON p.supplier_id = s.supplier_id
                LEFT JOIN " . DB_PREFIX . "manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
                LEFT JOIN " . DB_PREFIX . "product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=43)
                    LEFT JOIN " . DB_PREFIX . "product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=42)
                    LEFT JOIN " . DB_PREFIX . "user AS u ON p.user_id = u.user_id" .
                (!empty($data['filter_category_id']) ? " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)" : '') .
            (!empty($filter) ? " WHERE $filter" : '') .
            " GROUP BY p.product_id
        ";
//        $this->log->write($sql);
        return $this->db->query($sql)->rows;
    }

	public function getProductLayouts($product_id) {
		$product_layout_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_layout_data[$result['store_id']] = $result['layout_id'];
		}
		
		return $product_layout_data;
	}
		
	public function getProductCategories($product_id) {
		$product_category_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_category_data[] = $result['category_id'];
		}

		return $product_category_data;
	}

	public function getProductMainCategoryId($product_id) {
		$query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND main_category = '1' LIMIT 1");

		return ($query->num_rows ? (int)$query->row['category_id'] : 0);
	}
	
	public function getProductRelated($product_id) {
		$product_related_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
		
		foreach ($query->rows as $result) {
			$product_related_data[] = $result['related_id'];
		}
		
		return $product_related_data;
	}
	
	public function getProductTags($product_id) {
		$product_tag_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_tag WHERE product_id = '" . (int)$product_id . "'");
		
		$tag_data = array();
		
		foreach ($query->rows as $result) {
			$tag_data[$result['language_id']][] = $result['tag'];
		}
		
		foreach ($tag_data as $language => $tags) {
			$product_tag_data[$language] = implode(',', $tags);
		}
		
		return $product_tag_data;
	}
	
	public function getTotalProducts($data = array()) {
        $data['filterLanguageId'] = $this->config->get('config_language_id');
        $filter = $this->buildFilterString($data);
		$sql = "
		    SELECT COUNT(DISTINCT p.product_id) AS total, n.text AS link, a.text AS korean_name, u.user_id, u.username AS user_name
		    FROM
		        " . DB_PREFIX . "product p
		        LEFT JOIN " . DB_PREFIX . "manufacturer AS m ON p.manufacturer_id = m.manufacturer_id
		        LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)
		        LEFT JOIN " . DB_PREFIX . "supplier AS s ON p.supplier_id = s.supplier_id
		        LEFT JOIN " . DB_PREFIX . "product_attribute AS n ON (p.product_id = n.product_id AND n.attribute_id=43)
            LEFT JOIN " . DB_PREFIX . "product_attribute AS a ON (p.product_id = a.product_id AND a.attribute_id=42)
            LEFT JOIN " . DB_PREFIX . "user AS u ON p.user_id = u.user_id" . 
                (!empty($data['filter_category_id']) ? " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)" : '') .
            (!empty($filter) ? " WHERE $filter" : '');
		 			
		$query = $this->db->query($sql);
		return $query->row['total'];
	}	
	
	public function getTotalProductsByTaxClassId($tax_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

		return $query->row['total'];
	}
		
	public function getTotalProductsByStockStatusId($stock_status_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

		return $query->row['total'];
	}
	
	public function getTotalProductsByWeightClassId($weight_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

		return $query->row['total'];
	}
	
	public function getTotalProductsByLengthClassId($length_class_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

		return $query->row['total'];
	}

	public function getTotalProductsByDownloadId($download_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalProductsByManufacturerId($manufacturer_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

		return $query->row['total'];
	}

    public function getTotalProductsBySupplierId($supplier_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE supplier_id = '" . (int)$supplier_id . "'");

        return $query->row['total'];
    }
	
	public function getTotalProductsByAttributeId($attribute_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

		return $query->row['total'];
	}	
	
	public function getTotalProductsByOptionId($option_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

		return $query->row['total'];
	}	
	
	public function getTotalProductsByLayoutId($layout_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

		return $query->row['total'];
	}

    public function changeStatusProducts($products, $status) {
        function check_int($a) {
            return (int)$a;
        }
        $arr_products = array_map('check_int', $products);
        $products = implode("' OR product_id = '", $arr_products);
        $this->db->query("UPDATE " . DB_PREFIX . "product SET status = '" . (int)(bool)$status . "' WHERE product_id = '" . $products . "'");

        $this->cache->delete('product');
    }

}
?>