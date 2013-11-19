<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 19.8.12
 * Time: 16:33
 * To change this template use File | Settings | File Templates.
 */
class ControllerProductRepurchase extends Controller
{
    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->language->load('product/repurchase');
        $this->data['heading_title'] = $this->language->get('HEADING_TITLE');
    }

    public function downloadImage()
    {
        if (preg_match('/https?:\/\/([\w\-\.]+)/', $this->request->get['url']))
        {
            $fileName = $this->getImageFileName($this->request->get['url']);
            if ($fileName)
            {
                $dirName = DIR_IMAGE . 'upload/' . session_id();
                if (!file_exists($dirName))
                    mkdir($dirName);
                file_put_contents($dirName . '/' . $fileName, file_get_contents($this->request->get['url']));
                $json['filePath'] = 'upload/' . session_id() . '/' . $fileName;
            }
            else
            {
                $this->load->language('product/repurchase');
                $json['warning'] = $this->language->get('WARNING_HTML_PAGE_PROVIDED');
                $json['filePath'] = $this->request->get['url'];
            }
        }
        else
            $json['error'] = "Doesn't seem to be URL";

        $this->response->setOutput(json_encode($json));
    }

    private function getImageFileName($fileName)
    {
        $this->log->write(image_type_to_extension(@exif_imagetype($fileName)));
        if (@exif_imagetype($fileName))
            return time() . image_type_to_extension(exif_imagetype($fileName));
        else
            return '';
    }

    public function index()
    {
        //$this->log->write(print_r(session_id(), true));
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('HEADING_TITLE'),
            'href'      => $this->url->link('account/repurchase_order', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('ADD_AGENT_ORDER'),
            'href'      => $_SERVER['REQUEST_URI'] ,
            'separator' => $this->language->get('text_separator')
        );

//        $this->data['action'] = $this->url->link('product/repurchaseOrder/createOrder', '', 'SSL');
        $this->data['entry_color'] = $this->language->get('COLOR');
        $this->data['entry_fee'] = $this->language->get('field_fee');
        $this->data['entry_image_path'] = $this->language->get('IMAGE_PATH');
        $this->data['entry_item_url'] = $this->language->get('ITEM_URL');
        $this->data['entry_original_price'] = $this->language->get('field_original_price');
        $this->data['entry_quantity'] = $this->language->get('QUANTITY');
        $this->data['entry_size'] = $this->language->get('SIZE');
        $this->data['entry_subtotal'] = $this->language->get('field_subtotal');
        $this->data['entry_total'] = $this->language->get('field_total');
        $this->data['button_add'] = $this->language->get('ADD');
        $this->data['textAddToCart'] = $this->language->get('button_cart');
        $this->data['textApproximatePrice'] = $this->language->get('APPROXIMATE_PRICE');
        $this->data['textComment'] = $this->language->get('COMMENT');
        $this->data['textCustomerBuys'] = $this->language->get('CUSTOMER_BUYS');
        $this->data['textItemName'] = $this->language->get('ITEM_NAME');
        $this->data['textShopBuys'] = $this->language->get('SHOP_BUYS');
        $this->data['textShopName'] = $this->language->get('SHOP_NAME');
        $this->data['textUploadFile'] = $this->language->get('UPLOAD_FILE');
        $this->data['button_delete'] = $this->language->get('button_delete');

        $this->data['order_items'][] = array(
            'order_item_id'     => 'new0',
            'image_path'        => '',
            'item_name'         => '',
            'item_url'          => '',
            'size'              => '',
            'color'             => '',
            'original_price'    => '',
            'quantity'          => '',
            'shop_name'         => '',
            'subtotal'          => '',
            'fee'               => '',
            'total'             => ''
        );

        $template_name = '/template/product/repurchaseOrderForm.tpl';
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . $template_name)) {
            $this->template = $this->config->get('config_template') . $template_name;
        } else {
            $this->template = 'default' . $template_name;
        }

        $this->children = array(
            'common/column_right',
            'common/footer',
            'common/header'
        );

        $this->response->setOutput($this->render());
    }

    public function uploadImage()
    {
        //$this->log->write(print_r($_FILES, true));
        foreach ($_FILES as $file)
            if (is_uploaded_file($file['tmp_name']))
            {
                if (!file_exists(DIR_IMAGE . 'upload/' . session_id()))
                    mkdir(DIR_IMAGE . 'upload/' . session_id());
                move_uploaded_file($file['tmp_name'], DIR_IMAGE . 'upload/' . session_id() . '/' . $file['name']);
                $json['filePath'] = 'upload/' . session_id() . '/' . $file['name'];
            }
        $this->response->setOutput(json_encode($json));
    }
}
