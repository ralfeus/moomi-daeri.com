<?php
use model\catalog\ImportProduct;
use model\extension\ImportSourceSiteDAO;

class ControllerCatalogImport extends Controller {
    /** @var ModelCatalogImport */
    private $modelCatalogImport;
    /** @var ModelCatalogProduct */
    private $modelCatalogProduct;

    public function __construct(Registry $registry) {
        ini_set('max_execution_time', 3600);
        parent::__construct($registry);
        $this->load->language('catalog/import');
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->modelCatalogImport = $this->load->model('catalog/import');
    }

    /**
     * @param ImportProduct $productToAdd
     * @throws Exception
     */
    private function addFromSource($productToAdd) {
        /// Downloading images
        /** @var ModelToolImage $modelToolImage */
        $modelToolImage = $this->load->model('tool/image');
        $thumbnail = $modelToolImage->download($productToAdd->getThumbnailUrl());
        $images = array();
        foreach ($productToAdd->getImages() as $imageUrl)
            $images[] = array('image' => $modelToolImage->download($imageUrl));
        /// Preparing name, korean name, link and description
        $product_description = array();
//        $koreanName = array(
//            'attribute_id' => ATTRIBUTE_KOREAN_NAME,
//            'product_attribute_description' => array()
//        );
//        $sourceUrl = array(
//            'attribute_id' => ATTRIBUTE_LINK,
//            'product_attribute_description' => array()
//        );
        foreach ($this->load->model('localisation/language')->getLanguages() as $language) {
            $product_description[$language['language_id']] = array(
                'name' => $productToAdd->getName()
//                'description' => $productToAdd->getDescription()
            );
////            $koreanName['product_attribute_description'][$language['language_id']] = array( 'text' => $productToAdd->getName() );
////            $sourceUrl['product_attribute_description'][$language['language_id']] = array( 'text' => $productToAdd->getSourceUrl() );
        }

        $productId = $this->modelCatalogProduct->addProduct(array(
            'date_available' => date('Y-m-d'),
            'image_description' => $productToAdd->getDescription(),
            'height' => null,
            'image' => $thumbnail,
            'length' => null, 'length_class_id' => 1,
            'location' => null,
            'manufacturer_id' => $productToAdd->getSourceSite()->getDefaultManufacturer()->getId(),
            'meta_keywords' => null, 'meta_description' => null,
            'minimum' => 1,
            'model' => $productToAdd->getSourceProductId(),
            'points' => null,
            'price' => $productToAdd->getSourcePrice()->getPrice() * $productToAdd->getSourceSite()->getRegularCustomerPriceRate(),
            //'product_attribute' => array($koreanName, $sourceUrl),
            'product_category' => $productToAdd->getCategories(),
            'product_description' => $product_description,
            'product_image' => $images,
            'product_option' => $this->setProductOption($productToAdd),
            'product_special' => $this->getSpecialPrices($productToAdd),
            'product_store' => $productToAdd->getSourceSite()->getStores(),
            'product_tag' => null,
            'seo_title' => null, 'seo_h1' => null,
            'shipping' => 1,
            'sku' => null,
            'sort_order' => null,
            'status' => 0,
            'stock_status_id' => 8,
            'subtract' => null,
            'supplier_id' => $productToAdd->getSourceSite()->getDefaultSupplier()->getId(),
            'tax_class' => null,
            'upc' => null,
            'user_id' => 0,
            'weight' => $productToAdd->getWeight(), 'weight_class_id' => 1,
            'width' => null,
            'koreanName' => $productToAdd->getName(),
            'supplierUrl' => $productToAdd->getSourceUrl()
        ));

        $this->modelCatalogImport->pairImportedProduct($productToAdd->getId(), $productId);
    }

