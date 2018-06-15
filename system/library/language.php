<?php
namespace system\library;
use Exception;

final class Language {
	private $languageId;
    public $directory;
	private $data = array();

	public function __construct($directory, $languageId = null) {
		$this->directory = $directory;
		$this->languageId = $languageId;
	}
	
  	public function get($key) {
   		return (isset($this->data[$key]) ? $this->data[$key] : $key);
  	}

	public function getId() {
		return $this->languageId;
	}
	
	public function load($filename) {
		$file = DIR_LANGUAGE . $this->directory . '/' . $filename . '.php';
    	
		if (file_exists($file)) {
			$_ = array();
			require($file);
			$this->data = array_merge($this->data, $_);
			
			return $this->data;
		} else {
			throw new Exception('Error: Could not load language set "' . $filename . '" for language "' . $this->directory . '"!');
		}
  	}
}