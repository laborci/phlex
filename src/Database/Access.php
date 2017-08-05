<?php namespace Phlex\Database;

use PDO;
use Phlex\Sys\ServiceManager;
use Psr\Log\LoggerInterface;


class Access {

	/** @var \PDO */
	private $connection;

	/** @var string */
	private $database;

	/** @var  Log */
	private $logger;

	/**
	 * Access constructor.
	 * Creates a new DB handler to the specified database
	 * @param $connectionUrl
	 */
	public function __construct($connectionUrl) {

		$this->logger = ServiceManager::get(LoggerInterface::class);

		$url = parse_url($connectionUrl);
		parse_str($url['query'], $options);

		$host     = $url['host'];
		$database = trim($url['path'],'/');
		$user     = $url['user'];
		$password = $url['pass'];
		$port     = $url['port'];
		$charset  = array_key_exists('charset',$options) ? $options['charset'] : 'utf-8';

		$this->database = $database;

		$dsn = 'mysql:host='.$host.';dbname='.$database.';port='.$port.';charset='.$charset;

		$this->connection = new PDO($dsn, $user, $password, [PDO::ATTR_PERSISTENT => true]);
		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->prepare("SET CHARACTER SET ?")->execute(array($charset));
	}


	/**
	 * Executes a pure SQL statement.
	 *
	 * @param string $sql    SQL statement (needs to be properly escaped)
	 * @param string $method type of SQL method for debug purposes
	 * @return \PDOStatement|boolean result set as PDOStatement (if any) or false
	 * @throws \Phlex\Database\Exception
	 */
	private function runCommand($sql, $method) {
		if(!is_null($this->logger)) $this->logger->debug($sql, ['method'=> $method]);
		$result = $this->connection->query($sql, PDO::FETCH_ASSOC);
		return $result ? $result : false;
	}

	/**
	 * Imports the given variables into the SQL statements' $# placeholders, and exetutes the query.
	 * The # means the number of the parameter starting at value 1.
	 *
	 * @param string $sql
	 * @param array  ...$sqlParams
	 * @return \PDOStatement
	 */
	public function query(string $sql, ...$sqlParams) {
		if(count($sqlParams) > 0) $sql = $this->buildSQL($sql, $sqlParams);
		return $this->runCommand($sql, 'query');
	}

	/**
	 * An alias of getValue:
	 * Returns a field according to the given SQL statement. Can contain $# placeholders.
	 *
	 * @param  string $sql
	 * @param array   ...$sqlParams
	 * @return mixed
	 */
	public function getField(string $sql, ...$sqlParams) {
		if(count($sqlParams) > 0) $sql = $this->buildSQL($sql, $sqlParams);
		return $this->getValue($sql);
	}

	/**
	 * Returns a field according to the given SQL statement. Can contain $# placeholders.
	 *
	 * @param string $sql the SQL statement with optional $# placeholders
	 * @param array  ...$sqlParams
	 * @return mixed|null
	 */
	public function getValue(string $sql, ...$sqlParams) {
		if(count($sqlParams) > 0) $sql = $this->buildSQL($sql, $sqlParams);
		$row = $this->getRow($sql);
		if($row) return reset($row);
		return null;
	}

	/**
	 * An alias of getFirstRow:
	 * Returns the first matching row according to the given SQL statement. Can contain $# placeholders
	 *
	 * @param string $sql
	 * @param array  ...$sqlParams
	 * @return array
	 */
	public function getRow(string $sql, ...$sqlParams) {
		if(count($sqlParams) > 0) $sql = $this->buildSQL($sql, $sqlParams);
		if(stripos($sql, ' LIMIT ') === false) $sql .= " LIMIT 1";
		return $this->getFirstRow($sql);
	}

	/**
	 * @param string $sql
	 * @param array  ...$sqlParams
	 * @return bool|mixed
	 */
	public function getFirstRow(string $sql, ...$sqlParams) {
		if(count($sqlParams) > 0) $sql = $this->buildSQL($sql, $sqlParams);
		if(!$result = $this->runCommand($sql, 'getRow')) return false;

		$row = $result->fetch(PDO::FETCH_ASSOC);
		$result->closeCursor();
		return $row;
	}

	/**
	 * Returns a row from the specified table having the specified id
	 *
	 * @param string $table
	 * @param int    $id
	 * @return array
	 */
	public function getRowById(string $table, int $id) {
		$table = $this->escapeSQLEntity($table);
		$sql = "SELECT * FROM " . $table . " WHERE id=" . $this->quote($id);
		return $this->getFirstRow($sql);
	}

	/**
	 * Returns a row from the specified table having the specified id
	 *
	 * @param string $table
	 * @param int    $id
	 * @return array
	 */
	public function getRowsById(string $table, array $ids) {
		$table = $this->escapeSQLEntity($table);
		print_r($ids);
		$sql = 'SELECT * FROM '.$table.' WHERE  id IN ('.join(',', $this->quoteArray($ids)).')';
		return $this->getRows($sql);
	}

