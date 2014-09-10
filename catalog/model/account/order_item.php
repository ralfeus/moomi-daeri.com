<?php
class ModelAccountOrderItem extends Model
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
				c.nickname as customer_nick,
				p.product_id, p.supplier_id as supplier_id, p.image as image_path, p.weight, p.weight_class_id,
				s.name as supplier_name, s.supplier_group_id, s.internal_model as internal_model,
				oils.date_last_status_set as status_date
			FROM
				order_product as op
				JOIN `order` as o on o.order_id = op.order_id
				JOIN product as p on op.product_id  = p.product_id
				LEFT JOIN supplier as s on p.supplier_id = s.supplier_id
				LEFT JOIN customer as c on o.customer_id = c.customer_id
				JOIN
				    (
				        SELECT order_item_id, MAX(date_added) as date_last_status_set
				        FROM order_item_history
				        GROUP BY order_item_id
                   ) as oils on op.order_product_id = oils.order_item_id
            " . ($filter ? "WHERE $filter" : "") . "
			" . ($sort ? "ORDER BY $sort" : "") . "
			" . ($limit ? "LIMIT $limit" : "");
		//$this->log->write(print_r($query, true));
		$order_item_query = $this->db->query($query);

		if ($order_item_query->num_rows)
			return $order_item_query->rows;
		else
			return array();
	}

	private function fetchOrderItemsCount($filter = "")	{
		$query = "
			SELECT COUNT(*) as total
			FROM
				order_product as op join " . DB_DATABASE . ".order as o on o.order_id = op.order_id
				join customer as c on o.customer_id = c.customer_id
				join product as p on op.product_id  = p.product_id
				left join supplier as s on p.supplier_id = s.supplier_id
				JOIN (SELECT order_item_id, order_item_status_id
                    FROM
                        (SELECT order_item_id, oih.order_item_status_id, workflow_order
                        FROM
                            order_item_history as oih
                            JOIN order_item_status as ois on oih.order_item_status_id = ois.order_item_status_id
                        ORDER BY order_item_id, workflow_order DESC) as statuses
                    GROUP BY order_item_id) as oih1 on op.order_product_id = oih1.order_item_id
			" . ($filter ? "WHERE $filter" : "");
		$order_item_query = $this->db->query($query);

		return $order_item_query->row['total'];
	}

	public function getOrderItems($data = array()) 	{
        //print_r($data); die();
		$filter = $this->buildFilterString($data);
		$sort = "";
		$limit = "";

		$sort_data = array(
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
		$filter = "o.customer_id = " . $this->customer->getId();
        if (isset($data['selected_items']) && count($data['selected_items']))
            $filter = "op.order_product_id in (" . implode($data['selected_items'], ',') . ")";
        else
        {
            if (!empty($data['filter_supplier']))
                $filter .= ($filter ? " AND" : "") . " LCASE(s.name) LIKE '" . $this->db->escape(utf8_strtolower($data['filter_supplier'])) . "%'";
            if (!empty($data['filter_supplier_group']))
                $filter .= ($filter ? " AND" : "") . " s.supplier_group_id = " . (int)$data['filter_supplier_group'];
            if (!empty($data['filterItem']))
                $filter .= " AND (
                    op.model LIKE '%" . $this->db->escape($data['filterItem']) . "%'
                    OR op.name LIKE '%" . $this->db->escape($data['filterItem']) . "%')";
            if (!empty($data['filterOrderId']))
                $filter .= " AND op.order_id = " . (int)$data['filterOrderId'];
            if (!empty($data['filterOrderItemId']))
                $filter .= ($filter ? " AND" : "") . " op.order_product_id = " . (int)$data['filterOrderItemId'];
            if (!empty($data['filterProductId']))
                $filter .= ($filter ? " AND" : "") . " op.product_id = " . (int)$data['filterProductId'];
            if (!empty($data['filterStatusId']))
                $filter .= ($filter ? " AND" : "") . " op.status_id IN (" . implode(', ', $data['filterStatusId']) . ")";
        }

		return $filter;
	}

    public function getOrderItemOptions($orderItemId)
    {
        $order_item_options = "";
        $languageId = $this->config->get('config_language_id');
        $query = "
            SELECT
                order_product_id, type,
                oo.name as eng_name, oo.product_option_id,
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
//        $this->log->write($query);
//        $this->log->write(print_r($result->rows, true));
        $order_item_options = array();
        if ($result->num_rows)
            foreach ($result->rows as $order_option)
                $order_item_options[$order_option['product_option_id']] = $order_option;
//                $order_item_options .= $order_option['name'] . ":&nbsp;" . $order_option['value'] . ";&nbsp;";
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

    public function setOrderItemComment($order_item_id, $comment) {
        $query = "
                UPDATE order_product
                SET
                    comment = '" . $this->db->escape($comment) . "'
                WHERE order_product_id = " . (int)$order_item_id
        ;
        //$this->log->write($query);
        $this->db->query($query);
    }

    public function setOrderItemStatus($order_item_id, $order_item_status_id)
    {
        $this->log->write($order_item_status_id);
        $order_item = $this->getOrderItem($order_item_id);
        $this->log->write($order_item['status']);
        if ($order_item['status'] != $order_item_status_id)
        {
            $query = "
                INSERT order_item_history
                SET
                    order_item_id = " . (int)$order_item_id . ",
                    order_item_status_id = " . (int) $order_item_status_id . ",
                    date_added = NOW()
            ";
            //print_r($query);exit();
            $this->db->query($query);
            $this->db->query("
                UPDATE order_product
                SET status_id = " . (int)$order_item_status_id . "
                WHERE order_product_id = " . (int)$order_item_id
            );
            return true;
        }
        else
            return false;
    }
}
?>