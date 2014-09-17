<?php
class ControllerAccountEdit extends Controller {
	private $error = array();
    private $modelAccountCustomer;
    private $modelLocalisationCurrency;

    public function __construct($registry) {
        parent::__construct($registry);
        $this->language->load('account/edit');
        $this->document->setTitle($this->language->get('headingTitle'));

        $this->modelAccountCustomer = $this->load->model('account/customer');
        $this->modelLocalisationCurrency = $this->load->model('localisation/currency');
    }

	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/edit', '', 'SSL');
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_account_customer->editCustomer($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('account/account', '', 'SSL'));
		}

        $this->setBreadcrumbs();
// KBA		
		$this->data['text_edit_your_acc_plz'] = $this->language->get('text_edit_your_acc_plz');
// /KBA
		$this->data['heading_title'] = $this->language->get('headingTitle');
		$this->data['text_your_details'] = $this->language->get('text_your_details');
		$this->data['entry_firstname'] = $this->language->get('entry_firstname');
		$this->data['entry_lastname'] = $this->language->get('entry_lastname');
        $this->data['entry_nickname'] = $this->language->get('entry_nickname');
		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_telephone'] = $this->language->get('entry_telephone');
		$this->data['entry_fax'] = $this->language->get('entry_fax');
        $this->data['textAccountDetails'] = $this->language->get('ACCOUNT_DETAILS');
        $this->data['textBaseCurrency'] = $this->language->get('BASE_CURRENCY');

		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_back'] = $this->language->get('button_back');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->error['firstname'])) {
			$this->data['error_firstname'] = $this->error['firstname'];
		} else {
			$this->data['error_firstname'] = '';
		}

		if (isset($this->error['lastname'])) {
			$this->data['error_lastname'] = $this->error['lastname'];
		} else {
			$this->data['error_lastname'] = '';
		}

        if (isset($this->error['nickname'])) {
            $this->data['error_nickname'] = $this->error['nickname'];
        } else {
            $this->data['error_nickname'] = '';
        }

        if (isset($this->error['email'])) {
			$this->data['error_email'] = $this->error['email'];
		} else {
			$this->data['error_email'] = '';
		}	
		
		if (isset($this->error['telephone'])) {
			$this->data['error_telephone'] = $this->error['telephone'];
		} else {
			$this->data['error_telephone'] = '';
		}	

		$this->data['action'] = $this->url->link('account/edit', '', 'SSL');

        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());

		if (isset($this->request->post['firstname'])) {
			$this->data['firstname'] = $this->request->post['firstname'];
		} elseif (isset($customer_info)) {
			$this->data['firstname'] = $customer_info['firstname'];
		} else {
			$this->data['firstname'] = '';
		}

		if (isset($this->request->post['lastname'])) {
			$this->data['lastname'] = $this->request->post['lastname'];
		} elseif (isset($customer_info)) {
			$this->data['lastname'] = $customer_info['lastname'];
		} else {
			$this->data['lastname'] = '';
		}

        if (isset($this->request->post['nickname'])) {
            $this->data['nickname'] = $this->request->post['nickname'];
        } elseif (isset($customer_info)) {
            $this->data['nickname'] = $customer_info['nickname'];
        } else {
            $this->data['nickname'] = '';
        }

		if (isset($this->request->post['email'])) {
			$this->data['email'] = $this->request->post['email'];
		} elseif (isset($customer_info)) {
			$this->data['email'] = $customer_info['email'];
		} else {
			$this->data['email'] = '';
		}

		if (isset($this->request->post['telephone'])) {
			$this->data['telephone'] = $this->request->post['telephone'];
		} elseif (isset($customer_info)) {
			$this->data['telephone'] = $customer_info['telephone'];
		} else {
			$this->data['telephone'] = '';
		}

		if (isset($this->request->post['fax'])) {
			$this->data['fax'] = $this->request->post['fax'];
		} elseif (isset($customer_info)) {
			$this->data['fax'] = $customer_info['fax'];
		} else {
			$this->data['fax'] = '';
		}

        if (isset($this->request->post['baseCurrency']))
            $selectedCurrency = $this->request->post['baseCurrency'];
        else
            $selectedCurrency = $customer_info['base_currency_code'];

        $this->data['currencies'] = array();
        foreach ($this->modelLocalisationCurrency->getCurrencies() as $currency)
        {
            $this->data['currencies'][] = array(
                'currencyCode' => $currency['code'],
                'name' => $currency['title'],
                'selected' => ($currency['code'] == $selectedCurrency) ? "selected=\"true\"" : ""
            );
        }

		$this->data['back'] = $this->url->link('account/account', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/edit.tpl.php')) {
			$this->template = $this->config->get('config_template') . '/template/account/edit.tpl.php';
		} else {
			$this->template = 'default/template/account/edit.tpl.php';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);
						
		$this->response->setOutput($this->render());	
	}

    protected function setBreadcrumbs()
    {
        $this->data['breadcrumbs'] = array();

        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_account'),
            'href'      => $this->url->link('account/account', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
        $this->data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_edit'),
            'href'      => $this->url->link('account/edit', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );
    }

	private function validate() {
		if ((utf8_strlen($this->request->post['firstname']) < 1) || (utf8_strlen($this->request->post['firstname']) > 32)) {
			$this->error['firstname'] = $this->language->get('error_firstname');
		}

		if ((utf8_strlen($this->request->post['lastname']) < 1) || (utf8_strlen($this->request->post['lastname']) > 32)) {
			$this->error['lastname'] = $this->language->get('error_lastname');
		}

        if ((utf8_strlen($this->request->post['nickname']) < 1) || (utf8_strlen($this->request->post['nickname']) > 32)) {
            $this->error['nickname'] = $this->language->get('error_nickname');
        }

        if ((utf8_strlen($this->request->post['email']) > 96) || !preg_match('/^[^\@]+@.*\.[a-z]{2,6}$/i', $this->request->post['email'])) {
			$this->error['email'] = $this->language->get('error_email');
		}
		
		if (($this->customer->getEmail() != $this->request->post['email']) && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
			$this->error['warning'] = $this->language->get('error_exists');
		}

		if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
			$this->error['telephone'] = $this->language->get('error_telephone');
		}

        if (($this->customer->getBaseCurrency()->getid() != $this->request->post['baseCurrency']) &&
            (!isset($this->request->post['confirm'])))
        {
            $this->data['confirmationRequired'] = true;
            $this->data['confirmationRequestText'] =
                sprintf(
                    $this->language->get('CONFIRM_CURRENCY_CONVERT'),
                    $this->customer->getBaseCurrency()->getName(),
                    $this->currency->getName($this->request->post['baseCurrency']),
                    $this->customer->getBaseCurrency()->getName(),
                    $this->currency->getName($this->request->post['baseCurrency']),
                    $this->currency->format(
                        $this->customer->getBalance(),
                        $this->customer->getBaseCurrency()->getCode(),
                        1),
                    $this->currency->format(
                        $this->currency->convert(
                            $this->customer->getBalance(),
                            $this->customer->getBaseCurrency()->getCode(),
                            $this->config->get('config_currency')),
                        $this->request->post['baseCurrency']
                    )
                );
            $this->error['cur'] = true;
        }

		if (!$this->error) {
			return true;
		} else {
			return false;
		}
	}
}
?>