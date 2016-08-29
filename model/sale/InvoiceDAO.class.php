<?php
namespace model\sale;

use model\DAO;
use model\shipping\ShippingMethodDAO;
use system\library\Filter;
use system\library\FilterTree;

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
     */
    public function addInvoice($orderId, $orderItems, $shippingMethod = null, $weight = 0.0, $discount = 0.0, $comment = "", $shippingDate = '') {
        /** @var \ModelSaleOrder $orderModel */
        $orderModel = $this->getLoader()->model('sale/order');
        /// Get customer and shipping data from the primary order
        $order = $orderModel->getOrder($orderId);
        $customer = CustomerDAO::getInstance()->getCustomer($order['customer_id']);

        /// Calculate total weight and price in shop currency and customer currency
        $tmpWeight = 0; $tmpSubtotal = 0; $subtotalCustomerCurrency = 0;
        foreach ($orderItems as $orderItem) {
            /// If weight isn't defined by administrator it's calculated
            if (!$weight)
                $tmpWeight += $this->weight->format($orderItem->getWeight(), $this->getConfig()->get('config_weight_class_id')) * $orderItem->getQuantity();
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
            $this->getConfig()->get('config_currency'),
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
        $this->getCache()->deleteAll('/^invoice/');
        return $invoiceId;
    }

    /**
     * @param int $invoiceId
     * @param OrderItem[] $orderItems
     */
    private function addInvoiceItems($invoiceId, $orderItems) {
        $query = /** @lang text */
            '
            INSERT INTO invoice_items
            (invoice_id, order_item_id)
            VALUES
        ';

        foreach ($orderItems as $orderItem)
            $query .= "($invoiceId, " . $orderItem->getId() . "),\n";

        //$this->log->write(substr($query, 0, strlen($query) - 2));
        $this->getDb()->query(substr($query, 0, strlen($query) - 2));
    }

    /**
     * @param FilterTree|Filter|array $data
     * @return Filter
     */
    public function buildFilter($data) {
        if ($data instanceof FilterTree) {
            return $data->buildFilter([$this, 'buildFilter']);
        } else if ($data instanceof Filter) {
            return $data;
        }
        $filter = new Filter();
        $tmp0 = $tmp1 = '';
        if (isset($data['filterCustomerId'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('i.customer_id', $data['filterCustomerId'], $tmp0, $tmp1));
        }
        if (isset($data['filterInvoiceId'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('i.invoice_id', $data['filterInvoiceId'], $tmp0, $tmp1));
        }
        if (isset($data['filterInvoiceStatusId'])) {
            $filter->addChunk($this->buildSimpleFieldFilterEntry('i.invoice_status_id', $data['filterInvoiceStatusId'], $tmp0, $tmp1));
        }
        return $filter;
    }

    public function deleteInvoice($invoiceId)
    {
        $this->getDb()->query("DELETE FROM invoice_items WHERE invoice_id = " . (int)$invoiceId);
        $this->getDb()->query("DELETE FROM invoices WHERE invoice_id = " . (int)$invoiceId);
        $this->getCache()->deleteAll('/^invoice/');
    }

    /**
     * @param int $invoiceId
     * @return \model\sale\Invoice
     */
    public function getInvoice($invoiceId) {
        $query = $this->getDb()->query("SELECT * FROM invoices WHERE invoice_id = " . (int)$invoiceId);
        if ($query->num_rows)
            return new Invoice($this->getRegistry(),
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
        $query = $this->getDb()->query("
            SELECT invoice_id
            FROM invoice_items
            WHERE order_item_id = " . (int)$orderItemId
        );
        //$this->log->write(print_r($query, true));
        if (!$query->num_rows)
            return array();
        else {
            $data['filterInvoiceId'] = [];
            foreach ($query->rows as $row)
                $data['filterInvoiceId'][] = $row['invoice_id'];
//            $this->log->write(print_r($this->buildFilter($data), true));
            return $this->getInvoices($data);
        }
    }

    /**
     * @param array $filter
     * @return array[]
     */
    public function getInvoiceCustomers($filter) {
        unset($filter['filterCustomerId']);
        $filter = $this->buildFilter($filter);
        $query = "
            SELECT customer_id
            FROM invoices AS i
        ";
        if ($filter->isFilterSet()) {
            $query .= $filter->getFilterString(true);
        }
        $query .= "GROUP BY customer_id";
        $result = [];
        foreach ($this->getDb()->query($query, $filter->getParams())->rows as $row) {
            $result[] = CustomerDAO::getInstance()->getCustomer($row['customer_id']);
        }
        return $result;
    }

    /**
     * @param int $invoiceId
     * @return array
     */
    public function getInvoiceItems($invoiceId) {
        $query = $this->getDb()->query("SELECT * FROM invoice_items WHERE invoice_id = :invoiceId", [':invoiceId' => $invoiceId]);
        if ($query->num_rows) {
            return $query->rows;
        } else {
            return array();
        }
    }

    /**
     * @param int $invoiceId
     * @return int
     */
    public function getInvoiceItemsCount($invoiceId) {
        return $this->getDb()->queryScalar("
            SELECT COUNT(*) as total
            FROM invoice_items
            WHERE invoice_id = :invoiceId
            ", [ ':invoiceId' => $invoiceId ]
        );
    }

    /**
     * @param FilterTree|Filter|array $filter
     * @param int $start
     * @param int $limit
     * @return \int[]
     */
    public function getInvoiceIds($filter, $start = null, $limit = null) {
        $cacheKey = 'invoice.' . md5(serialize([$filter, $start, $limit]));
        $result = $this->getCache()->get($cacheKey);
        if (!is_null($result)) {
            return $result;
        }
        $filter = $this->buildFilter($filter);
        $query = '
            SELECT invoice_id
            FROM invoices AS i
        ';
        if ($filter->isFilterSet()) {
            $query .= $filter->getFilterString(true);
        }
        $query .= '
            ORDER BY time_modified DESC
        ' . $this->buildLimitString($start, $limit);
        $result = array_map(
            function($row) {
                return $row['invoice_id'];
            },
            $this->getDb()->query($query, $filter->getParams())->rows
        );
        $this->getCache()->set($cacheKey, $result);
        return $result;
    }

    /**
     * @param array $data
     * @param string $orderBy
     * @param int $start
     * @param int $limit
     * @return Invoice[]
     */
    public function getInvoices($data, $orderBy = 'time_modified DESC', $start = null, $limit = null) {
        $result = [];
        foreach ($this->getInvoiceIds($data, $start, $limit) as $id) {
            $result[] = $this->getInvoice($id);
        }
        return $result;
    }

    public function getInvoicesCount($data) {
        return sizeof($this->getInvoiceIds($data));
    }

    public function setComment($invoiceId, $comment) {
        $this->setTextField($invoiceId, 'comment', $comment);
    }

    public function setDiscount($invoiceId, $discount) {
        $this->setTextField($invoiceId, 'discount', $discount);
    }

    public function setInvoiceStatus($invoiceId, $invoiceStatusId) {
        $this->setTextField($invoiceId, 'invoice_status_id', $invoiceStatusId);
    }

    public function setPackageNumber($invoiceId, $packageNumber) {
        $this->setTextField($invoiceId, 'package_number', $packageNumber);
    }

    public function setShippingDate($invoiceId, $shippingDate) {
        $this->setTextField($invoiceId, 'shipping_date', $shippingDate);
    }

    private function setTextField($invoiceId, $field, $data) {
        $this->getDb()->query("
            UPDATE invoices
            SET 
                $field = :data,
                time_modified = NOW()
            WHERE invoice_id = :invoiceId
            ", [ ':data' => $data, ':invoiceId' => $invoiceId]
        );
        $this->getCache()->deleteAll('/^invoice/');
    }
}