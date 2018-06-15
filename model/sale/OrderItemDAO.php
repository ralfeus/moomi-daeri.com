<?php
namespace model\sale;

use model\catalog\Supplier;
use model\catalog\SupplierDAO;
use model\DAO;
use system\library\Filter;

class OrderItemDAO extends DAO {
    private $orderItemsFromQuery = "
        order_product as op
        JOIN `order` as o on o.order_id = op.order_id AND o.order_status_id <> 0
        LEFT JOIN product as p on op.product_id  = p.product_id
        LEFT JOIN supplier as s on p.supplier_id = s.supplier_id
        LEFT JOIN customer as c on o.customer_id = c.customer_id
        JOIN (
            SELECT order_item_id, MAX(date_added) as date_last_status_set
            FROM order_item_history
            GROUP BY order_item_id
        ) as oils on op.order_product_id = oils.order_item_id
    ";

    /**
     * @param int $order_item_id
     * @return OrderItem
     */
    public function getOrderItem($order_item_id) {
        $result = $this->fetchOrderItems($this->buildFilter(array('filterOrderItemId' => $order_item_id)));
        if ($result) {
            return new OrderItem($this->registry, $result[0]['affiliate_id'], $result[0]['affiliate_transaction_id'],
                $result[0]['comment'], $result[0]['customer_id'], $result[0]['customer_name'], $result[0]['customer_nick'],
                $result[0]['order_item_id'], $result[0]['image_path'], $result[0]['internal_model'], $result[0]['korean_name'],
                $result[0]['model'], $result[0]['name'], $result[0]['order_id'], $result[0]['price'], $result[0]['whiteprice'],
                $result[0]['product_id'], $result[0]['public_comment'], $result[0]['quantity'], $result[0]['shipping'],
                $result[0]['status_date'], $result[0]['status_id'], $result[0]['supplier_group_id'], $result[0]['supplier_id'],
                $result[0]['supplier_name'], $result[0]['supplier_url'], $result[0]['total'], $result[0]['weight'],
                $result[0]['weight_class_id']);
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
     * @param Filter $filter
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
				p.product_id, p.supplier_id as supplier_id, p.image as image_path, p.weight, p.weight_class_id, p.supplier_url, p.korean_name,
				s.name as supplier_name, s.supplier_group_id, s.internal_model as internal_model
			FROM
				" . $this->orderItemsFromQuery .
            " LEFT JOIN affiliate_transaction at ON op.order_product_id = at.order_product_id
            " . ($filter->isFilterSet() ? "WHERE " . $filter->getFilterString() : "") . "
        	" . ($sort ? "ORDER BY $sort" : "") . "
            /*ORDER BY supplier_name, op.model, op.order_product_id*/
			" . ($limit ? "LIMIT $limit" : "");

//        $this->getLogger()->write($query);
//        if ($filter->isFilterSet()) {
//            $this->getLogger()->write(print_r($filter->getParams(), true));
//        }
        $order_item_query = $this->getDb()->query($query, $filter->isFilterSet() ? $filter->getParams() : null);

        if ($order_item_query->num_rows) {
            $response = $order_item_query->rows;
            foreach ($response as $index => $row) {
                //$this->log->write("------?--------?-------- " . print_r($row['image_path'], true));
                if($row['image_path'] == '' || $row['image_path'] == "data/event/agent-moomidae.jpg") {
                    $options = $this->getOptions($row['order_product_id']);
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

    /**
     * @param Filter $filter
     * @return int
     */
    private function fetchOrderItemsCount($filter = null) {
        $key = "orderItem." . md5(serialize($filter));
        $result = $this->getCache()->get($key);
        if (is_null($result)) {
            if (is_null($filter)) {
                $filter = new Filter();
            }
            $query = "
			SELECT COUNT(*) AS total
			FROM
				" . $this->orderItemsFromQuery . "
			" . ($filter->isFilterSet() ? "WHERE " . $filter->getFilterString() : "");
            $order_item_query = $this->getDb()->query($query, $filter->getParams());

            $result = $order_item_query->row['total'];
            $this->getCache()->set($key, $result);
        }
        return $result;
    }

    /**
     * @param array $data
     * @param Filter $filter
     * @param bool $objects Defines whether OrderItem object should be returned instead of array TODO: to make only option
     * @return array|OrderItem[]
     * Best approach is to use OrderItemDAO::getOrderItems(<bla>, <bla>, true) to get array of objects as array of arrays
     * Will be removed
     */
    public function getOrderItems($data = array(), $filter = null, $objects = true) {
        if (is_null($filter)) { $filter = $this->buildFilter($data); }
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

        if (!empty($data['sort'])) { // && in_array($data['sort'], $sort_data)) {
            $sort = $data['sort'];
        } else {
//            $sort = "op.order_product_id";
            $sort = "order_item_id";
            $data['order'] = 'DESC';
        }

        if (!empty($data['order']) && ($data['order'] == 'DESC')) {
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

        $result = $this->fetchOrderItems($filter, $sort, $limit);
        if ($objects) {
            $objectArray = array();
            foreach ($result as $orderItemInfo) {
                $objectArray[] = new OrderItem($this->registry, $orderItemInfo['affiliate_id'], $orderItemInfo['affiliate_transaction_id'],
                    $orderItemInfo['comment'], $orderItemInfo['customer_id'], $orderItemInfo['customer_name'], $orderItemInfo['customer_nick'],
                    $orderItemInfo['order_item_id'], $orderItemInfo['image_path'], $orderItemInfo['internal_model'],
                    $orderItemInfo['korean_name'], $orderItemInfo['model'], $orderItemInfo['name'], $orderItemInfo['order_id'],
                    $orderItemInfo['price'], $orderItemInfo['whiteprice'], $orderItemInfo['product_id'],
                    $orderItemInfo['public_comment'], $orderItemInfo['quantity'], $orderItemInfo['shipping'],
                    $orderItemInfo['time_modified'], $orderItemInfo['status'], $orderItemInfo['supplier_group_id'],
                    $orderItemInfo['supplier_id'], $orderItemInfo['supplier_name'], $orderItemInfo['supplier_url'],
                    $orderItemInfo['total'], $orderItemInfo['weight'], $orderItemInfo['weight_class_id']);
            }
            return $objectArray;
        } else {
            return $result;
        }
    }

    /**
     * @param array $data
     * @return Customer[]
     */
    public function getOrderItemsCustomers($data = array()) {
        unset($data['filterCustomerId']);
        $filter = $this->buildFilter($data);
        $query = "
            SELECT DISTINCT c.*
            FROM " . $this->orderItemsFromQuery
            . ($filter->isFilterSet() ? "WHERE " . $filter->getFilterString() : "")
        ;
        $result = array();
        foreach ($this->getDb()->query($query, $filter->getParams())->rows as $customerEntry) {
            $result[] = new Customer($customerEntry['address_id'], $customerEntry['approved'], $customerEntry['balance'],
                $customerEntry['base_currency_code'], $customerEntry['cart'], $customerEntry['customer_group_id'],
                $customerEntry['date_added'], $customerEntry['email'], $customerEntry['fax'], $customerEntry['firstname'],
                $customerEntry['customer_id'], $customerEntry['ip'], $customerEntry['lastname'], $customerEntry['newsletter'],
                $customerEntry['nickname'], $customerEntry['password'], $customerEntry['telephone'], $customerEntry['status'],
                $customerEntry['store_id'], $customerEntry['token'], $customerEntry['wishlist']);
        }
        return $result;
    }

    /**
     * @param array $data
     * @return Supplier[]
     */
    public function getOrderItemsSuppliers($data = array()) {
        unset($data['filterSupplierId']);
        $filter = $this->buildFilter($data);
        $query = "
            SELECT DISTINCT s.supplier_id
            FROM " . $this->orderItemsFromQuery
            . ($filter->isFilterSet() ? "WHERE " . $filter->getFilterString() : "")
        ;
        $result = array();
        foreach ($this->getDb()->query($query, $filter->getParams())->rows as $supplierId) {
            $result[] = SupplierDAO::getInstance()->getSupplier($supplierId['supplier_id']);
        }
        return $result;
    }

    /**
     * @param array $data
     * @param Filter $filter
     * @return int
     */
    public function getOrderItemsCount($data = array(), $filter = null)	{
        if (is_null($filter)) { $filter = $this->buildFilter($data); }
        return $this->fetchOrderItemsCount($filter);
    }

    public function getOrderItemsTotals($data = array()) {
        $orderItems = $this->getOrderItems($data, null, true);
        $subtotal = 0;
        $total_weight = 0;

        foreach ($orderItems as $orderItem) {
            $subtotal += $orderItem->getTotal();
            $total_weight += $this->weight->convert($orderItem->getWeight(), $orderItem->getWeightClassId(), $this->config->get('config_weight_class_id')) * $orderItem->getQuantity();
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
     * Accepts filters:
     * filterComment: string
     * filterCustomerId: int[]
     * filterItem: string - substring of name or model
     * filterModel: string - substring of model
     * filterOrderId: int
     * filterOrderProductId: int
     * filterProductId: int
     * filterStatusId: int[]
     * filterSupplierId: int[] - supplier IDs
     * filterTimeModified: string
     * selected_items: int[] - item IDs
     * @param array $data
     * @return Filter
     */
    private function buildFilter($data = array()) {
        $filter = ""; $params = array(); $filterObject = new Filter();
        if (isset($data['selected_items']) && count($data['selected_items'])) {
            $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.order_product_id', $data['selected_items'], $filter, $params, 'i'));
        } else {
            if (!empty($data['filterComment'])) {
                $filterObject->addChunk("LCASE(op.comment) LIKE :filterComment
                    OR LCASE(op.public_comment) LIKE :filterComment",
                    [':filterComment' => '%' . utf8_strtolower($data['filterComment']) . '%']);
                $filter .= ($filter ? " AND " : "") . "
                    LCASE(op.comment) LIKE ?
                    OR LCASE(op.public_comment) LIKE ?";
                $params[] = 's:%' . utf8_strtolower($data['filterComment']) . '%';
                $params[] = 's:%' . utf8_strtolower($data['filterComment']) . '%';
            }
            if (isset($data['filterCustomerId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('c.customer_id', $data['filterCustomerId'], $filter, $params, 'i'));
            }
            if (!empty($data['filterItem'])) {
                $filterObject->addChunk("LCASE(op.model) LIKE :filterItem
                    OR LCASE(op.name) LIKE :filterItem",
                    [':filterItem' => '%' . utf8_strtolower($data['filterItem']) . '%']);
                $filter .= ($filter ? " AND " : "") . "
                    LCASE(op.model) LIKE ?
                    OR LCASE(op.name) LIKE ?";
                $params[] = 's:%' . utf8_strtolower($data['filterItem']) . '%';
                $params[] = 's:%' . utf8_strtolower($data['filterItem']) . '%';
            }
            if (!empty($data['filterModel'])) {
                $filterObject->addChunk("LCASE(op.model) LIKE :filterModel", [':filterModel' => '%' . utf8_strtolower($data['filterModel']) . '%']);
                $filter .= ($filter ? " AND " : "") . "LCASE(op.model) LIKE ?";
                $params[] = 's:%' . utf8_strtolower($data['filterModel']) . '%';
            }
            if (isset($data['filterStatusId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.status_id', $data['filterStatusId'], $filter, $params, 'i'));
            }
            if (isset($data['filterSupplierId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('s.supplier_id', $data['filterSupplierId'], $filter, $params, 'i'));
            }
            if (isset($data['filterOrderId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.order_id', $data['filterOrderId'], $filter, $params, 'i'));
            }
            if (isset($data['filterOrderItemId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.order_product_id', $data['filterOrderItemId'], $filter, $params, 'i'));
            }
            if (isset($data['filterProductId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.product_id', $data['filterProductId'], $filter, $params, 'i'));
            }
            if (!empty($data['filterTimeModifiedFrom'])) {
                $filterObject->addChunk("op.time_modified >= :filterTimeModifiedFrom", [':filterTimeModifiedFrom' => $data['filterTimeModifiedFrom']]);
                $filter .= ($filter ? " AND " : '') . "op.time_modified >= ?";
                $params[] = 's:' . $data['filterTimeModifiedFrom'];
            }
            if (!empty($data['filterTimeModifiedTo'])) {
                $filterObject->addChunk("op.time_modified <= :filterTimeModifiedTo", [':filterTimeModifiedTo' => $data['filterTimeModifiedTo']]);
                $filter .= ($filter ? " AND " : '') . "op.time_modified <= ?";
                $params[] = 's:' . $data['filterTimeModifiedTo'];
            }
        }

//        if (!$filter) {
//            return null;
//        }
        $result = new \stdClass();
        $result->filterString = $filter;
        $result->params = $params;
        return $filterObject; // $result; //
    }

//    /**
//     * @param string $fieldName
//     * @param mixed $filterValues
//     * @param string &$filterString
//     * @param array &$params
//     * @param string $entryType
//     * @return void
//     */
//    protected function buildSimpleFieldFilterEntry($fieldName, $filterValues, &$filterString, &$params, $entryType = null) {
//        if (isset($filterValues)) {
//            if (!is_array($filterValues)) {
//                if (empty($filterValues) && ($filterValues !== 0)) {
//                    return;
//                }
//                $filterValues = array($filterValues);
//            } elseif (!sizeof($filterValues)) {
//                return;
//            }
//            $filterString .= ($filterString ? " AND " : "") . "$fieldName IN (" . substr(str_repeat(',?', sizeof($filterValues)), 1) . ")";
//            foreach ($filterValues as $filterValue) {
//                $params[] = $entryType . ':' . $filterValue;
//            }
//        }
//    }

    public function getOptions($orderItemId) {
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
        foreach ($this->getOptions($orderItemId) as $option)
            if (preg_match(URL_PATTERN, $option['value']))
                $options .= $option['name'] . ":" . '<a target="_blank" href="' . $option['value'] . '">hyperlink</a>' . "\n";
            else
                $options .= $option['name'] . ": " . $option['value'] . "\n";
        return $options;
    }

    /**
     * @param OrderItem $orderItem
     * @param bool $delayed Defines whether update in the database must be performed immediately or can be delayed
     */
    public function saveOrderItem($orderItem, $delayed = false) {
        $this->getDb()->query("
            UPDATE" . ($delayed ? " LOW_PRIORITY " : "") . "order_product
            SET
                total = price * quantity + ?,
                comment = ?,
                public_comment = ?,
                shipping = ?,
                time_modified = NOW()
            WHERE order_product_id = ?
            ", array(
                'd:' . $orderItem->getShippingCost(),
                's:' . $orderItem->getPrivateComment(),
                's:' . $orderItem->getPublicComment(),
                'd:' . $orderItem->getShippingCost(),
                'i:' . $orderItem->getId()
            )
        );
    }

    /**
     * @param int $order_item_id
     * @param string $comment
     * @param bool $isPrivate
     */
    public function setOrderItemComment($order_item_id, $comment, $isPrivate = true) {
        $field = $isPrivate ? 'comment' : 'public_comment';
        $query = "
            UPDATE order_product
            SET
                $field = '" . $this->getDb()->escape($comment) . "'
            WHERE order_product_id = " . (int)$order_item_id
        ;
        $this->log->write($query);
        $this->getDb()->query($query);
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
     * @param int $orderItemId
     * @param int $orderItemStatusId
     * @return bool
     * TODO: Replace with change of the OrderItem object + saveOrderItem()
     */
    public function setStatus($orderItemId, $orderItemStatusId) {
        $orderItem = $this->getOrderItem($orderItemId);
        if ($orderItem->getStatusId() != $orderItemStatusId) {
            $this->getDb()->query("
                UPDATE order_product
                SET
                    status_id = ?,
                    time_modified = NOW()
                WHERE order_product_id = ?
            ", array("i:$orderItemStatusId", "i:$orderItemId"));
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

    public function setWhitePrice($orderItemId, $amount)
    {
        $query = "UPDATE order_product SET whiteprice = " . (float)$amount . " WHERE order_product_id = " . (int)$orderItemId;
        $this->getDb()->query($query);
        $query = "UPDATE order_product SET price = " . (float)$amount . " WHERE order_product_id = " . (int)$orderItemId;
        $this->getDb()->query($query);
    }

    public function setShipping($orderItemId, $amount)
    {
        $query = "UPDATE order_product SET shipping = " . (float)$amount . " WHERE order_product_id = " . (int)$orderItemId;
        $this->db->query($query);
        $query = "UPDATE order_product SET total = (quantity*price) + shipping WHERE order_product_id = " . (int)$orderItemId;
        $this->db->query($query);
    }
}