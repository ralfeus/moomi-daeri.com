<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 4/3/2016
 * Time: 9:52 PM
 */

namespace model\sale;


class Coupon {
    private $id;
    private $appliesToCategories;
    private $code;
    private $dateAdded;
    private $dateEnd;
    private $dateStart;
    private $discount;
    private $enabled;
    private $logged;
    private $name;
    private $shipping;
    private $total;
    private $type;
    private $usesCustomer;
    private $usesTotal;

    /**
     * Coupon constructor.
     * @param $id
     * @param $appliesToCategories
     * @param $code
     * @param $dateAdded
     * @param $dateEnd
     * @param $dateStart
     * @param $discount
     * @param $enabled
     * @param $logged
     * @param $name
     * @param $shipping
     * @param $total
     * @param $type
     * @param $usesCustomer
     * @param $usesTotal
     */
    public function __construct($id, $appliesToCategories = null, $code = null, $dateAdded = null, $dateEnd = null, $dateStart = null, $discount = null, 
                                $enabled = null, $logged = null, $name = null, $shipping = null, $total = null, $type = null, 
                                $usesCustomer = null, $usesTotal = null) {
        $this->id = $id;
        $this->appliesToCategories = $appliesToCategories;
        $this->code = $code;
        $this->dateAdded = $dateAdded;
        $this->dateEnd = $dateEnd;
        $this->dateStart = $dateStart;
        $this->discount = $discount;
        $this->enabled = $enabled;
        $this->logged = $logged;
        $this->name = $name;
        $this->shipping = $shipping;
        $this->total = $total;
        $this->type = $type;
        $this->usesCustomer = $usesCustomer;
        $this->usesTotal = $usesTotal;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function isAppliesToCategories() {
        if (is_null($this->appliesToCategories)) {
            $this->appliesToCategories = CouponDAO::getInstance()->isAppliesToCategories($this->id);
        }
        return $this->appliesToCategories;
    }
    
    /**
     * @return mixed
     */
    public function getCode() {
        if (is_null($this->code)) {
            $this->code = CouponDAO::getInstance()->getCode($this->id);
        }
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getDateAdded() {
        if (is_null($this->dateAdded)) {
            $this->dateAdded = CouponDAO::getInstance()->getDateAdded($this->id);
        }
        return $this->dateAdded;
    }

    /**
     * @return mixed
     */
    public function getDateEnd() {
        if (is_null($this->dateEnd)) {
            $this->dateEnd = CouponDAO::getInstance()->getDateEnd($this->id);
        }
        return $this->dateEnd;
    }

    /**
     * @return mixed
     */
    public function getDateStart() {
        if (is_null($this->dateStart)) {
            $this->dateStart = CouponDAO::getInstance()->getDateStart($this->id);
        }
        return $this->dateStart;
    }

    /**
     * @return mixed
     */
    public function getDiscount() {
        if (is_null($this->discount)) {
            $this->discount = CouponDAO::getInstance()->getDiscount($this->id);
        }
        return $this->discount;
    }

    /**
     * @return mixed
     */
    public function getEnabled() {
        if (is_null($this->enabled)) {
            $this->enabled = CouponDAO::getInstance()->isEnabled($this->id);
        }
        return $this->enabled;
    }

    /**
     * @return mixed
     */
    public function getLogged() {
        if (is_null($this->logged)) {
            $this->logged = CouponDAO::getInstance()->getLogged($this->id);
        }
        return $this->logged;
    }

    /**
     * @return mixed
     */
    public function getName() {
        if (is_null($this->name)) {
            $this->name = CouponDAO::getInstance()->getName($this->id);
        }
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getShipping() {
        if (is_null($this->shipping)) {
            $this->shipping = CouponDAO::getInstance()->getShipping($this->id);
        }
        return $this->shipping;
    }

    /**
     * @return mixed
     */
    public function getTotal() {
        if (is_null($this->total)) {
            $this->total = CouponDAO::getInstance()->getTotal($this->id);
        }
        return $this->total;
    }

    /**
     * @return mixed
     */
    public function getType() {
        if (is_null($this->type)) {
            $this->type = CouponDAO::getInstance()->getType($this->id);
        }
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getUsesCustomer() {
        if (is_null($this->usesCustomer)) {
            $this->usesCustomer = CouponDAO::getInstance()->getUsesCustomer($this->id);
        }
        return $this->usesCustomer;
    }

    /**
     * @return mixed
     */
    public function getUsesTotal() {
        if (is_null($this->usesTotal)) {
            $this->usesTotal = CouponDAO::getInstance()->getUsesTotal($this->id);
        }
        return $this->usesTotal;
    }
}