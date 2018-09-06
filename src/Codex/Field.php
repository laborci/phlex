<?php namespace Phlex\Codex;

class Field {

	public $field;
	public $label;
	public $default;

	/** @var Validation\Validator[] */
	protected $validators = [];

	public function __construct($field, $label = null, $default = null) {
		$this->field = $field;
		$this->label = is_null($label) ? $field : $label;
		$this->default = $default;
	}

	public function addValidator(Validation\Validator $validator): Field {
		$validator->setField($this);
		$this->validators[] = $validator;
		return $this;
	}

	public function validate($value) {
		$results = [];
		foreach ($this->validators as $validator) {
			$results[] = $validator->validate($value);
		}
		return $results;
	}

	public function setDefault($default){
		$this->default = $default;
	}
}
