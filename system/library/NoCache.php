<?php
namespace system\library;

final class NoCache extends Cache{
    /**
     * Dumb cache imitation
     * @param string $key
     * @return null
     */
    public function get($key) {
        return null;
    }

    /**
     * Dumb cache imitation
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value) { }

    /**
     * Dumb cache imitation
     * @param $key
     * @return void
     */
    public function delete($key) { }

    /**
     * Dumb cache imitation
     * @param $keyPattern
     * @return void
     */
    public function deleteAll($keyPattern) { }
}