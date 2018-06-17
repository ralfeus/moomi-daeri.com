<?php
namespace system\engine;
use Action;
use Customer;
use Document;
use Exception;
use system\library\Language;
use Request;
use Response;
use system\library\Session;
use Url;

require_once('OpenCartBase.php');

abstract class Controller extends OpenCartBase {
    /** @var string */
    private $action;
    /** @var array */
    protected $children = array();
    /** @var Customer */
    protected $customer;
    /** @var array */
    protected $data = array(); // contains data for the template
    /** @var Document */
    protected $document;
    protected $id;
    /** @var Language */
    protected $language;
    protected $layout;
    protected $output;
    protected $parameters = array(); // contains parameters passed to the controller and already handled
    /** @var Request */
    protected $request;
    /** @var Response */
    protected $response;
    protected $selfRoute;
    protected $selfUrl;
    /** @var Session */
    protected $session;
    /**
     * @deprecated Deprecated as used only in one method render().
     * @var string
     * Defines template file name. Deprecated as used only in one method render().
     */
    protected $template;

    /**
     * @param $registry Registry
     * @param string $action
     */
    public function __construct($registry, $action = null) {
        parent::__construct($registry);
        $this->action = $action;
        $this->customer = $this->registry->get('customer');
        $this->document = $this->registry->get('document');
        $this->language = $this->registry->get('language');
        $this->request = $this->registry->get('request');
        $this->response = $this->registry->get('response');
        $this->setSelfRoutes();
        $this->session = $this->registry->get('session');
        $_REQUEST = $this->cleanoutParameters($_REQUEST);
        $this->initParameters();
        $this->data['notifications'] = array();
        ////$this->load->library('audit');
    }

    protected function buildUrlParameterString($parameters, $paramsToReplace = array()) {
        $result = "";
        foreach ($parameters as $key => $value) {
            if (array_key_exists($key, $paramsToReplace)) {
                $value = $paramsToReplace[$key];
            }
            if (empty($value))
                continue;
            $result .= '&' . $this->getParamString($key, $value);
//            $this->log->write($result);
        }
        return substr($result, 1);
    }

    protected function getAction() {
        return $this->action;
    }

    protected function forward($route, $args = array()) {
        return new Action($route, $args);
    }


    /**
     * @return array
     */
    protected function getFilterParameters() {
        $filterParams = array();
        foreach ($this->parameters as $key => $value)
            if (!(strpos($key, 'filter') === false))
                $filterParams[$key] = $value;
        return $filterParams;
    }


    protected function redirect($url, $status = 302) {
        header('Status: ' . $status);
        header('Location: ' . str_replace('&amp;', '&', $url));
        exit();
    }

    /**
     * @param string $child
     * @param array $args
     * @return string
     */
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

    /**
     * @return Request
     */
    protected function getRequest() {
        return $this->registry->get('request');
    }

    /**
     * @return Response
     */
    protected function getResponse() {
        return $this->registry->get('response');
    }

    protected function getParamString($paramKey, $paramValue, $prefix = '') {
        if ($prefix)
            $paramKey = $prefix . '[' . $paramKey . ']';
        $result = '';
        if (is_array($paramValue)) {
            foreach ($paramValue as $key => $value)
                if (!empty($value) || is_numeric($value))
                    $result .= '&' . $this->getParamString($key, $value, $paramKey);
            $result = substr($result, 1);
        } else
            $result = "$paramKey=$paramValue";
        return $result;
    }

    /**
     * @return Url
     */
    protected function getUrl() {
        return $this->registry->get('url');
    }

    protected function initParameters() {
        $_REQUEST = $_COOKIE;
        $_REQUEST = array_merge($_REQUEST, $_GET);
        $_REQUEST = array_merge($_REQUEST, $_POST);
    }

    /**
     * @param array $paramsMap Key is parameter name.
     * Value can be of two types:
     * 1. Array of two values: default value and predicate function like this:
     *      function($value) { return someValueCondition($value); }
     * 2. Default value for cases request parameter is empty
     */
    protected function initParametersWithDefaults($paramsMap) {
        foreach ($paramsMap as $param => $value) {
            $paramName = preg_replace_callback('/_(\w)/', function ($m) {
                return strtoupper($m[1]);
            }, $param);
            $this->parameters[$paramName] =
                is_array($value)
                    ? (isset($value[1]) // Predicate
                    ? ($value[1]($_REQUEST[$param]) ? $_REQUEST[$param] : $value[0])
                    : (!empty($_REQUEST[$param]) ? $_REQUEST[$param] : $value[0]))
                    : (isset($_REQUEST[$param]) ? $_REQUEST[$param] : $value);
        }
    }

    /**
     * Stub function. Should be replaced with abstract one once all Controller derived classes implement it
     */
    protected function loadStrings() {
    }

    /**
     * @param string $template
     * @return string
     * @throws Exception
     */
    protected function render($template = null) {
        foreach ($this->children as $child) {
            $this->data[basename($child)] = $this->getChild($child);
        }
        $this->loadStrings();
        if (is_null($template)) {
            $template = $this->template;
        }

        $tmpTemplate = $template;
        if (!file_exists(DIR_TEMPLATE . $template)) {
            $template = 'default';
        }
        if (!file_exists(DIR_TEMPLATE . $template)) {
            throw new Exception('Error: Could not load template ' . DIR_TEMPLATE . $tmpTemplate . '!');
        }

        extract($this->data);

        ob_start();
        require(DIR_TEMPLATE . $template);
        $this->output = ob_get_contents();
        ob_end_clean();

        return $this->output;
    }

    protected function setBreadcrumbs($breadcrumbs = array()) {
        $this->data['breadcrumbs'] = array();
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->getUrl()->link('common/home', isset($this->session->data['token']) ? 'token=' . $this->session->data['token'] : '', 'SSL'),
            'separator' => false
        );
        foreach ($breadcrumbs as $breadcrumb) {
            $args = '';
            if (is_array($breadcrumb['args'])) {
                foreach ($breadcrumb['args'] as $param => $value) {
                    $args .= "&$param=$value";
                }
            }
            $args .= isset($this->session->data['token']) ? '&token=' . $this->session->data['token'] : '';

            $this->data['breadcrumbs'][] = [
                'text' => $breadcrumb['text'],
                'href' => $this->getUrl()->link(
                    $breadcrumb['route'],
                    strlen($args) ? substr($args, 1) : '' , 'SSL'),
                'separator' => '::'
            ];
        }
        $this->data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->selfUrl,
            'separator' => ' :: '
        );
    }

    private function setSelfRoutes() {
        $route = empty($_REQUEST['route']) ? 'common/home' : $_REQUEST['route'];
        $this->selfUrl = '/' . $_SERVER["PHP_SELF"];
        if (!empty($_REQUEST)) {
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

    protected function takeSessionVariables() {
//        $this->log->write(print_r($this->session->data, true));
        if (isset($this->session->data['notifications'])) {
            $this->data['notifications'] = $this->session->data['notifications'];
            unset($this->session->data['notifications']);
        } else
            $this->data['notifications'] = array();
    }


    /**
     * @param array $params
     * @return array
     */
    private function cleanoutParameters($params) {
        $result = [];
        foreach ($params as $key => $value) {
            if (is_array($value) && (sizeof($value) == 1) && empty($value[0])) {
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }
}