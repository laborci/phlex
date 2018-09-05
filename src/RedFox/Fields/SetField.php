<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class SetField extends Field{

	protected $options;

	public function __construct($entityClass, $name, $options) {
		parent::__construct($entityClass, $name);
		$this->options = $options;
	}

	public function getDataType(){return 'array';}

	public function getOptions(){ return $this->options; }

	public function import($value){ return explode(',',$value);}
	public function export($value){ return join(',',$value); }

	public function set($value){

		if(count(array_diff($value, $this->options))){
			throw new \Exception('Set Field type set error');
		}
		return $value;
	}

}