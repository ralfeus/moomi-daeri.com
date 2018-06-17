<?php
namespace test\model\catalog;
use model\catalog\ImportProductDAO;


/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ImportProductDAOTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * @test
     */
    public function testGetSelectedItems() {
        $itemsFilter = array('selectedItems' => array(1, 2, 3, 4));
        $items = ImportProductDAO::getInstance()->getImportedProducts($itemsFilter, true);
        $this->assertEquals(4, sizeof($items));
        $this->assertInstanceOf("model\\catalog\\ImportProduct", $items[0]);
    }

    /**
     * @test
     */
    public function testGetActiveItems() {
        $itemsFilter = array('filterIsActive' => true);
        $items = ImportProductDAO::getInstance()->getImportedProducts($itemsFilter, true);
        foreach ($items as $item) {
            $this->assertTrue($item->getIsActive());
        }
        $itemsFilter = array('filterIsActive' => false);
        $items = ImportProductDAO::getInstance()->getImportedProducts($itemsFilter, true);
        foreach ($items as $item) {
            $this->assertFalse($item->getIsActive());
        }
    }

    /**
     * @test
     */
    public function testDelete() {
        $importedProduct = ImportProductDAO::getInstance()->getImportedProducts(array('start' => 0, 'limit' => 1), true);
        ImportProductDAO::getInstance()->unpairImportedProduct($importedProduct[0]->getId());
        $product = ImportProductDAO::getInstance()->getImportedProducts(array('selectedItems' => array($importedProduct[0]->getId())), true);
        $this->assertEmpty($product[0]->getLocalProductId());
    }
}
 