<?php
namespace model\sale;

class Customer {
    private $id;
    private $storeId;
    private $firstName;
    private $lastName;
    private $nickName;
    private $eMail;
    private $phone;
    private $fax;
    private $password;
    private $cart;
    private $wishlist;
    private $newsletter;
    private $addressId;
    private $customerGroup;
    private $customerGroupId;
    private $ip;
    private $status;
    private $approved;
    private $token;
    private $dateCreated;
    private $baseCurrencyCode;
    private $balance;

    function __construct($addressId, $approved, $balance, $baseCurrencyCode, $cart, $customerGroupId, $dateCreated,
                         $eMail, $fax, $firstName, $id, $ip, $lastName, $newsletter, $nickName, $password, $phone,
                         $status, $storeId, $token, $wishlist) {
        $this->addressId = $addressId;
        $this->approved = $approved;
        $this->balance = $balance;
        $this->baseCurrencyCode = $baseCurrencyCode;
        $this->cart = $cart;
        $this->customerGroupId = $customerGroupId;
        $this->dateCreated = $dateCreated;
        $this->eMail = $eMail;
        $this->fax = $fax;
        $this->firstName = $firstName;
        $this->id = $id;
        $this->ip = $ip;
        $this->lastName = $lastName;
        $this->newsletter = $newsletter;
        $this->nickName = $nickName;
        $this->password = $password;
        $this->phone = $phone;
        $this->status = $status;
        $this->storeId = $storeId;
        $this->token = $token;
        $this->wishlist = $wishlist;
    }


    /**
     * @return mixed
     */
    public function getAddressId()
    {
        return $this->addressId;
    }

    /**
     * @return mixed
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * @return mixed
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @return mixed
     */
    public function getBaseCurrencyCode()
    {
        return $this->baseCurrencyCode;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return array
     */
    public function getCustomerGroup() {
        if (!isset($this->customerGroup)) {
            $this->customerGroup = CustomerGroupDAO::getInstance()->getCustomerGroup($this->customerGroupId);
        }
        return $this->customerGroup;
    }

    /**
     * @return mixed
     */
    public function getCustomerGroupId()
    {
        return $this->customerGroupId;
    }

    /**
     * @return mixed
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @return mixed
     */
    public function getEMail()
    {
        return $this->eMail;
    }

    /**
     * @return mixed
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return mixed
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @return mixed
     */
    public function getNickName()
    {
        return $this->nickName;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getWishlist()
    {
        return $this->wishlist;
    }
}