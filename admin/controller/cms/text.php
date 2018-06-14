<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 23.1.13
 * Time: 19:20
 * To change this template use File | Settings | File Templates.
 */
class ControllerCmsText extends \system\engine\Controller
{
    private $modelContentManagementEditText;

    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->modelContentManagementEditText = $this->load->model('cms/text');
        $this->load->language('cms/text');
    }

    public function edit()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
            foreach ($this->parameters['text'] as $languageId => $text)
                $this->modelContentManagementEditText->updateText(array(
                    'contentId' => $this->parameters['contentId'],
                    'languageId' => $languageId,
                    'title' => $this->language->get('ORDER_CONFIRMATION'),
                    'text' => $text
                ));
//        else
//            $this->modelContentManagementEditText->initText($this->parameters['contentId']);

        $this->showForm();
    }

    protected function initParameters()
    {
        $this->parameters['contentId'] = empty($_REQUEST['contentId']) ? null : $_REQUEST['contentId'];
        $this->parameters['text'] = empty($_REQUEST['text']) ? array() : $_REQUEST['text'];
        $this->parameters['token'] = $this->session->data['token'];
    }

    protected function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text' => $this->data['headingTitle'],
            'href' => '',
            'separator' => ' :: '
        );
    }

    private function showForm()
    {
        $this->data = $this->parameters;
        $modelLanguage = $this->load->model('localisation/language');
        $languages = $modelLanguage->getLanguages();
        $texts = $this->modelContentManagementEditText->getText($this->parameters['contentId']);
//        $this->log->write(print_r($texts, true));
        foreach ($languages as $language)
        {
            $textFound = false;
            foreach ($texts as $text)
                if ($text['language_id'] == $language['language_id'])
                {
                    $this->data['text'][$language['language_id']] = $text['text'];
                    $textFound = true;
                    if ($this->config->get('config_language_id') == $language['language_id'])
                        $title = $text['title'];
                    break;
                }
            if (!$textFound && empty($this->data['text'][$language['language_id']]))
                $this->data['text'][$language['language_id']] = '';
        }
        $this->log->write(print_r($this->data['text'][$this->config->get('config_language_id')], true));
        $this->data['languages'] = $languages;
        $this->data['headingTitle'] = sprintf($this->language->get('HEADING_TITLE'), $title);
        $this->data['textCancel'] = $this->language->get('CANCEL');
        $this->data['textImageManager'] = $this->language->get('IMAGE_MANAGER');
        $this->data['textSave'] = $this->language->get('SAVE');

        $this->data['urlCancel'] = $this->url->link('common/home', 'token=' . $this->parameter['token'], 'SSL');
        $this->data['urlFileManager'] = $this->url->link('common/filemanager', 'token=' . $this->parameters['token'], 'SSL');
        $this->data['urlSaveForm'] = $this->url->link('cms/text/edit', 'token=' . $this->parameters['token'], 'SSL');
//        $this->log->write(print_r($this->data, true));
        $this->setBreadcrumbs();
        $this->children = array('common/header', 'common/footer');
        $this->template = 'cms/textForm.php';
        $this->getResponse()->setOutput($this->render());
    }
}