	/**
	 * An alias of getRows:
	 * Returns the complete result set as associative a pure PHP array. Can contain $# placeholders.
	 *
	 * @param string $sql
	 * @param array  ...$sqlParams
	 * @return array
	 */
	public function getAll(string $sql, ...$sqlParams) {
		if(count($sqlParams) > 0) $sql = $this->buildSQL($sql, $sqlParams);
		return $this->getRows($sql);
	}

	/**
	 * Returns the complete result set as associative a pure PHP array. Can contain $# placeholders.
	 *
	 * @param string $sql
	 * @param array  ...$sqlParams
	 * @return array|bool
	 */
	public function getRows(string $sql, ...$sqlParams) {
		if(count($sqlParams) > 0) $sql = $this->buildSQL($sql, $sqlParams);

		$rows = array();
		if(!$result = $this->runCommand($sql, 'getAll')) return false;

		if($result->rowCount()) {
			foreach($result as $row) {
				if(array_key_exists('__KEY__', $row) && !array_key_exists('__VALUE__', $row)) {
					$key = $row['__KEY__'];
					unset($row['__KEY__']);
					$rows[$key] = $row;
				} else if(array_key_exists('__KEY__', $row) && array_key_exists('__VALUE__', $row)) {
					$rows[$row['__KEY__']] = $row['__VALUE__'];
				} else if(array_key_exists('__VALUE__', $row)) {
					$rows[] = $row['__VALUE__'];
				} else {
					$rows[] = $row;
				}
			}
		}
		$result->closeCursor();
		return $rows;
	}

	/**
	 * Executes an INSERT SQL statement.
	 *
	 * @param string $tableName
	 * @param array  ...$dataList [!]fieldName => newValue pairs. If fieldName starts with ! and the value is not ''
	 *                            value left unescaped, if the value '' the inserted value will be NULL
	 * @return bool|int
	 */
	public function insert(string $tableName, ...$dataList) {
		return $this->insertValues($tableName, $dataList, false);
	}

	/**
	 * @param string $tableName
	 * @param array  ...$dataList [!]fieldName => newValue pairs. If fieldName starts with ! and the value is not ''
	 *                            value left unescaped, if the value '' the inserted value will be NULL
	 * @return bool|int
	 */
	public function insertIgnore(string $tableName, ...$dataList) {
		return $this->insertValues($tableName, $dataList, true);
	}

	/**
	 * @param string $tableName
	 * @param array  $arrayOfValues
	 * @param bool   $isIgnore
	 * @return bool|int
	 * @throws \Phlex\Database\Exception
	 */
	public function insertValues(string $tableName, array $arrayOfValues, bool $isIgnore) {
		$table = $this->escapeSQLEntity($tableName);

		$fields = array();

		$valueMatrix = array();
		foreach($arrayOfValues as $data) {
			$values = array();

			if(!$fields) $fields = array_map(function ($row) { return ltrim($row, '!'); }, array_keys($data));
			else if(implode('', $fields) !== implode('', array_map(function ($row) { return ltrim($row, '!'); }, array_keys($data)))) throw new Exception ("Unidentical insertation field list.");

			foreach($data as $key=>$val){
				if(substr($key, 0, 1) == '!') {
					$key = substr($key, 1);
					array_push($values, strlen($val) == 0 ? 'NULL' : $val);
				} else {
					array_push($values, $this->quote($val));
				}
			}
			$valueMatrix[] = '(' . implode(',', $values) . ')';
		}

		$sql = 'INSERT ' . ($isIgnore === true ? 'IGNORE' : '') . ' INTO ' . $table . ' (' . implode(',', $this->escapeSQLEntities($fields)) . ') VALUES ' . implode(', ', $valueMatrix);
		if(!$result = $this->runCommand($sql, 'insert ignore')) return false;
		$id = (int)$this->connection->lastInsertId();   // the important comment is above at insert method... some sources say when INSERT IGNORE does not insert any row lastInsertId gives still the next id... donno.

		if($id === 0) return true;
		return $id;
	}

	/**
	 * Updates a table with the given data at the specified conditions.
	 *
	 * @param string     $tableName
	 * @param array      $data
	 * @param int|string $id
	 * @param array      ...$sqlParams
	 * @return bool|int
	 */
	public function update(string $tableName, array $data, $id, ...$sqlParams) {
		$table = $this->escapeSQLEntity($tableName);

		if(!trim($id)) return false;
		else if(is_numeric($id)) $where = "id=" . $this->quote($id);
		else {
			$where = $id;
			if(count($sqlParams) > 0) $where = $this->buildSQL($where, $sqlParams);
		}

		$field_value_pairs = array();
		foreach($data as $key=>$val){
			if($key[0] == '!') {
				$val = (strlen($val) == 0 ? 'NULL' : $val);
				array_push($field_value_pairs, '`' . (substr($key, 1) . '`=' . $val));
			} else {
				array_push($field_value_pairs, '`' . $key . '`=' . $this->quote($val));
			}
		}
		$sql = "UPDATE " . $table . " SET " . implode(",", $field_value_pairs) . (($where) ? (" WHERE " . $where) : (''));

		if(!$result = $this->runCommand($sql, 'update')) return false;
		return $result->rowCount();
	}

