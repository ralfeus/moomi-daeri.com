<?php
namespace system\library;
use system\exception\CacheNotInstalledException;

final class WinCache extends Cache{
    private $destination = '/var/tmpfs';
	private $cacheName = 'WinCache';

    /**
     * @param string $key
     * @return mixed
     * @throws CacheNotInstalledException
     */
    public function get($key) {
        if (function_exists('wincache_ucache_exists')) {
            if (wincache_ucache_exists($key)) {
                $value = wincache_ucache_get($key);
                return $value;
            } else {
                return null;
            }
        } else {
            throw new CacheNotInstalledException($this->cacheName);
        }
    }

    public function set($key, $value) {
        if (function_exists('wincache_ucache_set')) {
//            $this->logger->write("Storing " . count($value) . " of " . gettype($value) . " to '$key'");
            wincache_ucache_set($key, $value, $this->expire);
        } else {
            throw new CacheNotInstalledException($this->cacheName);
        }
    }

    /**
     * @param string $key
     * @return bool
     * @throws CacheNotInstalledException
     */
    public function delete($key) {
        if (function_exists('wincache_ucache_delete')) {
            return wincache_ucache_delete($key);
        } else {
            throw new CacheNotInstalledException($this->cacheName);
        }
    }

	/**
	 * @param string keyPattern A regex describing keys to delete
	 * @return bool
	 * @throws CacheNotInstalledException
	*/
    public function deleteAll($keyPattern) {
        if (function_exists('wincache_ucache_info')) {
            $cacheInfo = wincache_ucache_info();
            if ($keyPattern == '/^product/') {
                $this->logger->write(print_r($cacheInfo, true));
            }
            foreach ($cacheInfo['ucache_entries'] as $entry) {
				if (preg_match($keyPattern, $entry['key_name'])) {
					wincache_ucache_delete($entry['key']);
				}
            }
        } else {
            throw new CacheNotInstalledException($this->cacheName);
        }
    }
}