<?php
namespace model\sale;

use model\DAO;
use model\shipping\ShippingMethodDAO;

class InvoiceDAO extends DAO {
    /**
     * @param int $orderId
     * @param OrderItem[] $orderItems
     * @param mixed $shippingMethod
     * @param float $weight
     * @param float $discount
     * @param string $comment
     * @param string $shippingDate
     * @return int
     * @throws \Exception
     */
    public function addInvoice($orderId, $orderItems, $shippingMethod = null, $weight = 0, $discount = 0, $comment = "", $shippingDate = '') {
        /** @var \ModelSaleOrder $orderModel */
        $orderModel = $this->load->model('sale/order');
        /// Get customer and shipping data from the primary order
        $order = $orderModel->getOrder($orderId);
        $customer = CustomerDAO::getInstance()->getCustomer($order['customer_id']);

        /// Calculate total weight and price in shop currency and customer currency
        $tmpWeight = 0; $tmpSubtotal = 0; $subtotalCustomerCurrency = 0;
        foreach ($orderItems as $orderItem) {
            /// If weight isn't defined by administrator it's calculated
            if (!$weight)
                $tmpWeight += $this->weight->format($orderItem->getWeight(), $this->config->get('config_weight_class_id')) * $orderItem->getQuantity();
            $itemCost = $orderItem->getPrice() * $orderItem->getQuantity() + $orderItem->getShippingCost();
            $tmpSubtotal += $itemCost;
            $subtotalCustomerCurrency += $orderItem->getTotal(true);
        }
        $subtotal = $tmpSubtotal;
        /// It seems to originate from time when subtotal could be edited manually.
        /// Now it's done by specifying discount (I hope)
//        if (!isset($subtotal)) {
//            $subtotal = $tmpSubtotal;
//        } else {
//            $subtotalCustCurr = $this->getCurrency()->convert(
//                $subtotal,
//                $this->config->get('config_currency'),
//                $customer['base_currency_code']
//            );
//        }
        if (!$weight) {
            $weight = $tmpWeight;
        }
        /// Get shipping cost according to destination and order items
        /// The shipping cost calculation can take different order items factors into account
        /// Therefore it's better to pass whole items and let shipping calculation classes use it
        if (empty($shippingMethod))
            $shippingMethod = $order['shipping_method'];
        $shippingMethodComponents = explode('.', $shippingMethod);
        $shippingCost = ShippingMethodDAO::getInstance()->getMethod($shippingMethodComponents[0])->getCost(
            $shippingMethodComponents[1],
            $orderItems,
            ['weight' => $weight]
        );

        /// Calculate total. Currently it's subtotal, shipping and discount. In the future it can be something else
        $total = $subtotal + $shippingCost - $discount;
        $totalCustomerCurrency = $subtotalCustomerCurrency + $this->getCurrency()->convert(
            $shippingCost - $discount,
            $this->config->get('config_currency'),
            $customer['base_currency_code']
        );

        /// Add invoice record to the database
        $this->getDb()->query("
            INSERT INTO invoices
            SET
                customer_id = :customerId,
                comment = :comment,
                discount = :discount,
                shipping_address_id = :shippingAddressId,
                shipping_method = :shippingMethod,
                shipping_date = :shippingDate,
                shipping_cost = :shippingCost,
                subtotal = :subtotal,
                time_modified = NOW(),
                total = :total,
                total_customer_currency = :totalCustomerCurrency,
                currency_code = :currencyCode,
                weight = :weight
            ", array(
                ':customerId' => $order['customer_id'],
                ':comment' => $comment,
                ':discount' => $discount,
                ':shippingAddressId' => $orderModel->getShippingAddressId($orderId),
                ':shippingMethod' => $shippingMethod,
                ':shippingDate' => $shippingDate,
                ':shippingCost' => $shippingCost,
                ':subtotal' => $subtotal,
                ':total' => $total,
                ':totalCustomerCurrency' => $totalCustomerCurrency,
                ':currencyCode' => $customer['base_currency_code'],
                ':weight' => $weight
            )
        );

