<?php
final class Cache { 
	private $expire = 7200;

  	public function __construct() {
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

//	public function get($key) {
//		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', md5($key)) . '.*');
//
//		if ($files) {
//			$cache = file_get_contents($files[0]);
//
//			return unserialize($cache);
//		}
//	}

    /// New version with APC using
    public function get($key) {
        if (function_exists('apc_exists')) {
            if (apc_exists($key)) {
                return unserialize(apc_fetch($key));
            }
        } else {
            trigger_error("No APC is installed on the server. No cache functionality is available");
        }
//        else {
//            return null;
//        }
    }

//  	public function set($key, $value) {
//    	$this->delete($key);
//
//		$file = DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', md5($key)) . '.' . (time() + $this->expire);
//
//		$handle = fopen($file, 'w');
//
//    	fwrite($handle, serialize($value));
//
//    	fclose($handle);
//  	}

    /// New version with APC using
    public function set($key, $value) {
        if (function_exists('apc_store')) {
            apc_store($key, serialize($value), $this->expire);
        } else {
            trigger_error("No APC is installed on the server. No cache functionality is available");
        }
    }
	
//  	public function delete($key) {
//		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', md5($key)) . '.*');
//
//		if ($files) {
//    		foreach ($files as $file) {
//      			if (file_exists($file)) {
//					unlink($file);
//					clearstatcache();
//				}
//    		}
//		}
//  	}

    /// New version with APC using
    public function delete($key) {
        if (function_exists('apc_delete')) {
            apc_delete($key);
        } else {
            trigger_error("No APC is installed on the server. No cache functionality is available");
        }
    }
}