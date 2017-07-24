<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class EnumField extends Field {

	public function getDataType(){return 'string';}

	protected $options;
	public function setOptions(array $options){ $this->options = $options; return $this; }
	public function getOptions(){ return $this->options; }


	public function set($value) {
		if(!in_array($value, $this->options)) {
			throw new \Exception('Enum Field type set error');
		}
		return $value;
	}

}