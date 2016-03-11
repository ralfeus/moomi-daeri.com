<?php
use model\catalog\ManufacturerDAO;
use model\catalog\OptionValue;
use model\catalog\ProductDAO;
use model\catalog\Option;
use model\catalog\OptionDAO;
use model\catalog\ProductOption;
use model\catalog\ProductOptionValue;
use model\catalog\SupplierDAO;

class ControllerModuleMpchanges extends Controller {
    private $mpfilter = array();
    private $mp_message = array("type" => "success", "message" => array());
    
    private function loadFilter(){
        $data = array();

        $this->mpfilter["name"] = "";
        if (isset($this->request->post['name'])) {
            $this->mpfilter["name"] = $this->request->post['name'];
            $data['filter_name'] = $this->mpfilter["name"];
        }

        $this->mpfilter["model"] = "";
        if (isset($this->request->post['model'])) {
            $this->mpfilter["model"] = $this->request->post['model'];
            $data['filterModel'] = $this->mpfilter["model"];
        }

        $this->mpfilter["change_all"] = false;
        if (isset($this->request->post['change_all'])) {
            $this->mpfilter["change_all"] = true;
        }

        $this->mpfilter["change_ids"] = array();
        if (isset($this->request->post['product_to_change'])) {
            $this->mpfilter["change_ids"] = $this->request->post['product_to_change'];
        }

        if (isset($this->request->get['product_list'])) {
            $this->mpfilter["start"] = 0;
            $this->mpfilter["limit"] = 30;
            if (isset($this->request->get['start'])) {
                $this->mpfilter["start"] = $this->request->get['start'];
                $data['start'] = $this->mpfilter["start"];
            }
            if (isset($this->request->get['limit'])) {
                $this->mpfilter["limit"] = $this->request->get['limit'];
                $data['limit'] = $this->mpfilter["limit"];
            }
        }

        if ($this->request->post['filter_price_from'] > 0) {
            $this->mpfilter["filter_price_from"] = (int) $this->request->post['filter_price_from'];
            $data["filter_price_from"] = (int) $this->request->post['filter_price_from'];
        }

        if ($this->request->post['filter_price_to'] > 0) {
            $this->mpfilter["filter_price_to"] = (int) $this->request->post['filter_price_to'];
            $data["filter_price_to"] = (int) $this->request->post['filter_price_to'];
        }

        $this->mpfilter["change_special"] = false;
        if (isset($this->request->post['change_special'])) {
            $this->mpfilter["change_special"] = true;
        }

        $this->mpfilter["change_discount"] = false;
        if (isset($this->request->post['change_discount'])) {
            $this->mpfilter["change_discount"] = true;
        }

        $this->mpfilter["store_id"] = -1;
        if (isset($this->request->post['store_id'])) {
            if ($this->request->post['store_id'] >= 0){
                $this->mpfilter["store_id"] = $this->request->post['store_id'];
                $data['filter_store_id'] = $this->mpfilter["store_id"];
            }
        }

        $this->mpfilter["manufacturer_id"] = 0;
        if (isset($this->request->post['manufacturer_id'])) {
            $this->mpfilter["manufacturer_id"] = $this->request->post['manufacturer_id'];
            $data['filter_manufacturer_id'] = $this->mpfilter["manufacturer_id"];
        }

        $this->mpfilter["category_id"] = 0;
        if (isset($this->request->post['category_id'])) {
            $this->mpfilter["category_id"] = $this->request->post['category_id'];
            $data['filter_category_id'] = $this->mpfilter["category_id"];
        }

        $this->mpfilter["filter_sub_category"] = false;
        $data["filter_sub_category"] = false;
        if (isset($this->request->post['filter_sub_category'])) {
            $this->mpfilter["filter_sub_category"] = true;
            $data["filter_sub_category"] = true;
        }

        $this->mpfilter["manufacturer_price"] = 0;
        if (isset($this->request->post['manufacturer_price'])) {
            $this->mpfilter["manufacturer_price"] = $this->request->post['manufacturer_price'];
        }

        $this->mpfilter["manufacturer_quantities"] = 0;
        if (isset($this->request->post['manufacturer_quantities'])) {
            $this->mpfilter["manufacturer_quantities"] = $this->request->post['manufacturer_quantities'];
        }

        $this->mpfilter["customer_group"] = 0;
        if (isset($this->request->post['customer_group'])) {
            $this->mpfilter["customer_group"] = $this->request->post['customer_group'];
        }

        $this->mpfilter["change_type"] = "percent";
        if (isset($this->request->post['change_type'])) {
            $this->mpfilter["change_type"] = $this->request->post['change_type'];
        }

        $this->mpfilter["change_type_quantities"] = "percent";
        if (isset($this->request->post['change_type_quantities'])) {
            $this->mpfilter["change_type_quantities"] = $this->request->post['change_type_quantities'];
        }

        $this->mpfilter["quantities_diff"] = "-";
        if (isset($this->request->post['quantities_diff'])) {
            switch ($this->request->post['quantities_diff']){
                case '-':
                    $this->mpfilter["quantities_diff"] = '-';
                    break;
                case '+':
                    $this->mpfilter["quantities_diff"] = '+';
                    break;
                case '*':
                    $this->mpfilter["quantities_diff"] = '*';
                    break;
                case '/':
                    $this->mpfilter["quantities_diff"] = '/';
                    break;
                case '=':
                    $this->mpfilter["quantities_diff"] = '=';
                    break;
            }
        }

        $this->mpfilter["price_diff"] = '-';
        if (isset($this->request->post['price_diff'])) {
            switch ($this->request->post['price_diff']){
                case '-':
                    $this->mpfilter["price_diff"] = '-';
                    break;
                case '+':
                    $this->mpfilter["price_diff"] = '+';
                    break;
                case '*':
                    $this->mpfilter["price_diff"] = '*';
                    break;
                case '/':
                    $this->mpfilter["price_diff"] = '/';
                    break;
                case '=':
                    $this->mpfilter["price_diff"] = '=';
                    break;
            }
        }

        $this->mpfilter["round_decimal"] = 0;
        if (isset($this->request->post['filter_round'])) {
            $this->mpfilter["round_decimal"] = (int)$this->request->post['filter_round'];
        }

        $this->load->model('module/mpchanges');
        $this->mpfilter["products"] = $this->model_module_mpchanges->getProducts($data);
        $this->mpfilter["total"] = $this->model_module_mpchanges->getTotalProducts($data);
    }

