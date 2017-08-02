<?php namespace Phlex\Database;

use Phlex\Sys\ServiceManager;

class DataSource{

	/** @var  Access */
	protected $access;

	/** @var string */
	protected $table;

	/** @return \Phlex\Database\Access */
	public function getAccess() { return $this->access; }

	/** @return string */
	public function getTable() { return $this->table; }

	public function __construct($table, $database = 'database') {
		$this->access = ServiceManager::get('database');
		$this->table = $table;
	}

	public function pick($id){
		return $this->access->getRowById($this->table, $id);
	}

	public function collect(array $ids){
		return $this->access->getRows("SELECT * FROM " . $this->access->escapeSQLEntity($this->table) . " WHERE id IN ($1)", $ids);
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