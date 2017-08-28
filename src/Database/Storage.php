<?php namespace Phlex\Database;

// Todo: implement as ArrayAccess

use App\ServiceManager;

class Storage{
	
	protected $__cache = array();
	/** @var  Access */
	protected $access;
	protected $table;

	public function __construct(string $database, $table) {
		$this->access = ServiceManager::get($database);
		$this->table = $table;
	}

	function __invoke($key, $value = null) {
		if(func_num_args() == 1){
			return $this->get($key);
		}else{
			$this->set($key, $value);
			return null;
		}
	}

	public function get($key){
		if (!array_key_exists($key, $this->__cache)) {
			$data = $this->access->getValue("SELECT value FROM ".$this->table." WHERE `key`=$1", $key);
			if(!$data) return null;
			$this->__cache[$key] = unserialize($data);
		}
		return $this->__cache[$key];
	}
	
	public function set($key, $value) {
		if ($value === null) {
			$this->del($key);
		} else {
			$this->__cache[$key] = $value;
			$value = serialize($value);
			$this->access->query("INSERT INTO " . $this->table . " (`key`, `value`) VALUES ($1, $2) ON DUPLICATE KEY UPDATE `value`=$2", $key, $value);
		}
	}

	/**
	 * @param int|array $keys
	 */
	public function del($key) {
		$keys = !is_array($key)?[$key]:$key;
		if ($keys) {
			foreach ($keys as $key) if (array_key_exists($key, $this->__cache)) unset($this->__cache[$key]);
			$this->access->delete($this->table, Filter::where("`key` in $1", $keys));
		}
	}
	
	public function getKeys($sqlPattern = null) {
		$extension = $sqlPattern === null?'':" WHERE `key` LIKE $1 ";
		return $this->access->getValues("SELECT `key` FROM `".$this->table."`".$extension, $sqlPattern);
	}
	
	public function getValuesByPrefix($prefix) {
		return $this->access->getValuesWithKey("SELECT `key`, `value` FROM `".$this->table."` WHERE `key` LIKE $1 ", $prefix.'%');
	}

}
