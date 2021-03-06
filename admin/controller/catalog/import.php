<?php
use catalog\model\tool\ModelToolImage;
use model\catalog\ImportProduct;
use model\catalog\ImportProductDAO;
use model\catalog\Product;
use model\extension\ImportSourceSiteDAO;
use system\engine\Controller;

class ControllerCatalogImport extends \system\engine\Controller {
    /** @var ModelCatalogProduct */
    private $modelCatalogProduct;

    public function __construct(Registry $registry) {
        ini_set('max_execution_time', 3600);
        parent::__construct($registry);
        $this->load->language('catalog/import');
        $this->document->setTitle($this->language->get('headingTitle'));
        $this->modelCatalogProduct = $this->load->model('catalog/product');
    }

    /**
     * @param ImportProduct $productToAdd
     * @param bool $synchronizeImages
     * @throws Exception
     */
    private function addFromSource($productToAdd, $synchronizeImages = true) {

        $thumbnail = null; $images = array();
        if ($synchronizeImages) {
            $this->getImages($productToAdd, $images, $thumbnail);
            $productToAdd->setDescription($this->updateDescriptionImages($productToAdd->getId(), $productToAdd->getDescription()));
        }
        /// Preparing name, korean name, link and description
        $product_description = array();
        foreach ($this->load->model('localisation/language')->getLanguages() as $language) {
            $product_description[$language['language_id']] = array(
                'name' => $productToAdd->getName(),
                'description' => null
            );
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
            'minimum' => $productToAdd->getMinimalAmount(),
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

        ImportProductDAO::getInstance()->pairImportedProduct($productToAdd->getId(), $productId);
    }

    public function delete() {
        $productsToDelete = array();
        if ($this->parameters['what'] == 'selectedItems') {
            $productsToDelete = ImportProductDAO::getInstance()->getImportedProducts(array('selectedItems' => $this->parameters['selectedItems']), true);
        } elseif ($this->parameters['what'] == 'all') {
            $filter = $this->parameters; unset($filter['selectedItems']);
            $productsToDelete = ImportProductDAO::getInstance()->getImportedProducts($filter, true);
        }
        ImportProductDAO::getInstance()->deleteImportedProducts($productsToDelete);
        unset($this->parameters['selectedItems']);
        $this->redirect($this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters)));
    }

    public function disable() {
        $productsToDisable = array();
        if ($this->parameters['what'] == 'all') {
            $filter = $this->parameters; unset($filter['selectedItems']);
            $productsToDisable = ImportProductDAO::getInstance()->getImportedProducts($filter, true);
        } elseif ($this->parameters['what'] == 'inactiveItems') {
            $productsToDisable = ImportProductDAO::getInstance()->getImportedProducts(array('filterIsActive' => false), true);
        } elseif ($this->parameters['what'] == 'selectedItems') {
            $productsToDisable = ImportProductDAO::getInstance()->getImportedProducts(array('selectedItems' => $this->parameters['selectedItems']), true);
        }
        $this->modelCatalogProduct->changeStatusProducts(
            array_map(
                function(ImportProduct $element) {
                    return $element->getLocalProductId();
                },
                $productsToDisable
            ), false
        );
        unset($this->parameters['selectedItems']);
        $this->redirect($this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters)));
    }

    public function enable() {
        $productsToEnable = array();
        if ($this->parameters['what'] == 'all') {
            $filter = $this->parameters; unset($filter['selectedItems']);
            $productsToEnable = ImportProductDAO::getInstance()->getImportedProducts($filter, true);
        } elseif ($this->parameters['what'] == 'inactiveItems') {
            $productsToEnable = ImportProductDAO::getInstance()->getImportedProducts(array('filterIsActive' => false), true);
        } elseif ($this->parameters['what'] == 'selectedItems') {
            $productsToEnable = ImportProductDAO::getInstance()->getImportedProducts(array('selectedItems' => $this->parameters['selectedItems']), true);
        }
        $this->modelCatalogProduct->changeStatusProducts(
            array_map(
                function(ImportProduct $element) {
                    return $element->getLocalProductId();
                },
                $productsToEnable
            ), true
        );
        unset($this->parameters['selectedItems']);
        $this->redirect($this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters)));
    }

    private function showList() {
        $filter = $this->parameters;
        $filter['start'] = intval(($this->parameters['page'] - 1) * $this->config->get('config_admin_limit'));
        $filter['limit'] = intval($this->config->get('config_admin_limit'));
        unset($filter['selectedItems']);
        foreach (ImportProductDAO::getInstance()->getImportedProducts($filter) as $product) {
            $productItem = $product;
            $productItem->actions = $this->getProductActions($product);
            $productItem->isSelected = in_array($product->getId(), $this->parameters['selectedItems']);
            $productItem->localProductUrl = $this->url->link(
                'catalog/product/update', 'product_id=' . $product->getLocalProductId() . '&token=' . $this->parameters['token'], 'SSL');
            $this->data['products'][] = $productItem;
        }

        foreach (ImportSourceSiteDAO::getInstance()->getSourceSites() as $sourceSite) {
            $this->data['sourceSites'][$sourceSite->getClassName()] = $sourceSite->getName();
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
        $this->data['urlDisableAll'] = $this->url->link('catalog/import/disable', $this->buildUrlParameterString($this->parameters) . '&what=all', 'SSL');
        $this->data['urlDisableInactive'] = $this->url->link('catalog/import/disable', $this->buildUrlParameterString($this->parameters) . '&what=inactiveItems', 'SSL');
        $this->data['urlDisableSelected'] = $this->url->link('catalog/import/disable', $this->buildUrlParameterString($this->parameters) . '&what=selectedItems', 'SSL');
        $this->data['urlEnableSelected'] = $this->url->link('catalog/import/enable', $this->buildUrlParameterString($this->parameters) . '&what=selectedItems', 'SSL');
        $this->data['urlSyncAll'] = $this->url->link('catalog/import/synchronize', $this->buildUrlParameterString($this->parameters) . '&what=all', 'SSL');
        $this->data['urlSyncSelected'] = $this->url->link('catalog/import/synchronize', $this->buildUrlParameterString($this->parameters) . '&what=selectedItems', 'SSL');
        $this->data['urlSyncSelectedNoImages'] = $this->url->link('catalog/import/synchronizeWithoutImages', $this->buildUrlParameterString($this->parameters) . '&what=selectedItems', 'SSL');

        $page = $this->parameters['page']; unset($this->parameters['page']);
        $pagination = new Pagination(
            $page,
            $this->config->get('config_admin_limit'),
            ImportProductDAO::getInstance()->getImportedProductsQuantity($filter),
            $this->language->get('text_pagination'),
            $this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters) . '&page={page}', 'SSL')
        );
        $this->data['pagination'] = $pagination->render();

        $this->data = array_merge($this->data, $this->parameters);
        $this->setBreadcrumbs();
        $this->template = 'catalog/importProductsList.tpl.php';
        $this->children = array(
            'common/header',
            'common/footer'
        );
        $this->getResponse()->setOutput($this->render());
    }

    private function getProductActions($product) {
        return array();
    }

    public function index() {
        $this->showList();
    }

    protected function initParameters() {
        parent::initParameters();
        $this->parameters['filterIsActive'] = is_numeric($this->getRequest()->getParam('filterIsActive')) ? $this->getRequest()->getParam('filterIsActive') : null;
        $this->parameters['filterItem'] = $this->getRequest()->getParam('filterItem');
        $this->parameters['filterLocalProductId'] =
            is_numeric($this->getRequest()->getParam('filterLocalProductId')) || in_array($this->getRequest()->getParam('filterLocalProductId'), ['*', 'NULL'])
                ? $this->getRequest()->getParam('filterLocalProductId')
                : null;
        $this->parameters['filterSourceSiteClassName'] = $this->getRequest()->getParam('filterSourceSiteClassName', array());
        $this->parameters['page'] = $this->getRequest()->getParam('page', 1);
        $this->parameters['selectedItems'] = $this->getRequest()->getParam('selectedItems', array());
        $this->parameters['token'] = $this->session->data['token'];
        $this->parameters['what'] = $this->getRequest()->getParam('what');
    }

    protected function loadStrings() {
        $this->data['headingTitle'] = $this->language->get('headingTitle');
        $this->data['heading_title'] = $this->data['headingTitle'];
        $this->data['textActions'] = $this->language->get('ACTIONS');
        $this->data['textDeleteAll'] = $this->language->get('DELETE_ALL');
        $this->data['textDeleteSelected'] = $this->language->get('DELETE_SELECTED');
        $this->data['textDisableAll'] = $this->language->get('DISABLE_ALL');
        $this->data['textDisableInactive'] = $this->language->get('DISABLE_INACTIVE');
        $this->data['textDisableSelected'] = $this->language->get('DISABLE_SELECTED');
        $this->data['textEnableSelected'] = $this->language->get('ENABLE_SELECTED');
        $this->data['textFilter'] = $this->language->get('FILTER');
        $this->data['textId'] = $this->language->get('ID');
        $this->data['textImage'] = $this->language->get('ITEM_IMAGE');
        $this->data['textItem'] = $this->language->get('ITEM');
        $this->data['textLocal'] = $this->language->get('LOCAL');
        $this->data['textMinimalAmount'] = $this->language->get('MINIMAL_AMOUNT');
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
        $this->data['textUpdateSelectedNoImages'] = $this->language->get('UPDATE_SELECTED_NO_IMAGES');
        $this->data['textViewImportStatus'] = $this->language->get('VIEW_IMPORT_STATUS');
    }

    /**
     * @param bool $synchronizeImages
     */
    public function synchronize($synchronizeImages = true) {
        foreach ($this->getProductsToSynchronize() as $productToSynchronize) {
            if ($productToSynchronize->getLocalProductId()) {
                $this->updateFromSource($productToSynchronize, $synchronizeImages);
            } else {
                $this->addFromSource($productToSynchronize, $synchronizeImages);
            }
        }
        unset($this->parameters['selectedItems']);
        $this->redirect($this->url->link('catalog/import', $this->buildUrlParameterString($this->parameters)));
    }

    public function synchronizeWithoutImages() {
        $this->synchronize(false);
    }

    /**
     * @param ImportProduct $productToUpdate
     * @param bool $synchronizeImages
     * @throws Exception
     */
    private function updateFromSource($productToUpdate, $synchronizeImages = true) {
        $localProduct = $this->modelCatalogProduct->getProduct($productToUpdate->getLocalProductId());
        $thumbnail = null; $images = array();
        $imageDescription = $localProduct['image_description'] ? $localProduct['image_description'] : $productToUpdate->getDescription();
        if ($synchronizeImages) {
            $this->getImages($productToUpdate, $images, $thumbnail);
            $imageDescription = $this->updateDescriptionImages($productToUpdate->getId(), $imageDescription);
        }
        /// Preparing name, korean name and description
//        $product_description = array();
//        foreach ($this->load->model('localisation/language')->getLanguages() as $language) {
//            $product_description[$language['language_id']] = array(
//                'name' => $productToUpdate->getName(),
//                'description' => null
//            );
//        }

        /// Copying product options in order to preserve ones
        $localProductOptions = $this->modelCatalogProduct->getProductOptions($productToUpdate->getLocalProductId());
        /// Copying product categories in order to preserve ones
        $localProductCategories = $this->modelCatalogProduct->getProductCategories($productToUpdate->getLocalProductId());
        $this->modelCatalogProduct->editProduct($productToUpdate->getLocalProductId(), array(
            'date_available' => $localProduct['date_available'],
            'image_description' => $imageDescription,
            'height' => null,
            'image' => $thumbnail,
            'length' => null, 'length_class_id' => 1,
            'location' => null,
            'manufacturer_id' => $productToUpdate->getSourceSite()->getDefaultManufacturer()->getId(),
            'meta_keywords' => null, 'meta_description' => null,
            'minimum' => $productToUpdate->getMinimalAmount(),
            'model' => $localProduct['model'],
            'points' => null,
            'price' => $productToUpdate->getSourcePrice()->getPrice() * $productToUpdate->getSourceSite()->getRegularCustomerPriceRate(),
//            'product_attribute' => array($koreanName, $sourceUrl),
            'product_category' => $localProductCategories,
            'product_description' => $this->modelCatalogProduct->getProductDescriptions($productToUpdate->getLocalProductId()), //$product_description,
            'product_image' => $images,
            'product_option' => $localProductOptions,
            'product_special' => $this->getSpecialPrices($productToUpdate),
            'product_store' => $productToUpdate->getSourceSite()->getStores(),
            'product_tag' => null,
            'seo_title' => null, 'seo_h1' => null,
            'shipping' => 1,
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

        switch ($this->getRequest()->get['a']) {
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
        $this->getResponse()->setOutput(json_encode($status));
    }

    public function start() {
        if (!$this->status()) {
            $sites = implode(',', $this->parameters['selectedItems']);
            //$pid = shell_exec("nohup php -f " . DIR_AUTOMATION . "/crawler.cli.adapter.php $sites > /dev/null 2>&1 & printf \"%u\" $!");
//            chdir(DIR_AUTOMATION);
            //$pid = shell_exec("php -f crawler.php $sites > import.log 2>&1 & printf \"%u\" $!");
            file_put_contents(DIR_AUTOMATION . '/crawler.lck', $sites);
            $this->getResponse()->setOutput(json_encode(array(
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
        $this->getResponse()->setOutput(null);
    }

    /**
     * @return array|\model\catalog\ImportProduct[]
     */
    private function getProductsToSynchronize() {
        $productsToSynchronize = array();
        if ($this->parameters['what'] == 'selectedItems') {
            $productsToSynchronize = ImportProductDAO::getInstance()->getImportedProducts(array('selectedItems' => $this->parameters['selectedItems']));
            return $productsToSynchronize;
        } elseif ($this->parameters['what'] == 'all') {
            $filter = $this->parameters;
            unset($filter['selectedItems']);
            $productsToSynchronize = ImportProductDAO::getInstance()->getImportedProducts($filter);
            return $productsToSynchronize;
        }
        return $productsToSynchronize;
    }

    /**
     * @param ImportProduct $product
     * @param &$images
     * @param &$thumbnail
     * @throws Exception
     */
    private function getImages($product, &$images, &$thumbnail) {
        $localProduct = $this->modelCatalogProduct->getProduct($product->getLocalProductId());
        foreach ($this->modelCatalogProduct->getProductImages($product->getLocalProductId()) as $image) {
            if (file_exists(DIR_IMAGE . $image['image']) && is_file(DIR_IMAGE . $image['image'])) {
                unlink(DIR_IMAGE . $image['image']);
            }
        }
        if (file_exists(DIR_IMAGE . $localProduct['image']) && is_file(DIR_IMAGE . $localProduct['image'])) {
            unlink(DIR_IMAGE . $localProduct['image']);
        }
        /** @var \admin\model\tool\ModelToolImage $modelToolImage */
        $modelToolImage = $modelToolImage = new \catalog\model\tool\ModelToolImage($this->getRegistry());
        try {
            $thumbnail = $modelToolImage->download($product->getThumbnailUrl());
        } catch (Exception $e) {
            $error = "Couldn't download a thumbnail '" . $product->getThumbnailUrl() .
                "' for product " . $product->getId();
            $this->getLogger()->write($error);
            $this->getLogger()->write($e->getMessage());
            $this->data['notifications']['error'] .= "$error<br />";
        }
        foreach ($product->getImages() as $imageUrl) {
            try {
                $images[] = array('image' => $modelToolImage->download($imageUrl));
            } catch (Exception $e) {
                $error = "Couldn't download an image '$imageUrl' for product " . $product->getId();
                $this->getLogger()->write($error);
                $this->data['notifications']['error'] .= "$error<br />";
            }
        }
    }

    /**
     * @param int $id
     * @param string $description
     * @return string
     * @throws Exception
     */
    private function updateDescriptionImages($id, $description) {
        /** @var \admin\model\tool\ModelToolImage $modelToolImage */
        $modelToolImage = $modelToolImage = new \catalog\model\tool\ModelToolImage($this->getRegistry());
        /// Download images in image description
        $html = new \simple_html_dom($description);
        $descriptionImages = $html->find('img');
        foreach ($descriptionImages as $image) {
            try {
                $image->attr['src'] = HTTP_IMAGE . $modelToolImage->download($image->attr['src']);
            } catch (Exception $e) {
                $error = "Couldn't download an image '" . $image->attr['src'] . "'' for product $id.  Original image will be used instead";
                $this->getLogger()->write($error);
                $this->data['notifications']['error'] .= "$error<br />";
            }
        }
        return $html->__toString();
    }

}