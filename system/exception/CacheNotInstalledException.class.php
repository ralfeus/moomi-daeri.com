<?php
namespace system\exception;
use SebastianBergmann\Exporter\Exception;

final class CacheNotInstalledException extends \Exception {
    public function __construct($cacheName) {
        trigger_error("No $cacheName is installed on the server. No cache functionality is available");
    }
}