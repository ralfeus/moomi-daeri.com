<?php

use model\catalog\ImportCategory;
use model\catalog\ManufacturerDAO;
use model\catalog\SupplierDAO;
use model\extension\ImportSourceSite;
use model\extension\ImportSourceSiteDAO;
use system\engine\AdminController;

class ControllerExtensionImport extends AdminController {
    /** @var ImportSourceSite $model */
    private $model;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->getLoader()->language('extension/import');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['headingTitle'] = $this->language->get('heading_title');
    }

    public function edit() {
        $this->initModel();
        /// Initialize general elements
        $this->data = array_merge($this->data, $this->parameters);
        $this->data['manufacturers'] = ManufacturerDAO::getInstance()->getManufacturers();
        $this->data['suppliers'] = SupplierDAO::getInstance()->getSuppliers();

        $this->data['textAddEntry'] = $this->language->get("ADD_ENTRY");
        $this->data['textCancel'] = $this->language->get("CANCEL");
        $this->data['textClassName'] = $this->language->get("CLASS");
        $this->data['textDefaultCategories'] = $this->language->get("DEFAULT_CATEGORIES");
        $this->data['textDefaultItemWeight'] = $this->language->get("DEFAULT_ITEM_WEIGHT");
        $this->data['textDefaultManufacturer'] = $this->language->get("DEFAULT_MANUFACTURER");
        $this->data['textDefaultSupplier'] = $this->language->get("DEFAULT_SUPPLIER");
        $this->data['textLocalCategoryId'] = $this->language->get("LOCAL_CATEGORY_ID");
        $this->data['textImportMappedCategoriesOnly'] = $this->language->get("IMPORT_MAPPED_CATEGORIES_ONLY");
        $this->data['textRegularCustomerPriceRate'] = $this->language->get("PRICE_RATE_REGULAR_CUSTOMER");
        $this->data['textRemoveEntry'] = $this->language->get("DELETE");
        $this->data['textSave'] = $this->language->get("SAVE");
        $this->data['textSaveContinueEdit'] = $this->language->get("SAVE_CONTINUE_EDIT");
        $this->data['textSourceSiteCategoryId'] = $this->language->get("SOURCE_SITE_CATEGORY_ID");
        $this->data['textSiteName'] = $this->language->get("SITE");
        $this->data['textStores'] = $this->language->get("STORES");
        $this->data['textWholesaleCustomerPriceRate'] = $this->language->get("PRICE_RATE_WHOLESALE_CUSTOMER");
        $this->data['urlAction'] = $this->selfUrl;
        $this->data['urlList'] = $this->getUrl()->link('extension/import', 'token=' . $this->parameters['token'], 'SSL');
        $this->setBreadcrumbs();
        $this->children = array(
            'common/header',
            'common/footer'
        );

        /// Process site data
        $this->data['importSite'] = $this->model;
        if ($this->getRequest()->getMethod() == 'GET') {
            $this->editGET($this->model);
        } elseif ($this->getRequest()->getMethod() == 'POST') {
            $this->editPOST($this->model);
        }

        $this->getResponse()->setOutput($this->render('extension/importForm.tpl.php'));
    }

    /**
     * @param ImportSourceSite $importSite
     * @throws Exception
     */
    private function editGET($importSite) {

    }

    /**
     * @param ImportSourceSite $importSite
     */
    private function editPOST($importSite) {
        ImportSourceSiteDAO::getInstance()->saveSourceSite($importSite);
        if (!$this->parameters['continue']) {
            $this->redirect($this->data['urlList']);
        }
    }

    public function index() {
        $this->data['text_no_results'] = $this->language->get('text_no_results');
        $this->data['text_confirm'] = $this->language->get('text_confirm');

        $this->data['column_name'] = $this->language->get('column_name');
        $this->data['column_action'] = $this->language->get('column_action');

        $sourceSites = ImportSourceSiteDAO::getInstance()->getSourceSites();

        foreach ($sourceSites as $sourceSite) {
            if (!file_exists(DIR_ROOT . 'automation/SourceSite/' . $sourceSite->getClassName() . '.class.php')) {
                ImportSourceSiteDAO::getInstance()->removeSourceSite($sourceSite);
            }
        }

        $this->data['extensions'] = array();

        $files = glob(DIR_APPLICATION . '../automation/SourceSite/*.php');
        if ($files) {
            foreach ($files as $file) {
                $importClass = basename($file, '.class.php');
                try {
                    $importClassInfo = new ReflectionClass("automation\\SourceSite\\$importClass");
                    if ($importClassInfo->isAbstract()) { continue; }
                }
                catch (LogicException $e) { continue; }
                catch (ReflectionException $e) { continue; }

                $classInstalled = false;
                $sourceSiteName = '&lt;no&nbsp;name&gt;';
                foreach ($sourceSites as $sourceSite) {
                    if ($sourceSite->getClassName() == $importClass) {
                        $classInstalled = true;
                        $sourceSiteName = $sourceSite->getName();
                        break;
                    }
                }
                if (!$classInstalled) {
                    $sourceSiteName = $importClass;
                }
                $action = array();

                if (!$classInstalled) {
                    $action[] = array(
                        'text' => $this->language->get('text_install'),
                        'href' => $this->getUrl()->link('extension/import/install', 'token=' . $this->session->data['token'] . "&importClass=$importClass", 'SSL')
                    );
                } else {
                    $action[] = array(
                        'text' => $this->language->get('text_edit'),
                        'href' => $this->getUrl()->link('extension/import/edit', 'token=' . $this->session->data['token'] . "&importClass=$importClass", 'SSL')
                    );

                    $action[] = array(
                        'text' => $this->language->get('text_uninstall'),
                        'href' => $this->getUrl()->link('extension/import/uninstall', 'token=' . $this->session->data['token'] . "&importClass=$importClass", 'SSL')
                    );
                }

                $this->data['extensions'][] = array(
                    'name'   => $sourceSiteName,
                    'action' => $action
                );
            }
        }

        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->getResponse()->setOutput($this->render('extension/importList.tpl.php'));
    }

    protected function initModel() {
        if ($this->getRequest()->getMethod() == 'POST') {
            $categories  = [];
            foreach ($this->parameters['category'] as $category) {
                $categories[] = new ImportCategory(null, $category['source'], explode(',', $category['local']), null, null);
            }
            $this->model = new ImportSourceSite(
                $this->parameters['importClass'],
                $categories,
                $this->parameters['defaultCategories'],
                $this->parameters['defaultManufacturerId'],
                $this->parameters['defaultSupplierId'],
                $this->parameters['importMappedCategoriesOnly'],
                $this->parameters['siteName'],
                $this->parameters['regularCustomerPriceRate'],
                $this->parameters['stores'],
                $this->parameters['wholesaleCustomerPriceRate'],
                $this->parameters['defaultItemWeight']
            );
        } elseif (!is_null($this->parameters['importClass'])) {
            $this->model = ImportSourceSiteDAO::getInstance()->getSourceSite($this->parameters['importClass']);
        } else {
            $this->model = new ImportSourceSite(null);
        }
    }

    protected function initParameters() {
        parent::initParameters();
        $this->parameters['importMappedCategoriesOnly'] = !empty($_REQUEST['importMappedCategoriesOnly']) ? true : false;
        $this->parameters['defaultCategories'] = !empty($_REQUEST['defaultCategories']) ? explode(',', $_REQUEST['defaultCategories']) : array();
        $this->parameters['stores'] = !empty($_REQUEST['stores']) ? explode(',', $_REQUEST['stores']) : array();
        $this->initParametersWithDefaults([
            'category' => [],
            'continue' => 0,
            'defaultItemWeight' => 0,
            'defaultManufacturerId' => 0,
            'defaultSupplierId' => 0,
            'importClass' => null,
            'regularCustomerPriceRate' => 1,
            'siteName' => null,
            'token' => $this->session->data['token'],
            'wholesaleCustomerPriceRate' => 1
        ]);
    }

    public function install() {
        if (!$this->getUser()->hasPermission('modify', 'extension/import')) {
            $this->session->data['notifications']['error'] = $this->language->get('error_permission');
        } else {
            $sourceSiteName = "automation\\SourceSite\\" . $this->parameters['importClass'];
            /** @var \automation\ProductSource $sourceSiteName */
            $sourceSite = $sourceSiteName::createDefaultImportSourceSiteInstance();
            ImportSourceSiteDAO::getInstance()->addSourceSite($sourceSite);
        }
        $this->redirect($this->getUrl()->link('extension/import', 'token=' . $this->parameters['token'], 'SSL'));
    }

    public function uninstall() {
        if (!$this->getUser()->hasPermission('modify', 'extension/import')) {
            $this->getSession()->data['notifications']['error'] = $this->language->get('error_permission');
        } else {
            ImportSourceSiteDAO::getInstance()->removeSourceSite($this->parameters['importClass']);
        }
        $this->redirect($this->getUrl()->link('extension/import', 'token=' . $this->getSession()->data['token'], 'SSL'));
    }
}