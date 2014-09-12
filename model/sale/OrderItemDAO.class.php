<?php
namespace model\sale;

use model\DAO;

class OrderItemDAO extends DAO {
    private $orderItemsFromQuery = "
        order_product as op
        JOIN `order` as o on o.order_id = op.order_id
        JOIN product as p on op.product_id  = p.product_id
        LEFT JOIN supplier as s on p.supplier_id = s.supplier_id
        LEFT JOIN customer as c on o.customer_id = c.customer_id
    ";

    /**
     * @param int $order_item_id
     * @return OrderItem
     */
    public function getOrderItem($order_item_id) {
        $result = $this->fetchOrderItems($this->buildFilterString(array('filterOrderItemId' => $order_item_id)));
        if ($result) {
            return new OrderItem($this->registry, $result[0]['affiliate_id'], $result[0]['affiliate_transaction_id'],
                $result[0]['comment'], $result[0]['customer_id'], $result[0]['customer_name'], $result[0]['customer_nick'],
                $result[0]['order_item_id'], $result[0]['image_path'], $result[0]['internal_model'], $result[0]['model'],
                $result[0]['name'], $result[0]['order_id'], $result[0]['price'], $result[0]['product_id'], $result[0]['public_comment'],
                $result[0]['quantity'], $result[0]['shipping'], $result[0]['status_date'], $result[0]['status_id'],
                $result[0]['supplier_group_id'], $result[0]['supplier_id'], $result[0]['supplier_name'], $result[0]['total'],
                $result[0]['weight'], $result[0]['weight_class_id']);
        } else {
            return null;
        }
    }

    public function getOrderItemHistory($orderItemId) {
        $sql = "
            SELECT *
            FROM order_item_history JOIN statuses ON order_item_status_id = group_id * 65536 + status_id
            WHERE order_item_id = ?
            ORDER BY date_added
        ";
//        $this->log->write($sql);
        $query = $this->getDb()->query($sql, array("i:$orderItemId"));
        return $query->rows;
    }

    /**
     * @param \stdClass $filter
     * @param string $sort
     * @param string $limit
     * @return array
     */
    private function fetchOrderItems($filter = null, $sort = "", $limit = "") {
        $query = "
			SELECT o.affiliate_id, at.affiliate_transaction_id ,
				op.*, op.order_product_id as order_item_id, op.status_id as status,
				concat(o.firstname, ' ', o.lastname) as customer_name, o.date_added,
				c.customer_id, c.nickname as customer_nick,
				p.product_id, p.supplier_id as supplier_id, p.image as image_path, p.weight, p.weight_class_id,
				s.name as supplier_name, s.supplier_group_id, s.internal_model as internal_model
			FROM
				" . $this->orderItemsFromQuery .
            " LEFT JOIN affiliate_transaction at ON op.order_product_id = at.order_product_id "
            . ($filter ? "WHERE " . $filter->filterString : "") . /*"
        	" . ($sort ? "ORDER BY $sort" : "") . */"
            ORDER BY supplier_name, op.model, op.order_product_id
			" . ($limit ? "LIMIT $limit" : "");

//        $this->getLogger()->write($query);
        $order_item_query = $this->getDb()->query($query, isset($filter) && isset($filter->params) ? $filter->params : null);

        if ($order_item_query->num_rows) {
            $response = $order_item_query->rows;
            foreach ($response as $index => $row) {
                //$this->log->write("------?--------?-------- " . print_r($row['image_path'], true));
                if($row['image_path'] == '' || $row['image_path'] == "data/event/agent-moomidae.jpg") {
                    $options = $this->getOrderItemOptions($row['order_product_id']);
                    $itemUrl = !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'])
                        ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '';
                    $response[$index]['image_path'] = !empty($itemUrl) ? $itemUrl : $row['image_path'];
                }
            }

            return $response;
        }
        else
            return array();
    }

    private function fetchOrderItemsCount($filter = null)	{
        $query = "
			SELECT COUNT(*) as total
			FROM
				" . $this->orderItemsFromQuery . "
			" . ($filter ? "WHERE " . $filter->filterString : "");
        $order_item_query = $this->getDb()->query($query, $filter->params);

        return $order_item_query->row['total'];
    }

