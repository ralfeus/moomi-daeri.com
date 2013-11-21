<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 16.6.12
 * Time: 9:47
  */
class ModelSaleRepurchaseOrder extends Model
{
    private $modelOrderItem;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->modelOrderItem = $this->load->model('sale/order_item');
    }

    private function buildFilterString($data = array())
    {
        $filter = "op.product_id = " . REPURCHASE_ORDER_PRODUCT_ID;
        if (isset($data['selectedItems']) && count($data['selectedItems']))
            $filter = "op.order_product_id in (" . implode(', ', $data['selectedItems']) . ")";
        else
        {
            if (isset($data['filterAmount']) && ($data['filterAmount'] != null))
                $filter .= " AND op.total = " . (float)$data['filterAmount'];
            if (!empty($data['filterItemName'])) {
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM " . DB_PREFIX . "order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID . "
                        AND value LIKE '%" . $data['filterItemName'] . "%')";
            }
            if (!empty($data['filterShopName'])) {
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM " . DB_PREFIX . "order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID . "
                        AND value LIKE '%" . $data['filterShopName'] . "%')";
            }
            if (!empty($data['filterSiteName'])) {
                $filter .= " AND EXISTS (
                    SELECT order_option_id
                    FROM " . DB_PREFIX . "order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = " . REPURCHASE_ORDER_ITEM_URL_OPTION_ID . "
                        AND value LIKE '%" . $data['filterSiteName'] . "%')";
            }
            if (!empty($data['filterCustomerId']))
                $filter .= " AND c.customer_id IN (" . implode(', ', $data['filterCustomerId']) . ")";
            if (!empty($data['filterOrderId']))
                $filter .= " AND op.order_product_id = " . (int)$data['filterOrderId'];
            if (!empty($data['filterStatusId']))
                $filter .= " AND op.status_id IN (" . implode(', ', $data['filterStatusId']) . ")";
        }
