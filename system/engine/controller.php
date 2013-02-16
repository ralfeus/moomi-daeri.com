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
    protected $selfUrl;
    protected $session;
    protected $template;

    public function __construct($registry) {
		parent::__construct($registry);
        $this->language = new Language("");
        $this->request = new Request();
        $this->session = new Session();
        $this->language = $this->registry->get('language');
        $this->request = $this->registry->get('request');
        $this->setSelfRoutes();
        $this->session = $this->registry->get('session');
        $this->initParameters();
        $this->data['notifications'] = array();
        $this->load->library('audit');
    }

    protected function buildUrlParameterString($parameters)
    {
        $result = "";
        foreach ($parameters as $key => $value)
        {
            if (empty($value))
                continue;
            $result .= '&' . $this->getParamString($key, $value);
//            $this->log->write($result);
        }
        return substr($result, 1);
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

    protected function getParamString($paramKey, $paramValue, $prefix = '')
    {
        if ($prefix)
            $paramKey = $prefix . '[' . $paramKey . ']';
        $result = '';
        if (is_array($paramValue))
        {
            foreach ($paramValue as $key => $value)
                if (!empty($value) || is_numeric($value))
                    $result .= '&' . $this->getParamString($key, $value, $paramKey);
            $result = substr($result, 1);
        }
        else
            $result = "$paramKey=$paramValue";
        return $result;
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

    private function setSelfRoutes()
    {
        $route = empty($_REQUEST['route']) ? 'common/home' : $_REQUEST['route'];
        $this->selfUrl = '/'. $_SERVER["PHP_SELF"];
        if (!empty($_REQUEST))
        {
            $this->selfUrl .= '?';
            foreach ($_REQUEST as $key => $value)
                if (!empty($value) || is_numeric($value))
                    $this->selfUrl .= '&' . $this->getParamString($key, $value);
            $this->selfUrl = substr($this->selfUrl, 1);
        }
        $this->selfRoute = $route;
//        $this->log->write(print_r($_REQUEST, true));
//        $this->log->write(print_r($this->selfUrl, true));
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