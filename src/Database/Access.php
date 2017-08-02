<?php namespace Phlex\Database;

use PDO;
use PDOException;
use Phlex\Sys\Log;
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
	 * @param $logger
	 */
	public function __construct($connectionUrl, LoggerInterface $logger = null) {

		$this->logger = $logger;

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

		$where = Filter::factory($where)->GetSql($this);
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

	// ESCAPE AND QUOTE FUNCTIONS

	/**
	 * Quotes the specified value.
	 *
	 * @param string  $str           value to quote
	 * @param boolean $addQuoteMarks if it's true result will be enclosed into ' (apos) characters
	 * @return string the quoted value or the string NULL if the $str === null
	 */
	public function quote($str, bool $addQuoteMarks = true) {
		return $str === null ? 'NULL' : ($addQuoteMarks ? $this->connection->quote($str) : trim($this->connection->quote($str), "'"));
	}

	/**
	 * Quotes the values of the given array
	 *
	 * @param array   $array         of values need to be quoted
	 * @param boolean $addQuoteMarks if it's true result elements will be enclosed into ' (apos) characters
	 * @return array array of the quoted elements. null elements are translated to NULL strings.
	 */
	public function quoteArray(array $array, bool $addQuoteMarks = true) {
		if($array) foreach($array as $key => $value) $array[$key] = $this->quote($value, $addQuoteMarks);
		return $array;
	}

	/**
	 * Escapes a DB object/entity with ` (backtick) character
	 *
	 * @param string $string The entity needs to be quoted
	 * @return string The quoted object name
	 */
	public function escapeSQLEntity($string) { return '`' . trim($string, '`') . '`'; }

	public function escapeSQLEntities(array $arrayOfStrings) {
		foreach($arrayOfStrings as $i => $string) $arrayOfStrings[$i] = '`' . trim($string, '`') . '`';
		return $arrayOfStrings;
	}

	// END OF ESCAPE AND QUOTE FUNCTIONS

	// TRANSACTION HANDLING

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Initiates a transaction
	 *
	 * @link http://php.net/manual/en/pdo.begintransaction.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function beginTransaction() { return $this->connection->beginTransaction(); }

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Commits a transaction
	 *
	 * @link http://php.net/manual/en/pdo.commit.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function commit() { return $this->connection->commit(); }

	/**
	 * (PHP 5 &gt;= 5.1.0, PECL pdo &gt;= 0.1.0)<br/>
	 * Rolls back a transaction
	 *
	 * @link http://php.net/manual/en/pdo.rollback.php
	 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
	 */
	public function rollBack() { return $this->connection->rollBack(); }

	/**
	 * (PHP 5 &gt;= 5.3.3, Bundled pdo_pgsql)<br/>
	 * Checks if inside a transaction
	 *
	 * @link http://php.net/manual/en/pdo.intransaction.php
	 * @return bool <b>TRUE</b> if a transaction is currently active, and <b>FALSE</b> if not.
	 */
	public function inTransaction() { return $this->connection->inTransaction(); }

	// END OF TRANSACTION HANDLING

	// STRUCTURE INFO AND MANIPULATIONS

	/**
	 * Returns the possible enum values of the speicified field
	 *
	 * @param string $tableName the name of the table
	 * @param string $field     the name of the enum field
	 * @return array the enum options
	 */
	public function getEnumValues(string $tableName, string $field) {
		$table = $this->escapeSQLEntity($tableName);

		$sql = "SHOW COLUMNS FROM $table LIKE " . $this->quote($field);
		$result = $this->query($sql);
		if(!$result) throw new DBException('error getting enum field ', 'cannotReadEnumOptions');
		$row = $result->fetch(PDO::FETCH_NUM);
		$regex = "/'(.*?)'/";
		preg_match_all($regex, $row[1], $enum_array);
		$enum_fields = $enum_array[1];
		return $enum_fields;
	}

	/**
	 * Creates or delets the specified table depending on the $condition param.
	 *
	 * @param boolean $condition  true: creates the table; false: drops the table
	 * @param string  $table      the name of the table
	 * @param string  $properties properties of the table to create with
	 */
	public function toggleTable(bool $condition, string $table, string $properties) {
		if($condition) $this->addTable($table, $properties);
		else $this->delTable($table);
	}

	/**
	 * Renames the specified table
	 *
	 * @param string $from original name
	 * @param string $to   new name
	 * @return boolean
	 */
	public function renameTable(string $from, string $to) {
		if($this->tableExists($from) && (strtolower($from) == strtolower($to) || !$this->tableExists($to))) return $this->query("RENAME TABLE " . $this->escapeSQLEntity($from) . " TO " . $this->escapeSQLEntity($to));
		return false;
	}

	/**
	 * Creates a new table
	 *
	 * @param string $table      the name of the table
	 * @param string $properties the properties of the table to create with
	 */
	public function addTable(string $table, string $properties) {
		$this->query("CREATE TABLE IF NOT EXISTS `" . $table . "` " . $properties);
	}

	/**
	 * Drops a table
	 *
	 * @param string $table the name of the table to drop
	 */
	public function delTable(string $table) { $this->query("DROP TABLE IF EXISTS `" . $table . "`"); }

	/**
	 * Creates a new view
	 *
	 * @param string $view   the name of the view
	 * @param string $select the select statement of the view to create
	 */
	public function addView(string $view, string $select) {
		if(!$this->hasTable($view)) {
			$this->query("CREATE VIEW `" . $view . "` AS " . $select);
		}
	}

	/**
	 * Drops a view
	 *
	 * @param string $view the name of the view to drop
	 */
	public function delView($view) { $this->query("DROP VIEW IF EXISTS `" . $view . "`"); }

	/**
	 * Returns the type of a table object (table or view)
	 *
	 * @param string $table name of the table
	 * @return string
	 */
	public function getTableType(string $table) {
		$result = $this->getFirstRow("SHOW FULL TABLES WHERE Tables_in_" . $this->database . " = $1", $table);
		return $result['Table_type'];
	}

	/**
	 * Adds or drops a field in/from a table
	 *
	 * @param boolean $condition  true: creates the field; false: drops the field
	 * @param string  $table      the name of the table
	 * @param string  $field      the name of the field
	 * @param string  $properties properties of the field will be created
	 */
	public function toggleField(bool $condition, string $table, string $field, string $properties) {
		if($condition) $this->addField($table, $field, $properties);
		else $this->delField($table, $field);
	}

	/**
	 * Adds a field to the specified table
	 *
	 * @param string $table      the name of the table
	 * @param string $field      the name of the field
	 * @param string $properties properties of the field will be created
	 */
	public function addField(string $table, string $field, string $properties) {
		if(!$this->hasField($table, $field)) $this->query("ALTER TABLE " . $this->escapeSQLEntity($table) . " ADD " . $this->escapeSQLEntity($field) . " " . $properties);
	}

	/**
	 * Drops the specified field from the specified table
	 *
	 * @param string $table the name of the table
	 * @param string $field the name of the field to drop
	 */
	public function delField(string $table, string $field) {
		if($this->hasField($table, $field)) $this->query("ALTER TABLE " . $this->escapeSQLEntity($table) . " DROP " . $this->escapeSQLEntity($field));
	}

	/**
	 * Returns the list of field names of the given table
	 *
	 * @param string $table
	 * @return array<string>
	 */
	public function getFieldList(string $table) {
		$fieldData = $this->getFieldData($table);
		$fields = array();
		foreach($fieldData as $field) $fields[] = $field['Field'];
		return $fields;
	}

	/**
	 * Returns the detailed information of fields of the given table
	 *
	 * @param string $table
	 * @return array<array<string>>
	 */
	public function getFieldData(string $table) {
		return $this->getAll("SHOW FULL COLUMNS FROM " . $this->escapeSQLEntity($table));
	}

	/**
	 * An alias of hasTable
	 * Says that the database has a table named $table
	 *
	 * @param string $table name of the table
	 * @return boolean true: table exists; false: table does not exist
	 */
	public function tableExists(string $table) { return $this->hasTable($table); }

	/**
	 * Says that the database has a table named $table
	 *
	 * @param string $table name of the table
	 * @return boolean true: table exists; false: table does not exist
	 */
	public function hasTable(string $table) { return $this->getFirstRow("SHOW TABLES LIKE '" . $table . "'") ? true : false; }

	/**
	 * An alias of hasField
	 * Says that the specified table has a field named $field
	 *
	 * @param string $table name of the table
	 * @param string $field name of the field
	 * @return boolean true: field exists; false: field does not exist
	 */
	public function fieldExists(string $table, string $field) { return $this->hasField($table, $field); }

	/**
	 * Says that the specified table has a field named $field
	 *
	 * @param string $table name of the table
	 * @param string $field name of the field
	 * @return boolean true: field exists; false: field does not exist
	 */
	public function hasField(string $table, string $field) { return $this->getFirstRow("SHOW FULL COLUMNS FROM `" . $table . "` WHERE Field = '" . $field . "'") ? true : false; }

}