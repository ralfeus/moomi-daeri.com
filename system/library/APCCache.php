<?php
namespace system\library;
use system\exception\CacheNotInstalledException;

final class APCCache extends Cache{
    private $destination = '/var/tmpfs';
	private $cacheName = 'APC';

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

                $value = apc_fetch($key);
//                $this->logger->write("Returning " . count($value) . " of " . gettype($value) . " from '$key'");
                return $value;
            } else {
//                $this->logger->write("\tCouldn't find '$key'");
                return null; //TODO: Check why it was removed
            }
        } else {
//            $this->logger->write("No cache function");
            throw new CacheNotInstalledException($this->cacheName);
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
//            $this->logger->write("Storing " . count($value) . " of " . gettype($value) . " to '$key'");
            apc_store($key, $value, $this->expire);
        } else {
            throw new CacheNotInstalledException($this->cacheName);
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
            throw new CacheNotInstalledException($this->cacheName);
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
            throw new CacheNotInstalledException($this->cacheName);
        }
//        $cacheKey = str_replace('\/', '__', $keyPattern);
//        foreach (glob($this->destination . '/cache.*') as $cacheFile) {
//            if (preg_match($cacheKey, substr($cacheFile, strlen($this->destination) + 7))) {
//                unlink($cacheFile);
//            }
//        }
    }
}