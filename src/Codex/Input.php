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
}
