<?php
use model\sale\OrderItemDAO;

class ModelSaleOrderItemHistory extends  Model
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->load->model('localisation/order_item_status');
    }

    public function addEntry($order_item_id, $order_item_status_id)
    {
        if (OrderItemDAO::getInstance()->getOrderItem($order_item_id) &&
            Status::getStatus($order_item_id, $this->config->get('language_id')))
            $this->db->query("
                INSERT INTO order_item_history
                SET
                    order_item_id = " . (int)$order_item_id . ",
                    order_item_status_id = " . (int)$order_item_status_id . ",
                    date_added = DATE(NOW())"
            );
        else
            trigger_error("No order item or status found");
    }

    public function getOrderItemHistory($order_item_id)
    {
        $query = $this->db->query("
            SELECT *
            FROM order_item_history
            WHERE order_item_id = " . (int)$order_item_id
        );
        return $query->rows;
    }

    public function getOrderItemStatusDate($order_item_id, $order_item_status_id)
    {
        foreach ($this->getOrderItemHistory($order_item_id) as $order_item_status_event)
            if ($order_item_status_event['order_item_status_id'] == $order_item_status_id)
                return $order_item_status_event['date_added'];
        return null;
    }
}