//        $this->log->write($filter);
        return $filter;
    }

    public function setStatus($orderId, $statusId)
    {
//        $this->log->write($statusId);
        $this->load->model('sale/order_item')->setOrderItemStatus($orderId, $statusId);
    }

    public function deleteOrderItem($repurchase_order_item_id)
    {
        $this->db->query("
            DELETE FROM " . DB_PREFIX . "repurchase_order_item
            WHERE repurchase_order_item_id = " . (int)$repurchase_order_item_id
        );
    }

    public function getOrders($data = array())
    {
        $repurchaseOrderItems = $this->modelOrderItem->getOrderItems($data, $this->buildFilterString($data));
//        $this->log->write(print_r($repurchaseOrderItems, true));
        $items = array();
        if (!$repurchaseOrderItems)
            return $items;
        foreach ($repurchaseOrderItems as $repurchaseOrderItem)
        {
            $options = $this->modelOrderItem->getOrderItemOptions($repurchaseOrderItem['order_item_id']);
//            $this->log->write(print_r($options, true));
            if (!empty($data['filterWhoOrders']) && !empty($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]))
                if ($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]['value_id'] != $data['filterWhoOrders'])
                    continue;
            $items[] = array(
                'orderId' => $repurchaseOrderItem['order_id'],
                'orderItemId' => $repurchaseOrderItem['order_item_id'],
                'comment' => $repurchaseOrderItem['comment'],
                'customerId' => $repurchaseOrderItem['customer_id'],
                'customerName' => $repurchaseOrderItem['customer_name'],
                'customerNick' => $repurchaseOrderItem['customer_nick'],
                'whoOrders' => !empty($options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID])
                    ? $options[REPURCHASE_ORDER_WHO_BUYS_OPTION_ID]['value'] : '',
                'imagePath' => !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID])
                    ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '',
                'itemName' => !empty($options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_ITEM_NAME_OPTION_ID]['value'] : '',
                'itemUrl' => !empty($options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'])
                    ? $options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'] : '',
                'orderItemStatusId' => $repurchaseOrderItem['status'],
                'price' => $repurchaseOrderItem['price'],
                'publicComment' => !empty($repurchaseOrderItem['public_comment'])
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

    public function getOrdersCount($data = array())
    {
        $data['filter_model'] = 'Repurchase agent';
        return $this->modelOrderItem->getOrderItemsCount(null, $this->buildFilterString($data));
    }

    private function getOrderInitialStatus()
    {
        $query = $this->db->query("
            SELECT repurchase_order_status_id
            FROM " . DB_PREFIX . "repurchase_order_statuses
            WHERE workflow_order = 1
        ");
        return $query->row['repurchase_order_status_id'];
    }

    private function getOrderItemInitialStatus()
    {
        $query = $this->db->query("
            SELECT order_item_status_id
            FROM " . DB_PREFIX . "order_item_status
            WHERE workflow_order = 1
        ");
        return $query->row['order_item_status_id'];
    }

    public function getOrderOptions($repurchaseOrderId)
    {
        return $this->modelOrderItem->getOrderItemOptions($repurchaseOrderId);
    }

    public function getOrderOptionsString($repurchaseOrderId)
    {
        //return $this->modelOrderItem->getOrderItemOptionsString($repurchaseOrderId);
        $options = '';
        foreach ($this->getOrderOptions($repurchaseOrderId) as $option)
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

    public function setAmount($orderId, $amount)
    {
        $this->modelOrderItem->setOrderItemTotal($orderId, $amount);
        $this->modelOrderItem->setOrderItemPrice($orderId, $amount);
    }

    public function setItemName($orderId, $itemName) {
        $testRow = $this->getDb()->query("
            SELECT order_option_id
            FROM " . DB_PREFIX . "order_option
            WHERE
                order_product_id = " . (int)$orderId . "
                AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID
        );
        if ($testRow->num_rows) {
            $this->getDb()->query("
                UPDATE " . DB_PREFIX . "order_option
                SET value = '" . $this->getDb()->escape($itemName) . "'
                WHERE
                    order_product_id = " . (int)$orderId . "
                    AND product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID
            );
        }
        else {
            $orderItem = $this->modelOrderItem->getOrderItem($orderId);
            $this->getDb()->query("
                INSERT INTO " . DB_PREFIX . "order_option
                SET
                    order_id = " . (int)$orderItem['order_id'] . ",
                    order_product_id = " . (int)$orderId . ",
                    product_option_id = " . REPURCHASE_ORDER_ITEM_NAME_OPTION_ID . ",
                    product_option_value_id = 0,
                    name = 'Item Name',
                    value = '" . $this->getDb()->escape($itemName) . "',
                    type = 'text'
            ");
        }
    }

    public function setPrice($orderId, $amount)
    {
        $this->modelOrderItem->setPrice($orderId, $amount);
    }

    public function setShipping($orderId, $amount)
    {
        $this->modelOrderItem->setShipping($orderId, $amount);
    }

    public function setShopName($orderId, $shopName) {
        $testRow = $this->getDb()->query("
            SELECT order_option_id
            FROM " . DB_PREFIX . "order_option
            WHERE
                order_product_id = " . (int)$orderId . "
                AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID
        );
        if ($testRow->num_rows) {
            $this->getDb()->query("
                UPDATE " . DB_PREFIX . "order_option
                SET value = '" . $this->getDb()->escape($shopName) . "'
                WHERE
                    order_product_id = " . (int)$orderId . "
                    AND product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID
            );
        }
        else {
            $orderItem = $this->modelOrderItem->getOrderItem($orderId);
            $this->getDb()->query("
                INSERT INTO " . DB_PREFIX . "order_option
                SET
                    order_id = " . (int)$orderItem['order_id'] . ",
                    order_product_id = " . (int)$orderId . ",
                    product_option_id = " . REPURCHASE_ORDER_SHOP_NAME_OPTION_ID . ",
                    product_option_value_id = 0,
                    name = 'Shop Name',
                    value = '" . $this->getDb()->escape($shopName) . "',
                    type = 'text'
            ");
        }
    }

    public function getPrices($orderId) {
      $query = "SELECT * FROM " . DB_PREFIX . "order_product WHERE order_product_id = " . (int)$orderId;
      $result = $this->db->query($query);
      return $result->rows;
    }

    public function setImage($orderId, $imagePath)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "order_option
            SET value = '" . $this->db->escape($imagePath) . "'
            WHERE
                order_product_id = " . (int)$orderId . "
                AND product_option_id = " . REPURCHASE_ORDER_IMAGE_URL_OPTION_ID
        );
        if (!$this->db->countAffected())
        {
            $repurchaseOrder = $this->modelOrderItem->getOrderItem($orderId);
            $this->db->query("
                INSERT INTO " . DB_PREFIX . "order_option
                SET
                    order_id = " . $repurchaseOrder['order_id'] . ",
                    order_product_id = " . (int)$orderId . ",
                    product_option_id = " . REPURCHASE_ORDER_IMAGE_URL_OPTION_ID . ",
                    product_option_value_id = 0,
                    name = 'Image URL',
                    value = '" . $this->db->escape($imagePath) . "',
                    type = 'text'
            ");
        }
    }

    public function setQuantity($orderId, $quantity)
    {
        $query = "UPDATE " . DB_PREFIX . "order_product SET quantity = " . (int)$quantity . " WHERE order_product_id = " . (int)$orderId;
        $this->db->query($query);
        $query = "UPDATE " . DB_PREFIX . "order_product SET total = (quantity*price) + shipping WHERE order_product_id = " . (int)$orderId;
        $this->db->query($query);
    }
}
