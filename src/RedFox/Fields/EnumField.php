<?php namespace Phlex\RedFox\Fields;


use Phlex\RedFox\Field;

class EnumField extends Field {

	protected $options;

	public function __construct($options) {
		$this->options = $options;
	}

	public function getDataType(){return 'string';}

	public function getOptions(){ return $this->options; }
	public function set($value) {
		if(!in_array($value, $this->options)) {
			throw new \Exception('Enum Field type set error');
		}
		return $value;
	}

}