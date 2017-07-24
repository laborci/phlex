<?php namespace Phlex\Database;

// Todo: implement as ArrayAccess

class Storage{
	
	protected $__cache = array();
	protected $access;
	protected $storageTable;

	public function __construct(Access $access, $storageTable) {
		$this->access = $access;
		$this->storageTable = $storageTable;
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
			$data = $this->access->getValue("SELECT value FROM ".$this->storageTable." WHERE `key`=$1", $key);
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
			$this->access->query("INSERT INTO " . $this->storageTable . " (`key`, `value`) VALUES ($1, $2) ON DUPLICATE KEY UPDATE `value`=$2", $key, $value);
		}
	}

	/**
	 * @param int|array $keys
	 */
	public function del($key) {
		$keys = !is_array($key)?[$key]:$key;
		if ($keys) {
			foreach ($keys as $key) if (array_key_exists($key, $this->__cache)) unset($this->__cache[$key]);
			$this->access->Delete($this->storageTable, '`key` IN ($1)', $keys);
		}
	}
	
	public function getKeys($sqlPattern = null) {
		$extension = $sqlPattern === null?'':" WHERE `key` LIKE $1 ";
		return $this->access->getRows("SELECT `key` AS __VALUE__ FROM `".$this->storageTable."`".$extension, $sqlPattern);
	}
	
	public function getValuesByPrefix($prefix) {
		return $this->access->getRows("SELECT `key` AS __KEY__, `value` AS __VALUE FROM `".$this->storageTable."` WHERE `key` LIKE $1 ", $prefix.'%');
	}

}
