<?php namespace Phlex\Database;

use Phlex\Sys\ServiceManager;

class DataSource{

	/** @var  Access */
	protected $access;
	/** @var string */
	protected $table;
	/** @var  string */
	protected $database;

	public function getAccess():Access { return $this->access; }
	public function getTable():string { return $this->table; }
	public function getDatabase():string { return $this->database; }

	public function __construct($table, $database) {
		$this->access = ServiceManager::get($database);
		$this->database = $database;
		$this->table = $table;
	}

	public function pick($id){
		return $this->access->getRowById($this->table, $id);
	}

	public function collect(array $ids){
		return $this->access->getRowsById($this->table, $ids);
	}

	public function insert(array $data){
		return $this->access->insert($this->table, $data);
	}

	public function update($id, array $data){
		return $this->access->update($this->table, $data, $id);
	}

	public function delete($id){
		return $this->access->delete($this->table, $id);
	}

}