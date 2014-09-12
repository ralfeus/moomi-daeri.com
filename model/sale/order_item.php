<?php
class ModelSaleOrderItem extends Model {
    private $orderItemsFromQuery = "
        order_product as op
        JOIN `order` as o on o.order_id = op.order_id
        JOIN product as p on op.product_id  = p.product_id
        LEFT JOIN supplier as s on p.supplier_id = s.supplier_id
        LEFT JOIN customer as c on o.customer_id = c.customer_id
        JOIN (
            SELECT order_item_id, MAX(date_added) as date_last_status_set
            FROM order_item_history
            GROUP BY order_item_id
        ) AS oils on op.order_product_id = oils.order_item_id
        JOIN (
            SELECT order_item_id, MIN(date_added) AS date_first_status_set
            FROM order_item_history
            GROUP BY order_item_id
        ) AS oifs ON op.order_product_id = oifs.order_item_id
    ";

	private function fetchOrderItemsCount($filter = "")	{
		$query = "
			SELECT COUNT(*) as total
			FROM
				" . $this->orderItemsFromQuery . "
			" . ($filter ? "WHERE $filter" : "");
		$order_item_query = $this->db->query($query);

		return $order_item_query->row['total'];
	}

    /**
     * @param \model\sale\OrderItem $orderItem
     * @return float
     */
    public function getOrderItemTotalCustomerCurrency($orderItem) {
        $result = $this->getDb()->query("
            SELECT rate
            FROM
              currency AS c
              JOIN currency_history AS ch ON c.currency_id = ch.currency_id
            WHERE c.code = '" . $orderItem->getCustomer()['base_currency_code'] . "' AND ch.date_added <= '" . $orderItem->getTimeCreated() . "'
            ORDER BY ch.date_added DESC
            LIMIT 0,1
        ");
        if ($result->num_rows) {
            return $orderItem->getTotal() * $result->row['rate'];
        } else {
            return 0;
        }
    }

	public function getOrderItemsCount($data = array(), $filter = null)	{
        $filter = empty($filter) ? $this->buildFilterString($data) : $filter;
		return $this->fetchOrderItemsCount($filter);
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
                order_option as oo
                left join product_option as po on oo.product_option_id = po.product_option_id
                left join option_description as od on po.option_id = od.option_id
                left join product_option_value as pov on oo.product_option_value_id = pov.product_option_value_id
                left join option_value_description as ovd on pov.option_value_id = ovd.option_value_id
            WHERE
                oo.order_product_id = " . $orderItemId ."
                and (od.language_id = $languageId or od.language_id is null)
                and (ovd.language_id = $languageId or ovd.language_id is null)"
        ;
        $result = $this->db->query($query);

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
            UPDATE order_product
            SET
                $field = '" . $this->db->escape($comment) . "'
            WHERE order_product_id = " . (int)$order_item_id
        ;
        $this->log->write($query);
        $this->db->query($query);
    }

    public function setOrderItemQuantity($orderItemId, $quantity) {
        $query = "
            UPDATE order_product
            SET
                quantity = " . (int)$quantity . ",
                total = price * " . (int)$quantity . "
            WHERE order_product_id = " . (int)$orderItemId
        ;
        //$this->log->write($query);
        $this->getDb()->query($query);
    }

    /**
     * @param int $order_item_id
     * @param int $order_item_status_id
     * @return bool
     */
    public function setOrderItemStatus($order_item_id, $order_item_status_id) {
        $orderItem = $this->getOrderItem($order_item_id);
        if ($orderItem->getStatusId() != $order_item_status_id) {
            $query = "
                INSERT order_item_history
                SET
                    order_item_id = " . (int)$order_item_id . ",
                    order_item_status_id = " . (int) $order_item_status_id . ",
                    date_added = NOW()
            ";

            $this->getDb()->query($query);
            $this->getDb()->query("
                UPDATE order_product
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
        UPDATE order_product
        SET total = " . (float)$amount . "
        WHERE order_product_id = " . (int)$orderItemId
      );
    }

    public function setOrderItemPrice($orderItemId, $amount)
    {
      $query = "UPDATE order_product SET price = (total - shipping)/quantity WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
    }

    public function setPrice($orderItemId, $amount)
    {
      $query = "UPDATE order_product SET price = " . (float)$amount . " WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
      $query = "UPDATE order_product SET total = (quantity*price) + shipping WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
    }

    public function setShipping($orderItemId, $amount)
    {
      $query = "UPDATE order_product SET shipping = " . (float)$amount . " WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
      $query = "UPDATE order_product SET total = (quantity*price) + shipping WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
    }
}
?>
