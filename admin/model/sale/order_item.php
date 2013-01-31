<?php
class ModelSaleOrderItem extends Model
{
	public function getOrderItem($order_item_id) {
		$result = $this->fetchOrderItems("order_product_id = $order_item_id");
        if ($result)
            return $result[0];
        else
            return false;
	}

	private function fetchOrderItems($filter = "", $sort = "", $limit = "") {
		$query = "
			SELECT
				op.*, op.order_product_id as order_item_id, op.status_id as status,
				concat(o.firstname, ' ', o.lastname) as customer_name, o.date_added,
				c.customer_id, c.nickname as customer_nick,
				p.product_id, p.supplier_id as supplier_id, p.image as image_path, p.weight, p.weight_class_id,
				s.name as supplier_name, s.supplier_group_id, s.internal_model as internal_model,
				oils.date_last_status_set as status_date
			FROM
				" . DB_PREFIX . "order_product as op
				JOIN `" . DB_PREFIX . "order` as o on o.order_id = op.order_id
				JOIN " . DB_PREFIX . "product as p on op.product_id  = p.product_id
				LEFT JOIN " . DB_PREFIX . "supplier as s on p.supplier_id = s.supplier_id
				LEFT JOIN " . DB_PREFIX . "customer as c on o.customer_id = c.customer_id
				JOIN
				    (
				        SELECT order_item_id, MAX(date_added) as date_last_status_set
				        FROM order_item_history
				        GROUP BY order_item_id
                   ) as oils on op.order_product_id = oils.order_item_id
            " . ($filter ? "WHERE $filter" : "") . /*"
        	" . ($sort ? "ORDER BY $sort" : "") . */"
            ORDER BY supplier_name, op.model
			" . ($limit ? "LIMIT $limit" : "");
//		$this->log->write(print_r($query, true));
		$order_item_query = $this->db->query($query);

		if ($order_item_query->num_rows) 
			return $order_item_query->rows;
		else
			return false;
	}
	
	private function fetchOrderItemsCount($filter = "")	{
		$query = "
			SELECT COUNT(*) as total
			FROM
				" . DB_PREFIX . "order_product as op
				JOIN " . DB_DATABASE . "." . DB_PREFIX . "order AS o ON o.order_id = op.order_id
				JOIN " . DB_PREFIX . "customer AS c ON o.customer_id = c.customer_id
				JOIN " . DB_PREFIX . "product AS p ON op.product_id  = p.product_id
				LEFT JOIN " . DB_PREFIX . "supplier AS s ON p.supplier_id = s.supplier_id
				JOIN (SELECT order_item_id, order_item_status_id
                    FROM
                        (SELECT order_item_id, oih.order_item_status_id, workflow_order
                        FROM
                            " . DB_PREFIX . "order_item_history as oih
                            JOIN " . DB_PREFIX . "order_item_status as ois on oih.order_item_status_id = ois.order_item_status_id
                        ORDER BY order_item_id, workflow_order DESC) as statuses
                    GROUP BY order_item_id) as oih1 on op.order_product_id = oih1.order_item_id
			" . ($filter ? "WHERE $filter" : "");
		$order_item_query = $this->db->query($query);
		
		return $order_item_query->row['total'];
	}
	
	public function getOrderItems($data = array(), $filter = null) 	{
        //print_r($data);exit();
		$filter = empty($filter) ? $this->buildFilterString($data) : $filter;
		$sort = "";
		$limit = "";

		$sort_data = array(
            'customer_name',
			'order_id',
            'order_item_id',
			'status_date',
			'supplier_name',
            'supplier_group_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sort = $data['sort'];
		} else {
			$sort = "op.order_product_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sort .= " DESC";
		} else {
			$sort .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$limit = (int)$data['start'] . "," . (int)$data['limit'];
		}

		return $this->fetchOrderItems($filter, $sort, $limit);
	}

	public function getOrderItemsCount($data = array())	{
		return $this->fetchOrderItemsCount($this->buildFilterString($data));
	}

    public function getOrderItemsTotals($data = array())
    {
        $order_items = $this->getOrderItems($data);
        $subtotal = 0;
        $total_weight = 0;

        foreach ($order_items as $order_item)
        {
            $subtotal += $order_item['total'];
            $total_weight += $this->weight->convert($order_item['weight'], $order_item['weight_class_id'], $this->config->get('config_weight_class_id')) * $order_item['quantity'];
        }
        return
            array(
                'cost' => $subtotal,
                'weight' => $total_weight
            );
    }
	