    public function delete() {
        $this->modelCatalogProduct = $this->load->model('catalog/product');
        $productsToDelete = array();
        if ($this->parameters['what'] == 'selectedItems') {
            foreach ($this->parameters['selectedItems'] as $importedProductId) {
                $productsToDelete[] = $this->modelCatalogImport->getImportedProduct($importedProductId);
            }
        } elseif ($this->parameters['what'] == 'all') {
            $productsToDelete = $this->modelCatalogImport->getImportedProducts($this->parameters);
        }
        foreach ($productsToDelete as $productToDelete) {
            foreach ($this->modelCatalogProduct->getProductImages($productToDelete->getLocalProductId()) as $image) {
                unlink(DIR_IMAGE . $image['image']);
            }
            $localProduct = $this->modelCatalogProduct->getProduct($productToDelete->getLocalProductId());
            unlink(DIR_IMAGE . $localProduct['image']);
            $this->modelCatalogProduct->deleteProduct($productToDelete->getLocalProductId());
            $this->modelCatalogImport->unpairImportedProduct($productToDelete->getId());
        }
        unset($this->parameters['selectedItems']);
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

        foreach (ImportSourceSiteDAO::getInstance()->getSourceSites() as $sourceSite) {
            $this->data['sourceSites'][$sourceSite->getId()] = $sourceSite->getName();
        }
        /// Check import running status

        if ($this->status()) {
            $this->data['textToggleImport'] = $this->language->get('STOP_IMPORT');
            $this->data['importAction'] = 'stop';
        } else {
            $this->data['textToggleImport'] = $this->language->get('START_IMPORT');
            $this->data['importAction'] = 'start';
        }

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
        $this->data['textNoItems'] = $this->language->get('text_no_results');
        $this->data['textNoSelectedItems'] = $this->language->get('NO_ITEMS_SELECTED');
        $this->data['textPrice'] = $this->language->get('PRICE');
        $this->data['textProductId'] = $this->language->get('PRODUCT_ID');
        $this->data['textSelectSourceSitesToImport'] = $this->language->get('SELECT_SOURCE_SITES_TO_IMPORT');
        $this->data['textSource'] = $this->language->get('SOURCE');
        $this->data['textSourceSite'] = $this->language->get('SOURCE_SITE');
        $this->data['textStatus'] = $this->language->get('STATUS');
        $this->data['textTimeModified'] = $this->language->get('TIME_MODIFIED');
        $this->data['textUpdateAll'] = $this->language->get('UPDATE_ALL');
        $this->data['textUpdateSelected'] = $this->language->get('UPDATE_SELECTED');
        $this->data['textViewImportStatus'] = $this->language->get('VIEW_IMPORT_STATUS');
    }

