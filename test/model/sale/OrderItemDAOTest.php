<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 19.11.2014
 * Time: 23:59
 */

namespace test\model\sale;

use model\sale\OrderDAO;
use model\sale\OrderItemDAO;
use system\library\Filter;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class OrderItemDAOTest extends Test {
    /**
     * @test
     */
    public function testBuildFilter() {
        $expected = new Filter(
            'op.order_product_id IN (:oporder_product_id0, :oporder_product_id1)',
            [':oporder_product_id0' => 1, ':oporder_product_id1' => 2]);
        $this->assertEquals($expected, runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['selected_items' => [1, 2]]]));
        $expected = new Filter('LCASE(op.comment) LIKE :filterComment
                    OR LCASE(op.public_comment) LIKE :filterComment', [':filterComment' => '%test comment%']);
        $this->assertEquals($expected, runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterComment' => 'test comment']]));
        $this->assertEquals(
            new Filter('c.customer_id IN (:ccustomer_id0, :ccustomer_id1)', [':ccustomer_id0' => 1, ':ccustomer_id1' => 2]),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterCustomerId' => [1, 2]]])
        );
        $this->assertEquals(
            new Filter('LCASE(op.model) LIKE :filterItem
                    OR LCASE(op.name) LIKE :filterItem', [':filterItem' => '%test item%']),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterItem' => 'test item']])
        );
        $this->assertEquals(
            new Filter('LCASE(op.model) LIKE :filterModel', [':filterModel' => '%test model%']),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterModel' => 'test model']])
        );
        $this->assertEquals(
            new Filter('op.status_id = :opstatus_id', [':opstatus_id' => 300]),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterStatusId' => 300]])
        );
        $this->assertEquals(
            new Filter('s.supplier_id = :ssupplier_id', [':ssupplier_id' => 1]),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterSupplierId' => 1]])
        );
        $this->assertEquals(
            new Filter('op.order_id = :oporder_id', [':oporder_id' => 1]),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterOrderId' => 1]])
        );
        $this->assertEquals(
            new Filter('op.order_product_id = :oporder_product_id', [':oporder_product_id' => 1]),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterOrderItemId' => 1]])
        );
        $this->assertEquals(
            new Filter('op.product_id = :opproduct_id', [':opproduct_id' => 1]),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterProductId' => 1]])
        );
        $this->assertEquals(
            new Filter('op.time_modified >= :filterTimeModifiedFrom', [':filterTimeModifiedFrom' => '2014-01-01']),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterTimeModifiedFrom' => '2014-01-01']])
        );
        $this->assertEquals(
            new Filter('op.time_modified <= :filterTimeModifiedTo', [':filterTimeModifiedTo' => '2014-01-01']),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [['filterTimeModifiedTo' => '2014-01-01']])
        );
        $this->assertEquals(
            new Filter('op.time_modified >= :filterTimeModifiedFrom', [':filterTimeModifiedFrom' => '2014-01-01']),
            runMethod(OrderItemDAO::getInstance(), 'buildFilter', [[
                'filterTimeModifiedFrom' => '2014-01-01',
                'filterSupplierId' => []]])
        );
    }

    /**
     * @test
     */
    public function getOrderItemsOfUnconfirmedOrders() {
        $orders = OrderDAO::getInstance()->getOrders(['filter_order_status_id' => 0]);
        foreach ($orders as $order) {
            $orderItems = OrderItemDAO::getInstance()->getOrderItems(['filterOrderId' => $order['order_id']]);
            $this->assertEmpty($orderItems);
        }
    }
}
 