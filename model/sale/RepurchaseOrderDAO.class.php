<?php
namespace model\sale;

class RepurchaseOrderDAO extends OrderItemDAO {
    private function buildFilter($data = array()) {
        $filter = "op.product_id = " . REPURCHASE_ORDER_PRODUCT_ID;
        if (isset($data['selectedItems']) && count($data['selectedItems'])) {
            $this->buildSimpleFieldFilterEntry('i', 'op.order_product_id', $data['selectedItems'], $filter, $params);
        } else {
            $this->buildSimpleFieldFilterEntry('d', 'op.total', $data['filterAmount'], $filter, $params);
            if (!empty($data['filterItemName'])) {
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID . "
                        AND value LIKE ?)";
                $params[] = 's:%' . $data['filterItemName'] . '%';
            }
            if (!empty($data['filterShopName'])) {
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID . "
                        AND value LIKE ?)";
                $params[] = 's:%' . $data['filterShopName'] . '%';
            }
            if (!empty($data['filterSiteName'])) {
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_URL_OPTION_ID . "
                        AND value LIKE ?)";
                $params[] = 's:%' . $data['filterSiteName'] . '%';
            }
            $this->buildSimpleFieldFilterEntry('i', 'c.customer_id', $data['filterCustomerId'], $filter, $params);
            $this->buildSimpleFieldFilterEntry('i', 'op.order_product_id', $data['filterOrderId'], $filter, $params);
            $this->buildSimpleFieldFilterEntry('i', 'op.status_id', $data['filterStatusId'], $filter, $params);
            if (!empty($data['filterStatusIdDateSet']) && !empty($data['filterStatusSetDate'])) {
                $filter .= " AND EXISTS (
                    SELECT order_item_history_id
                    FROM order_item_history
                    WHERE
                        order_item_id = op.order_product_id
                        AND order_item_status_id IN (" . substr(str_repeat(',?', sizeof($data['filterStatusIdDateSet'])), 1) . ")
                        AND date_added = ?
                )";
                foreach ($data['filterStatusIdDateSet'] as $filterValue) {
                    $params[] = 'i:' . $filterValue;
                }
                $params[] = 's:' . $data['filterStatusSetDate'];
            }
        }

        if (!$filter) {
            return null;
        }
        $result = new \stdClass();
        $result->filterString = $filter;
        $result->params = $params;
        return $result;
    }

    public function deleteOrderItem($repurchaseOrderItemId) {
        $this->db->query("
            DELETE FROM repurchase_order_item
            WHERE repurchase_order_item_id = ?",
            array("i:$repurchaseOrderItemId")
        );
    }

    /**
     * @param $orderId int
     * @return array
     */
    public function getOrder($orderId) {
        $orderItem = OrderItemDAO::getInstance()->getOrderItem($orderId);
        $options = OrderItemDAO::getInstance()->getOptions($orderItem['order_item_id']);
        return array (
            'orderId' => $orderItem['order_id'],
            'orderItemId' => $orderItem['order_item_id'],
            'privateComment' => $orderItem['comment'],
            'customerId' => $orderItem['customer_id'],
            'customerName' => $orderItem['customer_name'],
            'customerNick' => $orderItem['customer_nick'],
//            'whoOrders' => !empty($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID])
//                ? $options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]['value'] : '',
            'imagePath' => !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID])
                    ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '',
            'itemName' => !empty($options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'] : '',
            'itemUrl' => !empty($options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'] : '',
            'orderItemStatusId' => $orderItem['status'],
            'price' => $orderItem['price'],
            'comment' => !empty($orderItem['public_comment'])
                    ? $orderItem['public_comment']
                    : (!empty($options[REPURCHASE_ORDER_COMMENT_OPTION_ID])
                        ? $options[REPURCHASE_ORDER_COMMENT_OPTION_ID]['value'] : ''),
            'quantity' => $orderItem['quantity'],
            'shipping' => $orderItem['shipping'],
            'shopName' => !empty($options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID])
                    ? $options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID]['value'] : '',
            'status' => $orderItem['status'] >> 16 == GROUP_REPURCHASE_ORDER_ITEM_STATUS
                    ? $orderItem['status'] : REPURCHASE_ORDER_ITEM_STATUS_WAITING,
            'timeAdded' => $orderItem['date_added'],
            'total' => $orderItem['total']
        );
    }

    public function getOrders($data = array()) {
        $data['filterProductId'] = REPURCHASE_ORDER_PRODUCT_ID;
        $data['filterOrderItemId'] = $data['filterOrderId'];
        $repurchaseOrderItems = OrderItemDAO::getInstance()->getOrderItems($data, $this->buildFilter($data));
//        $this->log->write(print_r($repurchaseOrderItems, true));
        $items = array();
        if (!$repurchaseOrderItems)
            return $items;
        foreach ($repurchaseOrderItems as $repurchaseOrderItem) {
            $options = $this->getOptions($repurchaseOrderItem['order_item_id']);
//            $this->log->write(print_r($options, true));
            if (!empty($data['filterWhoOrders']) && !empty($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]))
                if ($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]['value_id'] != $data['filterWhoOrders'])
                    continue;
            $items[] = array(
                'orderId' => $repurchaseOrderItem['order_id'],
                'orderItemId' => $repurchaseOrderItem['order_item_id'],
                'privateComment' => $repurchaseOrderItem['comment'],
                'customerId' => $repurchaseOrderItem['customer_id'],
                'customerName' => $repurchaseOrderItem['customer_name'],
                'customerNick' => $repurchaseOrderItem['customer_nick'],
//                'whoOrders' => !empty($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID])
//                    ? $options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]['value'] : '',
                'imagePath' => !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID])
                        ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '',
                'itemName' => !empty($options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'])
                        ? $options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'] : '',
                'itemUrl' => !empty($options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'])
                        ? $options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'] : '',
                'orderItemStatusId' => $repurchaseOrderItem['status'],
                'price' => $repurchaseOrderItem['price'],
                'comment' => !empty($repurchaseOrderItem['public_comment'])
                        ? $repurchaseOrderItem['public_comment']
                        : (!empty($options[REPURCHASE_ORDER_COMMENT_OPTION_ID])
                            ? $options[REPURCHASE_ORDER_COMMENT_OPTION_ID]['value'] : ''),
                'quantity' => $repurchaseOrderItem['quantity'],
                'shipping' => $repurchaseOrderItem['shipping'],
                'shopName' => !empty($options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID])
                        ? $options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID]['value'] : '',
                'status' => $repurchaseOrderItem['status'] >> 16 == GROUP_REPURCHASE_ORDER_ITEM_STATUS
                        ? $repurchaseOrderItem['status'] : REPURCHASE_ORDER_ITEM_STATUS_WAITING,
                'timeAdded' => $repurchaseOrderItem['date_added'],
                'total' => $repurchaseOrderItem['total']
            );
        }
        return $items;
    }

    public function getOrdersCount($data = array()) {
        $data['filterProductId'] = REPURCHASE_ORDER_PRODUCT_ID;
        return OrderItemDAO::getInstance()->getOrderItemsCount($data);
    }

    public function getOptionsString($repurchaseOrderId) {
        //return OrderItemDAO::getInstance()->getOrderItemOptionsString($repurchaseOrderId);
        $options = '';
        foreach ($this->getOptions($repurchaseOrderId) as $option)
//            $this->log->write(print_r($option, true));
            if (($option['product_option_id'] == REPURCHASE_ORDER_IMAGE_URL_OPTION_ID) ||
                ($option['product_option_id'] == REPURCHASE_ORDER_SHOP_NAME_OPTION_ID))
                continue;
            elseif (preg_match(URL_PATTERN, $option['value']))
                $options .= $option['name'] . ":" . '<a target="_blank" href="' . $option['value'] . '">hyperlink</a>' . "\n";
            else
                $options .= $option['name'] . ": " . $option['value'] . "\n";
        return $options;
    }

    public function setAmount($orderId, $amount) {
        OrderItemDAO::getInstance()->setOrderItemTotal($orderId, $amount);
        OrderItemDAO::getInstance()->setOrderItemPrice($orderId, $amount);
    }

    public function setItemName($orderId, $itemName) {
        $testRow = $this->getDb()->query("
            SELECT order_option_id
            FROM order_option
            WHERE
                order_product_id = ?
                AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID
            , array("i:$orderId")
        );
        if ($testRow->num_rows) {
            $this->getDb()->query("
                UPDATE order_option
                SET value = ?
                WHERE
                    order_product_id = ?
                    AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID
                , array("s:$itemName", "i:$orderId")
            );
        }
        else {
            $orderItem = OrderItemDAO::getInstance()->getOrderItem($orderId);
            $this->getDb()->query("
                INSERT INTO order_option
                SET
                    order_id = ?,
                    order_product_id = ?,
                    product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID . ",
                    product_option_value_id = 0,
                    name = 'Item Name',
                    value = ?,
                    type = 'text'
                ", array('i:' . $orderItem['order_id'], "i:$orderId", "s:$itemName")
            );
        }
    }

    public function setShopName($orderId, $shopName) {
        $testRow = $this->getDb()->query("
            SELECT order_option_id
            FROM order_option
            WHERE
                order_product_id = ?
                AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID
            , array("i:$orderId")
        );
        if ($testRow->num_rows) {
            $this->getDb()->query("
                UPDATE order_option
                SET value = ?
                WHERE
                    order_product_id = ?
                    AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID
                , array("s:$shopName", "i:$orderId")
            );
        }
        else {
            $orderItem = OrderItemDAO::getInstance()->getOrderItem($orderId);
            $this->getDb()->query("
                INSERT INTO order_option
                SET
                    order_id = ?,
                    order_product_id = ?,
                    product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID . ",
                    product_option_value_id = 0,
                    name = 'Shop Name',
                    value = ?,
                    type = 'text'
                ", array("i:" . $orderItem['order_id'], "i:$orderId", "s:$shopName")
            );
        }
    }

    /**
     * @param $orderId int
     * @return array
     */
    public function getPrices($orderId) {
        $result = $this->getDb()->query("SELECT * FROM order_product WHERE order_product_id = ?", array("i:$orderId"));
        foreach (array_keys($result->row) as $key) {
            if (is_numeric($result->row[$key])) {
                $result->row[$key] = (float)$result->row[$key];
            }
        }
        return $result->row;
    }

    public function setImage($orderId, $imagePath) {
        $this->getDb()->query("
            UPDATE order_option
            SET value = ?
            WHERE
                order_product_id = ?
                AND product_option_id = " . REPURCHASE_ORDER_IMAGE_URL_OPTION_ID
            , array("s:$imagePath", "i:$orderId")
        );
        if (!$this->getDb()->countAffected())
        {
            $repurchaseOrder = OrderItemDAO::getInstance()->getOrderItem($orderId);
            $this->getDb()->query("
                INSERT INTO order_option
                SET
                    order_id = ?,
                    order_product_id = ?,
                    product_option_id = " . REPURCHASE_ORDER_IMAGE_URL_OPTION_ID . ",
                    product_option_value_id = 0,
                    name = 'Image URL',
                    value = ?,
                    type = 'text'
                ", array("i:" . $repurchaseOrder['order_id'], "i:$orderId", "s:$imagePath")
            );
        }
    }

    public function setQuantity($orderId, $quantity) {
        $this->getDb()->query("UPDATE order_product SET quantity = ? WHERE order_product_id = ?", array("i:$quantity", "i:$orderId"));
        $this->getDb()->query("UPDATE order_product SET total = (quantity*price) + shipping WHERE order_product_id = ?", array("i:$orderId"));
    }
}