    public function synchronize() {
        $this->modelCatalogProduct = $this->load->model('catalog/product');
        $productsToSynchronize = array();
        if ($this->parameters['what'] == 'selectedItems') {
            foreach ($this->parameters['selectedItems'] as $importedProductId) {
                $productsToSynchronize[] = $this->modelCatalogImport->getImportedProduct($importedProductId);
            }
        } elseif ($this->parameters['what'] == 'all') {
            $productsToSynchronize = $this->modelCatalogImport->getImportedProducts($this->parameters);
        }
        foreach ($productsToSynchronize as $productToSynchronize) {
            if ($productToSynchronize->getLocalProductId()) {
                $this->updateFromSource($productToSynchronize);
            } else {
                $this->addFromSource($productToSynchronize);
            }
        }
        unset($this->parameters['selectedItems']);
        $this->redirect($this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters)));
    }

    /**
     * @param ImportProduct $productToUpdate
     * @throws Exception
     */
    private function updateFromSource($productToUpdate) {
        $localProduct = $this->modelCatalogProduct->getProduct($productToUpdate->getLocalProductId());
        /// Downloading images
        foreach ($this->modelCatalogProduct->getProductImages($productToUpdate->getLocalProductId()) as $image) {
            unlink(DIR_IMAGE . $image['image']);
        }
        unlink(DIR_IMAGE . $localProduct['image']);
        /** @var ModelToolImage $modelToolImage */
        $modelToolImage = $this->load->model('tool/image');
        $thumbnail = $modelToolImage->download($productToUpdate->getThumbnailUrl());
        $images = array();
        foreach ($productToUpdate->getImages() as $imageUrl)
            $images[] = array('image' => $modelToolImage->download($imageUrl));
        /// Preparing name, korean name and description
        $product_description = array();
        foreach ($this->load->model('localisation/language')->getLanguages() as $language) {
            $product_description[$language['language_id']] = array(
                'name' => $productToUpdate->getName(),
//                'description' => $productToUpdate->getDescription()
            );
        }

        /// Copying product options in order to preserve ones
        $localProductOptions = $this->modelCatalogProduct->getProductOptions($productToUpdate->getLocalProductId());
        /// Copying product categories in order to preserve ones
        $localProductCategories = $this->modelCatalogProduct->getProductCategories($productToUpdate->getLocalProductId());

        $this->modelCatalogProduct->editProduct($productToUpdate->getLocalProductId(), array(
            'date_available' => $localProduct['date_available'],
            'height' => null,
            'image' => $thumbnail,
            'length' => null, 'length_class_id' => 1,
            'location' => null,
            'manufacturer_id' => $productToUpdate->getSourceSite()->getDefaultManufacturer()->getId(),
            'meta_keywords' => null, 'meta_description' => null,
            'minimum' => null,
            'model' => $localProduct['model'],
            'points' => null,
            'price' => $productToUpdate->getSourcePrice()->getPrice() * $productToUpdate->getSourceSite()->getRegularCustomerPriceRate(),
//            'product_attribute' => array($koreanName, $sourceUrl),
            'product_category' => $localProductCategories,
            'product_description' => $product_description,
            'product_image' => $images,
            'product_option' => $localProductOptions,
            'product_special' => $this->getSpecialPrices($productToUpdate),
            'product_store' => $productToUpdate->getSourceSite()->getStores(),
            'product_tag' => null,
            'seo_title' => null, 'seo_h1' => null,
            'shipping' => null,
            'sku' => null,
            'sort_order' => null,
//            'status' => 1,
            'stock_status_id' => 8,
            'status' => $localProduct['status'],
            'subtract' => null,
            'supplier_id' => $productToUpdate->getSourceSite()->getDefaultSupplier()->getId(),
            'tax_class' => null,
            'upc' => null,
            'user_id' => 0,
            'weight' => $localProduct['weight'], 'weight_class_id' => $localProduct['weight_class_id'],
            'width' => null,
            'koreanName' => $productToUpdate->getName(),
            'supplierUrl' => $productToUpdate->getSourceUrl()
        ));
    }

    private function setProductOption($productToSetOptionsTo) {
        $productOptions = array();
        $productOptions[] = array(
            'name' => 'Memo',
            'option_id' => OPTION_MEMO_OPTION_ID,
            'option_value' => null,
            'product_option_id' => null,
            'required' => false,
            'type' => 'textarea'
        );
        return $productOptions;
    }

    /**
     * @param ImportProduct $product
     * @return array
     */
    private function getSpecialPrices($product) {
        $prices = array(
            array(
                'customer_group_id' => 6, /* Wholesales customers group ID */
                'priority' => 1, /// Highest priority
                'price' => $product->getSourcePrice()->getPrice() * $product->getSourceSite()->getWholeSaleCustomerPriceRate(),
                'date_start' => date('Y-m-d'),
                'date_end' => '2038-01-19' /// Maximum available date as a timestamp (limited by int type)
            )
        );
        /// Preparing promo price
        if ($product->getSourcePrice()->getPromoPrice()) {
            $prices[] = array(
                'customer_group_id' => 8, /* Default customer group ID */
                'priority' => 0, /// Highest priority
                'price' => $product->getSourcePrice()->getPromoPrice() * $product->getSourceSite()->getRegularCustomerPriceRate(),
                'date_start' => date('Y-m-d'),
                'date_end' => '2038-01-19' /// Maximum available date as a timestamp (limited by int type)
            );
            $prices[] = array(
                'customer_group_id' => 6, /* Wholesales customers group ID */
                'priority' => 0, /// Highest priority
                'price' => $product->getSourcePrice()->getPromoPrice() * $product->getSourceSite()->getWholeSaleCustomerPriceRate(),
                'date_start' => date('Y-m-d'),
                'date_end' => '2038-01-19' /// Maximum available date as a timestamp (limited by int type)
            );
        }
        return $prices;
    }

    public function parser() {

        defined('DIR_AUTOMATION') || define('DIR_AUTOMATION', dirname(DIR_APPLICATION) . '/automation/');

        switch ($this->request->get['a']) {
            case 'run':
                if ($this->parserStatus()) {
                    echo 'cant';
                } else {
                    $this->parserStart();
                    echo 'done';
                }
                break;

            default:
                $output = array('status' => false);
                $status = $this->parserStatus();
                if ($status) {
                    $output['status'] = true;
                    $output['stime'] = $status;
                }
                echo json_encode($output);
        }

    }

    /**
     * Checks whether import process is running.
     * If it's running returns time of start
     * Otherwise returns false
     * @return string
     */
    protected function status() {
        return (bool)file_exists(DIR_AUTOMATION . '/crawler.lck') || (bool)shell_exec("ps ax | grep '[[:digit:]] php -f crawler.php'");
    }

    public function getStatus() {
        $status = array();
        $status['running'] = (bool)$this->status();
        if (file_exists(DIR_AUTOMATION . '/import.log')) {
            $status['log'] = file_get_contents(DIR_AUTOMATION . '/import.log');
        } else {
            $status['log'] = 'No log file found';
        }
        $this->response->setOutput(json_encode($status));
    }

    public function start() {
        if (!$this->status()) {
            $sites = implode(',', $this->parameters['selectedItems']);
            //$pid = shell_exec("nohup php -f " . DIR_AUTOMATION . "/crawler.cli.adapter.php $sites > /dev/null 2>&1 & printf \"%u\" $!");
//            chdir(DIR_AUTOMATION);
            //$pid = shell_exec("php -f crawler.php $sites > import.log 2>&1 & printf \"%u\" $!");
            file_put_contents(DIR_AUTOMATION . '/crawler.lck', $sites);
            $this->response->setOutput(json_encode(array(
                'result' => 'started'
            )));
        }
    }

    public function stop() {
        if ($this->status()) {
            $process = shell_exec('ps ax | grep "[[:digit:]] php -f crawler.php"');
            if ($process) {
                $components = preg_split("/\s+/", $process);
                $pid = $components[0];
                shell_exec("kill $pid");
            }
        }
        $this->response->setOutput(null);
    }

}