    public function getOptionValues() {
        $result = OptionDAO::getInstance()->getOptionValues($this->parameters['optionId']);
        $json = [];
        foreach ($result as $optionValue) {
            $json[] = ['id' => $optionValue->getId(), 'text' => $optionValue->getName()];
        }
        $this->getResponse()->setOutput(json_encode($json));
    }

	public function index() {
        $this->load->language('module/mpchanges');
        $this->load->model('catalog/category');
        $this->load->model('setting/store');
        $this->load->model('sale/customer_group');

        $this->setBreadcrumbs([[
            'text' => $this->language->get('text_module'),
            'route' => 'extension/module'
        ]]);

        if (isset($this->error['warning'])) {
            $this->data['error_warning'] = $this->error['warning'];
        } else {
            $this->data['error_warning'] = '';
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $this->data['token'] = $this->session->data['token'];
        $this->data['url_cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_change_price'] = $this->url->link('module/mpchanges/changeprice', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_change_specials'] = $this->url->link('module/mpchanges/changespecial', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_save_specials'] = $this->url->link('module/mpchanges/addspecial', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_change_discounts'] = $this->url->link('module/mpchanges/changediscounts', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_save_discounts'] = $this->url->link('module/mpchanges/adddiscounts', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['action_del_elements'] = $this->url->link('module/mpchanges/delelements', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['urlGetOptionValues'] = $this->url->link('module/mpchanges/getOptionValues', 'token=' . $this->session->data['token'], 'SSL');
        $this->data['urlSetOption'] = $this->url->link('module/mpchanges/setOption', 'token=' . $this->session->data['token'], 'SSL');

        $this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
        $this->data['manufacturers'] = ManufacturerDAO::getInstance()->getManufacturers();
        $this->data['suppliers'] = SupplierDAO::getInstance()->getSuppliers();
        $this->data['categories'] = $this->model_catalog_category->getCategories();
        $this->data['options'] = OptionDAO::getInstance()->getOptions();

		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->getResponse()->setOutput($this->render('module/mpchanges.tpl.php'));
	}

    protected function initParameters() {
        $this->initParametersWithDefaults([
            'operation' => null,
            'optionId' => null,
            'optionValue' => null,
//            'optionValueType' => null,
            'products' => []
        ]);
    }

    public function loadFilteredProducts() {
        $json = array();
        $this->loadFilter();

        $json['products'] = $this->mpfilter['products'];
        $json['total'] = $this->mpfilter['total'];

        $this->getResponse()->setOutput(json_encode($json));
    }

    protected function loadStrings() {
        $this->data['heading_title'] = $this->language->get('heading_title');
        $this->data['button_cancel'] = $this->language->get('button_cancel');
        $this->data['button_change_price'] = $this->language->get('button_change_price');
        $this->data['button_change_specials'] = $this->language->get('button_change_specials');
        $this->data['button_add_specials'] = $this->language->get('button_add_specials');
        $this->data['button_change_discounts'] = $this->language->get('button_change_discounts');
        $this->data['button_add_discounts'] = $this->language->get('button_add_discounts');
        $this->data['button_del_elements'] = $this->language->get('button_del_elements');
        $this->data['button_change_quantities'] = $this->language->get('button_change_quantities');

        $this->data['button_add_row'] = $this->language->get('button_add_row');
        $this->data['button_remove'] = $this->language->get('button_remove');

        $this->data['tab_prices'] = $this->language->get('tab_prices');
        $this->data['tab_specials'] = $this->language->get('tab_specials');
        $this->data['tab_add_specials'] = $this->language->get('tab_add_specials');
        $this->data['tab_discounts'] = $this->language->get('tab_discounts');
        $this->data['tab_add_discounts'] = $this->language->get('tab_add_discounts');
        $this->data['tab_del_section'] = $this->language->get('tab_del_section');

        $this->data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
        $this->data['textSupplier'] = $this->language->get('SUPPLIER');
        $this->data['entry_category'] = $this->language->get('entry_category');
        $this->data['entry_price'] = $this->language->get('entry_price');
        $this->data['entry_specials'] = $this->language->get('entry_specials');
        $this->data['entry_discounts'] = $this->language->get('entry_discounts');
        $this->data['entry_quantities'] = $this->language->get('entry_quantities');
        $this->data['entry_model'] = $this->language->get('entry_model');
        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_store'] = $this->language->get('entry_store');
        $this->data['entry_subcategory'] = $this->language->get('entry_subcategory');
        $this->data['store_default'] = $this->language->get('store_default');

        $this->data['entry_customer_group'] = $this->language->get('entry_customer_group');
        $this->data['entry_date_start'] = $this->language->get('entry_date_start');
        $this->data['entry_date_end'] = $this->language->get('entry_date_end');
        $this->data['entry_priority'] = $this->language->get('entry_priority');
        $this->data['entry_price_diff'] = $this->language->get('entry_price_diff');

        $this->data['text_filtered_products'] = $this->language->get('text_filtered_products');

        $this->data['text_del_product'] = $this->language->get('text_del_product');
        $this->data['text_del_special'] = $this->language->get('text_del_special');
        $this->data['text_del_discount'] = $this->language->get('text_del_discount');

        $this->data['text_change_discount'] = $this->language->get('text_change_discount');

        $this->data['text_change_special'] = $this->language->get('text_change_special');
        $this->data['text_all'] = $this->language->get('text_all');

        $this->data['option_empty'] = $this->language->get('option_empty');
        $this->data['option_all'] = $this->language->get('option_all');

        $this->data['label_round'] = $this->language->get('label_round');
        $this->data['label_round_decimal'] = $this->language->get('label_round_decimal');
        $this->data['label_percent'] = $this->language->get('label_percent');
        $this->data['label_number'] = $this->language->get('label_number');

        $this->data['label_price'] = $this->language->get('label_price');
        $this->data['label_price_from'] = $this->language->get('label_price_from');
        $this->data['label_price_to'] = $this->language->get('label_price_to');

        $this->data['label_quantity_prefix'] = $this->language->get('label_quantity_prefix');
        $this->data['label_quantity_postfix'] = $this->language->get('label_quantity_postfix');
        $this->data['textAddOption'] = $this->language->get('ADD_OPTION');
        $this->data['textAddOptionValue'] = $this->language->get('ADD_OPTION_VALUE');
        $this->data['textDelOption'] = $this->language->get('DEL_OPTION');
        $this->data['textDelOptionValue'] = $this->language->get('DEL_OPTION_VALUE');
        $this->data['textExecute'] = $this->language->get('EXECUTE');
        $this->data['textOptions'] = $this->language->get('OPTIONS');
        $this->data['textOptionValues'] = $this->language->get('OPTION_VALUES');
    }

    private function getDiffValue($value, $diff, $diffValue, $type = "number"){
        $newValue = $value;

        switch ($diff){
            case '-':
                if ($type == "percent"){
                    $newValue = $value * (1 - $diffValue / 100);
                }else{
                    $newValue = $value - $diffValue;
                }
                break;
            case '+':
                if ($type == "percent"){
                    $newValue = $value * (1 + $diffValue / 100);
                }else{
                    $newValue = $value + $diffValue;
                }
                break;
            case '*':
                if ($type == "percent"){
                    $newValue = $value * (1 + $diffValue / 100);
                }else{
                    $newValue = $value * $diffValue;
                }
                break;
            case '/':
                if ($type == "percent"){
                    $newValue = $value * (1 - $diffValue / 100);
                }else{
                    $newValue = $value / $diffValue;
                }
                break;
            case '=':
                if ($type == "percent"){
                    $newValue = $value * ($diffValue / 100);
                }else{
                    $newValue = $diffValue;
                }
                break;
        }

        return $newValue;
    }

    public function changeprice() {
        $this->load->language('module/mpchanges');
        $this->load->model('module/mpchanges');
        $this->load->model('setting/store');
        $this->loadFilter();

        $changed = array();
        if ($this->mpfilter["products"]){
            foreach ($this->mpfilter["products"] as $product){
                if (in_array($product['product_id'],$this->mpfilter['change_ids']) or $this->mpfilter['change_all']){
                    $changed[$product['product_id']] = $product;
                    if ($this->mpfilter["manufacturer_quantities"] > 0){
                        $store_quantity = $this->getDiffValue($product['quantity'], $this->mpfilter["quantities_diff"], $this->mpfilter["manufacturer_quantities"]);
                        if ($store_quantity < 0) $store_quantity = 0;
                        $this->model_module_mpchanges->editProductQuantity($product['product_id'], $store_quantity);
                        $changed[$product['product_id']]['qty'][$this->mpfilter["store_id"]] = $store_quantity;
                    }
                    if ($this->mpfilter["manufacturer_price"] > 0){
                        $store_price = $this->roundPrice($this->getDiffValue($product['price'], $this->mpfilter["price_diff"], $this->mpfilter["manufacturer_price"], $this->mpfilter["change_type"]), $this->mpfilter['round_decimal']);

                        if ($store_price < 0) $store_price = 0;

                        $this->model_module_mpchanges->editProductPrice($product['product_id'], $store_price);
                        $changed[$product['product_id']]['price'][$this->mpfilter["store_id"]] = $store_price;

                        if ($this->mpfilter["change_special"]){
                            $specials = $this->model_module_mpchanges->getProductCurrentSpecials($product['product_id']);
                            foreach ($specials as $special) {
                                if ($special['customer_group_id'] == $this->mpfilter["customer_group"] or $this->mpfilter["customer_group"] == 0){
                                    $special['product_id'] = $product['product_id'];

                                    $special['price'] = $this->roundPrice($this->getDiffValue($special['price'], $this->mpfilter["price_diff"], $this->mpfilter["manufacturer_price"], $this->mpfilter["change_type"]), $this->mpfilter['round_decimal']);

                                    if ($product['price'] < $special['price']) $special['price'] = $product['price'];
                                    if ($special['price'] < 0) $special['price'] = 0;

                                    $this->model_module_mpchanges->updateProductSpecial($special);
                                    $changed[$product['product_id']]['specials'][] = $special;
                                }
                            }
                        }
                        if ($this->mpfilter["change_discount"]){
                            $discounts = $this->model_module_mpchanges->getProductCurrentDiscounts($product['product_id']);
                            foreach ($discounts as $discount) {
                                if ($discount['customer_group_id'] == $this->mpfilter["customer_group"] or $this->mpfilter["customer_group"] == 0){
                                    $discount['product_id'] = $discount['product_id'];
                                    $discount['price'] = $this->roundPrice($this->getDiffValue($discount['price'], $this->mpfilter["price_diff"], $this->mpfilter["manufacturer_price"], $this->mpfilter["change_type"]), $this->mpfilter['round_decimal']);

                                    if ($product['price'] < $discount['price']) $discount['price'] = $product['price'];
                                    if ($discount['price'] < 0) $discount['price'] = 0;

                                    $this->model_module_mpchanges->updateProductDiscount($discount);
                                    $changed[$product['product_id']]['discounts'][] = $discount;
                                }
                            }
                        }
                    }
                }
            }

            $this->model_module_mpchanges->cleanCache('product');
        }

        $this->mp_message['message'][] = $this->language->get('success_price_changed');
        $this->mp_message['message'] = implode('<br/> ', $this->mp_message['message']);

        $json['message'] = $this->mp_message;

        $this->loadFilter();
        $json['products'] = $this->mpfilter["products"];
        $this->getResponse()->setOutput(json_encode($json));
    }

    public function changespecial() {
        $this->load->language('module/mpchanges');
        $this->load->model('module/mpchanges');

        $this->loadFilter();
        $changed = array();

        if ($this->mpfilter["products"]){
            foreach ($this->mpfilter["products"] as $product){
                if (in_array($product['product_id'],$this->mpfilter['change_ids']) or $this->mpfilter['change_all']){
                    $product['need_customer_group'] = $this->mpfilter["customer_group"];
                    $specials = $this->model_module_mpchanges->getProductCurrentSpecials($product['product_id']);
                    $changed[$product['product_id']] = $product;
                    foreach ($specials as $special) {
                        if (($special['customer_group_id'] == $this->mpfilter["customer_group"] or $this->mpfilter["customer_group"] == 0)){
                            $special['product_id'] = $product['product_id'];
                            $special['price'] = $this->roundPrice($this->getDiffValue($special['price'], $this->mpfilter["price_diff"], $this->mpfilter["manufacturer_price"], $this->mpfilter["change_type"]), $this->mpfilter['round_decimal']);

                            if ($product['price'] < $special['price']) $special['price'] = $product['price'];
                            if ($special['price'] < 0) $special['price'] = 0;

                            $this->model_module_mpchanges->updateProductSpecial($special);
                            $changed[$product['product_id']]['specials'][] = $special;
                        }
                    }
                }
            }

            $this->model_module_mpchanges->cleanCache('product');
        }

        $this->mp_message['message'] = $this->mp_message['type'] == 'success' ? $this->language->get('success_special_changed') : implode('<br/> ', $this->mp_message['message']) ;
        $json['message'] = $this->mp_message;
        $json['products'] = $this->mpfilter["products"];

        $this->getResponse()->setOutput(json_encode($json));
    }

    public function addspecial() {
        $this->load->language('module/mpchanges');
        $this->load->model('module/mpchanges');

        $this->mp_message = array("type" => "success", "message" => array());
        $this->loadFilter();
        $changed = array();

        $add_specials = array();
        if (isset($this->request->post['product_special'])) {
            $add_specials = $this->request->post['product_special'];
        }

        if ($this->mpfilter["products"]){
            foreach ($this->mpfilter["products"] as $product){
                if (in_array($product['product_id'],$this->mpfilter['change_ids']) or $this->mpfilter['change_all']){
                    $changed[$product['product_id']] = $product;
                    foreach ($add_specials as $special) {
                        $special['product_id'] = $product['product_id'];
                        if (!isset($special['date_start']) || empty($special['date_start'])){$special['date_start'] = '1901-01-01';}
                        if (!isset($special['date_end']) || empty($special['date_end'])){$special['date_end'] = '3900-01-01';}
                        if ($this->validateDates($special)){
                            $special['price'] = $this->roundPrice($this->getDiffValue($product['price'], $special["price_diff"], $special['price'], "percent"), $this->mpfilter['round_decimal']);

                            if ($product['price'] < $special['price']) $special['price'] = $product['price'];
                            if ($special['price'] < 0) $special['price'] = 0;

                            $this->model_module_mpchanges->addProductSpecial($special);
                            $changed[$product['product_id']]['specials'][] = $special;
                        }
                    }
                }
            }

            $this->model_module_mpchanges->cleanCache('product');
        }

        $this->mp_message['message'] = $this->mp_message['type'] == 'success' ? $this->language->get('success_special_added') : implode('<br/> ', $this->mp_message['message']) ;

        $json['message'] = $this->mp_message;
        $json['products'] = $this->mpfilter["products"];

        $this->getResponse()->setOutput(json_encode($json));
    }

    public function changediscounts() {
        $this->load->language('module/mpchanges');
        $this->load->model('module/mpchanges');

        $this->loadFilter();

        $changed = array();

        $discount_qty = 1;
        if (isset($this->request->post['discount_quantity'])) {
            $discount_qty = $this->request->post['discount_quantity'];
        }

        if ($this->mpfilter["manufacturer_price"] > 0 and $this->mpfilter["products"]){
            foreach ($this->mpfilter["products"] as $product){
                if (in_array($product['product_id'],$this->mpfilter['change_ids']) or $this->mpfilter['change_all']){
                    $product['need_customer_group'] = $this->mpfilter["customer_group"];
                    $discounts = $this->model_module_mpchanges->getProductCurrentDiscounts($product['product_id']);
                    $changed[$product['product_id']] = $product;
                    foreach ($discounts as $discount) {
                        if (($discount['customer_group_id'] == $this->mpfilter["customer_group"] or $this->mpfilter["customer_group"] == 0) and $discount['quantity'] == $discount_qty){
                            $discount['price'] = $this->roundPrice($discount_qty * $this->getDiffValue($product['price'], $this->mpfilter["price_diff"], $this->mpfilter["manufacturer_price"], $this->mpfilter["change_type"]), $this->mpfilter['round_decimal']);

                            if ($product['price'] * $discount_qty < $discount['price']) $discount['price'] = $discount_qty * $product['price'];
                            if ($discount['price'] < 0) $discount['price'] = 0;

                            $this->model_module_mpchanges->updateProductDiscount($discount);
                            $changed[$product['product_id']]['discounts'][] = $discount;
                        }
                    }
                }
            }

            $this->model_module_mpchanges->cleanCache('product');
        }

        $this->mp_message['message'] = $this->mp_message['type'] == 'success' ? $this->language->get('success_discount_changed') : implode('<br/> ', $this->mp_message['message']) ;
        $json['message'] = $this->mp_message;
        $json['products'] = $this->mpfilter["products"];

        $this->getResponse()->setOutput(json_encode($json));
    }

    public function adddiscounts() {
        $this->load->language('module/mpchanges');
        $this->load->model('module/mpchanges');

        $this->loadFilter();
        $this->mp_message = array("type" => "success", "message" => array());

        $changed = array();

        $add_discounts = array();
        if (isset($this->request->post['product_discount'])) {
            $add_discounts = $this->request->post['product_discount'];
        }

        if ($this->mpfilter["products"]){
            foreach ($this->mpfilter["products"] as $product){
                if (in_array($product['product_id'],$this->mpfilter['change_ids']) or $this->mpfilter['change_all']){
                    $changed[$product['product_id']] = $product;
                    foreach ($add_discounts as $discount) {
                        $discount['product_id'] = $product['product_id'];
                        if (!isset($discount['date_start']) || empty($discount['date_start'])){$discount['date_start'] = '1901-01-01';}
                        if (!isset($discount['date_end']) || empty($discount['date_end'])){$discount['date_end'] = '3900-01-01';}
                        if ($this->validateDates($discount)){
                            $discount['price'] = $this->roundPrice($discount['quantity'] * $this->getDiffValue($product['price'], $this->mpfilter["price_diff"], $this->mpfilter["manufacturer_price"], $this->mpfilter["change_type"]), $this->mpfilter['round_decimal']);

                            if ($product['price'] * $discount['quantity'] < $discount['price']) $discount['price'] = $product['price'];
                            if ($discount['price'] < 0) $discount['price'] = 0;

                            $this->model_module_mpchanges->addProductDiscount($discount);
                            $changed[$product['product_id']]['discounts'][] = $discount;
                        }
                    }
                }
            }

            $this->model_module_mpchanges->cleanCache('product');
        }

        $this->mp_message['message'] = $this->mp_message['type'] == 'success' ? $this->language->get('success_discount_added') : implode('<br/> ', $this->mp_message['message']) ;

        $json['message'] = $this->mp_message;
        $json['products'] = $this->mpfilter["products"];

        $this->getResponse()->setOutput(json_encode($json));
    }

    public function delelements() {
        $this->load->language('module/mpchanges');
        $this->load->model('module/mpchanges');

        $this->loadFilter();
        $this->mp_message = array("type" => "success", "message" => array());

        $del_products = false;
        if (isset($this->request->post['del_product'])) {
            $del_products = true;
        }
        $del_special = false;
        if (isset($this->request->post['del_special'])) {
            $del_special = true;
        }
        $del_discount = false;
        if (isset($this->request->post['del_discount'])) {
            $del_discount = true;
        }

        $changed = array();

        if ($this->mpfilter["products"]){
            foreach ($this->mpfilter["products"] as $product){
                if (in_array($product['product_id'],$this->mpfilter['change_ids']) or $this->mpfilter['change_all']){
                    $changed[$product['product_id']] = $product;
                    if ($del_products){
                        $this->model_module_mpchanges->deleteProduct($product['product_id']);
                        unset($this->mpfilter["products"][$product['product_id']]);
                    }else{
                        if ($del_special){
                            $this->model_module_mpchanges->removeSpecials($product['product_id']);
                        }
                        if ($del_discount){
                            $this->model_module_mpchanges->removeDiscounts($product['product_id']);
                        }
                    }
                }
            }

            $this->model_module_mpchanges->cleanCache('product');
        }

        $this->mp_message['message'] = $this->mp_message['type'] == 'success' ? $this->language->get('success_delete') : implode('<br/> ', $this->mp_message['message']) ;

        $json['message'] = $this->mp_message;
        $json['products'] = $this->mpfilter["products"];

        $this->getResponse()->setOutput(json_encode($json));
    }

    private function validateDates($obj){
        if (!isset($obj['date_start'])){
            $this->mp_message['type'] = 'error'; $this->mp_message['message'][] = ' id товара - ' .$obj['product_id'] . ' - ' . $this->language->get('error_date_start_not_set');
        }
        if (!isset($obj['date_end'])){
            $this->mp_message['type'] = 'error'; $this->mp_message['message'][] = ' id товара - ' .$obj['product_id'] . ' - ' . $this->language->get('error_date_end_not_set');
        }
        if ($this->mp_message['type'] != 'error'){
            $ds = strtotime($obj['date_start']);
            $de = strtotime($obj['date_end']);
            if (!$ds || !$de){
                $this->mp_message['type'] = 'error'; $this->mp_message['message'][] = $obj['date_start'] . ' - ' .$obj['date_end'] . ' - id товара - ' .$obj['product_id'] . ' - ' . $this->language->get('error_date_start_bigger_date_end');
                return false;
            }
        }
        return true;
    }

    private function roundPrice($price, $decimal){
        if ($decimal != 0){
            return round($price, $decimal);
        }else{
            return round($price);
        }
    }

    public function setOption() {
        $option = OptionDAO::getInstance()->getOptionById($this->parameters['optionId']);
        foreach ($this->parameters['products'] as $productId) {
            $product = ProductDAO::getInstance()->getProduct($productId, true);
            $productOptions = $product->getOptions();
            if ($this->parameters['operation'] == 'AddOption') {
                if (is_null($productOptions->getByOptionId($option->getId()))) {
                    $productOptions->attach(new ProductOption(
                        null,
                        $product,
                        $option,
                        null,
                        false,
                        null
                    ));
                }
            } elseif ($this->parameters['operation'] == 'DelOption') {
                $productOptions->detach($productOptions->getByOptionId($option->getId()));
            } elseif ($this->parameters['operation'] == 'AddValue') {
                $productOption = $productOptions->getByOptionId($option->getId());
                if (is_null($productOption)){
                    $productOption = new ProductOption(
                        null,
                        $product,
                        $option,
                        null,
                        false,
                        null
                    );
                    $productOptions->attach($productOption);
                }
                if ($productOption->getOption()->isSingleValueType()) {
                    $productOption->setValue($this->parameters['optionValue']);
                } else { // Option value type is multivalue
                    if (is_null($productOption->getValue()->getByOptionValueId($this->parameters['optionValue']))) {
                        $productOption->setValue(new ProductOptionValue(
                            null,
                            $productOption,
                            new OptionValue($option, $this->parameters['optionValue']),
                            0,
                            0,
                            0,
                            0,
                            0,
                            null
                        ));
                    }
                }
            } elseif ($this->parameters['operation'] == 'DelValue') {
                $productOption = $productOptions->getByOptionId($option->getId());
                if (!is_null($productOption)) {
                    if ($productOption->getOption()->isSingleValueType()) {
                        $productOption->deleteValue();
                    } elseif ($productOption->getOption()->isMultiValueType()) {
                        $productOption->deleteValue(new ProductOptionValue(
                            null,
                            $productOption,
                            new OptionValue($option, $this->parameters['optionValue']),
                            null,
                            null,
                            null,
                            null,
                            null,
                            null
                        ));
                    } else {
                        throw new InvalidArgumentException("Not acceptable option value type '" . $this->parameters['optionValueType'] . "'");
                    }
                }
            }
            ProductDAO::getInstance()->saveProduct($product);
        }
    }
}