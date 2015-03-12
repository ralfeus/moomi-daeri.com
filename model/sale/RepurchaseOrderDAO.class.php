<?php
namespace model\sale;

use system\library\Filter;

class RepurchaseOrderDAO extends OrderItemDAO {
    /**
     * @param array $data
     * @return Filter
     */
    private function buildFilter($data = array()) {
        $filterObject = new Filter("op.product_id = " . REPURCHASE_ORDER_PRODUCT_ID);
        $filter = "op.product_id = " . REPURCHASE_ORDER_PRODUCT_ID; $params = array();
        if (isset($data['selectedItems']) && count($data['selectedItems'])) {
            $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.order_product_id', $data['selectedItems'], $filter, $params, 'i'));
        } else {
            if (isset($data['filterAmount']) && is_numeric($data['filterAmount'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.total', $data['filterAmount'], $filter, $params, 'd'));
            }
            if (!empty($data['filterItemName'])) {
                $filterObject->addChunk("EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID . "
                        AND value LIKE :itemName)", [':itemName' => '%' . $data['filterItemName'] . '%']);
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID . "
                        AND value LIKE :itemName)";
                $params[':itemName'] = '%' . $data['filterItemName'] . '%';
            }
            if (!empty($data['filterShopName'])) {
                $filterObject->addChunk("EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID . "
                        AND value LIKE :shopName)", [':shopName'=> '%' . $data['filterShopName'] . '%']);
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID . "
                        AND value LIKE :shopName)";
                $params[':shopName'] = '%' . $data['filterShopName'] . '%';
            }
            if (!empty($data['filterSiteName'])) {
                $filterObject->addChunk("EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_URL_OPTION_ID . "
                        AND value LIKE :siteName)", [':siteName'=> '%' . $data['filterSiteName'] . '%']);
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_URL_OPTION_ID . "
                        AND value LIKE :siteName)";
                $params[':siteName'] = '%' . $data['filterSiteName'] . '%';
            }
            if (isset($data['filterCustomerId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('c.customer_id', $data['filterCustomerId'], $filter, $params, 'i'));
            }
            if (isset($data['filterOrderId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.order_product_id', $data['filterOrderId'], $filter, $params, 'i'));
            }
            if (isset($data['filterStatusId'])) {
                $filterObject->addChunk($this->buildSimpleFieldFilterEntry('op.status_id', $data['filterStatusId'], $filter, $params, 'i'));
            }
            if (!empty($data['filterStatusIdDateSet']) && !empty($data['filterStatusSetDate'])) {
                $tmpFilterString = "EXISTS (
                    SELECT order_item_history_id
                    FROM order_item_history
                    WHERE
                        order_item_id = op.order_product_id
                        AND order_item_status_id IN (:statusIdDateSet" . implode(', :statusIdDateSet', array_keys($data['filterStatusIdDateSet'])) . ")
                        AND date_added = :dateStatusSet
                )";
                $filter .= " AND EXISTS (
                    SELECT order_item_history_id
                    FROM order_item_history
                    WHERE
                        order_item_id = op.order_product_id
                        AND order_item_status_id IN (:statusIdDateSet" . implode(', :statusIdDateSet', array_keys($data['filterStatusIdDateSet'])) . ")
                        AND date_added = :dateStatusSet
                )";
                foreach ($data['filterStatusIdDateSet'] as $key => $filterValue) {
                    $tmpParams[":statusIdDateSet$key"] = $filterValue;
                    $params[":statusIdDateSet$key"] = $filterValue;
                }
                $tmpParams[':dateStatusSet'] = $data['filterStatusSetDate'];
                $params[':dateStatusSet'] = $data['filterStatusSetDate'];
                $filterObject->addChunk($tmpFilterString, $tmpParams);
            }
        }

