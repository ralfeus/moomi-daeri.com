<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 27.06.2018
 * Time: 14:46
 */

namespace model;

use model\total\CouponDAO;
use model\total\LocalShippingDAO;
use model\total\LowOrderFeeDAO;
use model\total\PaymentMethodDiscountsDAO;
use model\total\TotalBaseDAO;
use PHPUnit\Framework\Assert;
use test\model\Test;


class TotalBaseDAOTest extends Test {
    /**
     * @test
     * @covers TotalBaseDAO::getTotalExtension()
     */
    public function getTotalExtension() {
        Assert::assertInstanceOf(CouponDAO::class, TotalBaseDAO::getTotalExtension('coupon'));
        Assert::assertInstanceOf(LocalShippingDAO::class, TotalBaseDAO::getTotalExtension('localShipping'));
        Assert::assertInstanceOf(LowOrderFeeDAO::class, TotalBaseDAO::getTotalExtension('low_order_fee'));
        Assert::assertInstanceOf(PaymentMethodDiscountsDAO::class, TotalBaseDAO::getTotalExtension('paymentmethoddiscounts'));
    }
}
