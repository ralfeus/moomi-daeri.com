<?php
use SebastianBergmann\Exporter\Exception;

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

    /**
     * @param string $key
     * @return mixed
     * @throws CacheNotInstalledException
     */
    public function get($key) {
        $logger = new Log("cache.log");
        if (function_exists('apc_exists')) {
            $logger->write("Trying to get '$key'");
            if (apc_exists($key)) {
                $logger->write("\tFound '$key'");
                return unserialize(apc_fetch($key));
            } else {
                $logger->write("\tCouldn't find '$key'");
                return null; //TODO: Check why it was removed
            }
        } else {
            $logger->write("No cache function");
            throw new CacheNotInstalledException();
        }
//        else {
//            return null;
//        }
    }

    public function set($key, $value) {
        if (function_exists('apc_store')) {
            apc_store($key, serialize($value), $this->expire);
        } else {
            throw new CacheNotInstalledException();
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws CacheNotInstalledException
     */
    public function delete($key) {
        if (function_exists('apc_delete')) {
            return apc_delete($key);
        } else {
            throw new CacheNotInstalledException();
        }
    }

    public function deleteAll($keyPattern) {
        if (class_exists('APCIterator')) {
            $iterator = new APCIterator('user', $keyPattern);
            foreach ($iterator as $entry) {
                apc_delete($entry['key']);
            }
        } else {
            throw new CacheNotInstalledException();
        }
    }
}

//TODO: Move to system\exception
final class CacheNotInstalledException extends \Exception {
    public function __construct() {
        trigger_error("No APC is installed on the server. No cache functionality is available");
    }
}