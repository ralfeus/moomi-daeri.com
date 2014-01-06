<?php
require_once('DBDriver.php');
final class MySQL implements DBDriver{
    /** @var PDO */
	private $connection;
    /** @var PDOStatement */
    private $statement;
	
	public function __construct($hostname, $username, $password, $database) {
        $this->connection = new PDO(
            "mysql:host=$hostname;dbname=$database",
            $username,
            $password,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET CHARACTER SET utf8; SET CHARACTER_SET_CONNECTION=utf8; SET SQL_MODE = ''")
        );
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  	}
		
  	public function query($sql, $log = false) {
        if ($log) {
          $log = new Log('error.log');
          $log->write($sql);
        }
        $this->statement = $this->connection->prepare($sql);
        $result = $this->statement->execute();
//		$resource = mysql_query($sql, $this->connection);
		if ($result) {
            $i = 0;

            $data = array();
            foreach ($this->statement->fetchAll() as $result) {
                $data[$i] = $result;
                $i++;
            }
            if ($log) $log->write($i);

            if ($log) $log->write("Resource is freed up");
            $query = new stdClass();
            $query->row = isset($data[0]) ? $data[0] : array();
            $query->rows = $data;
            $query->num_rows = $i;

            unset($data);
            return $query;
		} else {
			trigger_error('Error: ' . $this->statement->errorInfo() . '<br />Error No: ' . $this->statement->errorCode() . '<br />' . $sql);
			exit();
    	}
        $this->statement->closeCursor();
    }
	
	public function escape($value) {
		return $value;
	}
	
  	public function countAffected() {
    	return $this->statement->rowCount();
  	}

  	public function getLastId() {
    	return $this->connection->lastInsertId();
  	}	
	
	public function __destruct() {
		$this->statement->closeCursor();
	}
}
?>