<?php
require_once('DBDriver.php');
final class MySQL implements DBDriver{
    /** @var PDO */
	private $connection;
    /** @var PDOStatement[] */
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
            $this->statements[$statementHash] = $this->connection->prepare($sql);
            return $this->statements[$statementHash];
    }

    /**
     * @param string $sql
     * @param string[] $params
     * @param bool $log
     * @return int|stdClass
     * @throws Exception
     */
    public function query($sql, $params = array(), $log = false) {
        $sql = trim($sql);
        if ($log) {
            $log = new Log('error.log');
            $log->write($sql);
        }

        $attempts = 3; $statement = null; $lastError = new Exception("Unknown error has occurred during query execution");
        while ($attempts--) {
            try {
                $statement = $this->prepareQuery($sql);
                $args = array();
                if (sizeof($params)) {
                    if (array_keys($params)[0] === 0) {
                        $types = '';
                        $refArgs = array();
                        $i = 0;
                        // leftover from mysqli params binding
                        // PDO doesn't require such perversions
                        //TODO: remove as soon as all statements will adhere PDO conventions
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
                }
                if ($statement->execute($args) === true) {
                    if (strtoupper(substr($sql, 0, 6)) == 'SELECT') {
                        $rowSet = new stdClass();
                        $rowSet->rows = $statement->fetchAll(PDO::FETCH_ASSOC);
                        $rowSet->row = isset($rowSet->rows[0]) ? $rowSet->rows[0] : array();
                        $rowSet->num_rows = sizeof($rowSet->rows);
                        $result = $rowSet;
                    } else {
                        $this->affectedCount = $statement->rowCount();
                        $result = $this->affectedCount;
                    }
                    $statement->closeCursor();
                    return $result;
                } else {
                    error_log($statement->errorCode() . ": " . $statement->errorInfo()[2]);
                    throw new PDOException(print_r($statement->errorInfo(), true));
                }
            } catch (PDOException $exc) {
                $lastError = $exc;
                error_log($exc->getMessage());
                error_log($exc->getTraceAsString());
                $this->reconnect();
            }
        }
        if (!$log) {
            $log = new Log('error.log');
        }
        $log->write($sql);
        $log->write(print_r($params, true));
        throw $lastError;
    }

    /**
     * @param string $sql
     * @param array $params
     * @param bool $log
     * @return array|bool Returns false is no rows is returned
     */
    public function queryScalar($sql, $params = array(), $log = false) {
        $result = $this->query($sql, $params, $log);
        if ($result->num_rows) {
            $temp = array_keys($result->row);
            return $result->row[$temp[0]];
        } else {
            return false;
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

	public function __destruct() {
        unset($this->connection);
        file_put_contents(DIR_LOGS . '/sql.queries.log', self::$queriesLog, FILE_APPEND);
	}

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commitTransaction() {
        return $this->connection->commit();
    }

    public function rollbackTransaction() {
        return $this->connection->rollBack();
    }
}