    /**
     * @param array $data
     * @param string $filter
     * @return array
     */
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

    /**
     * @param OrderItem $orderItem
     * @return float
     */
    public function getOrderItemTotalCustomerCurrency($orderItem) {
        $rate = $this->getDb()->queryScalar("
            SELECT rate
            FROM
              currency AS c
              JOIN currency_history AS ch ON c.currency_id = ch.currency_id
            WHERE c.code = ? AND ch.date_added <= ?
            ORDER BY ch.date_added DESC
            LIMIT 0,1
        ", array("s:" . $orderItem->getCustomer()['base_currency_code'], "s:" . $orderItem->getTimeCreated())
        );
        if ($rate) {
            return $orderItem->getTotal() * $rate;
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

    public function getTimeCreated($orderItemId) {
        $results = $this->getDb()->queryScalar("
            SELECT MIN(date_added)
            FROM order_item_history
            WHERE order_item_id = ?
        ", array("i:$orderItemId"));
        return $results;
    }

    public function getTimeModified($orderItemId) {
        $results = $this->getDb()->queryScalar("
            SELECT MAX(date_added)
            FROM order_item_history
            WHERE order_item_id = ?
        ", array("i:$orderItemId"));
        return $results;
    }

    /**
     * Returns filter string and parameters array
     * @param array $data
     * @return \stdClass
     */
    private function buildFilterString($data = array()) {
        $filter = ""; $params = array();
        if (isset($data['selected_items']) && count($data['selected_items'])) {
            $filter = "op.order_product_id IN (" . substr(str_repeat(',?', sizeof($data['selected_items'])), 1) . ")";
            foreach ($data['selected_items'] as $orderItemId) {
                $params[] = "i:$orderItemId";
            }
        } else {
            if (!empty($data['filterCustomerId']))
                $filter .= ($filter ? " AND " : "") . "c.customer_id IN (" . implode(', ', $data['filterCustomerId']) . ")";
            if (!empty($data['filterItem']))
                $filter .= ($filter ? " AND " : "") . "
                    LCASE(op.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filterItem'])) . "%'
                    OR LCASE(op.name) LIKE '%" . $this->db->escape(utf8_strtolower($data['filterItem'])) . "%'";
            if (!empty($data['filter_model']))
                $filter .= ($filter ? " AND " : "") . "LCASE(op.model) LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_model'])) . "%'";
            if (!empty($data['filterStatusId'])) {
                $filter .= ($filter ? " AND " : "") . "op.status_id IN (" . substr(str_repeat(',?', sizeof($data['filterStatusId'])), 1) . ")";
                foreach ($data['filterStatusId'] as $statusId) {
                    $params[] = "i:$statusId";
                }
            }
            if (!empty($data['filterSupplierId']))
                $filter .= ($filter ? " AND " : "") . "s.supplier_id IN (" . implode(', ', $data['filterSupplierId']) . ")";
//            if (!empty($data['filter_supplier_group']))
//                $filter .= ($filter ? " AND" : "") . " s.supplier_group_id = " . (int)$data['filter_supplier_group'];
            if (!empty($data['filterOrderId']))
                $filter .= ($filter ? " AND " : "") . "op.order_id = " . (int)$data['filterOrderId'];
            if (!empty($data['filterOrderItemId'])) {
                if (!is_array($data['filterOrderItemId'])) {
                    $data['filterOrderItemId'] = array($data['filterOrderItemId']);
                }
                $filter .= ($filter ? " AND " : "") . "op.order_product_id IN (" . substr(str_repeat(',?', sizeof($data['filterOrderItemId'])), 1) . ")";
                foreach ($data['filterOrderItemId'] as $orderItemId) {
                    $params[] = "i:$orderItemId";
                }
            }
            if (!empty($data['filterProductId']))
                $filter .= ($filter ? " AND " : "") . "op.product_id IN (" . implode(', ', $data['filterProductId']) . ")";
        }

        $result = new \stdClass();
        $result->filterString = $filter;
        $result->params = $params;
        return $result;
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