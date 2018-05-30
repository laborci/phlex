<?php namespace Phlex\Form;


use Phlex\Form\Validator\ValidatorResult;

class Form{

	/** @var FormField[] */
	public $fields = [];

	/** @var bool  */
	public $hasAttachments = false;

	public function add($field):FormField{
		if(array_key_exists($field, $this->fields)){
			trigger_error('Duplicate formfield definition', E_USER_WARNING);
		}
		$formField  = new FormField($field);
		$this->fields[$field] = $formField;
		return $formField;
	}

	public function applyBindings($data){
		foreach ($this->fields as $field){
			if($field->bind){
				$data[$field->bind] = $field->value;
			}
		}
		return $data;
	}

	public function fill($data){
		foreach ($this->fields as $field){
			if(array_key_exists($field->name, $data)){
				$field->value = $data[$field->name];
			}
		}
	}

	public function validate($data){

		$result = new ValidatorResult();

		foreach ($this->fields as $field){
			if(array_key_exists($field->name, $data)){
				$field->validate($data[$field->name], $result);
			}
		}

		return $result;

	}

}