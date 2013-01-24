<?php
require_once("OpenCartBase.php");
final class Loader extends OpenCartBase
{
    protected $registry;

    public function __construct($registry)
    {
        $this->registry = new Registry();
        $this->registry = $registry;
    }

	public function library($library) {
		$file = DIR_SYSTEM . 'library/' . $library . '.php';
		
		if (file_exists($file))
        {
            include_once(DIR_SYSTEM . 'library/LibraryClass.php');
			include_once($file);
            return $library::getInstance($this->registry);
		}
        else
        {
			trigger_error('Error: Could not load library ' . $library . '!');
			exit();					
		}
	}
	
	public function model($model, $scope = null) {
        $modelName = 'model_' . str_replace('/', '_', $model);
        if ($this->registry->has($modelName))
            return $this->registry->get($modelName);
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
		
		if (file_exists($file))
        {
			require_once($file);
			$instance = new $class($this->registry);
			$this->registry->set($modelName, $instance);
			return $instance;
        }
        elseif ($scope != 'global')
            return $this->model($model, 'global');
		else
        {
			trigger_error('Error: Could not load model ' . $model . '!');
			exit();					
		}
	}
	 
	public function database($driver, $hostname, $username, $password, $database, $prefix = NULL, $charset = 'UTF8') {
		$file  = DIR_SYSTEM . 'database/' . $driver . '.php';
		$class = 'Database' . preg_replace('/[^a-zA-Z0-9]/', '', $driver);
		
		if (file_exists($file)) {
			include_once($file);
			
			$this->registry->set(str_replace('/', '_', $driver), new $class());
		} else {
			trigger_error('Error: Could not load database ' . $driver . '!');
			exit();				
		}
	}
	
	public function config($config) {
		$this->registry->get('config')->load($config);
	}
	
	public function language($language) {
		return $this->registry->get('language')->load($language);
	}
} 
?>