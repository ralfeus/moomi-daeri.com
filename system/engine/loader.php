<?php
require_once("OpenCartBase.php");
final class Loader extends OpenCartBase
{
    //protected $registry;

	/**
	 * Loader constructor.
	 * @param Registry $registry
     */
	public function __construct($registry) {
//        $this->getRegistry() = $registry;
		parent::__construct($registry);
        $this->log = new Log('Loader.log');
    }

	/**
	 * @param string $library
	 * @return mixed
	 */
	public function library($library) {
		$file = DIR_SYSTEM . 'library/' . $library . '.php';
		
		if (file_exists($file))
        {
            include_once(DIR_SYSTEM . 'library/LibraryClass.php');
			include_once($file);
			/** @var ILibrary $library */
            return $library::getInstance($this->getRegistry());
		}
        else
        {
			trigger_error('Error: Could not load library ' . $library . '!');
			exit();					
		}
	}

    /**
     * @param $model
     * @param null $scope
     * @return Model
     * @throws Exception
     */
	public function model($model, $scope = null) {
        $modelName = 'model_' . str_replace('/', '_', $model);
        if ($this->getRegistry()->has($modelName))
            return $this->getRegistry()->get($modelName);
        if (!$scope)
		    $appRoot = DIR_APPLICATION;
        elseif ($scope == 'admin')
            $appRoot = substr(DIR_APPLICATION, 0, strrpos(DIR_APPLICATION, '/', -2)) . '/admin/';
        elseif ($scope == 'global')
            $appRoot = substr(DIR_APPLICATION, 0, strrpos(DIR_APPLICATION, '/', -2));
        else
            $appRoot = substr(DIR_APPLICATION, 0, strrpos(DIR_APPLICATION, '/', -2)) . '/catalog/';
        $file  = $appRoot . '/model/' . $model . '.php';
		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);

		if (file_exists($file)) {
//            $this->log->write("Loading $file");
			require_once($file);
			$instance = new $class($this->getRegistry());
			$this->getRegistry()->set($modelName, $instance);
			return $instance;
        } elseif ($scope != 'global') {
            $this->log->write("Couldn't find file $file . Trying in global scope");
            return $this->model($model, 'global');
        } else {
            $this->log->write($file);
			throw new Exception('Error: Could not load model ' . $model . '!');
		}
	}
	 
	public function database($driver, $hostname, $username, $password, $database, $prefix = NULL, $charset = 'UTF8') {
		$file  = DIR_SYSTEM . 'database/' . $driver . '.php';
		$class = 'Database' . preg_replace('/[^a-zA-Z0-9]/', '', $driver);
		
		if (file_exists($file)) {
			include_once($file);
			
			$this->getRegistry()->set(str_replace('/', '_', $driver), new $class());
		} else {
			trigger_error('Error: Could not load database ' . $driver . '!');
			exit();				
		}
	}
	
	public function config($config) {
		$this->getRegistry()->get('config')->load($config);
	}

	/**
	 * @param string $languageResourceName
	 * @return Language
	 */
	public function language($languageResourceName) {
		$language = $this->getRegistry()->get('language');
		try {
			$language->load($languageResourceName);
        } catch (Exception $exc) {
            $language->load($this->getConfig()->get('config_admin_language'));
        }
		return $language;
	}
}