	/**
	 * Deletes the row having the given id from the specified table.
	 *
	 * @param string     $tableName
	 * @param int|string $id
	 * @param array      ...$sqlParams
	 * @return bool|int
	 */
	public function delete(string $tableName, $id, ...$sqlParams) {
		$table = $this->escapeSQLEntity($tableName);

		if(is_numeric($id)) $where = " `id` = " . $this->quote($id);
		else if(is_string($id)) {
			$where = $id;
			if(count($sqlParams) > 0) $where = $this->buildSQL($where, $sqlParams);
		} else $where = $id;

		$where = Filter::where($where)->GetSql($this);
		if(!trim($where)) return false;

		$sql = "DELETE FROM " . $table . " WHERE " . $where;
		if(!$result = $this->runCommand($sql, 'delete')) return false;
		return $result->rowCount();
	}

	/**
	 * Imports the given values into the SQL statements $# placeholders
	 *
	 * @param string $sql       the SQL statement. Can have $# placeholders.
	 * @param mixed  $sqlParams array of values to import or the first element of the values in the functions param list.
	 *                          Array values will be quoted and imploded with , characters
	 * @return string the built/translated SQL statement
	 */
	public function buildSQL(string $sql, array $sqlParams = []) {
		if(count($sqlParams)) {
			foreach($sqlParams as $key => $value) {
				if(is_array($value)) {
					$array = array();
					foreach($value as $item) $array[] = $this->quote($item);
					$sqlParams[$key] = join(',', $array);
				} else $sqlParams[$key] = $this->quote($value);
			}
		}

		for($i = count($sqlParams); $i > 0; --$i) $sql = str_replace('$' . $i, $sqlParams[$i - 1], $sql);

		return $sql;
	}

	#region escape & quote
	public function quote($subject, bool $quote = true):string { return $subject === null ? 'NULL' : ($quote ? $this->connection->quote($subject) : trim($this->connection->quote($subject),"'")); }
	public function quoteArray(array $array, bool $quote = true):array { return array_map(function($val) use ($quote) {return $this->quote($val, $quote);}, $array); }
	public function escapeSQLEntity($subject):string { return '`'.$subject.'`'; }
	public function escapeSQLEntities(array $array):array { return array_map(function($val)  {return $this->escapeSQLEntity($val, $quote);}, $array); }
	#endregion

	#region transaction
	public function beginTransaction():bool { return $this->connection->beginTransaction(); }
	public function commit():bool { return $this->connection->commit(); }
	public function rollBack():bool { return $this->connection->rollBack(); }
	public function inTransaction():bool { return $this->connection->inTransaction(); }
	#endregion

	#region table manipulation
	public function tableExists(string $table):bool { return $this->getFirstRow("SHOW TABLES LIKE '" . $table . "'") ? true : false; }
	public function getTableType(string $table):string { return $this->getFirstRow("SHOW FULL TABLES WHERE Tables_in_" . $this->database . " = $1", $table)['Table_type']; }
	public function renameTable(string $from, string $to):void { $this->query("RENAME TABLE " . $this->escapeSQLEntity($from) . " TO " . $this->escapeSQLEntity($to)); 	}
	public function addTable(string $table, string $properties):void {$this->query("CREATE TABLE ".$this->escapeSQLEntity($table)." " . $properties); }
	public function deleteTable(string $table):void { $this->query("DROP TABLE ".$this->escapeSQLEntity($table)); }
	public function addView(string $view, string $select):void { $this->query("CREATE VIEW ".$this->escapeSQLEntity($view)." AS ".$select); }
	public function deleteView(string $view):void { $this->query("DROP VIEW IF EXISTS `" . $view . "`"); }
	#endregion

	#region field manipulation
	public function fieldExists(string $table, string $field):bool { return $this->getFirstRow("SHOW FULL COLUMNS FROM `" . $table . "` WHERE Field = '" . $field . "'") ? true : false; }
	public function addField(string $table, string $field, string $properties):void { $this->query("ALTER TABLE ".$this->escapeSQLEntity($table)." ADD ".$this->escapeSQLEntity($field)." ".$properties); }
	public function deleteField(string $table, string $field):void { $this->query("ALTER TABLE ".$this->escapeSQLEntity($table)." DROP ".$this->escapeSQLEntity($field)); }
	public function getFieldList(string $table):array { return array_column($this->getFieldData($table), 'Field'); }
	public function getFieldData(string $table):array { return $this->getAll("SHOW FULL COLUMNS FROM " . $this->escapeSQLEntity($table)); }
	public function getEnumValues(string $tableName, string $field):array { preg_match_all("/'(.*?)'/", $this->getRows("DESCRIBE ".$this->escapeSQLEntity($tableName)." ".$this->quote($field))[0]['Type'], $matches); return $matches[1]; }
	#endregion
}