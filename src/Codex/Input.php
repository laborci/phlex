<?php namespace Phlex\Codex;

class Input {

	protected $type;
	protected $label;
	protected $field;
	protected $options;

	public function __construct($type, $label, $field, $options = []) {
		$this->type = $type;
		$this->label = $label;
		$this->field = $field;
		$this->options = $options;
	}

	public function get(){
		return [
			'label'=>$this->label,
			'type'=>$this->type,
			'field'=>$this->field,
			'options'=>$this->options
		];
	}

	public function __set($name, $value) {
		if(substr($name,0, 3) === 'opt'){
			$this->options[strtolower(substr($name, 3))] = $value;
		}
	}
	public function __call($name, $arguments) {
		if(substr($name,0, 3) === 'opt'){
			$this->options[strtolower(substr($name, 3))] = $arguments[0];
			return $this;
		}
	}



}
