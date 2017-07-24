<?php namespace Phlex\RedFox;


abstract class Field {

	const READONLY = 0; // only readable
	const WRITE    = 1; // can be written
	const CONSTANT = 2; // can be written if value is null

	private $nullable   = true;
	private $permission = 1;
	private $dbtype;

	function __construct($dbtype) {
		$this->dbtype = $dbtype;
	}

	public function isWritable() { return $this->permission == static::WRITE; }
	public function isConstant() { return $this->permission == static::CONSTANT; }
	public function isNullable() { return $this->nullable; }
	public function getDbType(){return $this->dbtype;}

	public function readonyl() { $this->permission = static::READONLY; return $this;}
	public function constant() { $this->permission = static::CONSTANT; return $this;}
	public function notNullable() { $this->nullable = false; return $this;}

	public function import($value) { return $value; }
	public function export($value) { return $value; }
	public function set($value) { return $value; }

	public function setValue($value){ return $this->set($value); }

	abstract public function getDataType();

}