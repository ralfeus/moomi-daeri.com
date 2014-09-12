<?php
require_once('DBDriver.php');
final class MySQL implements DBDriver{
    /** @var \mysqli */
	private $connection;
    /** @var mysqli_stmt[] */
    private $statements = array();
	
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

    /**
     * @param string $sql
     * @return mysqli_stmt
     * @throws mysqli_sql_exception
     */
    private function prepareQuery($sql) {
        $statementHash = md5($sql);
        if (!array_key_exists($statementHash, $this->statements)) {
            $this->statements[$statementHash] = $this->connection->prepare($sql);
        }

        if ( $this->statements[$statementHash]) {
            return $this->statements[$statementHash];
        } else {
            throw new mysqli_sql_exception($this->connection->error . "\n" . $sql, $this->connection->errno);
        }
    }

    /**
     * Parameters should be in format i|d|s|b:value
     * Where:
     * i - integer
     * d - double
     * s - string
     * b - BLOB
     * @param string $sql
     * @param string[] $params
     * @param bool $log
     * @return stdClass|int
     * @throws mysqli_sql_exception
     */
    public function query($sql, $params = array(), $log = false) {
        if ($log) {
            $log = new Log('error.log');
            $log->write($sql);
        }

        $statement = $this->prepareQuery($sql);
        if (sizeof($params)) {
            $types = ''; $args = array(); $refArgs = array(); $i = 0;
            foreach ($params as $param) {
                list($type, $value) = preg_split("/:/", $param);
                $types .= $type;
                $args[$i] = $value;
                $refArgs[] = &$args[$i++];
            }

            if (!call_user_func_array(array($statement, 'bind_param'), array_merge(array($types), $refArgs))) {
                throw new mysqli_sql_exception($statement->error, $statement->errno);
            }
        }
        if ($statement->execute()) {
            if ($statement->affected_rows == -1) {
                $statement->store_result();
                $fields = array(); $row = null;
                $result = new stdClass();
                $meta = $statement->result_metadata();

                while ($field = $meta->fetch_field()) {
                    $fields[] = &$row[$field->name];
                }
                call_user_func_array(array($statement, 'bind_result'), $this->refValues($fields));
                while ($statement->fetch()) {
                    $currentRow = array();
                    foreach ($row as $key => $value) {
                        $currentRow[$key] = $value;
                    }
                    $result->rows[] = $currentRow;
                }

                $result->row = isset($result->rows[0]) ? $result->rows[0] : array();
                $result->num_rows = sizeof($result->rows);

                return $result;
            } else {
               return $statement->affected_rows;
            }
        } else {
            throw new mysqli_sql_exception($statement->error, $statement->errno);
        }
  	}

    /**
     * Parameters should be in format i|d|s|b:value
     * Where:
     * i - integer
     * d - double
     * s - string
     * b - BLOB
     * @param string $sql
     * @param array $params
     * @param bool $log
     * @return mixed
     */
    public function queryScalar($sql, $params = array(), $log = false) {
        $result = $this->query($sql, $params, $log);
        if ($result->num_rows) {
            return $result->row[array_keys($result->row)[0]];
        } else {
            return null;
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

    private function refValues($arr) {
        if (strnatcmp(phpversion(),'5.3') >= 0) { //Reference is required for PHP 5.3+
            $refs = array();
            foreach($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }
	
	public function __destruct() {
		$this->connection->close();
	}
}