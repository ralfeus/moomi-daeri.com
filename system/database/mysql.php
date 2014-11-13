<?php
require_once('DBDriver.php');
final class MySQL implements DBDriver{
    /** @var \mysqli */
	private $connection;
    /** @var mysqli_stmt[] */
    private $statements = array();
    /** @var string */
    private static $queriesLog = "";
    private $hostName;
    private $userName;
    private $password;
    private $database;
	
	public function __construct($hostname, $username, $password, $database) {
        $this->hostName = $hostname;
        $this->userName = $username;
        $this->password = $password;
        $this->database = $database;
		$this->connect();
  	}

    private function connect() {
        if (!$this->connection = new mysqli($this->hostName, $this->userName, $this->password)) {
            throw new mysqli_sql_exception("Could not make a database connection using " . $this->userName . '@' . $this->hostName);
        }

        if (!$this->connection->select_db($this->database)) {
            throw new mysqli_sql_exception("Could not connect to database '" . $this->database . "'");
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
        if (array_key_exists($statementHash, $this->statements)) {
            return $this->statements[$statementHash];
        }
        $statement = null;
        $attempts = 3; $lastError = null;
        while ($attempts--) {
            $statement = $this->connection->prepare($sql);
            if ($statement) {
                $this->statements[$statementHash] = $statement;
                return $this->statements[$statementHash];
            } else {
                $lastError = new mysqli_sql_exception($this->connection->error . "\n" . $sql, $this->connection->errno);
                $this->reconnect();
            }
        }
        throw $lastError;
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

        $attempts = 3; $statement = null;
        while ($attempts--) {
            $statement = $this->prepareQuery($sql);
//        self::$queriesLog .= "#PID: " . getmypid() . "\r\n";
//        self::$queriesLog .= "#Query: $sql\r\n";
            if (sizeof($params)) {
                $types = '';
                $args = array();
                $refArgs = array();
                $i = 0;
                foreach ($params as $param) {
                    $type = substr($param, 0, 1);
                    $value = substr($param, 2);
                    $types .= $type;
                    $args[$i] = $value;
                    $refArgs[] = &$args[$i++];
                }

                if (!call_user_func_array(array($statement, 'bind_param'), array_merge(array($types), $refArgs))) {
                    throw new mysqli_sql_exception($statement->error, $statement->errno);
                }
            }
//        self::$queriesLog .= "#Start: " . (new DateTime())->format("Y-m-d H:i:s.u") . "\r\n";
            if ($statement->execute()) {
//            self::$queriesLog .= "#Stop: " . (new DateTime())->format("Y-m-d H:i:s.u") . "\r\n";
                if ($statement->affected_rows == -1) {
                    $statement->store_result();
                    $fields = array();
                    $row = null;
                    $result = new stdClass();
                    $meta = $statement->result_metadata();

                    while ($field = $meta->fetch_field()) {
                        $fields[] = &$row[$field->name];
                    }
                    call_user_func_array(array($statement, 'bind_result'), $this->refValues($fields));
                    $result->rows = array();
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
//            self::$queriesLog .= "#Stop: " . (new DateTime())->format("Y-m-d H:i:s.u") . "\r\n";
                error_log($statement->errno . ": " . $statement->error);
            }
        }
        throw new mysqli_sql_exception($statement->error, $statement->errno);
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
     * @return mixed|bool Returns false is no rows is returned
     */
    public function queryScalar($sql, $params = array(), $log = false) {
        $result = $this->query($sql, $params, $log);
        if ($result->num_rows) {
            $temp = array_keys($result->row);
            return $result->row[$temp[0]];
        } else {
            return null;
        }
    }

    private function reconnect() {
        $this->connection->close();
        unset($this->connection);
        $this->connect();
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
        unset($this->connection);
        file_put_contents(DIR_LOGS . '/sql.queries.log', self::$queriesLog, FILE_APPEND);
	}
}