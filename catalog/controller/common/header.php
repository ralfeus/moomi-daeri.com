<?php
use model\core\CurrencyDAO;
use model\setting\ExtensionDAO;
use model\shop\GeneralDAO;
use model\total\TotalBaseDAO;
use system\engine\CustomerController;

class ControllerCommonHeader extends CustomerController {
	protected function index() {
		$this->data['title'] = $this->document->getTitle();

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$this->data['base'] = $this->getConfig()->get('config_ssl');
		} else {
			$this->data['base'] = $this->getConfig()->get('config_url');
		}

        $this->data['description'] = $this->document->getDescription();
		$this->data['keywords'] = $this->document->getKeywords();
		$this->data['links'] = $this->document->getLinks();
		$this->data['styles'] = $this->document->getStyles();
		$this->data['scripts'] = $this->document->getScripts();
		$this->data['lang'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');
		$this->data['google_analytics'] = html_entity_decode($this->getConfig()->get('config_google_analytics'), ENT_QUOTES, 'UTF-8');

		$this->language->load('common/header');

		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
			$server = HTTPS_IMAGE;
		} else {
			$server = HTTP_IMAGE;
		}

		if ($this->getConfig()->get('config_icon') && file_exists(DIR_IMAGE . $this->getConfig()->get('config_icon'))) {
			$this->data['icon'] = $server . $this->getConfig()->get('config_icon');
		} else {
			$this->data['icon'] = '';
		}

		$this->data['name'] = $this->getConfig()->get('config_name');

		if ($this->getConfig()->get('config_logo') && file_exists(DIR_IMAGE . $this->getConfig()->get('config_logo'))) {
			$this->data['logo'] = $server . $this->getConfig()->get('config_logo');
		} else {
			$this->data['logo'] = '';
		}

		// Calculate Totals
		$total_data = array();
		$total = 0;
//		$taxes = $this->getCart()->getTaxes(false);

		if (($this->getConfig()->get('config_customer_price') && $this->getCurrentCustomer()->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
			$this->getLoader()->model('setting/extension');

			$sort_order = array();

			$results = ExtensionDAO::getInstance()->getExtensions('total');

			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->getConfig()->get($value['code'] . '_sort_order');
			}

			array_multisort($sort_order, SORT_ASC, $results);

			foreach ($results as $result) {
				if ($this->getConfig()->get($result['code'] . '_status')) {
                    $totalExtension = TotalBaseDAO::getTotalExtension($result['code']);
                    $totalExtension->getTotal($total_data, $total, $false);
				}
			}
		}

		if($this->getCurrentCustomer()->isLogged()){
			$isVip = GeneralDAO::getInstance()->isVip($this->getCurrentCustomer()->getId());
		}

		$str = "";
		if(isset($isVip) && $isVip) {
			$url = 'catalog/view/theme/default/image/vip.png';
			$str = "&nbsp;&nbsp;&nbsp;<img src='".$url."' alt='vip' height='20' class='bottom5' />&nbsp;";
		}

        $this->data['button_go'] = $this->language->get('GO');

        $this->data['entry_search'] = '';
        $this->data['keyword'] = '';

        $this->data['text_home'] = $this->language->get('text_home');
        $this->data['text_favorites'] = $this->language->get('text_favorites');
		$this->data['text_wishlist'] = sprintf($this->language->get('text_wishlist'), (isset($this->session->data['wishlist']) ? count($this->session->data['wishlist']) : 0));
		$this->data['text_cart'] = sprintf($this->language->get('text_cart'),(isset($this->session->data['cart']) ? count($this->session->data['cart']) : 0));
		$this->data['text_items'] = sprintf($this->language->get('text_items'), $this->getCart()->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->getCurrentCurrency()->format($total));
   	    $this->data['textSearchPrompt'] = $this->language->get('SEARCH_PROMPT');
		$this->data['text_welcome'] = sprintf($this->language->get('text_welcome'), $this->getUrl()->link('account/login', '', 'SSL'), $this->getUrl()->link('account/register', '', 'SSL'));
		$this->data['text_welcome_help'] = sprintf($this->language->get('text_welcome_help'), $this->getUrl()->link('account/login', '', 'SSL'));
		$this->data['text_welcome_guest_left'] = sprintf($this->language->get('text_welcome_guest_left'), $this->getUrl()->link('account/login', '', 'SSL'));
		$this->data['text_welcome_guest_right'] = sprintf($this->language->get('text_welcome_guest_right'), $this->getUrl()->link('account/register', '', 'SSL'));
		$this->data['text_logged'] = sprintf($this->language->get('text_logged'), $str, $this->getUrl()->link('account/account', '', 'SSL'), $this->getCurrentCustomer()->getNickName(), $this->getUrl()->link('account/logout', '', 'SSL'));
		$this->data['text_logged_help'] = sprintf($this->language->get('text_logged_help'), $str, $this->getCurrentCustomer()->getNickName());
		$this->data['text_logged_customer_left'] = sprintf($this->language->get('text_logged_customer_left'), $str, $this->getUrl()->link('account/account', '', 'SSL'), $this->getCurrentCustomer()->getNickName(),$this->getCurrentCustomer()->getNickName());
		$this->data['text_logged_customer_right'] = sprintf($this->language->get('text_logged_customer_right'), $this->getUrl()->link('account/logout', '', 'SSL'));
		$this->data['text_account'] = $this->language->get('text_account');
   	$this->data['text_checkout'] = $this->language->get('text_checkout');
		$this->data['text_language'] = $this->language->get('text_language');
        $this->data['text_advanced'] = $this->language->get('ADVANCED');
        $this->data['text_bookmark'] = $this->language->get('BOOKMARK');
        $this->data['text_contact'] = $this->language->get('CONTACT');
        $this->data['text_currency'] = $this->language->get('text_currency');
        $this->data['text_keyword'] = $this->language->get('KEYWORD');
        $this->data['text_login'] = $this->language->get('LOGIN');
        $this->data['text_logout'] = $this->language->get('LOGOUT');
        $this->data['text_repurchase_order'] = $this->language->get('text_repurchase_order');
        $this->data['text_auction'] = $this->language->get('text_auction');
        $this->data['text_sitemap'] = $this->language->get('SITEMAP');
        $this->data['text_special'] = $this->language->get('SPECIAL');
    $this->data['textGallery'] = $this->language->get('GALLERY');
    $this->data['textShoppingGuide'] = $this->language->get('text_shopping_guide');
        $this->data['text_totop'] = $this->language->get('text_totop');
        $this->data['text_back'] = $this->language->get('text_back');

