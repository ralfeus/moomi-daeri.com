<?php

use model\catalog\ManufacturerDAO;
use model\catalog\SupplierDAO;
use model\extension\ImportSourceSite;
use model\extension\ImportSourceSiteDAO;
use model\setting\ExtensionDAO;

class ControllerExtensionImport extends Controller {
    public function __construct($registry) {
        parent::__construct($registry);
        $this->load->language('extension/import');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->data['headingTitle'] = $this->language->get('heading_title');
    }

    public function edit() {
        global $_SERVER;

        /// Initialize general elements
        $this->data = array_merge($this->data, $this->parameters);
        $this->data['manufacturers'] = ManufacturerDAO::getInstance()->getManufacturers();
        $this->data['suppliers'] = SupplierDAO::getInstance()->getSuppliers();

        $this->data['textCancel'] = $this->language->get("CANCEL");
        $this->data['textClassName'] = $this->language->get("CLASS");
        $this->data['textDefaultCategories'] = $this->language->get("DEFAULT_CATEGORIES");
        $this->data['textDefaultManufacturer'] = $this->language->get("DEFAULT_MANUFACTURER");
        $this->data['textDefaultSupplier'] = $this->language->get("DEFAULT_SUPPLIER");
        $this->data['textRegularCustomerPriceRate'] = $this->language->get("PRICE_RATE_REGULAR_CUSTOMER");
        $this->data['textSave'] = $this->language->get("SAVE");
        $this->data['textSaveContinueEdit'] = $this->language->get("SAVE_CONTINUE_EDIT");
        $this->data['textSiteName'] = $this->language->get("SITE");
        $this->data['textStores'] = $this->language->get("STORES");
        $this->data['textWholesaleCustomerPriceRate'] = $this->language->get("PRICE_RATE_WHOLESALE_CUSTOMER");
        $this->data['urlAction'] = $this->selfUrl;
        $this->data['urlList'] = $this->url->link('extension/import', 'token=' . $this->parameters['token'], 'SSL');
        $this->setBreadcrumbs();
        $this->template = 'extension/importForm.tpl.php';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        /// Process site data
        if (!is_null($this->parameters['importClass'])) {
            $importSite = ImportSourceSiteDAO::getInstance()->getSourceSite($this->parameters['importClass']);
        } else {
            $importSite = new ImportSourceSite(0);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $this->editGET($importSite);
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->editPOST($importSite);
        }

        $this->response->setOutput($this->render());
    }

    /**
     * @param ImportSourceSite $importSite
     * @throws Exception
     */
    private function editGET($importSite) {
        $this->data['importSite'] = $importSite;

    }

    /**
     * @param ImportSourceSite $importSite
     */
    private function editPOST($importSite) {

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
                        'href' => $this->url->link('extension/import/install', 'token=' . $this->session->data['token'] . "&importClass=$importClass", 'SSL')
                    );
                } else {
                    $action[] = array(
                        'text' => $this->language->get('text_edit'),
                        'href' => $this->url->link('extension/import/edit', 'token=' . $this->session->data['token'] . "&importClass=$importClass", 'SSL')
                    );

                    $action[] = array(
                        'text' => $this->language->get('text_uninstall'),
                        'href' => $this->url->link('extension/import/uninstall', 'token=' . $this->session->data['token'] . "&importClass=$importClass", 'SSL')
                    );
                }

                $this->data['extensions'][] = array(
                    'name'   => $sourceSiteName,
                    'action' => $action
                );
            }
        }

        $this->template = 'extension/importList.tpl.php';
        $this->children = array(
            'common/header',
            'common/footer'
        );

        $this->response->setOutput($this->render());
    }

    protected function initParameters() {
        $this->initParametersWithDefaults(array(
            'importClass' => null,
            'token' => $this->session->data['token']
        ));
    }

    public function install() {
        if (!$this->user->hasPermission('modify', 'extension/import')) {
            $this->session->data['notifications']['error'] = $this->language->get('error_permission');
            $this->redirect($this->url->link('extension/import', 'token=' . $this->parameters['token'], 'SSL'));
        } else {
            $this->redirect($this->url->link('extension/import/create', $this->buildUrlParameterString($this->parameters), 'SSL'));
        }
    }

    protected function setBreadcrumbs() {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('headingTitle'),
            'href'      => '#',
            'separator' => ' :: '
        );
    }

    public function uninstall() {
        if (!$this->user->hasPermission('modify', 'extension/import')) {
            $this->session->data['notifications']['error'] = $this->language->get('error_permission');
        } else {
            ImportSourceSiteDAO::getInstance()->removeSourceSite($this->parameters['importClass']);
        }
        $this->redirect($this->url->link('extension/import', 'token=' . $this->session->data['token'], 'SSL'));
    }
}