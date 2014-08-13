<?php
class ControllerCommonUp extends Controller {

    public function cc() {

	if ($this->customer->isLogged()) {

	    $this->modelAccountCustomer = $this->load->model('account/customer');
	    $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
	    if ($this->request->get['route'] != 'account/edit' && (empty($customer_info['firstname']) || empty($customer_info['lastname']) || empty($customer_info['nickname']) || empty($customer_info['email']) || empty($customer_info['telephone']))) {
		    if ($this->request->get['route'] != 'account/logout') {
			$this->redirect($this->url->link('account/edit', 'token=1', 'SSL'));
		    }
	    }
	}
    }

}
?>
