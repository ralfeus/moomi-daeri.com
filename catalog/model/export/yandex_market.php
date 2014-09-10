<?php
class ModelExportYandexMarket extends Model {
	public function getCategory() {
		$query = $this->db->query("SELECT cd.name, c.category_id, c.parent_id FROM category c LEFT JOIN category_description cd ON (c.category_id = cd.category_id) LEFT JOIN category_to_store c2s ON (c.category_id = c2s.category_id) WHERE cd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND c2s.store_id = '" . (int)$this->config->get('config_store_id') . "'  AND c.status = '1' AND c.sort_order <> '-1'");

		return $query->rows;
	}

	public function getProduct($allowed_categories, $out_of_stock_id, $vendor_required = true) {
		$query = $this->db->query("
		    SELECT p.*, pd.name, pd.description, m.name AS manufacturer, p2c.category_id, IFNULL(ps.price, p.price) AS price
		    FROM
		        product p
		        JOIN product_to_category AS p2c ON (p.product_id = p2c.product_id)
		        " . ($vendor_required ? '' : 'LEFT ') . "JOIN manufacturer m ON (p.manufacturer_id = m.manufacturer_id)
		        LEFT JOIN product_description pd ON (p.product_id = pd.product_id)
		        LEFT JOIN product_to_store p2s ON (p.product_id = p2s.product_id)
		        LEFT JOIN product_special ps ON
		            (p.product_id = ps.product_id)
		            AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'
		            AND ps.date_start < '" . date('Y-m-d H:00:00') . "'
		            AND (ps.date_end = '0000-00-00' OR ps.date_end > '" . date('Y-m-d H:00:00', strtotime('+1 hour')) . "')
            WHERE
                p2c.category_id IN (" . $this->db->escape($allowed_categories) . ")
                AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'
                AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'
                AND p.date_available <= '" . date('Y-m-d H:00:00') . "'
                AND p.status = '1'
                AND (p.quantity > '0' OR p.stock_status_id != '" . (int)$out_of_stock_id . "')
            GROUP BY p.product_id
        ");

		return $query->rows;
	}
}
?>
