<?php
namespace model\sale;

use model\catalog\Supplier;
use model\catalog\SupplierDAO;
use model\core\Currency;
use model\core\CurrencyDAO;

class OrderItem {
    /** @var \Registry */
    private $registry;
    private $affiliateId;
    private $affiliateTransactionAmount;
    private $affiliateTransactionId;
    protected $downloadData;
    private $id;
    private $koreanName;
    private $orderId;
    protected $product;
    private $productId;
    private $name;
    private $model;
    protected $options;
    private $quantity;
    private $price;
    private $whiteprice;
    private $total;
    private $statusId;
    private $timeCreated;
    private $timeModified;
    private $privateComment;
    private $publicComment;
    private $shippingCost;
    private $customer;
    private $customerName;
    private $customerId;
    private $customerNick;
    /** @var Supplier */
    private $supplier;
    private $supplierId;
    private $supplierUrl;
    private $imagePath;
    private $weight;
    private $weightClassId;
    private $supplierName;
    private $supplierGroupId;
    private $internalModel;
    private $statusDate;
    /** @var Currency */
    private $currency;

    /**
     * @param \Registry $registry
     * @param int $affiliateId
     * @param int $affiliateTransactionId
     * @param string $privateComment
     * @param int $customerId
     * @param string $customerName
     * @param string $customerNick
     * @param int $id
     * @param string $imagePath
     * @param string $internalModel
     * @param string $koreanName
     * @param string $model
     * @param string $name
     * @param int $orderId
     * @param float $price
     * @param float $whiteprice
     * @param int $productId
     * @param string $publicComment
     * @param int $quantity
     * @param float $shippingCost
     * @param string $statusDate
     * @param int $statusId
     * @param int $supplierGroupId
     * @param int $supplierId
     * @param string $supplierName
     * @param string $supplierUrl
     * @param float $total
     * @param float $weight
     * @param int $weightClassId
     */
    function __construct($registry, $affiliateId, $affiliateTransactionId, $privateComment, $customerId, $customerName,
                         $customerNick, $id, $imagePath, $internalModel, $koreanName, $model, $name, $orderId, $price, $whiteprice, $productId,
                         $publicComment, $quantity, $shippingCost, $statusDate, $statusId, $supplierGroupId,
                         $supplierId, $supplierName, $supplierUrl, $total, $weight, $weightClassId) {
        $this->registry = $registry;
        $this->affiliateId = $affiliateId;
        $this->affiliateTransactionId = $affiliateTransactionId;
        $this->privateComment = $privateComment;
        $this->customerId = $customerId;
        $this->customerName = $customerName;
        $this->customerNick = $customerNick;
        $this->id = $id;
        $this->imagePath = $imagePath;
        $this->internalModel = $internalModel;
        $this->koreanName = $koreanName;
        $this->model = $model;
        $this->name = $name;
        $this->orderId = $orderId;
        $this->price = $price;
        $this->whiteprice = $whiteprice;
        $this->productId = $productId;
        $this->publicComment = $publicComment;
        $this->quantity = $quantity;
        $this->shippingCost = $shippingCost;
        $this->statusDate = $statusDate;
        $this->statusId = $statusId;
        $this->supplierGroupId = $supplierGroupId;
        $this->supplierId = $supplierId;
        $this->supplierName = $supplierName;
        $this->supplierUrl = $supplierUrl;
//        $this->timeCreated = $timeCreated;
//        $this->timeModified = $timeModified;
        $this->total = $total;
        $this->weight = $weight;
        $this->weightClassId = $weightClassId;
    }

    /**
     * @return int
     */
    public function getAffiliateId()
    {
        return $this->affiliateId;
    }

    /**
     * @return float
     */
    public function getAffiliateTransactionAmount() {
        return $this->affiliateTransactionAmount;
    }

    /**
     * @param float $value
     */
    public function setAffiliateTransactionAmount($value) {
        $this->affiliateTransactionAmount = $value;
    }

    /**
     * @return int
     */
    public function getAffiliateTransactionId()
    {
        return $this->affiliateTransactionId;
    }

    /**
     * @return string
     */
    public function getPrivateComment() {
        return $this->privateComment;
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
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return string
     */
    public function getCustomerName() {
        return $this->customerName;
    }

    /**
     * @return string
     */
    public function getCustomerNick()
    {
        return $this->customerNick;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getImagePath() {
        return $this->imagePath;
    }

    /**
     * @param string $value
     */
    public function setImagePath($value) {
        $this->imagePath = $value;
    }

    /**
     * @return string
     */
    public function getInternalModel()
    {
        return $this->internalModel;
    }

    /**
     * @return string
     */
    public function getKoreanName() {
        return $this->koreanName;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param bool $customerCurrency
     * @return float
     */
    public function getPrice($customerCurrency = false) {
        if ($customerCurrency) {
            return $this->price * $this->getCurrency()->getRate($this->getTimeCreated());
        } else {
            return $this->price;
        }
    }

    public function getWhitePrice($customerCurrency = false) {
        if ($customerCurrency) {
            return $this->whiteprice * $this->getCurrency()->getRate($this->getTimeCreated());
        } else {
            return $this->whiteprice;
        }
    }
    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @return string
     */
    public function getPublicComment()
    {
        return $this->publicComment;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param bool $customerCurrency
     * @return float
     */
    public function getShippingCost($customerCurrency = false) {
        if ($customerCurrency) {
            return $this->shippingCost * $this->getCurrency()->getRate($this->getTimeCreated());
        } else {
            return $this->shippingCost;
        }
    }

    /**
     * @param float $value
     */
    public function setShippingCost($value) {
        $this->shippingCost = $value;
    }

    /**
     * @return string
     */
    public function getStatusDate()
    {
        return $this->statusDate;
    }

    /**
     * @return int
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * @return Supplier
     */
    public function getSupplier() {
        if (!isset($this->supplier)) {
            $this->supplier = SupplierDAO::getInstance()->getSupplier($this->supplierId, true);
        }
        return $this->supplier;
    }

    /**
     * @return int
     */
    public function getSupplierGroupId()
    {
        return $this->supplierGroupId;
    }

    /**
     * @return int
     */
    public function getSupplierId()
    {
        return $this->supplierId;
    }

    /**
     * @return string
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * @return string
     */
    public function getSupplierUrl() {
        return $this->supplierUrl;
    }

    /**
     * @return string
     */
    public function getTimeCreated() {
        if (!isset($this->timeCreated)) {
            $this->timeCreated = OrderItemDAO::getInstance()->getTimeCreated($this->id);
        }
        return $this->timeCreated;
    }

    /**
     * @return string
     */
    public function getTimeModified() {
        if (!isset($this->timeModified)) {
            $this->timeModified = OrderItemDAO::getInstance()->getTimeModified($this->id);
        }
        return $this->timeModified;
    }

    /**
     * @param bool $customerCurrency
     * @return float
     */
    public function getTotal($customerCurrency = false) {
        if ($customerCurrency) {
            return $this->total * $this->getCurrency()->getRate($this->getTimeCreated());
        } else {
            return $this->total;
        }
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return int
     */
    public function getWeightClassId()
    {
        return $this->weightClassId;
    }

    /**
     * @return Currency
     * @throws \Exception
     */
    public function getCurrency() {
        if (!isset($this->currency)) {
	        $temp = $this->getCustomer();
            $this->currency = CurrencyDAO::getInstance()->getCurrency($temp['base_currency_code']);
            if (is_null($this->currency)) {
                throw new \Exception("The customer [" . $temp['nickname'] . "] has no base currency set");
            }
        }
        return $this->currency;
    }
}