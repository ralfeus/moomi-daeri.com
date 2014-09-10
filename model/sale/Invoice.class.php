<?php
namespace model\sale;

class Invoice {
    private $id;
    private $customer;
    private $customerId;
    private $shippingAddressId;
    private $shippingMethod;
    private $statusId;
    private $weight;
    private $shippingCost;
    private $subtotal;
    private $discount;
    private $total;
    private $totalCustomerCurrency;
    private $currencyCode;
    private $timeModified;
    private $comment;
    private $packageNumber;
    private $shippingDate;
    private $orderItems;

    /**
     * @param \Registry $registry
     * @param $comment
     * @param $customerCurrencyCode
     * @param $customerId
     * @param $discount
     * @param $id
     * @param $invoiceStatusId
     * @param $packageNumber
     * @param $shippingAddressId
     * @param $shippingCost
     * @param $shippingDate
     * @param $shippingMethod
     * @param $subtotal
     * @param $timeModified
     * @param $total
     * @param $totalCustomerCurrency
     * @param $weight
     */
    public function __construct(
        $registry, $comment, $customerCurrencyCode, $customerId, $discount, $id, $invoiceStatusId, $packageNumber,
        $shippingAddressId, $shippingCost, $shippingDate, $shippingMethod, $subtotal, $timeModified, $total,
        $totalCustomerCurrency, $weight) {
        $this->comment = $comment;
        $this->currencyCode = $customerCurrencyCode;
        $this->customerId = $customerId;
        $this->discount = $discount;
        $this->id = $id;
        $this->statusId = $invoiceStatusId;
        $this->packageNumber = $packageNumber;
        $this->shippingAddressId = $shippingAddressId;
        $this->shippingCost = $shippingCost;
        $this->shippingDate = $shippingDate;
        $this->shippingMethod = $shippingMethod;
        $this->subtotal = $subtotal;
        $this->timeModified = $timeModified;
        $this->total = $total;
        $this->totalCustomerCurrency = $totalCustomerCurrency;
        $this->weight = $weight;
    }


    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return array
     */
    public function getCustomer() {
        if (!isset($this->customer)) {
            $this->customer = CustomerDAO::getInstance()->getCustomer($this->customerId);
        }
        return $this->customer;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * @return string
     */
    public function getPackageNumber()
    {
        return $this->packageNumber;
    }

    /**
     * @return int
     */
    public function getShippingAddressId()
    {
        return $this->shippingAddressId;
    }

    /**
     * @return float
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * @return string
     */
    public function getShippingDate()
    {
        return $this->shippingDate;
    }

    /**
     * @return string
     */
    public function getShippingMethod()
    {
        return $this->shippingMethod;
    }

    /**
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * @return string
     */
    public function getTimeModified()
    {
        return $this->timeModified;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @return float
     */
    public function getTotalCustomerCurrency()
    {
        return $this->totalCustomerCurrency;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return OrderItem[]
     */
    public function getOrderItems() {
        if (!isset($this->orderItems)) {
            $this->orderItems = array();
            foreach (InvoiceDAO::getInstance()->getInvoiceItems($this->id) as $invoiceItem) {
                if ($orderItem = OrderItemDAO::getInstance()->getOrderItem($invoiceItem['order_item_id'])) {
                    $this->orderItems[] = $orderItem;
                }
            }
        }
        return $this->orderItems;
    }

}