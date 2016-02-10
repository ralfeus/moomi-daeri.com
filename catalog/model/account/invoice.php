<?php
use model\shipping\ShippingMethodDAO;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 18.7.12
 * Time: 18:25
 * To change this template use File | Settings | File Templates.
 */
class ModelAccountInvoice extends Model
{
    public function addInvoice($orderId, $order_items, $weight = 0)
    {
        $orderModel = $this->load->model('sale/order');
        /// Get customer and shipping data from the primary order
        $order = $orderModel->getOrder($orderId);

        /// Calculate total weight and price
        $tmpWeight = 0; $subtotal = 0;
        foreach ($order_items as $order_item)
        {
            /// If weight isn't defined by administrator it's calculated
            if (!$weight)
                $tmpWeight += $this->weight->format($order_item['weight'], $this->config->get('config_weight_class_id')) * $order_item['quantity'];
            $subtotal += $order_item['price'] * $order_item['quantity'];
        }
        if (!$weight)
            $weight = $tmpWeight;

        /// Get shipping cost according to destination and weight
        $shippingCost =  ShippingMethodDAO::getInstance()->getMethod(explode('.', $order['shipping_method'])[0])->
            getCost(explode('.', $order['shipping_method'])[1], $order_items, ['weight' => $weight]);

        /// Calculate total. Currently it's just subtotal and shipping. In the future it can be something else
        $total = $subtotal + $shippingCost;

        /// Add invoice record to the database
        $this->getDb()->query("
            INSERT INTO " . DB_PREFIX . "invoices
            SET
                customer_id = " . (int)$order['customer_id'] . ",
                date_added = NOW(),
                shipping_address_id = " . $orderModel->getShippingAddressId($orderId) . ",
                shipping_method = '" . $order['shipping_method'] . "'',
                shipping_cost = $shippingCost,
                subtotal = $subtotal,
                total = $total,
                weight = " . (float)$weight
        );
        /// Add invoice items
        $this->addInvoiceItems($this->getDb()->getLastId(), $order_items);
    }

    private function addInvoiceItems($invoiceId, $order_items)
    {
        $query = "
            INSERT INTO " . DB_PREFIX . "invoice_items
            (invoice_id, order_item_id)
            VALUES
        ";
        foreach ($order_items as $order_item)
            $query .= "($invoiceId, " . (int)$order_item['order_product_id'] . "),\n";

        //$this->log->write(substr($query, 0, strlen($query) - 2));
        $this->getDb()->query(substr($query, 0, strlen($query) - 2));
    }

    public function getInvoice($invoiceId)
    {
        $query = $this->getDb()->query("SELECT * FROM " . DB_PREFIX . "invoices WHERE invoice_id = " . (int)$invoiceId);
        if ($query->num_rows)
            return $query->row;
        else
            return null;
    }

    public function getInvoiceItems($invoiceId)
    {
        $query = $this->getDb()->query("SELECT * FROM " . DB_PREFIX . "invoice_items WHERE invoice_id = " . (int)$invoiceId);
        if ($query->num_rows)
            return $query->rows;
        else
            return null;
    }

    public function getInvoiceItemsCount($invoiceId)
    {
        $query = $this->getDb()->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "invoice_items WHERE invoice_id = " . (int)$invoiceId);
        return $query->row['total'];
    }

    public function getInvoices($customerId, $orderBy = '')
    {
      $arrDebug = debug_backtrace();

      $sql = "SELECT * FROM " . DB_PREFIX . "invoices WHERE customer_id = " . (int)$customerId;
      if($orderBy != '') {
        $sql .= " ORDER BY invoice_id " . $orderBy;
      }

      $query = $this->getDb()->query($sql);

      if ($query->num_rows)
        return $query->rows;
      else
        return null;
    }
}
