<?php
use system\engine\Controller;

class Controllercommontecdocmodule extends \system\engine\Controller {
    public function index() {

        //Save customer group ID for TDMod
        $_SESSION['TDM_CMS_USER_GROUP'] = intval($this->customer->getCustomerGroupId());
        $_SESSION['TDM_CMS_DEFAULT_CUR'] = $this->config->get('config_currency');

        //TecDoc
        if(defined('TDM_TITLE')){$this->document->setTitle(TDM_TITLE);}
        if(defined('TDM_KEYWORDS')){$this->document->setKeywords(TDM_KEYWORDS);}
        if(defined('TDM_DESCRIPTION')){$this->document->setDescription(TDM_DESCRIPTION);}

        if (isset($this->request->get['route'])) {
            $this->document->addLink(HTTP_SERVER, 'canonical');
        }

        $file = '/template/common/tecdoc_module.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $file)) {
            $this->template = $this->config->get('config_template') . $file;
        } else {
            $this->template = 'default' . $file;
        }

//        $data['column_left'] = $this->load->controller('common/column_left');
//        $data['column_right'] = $this->load->controller('common/column_right');
//        $data['content_top'] = $this->load->controller('common/content_top');
//        $data['content_bottom'] = $this->load->controller('common/content_bottom');
//        $data['footer'] = $this->load->controller('common/footer');
//        $data['header'] = $this->load->controller('common/header');
        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/content_top',
            'common/content_bottom',
            'common/footer',
            'common/header'
        );

//
//        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/common/tecdoc_module.tpl')) {
//            $this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/common/tecdoc_module.tpl', $data));
//        } else {
//            $this->response->setOutput($this->load->view('default/template/common/tecdoc_module.tpl', $data));
//        }
        $this->getResponse()->setOutput($this->render());
    }
}