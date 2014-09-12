<?php
use model\sale\OrderItemDAO;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 16.6.12
 * Time: 9:47
  */
class ModelAccountRepurchaseOrder extends Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);
    }

//    public function addOrder($customer_id, $repurchase_order_items = array())
//    {
//        //print_r($repurchase_order_items);exit();
//        $this->db->query("
//            INSERT INTO repurchase_orders
//            SET
//                customer_id = " . (int)$customer_id . ",
//                date_added = DATE(NOW())
//        ");
//        $repurchase_order_id = $this->db->getLastId();
//
//        /// Add initial status of the order into history
//        $this->addOrderStatus($repurchase_order_id, $this->getOrderInitialStatus());
//
//        /// Add repurchase order items if any
//        foreach ($repurchase_order_items as $repurchase_order_item)
//        {
//            //print_r($repurchase_order_item);exit();
//            $this->addOrderItem($repurchase_order_id, $repurchase_order_item);
//        }
//    }

    private function buildFilterString($data = array())
    {
        $filter = "";
        if (!empty($data['filter_customer']))
            $filter .= ($filter ? " AND" : "") . "customer_id = " . (int)$data['filter_customer'];
        if (!empty($data['filter_order_status']))
            $filter .= ($filter ? " AND" : "") . " roh1.status = " . (int)$data['filter_order_status'];
        if (!empty($data['filter_order_item_status']))
            $filter .= ($filter ? " AND" : "") . " roih1.status = " . (int)$data['filter_order_item_status'];
        if (!empty($data['filterOrderId']))
            $filter .= ($filter ? " AND" : "") . " order_item_id = " . (int)$data['filterOrderId'];

        return $filter;
    }

    private function buildLimitString($data = array())
    {
        $limit = "";
        if (isset($data['start']) || isset($data['limit']))
        {
            if ($data['start'] < 0)
                $data['start'] = 0;

            if ($data['limit'] < 1)
                $data['limit'] = 20;

            $limit = (int)$data['start'] . "," . (int)$data['limit'];
        }
        return $limit;
    }

    private function buildSortString($data = array())
    {
        $sort = "";
        $sort_data = array(
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data))
            $sort = $data['sort'];

        if (isset($data['order']) && ($data['order'] == 'DESC'))
            $sort .= " DESC";

        return $sort;
    }

    public function deleteOrder($orderId)
    {
        OrderItemDAO::getInstance()->deleteOrderItem($orderId);
    }

    public function getOrders($data = array(), $sort = "", $limit = "")
    {
//        $query = $this->db->query("
//            SELECT
//                *,
//                roi.items AS items, roi.subtotal AS subtotal, roi.total AS total
//            FROM
//                repurchase_orders as ro
//                JOIN (
//                    SELECT
//                        repurchase_order_id,
//                        COUNT(*) AS items,
//                        SUM(subtotal) AS subtotal,
//                        SUM(total) AS total
//                    FROM repurchase_order_items
//                    GROUP BY repurchase_order_id
//                    ) as roi on roi.repurchase_order_id = ro.repurchase_order_id
//				JOIN (
//				    SELECT repurchase_order_id, status
//                    FROM (
//                        SELECT repurchase_order_id, roh.status, workflow_order
//                        FROM
//                            repurchase_orders_history as roh
//                            JOIN repurchase_order_statuses as ros on roh.status = ros.repurchase_order_status_id
//                        ORDER BY repurchase_order_id, workflow_order DESC
//                        ) as statuses
//                    GROUP BY repurchase_order_id
//                    ) as roh1 on ro.repurchase_order_id = roh1.repurchase_order_id
//			" . ($filter ? "WHERE $filter" : "") . "
//			" . ($sort ? "ORDER BY $sort" : "") . "
//			" . ($limit ? "LIMIT $limit" : "")
//        );
        $data['filterProductId'] = REPURCHASE_ORDER_PRODUCT_ID;
        $data['filterOrderItemId'] = $data['filterOrderId'];
        $repurchaseOrderItems = OrderItemDAO::getInstance()->getOrderItems($data);
        $items = array();
        foreach ($repurchaseOrderItems as $repurchaseOrderItem)
        {
            $options = OrderItemDAO::getInstance()->getOrderItemOptions($repurchaseOrderItem['order_item_id']);
//            $this->log->write(print_r($options, true));
            $items[] = array(
                'comment' => !empty($repurchaseOrderItem['public_comment'])
                    ? $repurchaseOrderItem['public_comment']
                    : (!empty($options[REPURCHASE_ORDER_COMMENT_OPTION_ID])
                        ? $options[REPURCHASE_ORDER_COMMENT_OPTION_ID]['value'] : ''),
                'orderItemId' => $repurchaseOrderItem['order_item_id'],
                'customerName' => $repurchaseOrderItem['customer_name'],
                'customerNick' => $repurchaseOrderItem['customer_nick'],
                'imagePath' => !empty($options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID])
                    ? $options[REPURCHASE_ORDER_IMAGE_URL_OPTION_ID]['value'] : '',
                'itemUrl' => $options[REPURCHASE_ORDER_ITEM_URL_OPTION_ID]['value'],
                'orderItemStatusId' => $repurchaseOrderItem['status'],
                'price' => $repurchaseOrderItem['price'],
                'quantity' => $repurchaseOrderItem['quantity'],
                'shipping' => $repurchaseOrderItem['shipping'],
                'status' => $repurchaseOrderItem['status'] >> 16 == GROUP_REPURCHASE_ORDER_ITEM_STATUS
                    ? $repurchaseOrderItem['status'] : REPURCHASE_ORDER_ITEM_STATUS_WAITING,
                'subtotal' => $repurchaseOrderItem['price'] * $repurchaseOrderItem['quantity'],
                'timeAdded' => $repurchaseOrderItem['date_added'],
                'total' => $repurchaseOrderItem['total']
            );
        }
//        return $query->rows;
        return $items;
    }

    public function getOrdersCount($data)
    {
        $data['filterProductId'] = REPURCHASE_ORDER_PRODUCT_ID;
        return OrderItemDAO::getInstance()->getOrderItemsCount($data);
    }

    public function setStatus($orderId, $statusId)
    {
        $this->log->write($statusId);
        OrderItemDAO::getInstance()->setOrderItemStatus($orderId, $statusId);
    }
}
