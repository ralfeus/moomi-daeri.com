<?php
namespace system\library;
use system\database\DBDriver;

final class DB {
    /** @var DBDriver */
	private $driver;

    /**
     * @param string $driver
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $database
     * @return DBDriver
     */
    public static function getDB($driver, $hostname, $username, $password, $database) {
		if (file_exists(DIR_DATABASE . $driver . '.php')) {
			require_once(DIR_DATABASE . $driver . '.php');
		} else {
			exit('Error: Could not load database file ' . $driver . '!');
		}
				
		return DBDriver::getDriver($driver, $hostname, $username, $password, $database);
	}

    public function getDriver() {
        return $this->driver;
    }
		
  	public function query($sql, $params, $log) {
		return $this->driver->query($sql, $params, $log);
  	}

    public function queryScalar($sql, $params, $log) {
        return $this->driver->queryScalar($sql, $params, $log);
    }
	
	public function escape($value) {
		return $this->driver->escape($value);
	}
	
  	public function countAffected() {
		return $this->driver->countAffected();
  	}

  	public function getLastId() {
		return $this->driver->getLastId();
  	}	
}