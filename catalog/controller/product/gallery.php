<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.12.12
 * Time: 20:12
 * To change this template use File | Settings | File Templates.
 */
class ControllerProductGallery extends Controller
{
    private function getData()
    {
        $this->data['files'] = array();
        $this->log->write(print_r(glob(DIR_IMAGE . 'reviews/*'), true));
        foreach (glob(DIR_IMAGE . 'reviews/*') as $file)
            $this->data['images'][] = HTTP_IMAGE . 'reviews/' . basename($file);
    }

    public function index()
    {
        $this->getData();

        /// Set interface
        $this->setBreadcrumbs();
        $this->template = 'default/template/product/gallery.php';
        $this->children = array(
            'common/column_left',
            'common/column_right',
            'common/footer',
            'common/header'
        );
        $this->response->setOutput($this->render());
    }

    private function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => "::"
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('GALLERY'),
            'href'      => $this->url->link('product/gallery'),
            'separator' => "::"
        );
    }
}
