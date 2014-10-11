<?php
class ModelSaleOrderItem extends Model
{
    private $orderItemsFromQuery = "
            order_product as op
            JOIN `order` as o on o.order_id = op.order_id
            LEFT JOIN product as p on op.product_id  = p.product_id
            LEFT JOIN supplier as s on p.supplier_id = s.supplier_id
            LEFT JOIN customer as c on o.customer_id = c.customer_id
            JOIN
                (
                    SELECT order_item_id, MAX(date_added) as date_last_status_set
                    FROM order_item_history
                    GROUP BY order_item_id
               ) as oils on op.order_product_id = oils.order_item_id";

	public function getOrderItem($order_item_id) {

		$result = $this->fetchOrderItems("op.order_product_id = $order_item_id");
        if ($result)
            return $result[0];
        else
            return false;
	}

    public function getOrderItemHistory($orderItemId)
    {
        $sql = "
            SELECT *
            FROM order_item_history JOIN statuses ON order_item_status_id = group_id * 65536 + status_id
            WHERE order_item_id = " . (int)$orderItemId . "
            ORDER BY date_added
        ";
//        $this->log->write($sql);
        $query = $this->getDb()->query($sql);
        return $query->rows;
    }

	private function fetchOrderItems($filter = "", $sort = "", $limit = "") {
		$query = "
			SELECT o.affiliate_id, at.affiliate_transaction_id ,
				op.*, op.order_product_id as order_item_id, op.status_id as status,
				concat(o.firstname, ' ', o.lastname) as customer_name, o.date_added,
				c.customer_id, c.nickname as customer_nick,
				p.product_id, p.supplier_id as supplier_id, p.image as image_path, p.weight, p.weight_class_id,
				s.name as supplier_name, s.supplier_group_id, s.internal_model as internal_model,
				oils.date_last_status_set as status_date
			FROM
				" . $this->orderItemsFromQuery . 
                " LEFT JOIN " . DB_PREFIX . "affiliate_transaction at ON op.order_product_id = at.order_product_id "
             . ($filter ? "WHERE $filter" : "") . /*"
        	" . ($sort ? "ORDER BY $sort" : "") . */"
            ORDER BY supplier_name, op.model, op.order_product_id
			" . ($limit ? "LIMIT $limit" : "");
//
//        $this->getLogger()->write($query);
		$order_item_query = $this->getDb()->query($query);

		if ($order_item_query->num_rows) {
            $this->modelOrderItem = $this->load->model('sale/order_item');

            $response = $order_item_query->rows;
            foreach ($response as $index => $row) {
                //$this->log->write("------?--------?-------- " . print_r($row['image_path'], true));
                if($row['image_path'] == '' || $row['image_path'] == "data/event/agent-moomidae.jpg") {
                    $options = $this->modelOrderItem->getOrderItemOptions($row['order_product_id']);
                    $itemUrl = !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '';
                    $response[$index]['image_path'] = !empty($itemUrl) ? $itemUrl : $row['image_path'];
                }
            }

            return $response;
        }
		else
			return false;
	}

	private function fetchOrderItemsCount($filter = "")	{
		$query = "
			SELECT COUNT(*) as total
			FROM
				" . $this->orderItemsFromQuery . "
			" . ($filter ? "WHERE $filter" : "");
		$order_item_query = $this->db->query($query);

		return $order_item_query->row['total'];
	}

	public function getOrderItems($data = array(), $filter = null) 	{

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

        if (isset($data['page'])) {
            $data['start']           = ($data['page'] - 1) * $this->config->get('config_admin_limit');
            $data['limit']           = $this->config->get('config_admin_limit');
            $limit = (int)$data['start'] . "," . (int)$data['limit'];
        } elseif (isset($data['start']) && isset($data['limit'])) {
            if ($data['start'] < 0) {
                    $data['start'] = 0;
            }
            if ($data['limit'] < 1) {
                $data['limit'] = $this->config->get('config_admin_limit');
            }
            $limit = (int)$data['start'] . "," . (int)$data['limit'];
        } else {
            $limit = null;
        }

        return $this->fetchOrderItems($filter, $sort, $limit);
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

    public function setOrderItemPrice($orderItemId, $amount)
    {
      $query = "UPDATE " . DB_PREFIX . "order_product SET price = (total - shipping)/quantity WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
    }

    public function setPrice($orderItemId, $amount)
    {
      $query = "UPDATE " . DB_PREFIX . "order_product SET price = " . (float)$amount . " WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
      $query = "UPDATE " . DB_PREFIX . "order_product SET total = (quantity*price) + shipping WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
    }

    public function setShipping($orderItemId, $amount)
    {
      $query = "UPDATE " . DB_PREFIX . "order_product SET shipping = " . (float)$amount . " WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
      $query = "UPDATE " . DB_PREFIX . "order_product SET total = (quantity*price) + shipping WHERE order_product_id = " . (int)$orderItemId;
      $this->db->query($query);
    }
}
?>
