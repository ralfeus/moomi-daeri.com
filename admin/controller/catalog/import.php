<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 25.7.13
 * Time: 16:08
 * To change this template use File | Settings | File Templates.
 */

class ControllerCatalogImport extends Controller {
    private $modelCatalogImport;
    private $modelCatalogProduct;

    public function __construct(Registry $registry) {
        parent::__construct($registry);
        $this->load->language('catalog/import');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->modelCatalogImport = $this->load->model('catalog/import');
    }

    private function addFromSource(ImportedProduct $productToAdd) {
        /// Downloading images
        $modelToolImage = $this->load->model('tool/image');
        $thumbnail = $modelToolImage->download($productToAdd->getThumbnailUrl());
        $images = array();
        foreach ($productToAdd->getImages() as $imageUrl)
            $images[]['image'] = $modelToolImage->download($imageUrl);
        /// Preparing name, korean name and description
        $product_description = array();
        $koreanName = array();
        $koreanName['attribute_id'] = ATTRIBUTE_KOREAN_NAME;
        $koreanName['product_attribute_description'] = array();
        foreach ($this->load->model('localisation/language')->getLanguages() as $language) {
            $product_description[$language['language_id']] = array(
                'name' => $productToAdd->getName(),
                'description' => $productToAdd->getDescription()
            );
            $koreanName['product_attribute_description'][$language['language_id']] = array(
                'text' => $productToAdd->getName()
            );
        }

        $productId = $this->modelCatalogProduct->addProduct(array(
            'date_available' => date('Y-m-d'),
            'height' => null,
            'image' => $thumbnail,
            'length' => null, 'length_class_id' => 1,
            'location' => null,
            'manufacturer_id' => $productToAdd->getSourceSite()->getDefaultManufacturerId(),
            'meta_keywords' => null, 'meta_description' => null,
            'minimum' => null,
            'model' => $productToAdd->getName(),
            'points' => null,
            'price' => $productToAdd->getSourcePrice(),
            'product_attribute' => array($koreanName),
            'product_category' => array($productToAdd->getSourceSite()->getDefaultCategoryId()),
            'product_description' => $product_description,
            'product_image' => $images,
            'product_store' => array($productToAdd->getSourceSite()->getDefaultStoreId()),
            'product_tag' => null,
            'seo_title' => null, 'seo_h1' => null,
            'shipping' => null,
            'sku' => null,
            'sort_order' => null,
            'status' => 1,
            'stock_status_id' => 8,
            'subtract' => null,
            'supplier_id' => $productToAdd->getSourceSite()->getDefaultSupplierId(),
            'tax_class' => null,
            'upc' => null,
            'user_id' => 0,
            'weight' => null, 'weight_class_id' => 1,
            'width' => null
        ));

        $this->modelCatalogImport->pairImportedProduct($productToAdd->getId(), $productId);
    }