        if (!$filter) {
            return null;
        }
        $result = new \stdClass();
        $result->filterString = $filter;
        $result->params = $params;
        return $filterObject; // $result; //
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
        $options = OrderItemDAO::getInstance()->getOptions($orderItem->getId());
        return array (
            'orderId' => $orderItem->getOrderId(),
            'orderItemId' => $orderItem->getId(),
            'privateComment' => $orderItem->getPrivateComment(),
            'customerId' => $orderItem->getCustomerId(),
            'customerName' => $orderItem->getCustomerName(),
            'customerNick' => $orderItem->getCustomerNick(),
//            'whoOrders' => !empty($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID])
//                ? $options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]['value'] : '',
            'imagePath' => !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID])
                    ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '',
            'itemName' => !empty($options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'] : '',
            'itemUrl' => !empty($options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'] : '',
            'orderItemStatusId' => $orderItem->getStatusId(),
            'price' => $orderItem->getPrice(),
            'whiteprice' => $orderItem->getWhitePrice(),
            'comment' => !empty($options[REPURCHASE_ORDER_COMMENT_OPTION_ID])
                        ? $options[REPURCHASE_ORDER_COMMENT_OPTION_ID]['value'] : '',
            'quantity' => $orderItem->getQuantity(),
            'shipping' => $orderItem->getShippingCost(),
            'shopName' => !empty($options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID])
                    ? $options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID]['value'] : '',
            'status' => $orderItem->getStatusId() >> 16 == GROUP_REPURCHASE_ORDER_ITEM_STATUS
                    ? $orderItem->getStatusId() : REPURCHASE_ORDER_ITEM_STATUS_WAITING,
            'timeAdded' => $orderItem->getTimeCreated(),
            'total' => $orderItem->getTotal()
        );
    }

    public function getOrders($data = array()) {
        $data['filterProductId'] = REPURCHASE_ORDER_PRODUCT_ID;
        $data['filterOrderItemId'] = $data['filterOrderId'];
        $repurchaseOrderItems = OrderItemDAO::getInstance()->getOrderItems($data, $this->buildFilter($data), true);
//        $this->log->write(print_r($repurchaseOrderItems, true));
        $items = array();
        if (!$repurchaseOrderItems)
            return $items;
        foreach ($repurchaseOrderItems as $repurchaseOrderItem) {
            $options = $this->getOptions($repurchaseOrderItem->getId());
//            $this->log->write(print_r($options, true));
            if (!empty($data['filterWhoOrders']) && !empty($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]))
                if ($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]['value_id'] != $data['filterWhoOrders'])
                    continue;
            $items[] = array(
                'orderId' => $repurchaseOrderItem->getOrderId(),
                'orderItemId' => $repurchaseOrderItem->getId(),
                'privateComment' => $repurchaseOrderItem->getPrivateComment(),
                'customerId' => $repurchaseOrderItem->getCustomer()['customer_id'],
                'customerName' => $repurchaseOrderItem->getCustomer()['firstname'] . ' ' . $repurchaseOrderItem->getCustomer()['lastname'],
                'customerNick' => $repurchaseOrderItem->getCustomer()['nickname'],
                'imagePath' => !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID])
                        ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '',
                'itemName' => !empty($options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'])
                        ? $options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'] : '',
                'itemUrl' => !empty($options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'])
                        ? $options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'] : '',
                'orderItemStatusId' => $repurchaseOrderItem->getStatusId(),
                'price' => $repurchaseOrderItem->getPrice(),
                'whiteprice' => $repurchaseOrderItem->getWhitePrice(),
                'comment' => !empty($repurchaseOrderItem->getPublicComment())
                        ? $repurchaseOrderItem->getPublicComment()
                        : (!empty($options[REPURCHASE_ORDER_COMMENT_OPTION_ID])
                            ? $options[REPURCHASE_ORDER_COMMENT_OPTION_ID]['value'] : ''),
                'quantity' => $repurchaseOrderItem->getQuantity(),
                'shipping' => $repurchaseOrderItem->getShippingCost(),
                'shopName' => !empty($options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID])
                        ? $options[REPURCHASE_ORDER_SHOP_NAME_OPTION_ID]['value'] : '',
                'status' => $repurchaseOrderItem->getStatusId() >> 16 == GROUP_REPURCHASE_ORDER_ITEM_STATUS
                        ? $repurchaseOrderItem->getStatusId() : REPURCHASE_ORDER_ITEM_STATUS_WAITING,
                'timeAdded' => $repurchaseOrderItem->getTimeCreated(),
                'total' => $repurchaseOrderItem->getTotal()
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
                ", array('i:' . $orderItem->getId(), "i:$orderId", "s:$itemName")
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
                ", array("i:" . $orderItem->getId(), "i:$orderId", "s:$shopName")
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