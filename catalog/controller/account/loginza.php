<?php 
class ControllerAccountLoginza extends Controller { 
	public function index() {
		if (!isset($_POST['token'])) {
			die('Ошибка: Отсутствует токен');
		}
		// получаем данные о пользователе с сервера Loginza
		$loginzaUserInfo = file_get_contents('http://loginza.ru/api/authinfo?token=' . $_POST['token']);

		$loginzaUserInfo = json_decode($loginzaUserInfo);

		if (!isset($loginzaUserInfo->identity)) {
			die('Ошибка: ' . $loginzaUserInfo->error_message);
		}
		
		if (isset($loginzaUserInfo->name->first_name) && $loginzaUserInfo->name->first_name) {
			$firstname = $loginzaUserInfo->name->first_name;
		} else {
			$firstname = '';
		}
		
		if (isset($loginzaUserInfo->name->last_name) && $loginzaUserInfo->name->last_name) {
			$lastname = $loginzaUserInfo->name->last_name;
		} else {
			$lastname = '';
		}
		
		if (isset($loginzaUserInfo->email) && $loginzaUserInfo->email) {
			$email = $loginzaUserInfo->email;
		} else {
			$email = '';
		}
		
		$this->load->model('tool/loginza');
		$check_id = $this->model_tool_loginza->check_identity($loginzaUserInfo->identity);
		If (!$check_id) {
			// регистрируем
			 
			$data = array(
				'identity' => $loginzaUserInfo->identity,
				'firstname' => $firstname,
				'lastname' => $lastname,
				'email' => $email,
				'customer_group_id' => $this->config->get('config_customer_group_id'),
				'password' => $this->generate_password(10) // сгенерировать бы чего нить
			);
			
			$this->model_tool_loginza->login($this->model_tool_loginza->add_customer($data));
		} else {
			// входим
			$this->model_tool_loginza->login($check_id);
		}
		if (isset($this->session->data['loginza_redirect'])) {
			$this->redirect($this->session->data['loginza_redirect']);
		} else {
			$this->redirect(HTTPS_SERVER);
		}
		
  	}
	
	private function generate_password($number) {
		$arr = array('a','b','c','d','e','f',
						'g','h','i','j','k','l',
						'm','n','o','p','r','s',
						't','u','v','x','y','z',
						'A','B','C','D','E','F',
						'G','H','I','J','K','L',
						'M','N','O','P','R','S',
						'T','U','V','X','Y','Z',
						'1','2','3','4','5','6',
						'7','8','9','0');
		// Генерируем пароль
		$pass = "";
		for($i = 0; $i < $number; $i++) {
			// Вычисляем случайный индекс массива
			$index = rand(0, count($arr) - 1);
			$pass .= $arr[$index];
		}

		return $pass;
	}
}
?>
