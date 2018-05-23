<?php namespace Phlex\Form\Validator;

class ValidatorResult {

	protected $errors = [];
	protected $status = true;

	public function getStatus(){
		return $this->status;
	}

	public function getErrors(){
		return $this->errors;
	}

	public function error($fieldName, $message){
		$this->status = false;
		if(!array_key_exists($fieldName, $this->errors)){
			$this->errors[$fieldName] = [];
		}
		$this->errors[$fieldName] = $message;
	}

}