        /// Add invoice items
        $invoiceId = $this->getDb()->getLastId();
        $this->addInvoiceItems($invoiceId, $orderItems);
        return $invoiceId;
    }

    /**
     * @param int $invoiceId
     * @param OrderItem[] $orderItems
     */
    private function addInvoiceItems($invoiceId, $orderItems) {
        $query = "
            INSERT INTO invoice_items
            (invoice_id, order_item_id)
            VALUES
        ";

        foreach ($orderItems as $orderItem)
            $query .= "($invoiceId, " . $orderItem->getId() . "),\n";

        //$this->log->write(substr($query, 0, strlen($query) - 2));
        $this->getDb()->query(substr($query, 0, strlen($query) - 2));
    }

    private function buildFilterString($data) {
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
        $this->db->query("DELETE FROM invoice_items WHERE invoice_id = " . (int)$invoiceId);
        $this->db->query("DELETE FROM invoices WHERE invoice_id = " . (int)$invoiceId);
    }

    /**
     * @param int $invoiceId
     * @return \model\sale\Invoice
     */
    public function getInvoice($invoiceId) {
        $query = $this->getDb()->query("SELECT * FROM invoices WHERE invoice_id = " . (int)$invoiceId);
        if ($query->num_rows)
            return new \model\sale\Invoice($this->registry,
                $query->row['comment'], $query->row['currency_code'], $query->row['customer_id'], $query->row['discount'], $query->row['invoice_id'],
                $query->row['invoice_status_id'], $query->row['package_number'], $query->row['shipping_address_id'], $query->row['shipping_cost'],
                $query->row['shipping_date'], $query->row['shipping_method'], $query->row['subtotal'], $query->row['time_modified'], $query->row['total'],
                $query->row['total_customer_currency'], $query->row['weight']);
        else
            return null;
    }

    /**
     * @param int $customerId
     * @return \model\sale\Invoice[]
     */
    public function getInvoicesByCustomerId($customerId) {
        return $this->getInvoices(array("filterCustomerId" => array($customerId)));
    }

    /**
     * @param int $orderItemId
     * @return \model\sale\Invoice[]
     */
    public function getInvoicesByOrderItem($orderItemId)
    {
        $query = $this->db->query("
            SELECT invoice_id
            FROM invoice_items
            WHERE order_item_id = " . (int)$orderItemId
        );
        //$this->log->write(print_r($query, true));
        if (!$query->num_rows)
            return array();
        else {
            foreach ($query->rows as $row)
                $data['filterInvoiceId'][] = $row['invoice_id'];
//            $this->log->write(print_r($this->buildFilter($data), true));
            return $this->getInvoices($data);
        }
    }

    /**
     * @param int $invoiceId
     * @return array
     */
    public function getInvoiceItems($invoiceId) {
        $query = $this->getDb()->query("SELECT * FROM invoice_items WHERE invoice_id = " . (int)$invoiceId);
        if ($query->num_rows)
            return $query->rows;
        else
            return array();
    }

    /**
     * @param int $invoiceId
     * @return int
     */
    public function getInvoiceItemsCount($invoiceId) {
        $query = $this->getDb()->query("
            SELECT COUNT(*) as total
            FROM invoice_items
            WHERE invoice_id = " . (int)$invoiceId
        );
        return (int)$query->row['total'];
    }

    /**
     * @param array $data
     * @param string $orderBy
     * @return \model\sale\Invoice[]
     */
    public function getInvoices($data, $orderBy = 'time_modified DESC') {
        $filter = $this->buildFilterString($data);
        $query = $this->getDb()->query("
            SELECT *
            FROM
                invoices AS i
                JOIN customer AS c ON i.customer_id = c.customer_id
            " . ($filter ? "WHERE $filter" : "") . "
            ORDER BY " . $this->getDb()->escape($orderBy)
        );
        if ($query->num_rows) {
            $result = array();
            foreach ($query->rows as $row) {
                $result[] = new \model\sale\Invoice($this->registry,
                    $row['comment'], $row['currency_code'], $row['customer_id'], $row['discount'], $row['invoice_id'],
                    $row['invoice_status_id'], $row['package_number'], $row['shipping_address_id'], $row['shipping_cost'],
                    $row['shipping_date'], $row['shipping_method'], $row['subtotal'], $row['time_modified'], $row['total'],
                    $row['total_customer_currency'], $row['weight']);
            }
            return $result;
        } else
            return array();
    }

    public function setComment($invoiceId, $comment)
    {
        $this->setTextField($invoiceId, 'comment', $comment);
    }

    public function setDiscount($invoiceId, $discount)
    {
        $this->db->query("
            UPDATE invoices
            SET
                discount = " . (float)$discount . ",
                time_modified = NOW()
            WHERE invoice_id = " . (int)$invoiceId
        );
    }

    public function setInvoiceStatus($invoiceId, $invoiceStatusId)
    {
        $this->db->query("
            UPDATE invoices
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

    public function setShippingDate($invoiceId, $shippingDate)
    {
        $query = "UPDATE invoices SET shipping_date = '" . $shippingDate . "' WHERE invoice_id = " . (int)$invoiceId;
        $this->db->query($query);
    }

    private function setTextField($invoiceId, $field, $data)
    {
        $this->db->query("
            UPDATE invoices
            SET
                $field = '" . $this->db->escape($data) . "'
            WHERE invoice_id = " . (int)$invoiceId
        );
    }
}