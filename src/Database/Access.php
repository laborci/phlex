<?php namespace Phlex\Database;

use PDO;
use Phlex\Sys\ServiceManager;
use Psr\Log\LoggerInterface;


class Access {
	/** @var \PDO */
	private $connection;
	/** @var string */
	private $database;
	/** @var  LoggerInterface */
	private $logger;

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

	private function execute($sql, ...$sqlParams) {
		$sql = $this->buildSQL($sql, $sqlParams);
		if(!is_null($this->logger)) $this->logger->debug($sql, ['method'=>debug_backtrace()[1]['function']]);
		return $this->connection->query($sql);
	}

	public function query(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams); }

	public function getValue(string $sql, ...$sqlParams) { $row = $this->getRow($sql, ...$sqlParams); return $row ? reset($row) : null; }
	public function getRow(string $sql, ...$sqlParams) { return $this->getFirstRow($sql.(stripos($sql, ' LIMIT ') === false ? ' LIMIT 1' : ''), ...$sqlParams); }
	protected function getFirstRow(string $sql, ...$sqlParams) { return $this->execute($sql, $sqlParams)->fetch(PDO::FETCH_ASSOC); }

	public function getRowById(string $table, int $id) { return $this->getRow("SELECT * FROM ".$this->escapeSQLEntity($table)." WHERE id=".$this->quote($id)); }
	public function getRowsById(string $table, array $ids) { return $this->getRows('SELECT * FROM '.$this->escapeSQLEntity($table).' WHERE  id IN ('.join(',', $this->quoteArray($ids)).')'); }
	public function getValues(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_COLUMN, 0); }

	public function getRows(string $sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_ASSOC); }
	public function getValuesWithKey($sql, ...$sqlParams) { return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_KEY_PAIR); }
	public function getRowsWithKey($sql, ...$sqlParams){ return $this->execute($sql, ...$sqlParams)->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_ASSOC); }

	#region insert / update / delete
	public function insert(string $table, array $data, bool $ignore = false) {
		foreach ($data as $key => $value){
			if($key[0] === '!'){
				$key = substr($key, 1);
			}else{
				$value = $this->quote($value);
			}
			$data[$key] = [$this->escapeSQLEntity($key), $value];
		}
		$this->execute( $ignore === true ? 'INSERT IGNORE ' : 'INSERT '.
				'INTO '.$this->escapeSQLEntity($table).' '.
				'('.implode(', ', array_column($data, 0)).') '.
				'VALUE ('.implode(', ', array_column($data, 1)).')'
		);
		return $this->connection->lastInsertId();
	}


	public function update(string $table, Filter $filter, array $data):int {
		foreach ($data as $key=>$value){
			if($key[0] === '!'){
				$key = substr($key, 1);
			}else{
				$value = $this->quote($value);
			}
			$data[$key] = $this->escapeSQLEntity($key).'='.$value;
		}
		return $this->execute("UPDATE ".$this->escapeSQLEntity($table)." SET ".implode(", ", $data).' WHERE '.$filter->GetSql($this))->rowCount();
	}
	public function updateById(string $table, int $id, array $data):int { return $this->update($table, Filter::where('id=$1', $id), $data); }

	public function delete(string $table, Filter $filter):int { return $this->execute("DELETE FROM ".$this->escapeSQLEntity($table)." WHERE ".$filter->GetSql($this))->rowCount(); }
	public function deleteById(string $table, int $id):int { return $this->delete($table, Filter::where('id=$1', $id)); }
	#endregion

	#region escape & quote
	public function buildSQL(string $sql, array $sqlParams = []):string {
		if(count($sqlParams)) {
			foreach($sqlParams as $key => $param) {
				$param = is_array($param) ? join(',', $this->quoteArray($param)) : $this->quote($param);
				$sql = str_replace('$'.($key+1), $param, $sql);
			}
		}
		return $sql;
	}

	public function quote($subject, bool $quote = true):string { return $subject === null ? 'NULL' : ($quote ? $this->connection->quote($subject) : trim($this->connection->quote($subject),"'")); }
	public function quoteArray(array $array, bool $quote = true):array { return array_map(function($val) use ($quote) {return $this->quote($val, $quote);}, $array); }
	public function escapeSQLEntity($subject):string { return '`'.$subject.'`'; }
	public function escapeSQLEntities(array $array):array { return array_map(function($val){return $this->escapeSQLEntity($val);}, $array); }
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
	public function renameTable(string $from, string $to):void { $this->execute("RENAME TABLE " . $this->escapeSQLEntity($from) . " TO " . $this->escapeSQLEntity($to)); 	}
	public function addTable(string $table, string $properties):void {$this->execute("CREATE TABLE ".$this->escapeSQLEntity($table)." " . $properties); }
	public function deleteTable(string $table):void { $this->execute("DROP TABLE ".$this->escapeSQLEntity($table)); }
	public function addView(string $view, string $select):void { $this->execute("CREATE VIEW ".$this->escapeSQLEntity($view)." AS ".$select); }
	public function deleteView(string $view):void { $this->execute("DROP VIEW IF EXISTS `" . $view . "`"); }
	#endregion

	#region field manipulation
	public function fieldExists(string $table, string $field):bool { return $this->getFirstRow("SHOW FULL COLUMNS FROM `" . $table . "` WHERE Field = '" . $field . "'") ? true : false; }
	public function addField(string $table, string $field, string $properties):void { $this->execute("ALTER TABLE ".$this->escapeSQLEntity($table)." ADD ".$this->escapeSQLEntity($field)." ".$properties); }
	public function deleteField(string $table, string $field):void { $this->execute("ALTER TABLE ".$this->escapeSQLEntity($table)." DROP ".$this->escapeSQLEntity($field)); }
	public function getFieldList(string $table):array { return array_column($this->getFieldData($table), 'Field'); }
	public function getFieldData(string $table):array { return $this->getRows("SHOW FULL COLUMNS FROM ".$this->escapeSQLEntity($table)); }
	public function getEnumValues(string $tableName, string $field):array { preg_match_all("/'(.*?)'/", $this->getRows("DESCRIBE ".$this->escapeSQLEntity($tableName)." ".$this->quote($field))[0]['Type'], $matches); return $matches[1]; }
	#endregion
}