<?php
require_once('DBDriver.php');
final class MySQL implements DBDriver{
    /** @var \mysqli */
	private $connection;
	
	public function __construct($hostname, $username, $password, $database) {
		if (!$this->connection = new mysqli($hostname, $username, $password)) {
      		exit('Error: Could not make a database connection using ' . $username . '@' . $hostname);
    	}

    	if (!$this->connection->select_db($database)) {
      		exit('Error: Could not connect to database ' . $database);
    	}
		
		$this->connection->query("SET NAMES 'utf8'");
        $this->connection->query("SET CHARACTER SET utf8");
        $this->connection->query("SET CHARACTER_SET_CONNECTION=utf8");
        $this->connection->query("SET SQL_MODE = ''");
  	}
		
  	public function query($sql, $log = false) {
          if ($log) {
              $log = new Log('error.log');
              $log->write($sql);
          }
        /** @var $resource \mysqli_result */
		$resource = $this->connection->query($sql);
		if ($resource) {
			if (is_object($resource)) {
				$i = 0;
    	
				$data = array();
				while ($result = $resource->fetch_assoc()) {
					$data[$i] = $result;
					$i++;
				}
                if ($log) $log->write($i);

				$resource->free_result();
				if ($log) $log->write("Resource is freed up");
				$query = new stdClass();
				$query->row = isset($data[0]) ? $data[0] : array();
				$query->rows = $data;
				$query->num_rows = $i;
				
				unset($data);

				return $query;	
    		} else {
				return $resource;
			}
		} else {
//			trigger_error('Error: ' . $this->connection->error . '<br />Error No: ' . $this->connection->errno . '<br />' . $sql);
            throw new mysqli_sql_exception($this->connection->error, $this->connection->errno);
			exit();
    	}
  	}
	
	public function escape($value) {
		return $this->connection->real_escape_string($value);
	}
	
  	public function countAffected() {
    	return $this->connection->affected_rows;
  	}

  	public function getLastId() {
    	return $this->connection->insert_id;
  	}
	
	public function __destruct() {
		$this->connection->close();
	}
}
?>