<?php
final class Front {
	protected $registry;
	protected $pre_action = array();
	protected $error;
	
	public function __construct($registry) {
		$this->registry = $registry;
	}
	
	public function addPreAction($pre_action) {
		$this->pre_action[] = $pre_action;
	}
	
  	public function dispatch($action, $error) {
		$this->error = $error;
			
		foreach ($this->pre_action as $pre_action) {
			$result = $this->execute($pre_action);
					
			if ($result) {
				$action = $result;
				
				break;
			}
		}
		while ($action) {
			$action = $this->execute($action);
		}
  	}

	/**
	 * @param Action $action
	 * @return mixed
     */
	private function execute($action) {
		$file = $action->getFile();
		$class = $action->getClass();
		$method = $action->getMethod();
		$args = $action->getArgs();

		if (file_exists($file)) {
			require_once($file);

			$controller = new $class($this->registry, $method);
			
			if (is_callable(array($controller, $method))) {
				$action = call_user_func_array(array($controller, $method), $args);
			} else {
				$action = $this->error;
				$this->error = '';
			}
		} else {
			$action = $this->error;
			$this->error = '';
		}
		return $action;
	}
}