    public function delete() {
        $this->modelCatalogProduct = $this->load->model('catalog/product');
        $productsToDelete = array();
        if ($this->parameters['what'] == 'selectedItems')
            foreach ($this->parameters['selectedItems'] as $importedProductId)
                $productsToDelete[] = $this->modelCatalogImport->getImportedProduct($importedProductId);
        elseif ($this->parameters['what'] == 'all')
            $productsToDelete = $this->modelCatalogImport->getImportedProducts($this->parameters);
        foreach ($productsToDelete as $productToDelete) {
            $this->modelCatalogProduct->deleteProduct($productToDelete->getLocalProductId());
            $this->modelCatalogImport->unpairImportedProduct($productToDelete->getId());
        }
        $this->redirect($this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters)));
    }

    private function showList() {
        $this->parameters['start'] = ($this->parameters['page'] - 1) * $this->config->get('config_admin_limit');
        $this->parameters['limit'] = $this->config->get('config_admin_limit');

        foreach ($this->modelCatalogImport->getImportedProducts($this->parameters) as $product) {
            $productItem = $product;
            $productItem->actions = $this->getProductActions($product);
            $productItem->isSelected = in_array($product->getId(), $this->parameters['selectedItems']);
            $productItem->localProductUrl = $this->url->link(
                'catalog/product/update', 'product_id=' . $product->getLocalProductId() . '&token=' . $this->parameters['token'], 'SSL');
            $this->data['products'][] = $productItem;
        }

        foreach ($this->modelCatalogImport->getSourceSites() as $sourceSite)
            $this->data['sourceSites'][$sourceSite->getId()] = $sourceSite->getName();

        $this->data['urlDeleteAll'] = $this->url->link('catalog/import/delete', $this->buildUrlParameterString($this->parameters) . '&what=all', 'SSL');
        $this->data['urlDeleteSelected'] = $this->url->link('catalog/import/delete', $this->buildUrlParameterString($this->parameters) . '&what=selectedItems', 'SSL');
        $this->data['urlSyncAll'] = $this->url->link('catalog/import/synchronize', $this->buildUrlParameterString($this->parameters) . '&what=all', 'SSL');
        $this->data['urlSyncSelected'] = $this->url->link('catalog/import/synchronize', $this->buildUrlParameterString($this->parameters) . '&what=selectedItems', 'SSL');

        $page = $this->parameters['page']; unset($this->parameters['page']);
        $pagination = new Pagination(
            $page,
            $this->config->get('config_admin_limit'),
            $this->modelCatalogImport->getImportedProductsQuantity($this->parameters),
            $this->language->get('text_pagination'),
            $this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters) . '&page={page}', 'SSL')
        );
        $this->data['pagination'] = $pagination->render();

        $this->data = array_merge($this->data, $this->parameters);
        $this->setBreadcrumbs();
        $this->template = 'catalog/importProductsList.php';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->response->setOutput($this->render());
    }

    private function getProductActions($product) {
        return array();
    }

    public function index() {
        $this->showList();
    }

    protected function initParameters() {
        $this->parameters['filterItem'] = empty($_REQUEST['filterItem']) ? null : $_REQUEST['filterItem'];
        $this->parameters['filterSourceSiteId'] = empty($_REQUEST['filterSourceSiteId']) ? array() : $_REQUEST['filterSourceSiteId'];
        $this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
        $this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
        $this->parameters['token'] = $this->session->data['token'];
        $this->parameters['what'] = empty($_REQUEST['what']) ? null : $_REQUEST['what'];
    }

    protected function loadStrings() {
        $this->data['headingTitle'] = $this->language->get('headingTitle');
        $this->data['textActions'] = $this->language->get('ACTIONS');
        $this->data['textDeleteAll'] = $this->language->get('DELETE_ALL');
        $this->data['textDeleteSelected'] = $this->language->get('DELETE_SELECTED');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textId'] = $this->language->get('ID');
        $this->data['textImage'] = $this->language->get('IMAGE');
        $this->data['textItem'] = $this->language->get('ITEM');
        $this->data['textLocal'] = $this->language->get('LOCAL');
        $this->data['textPrice'] = $this->language->get('PRICE');
        $this->data['textProductId'] = $this->language->get('PRODUCT_ID');
        $this->data['textSource'] = $this->language->get('SOURCE');
        $this->data['textSourceSite'] = $this->language->get('SOURCE_SITE');
        $this->data['textTimeModified'] = $this->language->get('TIME_MODIFIED');
        $this->data['textUpdateAll'] = $this->language->get('UPDATE_ALL');
        $this->data['textUpdateSelected'] = $this->language->get('UPDATE_SELECTED');
    }

    public function synchronize() {
        $this->modelCatalogProduct = $this->load->model('catalog/product');
        $productsToSynchronize = array();
        if ($this->parameters['what'] == 'selectedItems')
            foreach ($this->parameters['selectedItems'] as $importedProductId)
                $productsToSynchronize[] = $this->modelCatalogImport->getImportedProduct($importedProductId);
        elseif ($this->parameters['what'] == 'all')
            $productsToSynchronize = $this->modelCatalogImport->getImportedProducts($this->parameters);

        foreach ($productsToSynchronize as $productToSynchronize)
        {
            if ($productToSynchronize->getLocalProductId())
                $this->updateFromSource($productToSynchronize);
            else
                $this->addFromSource($productToSynchronize);
        }

        $this->redirect($this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters)));
    }

    private function updateFromSource($productToUpdate) {

    }
}