<?php
final class Request {
	public $get = array();
	public $post = array();
	public $cookie = array();
	public $files = array();
	public $server = array();
	
  	public function __construct() {
        /// 05.01.2013
        /// It doesn't seem for me to be a good idea to replace some symbols in URL as it would be HTML
//		$_GET = $this->clean($_GET);
//		$_POST = $this->clean($_POST);
//		$_REQUEST = $this->clean($_REQUEST);
		$_COOKIE = $this->clean($_COOKIE);
		$_FILES = $this->clean($_FILES);
//		$_SERVER = $this->clean($_SERVER);
		
		$this->get = $_GET;
		$this->post = $_POST;
		$this->request = $_REQUEST;
		$this->cookie = $_COOKIE;
		$this->files = $_FILES;
		$this->server = $_SERVER;
	}
	
  	public function clean($data) {
    	if (is_array($data)) {
	  		foreach ($data as $key => $value) {
				unset($data[$key]);
				
	    		$data[$this->clean($key)] = $this->clean($value);
	  		}
		} else { 
	  		$data = htmlspecialchars($data, ENT_COMPAT);
		}

		return $data;
	}

    /**
     * @return string
     * Returns HTTP request method
     */
    public function getMethod() {
        return $this->getServerVariable('REQUEST_METHOD');
    }

    /**
     * @param string $param
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getParam($param, $defaultValue = null) {
		if (isset($_POST[$param])) {
			return $_POST[$param];
		} elseif (isset($_GET[$param])) {
			return $_GET[$param];
		} elseif (isset($_COOKIE[$param])) {
			return $_COOKIE[$param];
        } else {
            return $defaultValue;
        }
    }

    public function getServerVariable($variable) {
        if (isset($_SERVER[$variable])) {
            return $_SERVER[$variable];
        } else {
            return null;
        }
    }
}