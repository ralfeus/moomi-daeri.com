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
            $images[] = array('image' => $modelToolImage->download($imageUrl));
        /// Preparing name, korean name, link and description
        $product_description = array();
        $koreanName = array(
            'attribute_id' => ATTRIBUTE_KOREAN_NAME,
            'product_attribute_description' => array()
        );
        $sourceUrl = array(
            'attribute_id' => ATTRIBUTE_LINK,
            'product_attribute_description' => array()
        );
        foreach ($this->load->model('localisation/language')->getLanguages() as $language) {
            $product_description[$language['language_id']] = array(
                'name' => $productToAdd->getName(),
                'description' => $productToAdd->getDescription()
            );
            $koreanName['product_attribute_description'][$language['language_id']] = array( 'text' => $productToAdd->getName() );
            $sourceUrl['product_attribute_description'][$language['language_id']] = array( 'text' => $productToAdd->getSourceUrl() );
        }
        /// Preparing promo price
        if ($productToAdd->getSourcePrice()->getPromoPrice())
            $promoPrice = array(array(
                'customer_group_id' => 8, /* Default customer group ID */
                'priority' => 0, /// Highest priority
                'price' => $productToAdd->getSourcePrice()->getPromoPrice(),
                'date_start' => date('Y-m-d'),
                'date_end' => '2038-01-19' /// Maximum available date as a timestamp (limited by int type)
            ));
        else
            $promoPrice = null;

        $productId = $this->modelCatalogProduct->addProduct(array(
            'date_available' => date('Y-m-d'),
            'height' => null,
            'image' => $thumbnail,
            'length' => null, 'length_class_id' => 1,
            'location' => null,
            'manufacturer_id' => $productToAdd->getSourceSite()->getDefaultManufacturerId(),
            'meta_keywords' => null, 'meta_description' => null,
            'minimum' => 1,
            'model' => $productToAdd->getSourceProductId(),
            'points' => null,
            'price' => $productToAdd->getSourcePrice()->getPrice(),
            'product_attribute' => array($koreanName, $sourceUrl),
            'product_category' => array($productToAdd->getSourceSite()->getDefaultCategoryId()),
            'product_description' => $product_description,
            'product_image' => $images,
            'product_special' => $promoPrice,
            'product_store' => array($productToAdd->getSourceSite()->getDefaultStoreId()),
            'product_tag' => null,
            'seo_title' => null, 'seo_h1' => null,
            'shipping' => 1,
            'sku' => null,
            'sort_order' => null,
            'status' => 1,
            'stock_status_id' => 8,
            'subtract' => null,
            'supplier_id' => $productToAdd->getSourceSite()->getDefaultSupplierId(),
            'tax_class' => null,
            'upc' => null,
            'user_id' => 0,
            'weight' => $productToAdd->getWeight(), 'weight_class_id' => 1,
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
            foreach ($this->modelCatalogProduct->getProductImages($productToDelete->getLocalProductId()) as $image) {
                unlink(DIR_IMAGE . $image['image']);
            }
            $localProduct = $this->modelCatalogProduct->getProduct($productToDelete->getLocalProductId());
            unlink(DIR_IMAGE . $localProduct['image']);
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
        $this->parameters['filterIsActive'] = isset($_REQUEST['filterIsActive']) && is_numeric($_REQUEST['filterIsActive']) ? $_REQUEST['filterIsActive'] : null;
        $this->parameters['filterItem'] = empty($_REQUEST['filterItem']) ? null : $_REQUEST['filterItem'];
        $this->parameters['filterSourceSiteId'] = empty($_REQUEST['filterSourceSiteId']) ? array() : $_REQUEST['filterSourceSiteId'];
        $this->parameters['page'] = empty($_REQUEST['page']) ? 1 : $_REQUEST['page'];
        $this->parameters['selectedItems'] = empty($_REQUEST['selectedItems']) ? array() : $_REQUEST['selectedItems'];
        $this->parameters['token'] = $this->session->data['token'];
        $this->parameters['what'] = empty($_REQUEST['what']) ? null : $_REQUEST['what'];
    }

    protected function loadStrings() {
        $this->data['headingTitle'] = $this->language->get('headingTitle');
        $this->data['heading_title'] = $this->data['headingTitle'];
        $this->data['textActions'] = $this->language->get('ACTIONS');
        $this->data['textDeleteAll'] = $this->language->get('DELETE_ALL');
        $this->data['textDeleteSelected'] = $this->language->get('DELETE_SELECTED');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textId'] = $this->language->get('ID');
        $this->data['textImage'] = $this->language->get('ITEM_IMAGE');
        $this->data['textItem'] = $this->language->get('ITEM');
        $this->data['textLocal'] = $this->language->get('LOCAL');
        $this->data['textPrice'] = $this->language->get('PRICE');
        $this->data['textProductId'] = $this->language->get('PRODUCT_ID');
        $this->data['textSource'] = $this->language->get('SOURCE');
        $this->data['textSourceSite'] = $this->language->get('SOURCE_SITE');
        $this->data['textStatus'] = $this->language->get('STATUS');
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

    private function updateFromSource(ImportedProduct $productToUpdate) {
        $localProduct = $this->modelCatalogProduct->getProduct($productToUpdate->getLocalProductId());
        /// Downloading images
        foreach ($this->modelCatalogProduct->getProductImages($productToUpdate->getLocalProductId()) as $image) {
            unlink(DIR_IMAGE . $image['image']);
        }
        unlink(DIR_IMAGE . $localProduct['image']);
        $modelToolImage = $this->load->model('tool/image');
        $thumbnail = $modelToolImage->download($productToUpdate->getThumbnailUrl());
        $images = array();
        foreach ($productToUpdate->getImages() as $imageUrl)
            $images[] = array('image' => $modelToolImage->download($imageUrl));
        /// Preparing name, korean name and description
        $product_description = array();
        $koreanName = array(
            'attribute_id' => ATTRIBUTE_KOREAN_NAME,
            'product_attribute_description' => array()
        );
        $sourceUrl = array(
            'attribute_id' => ATTRIBUTE_LINK,
            'product_attribute_description' => array()
        );
        foreach ($this->load->model('localisation/language')->getLanguages() as $language) {
//            $product_description[$language['language_id']] = array(
//                'name' => $productToUpdate->getName(),
//                'description' => $productToUpdate->getDescription()
//            );
            $koreanName['product_attribute_description'][$language['language_id']] = array( 'text' => $productToUpdate->getName() );
            $sourceUrl['product_attribute_description'][$language['language_id']] = array( 'text' => $productToUpdate->getSourceUrl() );
        }
        /// Preparing promo price
        if ($productToUpdate->getSourcePrice()->getPromoPrice())
            $promoPrice = array(array(
                'customer_group_id' => 8, /* Default customer group ID */
                'priority' => 0, /// Highest priority
                'price' => $productToUpdate->getSourcePrice()->getPromoPrice(),
                'date_start' => date('Y-m-d'),
                'date_end' => '2038-01-19' /// Maximum available date as a timestamp (limited by int type)
            ));
        else
            $promoPrice = null;

        $this->modelCatalogProduct->editProduct($productToUpdate->getLocalProductId(), array(
            'date_available' => $localProduct['date_available'],
            'height' => null,
            'image' => $thumbnail,
            'length' => null, 'length_class_id' => 1,
            'location' => null,
            'manufacturer_id' => $productToUpdate->getSourceSite()->getDefaultManufacturerId(),
            'meta_keywords' => null, 'meta_description' => null,
            'minimum' => null,
            'model' => $localProduct['model'],
            'points' => null,
            'price' => $productToUpdate->getSourcePrice()->getPrice(),
            'product_attribute' => array($koreanName, $sourceUrl),
            'product_category' => array($productToUpdate->getSourceSite()->getDefaultCategoryId()),
            'product_description' => null,
            'product_image' => $images,
            'product_special' => $promoPrice,
            'product_store' => array($productToUpdate->getSourceSite()->getDefaultStoreId()),
            'product_tag' => null,
            'seo_title' => null, 'seo_h1' => null,
            'shipping' => null,
            'sku' => null,
            'sort_order' => null,
            'status' => 1,
            'stock_status_id' => 8,
            'subtract' => null,
            'supplier_id' => $productToUpdate->getSourceSite()->getDefaultSupplierId(),
            'tax_class' => null,
            'upc' => null,
            'user_id' => 0,
            'weight' => $localProduct['weight'], 'weight_class_id' => $localProduct['weight_class_id'],
            'width' => null
        ));
    }
}