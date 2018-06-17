<?php
/**
 * Created by PhpStorm.
 * User: dev
 * Date: 19.11.2014
 * Time: 23:59
 */

namespace test\model\sale;

use model\sale\RepurchaseOrderDAO;
use system\library\Filter;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class RepurchaseOrderDAOTest extends Test {
    /**
     * @test
     */
    public function testBuildFilter() {
        $expected = new Filter(
            'op.product_id = 8608 AND op.order_product_id IN (:oporder_product_id0, :oporder_product_id1)',
            [':oporder_product_id0' => 1, ':oporder_product_id1' => 2]);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['selectedItems' => [1, 2]]]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('op.total = :optotal', [':optotal' => 10]);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterAmount' => 10]]));
        $this->assertEquals(new Filter('op.product_id = 8608'), runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterAmount' => 'bla']]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = 103067
                        AND value LIKE :itemName)', [':itemName' => '%item name%']);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterItemName' => 'item name']]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = 103066
                        AND value LIKE :shopName)', [':shopName' => '%shop name%']);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterShopName' => 'shop name']]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('EXISTS (
                    SELECT order_option_id
                    FROM order_option
                    WHERE
                        order_product_id = op.order_product_id
                        AND product_option_id = 14968
                        AND value LIKE :siteName)', [':siteName' => '%site name%']);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterSiteName' => 'site name']]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('c.customer_id IN (:ccustomer_id0, :ccustomer_id1)',
            [':ccustomer_id0' => 1, ':ccustomer_id1' => 2]);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterCustomerId' => [1, 2]]]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('op.order_product_id = :oporder_product_id', [':oporder_product_id' => 1]);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterOrderId' => 1]]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('op.status_id = :opstatus_id', [':opstatus_id' => 300]);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [['filterStatusId' => 300]]));
        $expected = new Filter('op.product_id = 8608');
        $expected->addChunk('EXISTS (
                    SELECT order_item_history_id
                    FROM order_item_history
                    WHERE
                        order_item_id = op.order_product_id
                        AND order_item_status_id IN (:statusIdDateSet0, :statusIdDateSet1)
                        AND date_added = :dateStatusSet
                )',
            [':statusIdDateSet0' => 300, ':statusIdDateSet1' => 404, ':dateStatusSet' => '2014-11-01']);
        $this->assertEquals($expected, runMethod(RepurchaseOrderDAO::getInstance(), 'buildFilter', [[
            'filterStatusIdDateSet' => [300, 404], 'filterStatusSetDate' => '2014-11-01'
        ]]));
    }
}
 