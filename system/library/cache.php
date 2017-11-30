<?php
use SebastianBergmann\Exporter\Exception;

final class Cache {
	private $expire = 7200;
    private $logger;
    private $destination = '/var/tmpfs';

  	public function __construct() {
        $this->logger = new Log("cache.log");
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
        if (function_exists('apc_exists')) {
//            $this->logger->write("Trying to get '$key'");
            if (apc_exists($key)) {
//                $this->logger->write("\tFound '$key'");
                return unserialize(apc_fetch($key));
            } else {
//                $this->logger->write("\tCouldn't find '$key'");
                return null; //TODO: Check why it was removed
            }
        } else {
//            $this->logger->write("No cache function");
            throw new CacheNotInstalledException();
        }
//        else {
//            return null;
//        }
//        $cacheFile = $this->destination . '/cache.' . str_replace('/', '__', $key);
//        if (file_exists($cacheFile)) {
//            $this->logger->write("Found '$key'");
//            return unserialize(file_get_contents($cacheFile));
//        } else {
//            $this->logger->write("Didn't find '$key'");
//            return null;
//        }
    }

    public function set($key, $value) {
        if (function_exists('apc_store')) {
//            $this->logger->write("Setting '$key'");
            apc_store($key, serialize($value), $this->expire);
        } else {
            throw new CacheNotInstalledException();
        }
//        $cacheFile = $this->destination . '/cache.' . str_replace('/', '__', $key);
//        file_put_contents($cacheFile, serialize($value));
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
//        $cacheFile = $this->destination . '/cache.' . str_replace('/', '__', $key);
//        if (file_exists($cacheFile)) {
//            unlink($cacheFile);
//        }
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
//        $cacheKey = str_replace('\/', '__', $keyPattern);
//        foreach (glob($this->destination . '/cache.*') as $cacheFile) {
//            if (preg_match($cacheKey, substr($cacheFile, strlen($this->destination) + 7))) {
//                unlink($cacheFile);
//            }
//        }
    }
}

//TODO: Move to system\exception
final class CacheNotInstalledException extends \Exception {
    public function __construct() {
        trigger_error("No APC is installed on the server. No cache functionality is available");
    }
}