		$this->data['home'] = $this->getUrl()->link('common/home');
		$this->data['wishlist'] = $this->getUrl()->link('account/wishlist');
		$this->data['logged'] = $this->getCurrentCustomer()->isLogged();
		$this->data['account'] = $this->getUrl()->link('account/account', '', 'SSL');
		$this->data['cart'] = $this->getUrl()->link('checkout/cart');
 if($this->getConfig()->get('wk_auction_timezone_set')){
    $this->data['menuauction'] = $this->getUrl()->link('catalog/wkallauctions', '', 'SSL');
}
		$this->data['checkout'] = $this->getUrl()->link('checkout/checkout', '', 'SSL');
    $this->data['repurchase_order'] = $this->getUrl()->link('product/repurchase', '', 'SSL');
    $this->data['urlGallery'] = $this->getUrl()->link('product/gallery', '', 'SSL');
    $this->data['urlShoppingGuide'] = $this->getUrl()->link('shop/admin/showPage&page_id=15', '', 'SSL');

		if (isset($this->request->get['filter_name'])) {
			$this->data['filter_name'] = $this->request->get['filter_name'];
		} else {
			$this->data['filter_name'] = '';
		}

		$this->data['action'] = $this->getUrl()->link('common/home');

		if (!isset($this->request->get['route'])) {
			$this->data['redirect'] = $this->getUrl()->link('common/home');
		} else {
			$data = $this->request->get;

			unset($data['_route_']);

			$route = $data['route'];

			unset($data['route']);

			$url = '';

			if ($data) {
				//$url = '&amp;' . urldecode(http_build_query($data, '', '&amp;'));
				$url = '&' . urldecode(http_build_query($data, '', '&'));
			}

			$this->data['redirect'] = $this->getUrl()->link($route, $url);
		}

		$modelLanguage = new ModelLocalisationLanguage($this->getRegistry());
		$languages = $modelLanguage->getLanguages();
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->post['language_code'])) {
			$this->session->data['language'] = $this->request->post['language_code'];
            foreach ($languages as $language)
                if ($language['code'] == $this->session->data['language'])
                {
                    $this->session->data['language_id'] = $language['language_id'];
                    break;
                }

			if (isset($this->request->post['redirect'])) {
				$this->redirect($this->request->post['redirect']);
			} else {
				$this->redirect($this->getUrl()->link('common/home'));
			}
    	}

		$this->data['language_code'] = $this->session->data['language'];
//		$this->getLoader()->model('localisation/language');
		$this->data['languages'] = array();
//		$results = $this->model_localisation_language->getLanguages();
		foreach ($languages as $result) {
			if ($result['status']) {
				$this->data['languages'][] = array(
					'name'  => $result['name'],
					'code'  => $result['code'],
					'image' => $result['image']
				);
			}
		}
        $tmpQuery = $this->request->get;
        unset($tmpQuery['language']);
        $this->data['languagelessQuery'] =
            implode('&',
                array_map(function($key, $value) {
                    return "$key=$value";
                }, array_keys($tmpQuery), $tmpQuery)
            );

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !empty($this->request->post['currency_code'])) {
      		$this->getCurrentCurrency()->set($this->request->post['currency_code']);

			unset($this->session->data['shipping_methods']);
			unset($this->session->data['shipping_method']);

			if (isset($this->request->post['redirect'])) {
				$this->redirect($this->request->post['redirect']);
			} else {
				$this->redirect($this->getUrl()->link('common/home'));
			}
   		}

		$this->data['currency_code'] = $this->getCurrentCurrency()->getCode();
//		$this->getLoader()->model('localisation/currency');
	    $this->data['currencies'] = array();
		$results = CurrencyDAO::getInstance()->getCurrencies();
		foreach ($results as $result) {
			if ($result['status']) {
                if ($result['code'] == 'RUB') {
                    continue;
                }
   				$this->data['currencies'][] = array(
					'title'        => $result['title'],
					'code'         => $result['code'],
					'symbol_left'  => $result['symbol_left'],
					'symbol_right' => $result['symbol_right']
				);
			}
		}

		$this->getLoader()->language('shop/general');
		$this->data['text_no_select_images'] = $this->language->get('text_no_select_images');
		$this->data['text_button_download'] = $this->language->get('text_button_download');
        $this->setBreadcrumbs();

		if (file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . '/template/common/header.tpl.php'))
			$this->template = $this->getConfig()->get('config_template') . '/template/common/header.tpl.php';
		else
			$this->template = 'default/template/common/header.tpl.php';

        $this->render();
	}
}
