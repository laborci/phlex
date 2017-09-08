<?php namespace Phlex\RedFox;

abstract class Field {

	private $readonly = false;

	public function readonly(bool $set = false){
		if($set) $this->readonly = true;
		return $this->readonly;
	}

	public function import($value) { return $value; }
	public function export($value) { return $value; }
	public function set($value) { return $value; }

	//public function setValue($value){ return $this->set($value); }

	abstract public function getDataType();

}