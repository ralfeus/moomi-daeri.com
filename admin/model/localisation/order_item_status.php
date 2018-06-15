<?php
use system\library\Status;

class ModelLocalisationOrderItemStatus extends \system\engine\Model
{
    public function getOrderItemStatus($order_item_status_id)
    {
        $order_item_status_data = $this->cache->get('order_item_status.' . (int)$this->config->get('config_language_id'));
        //$this->load->library("system\library\Status");
        return Status::getInstance($this->getRegistry())->getStatus($order_item_status_id, $this->config->get('config_language_id'));
        if (!$order_item_status_data)
            $order_item_status_data = $this->getOrderItemStatuses();
        foreach ($order_item_status_data as $order_item_status)
            if ($order_item_status['order_item_status_id'] == $order_item_status_id)
                return $order_item_status;
        return null;
    }

    public function getOrderItemStatuses($data = array())
    {
        if ($data) {
        } else {
            $order_item_status_data = $this->cache->get('order_item_status.' . (int)$this->config->get('config_language_id'));

            if (!$order_item_status_data)
            {
                $query = $this->db->query("
                    SELECT * FROM order_item_status
                    ORDER BY workflow_order
                ");

                $order_item_status_data = $query->rows;
                $this->cache->set('order_item_status.' . (int)$this->config->get('config_language_id'), $order_item_status_data);
            }
        }
        //print_r($order_item_status_data);exit();
        return $order_item_status_data;
    }
	
	public function getOrderItemStatusName($order_item_status_id)
	{
		$orderItemStatus = $this->getOrderItemStatus($order_item_status_id);
		return $orderItemStatus['name'];
	}
}