	private function buildFilterString($data = array()) {
		$filter = "";
        if (isset($data['selected_items']) && count($data['selected_items']))
            $filter = "op.order_product_id in (" . implode(', ', $data['selected_items']) . ")";
        else
        {
            if (!empty($data['filterCustomerId']))
                $filter .= ($filter ? " AND " : "") . "c.customer_id IN (" . implode(', ', $data['filterCustomerId']) . ")";
            if (!empty($data['filterItem']))
                $filter .= ($filter ? " AND " : "") . "
                    LCASE(op.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filterItem'])) . "%'
                    OR LCASE(op.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filterItem'])) . "%'";
            if (!empty($data['filter_model']))
                $filter .= ($filter ? " AND " : "") . "LCASE(op.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";
			if (!empty($data['filterStatusId']))
				$filter .= ($filter ? " AND " : "") . "op.status_id IN (" . implode(', ', $data['filterStatusId']) . ")";
            if (!empty($data['filterSupplierId']))
                $filter .= ($filter ? " AND " : "") . "s.supplier_id IN (" . implode(', ', $data['filterSupplierId']) . ")";
//            if (!empty($data['filter_supplier_group']))
//                $filter .= ($filter ? " AND" : "") . " s.supplier_group_id = " . (int)$data['filter_supplier_group'];
            if (!empty($data['filterOrderId']))
                $filter .= ($filter ? " AND " : "") . "op.order_id = " . (int)$data['filterOrderId'];
            if (!empty($data['filterOrderItemId']))
                if (is_array($data['filterOrderItemId']))
                    $filter .= ($filter ? " AND " : "") . "op.order_product_id IN (" . implode(', ', $data['filterOrderItemId']) . ")";
                else
                    $filter .= ($filter ? " AND " : "") . "op.order_product_id = " . (int)$data['filterOrderItemId'];
            if (!empty($data['filterProductId']))
                $filter .= ($filter ? " AND " : "") . "op.product_id IN (" . implode(', ', $data['filterProductId']) . ")";
        }

		return $filter;
	}

    public function getOrderItemOptions($orderItemId)
    {
        $languageId = $this->config->get('config_language_id');
        $query = "
            SELECT
                order_product_id, oo.product_option_id, oo.product_option_value_id as value_id,
                ifnull(od.name, oo.name) as name,
                ifnull(ovd.name, oo.value) as value
            FROM
                " . DB_PREFIX . "order_option as oo
                left join " . DB_PREFIX . "product_option as po on oo.product_option_id = po.product_option_id
                left join " . DB_PREFIX . "option_description as od on po.option_id = od.option_id
                left join " . DB_PREFIX . "product_option_value as pov on oo.product_option_value_id = pov.product_option_value_id
                left join " . DB_PREFIX . "option_value_description as ovd on pov.option_value_id = ovd.option_value_id
            WHERE
                oo.order_product_id = " . $orderItemId ."
                and (od.language_id = $languageId or od.language_id is null)
                and (ovd.language_id = $languageId or ovd.language_id is null)"
        ;
        $result = $this->db->query($query);
//        $this->log->write($query);
//        $this->log->write(print_r($result->rows, true));
        $order_item_options = array();
        if ($result->num_rows)
            foreach ($result->rows as $order_option)
                $order_item_options[$order_option['product_option_id']] = $order_option;
        return $order_item_options;
    }

    public function getOrderItemOptionsString($orderItemId)
    {
        $options = '';
        foreach ($this->getOrderItemOptions($orderItemId) as $option)
            if (preg_match(URL_PATTERN, $option['value']))
                $options .= $option['name'] . ":" . '<a target="_blank" href="' . $option['value'] . '">hyperlink</a>' . "\n";
            else
                $options .= $option['name'] . ": " . $option['value'] . "\n";
        return $options;
    }

    public function setOrderItemComment($order_item_id, $comment, $isPrivate = true) {
        $this->log->write($isPrivate);
        $field = $isPrivate ? 'comment' : 'public_comment';
        $query = "
                UPDATE " . DB_PREFIX . "order_product
                SET
                    $field = '" . $this->db->escape($comment) . "'
                WHERE order_product_id = " . (int)$order_item_id
        ;
        $this->log->write($query);
        $this->db->query($query);
    }

    public function setOrderItemQuantity($orderItemId, $quantity)
    {
        $query = "
                UPDATE " . DB_PREFIX . "order_product
                SET
                    quantity = " . (int)$quantity . ",
                    total = price * " . (int)$quantity . "
                WHERE order_product_id = " . (int)$orderItemId
        ;
        //$this->log->write($query);
        $this->db->query($query);
    }
    public function setOrderItemStatus($order_item_id, $order_item_status_id)
    {
        $order_item = $this->getOrderItem($order_item_id);
        if ($order_item['status'] != $order_item_status_id)
        {
            $query = "
                INSERT " . DB_PREFIX . "order_item_history
                SET
                    order_item_id = " . (int)$order_item_id . ",
                    order_item_status_id = " . (int) $order_item_status_id . ",
                    date_added = NOW()
            ";
//            $this->log->write(print_r($query, true));
            $this->db->query($query);
            $this->db->query("
                UPDATE " . DB_PREFIX . "order_product
                SET status_id = " . (int)$order_item_status_id . "
                WHERE order_product_id = " . (int)$order_item_id
            );
            return true;
        }
        else
            return false;
    }

    public function setOrderItemTotal($orderItemId, $amount)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "order_product
            SET total = " . (float)$amount . "
            WHERE order_product_id = " . (int)$orderItemId
        );
    }
}
?>