<?php
require_once('DBDriver.php');
final class MySQL implements DBDriver{
    /** @var PDO */
	private $connection;
    /** @var mysqli_stmt[] */
    private $statements = array();
    /** @var string */
    private static $queriesLog = "";
    private $hostName;
    private $userName;
    private $password;
    private $database;
    private $affectedCount;
	
	public function __construct($hostname, $username, $password, $database) {
        $this->hostName = $hostname;
        $this->userName = $username;
        $this->password = $password;
        $this->database = $database;
		$this->connect();
  	}

    private function connect() {
//        if (!$this->connection = new mysqli($this->hostName, $this->userName, $this->password)) {
//            throw new mysqli_sql_exception("Could not make a database connection using " . $this->userName . '@' . $this->hostName);
//        }
        $this->connection = new PDO(
            'mysql:host=' . $this->hostName . ';dbname=' . $this->database,
            $this->userName,
            $this->password,
            array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "
                    SET NAMES utf8;
                    SET CHARACTER SET utf8;
                    SET CHARACTER_SET_CONNECTION=utf8;
                    SET SQL_MODE = ''
                ",
                PDO::ATTR_PERSISTENT => true
            )
        );

//        $this->connection->query("SET NAMES 'utf8'");
//        $this->connection->query("SET CHARACTER SET utf8");
//        $this->connection->query("SET CHARACTER_SET_CONNECTION=utf8");
//        $this->connection->query("SET SQL_MODE = ''");
    }

    /**
     * @param string $sql
     * @return PDOStatement
     * @throws PDOException
     */
    private function prepareQuery($sql) {
        $statementHash = md5($sql);
        if (array_key_exists($statementHash, $this->statements)) {
            return $this->statements[$statementHash];
        }
        $statement = null;
//        $attempts = 3; $lastError = null;
//        while ($attempts--) {
//            if (($statement = $this->connection->prepare($sql)) !== false) {
//                $this->statements[$statementHash] = $statement;
//                return $this->statements[$statementHash];
            $this->statements[$statementHash] = $this->connection->prepare($sql);
            return $this->statements[$statementHash];
//            } else {
//                throw new mysqli_sql_exception($this->connection->error . "\n" . $sql, $this->connection->errno);
////                $this->reconnect();
//            }
//        }
//        throw $lastError;
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
        $sql = trim($sql);
        if ($log) {
            $log = new Log('error.log');
            $log->write($sql);
        }

        $attempts = 3; $statement = null; $lastError = null;
        while ($attempts--) {
            try {
                $statement = $this->prepareQuery($sql);
//        self::$queriesLog .= "#PID: " . getmypid() . "\r\n";
//        self::$queriesLog .= "#Query: $sql\r\n";
                $args = array();
                if (sizeof($params)) {
                    if (array_keys($params)[0] === 0) {
                        $types = '';
    //                    $args = array();
                        $refArgs = array();
                        $i = 0;
                        foreach ($params as $param) {
                            $type = substr($param, 0, 1);
                            $value = substr($param, 2);
                            $types .= $type;
                            $args[$i] = $value;
                            $refArgs[] = &$args[$i++];
                        }
                    } else {
                        $args = $params;
                    }

//                    if (!call_user_func_array(array($statement, 'bind_param'), array_merge(array($types), $refArgs))) {
//                        throw new mysqli_sql_exception($statement->error, $statement->errno);
//                    }
                }
//        self::$queriesLog .= "#Start: " . (new DateTime())->format("Y-m-d H:i:s.u") . "\r\n";
//                if ($statement->execute()) {
                if ($statement->execute($args) === true) {
//            self::$queriesLog .= "#Stop: " . (new DateTime())->format("Y-m-d H:i:s.u") . "\r\n";
//                    if ($statement->affected_rows == -1) {
                    if (strtoupper(substr($sql, 0, 6)) == 'SELECT') {
//                        $statement->store_result();
//                        $fields = array();
//                        $row = null;
                        $rowSet = new stdClass();
//                        $meta = $statement->result_metadata();
//
//                        while ($field = $meta->fetch_field()) {
//                            $fields[] = &$row[$field->name];
//                        }
//                        call_user_func_array(array($statement, 'bind_result'), $this->refValues($fields));
                        $rowSet->rows = array();
//                        while ($statement->fetch()) {
//                            $currentRow = array();
//                            foreach ($row as $key => $value) {
//                                $currentRow[$key] = $value;
//                            }
//                            $rowSet->rows[] = $currentRow;
//                        }
//
                        $rowSet->rows = $statement->fetchAll();
                        $rowSet->row = isset($rowSet->rows[0]) ? $rowSet->rows[0] : array();
                        $rowSet->num_rows = sizeof($rowSet->rows);
                        $result = $rowSet;
                    } else {
//                        $result = $statement->affected_rows;
                        $this->affectedCount = $statement->rowCount();
                        $result = $this->affectedCount;
                    }
                    $statement->closeCursor();
                    return $result;
                } else {
//            self::$queriesLog .= "#Stop: " . (new DateTime())->format("Y-m-d H:i:s.u") . "\r\n";
                    $lastError = new PDOException(print_r($statement->errorInfo(), true), $statement->errorCode());
                    error_log($statement->errorCode() . ": " . $statement->errorInfo()[2]);
                }
            } catch (\Exception $exc) {
                $lastError = $exc;
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
        unset($this->connection);
        $this->connect();
    }

	public function escape($value) {
        $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
        $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

        return str_replace($search, $replace, $value);
    }
	
  	public function countAffected() {
    	return $this->affectedCount;
  	}

  	public function getLastId() {
    	return $this->connection->lastInsertId();
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
//		$this->connection->close();
        unset($this->connection);
        file_put_contents(DIR_LOGS . '/sql.queries.log', self::$queriesLog, FILE_APPEND);
	}
}