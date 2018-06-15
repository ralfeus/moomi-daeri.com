<?php
namespace system\library;

abstract class Cache {
	protected $expire = 7200;
    protected $logger;
	
	  	public function __construct() {
        $this->logger = new \system\library\Log("cache.log");
//		$files = glob(DIR_CACHE . 'cache.*');
//
//		if ($files) {
//			foreach ($files as $file) {
//				$time = substr(strrchr($file, '.'), 1);
//
//      			if ($time < time()) {
//					if (file_exists($file)) {
//						unlink($file);
//						clearstatcache();
//					}
//      			}
//    		}
//		}
  	}
	
	/**
     * @param string $key
     * @return mixed
     * @throws CacheNotInstalledException
    */
	public abstract function get($key);
	public abstract function set($key, $value);
	/**
     * @param string $key
     * @return bool
     * @throws CacheNotInstalledException
     */
    public abstract function delete($key);
	public abstract function deleteAll($keyPattern);
}