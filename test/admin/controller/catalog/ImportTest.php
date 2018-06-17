<?php
namespace test\admin\controller\catalog;
use model\catalog\ImportProduct;
use model\catalog\ImportProductDAO;
use model\catalog\ProductDAO;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ImportTest extends Test {
    public function __construct($name = null, $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);
        require_once(DIR_APPLICATION . "/controller/catalog/import.php");
    }

    protected function setUp() {
        parent::setUp();
        $this->class = new \ControllerCatalogImport($this->registry);
    }

    /**
     * @test
     * @covers ControllerCatalogImport::index
     */
    public function index() {
        $this->class->index();
        $response = runMethod($this->class, 'getResponse');
        $this->assertAttributeNotEmpty('output', $response);
        $this->assertAttributeContains('>&gt;|</a>', 'output', $response);
    }

    /**
     * @test
     * @covers ControllerCatalogImport::delete
     */
    public function delete() {
        $newProduct = $this->getFirstClearedProduct();
        $this->assertEmpty($newProduct->getLocalProductId());
    }

    /**
     * @test
     * @covers ControllerCatalogImport::enable
     */
    public function enable() {
        $mock = $this->getMock('ControllerCatalogImport', ['redirect'], [$this->registry]);
        $mock->expects($this->any())->method('redirect')->will($this->returnValue(null));
        //TODO: Add items to enable
        $mock->enable();
        //echo $buffer;
//        $this->assertAttributeNotEmpty('output', $this->class->getResponse());
    }

    /**
     * @test
     * @covers ControllerCatalogImport::synchronize
     */
    public function testSynchronizeExisting() {
        $products = ImportProductDAO::getInstance()->getImportedProducts([
            'start' => 0, 'limit' => 1, 'filterLocalProductId' => '*']);
        $product = $products[0];
        $_GET['what'] = 'selectedItems';
        $_GET['selectedItems'] = [$product->getId()];
        $mock = $this->getMock('ControllerCatalogImport', ['redirect'], [$this->registry]);
        $mock->expects($this->any())->method('redirect')->will($this->returnValue(null));
        $mock->synchronize();
        /** @var \ModelCatalogProduct $modelCatalogProduct */
        $modelCatalogProduct = $this->registry->get('load')->model('catalog/product');
        $processedProduct = ImportProductDAO::getInstance()->getImportedProduct($product->getId());
        $localProduct = $modelCatalogProduct->getProduct($processedProduct->getLocalProductId());
        $this->assertEquals($product->getName(), $localProduct['korean_name']);
        $this->assertEquals($product->getSourceSite()->getDefaultManufacturer()->getId(), $localProduct['manufacturer_id']);
        $this->assertEquals($product->getSourceSite()->getDefaultSupplier()->getId(), $localProduct['supplier_id']);
    }

    /**
     * @test
     * @covers ControllerCatalogImport::synchronize
     */
    public function testSynchronizeNew() {
        $product = $this->getFirstClearedProduct();
        $_GET['what'] = 'selectedItems';
        $_GET['selectedItems'] = [$product->getId()];
        $mock = $this->getMock('ControllerCatalogImport', ['redirect'], [$this->registry]);
        $mock->expects($this->any())->method('redirect')->will($this->returnValue(null));
        $mock->synchronize();
        /** @var \ModelCatalogProduct $modelCatalogProduct */
        $modelCatalogProduct = $this->registry->get('load')->model('catalog/product');
        $processedProduct = ImportProductDAO::getInstance()->getImportedProduct($product->getId());
        $localProduct = $modelCatalogProduct->getProduct($processedProduct->getLocalProductId());
        $this->assertEquals($product->getName(), $localProduct['name']);
        $this->assertEquals($product->getSourceSite()->getDefaultManufacturer()->getId(), $localProduct['manufacturer_id']);
        $this->assertEquals($product->getSourceSite()->getDefaultSupplier()->getId(), $localProduct['supplier_id']);
        $categories = $modelCatalogProduct->getProductCategories($localProduct['product_id']);
        $this->assertEquals($product->getCategories(), $categories);
    }

    /**
     * @return ImportProduct
     */
    private function getFirstClearedProduct() {
        $products = ImportProductDAO::getInstance()->getImportedProducts([
            'start' => 0, 'limit' => 1, 'filterLocalProductId' => '*']);
        ImportProductDAO::getInstance()->deleteImportedProducts($products);
        return ImportProductDAO::getInstance()->getImportedProduct($products[0]->getId());
    }
}
 