<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class SetField extends Field{

	public function getDataType(){return 'array';}

	protected $options;

	public function setOptions(array $options){ $this->options = $options; return $this; }
	public function getOptions(){ return $this->options; }

	public function import($value){
		return explode(',',$value);
	}

	public function export($value){
		return join(',',$value);
	}

	public function set($value){
		echo 'itt';
		print_r($value);
		if(count(array_diff($value, $this->options))){
			throw new \Exception('Set Field type set error');
		}
		return $value;
	}

}