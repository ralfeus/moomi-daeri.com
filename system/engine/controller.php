<?php
require_once('OpenCartBase.php');
abstract class Controller extends OpenCartBase
{
    protected $children = array();
    protected $data = array(); // contains data for the template
    protected $id;
    protected $language;
	protected $layout;
	protected $output;
    protected $parameters = array(); // contains parameters passed to the controller and already handled
    protected $request;
    protected $selfRoute;
    protected $session;
    protected $template;

    public function __construct($registry) {
		parent::__construct($registry);
        $this->language = new Language("");
        $this->request = new Request();
        $this->session = new Session();
        $this->language = $this->registry->get('language');
        $this->request = $this->registry->get('request');
        $this->setSelfRoute();
        $this->session = $this->registry->get('session');
        $this->initParameters();
        $this->data['notifications'] = array();
	}

    protected function buildUrlParameterString($parameters)
    {
        $result = "";
        foreach ($parameters as $key => $value)
        {
            if (empty($value))
                continue;
            $result .= is_array($value)
                ? '&' . $key . '[]=' . implode('&' . $key . '[]=', $value)
                : "&$key=$value";
        }
        return $result;
    }
	
	protected function forward($route, $args = array()) {
		return new Action($route, $args);
	}

	protected function redirect($url, $status = 302) {
		header('Status: ' . $status);
		header('Location: ' . str_replace('&amp;', '&', $url));
		exit();
	}
	
	protected function getChild($child, $args = array()) {
		$action = new Action($child, $args);
		$file = $action->getFile();
		$class = $action->getClass();
		$method = $action->getMethod();
	
		if (file_exists($file)) {
			require_once($file);

			$controller = new $class($this->registry);
			
			$controller->$method($args);
			
			return $controller->output;
		} else {
			trigger_error('Error: Could not load controller ' . $child . '!');
			exit();					
		}		
	}

    protected function initParameters()
    {
//        $this->log->write("Stub function");
    }
	
	protected function render() {
		foreach ($this->children as $child) {
			$this->data[basename($child)] = $this->getChild($child);
		}
		
		if (file_exists(DIR_TEMPLATE . $this->template)) {
			extract($this->data);
			
      		ob_start();
      
	  		require(DIR_TEMPLATE . $this->template);
      
	  		$this->output = ob_get_contents();

      		ob_end_clean();
      		
			return $this->output;
    	} else {
			trigger_error('Error: Could not load template ' . DIR_TEMPLATE . $this->template . '!');
			exit();				
    	}
	}

    private function setSelfRoute()
    {
        $token = empty($_REQUEST['token']) ? '' : "token=" . $_REQUEST['token'];
        $route = empty($_REQUEST['route']) ? 'common/home' : $_REQUEST['route'];
        $this->selfRoute = $this->url->link($route, $token, 'SSL');
    }

    protected function takeSessionVariables()
    {
//        $this->log->write(print_r($this->session->data, true));
        if (isset($this->session->data['notifications']))
        {
            $this->data['notifications'] = $this->session->data['notifications'];
            unset($this->session->data['notifications']);
        }
        else
            $this->data['notifications'] = array();
    }
}