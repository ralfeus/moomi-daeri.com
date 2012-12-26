<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 18.7.12
 * Time: 18:25
 * To change this template use File | Settings | File Templates.
 */
class ModelSaleInvoice extends Model
{
    public function addInvoice($orderId, $order_items, $weight = 0, $discount = 0, $comment = "", $subtotal = 0)
    {
        $orderModel = $this->load->model('sale/order');
        /// Get customer and shipping data from the primary order
        $order = $orderModel->getOrder($orderId);

        /// Calculate total weight and price
        $tmpWeight = 0; $tmpSubtotal = 0;
        foreach ($order_items as $order_item)
        {
            /// If weight isn't defined by administrator it's calculated
            if (!$weight)
                $tmpWeight += $this->weight->format($order_item['weight'], $this->config->get('config_weight_class_id')) * $order_item['quantity'];
            $tmpSubtotal += $order_item['price'] * $order_item['quantity'];
        }
        if (!$subtotal)
            $subtotal = $tmpSubtotal;
        if (!$weight)
            $weight = $tmpWeight;

        /// Get shipping cost according to destination and order items
        /// The shipping cost calculation can take different order items factors into account
        /// Therefore it's better to pass whole items and let shipping calculation classes use it
        $shippingCost = Shipping::getCost($order_items, $order['shipping_method'], $this->registry);

        /// Calculate total. Currently it's subtotal, shipping and discount. In the future it can be something else
        $total = $subtotal + $shippingCost - $discount;

        /// Add invoice record to the database
        $query = "
            INSERT INTO " . DB_PREFIX . "invoices
            SET
                customer_id = " . (int)$order['customer_id'] . ",
                comment = '" . $this->db->escape($comment) . "',
                discount = " . (float)$discount . ",
                shipping_address_id = " . $orderModel->getShippingAddressId($orderId) . ",
                shipping_method = '" . $order['shipping_method'] . "',
                shipping_cost = $shippingCost,
                subtotal = $subtotal,
                time_modified = NOW(),
                total = $total,
                weight = " . (float)$weight
        ;
//        $this->log->write($query);
        $this->db->query($query);
        /// Add invoice items
        $invoiceId = $this->db->getLastId();
        $this->addInvoiceItems($invoiceId, $order_items);
        return $invoiceId;
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
        $this->db->query(substr($query, 0, strlen($query) - 2));
    }

    private function buildFilterString($data)
    {
        $filter = "";
        if (!empty($data['filterCustomerId']))
            $filter .= ($filter ? " AND" : "") . " i.customer_id IN (" . implode(', ', $data['filterCustomerId']) . ")";
        if (!empty($data['filterInvoiceId']))
        $filter .= ($filter ? " AND" : "") . " i.invoice_id IN (" . implode(', ', $data['filterInvoiceId']) . ")";
        if (!empty($data['filterInvoiceStatusId']))
            $filter .= ($filter ? " AND" : "") . " i.invoice_status_id IN (" . implode(', ', $data['filterInvoiceStatusId']) . ")";

        return $filter;
    }

    public function deleteInvoice($invoiceId)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "invoice_items WHERE invoice_id = " . (int)$invoiceId);
        $this->db->query("DELETE FROM " . DB_PREFIX . "invoices WHERE invoice_id = " . (int)$invoiceId);
    }

    public function getInvoice($invoiceId)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "invoices WHERE invoice_id = " . (int)$invoiceId);
        if ($query->num_rows)
            return $query->row;
        else
            return null;
    }

    public function getInvoicesByCustomerId($customerId)
    {
        return  $this->getInvoices(array("filterCustomerId" => array($customerId)));
    }

    public function getInvoicesByOrderItem($orderItemId)
    {
        $query = $this->db->query("
            SELECT invoice_id
            FROM " . DB_PREFIX . "invoice_items
            WHERE order_item_id = " . (int)$orderItemId
        );
        //$this->log->write(print_r($query, true));
        if (!$query->num_rows)
            return null;
        else
        {
            foreach ($query->rows as $row)
                $data['filterInvoiceId'][] = $row['invoice_id'];
//            $this->log->write(print_r($this->buildFilterString($data), true));
            return $this->getInvoices($data);
        }
    }

    public function getInvoiceItems($invoiceId)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "invoice_items WHERE invoice_id = " . (int)$invoiceId);
        if ($query->num_rows)
            return $query->rows;
        else
            return null;
    }

    public function getInvoices($data)
    {
        $filter = $this->buildFilterString($data);
        $query = $this->db->query("
            SELECT *
            FROM
                " . DB_PREFIX . "invoices AS i
                JOIN " . DB_PREFIX . "customer AS c ON i.customer_id = c.customer_id
            " . ($filter ? "WHERE $filter" : "") . "
            ORDER BY time_modified DESC
        ");
        if ($query->num_rows)
            return $query->rows;
        else
            return null;
    }

    public function setComment($invoiceId, $comment)
    {
        $this->setTextField($invoiceId, 'comment', $comment);
    }

    public function setDiscount($invoiceId, $discount)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "invoices
            SET
                discount = " . (float)$discount . ",
                time_modified = NOW()
            WHERE invoice_id = " . (int)$invoiceId
        );
    }

    public function setInvoiceStatus($invoiceId, $invoiceStatusId)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "invoices
            SET
                invoice_status_id = " . (int)$invoiceStatusId . ",
                time_modified = NOW()
            WHERE invoice_id = " . (int)$invoiceId
        );
    }

    public function setPackageNumber($invoiceId, $packageNumber)
    {
        $this->setTextField($invoiceId, 'package_number', $packageNumber);
    }

    private function setTextField($invoiceId, $field, $data)
    {
        $this->db->query("
            UPDATE " . DB_PREFIX . "invoices
            SET
                $field = '" . $this->db->escape($data) . "',
                time_modified = NOW()
            WHERE invoice_id = " . (int)$invoiceId
        );
    }
}
